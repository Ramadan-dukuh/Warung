<?php
session_start();
include 'crudBarang.php';

// Get products for dropdown selection
$dataBarang = bacaSemuaBarang();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_POST['add_to_cart'])) {
    $kodeBarang = $_POST['kodeBarang'];
    $quantity = (int)$_POST['quantity'];
    
    // Find the selected product
    $product = cariBarang($kodeBarang);
    
    if ($product && $quantity > 0) {
        // Check if product is already in cart, update quantity if so
        $found = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['kodeBarang'] == $kodeBarang) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // If not found, add as new item
        if (!$found) {
            $_SESSION['cart'][] = [
                'kodeBarang' => $product['kodeBarang'],
                'namaBarang' => $product['namaBarang'],
                'hargaBarang' => $product['hargaBarang'],
                'quantity' => $quantity
            ];
        }
        
        $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang";
    } else {
        $_SESSION['error'] = "Gagal menambahkan produk ke keranjang";
    }
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
        $_SESSION['success'] = "Item berhasil dihapus dari keranjang";
    }
}

// Clear cart
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['success'] = "Keranjang berhasil dikosongkan";
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['hargaBarang'] * $item['quantity'];
}

// Process payment
$change = 0;
$payment = 0;
if (isset($_POST['process_payment'])) {
    $payment = (int)$_POST['payment_amount'];
    
    if ($payment < $total) {
        $_SESSION['error'] = "Pembayaran kurang dari total belanja";
    } else {
        $change = $payment - $total;
        $_SESSION['success'] = "Pembayaran berhasil! Kembalian: Rp " . number_format($change, 0, ',', '.');
        
        // Here you could add code to save the transaction to database
        // and update inventory stock
        
        // Clear cart after successful payment
        if (isset($_POST['clear_after_payment'])) {
            $_SESSION['cart'] = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern POS System</title>
    <link rel="stylesheet" href="test.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">POS System</div>
                <nav class="nav-links">
                    <a href="#" class="nav-link active">Kasir</a>
                    <a href="#" class="nav-link">Inventory</a>
                    <a href="#" class="nav-link">Laporan</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div id="notification-container"></div>

            <div class="search-container">
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Cari barang..." 
                    class="search-input"
                >
            </div>

            <div class="pos-grid">
                <!-- Product Selection -->
                <div class="card">
                    <div class="card-header">
                        Katalog Produk
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="product-table" id="productTable">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody">
                                    <?php if (count($dataBarang) > 0): ?>
                    <table class="data-table">
                       <div id="barangTableContainer">
    <!-- Data barang akan dimuat otomatis di sini -->
</div>

                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <p>Tidak ada data barang yang tersedia.</p>
                        <p>Silakan tambahkan barang baru atau ubah kata kunci pencarian.</p>
                    </div>
                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div id="pagination" class="pagination">
                            <!-- Pagination will be generated here -->
                        </div>
                    </div>
                </div>
                
                <!-- Shopping Cart -->
                <div class="card">
                    <div class="card-header">
                        Keranjang Belanja
                    </div>
                    <div class="card-body">
                        <div id="cartItems">
                            <!-- Cart items will be displayed here -->
                            <div class="empty-state" id="emptyCart">
                                <p>Keranjang belanja kosong.</p>
                                <p>Pilih produk dari katalog untuk menambahkannya ke keranjang.</p>
                            </div>
                        </div>
                        
                        <div class="cart-summary" id="cartSummary" style="display: none;">
                            <div class="cart-total">
                                <span>Total</span>
                                <span id="cartTotal">Rp 0</span>
                            </div>
                        </div>
                        
                        <form id="paymentForm" style="margin-top: 20px; display: none;">
                            <div class="form-group">
                                <label for="paymentAmount" class="form-label">Jumlah Pembayaran</label>
                                <input type="number" id="paymentAmount" class="form-control" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-container">
                                    <input type="checkbox" id="clearAfterPayment" checked>
                                    <label for="clearAfterPayment">Kosongkan keranjang setelah pembayaran</label>
                                </div>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" id="processPayment" class="btn btn-success">Proses Pembayaran</button>
                                <button type="button" id="clearCart" class="btn btn-danger">Kosongkan Keranjang</button>
                            </div>
                        </form>
                        
                        <div class="payment-result" id="paymentResult" style="display: none;">
                            <div>
                                <div class="payment-label">Pembayaran</div>
                                <div class="payment-amount" id="paymentDisplay">Rp 0</div>
                            </div>
                            <div>
                                <div class="payment-label">Kembalian</div>
                                <div class="payment-amount" id="changeDisplay">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Add Product Modal -->
            <div class="card">
                <div class="card-header">
                    Tambah Ke Keranjang
                </div>
                <div class="card-body">
                    <form id="quickAddForm">
                        <div class="form-group">
                            <label for="productSelect" class="form-label">Produk</label>
                            <select id="productSelect" class="form-control" required>
                                <option value="">-- Pilih Produk --</option>
                                <!-- Product options will be loaded here -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantityInput" class="form-label">Jumlah</label>
                            <input type="number" id="quantityInput" class="form-control" min="1" value="1" required>
                            <span id="stockInfo" class="stock-info"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="itemPrice" class="form-label">Harga Satuan</label>
                            <input type="text" id="itemPrice" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="subtotalDisplay" class="form-label">Subtotal</label>
                            <input type="text" id="subtotalDisplay" class="form-control" readonly>
                        </div>
                        
                        <button type="submit" id="addToCartBtn" class="btn btn-primary">Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <a href="#" class="back-to-top" id="backToTop">↑</a>

    <script src="test.js"></script>
</body>
</html>