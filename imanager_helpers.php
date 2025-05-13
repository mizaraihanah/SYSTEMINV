<?php
// Function to generate a unique product ID
function generateProductID($conn) {
    $prefix = "PROD";
    $sql = "SELECT MAX(CAST(SUBSTRING(product_id, 5) AS UNSIGNED)) as max_id FROM products WHERE product_id LIKE 'PROD%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $maxID = $row['max_id'] ?? 0;
    $newID = $maxID + 1;
    return $prefix . str_pad($newID, 4, '0', STR_PAD_LEFT);
}

// Function to log inventory activities
function logInventoryActivity($conn, $userID, $action, $itemID, $details) {
    $sql = "INSERT INTO inventory_logs (user_id, action, item_id, action_details, timestamp, ip_address) 
            VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("sssss", $userID, $action, $itemID, $details, $ipAddress);
    $stmt->execute();
    $stmt->close();
}

// Function to get count of products below threshold
function getLowStockCount($conn) {
    $threshold_sql = "SELECT COUNT(*) as low_stock_count FROM products WHERE stock_quantity <= reorder_threshold";
    $threshold_result = $conn->query($threshold_sql);
    $threshold_row = $threshold_result->fetch_assoc();
    return $threshold_row['low_stock_count'];
}
?>