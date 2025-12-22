document.addEventListener("DOMContentLoaded", () => {

    const token = document.querySelector('meta[name="csrf-token"]').content;

    // Reinitialize Lucide after DOM changes
    function refreshIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // SweetAlert theme configuration
    const swalTheme = {
        background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
        color: '#f5e6d3',
        confirmButtonColor: '#d4a373'
    };

    function attachMaterialListeners(row) {
        const btnAdd = row.querySelector('.btnAddStock');
        const btnReduce = row.querySelector('.btnReduceStock');
        const btnUpdate = row.querySelector('.btnUpdateMaterial');
        const btnDelete = row.querySelector('.btnDeleteMaterial');

        // --- ADD STOCK ---
        if (btnAdd) {
            btnAdd.addEventListener('click', () => {
                const { id, name, unit } = btnAdd.dataset;
                Swal.fire({
                    title: `Add Stock: ${name}`,
                    input: 'number',
                    inputLabel: `Enter amount (${unit})`,
                    inputAttributes: {
                        min: 0.01,
                        step: 0.01
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Add',
                    ...swalTheme,
                    preConfirm: qty => {
                        if (!qty || parseFloat(qty) <= 0) Swal.showValidationMessage('Enter a valid quantity');
                        return parseFloat(qty);
                    }
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch(`/admin/raw-material/add/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ quantity: result.value })
                    })
                    .then(res => res.json())
                    .then(data => {
                        const qtyCell = row.querySelector(`#displayQty${data.id}`);
                        const badge = row.querySelector('span.badge');
                        if (qtyCell) qtyCell.textContent = parseFloat(data.quantity).toFixed(2);
                        if (badge) {
                            badge.className = data.quantity < 5 ? 'badge bg-danger' : 'badge bg-success';
                            badge.textContent = data.quantity < 5 ? 'Low Stock' : 'In Stock';
                        }
                        Swal.fire({ ...swalTheme, icon: 'success', title: 'Success', text: 'Stock added!', timer: 2000, showConfirmButton: false });
                    })
                    .catch(err => Swal.fire({ ...swalTheme, icon: 'error', title: 'Error', text: err.message }));
                });
            });
        }

        // --- REDUCE STOCK ---
        if (btnReduce) {
            btnReduce.addEventListener('click', () => {
                const { id, name, unit } = btnReduce.dataset;
                Swal.fire({
                    title: `Reduce Stock: ${name}`,
                    input: 'number',
                    inputLabel: `Enter amount to reduce (${unit})`,
                    inputAttributes: { min: 0.01, step: 0.01 },
                    showCancelButton: true,
                    confirmButtonText: 'Reduce',
                    ...swalTheme,
                    preConfirm: qty => {
                        if (!qty || parseFloat(qty) <= 0) Swal.showValidationMessage('Enter a valid quantity');
                        return parseFloat(qty);
                    }
                })
                .then(result => {
                    if (!result.isConfirmed) return;
                    fetch(`/admin/raw-material/reduce/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ quantity: result.value })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Not enough stock!');
                        return res.json();
                    })
                    .then(data => {
                        const qtyCell = row.querySelector(`#displayQty${data.id}`);
                        const badge = row.querySelector('span.badge');
                        if (qtyCell) qtyCell.textContent = parseFloat(data.quantity).toFixed(2);
                        if (badge) {
                            badge.className = data.quantity < 5 ? 'badge bg-danger' : 'badge bg-success';
                            badge.textContent = data.quantity < 5 ? 'Low Stock' : 'In Stock';
                        }
                        Swal.fire({ ...swalTheme, icon: 'success', title: 'Success', text: 'Stock reduced!', timer: 2000, showConfirmButton: false });
                    })
                    .catch(err => Swal.fire({ ...swalTheme, icon: 'error', title: 'Error', text: err.message }));
                });
            });
        }

        // --- UPDATE MATERIAL ---
        if (btnUpdate) {
            btnUpdate.addEventListener('click', () => {
                const { id, name, unit } = btnUpdate.dataset;
                const row = btnUpdate.closest('tr');
                Swal.fire({
                    title: 'Update Material',
                    html: `
                        <input type="text" id="update_name" class="swal2-input" placeholder="Name" value="${name}">
                        <select id="update_unit" class="swal2-input">
                            <option value="g" ${unit==='g'?'selected':''}>Gram (g)</option>
                            <option value="ml" ${unit==='ml'?'selected':''}>Milliliter (ml)</option>
                            <option value="pcs" ${unit==='pcs'?'selected':''}>Pieces (pcs)</option>
                        </select>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    ...swalTheme,
                    preConfirm: () => {
                        const newName = document.getElementById('update_name').value.trim();
                        const newUnit = document.getElementById('update_unit').value;
                        if (!newName) Swal.showValidationMessage('Fill all fields');
                        return { newName, newUnit };
                    }
                }).then(result => {
                    if (!result.isConfirmed) return;
                    const { newName, newUnit } = result.value;

                    fetch(`/admin/raw-material/update/${id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ name: newName, unit: newUnit })
                    })
                    .then(res => res.json())
                    .then(data => {
                        row.cells[1].textContent = data.name;
                        row.cells[3].textContent = data.unit;

                        row.querySelectorAll('button').forEach(b => {
                            b.dataset.name = data.name;
                            b.dataset.unit = data.unit;
                        });

                        Swal.fire({ ...swalTheme, icon: 'success', title: 'Success', text: 'Material updated!', timer: 2000, showConfirmButton: false });
                    })
                    .catch(err => Swal.fire({ ...swalTheme, icon: 'error', title: 'Error', text: err.message }));
                });
            });
        }

        // --- DELETE MATERIAL ---
        if (btnDelete) {
            btnDelete.addEventListener('click', () => {
                const { id, name } = btnDelete.dataset;
                const qtyCell = row.querySelector(`#displayQty${id}`);
                const qty = qtyCell ? parseFloat(qtyCell.textContent) : 0;

                if (qty > 0) {
                    Swal.fire({ ...swalTheme, icon: 'error', title: 'Error', text: 'Cannot delete material with stock' });
                    return;
                }

                Swal.fire({
                    title: `Delete "${name}"?`,
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    ...swalTheme
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch(`/admin/raw-material/delete/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token }
                    })
                    .then(res => res.json())
                    .then(() => {
                        row.remove();
                        Swal.fire({ ...swalTheme, icon: 'success', title: 'Deleted!', text: 'Material removed', timer: 2000, showConfirmButton: false });
                    })
                    .catch(err => Swal.fire({ ...swalTheme, icon: 'error', title: 'Error', text: err.message }));
                });
            });
        }
    }

    // --- ADD NEW MATERIAL ---
    const btnAddMaterial = document.getElementById('btnAddMaterial');
    if (btnAddMaterial) {
        btnAddMaterial.addEventListener('click', () => {
            Swal.fire({
                title: 'Add Raw Material',
                html: `
                    <input type="number" id="rm_id" class="swal2-input" placeholder="ID">
                    <input type="text" id="rm_name" class="swal2-input" placeholder="Name">
                    <select id="rm_unit" class="swal2-input">
                        <option value="g">Gram (g)</option>
                        <option value="ml">Milliliter (ml)</option>
                        <option value="pcs">Pieces (pcs)</option>
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save',
                ...swalTheme,
                preConfirm: () => {
                    const id = parseInt(document.getElementById('rm_id').value);
                    const name = document.getElementById('rm_name').value.trim();
                    const unit = document.getElementById('rm_unit').value;
                    if (!id || !name) Swal.showValidationMessage('Fill all fields');
                    return { id, name, unit };
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                const { id, name, unit } = result.value;

                fetch(btnAddMaterial.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ id, name, unit, quantity: 0 })
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => {
                            throw new Error(Object.values(err.errors || {}).flat().join(', ') || err.message);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    const tbody = document.querySelector('table tbody');
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${data.id}</td>
                        <td id="displayName${data.id}">${data.name}</td>
                        <td id="displayQty${data.id}">${data.quantity.toFixed(2)}</td>
                        <td id="displayUnit${data.id}">${data.unit}</td>
                        <td><span class="badge ${data.quantity < 5 ? 'bg-danger' : 'bg-success'}">${data.quantity < 5 ? 'Low Stock' : 'In Stock'}</span></td>
                        <td>
                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                <button class="btn btn-success btn-action btnAddStock" data-id="${data.id}" data-name="${data.name}" data-unit="${data.unit}">
                                    <i class="lucide-plus" style="width: 16px; height: 16px;"></i>
                                    <span class="btn-text">Add</span>
                                </button>
                                <button class="btn btn-warning btn-action btnReduceStock" data-id="${data.id}" data-name="${data.name}" data-unit="${data.unit}">
                                    <i class="lucide-minus" style="width: 16px; height: 16px;"></i>
                                    <span class="btn-text">Reduce</span>
                                </button>
                                <button class="btn btn-primary btn-action btnUpdateMaterial" data-id="${data.id}" data-name="${data.name}" data-unit="${data.unit}">
                                    <i class="lucide-edit" style="width: 16px; height: 16px;"></i>
                                    <span class="btn-text">Edit</span>
                                </button>
                                <button class="btn btn-danger btn-action btnDeleteMaterial" data-id="${data.id}" data-name="${data.name}">
                                    <i class="lucide-trash-2" style="width: 16px; height: 16px;"></i>
                                    <span class="btn-text">Delete</span>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(newRow);
                    attachMaterialListeners(newRow);
                    refreshIcons();
                    Swal.fire({ ...swalTheme, icon: 'success', title: 'Success', text: 'Raw material added!', timer: 2000, showConfirmButton: false });
                })
                .catch(err => Swal.fire({ ...swalTheme, icon: 'error', title: 'Error', text: err.message }));
            });
        });
    }

    // Attach listeners to all existing rows
    document.querySelectorAll('table tbody tr').forEach(row => attachMaterialListeners(row));
});
