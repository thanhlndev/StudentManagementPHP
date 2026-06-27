-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2026 at 12:27 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
DROP DATABASE IF EXISTS qlsv;
CREATE DATABASE qlsv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qlsv;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;
--
-- Database: `qlsv`
--

-- --------------------------------------------------------
--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `username` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` datetime DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `accounts`
--

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
    'Lecturer',
    1,
    '2026-06-24 14:43:33'
  ),
  (
    'gv02',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv03',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv04',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv05',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv06',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv07',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv08',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv09',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv10',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-24 16:24:05'
  ),
  (
    'gv11',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'gv12',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'gv13',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Lecturer',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'SV001',
    '$2y$10$3QSAKBK/cTi9LFF7zu1vyO3GaQZZzsFjUqXTG1PdgWvPYpVrGQJTm',
    'Student',
    1,
    '2026-06-25 15:46:00'
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
  ),
  (
    'sv11',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Student',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'sv12',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Student',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'sv13',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Student',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'sv14',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Student',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'sv15',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Student',
    1,
    '2026-06-25 15:35:18'
  ),
  (
    'sv16',
    '$2y$10$FImpoYUlsHrBpzypufQuXOCQGDF6OqvsxbWHzlIKxzHU8mNvyg32i',
    'Student',
    0,
    '2026-06-25 15:35:18'
  );
-- --------------------------------------------------------
--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `maLop` varchar(20) NOT NULL,
  `tenLop` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `maKhoa` varchar(20) NOT NULL,
  `maGV` varchar(20) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`maLop`, `tenLop`, `email`, `maKhoa`, `maGV`)
VALUES (
    'LH01',
    'CNTT Khóa 01',
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
  ),
  (
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
-- --------------------------------------------------------
--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `schedule_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '2=Thứ 2, ..., 8=Chủ nhật',
  `start_period` tinyint(4) NOT NULL COMMENT 'Tiết bắt đầu (1-15)',
  `num_periods` tinyint(4) NOT NULL COMMENT 'Số tiết kéo dài',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `maLHP` int(11) NOT NULL,
  `room_id` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `class_schedules`
--

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
VALUES (1, 2, 1, 3, '2023-09-05', '2024-01-15', 1, 1),
  (2, 4, 7, 2, '2023-09-05', '2024-01-15', 1, 3),
  (3, 6, 1, 3, '2023-09-05', '2024-01-15', 10, 5),
  (4, 2, 2, 3, '2023-09-05', '2024-01-15', 3, 2),
  (5, 3, 4, 2, '2023-09-05', '2024-01-15', 2, 2),
  (6, 6, 2, 3, '2023-09-05', '2024-01-15', 2, 4),
  (7, 5, 1, 3, '2023-09-05', '2024-01-15', 4, 1),
  (8, 2, 7, 3, '2024-09-05', '2025-01-15', 11, 5),
  (9, 4, 1, 4, '2024-09-05', '2025-01-15', 12, 1),
  (10, 5, 7, 3, '2024-09-05', '2025-01-15', 13, 2),
  (11, 6, 4, 2, '2024-09-05', '2025-01-15', 14, 5),
  (12, 2, 1, 3, '2023-09-05', '2024-01-15', 8, 1),
  (14, 2, 7, 2, '2025-01-16', '2025-05-30', 15, 1);
-- --------------------------------------------------------
--
-- Table structure for table `courseclasses`
--

CREATE TABLE `courseclasses` (
  `maLHP` int(11) NOT NULL,
  `maMH` varchar(20) NOT NULL,
  `maHK` varchar(20) NOT NULL,
  `maGV` varchar(20) NOT NULL,
  `isLocked` tinyint(1) DEFAULT 0
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `courseclasses`
--

INSERT INTO `courseclasses` (`maLHP`, `maMH`, `maHK`, `maGV`, `isLocked`)
VALUES (1, 'CS101', 'HK2023.1', 'gv01', 0),
  (2, 'CS102', 'HK2023.1', 'gv02', 0),
  (3, 'EC201', 'HK2023.1', 'gv03', 0),
  (4, 'BA202', 'HK2023.2', 'gv04', 0),
  (5, 'EN101', 'HK2023.2', 'gv05', 0),
  (6, 'CH301', 'HK2024.1', 'gv07', 0),
  (7, 'AU401', 'HK2024.1', 'gv08', 0),
  (8, 'LW501', 'HK2024.2', 'gv09', 0),
  (9, 'TO601', 'HK2024.2', 'gv10', 0),
  (10, 'MA101', 'HK2023.1', 'gv01', 0),
  (11, 'LW502', 'HK2024.1', 'gv11', 0),
  (12, 'LW503', 'HK2024.1', 'gv12', 0),
  (13, 'LW504', 'HK2024.1', 'gv13', 0),
  (14, 'LW505', 'HK2024.2', 'gv11', 0),
  (15, 'LW501', 'HK2024.2', 'gv12', 0),
  (16, 'LW501', 'HK2024.2', 'gv12', 0);
-- --------------------------------------------------------
--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `maMH` varchar(20) NOT NULL,
  `tenMH` varchar(100) NOT NULL,
  `soTinChi` int(11) DEFAULT 3,
  `maKhoa` varchar(20) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`maMH`, `tenMH`, `soTinChi`, `maKhoa`)
VALUES (
    'AU401',
    'Hệ thống điều khiển tự động',
    4,
    'XDTD'
  ),
  ('BA202', 'Quản trị học cơ bản', 3, 'KTE'),
  ('CH301', 'Hóa học đại cương', 3, 'KHUD'),
  ('CS101', 'Cơ sở dữ liệu', 3, 'CNTT'),
  ('CS102', 'Lập trình hướng đối tượng', 4, 'CNTT'),
  ('EC201', 'Kinh tế vĩ mô', 3, 'KTE'),
  ('EN101', 'Tiếng Anh giao tiếp 1', 2, 'NN'),
  ('LW501', 'Luật đại cương', 2, 'luat'),
  ('LW502', 'Luật Dân sự 1', 3, 'luat'),
  ('LW503', 'Luật Hình sự', 4, 'luat'),
  ('LW504', 'Luật Thương mại Quốc tế', 3, 'luat'),
  ('LW505', 'Hiến pháp và Pháp luật', 2, 'luat'),
  ('MA101', 'Toán cao cấp A1', 4, 'KHUD'),
  ('TO601', 'Tổng quan ngành du lịch', 3, 'DL');
-- --------------------------------------------------------
--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `maKhoa` varchar(20) NOT NULL,
  `tenKhoa` varchar(100) NOT NULL,
  `diaChi` varchar(200) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`maKhoa`, `tenKhoa`, `diaChi`, `email`, `sdt`)
VALUES (
    'CNTT',
    'Công nghệ thông tin',
    'Tòa nhà A, Tầng 2',
    'cntt@university.edu.vn',
    '0243111222'
  ),
  (
    'DL',
    'Du lịch và Khách sạn',
    'Tòa nhà G, Tầng 1',
    'dulich@university.edu.vn',
    '0243111888'
  ),
  (
    'KHUD',
    'Khoa học ứng dụng',
    'Tòa nhà D, Tầng 1',
    'khud@university.edu.vn',
    '0243111555'
  ),
  (
    'KTE',
    'Kinh tế và Quản trị',
    'Tòa nhà B, Tầng 1',
    'kinhte@university.edu.vn',
    '0243111333'
  ),
  (
    'luat',
    'Luật học',
    'Tòa nhà F, Tầng 4',
    'luat@university.edu.vn',
    '0243111777'
  ),
  (
    'MT',
    'Môi trường',
    'Tòa nhà I, Tầng 2',
    'moitruong@university.edu.vn',
    '0243111000'
  ),
  (
    'NN',
    'Ngoại ngữ',
    'Tòa nhà C, Tầng 3',
    'ngoaingu@university.edu.vn',
    '0243111444'
  ),
  (
    'QLCC',
    'Quản lý công',
    'Tòa nhà J, Tầng 3',
    'qlcc@university.edu.vn',
    '0243111111'
  ),
  (
    'XDTD',
    'Xây dựng và Tự động hóa',
    'Tòa nhà E, Tầng 2',
    'xdtd@university.edu.vn',
    '0243111666'
  ),
  (
    'YDUOC',
    'Y Dược',
    'Tòa nhà H, Tầng 5',
    'yduoc@university.edu.vn',
    '0243111999'
  );
-- --------------------------------------------------------
--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `maSV` varchar(20) NOT NULL,
  `maLHP` int(11) NOT NULL,
  `diem1` decimal(4, 2) DEFAULT NULL CHECK (
    `diem1` between 0 and 10
  ),
  `diem2` decimal(4, 2) DEFAULT NULL CHECK (
    `diem2` between 0 and 10
  ),
  `diemThi` decimal(4, 2) DEFAULT NULL CHECK (
    `diemThi` between 0 and 10
  ),
  `diemTong` decimal(4, 2) GENERATED ALWAYS AS (
    cast(
      (`diem1` + `diem2`) / 2.0 * 0.4 + `diemThi` * 0.6 as decimal(4, 2)
    )
  ) VIRTUAL,
  `diemTongChu` varchar(2) GENERATED ALWAYS AS (
    case
      when `diemTong` is null then NULL
      when `diemTong` >= 8.5 then 'A'
      when `diemTong` >= 8.0 then 'B+'
      when `diemTong` >= 7.0 then 'B'
      when `diemTong` >= 6.5 then 'C+'
      when `diemTong` >= 5.5 then 'C'
      when `diemTong` >= 5.0 then 'D+'
      when `diemTong` >= 4.0 then 'D'
      else 'F'
    end
  ) VIRTUAL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`maSV`, `maLHP`, `diem1`, `diem2`, `diemThi`)
VALUES ('SV001', 11, NULL, NULL, NULL),
  ('SV001', 14, NULL, NULL, NULL),
  ('sv01', 1, 9.50, 9.50, 9.50),
  ('sv01', 10, 8.00, 9.00, 9.00),
  ('sv02', 1, 8.50, 8.50, 8.00),
  ('sv03', 2, 7.50, 7.50, 7.50),
  ('sv04', 3, 7.00, 7.00, 6.50),
  ('sv05', 4, 6.00, 6.00, 6.00),
  ('sv06', 5, 5.50, 5.50, 5.00),
  ('sv08', 6, 4.50, 4.50, 4.50),
  ('sv09', 7, 3.00, 3.00, 3.00),
  ('sv10', 8, 8.00, 8.00, 9.00),
  ('sv10', 12, 7.00, NULL, NULL),
  ('sv11', 11, 8.00, 9.00, 8.50),
  ('sv11', 13, 10.00, 9.50, 9.00),
  ('sv12', 11, 4.00, 5.00, 3.50),
  ('sv12', 13, 8.00, 8.00, 8.00),
  ('sv13', 11, 9.50, 10.00, 9.50),
  ('sv14', 12, 6.00, 6.50, 7.00),
  ('sv15', 12, NULL, NULL, NULL);
-- --------------------------------------------------------
--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `maGV` varchar(20) NOT NULL,
  `hoTenGV` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`maGV`, `hoTenGV`, `email`, `sdt`)
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
  ),
  (
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
  ),
  (
    'gv19',
    'Giảng viên 19',
    'gv19@gmail.com',
    '0987612345'
  );
-- --------------------------------------------------------
--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 50,
  `room_type` varchar(50) DEFAULT 'Lý thuyết'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `capacity`, `room_type`)
VALUES (1, 'A1-101', 80, 'Lý thuyết'),
  (2, 'A1-102', 80, 'Lý thuyết'),
  (3, 'B2-201', 40, 'Thực hành máy tính'),
  (4, 'B2-202', 40, 'Thực hành máy tính'),
  (5, 'C3-301', 120, 'Hội trường');
-- --------------------------------------------------------
--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `maHK` varchar(20) NOT NULL,
  `tenHK` varchar(50) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`maHK`, `tenHK`)
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
-- --------------------------------------------------------
--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `maSV` varchar(20) NOT NULL,
  `hoTen` varchar(100) NOT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `namSinh` int(11) DEFAULT NULL,
  `diaChi` varchar(200) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sdt` varchar(20) DEFAULT NULL,
  `maLop` varchar(20) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
--
-- Dumping data for table `students`
--

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
    'SV001',
    'Luong Ngoc Thanh',
    'Nam',
    2004,
    'B1-101',
    'sv001@gmail.com',
    '0987651234',
    'LH09'
  ),
  (
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
  ),
  (
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
--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
ADD PRIMARY KEY (`username`);
--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
ADD PRIMARY KEY (`maLop`),
  ADD KEY `FK_Class_Faculty` (`maKhoa`),
  ADD KEY `FK_Class_Lecturer` (`maGV`);
--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `FK_Schedule_CC` (`maLHP`),
  ADD KEY `FK_Schedule_Room` (`room_id`),
  ADD KEY `idx_time_check` (`day_of_week`, `start_period`, `num_periods`),
  ADD KEY `idx_room_time` (`room_id`, `day_of_week`);
--
-- Indexes for table `courseclasses`
--
ALTER TABLE `courseclasses`
ADD PRIMARY KEY (`maLHP`),
  ADD KEY `FK_CC_Course` (`maMH`),
  ADD KEY `FK_CC_Semester` (`maHK`),
  ADD KEY `FK_CC_Lecturer` (`maGV`);
--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
ADD PRIMARY KEY (`maMH`),
  ADD KEY `FK_Course_Faculty` (`maKhoa`);
--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
ADD PRIMARY KEY (`maKhoa`);
--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
ADD PRIMARY KEY (`maSV`, `maLHP`),
  ADD KEY `FK_Grades_CC` (`maLHP`);
--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
ADD PRIMARY KEY (`maGV`);
--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `idx_room_name` (`room_name`);
--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
ADD PRIMARY KEY (`maHK`);
--
-- Indexes for table `students`
--
ALTER TABLE `students`
ADD PRIMARY KEY (`maSV`),
  ADD KEY `FK_Student_Class` (`maLop`);
--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 15;
--
-- AUTO_INCREMENT for table `courseclasses`
--
ALTER TABLE `courseclasses`
MODIFY `maLHP` int(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 17;
--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 6;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
ADD CONSTRAINT `FK_Class_Faculty` FOREIGN KEY (`maKhoa`) REFERENCES `faculties` (`maKhoa`),
  ADD CONSTRAINT `FK_Class_Lecturer` FOREIGN KEY (`maGV`) REFERENCES `lecturers` (`maGV`);
--
-- Constraints for table `class_schedules`
--
ALTER TABLE `class_schedules`
ADD CONSTRAINT `FK_Schedule_CC` FOREIGN KEY (`maLHP`) REFERENCES `courseclasses` (`maLHP`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Schedule_Room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);
--
-- Constraints for table `courseclasses`
--
ALTER TABLE `courseclasses`
ADD CONSTRAINT `FK_CC_Course` FOREIGN KEY (`maMH`) REFERENCES `courses` (`maMH`),
  ADD CONSTRAINT `FK_CC_Lecturer` FOREIGN KEY (`maGV`) REFERENCES `lecturers` (`maGV`),
  ADD CONSTRAINT `FK_CC_Semester` FOREIGN KEY (`maHK`) REFERENCES `semesters` (`maHK`);
--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
ADD CONSTRAINT `FK_Course_Faculty` FOREIGN KEY (`maKhoa`) REFERENCES `faculties` (`maKhoa`);
--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
ADD CONSTRAINT `FK_Grades_CC` FOREIGN KEY (`maLHP`) REFERENCES `courseclasses` (`maLHP`),
  ADD CONSTRAINT `FK_Grades_Student` FOREIGN KEY (`maSV`) REFERENCES `students` (`maSV`);
--
-- Constraints for table `students`
--
ALTER TABLE `students`
ADD CONSTRAINT `FK_Student_Class` FOREIGN KEY (`maLop`) REFERENCES `classes` (`maLop`);
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;