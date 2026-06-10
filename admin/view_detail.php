<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Lấy ID báo cáo
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Lấy thông tin báo cáo
$stmt = $pdo->prepare("SELECT * FROM baocao WHERE id = :id");
$stmt->execute([':id' => $id]);
$report = $stmt->fetch();

if (!$report) {
    die("Không tìm thấy báo cáo!");
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'] ?? 'pending';
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    $update = $pdo->prepare("UPDATE baocao SET status = :status, admin_notes = :admin_notes, processed_at = NOW() WHERE id = :id");
    $update->execute([
        ':status' => $status,
        ':admin_notes' => $admin_notes,
        ':id' => $id
    ]);
    
    header("Location: view_detail.php?id=$id&updated=1");
    exit();
}

// Xử lý xóa báo cáo
if (isset($_GET['delete']) && $_GET['delete'] == 1) {
    $delete = $pdo->prepare("DELETE FROM baocao WHERE id = :id");
    $delete->execute([':id' => $id]);
    header("Location: dashboard.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết báo cáo #<?php echo $id; ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-right: 3px solid white;
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
        
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .info-section h4 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e1e5e9;
            color: #333;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-label {
            width: 250px;
            font-weight: 600;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .badge-pending { background: #ffc107; color: #333; }
        .badge-processing { background: #17a2b8; color: white; }
        .badge-resolved { background: #28a745; color: white; }
        .badge-rejected { background: #dc3545; color: white; }
        
        .btn-action {
            padding: 8px 20px;
            border-radius: 8px;
            margin-right: 10px;
        }
        
        @media print {
            .sidebar, .navbar-top, .action-buttons, .status-form {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
            .info-section {
                box-shadow: none;
                border: 1px solid #ddd;
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
            <a href="statistics.php"><i class="fas fa-chart-bar"></i> Thống kê</a>
            <a href="view_detail.php?id=<?php echo $id; ?>" class="active"><i class="fas fa-eye"></i> Chi tiết</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="navbar-top">
            <h4><i class="fas fa-file-alt"></i> Chi tiết báo cáo #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></h4>
            <div>
                <span class="me-3"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['nguoi_bao_cao'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>

        <?php if(isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> Đã cập nhật trạng thái báo cáo thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="info-section action-buttons">
            <button onclick="window.print()" class="btn btn-secondary btn-action">
                <i class="fas fa-print"></i> In báo cáo
            </button>
            <button onclick="window.history.back()" class="btn btn-info btn-action">
                <i class="fas fa-arrow-left"></i> Quay lại
            </button>
            <button type="button" class="btn btn-danger btn-action" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Xóa báo cáo
            </button>
        </div>

        <!-- Thông tin cơ bản -->
        <div class="info-section">
            <h4><i class="fas fa-info-circle"></i> Thông tin cơ bản</h4>
            
            <div class="info-row">
                <div class="info-label">Mã báo cáo:</div>
                <div class="info-value">#<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Trạng thái:</div>
                <div class="info-value">
                    <span class="badge-status badge-<?php echo $report['status']; ?>">
                        <?php 
                        $status_text = [
                            'pending' => '⏳ Chờ xử lý',
                            'processing' => '🔄 Đang xử lý',
                            'resolved' => '✅ Đã giải quyết',
                            'rejected' => '❌ Từ chối'
                        ];
                        echo $status_text[$report['status']] ?? $report['status'];
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Ngày tạo:</div>
                <div class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($report['created_at'])); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Cập nhật lần cuối:</div>
                <div class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($report['updated_at'])); ?></div>
            </div>
        </div>

        <!-- Thông tin người báo cáo -->
        <div class="info-section">
            <h4><i class="fas fa-user"></i> Thông tin người báo cáo</h4>
            
            <div class="info-row">
                <div class="info-label">Người báo cáo:</div>
                <div class="info-value"><?php echo htmlspecialchars($report['nguoibaocao']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($report['email'] ?: 'Không có'); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Hình thức báo cáo:</div>
                <div class="info-value">
                    <?php 
                    if ($report['hinhthuc'] == 'tự nguyện') {
                        echo '<span class="badge bg-success">Tự nguyện</span>';
                    } else {
                        echo '<span class="badge bg-warning">Bắt buộc</span>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Thông tin sự cố -->
        <div class="info-section">
            <h4><i class="fas fa-exclamation-triangle"></i> Thông tin sự cố</h4>
            
            <div class="info-row">
                <div class="info-label">Địa điểm xảy ra:</div>
                <div class="info-value">
                    <?php 
                    $diem = $report['diem_xay_ra_su_co'];
                    if ($diem == 'Mục khác' && !empty($report['diem_xay_ra_su_co_khac'])) {
                        echo htmlspecialchars($report['diem_xay_ra_su_co_khac']);
                    } else {
                        echo htmlspecialchars($diem);
                    }
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Nhóm sự cố:</div>
                <div class="info-value"><?php echo htmlspecialchars($report['nhom_su_co']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Thời gian xảy ra:</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($report['thoigian'])); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Đối tượng xảy ra:</div>
                <div class="info-value">
                    <?php 
                    $doi_tuong = $report['doi_tuong'];
                    if ($doi_tuong == 'Mục khác' && !empty($report['doi_tuong_khac'])) {
                        echo htmlspecialchars($report['doi_tuong_khac']);
                    } else {
                        echo htmlspecialchars($doi_tuong);
                    }
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Tính chất sự cố:</div>
                <div class="info-value">
                    <?php 
                    if ($report['tinh_chat_su_co'] == 'Suýt xảy ra') {
                        echo '<span class="badge bg-warning">⚠️ Suýt xảy ra</span>';
                    } else {
                        echo '<span class="badge bg-danger">💥 Đã xảy ra</span>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Mức độ sự cố:</div>
                <div class="info-value">
                    <?php 
                    $mucdo_color = [
                        'NC0' => 'secondary',
                        'NC1' => 'info',
                        'NC2' => 'warning',
                        'NC3' => 'danger'
                    ];
                    $mucdo_text = [
                        'NC0' => 'Chưa xảy ra (NC0) - Chưa xảy ra, có nguy cơ xảy ra sự cố',
                        'NC1' => 'Nhẹ (NC1) - Đã xảy ra, có tác động hoặc chưa tác động đến người bệnh, không gây nguy hại cho NB',
                        'NC2' => 'Trung bình (NC2) - Đã xảy ra, có gây nguy hại NB phải can thiệp điều trị',
                        'NC3' => 'Nặng (NC3) - Đã xảy ra, gây nguy hại hoặc tử vong NB'
                    ];
                    $color = $mucdo_color[$report['muc_do_su_co']] ?? 'secondary';
                    $text = $mucdo_text[$report['muc_do_su_co']] ?? $report['muc_do_su_co'];
                    ?>
                    <span class="badge bg-<?php echo $color; ?>"><?php echo $text; ?></span>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Phân loại sự cố:</div>
                <div class="info-value">
                    <?php 
                    if (!empty($report['phan_loai_su_co'])) {
                        $items = explode(', ', $report['phan_loai_su_co']);
                        foreach ($items as $item) {
                            echo '<span class="badge bg-primary me-1 mb-1">' . htmlspecialchars($item) . '</span>';
                        }
                    } else {
                        echo 'Không có';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Mô tả chi tiết -->
        <div class="info-section">
            <h4><i class="fas fa-file-alt"></i> Mô tả chi tiết</h4>
            
            <div class="info-row">
                <div class="info-label">Thông tin người bệnh:</div>
                <div class="info-value">
                    <?php echo nl2br(htmlspecialchars($report['thong_tin_nguoi_benh'] ?: 'Không có')); ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Mô tả sự cố:</div>
                <div class="info-value">
                    <?php echo nl2br(htmlspecialchars($report['mo_ta_su_co'])); ?>
                </div>
            </div>
        </div>

        <!-- Xử lý sự cố -->
        <div class="info-section">
            <h4><i class="fas fa-tools"></i> Xử lý sự cố</h4>
            
            <div class="info-row">
                <div class="info-label">Thông báo cấp trên:</div>
                <div class="info-value">
                    <?php echo $report['thong_bao_cap_tren'] == 'yes' ? '✅ Đã báo' : '❌ Chưa báo'; ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Xử lý ban đầu:</div>
                <div class="info-value">
                    <?php echo nl2br(htmlspecialchars($report['xu_ly_ban_dau'])); ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Giải pháp đề xuất:</div>
                <div class="info-value">
                    <?php echo nl2br(htmlspecialchars($report['giai_phap'])); ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Ghi nhận hồ sơ:</div>
                <div class="info-value">
                    <?php echo $report['ghi_nhan_ho_so'] == 'yes' ? '✅ Có ghi nhận' : '❌ Chưa ghi nhận'; ?>
                </div>
            </div>
        </div>

        <!-- Xử lý của Admin -->
        <div class="info-section">
            <h4><i class="fas fa-user-shield"></i> Xử lý của Admin</h4>
            
            <form method="POST" class="status-form">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Cập nhật trạng thái:</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?php echo $report['status'] == 'pending' ? 'selected' : ''; ?>>
                                ⏳ Chờ xử lý
                            </option>
                            <option value="processing" <?php echo $report['status'] == 'processing' ? 'selected' : ''; ?>>
                                🔄 Đang xử lý
                            </option>
                            <option value="resolved" <?php echo $report['status'] == 'resolved' ? 'selected' : ''; ?>>
                                ✅ Đã giải quyết
                            </option>
                            <option value="rejected" <?php echo $report['status'] == 'rejected' ? 'selected' : ''; ?>>
                                ❌ Từ chối
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Ghi chú xử lý:</label>
                        <textarea name="admin_notes" class="form-control" rows="3" 
                                  placeholder="Nhập ghi chú, hướng giải quyết..."><?php echo htmlspecialchars($report['admin_notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if($report['processed_at']): ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-clock"></i> 
                Đã xử lý lần cuối vào: <?php echo date('d/m/Y H:i:s', strtotime($report['processed_at'])); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('Bạn có chắc chắn muốn xóa báo cáo #<?php echo $id; ?> này không?\nHành động này không thể khôi phục!')) {
                window.location.href = 'view_detail.php?id=<?php echo $id; ?>&delete=1';
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>