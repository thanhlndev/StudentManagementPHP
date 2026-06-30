<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đang phát triển - Hệ thống Đăng ký học</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .maintenance-icon {
            font-size: 4rem;
            color: #f6c23e;
            /* Màu vàng cảnh báo của Bootstrap/SB Admin */
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5 text-center">
                            <div class="maintenance-icon">
                                <i class="fas fa-tools animation-pulse"></i>
                            </div>
                            <h1 class="h4 text-gray-900 mb-3 font-weight-bold">Tính năng đang phát triển!</h1>
                            <p class="text-muted mb-4">
                                Chức năng <strong>Khôi phục mật khẩu</strong> hiện đang trong quá trình xây dựng và hoàn
                                thiện nhằm mang lại trải nghiệm bảo mật tốt nhất.
                            </p>
                            <div class="alert alert-info small" role="alert">
                                <i class="fas fa-info-circle"></i> Nếu bạn không thể đăng nhập, vui lòng liên hệ
                                <strong>Phòng Đào tạo</strong> hoặc <strong>Quản trị viên</strong> để được hỗ trợ cấp
                                lại mật khẩu.
                            </div>
                            <hr>
                            <a href="login.php" class="btn btn-primary btn-user btn-block mt-4">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại trang Đăng nhập
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>