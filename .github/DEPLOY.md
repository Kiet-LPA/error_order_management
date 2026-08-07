# CI/CD — Tự động deploy (GitHub Actions)

Khi push / merge vào nhánh `main`, GitHub Actions SSH vào server production và chạy script deploy (git pull → composer → npm build → migrate → cache).

## Yêu cầu trên server

1. **Git clone** sẵn repo vào một thư mục (ví dụ `/var/www/error_order_management`)
2. Server có: `php` (≥ 8.1), `composer`, `git`, `npm` (khuyên dùng Node 18+)
3. File `.env` production **đã cấu hình** (không commit `.env`)
4. Deploy user có quyền ghi `storage/`, `bootstrap/cache/`
5. Remote `origin` trên server **có thể `git fetch`** (Deploy Key hoặc HTTPS token)

### Lần đầu setup server

```bash
# Ví dụ
cd /var/www
git clone git@github.com:Kiet-LPA/error_order_management.git
cd error_order_management

# Tạo .env production (APP_KEY, DB_*, SESSION_DRIVER=database, APP_DEBUG=false)
nano .env

composer install --no-dev --optimize-autoloader
php artisan key:generate   # nếu chưa có APP_KEY
php artisan migrate --force
php artisan storage:link
chmod -R ug+rwx storage bootstrap/cache
```

### Deploy Key (để server pull code)

1. Trên server: `ssh-keygen -t ed25519 -C "deploy-server" -f ~/.ssh/deploy_github -N ""`
2. GitHub repo → **Settings → Deploy keys → Add deploy key** (read-only) → dán `~/.ssh/deploy_github.pub`
3. Cấu hình `~/.ssh/config`:

```
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/deploy_github
  IdentitiesOnly yes
```

4. Test: `cd /var/www/error_order_management && git fetch origin`

## Secrets trên GitHub

Repo → **Settings → Secrets and variables → Actions → New repository secret**

| Secret | Mô tả | Ví dụ |
|--------|--------|--------|
| `SSH_HOST` | IP hoặc domain server | `123.45.67.89` |
| `SSH_USER` | User SSH | `deploy` / `ubuntu` |
| `SSH_PRIVATE_KEY` | Private key **máy CI** dùng để SSH vào server (full PEM, gồm `BEGIN`/`END`) | nội dung `id_ed25519` |
| `SSH_PORT` | (tuỳ chọn) Port SSH, mặc định `22` | `22` |
| `DEPLOY_PATH` | Đường dẫn absolute đến project trên server | `/var/www/error_order_management` |

### Tạo key cho GitHub Actions → server

Trên máy local hoặc server:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f gha_deploy -N ""
```

- Nội dung `gha_deploy` (private) → secret `SSH_PRIVATE_KEY`
- Nội dung `gha_deploy.pub` → append vào `~/.ssh/authorized_keys` của user deploy trên server

```bash
# trên server
echo "ssh-ed25519 AAAA... github-actions-deploy" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

## Workflow

| File | Khi chạy | Việc làm |
|------|----------|----------|
| `.github/workflows/ci.yml` | push / PR | Validate composer, build assets, smoke artisan |
| `.github/workflows/deploy.yml` | push `main` + manual | SSH deploy production |

### Chạy deploy thủ công

GitHub → **Actions → Deploy Production → Run workflow**

## Script trên server

`scripts/deploy.sh` — logic deploy chính. Có thể test tay:

```bash
cd /var/www/error_order_management
bash scripts/deploy.sh
```

## Checklist sau lần setup đầu

- [ ] Secrets đã điền đủ
- [ ] Server `git fetch` được
- [ ] `.env` production đủ (DB, APP_KEY, `APP_DEBUG=false`)
- [ ] Push một commit test lên `main` hoặc Run workflow
- [ ] Kiểm tra trang production + log Actions

## Lưu ý an toàn

- **Không** đưa password/DB vào workflow YAML
- **Không** commit private key hoặc `.env`
- Deploy chỉ từ `main`; feature branch dùng PR + CI
- Lần đầu nên chạy manual (`workflow_dispatch`) trước khi tin auto-deploy
