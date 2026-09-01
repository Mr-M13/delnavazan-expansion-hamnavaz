# Hamnavaz Product Specification

## 1. Product identity

**Name:** Hamnavaz
**Persian:** هم‌نواز

Hamnavaz is an initiative within Delnavazan designed to connect students with teachers of Iranian/Persian music around the world.

Possible conceptual positioning:

> ابتکاری از دلنوازان برای پیوند هنرجویان و استادان موسیقی ایرانی در سراسر جهان

Possible shorter descriptors include:

* شبکه جهانی استادان موسیقی ایرانی
* استادان موسیقی ایرانی در سراسر جهان

These phrases are conceptual and not necessarily final marketing copy.

## 2. Product philosophy

Hamnavaz should feel:

* cultural;
* trustworthy;
* warm;
* community-oriented;
* editorial rather than aggressively commercial.

It should not initially resemble a marketplace.

The objective is to strengthen access to Iranian music education and help Iranian/Persian music teachers become discoverable internationally.

## 3. Relationship with existing Delnavazan

The existing Delnavazan homepage and online academy are already functioning and gaining SEO traction.

Do not restructure the primary homepage positioning merely to accommodate Hamnavaz.

Hamnavaz should initially appear as a tasteful secondary Delnavazan initiative, potentially through:

* a subtle homepage section;
* selected teacher presentation;
* explanatory copy;
* a “Find a Teacher” CTA.

The dedicated Hamnavaz experience should live primarily under a dedicated section such as:

`/hamnavaz/`

Final URL architecture must be validated before bulk content creation.

## 4. V1 operating model

Hamnavaz V1 is a teacher discovery and contact network.

Delnavazan does NOT initially:

* schedule independent teachers' lessons;
* collect lesson fees;
* take commissions;
* manage teacher calendars;
* guarantee teacher availability;
* mediate normal lesson arrangements.

The student finds a suitable teacher and contacts the teacher directly.

## 5. Initial scale

Expected initial directory size is approximately 50 teachers across countries such as:

* Australia;
* New Zealand;
* Canada;
* United States;
* United Kingdom;
* European countries;
* other regions where suitable teachers are identified.

The architecture must support significantly larger datasets without unnecessary redesign.

## 6. Teacher discovery

V1 should prioritise a progressive location/instrument search rather than a map.

Preferred conceptual journey:

**Country → City → Instrument → Results**

The system should also permit useful combinations where appropriate.

Search should require minimal typing and work particularly well on mobile devices.

## 7. No-result behaviour

Empty searches must remain useful.

Example:

If no Tar teacher exists in Brisbane, the interface may explain that no matching teacher is currently listed and then offer:

* relevant teachers elsewhere in Australia;
* appropriate online tuition through the existing Delnavazan academy;
* a mechanism to suggest a teacher.

“No results” must not be a dead end.

## 8. Teacher profiles

Teacher profiles should be generated from structured data rather than individually hand-designed pages.

Profiles may contain:

* Persian name;
* English name;
* photograph;
* short biography;
* full biography;
* instruments;
* specialisations;
* teaching levels;
* in-person availability;
* optional online availability;
* languages;
* country;
* state/province;
* city;
* optional suburb/area;
* public location description;
* WhatsApp;
* phone;
* email;
* website;
* Instagram;
* YouTube/other social channels;
* indicative pricing;
* currency;
* price type;
* trial lesson availability;
* additional information;
* listing status;
* verification/freshness information.

Not every field must be publicly displayed.

## 9. Privacy

Residential addresses should not be published by default.

Normal public location should be approximately:

**City + Country**

Optional suburb/area may be displayed where the teacher explicitly chooses to provide it.

Internal notes, tokens, payment identifiers and private operational data must never become public profile fields.

Teachers must consent to publication of their contact information.

## 10. Freshness and trust

Stale directories quickly lose value.

Hamnavaz must therefore track listing freshness.

Public profiles should eventually communicate freshness in a human-readable way, for example:

**Information last confirmed June 2026**

The verification lifecycle is defined in `AUTOMATION-SPEC.md`.

## 11. Teacher applications

A future V1 capability should allow teachers to apply to join Hamnavaz.

Conceptual CTA:

**Are you an Iranian music teacher? Join Hamnavaz**

Applications must not auto-publish.

Flow:

Submission → Pending Review → Administrator Review → Approved/Rejected → Publication

Submission systems require validation, sanitisation, consent and anti-spam/rate-limiting controls.

## 12. Monetisation philosophy

Hamnavaz should not feel commercial at launch.

Standard listings should deliver meaningful value.

Architecture should nevertheless allow future optional premium functionality without requiring a fundamental data-model redesign.

Possible future premium capabilities:

* enhanced placement;
* verified/premium badge;
* richer profile;
* homepage or landing-page feature eligibility;
* additional media/features.

Payment must never override search relevance.

For example, a premium teacher in Sydney should not outrank a relevant Brisbane teacher merely because the Sydney teacher pays.

## 13. Independent academy systems

Amelia remains responsible for Delnavazan's own academy booking.

Hamnavaz should not become tightly coupled to Amelia because independent teachers are not being managed as Delnavazan academy employees.

## 14. Success criteria for V1

V1 should allow:

* administrators to manage teachers without coding;
* structured teacher information;
* teacher discovery by meaningful location/instrument criteria;
* clean public teacher profiles;
* excellent mobile usability;
* correct Persian RTL presentation;
* useful no-result fallbacks;
* moderated teacher applications;
* listing verification/freshness lifecycle;
* safe removal of stale listings from public discovery;
* notification fallback;
* logging and analytics;
* safe backup/rollback;
* good SEO and performance.
