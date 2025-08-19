# Hệ thống phân quyền theo phòng ban

## Tổng quan

Dự án đã được implement chức năng phân quyền theo phòng ban để đảm bảo:
- **Manager** của phòng ban A chỉ có thể giao task cho **Employee** thuộc phòng ban A
- **Manager** không thể giao task cho **Employee** thuộc phòng ban B
- **Manager** chỉ thấy và quản lý được tasks của phòng ban mình
- **Employee** chỉ thấy và thao tác được tasks của mình
- **Admin** có **toàn quyền** - có thể giao task cho mọi **Employee** và quản lý toàn bộ hệ thống

## Các thay đổi đã thực hiện

### 1. TaskController
- **Method `create()`**: Lọc danh sách users theo phòng ban
  - Admin: thấy tất cả users
  - Manager: chỉ thấy users cùng phòng ban
  - Employee: không thể tạo task

- **Method `store()`**: Kiểm tra quyền khi tạo task
  - Manager chỉ có thể giao việc cho employee cùng phòng ban
  - Admin có thể giao việc cho mọi employee

- **Method `index()`**: Lọc danh sách tasks theo quyền
  - Admin: thấy tất cả tasks
  - Manager: chỉ thấy tasks của phòng ban mình
  - Employee: chỉ thấy tasks của mình

- **Method `show()`**: Kiểm tra quyền xem task
  - Admin: xem được mọi task
  - Manager: chỉ xem được task của phòng ban mình
  - Employee: chỉ xem được task của mình

- **Method `updateStatus()`**: Kiểm tra quyền cập nhật task
  - Admin: cập nhật được mọi task
  - Manager: chỉ cập nhật được task của phòng ban mình
  - Employee: chỉ cập nhật được task của mình

- **Method `comment()`**: Kiểm tra quyền comment
  - Admin: comment được trên mọi task
  - Manager: chỉ comment được trên task của phòng ban mình
  - Employee: chỉ comment được trên task của mình

- **Method `history()`**: Kiểm tra quyền xem lịch sử
  - Admin: xem được lịch sử mọi task
  - Manager: chỉ xem được lịch sử task của phòng ban mình
  - Employee: chỉ xem được lịch sử task của mình

- **Method `myTasks()`**: Lọc tasks và thống kê theo quyền
  - Admin: thấy tất cả tasks và thống kê toàn hệ thống
  - Manager: chỉ thấy tasks và thống kê của phòng ban mình
  - Employee: chỉ thấy tasks và thống kê của mình

### 2. DashboardController
- **Method `index()`**: Hiển thị dashboard theo quyền
  - **Admin**: Hiển thị tasks theo từng phòng ban riêng biệt (nhiều card)
  - **Manager**: Hiển thị tasks của phòng ban mình (1 bảng)
  - **Employee**: Hiển thị tasks của mình (1 bảng)

### 3. UserController
- **Method `index()`**: Lọc danh sách users theo phòng ban (Admin thấy tất cả)
- **Method `create()`**: Giới hạn phòng ban có thể tạo user (Admin tạo được mọi nơi)
- **Method `store()`**: Kiểm tra quyền tạo user (Admin tạo được mọi role)
- **Method `edit()`**: Kiểm tra quyền chỉnh sửa user (Admin sửa được mọi user)
- **Method `update()`**: Kiểm tra quyền cập nhật user (Admin cập nhật được mọi thông tin)
- **Method `destroy()`**: Kiểm tra quyền xóa user (Admin xóa được mọi user)

### 4. View (create.blade.php)
- Hiển thị tên phòng ban bên cạnh tên user
- Thông báo cho manager về quyền hạn

### 5. Routes
- Áp dụng middleware `role:admin,manager` cho việc tạo task
- Loại bỏ route trùng lặp

### 6. Test Cases
- Tạo file test để kiểm tra chức năng phân quyền
- Test các trường hợp: manager giao việc, admin giao việc, employee không thể giao việc

## Quyền hạn chi tiết

### Admin (Toàn quyền)
- ✅ Xem tất cả users và departments
- ✅ Tạo/sửa/xóa user cho mọi phòng ban
- ✅ Giao task cho mọi employee
- ✅ Quản lý phòng ban
- ✅ Bỏ qua tất cả giới hạn phân quyền theo phòng ban
- ✅ Có thể thay đổi role của bất kỳ user nào
- ✅ Có thể di chuyển user giữa các phòng ban

### Manager
- ✅ Xem users cùng phòng ban
- ✅ Tạo/sửa/xóa user cho phòng ban của mình
- ✅ Giao task cho employee cùng phòng ban
- ✅ Xem và quản lý tasks của phòng ban mình
- ✅ Cập nhật trạng thái tasks của phòng ban mình
- ✅ Comment trên tasks của phòng ban mình
- ✅ Xem lịch sử tasks của phòng ban mình
- ❌ Không thể tạo admin
- ❌ Không thể quản lý phòng ban khác
- ❌ Không thể thấy tasks của phòng ban khác

### Employee
- ❌ Không thể tạo/sửa/xóa user
- ❌ Không thể giao task
- ✅ Chỉ có thể xem và thực hiện task được giao
- ✅ Chỉ thấy tasks của mình (được giao hoặc tự tạo)
- ✅ Chỉ cập nhật trạng thái tasks của mình
- ✅ Chỉ comment trên tasks của mình
- ✅ Chỉ xem lịch sử tasks của mình
- ❌ Không thể thấy tasks của người khác
- ❌ Không thể thao tác trên tasks của người khác

## Cách sử dụng

### 1. Đăng nhập với tài khoản Manager
```bash
# Manager sẽ thấy dropdown chỉ hiển thị employees cùng phòng ban
# Khi tạo task, chỉ có thể chọn employee cùng phòng ban
# Không thể tạo/sửa/xóa user của phòng ban khác
```

### 2. Đăng nhập với tài khoản Admin (Toàn quyền)
```bash
# Admin sẽ thấy dropdown hiển thị TẤT CẢ employees
# Có thể giao task cho employee bất kỳ phòng ban nào
# Có thể tạo/sửa/xóa user ở mọi phòng ban
# Có thể thay đổi role của bất kỳ user nào
# Có thể di chuyển user giữa các phòng ban
# Có thể quản lý toàn bộ hệ thống
```

### 3. Kiểm tra quyền
```bash
# Chạy test để kiểm tra chức năng
php artisan test tests/Feature/DepartmentPermissionTest.php
```

## Bảo mật

- Tất cả các endpoint đều được bảo vệ bởi middleware authentication
- Kiểm tra quyền được thực hiện ở cả Controller và View
- Sử dụng `abort(403)` để từ chối truy cập trái phép
- Validation được thực hiện ở cả client và server side

## Lưu ý

- Đảm bảo tất cả users đều có `department_id` được set (trừ admin)
- **Manager** không thể thay đổi phòng ban của user sang phòng ban khác
- **Admin** có thể di chuyển user giữa các phòng ban và thay đổi role
- Khi xóa department, cần xử lý các user thuộc department đó
- Có thể mở rộng để thêm quyền "super manager" có thể quản lý nhiều phòng ban
- **Admin luôn có toàn quyền** và bỏ qua mọi giới hạn phân quyền theo phòng ban
sdx