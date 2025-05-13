const DEBUG = true;

// Debug function to list all modals
function listAllModals() {
    console.log("Searching for modals...");
    const modals = document.querySelectorAll('.modal');
    console.log(`Found ${modals.length} modals with class 'modal':`);
    
    modals.forEach((modal, index) => {
      console.log(`Modal ${index+1}: id="${modal.id}", class="${modal.className}"`);
    });
    
    // List all elements with IDs containing "modal" (case insensitive)
    const allElements = document.querySelectorAll('*[id*="modal" i]');
    console.log(`Found ${allElements.length} elements with IDs containing 'modal':`);
    
    allElements.forEach((element, index) => {
      console.log(`Element ${index+1}: id="${element.id}", tagName="${element.tagName}"`);
    });
  }

// Run this when the page loads
document.addEventListener('DOMContentLoaded', listAllModals);

function debugLog(...args) {
    if (DEBUG) console.log(...args);
}

// Run when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    debugLog("DOM loaded - checking for modals");
    
    // Check if modals exist
    const addProductModal = document.getElementById('addProductModal');
    const addProductForm = document.getElementById('addProductForm');
    
    debugLog("Add Product Modal exists:", !!addProductModal);
    debugLog("Add Product Form exists:", !!addProductForm);
    
    // Attach direct event listeners to buttons
    const addProductButton = document.querySelector('.add-btn');
    if (addProductButton) {
        debugLog("Found Add Product button, attaching direct event listener");
        addProductButton.addEventListener('click', function(e) {
            debugLog("Add Product button clicked");
            e.preventDefault();
            showAddProductModal();
        });
    }
    
    // Attach event listeners to tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        debugLog("Attaching event listener to tab:", button.textContent);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.textContent.toLowerCase().replace(/\s+/g, '-');
            debugLog("Tab clicked:", tabName);
            openTab(tabName);
        });
    });
});

// Define openTab as a global function
function openTab(tabName) {
    debugLog("Opening tab:", tabName);
    
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
    const tabElement = document.getElementById(tabName);
    if (tabElement) {
        debugLog("Tab found:", tabName);
        tabElement.classList.add('active');
    } else {
        console.error("Tab not found:", tabName);
    }
    
    // Find and activate the corresponding button
    for (let i = 0; i < tabButtons.length; i++) {
        if (tabButtons[i].textContent.toLowerCase().includes(tabName.replace('-', ' '))) {
            tabButtons[i].classList.add('active');
        }
    }
}

function showAddProductModal() {
    debugLog("Show Add Product Modal called");
    
    // Try to find the modal multiple ways
    let modal = document.getElementById('addProductModal');
    
    if (!modal) {
        debugLog("Modal not found by ID, trying querySelector");
        modal = document.querySelector('div.modal#addProductModal');
    }
    
    if (!modal) {
        console.error("Modal not found by any method");
        
        // Create a direct force-show button as a failsafe
        const forceShowButton = document.createElement('button');
        forceShowButton.textContent = "Force Show Modal";
        forceShowButton.style.position = "fixed";
        forceShowButton.style.top = "10px";
        forceShowButton.style.right = "10px";
        forceShowButton.style.zIndex = "9999";
        forceShowButton.style.padding = "10px";
        forceShowButton.style.background = "red";
        forceShowButton.style.color = "white";
        
        forceShowButton.onclick = function() {
            const allModals = document.querySelectorAll('.modal');
            debugLog("Found", allModals.length, "modals");
            
            if (allModals.length > 0) {
                allModals[0].style.display = 'block';
            }
        };
        
        document.body.appendChild(forceShowButton);
        
        return;
    }
    
    // Try to reset the form
    const form = document.getElementById('addProductForm');
    if (form) {
        debugLog("Form found, resetting");
        form.reset();
    }
    
    // Show the modal directly via CSS
    debugLog("Setting modal display to block");
    modal.style.display = 'block';
}

function closeModal(modalId) {
    debugLog("Closing modal:", modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    } else {
        console.error("Modal not found for closing:", modalId);
    }
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