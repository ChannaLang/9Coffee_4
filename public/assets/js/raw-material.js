    document.addEventListener("DOMContentLoaded", () => {

        const token = document.querySelector('meta[name="csrf-token"]').content;

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
        step: 0.01  // <-- crucial to allow float numbers
    },
    showCancelButton: true,
    confirmButtonText: 'Add',
    preConfirm: qty => {
        if (!qty || parseFloat(qty) <= 0) Swal.showValidationMessage('Enter a valid quantity');
        return parseFloat(qty);  // convert to float
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
                                badge.textContent = data.quantity < 5 ? 'Low' : 'OK';
                            }
                            Swal.fire('Success', 'Stock added!', 'success');
                        })
                        .catch(err => Swal.fire('Error', err.message, 'error'));
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
                        inputAttributes: { min: 0.01, step: 0.01 }, // <-- allow decimals
                        showCancelButton: true,
                        confirmButtonText: 'Reduce',
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
                                badge.textContent = data.quantity < 5 ? 'Low' : 'OK';
                            }
                            Swal.fire('Success', 'Stock reduced!', 'success');
                        })
                        .catch(err => Swal.fire('Error', err.message, 'error'));
                    });
                });
            }

            // --- UPDATE MATERIAL ---
            if (btnUpdate) {
                btnUpdate.addEventListener('click', () => {
                    const { id, name, unit } = btnUpdate.dataset;
                    Swal.fire({
                        title: 'Update Material',
                        html: `
                            <input type="number" id="update_id" class="swal2-input" placeholder="ID" value="${id}">
                            <input type="text" id="update_name" class="swal2-input" placeholder="Name" value="${name}">
                            <select id="update_unit" class="swal2-input">
                                <option value="g" ${unit==='g'?'selected':''}>Gram (g)</option>
                                <option value="ml" ${unit==='ml'?'selected':''}>Milliliter (ml)</option>
                                <option value="pcs" ${unit==='pcs'?'selected':''}>Pieces (pcs)</option>
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        preConfirm: () => {
                            const newId = parseInt(document.getElementById('update_id').value);
                            const newName = document.getElementById('update_name').value.trim();
                            const newUnit = document.getElementById('update_unit').value;
                            if (!newId || !newName) Swal.showValidationMessage('Fill all fields');
                            return { newId, newName, newUnit };
                        }
                    }).then(result => {
                        if (!result.isConfirmed) return;
                        const { newId, newName, newUnit } = result.value;

                        fetch(`/admin/raw-material/update/${id}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ name: newName, unit: newUnit, new_id: newId !== parseInt(id) ? newId : undefined })
                        })
                        .then(res => res.json())
                        .then(data => {
                            row.cells[0].textContent = data.id;
                            row.cells[1].textContent = data.name;
                            row.cells[3].textContent = data.unit;
                            row.querySelectorAll('button').forEach(b => {
                                b.dataset.id = data.id;
                                b.dataset.name = data.name;
                                b.dataset.unit = data.unit;
                            });
                            Swal.fire('Success', 'Material updated!', 'success');
                        })
                        .catch(err => Swal.fire('Error', err.message, 'error'));
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
                        Swal.fire('Error', 'Cannot delete material with stock', 'error');
                        return;
                    }

                    Swal.fire({
                        title: `Delete "${name}"?`,
                        text: "This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete'
                    }).then(result => {
                        if (!result.isConfirmed) return;

                        fetch(`/admin/raw-material/delete/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': token }
                        })
                        .then(res => res.json())
                        .then(() => {
                            row.remove();
                            Swal.fire('Deleted!', 'Material removed', 'success');
                        })
                        .catch(err => Swal.fire('Error', err.message, 'error'));
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
                            <td><span class="badge ${data.quantity < 5 ? 'bg-danger' : 'bg-success'}">${data.quantity < 5 ? 'Low' : 'OK'}</span></td>
                            <td>
                                <button class="btn btn-success btnAddStock" data-id="${data.id}" data-name="${data.name}" data-unit="${data.unit}">➕ Add</button>
                                <button class="btn btn-warning btnReduceStock" data-id="${data.id}" data-name="${data.name}" data-unit="${data.unit}">➖ Reduce</button>
                                <button class="btn btn-primary btnUpdateMaterial" data-id="${data.id}" data-name="${data.name}" data-unit="${data.unit}">🔄 Update</button>
                                <button class="btn btn-danger btnDeleteMaterial" data-id="${data.id}" data-name="${data.name}">🗑 Delete</button>
                            </td>
                        `;
                        tbody.appendChild(newRow);
                        attachMaterialListeners(newRow);
                        Swal.fire('Success', 'Raw material added!', 'success');
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'));
                });
            });
        }

        // Attach listeners to all existing rows
        document.querySelectorAll('table tbody tr').forEach(row => attachMaterialListeners(row));
    });
