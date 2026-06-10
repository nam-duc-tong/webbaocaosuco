<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Lấy năm hiện tại cho filter
$current_year = date('Y');
$selected_year = $_GET['year'] ?? $current_year;
$selected_month = $_GET['month'] ?? '';

// ========== 1. THỐNG KÊ TỔNG QUAN ==========
$stats = [];

// Tổng số báo cáo
$stmt = $pdo->query("SELECT COUNT(*) as total FROM baocao");
$stats['total'] = $stmt->fetch()['total'];

// Theo trạng thái
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM baocao GROUP BY status");
$status_stats = $stmt->fetchAll();
foreach ($status_stats as $s) {
    $stats[$s['status']] = $s['count'];
}

// Theo mức độ
$stmt = $pdo->query("SELECT muc_do_su_co, COUNT(*) as count FROM baocao GROUP BY muc_do_su_co");
$severity_stats = $stmt->fetchAll();

// Theo địa điểm
$stmt = $pdo->query("
    SELECT 
        CASE 
            WHEN diem_xay_ra_su_co = 'Mục khác' THEN diem_xay_ra_su_co_khac
            ELSE diem_xay_ra_su_co
        END as location,
        COUNT(*) as count 
    FROM baocao 
    GROUP BY location 
    ORDER BY count DESC 
    LIMIT 10
");
$location_stats = $stmt->fetchAll();

// ========== 2. THỐNG KÊ THEO THỜI GIAN ==========

// Theo tháng trong năm
$monthly_stmt = $pdo->prepare("
    SELECT 
        MONTH(created_at) as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM baocao 
    WHERE YEAR(created_at) = :year
    GROUP BY MONTH(created_at)
    ORDER BY month
");
$monthly_stmt->execute([':year' => $selected_year]);
$monthly_stats = $monthly_stmt->fetchAll();

// Tạo mảng đầy đủ 12 tháng
$monthly_data = [];
for ($i = 1; $i <= 12; $i++) {
    $found = false;
    foreach ($monthly_stats as $m) {
        if ($m['month'] == $i) {
            $monthly_data[$i] = ['total' => $m['total'], 'resolved' => $m['resolved']];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $monthly_data[$i] = ['total' => 0, 'resolved' => 0];
    }
}

// Theo ngày trong tháng (nếu chọn tháng)
$daily_data = [];
if ($selected_month) {
    $year_month = $selected_year . '-' . str_pad($selected_month, 2, '0', STR_PAD_LEFT);
    $daily_stmt = $pdo->prepare("
        SELECT 
            DAY(created_at) as day,
            COUNT(*) as total
        FROM baocao 
        WHERE DATE_FORMAT(created_at, '%Y-%m') = :year_month
        GROUP BY DAY(created_at)
        ORDER BY day
    ");
    $daily_stmt->execute([':year_month' => $year_month]);
    $daily_stats = $daily_stmt->fetchAll();
    
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
    for ($i = 1; $i <= $days_in_month; $i++) {
        $found = false;
        foreach ($daily_stats as $d) {
            if ($d['day'] == $i) {
                $daily_data[$i] = $d['total'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $daily_data[$i] = 0;
        }
    }
}

// ========== 3. THỐNG KÊ THEO PHÂN LOẠI SỰ CỐ ==========
$category_stmt = $pdo->query("
    SELECT phan_loai_su_co, COUNT(*) as count 
    FROM baocao 
    WHERE phan_loai_su_co IS NOT NULL AND phan_loai_su_co != ''
    GROUP BY phan_loai_su_co
");
$category_stats = $category_stmt->fetchAll();

// Xử lý phân loại (vì có thể lưu nhiều giá trị trong 1 báo cáo)
$category_breakdown = [];
foreach ($category_stats as $cat) {
    $items = explode(', ', $cat['phan_loai_su_co']);
    foreach ($items as $item) {
        if (!isset($category_breakdown[$item])) {
            $category_breakdown[$item] = 0;
        }
        $category_breakdown[$item] += $cat['count'];
    }
}
arsort($category_breakdown);

// ========== 4. THỐNG KÊ NGƯỜI BÁO CÁO ==========
$reporter_stmt = $pdo->query("
    SELECT nguoibaocao, COUNT(*) as count 
    FROM baocao 
    GROUP BY nguoibaocao 
    ORDER BY count DESC 
    LIMIT 10
");
$reporter_stats = $reporter_stmt->fetchAll();

// ========== 5. THỜI GIAN XỬ LÝ TRUNG BÌNH ==========
$avg_time_stmt = $pdo->query("
    SELECT 
        AVG(TIMESTAMPDIFF(HOUR, created_at, processed_at)) as avg_hours
    FROM baocao 
    WHERE processed_at IS NOT NULL AND status = 'resolved'
");
$avg_processing_time = $avg_time_stmt->fetch()['avg_hours'] ?? 0;

// ========== 6. Lấy danh sách năm cho filter ==========
$year_stmt = $pdo->query("SELECT DISTINCT YEAR(created_at) as year FROM baocao ORDER BY year DESC");
$years = $year_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Báo cáo sự cố y tế</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header i {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .sidebar-header h3 {
            font-size: 18px;
            margin-top: 10px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        .navbar-top {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .chart-card h4 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e1e5e9;
        }

        .filter-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-bar select {
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .insight-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hospital-user"></i>
            <h3>Hệ thống quản lý</h3>
            <small>Báo cáo sự cố y tế</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Tổng quan</a>
            <a href="view_reports.php"><i class="fas fa-list"></i> Danh sách báo cáo</a>
            <a href="statistics.php" class="active"><i class="fas fa-chart-bar"></i> Thống kê</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="navbar-top">
            <h4><i class="fas fa-chart-line"></i> Thống kê & Báo cáo</h4>
            <div>
                <span class="me-3"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>

        <!-- Thống kê nhanh -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file-alt fa-2x text-primary"></i>
                <h3><?php echo number_format($stats['total']); ?></h3>
                <p class="text-muted">Tổng số báo cáo</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock fa-2x text-warning"></i>
                <h3><?php echo number_format($stats['pending'] ?? 0); ?></h3>
                <p class="text-muted">Chờ xử lý</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-spinner fa-2x text-info"></i>
                <h3><?php echo number_format($stats['processing'] ?? 0); ?></h3>
                <p class="text-muted">Đang xử lý</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle fa-2x text-success"></i>
                <h3><?php echo number_format($stats['resolved'] ?? 0); ?></h3>
                <p class="text-muted">Đã giải quyết</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-ban fa-2x text-danger"></i>
                <h3><?php echo number_format($stats['rejected'] ?? 0); ?></h3>
                <p class="text-muted">Từ chối</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-hourglass-half fa-2x text-secondary"></i>
                <h3><?php echo $avg_processing_time ? round($avg_processing_time, 1) : 0; ?>h</h3>
                <p class="text-muted">TB thời gian xử lý</p>
            </div>
        </div>

        <!-- Insight -->
        <div class="insight-text">
            <i class="fas fa-lightbulb fa-2x mb-2 d-block"></i>
            <h5>Góc nhìn nhanh</h5>
            <?php
            $resolve_rate = $stats['total'] > 0 ? round(($stats['resolved'] ?? 0) / $stats['total'] * 100, 1) : 0;
            $pending_percent = $stats['total'] > 0 ? round(($stats['pending'] ?? 0) / $stats['total'] * 100, 1) : 0;
            ?>
            <p>📊 Tỷ lệ giải quyết: <strong><?php echo $resolve_rate; ?>%</strong> (<?php echo number_format($stats['resolved'] ?? 0); ?>/<?php echo number_format($stats['total']); ?> báo cáo)</p>
            <p>⏳ Còn <strong><?php echo number_format($stats['pending'] ?? 0); ?> báo cáo</strong> đang chờ xử lý (<?php echo $pending_percent; ?>%)</p>
            <?php if ($avg_processing_time > 0): ?>
            <p>⚡ Thời gian xử lý trung bình: <strong><?php echo round($avg_processing_time, 1); ?> giờ</strong></p>
            <?php endif; ?>
        </div>

        <!-- Filter theo thời gian -->
        <div class="filter-bar">
            <form method="GET" class="d-flex gap-3 align-items-center flex-wrap">
                <label class="fw-bold">📅 Thống kê theo:</label>
                <select name="year" class="form-select w-auto">
                    <?php foreach($years as $y): ?>
                        <option value="<?php echo $y['year']; ?>" <?php echo $selected_year == $y['year'] ? 'selected' : ''; ?>>
                            Năm <?php echo $y['year']; ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if(empty($years)): ?>
                        <option value="<?php echo $current_year; ?>">Năm <?php echo $current_year; ?></option>
                    <?php endif; ?>
                </select>
                <select name="month" class="form-select w-auto">
                    <option value="">Tất cả các tháng</option>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $selected_month == $m ? 'selected' : ''; ?>>
                            Tháng <?php echo $m; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-chart-line"></i> Xem thống kê</button>
                <?php if($selected_month || $selected_year != $current_year): ?>
                    <a href="statistics.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Biểu đồ 1: Trạng thái báo cáo -->
        <div class="chart-card">
            <h4><i class="fas fa-chart-pie"></i> Phân bố theo trạng thái</h4>
            <canvas id="statusChart" style="max-height: 300px;"></canvas>
        </div>

        <div class="row">
            <!-- Biểu đồ 2: Mức độ sự cố -->
            <div class="col-md-6">
                <div class="chart-card">
                    <h4><i class="fas fa-chart-bar"></i> Mức độ sự cố</h4>
                    <canvas id="severityChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <!-- Biểu đồ 3: Top địa điểm -->
            <div class="col-md-6">
                <div class="chart-card">
                    <h4><i class="fas fa-map-marker-alt"></i> Top 5 địa điểm xảy ra sự cố</h4>
                    <canvas id="locationChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Biểu đồ 4: Xu hướng theo tháng -->
            <div class="col-md-12">
                <div class="chart-card">
                    <h4><i class="fas fa-chart-line"></i> Xu hướng báo cáo theo tháng (Năm <?php echo $selected_year; ?>)</h4>
                    <canvas id="trendChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <?php if($selected_month): ?>
        <div class="chart-card">
            <h4><i class="fas fa-calendar-day"></i> Báo cáo theo ngày (Tháng <?php echo $selected_month; ?>/<?php echo $selected_year; ?>)</h4>
            <canvas id="dailyChart" style="max-height: 300px;"></canvas>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Biểu đồ 5: Phân loại sự cố -->
            <div class="col-md-6">
                <div class="chart-card">
                    <h4><i class="fas fa-tags"></i> Phân loại sự cố</h4>
                    <canvas id="categoryChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <!-- Biểu đồ 6: Top người báo cáo -->
            <div class="col-md-6">
                <div class="chart-card">
                    <h4><i class="fas fa-users"></i> Top 10 người báo cáo nhiều nhất</h4>
                    <canvas id="reporterChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Bảng chi tiết -->
        <div class="chart-card">
            <h4><i class="fas fa-table"></i> Chi tiết thống kê theo tháng (Năm <?php echo $selected_year; ?>)</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tháng</th>
                            <th>Tổng số báo cáo</th>
                            <th>Đã giải quyết</th>
                            <th>Tỷ lệ giải quyết</th>
                            <th>Chưa giải quyết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $month_names = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
                        for($i = 1; $i <= 12; $i++): 
                            $total = $monthly_data[$i]['total'];
                            $resolved = $monthly_data[$i]['resolved'];
                            $rate = $total > 0 ? round($resolved / $total * 100, 1) : 0;
                            $pending = $total - $resolved;
                        ?>
                        <tr>
                            <td><?php echo $month_names[$i-1]; ?></td>
                            <td><?php echo number_format($total); ?></td>
                            <td><?php echo number_format($resolved); ?></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $rate; ?>%">
                                        <?php echo $rate; ?>%
                                    </div>
                                </div>
                            </td>
                            <td><?php echo number_format($pending); ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dữ liệu cho biểu đồ
        const statusData = {
            labels: ['Chờ xử lý', 'Đang xử lý', 'Đã giải quyết', 'Từ chối'],
            values: [
                <?php echo $stats['pending'] ?? 0; ?>,
                <?php echo $stats['processing'] ?? 0; ?>,
                <?php echo $stats['resolved'] ?? 0; ?>,
                <?php echo $stats['rejected'] ?? 0; ?>
            ]
        };

        const severityData = {
            labels: <?php 
                $severity_labels = [];
                $severity_counts = [];
                foreach($severity_stats as $s) {
                    $severity_labels[] = $s['muc_do_su_co'];
                    $severity_counts[] = $s['count'];
                }
                echo json_encode($severity_labels);
            ?>,
            values: <?php echo json_encode($severity_counts); ?>
        };

        const locationData = {
            labels: <?php 
                $location_labels = array_slice(array_column($location_stats, 'location'), 0, 5);
                $location_counts = array_slice(array_column($location_stats, 'count'), 0, 5);
                echo json_encode($location_labels);
            ?>,
            values: <?php echo json_encode($location_counts); ?>
        };

        const trendData = {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            total: [
                <?php 
                for($i = 1; $i <= 12; $i++) {
                    echo $monthly_data[$i]['total'] . ($i < 12 ? ',' : '');
                }
                ?>
            ],
            resolved: [
                <?php 
                for($i = 1; $i <= 12; $i++) {
                    echo $monthly_data[$i]['resolved'] . ($i < 12 ? ',' : '');
                }
                ?>
            ]
        };

        <?php if($selected_month): ?>
        const dailyData = {
            labels: <?php 
                $daily_labels = [];
                $daily_values = [];
                for($i = 1; $i <= $days_in_month; $i++) {
                    $daily_labels[] = 'Ngày ' . $i;
                    $daily_values[] = $daily_data[$i];
                }
                echo json_encode($daily_labels);
            ?>,
            values: <?php echo json_encode($daily_values); ?>
        };
        <?php endif; ?>

        const categoryData = {
            labels: <?php 
                $category_labels = array_keys(array_slice($category_breakdown, 0, 7));
                $category_values = array_values(array_slice($category_breakdown, 0, 7));
                echo json_encode($category_labels);
            ?>,
            values: <?php echo json_encode($category_values); ?>
        };

        const reporterData = {
            labels: <?php 
                $reporter_labels = array_column($reporter_stats, 'nguoibaocao');
                $reporter_counts = array_column($reporter_stats, 'count');
                echo json_encode($reporter_labels);
            ?>,
            values: <?php echo json_encode($reporter_counts); ?>
        };

        // Biểu đồ trạng thái
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.values,
                    backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
                }]
            }
        });

        // Biểu đồ mức độ
        if (severityData.labels.length > 0) {
            new Chart(document.getElementById('severityChart'), {
                type: 'bar',
                data: {
                    labels: severityData.labels,
                    datasets: [{
                        label: 'Số lượng báo cáo',
                        data: severityData.values,
                        backgroundColor: '#667eea'
                    }]
                }
            });
        }

        // Biểu đồ địa điểm
        if (locationData.labels.length > 0) {
            new Chart(document.getElementById('locationChart'), {
                type: 'pie',
                data: {
                    labels: locationData.labels,
                    datasets: [{
                        data: locationData.values,
                        backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b']
                    }]
                }
            });
        }

        // Biểu đồ xu hướng
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [
                    {
                        label: 'Tổng số báo cáo',
                        data: trendData.total,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Đã giải quyết',
                        data: trendData.resolved,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            }
        });

        <?php if($selected_month): ?>
        // Biểu đồ theo ngày
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: dailyData.labels,
                datasets: [{
                    label: 'Số báo cáo',
                    data: dailyData.values,
                    backgroundColor: '#17a2b8'
                }]
            }
        });
        <?php endif; ?>

        // Biểu đồ phân loại
        if (categoryData.labels.length > 0) {
            new Chart(document.getElementById('categoryChart'), {
                type: 'horizontalBar',
                data: {
                    labels: categoryData.labels,
                    datasets: [{
                        label: 'Số lượng',
                        data: categoryData.values,
                        backgroundColor: '#764ba2'
                    }]
                }
            });
        }

        // Biểu đồ người báo cáo
        if (reporterData.labels.length > 0) {
            new Chart(document.getElementById('reporterChart'), {
                type: 'bar',
                data: {
                    labels: reporterData.labels,
                    datasets: [{
                        label: 'Số báo cáo',
                        data: reporterData.values,
                        backgroundColor: '#fd7e14'
                    }]
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>