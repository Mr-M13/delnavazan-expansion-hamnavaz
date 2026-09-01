# Delnavazan Expansion

## Purpose

Delnavazan Expansion is the extensible architecture for new capabilities being added to the existing Delnavazan platform.

Delnavazan is an established Persian/Iranian music platform and online academy. The existing production website already contains content, SEO assets, teachers, booking, payments, notifications and custom integrations.

Expansion work must preserve and complement that existing system rather than unnecessarily restructure it.

The first expansion module is **Hamnavaz (هم‌نواز)**.

Future modules may include additional Delnavazan cultural, educational or physical-academy capabilities.

## Architectural principle

New expansion functionality should be implemented through maintainable, update-safe modules.

Never modify:

* WordPress core;
* Amelia core;
* third-party plugin files;
* parent theme files.

Avoid putting expansion business logic into the active theme.

The expansion architecture should survive normal WordPress, theme and plugin updates.

## Product ecosystem

The long-term Delnavazan ecosystem may include:

1. the existing Delnavazan Online Academy;
2. Hamnavaz;
3. cultural/editorial content;
4. a future physical Delnavazan academy/cultural space in Iran;
5. additional expansion modules where justified.

These remain under the Delnavazan umbrella rather than becoming unnecessarily fragmented brands or technology stacks.

## Hamnavaz

Hamnavaz is a community-oriented global network connecting students with independent teachers of Iranian/Persian music, particularly for in-person tuition outside Iran.

Initial product principle:

**Discovery and connection, not marketplace transactions.**

In V1 Delnavazan does not:

* book lessons for independent Hamnavaz teachers;
* collect their lesson payments;
* take commission;
* manage teacher calendars.

Students discover teachers and contact them directly.

See `HAMNAVAZ-SPEC.md` for the product specification.

## Development workflow

Development is incremental and phase-based.

`main` represents the reviewed known-good baseline.

Each implementation phase should normally:

1. begin from reviewed `main`;
2. use a dedicated branch;
3. remain within approved scope;
4. include appropriate testing;
5. update documentation;
6. create a pull request;
7. receive independent review before merge.

Do not silently implement future phases.

## Source of truth

The documents in this directory form the persistent product and engineering blueprint.

Important documents:

* `HAMNAVAZ-SPEC.md`
* `UI-UX-SPEC.md`
* `AUTOMATION-SPEC.md`
* `DATA-MODEL.md`
* `SECURITY.md`
* `FUTURE-FEATURES.md`
* `PHASE-1-ARCHITECTURE.md`
* `CHANGELOG.md`

When implementation and documentation diverge, identify and resolve the discrepancy rather than silently assuming either is correct.
