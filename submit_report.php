<?php
// submit_report.php - Xử lý khi người dùng gửi báo cáo từ website
require_once(__DIR__ . '/config/database.php');
require_once(__DIR__ . '/includes/send_mail.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ========== 1. LẤY DỮ LIỆU CƠ BẢN ==========

    // Thông tin người báo cáo
    $nguoibaocao = trim($_POST['nguoibaocao'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hinhthuc = $_POST['hinhthuc'] ?? '';

    // Xử lý địa điểm
    $diem_xay_ra_su_co = $_POST['diem_xay_ra_su_co'] ?? '';

    if ($diem_xay_ra_su_co === 'Mục khác') {
        $diem_xay_ra_su_co_khac = trim($_POST['diem_xay_ra_su_co_khac'] ?? '');
        if (empty($diem_xay_ra_su_co_khac)) {
            $errors[] = "Vui lòng nhập địa điểm cụ thể khi chọn 'Mục khác'";
        } else {
            $diem_xay_ra_su_co = $diem_xay_ra_su_co_khac;
        }
    }
    if (empty($diem_xay_ra_su_co)) {
        $errors[] = "Vui lòng chọn hoặc nhập địa điểm xảy ra sự cố";
    }

    // Nhóm sự cố
    $nhom_su_co = $_POST['nhom_su_co'] ?? '';
    $thoigian = $_POST['thoigian'] ?? '';

    // Xử lý đối tượng
    $doi_tuong = $_POST['doi_tuong'] ?? '';

    if ($doi_tuong === 'Mục khác') {
        $doi_tuong_khac = trim($_POST['doi_tuong_khac'] ?? '');
        if (empty($doi_tuong_khac)) {
            $errors[] = "Vui lòng nhập đối tượng cụ thể khi chọn 'Mục khác'";
        } else {
            $doi_tuong = $doi_tuong_khac;
        }
    }
    if (empty($doi_tuong)) {
        $errors[] = "Vui lòng chọn hoặc nhập đối tượng xảy ra sự cố";
    }

    // Các trường văn bản
    $thong_tin_nguoi_benh = trim($_POST['thong_tin_nguoi_benh'] ?? '');
    $mo_ta_su_co = trim($_POST['mo_ta_su_co'] ?? '');
    $tinh_chat_su_co = $_POST['tinh_chat_su_co'] ?? '';

    // ========== 2. XỬ LÝ MỨC ĐỘ SỰ CỐ ==========
    $muc_do_su_co = $_POST['muc_do_su_co'] ?? '';
    $valid_muc_do = ['NC0', 'NC1', 'NC2', 'NC3'];
    if (!in_array($muc_do_su_co, $valid_muc_do)) {
        $muc_do_su_co = '';
    }

    // ========== 3. XỬ LÝ PHÂN LOẠI SỰ CỐ (CHECKBOX) ==========
    $phan_loai_su_co_array = isset($_POST['phan_loai_su_co']) && is_array($_POST['phan_loai_su_co'])
        ? $_POST['phan_loai_su_co']
        : [];

    $phan_loai_su_co_string = implode(', ', $phan_loai_su_co_array);
    $phan_loai_su_co_json = json_encode($phan_loai_su_co_array, JSON_UNESCAPED_UNICODE);

    // ========== 4. CÁC TRƯỜNG XỬ LÝ KHÁC ==========
    $thong_bao_cap_tren = $_POST['thong_bao_cap_tren'] ?? '';
    $xu_ly_ban_dau = trim($_POST['xu_ly_ban_dau'] ?? '');
    $giai_phap = trim($_POST['giai_phap'] ?? '');
    $ghi_nhan_ho_so = $_POST['ghi_nhan_ho_so'] ?? 'no';

    // ========== 5. VALIDATE DỮ LIỆU ==========
    $errors = [];

    if (empty($nguoibaocao)) {
        $errors[] = "Vui lòng nhập tên người báo cáo";
    }
    if (empty($email)) {
        $errors[] = "Vui lòng nhập email để nhận xác nhận báo cáo!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không đúng định dạng!";
    }
    if (empty($nhom_su_co)) {
        $errors[] = "Vui lòng chọn nhóm sự cố";
    }
    if (empty($thoigian)) {
        $errors[] = "Vui lòng chọn thời gian xảy ra sự cố";
    }
    if (empty($mo_ta_su_co)) {
        $errors[] = "Vui lòng nhập mô tả sự cố";
    }
    if (empty($muc_do_su_co)) {
        $errors[] = "Vui lòng chọn mức độ sự cố";
    }
    if (empty($phan_loai_su_co_array)) {
        $errors[] = "Vui lòng chọn ít nhất một phân loại sự cố";
    }
    if (empty($xu_ly_ban_dau)) {
        $errors[] = "Vui lòng nhập xử lý ban đầu";
    }
    if (empty($giai_phap)) {
        $errors[] = "Vui lòng nhập giải pháp phòng ngừa";
    }

    // Nếu có lỗi, quay lại form
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: report_form.php");
        exit();
    }

    // ========== 6. LƯU VÀO DATABASE ==========
    $sql = "INSERT INTO baocao (
        nguoibaocao, email, hinhthuc, diem_xay_ra_su_co, 
        nhom_su_co, thoigian, doi_tuong, thong_tin_nguoi_benh, 
        mo_ta_su_co, tinh_chat_su_co, muc_do_su_co, 
        phan_loai_su_co, phan_loai_su_co_json,
        thong_bao_cap_tren, xu_ly_ban_dau, giai_phap, ghi_nhan_ho_so
    ) VALUES (
        :nguoibaocao, :email, :hinhthuc, :diem_xay_ra_su_co,
        :nhom_su_co, :thoigian, :doi_tuong, :thong_tin_nguoi_benh,
        :mo_ta_su_co, :tinh_chat_su_co, :muc_do_su_co,
        :phan_loai_su_co, :phan_loai_su_co_json,
        :thong_bao_cap_tren, :xu_ly_ban_dau, :giai_phap, :ghi_nhan_ho_so
    )";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':nguoibaocao' => $nguoibaocao,
        ':email' => $email,
        ':hinhthuc' => $hinhthuc,
        ':diem_xay_ra_su_co' => $diem_xay_ra_su_co,
        ':nhom_su_co' => $nhom_su_co,
        ':thoigian' => $thoigian,
        ':doi_tuong' => $doi_tuong,
        ':thong_tin_nguoi_benh' => $thong_tin_nguoi_benh,
        ':mo_ta_su_co' => $mo_ta_su_co,
        ':tinh_chat_su_co' => $tinh_chat_su_co,
        ':muc_do_su_co' => $muc_do_su_co,
        ':phan_loai_su_co' => $phan_loai_su_co_string,
        ':phan_loai_su_co_json' => $phan_loai_su_co_json,
        ':thong_bao_cap_tren' => $thong_bao_cap_tren,
        ':xu_ly_ban_dau' => $xu_ly_ban_dau,
        ':giai_phap' => $giai_phap,
        ':ghi_nhan_ho_so' => $ghi_nhan_ho_so
    ]);

    if ($result) {
        $report_id = $pdo->lastInsertId();

        // ========== 7. CHUẨN BỊ DỮ LIỆU CHO EMAIL ==========
        $report_data = [
            'diem_xay_ra_su_co' => $diem_xay_ra_su_co,
            'nhom_su_co' => $nhom_su_co,
            'thoigian' => $thoigian,
            'muc_do_su_co' => $muc_do_su_co,
            'phan_loai_su_co' => $phan_loai_su_co_string,
            'mo_ta_su_co' => $mo_ta_su_co
        ];
        // DEBUG: Ghi log để kiểm tra
        error_log("=== BẮT ĐẦU GỬI EMAIL ===");
        error_log("Email người nhận: " . $email);
        error_log("Tên người nhận: " . $nguoibaocao);
        error_log("Mã báo cáo: " . $report_id);

        // ========== 8. GỬI EMAIL XÁC NHẬN ==========
        if (!empty($email)) {
            $email_sent = sendConfirmationEmail($email, $nguoibaocao, $report_id, $report_data);
            if ($email_sent) {
                $_SESSION['email_status'] = 'Đã gửi email xác nhận đến ' . $email;
            } else {
                $_SESSION['email_status'] = 'Không thể gửi email xác nhận (kiểm tra lại email)';
            }
        }
        // ========== THIẾT LẬP THÔNG BÁO ==========
        $_SESSION['success_message'] = "Báo cáo #" . str_pad($report_id, 5, '0', STR_PAD_LEFT) . " đã được gửi thành công!";

        if ($email_sent) {
            $_SESSION['email_status'] = "📧 Email xác nhận đã được gửi đến " . $email;
        } else {
            $_SESSION['warning_message'] = "Không thể gửi email xác nhận, nhưng báo cáo vẫn được lưu!";
        }
        // Xóa session lỗi nếu có
        unset($_SESSION['form_errors']);
        unset($_SESSION['old_data']);

        // Chuyển đến trang cảm ơn
        // header("Location: thank_you.php?id=" . $report_id);
        header("Location: index.php");
        exit();
    } else {
        // Lỗi khi lưu database
        $_SESSION['form_errors'] = ['Có lỗi xảy ra khi lưu dữ liệu, vui lòng thử lại!'];
        $_SESSION['old_data'] = $_POST;
        header("Location: index.php");
        exit();
    }
}
