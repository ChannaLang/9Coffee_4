document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('table tbody');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    console.log('All Orders page loaded ✅');

    // ---------- CHANGE STATUS ----------
    tableBody.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-edit-status');
        if (!btn) return;

        const orderId = btn.dataset.id;
        const currentStatus = btn.dataset.status;

        Swal.fire({
            title: 'Change Order Status',
            input: 'select',
            inputOptions: {
                'Pending': 'Pending',
                'Paid': 'Paid',
                'Cancelled': 'Cancelled'
            },
            inputValue: currentStatus,
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ff6b35',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if(result.isConfirmed) {
                const newStatus = result.value;

                fetch(`/admin/edit-orders/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({status: newStatus})
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            title: 'Updated!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#ff6b35'
                        });

                        // Update status badge in table
                        const row = btn.closest('tr');
                        const statusCell = row.querySelector('td:nth-child(6)');
                        const statusClass = newStatus.toLowerCase();

                        statusCell.innerHTML = `<span class="status-badge ${statusClass}">${newStatus}</span>`;
                        btn.dataset.status = newStatus;
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#ff6b35'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error updating status:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong!',
                        icon: 'error',
                        confirmButtonColor: '#ff6b35'
                    });
                });
            }
        });
    });

    // ---------- DELETE ORDER ----------
    tableBody.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete');
        if(!btn) return;

        e.preventDefault();
        const orderId = btn.dataset.id;
        const orderName = btn.dataset.name || 'this order';
        const orderPrice = btn.dataset.price || '0.00';

        Swal.fire({
            title: 'Are you sure?',
            html: `You are about to delete order for <strong>${orderName}</strong><br>Amount: <strong>$${orderPrice}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if(result.isConfirmed) {
                fetch(`/admin/delete-orders/${orderId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#ff6b35'
                        });

                        // Remove row from table with animation
                        const row = btn.closest('tr');
                        row.style.opacity = '0';
                        row.style.transition = 'opacity 0.3s';
                        setTimeout(() => row.remove(), 300);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#ff6b35'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error deleting order:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong!',
                        icon: 'error',
                        confirmButtonColor: '#ff6b35'
                    });
                });
            }
        });
    });

    // ---------- DELETE ALL ORDERS ----------
    const deleteAllBtn = document.querySelector('.delete-all');
    if(deleteAllBtn){
        deleteAllBtn.addEventListener('click', function(e){
            e.preventDefault();

            Swal.fire({
                title: 'Delete All Orders?',
                text: "This will permanently delete ALL orders. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete all!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed){
                    const form = deleteAllBtn.closest('form');
                    fetch(form.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success){
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#ff6b35'
                            }).then(() => {
                                // Reload page to update stats
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#ff6b35'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Error deleting all orders:', err);
                        Swal.fire({
                            title: 'Error',
                            text: 'Something went wrong!',
                            icon: 'error',
                            confirmButtonColor: '#ff6b35'
                        });
                    });
                }
            });
        });
    }
});
