# CI/CD — chỉ deploy **workflow** (repo error_order_management)

**Không** deploy `stock` (source khác, người khác phụ trách).

```
push main → test → git push cPanel (workflow) → ssh migrate trong /home/ipoubwkrhosting/workflow
```

## 6 secrets trên GitHub

Repo → **Settings → Secrets and variables → Actions → New repository secret**

| Name | Secret |
|------|--------|
| `CPANEL_SSH_HOST` | `turboweb-032506.000nethost.com` |
| `CPANEL_SSH_PORT` | `22` |
| `CPANEL_SSH_USER` | `ipoubwkrhosting` |
| `CPANEL_SSH_PASSWORD` | mật khẩu cPanel |
| `CPANEL_APP_PATH` | `/home/ipoubwkrhosting/workflow` |
| `CPANEL_REPO_PATH` | path bare Git (cPanel Git Version Control) — **chỉ repo gắn workflow** |

### `CPANEL_REPO_PATH` lấy ở đâu

cPanel → **Git Version Control** → Create/Manage repo  
**Deployment path** = `/home/ipoubwkrhosting/workflow`  
Copy **Repository Path** (vd `/home/ipoubwkrhosting/repositories/workflow.git`)

### Không cần (đã bỏ)

- `CPANEL_APP_PATHS`
- `CPANEL_APP_PATH_2`
- `CPANEL_REPO_PATH_2`
- Mọi cấu hình liên quan `stock`

Nếu đã lỡ tạo secret stock: xóa trên GitHub (không hại).

## Checklist

- [ ] 6 secrets trên  
- [ ] cPanel Git deploy vào `workflow`  
- [ ] Push `main` hoặc Run workflow  
