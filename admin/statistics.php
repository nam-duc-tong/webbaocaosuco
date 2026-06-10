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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y['year']; ?>" <?php echo $selected_year == $y['year'] ? 'selected' : ''; ?>>
                            Năm <?php echo $y['year']; ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (empty($years)): ?>
                        <option value="<?php echo $current_year; ?>">Năm <?php echo $current_year; ?></option>
                    <?php endif; ?>
                </select>
                <select name="month" class="form-select w-auto">
                    <option value="">Tất cả các tháng</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $selected_month == $m ? 'selected' : ''; ?>>
                            Tháng <?php echo $m; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-chart-line"></i> Xem thống kê</button>
                <?php if ($selected_month || $selected_year != $current_year): ?>
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

        <?php if ($selected_month): ?>
            <div class="chart-card">
                <h4><i class="fas fa-calendar-day"></i> Báo cáo theo ngày (Tháng <?php echo $selected_month; ?>/<?php echo $selected_year; ?>)</h4>
                <canvas id="dailyChart" style="max-height: 300px;"></canvas>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Biểu đồ 5: Phân loại sự cố -->
            <div class="col-12">
                <div class="chart-card">
                    <!-- ==================== PHÂN LOẠI SỰ CỐ - CẢNH BÁO ==================== -->

                    <div class="chart-card" style="border-left: 4px solid #dc3545;">
                        <h4 style="color: #dc3545;">
                            <i class="fas fa-tags"></i> ⚠️ PHÂN LOẠI SỰ CỐ - Các loại sự cố thường gặp
                        </h4>
                        <p class="text-muted mb-3">
                            <i class="fas fa-chart-pie"></i> Thống kê chi tiết theo từng loại sự cố cần được kiểm soát
                        </p>

                        <?php
                        // Lấy thống kê phân loại sự cố từ JSON (chính xác hơn)
                        $category_stats = $pdo->query("
                            SELECT phan_loai_su_co_json, COUNT(*) as report_count
                            FROM baocao 
                            WHERE phan_loai_su_co_json IS NOT NULL AND phan_loai_su_co_json != ''
                            GROUP BY phan_loai_su_co_json
                        ")->fetchAll();

                        // Xử lý và đếm từng loại sự cố
                        $category_breakdown = [];

                        foreach ($category_stats as $stat) {
                            $categories = json_decode($stat['phan_loai_su_co_json'], true);
                            if (is_array($categories)) {
                                foreach ($categories as $cat) {
                                    // Chuẩn hóa tên category
                                    $cat_clean = trim($cat);
                                    if (!isset($category_breakdown[$cat_clean])) {
                                        $category_breakdown[$cat_clean] = 0;
                                    }
                                    $category_breakdown[$cat_clean] += $stat['report_count'];
                                }
                            }
                        }

                        // Sắp xếp theo số lượng giảm dần
                        arsort($category_breakdown);

                        // Lấy thống kê mức độ nghiêm trọng theo từng loại
                        $all_categories = array_keys($category_breakdown);
                        $severity_by_category = [];

                        foreach ($all_categories as $cat) {
                            // Đếm số báo cáo NC3 theo từng loại
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) as critical_count
                                FROM baocao 
                                WHERE muc_do_su_co = 'NC3' 
                                AND (phan_loai_su_co LIKE :cat OR phan_loai_su_co_json LIKE :cat_json)
                            ");
                            $like_cat = '%' . $cat . '%';
                            $stmt->execute([':cat' => $like_cat, ':cat_json' => $like_cat]);
                            $severity_by_category[$cat] = $stmt->fetch()['critical_count'];
                        }

                        $total_incidents = array_sum($category_breakdown);
                        ?>

                        <!-- Cảnh báo tổng quan -->
                        <div class="alert alert-danger mb-4" style="background: #fff5f5; border-left: 4px solid #dc3545;">
                            <i class="fas fa-chart-line"></i>
                            <strong>THỐNG KÊ SỰ CỐ:</strong> Tổng số sự cố đã ghi nhận: <strong><?php echo $total_incidents; ?> lượt</strong>
                            , tập trung vào <strong><?php echo count($category_breakdown); ?> nhóm</strong> sự cố chính.
                        </div>

                        <?php if (!empty($category_breakdown)): ?>
                            <!-- Bảng phân loại sự cố -->
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover">
                                    <thead style="background: #dc3545; color: white;">
                                        <tr>
                                            <th>#</th>
                                            <th>Loại sự cố</th>
                                            <th>Số lượng</th>
                                            <th>Tỷ lệ</th>
                                            <th>Mức độ NC3</th>
                                            <th>Mức cảnh báo</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $rank = 1;
                                        $max_count = max($category_breakdown);
                                        foreach ($category_breakdown as $category => $count):
                                            $percentage = round(($count / $total_incidents) * 100, 1);
                                            $bar_width = ($count / $max_count) * 100;
                                            $critical_count = $severity_by_category[$category] ?? 0;
                                            $critical_rate = $count > 0 ? round(($critical_count / $count) * 100, 1) : 0;

                                            // Xác định mức độ cảnh báo
                                            if ($count >= 20 || $critical_rate >= 30) {
                                                $warning_level = "CẢNH BÁO ĐỎ";
                                                $warning_class = "danger";
                                                $warning_icon = "🔴";
                                                $status = "Cần xử lý ngay";
                                            } elseif ($count >= 10 || $critical_rate >= 15) {
                                                $warning_level = "CẢNH BÁO CAM";
                                                $warning_class = "warning";
                                                $warning_icon = "🟠";
                                                $status = "Cần theo dõi";
                                            } else {
                                                $warning_level = "CẢNH BÁO VÀNG";
                                                $warning_class = "info";
                                                $warning_icon = "🟡";
                                                $status = "Bình thường";
                                            }
                                        ?>
                                            <tr style="background: <?php echo $rank <= 3 ? '#fff5f5' : 'white'; ?>;">
                                                <td class="text-center">
                                                    <span class="badge bg-<?php echo $warning_class; ?>" style="font-size: 14px;">
                                                        <?php echo $warning_icon; ?> #<?php echo $rank++; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($category); ?></strong>
                                                    <?php if ($rank <= 4): ?>
                                                        <span class="badge bg-danger ms-2">⚠️ Cần kiểm soát</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-dark" style="font-size: 14px;"><?php echo $count; ?> lượt</span>
                                                </td>
                                                <td style="min-width: 150px;">
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1" style="height: 10px;">
                                                            <div class="progress-bar bg-<?php echo $warning_class; ?>" style="width: <?php echo $bar_width; ?>%"></div>
                                                        </div>
                                                        <span class="ms-2 small"><?php echo $percentage; ?>%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-danger">💀 <?php echo $critical_count; ?> nghiêm trọng</span>
                                                    <small class="text-muted d-block">(<?php echo $critical_rate; ?>%)</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $warning_class; ?>">
                                                        <?php echo $warning_level; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($warning_level == 'CẢNH BÁO ĐỎ'): ?>
                                                        <i class="fas fa-exclamation-circle text-danger"></i> <?php echo $status; ?>
                                                    <?php elseif ($warning_level == 'CẢNH BÁO CAM'): ?>
                                                        <i class="fas fa-exclamation-triangle text-warning"></i> <?php echo $status; ?>
                                                    <?php else: ?>
                                                        <i class="fas fa-check-circle text-success"></i> <?php echo $status; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Biểu đồ phân loại sự cố (Horizontal Bar) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="categoryChart" style="max-height: 400px;"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <canvas id="categorySeverityChart" style="max-height: 400px;"></canvas>
                                </div>
                            </div>

                            <!-- Kết luận và khuyến nghị -->
                            <div class="alert alert-warning mt-4" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                                <i class="fas fa-clipboard-list"></i>
                                <strong>ĐÁNH GIÁ & KHUYẾN NGHỊ:</strong>
                                <ul class="mt-2 mb-0">
                                    <?php
                                    $top_category = array_key_first($category_breakdown);
                                    $top_count = $category_breakdown[$top_category] ?? 0;
                                    ?>
                                    <li>🔴 <strong>Loại sự cố phổ biến nhất:</strong> "<?php echo htmlspecialchars($top_category); ?>" với <?php echo $top_count; ?> lượt</li>
                                    <li>⚠️ Tập trung kiểm soát các loại sự cố có tỷ lệ NC3 cao (màu đỏ)</li>
                                    <li>📋 Tổ chức đào tạo chuyên sâu về <?php echo htmlspecialchars($top_category); ?> cho nhân viên</li>
                                    <li>✅ Cập nhật quy trình xử lý các loại sự cố thường gặp</li>
                                    <li>📊 Theo dõi sát sao các loại sự cố có xu hướng gia tăng</li>
                                </ul>
                            </div>

                        <?php else: ?>
                            <div class="alert alert-info text-center py-5">
                                <i class="fas fa-chart-simple fa-3x mb-3 d-block"></i>
                                <h5>Chưa có dữ liệu phân loại sự cố</h5>
                                <p>Khi có báo cáo được gửi, thống kê sẽ hiển thị tại đây</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <script>
                    // Biểu đồ phân loại sự cố - Horizontal Bar
                    <?php if (!empty($category_breakdown)): ?>
                        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
                        new Chart(categoryCtx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_keys($category_breakdown)); ?>,
                                datasets: [{
                                    label: '⚠️ Số lượng sự cố',
                                    data: <?php echo json_encode(array_values($category_breakdown)); ?>,
                                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                    borderColor: 'rgba(220, 53, 69, 1)',
                                    borderWidth: 1,
                                    borderRadius: 5
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            font: {
                                                size: 12,
                                                weight: 'bold'
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Số lượng: ' + context.raw + ' báo cáo';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Số lượng báo cáo',
                                            font: {
                                                weight: 'bold'
                                            }
                                        },
                                        ticks: {
                                            stepSize: 1
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Loại sự cố',
                                            font: {
                                                weight: 'bold'
                                            }
                                        },
                                        ticks: {
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Biểu đồ mức độ nghiêm trọng theo từng loại
                        const severityCtx = document.getElementById('categorySeverityChart').getContext('2d');
                        new Chart(severityCtx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_keys($category_breakdown)); ?>,
                                datasets: [{
                                    label: '💀 Số lượng NC3 (Nghiêm trọng)',
                                    data: <?php echo json_encode(array_values($severity_by_category)); ?>,
                                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                    borderColor: 'rgba(0, 0, 0, 1)',
                                    borderWidth: 1,
                                    borderRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            font: {
                                                size: 12,
                                                weight: 'bold'
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.raw + ' báo cáo mức độ nghiêm trọng';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Số lượng NC3',
                                            font: {
                                                weight: 'bold'
                                            }
                                        },
                                        ticks: {
                                            stepSize: 1
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Loại sự cố',
                                            font: {
                                                weight: 'bold'
                                            }
                                        },
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    <?php endif; ?>
                </script>
            </div>
        </div>

        <!-- Biểu đồ 6: Top người báo cáo -->
        <div class="col-12">
            <!-- ==================== TOP 10 NGƯỜI BÁO CÁO NHIỀU NHẤT (PHÊ BÌNH) ==================== -->
            <div class="chart-card" style="border-left: 4px solid #dc3545;">
                <h4 style="color: #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i> ⚠️ CẢNH BÁO: Top 10 người có số lượng báo cáo nhiều nhất
                </h4>
                <p class="text-muted mb-3">
                    <i class="fas fa-chart-line"></i> Những cá nhân cần chú ý cải thiện chất lượng công việc
                </p>

                <?php
                // Lấy dữ liệu top 10 người báo cáo
                $top_reporters = $pdo->query("
                    SELECT 
                        nguoibaocao, 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN muc_do_su_co = 'NC3' THEN 1 ELSE 0 END) as critical,
                        MAX(created_at) as last_report
                    FROM baocao 
                    WHERE nguoibaocao IS NOT NULL AND nguoibaocao != ''
                    GROUP BY nguoibaocao 
                    ORDER BY total DESC 
                    LIMIT 10
                ")->fetchAll();

                // Tính tổng số báo cáo để hiển thị cảnh báo
                $total_reports = array_sum(array_column($top_reporters, 'total'));
                ?>

                <!-- Cảnh báo đầu trang -->
                <div class="alert alert-danger mb-4" style="background: #fff5f5; border-left: 4px solid #dc3545;">
                    <i class="fas fa-bell"></i>
                    <strong>THỐNG KÊ CẢNH BÁO:</strong> Có <strong><?php echo count($top_reporters); ?> người</strong> đang có số lượng báo cáo sự cố cao,
                    tổng cộng <strong><?php echo $total_reports; ?> báo cáo</strong> cần được xem xét và cải thiện.
                </div>

                <?php if (!empty($top_reporters)): ?>
                    <!-- Bảng xếp hạng "phê bình" -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover">
                            <thead style="background: #dc3545; color: white;">
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Cá nhân</th>
                                    <th style="width: 120px;">Số báo cáo</th>
                                    <th style="width: 120px;">Chưa xử lý</th>
                                    <th style="width: 120px;">Mức độ NC3</th>
                                    <th style="width: 150px;">Mức độ nghiêm trọng</th>
                                    <th style="width: 120px;">Cảnh báo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rank = 1;
                                $max_total = $top_reporters[0]['total'] ?? 1;
                                foreach ($top_reporters as $reporter):
                                    $pending_rate = $reporter['total'] > 0 ? round($reporter['pending'] / $reporter['total'] * 100, 1) : 0;
                                    $critical_rate = $reporter['total'] > 0 ? round($reporter['critical'] / $reporter['total'] * 100, 1) : 0;
                                    $bar_width = ($reporter['total'] / $max_total) * 100;

                                    // Xác định mức độ cảnh báo
                                    if ($rank == 1) {
                                        $warning_level = "Cảnh báo đỏ";
                                        $warning_class = "danger";
                                        $warning_icon = "🔴";
                                    } elseif ($rank <= 3) {
                                        $warning_level = "Cảnh báo cam";
                                        $warning_class = "warning";
                                        $warning_icon = "🟠";
                                    } else {
                                        $warning_level = "Cảnh báo vàng";
                                        $warning_class = "info";
                                        $warning_icon = "🟡";
                                    }
                                ?>
                                    <tr style="background: <?php echo $rank <= 3 ? '#fff5f5' : 'white'; ?>;">
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $warning_class; ?>" style="font-size: 16px; padding: 5px 10px;">
                                                <?php echo $warning_icon; ?> #<?php echo $rank; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reporter['nguoibaocao']); ?></strong>
                                            <?php if ($rank <= 3): ?>
                                                <span class="badge bg-danger ms-2">Cần kiểm điểm</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark" style="font-size: 14px; padding: 6px 12px;">
                                                📊 <?php echo $reporter['total']; ?> báo cáo
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">⏳ <?php echo $reporter['pending']; ?> chưa xử lý</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">⚠️ <?php echo $reporter['critical']; ?> nghiêm trọng</span>
                                        </td>
                                        <td style="min-width: 150px;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-danger" style="width: <?php echo $critical_rate; ?>%"></div>
                                                </div>
                                                <span class="ms-2 small text-danger"><?php echo $critical_rate; ?>% NC3</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $warning_class; ?>">
                                                <?php echo $warning_level; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php $rank++;
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Biểu đồ cột với tông màu đỏ cam -->
                    <canvas id="topReporterChart" style="max-height: 400px;"></canvas>

                    <!-- Kết luận cảnh báo -->
                    <div class="alert alert-warning mt-4" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                        <i class="fas fa-clipboard-list"></i>
                        <strong>KHUYẾN NGHỊ:</strong>
                        <ul class="mt-2 mb-0">
                            <li>✅ Rà soát quy trình làm việc của những cá nhân có số lượng báo cáo cao</li>
                            <li>✅ Tổ chức đào tạo bồi dưỡng nghiệp vụ cho các khoa/phòng có nhiều sự cố</li>
                            <li>✅ Theo dõi sát sao tiến độ xử lý các báo cáo mức độ NC3</li>
                            <li>✅ Đề xuất giải pháp phòng ngừa sự cố tái diễn</li>
                        </ul>
                    </div>

                <?php else: ?>
                    <div class="alert alert-success text-center py-5">
                        <i class="fas fa-check-circle fa-3x mb-3 d-block"></i>
                        <h5>Chưa có dữ liệu báo cáo</h5>
                        <p>Hệ thống đang hoạt động tốt</p>
                    </div>
                <?php endif; ?>
            </div>

            <script>
                // Biểu đồ Top người báo cáo - Tông màu đỏ cam (phê bình)
                <?php if (!empty($top_reporters)): 
                ?>
                const topReporterCtx = document.getElementById('topReporterChart').getContext('2d');
                new Chart(topReporterCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($top_reporters, 'nguoibaocao')); ?>,
                        datasets: [{
                                label: '⚠️ Tổng số báo cáo',
                                data: <?php echo json_encode(array_column($top_reporters, 'total')); ?>,
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 2,
                                borderRadius: 8,
                                barPercentage: 0.6
                            },
                            {
                                label: '🔴 Chưa xử lý',
                                data: <?php echo json_encode(array_column($top_reporters, 'pending')); ?>,
                                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 2,
                                borderRadius: 8,
                                barPercentage: 0.6
                            },
                            {
                                label: '💀 Mức độ NC3',
                                data: <?php echo json_encode(array_column($top_reporters, 'critical')); ?>,
                                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                borderColor: 'rgba(0, 0, 0, 1)',
                                borderWidth: 2,
                                borderRadius: 8,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    usePointStyle: true,
                                    boxWidth: 10
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw + ' báo cáo';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                    callback: function(value) {
                                        return value + ' báo cáo';
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Số lượng báo cáo',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    }
                                },
                                grid: {
                                    borderDash: [5, 5],
                                    color: '#f0f0f0'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Người báo cáo',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    }
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                    autoSkip: true,
                                    font: {
                                        size: 11,
                                        weight: 'bold'
                                    }
                                }
                            }
                        },
                        elements: {
                            bar: {
                                borderSkipped: 'round',
                                borderRadius: 8
                            }
                        }
                    }
                });
                <?php endif; 
                ?>
            </script>
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
                    for ($i = 1; $i <= 12; $i++):
                        $total = $monthly_data[$i]['total'];
                        $resolved = $monthly_data[$i]['resolved'];
                        $rate = $total > 0 ? round($resolved / $total * 100, 1) : 0;
                        $pending = $total - $resolved;
                    ?>
                        <tr>
                            <td><?php echo $month_names[$i - 1]; ?></td>
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
                    foreach ($severity_stats as $s) {
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
                for ($i = 1; $i <= 12; $i++) {
                    echo $monthly_data[$i]['total'] . ($i < 12 ? ',' : '');
                }
                ?>
            ],
            resolved: [
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    echo $monthly_data[$i]['resolved'] . ($i < 12 ? ',' : '');
                }
                ?>
            ]
        };

        <?php if ($selected_month): ?>
            const dailyData = {
                labels: <?php
                        $daily_labels = [];
                        $daily_values = [];
                        for ($i = 1; $i <= $days_in_month; $i++) {
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
                datasets: [{
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

        <?php if ($selected_month): ?>
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