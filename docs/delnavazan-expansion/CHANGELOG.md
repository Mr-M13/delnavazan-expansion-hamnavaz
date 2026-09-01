# Changelog

All notable changes to Delnavazan Expansion are documented here.

## [0.2.0] - 2026-09-01

### Added

- Private, admin-managed `hamnavaz_teacher` custom post type using WordPress-native identity, biography, revision, and featured-image fields.
- Registered structured teacher metadata with controlled values, validation, sanitisation, and nonce/capability-protected saves.
- Private instrument, country, and language taxonomies for normalized administration and future discovery.
- Canonical listing and verification state fields without verification automation or public behavior.
- Logical teacher meta boxes, validation feedback, admin list columns, and instrument/country/listing-status filters.
- Phase 2 architecture, field mapping, taxonomy/status decisions, security controls, deferred fields, and version strategy documentation.

### Changed

- Advanced the plugin version to `0.2.0`.
- Expanded the Delnavazan admin presence to link to teacher management for users with standard page-editing capabilities.
- Scoped the existing admin stylesheet to Hamnavaz teacher screens as well as the module status page.
- Enforced one primary country per teacher with a single-select taxonomy control and assignment guard.
- Retained the readable city label while deriving `_hamnavaz_city_key` for normalized future Country → City → Instrument filtering without a global city taxonomy.

### Security

- Added explicit custom-field nonce and capability checks, autosave/revision guards, context-aware output escaping, controlled allowlists, and strict contact/price/date validation.
- Added a separate country nonce, term validation, and malformed multi-country normalization so teacher saves cannot persist multiple countries.
- Kept the teacher model private, non-queryable, and excluded from REST in Phase 2.

## [0.1.0] - 2026-09-01

### Added

- WordPress plugin bootstrap with shared version and location constants.
- Explicit module contract and module loader.
- Minimal Hamnavaz module with a capability-protected administrator status page.
- Translation loading and RTL-compatible, screen-scoped administrator styling.
- Reversible activation, deactivation, upgrade-version, and uninstall behaviour.
- Phase 1 architecture, security, lifecycle, versioning, limitations, and deferred-scope documentation.

### Changed

- Restored the canonical product, UI/UX, automation, data-model, security, and future-feature specifications as genuine UTF-8 Markdown.
- Updated Phase 1 documentation to reference the restored specifications as the persistent source of truth.
