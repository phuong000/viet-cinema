# Evidence — Foundation acceptance 2.4

Date: 2026-11-26. Existing phase-2 evidence outside this directory remains historical.

- `final/results.json`: final isolated Apache and Chrome run, 93/93 PASS, processes stopped.
- `final/<viewport>/<page>.png`: full-page screenshot at the exact CSS viewport in the directory name.
- `final/<viewport>/<page>.json`: HTTP status, measured viewport/overflow, element bounds, interactions,
  resource responses, JavaScript errors and failed requests for that page.
  Release 2.5 đề xuất không commit 45 JSON này vì mỗi object đã có nguyên vẹn trong
  `final/results.json` (mảng responsive). File gốc vẫn giữ local; đủ 45 PNG tiếp tục được đề xuất commit.
- `initial-results.json`: first run, 88/91 checks passed; three harness false negatives (JavaScript MIME
  alias and reused session cookies). All 45 responsive cases passed. Retained rather than hidden.
- `unit.txt`, `http.txt`, `lint.txt`, `git-audit.txt`, `javascript.txt`: rerun of the original 203 checks.
- `git-portable.txt`: additional portable Git audit and isolated fixture regression; strict mode retained.
- `candidates.txt`: complete commit-candidate inventory, including itself and acceptance report.

The Apache runner writes its own config/PID/logs only under ignored `storage/cache/acceptance` and
binds an ephemeral loopback port. No global Apache config, Windows service, existing browser profile,
database or Git ref is changed. The `/throw` fixture alias exists only in the isolated test config.

Reproduce with installed tools (change paths to match your machine, do not install implicitly):

```powershell
node tests/Acceptance/local.cjs 'E:/Projects/ehs-card-desk/apps/web/node_modules/playwright' 'C:/Program Files/Google/Chrome/Application/chrome.exe' 'C:/laragon/bin/apache/httpd-2.4.54-win64-VS16' 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64' final
node tests/Acceptance/portable-git.cjs 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe'
```

Playwright 1.65.1 and Chrome 158.0.8127.88 were already installed. No dependency download was used.
These tests validate Chrome desktop/mobile viewport emulation, not Safari, Firefox or physical phones.
See `../../FOUNDATION_ACCEPTANCE_REPORT.md` for scope, unresolved conditions and the final decision.
