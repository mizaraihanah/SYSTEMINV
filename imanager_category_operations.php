<?php
// Add New Product Category
function addCategory($conn, $userID) {
    $categoryName = trim($_POST['category_name']);
    $categoryDesc = trim($_POST['category_description']);
    
    if (!empty($categoryName)) {
        $sql = "INSERT INTO product_categories (category_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $categoryName, $categoryDesc);
        
        if ($stmt->execute()) {
            $categoryId = $conn->insert_id;
            logInventoryActivity($conn, $userID, "add_category", $categoryId, "Added new category: $categoryName");
            header("Location: imanager_invmanagement.php?success=Category added successfully");
        } else {
            header("Location: imanager_invmanagement.php?error=Failed to add category");
        }
        $stmt->close();
        exit();
    }
}

// Update Product Category
function updateCategory($conn, $userID) {
    $categoryId = $_POST['category_id'];
    $categoryName = trim($_POST['category_name']);
    $categoryDesc = trim($_POST['category_description']);
    
    if (!empty($categoryId) && !empty($categoryName)) {
        $sql = "UPDATE product_categories SET category_name = ?, description = ? WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $categoryName, $categoryDesc, $categoryId);
        
        if ($stmt->execute()) {
            logInventoryActivity($conn, $userID, "update_category", $categoryId, "Updated category: $categoryName");
            header("Location: imanager_invmanagement.php?success=Category updated successfully");
        } else {
            header("Location: imanager_invmanagement.php?error=Failed to update category");
        }
        $stmt->close();
        exit();
    }
}

// Delete Product Category
function deleteCategory($conn, $userID) {
    $categoryId = $_POST['category_id'];
    
    // Check if category is in use
    $checkSql = "SELECT COUNT(*) AS product_count FROM products WHERE category_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $categoryId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();
    
    if ($row['product_count'] > 0) {
        header("Location: imanager_invmanagement.php?error=Cannot delete category that is in use by products");
        $checkStmt->close();
        exit();
    }
    
    $sql = "DELETE FROM product_categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    
    if ($stmt->execute()) {
        logInventoryActivity($conn, $userID, "delete_category", $categoryId, "Deleted category ID: $categoryId");
        header("Location: imanager_invmanagement.php?success=Category deleted successfully");
    } else {
        header("Location: imanager_invmanagement.php?error=Failed to delete category");
    }
    $stmt->close();
    exit();
}
?>