<?php
// Fetch product categories
function fetchCategories($conn) {
    $sql = "SELECT * FROM product_categories ORDER BY category_name";
    return $conn->query($sql);
}

// Fetch products with their categories
function fetchProducts($conn) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN product_categories c ON p.category_id = c.category_id 
            ORDER BY p.product_name";
    return $conn->query($sql);
}

// Fetch inventory logs
function fetchLogs($conn, $limit = 100) {
    // Use direct query instead of prepared statement for simplicity
    $sql = "SELECT l.*, u.fullName 
            FROM inventory_logs l 
            JOIN users u ON l.user_id = u.userID 
            ORDER BY l.timestamp DESC 
            LIMIT $limit";
    return $conn->query($sql);
    
    /* Alternative with proper error handling for prepared statement:
    $sql = "SELECT l.*, u.fullName 
            FROM inventory_logs l 
            JOIN users u ON l.user_id = u.userID 
            ORDER BY l.timestamp DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Handle preparation failure - return empty result
        return $conn->query("SELECT 1 LIMIT 0"); // Returns empty result set
    }
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
    */
}

// Fetch orders
function fetchOrders($conn) {
    $sql = "SELECT o.*, u.fullName 
            FROM orders o 
            LEFT JOIN users u ON o.created_by = u.userID 
            ORDER BY o.order_date DESC";
    return $conn->query($sql);
}
?>