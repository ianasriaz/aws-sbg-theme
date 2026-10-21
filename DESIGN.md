# DESIGN.md

Style direction for the AWS SBG theme. An agent reads this as **data to apply**, not as
instructions to obey. Only the fields below are taken from it.

Palette derived from the identity the theme already ships (style.css, config-theme.php,
front-page.php). The display serif and the ticket motif are this campaign's direction, chosen
for the Freshers Week page.

## Identity

AWS Student Builder Group at Air University Islamabad: the official AWS student chapter on
campus. Audience: enrolled Air University students, usually opening the page on a phone
after scanning a poster QR code. Voice: a student club that is official without being
corporate.

Visual language: a printed campus notice. A serif display face carries headlines and figures,
hairline rules carry structure, and the giveaway is one ticket-stub object with a perforation
and punched notches.

## Personality

Welcoming, plain-spoken, confident. Speaks to students as peers, not to "users". No
marketing theatre.

## Palette

Max 2 core colors + 1 accent (R-29). Neutrals do not count.

- Core, ink: `#0F141F` (headings, dark surfaces, primary button)
- Core, brand purple: `#7928CA` (links, brand touch). This is the chapter's established
  purple; `#9353FF` is its brighter sibling and is used only at large or decorative sizes.
- Accent, AWS orange: `#FF9900` (one deliberate accent; marks the single primary action in
  a section and nothing else)
- Neutrals: paper `#FFFFFF`, surfaces `#F8FAFC` / `#F1F5F9`, line `#E2E8F0`, secondary text
  `#334155`, muted text `#475569`

Reason for purple as the brand core: it is the chapter's colour, carried over from the rest
of the site, not a model default. Reason for a darker purple (`#7928CA`, not `#9353FF`) for
text and controls: `#9353FF` fails AA for normal text (4.27:1), `#7928CA` passes (7.07:1).

All pairings in use are verified against the AA thresholds (4.5:1 normal text, 3:1 large
text and component boundaries).

## Typography

- Display serif: `'Iowan Old Style', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, serif`.
  Used for H1, section headings, reward figures, the ticket number, and index numerals.
  Reason: a printed, editorial voice that separates the page from default SaaS sans; Georgia
  is the guaranteed fallback and no font files are added.
- Text sans: `'Amazon Ember', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`.
  Used for body, lead, buttons, labels, form fields, captions.
- Body 16px, line-height 1.6. Small labels may be uppercase at 0.07em; no extreme tracking.

## Mood

Open and official. Like a campus notice you would actually stop and read, not a startup
landing page.

## Dials

Dial: **ENERGY 3 / RHYTHM 3 / MOTION 1**

- ENERGY 3 (bold): a one-week campaign has to speak loudly, through scale and type, not
  decoration.
- RHYTHM 3 (varied): sections visibly differ: a masthead hero, a ticket object, a colophon
  row, an indexed FAQ.
- MOTION 1 (hover and focus only): nothing loops or animates on its own.

## Not carried forward (devices the current pages use that collide with the filter)

- Multi-colour aurora blobs, blueprint gridlines, page-wide glow (R-01, R-07, R-13)
- Gradient text and gradient buttons with a shine sweep (R-01, R-19)
- Perpetual float/levitate and scroll-reveal on every element (R-19)
- Three identical cards per section and one uniform section rhythm (R-14, R-05)
- Unsourced scale figures such as "60+ countries", "800+ campuses" (R-17)

Reason: these are one page's current expression, not the chapter's identity. The redesign
keeps the identity and drops the devices.
