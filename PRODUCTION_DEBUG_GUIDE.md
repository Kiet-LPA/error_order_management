# Hướng dẫn Debug Production - Hệ thống Mượn Xe

## Vấn đề hiện tại
- Local: Mượn và trả xe hoạt động bình thường
- Production: Trả xe báo lỗi "không có quyền"

## Các bước Debug

### 1. Kiểm tra Session Configuration
Truy cập: `/rental/debug-auth` để xem thông tin session và authentication

### 2. Kiểm tra Session Test
Truy cập: `/rental/test-session` để test session write/read

### 3. Kiểm tra Logs
```bash
# Xem logs Laravel
tail -f storage/logs/laravel.log

# Tìm logs liên quan đến rental
grep -i "rental\|return\|session" storage/logs/laravel.log
```

### 4. Kiểm tra Database Session
```sql
-- Kiểm tra bảng sessions
SELECT * FROM sessions ORDER BY last_activity DESC LIMIT 10;

-- Kiểm tra session của user hiện tại
SELECT * FROM sessions WHERE user_id = [USER_ID];
```

### 5. Các nguyên nhân có thể

#### A. Session Driver không đúng
**Kiểm tra file .env:**
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

**Kiểm tra config:**
```bash
php artisan config:show session
```

#### B. Database Session Table
**Kiểm tra bảng sessions tồn tại:**
```sql
SHOW TABLES LIKE 'sessions';
DESCRIBE sessions;
```

#### C. Session Cookie Issues
**Kiểm tra cookie settings:**
- `SESSION_SECURE_COOKIE=true` (cho HTTPS)
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`

#### D. Cache Issues
**Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan session:table
php artisan migrate
```

### 6. Debug Commands

#### Kiểm tra session driver:
```bash
php artisan tinker
>>> config('session.driver')
>>> config('session.table')
>>> config('session.connection')
```

#### Test session trong tinker:
```bash
php artisan tinker
>>> session(['test' => 'value'])
>>> session('test')
>>> auth()->user()
```

### 7. Production Fixes

#### A. Đảm bảo .env đúng:
```env
APP_ENV=production
APP_DEBUG=false
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

#### B. Chạy migration:
```bash
php artisan migrate --force
```

#### C. Set permissions:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

#### D. Clear và rebuild:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 8. Monitoring

#### Thêm vào .env để debug:
```env
LOG_LEVEL=debug
LOG_CHANNEL=stack
```

#### Kiểm tra logs real-time:
```bash
tail -f storage/logs/laravel.log | grep -E "(rental|session|auth)"
```

### 9. Fallback Solution

Nếu vẫn có vấn đề, có thể thêm fallback authentication:

```php
// Trong returnCar method
if ($rental->user_id !== auth()->id()) {
    // Fallback: Check by email or other identifier
    $user = auth()->user();
    $rentalUser = $rental->user;
    
    if ($user && $rentalUser && $user->email === $rentalUser->email) {
        // Allow return if emails match
        \Log::info('Fallback authentication successful', [
            'user_email' => $user->email,
            'rental_user_email' => $rentalUser->email
        ]);
    } else {
        return back()->with('error', 'Bạn không có quyền trả xe này!');
    }
}
```

## Kết quả mong đợi

Sau khi debug, bạn sẽ thấy:
1. Session hoạt động đúng
2. Authentication ổn định
3. Logs chi tiết về mọi request
4. Thông báo lỗi rõ ràng cho user

## Liên hệ

Nếu vẫn có vấn đề, hãy cung cấp:
1. Output từ `/rental/debug-auth`
2. Logs từ `storage/logs/laravel.log`
3. Cấu hình session từ `php artisan config:show session`
