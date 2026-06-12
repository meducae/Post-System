<?php
session_start();
require_once 'config.php';

// Simple authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Kassir - Savdo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <style>
        .product-card:hover { transform: translateY(-2px); }
        .cart-item { transition: all 0.3s ease; }
        .low-stock { background-color: #fef2f2; border-color: #fecaca; }
        .barcode-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5); }
        
        /* Keep barcode input always focused */
        #barcodeInput {
            background-color: #f8fafc;
            border-color: #3b82f6;
        }
        
        #barcodeInput:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
        @media print {
          body > * { display: none !important; }
          #printable-receipt { display: block !important; position: absolute; left: 0; top: 0; width: 80mm; min-width: 80mm; max-width: 80mm; font-size: 12px; background: #fff; color: #000; margin: 0; padding: 0; box-shadow: none; border: none; }
        }
        .no-print { display: none !important; }
        .hidden { display: none !important; }
        #receiptModal {
          position: fixed; left: 0; top: 0; width: 100vw; height: 100vh;
          z-index: 99999 !important; display: none; align-items: center; justify-content: center;
        }
        #receiptModal.active { display: flex !important; }
        .hidden { display: none !important; }
        .no-print { display: block; }
        @media print {
          body > * { display: none !important; }
          #printable-receipt { display: block !important; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">🛒 POS Kassir</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Kassir: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    <a href="admin.php" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg text-sm">Admin Panel</a>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm">Chiqish</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column - Product Search & Selection -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Product Search -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">🔍 Mahsulot Qidirish</h2>
                    
                    <!-- Barcode Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Shtrix-kod</label>
                        <input type="text" id="barcodeInput" class="barcode-input w-full px-4 py-3 border border-gray-300 rounded-lg text-lg font-mono" 
                               placeholder="Shtrix-kodni kiriting..." maxlength="13" autocomplete="off">
                        <p class="text-xs text-gray-500 mt-1">13 xonali shtrix-kodni kiriting</p>
                    </div>
                    
                    <!-- Product Name Search -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mahsulot Nomi</label>
                        <input type="text" id="productSearch" class="w-full px-4 py-3 border border-gray-300 rounded-lg" 
                               placeholder="Mahsulot nomini kiriting...">
                    </div>
                    
                    <!-- Search Results -->
                    <div id="searchResults" class="space-y-2 max-h-64 overflow-y-auto"></div>
                </div>
                
                <!-- Product List -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">📦 Mavjud Mahsulotlar</h2>
                    <div id="productList" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                </div>
            </div>
            
            <!-- Right Column - Cart & Checkout -->
            <div class="space-y-6">
                
                <!-- Shopping Cart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">🛒 Savatcha</h2>
                    
                    <div id="cartItems" class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                        <p class="text-gray-500 text-center py-8">Savatcha bo'sh</p>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="flex justify-between text-lg font-semibold mb-2">
                            <span>Jami:</span>
                            <span id="cartTotal">0 so'm</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mb-4">
                            <span>Foyda:</span>
                            <span id="cartProfit">0 so'm</span>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="mb-4 payment-method">
                            <label class="block text-sm font-medium text-gray-700 mb-2">To'lov Turi</label>
                            <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="cash">💵 Naqd Pul</option>
                                <option value="card">💳 Karta</option>
                            </select>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <button id="completeSale" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold">
                                ✅ Savdoni Yakunlash
                            </button>
                            <button id="clearCart" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg">
                                🗑️ Savatchani Tozalash
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">📊 Bugungi Ma'lumotlar</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Savdolar:</span>
                            <span id="todaySales" class="font-semibold">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jami Savdo:</span>
                            <span id="todayRevenue" class="font-semibold">0 so'm</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jami Foyda:</span>
                            <span id="todayProfit" class="font-semibold">0 so'm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chek chiqarish modal (always at the end of body) -->
    <div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center no-print hidden" style="z-index:99999;">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="text-center mb-4">
          <h3 class="text-lg font-semibold">🧾 KASSA CHEKI</h3>
          <p class="text-sm text-gray-600">Chekni chop etish yoki yuklab olish</p>
        </div>
        <div class="space-y-3">
          <button id="printReceipt" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
            🖨️ Chop etish
          </button>
          <button id="closeReceipt" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg">
            Yopish
          </button>
        </div>
      </div>
    </div>

    <!-- Professional Printable Receipt (hidden by default) -->
    <div id="printable-receipt" style="display:none;"></div>

    <script>
        // Global variables
        let cart = [];
        let currentSale = null;
        
        // Initialize page
        $(document).ready(function() {
            loadProducts();
            loadTodayStats();
            focusBarcodeInput();
            autoFocusBarcodeInput(); // Start auto-focusing
            
            // Event listeners
            $('#barcodeInput').on('keypress', handleBarcodeInput);
            $('#productSearch').on('input', handleProductSearch);
            $('#completeSale').on('click', completeSale);
            $('#clearCart').on('click', clearCart);
            // Print button handler
            $('#printReceipt').off('click').on('click', function() {
                if (!currentSale) {
                    showNotification('Chek chiqarish uchun savdo ma\'lumotlari topilmadi!', 'error');
                    return;
                }
                const cartData = currentSale.items.map(item => ({
                    name: item.name,
                    quantity: item.quantity,
                    total: (item.sell_price * item.quantity).toLocaleString()
                }));
                printReceipt(cartData, currentSale.total.toLocaleString(), currentSale.payment_type);
            });
            $('#closeReceipt').off('click').on('click', closeReceiptModal);
            
                    // Keep barcode input focused, but allow payment method selection
        $(document).on('click', function(e) {
            // If click is not on barcode input, payment method, or their containers, refocus
            if (!$(e.target).closest('#barcodeInput, .barcode-input, #paymentMethod, .payment-method').length) {
                setTimeout(focusBarcodeInput, 100);
            }
        });
            
            // Focus on window focus
            $(window).on('focus', function() {
                setTimeout(focusBarcodeInput, 100);
            });
            
            // Focus after any modal closes
            $(document).on('hidden.bs.modal', function() {
                setTimeout(focusBarcodeInput, 100);
            });
        });
        
        // Focus barcode input
        function focusBarcodeInput() {
            $('#barcodeInput').focus();
            // Ensure input is visible and scroll to it if needed
            $('#barcodeInput')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Auto-focus barcode input periodically, but not when payment method is focused
        function autoFocusBarcodeInput() {
            setInterval(function() {
                if (!$('#barcodeInput').is(':focus') && !$('#paymentMethod').is(':focus')) {
                    focusBarcodeInput();
                }
            }, 2000); // Check every 2 seconds
        }
        
        // Handle barcode input
        function handleBarcodeInput(e) {
            if (e.which === 13) { // Enter key
                const barcode = $(this).val().trim();
                if (barcode.length === 13) {
                    searchProductByBarcode(barcode);
                    $(this).val('');
                }
            }
        }
        
        // Search product by barcode
        function searchProductByBarcode(barcode) {
            $.get('api.php?action=product&barcode=' + barcode)
                .done(function(response) {
                    if (response.success && response.data) {
                        addToCart(response.data, 1);
                        showNotification('Mahsulot qo\'shildi: ' + response.data.name, 'success');
                    } else {
                        showNotification('Mahsulot topilmadi', 'error');
                    }
                })
                .fail(function() {
                    showNotification('Xatolik yuz berdi', 'error');
                });
        }
        
        // Handle product search
        function handleProductSearch() {
            const searchTerm = $(this).val().trim();
            if (searchTerm.length > 2) {
                $.get('api.php?action=product&name=' + encodeURIComponent(searchTerm))
                    .done(function(response) {
                        if (response.success && response.data) {
                            displaySearchResults([response.data]);
                        }
                    });
            } else {
                $('#searchResults').empty();
            }
        }
        
        // Display search results
        function displaySearchResults(products) {
            const container = $('#searchResults');
            container.empty();
            
            products.forEach(product => {
                const item = $(`
                    <div class="product-card p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 ${product.low_stock ? 'low-stock' : ''}">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-semibold">${product.name}</h4>
                                <p class="text-sm text-gray-600">${product.sell_price.toLocaleString()} so'm</p>
                                <p class="text-xs text-gray-500">Soni: ${product.quantity}</p>
                            </div>
                            <button class="add-to-cart-btn bg-blue-600 text-white px-3 py-1 rounded text-sm" 
                                    data-product='${JSON.stringify(product)}'>
                                + Qo'shish
                            </button>
                        </div>
                    </div>
                `);
                
                item.find('.add-to-cart-btn').on('click', function() {
                    const productData = $(this).data('product');
                    addToCart(productData, 1);
                });
                
                container.append(item);
            });
        }
        
        // Load products
        function loadProducts() {
            $.get('api.php?action=products')
                .done(function(response) {
                    if (response.success) {
                        displayProducts(response.data);
                    }
                });
        }
        
        // Display products
        function displayProducts(products) {
            const container = $('#productList');
            container.empty();
            
            products.forEach(product => {
                const item = $(`
                    <div class="product-card p-4 border border-gray-200 rounded-lg cursor-pointer hover:shadow-md ${product.low_stock ? 'low-stock' : ''}">
                        <h4 class="font-semibold mb-2">${product.name}</h4>
                        <p class="text-lg font-bold text-blue-600">${product.sell_price.toLocaleString()} so'm</p>
                        <p class="text-sm text-gray-600">Soni: ${product.quantity}</p>
                        ${product.low_stock ? '<p class="text-xs text-red-600 font-semibold">⚠️ Kam son</p>' : ''}
                        <button class="add-to-cart-btn w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">
                            + Savatchaga
                        </button>
                    </div>
                `);
                
                item.find('.add-to-cart-btn').on('click', function() {
                    addToCart(product, 1);
                });
                
                container.append(item);
            });
        }
        
        // Add to cart
        function addToCart(product, quantity = 1) {
            const existingItem = cart.find(item => item.id === product.id);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    ...product,
                    quantity: quantity
                });
            }
            
            updateCartDisplay();
            focusBarcodeInput(); // Focus after adding to cart
        }
        
        // Update cart display
        function updateCartDisplay() {
            const container = $('#cartItems');
            container.empty();
            
            if (cart.length === 0) {
                container.html('<p class="text-gray-500 text-center py-8">Savatcha bo\'sh</p>');
                $('#cartTotal').text('0 so\'m');
                $('#cartProfit').text('0 so\'m');
                return;
            }
            
            let total = 0;
            let profit = 0;
            
            cart.forEach((item, index) => {
                const itemTotal = item.sell_price * item.quantity;
                const itemProfit = (item.sell_price - item.buy_price) * item.quantity;
                total += itemTotal;
                profit += itemProfit;
                
                const cartItem = $(`
                    <div class="cart-item p-3 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <h4 class="font-semibold">${item.name}</h4>
                                <p class="text-sm text-gray-600">${item.sell_price.toLocaleString()} so'm x ${item.quantity}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">${itemTotal.toLocaleString()} so'm</p>
                                <div class="flex space-x-1 mt-1">
                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-xs" data-index="${index}">-</button>
                                    <span class="text-sm">${item.quantity}</span>
                                    <button class="quantity-btn bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded text-xs" data-index="${index}">+</button>
                                    <button class="remove-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs" data-index="${index}">×</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                cartItem.find('.quantity-btn').on('click', function() {
                    const idx = $(this).data('index');
                    const isIncrease = $(this).text() === '+';
                    
                    if (isIncrease) {
                        cart[idx].quantity++;
                    } else if (cart[idx].quantity > 1) {
                        cart[idx].quantity--;
                    }
                    
                    updateCartDisplay();
                });
                
                cartItem.find('.remove-btn').on('click', function() {
                    const idx = $(this).data('index');
                    cart.splice(idx, 1);
                    updateCartDisplay();
                });
                
                container.append(cartItem);
            });
            
            $('#cartTotal').text(total.toLocaleString() + ' so\'m');
            $('#cartProfit').text(profit.toLocaleString() + ' so\'m');
        }
        
        // Complete sale
        function completeSale() {
            if (cart.length === 0) {
                showNotification('Savatcha bo\'sh!', 'error');
                return;
            }
            
            const paymentType = $('#paymentMethod').val();
            const items = cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity
            }));
            
            $.ajax({
                url: 'api.php?action=sales',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    items: items,
                    payment_type: paymentType
                })
            })
            .done(function(response) {
                console.log('completeSale response:', response); // DIAGNOSTIC
                if (response.success) {
                    currentSale = {
                        id: response.sale_id,
                        items: cart,
                        payment_type: paymentType,
                        total: cart.reduce((sum, item) => sum + (item.sell_price * item.quantity), 0),
                        profit: cart.reduce((sum, item) => sum + ((item.sell_price - item.buy_price) * item.quantity), 0),
                        date: new Date()
                    };
                    
                    showReceiptModal();
                    cart = [];
                    updateCartDisplay();
                    loadProducts();
                    loadTodayStats();
                    showNotification('Savdo muvaffaqiyatli yakunlandi!', 'success');
                    focusBarcodeInput(); // Focus after completing sale
                } else {
                    showNotification('Xatolik: ' + response.error, 'error');
                }
            })
            .fail(function() {
                showNotification('Xatolik yuz berdi', 'error');
            });
        }
        
        // Clear cart
        function clearCart() {
            if (confirm('Savatchani tozalashni xohlaysizmi?')) {
                cart = [];
                updateCartDisplay();
                focusBarcodeInput();
            }
        }
        
        // Show receipt modal
        function showReceiptModal() {
            $('#receiptModal').removeClass('hidden').addClass('active');
        }
        
        // Close receipt modal
        function closeReceiptModal() {
            $('#receiptModal').addClass('hidden').removeClass('active');
            focusBarcodeInput(); // Focus after closing modal
        }
        
        // Simple printReceipt function for Chrome print dialog
        function printReceipt(cart, total, paymentType) {
          const now = new Date();
          const receipt = `
            <style>
              @media print {
                @page { size: 80mm auto; margin: 0; }
                body { margin: 0; }
              }
              .receipt80 {
                font-family: monospace;
                width: 80mm;
                max-width: 80mm;
                margin: 0 auto;
                font-size: 11px;
                background: #fff;
                color: #000;
              }
              .receipt80 th, .receipt80 td { font-size: 11px; }
              .receipt80 .center { text-align: center; }
              .receipt80 .bold { font-weight: bold; }
              .receipt80 .separator { border-top: 1px dashed #000; margin: 6px 0; }
              .receipt80 .item-row td { border-bottom: 1px dotted #ccc; padding: 2px 0; }
            </style>
            <div class="receipt80">
              <div class="center bold" style="font-size:16px;">POS SISTEMI</div>
              <div class="center" style="font-size:13px;">KASSA CHEKI</div>
              <div class="center">-----------------------------</div>
              <div>Chek raqami: <span class="bold">${(window.currentSale && window.currentSale.id) ? window.currentSale.id : '-'}</span></div>
              <div>Kassir: <span class="bold">${(window.currentSale && window.currentSale.cashier_name) ? window.currentSale.cashier_name : '-'}</span></div>
              <div>Sana/Vaqt: ${now.toLocaleDateString('uz-UZ')} ${now.toLocaleTimeString('uz-UZ')}</div>
              <div>To‘lov turi: <span class="bold">${paymentType.toUpperCase()}</span></div>
              <div class="separator"></div>
              <table style="width:100%;border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left;">Mahsulot</th>
                    <th style="text-align:right;">Soni</th>
                    <th style="text-align:right;">Jami</th>
                  </tr>
                </thead>
                <tbody>
                  ${cart.map(item => `
                    <tr class="item-row">
                      <td>${item.name}</td>
                      <td style="text-align:right;">${item.quantity}</td>
                      <td style="text-align:right;">${item.total} so‘m</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
              <div class="separator"></div>
              <div style="display:flex;justify-content:space-between;">
                <span>Jami summa:</span>
                <span class="bold">${total} so'm</span>
              </div>
              <div class="separator"></div>
              <div class="center" style="margin:8px 0;">(Barcod uchun joy)</div>
              <div class="center">-----------------------------</div>
              <div class="center" style="margin-top:8px;">Rahmat! Yana keling!</div>
            </div>
          `;
          const win = window.open('', 'PRINT', 'height=400,width=600');
          win.document.write(receipt);
          win.document.close();
          win.print();
        }
        
        // Download receipt
        function downloadReceipt() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add header
            doc.setFontSize(20);
            doc.text('POS SISTEMI', 105, 20, { align: 'center' });
            
            doc.setFontSize(12);
            doc.text('Chek №' + currentSale.id, 20, 40);
            doc.text('Sana: ' + currentSale.date.toLocaleDateString('uz-UZ'), 20, 50);
            doc.text('Vaqt: ' + currentSale.date.toLocaleTimeString('uz-UZ'), 20, 60);
            
            // Add items
            let y = 80;
            currentSale.items.forEach(item => {
                const itemTotal = item.sell_price * item.quantity;
                doc.text(item.name, 20, y);
                doc.text(item.quantity + ' x ' + item.sell_price.toLocaleString() + ' = ' + itemTotal.toLocaleString(), 20, y + 5);
                y += 15;
            });
            
            // Add totals
            doc.line(20, y, 190, y);
            y += 10;
            doc.setFontSize(14);
            doc.text('Jami: ' + currentSale.total.toLocaleString() + ' so\'m', 20, y);
            doc.text('To\'lov: ' + (currentSale.payment_type === 'cash' ? 'Naqd' : 'Karta'), 20, y + 10);
            
            doc.save('chek_' + currentSale.id + '.pdf');
        }
        
        // Generate receipt HTML
        function generateReceiptHTML() {
            const cashier = currentSale.cashier_name || '';
            return `
              <div style="width:80mm;max-width:80mm;margin:0 auto;font-family:monospace;">
                <div style="text-align:center; margin-bottom:8px;">
                  <div style="font-size:20px;font-weight:bold;">POS SISTEMI</div>
                  <div style="font-size:13px;">Chek №${currentSale.id}</div>
                  <div style="font-size:12px;">${currentSale.date.toLocaleDateString('uz-UZ')} ${currentSale.date.toLocaleTimeString('uz-UZ')}</div>
                  ${cashier ? `<div style='font-size:12px;'>Kassir: ${cashier}</div>` : ''}
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                  <thead>
                    <tr>
                      <th style="border-bottom:1px solid #000;text-align:left;">Mahsulot</th>
                      <th style="border-bottom:1px solid #000;text-align:right;">Soni</th>
                      <th style="border-bottom:1px solid #000;text-align:right;">Narxi</th>
                      <th style="border-bottom:1px solid #000;text-align:right;">Jami</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${currentSale.items.map(item => `
                      <tr>
                        <td style="padding:2px 0;">${item.name}</td>
                        <td style="text-align:right;">${item.quantity}</td>
                        <td style="text-align:right;">${item.sell_price.toLocaleString()}</td>
                        <td style="text-align:right;">${(item.sell_price * item.quantity).toLocaleString()}</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
                <div style="border-top:1px dashed #000;margin:8px 0 0 0;padding-top:6px;font-size:13px;">
                  <div style="display:flex;justify-content:space-between;">
                    <span><b>Jami:</b></span>
                    <span><b>${currentSale.total.toLocaleString()} so'm</b></span>
                  </div>
                  <div style="display:flex;justify-content:space-between;">
                    <span>To'lov:</span>
                    <span>${currentSale.payment_type === 'cash' ? 'Naqd' : 'Karta'}</span>
                  </div>
                </div>
                <div style="text-align:center;margin-top:10px;font-size:13px;">Rahmat! Xush kelibsiz!</div>
              </div>
            `;
        }
        
        // Load today's stats
        function loadTodayStats() {
            $.get('api.php?action=sales')
                .done(function(response) {
                    if (response.success) {
                        const today = new Date().toISOString().split('T')[0];
                        const todaySales = response.data.filter(sale => 
                            sale.created_at.startsWith(today)
                        );
                        
                        const totalSales = todaySales.length;
                        const totalRevenue = todaySales.reduce((sum, sale) => sum + parseFloat(sale.total_amount), 0);
                        const totalProfit = todaySales.reduce((sum, sale) => sum + parseFloat(sale.profit_amount), 0);
                        
                        $('#todaySales').text(totalSales);
                        $('#todayRevenue').text(totalRevenue.toLocaleString() + ' so\'m');
                        $('#todayProfit').text(totalProfit.toLocaleString() + ' so\'m');
                    }
                });
        }
        
        // Show notification
        function showNotification(message, type) {
            const notification = $(`
                <div class="fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white">
                    ${message}
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 3000);
        }

        // Update cart item quantity
        function updateCartItemQuantity(productId, newQuantity) {
            const item = cart.find(item => item.id === productId);
            if (item) {
                if (newQuantity <= 0) {
                    removeFromCart(productId);
                } else {
                    item.quantity = newQuantity;
                    updateCartDisplay();
                }
            }
            focusBarcodeInput(); // Focus after updating quantity
        }
        
        // Remove from cart
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCartDisplay();
            focusBarcodeInput(); // Focus after removing item
        }
    </script>
</body>
</html> 