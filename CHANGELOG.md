# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.4.0] - 2026-09-11

### Added

- Added French translations for France (`fr_FR`), Canada (`fr_CA`), and Belgium (`fr_BE`).
- Added a dedicated permissions tab to GLPI profiles, allowing administrators to manage DelaInventory access.
- Added `READ` and `UPDATE` permissions for DelaInventory, with `UPDATE` controlling inventory records and label printing.

### Changed

- Fixed the printer connection test token expiration issue that caused subsequent requests to fail.
- Refactored the plugin structure to better follow GLPI plugin development best practices.
- Renamed and reorganized plugin classes to improve separation of responsibilities and maintainability.
- Separated frontend rendering files from form request handlers.
- Reorganized the `src/` classes, moving functionality previously concentrated in `Config.php` into dedicated classes.

## [0.3.0] - 2026-08-31

### Added

- Printer connection test through the plugin configuration interface, allowing users to verify connectivity to the configured Zebra printer.
- ZPL label preview through Labelary, providing a quick way to preview the generated label before printing.
- Internationalization (i18n) structure using `.po` and `.mo` translation files.
- Added Portuguese translations for Brazil (`pt_BR`) and Portugal (`pt_PT`).
- Added English translations for the United States (`en_US`) and United Kingdom (`en_GB`).

## [0.2.0] - 2026-08-06

### Added

- Zebra printer network configuration through IP address and TCP port inputs.
- Custom ZPL template configuration through the plugin interface.
- Dynamic asset variable/tag system for generating customizable labels.

## [0.1.0] - 2026-07-22

### Added

- Initial public beta release.
- Inventory audit and traceability.
- Zebra label printing.
- Plugin configuration interface.
- Compatibility with GLPI 11.0.x.