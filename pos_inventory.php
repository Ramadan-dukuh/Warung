<?php
session_start();
include 'crudBarang.php';

// Handle form submission untuk simpan/update barang
if (isset($_POST['simpan'])) {
    $gambar = '';
    
    // Handle cropped image dari JavaScript
    if (isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $image_parts = explode(";base64,", $_POST['cropped_image']);
        if (count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
            $fileName = uniqid() . '.jpg';
            $targetFile = $targetDir . $fileName;
            
            if (file_put_contents($targetFile, $image_base64)) {
                $gambar = $fileName;
            }
        }
    }
    // Handle upload file biasa
    elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileInfo = pathinfo($_FILES['gambar']['name']);
        $fileName = uniqid() . '.' . $fileInfo['extension'];
        $targetFile = $targetDir . $fileName;
        
        // Validasi tipe file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileInfo['extension']), $allowedTypes)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
                $gambar = $fileName;
            }
        }
    }
    
    // Jika edit dan tidak ada gambar baru, gunakan gambar lama
    if ($_POST['kodeBarang'] != "" && empty($gambar)) {
        $barangLama = cariBarang($_POST['kodeBarang']);
        $gambar = $barangLama['gambar'] ?? '';
    }
    
    // Simpan atau update barang
    if ($_POST['kodeBarang'] == "") {
        // Tambah barang baru
        if (tambahBarangDenganGambar($_POST['namaBarang'], $_POST['hargaBarang'], $_POST['stok'], $_POST['idKategori'], $gambar)) {
            $_SESSION['success'] = "Barang berhasil ditambahkan";
        } else {
            $_SESSION['error'] = "Gagal menambahkan barang";
        }
    } else {
        // Edit barang
        if (editBarangDenganGambar($_POST['kodeBarang'], $_POST['namaBarang'], $_POST['hargaBarang'], $_POST['stok'], $_POST['idKategori'], $gambar)) {
            $_SESSION['success'] = "Barang berhasil diupdate";
        } else {
            $_SESSION['error'] = "Gagal mengupdate barang";
        }
    }
    
    header("Location: pos_inventory.php");
    exit();
}

// Handle hapus barang
if (isset($_GET['hapus'])) {
    $barang = cariBarang($_GET['hapus']);
    if ($barang && $barang['gambar'] && file_exists('uploads/' . $barang['gambar'])) {
        unlink('uploads/' . $barang['gambar']);
    }
    if (hapusBarang($_GET['hapus'])) {
        $_SESSION['success'] = "Barang berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus barang";
    }
    header("Location: pos_inventory.php");
    exit();
}

$cari = isset($_GET['cari']) ? $_GET['cari'] : '';
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $items_per_page;

$totalBarang = hitungTotalBarang($cari);
$total_pages = ceil($totalBarang / $items_per_page);
$dataBarang = bacaBarangPagination($cari, $offset, $items_per_page);
$dataKategori = bacaKategori();
$barangEdit = isset($_GET['edit']) ? cariBarang($_GET['edit']) : null;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="Logo Kidding.png" type="image/x-icon">
    <title>POS - Manajemen Inventaris</title>
    <style>
        /* CSS yang sama seperti sebelumnya */
          * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header Styles */
        .page-header {
            
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .page-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .page-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px 20px;            
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Product Grid Styles */
        .inventory-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            display: grid;
            grid-template-columns: 200px 1fr;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image-container {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        
        .product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .product-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .product-name {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: #333;
        }
        
        .product-price {
            color: #007bff;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .product-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .stock-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .stock-high {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }
        
        /* Search and Filter Styles */
        .search-filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            flex: 1;
            min-width: 200px;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            font-size: 1rem;
        }
        
        /* Image Upload Styles */
        .image-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        
        .image-upload-area:hover {
            border-color: #007bff;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
        }
        
        /* Camera Styles */
        .camera-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .camera-preview {
            width: 100%;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        #cameraVideo {
            width: 100%;
            display: none;
        }
        
        #cameraCanvas {
            width: 100%;
            display: none;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            position: absolute;
            max-height: 90vh;
            overflow: auto;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
        }
        
        .cropper-container {
            max-height: 70vh;
            overflow: auto;
        }
        
        .modal-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            position: sticky;
            bottom: 0;
            background: white;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }
        
        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }
        
        .pagination-item {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
        }
        
        .pagination-item:hover {
            background-color: #f8f9fa;
        }
        
        .pagination-item.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .pagination-item.disabled {
            color: #95a5a6;
            pointer-events: none;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .product-card {
                grid-template-columns: 1fr;
            }
            
            .product-image-container {
                height: 150px;
            }
            
            .page-header .container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-filter-bar {
                flex-direction: column;
            }
            
            .search-input, .filter-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">📦 Manajemen Inventaris</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">← Dashboard</a>
                <a href="pos_sale.php" class="btn btn-primary">Penjualan</a>
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

        <!-- Search and Filter Bar -->
        <div class="search-filter-bar">
            <input type="text" id="searchInput" placeholder="🔍 Cari produk..." class="search-input" style="flex: 1; min-width: 200px;">
            
            <select id="categoryFilter" class="filter-select">
                <option value="">Semua Kategori</option>
                <?php foreach ($dataKategori as $kat): ?>
                    <option value="<?= $kat['idKategori'] ?>"><?= $kat['namaKategori'] ?></option>
                <?php endforeach; ?>
            </select>
            
            <select id="stockFilter" class="filter-select">
                <option value="">Semua Stok</option>
                <option value="high">Stok Tinggi (>50)</option>
                <option value="medium">Stok Sedang (11-50)</option>
                <option value="low">Stok Rendah (≤10)</option>
            </select>
        </div>

        <div class="form-grid-2">
            <!-- Form Tambah/Edit Barang - DIGABUNG MENJADI SATU FORM -->
            <div class="card">
                <div class="card-header">
                    <?= $barangEdit ? 'Edit Produk' : 'Tambah Produk Baru' ?>
                </div>
                <div class="card-body">
                    <form method="post" action="" enctype="multipart/form-data" id="productForm">
                        <?php if($barangEdit): ?>
                            <input type="hidden" name="kodeBarang" value="<?= $barangEdit['kodeBarang'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="kodeBarang" value="">
                        <?php endif; ?>
                        
                        <!-- Input hidden untuk cropped image -->
                        <input type="hidden" name="cropped_image" id="croppedImageInput">
                        
                        <div class="form-group">
                            <label for="namaBarang" class="form-label">Nama Produk</label>
                            <input type="text" id="namaBarang" name="namaBarang" class="form-control" 
                                   placeholder="Masukkan nama produk" required 
                                   value="<?= $barangEdit['namaBarang'] ?? '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="hargaBarang" class="form-label">Harga Produk</label>
                            <input type="number" id="hargaBarang" name="hargaBarang" class="form-control" 
                                   placeholder="Masukkan harga produk" required
                                   value="<?= $barangEdit['hargaBarang'] ?? '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" id="stok" name="stok" class="form-control" 
                                   placeholder="Masukkan jumlah stok" required
                                   value="<?= $barangEdit['stok'] ?? '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="idKategori" class="form-label">Kategori</label>
                            <select id="idKategori" name="idKategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($dataKategori as $kat): ?>
                                    <option value="<?= $kat['idKategori'] ?>"
                                        <?= isset($barangEdit['idKategori']) && $barangEdit['idKategori'] == $kat['idKategori'] ? 'selected' : '' ?>>
                                        <?= $kat['namaKategori'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Upload Gambar Section di dalam form yang sama -->
                        <div class="form-group">
                            <label class="form-label">Gambar Produk</label>
                            <div class="image-upload-area" onclick="document.getElementById('gambar').click()">
                                <input type="file" id="gambar" name="gambar" accept="image/*" style="display: none;" onchange="openCropper(event)">
                                <div id="uploadText">
                                    <p>📸 Klik untuk upload gambar</p>
                                    <small>Format: JPG, PNG, GIF (Max 5MB)</small>
                                </div>
                                <img id="imagePreview" class="image-preview" style="display: none;">
                            </div>
                            
                            <div class="camera-options">
                                <button type="button" class="btn btn-secondary" onclick="startCamera()" style="flex: 1;">Buka Kamera</button>
                                <button type="button" class="btn btn-secondary" onclick="stopCamera()" style="flex: 1;" disabled id="stopCameraBtn">Tutup Kamera</button>
                            </div>
                            
                            <div class="camera-preview">
                                <video id="cameraVideo" autoplay playsinline></video>
                                <canvas id="cameraCanvas"></canvas>
                                <button type="button" class="btn btn-primary" onclick="capturePhoto()" style="width: 100%; margin-top: 10px; display: none;" id="captureBtn">Ambil Foto</button>
                            </div>                                                      
                        </div>
                        
                        <div class="btn-container">
                            <button type="submit" name="simpan" class="btn btn-primary">
                                <?= $barangEdit ? 'Update Produk' : 'Simpan Produk' ?>
                            </button>
                            <a href="pos_inventory.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="card">
                <div class="card-header">
                    Daftar Produk (<?= $totalBarang ?> produk)
                </div>
                <div class="card-body">
                    <div id="productGrid" class="inventory-grid">
                        <?php if (count($dataBarang) > 0): ?>
                            <?php foreach ($dataBarang as $barang): ?>
                                <div class="product-card" data-category="<?= $barang['idKategori'] ?>" data-stock="<?= $barang['stok'] ?>">
                                    <?php if (isset($barang['gambar']) && $barang['gambar']): ?>
                                        <img src="uploads/<?= $barang['gambar'] ?>" alt="<?= $barang['namaBarang'] ?>" class="product-image">
                                    <?php else: ?>
                                        <div class="no-image-placeholder">📦</div>
                                    <?php endif; ?>
                                    
                                    <div class="product-info">
                                        <div class="product-name"><?= $barang['namaBarang'] ?></div>
                                        <div class="product-price">Rp <?= number_format($barang['hargaBarang'], 0, ',', '.') ?></div>
                                        
                                        <div class="product-stock">
                                            <span>Stok:</span>
                                            <span class="stock-badge <?= $barang['stok'] <= 10 ? 'stock-low' : ($barang['stok'] <= 50 ? 'stock-medium' : 'stock-high') ?>">
                                                <?= $barang['stok'] ?> pcs
                                            </span>
                                        </div>
                                        
                                        <div class="product-actions">
                                            <a href="?edit=<?= $barang['kodeBarang'] ?>" class="btn btn-primary btn-small">Edit</a>
                                            <a href="?hapus=<?= $barang['kodeBarang'] ?>" class="btn btn-danger btn-small" 
                                               onclick="return confirm('Hapus produk ini?')">Hapus</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>Belum ada produk yang tersedia.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cropper -->
        <div id="cropperModal" class="modal">
            <div class="modal-content">
                <img id="imageToCrop" src="" alt="Image to crop">
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCropper()">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="cropImage()">Potong & Simpan</button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
            $prev_url = ($current_page > 1) ? '?page=' . ($current_page - 1) . (($cari) ? '&cari=' . urlencode($cari) : '') : '#';
            ?>
            <a href="<?= $prev_url ?>" class="pagination-item <?= $prev_disabled ?>">«</a>
            
            <?php
            $range = 2;
            $start_page = max(1, $current_page - $range);
            $end_page = min($total_pages, $current_page + $range);
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = ($i == $current_page) ? 'active' : '';
                echo '<a href="?page=' . $i . (($cari) ? '&cari=' . urlencode($cari) : '') . '" class="pagination-item ' . $active . '">' . $i . '</a>';
            }
            ?>
            
            <?php
            $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
            $next_url = ($current_page < $total_pages) ? '?page=' . ($current_page + 1) . (($cari) ? '&cari=' . urlencode($cari) : '') : '#';
            ?>
            <a href="<?= $next_url ?>" class="pagination-item <?= $next_disabled ?>">»</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Include Cropper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    
    <script>
        let cropper;
        let cameraStream;
        
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const stockFilter = document.getElementById('stockFilter');
            const productCards = document.querySelectorAll('.product-card');

            function filterProducts() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedCategory = categoryFilter.value;
                const selectedStock = stockFilter.value;

                productCards.forEach(card => {
                    const productName = card.querySelector('.product-name').textContent.toLowerCase();
                    const category = card.dataset.category;
                    const stock = parseInt(card.dataset.stock);

                    let showCard = true;

                    // Filter by search term
                    if (searchTerm && !productName.includes(searchTerm)) {
                        showCard = false;
                    }

                    // Filter by category
                    if (selectedCategory && category !== selectedCategory) {
                        showCard = false;
                    }

                    // Filter by stock
                    if (selectedStock === 'high' && stock <= 50) {
                        showCard = false;
                    } else if (selectedStock === 'medium' && (stock <= 10 || stock > 50)) {
                        showCard = false;
                    } else if (selectedStock === 'low' && stock > 10) {
                        showCard = false;
                    }

                    card.style.display = showCard ? 'block' : 'none';
                });
            }

            // Event listeners for filters
            searchInput.addEventListener('input', filterProducts);
            categoryFilter.addEventListener('change', filterProducts);
            stockFilter.addEventListener('change', filterProducts);

            // Initialize image preview for edit mode
            <?php if (isset($barangEdit['gambar']) && $barangEdit['gambar']): ?>
                document.getElementById('uploadText').style.display = 'none';
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('imagePreview').src = 'uploads/<?= $barangEdit['gambar'] ?>';
            <?php endif; ?>
        });
        
        // Fungsi untuk membuka cropper
        function openCropper(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const modal = document.getElementById('cropperModal');
                    const image = document.getElementById('imageToCrop');
                    
                    image.src = e.target.result;
                    modal.style.display = 'block';
                    
                    // Inisialisasi cropper setelah gambar dimuat
                    image.onload = function() {
                        if (cropper) {
                            cropper.destroy();
                        }
                        cropper = new Cropper(image, {
                            aspectRatio: 1, // Rasio 1:1
                            viewMode: 1,
                            autoCropArea: 0.8,
                            responsive: true,
                            guides: true
                        });
                    };
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Fungsi untuk menutup cropper
        function closeCropper() {
            document.getElementById('cropperModal').style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }
        
        // Fungsi untuk memotong gambar
        function cropImage() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 800,
                    height: 800,
                    fillColor: '#fff'
                });
                
                if (canvas) {
                    const croppedImage = canvas.toDataURL('image/jpeg');
                    document.getElementById('croppedImageInput').value = croppedImage;
                    closeCropper();
                    
                    // Tampilkan preview
                    const preview = document.getElementById('imagePreview');
                    preview.src = croppedImage;
                    preview.style.display = 'block';
                    document.getElementById('uploadText').style.display = 'none';
                }
            }
        }
        
        // Fungsi untuk membuka kamera
        function startCamera() {
            const video = document.getElementById('cameraVideo');
            const captureBtn = document.getElementById('captureBtn');
            const stopBtn = document.getElementById('stopCameraBtn');
            
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(function(stream) {
                    cameraStream = stream;
                    video.srcObject = stream;
                    video.style.display = 'block';
                    captureBtn.style.display = 'block';
                    stopBtn.disabled = false;
                })
                .catch(function(err) {
                    console.error("Error accessing camera: ", err);
                    alert("Tidak dapat mengakses kamera. Pastikan Anda memberikan izin.");
                });
        }
        
        // Fungsi untuk menghentikan kamera
        function stopCamera() {
            const video = document.getElementById('cameraVideo');
            const captureBtn = document.getElementById('captureBtn');
            const stopBtn = document.getElementById('stopCameraBtn');
            
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
                video.style.display = 'none';
                captureBtn.style.display = 'none';
                stopBtn.disabled = true;
            }
        }
        
        // Fungsi untuk mengambil foto dari kamera
        function capturePhoto() {
            const video = document.getElementById('cameraVideo');
            const canvas = document.getElementById('cameraCanvas');
            const context = canvas.getContext('2d');
            
            // Set canvas size sama dengan video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Gambar frame video ke canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Tampilkan canvas dan sembunyikan video
            canvas.style.display = 'block';
            video.style.display = 'none';
            
            // Buka cropper untuk foto yang diambil
            const imageData = canvas.toDataURL('image/png');
            const modal = document.getElementById('cropperModal');
            const image = document.getElementById('imageToCrop');
            
            image.src = imageData;
            modal.style.display = 'block';
            
            // Inisialisasi cropper setelah gambar dimuat
            image.onload = function() {
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.8,
                    responsive: true,
                    guides: true
                });
            };
            
            // Hentikan kamera setelah mengambil foto
            stopCamera();
        }
        
        // Event listener untuk menutup modal saat klik di luar
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('cropperModal');
            if (event.target === modal) {
                closeCropper();
            }
        });
</script>
</body>
</html>