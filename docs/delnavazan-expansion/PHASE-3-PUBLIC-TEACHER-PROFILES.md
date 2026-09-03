# Phase 3 Public Teacher Profiles and Card Architecture

## Scope

Phase 3 adds the controlled public presentation layer for Hamnavaz teachers. Plugin version `0.3.0` provides one public teacher profile route, a reusable view model, a reusable teacher card, a plugin-owned profile template, and narrowly scoped public styling.

It does not add the Phase 4 directory, Country → City → Instrument search, faceted filtering, public taxonomy pages, applications, self-management, verification automation, booking, payments, rankings, maps, reviews, or homepage integration.

## Controlled routing

Public profiles use:

`/hamnavaz/teacher/{teacher-slug}/`

`Public_Routing` registers this path as a rewrite to the private `hamnavaz_teacher_profile` query variable. It resolves the slug against `hamnavaz_teacher`, applies the central eligibility check, builds an allowlisted view model, and selects the plugin-owned profile template. It does not change the post type's native visibility.

The following Phase 2 protections remain unchanged:

- `hamnavaz_teacher` remains `public => false` and `publicly_queryable => false`;
- native teacher archives, rewrite rules, and query variables remain disabled;
- instrument, country, and language taxonomies remain private and have no public archives;
- the teacher post type and its metadata/taxonomies remain excluded from REST.

A missing, malformed, or ineligible slug receives the active theme's normal 404 presentation and does not reveal whether a matching internal record exists or why it is unavailable. Eligible non-canonical requests redirect to the canonical profile URL.

Rewrite rules are flushed once on plugin activation, deactivation, and a detected plugin-version upgrade. They are never flushed on ordinary frontend requests.

## Slugs

Profiles use the WordPress core `post_name`. WordPress therefore supplies sanitisation, Persian/Latin handling, and uniqueness suffixes for collisions. A teacher's slug is generated through the normal save/publish lifecycle and does not automatically change merely because the display title later changes.

Because the teacher post type is intentionally private, Phase 3 adds the core WordPress Slug meta box to its edit screen. Editors can deliberately change the profile slug through a familiar native control. Phase 3 does not create a parallel slug table or redirect history; preserving redirects after a deliberate slug change is a documented future consideration.

## Public eligibility

`Public_Eligibility` is the single reusable public-visibility authority. A teacher is eligible only when:

1. the record exists and is a `hamnavaz_teacher` post;
2. its WordPress post status is `publish`;
3. `_hamnavaz_listing_status` is `active` or `verification_due`.

The inclusion of `verification_due` follows the canonical verification lifecycle: the due state remains public during the confirmation window, while continued non-response moves the listing to `on_hold`, which removes it from public presentation. Draft, pending-review, on-hold, archived, trashed, and non-published records are ineligible.

The same class exposes the listing-status metadata query fragment intended for Phase 4. Templates do not duplicate status checks.

## Public presenter and allowlist

The public data flow is:

`Teacher record → Public_Eligibility → Teacher_Presenter → card/profile template`

`Teacher_Presenter::PUBLIC_META_KEYS` is the explicit Phase 3 metadata allowlist. The presenter fetches only these keys and returns normalized presentation values:

- identity: English name; core title, excerpt, content, and featured image are handled explicitly;
- teaching: specialisations, teaching levels, in-person/online availability;
- location: state/province, readable city, suburb/area, and public location description;
- contact: WhatsApp, phone, email, website, Instagram, YouTube, and the approved additional public URL;
- lesson information: indicative price, currency, price type, trial lesson availability, and public lesson notes;
- freshness: last-confirmed timestamp, exposed only as a formatted date.

Public taxonomy output is limited to term names for instruments, languages, and the single assigned country. The normalized city key remains internal and is not included in the view model.

The presenter never performs a generic post-meta fetch for rendering and never passes a raw metadata array to a template.

## Fields that remain private

Operational and administrative values are absent from the allowlist and view model, including:

- listing and verification states;
- next-verification timestamp;
- normalized city key;
- internal notes;
- post/user IDs and WordPress usernames;
- future tokens, payment references, automation state, logs, ranking controls, or credentials.

They are not placed in HTML, hidden inputs, data attributes, comments, JavaScript, JSON-LD, or REST. Phase 3 introduces no public JSON endpoint and no JavaScript payload.

## Contact safety

Metadata remains sanitized at write time by Phase 2 and is normalized again for public actions:

- WhatsApp removes presentation punctuation and accepts only a plausible 7–15 digit international number that does not begin with zero; an international `00` prefix is converted before building `https://wa.me/{digits}`;
- telephone links contain only an optional leading `+` and bounded digits;
- email uses WordPress email validation and `mailto:`;
- website/social links accept only absolute HTTP or HTTPS URLs;
- templates escape every URL with an explicit protocol allowlist;
- external links use `target="_blank"` with `rel="external noopener noreferrer"`.

Missing or malformed values produce no action rather than a broken link. No Meta/WhatsApp API integration is involved.

## Card and profile components

`Teacher_View::render_card()` builds the same presenter model and includes `templates/hamnavaz/teacher-card.php`. The card is intentionally compact: image, names, instruments, city/country, availability, optional indicative price, and a profile call to action. It contains no direct contact actions and can be reused by Phase 4.

The profile template includes the normal theme header and footer, then renders plugin-owned semantic content. Empty optional sections collapse. The hierarchy covers identity, profile photograph, instruments, location, introduction, biography, teaching, languages, lesson information, safe contacts, and optional information freshness.

Public biographies do not invoke the site-wide `the_content` pipeline, shortcode callbacks, or dynamic block render callbacks. HTML and Gutenberg delimiter comments are removed, registered shortcode syntax is removed without rendering it, stored static block HTML and ordinary line-separated editorial text retain useful structure through `wpautop()`, and the result is constrained with `wp_kses_post()` before it reaches the template. This keeps biography formatting usable without allowing unrelated plugins to expand the public presentation boundary or leave block metadata in public source.

No standalone `/hamnavaz/` landing page is required for the route and none is introduced in Phase 3.

## RTL, accessibility, and performance

Templates use `dir="auto"`, `<bdi>` around mixed-direction names and values, explicit isolated LTR handling for the English name (including protection from forceful RTL theme styles), CSS logical properties, and no hard-coded left/right layout dependencies. Telephone/contact values are link destinations rather than unescaped visible raw metadata.

The templates use semantic headings and sections, a labelled breadcrumb, descriptive link labels, responsive WordPress image markup, 44-pixel minimum action targets, visible focus states, non-colour-only labels, responsive layouts, and long-name wrapping. Missing images receive a decorative neutral placeholder.

The public stylesheet is dependency-free and enqueued only for an eligible profile or when the reusable card renderer is invoked. Phase 3 adds no JavaScript, framework, map, external API call, webfont, or third-party frontend dependency.

## SEO and schema decision

The route provides:

- a meaningful document title;
- one semantic H1;
- a concise excerpt-based meta description;
- a canonical profile URL;
- server-rendered crawlable content;
- meaningful responsive-image alt text;
- breadcrumb-ready semantic navigation.

When Rank Math is active, the route supplies title and description values through Rank Math's documented frontend filters. The custom route owns its canonical tag directly and suppresses a competing Rank Math canonical because there is deliberately no native public queried object. Without Rank Math, it uses the core document-title filter and outputs a minimal escaped description. The same single route-owned canonical is always emitted.

Structured data is deliberately deferred. Schema.org does not provide an unambiguous music-teacher type, and adding `Person`, employment, organisation, service, rating, or offer semantics would risk claims not established by the current data. This avoids speculative affiliation with Delnavazan and prevents accidental metadata expansion into JSON-LD.

## Theme integration

The plugin owns all Hamnavaz markup and CSS but calls the active theme's normal `get_header()` and `get_footer()` functions. It does not modify theme files or produce an isolated HTML document. A narrowly scoped body class and `.hamnavaz-*` selectors reduce style collisions while retaining normal site navigation, analytics, and accessibility infrastructure.

## Compatibility and lifecycle

Phase 3 changes no existing teacher field keys, taxonomy contracts, country enforcement, city normalization, admin filters, capabilities, or uninstall preservation. Activation and upgrade add only the route rewrite. Deactivation refreshes rewrites while preserving all teacher data.

The plugin version option remains an implementation-version marker. No data/schema migration is required because Phase 3 adds no persistent schema.

## Beta runtime validation

The 0.3.0 package was exercised on the backed-up Delnavazan beta environment with an unmistakably labelled, isolated teacher fixture and isolated taxonomy fixtures. Validation covered activation/update health, the full eligibility state matrix, Persian-slug routing and canonicalization, private CPT/taxonomy/REST behavior, the public-field allowlist, contact validation, responsive RTL/LTR rendering, optional-field collapse, asset scoping, and Phase 2 administration regressions.

The runtime checkpoint found and corrected four Phase 3 integration defects: Persian slugs were initially encoded twice, the active Neve theme and profile template initially produced nested main landmarks, Rank Math initially omitted a canonical for the custom route, and an active-theme RTL rule overrode the English-name direction. These corrections do not change the Phase 2 data architecture or expose any additional public field.

## Known limitations and deferred decisions

- Phase 4 must build the directory/search queries and call the central eligibility helper and card renderer; it must not expose native CPT/taxonomy archives.
- A deliberate slug change does not yet create a historical redirect.
- Administrator preview of ineligible profiles is deferred rather than weakening anonymous eligibility.
- The public suburb and location description depend on editorial compliance with Phase 2's public-only guidance; there is intentionally no residential-address field.
- City aliases beyond WordPress's normalized city key still require editorial governance in Phase 4.
- Structured data remains deferred pending a product-approved semantic model.
