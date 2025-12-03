
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('table tbody');
    const btnAddProduct = document.getElementById('btnAddProduct');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    // --- SEARCH FILTER ---
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const search = searchInput.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const name = row.children[1]?.textContent.toLowerCase() || "";
                const type = row.children[4]?.textContent.toLowerCase() || "";
                const subtype = row.children[5]?.textContent.toLowerCase() || "";

                if (
                    name.includes(search) ||
                    type.includes(search) ||
                    subtype.includes(search)
                ) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }


    if (!tableBody || !btnAddProduct || !csrfToken) {
        console.warn('Essential DOM elements or CSRF token missing.');
        return;
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
                <select id="prod-subtype" class="swal2-input" style="display:none;">
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

                if (!typeSelect || !subtypeSelect) return;

                typeSelect.addEventListener('change', () => {
                    const subtypes = window.subTypes?.[typeSelect.value] || [];
                    subtypeSelect.innerHTML = `<option value="" disabled selected>Select Subtype</option>`;
                    if (subtypes.length) {
                        subtypeSelect.style.display = 'block';
                        subtypes.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name;
                            subtypeSelect.appendChild(opt);
                        });
                    } else {
                        subtypeSelect.style.display = 'none';
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

                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${tableBody.rows.length + 1}</td>
                    <td>${p.name}</td>
                    <td><img src="/assets/images/${p.image}" alt="${p.name}" width="50" style="border-radius:8px;"></td>
                    <td>$${Number(p.price).toFixed(2)}</td>
                    <td>${p.product_type_name}</td>
                    <td>${p.sub_type_name || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-edit"
                            data-id="${p.id}"
                            data-name="${p.name}"
                            data-price="${p.price}"
                            data-type-id="${p.product_type_id}"
                            data-subtype-id="${p.sub_type_id}">
                            Edit
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-delete"
                            data-id="${p.id}"
                            data-name="${p.name}">
                            Delete
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm btn-create-variant" data-id="${p.id}">
                            Create Variant
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);
                Swal.fire('Success', 'Product added!', 'success');
            }
        });
    });

    // --- TABLE BUTTON DELEGATION ---
    tableBody.addEventListener('click', async (e) => {
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
                    <select id="swal-subtype" class="swal2-input"><option value="" disabled>Select Subtype</option></select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                didOpen: () => {
                    const typeSelect = document.getElementById('swal-type');
                    const subtypeSelect = document.getElementById('swal-subtype');
                    const loadSubtypes = (selectedTypeId, selectedSubtypeId) => {
                        subtypeSelect.innerHTML = `<option value="" disabled>Select Subtype</option>`;
                        const subtypes = window.subTypes?.[selectedTypeId] || [];
                        subtypes.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name;
                            if (s.id == selectedSubtypeId) opt.selected = true;
                            subtypeSelect.appendChild(opt);
                        });
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
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(res.value)
                    });
                    const data = await r.json();
                    if (data.success) {
                        Swal.fire('Updated!', data.message, 'success');
                        const row = btn.closest('tr');
                        row.children[1].textContent = res.value.name;
                        row.children[3].textContent = `$${Number(res.value.price).toFixed(2)}`;
                        row.children[4].textContent = window.productTypes.find(t => t.id == res.value.product_type_id)?.name || 'N/A';
                        row.children[5].textContent = res.value.sub_type_id
                            ? window.subTypes[res.value.product_type_id]?.find(s => s.id == res.value.sub_type_id)?.name || 'N/A'
                            : 'N/A';

                        const editBtn = row.querySelector('.btn-edit');
                        editBtn.dataset.name = res.value.name;
                        editBtn.dataset.price = res.value.price;
                        editBtn.dataset.typeId = res.value.product_type_id;
                        editBtn.dataset.subtypeId = res.value.sub_type_id;
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
                        btn.closest('tr')?.remove();
                    } else Swal.fire('Error', data.message, 'error');
                } catch {
                    Swal.fire('Error', 'Request failed.', 'error');
                }
            });
        }

        // --- CREATE VARIANT ---
        if (btn.classList.contains('btn-create-variant')) {
            if (window.variantCreateUrl) {
                window.location.href = `${window.variantCreateUrl}/${id}/variants/create`;
            }
        }
    });
});
