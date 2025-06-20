    <?php
    require_once('koneksi.php');

    function bacaSemuaBarang($keyword = ''){
        $koneksi = koneksiToko();
        $keyword = mysqli_real_escape_string($koneksi, $keyword);
        $sql = "SELECT b.*, k.namaKategori 
                FROM barang b 
                LEFT JOIN kategori k ON b.idKategori = k.idKategori
                WHERE b.kodeBarang LIKE '%$keyword%' 
                OR b.namaBarang LIKE '%$keyword%' 
                OR b.hargaBarang LIKE '%$keyword%' 
                OR k.namaKategori LIKE '%$keyword%'";

        $hasil = mysqli_query($koneksi, $sql);
        $data = [];
        while ($baris = mysqli_fetch_assoc($hasil)) {
            $data[] = $baris;
        }
        mysqli_close($koneksi);
        return $data;
    }


    function cariBarang($kodeBarang){
        $koneksi = koneksiToko();
        $sql = "SELECT * FROM barang WHERE kodeBarang = '$kodeBarang'";
        $hasil = mysqli_query($koneksi, $sql);
        $data = null;
        if (mysqli_num_rows($hasil) > 0) {
            $data = mysqli_fetch_assoc($hasil);
        }
        mysqli_close($koneksi);
        return $data;
    }

    function tambahBarang($nama, $harga, $stok, $idKategori) {
        $koneksi = koneksiToko();
        $nama = mysqli_real_escape_string($koneksi, $nama);
        $cekSql = "SELECT * FROM barang WHERE namaBarang = '$nama'";
        $cekResult = mysqli_query($koneksi, $cekSql);

        if (mysqli_num_rows($cekResult) > 0) {
            $_SESSION['error'] = "Barang dengan nama '$nama' sudah ada.";
        } else {
            $sql = "INSERT INTO barang (namaBarang, hargaBarang, stok, idKategori) 
                    VALUES ('$nama', '$harga', '$stok', '$idKategori')";
            mysqli_query($koneksi, $sql);
            $_SESSION['success'] = "Barang berhasil ditambahkan.";
        }
        mysqli_close($koneksi);
    }

    function editBarang($kode, $nama, $harga, $stok, $idKategori) {
        $koneksi = koneksiToko();
        $sql = "UPDATE barang SET namaBarang='$nama', hargaBarang='$harga', stok='$stok', idKategori='$idKategori' WHERE kodeBarang='$kode'";
        mysqli_query($koneksi, $sql);
        mysqli_close($koneksi);
    }

    function hapusBarang($kode) {
        $koneksi = koneksiToko();
        $sql = "DELETE FROM barang WHERE kodeBarang='$kode'";
        mysqli_query($koneksi, $sql);
        mysqli_close($koneksi);
    }

    // Fungsi untuk menghitung total barang (dengan atau tanpa filter)
    function hitungTotalBarang($cari = '') {
        $koneksi = koneksiToko();
        $sql = "SELECT COUNT(*) as total FROM barang b 
                JOIN kategori k ON b.idKategori = k.idKategori 
                WHERE namaBarang LIKE '%$cari%' OR namaKategori LIKE '%$cari%'";
        $hasil = mysqli_query($koneksi, $sql);
        $data = mysqli_fetch_assoc($hasil);
        mysqli_close($koneksi);
        return $data['total'];
    }

    // Fungsi untuk membaca barang dengan pagination
    function bacaBarangPagination($cari = '', $offset = 0, $limit = 10) {
        $koneksi = koneksiToko();
        $sql = "SELECT b.*, k.namaKategori 
                FROM barang b 
                JOIN kategori k ON b.idKategori = k.idKategori 
                WHERE namaBarang LIKE '%$cari%' OR namaKategori LIKE '%$cari%'
                LIMIT $offset, $limit";
        $hasil = mysqli_query($koneksi, $sql);
        $data = array();
        while ($row = mysqli_fetch_assoc($hasil)) {
            $data[] = $row;
        }
        mysqli_close($koneksi);
        return $data;
    }

    // Fungsi untuk barang dengan stok rendah
    function bacaBarangStokRendah($batas = 10) {
        $koneksi = koneksiToko();
        $sql = "SELECT b.*, k.namaKategori 
                FROM barang b 
                LEFT JOIN kategori k ON b.idKategori = k.idKategori 
                WHERE b.stok <= $batas 
                ORDER BY b.stok ASC";
        $hasil = mysqli_query($koneksi, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($hasil)) {
            $data[] = $row;
        }
        mysqli_close($koneksi);
        return $data;
    }

    // Fungsi untuk upload gambar
   function tambahBarangDenganGambar($nama, $harga, $stok, $kategori, $gambar) {
    $koneksi = koneksiToko();
    $nama = mysqli_real_escape_string($koneksi, $nama);
    $gambar = mysqli_real_escape_string($koneksi, $gambar);
    
    $cekSql = "SELECT * FROM barang WHERE namaBarang = '$nama'";
    $cekResult = mysqli_query($koneksi, $cekSql);

    if (mysqli_num_rows($cekResult) > 0) {
        $_SESSION['error'] = "Barang dengan nama '$nama' sudah ada";
        return false;
    }
    
    $sql = "INSERT INTO barang (namaBarang, hargaBarang, stok, idKategori, gambar) 
            VALUES ('$nama', '$harga', '$stok', '$kategori', '$gambar')";
    $hasil = mysqli_query($koneksi, $sql);
    mysqli_close($koneksi);
    return $hasil;
}

function editBarangDenganGambar($kode, $nama, $harga, $stok, $kategori, $gambar) {
    $koneksi = koneksiToko();
    $success = false;
    
    try {
        // Jika ada gambar baru, update dengan gambar baru
        if (!empty($gambar)) {
            // Hapus gambar lama jika ada
            $barangLama = cariBarang($kode);
            if ($barangLama && $barangLama['gambar'] && file_exists('uploads/' . $barangLama['gambar'])) {
                unlink('uploads/' . $barangLama['gambar']);
            }
            
            $sql = "UPDATE barang SET 
                    namaBarang = '$nama', 
                    hargaBarang = '$harga', 
                    stok = '$stok', 
                    idKategori = '$kategori', 
                    gambar = '$gambar' 
                    WHERE kodeBarang = '$kode'";
        } else {
            // Jika tidak ada gambar baru, pertahankan gambar lama
            $sql = "UPDATE barang SET 
                    namaBarang = '$nama', 
                    hargaBarang = '$harga', 
                    stok = '$stok', 
                    idKategori = '$kategori'
                    WHERE kodeBarang = '$kode'";
        }
        
        $success = mysqli_query($koneksi, $sql);
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        $success = false;
    } finally {
        mysqli_close($koneksi);
    }
    
    return $success;
}
    // Fungsi untuk penjualan
    function simpanTransaksi($items, $total, $bayar, $kembalian) {
        $koneksi = koneksiToko();
        
        // Insert ke tabel transaksi
        $tanggal = date('Y-m-d H:i:s');
        $sql = "INSERT INTO transaksi (tanggal, total, bayar, kembalian) VALUES ('$tanggal', '$total', '$bayar', '$kembalian')";
        mysqli_query($koneksi, $sql);
        
        $idTransaksi = mysqli_insert_id($koneksi);
        
        // Insert detail transaksi dan update stok
        foreach ($items as $item) {
            $kodeBarang = $item['kode'];
            $jumlah = $item['jumlah'];
            $harga = $item['harga'];
            $subtotal = $jumlah * $harga;
            
            // Insert detail transaksi
            $sqlDetail = "INSERT INTO detail_transaksi (idTransaksi, kodeBarang, jumlah, harga, subtotal) 
                        VALUES ('$idTransaksi', '$kodeBarang', '$jumlah', '$harga', '$subtotal')";
            mysqli_query($koneksi, $sqlDetail);
            
            // Update stok barang
            $sqlUpdateStok = "UPDATE barang SET stok = stok - $jumlah WHERE kodeBarang = '$kodeBarang'";
            mysqli_query($koneksi, $sqlUpdateStok);
        }
        
        mysqli_close($koneksi);
        return $idTransaksi;
    }

    function getTotalPenjualanHari($tanggal = null) {
        $koneksi = koneksiToko();
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $sql = "SELECT COALESCE(SUM(total), 0) as total_penjualan 
                FROM transaksi 
                WHERE DATE(tanggal) = '$tanggal'";
        $hasil = mysqli_query($koneksi, $sql);
        $data = mysqli_fetch_assoc($hasil);
        mysqli_close($koneksi);
        return $data['total_penjualan'];
    }

    function getRekapPenjualan($dari = null, $sampai = null) {
        $koneksi = koneksiToko();
        if (!$dari) $dari = date('Y-m-d');
        if (!$sampai) $sampai = date('Y-m-d');
        
        $sql = "SELECT t.*, COUNT(dt.idDetail) as jumlah_item
                FROM transaksi t
                LEFT JOIN detail_transaksi dt ON t.idTransaksi = dt.idTransaksi
                WHERE DATE(t.tanggal) BETWEEN '$dari' AND '$sampai'
                GROUP BY t.idTransaksi
                ORDER BY t.tanggal DESC";
        
        $hasil = mysqli_query($koneksi, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($hasil)) {
            $data[] = $row;
        }
        mysqli_close($koneksi);
        return $data;
    }
    // Fungsi untuk membaca produk terlaris
function bacaProdukTerlaris($limit = 5) {
    $koneksi = koneksiToko();
    $sql = "SELECT b.kodeBarang, b.namaBarang, b.gambar, SUM(dt.jumlah) as total_terjual
            FROM detail_transaksi dt
            JOIN barang b ON dt.kodeBarang = b.kodeBarang
            GROUP BY b.kodeBarang, b.namaBarang, b.gambar
            ORDER BY total_terjual DESC
            LIMIT $limit";
    $hasil = mysqli_query($koneksi, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data[] = $row;
    }
    mysqli_close($koneksi);
    return $data;
}

// Fungsi untuk membaca kategori terlaris
function bacaKategoriTerlaris($limit = 3) {
    $koneksi = koneksiToko();
    $sql = "SELECT k.idKategori, k.namaKategori, SUM(dt.jumlah) AS total_terjual
            FROM detail_transaksi dt
            JOIN barang b ON dt.kodeBarang = b.kodeBarang
            JOIN kategori k ON b.idKategori = k.idKategori
            GROUP BY k.idKategori, k.namaKategori
            ORDER BY total_terjual DESC
            LIMIT $limit";
    $hasil = mysqli_query($koneksi, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data[] = $row;
    }
    mysqli_close($koneksi);
    return $data;
}

// Fungsi untuk membaca transaksi terakhir
function bacaTransaksiTerakhir($limit = 10) {
    $koneksi = koneksiToko();
    $sql = "SELECT t.*, 
                   COUNT(dt.idDetail) as jumlah_item
            FROM transaksi t
            LEFT JOIN detail_transaksi dt ON t.idTransaksi = dt.idTransaksi
            GROUP BY t.idTransaksi
            ORDER BY t.tanggal DESC
            LIMIT $limit";
    
    $hasil = mysqli_query($koneksi, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data[] = $row;
    }
    mysqli_close($koneksi);
    return $data;
}
// Tambah kategori
function tambahKategori($namaKategori) {
    $koneksi = koneksiToko();
    $namaKategori = mysqli_real_escape_string($koneksi, $namaKategori);

    // Cek duplikasi
    $cek = mysqli_query($koneksi, "SELECT * FROM kategori WHERE namaKategori = '$namaKategori'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = "Kategori '$namaKategori' sudah ada.";
    } else {
        $sql = "INSERT INTO kategori (namaKategori) VALUES ('$namaKategori')";
        mysqli_query($koneksi, $sql);
    }
    mysqli_close($koneksi);
}

// Edit kategori
function editKategori($idKategori, $namaKategori) {
    $koneksi = koneksiToko();
    $namaKategori = mysqli_real_escape_string($koneksi, $namaKategori);
    $sql = "UPDATE kategori SET namaKategori = '$namaKategori' WHERE idKategori = '$idKategori'";
    mysqli_query($koneksi, $sql);
    mysqli_close($koneksi);
}

// Hapus kategori
function hapusKategori($idKategori) {
    $koneksi = koneksiToko();
    $sql = "DELETE FROM kategori WHERE idKategori = '$idKategori'";
    mysqli_query($koneksi, $sql);
    mysqli_close($koneksi);
}

// Ambil semua kategori
function bacaKategori() {
    $koneksi = koneksiToko();
    $sql = "SELECT * FROM kategori ORDER BY idKategori DESC";
    $hasil = mysqli_query($koneksi, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data[] = $row;
    }
    mysqli_close($koneksi);
    return $data;
}

// Ambil satu kategori untuk diedit
function cariKategori($idKategori) {
    $koneksi = koneksiToko();
    $sql = "SELECT * FROM kategori WHERE idKategori = '$idKategori'";
    $hasil = mysqli_query($koneksi, $sql);
    $data = null;
    if (mysqli_num_rows($hasil) > 0) {
        $data = mysqli_fetch_assoc($hasil);
    }
    mysqli_close($koneksi);
    return $data;
}
function hitungProdukPerKategori($idKategori) {
    $koneksi = koneksiToko();
    $idKategori = mysqli_real_escape_string($koneksi, $idKategori);

    $sql = "SELECT COUNT(*) AS total FROM barang WHERE idKategori = '$idKategori'";
    $hasil = mysqli_query($koneksi, $sql);
    $data = mysqli_fetch_assoc($hasil);
    mysqli_close($koneksi);

    return $data['total'] ?? 0;
}
// Fungsi untuk mendapatkan data penjualan per bulan (tahun berjalan)
function bacaPenjualanPerBulan() {
    $koneksi = koneksiToko();
    $tahun = date('Y');
    
    // Inisialisasi array dengan 12 bulan (0 untuk bulan tanpa penjualan)
    $penjualanPerBulan = array_fill(0, 12, 0);
    
    $sql = "SELECT MONTH(tanggal) as bulan, SUM(total) as total 
            FROM transaksi 
            WHERE YEAR(tanggal) = '$tahun'
            GROUP BY MONTH(tanggal)";
    
    $hasil = mysqli_query($koneksi, $sql);
    
    while ($row = mysqli_fetch_assoc($hasil)) {
        $bulan = $row['bulan'] - 1; // Array dimulai dari 0 (Januari = 0)
        $penjualanPerBulan[$bulan] = (float)$row['total'];
    }
    
    mysqli_close($koneksi);
    return $penjualanPerBulan;
}

// Fungsi untuk mendapatkan data penjualan per hari (30 hari terakhir)
function bacaPenjualanPerHari() {
    $koneksi = koneksiToko();
    $tanggalAwal = date('Y-m-d', strtotime('-29 days'));
    $tanggalAkhir = date('Y-m-d');
    
    // Inisialisasi array untuk 30 hari
    $penjualanPerHari = [];
    $labelsHari = [];
    
    // Buat range tanggal
    $current = strtotime($tanggalAwal);
    $last = strtotime($tanggalAkhir);
    
    while ($current <= $last) {
        $tanggal = date('Y-m-d', $current);
        $labelsHari[] = date('d M', $current);
        $penjualanPerHari[$tanggal] = 0;
        $current = strtotime('+1 day', $current);
    }
    
    // Query data dari database
    $sql = "SELECT DATE(tanggal) as tanggal, SUM(total) as total 
            FROM transaksi 
            WHERE DATE(tanggal) BETWEEN '$tanggalAwal' AND '$tanggalAkhir'
            GROUP BY DATE(tanggal)";
    
    $hasil = mysqli_query($koneksi, $sql);
    
    while ($row = mysqli_fetch_assoc($hasil)) {
        $tanggal = $row['tanggal'];
        $penjualanPerHari[$tanggal] = (float)$row['total'];
    }
    
    mysqli_close($koneksi);
    
    return [
        'labels' => $labelsHari,
        'data' => array_values($penjualanPerHari)
    ];
}

// Fungsi untuk mendapatkan data penjualan berdasarkan filter tanggal
function bacaPenjualanPerTanggal($dari, $sampai) {
    $koneksi = koneksiToko();
    
    // Inisialisasi array
    $penjualanPerTanggal = [];
    $labelsTanggal = [];
    
    // Hitung selisih hari
    $current = strtotime($dari);
    $last = strtotime($sampai);
    $selisihHari = ($last - $current) / (60 * 60 * 24);
    
    // Jika rentang <= 31 hari, tampilkan per hari
    if ($selisihHari <= 31) {
        while ($current <= $last) {
            $tanggal = date('Y-m-d', $current);
            $labelsTanggal[] = date('d M', $current);
            $penjualanPerTanggal[$tanggal] = 0;
            $current = strtotime('+1 day', $current);
        }
        
        $sql = "SELECT DATE(tanggal) as tanggal, SUM(total) as total 
                FROM transaksi 
                WHERE DATE(tanggal) BETWEEN '$dari' AND '$sampai'
                GROUP BY DATE(tanggal)";
    } 
    // Jika rentang > 31 hari tapi <= 12 bulan, tampilkan per minggu
    elseif ($selisihHari <= 365) {
        $current = strtotime('last monday', strtotime($dari));
        while ($current <= $last) {
            $minggu = date('Y-\WW', $current);
            $labelsTanggal[] = 'Minggu ' . date('W', $current);
            $penjualanPerTanggal[$minggu] = 0;
            $current = strtotime('+1 week', $current);
        }
        
        $sql = "SELECT YEAR(tanggal) as tahun, WEEK(tanggal) as minggu, SUM(total) as total 
                FROM transaksi 
                WHERE DATE(tanggal) BETWEEN '$dari' AND '$sampai'
                GROUP BY YEAR(tanggal), WEEK(tanggal)";
    } 
    // Jika rentang > 12 bulan, tampilkan per bulan
    else {
        $current = strtotime(date('Y-m-01', strtotime($dari)));
        while ($current <= $last) {
            $bulan = date('Y-m', $current);
            $labelsTanggal[] = date('M Y', $current);
            $penjualanPerTanggal[$bulan] = 0;
            $current = strtotime('+1 month', $current);
        }
        
        $sql = "SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(total) as total 
                FROM transaksi 
                WHERE DATE(tanggal) BETWEEN '$dari' AND '$sampai'
                GROUP BY DATE_FORMAT(tanggal, '%Y-%m')";
    }
    
    $hasil = mysqli_query($koneksi, $sql);
    
    while ($row = mysqli_fetch_assoc($hasil)) {
        if ($selisihHari <= 31) {
            $key = $row['tanggal'];
        } elseif ($selisihHari <= 365) {
            $key = $row['tahun'] . '-W' . str_pad($row['minggu'], 2, '0', STR_PAD_LEFT);
        } else {
            $key = $row['bulan'];
        }
        
        if (array_key_exists($key, $penjualanPerTanggal)) {
            $penjualanPerTanggal[$key] = (float)$row['total'];
        }
    }
    
    mysqli_close($koneksi);
    
    return [
        'labels' => $labelsTanggal,
        'data' => array_values($penjualanPerTanggal)
    ];
}
    ?>