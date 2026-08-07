# CI/CD cPanel (Git + sshpass)

Workflow: `.github/workflows/deploy.yml`

```
push main → php artisan test (sqlite) → git push -f cPanel → ssh artisan migrate
```

Phù hợp hosting **cPanel Git Version Control** (SSH + password), đúng kiểu file workflow bạn đưa.

## Secrets (GitHub → Settings → Secrets and variables → Actions)

| Secret | Bắt buộc | Ví dụ / ghi chú |
|--------|----------|------------------|
| `CPANEL_SSH_HOST` | ✅ | `server.hpfoods.com` hoặc IP |
| `CPANEL_SSH_PORT` | ✅ | Thường `22` hoặc `2222` (cPanel) |
| `CPANEL_SSH_USER` | ✅ | Username cPanel (vd `hpfoods`) |
| `CPANEL_SSH_PASSWORD` | ✅ | Mật khẩu SSH / cPanel |
| `CPANEL_REPO_PATH` | ✅ | Path bare repo Git trên cPanel, **bắt đầu bằng `/`** — vd `/home/hpfoods/repositories/error_order_management.git` |
| `CPANEL_APP_PATH` | ⭐ khuyên dùng | Thư mục app đã deploy (có file `artisan`) — vd `/home/hpfoods/work.hpfoods.com.vn` hoặc path deployment trong cPanel |

### Lấy `CPANEL_REPO_PATH`

cPanel → **Git Version Control** → repo → **Clone URL / Repository Path**  
Dạng: `/home/USER/repositories/TÊN_REPO.git`

Workflow ghép thành:

```text
ssh://USER@HOST:PORT/home/USER/repositories/TÊN_REPO.git
```

### Lấy `CPANEL_APP_PATH`

cPanel Git → **Manage** → **Deployment Path** (nơi checkout code live).  
Thư mục đó phải có `artisan`, `composer.json`, và file `.env` production (tạo tay 1 lần, không commit).

## Cấu hình cPanel 1 lần

1. Tạo Git repository trong cPanel, trỏ deployment path tới app Laravel
2. Bật **Auto deploy** / deploy after push (nếu có)
3. Clone 1 lần hoặc push từ local để seed repo
4. Trên **APP_PATH**: tạo `.env` production, `php artisan key:generate` nếu cần
5. Đảm bảo SSH Access bật, dùng đúng port

### Composer trên server (nếu cPanel chưa auto-install vendor)

Deploy hook của cPanel nên chạy `composer install --no-dev`.  
Hoặc bổ sung bước SSH sau migrate (khi server có composer).

Thường trên shared host:

```bash
cd $HOME/path-to-app
/usr/local/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

Có thể thêm lệnh tương tự trong step “Run artisan migrate” nếu cần.

## Lưu ý quan trọng

| Chủ đề | Chi tiết |
|--------|----------|
| **Force push (`-f`)** | Đúng pattern cPanel khi working tree bẩn sau hook; chỉ push branch `main` |
| **Password SSH** | Kém an toàn hơn key; nếu cPanel hỗ trợ key, nên chuyển `SSH_PRIVATE_KEY` sau |
| **Test fail → không deploy** | Job `deploy` có `needs: laravel-tests` |
| **ExampleTest** | `/` redirect login → test mặc định assert 200 có thể fail — đã/ sẽ chỉnh test |
| **Vendor / node** | Git thường không chứa `vendor`/`public/build` → server phải `composer install` + (tuỳ) `npm run build` |

## Chạy thử

1. Điền secrets  
2. Push `main` **hoặc** Actions → **Laravel CI/CD (cPanel)** → **Run workflow**  
3. Xem log: Tests → Push cPanel → Migrate  

## Khác workflow SSH VPS cũ

| | cPanel (file này) | VPS SSH trước đó |
|--|-------------------|------------------|
| Push code | `git push` remote cPanel | `ssh` + `git pull` trên server |
| Auth | user + password (`sshpass`) | SSH private key |
| Secrets | `CPANEL_*` | `SSH_*`, `DEPLOY_PATH` |

Giữ `scripts/deploy.sh` nếu sau này chuyển VPS; workflow hiện tại **không** phụ thuộc file đó.
