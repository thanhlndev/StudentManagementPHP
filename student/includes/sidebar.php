<ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="sidebar-brand-text mx-3">CỔNG SINH VIÊN</div>
    </a>

    <hr class="sidebar-divider my-0">
    <?php $page = $_GET['page'] ?? 'dashboard'; ?>

    <li class="nav-item <?= ($page == 'dashboard') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Tổng quan</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Học tập</div>

    <li class="nav-item <?= ($page == 'course_classes') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=course_classes">
            <i class="fas fa-fw fa-edit"></i>
            <span>Đăng ký Học phần</span>
        </a>
    </li>

    <li class="nav-item <?= ($page == 'grades') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=grades">
            <i class="fas fa-fw fa-chart-bar"></i>
            <span>Kết quả Học tập</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Cá nhân</div>

    <li class="nav-item <?= ($page == 'profile') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=profile">
            <i class="fas fa-fw fa-id-card"></i>
            <span>Hồ sơ Cá nhân</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>