<?php
session_start();
include 'crudBarang.php';

// Handle kategori
if (isset($_GET['hapus'])) {
    hapusKategori($_GET['hapus']);
    $_SESSION['success'] = "Kategori berhasil dihapus";
}

if (isset($_POST['simpan'])) {
    if ($_POST['idKategori'] == "") {
        tambahKategori($_POST['namaKategori']);
        $_SESSION['success'] = "Kategori berhasil ditambahkan";
    } else {
        editKategori($_POST['idKategori'], $_POST['namaKategori']);
        $_SESSION['success'] = "Kategori berhasil diupdate";
    }
}

$dataKategori = bacaKategori();
$kategoriEdit = isset($_GET['edit']) ? cariKategori($_GET['edit']) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="Logo Kidding.png" type="image/x-icon">
    <title>POS System - Kategori</title>
    <style>
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .category-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .category-name {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .category-product-count {
            color: #666;
            font-size: 0.9rem;
        }
        
        .category-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
        }
        
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
            
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">🏷️ Manajemen Kategori</h1>
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

        <div class="form-grid-2">
            <!-- Form Tambah/Edit Kategori -->
            <div class="card">
                <div class="card-header">
                    <?= $kategoriEdit ? 'Edit Kategori' : 'Tambah Kategori Baru' ?>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <?php if($kategoriEdit): ?>
                            <input type="hidden" name="idKategori" value="<?= $kategoriEdit['idKategori'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="idKategori" value="">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="namaKategori" class="form-label">Nama Kategori</label>
                            <input type="text" id="namaKategori" name="namaKategori" class="form-control" 
                                   placeholder="Masukkan nama kategori" required 
                                   value="<?= $kategoriEdit['namaKategori'] ?? '' ?>">
                        </div>
                        
                        <div class="btn-container">
                            <button type="submit" name="simpan" class="btn btn-primary">
                                <?= $kategoriEdit ? 'Update Kategori' : 'Simpan Kategori' ?>
                            </button>
                            <a href="pos_categories.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informasi Kategori -->
            <div class="card">
                <div class="card-header">Informasi Kategori</div>
                <div class="card-body">
                    <p>Kategori membantu mengorganisir produk Anda menjadi kelompok-kelompok yang logis.</p>
                    <p>Beberapa manfaat menggunakan kategori:</p>
                    <ul>
                        <li>Memudahkan pencarian produk</li>
                        <li>Analisis penjualan per kategori</li>
                        <li>Tampilan produk yang lebih terorganisir</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Daftar Kategori -->
        <div class="card">
            <div class="card-header">
                Daftar Kategori (<?= count($dataKategori) ?> kategori)
            </div>
            <div class="card-body">
                <div class="categories-grid">
                    <?php if (count($dataKategori) > 0): ?>
                        <?php foreach ($dataKategori as $kategori): ?>
                            <div class="category-card">
                                <div>
                                    <div class="category-name"><?= $kategori['namaKategori'] ?></div>
                                    <div class="category-product-count"><?= hitungProdukPerKategori($kategori['idKategori']) ?> produk</div>
                                </div>
                                
                                <div class="category-actions">
                                    <a href="?edit=<?= $kategori['idKategori'] ?>" class="btn btn-primary btn-small">Edit</a>
                                    <a href="?hapus=<?= $kategori['idKategori'] ?>" class="btn btn-danger btn-small" 
                                       onclick="return confirm('Hapus kategori ini? Produk dalam kategori ini akan menjadi uncategorized.')">Hapus</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Belum ada kategori yang tersedia.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>