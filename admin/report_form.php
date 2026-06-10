<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo sự cố - Hệ thống quản lý</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100%;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            z-index: 100;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header i {
            font-size: 50px;
            margin-bottom: 10px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu-item:hover, .sidebar-menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 30px;
        }

        .main-content {
            margin-left: 280px;
            padding: 20px;
        }

        .top-navbar {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-size: 18px;
            border-left: 4px solid #dc3545;
            padding-left: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group label.required::after {
            content: " *";
            color: #dc3545;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hospital-user"></i>
            <h3>Hệ thống quản lý</h3>
            <p>Báo cáo sự cố y tế</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tổng quan</span>
            </a>
            <a href="report_form.php" class="sidebar-menu-item active">
                <i class="fas fa-plus-circle"></i>
                <span>Báo cáo mới</span>
            </a>
            <a href="./view_reports.php" class="sidebar-menu-item">
                <i class="fas fa-list"></i>
                <span>Danh sách báo cáo</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <h2><i class="fas fa-plus-circle"></i> Báo cáo sự cố / Hỗ trợ kỹ thuật</h2>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>

        <div class="form-container">
            <form action="submit_report.php" method="POST">
                <!-- Thông tin người báo cáo -->
                <div class="form-section">
                    <h3>Thông tin người báo cáo</h3>
                    <div class="row">
                        <div class="form-group">
                            <label class="required">Tên bạn</label>
                            <input type="text" name="reporter_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="reporter_email">
                        </div>
                        <div class="form-group">
                            <label class="required">Hình thức báo cáo</label>
                            <div class="radio-group">
                                <label><input type="radio" name="report_type" value="voluntary" required> Tự nguyện</label>
                                <label><input type="radio" name="report_type" value="mandatory"> Bắt buộc</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Địa điểm -->
                <div class="form-section">
                    <h3>Địa điểm xảy ra sự cố</h3>
                    <div class="form-group">
                        <label class="required">Địa điểm *</label>
                        <div class="radio-group">
                            <label><input type="radio" name="location" value="Khoa Dược" required> Khoa Dược</label>
                            <label><input type="radio" name="location" value="Khoa Khám bệnh"> Khoa Khám bệnh</label>
                            <label><input type="radio" name="location" value="Khoa Mắt Tổng Hợp"> Khoa Mắt Tổng Hợp</label>
                            <label><input type="radio" name="location" value="Khoa Xét Nghiệm"> Khoa Xét Nghiệm</label>
                            <label><input type="radio" name="location" value="Phòng KHTH QLCL"> Phòng KHTH QLCL</label>
                            <label><input type="radio" name="location" value="Phòng Điều dưỡng"> Phòng Điều dưỡng</label>
                            <label><input type="radio" name="location" value="Phòng Chăm sóc khách hàng"> Phòng Chăm sóc khách hàng</label>
                            <label><input type="radio" name="location" value="Phòng Tài chính kế toán"> Phòng Tài chính kế toán</label>
                            <label><input type="radio" name="location" value="Mục khác"> Mục khác:</label>
                            <input type="text" name="location_other" placeholder="Vui lòng nhập địa điểm" style="width: 200px;">
                        </div>
                    </div>
                </div>

                <!-- Phân loại sự cố -->
                <div class="form-section">
                    <h3>Phân loại sự cố</h3>
                    <div class="row">
                        <div class="form-group">
                            <label class="required">Nhóm sự cố</label>
                            <div class="radio-group">
                                <label><input type="radio" name="incident_group" value="medical" required> Sự cố y khoa</label>
                                <label><input type="radio" name="incident_group" value="non_medical"> Sự cố ngoài y khoa</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">Thời gian xảy ra sự cố</label>
                            <input type="date" name="incident_date" required>
                        </div>
                    </div>
                </div>

                <!-- Đối tượng -->
                <div class="form-section">
                    <h3>Đối tượng xảy ra sự cố</h3>
                    <div class="form-group">
                        <label class="required">Đối tượng</label>
                        <div class="radio-group">
                            <label><input type="radio" name="affected_subject" value="Người bệnh" required> Người bệnh</label>
                            <label><input type="radio" name="affected_subject" value="Người nhà"> Người nhà</label>
                            <label><input type="radio" name="affected_subject" value="Nhân viên y tế"> Nhân viên y tế</label>
                            <label><input type="radio" name="affected_subject" value="Mục khác"> Mục khác:</label>
                            <input type="text" name="affected_subject_other" placeholder="Vui lòng nhập" style="width: 200px;">
                        </div>
                    </div>
                </div>

                <!-- Thông tin bệnh nhân -->
                <div class="form-section">
                    <h3>Thông tin người bệnh</h3>
                    <div class="form-group">
                        <label>Họ và tên; năm sinh; địa chỉ</label>
                        <textarea name="patient_info" rows="3" placeholder="Câu trả lời của bạn"></textarea>
                    </div>
                </div>

                <!-- Mô tả sự cố -->
                <div class="form-section">
                    <h3>Mô tả sự cố</h3>
                    <div class="form-group">
                        <label class="required">Mô tả sự cố (tình huống xảy ra sự cố)</label>
                        <textarea name="incident_description" rows="4" required placeholder="Câu trả lời của bạn"></textarea>
                    </div>
                </div>

                <!-- Tính chất và mức độ -->
                <div class="form-section">
                    <h3>Tính chất và mức độ sự cố</h3>
                    <div class="row">
                        <div class="form-group">
                            <label class="required">Tính chất sự cố</label>
                            <div class="radio-group">
                                <label><input type="radio" name="incident_nature" value="near_miss" required> Suýt xảy ra</label>
                                <label><input type="radio" name="incident_nature" value="occurred"> Đã xảy ra</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">Mức độ sự cố</label>
                            <div class="radio-group">
                                <label><input type="radio" name="severity_level" value="NC0" required> NC0 (Chưa xảy ra, có nguy cơ)</label>
                                <label><input type="radio" name="severity_level" value="NC1"> NC1 (Nhẹ)</label>
                                <label><input type="radio" name="severity_level" value="NC2"> NC2 (Trung bình)</label>
                                <label><input type="radio" name="severity_level" value="NC3"> NC3 (Nặng)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phân loại chi tiết -->
                <div class="form-section">
                    <h3>Phân loại sự cố</h3>
                    <div class="form-group">
                        <label class="required">Loại sự cố</label>
                        <div class="radio-group">
                            <label><input type="radio" name="incident_category" value="Quy trình, kỹ thuật" required> Quy trình, kỹ thuật</label>
                            <label><input type="radio" name="incident_category" value="Thuốc và dịch truyền"> Thuốc và dịch truyền</label>
                            <label><input type="radio" name="incident_category" value="Thiết bị y tế"> Thiết bị y tế</label>
                            <label><input type="radio" name="incident_category" value="Hồ sơ bệnh án"> Hồ sơ bệnh án</label>
                            <label><input type="radio" name="incident_category" value="Té ngã"> Té ngã</label>
                            <label><input type="radio" name="incident_category" value="Xét nghiệm"> Xét nghiệm, chẩn đoán</label>
                            <label><input type="radio" name="incident_category" value="Nhiễm khuẩn"> Nhiễm khuẩn bệnh viện</label>
                        </div>
                    </div>
                </div>

                <!-- Xử lý -->
                <div class="form-section">
                    <h3>Xử lý sự cố</h3>
                    <div class="form-group">
                        <label class="required">Thông báo cho cấp trên quản lý trực tiếp</label>
                        <div class="radio-group">
                            <label><input type="radio" name="reported_to_superior" value="yes" required> Đã báo</label>
                            <label><input type="radio" name="reported_to_superior" value="no"> Chưa báo</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">Điều trị/ xử lý ban đầu đã được thực hiện</label>
                        <textarea name="initial_treatment" rows="3" required placeholder="Câu trả lời của bạn"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="required">Đề xuất giải pháp phòng ngừa sự cố</label>
                        <textarea name="preventive_solution" rows="3" required placeholder="Câu trả lời của bạn"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="required">Ghi nhận vào hồ sơ bệnh án/ Giấy tờ liên quan</label>
                        <div class="radio-group">
                            <label><input type="radio" name="documented_in_record" value="yes" required> Có</label>
                            <label><input type="radio" name="documented_in_record" value="no"> Không</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Gửi báo cáo
                </button>
            </form>
        </div>
    </div>
</body>
</html>