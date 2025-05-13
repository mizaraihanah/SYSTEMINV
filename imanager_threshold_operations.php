<?php
// Update Reorder Thresholds
function updateThresholds($conn, $userID) {
    $productIds = $_POST['threshold_product_id'];
    $thresholds = $_POST['threshold_value'];
    
    $sql = "UPDATE products SET reorder_threshold = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    
    $successCount = 0;
    for ($i = 0; $i < count($productIds); $i++) {
        if (!empty($productIds[$i]) && isset($thresholds[$i])) {
            $stmt->bind_param("is", $thresholds[$i], $productIds[$i]);
            if ($stmt->execute()) {
                logInventoryActivity($conn, $userID, "update_threshold", $productIds[$i], "Updated reorder threshold to: {$thresholds[$i]}");
                $successCount++;
            }
        }
    }
    
    if ($successCount > 0) {
        header("Location: imanager_invmanagement.php?success=Updated reorder thresholds for $successCount products");
    } else {
        header("Location: imanager_invmanagement.php?error=Failed to update reorder thresholds");
    }
    $stmt->close();
    exit();
}
?>