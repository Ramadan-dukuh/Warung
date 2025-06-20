<?php
session_start();
include 'crudBarang.php';

// Statistik dasar
$totalBarang = hitungTotalBarang();
$totalKategori = count(bacaKategori());
$barangStokRendah = bacaBarangStokRendah(10); // Stok kurang dari 10
$totalPenjualanHari = isset($_SESSION['total_penjualan_hari']) ? $_SESSION['total_penjualan_hari'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="Logo Kidding.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <title>POS System - Dashboard</title>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            display: block;
            padding: 20px;
            background: white;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .action-btn:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
        }
        
        .action-btn .icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .low-stock-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .low-stock-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .low-stock-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">📊 POS System Dashboard</h1>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div style="color: green; background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistik Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalBarang ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-number"><?= $totalKategori ?></div>
                <div class="stat-label">Kategori</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number"><?= count($barangStokRendah) ?></div>
                <div class="stat-label">Stok Rendah</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-number">Rp <?= number_format($totalPenjualanHari, 0, ',', '.') ?></div>
                <div class="stat-label">Penjualan Hari Ini</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">Menu Utama</div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="pos_sale.php" class="action-btn">
                        <span class="icon">🛒</span>
                        Penjualan
                    </a>
                    
                    <a href="pos_inventory.php" class="action-btn">
                        <span class="icon">📦</span>
                        Manajemen Stok
                    </a>
                    
                    <a href="pos_reports.php" class="action-btn">
                        <span class="icon">📊</span>
                        Laporan
                    </a>
                    
                    <a href="pos_categories.php" class="action-btn">
                        <span class="icon">🏷️</span>
                        Kategori
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Stok Rendah -->
        <?php if (count($barangStokRendah) > 0): ?>
        <div class="card">
            <div class="card-header">⚠️ Peringatan Stok Rendah</div>
            <div class="card-body">
                <div class="low-stock-alert">
                    <strong>Perhatian!</strong> Ada beberapa produk dengan stok yang hampir habis:
                </div>
                
                <?php foreach ($barangStokRendah as $barang): ?>
                <div class="low-stock-item">
                    <div>
                        <strong><?= $barang['namaBarang'] ?></strong><br>
                        <small>Kode: <?= $barang['kodeBarang'] ?></small>
                    </div>
                    <div style="text-align: right;">
                        <span style="color: #e74c3c; font-weight: bold;">Stok: <?= $barang['stok'] ?></span><br>
                        <small>Rp <?= number_format($barang['hargaBarang'], 0, ',', '.') ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px; color: #666;">
            <p>© 2025 Kidding. Sistem Manajemen Penjualan Terintegrasi</p>
        </div>
    </div>

    <a href="#" class="back-to-top" id="backToTop">↑</a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Back to top functionality
            const backToTop = document.getElementById('backToTop');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 100) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });
            
            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        });
    </script>
</body>
</html>