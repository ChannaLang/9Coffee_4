// ===== Create Add Subtype Modal via JS =====
function createAddSubtypeModal(productTypes) {
    // Check if modal already exists
    if(document.getElementById('addSubtypeModal')) return;

    // Create modal container
    const modalDiv = document.createElement('div');
    modalDiv.className = 'modal fade';
    modalDiv.id = 'addSubtypeModal';
    modalDiv.tabIndex = -1;
    modalDiv.setAttribute('aria-hidden', 'true');

    // Modal inner HTML
    modalDiv.innerHTML = `
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subtype</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-subtype-form">
                    <div class="mb-3">
                        <label for="productTypeSelect" class="form-label">Product Type</label>
                        <select class="form-select" id="productTypeSelect" name="product_type_id" required>
                            ${productTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subtypeName" class="form-label">Subtype Name</label>
                        <input type="text" class="form-control" id="subtypeName" name="name" placeholder="e.g., Ice" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Add Subtype</button>
                </form>
            </div>
        </div>
    </div>
    `;

    document.body.appendChild(modalDiv);

    // Handle form submission
    document.getElementById('add-subtype-form').addEventListener('submit', async function(e){
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch("/subtypes/store", { // Your Laravel route
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            const data = await response.json();
            if(data.success){
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('addSubtypeModal')).hide();

                // Append new button dynamically
                const container = document.querySelector('.filter-sub-btn-container');
                const btn = document.createElement('button');
                btn.className = 'btn btn-outline-warning filter-sub-btn';
                btn.dataset.subtype = data.subtype.name.toLowerCase();
                btn.textContent = data.subtype.name;
                container.appendChild(btn);
            } else {
                alert(data.message);
            }
        } catch(err) {
            console.error(err);
        }
    });
}

// ===== Attach to Add Subtype button =====
document.getElementById('btnAddSubtype').addEventListener('click', function(e){
    e.preventDefault();
    createAddSubtypeModal(window.productTypes); // create modal if not exists
    const modal = new bootstrap.Modal(document.getElementById('addSubtypeModal'));
    modal.show(); // show modal
});
