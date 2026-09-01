# Hamnavaz UI/UX Specification

## 1. Experience principles

Hamnavaz should feel like a high-quality Delnavazan cultural initiative rather than a generic directory or commercial marketplace.

Design characteristics:

* elegant;
* calm;
* culturally appropriate;
* trustworthy;
* modern;
* Persian-friendly;
* mobile-first.

Avoid excessive marketplace badges, cluttered filters and aggressive sales language.

## 2. Mobile first

Design primarily for approximately 360–430 px widths before expanding to desktop.

Requirements:

* large touch targets;
* minimal typing;
* readable Persian typography;
* clear spacing;
* simple progressive interactions;
* fast loading;
* correct mixed RTL/LTR content.

## 3. Search experience

Initial V1 should not require a map.

Preferred progressive flow:

Country → City → Instrument → Results

Do not create a visually dense filter panel simply because the underlying data supports many fields.

Additional filters can be introduced only when user need justifies them.

## 4. Teacher result cards

Cards should communicate the information necessary to decide whether to inspect a teacher without becoming miniature full profiles.

Potential card information:

* photograph;
* teacher name;
* instrument(s);
* city/country;
* concise speciality/description;
* teaching mode;
* freshness/verification indicator where appropriate;
* profile CTA.

Premium presentation, if introduced later, should remain tasteful.

## 5. Teacher profile

Profiles should establish trust quickly.

Likely hierarchy:

1. identity/photo;
2. instrument/specialisation;
3. location;
4. concise introduction;
5. teaching details;
6. biography;
7. contact methods;
8. freshness/verification information.

Contact actions, especially WhatsApp where available, should work well on mobile.

## 6. Empty states

No-result states should explain the situation and provide alternatives.

Possible alternatives:

* broaden location;
* related teachers;
* teachers elsewhere in the country;
* Delnavazan online academy;
* suggest a teacher.

## 7. Homepage integration

Do not replace the current Delnavazan homepage positioning.

Hamnavaz should initially appear farther down the homepage as a secondary initiative.

Potential presentation:

* short cultural/community explanation;
* “Find a Teacher” CTA;
* tasteful selected teacher cards or rotating editorial selection.

Avoid turning the homepage into a directory interface.

## 8. Accessibility

Meet appropriate modern accessibility expectations.

Include:

* keyboard navigation;
* visible focus behaviour;
* form labels;
* semantic headings;
* meaningful alt text;
* sufficient contrast;
* screen-reader-compatible controls;
* correct language attributes;
* correct RTL handling.

## 9. RTL and multilingual content

Persian content must render naturally RTL.

English names, URLs, phone numbers and mixed-language fields must remain readable.

Prefer logical CSS properties where practical.

Do not assume every profile is exclusively Persian or exclusively English.

## 10. Performance

Avoid heavy UI frameworks without compelling justification.

Prefer:

* minimal JavaScript;
* lazy-loaded imagery;
* responsive images;
* WebP/AVIF where supported;
* efficient queries;
* pagination where needed;
* assets loaded only on relevant Hamnavaz pages/components.
