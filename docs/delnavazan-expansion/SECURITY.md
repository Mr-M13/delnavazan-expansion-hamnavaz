# Delnavazan Expansion Security Specification

## 1. Security principle

All expansion functionality must follow WordPress security practices and minimise unnecessary attack surface.

Security controls must be designed into features rather than added after implementation.

## 2. Core requirements

Where applicable use:

* capability checks;
* nonces;
* sanitisation;
* validation;
* escaped output;
* prepared database queries;
* CSRF protection;
* secure token generation;
* appropriate authentication/authorisation;
* rate limiting.

## 3. File safety

PHP entry points should prevent unintended direct execution where appropriate.

Do not modify:

* WordPress core;
* Amelia core;
* third-party plugin files;
* parent theme files.

## 4. Secrets

Never commit:

* API secrets;
* access tokens;
* passwords;
* Stripe secret keys;
* Meta credentials;
* private webhook secrets;
* production credentials.

Use appropriate environment/configuration mechanisms.

`.gitignore` must exclude local/system artefacts and any future secret-bearing local configuration.

## 5. Administrative actions

Administrative functionality must verify appropriate capabilities.

State-changing actions must use appropriate CSRF/nonce protection.

Do not rely solely on hiding UI controls.

## 6. Teacher submissions

Public teacher application forms require:

* validation;
* sanitisation;
* anti-spam controls;
* rate limiting where appropriate;
* consent capture;
* safe media handling;
* moderation before publication.

Never auto-publish public submissions.

## 7. Verification tokens

Teacher verification/update links require secure random tokens with expiration and appropriate revocation/rotation strategy.

Token validation should not reveal unnecessary information about whether a teacher exists.

## 8. Output

Escape output according to context.

URLs, HTML attributes, text and allowed HTML require appropriate WordPress escaping mechanisms.

## 9. Database

Use WordPress APIs where suitable.

Any direct SQL must use prepared queries.

Do not trust IDs or parameters simply because they originated from an administrator-facing page.

## 10. External integrations

External services such as Meta/WhatsApp or Stripe must be isolated behind maintainable integration boundaries.

Validate webhook authenticity using the provider's supported mechanism.

Design webhook processing to be idempotent where duplicate delivery is possible.

## 11. Logging

Security/operational logs should provide useful diagnostics without unnecessarily storing:

* full tokens;
* credentials;
* excessive personal information.

Define reasonable retention policies as logging develops.

## 12. Backups and rollback

Before significant production changes:

* maintain WordPress backup;
* maintain database backup;
* use version-controlled code;
* test in staging where practical;
* preserve documented rollback path.
