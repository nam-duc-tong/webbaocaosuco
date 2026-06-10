<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Xử lý xóa báo cáo
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Kiểm tra báo cáo tồn tại
    $check = $pdo->prepare("SELECT id FROM baocao WHERE id = :id");
    $check->execute([':id' => $delete_id]);
    
    if ($check->fetch()) {
        $delete = $pdo->prepare("DELETE FROM baocao WHERE id = :id");
        $delete->execute([':id' => $delete_id]);
        $success = "Đã xóa báo cáo #" . str_pad($delete_id, 5, '0', STR_PAD_LEFT) . " thành công!";
    } else {
        $error = "Không tìm thấy báo cáo cần xóa!";
    }
}

// Xử lý cập nhật trạng thái hàng loạt (nếu có)
if (isset($_POST['bulk_status']) && isset($_POST['selected_ids'])) {
    $status = $_POST['bulk_status'];
    $selected_ids = $_POST['selected_ids'];
    
    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $update = $pdo->prepare("UPDATE baocao SET status = ? WHERE id IN ($placeholders)");
        $params = array_merge([$status], $selected_ids);
        $update->execute($params);
        $success = "Đã cập nhật trạng thái cho " . count($selected_ids) . " báo cáo!";
    }
}

// Lấy danh sách báo cáo với filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$diem_xay_ra_su_co_filter = $_GET['diem_xay_ra_su_co'] ?? '';

$sql = "SELECT * FROM baocao WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (nguoibaocao LIKE :search OR email LIKE :search OR mo_ta_su_co LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($status_filter) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}
if ($diem_xay_ra_su_co_filter) {
    $sql .= " AND (diem_xay_ra_su_co = :diem_xay_ra_su_co OR diem_xay_ra_su_co_khac = :diem_xay_ra_su_co)";
    $params[':diem_xay_ra_su_co'] = $diem_xay_ra_su_co_filter;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Lấy danh sách địa điểm cho filter
$diem_xay_ra_su_cos = $pdo->query("SELECT DISTINCT diem_xay_ra_su_co FROM baocao WHERE diem_xay_ra_su_co IS NOT NULL")->fetchAll();

// Thống kê
$total = count($reports);
$pending = count(array_filter($reports, fn($r) => $r['status'] == 'pending'));
$resolved = count(array_filter($reports, fn($r) => $r['status'] == 'resolved'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách báo cáo - Admin</title>
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
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .badge-pending { background: #ffc107; color: #333; }
        .badge-processing { background: #17a2b8; color: white; }
        .badge-resolved { background: #28a745; color: white; }
        .badge-rejected { background: #dc3545; color: white; }
        
        .btn-sm {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            margin: 0 2px;
        }
        
        .delete-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
        }
        
        .delete-btn:hover {
            color: #bb2d3b;
        }
        
        .bulk-actions {
            display: none;
            margin-bottom: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
        }
        
        .select-all {
            cursor: pointer;
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
            <a href="view_reports.php" class="active"><i class="fas fa-list"></i> Danh sách báo cáo</a>
            <a href="statistics.php"><i class="fas fa-chart-bar"></i> Thống kê</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="navbar-top">
            <h4><i class="fas fa-list"></i> Danh sách báo cáo sự cố</h4>
            <div>
                <span class="me-3"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>

        <!-- Thông báo -->
        <?php if(isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Thống kê nhanh -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file-alt fa-2x text-primary"></i>
                <h3><?php echo $total; ?></h3>
                <p class="text-muted">Tổng báo cáo</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock fa-2x text-warning"></i>
                <h3><?php echo $pending; ?></h3>
                <p class="text-muted">Chờ xử lý</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle fa-2x text-success"></i>
                <h3><?php echo $resolved; ?></h3>
                <p class="text-muted">Đã giải quyết</p>
            </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Đã giải quyết</option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="diem_xay_ra_su_co" class="form-select">
                        <option value="">Tất cả địa điểm</option>
                        <?php foreach($diem_xay_ra_su_cos as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc['diem_xay_ra_su_co']); ?>" <?php echo $diem_xay_ra_su_co_filter == $loc['diem_xay_ra_su_co'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['diem_xay_ra_su_co']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Lọc</button>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions">
            <form method="POST" class="row g-3 align-items-center">
                <div class="col-auto">
                    <strong>Đã chọn <span id="selectedCount">0</span> báo cáo</strong>
                </div>
                <div class="col-auto">
                    <select name="bulk_status" class="form-select form-select-sm">
                        <option value="">Chọn thao tác</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="resolved">Đã giải quyết</option>
                        <option value="rejected">Từ chối</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">Hủy chọn</button>
                </div>
            </form>
        </div>

        <!-- Danh sách báo cáo -->
        <div class="table-container">
            <div class="mb-3">
                <label class="select-all">
                    <input type="checkbox" id="selectAllCheckbox" class="me-2">
                    <strong>Chọn tất cả</strong>
                </label>
            </div>
            
            <form id="bulkForm" method="POST">
                <input type="hidden" name="bulk_status" id="bulkStatusInput">
                <div id="selectedIdsContainer"></div>
            
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <th>ID</th>
                            <th>Người báo cáo</th>
                            <th>Địa điểm</th>
                            <th>Mức độ</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($reports) > 0): ?>
                            <?php foreach($reports as $report): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_ids[]" value="<?php echo $report['id']; ?>" class="report-checkbox">
                                </td>
                                <td>#<?php echo str_pad($report['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($report['nguoibaocao']); ?></td>
                                <td>
                                    <?php 
                                    $diem = $report['diem_xay_ra_su_co'];
                                    if ($diem == 'Mục khác' && !empty($report['diem_xay_ra_su_co_khac'])) {
                                        echo htmlspecialchars($report['diem_xay_ra_su_co_khac']);
                                    } else {
                                        echo htmlspecialchars($diem);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $mucdo_color = [
                                        'NC0' => 'secondary',
                                        'NC1' => 'info',
                                        'NC2' => 'warning',
                                        'NC3' => 'danger'
                                    ];
                                    $color = $mucdo_color[$report['muc_do_su_co']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo $report['muc_do_su_co']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $report['status']; ?>">
                                        <?php 
                                        $status_text = [
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'resolved' => 'Đã giải quyết',
                                            'rejected' => 'Từ chối'
                                        ];
                                        echo $status_text[$report['status']];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></td>
                                <td>
                                    <a href="view_detail.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_report.php?id=<?php echo $report['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Xóa" onclick="confirmDelete(<?php echo $report['id']; ?>, '<?php echo htmlspecialchars($report['nguoibaocao']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    Không có báo cáo nào
                                    <div class="mt-3">
                                        <a href="dashboard.php" class="btn btn-primary btn-sm">Về trang chủ</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash text-danger"></i> Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa báo cáo <strong id="deleteReportInfo"></strong> không?</p>
                    <p class="text-danger">Hành động này không thể khôi phục!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Xử lý checkbox chọn tất cả
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const reportCheckboxes = document.querySelectorAll('.report-checkbox');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCountSpan = document.getElementById('selectedCount');
        
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.report-checkbox:checked');
            const count = checked.length;
            selectedCountSpan.textContent = count;
            bulkActions.style.display = count > 0 ? 'block' : 'none';
        }
        
        selectAllCheckbox.addEventListener('change', function() {
            reportCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateSelectedCount();
        });
        
        reportCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = reportCheckboxes.length === document.querySelectorAll('.report-checkbox:checked').length;
                selectAllCheckbox.checked = allChecked;
                updateSelectedCount();
            });
        });
        
        function clearSelection() {
            reportCheckboxes.forEach(cb => cb.checked = false);
            selectAllCheckbox.checked = false;
            updateSelectedCount();
        }
        
        // Xử lý bulk action
        const bulkForm = document.getElementById('bulkForm');
        const bulkStatusInput = document.getElementById('bulkStatusInput');
        
        document.querySelector('.bulk-actions select[name="bulk_status"]').addEventListener('change', function() {
            if (this.value) {
                bulkStatusInput.value = this.value;
                bulkForm.submit();
            }
        });
        
        // Xóa báo cáo
        let deleteId = null;
        
        function confirmDelete(id, name) {
            deleteId = id;
            document.getElementById('deleteReportInfo').innerHTML = `<strong>#${String(id).padStart(5, '0')} - ${name}</strong>`;
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.href = `view_reports.php?delete_id=${id}`;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>