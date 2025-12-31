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
                    // Check if there's an empty state and remove it
                    const emptyState = tabContent.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    // Check if subtypes grid exists, if not create it
                    let gridContainer = tabContent.querySelector('.subtypes-grid');
                    if (!gridContainer) {
                        gridContainer = document.createElement('div');
                        gridContainer.className = 'subtypes-grid';
                        tabContent.appendChild(gridContainer);
                    }

                    // Create new subtype card with clean HTML structure
                    const cardWrapper = document.createElement('div');
                    cardWrapper.innerHTML = `
                        <div class="subtype-card">
                            <div class="subtype-header">
                                <div class="subtype-title">
                                    <span class="emoji">🏷️</span>
                                    <h3>${subtype.name}</h3>
                                </div>
                                <div class="product-count-badge">
                                    <span class="emoji">📦</span>
                                    0
                                </div>
                            </div>

                            <div class="subtype-body">
                                <div class="products-list">
                                    <div class="empty-products">
                                        <span class="emoji empty-emoji">📭</span>
                                        <p>No products yet</p>
                                        <span class="empty-hint">Add products to this category</span>
                                    </div>
                                </div>

                                <form action="/admin/categories/delete-subtype/${subtype.id}" method="POST" class="delete-form">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn-delete-subtype">
                                        <span class="emoji">🗑️</span>
                                        Delete Category
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;

                    gridContainer.appendChild(cardWrapper.firstElementChild);

                    // Reinitialize Lucide icons for new content
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Subtype Created!',
                    text: `${data.subtype.name} has been added successfully`,
                    timer: 2000,
                    showConfirmButton: false,
                    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                    color: '#f5e6d3',
                    iconColor: '#43a047'
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
                text: err.message || 'Something went wrong. Please try again.',
                background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                color: '#f5e6d3',
                confirmButtonColor: '#d4a373'
            });
        }
    });

    // Prevent deletion if subtype has products
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.matches('.delete-form')) {
            e.preventDefault();

            // Get product count from badge
            const card = form.closest('.subtype-card');
            const countBadge = card.querySelector('.product-count-badge');
            const productCount = parseInt(countBadge.textContent.trim()) || 0;

            if (productCount > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Delete',
                    text: `This subtype has ${productCount} product(s). Please remove all products before deleting.`,
                    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                    color: '#f5e6d3',
                    confirmButtonColor: '#d4a373',
                    iconColor: '#ffa726'
                });
            } else {
                // Confirm deletion
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This subtype will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                    color: '#f5e6d3',
                    confirmButtonColor: '#ef5350',
                    cancelButtonColor: '#6c757d',
                    iconColor: '#ffa726'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        }
    });
}

// Initialize once
document.addEventListener('DOMContentLoaded', initAddSubtypeModal);
