<?php
// index.php - Trang chủ (form báo cáo)
session_start();
require_once(__DIR__ . '/config/database.php');

// Lấy thông báo từ session (nếu có)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$warning_message = $_SESSION['warning_message'] ?? null;
$email_status = $_SESSION['email_status'] ?? null;

// Xóa session sau khi lấy
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
unset($_SESSION['warning_message']);
unset($_SESSION['email_status']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo sự cố y khoa - Hệ thống quản lý chất lượng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        /* Container chính */
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .form-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px 40px;
            color: white;
            position: relative;
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .form-header .badge-header {
            position: absolute;
            top: 30px;
            right: 40px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
        }

        /* Body */
        .form-body {
            padding: 40px;
        }

        /* Các section */
        .form-section {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            transition: box-shadow 0.3s;
        }

        .form-section:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #dc3545;
            font-size: 20px;
        }

        /* Form groups */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            color: #333;
            font-size: 14px;
        }

        .form-group label.required::after {
            content: " *";
            color: #dc3545;
        }

        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Radio/Checkbox groups */
        .radio-group, .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .radio-item, .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-item input, .checkbox-item input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .radio-item label, .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }

        /* Row 2 cột */
        .row-2cols {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Toast message */
        .toast-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            animation: slideInRight 0.5s ease;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .toast-error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .toast-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .toast-content {
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .toast-close {
            position: absolute;
            right: 12px;
            top: 12px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.6;
        }

        /* Button submit */
        .btn-submit {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            margin-right: 8px;
        }

        /* Loading spinner */
        .btn-submit.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-body {
                padding: 24px;
            }
            .row-2cols {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .form-header .badge-header {
                position: static;
                margin-top: 15px;
                display: inline-block;
            }
            .toast-message {
                left: 20px;
                right: 20px;
                min-width: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notifications -->
    <?php if($success_message): ?>
    <div class="toast-message" id="toastMessage">
        <div class="toast-content toast-success">
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            <i class="fas fa-check-circle"></i> 
            <strong>Thành công!</strong> <?php echo htmlspecialchars($success_message); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if($error_message): ?>
    <div class="toast-message" id="toastMessage">
        <div class="toast-content toast-error">
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Lỗi!</strong> <?php echo htmlspecialchars($error_message); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if($email_status): ?>
    <div class="toast-message" id="toastMessage">
        <div class="toast-content toast-info" style="background: #d1ecf1; border-left-color: #17a2b8; color: #0c5460;">
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            <i class="fas fa-envelope"></i> 
            <?php echo htmlspecialchars($email_status); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-exclamation-triangle"></i> Báo cáo sự cố y khoa</h1>
            <p>Vui lòng điền đầy đủ thông tin chi tiết về sự cố</p>
            <div class="badge-header">
                <i class="fas fa-flag-checkered"></i> Báo cáo bắt buộc theo Thông tư 43/2018/TT-BYT
            </div>
        </div>

        <div class="form-body">
            <form action="submit_report.php" method="POST" id="reportForm">
                
                <!-- ========== PHẦN 1: THÔNG TIN NGƯỜI BÁO CÁO ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user-md"></i>
                        <span>Thông tin người báo cáo</span>
                    </div>
                    <div class="row-2cols">
                        <div class="form-group">
                            <label class="required">Họ và tên người báo cáo</label>
                            <input type="text" name="nguoibaocao" class="form-control" placeholder="Nhập họ và tên" required>
                        </div>
                        <div class="form-group">
                            <label class="required">Email (để nhận xác nhận)</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Hình thức báo cáo</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="hinhthuc" value="tự nguyện" id="tunguyen" checked>
                                <label for="tunguyen">Tự nguyện</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="hinhthuc" value="bắt buộc" id="batbuoc">
                                <label for="batbuoc">Bắt buộc</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== PHẦN 2: ĐỊA ĐIỂM VÀ NHÓM SỰ CỐ ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Địa điểm và phân loại sự cố</span>
                    </div>
                    <div class="row-2cols">
                        <div class="form-group">
                            <label class="required">Địa điểm xảy ra sự cố</label>
                            <div class="radio-group" style="flex-direction: column; gap: 10px;">
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Khoa Dược" id="khoa_duoc">
                                    <label for="khoa_duoc">Khoa Dược</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Khoa Khám bệnh" id="khoa_kham">
                                    <label for="khoa_kham">Khoa Khám bệnh</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Khoa Mắt Tổng Hợp" id="khoa_mat">
                                    <label for="khoa_mat">Khoa Mắt Tổng Hợp</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Khoa Xét Nghiệm" id="khoa_xn">
                                    <label for="khoa_xn">Khoa Xét Nghiệm</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Phòng KHTH_QLCL" id="phong_khth">
                                    <label for="phong_khth">Phòng KHTH_QLCL</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Phòng Điều dưỡng" id="phong_dd">
                                    <label for="phong_dd">Phòng Điều dưỡng</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Phòng Chăm sóc khách hàng" id="phong_cskh">
                                    <label for="phong_cskh">Phòng Chăm sóc khách hàng</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Phòng Tài chính kế toán" id="phong_tc">
                                    <label for="phong_tc">Phòng Tài chính kế toán</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="diem_xay_ra_su_co" value="Mục khác" id="diem_khac">
                                    <label for="diem_khac">Mục khác</label>
                                </div>
                            </div>
                            <div id="otherLocationDiv" style="display: none; margin-top: 12px;">
                                <input type="text" name="diem_xay_ra_su_co_khac" class="form-control" placeholder="Vui lòng nhập địa điểm cụ thể">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">Nhóm sự cố</label>
                            <div class="radio-group" style="flex-direction: column; gap: 10px;">
                                <div class="radio-item">
                                    <input type="radio" name="nhom_su_co" value="Sự cố y khoa" id="ykhoa">
                                    <label for="ykhoa">Sự cố y khoa</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="nhom_su_co" value="Sự cố ngoài y khoa" id="ngoaiykhoa">
                                    <label for="ngoaiykhoa">Sự cố ngoài y khoa</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Thời gian xảy ra sự cố</label>
                        <input type="date" name="thoigian" class="form-control" required style="width: 250px;">
                    </div>
                </div>

                <!-- ========== PHẦN 3: ĐỐI TƯỢNG XẢY RA SỰ CỐ ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-users"></i>
                        <span>Đối tượng xảy ra sự cố</span>
                    </div>
                    <div class="form-group">
                        <label class="required">Đối tượng</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="doi_tuong" value="Người bệnh" id="nguoibenh">
                                <label for="nguoibenh">Người bệnh</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="doi_tuong" value="Người nhà" id="nguoinha">
                                <label for="nguoinha">Người nhà</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="doi_tuong" value="Nhân viên y tế" id="nhanvien">
                                <label for="nhanvien">Nhân viên y tế</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="doi_tuong" value="Mục khác" id="doituong_khac">
                                <label for="doituong_khac">Mục khác</label>
                            </div>
                        </div>
                        <div id="otherObjectDiv" style="display: none; margin-top: 12px;">
                            <input type="text" name="doi_tuong_khac" class="form-control" placeholder="Vui lòng nhập đối tượng cụ thể">
                        </div>
                    </div>
                </div>

                <!-- ========== PHẦN 4: THÔNG TIN BỆNH NHÂN ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-notes-medical"></i>
                        <span>Thông tin người bệnh</span>
                    </div>
                    <div class="form-group">
                        <label>Thông tin người bệnh (Họ tên, năm sinh, địa chỉ)</label>
                        <textarea name="thong_tin_nguoi_benh" class="form-control" rows="3" placeholder="Nhập thông tin người bệnh..."></textarea>
                    </div>
                </div>

                <!-- ========== PHẦN 5: MÔ TẢ SỰ CỐ ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-file-alt"></i>
                        <span>Mô tả sự cố</span>
                    </div>
                    <div class="form-group">
                        <label class="required">Mô tả chi tiết sự cố</label>
                        <textarea name="mo_ta_su_co" class="form-control" rows="4" placeholder="Mô tả cụ thể diễn biến sự cố..." required></textarea>
                    </div>
                </div>

                <!-- ========== PHẦN 6: TÍNH CHẤT VÀ MỨC ĐỘ ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-chart-line"></i>
                        <span>Tính chất và mức độ sự cố</span>
                    </div>
                    <div class="row-2cols">
                        <div class="form-group">
                            <label class="required">Tính chất sự cố</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="tinh_chat_su_co" value="Suýt xảy ra" id="suytxayra">
                                    <label for="suytxayra">Suýt xảy ra</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="tinh_chat_su_co" value="Đã xảy ra" id="daxayra">
                                    <label for="daxayra">Đã xảy ra</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">Mức độ sự cố</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="muc_do_su_co" value="NC0" id="nc0">
                                    <label for="nc0">NC0 - Chưa xảy ra (có nguy cơ)</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="muc_do_su_co" value="NC1" id="nc1">
                                    <label for="nc1">NC1 - Nhẹ</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="muc_do_su_co" value="NC2" id="nc2">
                                    <label for="nc2">NC2 - Trung bình</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="muc_do_su_co" value="NC3" id="nc3">
                                    <label for="nc3">NC3 - Nặng</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== PHẦN 7: PHÂN LOẠI SỰ CỐ ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-tags"></i>
                        <span>Phân loại sự cố</span>
                    </div>
                    <div class="row-2cols">
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Quy trình, kỹ thuật, thủ thuật chuyên môn" id="pl1">
                            <label for="pl1">Quy trình, kỹ thuật, thủ thuật chuyên môn</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Thuốc và dịch truyền" id="pl2">
                            <label for="pl2">Thuốc và dịch truyền</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Thiết bị y tế" id="pl3">
                            <label for="pl3">Thiết bị y tế</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Hồ sơ bệnh án, tài liệu hành chính" id="pl4">
                            <label for="pl4">Hồ sơ bệnh án, tài liệu hành chính</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Tai nạn, chấn thương, té ngã" id="pl5">
                            <label for="pl5">Tai nạn, chấn thương, té ngã</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Xét nghiệm, chẩn đoán hình ảnh" id="pl6">
                            <label for="pl6">Xét nghiệm, chẩn đoán hình ảnh</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="phan_loai_su_co[]" value="Nhiễm khuẩn bệnh viện" id="pl7">
                            <label for="pl7">Nhiễm khuẩn bệnh viện</label>
                        </div>
                    </div>
                </div>

                <!-- ========== PHẦN 8: XỬ LÝ SỰ CỐ ========== -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-tools"></i>
                        <span>Xử lý sự cố</span>
                    </div>
                    <div class="form-group">
                        <label class="required">Thông báo cho cấp trên quản lý trực tiếp</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="thong_bao_cap_tren" value="yes" id="dabao">
                                <label for="dabao">Đã báo</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="thong_bao_cap_tren" value="no" id="chuabao">
                                <label for="chuabao">Chưa báo</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Điều trị / xử lý ban đầu đã được thực hiện</label>
                        <textarea name="xu_ly_ban_dau" class="form-control" rows="3" placeholder="Mô tả các biện pháp đã xử lý ngay khi phát hiện sự cố..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="required">Đề xuất giải pháp phòng ngừa sự cố</label>
                        <textarea name="giai_phap" class="form-control" rows="3" placeholder="Đề xuất các giải pháp để phòng ngừa sự cố tương tự..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="required">Ghi nhận vào hồ sơ bệnh án / Giấy tờ liên quan</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="ghi_nhan_ho_so" value="yes" id="ghinhanc">
                                <label for="ghinhanc">Có ghi nhận</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="ghi_nhan_ho_so" value="no" id="ghinhank">
                                <label for="ghinhank">Chưa ghi nhận</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> GỬI BÁO CÁO
                </button>

            </form>
        </div>
    </div>

    <script>
        // Tự động ẩn toast sau 5 giây
        setTimeout(function() {
            const toast = document.getElementById('toastMessage');
            if (toast) {
                toast.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(function() {
                    if (toast && toast.remove) toast.remove();
                }, 500);
            }
        }, 5000);

        // Xử lý hiển thị ô nhập cho "Mục khác" - Địa điểm
        const radioDiemKhac = document.getElementById('diem_khac');
        const otherLocationDiv = document.getElementById('otherLocationDiv');
        const allDiemRadios = document.querySelectorAll('input[name="diem_xay_ra_su_co"]');
        const otherLocationInput = document.querySelector('input[name="diem_xay_ra_su_co_khac"]');

        function toggleOtherLocation() {
            if (radioDiemKhac && radioDiemKhac.checked) {
                otherLocationDiv.style.display = 'block';
                if (otherLocationInput) otherLocationInput.required = true;
            } else {
                otherLocationDiv.style.display = 'none';
                if (otherLocationInput) {
                    otherLocationInput.required = false;
                    otherLocationInput.value = '';
                }
            }
        }

        if (allDiemRadios.length) {
            allDiemRadios.forEach(radio => radio.addEventListener('change', toggleOtherLocation));
        }
        toggleOtherLocation();

        // Xử lý hiển thị ô nhập cho "Mục khác" - Đối tượng
        const radioDoiTuongKhac = document.getElementById('doituong_khac');
        const otherObjectDiv = document.getElementById('otherObjectDiv');
        const allDoiTuongRadios = document.querySelectorAll('input[name="doi_tuong"]');
        const otherObjectInput = document.querySelector('input[name="doi_tuong_khac"]');

        function toggleOtherObject() {
            if (radioDoiTuongKhac && radioDoiTuongKhac.checked) {
                otherObjectDiv.style.display = 'block';
                if (otherObjectInput) otherObjectInput.required = true;
            } else {
                otherObjectDiv.style.display = 'none';
                if (otherObjectInput) {
                    otherObjectInput.required = false;
                    otherObjectInput.value = '';
                }
            }
        }

        if (allDoiTuongRadios.length) {
            allDoiTuongRadios.forEach(radio => radio.addEventListener('change', toggleOtherObject));
        }
        toggleOtherObject();

        // Xử lý submit form
        document.getElementById('reportForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<span class="spinner"></span> ĐANG GỬI BÁO CÁO...';
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>