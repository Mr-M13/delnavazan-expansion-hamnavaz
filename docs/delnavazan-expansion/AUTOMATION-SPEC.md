# Hamnavaz Automation and Listing Lifecycle Specification

## 1. Objective

The directory must remain trustworthy over time.

Teacher records should not remain publicly visible indefinitely when their information may no longer be valid.

Automation must therefore support verification, reminders, temporary hiding and archival while protecting against accidental data loss.

## 2. Conceptual lifecycle

Preferred lifecycle:

**DRAFT → PENDING REVIEW → ACTIVE → VERIFICATION DUE → ACTIVE / ON HOLD → ARCHIVED**

Exact internal implementation may differ if there is a strong technical reason, but behaviour must preserve these concepts.

## 3. Verification interval

Suggested initial lifecycle:

* teacher activated;
* approximately six months later: verification requested;
* if no response, reminder approximately 30 days later;
* continued non-response may place listing ON HOLD / hide it from public discovery;
* extended inactivity may eventually archive the listing.

Intervals must be configurable rather than hard-coded throughout the application.

## 4. Never hard-delete for inactivity

Automated lifecycle processes must never permanently delete a teacher simply because they did not respond.

Use archival/status changes.

Administrative restoration should remain possible.

## 5. Verification link

Teacher verification should eventually use a secure tokenised link.

Possible actions:

* Everything is correct
* Update my information
* Remove my listing

### Confirm

Confirmation should:

* record verification timestamp;
* calculate next verification;
* restore/maintain ACTIVE status where appropriate.

### Update

Teacher receives a controlled editing mechanism.

Changes affecting public information should normally return to administrator review before publication.

### Remove

Teacher-requested removal should normally archive/unpublish rather than hard-delete the underlying record.

## 6. Tokens

Verification tokens must be:

* cryptographically secure;
* sufficiently random;
* expiring;
* revocable where appropriate;
* stored safely;
* resistant to replay/misuse.

Do not expose sensitive token material unnecessarily in logs.

## 7. Notification abstraction

Hamnavaz notifications must not be tightly coupled to Amelia.

Conceptual architecture:

**Hamnavaz Event → Notification Service → Channel**

Channels may include:

* email;
* WhatsApp;
* future channels.

Potential events:

* verification_due;
* verification_reminder;
* listing_suspended;
* listing_reactivated;
* profile_update_received;
* profile_update_approved.

Future commercial events may include:

* premium_expiring;
* premium_expired;
* payment_failed.

## 8. Fallback behaviour

WhatsApp should not become a single point of failure.

Where appropriate:

WhatsApp failure → email fallback

Notification attempts/failures should be logged sufficiently for troubleshooting.

A notification delivery failure must not itself automatically delete or permanently suspend a listing.

## 9. Scheduling

Use WordPress-native scheduling or another justified architecture appropriate to scale.

Scheduling must be idempotent enough to avoid repeated duplicate actions.

Do not assume WordPress cron runs at exact times.

## 10. Auditability

Important lifecycle events should be traceable, including:

* teacher created;
* application received;
* teacher approved;
* verification requested;
* verification confirmed;
* public details changed;
* listing put on hold;
* listing archived;
* listing restored;
* notification failed.

Avoid logging unnecessary sensitive information.
