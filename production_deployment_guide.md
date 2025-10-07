# Production Deployment Guide - Task Comments Feature

## 🚀 **Các bước deploy lên production:**

### 1. **Upload Code:**
- Upload tất cả file đã sửa lên server
- Đảm bảo permissions đúng cho thư mục storage

### 2. **Storage Setup (QUAN TRỌNG):**
```bash
# Tạo symbolic link cho storage
php artisan storage:link

# Hoặc nếu không có quyền, tạo thủ công:
ln -s /path/to/your/project/storage/app/public /path/to/your/project/public/storage

# Đảm bảo permissions:
chmod -R 755 storage/
chmod -R 755 public/storage/
```

### 3. **Database Migration (nếu cần):**
```bash
# Kiểm tra migrations
php artisan migrate:status

# Chạy migrations nếu cần
php artisan migrate
```

### 4. **Clear Cache:**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 5. **Kiểm tra Permissions:**
```bash
# Đảm bảo web server có quyền write vào storage
chown -R www-data:www-data storage/
chmod -R 775 storage/
```

## ⚠️ **Lưu ý quan trọng:**

### **File Storage Path:**
- File sẽ được lưu vào: `storage/app/public/task-comments/`
- URL truy cập: `public/storage/task-comments/`
- Đảm bảo symbolic link hoạt động đúng

### **Error Handling:**
- Code đã có error handling cho file không tồn tại
- Sẽ thử nhiều đường dẫn khác nhau
- Log errors để debug nếu cần

### **Security:**
- Routes đã có middleware auth
- Kiểm tra quyền truy cập file
- Validate file types và sizes

## 🧪 **Test sau khi deploy:**

1. **Upload file mới** trong task comment
2. **Click xem ảnh** - modal hiển thị đúng
3. **Download file** - hoạt động bình thường
4. **Kiểm tra logs** nếu có lỗi

## 🔧 **Troubleshooting:**

### Nếu gặp lỗi 403 Forbidden:
```bash
# Kiểm tra permissions
ls -la storage/app/public/
ls -la public/storage/

# Sửa permissions
chmod 755 storage/app/public/task-comments/
```

### Nếu gặp lỗi 404 File not found:
- Kiểm tra symbolic link
- Kiểm tra file có tồn tại trong storage/app/public/task-comments/
- Kiểm tra logs để xem đường dẫn nào được thử

### Nếu modal không hiển thị đúng:
- Kiểm tra Bootstrap CSS/JS đã load
- Kiểm tra console errors
- Kiểm tra z-index conflicts
