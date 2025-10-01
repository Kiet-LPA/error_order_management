# Hướng dẫn hiển thị Avatar trong UI

## 📋 Tổng quan
Avatar đã được đồng bộ và hiển thị trên tất cả các hệ thống:
- ✅ **Laravel Main App** - Hệ thống chính
- ✅ **Checkin System** - Hệ thống điểm danh
- ✅ **RentalCar System** - Hệ thống thuê xe

## 🎨 CSS Styles

File CSS chung đã được tạo: `public/css/avatar-styles.css`

### Cách sử dụng CSS:

#### Trong Laravel Blade:
```blade
<link rel="stylesheet" href="{{ asset('css/avatar-styles.css') }}">
```

#### Trong PHP thuần (Checkin/RentalCar):
```html
<link rel="stylesheet" href="/css/avatar-styles.css">
```

## 💡 Cách hiển thị Avatar

### 1. Avatar đơn giản
```html
<img src="<?= getUserAvatar($user['avatar'], $user['full_name']) ?>" 
     alt="<?= htmlspecialchars($user['full_name']) ?>" 
     class="user-avatar">
```

### 2. Avatar với thông tin user
```html
<div class="user-info">
    <img src="<?= getUserAvatar($user['avatar'], $user['full_name']) ?>" 
         alt="<?= htmlspecialchars($user['full_name']) ?>" 
         class="avatar-img">
    <div class="user-details">
        <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
    </div>
</div>
```

### 3. Avatar với kích thước khác nhau
```html
<!-- Small -->
<img src="<?= getUserAvatar($user['avatar'], $user['name']) ?>" 
     class="avatar-img avatar-sm">

<!-- Medium (default) -->
<img src="<?= getUserAvatar($user['avatar'], $user['name']) ?>" 
     class="avatar-img avatar-md">

<!-- Large -->
<img src="<?= getUserAvatar($user['avatar'], $user['name']) ?>" 
     class="avatar-img avatar-lg">

<!-- Extra Large -->
<img src="<?= getUserAvatar($user['avatar'], $user['name']) ?>" 
     class="avatar-img avatar-xl">
```

### 4. Avatar với status badge
```html
<div class="avatar-with-status">
    <img src="<?= getUserAvatar($user['avatar'], $user['name']) ?>" 
         class="avatar-img">
    <span class="avatar-status-badge status-online"></span>
</div>
```

Status classes:
- `status-online` - Xanh lá (online)
- `status-offline` - Đỏ (offline)
- `status-away` - Vàng (away)

### 5. Avatar Group (nhiều avatars)
```html
<div class="avatar-group">
    <img src="<?= getUserAvatar($user1['avatar'], $user1['name']) ?>" class="avatar-img">
    <img src="<?= getUserAvatar($user2['avatar'], $user2['name']) ?>" class="avatar-img">
    <img src="<?= getUserAvatar($user3['avatar'], $user3['name']) ?>" class="avatar-img">
    <div class="avatar-more">+5</div>
</div>
```

## 📍 Các trang đã cập nhật

### Checkin System
- ✅ `/checkin/reports.php` - Báo cáo điểm danh
  - Thống kê điểm danh theo nhân viên
  - Yêu cầu GPS
- ✅ `/checkin/manage_users.php` - Quản lý users
  - Danh sách users với avatar

### RentalCar System
- ✅ `/rentalcar/admin/rentals.php` - Quản lý thuê xe
  - Danh sách người thuê với avatar

## 🔧 Function Helper

### PHP Function: `getUserAvatar()`

```php
/**
 * Lấy URL avatar của user
 * @param string|null $avatar - Đường dẫn avatar
 * @param string $name - Tên user (để tạo fallback)
 * @return string - URL avatar hoặc SVG fallback
 */
function getUserAvatar($avatar, $name = '') {
    if ($avatar && file_exists(__DIR__ . '/..' . $avatar)) {
        return $avatar;
    }
    
    // Tạo avatar mặc định với chữ cái đầu
    $initial = $name ? strtoupper(substr($name, 0, 1)) : '?';
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='48'%3E%3Crect width='48' height='48' fill='%23667eea'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='24' fill='white' font-weight='bold'%3E{$initial}%3C/text%3E%3C/svg%3E";
}
```

## 🎯 Best Practices

### 1. Luôn có fallback
```php
// ✅ Good - Có fallback
<img src="<?= getUserAvatar($user['avatar'], $user['name']) ?>">

// ❌ Bad - Không có fallback
<img src="<?= $user['avatar'] ?>">
```

### 2. Sử dụng class CSS
```html
<!-- ✅ Good - Sử dụng class -->
<img src="..." class="avatar-img">

<!-- ❌ Bad - Inline style -->
<img src="..." style="width: 40px; border-radius: 50%;">
```

### 3. Alt text cho accessibility
```html
<!-- ✅ Good -->
<img src="..." alt="<?= htmlspecialchars($user['name']) ?>">

<!-- ❌ Bad -->
<img src="...">
```

## 🔄 Tự động đồng bộ

Avatar sẽ tự động đồng bộ từ Laravel khi:
- User upload avatar mới
- Admin cập nhật avatar cho user
- Chạy command: `php artisan sync:avatars`

## 📱 Responsive Design

Avatar tự động điều chỉnh kích thước trên mobile:
- Desktop: 40px
- Mobile: 32px

## 🎨 Customization

### Thay đổi màu fallback avatar:
```css
.avatar-placeholder {
    background: linear-gradient(135deg, #your-color 0%, #your-color-2 100%);
}
```

### Thay đổi border:
```css
.avatar-img {
    border: 3px solid #your-border-color;
}
```

### Thay đổi shadow:
```css
.avatar-img {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
```

## 🐛 Troubleshooting

### Avatar không hiển thị?
1. Kiểm tra avatar đã đồng bộ: `php artisan sync:avatars`
2. Kiểm tra file tồn tại: `storage/app/public/avatars/`
3. Kiểm tra symlink: `php artisan storage:link`

### Avatar bị vỡ layout?
1. Thêm CSS: `public/css/avatar-styles.css`
2. Kiểm tra container có `display: flex`

### Avatar không rounded?
1. Thêm class: `avatar-img` hoặc `user-avatar`
2. Hoặc CSS: `border-radius: 50%`

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. `checkin/config.php` - Function `getUserAvatar()`
2. `rentalcar/includes/config.php` - Function `getUserAvatar()`
3. `app/Observers/UserObserver.php` - Auto sync logic
4. `SYNC_AVATAR_GUIDE.md` - Hướng dẫn đồng bộ

