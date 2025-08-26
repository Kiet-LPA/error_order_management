# Hướng dẫn sử dụng chức năng Báo cáo công việc

## Tổng quan

Chức năng báo cáo công việc cho phép nhân viên tạo báo cáo hàng ngày, tuần, tháng theo cấu trúc phân cấp: Năm → Tháng → Tuần → Ngày. Mỗi role sẽ có quyền truy cập và sử dụng khác nhau.

## Cấu trúc phân quyền

### 1. Employee (Nhân viên)
- **Quyền truy cập**: Chỉ có thể tạo và xem báo cáo của mình
- **Chức năng chính**:
  - Tạo báo cáo mới theo năm/tháng/tuần
  - Xem lịch sử báo cáo của mình
  - Cập nhật báo cáo đã tạo

### 2. Manager (Quản lý)
- **Quyền truy cập**: Quản lý báo cáo của nhân viên trong phòng ban mình
- **Chức năng chính**:
  - Tạo báo cáo mới (giống Employee)
  - Xem báo cáo của tất cả nhân viên trong phòng ban
  - Quản lý và theo dõi tiến độ báo cáo

### 3. Admin (Quản trị viên)
- **Quyền truy cập**: Quản lý báo cáo toàn hệ thống
- **Chức năng chính**:
  - Tạo báo cáo mới (giống Employee)
  - Xem báo cáo của tất cả nhân viên trong tất cả phòng ban
  - Quản lý và theo dõi tiến độ báo cáo toàn hệ thống

## Cách sử dụng

### 1. Truy cập chức năng
- Đăng nhập vào hệ thống
- Click vào menu "Báo cáo công việc" trong sidebar
- Hệ thống sẽ hiển thị giao diện phù hợp với role của bạn

### 2. Tạo báo cáo mới

#### Bước 1: Chọn thời gian
- Chọn **Năm** (ví dụ: 2025)
- Chọn **Tháng** (ví dụ: Tháng 8)
- Chọn **Tuần** (ví dụ: Tuần 1)

#### Bước 2: Điền thông tin báo cáo
Bảng báo cáo có các cột:
- **STT**: Số thứ tự tự động
- **Ngày/Tháng/Năm**: Ngày báo cáo
- **Tên**: Tên nhân viên (tự động điền)
- **Phòng ban**: Phòng ban (tự động điền)
- **Vị trí**: Vị trí công việc (tự động điền)
- **Công việc trong ngày**: Mô tả công việc đã làm
- **Khó khăn**: Những khó khăn gặp phải
- **Nhận xét**: Nhận xét, đề xuất

#### Bước 3: Thêm hàng báo cáo (tùy chọn)
- Click "Thêm hàng báo cáo" để tạo báo cáo cho nhiều ngày
- Có thể xóa hàng bằng nút "Xóa" (nếu có nhiều hơn 1 hàng)

#### Bước 4: Điền thông tin bổ sung (theo phòng ban)
Tùy theo phòng ban, sẽ có các trường bổ sung:

**Phòng IT:**
- Dự án đang làm
- Lỗi đã sửa
- Code review
- Cuộc họp tham gia

**Phòng HR:**
- Ứng viên phỏng vấn
- Hợp đồng xử lý
- Buổi đào tạo
- Vấn đề nhân viên

**Phòng Finance:**
- Giao dịch xử lý
- Báo cáo tạo
- Đánh giá ngân sách
- Công việc kiểm toán

#### Bước 5: Lưu báo cáo
- Click "Lưu báo cáo" để hoàn tất

### 3. Xem và quản lý báo cáo

#### Đối với Employee:
- Xem danh sách báo cáo theo năm/tháng/tuần
- Click vào tuần để xem chi tiết báo cáo

#### Đối với Manager:
- Tab "Tạo báo cáo": Tạo báo cáo mới
- Tab "Quản lý báo cáo": 
  - Chọn nhân viên để xem báo cáo
  - Xem cây báo cáo theo năm/tháng/tuần
  - Click vào tuần để xem chi tiết

#### Đối với Admin:
- Tab "Tạo báo cáo": Tạo báo cáo mới
- Tab "Quản lý báo cáo":
  - Chọn phòng ban
  - Chọn nhân viên trong phòng ban
  - Xem cây báo cáo theo năm/tháng/tuần
  - Click vào tuần để xem chi tiết

## Cấu trúc dữ liệu

### Bảng work_reports
```sql
- id: Khóa chính
- user_id: ID nhân viên
- department_id: ID phòng ban
- year: Năm
- month: Tháng
- week: Tuần
- report_date: Ngày báo cáo
- daily_work: Công việc trong ngày
- difficulties: Khó khăn
- comments: Nhận xét
- custom_fields: Thông tin bổ sung (JSON)
- created_at, updated_at: Timestamps
```

### Ràng buộc
- Mỗi nhân viên chỉ có 1 báo cáo cho 1 ngày cụ thể
- Báo cáo được phân cấp theo: Năm → Tháng → Tuần → Ngày

## API Endpoints

### 1. Lấy danh sách báo cáo
```
GET /work-reports
```

### 2. Tạo báo cáo mới
```
GET /work-reports/create
POST /work-reports
```

### 3. Cập nhật báo cáo
```
PUT /work-reports/{id}
```

### 4. Xóa báo cáo
```
DELETE /work-reports/{id}
```

### 5. API hỗ trợ
```
GET /work-reports/week - Xem báo cáo theo tuần
GET /work-reports/months - Lấy danh sách tháng
GET /work-reports/weeks - Lấy danh sách tuần
GET /work-reports/employees - Lấy danh sách nhân viên theo phòng ban
```

## Tính năng đặc biệt

### 1. Custom Fields theo phòng ban
- Mỗi phòng ban có thể có các trường bổ sung khác nhau
- Dữ liệu được lưu dưới dạng JSON trong cột `custom_fields`
- Dễ dàng mở rộng thêm trường mới

### 2. Giao diện responsive
- Hỗ trợ đầy đủ trên desktop và mobile
- Bottom navigation cho mobile
- Sidebar có thể thu gọn

### 3. Validation và bảo mật
- Kiểm tra quyền truy cập theo role
- Validation dữ liệu đầu vào
- Ngăn chặn tạo báo cáo trùng lặp

## Troubleshooting

### 1. Lỗi "Đã có báo cáo cho ngày này"
- Mỗi nhân viên chỉ có thể tạo 1 báo cáo cho 1 ngày
- Nếu cần cập nhật, hãy chỉnh sửa báo cáo hiện có

### 2. Không thấy menu "Báo cáo công việc"
- Kiểm tra quyền đăng nhập
- Đảm bảo đã đăng nhập với tài khoản có quyền truy cập

### 3. Không thể tạo báo cáo
- Kiểm tra thông tin bắt buộc (ngày, công việc)
- Đảm bảo đã chọn đầy đủ năm/tháng/tuần

## Mở rộng tính năng

### 1. Thêm custom fields cho phòng ban mới
Chỉnh sửa method `getDepartmentCustomFields()` trong model `WorkReport`:

```php
public function getDepartmentCustomFields()
{
    $customFields = [
        'IT' => [
            'projects_worked_on' => 'Dự án đang làm',
            // Thêm trường mới
        ],
        'NewDepartment' => [
            'new_field' => 'Tên trường mới',
        ]
    ];
    
    return $customFields[$this->department->name] ?? [];
}
```

### 2. Thêm validation rules
Chỉnh sửa trong `WorkReportController`:

```php
$request->validate([
    'daily_work' => 'required|string|max:1000',
    'custom_fields.new_field' => 'nullable|string',
]);
```

### 3. Thêm export báo cáo
Có thể thêm tính năng export PDF/Excel cho báo cáo theo yêu cầu.

## Hỗ trợ

Nếu gặp vấn đề hoặc cần hỗ trợ, vui lòng liên hệ:
- Email: support@company.com
- Hotline: 1900-xxxx
