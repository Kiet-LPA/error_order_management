# CI/CD cPanel — cấu hình cho server hiện tại

Workflow: `.github/workflows/deploy.yml`

```
push main → php artisan test → git push -f cPanel → ssh migrate trong /home/ipoubwkrhosting/workflow
```

## Thông tin server (từ cPanel File Manager)

| Mục | Giá trị |
|-----|---------|
| cPanel host | `turboweb-032506.000nethost.com` |
| Home | `/home/ipoubwkrhosting` |
| **App path** (có `artisan`, `.env`) | `/home/ipoubwkrhosting/workflow` |
| SSH user (gần chắc) | `ipoubwkrhosting` |

## Điền GitHub Secrets

Repo → **Settings → Secrets and variables → Actions → New repository secret**

| Secret | Giá trị nên điền |
|--------|------------------|
| `CPANEL_SSH_HOST` | `turboweb-032506.000nethost.com` |
| `CPANEL_SSH_PORT` | `22` (nếu fail thử `2222`) |
| `CPANEL_SSH_USER` | `ipoubwkrhosting` |
| `CPANEL_SSH_PASSWORD` | *(mật khẩu cPanel / SSH — bạn tự điền, không gửi cho AI)* |
| `CPANEL_APP_PATH` | `/home/ipoubwkrhosting/workflow` |
| `CPANEL_REPO_PATH` | Xem bên dưới ⬇️ |

### `CPANEL_REPO_PATH` — bắt buộc tạo / lấy từ Git Version Control

Trong File Manager hiện **chưa thấy** thư mục `repositories`. Cần cấu hình **Git Version Control** trong cPanel 1 lần:

1. cPanel → tìm **Git Version Control** (hoặc *Version Control*)
2. **Create** repository:
   - **Repository Path**: gợi ý tạo trong home, ví dụ  
     `/home/ipoubwkrhosting/repositories/workflow.git`  
     (hoặc path cPanel gợi ý)
   - **Repository Name**: `workflow` (tuỳ)
   - Bật liên kết **Deploy / Clone** tới thư mục app:  
     **`/home/ipoubwkrhosting/workflow`**
3. Sau khi tạo, copy **Repository Path** chính xác (thường dạng  
   `/home/ipoubwkrhosting/repositories/workflow.git`)  
   → dán vào secret `CPANEL_REPO_PATH`  
   (**phải bắt đầu bằng `/`**, **không** thêm `ssh://` hay host)

Workflow sẽ push tới:

```text
ssh://ipoubwkrhosting@turboweb-032506.000nethost.com:22/home/ipoubwkrhosting/repositories/workflow.git
```

### Deploy path trong cPanel Git

Khi cPanel hỏi **Deployment / Checked-out directory**, chọn:

```text
/home/ipoubwkrhosting/workflow
```

Bật **automatically deploy** sau push nếu có.

## Checklist trước lần deploy đầu

- [ ] SSH Access bật trên cPanel (Shell Access)
- [ ] Đã tạo Git repo + biết đúng `CPANEL_REPO_PATH`
- [ ] 6 secrets đã lưu trên GitHub
- [ ] File `.env` production còn nguyên trong `workflow` (push **không** xoá/over write nếu `.env` trong `.gitignore` — an toàn)
- [ ] Domain document root trỏ `workflow/public` (nếu site live qua folder này)

## Sau khi điền secrets

```bash
git push origin main
```

Hoặc GitHub → **Actions → Laravel CI/CD (cPanel) → Run workflow**

## Lỗi hay gặp

| Lỗi | Cách xử lý |
|-----|------------|
| `Permission denied` / auth fail | Sai user/password hoặc port; thử port `2222` |
| `Could not read from remote` | Sai `CPANEL_REPO_PATH` hoặc chưa tạo Git repo |
| Tests fail, không deploy | Xem log job `laravel-tests`, sửa test trước |
| App không đổi sau push | Chưa bật deploy path / deploy hook trong cPanel Git |
| `artisan not found` | Sai `CPANEL_APP_PATH` (phải là `.../workflow`) |
| Thiếu vendor sau push | Thêm `composer install --no-dev` vào deploy hook cPanel hoặc bước SSH |

## Lệnh kiểm tra tay (SSH / Terminal cPanel)

```bash
cd /home/ipoubwkrhosting/workflow
pwd
ls artisan .env
php -v
php artisan --version
php artisan migrate:status
```
