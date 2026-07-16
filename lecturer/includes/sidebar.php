<ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="sidebar-brand-text mx-3">CỔNG GIẢNG VIÊN</div>
    </a>
    <hr class="sidebar-divider my-0">
    
    <?php $page = $_GET['page'] ?? 'dashboard'; ?>

    <li class="nav-item <?= ($page == 'dashboard') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=dashboard">
            <i class="fas fa-fw fa-tachometer-alt"></i><span>Tổng quan</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Nghiệp vụ Đào tạo</div>

    <li class="nav-item <?= (in_array($page, ['grades', 'grade_detail'])) ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=grades">
            <i class="fas fa-fw fa-edit"></i><span>Quản lý Lớp & Nhập điểm</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="sidebar-heading">Cá nhân</div>

    <li class="nav-item <?= ($page == 'profile') ? 'active' : '' ?>">
        <a class="nav-link" href="index.php?page=profile">
            <i class="fas fa-fw fa-id-badge"></i><span>Hồ sơ Giảng viên</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>