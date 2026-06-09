<?php  //require_once './config/database.php'; 
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo sự cố</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        }

        .form-label {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .required {
            color: #dc3545;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
        }

        .form-check input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            cursor: pointer;
        }

        .form-check label {
            font-size: 16px;
            color: #333;
            cursor: pointer;
            transition: .2s;
        }

        .form-check:hover label {
            color: #0d6efd;
        }
        .date-input{
            width: 250px;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 15px;
        }

        .date-input:focus{
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,.15);
        }
        .form-answer{
            width:100%;
            border:none;
            border-bottom:1px solid #ccc;
            font-size:15px;
            background:transparent;
        }

        .form-answer:focus{
            outline:none;
            border-bottom:2px solid #673ab7;
        }
        /* 
.other-input{
    display: none;
    margin-left: 35px;
    margin-top: 10px;
    width: 400px;
    border: none;
    border-bottom: 2px solid #0d6efd;
    padding: 6px 0;
    background: transparent;
}

.other-input:focus{
    outline: none;
    border-bottom-color: #084298;
} */
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4>📝 Báo cáo sự cố / Hỗ trợ kỹ thuật</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                                unset($_SESSION['success']); ?></div>
                        <?php endif; ?>

                        <form action="submit_report.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label">Tên bạn</label>
                                    <input type="text" name="reporter_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="reporter_email" class="form-control">
                                </div>
                            <!-- <div class="mb-3">
                                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                                <textarea name="description" rows="5" class="form-control" required></textarea>
                            </div> -->
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Hình thức báo cáo <span class="text-danger">*</span>
                                </label>

                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="hinh_thuc" id="tu_nguyen">
                                        <label class="form-check-label" for="tu_nguyen">
                                            Tự nguyện
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="hinh_thuc" id="bat_buoc">
                                        <label class="form-check-label" for="bat_buoc">
                                            Bắt buộc
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Địa điểm xảy ra sự cố <span class="text-danger">*</span>
                                </label>

                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="duoc">
                                        <label class="form-check-label" for="duoc">
                                            Khoa Dược
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="phongkham">
                                        <label class="form-check-label" for="phongkham">
                                            Khoa Khám bệnh
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="mattonghop">
                                        <label class="form-check-label" for="mattonghop">
                                            Khoa Mắt Tổng Hợp
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="xetnghiem">
                                        <label class="form-check-label" for="xetnghiem">
                                            Khoa Xét Nghiệm
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="khth">
                                        <label class="form-check-label" for="khth">
                                            Phòng KHTH_QLCL
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="dieuduong">
                                        <label class="form-check-label" for="dieuduong">
                                            Phòng Điều dưỡng
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="cskh">
                                        <label class="form-check-label" for="cskh">
                                            Phòng Chăm sóc khách hàng
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="ke_toan">
                                        <label class="form-check-label" for="ke_toan">
                                            Phòng Tài chính kế toán
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="dia_diem" id="muc_khac" value="Khác">
                                        <label class="form-check-label me-3" for="muc_khac">
                                            Mục khác:
                                        </label>
                                        <input type="text"
                                            id="txtKhac"
                                            name="dia_diem_khac"
                                            class="form-control"
                                            style="display:none; max-width:500px;">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Nhóm sự cố <span class="text-danger">*</span>
                                </label>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="nhom" id="y_khoa">
                                        <label class="form-check-label" for="y_khoa">
                                            Sự cố y khoa
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="nhom" id="ngoai_y_khoa">
                                        <label class="form-check-label" for="ngoai_y_khoa">
                                            Sự cố ngoài y khoa
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Thời gian xảy ra sự cố<span class="text-danger">*</span>
                                </label>
                                <div class="form-check mt-3">
                                    <input type="date"
                                        name="ngay_su_co"
                                        class="date-input"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Đối tượng xảy ra sự cố <span class="text-danger">*</span>
                                </label>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="doituong" id="nguoibenh">
                                        <label class="form-check-label" for="nguoibenh">
                                            Người bệnh
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doituong" id="nguoinha">
                                        <label class="form-check-label" for="nguoinha">
                                            Người nhà
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="doituong" id="nhanvien">
                                        <label class="form-check-label" for="nhanvien">
                                            Nhân viên y tế
                                        </label>
                                    </div>

                                     <div class="form-check">
                                        <input class="form-check-input" type="radio" name="doituong" id="muc_khac" value="Khacdt">
                                        <label class="form-check-label me-3" for="muc_khac">
                                            Mục khác:
                                        </label>
                                        <input type="text"
                                            id="txtKhacdt"
                                            name="dia_diem_khac"
                                            class="form-control"
                                            style="display:none; max-width:500px;">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Thông tin người bệnh: Họ và tên; năm sinh; địa chỉ<span class="text-danger">*</span>
                                </label>
                                <div class = "form-check mt-3">
                                <input type="text"
                                    name="thong_tin_benh_nhan"
                                    placeholder="Câu trả lời của bạn"
                                    class="form-answer">
                                </div>
                            </div>
                             <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Mô tả sự cố (tình huống xảy ra sự cố)<span class="text-danger">*</span>
                                </label>
                                <div class = "form-check mt-3">
                                <input type="text"
                                    name="mo_ta_su_co"
                                    placeholder="Câu trả lời của bạn"
                                    class="form-answer">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Tính chất sự cố <span class="text-danger">*</span>
                                </label>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="tinhchat" id="suyt_xay_ra">
                                        <label class="form-check-label" for="suyt_xay_ra">
                                            Suýt xảy ra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tinhchat" id="da_xay_ra">
                                        <label class="form-check-label" for="da_xay_ra">
                                            Đã xảy ra
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Mức độ sự cố <span class="text-danger">*</span>
                                </label>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="mucdo" id="nc0">
                                        <label class="form-check-label" for="nc0">
                                            Chưa xảy ra (NC0) (Chưa xảy ra, có nguy cơ xảy ra sự cố)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mucdo" id="nc1">
                                        <label class="form-check-label" for="nc1">
                                            Nhẹ (NC1) (Đã xảy ra, có tác động hoặc chưa tác động đến người bệnh, không gây nguy hại cho NB)
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="mucdo" id="nc2">
                                        <label class="form-check-label" for="nc2">
                                            Trung bình (NC2) (Đã xảy ra, có gây nguy hại NB phải can thiệp điều trị)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mucdo" id="nc3">
                                        <label class="form-check-label" for="nc3">
                                            Nặng (NC3) (Đã xảy ra, gây nguy hại hoặc tử vong NB)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Phân loại sự cố <span class="text-danger">*</span>
                                </label>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl1" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl1">
                                            Quy trình, kỹ thuật, thủ thuật chuyên môn
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl2" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl2">
                                            Thuốc và dịch truyền
                                        </label>
                                    </div>
                                     <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl3" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl3">
                                            Thiết bị y tế
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl4" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl4">
                                            Hồ sơ bệnh án, tài liệu hành chính
                                        </label>
                                    </div>
                                     <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl5" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl5">
                                            Tai nạn, chấn thương, té ngã
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl6" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl6">
                                            Xét nghiệm, chẩn đoán hình ảnh
                                        </label>
                                    </div>
                                     <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="phanloai" id="pl7" style="margin-right: 10px;">
                                        <label class="form-check-label" for="pl7">
                                            Nhiễm khuẩn bệnh viện
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Thông báo cho cấp trên quản lý trực tiếp<span class="text-danger">*</span>
                                </label>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="thongbao" id="da_bao">
                                        <label class="form-check-label" for="da_bao">
                                            Đã báo
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="thongbao" id="chua_bao">
                                        <label class="form-check-label" for="chua_bao">
                                            Chưa báo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Điều trị/ xử lý ban đầu đã được thực hiện<span class="text-danger">*</span>
                                </label>
                                <div class = "form-check mt-3">
                                <input type="text"
                                    name="dieu_tri_ban_dau"
                                    placeholder="Câu trả lời của bạn"
                                    class="form-answer">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Đề xuất giải pháp phòng ngừa sự cố<span class="text-danger">*</span>
                                </label>
                                <div class = "form-check mt-3">
                                <input type="text"
                                    name="de_xuat_giai_phap"
                                    placeholder="Câu trả lời của bạn"
                                    class="form-answer">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Ghi nhận vào hồ sơ bệnh án/ Giấy tờ liên quan<span class="text-danger">*</span>
                                </label>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="hinh_thuc" id="yes">
                                        <label class="form-check-label" for="yes">
                                            Có
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="hinh_thuc" id="no">
                                        <label class="form-check-label" for="no">
                                            Không
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-normal">
                                    Người báo cáo<span class="text-danger">*</span>
                                </label>
                                <div class = "form-check mt-3">
                                <input type="text"
                                    name="nguoi_bao_cao"
                                    placeholder="Câu trả lời của bạn"
                                    class="form-answer">
                                </div>
                            </div>
                        </div>
                            <button type="submit" class="btn btn-primary">Gửi báo cáo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const radios = document.querySelectorAll('input[name="dia_diem"]');
        const txtKhac = document.getElementById('txtKhac');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Khác') {
                    txtKhac.style.display = 'inline-block';
                    txtKhac.focus();
                } else {
                    txtKhac.style.display = 'none';
                    txtKhac.value = '';
                }

            });
        });
        const radiodt = document.querySelectorAll('input[name="doituong"]');
        const txtKhacdt = document.getElementById('txtKhacdt');
        radiodt.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Khacdt') {
                    txtKhacdt.style.display = 'inline-block';
                    txtKhacdt.focus();
                } else {
                    txtKhacdt.style.display = 'none';
                    txtKhacdt.value = '';
                }

            });
        });
    </script>
</body>

</html>