# Contributing to local_consentmanager

Thanks for your interest! This plugin follows standard Moodle plugin conventions.

## Development setup

1. Clone a Moodle 5.0+ checkout (`git clone -b MOODLE_500_STABLE https://github.com/moodle/moodle.git`).
2. Clone this plugin into `<moodle>/local/consentmanager`.
3. Install Moodle, then run `php admin/cli/upgrade.php` to install the plugin.

## Building AMD modules

Moodle ships a Gruntfile at the repo root. To rebuild the minified JS for this plugin:

```bash
cd <moodle-root>
npm install
npx grunt amd --root=local/consentmanager
```

This regenerates `amd/build/*.min.js` from `amd/src/*.js`. Always commit the rebuilt `amd/build/*` files together with your `amd/src/*` changes — Moodle serves the build files in production.

## Coding standards

We follow the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle). The CI runs:

- `moodle-plugin-ci phpcs` (PHP CodeSniffer with Moodle ruleset)
- `moodle-plugin-ci phpdoc` (PHPDoc validation)
- `moodle-plugin-ci phpmd` (PHP mess detector)
- `moodle-plugin-ci grunt` (verifies `amd/build/` is in sync with `amd/src/`)
- `moodle-plugin-ci mustache` (template lint)
- `moodle-plugin-ci phpunit` (unit tests)
- `moodle-plugin-ci behat` (acceptance tests)

To run any of these locally, install [moodle-plugin-ci](https://moodlehq.github.io/moodle-plugin-ci/).

## Pull requests

- Branch from `main`.
- Add or update tests for any behaviour change.
- Update `CHANGES.md` with a one-liner.
- Bump `version.php` if the change affects DB schema or plugin metadata.
- Make sure CI is green before requesting review.

## Reporting issues

Use GitHub Issues. Include Moodle version, PHP version, browser, and steps to reproduce.

## License

By contributing, you agree your contributions are licensed under GPL v3 or later.
