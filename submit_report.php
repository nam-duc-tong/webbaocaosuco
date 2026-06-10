<?php
// submit_report.php - Xử lý khi người dùng gửi báo cáo từ website
require_once(__DIR__ . '/config/database.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ========== 1. LẤY DỮ LIỆU CƠ BẢN ==========

    // Thông tin người báo cáo
    $nguoibaocao = trim($_POST['nguoibaocao'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hinhthuc = $_POST['hinhthuc'] ?? '';

    // // Địa điểm
    // $diem_xay_ra_su_co = $_POST['diem_xay_ra_su_co'] ?? '';

    // // Xử lý "Mục khác" cho địa điểm
    // if ($diem_xay_ra_su_co == 'Mục khác') {
    //     $diem_xay_ra_su_co = trim($_POST['diem_xay_ra_su_co_khac'] ?? '');
    // }

    // Lấy dữ liệu địa điểm
    $diem_xay_ra_su_co = $_POST['diem_xay_ra_su_co'] ?? '';

    // Xử lý trường hợp "Mục khác"
    if ($diem_xay_ra_su_co === 'Mục khác') {
        $diem_xay_ra_su_co_khac = trim($_POST['diem_xay_ra_su_co_khac'] ?? '');

        // Validate: bắt buộc phải nhập text khi chọn "Mục khác"
        if (empty($diem_xay_ra_su_co_khac)) {
            $errors[] = "Vui lòng nhập địa điểm cụ thể khi chọn 'Mục khác'";
        } else {
            // Gán giá trị nhập vào cho địa điểm
            $diem_xay_ra_su_co = $diem_xay_ra_su_co_khac;
        }
    }
    // Validate chung
    if (empty($diem_xay_ra_su_co)) {
        $errors[] = "Vui lòng chọn hoặc nhập địa điểm xảy ra sự cố";
    }

    // Nhóm sự cố
    $nhom_su_co = $_POST['nhom_su_co'] ?? '';
    $thoigian = $_POST['thoigian'] ?? '';

    // // Đối tượng
    // $doi_tuong = $_POST['doi_tuong'] ?? '';

    // // Xử lý "Mục khác" cho đối tượng
    // if ($doi_tuong == 'Mục khác') {
    //     $doi_tuong = trim($_POST['doi_tuong_khac'] ?? '');
    // }

    // Lấy dữ liệu đối tượng
    $doi_tuong = $_POST['doi_tuong'] ?? '';

    // Xử lý trường hợp "Mục khác"
    if ($doi_tuong === 'Mục khác') {
        $doi_tuong_khac = trim($_POST['doi_tuong_khac'] ?? '');

        // Validate: bắt buộc phải nhập text khi chọn "Mục khác"
        if (empty($doi_tuong_khac)) {
            $errors[] = "Vui lòng nhập đối tượng cụ thể khi chọn 'Mục khác'";
        } else {
            // Gán giá trị nhập vào cho đối tượng
            $doi_tuong = $doi_tuong_khac;
        }
    }

    // Validate chung
    if (empty($doi_tuong)) {
        $errors[] = "Vui lòng chọn hoặc nhập đối tượng xảy ra sự cố";
    }

    // Các trường văn bản
    $thong_tin_nguoi_benh = trim($_POST['thong_tin_nguoi_benh'] ?? '');
    $mo_ta_su_co = trim($_POST['mo_ta_su_co'] ?? '');
    $tinh_chat_su_co = $_POST['tinh_chat_su_co'] ?? '';

    // ========== 2. XỬ LÝ MỨC ĐỘ SỰ CỐ (RADIO) ==========
    // Giá trị gửi lên sẽ là NC0, NC1, NC2, NC3
    $muc_do_su_co = $_POST['muc_do_su_co'] ?? '';

    // Validate: đảm bảo giá trị hợp lệ
    $valid_muc_do = ['NC0', 'NC1', 'NC2', 'NC3'];
    if (!in_array($muc_do_su_co, $valid_muc_do)) {
        $muc_do_su_co = ''; // Nếu không hợp lệ thì để trống
    }

    // ========== 3. XỬ LÝ PHÂN LOẠI SỰ CỐ (CHECKBOX) ==========
    $phan_loai_su_co_array = isset($_POST['phan_loai_su_co']) && is_array($_POST['phan_loai_su_co'])
        ? $_POST['phan_loai_su_co']
        : [];

    // Lưu dạng chuỗi (cho cột text)
    $phan_loai_su_co_string = implode(', ', $phan_loai_su_co_array);

    // Lưu dạng JSON (cho cột JSON) - dùng json_encode
    $phan_loai_su_co_json = json_encode($phan_loai_su_co_array, JSON_UNESCAPED_UNICODE);


    // Sau đó lưu cả 2 vào database

    // ========== 4. CÁC TRƯỜNG XỬ LÝ KHÁC ==========
    $thong_bao_cap_tren = $_POST['thong_bao_cap_tren'] ?? '';
    $xu_ly_ban_dau = trim($_POST['xu_ly_ban_dau'] ?? '');
    $giai_phap = trim($_POST['giai_phap'] ?? '');
    $ghi_nhan_ho_so = $_POST['ghi_nhan_ho_so'] ?? 'no';

    // ========== 5. DEBUG (TẠM THỜI - CÓ THỂ BỎ SAU KHI CHẠY THỬ) ==========
    // Bỏ comment để kiểm tra dữ liệu nhận được
    /*
    echo "<pre>";
    echo "=== DỮ LIỆU NHẬN ĐƯỢC ===\n";
    echo "nguoibaocao: $nguoibaocao\n";
    echo "email: $email\n";
    echo "hinhthuc: $hinhthuc\n";
    echo "diem_xay_ra_su_co: $diem_xay_ra_su_co\n";
    echo "nhom_su_co: $nhom_su_co\n";
    echo "thoigian: $thoigian\n";
    echo "doi_tuong: $doi_tuong\n";
    echo "thong_tin_nguoi_benh: $thong_tin_nguoi_benh\n";
    echo "mo_ta_su_co: $mo_ta_su_co\n";
    echo "tinh_chat_su_co: $tinh_chat_su_co\n";
    echo "muc_do_su_co: $muc_do_su_co\n";
    echo "phan_loai_su_co_array: ";
    print_r($phan_loai_su_co_array);
    echo "phan_loai_su_co_string: $phan_loai_su_co_string\n";
    echo "phan_loai_su_co_json: $phan_loai_su_co_json\n";
    echo "thong_bao_cap_tren: $thong_bao_cap_tren\n";
    echo "xu_ly_ban_dau: $xu_ly_ban_dau\n";
    echo "giai_phap: $giai_phap\n";
    echo "ghi_nhan_ho_so: $ghi_nhan_ho_so\n";
    echo "</pre>";
    exit();
    */

    // ========== 6. VALIDATE DỮ LIỆU BẮT BUỘC ==========
    $errors = [];

    if (empty($nguoibaocao)) {
        $errors[] = "Vui lòng nhập tên người báo cáo";
    }
    if (empty($diem_xay_ra_su_co)) {
        $errors[] = "Vui lòng chọn địa điểm xảy ra sự cố";
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

    // Nếu có lỗi, quay lại form và hiển thị
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_data'] = $_POST;
        header("Location: report_form.php");
        exit();
    }

    // ========== 7. LƯU VÀO DATABASE ==========

    // Cập nhật câu lệnh SQL để bao gồm cột phan_loai_su_co_json (nếu có)
    // Nếu bảng của bạn chưa có cột json, hãy bỏ dòng đó đi

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

        // Xóa session lỗi nếu có
        unset($_SESSION['form_errors']);
        unset($_SESSION['old_data']);

        // Chuyển đến trang cảm ơn
        header("Location: index.php");
        exit();
    } else {
        // Lỗi khi lưu database
        $_SESSION['form_errors'] = ['Có lỗi xảy ra khi lưu dữ liệu, vui lòng thử lại!'];
        $_SESSION['old_data'] = $_POST;
        header("Location: report_form.php");
        exit();
    }
}
