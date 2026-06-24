# StudentManagement System

Hệ thống Quản lý Đào tạo Tín chỉ (Credit-based Academic Management System) được thiết kế để quản lý dữ liệu sinh viên, giảng viên, môn học và kết quả học tập.

## Công nghệ sử dụng

- **Backend:** PHP 8.x
- **Database:** MySQL (MariaDB)
- **Frontend:** HTML5, Bootstrap 4 (SB Admin 2 Theme), JavaScript (Chart.js cho báo cáo)
- **Kiến trúc:** Clean Architecture & MVC pattern (tùy chỉnh)

## Kiến trúc Cơ sở dữ liệu

Hệ thống sử dụng các bảng liên kết chặt chẽ với khóa ngoại (Foreign Keys) để đảm bảo tính toàn vẹn dữ liệu:

- **Accounts:** Quản lý đăng nhập và phân quyền (Admin, Teacher, Student).
- **Faculties & Classes:** Phân cấp đơn vị đào tạo.
- **CourseClasses:** Quản lý các lớp học phần (khớp với học kỳ và giảng viên).
- **Grades:** Lưu trữ kết quả học tập với Virtual Columns (tự động tính điểm tổng và quy đổi điểm chữ).

## Các Module chính

1. **Quản trị (Admin):**
   - Quản lý dữ liệu tập trung (Sinh viên, Giảng viên, Khoa, Môn học).
   - Dashboard phân tích 3 tầng (Chỉ số, Phân bổ khoa, Cảnh báo tỷ lệ trượt).
   - Kiểm soát khóa/mở điểm toàn hệ thống.
2. **Giảng viên (Teacher):**
   - Quản lý danh sách lớp được phân công.
   - Nhập điểm quá trình và điểm thi.
   - Xem nhanh trạng thái khóa/mở điểm của từng lớp.
3. **Sinh viên (Student):**
   - Đăng ký học phần (sử dụng Transaction để chống Race Condition).
   - Xem kết quả học tập & Tính toán GPA tích lũy.
   - Xem thông tin giảng viên và danh sách bạn học.

## Thiết lập môi trường

1. Yêu cầu **XAMPP** (hoặc môi trường LAMP/WAMP tương tự).
2. Import file `database.sql` vào MySQL.
3. Cấu hình `config/database.php` với thông tin kết nối DB của bạn.
4. Truy cập hệ thống qua: `http://localhost/StudentManagement/`

## Best Practices đã áp dụng

- **Data Integrity:** Sử dụng `Transactions` cho các tác vụ ghi danh để tránh xung đột (Race Condition).
- **Security:** Mã hóa mật khẩu bằng `password_hash()` (Bcrypt).
- **UX/UI:** Tối ưu hóa Dashboard với `Chart.js` và thiết kế Responsive cho mọi thiết bị.
- **Maintainability:** Tách biệt cấu trúc thư mục, logic xử lý dữ liệu và giao diện hiển thị.
