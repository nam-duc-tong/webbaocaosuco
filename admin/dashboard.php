<?php
// admin/dashboard.php - Admin xem danh sách báo cáo
session_start();
require_once(__DIR__ . '/../config/database.php');

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}
// Lấy dữ liệu từ checkbox (dạng mảng)
if (isset($_POST['phan_loai_su_co']) && is_array($_POST['phan_loai_su_co'])) {
    // Chuyển mảng thành chuỗi, ví dụ: "A, B, C"
    $phan_loai_su_co = implode(', ', $_POST['phan_loai_su_co']);
    
    // Hoặc lưu dạng JSON (khuyến nghị)
    $phan_loai_su_co_json = json_encode($_POST['phan_loai_su_co'], JSON_UNESCAPED_UNICODE);
} else {
    $phan_loai_su_co = '';
    $phan_loai_su_co_json = '';
}

// Lấy tất cả báo cáo từ database (chính là dữ liệu từ website)
$stmt = $pdo->query("SELECT * FROM baocao ORDER BY created_at DESC");
$reports = $stmt->fetchAll();

// Thống kê
$total = $pdo->query("SELECT COUNT(*) FROM baocao")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM baocao WHERE status = 'pending'")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM baocao WHERE status = 'resolved'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Báo cáo sự cố</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background: linear-gradient(135deg, #1e3c72, #2a5298); color: white;
        }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: block; padding: 12px 25px; color: rgba(255,255,255,0.8);
            text-decoration: none; transition: 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; }
        .main-content { margin-left: 260px; padding: 20px; }
        .navbar-top {
            background: white; padding: 15px 25px; border-radius: 12px;
            display: flex; justify-content: space-between; margin-bottom: 25px;
        }
        .stat-card {
            background: white; padding: 20px; border-radius: 12px;
            transition: transform 0.3s; margin-bottom: 20px;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .table-container { background: white; border-radius: 12px; padding: 20px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .badge {
            padding: 4px 8px; border-radius: 20px; font-size: 12px;
        }
        .badge-pending { background: #ffc107; color: #333; }
        .badge-processing { background: #17a2b8; color: white; }
        .badge-resolved { background: #28a745; color: white; }
        .btn-sm { padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        .btn-view { background: #17a2b8; color: white; }
        .logout-btn { background: #dc3545; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hospital-user" style="font-size: 48px;"></i>
            <h4>Quản lý sự cố</h4>
            <small>Hệ thống báo cáo</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tổng quan</a>
            <a href="view_reports.php"><i class="fas fa-list"></i> Danh sách báo cáo</a>
            <a href="statistics.php"><i class="fas fa-chart-bar"></i> Thống kê</a>
        </div>
    </div>

    <div class="main-content">
        <div class="navbar-top">
            <h3><i class="fas fa-chart-line"></i> Bảng điều khiển</h3>
            <div>
                <span class="me-3"><i class="fas fa-user"></i> <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-file-alt fa-3x text-primary"></i>
                    <h3 class="mt-2"><?php echo $total; ?></h3>
                    <p class="text-muted">Tổng báo cáo</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-clock fa-3x text-warning"></i>
                    <h3 class="mt-2"><?php echo $pending; ?></h3>
                    <p class="text-muted">Chờ xử lý</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-check-circle fa-3x text-success"></i>
                    <h3 class="mt-2"><?php echo $resolved; ?></h3>
                    <p class="text-muted">Đã giải quyết</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-building fa-3x text-info"></i>
                    <h3 class="mt-2"><?php echo count(array_unique(array_column($reports, 'location'))); ?></h3>
                    <p class="text-muted">Đơn vị báo cáo</p>
                </div>
            </div>
        </div>

        <!-- Danh sách báo cáo gần đây -->
        <div class="table-container">
            <h4><i class="fas fa-history"></i> Báo cáo gần đây</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người báo cáo</th>
                        <th>Địa điểm</th>
                        <th>Mức độ</th>
                        <th>Loại sự cố</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach(array_slice($reports, 0, 10) as $report): ?>
                    <tr>
                        <td>#<?php echo $report['id']; ?></td>
                        <td><?php echo htmlspecialchars($report['nguoibaocao']); ?></td>
                        <td><?php echo htmlspecialchars($report['diem_xay_ra_su_co']); ?></td>
                        <td>
                            <?php 
                            $severity_color = match($report['muc_do_su_co']) {
                                'NC3' => 'danger',
                                'NC2' => 'warning', 
                                'NC1' => 'info',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $severity_color; ?>">
                                <?php echo $report['muc_do_su_co']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($report['phan_loai_su_co']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $report['status']; ?>">
                                <?php 
                                $status_map = ['pending'=>'Chờ xử lý', 'processing'=>'Đang xử lý', 'resolved'=>'Đã giải quyết'];
                                echo $status_map[$report['status']] ?? $report['status'];
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></td>
                        <td>
                            <a href="view_detail.php?id=<?php echo $report['id']; ?>" class="btn-sm btn-view">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($reports) == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            Chưa có báo cáo nào từ website
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>