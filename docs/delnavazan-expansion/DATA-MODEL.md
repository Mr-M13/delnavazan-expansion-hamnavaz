# Hamnavaz Data Model Specification

## 1. Principle

Teacher information must be structured.

Do not build the directory around manually composed pages whose important information cannot be reliably searched, validated or automated.

WordPress-native content structures are preferred where appropriate.

Custom database tables should be introduced only where technically justified.

## 2. Teacher entity

A teacher record should be capable of representing:

### Identity

* internal teacher ID;
* Persian name;
* English name;
* profile photo;
* short biography;
* full biography.

### Teaching

* instruments;
* specialisations;
* teaching level(s);
* in-person availability;
* optional online availability;
* languages.

### Location

* country;
* state/province;
* city;
* optional suburb/area;
* public location description.

### Contact

* WhatsApp;
* phone;
* email;
* website;
* Instagram;
* YouTube;
* other approved social/contact channels.

### Commercial information

* indicative price;
* currency;
* price type;
* trial lesson availability.

These fields do not imply that Delnavazan processes lesson payments.

### Operational information

* listing status;
* verification status;
* last verified timestamp;
* next verification timestamp;
* created timestamp;
* updated timestamp;
* internal notes.

## 3. Taxonomies and normalisation

Fields heavily used for discovery/filtering should use normalised structures rather than uncontrolled free text.

Likely candidates include:

* instrument;
* country;
* potentially region/city depending on final implementation;
* teaching mode/type where useful.

Avoid premature taxonomy proliferation.

## 4. Status model

Data structures must support at minimum the conceptual states:

* draft;
* pending review;
* active;
* verification due;
* on hold;
* archived.

WordPress-native status mechanisms may be used where appropriate, but lifecycle semantics must remain clear.

## 5. Future-ready fields

Architecture should be capable of later supporting dormant/future information such as:

* premium status;
* premium start;
* premium expiry;
* premium tier;
* verified badge;
* verification method;
* verification date;
* featured status;
* featured priority;
* featured expiry;
* Stripe customer/subscription references;
* payment/subscription state;
* teacher self-management enabled;
* teacher account/user ID;
* availability-request feature enabled.

Do not necessarily expose or implement these in V1 merely because they are anticipated.

## 6. Privacy classification

Public and private data must be intentionally separated.

Never accidentally expose:

* internal notes;
* private payment references;
* security tokens;
* operational logs;
* administrative-only metadata.

Residential street addresses should not be required or public by default.

## 7. Data deletion

Prefer archive/soft removal for normal operational workflows.

Permanent deletion should be deliberate and authorised.

Uninstall behaviour must be carefully designed before production use; uninstalling the plugin must not unexpectedly destroy valuable teacher records.

## 8. Schema/version evolution

Plugin version and data/schema version should be conceptually separate once schema migrations become necessary.

Migrations must be explicit, versioned and safe to retry where practical.

Never rely on undocumented manual database edits.
