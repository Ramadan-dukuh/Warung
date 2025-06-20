<?php
session_start();
include 'crudBarang.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add_to_cart':
            $kodeBarang = $_POST['kode'];
            $barang = cariBarang($kodeBarang);
            
            if ($barang && $barang['stok'] > 0) {
                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['kode'] == $kodeBarang) {
                        if ($item['jumlah'] < $barang['stok']) {
                            $item['jumlah']++;
                            $found = true;
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
                            exit;
                        }
                        break;
                    }
                }
                
                if (!$found) {
                    $_SESSION['cart'][] = [
                        'kode' => $barang['kodeBarang'],
                        'nama' => $barang['namaBarang'],
                        'harga' => $barang['hargaBarang'],
                        'jumlah' => 1,
                        'stok' => $barang['stok']
                    ];
                }
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Barang tidak ditemukan atau stok habis']);
            }
            exit;
            
        case 'update_cart':
            $index = $_POST['index'];
            $jumlah = max(0, intval($_POST['jumlah']));
            
            if ($jumlah == 0) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            } else {
                $barang = cariBarang($_SESSION['cart'][$index]['kode']);
                if ($jumlah <= $barang['stok']) {
                    $_SESSION['cart'][$index]['jumlah'] = $jumlah;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
                    exit;
                }
            }
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'remove_from_cart':
            $index = $_POST['index'];
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            echo json_encode(['success' => true]);
            exit;
            
        case 'clear_cart':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true]);
            exit;
            
        case 'get_cart':
            echo json_encode($_SESSION['cart']);
            exit;
    }
}
// Handle checkout
if (isset($_POST['checkout'])) {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['harga'] * $item['jumlah'];
    }
    
    $bayar = floatval($_POST['bayar']);
    $kembalian = $bayar - $total;
    
    if ($bayar >= $total && count($_SESSION['cart']) > 0) {
        $idTransaksi = simpanTransaksi($_SESSION['cart'], $total, $bayar, $kembalian);
        
        // Update session total penjualan hari ini
        $_SESSION['total_penjualan_hari'] = getTotalPenjualanHari();
        
        $_SESSION['last_transaction'] = [
            'id' => $idTransaksi,
            'items' => $_SESSION['cart'],
            'total' => $total,
            'bayar' => $bayar,
            'kembalian' => $kembalian
        ];
        
        $_SESSION['cart'] = [];
        $_SESSION['success'] = "Transaksi berhasil! ID: $idTransaksi";
    } else {
        $_SESSION['error'] = "Pembayaran tidak mencukupi atau keranjang kosong!";
    }
}

$dataBarang = bacaSemuaBarang();
$dataKategori = bacaKategori();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="Logo Kidding.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <title>POS - Sistem Penjualan</title>
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            min-height: calc(100vh - 120px);
        }
        
        .products-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .cart-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .product-item {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .product-item:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .product-price {
            color: #007bff;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .product-stock {
            font-size: 0.8rem;
            color: #666;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e6ed;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .item-price {
            color: #007bff;
            font-size: 0.8rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }
        
        .qty-btn {
            width: 25px;
            height: 25px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qty-input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 2px;
        }
        
        .cart-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e0e6ed;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-final {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007bff;
        }
        
        .checkout-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e0e6ed;
        }
        
        .payment-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e6ed;
            border-radius: 6px;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .change-display {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            border: 2px solid #e0e6ed;
            border-radius: 6px;
        }
        
        .filter-select {
            padding: 10px;
            border: 2px solid #e0e6ed;
            border-radius: 6px;
            background: white;
        }
        
        .no-image-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 2rem;
            border-radius: 8px;
            margin: 0 auto 10px;
        }
        
        @media (max-width: 1024px) {
            .pos-container {
                grid-template-columns: 1fr;
            }
            
            .cart-section {
                position: relative;
                top: auto;
            }
        }
        
        .receipt-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .receipt-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #ccc;
        }
        
        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .receipt-total {
            border-top: 2px dashed #ccc;
            margin-top: 15px;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">🛒 Sistem Penjualan POS</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">← Dashboard</a>
                <a href="pos_inventory.php" class="btn btn-primary">Inventaris</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: red; background: #f8d7da; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="color: green; background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="pos-container">
            <!-- Products Section -->
            <div class="products-section">
                <div class="search-filter">
                    <input type="text" id="searchProduct" class="search-input" placeholder="🔍 Cari produk...">
                    <select id="categoryFilter" class="filter-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($dataKategori as $kat): ?>
                            <option value="<?= $kat['idKategori'] ?>"><?= $kat['namaKategori'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="productsGrid" class="products-grid">
                    <?php foreach ($dataBarang as $barang): ?>
                        <div class="product-item" data-kode="<?= $barang['kodeBarang'] ?>" data-kategori="<?= $barang['idKategori'] ?>">
                            <?php if (isset($barang['gambar']) && $barang['gambar']): ?>
                                <img src="uploads/<?= $barang['gambar'] ?>" alt="<?= $barang['namaBarang'] ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image-placeholder">📦</div>
                            <?php endif; ?>
                            
                            <div class="product-name"><?= $barang['namaBarang'] ?></div>
                            <div class="product-price">Rp <?= number_format($barang['hargaBarang'], 0, ',', '.') ?></div>
                            <div class="product-stock">Stok: <?= $barang['stok'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="cart-section">
                <div class="cart-header">
                    <h3>🛒 Keranjang</h3>
                    <button onclick="clearCart()" class="btn btn-danger btn-small">Kosongkan</button>
                </div>

                <div id="cartItems">
                    <!-- Cart items will be loaded here -->
                </div>

                <div class="cart-total">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">Rp 0</span>
                    </div>
                    <div class="total-row total-final">
                        <span>Total:</span>
                        <span id="total">Rp 0</span>
                    </div>
                </div>

                <div class="checkout-section">
                    <form method="post" onsubmit="return validateCheckout()">
                        <label class="form-label">Jumlah Bayar:</label>
                        <input type="number" name="bayar" id="bayar" class="payment-input" 
                               placeholder="Masukkan jumlah bayar" required min="0" step="0.01"
                               oninput="calculateChange()">
                        
                        <div id="changeDisplay" class="change-display" style="display: none;">
                            <strong>Kembalian: <span id="kembalian">Rp 0</span></strong>
                        </div>
                        
                        <button type="submit" name="checkout" class="btn btn-success" style="width: 100%;">
                            💳 Proses Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="receipt-modal">
        <div class="receipt-content">
            <div class="receipt-header">
                <h3>📄 STRUK PEMBAYARAN</h3>
                <p>Toko ABC</p>
                <small><?= date('d/m/Y H:i:s') ?></small>
            </div>
            
            <div id="receiptItems">
                <!-- Receipt items will be loaded here -->
            </div>
            
            <div class="receipt-total">
                <div class="receipt-item">
                    <strong>Total: </strong>
                    <strong id="receiptTotal">Rp 0</strong>
                </div>
                <div class="receipt-item">
                    <span>Bayar: </span>
                    <span id="receiptBayar">Rp 0</span>
                </div>
                <div class="receipt-item">
                    <span>Kembalian: </span>
                    <span id="receiptKembalian">Rp 0</span>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="printReceipt()" class="btn btn-primary">🖨️ Print</button>
                <button onclick="closeReceipt()" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // Load cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();
            
            // Add event listeners for product items
            document.querySelectorAll('.product-item').forEach(item => {
                item.addEventListener('click', function() {
                    const kode = this.dataset.kode;
                    addToCart(kode);
                });
            });
            
            // Search functionality
            document.getElementById('searchProduct').addEventListener('input', filterProducts);
            document.getElementById('categoryFilter').addEventListener('change', filterProducts);
            
            // Show receipt if last transaction exists
            <?php if (isset($_SESSION['last_transaction'])): ?>
                showReceipt(<?= json_encode($_SESSION['last_transaction']) ?>);
                <?php unset($_SESSION['last_transaction']); ?>
            <?php endif;
                    ?>
    });

    // Filter products based on search and category
    function filterProducts() {
        const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        
        document.querySelectorAll('.product-item').forEach(item => {
            const name = item.querySelector('.product-name').textContent.toLowerCase();
            const itemCategory = item.dataset.kategori;
            
            let show = true;
            
            // Filter by search term
            if (searchTerm && !name.includes(searchTerm)) {
                show = false;
            }
            
            // Filter by category
            if (category && itemCategory !== category) {
                show = false;
            }
            
            item.style.display = show ? 'block' : 'none';
        });
    }

    // Add item to cart via AJAX
    function addToCart(kode) {
        fetch('pos_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add_to_cart&kode=' + kode
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
        });
    }

    // Load cart items and calculate totals
    function loadCart() {
        fetch('pos_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_cart'
        })
        .then(response => response.json())
        .then(data => {
            const cartItems = document.getElementById('cartItems');
            cartItems.innerHTML = '';
            
            let subtotal = 0;
            
            if (data.length === 0) {
                cartItems.innerHTML = '<p>Keranjang kosong</p>';
            } else {
                data.forEach((item, index) => {
                    const itemTotal = item.harga * item.jumlah;
                    subtotal += itemTotal;
                    
                    const itemElement = document.createElement('div');
                    itemElement.className = 'cart-item';
                    itemElement.innerHTML = `
                        <div class="item-info">
                            <div class="item-name">${item.nama}</div>
                            <div class="item-price">Rp ${item.harga.toLocaleString('id-ID')} x ${item.jumlah}</div>
                            <div class="quantity-controls">
                                <button class="qty-btn" onclick="updateQuantity(${index}, ${item.jumlah - 1})">-</button>
                                <input type="number" class="qty-input" value="${item.jumlah}" min="1" max="${item.stok}" 
                                    onchange="updateQuantity(${index}, this.value)">
                                <button class="qty-btn" onclick="updateQuantity(${index}, ${item.jumlah + 1})">+</button>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-danger btn-small" onclick="removeFromCart(${index})">×</button>
                        </div>
                    `;
                    
                    cartItems.appendChild(itemElement);
                });
            }
            
            document.getElementById('subtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('total').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            
            // Update payment input min value
            document.getElementById('bayar').min = subtotal;
        });
    }

    // Update item quantity
    function updateQuantity(index, newQuantity) {
        fetch('pos_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_cart&index=${index}&jumlah=${newQuantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
                calculateChange();
            } else {
                alert(data.message || 'Gagal mengupdate keranjang');
            }
        });
    }

    // Remove item from cart
    function removeFromCart(index) {
        fetch('pos_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove_from_cart&index=${index}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
                calculateChange();
            }
        });
    }

    // Clear entire cart
    function clearCart() {
        if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
            fetch('pos_sale.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=clear_cart'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCart();
                    calculateChange();
                }
            });
        }
    }

    // Calculate change based on payment amount
    function calculateChange() {
        const totalText = document.getElementById('total').textContent;
        const total = parseInt(totalText.replace(/[^\d]/g, ''));
        const bayar = parseFloat(document.getElementById('bayar').value) || 0;
        
        const changeDisplay = document.getElementById('changeDisplay');
        const kembalian = document.getElementById('kembalian');
        
        if (bayar > 0) {
            const change = bayar - total;
            kembalian.textContent = `Rp ${Math.max(0, change).toLocaleString('id-ID')}`;
            changeDisplay.style.display = 'block';
        } else {
            changeDisplay.style.display = 'none';
        }
    }

    // Validate checkout
    function validateCheckout() {
        const totalText = document.getElementById('total').textContent;
        const total = parseInt(totalText.replace(/[^\d]/g, ''));
        const bayar = parseFloat(document.getElementById('bayar').value) || 0;
        
        if (total === 0) {
            alert('Keranjang belanja kosong!');
            return false;
        }
        
        if (bayar < total) {
            alert('Jumlah pembayaran kurang!');
            return false;
        }
        
        return true;
    }

    // Show receipt modal
    function showReceipt(transaction) {
        const modal = document.getElementById('receiptModal');
        const receiptItems = document.getElementById('receiptItems');
        
        receiptItems.innerHTML = '';
        
        // Add items to receipt
        transaction.items.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'receipt-item';
            itemElement.innerHTML = `
                <span>${item.nama} (${item.jumlah}x)</span>
                <span>Rp ${(item.harga * item.jumlah).toLocaleString('id-ID')}</span>
            `;
            receiptItems.appendChild(itemElement);
        });
        
        // Set totals
        document.getElementById('receiptTotal').textContent = `Rp ${transaction.total.toLocaleString('id-ID')}`;
        document.getElementById('receiptBayar').textContent = `Rp ${transaction.bayar.toLocaleString('id-ID')}`;
        document.getElementById('receiptKembalian').textContent = `Rp ${transaction.kembalian.toLocaleString('id-ID')}`;
        
        // Show modal
        modal.style.display = 'block';
    }

    // Close receipt modal
    function closeReceipt() {
        document.getElementById('receiptModal').style.display = 'none';
    }

    // Print receipt
    function printReceipt() {
        const receiptContent = document.querySelector('.receipt-content').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .receipt-item { display: flex; justify-content: space-between; margin-bottom: 8px; }
                .receipt-total { border-top: 2px dashed #ccc; margin-top: 15px; padding-top: 15px; }
                @media print {
                    button { display: none; }
                }
            </style>
            ${receiptContent}
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        loadCart();
    }
</script>
</body>
</html>