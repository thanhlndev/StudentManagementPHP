<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=profile">
                        <span class="mr-2 text-gray-800 font-weight-bold">
                            Sinh viên: <?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>
                        </span>
                        <i class="fas fa-user-circle fa-lg text-info"></i>
                    </a>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>

                <li class="nav-item">
                    <a class="nav-link text-danger font-weight-bold" href="<?= BASE_URL ?>auth/logout.php" onclick="return confirm('Bạn chắc chắn muốn thoát khỏi phiên đăng nhập?');">
                        <i class="fas fa-sign-out-alt fa-fw mr-1"></i> Đăng xuất
                    </a>
                </li>

            </ul>
        </nav>