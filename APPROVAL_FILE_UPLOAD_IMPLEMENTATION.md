# Triển khai chức năng Upload File cho Thảo luận Approval

## Tổng quan
Đã triển khai thành công chức năng upload file và ảnh (tối đa 5MB) cho phần Thảo luận trong hệ thống Approval. Chức năng này hoạt động mượt mà và tương thích với các chức năng thảo luận khác trong hệ thống.

## Các file đã được tạo/cập nhật

### 1. Database & Models
- **Migration**: `database/migrations/2025_01_22_120000_create_approval_comment_attachments_table.php`
  - Tạo bảng `approval_comment_attachments` để lưu trữ thông tin file đính kèm
  - Có đầy đủ các trường: original_name, file_name, file_path, file_url, mime_type, file_size, file_extension, meta

- **Model**: `app/Models/ApprovalCommentAttachment.php`
  - Model mới để quản lý file đính kèm của approval comments
  - Có các method: isImage(), isVideo(), isDocument(), isCompressed(), getFormattedSize(), getIconClass()

- **Model cập nhật**: `app/Models/ApprovalComment.php`
  - Thêm relationship `attachments()` để liên kết với ApprovalCommentAttachment

### 2. Controller & Routes
- **Controller cập nhật**: `app/Http/Controllers/CommentController.php`
  - Cập nhật method `storeApprovalComment()` để xử lý file upload
  - Thêm validation: file tối đa 5MB, hỗ trợ nhiều loại file
  - Thêm các method mới:
    - `viewApprovalAttachment()`: Xem file inline
    - `downloadApprovalAttachment()`: Download file
    - `deleteApprovalAttachment()`: Xóa file

- **Routes cập nhật**: `routes/web.php`
  - Thêm 3 routes mới cho approval comment attachments:
    - `approval.attachment.view`
    - `approval.attachment.download`
    - `approval.attachment.delete`

### 3. View & Frontend
- **View cập nhật**: `resources/views/approval/show.blade.php`
  - Thêm form upload file với drag & drop
  - Thêm preview file trước khi upload
  - Hiển thị attachments trong comments
  - Thêm CSS và JavaScript cho UX tốt

- **Controller cập nhật**: `app/Http/Controllers/ApprovalController.php`
  - Cập nhật method `show()` để load `comments.attachments`

### 4. Storage
- Tạo thư mục: `public/storage/approval-comments/`
- File được lưu trong: `storage/app/public/approval-comments/`

## Tính năng chính

### 1. Upload File
- **Drag & Drop**: Kéo thả file vào vùng upload
- **Click to Select**: Click để chọn file
- **Multiple Files**: Hỗ trợ upload nhiều file cùng lúc
- **File Preview**: Hiển thị preview file trước khi upload
- **Validation**: Kiểm tra kích thước (5MB) và loại file

### 2. Loại file hỗ trợ
- **Ảnh**: jpg, jpeg, png, gif, webp
- **Video**: mp4, avi, mov, wmv, flv, webm
- **Documents**: pdf, doc, docx, xls, xlsx, ppt, pptx
- **Text**: txt
- **Archive**: zip, rar

### 3. Hiển thị Attachments
- **Ảnh**: Hiển thị thumbnail, click để xem full size
- **Video**: Hiển thị icon play, click để xem
- **Documents**: Hiển thị icon theo loại file, click để download
- **File Info**: Hiển thị tên file và kích thước

### 4. Quản lý File
- **View**: Xem file inline trong browser
- **Download**: Tải file về máy
- **Delete**: Xóa file (chỉ người tạo comment hoặc admin/director)

### 5. Bảo mật
- **Permission Check**: Kiểm tra quyền xem/download/delete
- **File Validation**: Kiểm tra loại file và kích thước
- **Safe File Names**: Tạo tên file an toàn, tránh trùng lặp

## Cách sử dụng

### 1. Upload File
1. Vào trang chi tiết Approval Request
2. Scroll xuống phần "Thảo luận"
3. Nhập nội dung comment
4. Kéo thả file vào vùng upload hoặc click để chọn
5. Xem preview file đã chọn
6. Click "Gửi bình luận"

### 2. Xem File
- **Ảnh**: Click vào thumbnail để xem full size
- **Video**: Click vào icon play để xem video
- **Documents**: Click vào icon để download

### 3. Quản lý File
- **Xóa**: Click nút "Xóa" bên cạnh file (chỉ hiện với người có quyền)

## Cấu hình

### 1. Giới hạn File
- **Kích thước tối đa**: 5MB mỗi file
- **Tổng kích thước**: 50MB cho tất cả file trong 1 comment
- **Số lượng file**: Không giới hạn (trong giới hạn tổng kích thước)

### 2. Thư mục Storage
- **Upload**: `storage/app/public/approval-comments/`
- **Public**: `public/storage/approval-comments/`
- **URL**: `asset('storage/approval-comments/filename')`

## Tương thích
- ✅ Tương thích với hệ thống thảo luận hiện có
- ✅ Không ảnh hưởng đến chức năng khác
- ✅ Sử dụng cùng pattern với task comments
- ✅ Responsive design cho mobile

## Lưu ý kỹ thuật
1. **Migration**: Cần chạy migration để tạo bảng mới
2. **Storage Link**: Đảm bảo `php artisan storage:link` đã được chạy
3. **Permissions**: Đảm bảo thư mục storage có quyền ghi
4. **File Cleanup**: File sẽ tự động xóa khi xóa comment (cascade delete)

## Kết luận
Chức năng upload file cho thảo luận Approval đã được triển khai hoàn chỉnh với:
- ✅ Giao diện thân thiện, dễ sử dụng
- ✅ Bảo mật tốt với kiểm tra quyền
- ✅ Hiệu suất cao với validation và preview
- ✅ Tương thích hoàn toàn với hệ thống hiện có
- ✅ Hỗ trợ đầy đủ các loại file phổ biến
