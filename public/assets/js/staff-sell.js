    document.addEventListener('DOMContentLoaded', function () {
        const staffSellSection = document.querySelector('.staff-sell-section');
        if (!staffSellSection) return;

        let cart = {};
        const sizePriceMap = { S: 2.00, M: 2.25, L: 2.50 }; // USD prices per size
        const EXCHANGE_RATE = 4100; // 1 USD = 4100 KHR (modifiable)

        const filterBtns = document.querySelectorAll('.filter-btn');
        const filterSubBtns = document.querySelectorAll('.filter-sub-btn');
        const productWrappers = document.querySelectorAll('.product-wrapper');
        const checkoutBtn = document.querySelector('#checkout');
        const walletEl = document.getElementById('wallet-balance');
        let selectedType = 'all', selectedSubType = 'all';

        // ===== Toast Message =====
        function showToast(msg, icon = 'success') {
            Swal.fire({
                title: msg,
                icon,
                timer: undefined,
                showConfirmButton: false,
                position: 'center'
            });
        }

        // ===== Product Filter =====
        function filterProducts() {
            productWrappers.forEach(wrapper => {
                const type = wrapper.dataset.type;
                const subtype = wrapper.dataset.subtype.toLowerCase();
                wrapper.style.display =
                    (selectedType === 'all' || selectedType === type) &&
                    (selectedSubType === 'all' || subtype.includes(selectedSubType))
                        ? 'block'
                        : 'none';
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

        filterProducts();

        // ===== Wallet Update =====
        function updateWalletBalance(amount = 0) {
            if (!walletEl) return;
            let currentBalance = parseFloat(walletEl.dataset.balance) || 0;
            currentBalance += amount;
            walletEl.dataset.balance = currentBalance.toFixed(2);
            walletEl.textContent = '$' + currentBalance.toFixed(2);
        }

        // ===== Add/Remove Products =====
        staffSellSection.addEventListener('click', function (e) {
            const target = e.target;

            // --- Select Size ---
            if (target.classList.contains('size-btn')) {
                const group = target.closest('.btn-group');
                group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                target.classList.add('active');

                const card = target.closest('.product-card');
                const size = target.dataset.size;
                card.querySelector('.product-price').textContent = `$${sizePriceMap[size].toFixed(2)}`;
            }

            // --- Add to Cart ---
            const addBtn = target.closest('.btn-add-to-cart');
            if (addBtn) {
                const card = addBtn.closest('.product-card');
                const id = card.dataset.id;
                const name = card.dataset.name;

                // Get size from active button
                const sizeBtn = card.querySelector('.size-btn.active');
                const size = sizeBtn ? sizeBtn.dataset.size : null;

                // Get sugar from <select>
                const sugarSelect = card.querySelector('select');
                const sugar = sugarSelect ? sugarSelect.value : null;

                const qtyInput = parseInt(card.querySelector('.qty-input')?.value) || 1;

                if (!size || !sugar) {
                    showToast('Select size & sugar', 'error');
                    return;
                }

                const key = `${id}_${size}_${sugar}`;
                const unitPrice = sizePriceMap[size];
                const baseStock = parseInt(card.dataset.quantity) || 0;
                const currentInCart = cart[key] ? cart[key].quantity : 0;

                if (currentInCart + qtyInput > baseStock) {
                    showToast('Not enough stock', 'error');
                    return;
                }

                if (cart[key]) {
                    cart[key].quantity += qtyInput;
                } else {
                    cart[key] = { id, name, size, sugar, unit_price: unitPrice, quantity: qtyInput };
                }

                renderCart();
                updateStockUI(card);
                showToast(`${name} (${size}, ${sugar}) x${qtyInput} added!`);
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
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.size}</td>
                        <td>${item.sugar}</td>
                        <td>${item.quantity}</td>
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
        }

        // ===== Update Stock Display =====
        function updateStockUI(card) {
            const id = card.dataset.id;
            const baseStock = parseInt(card.dataset.quantity) || 0;
            const used = Object.values(cart).filter(i => i.id === id)
                .reduce((sum, i) => sum + i.quantity, 0);
            const span = card.querySelector('.available-stock');
            if (span) span.textContent = baseStock - used;
        }

        // ===== Checkout =====
      checkoutBtn.addEventListener('click', async function (e) {
    e.preventDefault();
    if (Object.keys(cart).length === 0) {
        showToast('Cart empty', 'error');
        return;
    }

    const total = Object.values(cart).reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
    const totalKHR = total * EXCHANGE_RATE;

    const result = await Swal.fire({
        title: '🧾 Checkout Confirmation',
        html: `<div style="max-height:300px;overflow-y:auto;text-align:left;">
            <table class="table table-sm">
                <thead>
                    <tr><th>Product</th><th>Size</th><th>Sugar</th><th>Qty</th><th>Price</th></tr>
                </thead>
                <tbody>
                    ${Object.values(cart).map(item => `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.size}</td>
                            <td>${item.sugar}</td>
                            <td>${item.quantity}</td>
                            <td>$${(item.unit_price * item.quantity).toFixed(2)}</td>
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
                if (result.isConfirmed) {
                    // 🖨️ Print invoice only
                    printInvoice(cart, total, totalKHR);
                } else if (result.isDenied) {
                    // 💾 Confirm Checkout
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch(checkoutUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    cart_data: JSON.stringify(cart),
                    payment_method: 'Cash'
                }),
                credentials: 'same-origin'  // 🔑 include auth session
            });

            const data = await response.json();

            if (data.success) {
                // Update stock UI
                Object.entries(data.updated_stock).forEach(([id, qty]) => {
                    const card = document.querySelector(`.product-card[data-id="${id}"]`);
                    if (card) card.querySelector('.available-stock').textContent = qty;
                });

                // Update wallet balance
                updateWalletBalance(data.total_amount);

                // Clear cart
                cart = {};
                renderCart();

                showToast('Checkout successful!', 'success');
            } else {
                showToast(data.message, 'error');
            }

        } catch (err) {
            showToast('Checkout failed! ' + err.message, 'error');
        }
    } else {
        showToast('Checkout canceled', 'info');
    }
});



    function printInvoice(cart, total, totalKHR, paymentMethod = 'Cash') {
        const invoiceNumber = `INV-${Date.now()}`;
        const cashier = 'Staff';
        const dateTime = new Date().toLocaleString();

        let html = `
            <div class="p-4">
                <div class="text-center mb-2">
                    <img src="${window.location.origin}/assets/images/menu-1.jpg"
                        style="width:70px;height:70px;border-radius:50%;object-fit:cover;">
                    <h4 class="mt-2">☕ 9Nine Coffee ☕</h4>
                    <p>Tel: 012 345 678 | ផ្ទះលេខ 25 ផ្លូវព្រះនរោត្តម</p>
                    <hr>
                </div>

                <div>
                    <p><strong>Invoice #:</strong> ${invoiceNumber}</p>
                    <p><strong>Cashier:</strong> ${cashier}</p>
                    <p><strong>Date/Time:</strong> ${dateTime}</p>
                </div>

                <table class="table table-bordered text-center mt-3">
                    <thead class="table-light">
                        <tr>z
                            <th>Product</th>
                            <th>Size</th>
                            <th>Sugar</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>`;

        Object.values(cart).forEach(item => {
            const lineTotal = item.unit_price * item.quantity;
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.size}</td>
                    <td>${item.sugar}</td>
                    <td>${item.quantity}</td>
                    <td>$${lineTotal.toFixed(2)}</td>
                </tr>`;
        });

        html += `
                    <tr class="fw-bold">
                        <td colspan="4">Total (USD)</td><td>$${total.toFixed(2)}</td>
                    </tr>
                    <tr class="fw-bold text-warning">
                        <td colspan="4">Total (KHR)</td><td>៛${totalKHR.toLocaleString()}</td>
                    </tr>
                </tbody>
                </table>

                <div class="text-start mt-3">
                    <strong>Payment Method:</strong> ${paymentMethod}
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-primary" onclick="window.print()">Print</button>
                </div>

                <div class="text-center mt-2 border-top pt-2">
                    <small>អរគុណសម្រាប់ការទិញទំនិញ! ☕</small><br>
                    <small>Wi-Fi: ninecoffee168</small>
                </div>
            </div>`;

        // Show inside modal
        document.getElementById('receipt-content').innerHTML = html;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }


    });
