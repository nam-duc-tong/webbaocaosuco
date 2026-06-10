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
    header("Location: view_reports.php");
    exit();
}

// Lấy thông tin báo cáo
$stmt = $pdo->prepare("SELECT * FROM baocao WHERE id = :id");
$stmt->execute([':id' => $id]);
$report = $stmt->fetch();

if (!$report) {
    die("Không tìm thấy báo cáo!");
}

// Xử lý cập nhật báo cáo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Lấy dữ liệu từ form
    $nguoibaocao = trim($_POST['nguoibaocao'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hinhthuc = $_POST['hinhthuc'] ?? '';

    // Xử lý địa điểm
    $diem_xay_ra_su_co = $_POST['diem_xay_ra_su_co'] ?? '';
    $diem_xay_ra_su_co_khac = trim($_POST['diem_xay_ra_su_co_khac'] ?? '');

    if ($diem_xay_ra_su_co === 'Mục khác' && !empty($diem_xay_ra_su_co_khac)) {
        $diem_xay_ra_su_co = $diem_xay_ra_su_co_khac;
    }

    $nhom_su_co = $_POST['nhom_su_co'] ?? '';
    $thoigian = $_POST['thoigian'] ?? '';

    // Xử lý đối tượng
    $doi_tuong = $_POST['doi_tuong'] ?? '';
    $doi_tuong_khac = trim($_POST['doi_tuong_khac'] ?? '');

    if ($doi_tuong === 'Mục khác' && !empty($doi_tuong_khac)) {
        $doi_tuong = $doi_tuong_khac;
    }

    $thong_tin_nguoi_benh = trim($_POST['thong_tin_nguoi_benh'] ?? '');
    $mo_ta_su_co = trim($_POST['mo_ta_su_co'] ?? '');
    $tinh_chat_su_co = $_POST['tinh_chat_su_co'] ?? '';
    $muc_do_su_co = $_POST['muc_do_su_co'] ?? '';

     // ========== XỬ LÝ PHÂN LOẠI SỰ CỐ (QUAN TRỌNG) ==========
    // Lấy mảng checkbox được chọn
    $phan_loai_su_co_array = isset($_POST['phan_loai_su_co']) && is_array($_POST['phan_loai_su_co']) 
        ? $_POST['phan_loai_su_co'] 
        : [];
    
    // Lưu vào cột text (dạng chuỗi, ví dụ: "Thuốc, Thiết bị, Hồ sơ")
    $phan_loai_su_co_text = !empty($phan_loai_su_co_array) 
        ? implode(', ', $phan_loai_su_co_array) 
        : null;
    
    // Lưu vào cột JSON (dạng mảng JSON)
    $phan_loai_su_co_json = !empty($phan_loai_su_co_array) 
        ? json_encode($phan_loai_su_co_array, JSON_UNESCAPED_UNICODE) 
        : null;
    
    // DEBUG: Kiểm tra dữ liệu (bỏ comment nếu cần)
    // echo "<pre>";
    // echo "Mảng checkbox: ";
    // print_r($phan_loai_su_co_array);
    // echo "Text: " . ($phan_loai_su_co_text ?? 'NULL') . "\n";
    // echo "JSON: " . ($phan_loai_su_co_json ?? 'NULL') . "\n";
    // echo "</pre>";
    // exit();

    $thong_bao_cap_tren = $_POST['thong_bao_cap_tren'] ?? '';
    $xu_ly_ban_dau = trim($_POST['xu_ly_ban_dau'] ?? '');
    $giai_phap = trim($_POST['giai_phap'] ?? '');
    $ghi_nhan_ho_so = $_POST['ghi_nhan_ho_so'] ?? 'no';

    // Cập nhật database
    $sql = "UPDATE baocao SET 
        nguoibaocao = :nguoibaocao,
        email = :email,
        hinhthuc = :hinhthuc,
        diem_xay_ra_su_co = :diem_xay_ra_su_co,
        diem_xay_ra_su_co_khac = :diem_xay_ra_su_co_khac,
        nhom_su_co = :nhom_su_co,
        thoigian = :thoigian,
        doi_tuong = :doi_tuong,
        doi_tuong_khac = :doi_tuong_khac,
        thong_tin_nguoi_benh = :thong_tin_nguoi_benh,
        mo_ta_su_co = :mo_ta_su_co,
        tinh_chat_su_co = :tinh_chat_su_co,
        muc_do_su_co = :muc_do_su_co,
        phan_loai_su_co = :phan_loai_su_co,
        phan_loai_su_co_json = :phan_loai_su_co_json,
        thong_bao_cap_tren = :thong_bao_cap_tren,
        xu_ly_ban_dau = :xu_ly_ban_dau,
        giai_phap = :giai_phap,
        ghi_nhan_ho_so = :ghi_nhan_ho_so
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':nguoibaocao' => $nguoibaocao,
        ':email' => $email,
        ':hinhthuc' => $hinhthuc,
        ':diem_xay_ra_su_co' => $diem_xay_ra_su_co,
        ':diem_xay_ra_su_co_khac' => $diem_xay_ra_su_co_khac ?: null,
        ':nhom_su_co' => $nhom_su_co,
        ':thoigian' => $thoigian,
        ':doi_tuong' => $doi_tuong,
        ':doi_tuong_khac' => $doi_tuong_khac ?: null,
        ':thong_tin_nguoi_benh' => $thong_tin_nguoi_benh,
        ':mo_ta_su_co' => $mo_ta_su_co,
        ':tinh_chat_su_co' => $tinh_chat_su_co,
        ':muc_do_su_co' => $muc_do_su_co,
        ':phan_loai_su_co' => $phan_loai_su_co_text,
        ':phan_loai_su_co_json' => $phan_loai_su_co_json,
        ':thong_bao_cap_tren' => $thong_bao_cap_tren,
        ':xu_ly_ban_dau' => $xu_ly_ban_dau,
        ':giai_phap' => $giai_phap,
        ':ghi_nhan_ho_so' => $ghi_nhan_ho_so,
        ':id' => $id
    ]);

    if ($result) {
        header("Location: view_detail.php?id=$id&updated=1");
        exit();
    } else {
        $error = "Có lỗi xảy ra khi cập nhật!";
    }
}

// Lấy danh sách phân loại đã chọn (để checked checkbox)
$phan_loai_da_chon = !empty($report['phan_loai_su_co']) ? explode(', ', $report['phan_loai_su_co']) : [];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa báo cáo #<?php echo $id; ?></title>
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header i {
            font-size: 50px;
            margin-bottom: 10px;
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

        .sidebar-menu a:hover {
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

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .form-section h5 {
            margin-bottom: 20px;
            color: #333;
            border-left: 4px solid #dc3545;
            padding-left: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .required::after {
            content: " *";
            color: red;
        }

        .btn-save {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-save:hover {
            opacity: 0.9;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            margin-left: 10px;
        }

        .other-input {
            margin-top: 10px;
            max-width: 400px;
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
            <a href="statistics.php"><i class="fas fa-chart-bar"></i> Thống kê</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="navbar-top">
            <h4><i class="fas fa-edit"></i> Chỉnh sửa báo cáo #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?></h4>
            <div>
                <span class="me-3"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <!-- Thông tin người báo cáo -->
                <div class="form-section">
                    <h5><i class="fas fa-user"></i> Thông tin người báo cáo</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Người báo cáo</label>
                                <input type="text" name="nguoibaocao" class="form-control"
                                    value="<?php echo htmlspecialchars($report['nguoibaocao']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo htmlspecialchars($report['email']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Hình thức báo cáo</label>
                                <select name="hinhthuc" class="form-select" required>
                                    <option value="tự nguyện" <?php echo $report['hinhthuc'] == 'tự nguyện' ? 'selected' : ''; ?>>Tự nguyện</option>
                                    <option value="bắt buộc" <?php echo $report['hinhthuc'] == 'bắt buộc' ? 'selected' : ''; ?>>Bắt buộc</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Địa điểm -->
                <div class="form-section">
                    <h5><i class="fas fa-map-marker-alt"></i> Địa điểm xảy ra sự cố</h5>
                    <div class="row">
                        <?php
                        $locations = [
                            'Khoa Dược',
                            'Khoa Khám bệnh',
                            'Khoa Mắt Tổng Hợp',
                            'Khoa Xét Nghiệm',
                            'Phòng KHTH_QLCL',
                            'Phòng Điều dưỡng',
                            'Phòng Chăm sóc khách hàng',
                            'Phòng Tài chính kế toán'
                        ];
                        $current_location = $report['diem_xay_ra_su_co'];
                        $is_other = !in_array($current_location, $locations) && !empty($current_location);
                        ?>
                        <div class="col-md-6">
                            <?php foreach (array_slice($locations, 0, 4) as $loc): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="diem_xay_ra_su_co"
                                        value="<?php echo $loc; ?>" id="loc_<?php echo $loc; ?>"
                                        <?php echo ($current_location == $loc) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="loc_<?php echo $loc; ?>"><?php echo $loc; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <?php foreach (array_slice($locations, 4) as $loc): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="diem_xay_ra_su_co"
                                        value="<?php echo $loc; ?>" id="loc_<?php echo $loc; ?>"
                                        <?php echo ($current_location == $loc) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="loc_<?php echo $loc; ?>"><?php echo $loc; ?></label>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="diem_xay_ra_su_co"
                                    value="Mục khác" id="loc_khac"
                                    <?php echo $is_other ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="loc_khac">Mục khác:</label>
                            </div>
                            <input type="text" name="diem_xay_ra_su_co_khac" class="form-control other-input"
                                placeholder="Nhập địa điểm cụ thể..."
                                value="<?php echo $is_other ? htmlspecialchars($current_location) : ''; ?>"
                                style="display: <?php echo $is_other ? 'block' : 'none'; ?>;">
                        </div>
                    </div>
                </div>

                <!-- Thông tin sự cố -->
                <div class="form-section">
                    <h5><i class="fas fa-info-circle"></i> Thông tin sự cố</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Nhóm sự cố</label>
                                <select name="nhom_su_co" class="form-select" required>
                                    <option value="Sự cố y khoa" <?php echo $report['nhom_su_co'] == 'Sự cố y khoa' ? 'selected' : ''; ?>>Sự cố y khoa</option>
                                    <option value="Sự cố ngoài y khoa" <?php echo $report['nhom_su_co'] == 'Sự cố ngoài y khoa' ? 'selected' : ''; ?>>Sự cố ngoài y khoa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Thời gian xảy ra</label>
                                <input type="date" name="thoigian" class="form-control"
                                    value="<?php echo $report['thoigian']; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Đối tượng -->
                <div class="form-section">
                    <h5><i class="fas fa-users"></i> Đối tượng xảy ra sự cố</h5>
                    <?php
                    $objects = ['Người bệnh', 'Người nhà', 'Nhân viên y tế'];
                    $current_object = $report['doi_tuong'];
                    $is_object_other = !in_array($current_object, $objects) && !empty($current_object);
                    ?>
                    <?php foreach ($objects as $obj): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="doi_tuong"
                                value="<?php echo $obj; ?>" id="obj_<?php echo $obj; ?>"
                                <?php echo ($current_object == $obj) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="obj_<?php echo $obj; ?>"><?php echo $obj; ?></label>
                        </div>
                    <?php endforeach; ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="doi_tuong"
                            value="Mục khác" id="obj_khac"
                            <?php echo $is_object_other ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="obj_khac">Mục khác:</label>
                    </div>
                    <input type="text" name="doi_tuong_khac" class="form-control other-input"
                        placeholder="Nhập đối tượng cụ thể..."
                        value="<?php echo $is_object_other ? htmlspecialchars($current_object) : ''; ?>"
                        style="display: <?php echo $is_object_other ? 'block' : 'none'; ?>;">
                </div>

                <!-- Mô tả -->
                <div class="form-section">
                    <h5><i class="fas fa-file-alt"></i> Mô tả chi tiết</h5>
                    <div class="form-group">
                        <label>Thông tin người bệnh</label>
                        <textarea name="thong_tin_nguoi_benh" class="form-control" rows="3"><?php echo htmlspecialchars($report['thong_tin_nguoi_benh']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="required">Mô tả sự cố</label>
                        <textarea name="mo_ta_su_co" class="form-control" rows="4" required><?php echo htmlspecialchars($report['mo_ta_su_co']); ?></textarea>
                    </div>
                </div>

                <!-- Tính chất và mức độ -->
                <div class="form-section">
                    <h5><i class="fas fa-chart-line"></i> Tính chất và mức độ sự cố</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Tính chất sự cố</label>
                                <select name="tinh_chat_su_co" class="form-select" required>
                                    <option value="Suýt xảy ra" <?php echo $report['tinh_chat_su_co'] == 'Suýt xảy ra' ? 'selected' : ''; ?>>Suýt xảy ra</option>
                                    <option value="Đã xảy ra" <?php echo $report['tinh_chat_su_co'] == 'Đã xảy ra' ? 'selected' : ''; ?>>Đã xảy ra</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Mức độ sự cố</label>
                                <select name="muc_do_su_co" class="form-select" required>
                                    <option value="NC0" <?php echo $report['muc_do_su_co'] == 'NC0' ? 'selected' : ''; ?>>NC0 - Chưa xảy ra (có nguy cơ)</option>
                                    <option value="NC1" <?php echo $report['muc_do_su_co'] == 'NC1' ? 'selected' : ''; ?>>NC1 - Nhẹ</option>
                                    <option value="NC2" <?php echo $report['muc_do_su_co'] == 'NC2' ? 'selected' : ''; ?>>NC2 - Trung bình</option>
                                    <option value="NC3" <?php echo $report['muc_do_su_co'] == 'NC3' ? 'selected' : ''; ?>>NC3 - Nặng</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phân loại sự cố -->
                <div class="form-section">
                    <h5><i class="fas fa-tags"></i> Phân loại sự cố</h5>

                    <?php
                    // Lấy danh sách phân loại đã chọn
                    $phan_loai_da_chon = [];

                    // Ưu tiên lấy từ cột JSON (sau khi đã đổi sang kiểu JSON)
                    if (!empty($report['phan_loai_su_co_json'])) {
                        // Nếu cột đã là JSON, fetch ra sẽ là mảng PHP tự động
                        if (is_string($report['phan_loai_su_co_json'])) {
                            $phan_loai_da_chon = json_decode($report['phan_loai_su_co_json'], true);
                        } else {
                            $phan_loai_da_chon = $report['phan_loai_su_co_json'];
                        }
                        if (!is_array($phan_loai_da_chon)) {
                            $phan_loai_da_chon = [];
                        }
                    }

                    // Nếu JSON rỗng, lấy từ cột text
                    if (empty($phan_loai_da_chon) && !empty($report['phan_loai_su_co'])) {
                        $phan_loai_da_chon = explode(', ', $report['phan_loai_su_co']);
                    }

                    $categories = [
                        'Quy trình, kỹ thuật, thủ thuật chuyên môn',
                        'Thuốc và dịch truyền',
                        'Thiết bị y tế',
                        'Hồ sơ bệnh án, tài liệu hành chính',
                        'Tai nạn, chấn thương, té ngã',
                        'Xét nghiệm, chẩn đoán hình ảnh',
                        'Nhiễm khuẩn bệnh viện'
                    ];
                    ?>

                    <div class="row">
                        <?php foreach ($categories as $cat): ?>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                        name="phan_loai_su_co[]"
                                        value="<?php echo htmlspecialchars($cat); ?>"
                                        id="cat_<?php echo md5($cat); ?>"
                                        <?php echo in_array($cat, $phan_loai_da_chon) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cat_<?php echo md5($cat); ?>">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Xử lý sự cố -->
                <div class="form-section">
                    <h5><i class="fas fa-tools"></i> Xử lý sự cố</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Thông báo cấp trên</label>
                                <select name="thong_bao_cap_tren" class="form-select" required>
                                    <option value="yes" <?php echo $report['thong_bao_cap_tren'] == 'yes' ? 'selected' : ''; ?>>Đã báo</option>
                                    <option value="no" <?php echo $report['thong_bao_cap_tren'] == 'no' ? 'selected' : ''; ?>>Chưa báo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">Ghi nhận hồ sơ</label>
                                <select name="ghi_nhan_ho_so" class="form-select" required>
                                    <option value="yes" <?php echo $report['ghi_nhan_ho_so'] == 'yes' ? 'selected' : ''; ?>>Có</option>
                                    <option value="no" <?php echo $report['ghi_nhan_ho_so'] == 'no' ? 'selected' : ''; ?>>Không</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Xử lý ban đầu</label>
                        <textarea name="xu_ly_ban_dau" class="form-control" rows="3" required><?php echo htmlspecialchars($report['xu_ly_ban_dau']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="required">Giải pháp phòng ngừa</label>
                        <textarea name="giai_phap" class="form-control" rows="3" required><?php echo htmlspecialchars($report['giai_phap']); ?></textarea>
                    </div>
                </div>

                <div class="text-end">
                    <a href="view_detail.php?id=<?php echo $id; ?>" class="btn-cancel"><i class="fas fa-times"></i> Hủy</a>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Xử lý hiển thị ô nhập cho Địa điểm
        const locRadios = document.querySelectorAll('input[name="diem_xay_ra_su_co"]');
        const locOtherInput = document.querySelector('input[name="diem_xay_ra_su_co_khac"]');

        function toggleLocOther() {
            const selected = document.querySelector('input[name="diem_xay_ra_su_co"]:checked');
            if (selected && selected.value === 'Mục khác') {
                locOtherInput.style.display = 'block';
            } else {
                locOtherInput.style.display = 'none';
            }
        }

        locRadios.forEach(radio => radio.addEventListener('change', toggleLocOther));
        toggleLocOther();

        // Xử lý hiển thị ô nhập cho Đối tượng
        const objRadios = document.querySelectorAll('input[name="doi_tuong"]');
        const objOtherInput = document.querySelector('input[name="doi_tuong_khac"]');

        function toggleObjOther() {
            const selected = document.querySelector('input[name="doi_tuong"]:checked');
            if (selected && selected.value === 'Mục khác') {
                objOtherInput.style.display = 'block';
            } else {
                objOtherInput.style.display = 'none';
            }
        }

        objRadios.forEach(radio => radio.addEventListener('change', toggleObjOther));
        toggleObjOther();
    </script>
</body>

</html>