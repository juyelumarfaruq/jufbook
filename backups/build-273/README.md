# JUFbook Build 273 Backup

GitHub backup for the 2026-10-03 JUFbook website build and the cumulative update-report history.

## Exact website archive

Original file:

`273_JUFbook_Focused_Author_Book_Filters_FULL_2026-10-03.zip`

- Size: 4,212,629 bytes
- SHA-256: `2a987ffa06ae0b59e30ebca656f6a4dc73667361e65ad4a92f3b18d446b0cf3b`
- Stored losslessly in `artifacts/website/part-001.b64` through `part-024.b64`.

## Exact report-history archive

Original file:

`274_JUFbook_Focused_Filters_FULL_Update_Report_History_PATCH_2026-10-03.zip`

- Size: 685,844 bytes
- SHA-256: `d42c6987e134ef8b3643bb9d347691003ef7fb08a8571c40c767cc03ab3b0ad3`
- Stored losslessly in `artifacts/reports/part-001.b64` through `part-004.b64`.
- The archive contains the cumulative update report and the recovered individual update-report history.

## Restore

The Base64 parts are an exact lossless representation of the original ZIP bytes.

Linux/macOS:

```bash
cat backups/build-273/artifacts/website/part-*.b64 | base64 --decode > 273_JUFbook_Focused_Author_Book_Filters_FULL_2026-10-03.zip
cat backups/build-273/artifacts/reports/part-*.b64 | base64 --decode > 274_JUFbook_Focused_Filters_FULL_Update_Report_History_PATCH_2026-10-03.zip
sha256sum 273_JUFbook_Focused_Author_Book_Filters_FULL_2026-10-03.zip
sha256sum 274_JUFbook_Focused_Filters_FULL_Update_Report_History_PATCH_2026-10-03.zip
```

PowerShell instructions are in `artifacts/RESTORE.md`. Expected hashes are also recorded in `artifacts/manifest.json` and the two `.sha256` files in this folder.

## Security pre-check

Before the public-repository backup was prepared, the website ZIP was checked for common credential files and obvious live credential literals.

- No `.env` file was present.
- No `config/private.php` file was present.
- `config/database.php` reads credentials from environment/private configuration and contains only safe defaults.
- `config/private.example.php` contains placeholders only.
- `config/secret_store.php` contains secret-storage logic, not live secret values.

This folder therefore preserves both exact archives plus integrity metadata without adding live deployment credentials.
