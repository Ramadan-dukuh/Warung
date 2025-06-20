<?php
session_start();
include 'crudBarang.php';
         $penjualanPerBulan = bacaPenjualanPerBulan();

// Data untuk laporan
$totalPenjualanHari = isset($_SESSION['total_penjualan_hari']) ? $_SESSION['total_penjualan_hari'] : 0;
$totalPenjualanMinggu = isset($_SESSION['total_penjualan_minggu']) ? $_SESSION['total_penjualan_minggu'] : 0;
$totalPenjualanBulan = isset($_SESSION['total_penjualan_bulan']) ? $_SESSION['total_penjualan_bulan'] : 0;
$produkTerlaris = bacaProdukTerlaris(5); // 5 produk terlaris
$kategoriTerlaris = bacaKategoriTerlaris(3); // 3 kategori terlaris
$transaksiTerakhir = bacaTransaksiTerakhir(10); // 10 transaksi terakhir

// Fungsi sementara jika belum ada di crudBarang.php
if (!function_exists('bacaProdukTerlaris')) {
    function bacaProdukTerlaris($limit) {
        // Implementasi query untuk produk terlaris
        return [];
    }
}

if (!function_exists('bacaKategoriTerlaris')) {
    function bacaKategoriTerlaris($limit) {
        // Implementasi query untuk kategori terlaris
        return [];
    }
}

if (!function_exists('bacaTransaksiTerakhir')) {
    function bacaTransaksiTerakhir($limit) {
        // Implementasi query untuk transaksi terakhir
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="Logo Kidding.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <title>POS System - Laporan</title>
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
        
        .report-section {
            margin-bottom: 40px;
        }
        
        .report-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .report-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .report-card-header {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .report-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .report-item:last-child {
            border-bottom: none;
        }
        
        .report-item-name {
            font-weight: 500;
        }
        
        .report-item-value {
            font-weight: bold;
        }
        
        .transaction-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .transaction-id {
            font-weight: bold;
            color: #007bff;
        }
        
        .transaction-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .transaction-total {
            font-weight: bold;
            text-align: right;
        }
        
        .transaction-detail {
            color: #666;
            font-size: 0.9rem;
        }
        
        .date-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-btn {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .print-btn {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }
        /* Add this to your existing CSS */
.flatpickr-calendar {
    font-family: inherit;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.flatpickr-day.selected {
    background: #667eea;
    border-color: #667eea;
}

.flatpickr-day.selected:hover {
    background: #764ba2;
    border-color: #764ba2;
}

.form-control.flatpickr {
    cursor: pointer;
    background-color: white;
}
    </style>
</head>
<body>
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">📊 Laporan Penjualan</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">← Dashboard</a>
                <button class="print-btn" onclick="window.print()">🖨️ Cetak Laporan</button>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div style="color: green; background: #d4edda; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
<!-- Filter Tanggal -->
<div class="card">
    <div class="card-header">Filter Laporan</div>
    <div class="card-body">
        <div class="date-filter">
            <div>
                <label for="startDate">Dari Tanggal</label>
                <input type="text" id="startDate" class="form-control flatpickr" placeholder="Pilih tanggal">
            </div>
            <div>
                <label for="endDate">Sampai Tanggal</label>
                <input type="text" id="endDate" class="form-control flatpickr" placeholder="Pilih tanggal">
            </div>
            <button class="filter-btn">Terapkan Filter</button>
        </div>
    </div>
</div>

        <!-- Statistik Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-number">Rp <?= number_format($totalPenjualanHari, 0, ',', '.') ?></div>
                <div class="stat-label">Penjualan Hari Ini</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-number">Rp <?= number_format($totalPenjualanMinggu, 0, ',', '.') ?></div>
                <div class="stat-label">Penjualan Minggu Ini</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number">Rp <?= number_format($totalPenjualanBulan, 0, ',', '.') ?></div>
                <div class="stat-label">Penjualan Bulan Ini</div>
            </div>
        </div>

        <!-- Produk Terlaris -->
        <div class="report-section">
            <h2 class="report-title">🔥 Produk Terlaris</h2>
            <div class="report-grid">
                <div class="report-card">
                    <div class="report-card-header">Top 5 Produk</div>
                    <?php if (count($produkTerlaris) > 0): ?>
                        <?php foreach ($produkTerlaris as $produk): ?>
                            <div class="report-item">
                              <span class="report-item-name"><?= $produk['namaBarang'] ?></span>
    <span class="report-item-value"><?= $produk['total_terjual'] ?> terjual</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada data produk terlaris</p>
                    <?php endif; ?>
                </div>
                
                <div class="report-card">
                    <div class="report-card-header">Top 3 Kategori</div>
                    <?php if (count($kategoriTerlaris) > 0): ?>
                        <?php foreach ($kategoriTerlaris as $kategori): ?>
                            <div class="report-item">
                                 <span class="report-item-name"><?= $kategori['namaKategori'] ?></span>
    <span class="report-item-value"><?= $kategori['total_terjual'] ?> produk</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada data kategori terlaris</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Transaksi Terakhir -->
        <div class="report-section">
            <h2 class="report-title">⏱️ Transaksi Terakhir</h2>
            <div class="report-card">
                <?php if (count($transaksiTerakhir) > 0): ?>
                    <?php foreach ($transaksiTerakhir as $transaksi): ?>
                        <div class="transaction-item">
                            <div class="transaction-header">
                                <span class="transaction-id">#<?= $transaksi['idTransaksi'] ?></span>
                                <span class="transaction-date"><?= date('d M Y H:i', strtotime($transaksi['tanggal'])) ?></span>
                            </div>
                            <div class="transaction-detail">
                                <?= $transaksi['jumlah_item'] ?> item
                            </div>
                            <div class="transaction-total">
                                Rp <?= number_format($transaksi['total'], 0, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Belum ada transaksi</p>
                <?php endif; ?>
            </div>
        </div>

       <!-- Grafik Penjualan -->
<div class="report-section">
    <h2 class="report-title">📈 Grafik Penjualan</h2>
    
    <!-- Filter Tanggal untuk Grafik -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form id="chartFilterForm">
                <div class="row">
                    <div class="col-md-4">
                        <label>Periode</label>
                        <select class="form-control" id="chartPeriod">
                            <option value="30days">30 Hari Terakhir</option>
                            <option value="monthly">Bulan Ini</option>
                            <option value="yearly">Tahun Ini</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="customDateStartContainer" style="display:none;">
                        <label>Dari Tanggal</label>
                        <input type="date" class="form-control" id="customDateStart">
                    </div>
                    <div class="col-md-4" id="customDateEndContainer" style="display:none;">
                        <label>Sampai Tanggal</label>
                        <input type="date" class="form-control" id="customDateEnd">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Terapkan</button>
            </form>
        </div>
    </div>
    
    <!-- Chart Container -->
    <div class="report-card">
        <canvas id="salesChart" height="400"></canvas>
    </div>
</div>

<!-- Grafik Produk Terlaris -->
<div class="report-section">
    <h2 class="report-title">📊 Grafik Produk Terlaris</h2>
    <div class="report-card">
        <canvas id="topProductsChart" height="300"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Fungsi untuk format currency
function formatCurrency(value) {
    return 'Rp ' + value.toLocaleString('id-ID');
}

// Inisialisasi chart
let salesChart, topProductsChart;

// Fungsi untuk memuat data chart
function loadCharts(period = '30days', startDate = null, endDate = null) {
    // Tampilkan loading
    document.getElementById('salesChart').innerHTML = '<p>Memuat data...</p>';
    
    // Ambil data dari server
    fetch(`get_chart_data.php?period=${period}&start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            // Update sales chart
            updateSalesChart(data.sales);
            
            // Update top products chart
            updateTopProductsChart(data.topProducts);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data grafik');
        });
}

// Fungsi untuk update sales chart
function updateSalesChart(chartData) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // Hancurkan chart sebelumnya jika ada
    if (salesChart) {
        salesChart.destroy();
    }
    
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Total Penjualan',
                data: chartData.data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                },
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Penjualan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

// Fungsi untuk update top products chart
function updateTopProductsChart(chartData) {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    
    // Hancurkan chart sebelumnya jika ada
    if (topProductsChart) {
        topProductsChart.destroy();
    }
    
    topProductsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Jumlah Terjual',
                data: chartData.data,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Produk Terlaris'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' unit terjual';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Event listener untuk form filter
document.getElementById('chartFilterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const period = document.getElementById('chartPeriod').value;
    let startDate = null, endDate = null;
    
    if (period === 'custom') {
        startDate = document.getElementById('customDateStart').value;
        endDate = document.getElementById('customDateEnd').value;
        
        if (!startDate || !endDate) {
            alert('Harap pilih range tanggal');
            return;
        }
    }
    
    loadCharts(period, startDate, endDate);
});

// Toggle custom date input
document.getElementById('chartPeriod').addEventListener('change', function() {
    const isCustom = this.value === 'custom';
    document.getElementById('customDateStartContainer').style.display = isCustom ? 'block' : 'none';
    document.getElementById('customDateEndContainer').style.display = isCustom ? 'block' : 'none';
});

// Load initial chart data
document.addEventListener('DOMContentLoaded', function() {
    // Set default date for custom range
    const today = new Date();
    const lastMonth = new Date();
    lastMonth.setMonth(today.getMonth() - 1);
    
    document.getElementById('customDateStart').valueAsDate = lastMonth;
    document.getElementById('customDateEnd').valueAsDate = today;
    
    // Load initial data
    loadCharts();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (current month)
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    // Initialize Flatpickr for start date
    flatpickr("#startDate", {
        dateFormat: "Y-m-d",
        defaultDate: firstDayOfMonth,
        maxDate: today,
        onChange: function(selectedDates, dateStr) {
            // Update end date min date when start date changes
            endDatePicker.set("minDate", dateStr);
        }
    });
    
    // Initialize Flatpickr for end date
    const endDatePicker = flatpickr("#endDate", {
        dateFormat: "Y-m-d",
        defaultDate: today,
        maxDate: today
    });
    
    // Filter button click handler
    document.querySelector('.filter-btn').addEventListener('click', function() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        if (!startDate || !endDate) {
            alert('Harap pilih range tanggal');
            return;
        }
        
        // Here you would typically reload the data or page with the new date range
        alert(`Filter diterapkan: ${startDate} hingga ${endDate}`);
        
        // Example of how you might refresh the charts:
        loadCharts('custom', startDate, endDate);
    });
});
</script>
</script>
</body>
</html>