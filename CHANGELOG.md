# Changelog

All notable changes to `local_consentmanager` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] — 2026-05-19

### Changed
- Compatibility verified for Moodle 5.2.
- `$plugin->requires` raised to `2025041400` (Moodle 5.0) to align with the
  actual tested range; `$plugin->supported = [500, 502]`. Events use
  `objecttable` semantics that 4.5 LTS validates more strictly than 5.x,
  so 4.5 is not advertised as supported.
- CI matrix: `MOODLE_500_STABLE/pgsql`, `MOODLE_501_STABLE/mariadb`,
  `MOODLE_502_STABLE/pgsql`, `MOODLE_502_STABLE` + PHP 8.4/mariadb.
- CI now also covers MariaDB (mariadb:10.11 service added).
- Bump version 2026042500 → 2026051905, release 1.1.0 → 1.1.1.

## [1.1.0] — earlier 2026

- Previous releases (refer to git history for details).
