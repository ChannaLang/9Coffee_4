document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('table tbody');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ===== RAW MATERIAL HELPERS =====
    function attachMaterialListeners(row) {
        const btnAddStock = row.querySelector('.btnAddStock');
        const btnReduceStock = row.querySelector('.btnReduceStock');
        const btnUpdateMaterial = row.querySelector('.btnUpdateMaterial');
        const btnDeleteMaterial = row.querySelector('.btnDeleteMaterial');

        if (btnAddStock) btnAddStock.addEventListener('click', () => handleStock(btnAddStock, 'add-stock', 'Add Stock'));
        if (btnReduceStock) btnReduceStock.addEventListener('click', () => handleStock(btnReduceStock, 'reduce-stock', 'Reduce Stock'));
        if (btnUpdateMaterial) btnUpdateMaterial.addEventListener('click', () => handleUpdateMaterial(btnUpdateMaterial));
        if (btnDeleteMaterial) btnDeleteMaterial.addEventListener('click', () => handleDeleteMaterial(btnDeleteMaterial));
    }

    async function handleStock(btn, action, title) {
        const { id, name, unit } = btn.dataset;
        const { value: qty } = await Swal.fire({
            title: `${title}: ${name}`,
            input: 'number',
            inputLabel: `Enter amount (${unit})`,
            inputAttributes: { min: 1 },
            showCancelButton: true,
            confirmButtonText: title,
            preConfirm: v => v > 0 ? v : Swal.showValidationMessage("Enter a valid quantity")
        });
        if (qty) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/raw-material/${id}/${action}`;
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">
                              <input type="hidden" name="quantity" value="${qty}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    async function handleUpdateMaterial(btn) {
        const { id, name, unit } = btn.dataset;
        const { value } = await Swal.fire({
            title: 'Update Raw Ingredient',
            html: `
                <input type="text" id="update_name" class="swal2-input" value="${name}">
                <select id="update_unit" class="swal2-input">
                    <option value="g" ${unit==='g'?'selected':''}>Gram (g)</option>
                    <option value="ml" ${unit==='ml'?'selected':''}>Milliliter (ml)</option>
                    <option value="pcs" ${unit==='pcs'?'selected':''}>Pieces (pcs)</option>
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            preConfirm: () => {
                const newName = document.getElementById('update_name').value.trim();
                const newUnit = document.getElementById('update_unit').value;
                if (!newName) Swal.showValidationMessage('Enter valid name');
                return { newName, newUnit };
            }
        });

        if (!value) return;
        const payload = { name: value.newName, unit: value.newUnit };
        try {
            const res = await fetch(`/admin/raw-material/update/${id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                const row = btn.closest('tr');
                row.cells[1].textContent = data.name;
                row.cells[3].textContent = data.unit;
                row.querySelectorAll('button').forEach(b => {
                    b.dataset.name = data.name;
                    b.dataset.unit = data.unit;
                });
                Swal.fire('Success', 'Ingredient updated!', 'success');
            } else Swal.fire('Error', data.message, 'error');
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    }

    function handleDeleteMaterial(btn) {
        const { id, name } = btn.dataset;
        const qtyCell = btn.closest('tr').querySelector(`#displayQty${id}`);
        const quantity = parseFloat(qtyCell?.textContent || 0);
        if (quantity > 0) return Swal.fire('Error', 'Cannot delete material with stock!', 'error');

        Swal.fire({
            title: `Delete "${name}"?`,
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(result => {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/raw-material/${id}`;
            form.innerHTML = `<input type="hidden" name="_method" value="DELETE">
                              <input type="hidden" name="_token" value="${csrfToken}">`;
            document.body.appendChild(form);
            form.submit();
        });
    }

    // ===== PRODUCT CRUD / ASSIGN RECIPE =====
    const assignModalEl = document.getElementById('assignRecipeModal') || createAssignModal();
    const assignModal = new bootstrap.Modal(assignModalEl);
    const recipeBody = document.getElementById('recipeMaterialsBody');
    const recipeForm = document.getElementById('assignRecipeForm');
    const recipeProductId = document.getElementById('recipeProductId');

    function createAssignModal() {
        document.body.insertAdjacentHTML('beforeend', `
<div class="modal fade" id="assignRecipeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background-color:#2c3e50; color:#ecf0f1; border-radius:1rem;">
      <div class="modal-header" style="border-bottom:2px solid #f39c12;">
        <h5 class="modal-title" style="color:#f39c12;">Assign Recipe</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="assignRecipeForm">
          <input type="text" id="materialSearch" placeholder="Search raw materials..." class="form-control mb-2">
          <input type="hidden" id="recipeProductId" name="product_id">
          <table class="table table-hover" style="color:#ecf0f1;">
            <thead style="background-color:#34495e; color:#f1c40f;">
              <tr>
                <th>Raw Material</th>
                <th>In-Stock</th>
                <th>Quantity Required</th>
                <th>Unit</th>
              </tr>
            </thead>
            <tbody id="recipeMaterialsBody"></tbody>
          </table>
          <button type="submit" class="btn btn-warning text-dark fw-bold">Save Recipe</button>
        </form>
      </div>
    </div>
  </div>
</div>
        `);
        return document.getElementById('assignRecipeModal');
    }

    tableBody.addEventListener('click', async e => {
        const btn = e.target.closest('button');

        if (!btn) return;

        // --- EDIT PRODUCT ---
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
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
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
        } catch (err) {
            Swal.fire('Error', 'Request failed or token invalid.', 'error');
        }
    });
}


        // --- DELETE PRODUCT ---
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
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        btn.closest('tr').remove();
                    } else Swal.fire('Error', data.message, 'error');
                } catch { Swal.fire('Error', 'Request failed or token invalid.', 'error'); }
            });
        }

        // --- VIEW ASSIGNED MATERIALS ---
        if (btn.classList.contains('btnViewRecipe')) {
            try {
                const materials = await (await fetch(btn.dataset.url, { headers: { 'Accept': 'application/json' } })).json();
                if (!materials.length) return Swal.fire('No materials', 'No assigned raw materials.', 'info');
                const html = `<ul style="text-align:left;">${materials.map(mat => `<li>${mat.name} - ${mat.quantity_required} ${mat.unit}</li>`).join('')}</ul>`;
                Swal.fire({ title: 'Assigned Materials', html, icon: 'info' });
            } catch { Swal.fire('Error', 'Failed to fetch assigned materials.', 'error'); }
        }

        // --- ASSIGN RECIPE ---
        if (btn.classList.contains('btnAssignRecipe')) {
            const productId = btn.dataset.productId;
            recipeProductId.value = productId;
            recipeBody.innerHTML = `<tr><td colspan="4">Loading materials...</td></tr>`;
            assignModal.show();

            let rawMaterials = [];
            try { rawMaterials = await (await fetch(`/admin/product/${productId}/get-materials`)).json(); }
            catch { recipeBody.innerHTML = `<tr><td colspan="4">Failed to load raw materials.</td></tr>`; return Swal.fire('Error', 'Could not load raw materials.', 'error'); }

            if (!rawMaterials.length) { recipeBody.innerHTML = `<tr><td colspan="4">No raw materials available.</td></tr>`; return; }

            recipeBody.innerHTML = rawMaterials.map(mat => `
                <tr>
                    <td>${mat.name}</td>
                    <td>${mat.quantity}</td>
                    <td><input type="number" name="materials[${mat.id}]" min="0" max="${mat.quantity}" step="0.01" value="${mat.assigned_qty || 0}" class="form-control"></td>
                    <td>${mat.unit}</td>
                </tr>
            `).join('');

            document.getElementById('materialSearch').oninput = function() {
                const query = this.value.toLowerCase();
                recipeBody.querySelectorAll('tr').forEach(row => {
                    row.style.display = row.cells[0].textContent.toLowerCase().includes(query) ? '' : 'none';
                });
            };
        }
    });

    // --- SAVE ASSIGNED MATERIALS ---
    recipeForm.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(recipeForm);
        const productId = recipeProductId.value;
        try {
            const res = await fetch(`/admin/product/${productId}/add-materials`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData });
            if (!res.ok) throw new Error('Failed to save recipe');
            Swal.fire('Success', 'Recipe saved successfully!', 'success');
            assignModal.hide();
            location.reload();
        } catch (err) { Swal.fire('Error', err.message, 'error'); }
    });

    // Attach raw material listeners for existing rows
    document.querySelectorAll('table tbody tr').forEach(row => attachMaterialListeners(row));
     // ===== ADD PRODUCT =====
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
                    <td>
                        <button class="btn btn-primary btn-sm btnAssignRecipe" data-product-id="${p.id}">Assign Recipe</button>
                        <button class="btn btn-sm btn-info btnViewRecipe" data-url="/admin/product/${p.id}/get-assigned-materials">View Assigned</button>
                    </td>
                `;
                tableBody.prepend(newRow);
            }
        });
    });

    // Attach material listeners to existing rows
    document.querySelectorAll('table tbody tr').forEach(row => attachMaterialListeners(row));
});
