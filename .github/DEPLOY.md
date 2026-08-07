# CI/CD cPanel — **2 subdomain = 2 hệ thống + 2 DB**

Cùng 1 repo GitHub, deploy vào **2 thư mục**, mỗi chỗ **`.env` riêng / DB riêng**.  
Workflow **không bao giờ ghi đè `.env`**.

```
push main
  → test
  → git push cPanel repo 1 (+ repo 2 nếu có)
  → SSH: migrate + optimize trên từng app path
```

## Secrets cần điền

### SSH (chung 1 lần)

| Name | Secret |
|------|--------|
| `CPANEL_SSH_HOST` | `turboweb-032506.000nethost.com` |
| `CPANEL_SSH_PORT` | `22` (hoặc `2222`) |
| `CPANEL_SSH_USER` | `ipoubwkrhosting` |
| `CPANEL_SSH_PASSWORD` | mật khẩu cPanel |

### Path 2 app (DB riêng) — chọn 1 cách

**Cách A (khuyên dùng):** 1 secret danh sách

| Name | Secret (ví dụ — thay đúng path thật) |
|------|--------------------------------------|
| `CPANEL_APP_PATHS` | `/home/ipoubwkrhosting/workflow,/home/ipoubwkrhosting/TÊN_THƯ_MỤC_SUBDOMAIN_2` |

**Cách B:** 2 secret riêng

| Name | Secret |
|------|--------|
| `CPANEL_APP_PATH` | `/home/ipoubwkrhosting/workflow` |
| `CPANEL_APP_PATH_2` | `/home/ipoubwkrhosting/...` (path app subdomain 2) |

> Trong File Manager đã thấy app 1: **`/home/ipoubwkrhosting/workflow`**.  
> Path app 2: mở File Manager → folder subdomain kia → copy path (cùng cấp home, có `artisan` + `.env`).

### Git bare repo trên cPanel

| Name | Bắt buộc | Ghi chú |
|------|----------|---------|
| `CPANEL_REPO_PATH` | ✅ | Path bare repo deploy app 1, vd `/home/ipoubwkrhosting/repositories/workflow.git` |
| `CPANEL_REPO_PATH_2` | ⭐ nếu app 2 cũng dùng cPanel Git | Path bare repo app 2 |

**Mỗi subdomain:** cPanel → **Git Version Control** → Create →  
Deployment path = thư mục app tương ứng → copy **Repository Path** vào secret.

Nếu app 2 **không** dùng Git cPanel (chỉ copy code tay): vẫn set `CPANEL_APP_PATHS` 2 path; sau khi push repo 1, bạn cần cơ chế sync code sang path 2 (rsync/git pull). **Khuyến nghị:** mỗi subdomain 1 Git repo + deployment path riêng.

## `.env` trên server (1 lần / mỗi app)

**App 1** `/home/ipoubwkrhosting/workflow/.env`

```env
APP_URL=https://subdomain1.example.com
DB_DATABASE=db_app_1
DB_USERNAME=...
DB_PASSWORD=...
```

**App 2** `/home/.../app2/.env`

```env
APP_URL=https://subdomain2.example.com
DB_DATABASE=db_app_2
DB_USERNAME=...
DB_PASSWORD=...
```

CI/CD chỉ cập nhật code + `php artisan migrate` trên **đúng DB của từng `.env`**.

## Tóm tắt số secret

| Số | Secret |
|----|--------|
| 1 | `CPANEL_SSH_HOST` |
| 2 | `CPANEL_SSH_PORT` |
| 3 | `CPANEL_SSH_USER` |
| 4 | `CPANEL_SSH_PASSWORD` |
| 5 | `CPANEL_APP_PATHS` *hoặc* `CPANEL_APP_PATH` + `CPANEL_APP_PATH_2` |
| 6 | `CPANEL_REPO_PATH` |
| 7 (tuỳ chọn) | `CPANEL_REPO_PATH_2` |

→ Thực tế **6–7 secret**, không còn “1 path cho cả 2 subdomain”.

## Checklist

- [ ] 2 folder app, mỗi folder có `artisan` + `.env` + DB riêng  
- [ ] 2 subdomain document root → `…/public` tương ứng  
- [ ] 2 Git Version Control (khuyên) + repo path secrets  
- [ ] Secrets GitHub đã lưu  
- [ ] Push `main` / Run workflow  

## Lỗi hay gặp

| Lỗi | Nguyên nhân |
|-----|-------------|
| App 2 không đổi code | Chưa có `REPO_PATH_2` / deployment cPanel chưa gắn path 2 |
| Sai DB migrate | Sai path hoặc 2 app trỏ cùng `.env` |
| `.env missing` | Path 2 chưa có `.env` production |
