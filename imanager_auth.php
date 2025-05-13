<?php
// Only start session if one doesn't already exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication and session management
function authenticate() {
    // Redirect to login page if user is not logged in
    if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Inventory Manager') {
        header("Location: login.html");
        exit();
    }
}

// Get user full name
function getUserFullName($conn, $userID) {
    $sql = "SELECT fullName FROM users WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Inventory Manager"; // Fallback if prepare fails
    }
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fullName = $row['fullName'];
    } else {
        $fullName = "Inventory Manager";
    }
    
    $stmt->close();
    return $fullName;
}
?>