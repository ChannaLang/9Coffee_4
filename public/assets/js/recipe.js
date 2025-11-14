document.addEventListener('DOMContentLoaded', () => {
    const sizes = JSON.parse(document.getElementById('quantityTable').dataset.sizes);
    const addBtn = document.getElementById('addIngredientBtn');
    const select = document.getElementById('materialSelect');
    const quantityTbody = document.querySelector('#quantityTable tbody');

    // --- Add new material row ---
    addBtn.addEventListener('click', () => {
        const matId = select.value;
        if (!matId) return alert('Please select a material.');
        const matName = select.selectedOptions[0].dataset.name;
        const matUnit = select.selectedOptions[0].dataset.unit;

        // Prevent duplicate
        if (quantityTbody.querySelector(`tr[data-id="${matId}"]`)) return;

        const row = document.createElement('tr');
        row.dataset.id = matId;
        row.innerHTML = `
            <td>${matName}</td>
            ${sizes.map(size => `<td><input type="number" step="0.01" min="0" class="form-control" name="materials[${matId}][${size}]"></td>`).join('')}
            <td>${matUnit}</td>
            <td><button type="button" class="btn btn-danger btn-sm btnRemove">X</button></td>
        `;
        quantityTbody.appendChild(row);
        select.value = null;
        $('#materialSelect').trigger('change'); // clear select2
    });

    // --- Remove row ---
    document.addEventListener('click', e => {
        if (e.target.classList.contains('btnRemove')) {
            e.target.closest('tr').remove();
        }
    });
});
