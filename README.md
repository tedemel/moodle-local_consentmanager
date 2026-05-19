# local_consentmanager — GDPR-compliant Consent Manager for Moodle 5

[![Moodle Plugin CI](https://github.com/tedemel/moodle-local_consentmanager/actions/workflows/moodle-ci.yml/badge.svg)](https://github.com/tedemel/moodle-local_consentmanager/actions/workflows/moodle-ci.yml)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

A fully Moodle-API-conformant consent manager plugin for Moodle 4.5 LTS, 5.0, 5.1 and 5.2 by **Tessa Demel**.

## Overview

`local_consentmanager` provides a GDPR/TTDSG-compliant consent layer for Moodle 5.  
It blocks third-party iframes (YouTube, Vimeo, H5P.com, Matomo, Google Fonts, etc.) until the user explicitly consents per service category.

It is delivered together with its companion filter plugin **filter_consentmanager**, which handles iframe replacement in text content.

### Key features

- **Category-based consent** — configurable categories (Essential, Functional, Statistics, Marketing); Essential always active
- **Page-level consent banner** — modal dialog with simple and detailed views; ARIA-accessible, keyboard-navigable, focus-trapped
- **Service registry** — admin configures per-service domain patterns (regex); any new third-party service can be added without code changes
- **Iframe blocking** — `filter_consentmanager` replaces unconsented iframes with a branded placeholder + "Accept and show" button
- **User preferences page** — `/local/consentmanager/mypreferences.php`; withdraw per-category, view history; "equally easy withdrawal" per GDPR Art. 7(3)
- **Revision management** — admin bumps the revision to re-collect consent after policy changes; minor revisions can be flagged to skip re-consent
- **Audit log** — append-only log of all consent events; configurable IP/user-agent retention; CSV export
- **Scheduled cleanup** — configurable log retention period with automated pruning
- **Admin dashboard** — consent rates per category, recent activity, revision info, CSV export
- **Admin banner preview** — "Preview banner" button on the settings page
- **Dark mode support** — uses Bootstrap 5 CSS custom properties; adapts to `data-bs-theme="dark"` (Boost Union) and `prefers-color-scheme: dark`
- **Full Privacy API** — exports and anonymises user data on request; audit log preserved (anonymised) for compliance
- **Moodle 5 Hooks API** — uses `before_http_headers` and `before_footer_html_generation`; no legacy `lib.php` callbacks
- **Web Services / AJAX** — `set_consent`, `withdraw_consent`, `get_consent_status` external functions
- **Multilingual** — English and German included

## User experience

### First visit

When a user opens a page that contains blocked third-party content, a consent
banner slides up from the bottom of the screen:

> **Your privacy settings**
> This learning platform uses cookies and embeds content from external providers
> (e.g. YouTube, Vimeo). Technically necessary cookies are always active.
>
> \[Accept all\]   \[Essential only\]   \[Settings\]

At the same time, any embedded iframe (YouTube video, H5P content, etc.) is
replaced by a placeholder:

> 🔒 To display this YouTube video, please consent to the "Marketing" category.
>
> \[Accept and show\]   \[Privacy policy\]

**No request is made to YouTube or any other third party before the user
explicitly clicks a consent button.**

### After consent

| Action | Result |
|--------|--------|
| Click **Accept all** in banner | Page reloads; all iframes appear |
| Click **Accept and show** on a placeholder | That single iframe loads immediately via JavaScript |
| Click **Essential only** | Page reloads; iframes remain blocked |

On subsequent page loads the banner does not appear again (consent is stored
for the configured duration).

### Withdrawing consent

Users can manage and withdraw their consent at any time under
**My preferences** (linked in the banner footer and the user profile).
Per-category withdrawal is supported. After withdrawal, placeholders reappear
on the next page load.

## Requirements

| Component | Minimum | Supported |
|-----------|---------|-----------|
| Moodle    | 4.5 LTS | 4.5 LTS, 5.0, 5.1, 5.2 |
| PHP       | 8.2     | 8.2 – 8.4 |
| Database  | MariaDB 10.6 / PostgreSQL 16 | MariaDB ≥ 10.6, PostgreSQL ≥ 16 (5.2) |

Both plugins must be installed together:

- `local_consentmanager` (this plugin)
- `filter_consentmanager` (companion filter, included in the same ZIP)

## Installation

### Option A — Plugin directory / ZIP upload

1. Download the release ZIP from the [Moodle Plugin Directory](https://moodle.org/plugins).
2. In Moodle: **Site Administration → Plugins → Install plugins** → upload the ZIP.
3. Repeat for `filter_consentmanager`.
4. Follow the on-screen upgrade steps.

### Option B — Manual

```bash
# Unzip to the correct directories
unzip local_consentmanager.zip -d /path/to/moodle/local/
unzip filter_consentmanager.zip -d /path/to/moodle/filter/

# Run upgrade
php admin/cli/upgrade.php
```

### Option C — Git

```bash
cd /path/to/moodle
git clone https://github.com/tedemel/moodle-local_consentmanager local/consentmanager
git clone https://github.com/tedemel/moodle-filter_consentmanager filter/consentmanager
php admin/cli/upgrade.php
```

## Configuration

After installation:

1. **Enable the plugin** — Site Administration → Privacy and policies → Consent Manager → Settings → ☑ Enable Consent Manager
2. **Edit categories** — Site Administration → Privacy and policies → Consent Manager → Manage categories  
   Default categories (Essential, Functional, Statistics, Marketing) are created on install.
3. **Add services** — Site Administration → Privacy and policies → Consent Manager → Manage services  
   Add a service with name, category, privacy URL, and a domain pattern regex (e.g. `youtube\.com|youtu\.be`).
4. **Enable the filter** — Site Administration → Plugins → Filters → Manage filters → Enable "Consent Manager — Iframe Filter"
5. **Configure the banner** — Settings page: add banner intro text, privacy policy URL, imprint URL.
6. **Preview** — Click "Preview banner" on the Settings page to check the banner appearance.
7. **Set revision** — Increment the revision number whenever you make a substantive policy change to trigger re-consent.

## Capabilities

| Capability | Default role |
|-----------|-------------|
| `local/consentmanager:manage` | Manager |
| `local/consentmanager:viewreports` | Manager |
| `local/consentmanager:giveconsent` | All authenticated users + guests |
| `local/consentmanager:exportlogs` | Manager |

## Privacy / GDPR

This plugin is designed for GDPR compliance. It:

- Stores consent records and an append-only audit log per user/category/revision.
- Fully implements the Moodle Privacy API: data export, anonymisation on deletion.
- On user deletion: consent records are deleted; audit log entries are anonymised (userid set to NULL) — the log is retained to satisfy accountability obligations (GDPR Art. 5(2)).
- IP addresses and user-agents are stored only if configured; IP anonymisation (last octet truncated) is enabled by default.
- Log entries older than the configured retention period are automatically deleted by a scheduled task.

## Database tables

| Table | Purpose |
|-------|---------|
| `local_consentmanager_cats` | Consent categories |
| `local_consentmanager_services` | Third-party service definitions |
| `local_consentmanager_consents` | Current consent state per user × category |
| `local_consentmanager_log` | Append-only audit trail |

## License

This plugin is free software: you can redistribute it and/or modify it under the terms of the
[GNU General Public License v3 or later](https://www.gnu.org/licenses/gpl-3.0.html).

Copyright © 2026 Tessa Demel

## Support

- GitHub Issues: <https://github.com/tedemel/moodle-local_consentmanager/issues>
