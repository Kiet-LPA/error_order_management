# CI/CD cPanel — chỉ app **workflow**

Repo `error_order_management` deploy **một mình** vào:

```text
/home/ipoubwkrhosting/workflow
```

**`stock`** là source khác, người khác maintain — **không** đưa vào secrets/deploy này.

```
push main → tests → git push cPanel repo → ssh migrate/cache trong workflow
```

## 6 secrets cần điền

| # | Name | Secret |
|---|------|--------|
| 1 | `CPANEL_SSH_HOST` | `turboweb-032506.000nethost.com` |
| 2 | `CPANEL_SSH_PORT` | `22` |
| 3 | `CPANEL_SSH_USER` | `ipoubwkrhosting` |
| 4 | `CPANEL_SSH_PASSWORD` | mật khẩu cPanel |
| 5 | `CPANEL_APP_PATH` | `/home/ipoubwkrhosting/workflow` |
| 6 | `CPANEL_REPO_PATH` | path bare Git của workflow (Git Version Control) |

### Không cần

- `CPANEL_APP_PATHS`
- `CPANEL_APP_PATH_2`
- `CPANEL_REPO_PATH_2`
- Bất kỳ secret liên quan `stock`

Nếu đã tạo nhầm secret stock → **Actions secrets → Delete**.

### `CPANEL_REPO_PATH` lấy ở đâu

cPanel → **Git Version Control** → Create/Manage repo:

- **Deployment directory:** `/home/ipoubwkrhosting/workflow`
- Copy **Repository Path** (vd `/home/ipoubwkrhosting/repositories/workflow.git`)
- Dán vào secret `CPANEL_REPO_PATH` (chỉ path, bắt đầu bằng `/`)

## Checklist

- [ ] 6 secrets như bảng trên  
- [ ] Git cPanel gắn deploy path = `workflow`  
- [ ] `.env` production còn trong `workflow`  
- [ ] Push `main` / Run workflow  
