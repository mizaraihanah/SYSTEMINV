<?php
// Add New Product
function addProduct($conn, $userID) {
    $productName = trim($_POST['product_name']);
    $productDesc = trim($_POST['product_description']);
    $categoryId = $_POST['category_id'];
    $stockQuantity = $_POST['stock_quantity'];
    $reorderThreshold = $_POST['reorder_threshold'];
    $unitPrice = $_POST['unit_price'];
    $productId = generateProductID($conn);
    
    if (!empty($productName) && !empty($categoryId)) {
        $sql = "INSERT INTO products (product_id, product_name, description, category_id, stock_quantity, reorder_threshold, unit_price, last_updated) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiids", $productId, $productName, $productDesc, $categoryId, $stockQuantity, $reorderThreshold, $unitPrice);
        
        if ($stmt->execute()) {
            logInventoryActivity($conn, $userID, "add_product", $productId, "Added new product: $productName");
            header("Location: imanager_invmanagement.php?success=Product added successfully");
        } else {
            header("Location: imanager_invmanagement.php?error=Failed to add product");
        }
        $stmt->close();
        exit();
    }
}

// Update Product
function updateProduct($conn, $userID) {
    $productId = $_POST['product_id'];
    $productName = trim($_POST['product_name']);
    $productDesc = trim($_POST['product_description']);
    $categoryId = $_POST['category_id'];
    $stockQuantity = $_POST['stock_quantity'];
    $reorderThreshold = $_POST['reorder_threshold'];
    $unitPrice = $_POST['unit_price'];
    
    if (!empty($productId) && !empty($productName)) {
        $sql = "UPDATE products 
                SET product_name = ?, description = ?, category_id = ?, 
                    stock_quantity = ?, reorder_threshold = ?, unit_price = ?, last_updated = NOW() 
                WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiids", $productName, $productDesc, $categoryId, $stockQuantity, $reorderThreshold, $unitPrice, $productId);
        
        if ($stmt->execute()) {
            logInventoryActivity($conn, $userID, "update_product", $productId, "Updated product: $productName");
            header("Location: imanager_invmanagement.php?success=Product updated successfully");
        } else {
            header("Location: imanager_invmanagement.php?error=Failed to update product: " . $stmt->error);
        }
        $stmt->close();
        exit();
    }
}

// Delete Product
function deleteProduct($conn, $userID) {
    $productId = $_POST['product_id'];
    
    // Check if product is in any orders
    $checkSql = "SELECT COUNT(*) AS order_count FROM order_items WHERE product_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();
    
    if ($row['order_count'] > 0) {
        header("Location: imanager_invmanagement.php?error=Cannot delete product that is in use by orders");
        $checkStmt->close();
        exit();
    }
    
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productId);
    
    if ($stmt->execute()) {
        logInventoryActivity($conn, $userID, "delete_product", $productId, "Deleted product ID: $productId");
        header("Location: imanager_invmanagement.php?success=Product deleted successfully");
    } else {
        header("Location: imanager_invmanagement.php?error=Failed to delete product");
    }
    $stmt->close();
    exit();
}
?>