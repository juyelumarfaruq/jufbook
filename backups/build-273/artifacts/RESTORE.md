# Restore archived ZIPs

Each original ZIP is stored losslessly as ordered Base64 text parts.

Linux/macOS:
```bash
cat part-*.b64 | base64 --decode > ORIGINAL_FILENAME.zip
sha256sum ORIGINAL_FILENAME.zip
```

PowerShell:
```powershell
$all = (Get-Content .\part-*.b64 -Raw) -join ''
[IO.File]::WriteAllBytes('ORIGINAL_FILENAME.zip', [Convert]::FromBase64String($all))
Get-FileHash .\ORIGINAL_FILENAME.zip -Algorithm SHA256
```

Verify against `manifest.json` or the `.sha256` files in the parent backup folder.