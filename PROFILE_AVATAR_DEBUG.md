# 🐛 Debug Avatar Upload trong Profile

## ✅ Những gì đã sửa

### 1. **Form đã đúng**
- ✅ Form có `enctype="multipart/form-data"` (line 109 trong edit.blade.php)
- ✅ Input có `name="avatar"` (line 21 trong avatar-form.blade.php)
- ✅ Form action đúng: `route('profile.update')`

### 2. **Controller đã đúng**
- ✅ Validate: `'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']`
- ✅ Check: `if ($request->hasFile('avatar'))`
- ✅ Store: `$file->storeAs('avatars', $filename, 'public')`
- ✅ Save filename: `$user->avatar = $filename`

### 3. **Visual Feedback**
- ✅ Preview ảnh ngay khi chọn
- ✅ Hiển thị tên file đã chọn
- ✅ Hiển thị kích thước file
- ✅ Animation scale khi preview

## 🧪 Cách test

### Bước 1: Mở trang Profile
```
http://localhost/profile
```

### Bước 2: Mở Console (F12)
Click tab "Console"

### Bước 3: Chọn ảnh
1. Click nút "Chọn tập tin"
2. Chọn 1 ảnh (JPG, PNG, GIF)
3. Quan sát:
   - ✅ Avatar preview thay đổi ngay
   - ✅ Hiển thị: "Đã chọn: filename.jpg (XX KB)"
   - ✅ Nút "Lưu tất cả" đổi màu vàng

### Bước 4: Click "Lưu tất cả thay đổi"
Trong Console, bạn sẽ thấy:
```
Avatar file detected: your-image.jpg
Form data entries:
_token: ...
_method: PATCH
name: Your Name
email: your@email.com
avatar: your-image.jpg
```

### Bước 5: Kiểm tra kết quả
1. Trang reload
2. Avatar mới hiển thị
3. Thông báo: "Đã cập nhật: Ảnh đại diện, Thông tin"

## 📁 Kiểm tra file đã lưu

### Trong Laravel:
```bash
# Xem files trong storage
ls -la storage/app/public/avatars/

# Hoặc trên Windows
dir storage\app\public\avatars\
```

### Trong public (symlink):
```bash
# Kiểm tra symlink
ls -la public/storage/avatars/

# Nếu không có, tạo symlink
php artisan storage:link
```

## 🔧 Troubleshooting

### Vấn đề 1: File không được upload

**Nguyên nhân:** PHP upload_max_filesize quá nhỏ

**Giải pháp:**
```ini
# php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Vấn đề 2: Storage link không tồn tại

**Nguyên nhân:** Chưa tạo symlink

**Giải pháp:**
```bash
php artisan storage:link
```

### Vấn đề 3: Permission denied

**Nguyên nhân:** Folder không có quyền ghi

**Giải pháp:**
```bash
chmod -R 775 storage
chmod -R 775 public/storage
```

### Vấn đề 4: Avatar không hiển thị

**Kiểm tra:**
1. Console log có thấy "Avatar file detected" không?
2. File có tồn tại trong `storage/app/public/avatars/`?
3. URL avatar đúng chưa? Kiểm tra trong DevTools Network tab

## 📊 Check Log

```bash
# Xem log Laravel
tail -f storage/logs/laravel.log
```

Sau khi upload, bạn sẽ thấy:
```
[TIMESTAMP] local.INFO: Avatar upload started for user: X
[TIMESTAMP] local.INFO: Old avatar deleted: old_avatar.jpg
[TIMESTAMP] local.INFO: New avatar saved: 1234567890_1.jpg
```

## ✨ Features

### Auto Preview
- Khi chọn ảnh → Preview ngay lập tức
- Không cần upload server
- FileReader API

### File Info
- Tên file
- Kích thước
- Badge màu xanh

### Validation
- ✅ Max 2MB
- ✅ Chỉ chấp nhận JPG, PNG, GIF
- ✅ Alert nếu sai format

## 🎯 Expected Behavior

1. **Chọn ảnh:**
   - Preview ngay
   - Hiển thị tên file
   - Nút save đổi màu vàng

2. **Click Save:**
   - Nút hiển thị "Đang lưu..."
   - Upload file lên server
   - Lưu vào database

3. **Sau khi save:**
   - Page reload
   - Avatar mới hiển thị
   - Alert xanh: "Đã cập nhật: Ảnh đại diện"

## 🔍 Debug Checklist

- [ ] Form có `enctype="multipart/form-data"`?
- [ ] Input có `name="avatar"`?
- [ ] Input nằm TRONG tag `<form>`?
- [ ] Route `/profile` method PATCH tồn tại?
- [ ] Storage symlink đã tạo?
- [ ] Folder avatars có quyền ghi?
- [ ] PHP upload_max_filesize đủ lớn?
- [ ] Console log có "Avatar file detected"?

Nếu tất cả đều ✅ thì avatar sẽ upload thành công!

