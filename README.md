# Hệ Thống Quản Lý Công Việc (Task Management System)

## Mô Tả Tổng Quan

Hệ thống quản lý công việc với khả năng giao việc đa phòng ban và đa người dùng, tích hợp giao diện quản lý tối ưu cho từng phòng ban, hệ thống filter và sắp xếp theo thời gian, trang quản lý nhân viên hiện đại với thống kê và tìm kiếm nâng cao.

## Tính Năng Chính

### 🔐 Hệ Thống Phân Quyền Và Vai Trò

- **Admin**: Toàn quyền quản lý hệ thống, có thể giao việc cho mọi phòng ban và nhân viên
- **Manager**: Quản lý phòng ban và nhân viên thuộc phòng ban, chỉ giao việc cho nhân viên cùng phòng ban
- **Employee**: Thực hiện và theo dõi công việc được giao, không thể giao việc cho người khác

### 📋 Quản Lý Công Việc

#### Multi-Department Tasks
- Tạo công việc cho nhiều phòng ban cùng lúc
- Hiển thị riêng biệt công việc đa phòng ban trong dashboard
- Quản lý và theo dõi tiến độ theo từng phòng ban

#### Multi-User Assignments
- Giao việc cho nhiều người cùng lúc
- Theo dõi tiến độ của từng người được giao việc
- Hỗ trợ comment và cập nhật trạng thái

#### Hệ Thống Filter Và Sắp Xếp
- Filter theo trạng thái (đang làm, chờ duyệt, từ chối, trễ hạn, kết thúc)
- Filter theo khoảng thời gian
- Sắp xếp theo thời gian (mới nhất/cũ nhất)
- Filter theo phòng ban

### 👥 Quản Lý Nhân Viên

#### Dashboard Thống Kê
- Tổng số nhân viên
- Phân bố theo vai trò (Admin, Manager, Employee)
- Thống kê theo phòng ban

#### Tìm Kiếm Và Filter Nâng Cao
- Tìm kiếm theo tên, email, số điện thoại
- Filter theo phòng ban
- Filter theo vai trò
- Sắp xếp theo nhiều tiêu chí
- Pagination linh hoạt (10, 15, 25, 50 kết quả/trang)

### 🎯 Dashboard Theo Role

#### Admin Dashboard
- Thống kê tổng quan toàn hệ thống
- Hiển thị tasks theo từng phòng ban riêng biệt
- Section riêng cho multi-department tasks
- Drag & drop để sắp xếp thứ tự phòng ban

#### Manager Dashboard
- Thống kê phòng ban
- Bảng tasks phòng ban
- Section multi-department tasks có tham gia
- Filter panel tương tự Admin

#### Employee Dashboard
- Thống kê cá nhân
- Bảng tasks được giao (bao gồm multi-assignments)
- Filter panel đơn giản

## Cấu Trúc Database

### Bảng Users
```sql
- id, name, email, phone, password, role, department_id
- role: enum('admin', 'manager', 'employee')
- department_id: foreign key to departments
- timestamps
```

### Bảng Departments
```sql
- id, name
- timestamps
```

### Bảng Tasks
```sql
- id, title, description, status, priority, deadline
- creator_id, department_id, is_multi_department
- attachments, rejection_reason, finish_note
- is_recurring, recurring_start_date, recurring_days
- completed_at
- timestamps
```

### Bảng Task Assignees (Many-to-Many)
```sql
- id, task_id, user_id
- timestamps
```

### Bảng Department Task (Many-to-Many)
```sql
- id, task_id, department_id
- timestamps
```

## Cài Đặt Và Chạy

### Yêu Cầu Hệ Thống
- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL
- Composer
- Node.js & NPM

### Cài Đặt

1. **Clone repository**
```bash
git clone <repository-url>
cd error_order_management
```

2. **Cài đặt dependencies**
```bash
composer install
npm install
```

3. **Cấu hình môi trường**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Cấu hình database trong .env**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Chạy migrations**
```bash
php artisan migrate
```

6. **Chạy seeders (tùy chọn)**
```bash
php artisan db:seed
```

7. **Build assets**
```bash
npm run build
```

8. **Khởi động server**
```bash
php artisan serve
```

## Sử Dụng

### Tạo Công Việc Mới

1. **Đăng nhập với tài khoản Admin hoặc Manager**
2. **Truy cập trang tạo công việc**
3. **Chọn loại công việc:**
   - Single Department: Chọn một phòng ban
   - Multi-Department: Chọn nhiều phòng ban tham gia
4. **Chọn người nhận:**
   - Single User: Chọn một người
   - Multi-User: Chọn nhiều người cùng lúc
5. **Điền thông tin chi tiết và lưu**

### Quản Lý Nhân Viên

1. **Truy cập trang quản lý nhân viên (Admin/Manager)**
2. **Sử dụng các filter:**
   - Tìm kiếm theo tên, email, số điện thoại
   - Filter theo phòng ban
   - Filter theo vai trò
3. **Sắp xếp theo các tiêu chí khác nhau**
4. **Thêm, sửa, xóa nhân viên**

### Dashboard

1. **Admin:** Xem tổng quan toàn hệ thống, sắp xếp phòng ban
2. **Manager:** Xem tasks phòng ban và multi-department tasks có tham gia
3. **Employee:** Xem tasks được giao và tiến độ cá nhân

## Middleware

### DepartmentPermissionMiddleware
Kiểm tra quyền theo phòng ban:
- Admin: Toàn quyền
- Manager: Chỉ quản lý phòng ban của mình
- Employee: Không có quyền quản lý

### RoleMiddleware
Kiểm tra vai trò người dùng cho các route cụ thể.

## Validation Rules

### Task Validation
```php
'title' => 'required|string|max:255',
'description' => 'nullable|string',
'status' => 'required|in:in_progress,completed,rejected,overdue,finished',
'priority' => 'required|in:low,medium,high,urgent',
'deadline' => 'required|date|after:now',
'department_id' => 'required|exists:departments,id',
'assignee_ids' => 'required|array|min:1',
'assignee_ids.*' => 'exists:users,id',
'is_multi_department' => 'boolean',
'department_ids' => 'required_if:is_multi_department,true|array',
'department_ids.*' => 'exists:departments,id'
```

### User Validation
```php
'name' => 'required|string|max:255',
'email' => 'nullable|email|unique:users,email',
'phone' => 'nullable|string|max:20|unique:users,phone',
'password' => 'required|min:8|confirmed',
'role' => 'required|in:admin,manager,employee',
'department_id' => 'nullable|exists:departments,id'
```

## Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

Nếu bạn gặp vấn đề hoặc có câu hỏi, vui lòng tạo issue trong repository hoặc liên hệ với team phát triển.
