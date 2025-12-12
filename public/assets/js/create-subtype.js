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
                    // Check if there's a "No subtypes yet" message and remove it
                    const emptyMessage = tabContent.querySelector('p');
                    if (emptyMessage) {
                        emptyMessage.remove();
                    }

                    // Check if row exists, if not create it
                    let rowContainer = tabContent.querySelector('.row');
                    if (!rowContainer) {
                        rowContainer = document.createElement('div');
                        rowContainer.className = 'row';
                        tabContent.appendChild(rowContainer);
                    }

                    // Create new subtype card with updated HTML structure
                    const div = document.createElement('div');
                    div.className = 'col-xxl-2 col-xl-3 col-lg-4 col-md-6 mb-4';
                    div.innerHTML = `
                        <div class="card category-card" data-product-count="0">
                            <div class="card-header">
                                <i class="lucide-tag" style="width: 20px; height: 20px; vertical-align: middle;"></i>
                                ${subtype.name}
                            </div>
                            <div class="card-body">
                                <ul class="list-group mb-2">
                                    <li class="list-group-item text-muted">
                                        <i class="lucide-package-x" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                                        No products yet.
                                    </li>
                                </ul>
                                <form action="/admin/categories/delete-subtype/${subtype.id}" method="POST" class="mt-2 delete-subtype-form">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-danger w-100">
                                        <i class="lucide-trash-2" style="width: 16px; height: 16px;"></i>
                                        Delete Subtype
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;

                    rowContainer.appendChild(div);

                    // Reinitialize Lucide icons for new content
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Subtype added!',
                    timer: 1500,
                    showConfirmButton: false,
                    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                    color: '#f5e6d3',
                    confirmButtonColor: '#d4a373'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to add subtype',
                    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                    color: '#f5e6d3',
                    confirmButtonColor: '#d4a373'
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message,
                background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                color: '#f5e6d3',
                confirmButtonColor: '#d4a373'
            });
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Delete',
                    text: 'Cannot delete this subtype because it has products.',
                    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                    color: '#f5e6d3',
                    confirmButtonColor: '#d4a373'
                });
            }
        }
    });
}

// Initialize once
document.addEventListener('DOMContentLoaded', initAddSubtypeModal);
