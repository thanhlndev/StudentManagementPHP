# Student Management System

Hệ thống Quản lý Đào tạo Tín chỉ được thiết kế nhằm tối ưu hóa công tác quản lý dữ liệu sinh viên, giảng viên và học phần. Hệ thống tập trung vào tính toàn vẹn dữ liệu, hiệu năng xử lý cao và trải nghiệm người dùng tối ưu.

## Công nghệ sử dụng

- **Backend:** PHP 8.x
- **Database:** MySQL (MariaDB)
- **Frontend:** HTML5, Bootstrap 4 (SB Admin 2 Theme), JavaScript (Chart.js cho báo cáo)
- **Kiến trúc:** Clean Architecture & MVC pattern (tùy chỉnh)

## Kiến trúc Cơ sở dữ liệu

Hệ thống sử dụng các bảng liên kết chặt chẽ với khóa ngoại (Foreign Keys) để đảm bảo tính toàn vẹn dữ liệu:

- **Module Nhân sự & Đơn vị:** Accounts, Faculties, Lectures, Classes, Students.

- **Module Đào tạo:** Courses, Semesters, CourseClasses.

- **Module Quản lý thời gian & Tài nguyên:** Rooms, ClassSchedules (hỗ trợ kiểm soát xung đột tài nguyên thời gian thực).

- **Module Kết quả: Grades với Virtual Columns** (tự động tính điểm tổng và quy đổi điểm chữ theo quy chế tín chỉ).

## Các Module chính

1. **Admin:** Quản trị dữ liệu tập trung, phân tích Dashboard, quản lý phòng học và lịch học (xếp lịch theo tài nguyên).

2. **Lecturer(Giảng viên):** Quản lý lớp học phần được phân công, nhập điểm và theo dõi trạng thái khóa/mở điểm.

3. **Student(Sinh viên):** Đăng ký học phần, xem kết quả học tập, tính toán GPA và theo dõi thời khóa biểu cá nhân với bộ lọc trùng lịch thông minh.

## Thiết lập môi trường

1. Yêu cầu **XAMPP** (hoặc môi trường LAMP/WAMP tương tự).
2. gỉt clone https://github.com/thanhlndev/StudentManagementPHP.git
   hoặc Dowload Zip trong thư mục xampp/htdocs/
3. Import file `qlsv.sql` vào MySQL.
4. Cấu hình `config/database.php` với thông tin kết nối DB của bạn.
5. Truy cập hệ thống qua: `http://localhost/StudentManagement/`

## Best Practices đã áp dụng

**Collision Detection:** Thuật toán phát hiện xung đột thời gian (Overlap Detection) giúp ngăn chặn sinh viên đăng ký môn bị trùng lịch và ngăn Admin xếp lịch trùng phòng/giảng viên.

**Concurrency Control:** Sử dụng Transaction và Pessimistic Locking (FOR UPDATE) để đảm bảo tính toàn vẹn khi đăng ký học phần, chống lỗi Race Condition khi quá tải sĩ số.

**Performance Optimization:** Sử dụng kỹ thuật GROUP_CONCAT trong SQL để lấy dữ liệu lịch học chỉ với 1 truy vấn duy nhất, loại bỏ lỗi N+1 Query.

**Dynamic Filtering:** Bộ lọc NOT EXISTS hỗ trợ sinh viên ẩn các môn học bị trùng lịch học ngay trong danh sách đăng ký.

**Security:** Mã hóa mật khẩu sử dụng password_hash() (Bcrypt).

**UX/UI:** Giao diện Responsive tích hợp công cụ lọc thông minh theo Khoa và bộ lọc thời gian thực.
