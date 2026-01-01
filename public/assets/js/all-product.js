document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.getElementById('product-table-body');
    const btnAddProduct = document.getElementById('btnAddProduct');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!tableBody || !btnAddProduct || !csrfToken) {
        console.error('Essential DOM elements or CSRF token missing:', {
            tableBody: !!tableBody,
            btnAddProduct: !!btnAddProduct,
            csrfToken: !!csrfToken
        });
        return;
    }

    // --- HELPER: Sync folder card with table row ---
    function syncFolderCardWithTable(productId) {
        const tableRow = document.querySelector(`#product-table-body tr[data-product-id="${productId}"]`);
        const folderCard = document.querySelector(`.products-folder-grid .product-folder-card[data-product-id="${productId}"]`);

        if (!tableRow || !folderCard) return;

        const name = tableRow.children[1].textContent;
        const price = tableRow.children[3].textContent;
        const type = tableRow.children[4].textContent;
        const subtype = tableRow.children[5].textContent;
        const image = tableRow.children[2].querySelector('img').src;

        folderCard.querySelector('.product-name-text').textContent = name;
        folderCard.querySelector('.product-price-text').textContent = price;
        folderCard.querySelector('.product-type-text').textContent = type;
        folderCard.querySelector('.product-subtype-text').textContent = subtype;
        folderCard.querySelector('.product-folder-img').src = image;
        folderCard.setAttribute('data-product-name', name.toLowerCase());

        const editBtn = folderCard.querySelector('.btn-edit');
        const deleteBtn = folderCard.querySelector('.btn-delete');
        const tableEditBtn = tableRow.querySelector('.btn-edit');
        const tableDeleteBtn = tableRow.querySelector('.btn-delete');

        if (editBtn && tableEditBtn) {
            editBtn.dataset.name = tableEditBtn.dataset.name;
            editBtn.dataset.price = tableEditBtn.dataset.price;
            editBtn.dataset.typeId = tableEditBtn.dataset.typeId;
            editBtn.dataset.subtypeId = tableEditBtn.dataset.subtypeId;
        }
        if (deleteBtn && tableDeleteBtn) {
            deleteBtn.dataset.name = tableDeleteBtn.dataset.name;
            deleteBtn.dataset.price = tableDeleteBtn.dataset.price;
        }
    }

    // --- HELPER: Add product to folder grid ---
    function addProductToFolderGrid(product) {
        const grid = document.getElementById('products-folder-grid');
        if (!grid) return;

        const card = document.createElement('div');
        card.className = 'product-folder-card';
        card.setAttribute('data-product-id', product.id);
        card.setAttribute('data-product-name', product.name.toLowerCase());
        card.innerHTML = `
            <div class="product-folder-image">
                <img src="/assets/images/${product.image}" alt="${product.name}" class="product-folder-img">
                <div class="product-folder-overlay">
                    <button type="button" class="product-action-btn btn-view-detail" data-product-id="${product.id}">
                        <i class="lucide-eye"></i>
                    </button>
                </div>
            </div>
            <div class="product-folder-content">
                <div class="product-folder-header">
                    <h5 class="product-folder-name">
                        <i class="lucide-coffee"></i>
                        <span class="product-name-text">${product.name}</span>
                    </h5>
                    <span class="product-folder-price product-price-text">$${Number(product.price).toFixed(2)}</span>
                </div>
                <div class="product-folder-meta">
                    <div class="meta-item">
                        <i class="lucide-layers"></i>
                        <span class="product-type-text">${product.product_type_name}</span>
                    </div>
                    <div class="meta-item">
                        <i class="lucide-folder"></i>
                        <span class="product-subtype-text">${product.sub_type_name || 'N/A'}</span>
                    </div>
                </div>
                <div class="product-folder-actions">
                    <button type="button" class="btn-product-action btn-edit-product btn-edit"
                            data-id="${product.id}"
                            data-name="${product.name}"
                            data-price="${product.price}"
                            data-type-id="${product.product_type_id}"
                            data-subtype-id="${product.sub_type_id || ''}">
                        <i class="lucide-edit"></i> Edit
                    </button>
                    <button type="button" class="btn-product-action btn-options-product btn-create-variant"
                            data-id="${product.id}">
                        <i class="lucide-settings"></i> Options
                    </button>
                    <button type="button" class="btn-product-action btn-delete-product btn-delete"
                            data-id="${product.id}"
                            data-name="${product.name}"
                            data-price="${product.price}">
                        <i class="lucide-trash-2"></i> Delete
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(card);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // --- HELPER: Remove product from folder grid ---
    function removeProductFromFolderGrid(productId) {
        const card = document.querySelector(`.products-folder-grid .product-folder-card[data-product-id="${productId}"]`);
        if (card) card.remove();
    }

    // --- SEARCH FILTER (works with both table and folder cards) ---
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const search = searchInput.value.toLowerCase();

            // Filter table rows
            tableBody.querySelectorAll('tr').forEach(row => {
                const name = row.children[1]?.textContent.toLowerCase() || "";
                const type = row.children[4]?.textContent.toLowerCase() || "";
                const subtype = row.children[5]?.textContent.toLowerCase() || "";
                row.style.display = (name.includes(search) || type.includes(search) || subtype.includes(search)) ? "" : "none";
            });

            // Filter folder cards
            const productCards = document.querySelectorAll('.product-folder-card');
            productCards.forEach(card => {
                const productName = card.getAttribute('data-product-name') || '';
                const type = card.querySelector('.product-type-text')?.textContent.toLowerCase() || '';
                const subtype = card.querySelector('.product-subtype-text')?.textContent.toLowerCase() || '';

                if (productName.includes(search) || type.includes(search) || subtype.includes(search)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // --- ADD PRODUCT ---
    btnAddProduct.addEventListener('click', () => {
        Swal.fire({
            title: 'Add New Product',
            html: `
                <input id="prod-name" class="swal2-input" placeholder="Product Name">
                <input id="prod-price" type="number" step="0.01" class="swal2-input" placeholder="Price ($)">
                <select id="prod-type" class="swal2-input">
                    <option value="" disabled selected>Select Type</option>
                    ${window.productTypes?.map(t => `<option value="${t.id}">${t.name}</option>`).join('') || ''}
                </select>
                <select id="prod-subtype" class="swal2-input hidden">
                    <option value="" disabled selected>Select Subtype</option>
                </select>
                <input id="prod-image" type="file" accept="image/*" class="swal2-file">
                <textarea id="prod-desc" class="swal2-textarea" placeholder="Description"></textarea>
            `,
            confirmButtonText: 'Create',
            showCancelButton: true,
            focusConfirm: false,
            didOpen: () => {
                const typeSelect = document.getElementById('prod-type');
                const subtypeSelect = document.getElementById('prod-subtype');
                typeSelect?.addEventListener('change', () => {
                    const subtypes = window.subTypes?.[typeSelect.value] || [];
                    subtypeSelect.innerHTML = `<option value="" disabled selected>Select Subtype</option>`;
                    if (subtypes.length) {
                        subtypeSelect.classList.remove('hidden');
                        subtypes.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name;
                            subtypeSelect.appendChild(opt);
                        });
                    } else {
                        subtypeSelect.classList.add('hidden');
                    }
                });
            },
            preConfirm: async () => {
                const name = document.getElementById('prod-name')?.value.trim();
                const price = document.getElementById('prod-price')?.value;
                const type = document.getElementById('prod-type')?.value;
                const subtype = document.getElementById('prod-subtype')?.value;
                const desc = document.getElementById('prod-desc')?.value;
                const image = document.getElementById('prod-image')?.files[0];

                if (!name || !price || !type || !image) {
                    return Swal.showValidationMessage('Please fill in all required fields.');
                }

                const formData = new FormData();
                formData.append('name', name);
                formData.append('price', price);
                formData.append('product_type_id', type);
                formData.append('sub_type_id', subtype || '');
                formData.append('description', desc || '');
                formData.append('image', image);

                try {
                    const res = await fetch(`/admin/products/store-products`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData
                    });
                    if (!res.ok) {
                        const err = await res.json();
                        const msg = err.errors ? Object.values(err.errors).flat().join('<br>') : 'Validation failed.';
                        return Swal.showValidationMessage(msg);
                    }
                    return res.json();
                } catch {
                    return Swal.showValidationMessage('Request failed.');
                }
            }
        }).then(result => {
            if (result.isConfirmed && result.value?.success) {
                const p = result.value.product;

                // --- CREATE ROW DYNAMICALLY ---
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-product-id', p.id);
                newRow.innerHTML = `
                    <td>${tableBody.rows.length + 1}</td>
                    <td>${p.name}</td>
                    <td><img src="/assets/images/${p.image}" alt="${p.name}" class="product-img"></td>
                    <td>$${Number(p.price).toFixed(2)}</td>
                    <td>${p.product_type_name}</td>
                    <td>${p.sub_type_name || 'N/A'}</td>
                    <td>
                        <button class="btn btn-info btn-sm btn-edit"
                                data-id="${p.id}"
                                data-name="${p.name}"
                                data-price="${p.price}"
                                data-type-id="${p.product_type_id}"
                                data-subtype-id="${p.sub_type_id || ''}">
                            Edit
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm btn-delete"
                                data-id="${p.id}"
                                data-name="${p.name}"
                                data-price="${p.price}">
                            Delete
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm btn-create-variant"
                                data-id="${p.id}">
                            Add Options
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);

                // Update row numbers dynamically
                Array.from(tableBody.rows).forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                });

                // Add to folder grid
                addProductToFolderGrid(p);

                Swal.fire('Success', 'Product added!', 'success');
            }
        });
    });

    // --- TABLE BUTTON DELEGATION (EDIT, DELETE, CREATE VARIANT) ---
    // Use document delegation to catch clicks from both table and folder cards
    document.addEventListener('click', async e => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = btn.dataset.id;

        // --- EDIT ---
        if (btn.classList.contains('btn-edit')) {
            const name = btn.dataset.name;
            const price = btn.dataset.price;
            const typeId = btn.dataset.typeId;
            const subtypeId = btn.dataset.subtypeId;

            const typeOptions = window.productTypes?.map(t =>
                `<option value="${t.id}" ${t.id == typeId ? 'selected' : ''}>${t.name}</option>`
            ).join('') || '';

            Swal.fire({
                title: `Edit "${name}"`,
                html: `
                    <input id="swal-name" class="swal2-input" value="${name}">
                    <input id="swal-price" type="number" class="swal2-input" value="${price}">
                    <select id="swal-type" class="swal2-input">${typeOptions}</select>
                    <select id="swal-subtype" class="swal2-input hidden"><option value="" disabled>Select Subtype</option></select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                didOpen: () => {
                    const typeSelect = document.getElementById('swal-type');
                    const subtypeSelect = document.getElementById('swal-subtype');
                    const loadSubtypes = (selectedTypeId, selectedSubtypeId) => {
                        subtypeSelect.innerHTML = `<option value="" disabled>Select Subtype</option>`;
                        const subtypes = window.subTypes?.[selectedTypeId] || [];
                        if (subtypes.length) {
                            subtypeSelect.classList.remove('hidden');
                            subtypes.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.id;
                                opt.textContent = s.name;
                                if (s.id == selectedSubtypeId) opt.selected = true;
                                subtypeSelect.appendChild(opt);
                            });
                        } else {
                            subtypeSelect.classList.add('hidden');
                        }
                    };
                    if (typeId) loadSubtypes(typeId, subtypeId);
                    typeSelect.addEventListener('change', () => loadSubtypes(typeSelect.value, null));
                },
                preConfirm: () => {
                    const newName = document.getElementById('swal-name')?.value.trim();
                    const newPrice = parseFloat(document.getElementById('swal-price')?.value);
                    const newTypeId = document.getElementById('swal-type')?.value;
                    const newSubtypeId = document.getElementById('swal-subtype')?.value || null;

                    if (!newName) Swal.showValidationMessage('Enter product name');
                    if (isNaN(newPrice) || newPrice <= 0) Swal.showValidationMessage('Enter valid price');
                    if (!newTypeId) Swal.showValidationMessage('Select a product type');

                    return { name: newName, price: newPrice, product_type_id: newTypeId, sub_type_id: newSubtypeId };
                }
            }).then(async res => {
                if (!res.isConfirmed) return;
                try {
                    const r = await fetch(`/admin/products/${id}/edit-products`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(res.value)
                    });
                    const data = await r.json();
                    if (data.success) {
                        Swal.fire('Updated!', data.message, 'success');

                        // Update table row
                        const row = tableBody.querySelector(`tr[data-product-id="${id}"]`);
                        if (row) {
                            row.children[1].textContent = res.value.name;
                            row.children[3].textContent = `$${Number(res.value.price).toFixed(2)}`;
                            row.children[4].textContent = window.productTypes.find(t => t.id == res.value.product_type_id)?.name || 'N/A';
                            row.children[5].textContent = res.value.sub_type_id
                                ? window.subTypes[res.value.product_type_id]?.find(s => s.id == res.value.sub_type_id)?.name || 'N/A'
                                : 'N/A';

                            // Update button data attributes in table
                            const tableEditBtn = row.querySelector('.btn-edit');
                            if (tableEditBtn) {
                                tableEditBtn.dataset.name = res.value.name;
                                tableEditBtn.dataset.price = res.value.price;
                                tableEditBtn.dataset.typeId = res.value.product_type_id;
                                tableEditBtn.dataset.subtypeId = res.value.sub_type_id;
                            }

                            const tableDeleteBtn = row.querySelector('.btn-delete');
                            if (tableDeleteBtn) {
                                tableDeleteBtn.dataset.name = res.value.name;
                                tableDeleteBtn.dataset.price = res.value.price;
                            }
                        }

                        // Sync folder card
                        syncFolderCardWithTable(id);
                    } else Swal.fire('Error', data.message, 'error');
                } catch {
                    Swal.fire('Error', 'Request failed.', 'error');
                }
            });
        }

        // --- DELETE ---
        if (btn.classList.contains('btn-delete')) {
            Swal.fire({
                title: `Delete "${btn.dataset.name}"?`,
                html: `<p>Price: <strong>$${btn.dataset.price}</strong></p><p>This action cannot be undone.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(async r => {
                if (!r.isConfirmed) return;
                try {
                    const res = await fetch(`/admin/products/${id}/delete-products`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');

                        // Remove from table
                        const row = tableBody.querySelector(`tr[data-product-id="${id}"]`);
                        if (row) row.remove();

                        // Update row numbers
                        Array.from(tableBody.rows).forEach((row, index) => {
                            row.cells[0].textContent = index + 1;
                        });

                        // Remove from folder grid
                        removeProductFromFolderGrid(id);
                    } else Swal.fire('Error', data.message, 'error');
                } catch {
                    Swal.fire('Error', 'Request failed.', 'error');
                }
            });
        }

        // --- CREATE VARIANT ---
        if (btn.classList.contains('btn-create-variant') && window.variantCreateUrl) {
            window.location.href = `${window.variantCreateUrl}/${id}/variants/create`;
        }
    });
});
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        // Sync folder cards with table updates
        window.syncFolderCardWithTable = function(productId) {
            const tableRow = document.querySelector(`#product-table-body tr[data-product-id="${productId}"]`);
            const folderCard = document.querySelector(`.products-folder-grid .product-folder-card[data-product-id="${productId}"]`);

            if (!tableRow || !folderCard) return;

            // Update folder card content from table
            const name = tableRow.children[1].textContent;
            const price = tableRow.children[3].textContent;
            const type = tableRow.children[4].textContent;
            const subtype = tableRow.children[5].textContent;
            const image = tableRow.children[2].querySelector('img').src;

            folderCard.querySelector('.product-name-text').textContent = name;
            folderCard.querySelector('.product-price-text').textContent = price;
            folderCard.querySelector('.product-type-text').textContent = type;
            folderCard.querySelector('.product-subtype-text').textContent = subtype;
            folderCard.querySelector('.product-folder-img').src = image;
            folderCard.setAttribute('data-product-name', name.toLowerCase());

            // Update button data attributes
            const editBtn = folderCard.querySelector('.btn-edit');
            const deleteBtn = folderCard.querySelector('.btn-delete');
            if (editBtn && tableRow.querySelector('.btn-edit')) {
                const tableEditBtn = tableRow.querySelector('.btn-edit');
                editBtn.dataset.name = tableEditBtn.dataset.name;
                editBtn.dataset.price = tableEditBtn.dataset.price;
                editBtn.dataset.typeId = tableEditBtn.dataset.typeId;
                editBtn.dataset.subtypeId = tableEditBtn.dataset.subtypeId;
            }
            if (deleteBtn && tableRow.querySelector('.btn-delete')) {
                const tableDeleteBtn = tableRow.querySelector('.btn-delete');
                deleteBtn.dataset.name = tableDeleteBtn.dataset.name;
                deleteBtn.dataset.price = tableDeleteBtn.dataset.price;
            }
        };

        // Add new product to folder grid
        window.addProductToFolderGrid = function(product) {
            const grid = document.getElementById('products-folder-grid');
            if (!grid) return;

            const card = document.createElement('div');
            card.className = 'product-folder-card';
            card.setAttribute('data-product-id', product.id);
            card.setAttribute('data-product-name', product.name.toLowerCase());
            card.innerHTML = `
                <div class="product-folder-image">
                    <img src="/assets/images/${product.image}" alt="${product.name}" class="product-folder-img">
                    <div class="product-folder-overlay">
                        <button type="button" class="product-action-btn btn-view-detail" data-product-id="${product.id}">
                            <i class="lucide-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-folder-content">
                    <div class="product-folder-header">
                        <h5 class="product-folder-name">
                            <i class="lucide-coffee"></i>
                            <span class="product-name-text">${product.name}</span>
                        </h5>
                        <span class="product-folder-price product-price-text">${Number(product.price).toFixed(2)}</span>
                    </div>
                    <div class="product-folder-meta">
                        <div class="meta-item">
                            <i class="lucide-layers"></i>
                            <span class="product-type-text">${product.product_type_name}</span>
                        </div>
                        <div class="meta-item">
                            <i class="lucide-folder"></i>
                            <span class="product-subtype-text">${product.sub_type_name || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="product-folder-actions">
                        <button type="button" class="btn-product-action btn-edit-product btn-edit"
                                data-id="${product.id}"
                                data-name="${product.name}"
                                data-price="${product.price}"
                                data-type-id="${product.product_type_id}"
                                data-subtype-id="${product.sub_type_id || ''}">
                            <i class="lucide-edit"></i> Edit
                        </button>
                        <button type="button" class="btn-product-action btn-options-product btn-create-variant"
                                data-id="${product.id}">
                            <i class="lucide-settings"></i> Options
                        </button>
                        <button type="button" class="btn-product-action btn-delete-product btn-delete"
                                data-id="${product.id}"
                                data-name="${product.name}"
                                data-price="${product.price}">
                            <i class="lucide-trash-2"></i> Delete
                        </button>
                    </div>
                </div>
            `;
            grid.appendChild(card);
            lucide.createIcons();
        };

        // Remove product from folder grid
        window.removeProductFromFolderGrid = function(productId) {
            const card = document.querySelector(`.products-folder-grid .product-folder-card[data-product-id="${productId}"]`);
            if (card) card.remove();
        };

        // Override table update to also update folder cards
        const originalTableUpdates = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    const row = mutation.target.closest('tr[data-product-id]');
                    if (row) {
                        const productId = row.getAttribute('data-product-id');
                        syncFolderCardWithTable(productId);
                    }
                }
            });
        });

        const tableBody = document.getElementById('product-table-body');
        if (tableBody) {
            originalTableUpdates.observe(tableBody, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }

        // Product search functionality for folder cards
        const productSearch = document.getElementById('productSearch');
        if (productSearch) {
            productSearch.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const productCards = document.querySelectorAll('.product-folder-card');

                productCards.forEach(card => {
                    const productName = card.getAttribute('data-product-name');
                    const type = card.querySelector('.product-type-text')?.textContent.toLowerCase() || '';
                    const subtype = card.querySelector('.product-subtype-text')?.textContent.toLowerCase() || '';

                    if (productName.includes(searchTerm) || type.includes(searchTerm) || subtype.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }

        // Add event listeners for folder view/close buttons
        document.querySelectorAll('.btn-view-folder').forEach(btn => {
            btn.addEventListener('click', function() {
                const subtypeId = this.getAttribute('data-subtype-id');
                toggleFolder(subtypeId);
            });
        });

        document.querySelectorAll('.btn-close-folder').forEach(btn => {
            btn.addEventListener('click', function() {
                const subtypeId = this.getAttribute('data-subtype-id');
                toggleFolder(subtypeId);
            });
        });
    });

    // Toggle folder view
    function toggleFolder(subtypeId) {
        const folderOverlay = document.getElementById(`folder-products-${subtypeId}`);

        if (!folderOverlay) {
            console.error('Folder overlay not found for subtype:', subtypeId);
            return;
        }

        if (folderOverlay.style.display === 'none') {
            folderOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        } else {
            folderOverlay.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        setTimeout(() => lucide.createIcons(), 50);
    }
