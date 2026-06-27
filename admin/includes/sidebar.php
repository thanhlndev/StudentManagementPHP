<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-graduation-cap"></i></div>
                <div class="sidebar-brand-text mx-3">Quản lý sinh viên</div>
        </a>
        <hr class="sidebar-divider my-0">

        <?php $page = $_GET['page'] ?? 'dashboard'; ?>

        <li class="nav-item <?= ($page == 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Tổng
                                quan</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Quản lý Dữ liệu</div>

        <li class="nav-item <?= ($page == 'faculties') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=faculties"><i class="fas fa-fw fa-building"></i><span>Khoa</span></a></li>
        <li class="nav-item <?= ($page == 'courses') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=courses"><i class="fas fa-fw fa-book"></i><span>Môn học phần</span></a>
        </li>
        <li class="nav-item <?= ($page == 'classes') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=classes"><i class="fas fa-fw fa-users"></i><span>Lớp hành chính</span></a>
        </li>
        <li class="nav-item <?= ($page == 'course_classes') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=course_classes"><i class="fas fa-fw fa-chalkboard"></i><span>Lớp học
                                phần</span></a>
        </li>
        <li class="nav-item <?= ($page == 'grades') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=grades"><i class="fas fa-fw fa-list-ol"></i><span>Bảng điểm lớp</span></a>
        </li>
        <li class="nav-item <?= ($page == 'lecturers') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=lecturers"><i class="fas fa-fw fa-chalkboard-teacher"></i><span>Giảng
                                viên</span></a>
        </li>
        <li class="nav-item <?= ($page == 'semesters') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=semesters"><i class="fas fa-calendar-alt"></i><span>Học kỳ</span></a></li>
        <li class="nav-item <?= ($page == 'students') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=students"><i class="fas fa-user-graduate"></i><span>Sinh viên</span></a>
        </li>
        <li class="nav-item <?= ($page == 'rooms') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=rooms"><i class="fas fa-user-home"></i><span>Phòng học</span></a>
        </li>
        <li class="nav-item <?= ($page == 'class_schedules') ? 'active' : '' ?>"><a class="nav-link"
                        href="index.php?page=class_schedules"><i class="fas fa-user-home"></i><span>Quản lý thời gian
                                cho LHP</span></a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Cấu hình Hệ thống</div>
        <li class="nav-item <?= ($page == 'accounts') ? 'active' : '' ?>">
                <a class="nav-link" href="index.php?page=accounts">
                        <i class="fas fa-fw fa-user-shield"></i><span>Quản lý Tài khoản</span>
                </a>
        </li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
</ul>