// JavaScript for Inventory Management

// Load data and initialize components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs
    const activeTab = localStorage.getItem('activeInventoryTab') || 'products';
    openTab(activeTab);
    
    // Load stock level data
    loadStockLevelsData();
    
    // Initialize order date to today
    document.getElementById('order_date').valueAsDate = new Date();
});

// Tab Navigation
function openTab(tabName) {
    // Hide all tab content
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }
    
    // Remove active class from all tab buttons
    const tabButtons = document.getElementsByClassName('tab-button');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove('active');
    }
    
    // Show the selected tab content and mark the button as active
    document.getElementById(tabName).classList.add('active');
    
    // Find and activate the corresponding button
    const buttons = document.getElementsByClassName('tab-button');
    for (let i = 0; i < buttons.length; i++) {
        if (buttons[i].textContent.toLowerCase().includes(tabName.replace('-', ' '))) {
            buttons[i].classList.add('active');
        }
    }
    
    // Save active tab to localStorage
    localStorage.setItem('activeInventoryTab', tabName);
    
    // Load specific tab data if needed
    if (tabName === 'stock-levels') {
        initializeStockChart();
    }
}

// Modal functions
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Category Functions
function showAddCategoryModal() {
    document.getElementById('addCategoryForm').reset();
    document.getElementById('addCategoryModal').style.display = 'block';
}

function showEditCategoryModal(id, name, description) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_category_description').value = description;
    document.getElementById('editCategoryModal').style.display = 'block';
}

function confirmDeleteCategory(id, name) {
    document.getElementById('delete_category_id').value = id;
    document.getElementById('delete_category_name').textContent = name;
    document.getElementById('deleteCategoryModal').style.display = 'block';
}

// Product Functions
function showAddProductModal() {
    document.getElementById('addProductForm').reset();
    document.getElementById('addProductModal').style.display = 'block';
}

function showEditProductModal(id, name, description, categoryId, stockQty, threshold, price) {
    document.getElementById('edit_product_id').value = id;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_product_description').value = description;
    document.getElementById('edit_category_id').value = categoryId;
    document.getElementById('edit_stock_quantity').value = stockQty;
    document.getElementById('edit_reorder_threshold').value = threshold;
    document.getElementById('edit_unit_price').value = parseFloat(price).toFixed(2);
    document.getElementById('editProductModal').style.display = 'block';
}

function confirmDeleteProduct(id, name) {
    document.getElementById('delete_product_id').value = id;
    document.getElementById('delete_product_name').textContent = name;
    document.getElementById('deleteProductModal').style.display = 'block';
}
// Order Management Functions
let orderItemCount = 1;

function showAddOrderModal() {
    document.getElementById('addOrderForm').reset();
    document.getElementById('orderItemsContainer').innerHTML = '';
    addOrderItem(); // Start with one item
    document.getElementById('addOrderModal').style.display = 'block';
}

function addOrderItem() {
    const container = document.getElementById('orderItemsContainer');
    const index = orderItemCount;
    orderItemCount++;
    
    const itemRow = document.createElement('div');
    itemRow.className = 'order-item';
    itemRow.innerHTML = `
        <div class="form-group">
            <label for="product_id_${index}">Product:</label>
            <select id="product_id_${index}" name="product_id[]" class="product-select" onchange="updatePrice(${index})" required>
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
            <label for="quantity_${index}">Quantity:</label>
            <input type="number" id="quantity_${index}" name="quantity[]" min="1" value="1" onchange="updateSubtotal(${index})" required>
        </div>
        <div class="form-group">
            <label for="price_${index}">Unit Price (RM):</label>
            <input type="number" id="price_${index}" name="price[]" min="0" step="0.01" value="0.00" onchange="updateSubtotal(${index})" required>
        </div>
        <div class="form-group">
            <label for="subtotal_${index}">Subtotal (RM):</label>
            <input type="text" id="subtotal_${index}" class="subtotal" readonly value="0.00">
        </div>
        <button type="button" class="remove-item-btn" onclick="removeOrderItem(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.getElementById('orderItemsContainer').appendChild(itemRow);
    
    // Show all remove buttons if there's more than one item
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    if (removeButtons.length > 1) {
        removeButtons.forEach(btn => {
            btn.style.display = 'block';
        });
    }
}

function removeOrderItem(button) {
    const container = document.getElementById('orderItemsContainer');
    const items = container.getElementsByClassName('order-item');
    
    if (items.length > 1) {
        button.parentElement.remove();
        updateOrderTotal();
        
        // Hide the remove button if only one item is left
        if (items.length <= 2) { // Will be 2 before the removal takes effect
            document.querySelector('.remove-item-btn').style.display = 'none';
        }
    }
}

function updatePrice(index) {
    const selectElement = document.getElementById(`product_id_${index}`);
    const priceInput = document.getElementById(`price_${index}`);
    
    if (selectElement.selectedIndex > 0) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        priceInput.value = parseFloat(price).toFixed(2);
        updateSubtotal(index);
    }
}

function updateSubtotal(index) {
    const quantity = parseFloat(document.getElementById(`quantity_${index}`).value) || 0;
    const price = parseFloat(document.getElementById(`price_${index}`).value) || 0;
    const subtotal = quantity * price;
    
    document.getElementById(`subtotal_${index}`).value = subtotal.toFixed(2);
    updateOrderTotal();
}

function updateOrderTotal() {
    const subtotalInputs = document.getElementsByClassName('subtotal');
    let total = 0;
    
    for (let i = 0; i < subtotalInputs.length; i++) {
        total += parseFloat(subtotalInputs[i].value) || 0;
    }
    
    document.getElementById('orderTotal').textContent = total.toFixed(2);
}

function showUpdateOrderStatusModal(orderId, status) {
    document.getElementById('update_order_id').value = orderId;
    document.getElementById('order_status').value = status;
    document.getElementById('updateOrderStatusModal').style.display = 'block';
}

function confirmDeleteOrder(orderId) {
    document.getElementById('delete_order_id_input').value = orderId;
    document.getElementById('delete_order_id').textContent = orderId;
    document.getElementById('deleteOrderModal').style.display = 'block';
}

function viewOrderDetails(orderId) {
    // Fetch order details via AJAX
    fetch('imanager_getorderdetails.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            // Populate order header
            document.getElementById('view_order_id').textContent = data.order.order_id;
            document.getElementById('view_order_type').textContent = data.order.order_type;
            document.getElementById('view_customer_name').textContent = data.order.customer_name;
            document.getElementById('view_order_date').textContent = data.order.order_date;
            document.getElementById('view_order_status').textContent = data.order.status;
            document.getElementById('view_created_by').textContent = data.order.created_by;
            document.getElementById('view_total_amount').textContent = parseFloat(data.order.total_amount).toFixed(2);
            
            // Populate order items
            const itemsTable = document.getElementById('orderItemsTable');
            itemsTable.innerHTML = '';
            
            data.items.forEach(item => {
                const row = document.createElement('tr');
                const subtotal = parseFloat(item.quantity) * parseFloat(item.unit_price);
                row.innerHTML = `
                    <td>${item.product_id}</td>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>RM ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>RM ${subtotal.toFixed(2)}</td>
                `;
                itemsTable.appendChild(row);
            });
            
            document.getElementById('viewOrderModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            alert('Failed to load order details.');
        });
}

// Stock Level Management
function showUpdateThresholdsModal() {
    // Fetch current product data
    fetch('imanager_getproducts.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('thresholdTableBody');
            tableBody.innerHTML = '';
            
            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.product_id}</td>
                    <td>${product.product_name}</td>
                    <td>${product.stock_quantity}</td>
                    <td>${product.reorder_threshold}</td>
                    <td>
                        <input type="hidden" name="threshold_product_id[]" value="${product.product_id}">
                        <input type="number" name="threshold_value[]" value="${product.reorder_threshold}" min="0" class="form-control">
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            document.getElementById('updateThresholdsModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            alert('Failed to load product data.');
        });
}

// Chart for Stock Levels
let stockChart;

function loadStockLevelsData() {
    // In a real application, this would be an AJAX call to fetch data
    // For now, we'll use the PHP data already on the page
    const products = [];
    const lowStockProducts = [];
    
    // This would normally be done with AJAX, but for demonstration:
    <?php 
    if ($result_products) {
        $result_products->data_seek(0); // Reset result pointer
        echo "const productData = [";
        while ($product = $result_products->fetch_assoc()) {
            echo "{
                id: '" . addslashes($product['product_id']) . "',
                name: '" . addslashes($product['product_name']) . "',
                category: '" . addslashes($product['category_name']) . "',
                stock: " . $product['stock_quantity'] . ",
                threshold: " . $product['reorder_threshold'] . "
            },";
        }
        echo "];";
    } else {
        echo "const productData = [];";
    }
    ?>
    
    // Populate low stock table
    const lowStockTable = document.getElementById('lowStockTable');
    if (lowStockTable) {
        lowStockTable.innerHTML = '';
        
        productData.forEach(product => {
            if (product.stock <= product.threshold) {
                const row = document.createElement('tr');
                row.className = product.stock === 0 ? 'out-of-stock' : 'low-stock';
                
                let status = '';
                if (product.stock === 0) {
                    status = '<span class="status-badge status-outofstock">Out of Stock</span>';
                } else {
                    status = '<span class="status-badge status-low">Low Stock</span>';
                }
                
                row.innerHTML = `
                    <td>${product.id}</td>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>${product.stock}</td>
                    <td>${product.threshold}</td>
                    <td>${status}</td>
                `;
                lowStockTable.appendChild(row);
            }
        });
        
        if (lowStockTable.innerHTML === '') {
            lowStockTable.innerHTML = '<tr><td colspan="6" class="text-center">No low stock items found</td></tr>';
        }
    }
    
    // Initialize chart if we're on the stock-levels tab
    if (document.getElementById('stock-levels').classList.contains('active')) {
        initializeStockChart();
    }
}

function initializeStockChart() {
    const ctx = document.getElementById('stockLevelsChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (stockChart) {
        stockChart.destroy();
    }
    
    // Get data from the page (in a real app, this would be from an AJAX call)
    const labels = [];
    const stockData = [];
    const thresholdData = [];
    const backgroundColors = [];
    
    productData.forEach(product => {
        labels.push(product.name);
        stockData.push(product.stock);
        thresholdData.push(product.threshold);
        
        // Red if out of stock, orange if low, green if ok
        if (product.stock === 0) {
            backgroundColors.push('rgba(255, 99, 132, 0.7)');
        } else if (product.stock <= product.threshold) {
            backgroundColors.push('rgba(255, 159, 64, 0.7)');
        } else {
            backgroundColors.push('rgba(75, 192, 192, 0.7)');
        }
    });
    
    stockChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Current Stock',
                    data: stockData,
                    backgroundColor: backgroundColors,
                    borderColor: backgroundColors.map(color => color.replace('0.7', '1')),
                    borderWidth: 1
                },
                {
                    label: 'Reorder Threshold',
                    data: thresholdData,
                    type: 'line',
                    fill: false,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    pointRadius: 0
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity'
                    }
                },
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 45
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Inventory Stock Levels & Reorder Thresholds'
                }
            }
        }
    });
}

// Export Functions
function exportProductsPDF() {
    // In a real application, this would call a server-side endpoint to generate a PDF
    window.open('export_products.php?format=pdf', '_blank');
}

function exportProductsExcel() {
    // In a real application, this would call a server-side endpoint to generate an Excel file
    window.open('export_products.php?format=excel', '_blank');
}

function exportLogsPDF() {
    window.open('imanager_exportlogs.php?format=pdf', '_blank');
}

function exportLogsExcel() {
    window.open('imanager_exportlogs.php?format=excel', '_blank');
}

function printOrder() {
    const printContents = document.getElementById('orderDetailsContainer').innerHTML;
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div style="padding: 20px;">
            <h1 style="text-align: center;">Order Details</h1>
            ${printContents}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContents;
    
    // Reattach event listeners
    document.querySelectorAll('.close').forEach(el => {
        el.addEventListener('click', function() {
            closeModal(this.closest('.modal').id);
        });
    });
}

function exportOrderPDF() {
    const orderId = document.getElementById('view_order_id').textContent;
    window.open('imanager_exportorder.php?id=' + orderId + '&format=pdf', '_blank');
}

// Utility Functions
function formatCurrency(amount) {
    return 'RM ' + parseFloat(amount).toFixed(2);
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.getElementsByClassName('modal');
        for (let i = 0; i < modals.length; i++) {
            if (modals[i].style.display === 'block') {
                modals[i].style.display = 'none';
            }
        }
    }
});

// Initialize order items
if (document.getElementById('orderItemsContainer')) {
    addOrderItem();
}