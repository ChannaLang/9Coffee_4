    document.addEventListener('DOMContentLoaded', function () {
        // Show sugar for drinks initially
    document.querySelectorAll('.product-card').forEach(card => {
        const sugarSelect = card.querySelector('select.sugar-select');
        const productType = card.dataset.type?.toLowerCase() || 'food';
        if(sugarSelect) {
            if(productType === 'drink') sugarSelect.classList.remove('d-none');
            else sugarSelect.classList.add('d-none');
        }
    });

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
            Swal.fire({ title: msg, icon, timer: 10000000000, showConfirmButton: false, position: 'center' });
        }
        // ===== Product Filter =====


        function filterProducts() {
            document.querySelectorAll('.product-wrapper').forEach(wrapper => {
                const type = wrapper.dataset.type.toLowerCase();
                const subtype = wrapper.dataset.subtype.toLowerCase();

                const typeMatch = selectedType === 'all' || type === selectedType;
                const subMatch = selectedSubType === 'all' || subtype === selectedSubType;

                wrapper.style.display = (typeMatch && subMatch) ? 'block' : 'none';
            });
        }

        // Type filter buttons
        document.querySelectorAll('.filter-type-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-type-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedType = btn.dataset.type.toLowerCase();
                filterProducts();
            });
        });

        // SubType filter buttons
        document.querySelectorAll('.filter-sub-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-sub-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedSubType = btn.dataset.subtype.toLowerCase();
                filterProducts();
            });
        });


        // Optional: Name Buttons
        filterNameBtns.forEach(btn => btn.addEventListener('click', () => {
            filterNameBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            selectedName = btn.dataset.name || 'all';
            filterProducts();
        }));

        // ===== Wallet =====
        function updateWalletBalance(amount = 0) {
            if (!walletEl) return;
            let current = parseFloat(walletEl.dataset.balance) || 0;
            current += amount;
            walletEl.dataset.balance = current.toFixed(2);
            walletEl.textContent = '$' + current.toFixed(2);
        }
// ===== Add to Cart / Variant Select =====
staffSellSection.addEventListener('click', function (e) {
    const target = e.target;
    const card = target.closest('.product-card');
    if (!card) return;

    // ----- Variant Select (all variants visible instantly) -----
    if (target.classList.contains('variant-btn')) {
        // Remove 'active' from other variants in the card
        card.querySelectorAll('.variant-btn').forEach(btn => btn.classList.remove('active'));
        target.classList.add('active');

        // Set selected variant info directly on the card
        card.dataset.variantId = target.dataset.variantId;
        card.dataset.variant = target.dataset.variantName;
        card.dataset.variantPrice = target.dataset.variantPrice;
        card.dataset.available = target.dataset.available;

        // ===== Update displayed price instantly =====
        const priceDiv = card.querySelector('.product-price');
        if (priceDiv) {
            priceDiv.textContent = `$${Number(target.dataset.variantPrice).toFixed(2)}`;
        }

        // ===== Show sugar only for Drinks =====
        const sugarWrapper = card.querySelector('.sugar-wrapper');
        const productType = card.dataset.type.toLowerCase();

        if (sugarWrapper) {
            if (productType === 'drink') sugarWrapper.classList.remove('d-none');
            else sugarWrapper.classList.add('d-none');
        }
        return; // stop further processing
    }

    // ----- Add to Cart -----
    if (target.closest('.btn-add-to-cart')) {
        const id = card.dataset.variantId;
        const name = card.dataset.name;
        const variant = card.dataset.variant;
        const unit_price = parseFloat(card.dataset.variantPrice);

        if (!variant) {
            showToast('Please select a variant first', 'error');
            return;
        }

        // Include sugar only for drinks
        const productType = card.dataset.type.toLowerCase();
        let sugar = null;
        if (productType === 'drink') {
            const sugarSelect = card.querySelector('select.sugar-select');
            sugar = sugarSelect ? sugarSelect.value : null;
        }

        const key = `${id}_${variant}_${sugar}`;
        if (cart[key]) cart[key].quantity++;
        else cart[key] = { id, name, variant, sugar, unit_price, quantity: 1 };

        renderCart();
    }
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
                    <tr data-key="${item.id}_${item.variant}_${item.sugar}">
                        <td>${item.name}</td>
                        <td>${item.variant}</td>
                        <td>${item.sugar || 'None'}</td>
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
                        <thead>
                        <tr>
                            <th>Product</th>
                            <th>Option</th>
                            <th>Sugar</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr></thead>
                        <tbody>
                            ${Object.values(cart).map(item => `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.variant}</td>
                                    <td>${item.sugar || 'None'}</td>
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
                        <thead class="table-light"><tr>
                        <th>Product</th>
                        <th>Option</th>
                        <th>Sugar</th>
                        <th>Qty</th>
                        <th>Price</th>
                        </tr></thead>
                        <tbody>
                            ${Object.values(cart).map(i => `<tr>
                                <td>${i.name}</td>
                                <td>${i.variant}</td>
                                <td>${i.sugar || 'None'}</td>
                                <td>${i.quantity}</td>
                                <td>$${(i.unit_price*i.quantity).toFixed(2)}</td></tr>`).join('')}
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
