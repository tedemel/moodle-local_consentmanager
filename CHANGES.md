# Changelog — local_consentmanager

All notable changes are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.0.0] — 2026-04-19

### Added
- Initial release
- Category-based consent banner with simple and detail views
- Service registry with configurable domain-pattern matching
- Iframe blocking via companion filter_consentmanager
- User preferences page with consent history and per-category withdrawal
- Admin dashboard with consent statistics and CSV export
- Revision management with minor-revision flag
- Append-only audit log with configurable IP/user-agent retention
- Scheduled task for log cleanup
- Full Moodle Privacy API implementation (export, anonymise, delete)
- Web service functions: set_consent, withdraw_consent, get_consent_status
- Admin banner preview button on settings page
- Dark mode support (Bootstrap 5 CSS vars + data-bs-theme + prefers-color-scheme)
- English and German language packs
