document.addEventListener('DOMContentLoaded', function () {
    const staffSellSection = document.querySelector('.staff-sell-section');
    if (!staffSellSection) return;

    let cart = {};
    const EXCHANGE_RATE = 4100;

    const filterBtns = document.querySelectorAll('.filter-btn');
    const filterSubBtns = document.querySelectorAll('.filter-sub-btn');
    const filterNameBtns = document.querySelectorAll('.filter-name-btn');
    const productWrappers = document.querySelectorAll('.product-wrapper');
    const checkoutBtn = document.querySelector('#checkout');
    const walletEl = document.getElementById('wallet-balance');

    let selectedType = 'all', selectedSubType = 'all', selectedName = 'all';

    // ===== Helper: Toast =====
    function showToast(msg, icon = 'success') {
        Swal.fire({ title: msg, icon, timer: 1400, showConfirmButton: false, position: 'center' });
    }

    // ===== Product Filter =====
    function filterProducts() {
        productWrappers.forEach(wrapper => {
            const type = wrapper.dataset.type;
            const subtype = (wrapper.dataset.subtype || '').toLowerCase();
            const name = (wrapper.dataset.name || '').toLowerCase();

            const matchType = selectedType === 'all' || selectedType === type;
            const matchSub = selectedSubType === 'all' || subtype.includes(selectedSubType);
            const matchName = selectedName === 'all' || name.includes(selectedName);

            wrapper.style.display = (matchType && matchSub && matchName) ? 'block' : 'none';
        });
    }

    filterBtns.forEach(btn => btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedType = btn.dataset.type;
        filterProducts();
    }));

    filterSubBtns.forEach(btn => btn.addEventListener('click', () => {
        filterSubBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedSubType = btn.dataset.subtype.toLowerCase();
        filterProducts();
    }));

    filterNameBtns.forEach(btn => btn.addEventListener('click', () => {
        filterNameBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedName = btn.dataset.name.toLowerCase();
        filterProducts();
    }));

    filterProducts();

    // ===== Wallet =====
    function updateWalletBalance(amount = 0) {
        if (!walletEl) return;
        let current = parseFloat(walletEl.dataset.balance) || 0;
        current += amount;
        walletEl.dataset.balance = current.toFixed(2);
        walletEl.textContent = '$' + current.toFixed(2);
    }

    // ===== Add to Cart =====
staffSellSection.addEventListener('click', function (e) {
    const target = e.target;
    const card = target.closest('.product-card');

    // ===== Variant Toggle =====
    if(target.classList.contains('select-variant-btn') && card) {
        const variantGroup = card.querySelector('.variant-group');
        variantGroup.classList.toggle('d-none'); // show/hide
        return;
    }

   
    // ===== Variant Select =====
    if(target.classList.contains('variant-btn') && card) {
        // deselect other variants
        card.querySelectorAll('.variant-btn').forEach(btn => btn.classList.remove('active'));
        target.classList.add('active');

        const selectBtn = card.querySelector('.select-variant-btn');

        // set selected variant on card
        card.dataset.variant = target.dataset.variantName;   // <- use variant name
        card.dataset.variantPrice = target.dataset.variantPrice;
        card.dataset.available = target.dataset.available;

        // update button text to show chosen variant name
        selectBtn.textContent = target.dataset.variantName;

        // hide variants after selection
        target.parentElement.classList.add('d-none');
        return;
    }


    // ===== Existing Add to Cart =====
    const addBtn = target.closest('.btn-add-to-cart');
    if (!addBtn) return;
    // ... your existing Add to Cart logic ...
});

    // ===== Render Cart =====
    function renderCart() {
        const tbody = document.querySelector('#cart-table tbody');
        tbody.innerHTML = '';
        let total = 0;

        Object.values(cart).forEach(item => {
            const lineTotal = item.unit_price * item.quantity;
            total += lineTotal;

            tbody.innerHTML += `
                <tr data-key="${item.id}_${item.size}_${item.sugar}">
                    <td>${item.name}</td>
                    <td>${item.size}</td>
                    <td>${item.sugar}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-light qty-btn" data-action="decrease">-</button>
                        <span class="mx-1">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-light qty-btn" data-action="increase">+</button>
                    </td>
                    <td>$${lineTotal.toFixed(2)}</td>
                </tr>`;
        });

        if (Object.keys(cart).length > 0) {
            const totalKHR = total * EXCHANGE_RATE;
            tbody.innerHTML += `
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total (USD):</td>
                    <td class="fw-bold">$${total.toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end fw-bold text-warning">Total (KHR):</td>
                    <td class="fw-bold text-warning">៛${totalKHR.toLocaleString()}</td>
                </tr>`;
        }

        // ===== Qty Buttons =====
        tbody.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tr = btn.closest('tr');
                const key = tr.dataset.key;
                if (!cart[key]) return;
                const item = cart[key];
                const action = btn.dataset.action;

                if(action === 'increase') item.quantity++;
                else if(action === 'decrease') {
                    item.quantity--;
                    if(item.quantity <= 0) delete cart[key];
                }

                renderCart();
            });
        });
    }

    // ===== Checkout =====
    checkoutBtn.addEventListener('click', async function (e) {
        e.preventDefault();
        if (Object.keys(cart).length === 0) { showToast('Cart empty', 'error'); return; }

        const total = Object.values(cart).reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
        const totalKHR = total * EXCHANGE_RATE;
        const paymentMethod = document.querySelector('#payment_method')?.value || 'Cash';

        const result = await Swal.fire({
            title: '🧾 Checkout Confirmation',
            html: `<div style="max-height:300px;overflow-y:auto;text-align:left;">
                <table class="table table-sm">
                    <thead><tr><th>Product</th><th>Variant</th><th>Sugar</th><th>Qty</th><th>Price</th></tr></thead>
                    <tbody>
                        ${Object.values(cart).map(item => `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.size}</td>
                                <td>${item.sugar}</td>
                                <td>${item.quantity}</td>
                                <td>$${(item.unit_price*item.quantity).toFixed(2)}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
                <hr>
                <div class="text-end">
                    <p><strong>Total (USD):</strong> $${total.toFixed(2)}</p>
                    <p class="text-warning"><strong>Total (KHR):</strong> ៛${totalKHR.toLocaleString()}</p>
                </div>
            </div>`,
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: '🖨️ Print Invoice',
            denyButtonText: '💾 Confirm Checkout',
            cancelButtonText: '❌ Cancel',
            width: 700,
        });

        if(result.isConfirmed) printInvoice(cart, total, totalKHR, paymentMethod);
        else if(result.isDenied) {
            try {
                const response = await fetch(checkoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ cart_data: JSON.stringify(cart), payment_method: paymentMethod }),
                    credentials: 'same-origin'
                });
                const data = await response.json();
                if(data.success){
                    cart = {};
                    renderCart();
                    updateWalletBalance(data.total_amount);
                    showToast('Checkout successful!', 'success');
                } else showToast(data.message, 'error');
            } catch(err) {
                showToast('Checkout failed! ' + err.message, 'error');
            }
        } else showToast('Checkout canceled', 'info');
    });

    // ===== Print Invoice =====
    function printInvoice(cart, total, totalKHR, paymentMethod = 'Cash'){
        const invoiceNumber = `INV-${Date.now()}`;
        const dateTime = new Date().toLocaleString();
        let html = `
            <div class="p-4 text-dark">
                <div class="text-center mb-2">
                    <img src="${window.location.origin}/assets/images/menu-1.jpg" style="width:70px;height:70px;border-radius:50%;object-fit:cover;">
                    <h4 class="mt-2">☕ 9Nine Coffee ☕</h4>
                    <p>Tel: 012 345 678 | ផ្ទះលេខ 25 ផ្លូវព្រះនរោត្តម</p>
                    <hr>
                </div>
                <p><strong>Invoice #:</strong> ${invoiceNumber}</p>
                <p><strong>Date/Time:</strong> ${dateTime}</p>
                <table class="table table-bordered text-center mt-3">
                    <thead class="table-light"><tr><th>Product</th><th>Variant</th><th>Sugar</th><th>Qty</th><th>Price</th></tr></thead>
                    <tbody>
                        ${Object.values(cart).map(i => `<tr><td>${i.name}</td><td>${i.size}</td><td>${i.sugar}</td><td>${i.quantity}</td><td>$${(i.unit_price*i.quantity).toFixed(2)}</td></tr>`).join('')}
                        <tr class="fw-bold"><td colspan="4">Total (USD)</td><td>$${total.toFixed(2)}</td></tr>
                        <tr class="fw-bold text-warning"><td colspan="4">Total (KHR)</td><td>៛${totalKHR.toLocaleString()}</td></tr>
                    </tbody>
                </table>
                <div class="text-start mt-3"><strong>Payment Method:</strong> ${paymentMethod}</div>
                <div class="text-center mt-3"><button class="btn btn-primary" onclick="window.print()">Print</button></div>
                <div class="text-center mt-2 border-top pt-2"><small>អរគុណសម្រាប់ការទិញទំនិញ! ☕</small><br><small>Wi-Fi: ninecoffee168</small></div>
            </div>`;
        document.getElementById('receipt-content').innerHTML = html;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }
});
