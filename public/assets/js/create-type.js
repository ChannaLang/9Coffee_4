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
            li.innerHTML = `
                <button class="nav-link" id="type-tab-${data.type.id}"
                        data-bs-toggle="tab"
                        data-bs-target="#type-${data.type.id}"
                        type="button" role="tab">
                    ${data.type.name}
                </button>
            `;
            tabs.appendChild(li);

            const content = document.getElementById('typeTabsContent');
            const div = document.createElement('div');
            div.className = 'tab-pane fade';
            div.id = `type-${data.type.id}`;
            div.setAttribute('role','tabpanel');
            div.innerHTML = `<p>No subtypes yet.</p>`;
            content.appendChild(div);

            // Activate new tab
            const newTab = new bootstrap.Tab(document.getElementById(`type-tab-${data.type.id}`));
            newTab.show();

            Swal.fire({
                icon: 'success',
                title: 'Product Type added!',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to add type', 'error');
        }

    } catch(err){
        console.error(err);
        Swal.fire('Error', err.message || 'Error adding type', 'error');
    }
});

// ===== REMOVE TYPE =====
const removeTypeModalEl = document.getElementById('removeTypeModal');
const removeTypeModal = new bootstrap.Modal(removeTypeModalEl);



// Disable checkboxes for types that have subtypes
document.querySelectorAll('#typeCheckboxes input[type="checkbox"]').forEach(checkbox => {
    const typeId = checkbox.value;
    const tabContent = document.getElementById(`type-${typeId}`);
    const hasSubtypes = tabContent.querySelectorAll('.category-card').length > 0;

    if(hasSubtypes) {
        checkbox.disabled = true;
        checkbox.nextElementSibling.title = "Cannot delete because this type has subtypes";
        checkbox.nextElementSibling.style.opacity = 0.6;
    }
});

document.getElementById('btnRemoveType').addEventListener('click', () => removeTypeModal.show());

document.getElementById('btnRemoveType').addEventListener('click', () => removeTypeModal.show());

document.getElementById('remove-type-form').addEventListener('submit', async function(e){
    e.preventDefault();

    const checkboxes = Array.from(document.querySelectorAll('#typeCheckboxes input[type="checkbox"]:checked'));
    if(checkboxes.length === 0) {
        Swal.fire('Error', 'Please select at least one type to remove', 'warning');
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        for (const checkbox of checkboxes) {
            const typeId = checkbox.value;

            // Check if the type has subtypes
            const tabContent = document.getElementById(`type-${typeId}`);
            const hasSubtypes = tabContent.querySelectorAll('.category-card').length > 0;

            if(hasSubtypes) {
                Swal.fire('Warning', `Cannot delete type "${checkbox.nextElementSibling.textContent}" because it has subtypes.`, 'warning');
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
                tab?.parentNode.remove();
                tabContent?.remove();

                // Remove checkbox
                checkbox.parentNode.remove();
            } else {
                Swal.fire('Error', data.message || `Failed to remove type ${typeId}`, 'error');
            }
        }

        removeTypeModal.hide();
        Swal.fire({
            icon: 'success',
            title: 'Selected types removed!',
            timer: 1500,
            showConfirmButton: false
        });

    } catch(err){
        console.error(err);
        Swal.fire('Error', err.message || 'Error removing types', 'error');
    }
});

