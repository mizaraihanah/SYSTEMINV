<?php
// Create New Order
function addOrder($conn, $userID) {
    $orderType = $_POST['order_type'];
    $customerName = trim($_POST['customer_name']);
    $orderDate = $_POST['order_date'];
    $status = 'Pending';
    
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $orderIdPrefix = ($orderType == 'Purchase') ? 'PO' : 'SO';
        $orderIdQuery = "SELECT MAX(CAST(SUBSTRING(order_id, 3) AS UNSIGNED)) as max_id FROM orders WHERE order_id LIKE '$orderIdPrefix%'";
        $orderIdResult = $conn->query($orderIdQuery);
        $orderIdRow = $orderIdResult->fetch_assoc();
        $maxOrderId = $orderIdRow['max_id'] ?? 0;
        $newOrderId = $orderIdPrefix . str_pad($maxOrderId + 1, 6, '0', STR_PAD_LEFT);
        
        $orderSql = "INSERT INTO orders (order_id, order_type, customer_name, order_date, status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bind_param("ssssss", $newOrderId, $orderType, $customerName, $orderDate, $status, $userID);
        $orderStmt->execute();
        
        // Insert order items
        $productIds = $_POST['product_id'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];
        
        $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
        $itemStmt = $conn->prepare($itemSql);
        
        $totalAmount = 0;
        
        for ($i = 0; $i < count($productIds); $i++) {
            if (!empty($productIds[$i]) && !empty($quantities[$i]) && !empty($prices[$i])) {
                $itemStmt->bind_param("ssid", $newOrderId, $productIds[$i], $quantities[$i], $prices[$i]);
                $itemStmt->execute();
                
                $totalAmount += $quantities[$i] * $prices[$i];
                
                // Update inventory for Purchase orders
                if ($orderType == 'Purchase') {
                    $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity + ?, last_updated = NOW() WHERE product_id = ?";
                    $updateStockStmt = $conn->prepare($updateStockSql);
                    $updateStockStmt->bind_param("is", $quantities[$i], $productIds[$i]);
                    $updateStockStmt->execute();
                    
                    logInventoryActivity($conn, $userID, "stock_update", $productIds[$i], "Increased stock by {$quantities[$i]} via purchase order $newOrderId");
                    $updateStockStmt->close();
                }
                // Update inventory for Sales orders
                else if ($orderType == 'Sales') {
                    $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity - ?, last_updated = NOW() WHERE product_id = ?";
                    $updateStockStmt = $conn->prepare($updateStockSql);
                    $updateStockStmt->bind_param("is", $quantities[$i], $productIds[$i]);
                    $updateStockStmt->execute();
                    
                    logInventoryActivity($conn, $userID, "stock_update", $productIds[$i], "Decreased stock by {$quantities[$i]} via sales order $newOrderId");
                    $updateStockStmt->close();
                }
            }
        }
        
        // Update total amount
        $updateOrderSql = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
        $updateOrderStmt = $conn->prepare($updateOrderSql);
        $updateOrderStmt->bind_param("ds", $totalAmount, $newOrderId);
        $updateOrderStmt->execute();
        
        logInventoryActivity($conn, $userID, "create_order", $newOrderId, "Created new $orderType order: $newOrderId");
        
        $conn->commit();
        header("Location: imanager_invmanagement.php?success=Order created successfully");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: imanager_invmanagement.php?error=Failed to create order: " . $e->getMessage());
    }
    exit();
}

// Update Order Status
function updateOrderStatus($conn, $userID) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $newStatus, $orderId);
    
    if ($stmt->execute()) {
        logInventoryActivity($conn, $userID, "update_order", $orderId, "Updated order status to: $newStatus");
        header("Location: imanager_invmanagement.php?success=Order status updated successfully");
    } else {
        header("Location: imanager_invmanagement.php?error=Failed to update order status");
    }
    $stmt->close();
    exit();
}

// Delete Order
function deleteOrder($conn, $userID) {
    $orderId = $_POST['order_id'];
    
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Get order type
        $orderTypeSql = "SELECT order_type FROM orders WHERE order_id = ?";
        $orderTypeStmt = $conn->prepare($orderTypeSql);
        $orderTypeStmt->bind_param("s", $orderId);
        $orderTypeStmt->execute();
        $orderTypeResult = $orderTypeStmt->get_result();
        $orderTypeRow = $orderTypeResult->fetch_assoc();
        $orderType = $orderTypeRow['order_type'];
        
        // Get order items
        $itemsSql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
        $itemsStmt = $conn->prepare($itemsSql);
        $itemsStmt->bind_param("s", $orderId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        // Revert inventory changes
        while ($itemRow = $itemsResult->fetch_assoc()) {
            $productId = $itemRow['product_id'];
            $quantity = $itemRow['quantity'];
            
            if ($orderType == 'Purchase') {
                // If deleting a purchase, subtract the quantity
                $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity - ?, last_updated = NOW() WHERE product_id = ?";
            } else {
                // If deleting a sale, add the quantity back
                $updateStockSql = "UPDATE products SET stock_quantity = stock_quantity + ?, last_updated = NOW() WHERE product_id = ?";
            }
            
            $updateStockStmt = $conn->prepare($updateStockSql);
            $updateStockStmt->bind_param("is", $quantity, $productId);
            $updateStockStmt->execute();
            
            $action = ($orderType == 'Purchase') ? "Decreased" : "Increased";
            logInventoryActivity($conn, $userID, "stock_update", $productId, "$action stock by $quantity due to deletion of $orderType order $orderId");
            $updateStockStmt->close();
        }
        
        // Delete order items
        $deleteItemsSql = "DELETE FROM order_items WHERE order_id = ?";
        $deleteItemsStmt = $conn->prepare($deleteItemsSql);
        $deleteItemsStmt->bind_param("s", $orderId);
        $deleteItemsStmt->execute();
        
        // Delete order
        $deleteOrderSql = "DELETE FROM orders WHERE order_id = ?";
        $deleteOrderStmt = $conn->prepare($deleteOrderSql);
        $deleteOrderStmt->bind_param("s", $orderId);
        $deleteOrderStmt->execute();
        
        logInventoryActivity($conn, $userID, "delete_order", $orderId, "Deleted $orderType order: $orderId");
        
        $conn->commit();
        header("Location: imanager_invmanagement.php?success=Order deleted successfully");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: imanager_invmanagement.php?error=Failed to delete order: " . $e->getMessage());
    }
    exit();
}
?>