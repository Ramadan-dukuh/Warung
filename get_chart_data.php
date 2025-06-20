<?php
require_once('koneksi.php');
require_once('crudBarang.php');

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'sales' => [],
    'topProducts' => []
];

try {
    $period = $_GET['period'] ?? '30days';
    $startDate = $_GET['start'] ?? null;
    $endDate = $_GET['end'] ?? null;

    // Get sales data based on period
    switch ($period) {
        case '30days':
            $salesData = bacaPenjualanPerHari();
            break;
        case 'monthly':
            $salesData = [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'data' => bacaPenjualanPerBulan()
            ];
            break;
        case 'yearly':
            // Implement yearly data if needed
            $salesData = [
                'labels' => [],
                'data' => []
            ];
            break;
        case 'custom':
            if ($startDate && $endDate) {
                $salesData = bacaPenjualanPerTanggal($startDate, $endDate);
            } else {
                throw new Exception('Range tanggal tidak valid');
            }
            break;
        default:
            throw new Exception('Periode tidak valid');
    }

    // Get top products data
    $topProducts = bacaProdukTerlaris(5);
    $topProductsLabels = [];
    $topProductsData = [];

    foreach ($topProducts as $product) {
        $topProductsLabels[] = $product['namaBarang'];
        $topProductsData[] = (int)$product['total_terjual'];
    }

    $response['success'] = true;
    $response['sales'] = $salesData;
    $response['topProducts'] = [
        'labels' => $topProductsLabels,
        'data' => $topProductsData
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>