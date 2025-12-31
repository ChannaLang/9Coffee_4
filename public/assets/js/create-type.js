// ===== CREATE TYPE MODALS =====

// ===== Add Product Type =====
const addTypeModalEl = document.getElementById('addTypeModal');
const addTypeModal = new bootstrap.Modal(addTypeModalEl);

document.getElementById('btnAddType').addEventListener('click', () => addTypeModal.show());

document.getElementById('add-type-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const formData = new FormData(this);

    try {
        const response = await fetch("/admin/categories/store-type", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if(data.success){
            addTypeModal.hide();
            this.reset();

            // Append new tab dynamically
            const tabs = document.getElementById('typeTabs');
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.setAttribute('role', 'presentation');
            li.innerHTML = `
                <button class="nav-link category-tab" id="type-tab-${data.type.id}"
                        data-bs-toggle="tab"
                        data-bs-target="#type-${data.type.id}"
                        type="button" role="tab">
                    <span class="tab-icon">🏷️</span>
                    <span class="tab-text">${data.type.name}</span>
                    <span class="tab-count">0</span>
                </button>
            `;
            tabs.appendChild(li);

            const content = document.getElementById('typeTabsContent');
            const div = document.createElement('div');
            div.className = 'tab-pane fade';
            div.id = `type-${data.type.id}`;
            div.setAttribute('role','tabpanel');
            div.innerHTML = `
                <div class="empty-state">
                    <span class="emoji large-emoji">📂</span>
                    <h3>No Subtypes Found</h3>
                    <p>Create your first subtype to organize products under ${data.type.name}</p>
                    <button class="btn-action btn-primary" onclick="document.getElementById('btnAddSubtype').click()">
                        <span class="emoji">➕</span>
                        Add First Subtype
                    </button>
                </div>
            `;
            content.appendChild(div);

            // Activate new tab
            const newTab = new bootstrap.Tab(document.getElementById(`type-tab-${data.type.id}`));
            newTab.show();

            // Reinitialize Lucide icons for new content
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            Swal.fire({
                icon: 'success',
                title: 'Product Type Created!',
                text: `${data.type.name} has been added successfully`,
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
                text: data.message || 'Failed to add type',
                background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                color: '#f5e6d3',
                confirmButtonColor: '#d4a373'
            });
        }

    } catch(err){
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

// ===== REMOVE TYPE =====
const removeTypeModalEl = document.getElementById('removeTypeModal');
const removeTypeModal = new bootstrap.Modal(removeTypeModalEl);

// Disable checkboxes for types that have subtypes
document.querySelectorAll('.checkbox-list input[type="checkbox"]').forEach(checkbox => {
    const typeId = checkbox.value;
    const tabContent = document.getElementById(`type-${typeId}`);
    const hasSubtypes = tabContent && tabContent.querySelectorAll('.subtype-card').length > 0;

    if(hasSubtypes) {
        checkbox.disabled = true;
        const checkboxItem = checkbox.closest('.checkbox-item');
        if(checkboxItem) {
            checkboxItem.classList.add('checkbox-disabled');
            checkboxItem.title = "Cannot delete because this type has subtypes";
        }
    }
});

document.getElementById('btnRemoveType').addEventListener('click', () => removeTypeModal.show());

document.getElementById('remove-type-form').addEventListener('submit', async function(e){
    e.preventDefault();

    const checkboxes = Array.from(document.querySelectorAll('.checkbox-list input[type="checkbox"]:checked'));
    if(checkboxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one type to remove',
            background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
            color: '#f5e6d3',
            confirmButtonColor: '#d4a373',
            iconColor: '#ffa726'
        });
        return;
    }

    // Confirm deletion
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete ${checkboxes.length} product type(s)!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel',
        background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
        color: '#f5e6d3',
        confirmButtonColor: '#ef5350',
        cancelButtonColor: '#6c757d',
        iconColor: '#ffa726'
    });

    if(!result.isConfirmed) return;

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let successCount = 0;
    let errorMessages = [];

    try {
        for (const checkbox of checkboxes) {
            const typeId = checkbox.value;
            const typeName = checkbox.closest('.checkbox-item').querySelector('.checkbox-label').textContent.trim();

            // Check if the type has subtypes
            const tabContent = document.getElementById(`type-${typeId}`);
            const hasSubtypes = tabContent && tabContent.querySelectorAll('.subtype-card').length > 0;

            if(hasSubtypes) {
                errorMessages.push(`${typeName} has subtypes`);
                continue; // Skip this type
            }

            // Proceed to delete
            const response = await fetch(`/admin/categories/delete-type/${typeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if(data.success){
                // Remove tab and content
                const tab = document.getElementById(`type-tab-${typeId}`);
                if(tab) tab.closest('.nav-item').remove();
                if(tabContent) tabContent.remove();

                // Remove checkbox item
                checkbox.closest('.checkbox-item').remove();

                successCount++;
            } else {
                errorMessages.push(`${typeName}: ${data.message || 'Unknown error'}`);
            }
        }

        removeTypeModal.hide();

        // Show results
        if(successCount > 0) {
            Swal.fire({
                icon: 'success',
                title: `${successCount} Type(s) Deleted!`,
                text: errorMessages.length > 0 ? `Some types couldn't be deleted: ${errorMessages.join(', ')}` : 'All selected types were removed successfully',
                background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                color: '#f5e6d3',
                confirmButtonColor: '#d4a373',
                iconColor: '#43a047'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Deletion Failed',
                text: errorMessages.join(', '),
                background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
                color: '#f5e6d3',
                confirmButtonColor: '#d4a373'
            });
        }

    } catch(err){
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'An unexpected error occurred',
            background: 'linear-gradient(135deg, #3e2723 0%, #2c1810 100%)',
            color: '#f5e6d3',
            confirmButtonColor: '#d4a373'
        });
    }
});
