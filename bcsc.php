<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Document</title>
</head>

<body>
    <div class="container">
        <h1>Form Báo cáo sự cố</h1>
        <form>
            <div class="mb-3">
                <label for="name" class="form-label">Họ và tên</label>
                <input type="text" class="form-control" id="name" placeholder="Nhập họ và tên">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" placeholder="Nhập email">
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="tel" class="form-control" id="phone" placeholder="Nhập số điện thoại">
            </div>
            <div class="form-card">
                <h2 class="question-title">Hình thức báo cáo<span class="required-star">*</span></h2>

                <label class="option-group">
                    <input type="radio" name="hinh_thuc" value="tu_nguyen">
                    <span class="custom-radio"></span>
                    <span class="option-label">Tự nguyện</span>
                </label>

                <label class="option-group">
                    <input type="radio" name="hinh_thuc" value="bat_buoc">
                    <span class="custom-radio"></span>
                    <span class="option-label">Bắt buộc</span>
                </label>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Địa điểm xảy ra sự cố</label>
                <input type="text" class="form-control" id="location" placeholder="Nhập địa điểm">
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Ngày xảy ra sự cố</label>
                <input type="date" class="form-control" id="date">
            </div>
            <div class="mb-3">
                <label for="issueType" class="form-label">Loại sự cố</label>
                <select class="form-select" id="issueType">
                    <option selected>Chọn loại sự cố</option>
                    <option value="1">Sự cố kỹ thuật</option>
                    <option value="2">Sự cố tài khoản</option>
                    <option value="3">Sự cố thanh toán</option>
                    <option value="4">Khác</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả chi tiết</label>
                <textarea class="form-control" id="description" rows="4" placeholder="Mô tả chi tiết về sự cố"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gửi báo cáo</button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>