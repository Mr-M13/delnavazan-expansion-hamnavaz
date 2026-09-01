# Delnavazan Expansion — Future Features

This document records intentionally deferred capabilities.

Items here are **not permission to implement them early**.

They exist so current architecture does not unnecessarily block known future directions.

## 1. Premium Hamnavaz listings

Potential features:

* premium tier;
* enhanced profile;
* tasteful badge;
* increased feature eligibility;
* featured teacher placement;
* richer media.

Search relevance must remain more important than payment.

## 2. Stripe subscription integration

Potential future model:

Stripe subscription event → verified webhook → update premium state

Cancellation/expiry → premium removed → teacher remains a standard listing.

Premium expiration must never automatically remove the underlying teacher listing.

## 3. Teacher self-management

Potential secure teacher account/dashboard allowing controlled profile updates.

Public changes may still require moderation.

## 4. Lightweight lesson enquiries

Rather than full calendar integration, a future teacher contact flow may collect:

* preferred day;
* preferred time;
* message.

The teacher receives the request by WhatsApp/email and manages the arrangement independently.

## 5. Calendar availability

Live calendar synchronisation is deliberately excluded from initial Hamnavaz because managing many independent teacher calendars creates complexity and maintenance risk.

Reconsider only if demonstrated user demand justifies it.

## 6. Maps

A map is not required for initial low-density teacher listings.

It may be reconsidered after directory density grows enough to make geographic browsing useful.

## 7. AI capabilities

Structured data must come first.

Possible later AI uses:

* conversational teacher discovery;
* teacher bio drafting;
* Persian/English translation assistance;
* data normalisation;
* duplicate detection;
* stale-listing detection;
* teacher recommendations.

AI should enhance structured systems rather than compensate for poor data architecture.

## 8. Shops and academy directory

Iranian music shops and independent physical academies could eventually become separate Delnavazan directory categories.

Do not mix them into the teacher directory merely because they share location data.

## 9. Delnavazan physical academy in Iran

Long-term Delnavazan plans may include a physical cultural/educational institution in Iran containing activities such as:

* Iranian music tuition;
* pottery/art;
* cultural events;
* student concerts;
* instrument/art retail.

Do not automatically create a separate platform.

Investigate reuse of existing Delnavazan infrastructure where appropriate.

For academy bookings, investigate whether the existing Amelia installation can support multiple locations such as:

* Online;
* Tehran;
* future physical branches.

Validate in staging before changing production booking architecture.

## 10. Iran routing

Do not depend on IP geolocation to determine Iranian versus international experience because VPN usage makes this unreliable.

Prefer explicit navigation/entry points.

A future `delnavazan.ir` domain may act as an additional entry point or redirect, but does not automatically justify a separate application.
