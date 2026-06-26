-- Xóa database cũ nếu tồn tại và tạo mới
DROP DATABASE IF EXISTS qlsv;
CREATE DATABASE qlsv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlsv;
-- 1. Bảng Accounts
CREATE TABLE Accounts (
    username VARCHAR(20) PRIMARY KEY,
    -- Mã SV hoặc Mã GV
    password VARCHAR(255) NULL,
    -- Mật khẩu đã mã hóa (hashed)
    role VARCHAR(20) NOT NULL,
    -- 'Student', 'Lecturer', 'Admin'
    isActive BOOLEAN DEFAULT 1,
    -- Trạng thái khóa/mở tài khoản
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO `accounts` (
        `username`,
        `password`,
        `role`,
        `isActive`,
        `createdAt`
    )
VALUES (
        'admin',
        '$2y$10$O/ua.XOMkho/mPQEVsoy1.IV0YopQl.WbrTj1sxnSxXjEwVZ9uv6C',
        'Admin',
        1,
        '2026-06-24 14:48:48'
    ),
    (
        'gv01',
        '$2y$10$wOUI.EqI8corfdduw7XLk.M3NWMqYUzrrMTQcKmCGFi4ZdzKEHYUO',
        'lecturer',
        1,
        '2026-06-24 14:43:33'
    ),
    (
        'gv02',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv03',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv04',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv05',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv06',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv07',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv08',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv09',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'gv10',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'lecturer',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv01',
        '$2y$10$LVhLKuxsQLw1cDTyFQ..i.qBvOrrJrwK97.i36VV8bejaGhCAaTzy',
        'Student',
        1,
        '2026-06-24 14:43:33'
    ),
    (
        'sv02',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv03',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv04',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv05',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv06',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv07',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv08',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv09',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    ),
    (
        'sv10',
        '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
        'Student',
        1,
        '2026-06-24 16:24:05'
    );
-- 2. Bảng Faculties
CREATE TABLE Faculties (
    maKhoa VARCHAR(20) PRIMARY KEY,
    tenKhoa VARCHAR(100) NOT NULL,
    diaChi VARCHAR(200),
    email VARCHAR(100),
    sdt VARCHAR(20)
);
-- 2. Chèn 10 dòng vào Faculties
INSERT INTO Faculties (maKhoa, tenKhoa, diaChi, email, sdt)
VALUES (
        'CNTT',
        'Công nghệ thông tin',
        'Tòa nhà A, Tầng 2',
        'cntt@university.edu.vn',
        '0243111222'
    ),
    (
        'KTE',
        'Kinh tế và Quản trị',
        'Tòa nhà B, Tầng 1',
        'kinhte@university.edu.vn',
        '0243111333'
    ),
    (
        'NN',
        'Ngoại ngữ',
        'Tòa nhà C, Tầng 3',
        'ngoaingu@university.edu.vn',
        '0243111444'
    ),
    (
        'KHUD',
        'Khoa học ứng dụng',
        'Tòa nhà D, Tầng 1',
        'khud@university.edu.vn',
        '0243111555'
    ),
    (
        'XDTD',
        'Xây dựng và Tự động hóa',
        'Tòa nhà E, Tầng 2',
        'xdtd@university.edu.vn',
        '0243111666'
    ),
    (
        'luat',
        'Luật học',
        'Tòa nhà F, Tầng 4',
        'luat@university.edu.vn',
        '0243111777'
    ),
    (
        'DL',
        'Du lịch và Khách sạn',
        'Tòa nhà G, Tầng 1',
        'dulich@university.edu.vn',
        '0243111888'
    ),
    (
        'YDUOC',
        'Y Dược',
        'Tòa nhà H, Tầng 5',
        'yduoc@university.edu.vn',
        '0243111999'
    ),
    (
        'MT',
        'Môi trường',
        'Tòa nhà I, Tầng 2',
        'moitruong@university.edu.vn',
        '0243111000'
    ),
    (
        'QLCC',
        'Quản lý công',
        'Tòa nhà J, Tầng 3',
        'qlcc@university.edu.vn',
        '0243111111'
    );
-- 3. Bảng Lecturers (Phải tạo trước bảng Classes và CourseClasses)
CREATE TABLE Lecturers (
    maGV VARCHAR(20) PRIMARY KEY,
    hoTenGV VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    sdt VARCHAR(20)
);
-- 3. Chèn 10 dòng vào Lecturers
INSERT INTO Lecturers (maGV, hoTenGV, email, sdt)
VALUES (
        'gv01',
        'Nguyễn Văn A',
        'nguyenvana@university.edu.vn',
        '0901234567'
    ),
    (
        'gv02',
        'Trần Thị B',
        'tranthib@university.edu.vn',
        '0902345678'
    ),
    (
        'gv03',
        'Lê Hoàng C',
        'lehoangc@university.edu.vn',
        '0903456789'
    ),
    (
        'gv04',
        'Phạm Minh D',
        'phamminhd@university.edu.vn',
        '0904567890'
    ),
    (
        'gv05',
        'Hoàng Trung E',
        'hoangtrunge@university.edu.vn',
        '0905678901'
    ),
    (
        'gv06',
        'Vũ Thị F',
        'vuthif@university.edu.vn',
        '0906789012'
    ),
    (
        'gv07',
        'Đặng Văn G',
        'dangvang@university.edu.vn',
        '0907890123'
    ),
    (
        'gv08',
        'Bùi Minh H',
        'buiminhh@university.edu.vn',
        '0908901234'
    ),
    (
        'gv09',
        'Ngô Thanh I',
        'ngothanhi@university.edu.vn',
        '0909012345'
    ),
    (
        'gv10',
        'Đỗ Đức J',
        'doducj@university.edu.vn',
        '0900123456'
    );
-- trong thực tế nên tách gv ra nhiều loại(gv giảng dạy, gv cố vấn, v.v)
-- 4. Bảng Classes
CREATE TABLE Classes (
    maLop VARCHAR(20) PRIMARY KEY,
    tenLop VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    maKhoa VARCHAR(20) NOT NULL,
    maGV VARCHAR(20) NOT NULL,
    CONSTRAINT FK_Class_Faculty FOREIGN KEY (maKhoa) REFERENCES Faculties(maKhoa),
    CONSTRAINT FK_Class_lecturer FOREIGN KEY (maGV) REFERENCES Lecturers(maGV)
);
-- 5. Bảng Students
CREATE TABLE Students (
    maSV VARCHAR(20) PRIMARY KEY,
    hoTen VARCHAR(100) NOT NULL,
    gioiTinh VARCHAR(10),
    namSinh INT,
    diaChi VARCHAR(200),
    email VARCHAR(100),
    sdt VARCHAR(20),
    maLop VARCHAR(20) NOT NULL,
    CONSTRAINT FK_Student_Class FOREIGN KEY (maLop) REFERENCES Classes(maLop)
);
-- 6. Bảng Courses
CREATE TABLE Courses (
    maMH VARCHAR(20) PRIMARY KEY,
    tenMH VARCHAR(100) NOT NULL,
    soTinChi INT DEFAULT 3,
    maKhoa VARCHAR(20) NOT NULL,
    CONSTRAINT FK_Course_Faculty FOREIGN KEY (maKhoa) REFERENCES Faculties(maKhoa)
);
-- 6. Chèn 10 dòng vào Courses
INSERT INTO Courses (maMH, tenMH, soTinChi, maKhoa)
VALUES ('CS101', 'Cơ sở dữ liệu', 3, 'CNTT'),
    ('CS102', 'Lập trình hướng đối tượng', 4, 'CNTT'),
    ('EC201', 'Kinh tế vĩ mô', 3, 'KTE'),
    ('BA202', 'Quản trị học cơ bản', 3, 'KTE'),
    ('EN101', 'Tiếng Anh giao tiếp 1', 2, 'NN'),
    ('CH301', 'Hóa học đại cương', 3, 'KHUD'),
    (
        'AU401',
        'Hệ thống điều khiển tự động',
        4,
        'XDTD'
    ),
    ('LW501', 'Luật đại cương', 2, 'luat'),
    ('TO601', 'Tổng quan ngành du lịch', 3, 'DL'),
    ('MA101', 'Toán cao cấp A1', 4, 'KHUD');
-- 7. Bảng Semesters
CREATE TABLE Semesters (
    maHK VARCHAR(20) PRIMARY KEY,
    tenHK VARCHAR(50) NOT NULL
);
-- 7. Chèn 10 dòng vào Semesters
INSERT INTO Semesters (maHK, tenHK)
VALUES ('HK2023.1', 'Học kỳ 1 - Năm học 2023-2024'),
    ('HK2023.2', 'Học kỳ 2 - Năm học 2023-2024'),
    ('HK2023.3', 'Học kỳ phụ - Năm học 2023-2024'),
    ('HK2024.1', 'Học kỳ 1 - Năm học 2024-2025'),
    ('HK2024.2', 'Học kỳ 2 - Năm học 2024-2025'),
    ('HK2024.3', 'Học kỳ phụ - Năm học 2024-2025'),
    ('HK2025.1', 'Học kỳ 1 - Năm học 2025-2026'),
    ('HK2025.2', 'Học kỳ 2 - Năm học 2025-2026'),
    ('HK2026.1', 'Học kỳ 1 - Năm học 2026-2027'),
    ('HK2026.2', 'Học kỳ 2 - Năm học 2026-2027');
-- 8. Bảng CourseClasses
CREATE TABLE CourseClasses (
    maLHP INT AUTO_INCREMENT PRIMARY KEY,
    maMH VARCHAR(20) NOT NULL,
    maHK VARCHAR(20) NOT NULL,
    maGV VARCHAR(20) NOT NULL,
    isLocked TINYINT(1) DEFAULT 0,
    CONSTRAINT FK_CC_Course FOREIGN KEY (maMH) REFERENCES Courses(maMH),
    CONSTRAINT FK_CC_Semester FOREIGN KEY (maHK) REFERENCES Semesters(maHK),
    CONSTRAINT FK_CC_lecturer FOREIGN KEY (maGV) REFERENCES Lecturers(maGV)
);
-- 8. Chèn 10 dòng vào CourseClasses (maLHP tự tăng từ 1 đến 10)
INSERT INTO CourseClasses (maMH, maHK, maGV)
VALUES ('CS101', 'HK2023.1', 'gv01'),
    ('CS102', 'HK2023.1', 'gv02'),
    ('EC201', 'HK2023.1', 'gv03'),
    ('BA202', 'HK2023.2', 'gv04'),
    ('EN101', 'HK2023.2', 'gv05'),
    ('CH301', 'HK2024.1', 'gv07'),
    ('AU401', 'HK2024.1', 'gv08'),
    ('LW501', 'HK2024.2', 'gv09'),
    ('TO601', 'HK2024.2', 'gv10'),
    ('MA101', 'HK2023.1', 'gv01');
-- 9. Bảng Grades
CREATE TABLE Grades (
    maSV VARCHAR(20) NOT NULL,
    maLHP INT NOT NULL,
    diem1 DECIMAL(4, 2) CHECK (
        diem1 BETWEEN 0 AND 10
    ),
    diem2 DECIMAL(4, 2) CHECK (
        diem2 BETWEEN 0 AND 10
    ),
    diemThi DECIMAL(4, 2) CHECK (
        diemThi BETWEEN 0 AND 10
    ),
    diemTong DECIMAL(4, 2) GENERATED ALWAYS AS (
        CAST(
            (((diem1 + diem2) / 2.0) * 0.4 + diemThi * 0.6) AS DECIMAL(4, 2)
        )
    ) VIRTUAL,
    -- Cập nhật kiểu dữ liệu thành VARCHAR(2) và mở rộng logic phân loại
    diemTongChu VARCHAR(2) GENERATED ALWAYS AS (
        CASE
            WHEN diemTong IS NULL THEN NULL
            WHEN diemTong >= 8.5 THEN 'A'
            WHEN diemTong >= 8.0 THEN 'B+'
            WHEN diemTong >= 7.0 THEN 'B'
            WHEN diemTong >= 6.5 THEN 'C+'
            WHEN diemTong >= 5.5 THEN 'C'
            WHEN diemTong >= 5.0 THEN 'D+'
            WHEN diemTong >= 4.0 THEN 'D'
            ELSE 'F'
        END
    ) VIRTUAL,
    CONSTRAINT PK_Grades PRIMARY KEY (maSV, maLHP),
    CONSTRAINT FK_Grades_Student FOREIGN KEY (maSV) REFERENCES Students(maSV),
    CONSTRAINT FK_Grades_CC FOREIGN KEY (maLHP) REFERENCES CourseClasses(maLHP)
);
-- ==========================================================
-- MODULE QUẢN LÝ THỜI GIAN VÀ LỊCH HỌC
-- ==========================================================
-- 1. Tạo bảng Rooms (Quản lý Phòng học)
CREATE TABLE `rooms` (
    `room_id` int(11) NOT NULL AUTO_INCREMENT,
    `room_name` varchar(50) NOT NULL,
    `capacity` int(11) NOT NULL DEFAULT 50,
    `room_type` varchar(50) DEFAULT 'Lý thuyết',
    PRIMARY KEY (`room_id`),
    UNIQUE KEY `idx_room_name` (`room_name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- Thêm dữ liệu mẫu cho Phòng học
INSERT INTO `rooms` (`room_name`, `capacity`, `room_type`)
VALUES ('A1-101', 80, 'Lý thuyết'),
    ('A1-102', 80, 'Lý thuyết'),
    ('B2-201', 40, 'Thực hành máy tính'),
    ('B2-202', 40, 'Thực hành máy tính'),
    ('C3-301', 120, 'Hội trường');
-- 2. Tạo bảng ClassSchedules (Ma trận Lịch học)
CREATE TABLE `class_schedules` (
    `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
    `day_of_week` tinyint(4) NOT NULL COMMENT '2=Thứ 2, ..., 8=Chủ nhật',
    `start_period` tinyint(4) NOT NULL COMMENT 'Tiết bắt đầu (1-15)',
    `num_periods` tinyint(4) NOT NULL COMMENT 'Số tiết kéo dài',
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `maLHP` int(11) NOT NULL,
    `room_id` int(11) NOT NULL,
    PRIMARY KEY (`schedule_id`),
    KEY `FK_Schedule_CC` (`maLHP`),
    KEY `FK_Schedule_Room` (`room_id`),
    CONSTRAINT `FK_Schedule_CC` FOREIGN KEY (`maLHP`) REFERENCES `courseclasses` (`maLHP`) ON DELETE CASCADE,
    CONSTRAINT `FK_Schedule_Room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- 3. Đánh Composite Indexes (Bắt buộc để chống nghẽn CSDL khi check trùng lịch)
ALTER TABLE `class_schedules`
ADD INDEX `idx_time_check` (`day_of_week`, `start_period`, `num_periods`),
    ADD INDEX `idx_room_time` (`room_id`, `day_of_week`);
-- Thêm dữ liệu lịch học mẫu (Liên kết với CourseClasses cũ)
-- Giả sử: Học kỳ 1 (HK2023.1) bắt đầu từ 05/09/2023 đến 15/01/2024
INSERT INTO `class_schedules` (
        `maLHP`,
        `room_id`,
        `day_of_week`,
        `start_period`,
        `num_periods`,
        `start_date`,
        `end_date`
    )
VALUES (1, 1, 2, 1, 3, '2023-09-05', '2024-01-15'),
    -- LHP 1 (CS101): Thứ 2, tiết 1-3, phòng A1-101
    (1, 3, 4, 7, 2, '2023-09-05', '2024-01-15'),
    -- LHP 1 (CS101): Thứ 4, tiết 7-8, phòng B2-201 (Thực hành)
    (2, 2, 3, 4, 3, '2023-09-05', '2024-01-15'),
    -- LHP 2 (CS102): Thứ 3, tiết 4-6, phòng A1-102
    (3, 1, 2, 4, 3, '2023-09-05', '2024-01-15');
-- LHP 3 (EC201): Thứ 2, tiết 4-6, phòng A1-101
-- 4. Chèn 10 dòng vào Classes
INSERT INTO Classes (maLop, tenLop, email, maKhoa, maGV)
VALUES (
        'LH01',
        'CNTT Khóa 1',
        'cntt1@university.edu.vn',
        'CNTT',
        'gv01'
    ),
    (
        'LH02',
        'CNTT Khóa 2',
        'cntt2@university.edu.vn',
        'CNTT',
        'gv02'
    ),
    (
        'LH03',
        'Kinh tế đối ngoại',
        'ktdn@university.edu.vn',
        'KTE',
        'gv03'
    ),
    (
        'LH04',
        'Quản trị kinh doanh',
        'qtkd@university.edu.vn',
        'KTE',
        'gv04'
    ),
    (
        'LH05',
        'Ngôn ngữ Anh',
        'nna@university.edu.vn',
        'NN',
        'gv05'
    ),
    (
        'LH06',
        'Ngôn ngữ Trung',
        'nnt@university.edu.vn',
        'NN',
        'gv06'
    ),
    (
        'LH07',
        'Kỹ thuật Sinh học',
        'ktsinhhoc@university.edu.vn',
        'KHUD',
        'gv07'
    ),
    (
        'LH08',
        'Tự động hóa',
        'tdh@university.edu.vn',
        'XDTD',
        'gv08'
    ),
    (
        'LH09',
        'Luật Kinh tế',
        'luatkt@university.edu.vn',
        'LUAT',
        'gv09'
    ),
    (
        'LH10',
        'Quản trị dịch vụ Du lịch',
        'qtdl@university.edu.vn',
        'DL',
        'gv10'
    );
-- 5. Chèn 10 dòng vào Students (bao gồm cả 'sv01' từ mẫu cũ)
INSERT INTO Students (
        maSV,
        hoTen,
        gioiTinh,
        namSinh,
        diaChi,
        email,
        sdt,
        maLop
    )
VALUES (
        'sv01',
        'Trần Văn Nam',
        'Nam',
        2004,
        'Hà Nội',
        'vannam@student.edu.vn',
        '0812345678',
        'LH01'
    ),
    (
        'sv02',
        'Nguyễn Thị Mai',
        'Nữ',
        2004,
        'Hải Phòng',
        'thimai@student.edu.vn',
        '0823456789',
        'LH01'
    ),
    (
        'sv03',
        'Lê Minh Quân',
        'Nam',
        2004,
        'Đà Nẵng',
        'minhquan@student.edu.vn',
        '0834567890',
        'LH02'
    ),
    (
        'sv04',
        'Phạm Hồng Nhung',
        'Nữ',
        2004,
        'Quảng Ninh',
        'hongnhung@student.edu.vn',
        '0845678901',
        'LH03'
    ),
    (
        'sv05',
        'Hoàng Đình Tú',
        'Nam',
        2003,
        'Nghệ An',
        'dinhtu@student.edu.vn',
        '0856789012',
        'LH04'
    ),
    (
        'sv06',
        'Vũ Phương Thảo',
        'Nữ',
        2004,
        'Thanh Hóa',
        'phuongthao@student.edu.vn',
        '0867890123',
        'LH05'
    ),
    (
        'sv07',
        'Nguyễn Tiến Đạt',
        'Nam',
        2004,
        'Hà Nội',
        'tiendat@student.edu.vn',
        '0878901234',
        'LH06'
    ),
    (
        'sv08',
        'Bùi Thúy Quỳnh',
        'Nữ',
        2003,
        'Nam Định',
        'thuyquynh@student.edu.vn',
        '0889012345',
        'LH07'
    ),
    (
        'sv09',
        'Ngô Quốc Khánh',
        'Nam',
        2004,
        'Thái Bình',
        'quockhanh@student.edu.vn',
        '0890123456',
        'LH08'
    ),
    (
        'sv10',
        'Đặng Bảo Trâm',
        'Nữ',
        2004,
        'Cần Thơ',
        'baotram@student.edu.vn',
        '0801234567',
        'LH09'
    );
-- 9. Chèn 10 dòng vào Grades (maLHP map trực tiếp từ 1 đến 10 tương ứng dữ liệu trên)
-- Lưu ý: Cột diemTong tự động tính toán từ biểu thức nên không cần chèn thủ công.
INSERT INTO Grades (maSV, maLHP, diem1, diem2, diemThi)
VALUES ('sv01', 1, 9.50, 9.50, 9.50),
    -- Đạt 9.50 -> Kịch trần điểm A
    ('sv02', 1, 8.50, 8.50, 8.00),
    -- Tính ra 8.20 -> Rơi vào nhóm B+ mới
    ('sv03', 2, 7.50, 7.50, 7.50),
    -- Tính ra 7.50 -> Nhóm B
    ('sv04', 3, 7.00, 7.00, 6.50),
    -- Tính ra 6.70 -> Rơi vào nhóm C+ mới
    ('sv05', 4, 6.00, 6.00, 6.00),
    -- Tính ra 6.00 -> Nhóm C
    ('sv06', 5, 5.50, 5.50, 5.00),
    -- Tính ra 5.20 -> Rơi vào nhóm D+ mới
    ('sv08', 6, 4.50, 4.50, 4.50),
    -- Tính ra 4.50 -> Nhóm D
    ('sv09', 7, 3.00, 3.00, 3.00),
    -- Tính ra 3.00 -> Nhóm F (Trượt)
    ('sv10', 8, NULL, NULL, NULL),
    -- Edge case: Chưa có điểm -> Trả về NULL (Không bị lỗi ép về F)
    ('sv01', 10, 8.00, 9.00, 8.50);
-- Tính ra 8.50 -> Chạm biên dưới của điểm A
-- ==============================================================================
-- 2. THÊM GIÁO VIÊN KHOA LUẬT
-- ==============================================================================
INSERT INTO `Lecturers` (`maGV`, `hoTenGV`, `email`, `sdt`)
VALUES (
        'gv11',
        'TS. Nguyễn Luật Sư',
        'luatsu.nguyen@university.edu.vn',
        '0911222333'
    ),
    (
        'gv12',
        'ThS. Trần Pháp Lý',
        'phaply.tran@university.edu.vn',
        '0911222444'
    ),
    (
        'gv13',
        'GS. Lê Công Bằng',
        'congbang.le@university.edu.vn',
        '0911222555'
    );
-- ==============================================================================
-- 3. THÊM MÔN HỌC KHOA LUẬT
-- Lưu ý: maKhoa sử dụng 'luat' (chữ thường) để khớp với bản ghi trong bảng faculties
-- ==============================================================================
INSERT INTO `courses` (`maMH`, `tenMH`, `soTinChi`, `maKhoa`)
VALUES ('LW502', 'Luật Dân sự 1', 3, 'luat'),
    ('LW503', 'Luật Hình sự', 4, 'luat'),
    ('LW504', 'Luật Thương mại Quốc tế', 3, 'luat'),
    ('LW505', 'Hiến pháp và Pháp luật', 2, 'luat');
-- ==============================================================================
-- 4. THÊM LỚP HÀNH CHÍNH (LỚP NIÊN CHẾ) KHOA LUẬT
-- ==============================================================================
INSERT INTO `classes` (`maLop`, `tenLop`, `email`, `maKhoa`, `maGV`)
VALUES (
        'LH11',
        'Luật Dân sự K1',
        'luatds1@university.edu.vn',
        'luat',
        'gv11'
    ),
    (
        'LH12',
        'Luật Hình sự K1',
        'luaths1@university.edu.vn',
        'luat',
        'gv12'
    );
-- ==============================================================================
-- 5. THÊM SINH VIÊN KHOA LUẬT
-- Phân bổ vào các lớp LH09, LH11, LH12
-- ==============================================================================
INSERT INTO `students` (
        `maSV`,
        `hoTen`,
        `gioiTinh`,
        `namSinh`,
        `diaChi`,
        `email`,
        `sdt`,
        `maLop`
    )
VALUES (
        'sv11',
        'Trần Công Lý',
        'Nam',
        2004,
        'Hà Nội',
        'congly@student.edu.vn',
        '0899111222',
        'LH11'
    ),
    (
        'sv12',
        'Phạm Quỳnh Án',
        'Nữ',
        2004,
        'Hải Phòng',
        'quynhan@student.edu.vn',
        '0899111333',
        'LH11'
    ),
    (
        'sv13',
        'Nguyễn Tòa Án',
        'Nam',
        2003,
        'Đà Nẵng',
        'toaan@student.edu.vn',
        '0899111444',
        'LH12'
    ),
    (
        'sv14',
        'Lê Cảnh Sát',
        'Nam',
        2004,
        'Nghệ An',
        'canhsat@student.edu.vn',
        '0899111555',
        'LH12'
    ),
    (
        'sv15',
        'Hoàng Biện Hộ',
        'Nữ',
        2004,
        'Hồ Chí Minh',
        'bienho@student.edu.vn',
        '0899111666',
        'LH09'
    ),
    (
        'sv16',
        'Võ Vô Tội',
        'Nam',
        2005,
        'Cần Thơ',
        'votoi@student.edu.vn',
        '0899111777',
        'LH09'
    );
-- Sinh viên này đã bị disable account ở trên
-- ==============================================================================
-- 6. THÊM LỚP HỌC PHẦN (COURSE CLASSES) MÔN LUẬT
-- ==============================================================================
INSERT INTO `courseclasses` (`maLHP`, `maMH`, `maHK`, `maGV`, `isLocked`)
VALUES (11, 'LW502', 'HK2024.1', 'gv11', 0),
    (12, 'LW503', 'HK2024.1', 'gv12', 0),
    (13, 'LW504', 'HK2024.1', 'gv13', 1),
    -- Lớp học phần này đã bị KHÓA nhập điểm (isLocked = 1)
    (14, 'LW505', 'HK2024.2', 'gv11', 0);
-- Tạo lớp học phần mới (Môn Luật đại cương, HK2 24-25, GV khác)
INSERT INTO `courseclasses` (`maLHP`, `maMH`, `maHK`, `maGV`, `isLocked`)
VALUES (15, 'LW501', 'HK2024.2', 'gv12', 0);
-- Xếp lịch cố tình đè lên phòng của LHP #8 (Giao nhau tiết 2-3)
INSERT INTO `class_schedules` (
        `day_of_week`,
        `start_period`,
        `num_periods`,
        `start_date`,
        `end_date`,
        `maLHP`,
        `room_id`
    )
VALUES (2, 2, 3, '2025-01-16', '2025-05-30', 15, 1);
-- ==============================================================================
-- 7. LỊCH HỌC KHOA LUẬT (Để test xếp thời khóa biểu)
-- Test case: Học trải dài các thứ, dùng chung phòng hội trường C3-301
-- ==============================================================================
INSERT INTO `class_schedules` (
        `schedule_id`,
        `day_of_week`,
        `start_period`,
        `num_periods`,
        `start_date`,
        `end_date`,
        `maLHP`,
        `room_id`
    )
VALUES (8, 2, 7, 3, '2024-09-05', '2025-01-15', 11, 5),
    -- Thứ 2, tiết 7-9, Hội trường
    (9, 4, 1, 4, '2024-09-05', '2025-01-15', 12, 1),
    -- Thứ 4, tiết 1-4
    (10, 5, 7, 3, '2024-09-05', '2025-01-15', 13, 2),
    -- Thứ 5, tiết 7-9
    (11, 6, 4, 2, '2024-09-05', '2025-01-15', 14, 5);
INSERT INTO `class_schedules` (
        `day_of_week`,
        `start_period`,
        `num_periods`,
        `start_date`,
        `end_date`,
        `maLHP`,
        `room_id`
    )
VALUES (2, 1, 3, '2023-09-05', '2024-01-15', 8, 1);
-- Thứ 6, tiết 4-5
-- ==============================================================================
-- 8. THÊM ĐIỂM SỐ (Test Logic Tính Toán Điểm Chữ & Tính Chuyên Cần)
-- ==============================================================================
INSERT INTO `grades` (`maSV`, `maLHP`, `diem1`, `diem2`, `diemThi`)
VALUES -- Môn LW502 (LHP 11)
    ('sv11', 11, 8.00, 9.00, 8.50),
    -- Sinh viên Giỏi (Test B+/A)
    ('sv12', 11, 4.00, 5.00, 3.50),
    -- Sinh viên Yếu (Test D/F)
    ('sv13', 11, 9.50, 10.00, 9.50),
    -- Sinh viên Xuất sắc (Test A)
    -- Môn LW503 (LHP 12)
    ('sv14', 12, 6.00, 6.50, 7.00),
    -- Sinh viên Trung bình khá
    ('sv15', 12, NULL, NULL, NULL),
    -- Mới đăng ký, chưa có điểm thành phần (Test NULL handler)
    ('sv10', 12, 7.00, NULL, NULL),
    -- Có điểm hệ số 1, vắng hệ số 2 và thi (Sinh viên khóa cũ học ghép)
    -- Môn LW504 (LHP 13 - Lớp đã bị khóa)
    ('sv11', 13, 10.00, 9.50, 9.00),
    ('sv12', 13, 8.00, 8.00, 8.00);