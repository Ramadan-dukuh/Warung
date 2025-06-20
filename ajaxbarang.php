<?php
include 'crudBarang.php';

$cari = isset($_GET['cari']) ? $_GET['cari'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$totalBarang = hitungTotalBarang($cari);
$total_pages = ceil($totalBarang / $items_per_page);

$dataBarang = bacaBarangPagination($cari, $offset, $items_per_page);
?>

<div id="table-container">
    <?php if (count($dataBarang) > 0): ?>
    <table class="data-table">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Kategori</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dataBarang as $barang): ?>
        <tr>
            <td><?= $barang['kodeBarang'] ?></td>
            <td><?= $barang['namaBarang'] ?></td>
            <td>Rp <?= number_format($barang['hargaBarang'], 0, ',', '.') ?></td>
            <td><?= $barang['stok'] ?></td>
            <td><?= $barang['namaKategori'] ?></td>
            <td class="action-cell">
                <a href="?edit=<?= $barang['kodeBarang'] ?>" class="btn-action btn-edit">Edit</a>
                <a href="?hapus=<?= $barang['kodeBarang'] ?>" onclick="return confirm('Yakin ingin hapus?')" class="btn-action btn-delete">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="#" class="pagination-link <?= ($i == $page) ? 'active' : '' ?>" 
               data-page="<?= $i ?>" data-keyword="<?= htmlspecialchars($cari) ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <div class="empty-state">
        <p>Tidak ada data ditemukan.</p>
    </div>
    <?php endif; ?>
</div>
