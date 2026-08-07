# CI/CD cPanel — rsync vào **workflow** only

```
push main → unit test → rsync code → composer + migrate trên server
```

`stock` không đụng. **Không** dùng `git push` remote cPanel (dễ lỗi path).

## Secrets (5 cái — đủ)

| Name | Secret |
|------|--------|
| `CPANEL_SSH_HOST` | `hpfoods.com.vn` |
| `CPANEL_SSH_PORT` | `65333` |
| `CPANEL_SSH_USER` | `ipoubwkrhosting` |
| `CPANEL_SSH_PASSWORD` | mật khẩu cPanel / SSH |
| `CPANEL_APP_PATH` | `/home/ipoubwkrhosting/workflow` |

### Không cần nữa

- `CPANEL_REPO_PATH` — có thể **Delete** trên GitHub
- Git Version Control cPanel — không bắt buộc cho deploy kiểu rsync

## Giữ trên server

Rsync **không** ghi đè:

- `.env` (DB / APP_KEY)
- `vendor/` (cài lại bằng composer trên server)
- `storage` uploads & logs
- `node_modules/`

## Checklist

1. 5 secrets đúng (port **65333**, host **hpfoods.com.vn**)
2. SSH Access bật trên cPanel
3. Push `main` → Actions xanh
4. Kiểm tra site live
