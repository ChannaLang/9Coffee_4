// ===== Create Add Subtype Modal via JS =====
function initAddSubtypeModal() {
    const btnAddSubtype = document.getElementById('btnAddSubtype');
    const modalEl = document.getElementById('addSubtypeModal');
    const form = document.getElementById('add-subtype-form');

    if (!btnAddSubtype || !modalEl || !form) return;

    const modal = new bootstrap.Modal(modalEl);

    // Show modal on button click
    btnAddSubtype.addEventListener('click', () => modal.show());

    // Handle form submission to add subtype
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch("/admin/categories/store-subtype", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                modal.hide();
                form.reset();

                const subtype = data.subtype;
                const tabContent = document.getElementById(`type-${subtype.product_type_id}`);
                if (tabContent) {
                    // Create new subtype card
                    const div = document.createElement('div');
                    div.className = 'col-md-4 mb-3';
                    div.innerHTML = `
                        <div class="card category-card" data-product-count="0">
                            <div class="card-header">${subtype.name}</div>
                            <div class="card-body">
                                <ul class="list-group mb-2">
                                    <li class="list-group-item text-muted">No products yet.</li>
                                </ul>
                                <form action="/categories/subtype/${subtype.id}/delete" method="POST" class="mt-2 delete-subtype-form">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-danger w-100">Delete Subtype</button>
                                </form>
                            </div>
                        </div>
                    `;

                    tabContent.querySelector('.row')?.appendChild(div);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Subtype added!',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to add subtype', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', err.message, 'error');
        }
    });


    // Prevent deletion if subtype has products
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.matches('.delete-subtype-form')) {
            const card = form.closest('.card');
            const productCount = parseInt(card.getAttribute('data-product-count') || 0);

            if (productCount > 0) {
                e.preventDefault();
                Swal.fire('Warning', 'Cannot delete this subtype because it has products.', 'warning');
            }
        }
    });


}

// Initialize once
document.addEventListener('DOMContentLoaded', initAddSubtypeModal);
