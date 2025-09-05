# HƯỚNG DẪN SỬ DỤNG HỆ THỐNG THEO TỪNG ROLE

## 📋 TỔNG QUAN HỆ THỐNG

Hệ thống quản lý công việc với 4 role chính:
- **Admin**: Toàn quyền quản lý hệ thống
- **Director**: Quản lý cấp cao, có thể giao việc cho mọi phòng ban
- **Manager**: Quản lý phòng ban, chỉ giao việc cho nhân viên cùng phòng ban
- **Employee**: Thực hiện công việc được giao

---

## 👤 EMPLOYEE (NHÂN VIÊN)

### 🔹 **1. BÁO CÁO TRẠNG THÁI TASK**

**Mục đích**: Cập nhật tiến độ và trạng thái hoàn thành của task được giao

**Quy trình**:
1. **Truy cập**: Đăng nhập → Menu "Danh sách công việc" hoặc "Dashboard"
2. **Xác định task**: Tìm task muốn báo cáo trạng thái
3. **Xem chi tiết**: Click "Xem" để mở trang chi tiết task
4. **Cập nhật trạng thái**: 
   - Chọn "Đã hoàn thành" nếu hoàn thành task
   - Task sẽ chuyển sang trạng thái "Chờ duyệt"
5. **Chờ phê duyệt**: Admin/Director/Manager liên quan sẽ duyệt task
6. **Nhận thông báo**: Khi task được duyệt hoặc từ chối

**Lưu ý**: 
- Chỉ có thể cập nhật trạng thái task được giao cho mình
- Không thể thay đổi trạng thái task của người khác

### 🔹 **2. TẠO YÊU CẦU HỖ TRỢ**

**Mục đích**: Gửi yêu cầu hỗ trợ đến Manager/Director/Admin

**Quy trình**:
1. **Truy cập**: Menu "Yêu cầu hỗ trợ" → "Tạo yêu cầu mới"
2. **Nhập thông tin cơ bản**:
   - **Tiêu đề**: Mô tả ngắn gọn yêu cầu
   - **Mô tả**: Chi tiết yêu cầu hỗ trợ
3. **Chọn người nhận**: Chọn Manager/Director/Admin để nhận yêu cầu
4. **Thiết lập ưu tiên**: Chọn mức độ ưu tiên (Thấp/Trung bình/Cao)
5. **Đặt deadline**: Chọn thời hạn cần hoàn thành (nếu có)
6. **Đính kèm file**: Upload hình ảnh, tài liệu liên quan (nếu có)
7. **Gửi yêu cầu**: Click "Tạo yêu cầu"

**Lưu ý**:
- Có thể chọn nhiều người nhận
- File đính kèm tối đa 50MB
- Yêu cầu sẽ được gửi thông báo đến người nhận

### 🔹 **3. XEM BÁO CÁO CÔNG VIỆC**

**Mục đích**: Tạo và xem báo cáo công việc hàng ngày/tuần/tháng

**Quy trình**:
1. **Truy cập**: Menu "Báo cáo công việc"
2. **Chọn thời gian**: Năm → Tháng → Tuần
3. **Tạo báo cáo**: Điền thông tin công việc đã thực hiện
4. **Lưu báo cáo**: Click "Lưu" để lưu báo cáo
5. **Xem lịch sử**: Xem các báo cáo đã tạo trước đó

### 🔹 **4. XEM THÔNG BÁO**

**Mục đích**: Nhận và xem thông báo từ hệ thống

**Quy trình**:
1. **Truy cập**: Icon thông báo trên header
2. **Xem danh sách**: Xem tất cả thông báo chưa đọc
3. **Đọc chi tiết**: Click vào thông báo để xem chi tiết
4. **Đánh dấu đã đọc**: Thông báo sẽ tự động đánh dấu đã đọc

### 🔹 **5. XEM DASHBOARD CÁ NHÂN**

**Mục đích**: Tổng quan công việc và tiến độ cá nhân

**Quy trình**:
1. **Truy cập**: Trang chủ sau khi đăng nhập
2. **Xem thống kê**: 
   - Số task đang thực hiện
   - Số task đã hoàn thành
   - Số task chờ duyệt
   - Số task trễ hạn
3. **Xem danh sách task**: Task được giao và task tự tạo
4. **Lọc và sắp xếp**: Theo trạng thái, thời gian, ưu tiên

---

## 👨‍💼 MANAGER (QUẢN LÝ PHÒNG BAN)

### 🔹 **1. TẠO CÔNG VIỆC MỚI**

**Mục đích**: Giao việc cho nhân viên trong phòng ban

**Quy trình**:
1. **Truy cập**: Menu "Công việc" → "Tạo công việc mới"
2. **Nhập thông tin cơ bản**:
   - **Tiêu đề**: Tên công việc
   - **Mô tả**: Chi tiết công việc cần thực hiện
3. **Chọn loại công việc**:
   - **Công việc thường**: Chỉ cho phòng ban của mình
   - **Công việc đa phòng ban**: Chọn nhiều phòng ban (phải có ít nhất 1 người từ phòng ban mình)
4. **Chọn người nhận**:
   - **Giao cho 1 người**: Chọn nhân viên cụ thể
   - **Giao cho nhiều người**: Chọn nhiều nhân viên
5. **Thiết lập thời gian**: Chọn deadline (bắt buộc)
6. **Thiết lập ưu tiên**: Thấp/Trung bình/Cao
7. **Đính kèm file**: Upload tài liệu liên quan (nếu có)
8. **Chọn người theo dõi**: Chọn người sẽ nhận thông báo khi task có thay đổi
9. **Tạo công việc**: Click "Giao việc"

**Lưu ý**:
- Chỉ có thể giao việc cho Employee
- Công việc thường: Chỉ cho nhân viên cùng phòng ban
- Công việc đa phòng ban: Phải có ít nhất 1 người từ phòng ban mình

### 🔹 **2. QUẢN LÝ CÔNG VIỆC PHÒNG BAN**

**Mục đích**: Theo dõi và quản lý tiến độ công việc của phòng ban

**Quy trình**:
1. **Truy cập**: Menu "Công việc" → "Danh sách công việc"
2. **Xem danh sách**: Tất cả công việc của phòng ban
3. **Lọc và tìm kiếm**:
   - Theo trạng thái (đang làm, chờ duyệt, hoàn thành, trễ hạn)
   - Theo người thực hiện
   - Theo thời gian
   - Theo ưu tiên
4. **Cập nhật trạng thái**: Duyệt/từ chối task đã hoàn thành
5. **Thêm comment**: Góp ý hoặc hướng dẫn thêm
6. **Xem lịch sử**: Theo dõi các thay đổi của task

### 🔹 **3. QUẢN LÝ NHÂN VIÊN PHÒNG BAN**

**Mục đích**: Quản lý thông tin nhân viên trong phòng ban

**Quy trình**:
1. **Truy cập**: Menu "Quản lý nhân viên"
2. **Xem danh sách**: Tất cả nhân viên trong phòng ban
3. **Thêm nhân viên mới**:
   - Click "Thêm nhân viên"
   - Điền thông tin: Tên, email, số điện thoại, phòng ban
   - Chọn role: Employee
   - Tạo tài khoản
4. **Sửa thông tin nhân viên**:
   - Click "Sửa" bên cạnh nhân viên
   - Cập nhật thông tin cần thiết
   - Lưu thay đổi
5. **Xóa nhân viên**: Click "Xóa" (cẩn thận với thao tác này)
6. **Tìm kiếm và lọc**: Theo tên, email, phòng ban

**Lưu ý**:
- Chỉ có thể quản lý nhân viên trong phòng ban của mình
- Không thể tạo Manager hoặc Admin
- Không thể quản lý nhân viên phòng ban khác

### 🔹 **4. XỬ LÝ YÊU CẦU HỖ TRỢ**

**Mục đích**: Xử lý yêu cầu hỗ trợ từ nhân viên

**Quy trình**:
1. **Truy cập**: Menu "Yêu cầu hỗ trợ"
2. **Xem danh sách**: Yêu cầu được gửi đến mình
3. **Xem chi tiết**: Click "Xem" để xem chi tiết yêu cầu
4. **Xử lý yêu cầu**:
   - **Duyệt**: Nếu có thể hỗ trợ
   - **Từ chối**: Nếu không thể hỗ trợ (kèm lý do)
   - **Chuyển tiếp**: Chuyển đến Manager/Director khác
5. **Thêm comment**: Góp ý hoặc hướng dẫn
6. **Cập nhật trạng thái**: Theo dõi tiến độ xử lý

### 🔹 **5. XEM BÁO CÁO CÔNG VIỆC PHÒNG BAN**

**Mục đích**: Theo dõi báo cáo công việc của nhân viên

**Quy trình**:
1. **Truy cập**: Menu "Báo cáo công việc"
2. **Chọn thời gian**: Năm → Tháng → Tuần
3. **Xem báo cáo**: Tất cả báo cáo của nhân viên trong phòng ban
4. **Lọc theo nhân viên**: Chọn nhân viên cụ thể
5. **Xuất báo cáo**: Tải về file Excel/PDF (nếu có)

### 🔹 **6. XEM DASHBOARD PHÒNG BAN**

**Mục đích**: Tổng quan tình hình phòng ban

**Quy trình**:
1. **Truy cập**: Trang chủ sau khi đăng nhập
2. **Xem thống kê phòng ban**:
   - Tổng số nhân viên
   - Số task đang thực hiện
   - Số task đã hoàn thành
   - Số task trễ hạn
   - Số yêu cầu hỗ trợ chờ xử lý
3. **Xem task đa phòng ban**: Task có tham gia
4. **Xem thông báo**: Thông báo liên quan đến phòng ban

---

## 👔 DIRECTOR (GIÁM ĐỐC)

### 🔹 **1. TẠO CÔNG VIỆC ĐA PHÒNG BAN**

**Mục đích**: Giao việc cho nhiều phòng ban cùng lúc

**Quy trình**:
1. **Truy cập**: Menu "Công việc" → "Tạo công việc mới"
2. **Nhập thông tin cơ bản**:
   - **Tiêu đề**: Tên công việc
   - **Mô tả**: Chi tiết công việc cần thực hiện
3. **Chọn loại công việc**:
   - **Công việc thường**: Chọn 1 phòng ban
   - **Công việc đa phòng ban**: Chọn nhiều phòng ban (không bị hạn chế)
4. **Chọn người nhận**:
   - **Giao cho 1 người**: Chọn bất kỳ nhân viên nào
   - **Giao cho nhiều người**: Chọn nhiều nhân viên từ các phòng ban khác nhau
5. **Thiết lập thời gian**: Chọn deadline
6. **Thiết lập ưu tiên**: Thấp/Trung bình/Cao
7. **Đính kèm file**: Upload tài liệu liên quan
8. **Chọn người theo dõi**: Chọn người sẽ nhận thông báo
9. **Tạo công việc**: Click "Giao việc"

**Lưu ý**:
- Có thể giao việc cho Employee và Manager
- Không bị hạn chế về phòng ban
- Có thể tạo công việc đa phòng ban mà không cần có người từ phòng ban mình

### 🔹 **2. QUẢN LÝ TOÀN BỘ CÔNG VIỆC**

**Mục đích**: Theo dõi và quản lý tất cả công việc trong công ty

**Quy trình**:
1. **Truy cập**: Menu "Công việc" → "Danh sách công việc"
2. **Xem danh sách**: Tất cả công việc trong hệ thống
3. **Lọc và tìm kiếm**:
   - Theo phòng ban
   - Theo trạng thái
   - Theo người thực hiện
   - Theo thời gian
   - Theo ưu tiên
4. **Cập nhật trạng thái**: Duyệt/từ chối task
5. **Thêm comment**: Góp ý hoặc hướng dẫn
6. **Xem lịch sử**: Theo dõi các thay đổi
7. **Xuất báo cáo**: Tải về file Excel/PDF

### 🔹 **3. QUẢN LÝ NHÂN VIÊN TOÀN CÔNG TY**

**Mục đích**: Quản lý thông tin nhân viên toàn công ty

**Quy trình**:
1. **Truy cập**: Menu "Quản lý nhân viên"
2. **Xem danh sách**: Tất cả nhân viên trong công ty
3. **Thêm nhân viên mới**:
   - Click "Thêm nhân viên"
   - Điền thông tin: Tên, email, số điện thoại, phòng ban
   - Chọn role: Employee hoặc Manager
   - Tạo tài khoản
4. **Sửa thông tin nhân viên**:
   - Click "Sửa" bên cạnh nhân viên
   - Cập nhật thông tin, phòng ban, role
   - Lưu thay đổi
5. **Xóa nhân viên**: Click "Xóa"
6. **Tìm kiếm và lọc**: Theo tên, email, phòng ban, role
7. **Xuất danh sách**: Tải về file Excel

**Lưu ý**:
- Có thể quản lý nhân viên tất cả phòng ban
- Có thể tạo Manager
- Có thể thay đổi role của nhân viên
- Có thể di chuyển nhân viên giữa các phòng ban

### 🔹 **4. QUẢN LÝ PHÒNG BAN**

**Mục đích**: Quản lý thông tin phòng ban

**Quy trình**:
1. **Truy cập**: Menu "Quản lý phòng ban"
2. **Xem danh sách**: Tất cả phòng ban
3. **Thêm phòng ban mới**:
   - Click "Thêm phòng ban"
   - Điền tên phòng ban
   - Lưu thông tin
4. **Sửa thông tin phòng ban**:
   - Click "Sửa" bên cạnh phòng ban
   - Cập nhật tên phòng ban
   - Lưu thay đổi
5. **Xóa phòng ban**: Click "Xóa" (cẩn thận với thao tác này)

### 🔹 **5. XỬ LÝ YÊU CẦU HỖ TRỢ**

**Mục đích**: Xử lý yêu cầu hỗ trợ từ nhân viên và Manager

**Quy trình**:
1. **Truy cập**: Menu "Yêu cầu hỗ trợ"
2. **Xem danh sách**: Tất cả yêu cầu hỗ trợ
3. **Xem chi tiết**: Click "Xem" để xem chi tiết
4. **Xử lý yêu cầu**:
   - **Duyệt**: Nếu có thể hỗ trợ
   - **Từ chối**: Nếu không thể hỗ trợ (kèm lý do)
   - **Chuyển tiếp**: Chuyển đến người khác
5. **Thêm comment**: Góp ý hoặc hướng dẫn
6. **Cập nhật trạng thái**: Theo dõi tiến độ xử lý

### 🔹 **6. XEM BÁO CÁO TOÀN CÔNG TY**

**Mục đích**: Theo dõi báo cáo công việc toàn công ty

**Quy trình**:
1. **Truy cập**: Menu "Báo cáo công việc"
2. **Chọn thời gian**: Năm → Tháng → Tuần
3. **Xem báo cáo**: Tất cả báo cáo của nhân viên
4. **Lọc theo phòng ban**: Chọn phòng ban cụ thể
5. **Lọc theo nhân viên**: Chọn nhân viên cụ thể
6. **Xuất báo cáo**: Tải về file Excel/PDF

### 🔹 **7. XEM DASHBOARD TOÀN CÔNG TY**

**Mục đích**: Tổng quan tình hình toàn công ty

**Quy trình**:
1. **Truy cập**: Trang chủ sau khi đăng nhập
2. **Xem thống kê toàn công ty**:
   - Tổng số nhân viên
   - Phân bố theo phòng ban
   - Số task đang thực hiện
   - Số task đã hoàn thành
   - Số task trễ hạn
   - Số yêu cầu hỗ trợ chờ xử lý
3. **Xem biểu đồ**: Thống kê theo thời gian
4. **Xem thông báo**: Thông báo quan trọng

---

## 🔧 ADMIN (QUẢN TRỊ VIÊN)

### 🔹 **1. TẠO CÔNG VIỆC TOÀN QUYỀN**

**Mục đích**: Giao việc cho bất kỳ ai trong hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Công việc" → "Tạo công việc mới"
2. **Nhập thông tin cơ bản**:
   - **Tiêu đề**: Tên công việc
   - **Mô tả**: Chi tiết công việc cần thực hiện
3. **Chọn loại công việc**:
   - **Công việc thường**: Chọn 1 phòng ban
   - **Công việc đa phòng ban**: Chọn nhiều phòng ban (không bị hạn chế)
4. **Chọn người nhận**:
   - **Giao cho 1 người**: Chọn bất kỳ ai (Employee, Manager, Director)
   - **Giao cho nhiều người**: Chọn nhiều người từ các phòng ban khác nhau
5. **Thiết lập thời gian**: Chọn deadline
6. **Thiết lập ưu tiên**: Thấp/Trung bình/Cao
7. **Đính kèm file**: Upload tài liệu liên quan
8. **Chọn người theo dõi**: Chọn người sẽ nhận thông báo
9. **Tạo công việc**: Click "Giao việc"

**Lưu ý**:
- Có thể giao việc cho bất kỳ ai (Employee, Manager, Director)
- Không bị hạn chế về phòng ban
- Có thể tạo công việc đa phòng ban mà không cần có người từ phòng ban mình

### 🔹 **2. QUẢN LÝ TOÀN BỘ HỆ THỐNG**

**Mục đích**: Quản lý và giám sát toàn bộ hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Công việc" → "Danh sách công việc"
2. **Xem danh sách**: Tất cả công việc trong hệ thống
3. **Lọc và tìm kiếm**:
   - Theo phòng ban
   - Theo trạng thái
   - Theo người thực hiện
   - Theo thời gian
   - Theo ưu tiên
4. **Cập nhật trạng thái**: Duyệt/từ chối task
5. **Thêm comment**: Góp ý hoặc hướng dẫn
6. **Xem lịch sử**: Theo dõi các thay đổi
7. **Xuất báo cáo**: Tải về file Excel/PDF
8. **Xóa công việc**: Xóa công việc không cần thiết

### 🔹 **3. QUẢN LÝ NGƯỜI DÙNG TOÀN QUYỀN**

**Mục đích**: Quản lý tất cả người dùng trong hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Quản lý nhân viên"
2. **Xem danh sách**: Tất cả người dùng trong hệ thống
3. **Thêm người dùng mới**:
   - Click "Thêm nhân viên"
   - Điền thông tin: Tên, email, số điện thoại, phòng ban
   - Chọn role: Employee, Manager, Director, hoặc Admin
   - Tạo tài khoản
4. **Sửa thông tin người dùng**:
   - Click "Sửa" bên cạnh người dùng
   - Cập nhật thông tin, phòng ban, role
   - Lưu thay đổi
5. **Xóa người dùng**: Click "Xóa"
6. **Tìm kiếm và lọc**: Theo tên, email, phòng ban, role
7. **Xuất danh sách**: Tải về file Excel
8. **Reset mật khẩu**: Đặt lại mật khẩu cho người dùng

**Lưu ý**:
- Có thể quản lý tất cả người dùng
- Có thể tạo bất kỳ role nào
- Có thể thay đổi role của bất kỳ ai
- Có thể di chuyển người dùng giữa các phòng ban

### 🔹 **4. QUẢN LÝ PHÒNG BAN TOÀN QUYỀN**

**Mục đích**: Quản lý tất cả phòng ban trong hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Quản lý phòng ban"
2. **Xem danh sách**: Tất cả phòng ban
3. **Thêm phòng ban mới**:
   - Click "Thêm phòng ban"
   - Điền tên phòng ban
   - Lưu thông tin
4. **Sửa thông tin phòng ban**:
   - Click "Sửa" bên cạnh phòng ban
   - Cập nhật tên phòng ban
   - Lưu thay đổi
5. **Xóa phòng ban**: Click "Xóa"
6. **Sắp xếp phòng ban**: Thay đổi thứ tự hiển thị

### 🔹 **5. XỬ LÝ YÊU CẦU HỖ TRỢ TOÀN QUYỀN**

**Mục đích**: Xử lý tất cả yêu cầu hỗ trợ trong hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Yêu cầu hỗ trợ"
2. **Xem danh sách**: Tất cả yêu cầu hỗ trợ
3. **Xem chi tiết**: Click "Xem" để xem chi tiết
4. **Xử lý yêu cầu**:
   - **Duyệt**: Nếu có thể hỗ trợ
   - **Từ chối**: Nếu không thể hỗ trợ (kèm lý do)
   - **Chuyển tiếp**: Chuyển đến người khác
5. **Thêm comment**: Góp ý hoặc hướng dẫn
6. **Cập nhật trạng thái**: Theo dõi tiến độ xử lý
7. **Xóa yêu cầu**: Xóa yêu cầu không cần thiết

### 🔹 **6. XEM BÁO CÁO TOÀN HỆ THỐNG**

**Mục đích**: Theo dõi báo cáo công việc toàn hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Báo cáo công việc"
2. **Chọn thời gian**: Năm → Tháng → Tuần
3. **Xem báo cáo**: Tất cả báo cáo của người dùng
4. **Lọc theo phòng ban**: Chọn phòng ban cụ thể
5. **Lọc theo người dùng**: Chọn người dùng cụ thể
6. **Xuất báo cáo**: Tải về file Excel/PDF
7. **Xóa báo cáo**: Xóa báo cáo không cần thiết

### 🔹 **7. XEM DASHBOARD TOÀN HỆ THỐNG**

**Mục đích**: Tổng quan toàn bộ hệ thống

**Quy trình**:
1. **Truy cập**: Trang chủ sau khi đăng nhập
2. **Xem thống kê toàn hệ thống**:
   - Tổng số người dùng
   - Phân bố theo role
   - Phân bố theo phòng ban
   - Số task đang thực hiện
   - Số task đã hoàn thành
   - Số task trễ hạn
   - Số yêu cầu hỗ trợ chờ xử lý
3. **Xem biểu đồ**: Thống kê theo thời gian
4. **Xem thông báo**: Tất cả thông báo quan trọng
5. **Quản lý hệ thống**: Cài đặt và cấu hình hệ thống

### 🔹 **8. QUẢN LÝ HỆ THỐNG**

**Mục đích**: Cài đặt và cấu hình hệ thống

**Quy trình**:
1. **Truy cập**: Menu "Cài đặt hệ thống"
2. **Cấu hình hệ thống**:
   - Cài đặt thông báo
   - Cài đặt email
   - Cài đặt bảo mật
   - Cài đặt backup
3. **Quản lý log**: Xem log hệ thống
4. **Quản lý cache**: Xóa cache hệ thống
5. **Quản lý database**: Backup và restore database

---

## 📊 KANBAN BOARD (TẤT CẢ ROLE)

### 🔹 **XEM KANBAN BOARD**

**Mục đích**: Xem công việc dưới dạng bảng Kanban

**Quy trình**:
1. **Truy cập**: Menu "Kanban Board"
2. **Xem các cột**:
   - **Chờ thực hiện**: Task chưa bắt đầu
   - **Đang thực hiện**: Task đang được làm
   - **Chờ duyệt**: Task đã hoàn thành, chờ duyệt
   - **Hoàn thành**: Task đã được duyệt
   - **Từ chối**: Task bị từ chối
3. **Xem chi tiết task**: Click vào task để xem chi tiết
4. **Lọc task**: Theo phòng ban, người thực hiện, ưu tiên

**Lưu ý**:
- **Employee**: Chỉ có thể xem, không thể drag & drop
- **Manager**: Có thể drag & drop task của phòng ban mình
- **Director/Admin**: Có thể drag & drop tất cả task

---

## 🔔 THÔNG BÁO (TẤT CẢ ROLE)

### 🔹 **XEM VÀ QUẢN LÝ THÔNG BÁO**

**Mục đích**: Nhận và quản lý thông báo từ hệ thống

**Quy trình**:
1. **Truy cập**: Icon thông báo trên header
2. **Xem danh sách**: Tất cả thông báo
3. **Lọc thông báo**: Theo loại, thời gian, trạng thái
4. **Đọc chi tiết**: Click vào thông báo để xem chi tiết
5. **Đánh dấu đã đọc**: Thông báo sẽ tự động đánh dấu đã đọc
6. **Xóa thông báo**: Xóa thông báo không cần thiết

**Các loại thông báo**:
- **Task được giao**: Khi có task mới được giao
- **Task hoàn thành**: Khi task được hoàn thành
- **Task được duyệt**: Khi task được duyệt
- **Task bị từ chối**: Khi task bị từ chối
- **Yêu cầu hỗ trợ**: Khi có yêu cầu hỗ trợ mới
- **Comment mới**: Khi có comment mới trên task
- **Thông báo hệ thống**: Thông báo từ hệ thống

---

## 📈 BÁO CÁO VÀ THỐNG KÊ

### 🔹 **XEM BÁO CÁO THEO ROLE**

**Employee**:
- Báo cáo công việc cá nhân
- Thống kê task cá nhân

**Manager**:
- Báo cáo công việc phòng ban
- Thống kê task phòng ban
- Thống kê nhân viên phòng ban

**Director**:
- Báo cáo công việc toàn công ty
- Thống kê task toàn công ty
- Thống kê nhân viên toàn công ty

**Admin**:
- Báo cáo toàn hệ thống
- Thống kê toàn hệ thống
- Báo cáo hiệu suất hệ thống

---

## 🚨 LƯU Ý QUAN TRỌNG

### **Bảo mật**:
- Không chia sẻ tài khoản đăng nhập
- Đăng xuất sau khi sử dụng xong
- Báo cáo ngay khi phát hiện lỗi bảo mật

### **Quyền hạn**:
- Mỗi role có quyền hạn khác nhau
- Không thể thực hiện thao tác ngoài quyền hạn
- Liên hệ Admin nếu cần quyền hạn cao hơn

### **Hỗ trợ**:
- Sử dụng chức năng "Yêu cầu hỗ trợ" khi cần giúp đỡ
- Liên hệ IT Support khi gặp lỗi kỹ thuật
- Đọc hướng dẫn trước khi sử dụng

---

*Tài liệu này được cập nhật thường xuyên. Vui lòng kiểm tra phiên bản mới nhất.*
