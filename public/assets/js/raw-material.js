/**
 * Raw Material Inventory Management
 * Handles CRUD operations for ingredients
 */

document.addEventListener("DOMContentLoaded", () => {

    // ===== Configuration =====
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token) {
        console.error('CSRF token not found');
        return;
    }

    const swalTheme = {
        background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
        color: '#f5e6d3',
        confirmButtonColor: '#d4a373',
        cancelButtonColor: '#8d6e63'
    };

    // ===== Utility Functions =====

    /**
     * Refresh Lucide icons after DOM changes
     */
    function refreshIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /**
     * Show toast notification
     */
    function showToast(title, text, icon = 'success') {
        Swal.fire({
            ...swalTheme,
            icon,
            title,
            text,
            timer: 2000,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
    }

    /**
     * Update quantity display in card
     */
    function updateQuantityDisplay(card, quantity) {
        const qtyNumber = card.querySelector('.qty-number');
        if (qtyNumber) {
            qtyNumber.textContent = parseFloat(quantity).toFixed(2);
            qtyNumber.className = quantity < 5 ? 'qty-number qty-low' : 'qty-number qty-good';
        }

        // Update card border color
        if (quantity < 5) {
            card.classList.add('card-low-stock');
        } else {
            card.classList.remove('card-low-stock');
        }
    }

    /**
     * Update status badge in card
     */
    function updateStatusDisplay(card, quantity) {
        const statusBadge = card.querySelector('.status-badge-small');
        if (statusBadge) {
            const isLowStock = quantity < 5;
            statusBadge.className = isLowStock ? 'status-badge-small status-low' : 'status-badge-small status-good';
            statusBadge.innerHTML = isLowStock
                ? '<i class="lucide-alert-circle"></i> Low Stock'
                : '<i class="lucide-check-circle"></i> In Stock';
            refreshIcons();
        }
    }

    /**
     * Update stats cards
     */
    function updateStatsCards() {
        const cards = document.querySelectorAll('.ingredient-card-wrapper');
        const totalCount = cards.length;
        const lowStockCount = Array.from(cards).filter(wrapper => {
            const card = wrapper.querySelector('.ingredient-card');
            return card && card.classList.contains('card-low-stock');
        }).length;
        const inStockCount = totalCount - lowStockCount;

        // Update stat values
        const statCards = document.querySelectorAll('.stat-card');
        if (statCards[0]) statCards[0].querySelector('.stat-value').textContent = totalCount;
        if (statCards[1]) statCards[1].querySelector('.stat-value').textContent = lowStockCount;
        if (statCards[2]) statCards[2].querySelector('.stat-value').textContent = inStockCount;
    }

    // ===== API Functions =====

    /**
     * Add stock to material
     */
    async function addStock(id, quantity) {
        const response = await fetch(`/admin/raw-material/add/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ quantity })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to add stock');
        }

        return response.json();
    }

    /**
     * Reduce stock from material
     */
    async function reduceStock(id, quantity) {
        const response = await fetch(`/admin/raw-material/reduce/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ quantity })
        });

        if (!response.ok) {
            throw new Error('Not enough stock!');
        }

        return response.json();
    }

    /**
     * Update material details
     */
    async function updateMaterial(id, name, unit) {
        const response = await fetch(`/admin/raw-material/update/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ name, unit })
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to update material');
        }

        return response.json();
    }

    /**
     * Delete material
     */
    async function deleteMaterial(id) {
        const response = await fetch(`/admin/raw-material/delete/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token }
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Failed to delete material');
        }

        return response.json();
    }

    /**
     * Create new material
     */
    async function createMaterial(storeUrl, data) {
        const response = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const error = await response.json();
            const errorMsg = Object.values(error.errors || {}).flat().join(', ') || error.message;
            throw new Error(errorMsg);
        }

        return response.json();
    }

    // ===== Event Handlers =====

    /**
     * Handle add stock button click
     */
    function handleAddStock(button) {
        const { id, name, unit } = button.dataset;
        const card = button.closest('.ingredient-card');

        Swal.fire({
            title: `Add Stock: ${name}`,
            input: 'number',
            inputLabel: `Enter amount (${unit})`,
            inputAttributes: {
                min: '0.01',
                step: '0.01',
                placeholder: 'Enter quantity'
            },
            showCancelButton: true,
            confirmButtonText: 'Add Stock',
            ...swalTheme,
            preConfirm: qty => {
                const quantity = parseFloat(qty);
                if (!qty || quantity <= 0) {
                    Swal.showValidationMessage('Please enter a valid quantity');
                    return false;
                }
                return quantity;
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            try {
                const data = await addStock(id, result.value);
                updateQuantityDisplay(card, data.quantity);
                updateStatusDisplay(card, data.quantity);
                updateStatsCards();
                showToast('Success!', 'Stock added successfully', 'success');
            } catch (error) {
                showToast('Error', error.message, 'error');
            }
        });
    }

    /**
     * Handle reduce stock button click
     */
    function handleReduceStock(button) {
        const { id, name, unit } = button.dataset;
        const card = button.closest('.ingredient-card');

        Swal.fire({
            title: `Reduce Stock: ${name}`,
            input: 'number',
            inputLabel: `Enter amount to reduce (${unit})`,
            inputAttributes: {
                min: '0.01',
                step: '0.01',
                placeholder: 'Enter quantity'
            },
            showCancelButton: true,
            confirmButtonText: 'Reduce Stock',
            ...swalTheme,
            preConfirm: qty => {
                const quantity = parseFloat(qty);
                if (!qty || quantity <= 0) {
                    Swal.showValidationMessage('Please enter a valid quantity');
                    return false;
                }
                return quantity;
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            try {
                const data = await reduceStock(id, result.value);
                updateQuantityDisplay(card, data.quantity);
                updateStatusDisplay(card, data.quantity);
                updateStatsCards();
                showToast('Success!', 'Stock reduced successfully', 'success');
            } catch (error) {
                showToast('Error', error.message, 'error');
            }
        });
    }

    /**
     * Handle update material button click
     */
    function handleUpdateMaterial(button) {
        const { id, name, unit } = button.dataset;
        const wrapper = button.closest('.ingredient-card-wrapper');
        const card = button.closest('.ingredient-card');
        const existingImage = card.querySelector('.ingredient-image');
        const currentImageSrc = existingImage ? existingImage.src : null;

        Swal.fire({
            title: 'Update Material',
            html: `
                <div class="swal-form-group">
                    <label class="swal-label">Material Name</label>
                    <input type="text" id="update_name" class="swal2-input" placeholder="Name" value="${name}">
                </div>
                <div class="swal-form-group">
                    <label class="swal-label">Unit of Measurement</label>
                    <select id="update_unit" class="swal2-input">
                        <option value="g" ${unit === 'g' ? 'selected' : ''}>Gram (g)</option>
                        <option value="kg" ${unit === 'kg' ? 'selected' : ''}>Kilogram (kg)</option>
                        <option value="ml" ${unit === 'ml' ? 'selected' : ''}>Milliliter (ml)</option>
                        <option value="l" ${unit === 'l' ? 'selected' : ''}>Liter (l)</option>
                        <option value="pcs" ${unit === 'pcs' ? 'selected' : ''}>Pieces (pcs)</option>
                        <option value="box" ${unit === 'box' ? 'selected' : ''}>Box</option>
                    </select>
                </div>
                <div class="swal-form-group">
                    <label class="swal-label">Material Image (Optional)</label>
                    <div class="image-preview-container">
                        ${currentImageSrc ? `<img id="preview_image" src="${currentImageSrc}" class="image-preview">` : '<div id="preview_placeholder" class="image-preview-placeholder"><i class="lucide-image"></i><span>No image</span></div>'}
                    </div>
                    <input type="file" id="update_image" class="swal2-file" accept="image/*">
                    ${currentImageSrc ? '<button type="button" id="remove_image_btn" class="btn-remove-image">Remove Image</button>' : ''}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            ...swalTheme,
            didOpen: () => {
                // Image preview
                const fileInput = document.getElementById('update_image');
                const preview = document.getElementById('preview_image');
                const placeholder = document.getElementById('preview_placeholder');
                const removeBtn = document.getElementById('remove_image_btn');

                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            if (preview) {
                                preview.src = event.target.result;
                            } else if (placeholder) {
                                placeholder.outerHTML = `<img id="preview_image" src="${event.target.result}" class="image-preview">`;
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });

                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        if (preview) {
                            preview.outerHTML = '<div id="preview_placeholder" class="image-preview-placeholder"><i class="lucide-image"></i><span>Image will be removed</span></div>';
                        }
                        fileInput.value = '';
                        fileInput.dataset.removeImage = 'true';
                        refreshIcons();
                    });
                }

                refreshIcons();
            },
            preConfirm: () => {
                const newName = document.getElementById('update_name').value.trim();
                const newUnit = document.getElementById('update_unit').value;
                const imageFile = document.getElementById('update_image').files[0];
                const removeImage = document.getElementById('update_image').dataset.removeImage === 'true';

                if (!newName) {
                    Swal.showValidationMessage('Please enter a material name');
                    return false;
                }

                return { newName, newUnit, imageFile, removeImage };
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            const { newName, newUnit, imageFile, removeImage } = result.value;

            try {
                // Create FormData for file upload
                const formData = new FormData();
                formData.append('name', newName);
                formData.append('unit', newUnit);
                if (imageFile) {
                    formData.append('image', imageFile);
                }
                if (removeImage) {
                    formData.append('remove_image', 'true');
                }

                const response = await fetch(`/admin/raw-material/update/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-HTTP-Method-Override': 'PATCH'
                    },
                    body: formData
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to update material');
                }

                const data = await response.json();

                // Update name display
                const nameElement = card.querySelector('.ingredient-card-name');
                if (nameElement) nameElement.textContent = data.name;

                // Update unit text
                const unitElement = card.querySelector('.unit-text');
                if (unitElement) unitElement.textContent = data.unit;

                // Update image
                const iconDiv = card.querySelector('.ingredient-card-icon');
                if (data.image) {
                    iconDiv.innerHTML = `<img src="${data.image}" alt="${data.name}" class="ingredient-image">`;
                } else {
                    iconDiv.innerHTML = '<i class="lucide-package-2"></i>';
                }
                refreshIcons();

                // Update data attributes on all buttons
                card.querySelectorAll('button').forEach(btn => {
                    btn.dataset.name = data.name;
                    btn.dataset.unit = data.unit;
                });

                // Update search data attribute
                wrapper.dataset.name = data.name.toLowerCase();

                showToast('Success!', 'Material updated successfully', 'success');
            } catch (error) {
                showToast('Error', error.message, 'error');
            }
        });
    }

    /**
     * Handle delete material button click
     */
    function handleDeleteMaterial(button) {
        const { id, name } = button.dataset;
        const wrapper = button.closest('.ingredient-card-wrapper');
        const card = button.closest('.ingredient-card');
        const qtyNumber = card.querySelector('.qty-number');
        const quantity = qtyNumber ? parseFloat(qtyNumber.textContent) : 0;

        // Check if material has stock
        if (quantity > 0) {
            Swal.fire({
                ...swalTheme,
                icon: 'error',
                title: 'Cannot Delete',
                text: 'Cannot delete material with remaining stock. Please reduce stock to zero first.'
            });
            return;
        }

        Swal.fire({
            title: `Delete "${name}"?`,
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            ...swalTheme
        }).then(async result => {
            if (!result.isConfirmed) return;

            try {
                await deleteMaterial(id);
                wrapper.remove();
                updateStatsCards();

                // Check if grid is now empty
                const remainingCards = document.querySelectorAll('.ingredient-card-wrapper');
                if (remainingCards.length === 0) {
                    const grid = document.getElementById('ingredientsGrid');
                    grid.innerHTML = `
                        <div class="col-12">
                            <div class="empty-state-card">
                                <i class="lucide-package-x empty-icon-large"></i>
                                <h3 class="empty-title-large">No Ingredients Found</h3>
                                <p class="empty-text-large">Start by adding your first ingredient to track inventory</p>
                                <button class="btn btn-add-material btnAddFromEmpty">
                                    <i class="lucide-plus-circle"></i>
                                    Add First Ingredient
                                </button>
                            </div>
                        </div>
                    `;
                    refreshIcons();

                    // Re-attach event listener for empty button
                    document.querySelector('.btnAddFromEmpty')?.addEventListener('click', handleAddNewMaterial);
                }

                showToast('Deleted!', 'Material removed successfully', 'success');
            } catch (error) {
                showToast('Error', error.message, 'error');
            }
        });
    }

    /**
     * Handle add new material button click
     */
    function handleAddNewMaterial() {
        const btnAddMaterial = document.getElementById('btnAddMaterial');
        const storeUrl = btnAddMaterial?.dataset.url;

        if (!storeUrl) {
            console.error('Store URL not found');
            return;
        }

        Swal.fire({
            title: 'Add New Ingredient',
            html: `
                <div class="swal-form-group">
                    <label class="swal-label">Material ID</label>
                    <input type="number" id="rm_id" class="swal2-input" placeholder="Enter unique ID">
                </div>
                <div class="swal-form-group">
                    <label class="swal-label">Material Name</label>
                    <input type="text" id="rm_name" class="swal2-input" placeholder="Enter material name">
                </div>
                <div class="swal-form-group">
                    <label class="swal-label">Unit of Measurement</label>
                    <select id="rm_unit" class="swal2-input">
                        <option value="">Select unit...</option>
                        <option value="g">Gram (g)</option>
                        <option value="kg">Kilogram (kg)</option>
                        <option value="ml">Milliliter (ml)</option>
                        <option value="l">Liter (l)</option>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="box">Box</option>
                    </select>
                </div>
                <div class="swal-form-group">
                    <label class="swal-label">Material Image (Optional)</label>
                    <div class="image-preview-container">
                        <div id="new_preview_placeholder" class="image-preview-placeholder">
                            <i class="lucide-image"></i>
                            <span>No image selected</span>
                        </div>
                    </div>
                    <input type="file" id="rm_image" class="swal2-file" accept="image/*">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add Material',
            ...swalTheme,
            didOpen: () => {
                // Image preview for new material
                const fileInput = document.getElementById('rm_image');
                const placeholder = document.getElementById('new_preview_placeholder');

                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            placeholder.outerHTML = `<img id="new_preview_image" src="${event.target.result}" class="image-preview">`;
                        };
                        reader.readAsDataURL(file);
                    }
                });

                refreshIcons();
            },
            preConfirm: () => {
                const id = parseInt(document.getElementById('rm_id').value);
                const name = document.getElementById('rm_name').value.trim();
                const unit = document.getElementById('rm_unit').value;
                const imageFile = document.getElementById('rm_image').files[0];

                if (!id) {
                    Swal.showValidationMessage('Please enter a valid ID');
                    return false;
                }
                if (!name) {
                    Swal.showValidationMessage('Please enter a material name');
                    return false;
                }
                if (!unit) {
                    Swal.showValidationMessage('Please select a unit');
                    return false;
                }

                return { id, name, unit, imageFile };
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            const { id, name, unit, imageFile } = result.value;

            try {
                // Create FormData for file upload
                const formData = new FormData();
                formData.append('id', id);
                formData.append('name', name);
                formData.append('unit', unit);
                formData.append('quantity', 0);
                if (imageFile) {
                    formData.append('image', imageFile);
                }

                const response = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    body: formData
                });

                if (!response.ok) {
                    const error = await response.json();
                    const errorMsg = Object.values(error.errors || {}).flat().join(', ') || error.message;
                    throw new Error(errorMsg);
                }

                const data = await response.json();

                // Remove empty state if it exists
                const emptyState = document.querySelector('.empty-state-card');
                if (emptyState) {
                    emptyState.closest('.col-12').remove();
                }

                // Create new card
                const grid = document.getElementById('ingredientsGrid');
                const newWrapper = document.createElement('div');
                newWrapper.className = 'col-lg-3 col-md-4 col-sm-6 ingredient-card-wrapper';
                newWrapper.dataset.name = data.name.toLowerCase();

                const imageHtml = data.image
                    ? `<img src="${data.image}" alt="${data.name}" class="ingredient-image">`
                    : '<i class="lucide-package-2"></i>';

                newWrapper.innerHTML = `
                    <div class="ingredient-card card-low-stock">
                        <div class="ingredient-card-header">
                            <div class="ingredient-card-icon">
                                ${imageHtml}
                            </div>
                            <span class="ingredient-id">#${data.id}</span>
                        </div>
                        <div class="ingredient-card-body">
                            <h5 class="ingredient-card-name" id="displayName${data.id}">
                                ${data.name}
                            </h5>
                            <div class="ingredient-stats">
                                <div class="stat-item">
                                    <div class="stat-item-label">
                                        <i class="lucide-package"></i>
                                        Quantity
                                    </div>
                                    <div class="stat-item-value" id="displayQty${data.id}">
                                        <span class="qty-number qty-low">${data.quantity.toFixed(2)}</span>
                                        <span class="unit-text" id="displayUnit${data.id}">${data.unit}</span>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-item-label">
                                        <i class="lucide-activity"></i>
                                        Status
                                    </div>
                                    <div class="stat-item-value">
                                        <span class="status-badge-small status-low">
                                            <i class="lucide-alert-circle"></i>
                                            Low Stock
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ingredient-card-actions">
                            <button class="btn-card-action btn-card-add btnAddStock"
                                    data-id="${data.id}"
                                    data-name="${data.name}"
                                    data-unit="${data.unit}"
                                    title="Add Stock">
                                <i class="lucide-plus"></i>
                                <span class="btn-card-text">Add</span>
                            </button>
                            <button class="btn-card-action btn-card-reduce btnReduceStock"
                                    data-id="${data.id}"
                                    data-name="${data.name}"
                                    data-unit="${data.unit}"
                                    title="Reduce Stock">
                                <i class="lucide-minus"></i>
                                <span class="btn-card-text">Reduce</span>
                            </button>
                            <button class="btn-card-action btn-card-edit btnUpdateMaterial"
                                    data-id="${data.id}"
                                    data-name="${data.name}"
                                    data-unit="${data.unit}"
                                    title="Edit Material">
                                <i class="lucide-edit"></i>
                                <span class="btn-card-text">Edit</span>
                            </button>
                            <button class="btn-card-action btn-card-delete btnDeleteMaterial"
                                    data-id="${data.id}"
                                    data-name="${data.name}"
                                    title="Delete Material">
                                <i class="lucide-trash-2"></i>
                                <span class="btn-card-text">Delete</span>
                            </button>
                        </div>
                    </div>
                `;

                grid.appendChild(newWrapper);
                attachCardListeners(newWrapper);
                refreshIcons();
                updateStatsCards();

                showToast('Success!', 'Material added successfully', 'success');
            } catch (error) {
                showToast('Error', error.message, 'error');
            }
        });
    }

    /**
     * Attach event listeners to a card
     */
    function attachCardListeners(wrapper) {
        const card = wrapper.querySelector('.ingredient-card');
        if (!card) return;

        const btnAdd = card.querySelector('.btnAddStock');
        const btnReduce = card.querySelector('.btnReduceStock');
        const btnUpdate = card.querySelector('.btnUpdateMaterial');
        const btnDelete = card.querySelector('.btnDeleteMaterial');

        if (btnAdd) btnAdd.addEventListener('click', () => handleAddStock(btnAdd));
        if (btnReduce) btnReduce.addEventListener('click', () => handleReduceStock(btnReduce));
        if (btnUpdate) btnUpdate.addEventListener('click', () => handleUpdateMaterial(btnUpdate));
        if (btnDelete) btnDelete.addEventListener('click', () => handleDeleteMaterial(btnDelete));
    }

    // ===== Initialize =====

    // Initialize search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const wrappers = document.querySelectorAll('.ingredient-card-wrapper');

            wrappers.forEach(wrapper => {
                const name = wrapper.dataset.name || '';
                if (name.includes(searchTerm)) {
                    wrapper.style.display = '';
                } else {
                    wrapper.style.display = 'none';
                }
            });

            // Show/hide empty state if no results
            const visibleWrappers = Array.from(wrappers).filter(w => w.style.display !== 'none');
            const emptyState = document.querySelector('.empty-state-card');

            if (visibleWrappers.length === 0 && !emptyState) {
                const grid = document.getElementById('ingredientsGrid');
                const noResultsCol = document.createElement('div');
                noResultsCol.className = 'col-12 no-results-wrapper';
                noResultsCol.innerHTML = `
                    <div class="empty-state-card">
                        <i class="lucide-search-x empty-icon-large"></i>
                        <h3 class="empty-title-large">No Results Found</h3>
                        <p class="empty-text-large">No ingredients match "${searchTerm}"</p>
                    </div>
                `;
                grid.appendChild(noResultsCol);
                refreshIcons();
            } else {
                const noResults = document.querySelector('.no-results-wrapper');
                if (noResults) noResults.remove();
            }
        });
    }

    // Attach listeners to existing cards
    document.querySelectorAll('.ingredient-card-wrapper').forEach(wrapper => attachCardListeners(wrapper));

    // Attach listener to add material button
    const btnAddMaterial = document.getElementById('btnAddMaterial');
    if (btnAddMaterial) {
        btnAddMaterial.addEventListener('click', handleAddNewMaterial);
    }

    // Attach listener to "add from empty" button if it exists
    document.querySelectorAll('.btnAddFromEmpty').forEach(btn => {
        btn.addEventListener('click', handleAddNewMaterial);
    });

    // Initialize icons
    refreshIcons();
});
