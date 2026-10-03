# JUFbook Build 273 Backup

Backup record for the 2026-10-03 JUFbook website build and update-report archive.

## Website archive

- File: `273_JUFbook_Focused_Author_Book_Filters_FULL_2026-10-03.zip`
- Size: 4,212,629 bytes
- SHA-256: `2a987ffa06ae0b59e30ebca656f6a4dc73667361e65ad4a92f3b18d446b0cf3b`

## Report-history archive

- File: `274_JUFbook_Focused_Filters_FULL_Update_Report_History_PATCH_2026-10-03.zip`
- Size: about 670 KB
- SHA-256: `d42c6987e134ef8b3643bb9d347691003ef7fb08a8571c40c767cc03ab3b0ad3`
- Contains the cumulative update report plus 240 individual report snapshots representing 208 unique build numbers.

## Security pre-check

Before preparing this public-repository backup, the website ZIP was checked for common credential files and obvious live credential literals.

- No `.env` file was present.
- No `config/private.php` file was present.
- `config/database.php` reads credentials from environment/private configuration and contains only safe defaults.
- `config/private.example.php` contains placeholders only.
- `config/secret_store.php` contains secret-storage logic, not live secret values.

The binary archives themselves are tracked by the SHA files in this folder. See `REPORT_HISTORY_FILES.txt` for the report set and `BUILD_273_UPDATE_REPORT.md` for the latest build summary.
