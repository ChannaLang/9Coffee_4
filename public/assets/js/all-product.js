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
                    ${window.productTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
                </select>
                <input id="prod-image" type="file" accept="image/*" class="swal2-file">
                <textarea id="prod-desc" class="swal2-textarea" placeholder="Description"></textarea>
            `,
            confirmButtonText: 'Create',
            showCancelButton: true,
            focusConfirm: false,
            preConfirm: async () => {
                const name = document.getElementById('prod-name').value.trim();
                const price = document.getElementById('prod-price').value;
                const type = document.getElementById('prod-type').value;
                const desc = document.getElementById('prod-desc').value;
                const image = document.getElementById('prod-image').files[0];

                if (!name || !price || !type || !image) return Swal.showValidationMessage('Please fill in all required fields.');

                const formData = new FormData();
                formData.append('name', name);
                formData.append('price', price);
                formData.append('product_type_id', type);
                formData.append('description', desc);
                formData.append('image', image);

                const res = await fetch(`/admin/products/store-products`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData });
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
                const counter = tableBody.querySelectorAll('tr').length + 1;
                newRow.innerHTML = `
                    <th scope="row">${counter}</th>
                    <td>${p.name}</td>
                    <td><img src="/assets/images/${p.image}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #6b4c3b;"></td>
                    <td>$${parseFloat(p.price).toFixed(2)}</td>
                    <td>${p.product_type_name || 'N/A'}</td>
                    <td><button class="btn btn-info btn-sm rounded-pill btn-edit" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}" data-type="${p.product_type_name || ''}">Edit</button></td>
                    <td><button class="btn btn-danger btn-sm rounded-pill btn-delete" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}">Delete</button></td>
                `;
                tableBody.prepend(newRow);
            }
        });
    });

    // --- Edit Product ---
    tableBody.addEventListener('click', async e => {
        const btn = e.target.closest('button');
        if (!btn) return;
        if (btn.classList.contains('btn-edit')) {
            const { id, name, price, type } = btn.dataset;
            const typeOptions = window.productTypes.map(t =>
                `<option value="${t.id}" ${t.name === type ? 'selected' : ''}>${t.name}</option>`
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
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                preConfirm: () => {
                    const newName = document.getElementById('swal-name').value.trim();
                    const newPrice = parseFloat(document.getElementById('swal-price').value);
                    const typeId = document.getElementById('swal-type').value;
                    if (!newName) Swal.showValidationMessage('Enter product name');
                    if (isNaN(newPrice) || newPrice <= 0) Swal.showValidationMessage('Enter valid price');
                    if (!typeId) Swal.showValidationMessage('Select a product type');
                    return { name: newName, price: newPrice, product_type_id: typeId };
                }
            }).then(async result => {
                if (!result.isConfirmed) return;
                const formData = result.value;
                try {
                    const res = await fetch(`/admin/products/${id}/edit-products`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire('Updated!', data.message, 'success');
                        const row = btn.closest('tr');
                        row.cells[1].textContent = formData.name;
                        row.cells[3].textContent = `$${formData.price.toFixed(2)}`;
                        row.cells[4].textContent = window.productTypes.find(t => t.id == formData.product_type_id).name;
                        btn.dataset.name = formData.name;
                        btn.dataset.price = formData.price;
                        btn.dataset.type = window.productTypes.find(t => t.id == formData.product_type_id).name;
                    } else Swal.fire('Error', data.message, 'error');
                } catch { Swal.fire('Error', 'Request failed.', 'error'); }
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
