# Phase 2 Teacher Data and Admin Architecture

## Scope

Phase 2 adds the structured, administrator-managed Hamnavaz teacher data model. It provides no public directory, public profile, search, homepage, application, automation, integration, payment, ranking, map, calendar, AI, or analytics behavior.

Plugin version `0.2.0` contains this phase.

## Teacher content model

Teachers use the WordPress-native `hamnavaz_teacher` custom post type. It is deliberately private and admin-only:

- `public`, `publicly_queryable`, `has_archive`, `rewrite`, and `query_var` are disabled;
- WordPress admin UI and export remain enabled;
- REST exposure is disabled in Phase 2;
- records use WordPress revisions;
- deleting a WordPress user does not automatically delete their teacher records.

The content model uses standard page-style capabilities with WordPress meta-capability mapping. Administrators and editors can manage teachers through their existing page-editing capabilities. No custom role or capability migration is introduced.

## Core field mapping

WordPress core fields are reused rather than duplicated:

| Product concept | WordPress field |
| --- | --- |
| Persian name | Post title |
| Short biography | Post excerpt |
| Full biography | Post content/editor |
| Profile photograph | Featured image / Media Library |
| Internal teacher ID | Post ID |
| Created and updated timestamps | Core post timestamps |

The English name is stored as registered post metadata because it is distinct from the primary Persian title.

## Metadata contract

All custom fields use private `_hamnavaz_`-prefixed keys, are registered for `hamnavaz_teacher`, are single-value metadata entries, are excluded from REST in this phase, and require `edit_post` authorization.

### Identity

- `_hamnavaz_english_name`

### Teaching

- `_hamnavaz_specialisations`
- `_hamnavaz_teaching_levels`
- `_hamnavaz_in_person`
- `_hamnavaz_online`

### Location

- `_hamnavaz_state`
- `_hamnavaz_city`
- `_hamnavaz_suburb`
- `_hamnavaz_public_location`

No residential street-address field exists.

### Contact

- `_hamnavaz_whatsapp`
- `_hamnavaz_phone`
- `_hamnavaz_email`
- `_hamnavaz_website`
- `_hamnavaz_instagram`
- `_hamnavaz_youtube`
- `_hamnavaz_other_contact_url`

### Lesson information

- `_hamnavaz_price`
- `_hamnavaz_currency`
- `_hamnavaz_price_type`
- `_hamnavaz_trial_lesson`
- `_hamnavaz_lesson_notes`

These fields are informational only. Delnavazan does not process independent teacher payments.

### Operational

- `_hamnavaz_listing_status`
- `_hamnavaz_verification_status`
- `_hamnavaz_last_verified_at`
- `_hamnavaz_next_verification_at`
- `_hamnavaz_internal_notes`

Verification timestamps are stored as Unix timestamps. The admin UI converts between timestamps and the configured WordPress site timezone.

## Taxonomy decisions

Phase 2 registers three private, admin-managed, non-hierarchical taxonomies:

- `hamnavaz_instrument`: instruments are core discovery criteria and many-to-many teacher classifications;
- `hamnavaz_country`: countries require normalized names and will be a primary discovery/admin-filter dimension;
- `hamnavaz_language`: languages are reusable many-to-many classifications and likely future filters.

State/province, city, suburb/area, and public location description remain structured metadata. Turning each location level into free-growing taxonomies now would increase maintenance and term duplication before the final public URL/SEO architecture is validated.

Teaching levels are a controlled multi-value metadata field (`beginner`, `intermediate`, `advanced`, `professional`) rather than a taxonomy. They are a small fixed vocabulary and do not yet need independent term pages or editorial term management. Specialisations remain sanitized free text because the vocabulary is not yet stable enough to normalize safely.

## Status model

The authoritative Hamnavaz listing lifecycle is `_hamnavaz_listing_status`, restricted to:

- `draft`;
- `pending_review`;
- `active`;
- `verification_due`;
- `on_hold`;
- `archived`.

WordPress post status remains the core editorial/save mechanism and does not represent Hamnavaz public eligibility. This separation avoids custom post-status integration complexity and supports every canonical lifecycle concept without hard deletion. Later public queries must require the appropriate listing status explicitly.

Verification state is separately restricted to `not_requested`, `pending`, `confirmed`, or `overdue`. It is manually administered in Phase 2; no verification scheduling or messages run.

## Admin experience

The teacher edit screen uses logical meta boxes for Identity, Teaching, Location, Contact, Lesson Information, and Operational data. Core taxonomy boxes manage instruments, countries, and languages. The WordPress Featured Image box supplies the Media Library workflow; the plugin registers that core box as a fallback when the active theme does not declare thumbnail support for the teacher post type.

The teacher list screen adds:

- Persian name;
- instruments;
- City, Country location;
- listing status;
- verification state and last-confirmed date;
- last modified date.

It supports bounded admin-only filters for instrument, country, and listing status. No public queries or public assets are added.

## Validation and sanitisation

- plain text and textarea values use the corresponding WordPress sanitizers;
- emails must pass `is_email`;
- contact links allow only HTTP or HTTPS URLs;
- phone and WhatsApp values use a bounded international phone-like format;
- indicative price must be numeric, non-negative, bounded, and limited to two decimals;
- currency must be exactly three ASCII letters and is normalized to uppercase;
- listing, verification, price-type, and teaching-level values must match controlled allowlists;
- booleans are normalized explicitly;
- dates must be valid `datetime-local` values or positive timestamps;
- taxonomy assignment remains protected and normalized by WordPress core.

Invalid custom-field values do not overwrite existing metadata. A short-lived, user-specific admin notice identifies rejected field labels without echoing submitted data.

## Save and security controls

Custom metadata saves require an intent-specific nonce and `edit_post` capability. The save handler ignores autosaves, revisions, unrelated post types, missing/invalid nonces, and unauthorized requests. Output is escaped by context. Entry files reject direct access. No arbitrary HTML, SQL, uploads, secrets, external requests, or third-party dependencies are introduced.

The plugin uses WordPress core taxonomy and featured-image handlers for their respective nonce/capability workflows.

## Future-ready fields

The following planned fields are deliberately deferred rather than pre-created:

- premium state, dates, and tier;
- verified badge, method, and separate badge date;
- featured state, priority, and expiry;
- Stripe customer/subscription references and payment state;
- teacher self-management flag and linked user ID;
- availability-request feature flag.

WordPress post metadata is extensible, so these can be registered with feature-specific validation when their behavior and controlled vocabularies are approved. Adding inactive provider-specific fields now would create misleading administrative state and would not improve migration safety. Current verification state/timestamps cover the approved Phase 2 operational requirement without activating badges or automation.

## Version and data evolution

Phase 2 uses declarative CPT, taxonomy, and registered-meta definitions. It creates no custom tables and requires no one-time migration, so no separate data/schema version is introduced yet. The plugin version advances to `0.2.0`; the existing non-autoloaded plugin-version option is updated through the Phase 1 upgrade check.

When an actual persistent schema migration becomes necessary, it must use a separate data/schema version and an explicit, retry-safe migration path.

## Activation, deactivation, and uninstall

No rewrite rules, schedules, roles, capabilities, or custom tables are created. Activation/deactivation therefore require no Phase 2 lifecycle work.

Uninstall removes only the plugin-version option. Teacher posts, metadata, taxonomy terms, and term assignments are intentionally preserved so uninstall cannot unexpectedly destroy valuable records. A future destructive data-removal workflow would require explicit product approval and authorization.

## Known limitations and deferred work

- Runtime activation, edit/save, invalid-input, taxonomy, permission, admin-column, and warning/notice tests require a representative WordPress environment.
- Country and language term vocabularies are administered manually; import/seed governance is not part of Phase 2.
- There is no public output, public query, application form, verification automation, notification channel, payment behavior, ranking, map, calendar integration, AI, or analytics implementation.
- Teacher data export/import policy, audit logging, and a destructive erasure workflow require later product decisions.
