<?php
// Only start the session in the main file
session_start();

// Include required files
include 'db_connection.php';
include 'imanager_auth.php';
include 'imanager_helpers.php';
include 'imanager_data.php';
include 'imanager_category_operations.php';
include 'imanager_product_operations.php';
include 'imanager_order_operations.php';
include 'imanager_threshold_operations.php';

// Authenticate user
authenticate();

// Get user information
$userID = $_SESSION['userID'];
$fullName = getUserFullName($conn, $userID);

// Fetch data with error handling
$result_categories = fetchCategories($conn);
$result_products = fetchProducts($conn);
$result_logs = fetchLogs($conn);
$result_orders = fetchOrders($conn);

// Get low stock count with error handling
$low_stock_count = getLowStockCount($conn);

// Process form submissions based on post parameters
// ... rest of your code
// Process form submissions based on post parameters
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Category operations
    if (isset($_POST['add_category'])) {
        addCategory($conn, $userID);
    }
    
    if (isset($_POST['update_category'])) {
        updateCategory($conn, $userID);
    }
    
    if (isset($_POST['delete_category'])) {
        deleteCategory($conn, $userID);
    }
    
    // Product operations
    if (isset($_POST['add_product'])) {
        addProduct($conn, $userID);
    }
    
    if (isset($_POST['update_product'])) {
        updateProduct($conn, $userID);
    }
    
    if (isset($_POST['delete_product'])) {
        deleteProduct($conn, $userID);
    }
    
    // Order operations
    if (isset($_POST['add_order'])) {
        addOrder($conn, $userID);
    }
    
    if (isset($_POST['update_order_status'])) {
        updateOrderStatus($conn, $userID);
    }
    
    if (isset($_POST['delete_order'])) {
        deleteOrder($conn, $userID);
    }
    
    // Threshold operations
    if (isset($_POST['update_thresholds'])) {
        updateThresholds($conn, $userID);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>

    <!-- Your existing CSS and other script includes -->
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="imanager_invmanagement.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>

<body>
    <div class="sidebar-container">
        <div class="header-section">
            <div class="company-logo">
                <img src="image/icon/logo.png" class="logo-icon" alt="Company Logo">
                <div class="company-text">
                    <span class="company-name">RotiSeri</span>
                    <span class="company-name2">Inventory</span>
                </div>
            </div>

            <nav class="nav-container" role="navigation">
                <a href="imanager_dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <div class="nav-text">Home</div>
                </a>
                <a href="imanager_invmanagement.php" class="nav-item active">
                    <i class="fas fa-boxes"></i>
                    <div class="nav-text">Manage Inventory</div>
                </a>
                <a href="imanager_supplierpurchase.php" class="nav-item">
                    <i class="fas fa-truck-loading"></i>
                    <div class="nav-text">View Supplier Purchases</div>
                </a>
                <a href="imanager_salesreport.php" class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <div class="nav-text">Sales Report</div>
                </a>
                <a href="profile.php" class="nav-item">
                    <i class="fas fa-user-circle"></i>
                    <div class="nav-text">My Profile</div>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <div class="nav-text">Log Out</div>
                </a>
            </nav>
        </div>

        <div class="footer-section"></div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Inventory Management</h1>
            <p>Welcome, <?php echo htmlspecialchars($fullName); ?>!</p>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Inventory Overview -->
        <div class="inventory-overview">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-tachometer-alt"></i> Inventory Dashboard</h2>
                    <div class="card-actions">
                        <button class="export-btn" onclick="exportProductsPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                        <button class="export-btn" onclick="exportProductsExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    </div>
                </div>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <i class="fas fa-boxes"></i>
                        <div class="stat-info">
                            <h3>Total Products</h3>
                            <p><?php echo $result_products->num_rows; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-tags"></i>
                        <div class="stat-info">
                            <h3>Categories</h3>
                            <p><?php echo $result_categories->num_rows; ?></p>
                        </div>
                    </div>
                    <div class="stat-card <?php echo ($low_stock_count > 0) ? 'alert-status' : ''; ?>">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="stat-info">
                            <h3>Low Stock Items</h3>
                            <p><?php echo $low_stock_count; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-shopping-cart"></i>
                        <div class="stat-info">
                            <h3>Orders</h3>
                            <p><?php echo $result_orders->num_rows; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
           <button class="tab-button active">Products</button>
           <button class="tab-button">Categories</button>
           <button class="tab-button">Orders</button>
           <button class="tab-button">Stock Levels</button>
           <button class="tab-button">Activity Logs</button>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-box"></i> Manage Products</h2>
                    <button id="addProductButton" class="add-btn"><i class="fas fa-plus"></i> Add Product</button>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Stock Qty</th>
                                <th>Reorder Level</th>
                                <th>Unit Price</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $result_products->fetch_assoc()): ?>
                                <tr class="<?php echo ($product['stock_quantity'] <= $product['reorder_threshold']) ? 'low-stock' : ''; ?>">
                                    <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($product['reorder_threshold']); ?></td>
                                    <td>RM <?php echo number_format($product['unit_price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['last_updated']); ?></td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick="showEditProductModal('<?php echo $product['product_id']; ?>', '<?php echo addslashes($product['product_name']); ?>', '<?php echo addslashes($product['description']); ?>', '<?php echo $product['category_id']; ?>', '<?php echo $product['stock_quantity']; ?>', '<?php echo $product['reorder_threshold']; ?>', '<?php echo $product['unit_price']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" onclick="confirmDeleteProduct('<?php echo $product['product_id']; ?>', '<?php echo addslashes($product['product_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-tags"></i> Manage Categories</h2>
                    <button class="add-btn" onclick="showAddCategoryModal()"><i class="fas fa-plus"></i> Add Category</button>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result_categories) {
                                $result_categories->data_seek(0); // Reset result pointer
                                while ($category = $result_categories->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td>
                                        <button class="action-btn edit-btn" onclick="showEditCategoryModal('<?php echo $category['category_id']; ?>', '<?php echo addslashes($category['category_name']); ?>', '<?php echo addslashes($category['description']); ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" onclick="confirmDeleteCategory('<?php echo $category['category_id']; ?>', '<?php echo addslashes($category['category_name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-cart"></i> Manage Orders</h2>
                    <button class="add-btn" onclick="showAddOrderModal()"><i class="fas fa-plus"></i> Create Order</button>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Type</th>
                                <th>Customer/Supplier</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result_orders) {
                                while ($order = $result_orders->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_type']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                    <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                                    <td>
                                        <button class="action-btn view-btn" onclick="viewOrderDetails('<?php echo $order['order_id']; ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit-btn" onclick="showUpdateOrderStatusModal('<?php echo $order['order_id']; ?>', '<?php echo $order['status']; ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete-btn" onclick="confirmDeleteOrder('<?php echo $order['order_id']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stock Levels Tab -->
        <div id="stock-levels" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-line"></i> Stock Levels Monitoring</h2>
                    <button class="add-btn" onclick="showUpdateThresholdsModal()"><i class="fas fa-cog"></i> Update Thresholds</button>
                </div>
                <div class="stock-levels-container">
                    <div class="stock-chart-container">
                        <canvas id="stockLevelsChart"></canvas>
                    </div>
                    <div class="table-responsive">
                        <h3>Low Stock Items</h3>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Reorder Threshold</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockTable">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Logs Tab -->
        <div id="logs" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-history"></i> Inventory Activity Logs</h2>
                    <div class="card-actions">
                        <button class="export-btn" onclick="exportLogsPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                        <button class="export-btn" onclick="exportLogsExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Item ID</th>
                                <th>Details</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $result_logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                    <td><?php echo htmlspecialchars($log['fullName']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['item_id']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action_details']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Add Category Modal -->
        <div id="addCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addCategoryModal')">&times;</span>
                <h2>Add New Category</h2>
                <form id="addCategoryForm" action="imanager_invmanagement.php" method="POST">
                    <div class="form-group">
                        <label for="category_name">Category Name:</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_description">Description:</label>
                        <textarea id="category_description" name="category_description" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_category">Save Category</button>
                        <button type="button" onclick="closeModal('addCategoryModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div id="editCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editCategoryModal')">&times;</span>
                <h2>Edit Category</h2>
                <form id="editCategoryForm" action="imanager_invmanagement.php" method="POST">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="form-group">
                        <label for="edit_category_name">Category Name:</label>
                        <input type="text" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_category_description">Description:</label>
                        <textarea id="edit_category_description" name="category_description" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_category">Update Category</button>
                        <button type="button" onclick="closeModal('editCategoryModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Category Confirmation Modal -->
        <div id="deleteCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('deleteCategoryModal')">&times;</span>
                <h2>Confirm Deletion</h2>
                <p>Are you sure you want to delete the category: <span id="delete_category_name"></span>?</p>
                <p class="warning-text">This action cannot be undone.</p>
                <form id="deleteCategoryForm" action="imanager_invmanagement.php" method="POST">
                    <input type="hidden" id="delete_category_id" name="category_id">
                    <div class="form-actions">
                        <button type="submit" name="delete_category" class="delete-confirm-btn">Delete</button>
                        <button type="button" onclick="closeModal('deleteCategoryModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div id="addProductModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addProductModal')">&times;</span>
                <h2>Add New Product</h2>
                <form id="addProductForm" action="imanager_invmanagement.php" method="POST">
                    <div class="form-group">
                        <label for="product_name">Product Name:</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="product_description">Description:</label>
                        <textarea id="product_description" name="product_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php 
                            if ($result_categories) {
                                $result_categories->data_seek(0); // Reset result pointer
                                while ($category = $result_categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Initial Stock Quantity:</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="reorder_threshold">Reorder Threshold:</label>
                        <input type="number" id="reorder_threshold" name="reorder_threshold" min="0" value="10" required>
                    </div>
                    <div class="form-group">
                        <label for="unit_price">Unit Price (RM):</label>
                        <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" value="0.00" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="add_product">Save Product</button>
                        <button type="button" onclick="closeModal('addProductModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div id="editProductModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editProductModal')">&times;</span>
                <h2>Edit Product</h2>
                <form id="editProductForm" action="imanager_invmanagement.php" method="POST">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <div class="form-group">
                        <label for="edit_product_name">Product Name:</label>
                        <input type="text" id="edit_product_name" name="product_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_description">Description:</label>
                        <textarea id="edit_product_description" name="product_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_category_id">Category:</label>
                        <select id="edit_category_id" name="category_id" required>
                            <?php 
                            if ($result_categories) {
                                $result_categories->data_seek(0); // Reset result pointer
                                while ($category = $result_categories->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_stock_quantity">Stock Quantity:</label>
                        <input type="number" id="edit_stock_quantity" name="stock_quantity" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_reorder_threshold">Reorder Threshold:</label>
                        <input type="number" id="edit_reorder_threshold" name="reorder_threshold" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit_price">Unit Price (RM):</label>
                        <input type="number" id="edit_unit_price" name="unit_price" min="0" step="0.01" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_product">Update Product</button>
                        <button type="button" onclick="closeModal('editProductModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Product Confirmation Modal -->
        <div id="deleteProductModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('deleteProductModal')">&times;</span>
                <h2>Confirm Deletion</h2>
                <p>Are you sure you want to delete the product: <span id="delete_product_name"></span>?</p>
                <p class="warning-text">This action cannot be undone.</p>
                <form id="deleteProductForm" action="imanager_invmanagement.php" method="POST">
                    <input type="hidden" id="delete_product_id" name="product_id">
                    <div class="form-actions">
                        <button type="submit" name="delete_product" class="delete-confirm-btn">Delete</button>
                        <button type="button" onclick="closeModal('deleteProductModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Order Modal -->
        <div id="addOrderModal" class="modal">
            <div class="modal-content wider-modal">
                <span class="close" onclick="closeModal('addOrderModal')">&times;</span>
                <h2>Create New Order</h2>
                <form id="addOrderForm" action="imanager_invmanagement.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="order_type">Order Type:</label>
                            <select id="order_type" name="order_type" required>
                                <option value="Purchase">Purchase (From Supplier)</option>
                                <option value="Sales">Sales (To Customer)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="customer_name">Customer/Supplier Name:</label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="order_date">Order Date:</label>
                            <input type="date" id="order_date" name="order_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <h3>Order Items</h3>
                    <div class="order-items" id="orderItemsContainer">
                        <div class="order-item">
                            <div class="form-group">
                                <label for="product_id_0">Product:</label>
                                <select id="product_id_0" name="product_id[]" class="product-select" onchange="updatePrice(0)" required>
                                    <option value="">Select Product</option>
                                    <?php 
                                    if ($result_products) {
                                        $result_products->data_seek(0); // Reset result pointer
                                        while ($product = $result_products->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['unit_price']; ?>">
                                            <?php echo htmlspecialchars($product['product_name']); ?> - RM<?php echo number_format($product['unit_price'], 2); ?>
                                        </option>
                                    <?php endwhile; } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity_0">Quantity:</label>
                                <input type="number" id="quantity_0" name="quantity[]" min="1" value="1" onchange="updateSubtotal(0)" required>
                            </div>
                            <div class="form-group">
                                <label for="price_0">Unit Price (RM):</label>
                                <input type="number" id="price_0" name="price[]" min="0" step="0.01" value="0.00" onchange="updateSubtotal(0)" required>
                            </div>
                            <div class="form-group">
                                <label for="subtotal_0">Subtotal (RM):</label>
                                <input type="text" id="subtotal_0" class="subtotal" readonly value="0.00">
                            </div>
                            <button type="button" class="remove-item-btn" onclick="removeOrderItem(this)" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" onclick="addOrderItem()">+ Add Another Item</button>
                    </div>
                    
                    <div class="order-total">
                        <h3>Total: RM <span id="orderTotal">0.00</span></h3>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="add_order">Create Order</button>
                        <button type="button" onclick="closeModal('addOrderModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Order Status Modal -->
        <div id="updateOrderStatusModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('updateOrderStatusModal')">&times;</span>
                <h2>Update Order Status</h2>
                <form id="updateOrderStatusForm" action="imanager_invmanagement.php" method="POST">
                    <input type="hidden" id="update_order_id" name="order_id">
                    <div class="form-group">
                        <label for="order_status">Status:</label>
                        <select id="order_status" name="status" required>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_order_status">Update Status</button>
                        <button type="button" onclick="closeModal('updateOrderStatusModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Order Confirmation Modal -->
        <div id="deleteOrderModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('deleteOrderModal')">&times;</span>
                <h2>Confirm Deletion</h2>
                <p>Are you sure you want to delete the order: <span id="delete_order_id"></span>?</p>
                <p class="warning-text">This action cannot be undone and will revert all inventory changes made by this order.</p>
                <form id="deleteOrderForm" action="imanager_invmanagement.php" method="POST">
                    <input type="hidden" id="delete_order_id_input" name="order_id">
                    <div class="form-actions">
                        <button type="submit" name="delete_order" class="delete-confirm-btn">Delete</button>
                        <button type="button" onclick="closeModal('deleteOrderModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Thresholds Modal -->
        <div id="updateThresholdsModal" class="modal">
            <div class="modal-content wider-modal">
                <span class="close" onclick="closeModal('updateThresholdsModal')">&times;</span>
                <h2>Update Reorder Thresholds</h2>
                <form id="updateThresholdsForm" action="imanager_invmanagement.php" method="POST">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Current Stock</th>
                                    <th>Current Threshold</th>
                                    <th>New Threshold</th>
                                </tr>
                            </thead>
                            <tbody id="thresholdTableBody">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_thresholds">Update Thresholds</button>
                        <button type="button" onclick="closeModal('updateThresholdsModal')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Order Details Modal -->
        <div id="viewOrderModal" class="modal">
            <div class="modal-content wider-modal">
                <span class="close" onclick="closeModal('viewOrderModal')">&times;</span>
                <h2>Order Details</h2>
                <div id="orderDetailsContainer">
                    <div class="order-header">
                        <div class="order-info">
                            <p><strong>Order ID:</strong> <span id="view_order_id"></span></p>
                            <p><strong>Type:</strong> <span id="view_order_type"></span></p>
                            <p><strong>Customer/Supplier:</strong> <span id="view_customer_name"></span></p>
                        </div>
                        <div class="order-info">
                            <p><strong>Date:</strong> <span id="view_order_date"></span></p>
                            <p><strong>Status:</strong> <span id="view_order_status"></span></p>
                            <p><strong>Created By:</strong> <span id="view_created_by"></span></p>
                        </div>
                    </div>
                    <h3>Order Items</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsTable">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                    <td><strong>RM <span id="view_total_amount"></span></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="form-actions">
                        <button type="button" onclick="printOrder()"><i class="fas fa-print"></i> Print</button>
                        <button type="button" onclick="exportOrderPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                        <button type="button" onclick="closeModal('viewOrderModal')">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Load scripts at the end of body -->
    <script src="imanager_main.js"></script>
    <!-- Add inventory_functions.js AFTER imanager_main.js -->
    <script src="inventory_functions.js"></script>
</body>
</html>