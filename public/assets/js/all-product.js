document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('table tbody');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // --- Add Product ---
    const btnAddProduct = document.getElementById('btnAddProduct');
btnAddProduct.addEventListener('click', () => {
    Swal.fire({
        title: 'Add New Product',
        html: `
            <input id="prod-name" class="swal2-input" placeholder="Product Name">
            <input id="prod-price" type="number" step="0.01" class="swal2-input" placeholder="Price ($)">
            <select id="prod-type" class="swal2-input">
                <option value="" disabled selected>Select Type</option>
                ${window.productTypes.map(t => `<option value="${t.id}">${t.name}</option>`).join('')}
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

            typeSelect.addEventListener('change', () => {
                const typeId = typeSelect.value;
                const subtypes = window.subTypes[typeId] || [];

                // clear old options
                subtypeSelect.innerHTML = `<option value="" disabled selected>Select Subtype</option>`;

                if(subtypes.length > 0){
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
            const name = document.getElementById('prod-name').value.trim();
            const price = document.getElementById('prod-price').value;
            const type = document.getElementById('prod-type').value;
            const subtype = document.getElementById('prod-subtype').value;
            const desc = document.getElementById('prod-desc').value;
            const image = document.getElementById('prod-image').files[0];

            if (!name || !price || !type || !image)
                return Swal.showValidationMessage('Please fill in all required fields.');

            const formData = new FormData();
            formData.append('name', name);
            formData.append('price', price);
            formData.append('product_type_id', type);
            formData.append('sub_type_id', subtype || '');
            formData.append('description', desc);
            formData.append('image', image);

            const res = await fetch(`/admin/products/store-products`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'  // <-- force Laravel JSON response
                },
                body: formData
            });

            if (!res.ok) {
                const err = await res.json();
                let msg = err.errors ? Object.values(err.errors).flat().join('<br>') : 'Validation failed.';
                return Swal.showValidationMessage(msg);
            }
            return res.json();
        }
    }).then(result => {
    if (result.isConfirmed && result.value.success) {
        const p = result.value.product;

        const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${tableBody.rows.length + 1}</td>
                <td>${p.name}</td>
                <td><img src="/assets/images/${p.image}" alt="${p.name}" width="50"></td>
                <td>$${Number(p.price).toFixed(2)}</td>
                <td>${p.product_type_name}</td>
                <td>${p.sub_type_name || 'N/A'}</td>
                <td>
                    <button class="btn btn-sm btn-primary btn-edit"
                            data-id="${p.id}"
                            data-name="${p.name}"
                            data-price="${p.price}"
                            data-type-id="${p.product_type_id}"
                            data-subtype-id="${p.sub_type_id}">
                        Edit
                    </button>
                </td>
                <td>
                    <button class="btn btn-sm btn-danger btn-delete"
                            data-id="${p.id}"
                            data-name="${p.name}">
                        Delete
                    </button>
                </td>
                <td>
                <button class="btn btn-success btn-sm rounded-pill btn-create-variant"
                        data-id="${p.id}">
                    Create Variant
                </button>
                </td>

            `;

        tableBody.appendChild(newRow);
        Swal.fire('Success', 'Product added!', 'success');
    }
});

});


    // --- Edit Product ---
    tableBody.addEventListener('click', async e => {
        const btn = e.target.closest('button');
        if (!btn) return;
        if (btn.classList.contains('btn-edit')) {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const price = btn.dataset.price;
            const typeId = btn.dataset.typeId;       // data-type-id
            const subtypeId = btn.dataset.subtypeId; // data-subtype-id

            // Build type options
            const typeOptions = window.productTypes.map(t =>
                `<option value="${t.id}" ${t.id == typeId ? 'selected' : ''}>${t.name}</option>`
            ).join('');

            Swal.fire({
                title: `Edit "${name}"`,
                html: `
                    <input id="swal-name" class="swal2-input" value="${name}">
                    <input id="swal-price" type="number" class="swal2-input" value="${price}">
                    <select id="swal-type" class="swal2-input">
                        <option value="" disabled>Select Type</option>
                        ${typeOptions}
                    </select>
                    <select id="swal-subtype" class="swal2-input">
                        <option value="" disabled>Select Subtype</option>
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                didOpen: () => {
                    const typeSelect = document.getElementById('swal-type');
                    const subtypeSelect = document.getElementById('swal-subtype');

                    const loadSubtypes = (selectedTypeId, selectedSubtypeId) => {
                        subtypeSelect.innerHTML = `<option value="" disabled>Select Subtype</option>`;
                        const subtypes = window.subTypes[selectedTypeId] || [];
                        subtypes.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.name;
                            if (s.id == selectedSubtypeId) opt.selected = true;
                            subtypeSelect.appendChild(opt);
                        });
                    };

                    // Initial load
                    if (typeId) loadSubtypes(typeId, subtypeId);

                    // When type changes
                    typeSelect.addEventListener('change', () => {
                        loadSubtypes(typeSelect.value, null);
                    });
                },
                preConfirm: () => {
                    const newName = document.getElementById('swal-name').value.trim();
                    const newPrice = parseFloat(document.getElementById('swal-price').value);
                    const newTypeId = document.getElementById('swal-type').value;
                    const newSubtypeId = document.getElementById('swal-subtype').value || null;

                    if (!newName) Swal.showValidationMessage('Enter product name');
                    if (isNaN(newPrice) || newPrice <= 0) Swal.showValidationMessage('Enter valid price');
                    if (!newTypeId) Swal.showValidationMessage('Select a product type');

                    return {
                        name: newName,
                        price: newPrice,
                        product_type_id: newTypeId,
                        sub_type_id: newSubtypeId
                    };
                }
            }).then(async result => {
    if (!result.isConfirmed) return;

    const data = result.value; // {name, price, product_type_id, sub_type_id}

    try {
        const res = await fetch(`/admin/products/${id}/edit-products`, {
            method: 'POST', 
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const resData = await res.json();

        if (resData.success) {
            Swal.fire('Updated!', resData.message, 'success');

            // Update table row in-place
            const row = btn.closest('tr');
            row.children[1].textContent = data.name;
            row.children[3].textContent = `$${Number(data.price).toFixed(2)}`;
            row.children[4].textContent = window.productTypes.find(t => t.id == data.product_type_id).name;
            row.children[5].textContent = data.sub_type_id
                ? (window.subTypes[data.product_type_id].find(s => s.id == data.sub_type_id)?.name || 'N/A')
                : 'N/A';

            // Update button dataset
            const editBtn = row.querySelector('.btn-edit');
            editBtn.dataset.name = data.name;
            editBtn.dataset.price = data.price;
            editBtn.dataset.typeId = data.product_type_id;
            editBtn.dataset.subtypeId = data.sub_type_id;
        } else {
            Swal.fire('Error', resData.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Request failed.', 'error');
    }
});

        }


        // --- Delete Product ---
        if (btn.classList.contains('btn-delete')) {
            const { id, name, price } = btn.dataset;
            Swal.fire({
                title: `Delete "${name}"?`,
                html: `<p>Price: <strong>$${price}</strong></p><p>This action cannot be undone.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then(async result => {
                if (!result.isConfirmed) return;
                try {
                    const res = await fetch(`/admin/products/${id}/delete-products`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        btn.closest('tr').remove();
                    } else Swal.fire('Error', data.message, 'error');
                } catch { Swal.fire('Error', 'Request failed.', 'error'); }
            });
        }
    });
        // --- Create Variant ---
tableBody.addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;

    if (btn.classList.contains('btn-create-variant')) {
        const productId = btn.dataset.id;

        // Use the same route structure that your old working button used
        window.location.href = `${window.variantCreateUrl}/${productId}/variants/create`;
    }
});

});
