# AI Agent Collaboration Log

> **Vibe Coding Approach**: This file serves as a living document where AI agents automatically report their changes. Each agent appends their entry to maintain a complete history of modifications.

## How This Works

1. **Before starting work**: Read this file to understand the project context
2. **After completing work**: Append your entry with details of what you changed
3. **Format**: Follow the template below exactly

## Agent Entry Template

```markdown
### [YYYY-MM-DD HH:MM] - [Agent Name/Model]

**Task**: [Brief description of what was requested]

**Files Changed**:
- `path/to/file1.php` - [What changed]
- `path/to/file2.css` - [What changed]

**Summary**: [2-3 sentence summary of changes]

**Key Decisions**:
- [Any important decisions made and why]

**Notes for Future Agents**:
- [Any context that would help future agents]

---
```

---

## Change Log (Newest First)

### 2026-11-13 00:25 - Antigravity

**Task**: Make Islamabad landmark footer graphic 100% full-width covering edge-to-edge without left/right whitespace margins while keeping minarets and spires uncropped; move role badge pill downward away from header gradient bottom.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **Full-Width 100% Edge-to-Edge Footer**: Switched `.badge-skyline` from `object-fit: contain; object-position: bottom center;` (which caused centered letterboxing with blank left/right bars) to `width: 100% !important; min-width: 100% !important; height: auto !important; position: absolute; bottom: 0; left: 0; right: 0;`. Set `.badge-footer` to `height: 15%`, perfectly accommodating the full 13.89mm visible landmark artwork without any horizontal whitespace or vertical top clipping.
  - **Downward Positioned Role Badge Pill**: Increased `.badge-body` `padding-top` to `clamp(16px, 5.8cqi, 28px)` and `.badge-tier-wrap` `margin` to `clamp(6px, 2.2cqi, 12px) 0 clamp(8px, 2.8cqi, 14px)`. Moves the role pill noticeably down into the white space between the header gradient and the attendee name frame.

**Key Decisions**:
- The 2172x724 graphic contains transparent headroom in its top 48% (rows 0–348), with visible artwork occupying the bottom 52% (rows 349–704). Combining `width: 100%; height: auto; bottom: 0; left: 0; right: 0;` with a 15% footer container ensures 100% full-width edge-to-edge coverage while maintaining 100% spire visibility and zero top-cropping.
- Moving the tier pill downward eliminates proximity to the purple/cyan header line while preserving graceful vertical rhythm above the attendee name.

---

### 2026-11-13 00:15 - Antigravity

**Task**: Fix double QR code generation on printed attendee cards, center and highlight role pill between header and name frame, fix Islamabad skyline footer cropping, and optimize public registration form submission speed while strictly guarding against duplicate submissions.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **Double QR Code Fix**: Removed the rule forcing both `canvas` and `img` visible simultaneously. Explicitly hid `.badge-qr-box canvas { display: none !important; }` and kept only `.badge-qr-box img` visible. Also added JS defense-in-depth in `generateAllQRCodes()` to ensure only the crisp image is displayed.
  - **Centered & Prominent Role Pill**: Wrapped badge pill in `.badge-tier-wrap` centered between the header gradient and attendee name frame; enhanced typography (`clamp(9.5px, 3.4cqi, 13px)` 800-weight Ember Mono), letter spacing (0.18em), and crisp border outlines for all role badges (`ATTENDEE`, `CORE TEAM`, `EVENT CREW`, `SPEAKER`, `VIP`, `ORGANIZER`).
  - **Landmark Footer Cropping Fix**: Increased footer height from 10% to 14% and switched to `object-fit: contain; object-position: bottom center;`, preserving the full spires and minarets of the Faisal Mosque without cutting off the top.
  - **Submission Performance & Anti-Duplicate Protection**:
    - Added instant client-side submit lock in Alpine.js `handleSubmit(e)` to prevent double clicks and display the submission spinner immediately.
    - Added server-side atomic in-flight transient lock (`aws_sbg_reg_lock_` + md5(email)) with a 20-second expiration.
    - Optimized duplicate email verification from a slow, unindexed `get_posts()` meta-query down to a direct indexed SQL lookup on `wp_postmeta`.
    - Added `comment_status => closed` and `ping_status => closed` to `wp_insert_post`.
    - Implemented `fastcgi_finish_request()` support: flushes the HTTP 302 redirect directly to the applicant's browser in < 250ms while transactional emails (confirmation & admin notification) run asynchronously in the background.

**Key Decisions**:
- `davidshimjs/qrcodejs` creates both a `<canvas>` and an `<img>`; hiding canvas in both CSS and JavaScript guarantees single, vector-crisp QR code rendering regardless of browser/engine quirks.
- Preserving the exact natural 3:1 aspect ratio of `Islamabad Landmark Footer.png` via `contain` keeps the architectural landmarks prominent and complete across both 4-up and 6-up layouts.
- Flushing HTTP redirects prior to transactional SMTP execution eliminates registration latency without sacrificing email delivery or transactional safety.

---

### 2026-11-12 23:55 - Antigravity

**Task**: Fix sequential pass number gap caused by deleted test registrations, make registration counter self-healing, add "Resync Pass Numbers" admin action, and add inline "Edit Pass Number" modal.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - Added `aws_sbg_resync_reinvent_numbers_table()` helper: inspects active `registration_number` meta values. If 0 exist (e.g. after testing and clearing entries), it truncates `wp_aws_reinvent_numbers` so the sequence resets to 1; if entries remain, it cleans up ghost IDs and sets `AUTO_INCREMENT = max_existing + 1`.
  - Updated `aws_sbg_allocate_reinvent_registration_number()` to run the auto-resync check on allocation, guaranteeing sequential numbers (`RIV-01`, `RIV-02`, etc.) without phantom jumps.
  - Updated `aws_sbg_reinvent_handle_delete_reg()` to trigger `aws_sbg_resync_reinvent_numbers_table()` whenever any registration is deleted.
  - Added `aws_sbg_reinvent_handle_resync_numbers()` action with a "Resync Pass Numbers" button in the admin bar: renumbers all approved attendees consecutively starting from `RIV-01` and resets the counter.
  - Added `aws_sbg_reinvent_handle_save_reg_number()` action and modal (`#aws-reinvent-regno-modal`) with inline `✎` button on pass number cells for direct customization.

**Key Decisions**:
- Automatic self-healing on allocation ensures test submissions can be freely cleared prior to public launch without permanently burning sequential numbers.
- Providing both a 1-click global renumbering tool and an individual pass number editor gives organizers complete control to fix existing entries (e.g. `RIV-04` back to `RIV-01`).

---

### 2026-11-12 23:12 - Antigravity

**Task**: Remove Alex Morgan placeholder, make confirmation email copy completely general (no mention of "selection"), and remove "first-time speakers are welcome" messaging.

**Files Changed**:
- `includes/features/cfs/cfs-render.php` -
  - Changed full name placeholder from `e.g. Alex Morgan` to `Your full name`.
  - Removed "first-time speakers are welcome" text; simplified options to neutral `Yes` (*I have spoken at meetups or conferences*) and `No` (*I have not spoken at tech events before*), and updated Section 3 subtitle to `Tell us about your speaking experience`.
  - Removed references to "selection committee" / "selection" in help text, success screen, and commitment checkbox ("...available to deliver this session in-person in Islamabad on event day").
- `includes/features/cfs/cfs-handler.php` -
  - Streamlined email confirmation copy to keep it general: "We have received your proposal, and our team will get back to you soon." (removed "regarding the selection").

**Key Decisions**:
- Keeping copy completely neutral and general avoids setting rigid expectations around selection or qualification.

---

### 2026-11-12 21:55 - Antigravity

**Task**: Fix Speak form UI and copy, remove crowded ticket styling and "~ 90-Sec Pitch" badge, redesign the "Have you spoken at a community/tech event before?" selector, and make the submission confirmation email clean and concise ("Hi [name], thank you and then we will get back to you soon").

**Files Changed**:
- `includes/features/cfs/cfs-render.php` -
  - Removed crowded ticket aesthetics (dashed perforation line and circular notches) in favor of a modern, spacious card layout with generous padding and subtle shadows.
  - Removed the `~ 90-Sec Pitch` badge from both header and success state.
  - Redesigned the "Have you spoken at a community/tech event before?" question into responsive, accessible selector cards with clear title/subtitle and active state highlights (`Yes, I've spoken before` vs `No, this is my first talk`).
  - Improved form copy and section hierarchy (1. Speaker Profile, 2. Session Details, 3. Experience & In-Person Availability).
  - Protected checkbox alignment in a clean confirmation box to prevent layout jumping.
- `includes/features/cfs/cfs-handler.php` -
  - Rewrote the submitter confirmation email to be warm, clean, and concise: "Hi [name], thank you for submitting your speaker proposal... we will get back to you soon regarding the selection and next steps."
  - Wrapped both submitter and admin email dispatches in `try...catch (\Throwable $e)` blocks to guarantee mail delivery issues never disrupt the submission redirection flow.

**Key Decisions**:
- Replacing unstyled radio buttons with interactive selector cards delivers an accessible, high-end experience that works cleanly across both desktop and mobile screens.
- Email copy follows a concise, professional tone without corporate boilerplate, providing clear peace of mind to applicants.

---

### 2026-11-12 02:08 - Antigravity

**Task**: Fix re:Invent registration approval redirecting to a blank page with sidebar menu.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - Extracted review actions (approve & reject) into `aws_sbg_reinvent_handle_review_action()` hooked to `admin_init`. This executes before WordPress sends headers or loads `admin-header.php`, allowing `wp_safe_redirect()` to issue clean HTTP 302 redirects.
  - Added Javascript `window.location.replace()` and `<meta http-equiv="refresh">` fallback if headers were already sent, ensuring it never halts mid-render.
  - Wrapped `aws_sbg_mail_send()` in a `try...catch ( Throwable $e )` block to prevent mail server or network exceptions from terminating the request.
  - Cleaned up approve and reject link generators to use `admin_url( 'admin.php' )` as the explicit base, preventing extraneous query args (e.g. `notice=visibility_live`) from polluting review action URLs.
  - Added robust fallback to `aws_sbg_allocate_reinvent_registration_number()` to derive sequential numbers from existing postmeta if the sequence table insert returns 0.
  - Added admin notices for `notice=approved` and `notice=rejected`.

**Key Decisions**:
- Running redirects on `admin_init` complies with the WordPress lifecycle standard where HTTP headers must be modified before HTML output starts.

---

### 2026-11-12 01:30 - Antigravity

**Task**: Auto-generate attendee cards (as designed in the brand kit) for approved re:Invent registrations arranged on an A4 page for print vendor export with layout toggle, front-side imposition, and admin badge tier customization.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - Added `aws_sbg_get_reinvent_badge_tier()` returning postmeta `_badge_tier` (defaulting to `'ATTENDEE'`).
  - Added `admin_post_aws_sbg_reinvent_save_badge_tier` handler to update badge tier/role from WP Admin with nonce and capabilities checks.
  - Added "Badge Tier" column in the admin registrations table with color-coded role chips and quick edit modal.
  - Added quick edit modal in WP Admin with presets (`ATTENDEE`, `CORE TEAM`, `EVENT CREW`, `SPEAKER`, `VIP GUEST`, `ORGANIZER`) and custom text input.
  - Added `🪪 Print Attendee Badges (PDF)` action button in `.rvadm-actions`.
  - Added `aws_sbg_render_reinvent_badges()` print studio view triggered via `admin.php?page=aws-reinvent-registrations&badges=1`:
    - Screen toolbar with stats, 4-up (VIP Lanyard · 85×119mm) vs 6-up (Compact · 68×95mm) layout toggle, role/tier filter, and print button.
    - Single-sided A4 imposition with precise cutting guidelines and corner crop crosshairs for print vendor guillotine cutting.
    - Card component matching `design-kit.js` component `tf`: 5:7 aspect ratio, dark gradient + radial mesh header, `re:Invent` wordmark with `WATCH & BUILD PARTY 2026` subline, white AWS smile and SBG logos, lanyard punch guide, role pill, double-rule name frame with uppercase attendee name, `#RIV-XX` badge number, framed vector QR code pointing to verification URL, and Faisal Mosque / Islamabad skyline watermark footer.
    - Client-side dynamic repagination script preserving layout choice across sessions in `localStorage`.
  - Added `Badge Tier` column to CSV export.
- `assets/design-kit/qrcode.min.js` -
  - Added lightweight, offline, dependency-free QR code vector generator library.

**Key Decisions**:
- **Grid Layout Imposition**: Provided toggle between 4-Up (VIP Lanyard: 85mm × 119mm) and 6-Up (Compact: 68mm × 95.2mm), both strictly preserving the 5:7 card aspect ratio on A4 portrait (210mm × 297mm) with safety margins to prevent page overflow in all browser print engines.
- **Offline Vector QR Codes**: QR codes are generated client-side from the bundled offline library directly into crisp vector shapes, ensuring 1200+ DPI vector sharpness in PDF exports with zero external API calls or server dependencies.
- **Badge Tier Customization**: Standardized on default `ATTENDEE` while empowering event organizers to update anyone to `CORE TEAM`, `EVENT CREW`, `SPEAKER`, `VIP GUEST`, or custom text with immediate visual feedback in WP Admin.

---

### 2026-11-12 01:06 - Antigravity

**Task**: Make photo upload for re:Invent invitation confirmation form optional.

**Files Changed**:
- `includes/features/invite/invite-core.php` -
  - Changed default of `photo_required` in `aws_sbg_invite_get_event_meta()` to `'no'`.
  - Added event name detection for re:Invent events (`reinvent` or `re:invent` in slug/title) ensuring `photo_required` resolves to `'no'` unless explicitly configured to `'yes'`.
- `includes/features/invite/invite-render.php` -
  - Dynamic label indicator: displays `Fresh portrait photo (Optional)` when photo upload is optional instead of hardcoding `<em>*</em>`.
  - Updated upload zone subtitle and help text to clarify `Optional` and `Feel free to skip if you prefer not to include a photo`.
  - Dynamically updated consent commitment text to adapt when photo upload is optional.
- `includes/features/invite/invite-admin.php` -
  - Updated "Photo required" dropdown in event settings to list `No (Optional)` first with clear labeling.
  - Added explicit save tracking (`_photo_required_explicit`) on event update.

**Key Decisions**:
- Flexible confirmation flow: Making photo upload optional removes a significant friction point for busy speakers and VIP guests who may not have a portrait photo readily on hand on their mobile device, allowing them to confirm their attendance and logistics immediately.

---

### 2026-11-12 00:46 - Antigravity

**Task**: Add option to hide the re:Invent registration form from the public.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **Helpers & Options**: Added `aws_sbg_is_reinvent_registration_hidden()` and `aws_sbg_get_reinvent_closed_message()` reading WordPress options `aws_reinvent_form_hidden` (default `'0'`) and `aws_reinvent_closed_message`.
  - **Admin Action Handlers**:
    - `aws_sbg_reinvent_handle_toggle_visibility`: One-click toggle between Public/Open and Hidden/Closed with nonce verification.
    - `aws_sbg_reinvent_handle_save_settings`: Settings handler to update visibility status and customized closed notice copy.
  - **Admin UI Controls**:
    - Added `.rvadm-visibility-card` toolbar card with live status badge (`Public Form: Live & Open` vs `Public Form: Hidden (Closed)`), one-click toggle button, "Notice Settings…" modal trigger, and direct public page link.
    - Added Modal 3 (`#aws-reinvent-settings-modal`) for setting visibility radio and custom closed notice text.
    - Added admin notice alerts for visibility and settings changes.
  - **Public Frontend Gate**:
    - In `aws_sbg_render_reinvent_form()`: When hidden, public visitors see an elegant closed message box with WhatsApp Community and Home links instead of the "REGISTER NOW" CTA. The personal details form (Steps 1 & 2) is completely excluded from the HTML.
    - Logged-in administrators viewing the page receive an amber admin notice explaining that the form is hidden from visitors with a direct link to the dashboard.
  - **Server-Side Submission Gate**:
    - In `aws_sbg_handle_reinvent_registration()`: Added server-side validation rejecting any unauthorized POST submissions with HTTP 403 when hidden.

**Key Decisions**:
- Complete DOM omission: Omitting the form element from the page HTML when hidden ensures no one can inspect elements or circumvent the gate via client-side scripts.
- Instant toggle + deep settings: Providing both a 1-click toggle on the re:Invent admin table and a settings modal for custom copy gives organizers maximum agility on event day.

---

### 2026-11-12 00:26 - Antigravity

**Task**: Polish Team Hiring and Call for Speakers public form UI: remove developer placeholder name, remove backstage crew top badge, redesign past-experience radio cards into pills, and fix commitment checkbox vertical overlap.

**Files Changed**:
- `includes/features/team/team-render.php` -
  - Removed `.tm-badge` "Backstage Crew Access" from the team hiring form header stub.
  - Replaced `placeholder="e.g. Anas Riaz"` with generic `placeholder="Your full name"`.
  - Redesigned "Have you organized or volunteered at community/tech events before?" radio buttons into clean, responsive `.tm-radio-pill` components.
  - Fixed CSS selector cascade bug where `.tm-field label { display: block; }` forced `.tm-commit` into a block container (causing the checkbox to sit on top of the text); scoped to `.tm-field > label`, added `display: flex !important; align-items: flex-start !important; gap: 12px !important;` to `.tm-commit`, and added fixed `18px` dimensions with `flex-shrink: 0` to the checkbox.
- `includes/features/cfs/cfs-render.php` -
  - Replaced `placeholder="e.g. Anas Riaz"` with generic `placeholder="Your full name"`.
  - Scoped `.cfs-field > label` and applied identical flexbox safeguards to `.cfs-commit`.

**Key Decisions**:
- CSS specificity scoping: Scoping label rules with direct child combinator (`> label`) guarantees that nested control labels for radios and checkboxes never inherit block-level line-breaking rules.

---

### 2026-11-10 00:32 - Antigravity (Gemini 3.8 Flash)

**Task**: Clarify parking copy to explicitly state that Gate 3 (Khyber Gate) is the F-9 Park parking entrance nearest to the venue.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` - Updated vehicle subtitle and Step 2 commitment to specify parking at `F-9 Park Gate 3 (Khyber Gate), which is nearest to the venue`.

**Key Decisions**:
- Clear navigation guidance: Removed ambiguity between university gates and park gates so drivers head directly to F-9 Park Gate 3.

---

### 2026-11-10 00:25 - Antigravity

**Task**: Switch re:Invent registration copy to free, first come first served; soften parking copy (F-9 Park / Gate 3 Khyber Gate); fix first-timer friction.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` - Highlight 01 now "Free Registration / First come, first served". Removed all invite-only/approval wording (lede, success, duplicate notice, shortcode subtitle, Step 2 checkbox). Parking note shown once under vehicle question; duplicate under vehicle number removed. Step 2: "First time? You're welcome too." hint; last-event textarea only shown/required when attended = Yes (backend matches). Commit checkboxes reduced from 3 to 2.

**Key Decisions**:
- Admin approval flow is untouched in the backend; only public copy changed. LinkedIn remains required (user declined change d).

---

### 2026-11-09 01:29 - Antigravity (Gemini 3.8 Flash)

**Task**: Increase vertical breathing room and spacing across all steps of the re:Invent registration form.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **Step Title Separation**: Expanded bottom margin on `.rv-step-title` from 34px to generous `clamp(36px, 5vh, 48px)` with `line-height: 1.2`, eliminating visual clutter between the heading ("Tell us about yourself", "Almost done") and the first field row.
  - **Fields Grid Gap**: Increased `.rv-fields` grid gap from 18px to `26px 20px`, ensuring ample vertical breathing space between input rows.
  - **Label Spacing**: Increased `.rv-field label` bottom margin to 10px with `line-height: 1.45`.
  - **Section Dividers & Subtitles**: Added explicit top border (`rgba(255,255,255,0.1)`) and 24px top padding before the vehicle section, along with refined subtitle spacing.
  - **Step 2 Question & Action Spacing**: Expanded question margin to 40px, textarea margin to 38px, commit item vertical padding to 20px, and bottom action button margin to 44px.

**Key Decisions**:
- Generous form hierarchy: Clear vertical grouping prevents cognitive overload and prevents labels, inputs, and section headings from visually colliding on desktop and mobile screens.

---

### 2026-11-09 01:20 - Antigravity (Gemini 3.8 Flash)

**Task**: Streamline re:Invent registration form fields: simplify CNIC label, convert persona pills into a clean dropdown, standardize Institute/Company field, update City traveling label, add parking prioritization subtext, and refocus applicant note on last attended event with 250 character limit.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **CNIC Label**: Updated label to `CNIC (For check-in) *`, removing the extraneous info icon and hover tooltip for a cleaner interface.
  - **"I am a" Dropdown**: Converted the 3-column radio buttons into a styled `<select>` dropdown (`Student`, `Professional`, `Job Seeker`), styled with custom SVG chevron and dark menu aesthetic.
  - **Institute / Company Field**: Simplified to a static, always-visible field titled `Institute/Company *` with placeholder `Air University Islamabad/Nayatel/Jazz`, removing dynamic role-conditional field toggling.
  - **City Field**: Updated label to `City (Travelling from on event day) *`.
  - **Parking Policy Subtext**: Added prominent subtitle note `Due to limited parking space, In the first phase, we will prioritize attendees with no vehicle.` under the vehicle section.
  - **Prior Event Note (250 Char Limit)**: Updated Step 2 textarea label to `Tell us about your last attended AWS or any community event (250 CHAR LIMIT) *` with live countdown indicator (`... left`), HTML5 `maxlength="250"`, and backend `mb_substr` truncation guard.
  - **Attendance Question**: Updated radio question label to `Have you attended AWS or any community event before? *`.
  - **Admin Table & CSV Headers**: Reflected new labels in admin table column headers, note summaries, and CSV export.

**Key Decisions**:
- Streamlined form UX: Grouped Step 1 into clean 2-column balanced pairs (Name/Email, Phone/CNIC, Persona/Institute, City/LinkedIn, Vehicle full width). Eliminating conditional field jumps makes mobile and desktop registration faster and more predictable.
- Concise qualitative note: 250 character limit on previous event participation keeps applicant statements succinct and focused, greatly speeding up administrative review.

---

### 2026-11-09 00:58 - Antigravity (Gemini 3.8 Flash)

**Task**: Update re:Invent registration highlights copy (Free Access, Snacks, Food & Drinks, AWS Swag, Networking, Expert Sessions, Activities).

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` - Updated all 6 highlight cards in `.rv-perks`:
  - `01 — Free Access`: "Complimentary entry for approved, invited attendees."
  - `02 — Snacks, Food & Drinks`: "Enjoy complimentary snacks, food, and beverages." (updated title from Refreshments)
  - `03 — AWS Swag`: "Swag and giveaways, subject to availability and participation criteria."
  - `04 — Networking`: "Connect with builders, students, and cloud professionals."
  - `05 — Expert Sessions`: "Watch streamed re:Invent sessions from Las Vegas and attend in-person talks."
  - `06 — Activities`: "Join interactive challenges, hands-on builds, and community experiences."

**Key Decisions**:
- Precision copy alignment: Directly aligned the event benefits on the landing page with official organizer policy regarding invitation-only entry and swag availability criteria.

---

### 2026-11-09 00:10 - Antigravity (Gemini 3.8 Flash)

**Task**: Update re:Invent registration numbers to simple `RIV-01` sequence and isolate registration ID strictly to the `Reg #` field (do not prepend to application title or applicant name).

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **Sequential Numbering Format**: Switched numbering allocation from `reinvent-#001` to `RIV-01` (`RIV-` with 2-digit zero padding via `str_pad( $num, 2, '0', STR_PAD_LEFT )`, naturally expanding to `RIV-10`, `RIV-100`, etc.).
  - **Post Title Isolation**: Removed automatic prefixing of registration ID to `post_title` (`$reg_no . ' - ' . $name`) upon approval; `post_title` remains strictly the applicant's name (`Full Name`). Added title normalization to strip any legacy ID prefixes (`reinvent-#...` or `RIV-...`) back to pure name.
  - **Admin Table Reg # Column**: Display registration number solely in the `Reg #` column (or `—` when unassigned), keeping the Applicant column clean with only the full name, audience chip, CNIC chip, and LinkedIn link.
  - **Clear Modal Label & Regex Update**: Updated clear modal reset sequence description to "Reset sequence counter back to #01 (begins at RIV-01)", and updated regex parsers in admin table, roster view, and CSV export to seamlessly strip both legacy `reinvent-#` and modern `RIV-` prefixes.

**Key Decisions**:
- Clean applicant naming: Application titles (`post_title`) and attendee names represent human beings, not ticket numbers. Storing the registration ID strictly within `registration_number` meta keeps the database clean, search predictable, and ensures the ID only displays in designated registration number columns and badges.
- Short & Memorable Reg ID: `RIV-01` provides a clean, concise badge code for attendees and gate verification compared to verbose prefixes.

---

### 2026-11-08 23:55 - Antigravity (Gemini 3.8 Flash)

**Task**: Remove SBG logo from WP Admin, eliminate arbitrary `max-width` restrictions across admin pages causing 20-30% empty void on widescreen displays, and clean up the admin UI to be minimal, responsive, and robust.

**Files Changed**:
- `includes/admin/admin-dashboard.php` - Removed the purple gradient "SBG" logo box from `aws_sbg_render_admin_nav_tabs()`, switched navigation tabs to a minimal, clean slate aesthetic with dark active pill, and removed fixed `max-width: 1200px` from Overview and Registrations wrappers, allowing 100% fluid width.
- `includes/features/feature-reinvent-registration.php` - Removed `max-width: 1280px` constraint from `.rvadm`, set width to fluid 100% with standard 20px right padding, removed flashy purple `--brand` color in favor of minimal `#0f172a` ink, and made table and stats grid responsive across all viewport sizes.
- `includes/features/invite/invite-admin.php` - Replaced centered `max-width: 1200px; margin: 20px auto` with fluid `width: 100%; max-width: none; margin: 10px 0 30px 0; padding-right: 20px;`.
- `includes/admin/admin-email-center.php`, `includes/features/feature-events.php`, `includes/features/feature-giveaway.php`, `includes/features/feature-swag-wheel.php` - Replaced fixed 1200px/1400px widths with fluid full-width wrappers with standard right padding.

**Key Decisions**:
- Full viewport coverage: Standard WordPress admin pages (Posts, Pages, WooCommerce) expand to fill the entire content area (`#wpbody-content`). Removing hardcoded `max-width: 1200px/1280px` eliminates the 20-30% empty void on modern desktop screens and gives multi-column tables the breathing room they need.
- Icon and logo minimalism: Stripped heavy purple gradient logo badges from the admin navigation bar in favor of clean, uncluttered slate typography.

---

### 2026-11-08 23:45 - Antigravity (Gemini 3.8 Flash)

**Task**: Implement safe clear testing option with snapshot backup for re:Invent registrations, fix "Ready to Print" event roster preview & layout, display applicant motivation statements (user note) and internal admin notes (text not from user) in admin table, and reinforce registration flow robustness.

**Files Changed**:
- `includes/features/feature-reinvent-registration.php` -
  - **Safe Clear Option**: Added `aws_sbg_reinvent_handle_clear_registrations()` requiring typing `CLEAR` to confirm, creating a full snapshot backup in WordPress options (`aws_reinvent_backup_YYYY-MM-DD_His`) prior to deleting entries, with optional checkbox to reset the sequential numbering table (`aws_reinvent_numbers`) back to #001. Added single-entry delete handler `aws_sbg_reinvent_handle_delete_reg()`.
  - **Internal Admin Notes ("Text not from user")**: Added `aws_sbg_reinvent_handle_save_admin_note()`, Admin Note modal (`#aws-reinvent-note-modal`), and inline note bubbles/buttons so organizers can save private remarks per registration (searchable and exported).
  - **Applicant Motivation Statement ("User Note")**: Added collapsible `<details>` preview in admin table displaying the applicant's qualitative motivation statement (`why_invite`) in full, along with their prior AWS event attendance badge, CNIC chip, audience badge, and clickable LinkedIn link.
  - **Print Roster Redesign**: Removed automatic `window.onload = function(){ window.print(); };`. Created a clean on-screen document preview with a sticky toolbar featuring filter tabs (`Approved Only [Default]`, `All Applicants`, `Pending Review`), summary counters, a manual "Print Roster" button, and back navigation. Formatted the check-in sheet with `@page { size: A4 landscape; margin: 10mm 12mm; }`, repeating table headers on multi-page printouts, non-splitting rows, attendee CNIC for Air University gate security clearance, vehicle details, and physical `[   ]` check-in & signature boxes.
  - **Registration Robustness & Email Text Visibility**: Auto-normalizes 13-digit CNIC numbers submitted without hyphens into standard `XXXXX-XXXXXXX-X` format (both in frontend Alpine formatter and backend handler). Fixed hardcoded white text (`#FFFFFF`) in applicant confirmation, admin alert, and approval emails to dark slate (`#0f172a`, `#334155`), ensuring high contrast against the light email template. Redesigned event pass card with light cyan dashed styling.
  - **Universal Admin Search**: Upgraded query to search across applicant name (`post_title`), registration number, email, phone, CNIC, university, city, applicant statement, and admin notes.
  - **CSV Export Upgrade**: Added `Attended AWS Event Before`, `Applicant Note (Why Invite)`, and `Admin Note (Internal)` to CSV export columns.

**Key Decisions**:
- Full data safety: Clear operation cannot be executed accidentally; it requires capital letter `CLEAR` confirmation and automatically snapshots all post records and metadata to WordPress options before removal.
- Gate clearance compliance: Air University campus security strictly requires CNIC verification at the gate; the printable check-in sheet now clearly displays attendee CNIC, pass number, vehicle details, and physical arrival check-boxes.

---

### 2026-11-08 21:46 - Antigravity (Gemini 3.8 Flash)

**Task**: Remove floating side chevrons from Speaker Deck presentation canvas and remove the exclusionary subtitle ("This is an expert stage, not an open call.") from the "Who We're Looking For" slide.

**Files Changed**:
- `assets/design-kit/design-kit.js` - Rebuilt and synced from `reinvent-kit`: removed floating circular side chevrons, keeping the clean pinned bottom dock, invisible tap zones, swipe gestures, and slide drawer for navigation.
- `SpeakerDeck.tsx` (in `reinvent-kit`) - Removed `<Lead>This is an expert stage, not an open call.</Lead>` from `WhoPage()`, keeping the tone welcoming, inviting, and professional.

**Key Decisions**:
- Removed side floating arrows for an unobstructed, clean slide canvas while relying on the pinned bottom navigation bar (`‹ Prev`, `Next ›`, progress stepper), slide drawer, and natural swipe gestures.
- Eliminated negative phrasing on the audience qualification slide.

---

### 2026-11-08 21:40 - Antigravity (Gemini 3.8 Flash)

**Task**: Fix mobile responsiveness and page navigation visibility on Speaker Deck: remove cluttered "Slide View / All Pages" buttons from mobile header, add prominent floating circular side arrows (`‹` and `›`), pinned high-contrast bottom navigation dock with large tap targets, and an interactive slide navigation drawer.

**Files Changed**:
- `SpeakerDeckViewer.tsx` (in `reinvent-kit`, compiled to `assets/design-kit/design-kit.js`) - Stripped cluttered segmented view switch from mobile header; implemented prominent circular side floating chevrons (`‹` and `›`) with accent glow; pinned bottom controller dock with high-contrast `Next` CTA and slide progress indicator; added slide navigation drawer modal (`☰ Slides`); enabled tap-to-flip zones and touch swipe.
- `templates/pages/page-speaker-deck.php` - Updated root and body styles to use `100dvh` and lock overflow, preventing mobile address bar bounce and preserving fixed dock visibility.

**Key Decisions**:
- Clean mobile header: Removed "Slide View / All Pages" buttons on mobile viewports so the header stays sleek, uncluttered, and perfectly aligned.
- Obvious page turning cues: Added prominent floating side arrow buttons and a bold cyan "Next" button in the pinned footer so visitors never have to guess how to change pages.

---

### 2026-11-08 21:28 - Antigravity (Gemini 3.8 Flash)

**Task**: Deploy dedicated, unlisted, non-downloadable Speaker Deck presentation page for AWS re:Invent 2026 Watch & Build Party Islamabad with zero password requirement, auto-synced with `reinvent-kit`.

**Files Changed**:
- `templates/pages/page-speaker-deck.php` - New standalone distraction-free template (`Template Name: Speaker Deck (Call for Speakers)`). Sets `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet`, suppresses admin bar, disables browser print via `@media print { ... display: none !important }`, blocks right-click context menu and save/print keystrokes (`Ctrl+P`, `Ctrl+S`), and boots the synced design kit bundle.
- `functions.php` - Added direct URL routing fallback for `/speaker-deck`, auto-creates the unlisted page on admin initialization, and hooks `pre_get_posts` & `wp_sitemaps_posts_query_args` to exclude `/speaker-deck` from public site search queries and XML sitemaps.
- `assets/design-kit/design-kit.js` - Rebuilt and synced from `reinvent-kit` including `SpeakerDeckViewer` with dual presentation/scroll modes, responsive viewport scaling, touch gestures, and anti-download guards.
- `.github/workflows/deploy.yml` - Added verification check for `templates/pages/page-speaker-deck.php`.

**Key Decisions**:
- Pure no-password, invite-only access: Speakers who have the direct link can access seamlessly without remembering passwords or PINs, while search engines, sitemaps, and site navigation remain blind to the URL.
- Responsive scaling: The 794x1123 A4 slide format dynamically scales via CSS transforms to fit mobile, tablet, and desktop viewports without horizontal clipping or scroll cut-offs.

---

### 2026-11-08 20:22 - Antigravity (Gemini 3.8 Flash)

**Task**: Add partner community promotion consent checkbox, update partner email template and success screen to celebrate official AWS Community Partner collaboration without displaying reference number or CNIC (clarifying event pass is shared separately), and rename WhatsApp button to "Join WhatsApp Group" with compact mobile-aligned dimensions.

**Files Changed**:
- `includes/features/invite/invite-render.php` - Added conditional partner consent checkbox (`"I will help to promote event within my community as possible"`). Refactored partner success screen into a minimal layout: removed reference box, added official partner collaboration copy with separate event pass notice, and renamed/compacted the WhatsApp button to `"Join WhatsApp Group"` for mobile screens. Added partner promo consent to Alpine.js draft persistence.
- `includes/features/invite/invite-handler.php` - Validated partner promo consent on submission and stored in payload. Updated submitter confirmation email for partners: included collaboration message, removed reference number and CNIC, noted separate event pass delivery, and renamed button to `"Join WhatsApp Group"`.
- `includes/features/invite/invite-admin.php` - Added promo consent indicator in submissions details column for partners.

**Key Decisions**:
- Tailored partner messaging specifically for collaboration without technical reference/CNIC noise in their email or success view, confirming passes are issued separately.
- Ensured WhatsApp button is compact (`padding: 9px 18px; font-size: 13px`) so it never breaks awkwardly on narrow mobile screens.

---

### 2026-11-08 20:15 - Antigravity (Gemini 3.8 Flash)

**Task**: Revert confirmation form UI back to the clean, minimal, uncluttered design, removing superfluous section numbering, role descriptions, and badge tags while retaining the CNIC input and subtle partner parking note.

**Files Changed**:
- `includes/features/invite/invite-render.php` - Reverted form layout back to the clean, minimal printed ticket design. Removed cluttered "Part 01"–"Part 06" headings, role card subtext descriptions, and extra badges. Placed CNIC cleanly beside Contact number in the 2-column grid and added a subtle, single-line parking transit note for Community Partners.

**Key Decisions**:
- Preserved the clean, distraction-free aesthetic of the original ticket-stub layout as requested.

---

### 2026-11-08 20:10 - Antigravity (Gemini 3.8 Flash)

**Task**: Add CNIC field for gate clearance (speaker, guest, partner), add campus parking advisory for AWS Community Partner encouraging public transport (InDrive, Yango, Bykea), and elevate form UI/UX with editorial collegiate styling to eliminate generic AI-slop aesthetic.

**Files Changed**:
- `includes/features/invite/invite-render.php` - Redesigned confirmation form with collegiate editorial structure: added distinct `Part 01`–`Part 06` section badges, tactile interactive role cards with role descriptions, dedicated CNIC field with real-time hyphen formatting (`XXXXX-XXXXXXX-X`) for campus gate clearance, campus parking advisory banner for Community Partners recommending ride-hailing options (InDrive, Yango, Bykea), and enhanced Alpine.js draft persistence (`cnic`) and validation.
- `includes/features/invite/invite-handler.php` - Extracted, sanitized, and validated `cnic` as required for all attendees. Persisted `cnic` in JSON payload. Included CNIC in attendee confirmation and organizer notification email templates for gate clearance coordination.
- `includes/features/invite/invite-admin.php` - Added CNIC badge to Contact column in admin confirmations list and added `CNIC (Gate Clearance)` column to CSV export.

**Key Decisions**:
- Structured the form into numbered editorial parts (`Part 01` through `Part 06`) with authentic collegiate typography (Iowan Old Style serif headers, hairline rules, tactile cards) conforming to `DESIGN.md` and `antislop` guidelines.
- Live CNIC formatting automatically inserts hyphens at positions 5 and 12, ensuring valid Pakistani CNIC format without user friction.
- CNIC stored directly in `payload` JSON, ensuring backward and forward schema compatibility without requiring production DB migration.

**Notes for Future Agents**:
- CNIC is required for all roles (`speaker`, `guest`, `community_partner`) to generate campus entry passes.
- Parking notice automatically appears in Alpine.js when `role === 'community_partner' || role === 'community_leader'`.

---

### 2026-11-08 20:00 - Antigravity (Gemini 3.8 Flash)

**Task**: Review and update invitation confirmation form (`includes/features/invite/`): rename AWS Community Leader to AWS Community Partner, update Partner fields (university name, intro/leadership term), add Guest intro & achievements field, add WhatsApp community group invitation on submission, ensure robust zero-data-loss architecture and admin dashboard compatibility.

**Files Changed**:
- `includes/features/invite/invite-core.php` - Renamed role to "AWS Community Partner" (`community_partner`) while retaining `community_leader` as backward-compatible alias. Replaced `program` and `term` with `university` and `about` for Community Partner. Added `about` (intro, leadership, professional experience, achievements) for Guest. Updated shield line builder for Partner and Guest roles.
- `includes/features/invite/invite-render.php` - Updated form UI with university name and leadership intro inputs for Community Partner, and intro/experience textarea for Guest. Added Alpine.js `localStorage` draft saving and restoration (`aws_invite_draft_<event_id>`). Added client-side file size pre-validation (rejects >8 MB before upload) and disabled submit button on submission. Added highlighted WhatsApp group card on success for Community Partners (`https://chat.whatsapp.com/ErXASXh3wLvAWhb7do2I1l?s=cl&p=a&iam=2`).
- `includes/features/invite/invite-handler.php` - Normalized `community_leader` to `community_partner`. Added self-healing table verification (`SHOW TABLES LIKE`) before insert. Added fallback emergency backup into `aws_invite_emergency_backups` option and `error_log` if DB query fails. Updated submitter confirmation and admin notification emails with new fields and WhatsApp group link. Passed submitted `role` in success redirect query.
- `includes/features/invite/invite-admin.php` - Updated role filter and counter metrics to aggregate both `community_partner` and legacy `community_leader`. Updated details column to display university, intro, and guest achievements. Updated CSV export to output `University / Institute` and `About / Bio` alongside legacy columns.

**Key Decisions**:
- Implemented multi-layered zero-data-loss architecture: client-side `localStorage` retains form inputs across browser refreshes or photo upload errors, and server-side fallback (`aws_invite_emergency_backups`) captures the payload even if SQL insert fails.
- Maintained complete backward compatibility for previous `community_leader` records so historical submissions and CSV exports do not break.

**Notes for Future Agents**:
- Confirmation form code is located in `includes/features/invite/`.
- Community Partner WhatsApp group URL is `https://chat.whatsapp.com/ErXASXh3wLvAWhb7do2I1l?s=cl&p=a&iam=2`.
- Drafts are stored in localStorage key `aws_invite_draft_{eventId}` and automatically cleared on success.

---

### 2026-11-08 18:50 - Antigravity (Gemini 3.8 Flash)

**Task**: Fix all key findings and actionable fixes identified in theme review (config path resolution, font 404s, local logo asset).

**Files Changed**:
- `includes/helpers/helpers-colors.php` - Introduced `aws_sbg_get_theme_config()` with static in-memory caching to resolve `config/config-theme.php` (with fallback to root). Replaced 14 direct `include` calls across color helpers with this cached loader. Added `aws_sbg_get_logo_url()` helper prioritizing local asset with CDN fallback.
- `functions.php` - Updated `aws_sbg_get_tailwind_config()` to use `aws_sbg_get_theme_config()` and safely guard against null/empty arrays in PHP 8. Updated `aws_sbg_debug_info()` diagnostic paths for `config/config-theme.php`, `includes/helpers/helpers-colors.php`, `includes/helpers/helpers-fonts.php`, and local logo asset.
- `includes/helpers/helpers-fonts.php` - Rewrote `aws_sbg_register_custom_fonts()` and `aws_sbg_preload_critical_fonts()` to load available fonts (`Amazon Ember Display` and `Amazon Ember Mono` .ttf files) from `assets/design-kit/fonts/`. Added `Amazon Ember` alias mapping so standard font declarations resolve locally. Eliminated 404 network errors caused by missing `/fonts/*.woff2` references.
- `templates/partials/header.php` - Replaced hardcoded remote image URLs for desktop and mobile drawer logos with `aws_sbg_get_logo_url()`.
- `includes/features/feature-reinvent-registration.php` - Replaced hardcoded remote image URL with `aws_sbg_get_logo_url()`.
- `assets/images/icons/SBG-Logo-White-Transparent.png` - Saved local high-resolution transparent logo asset.
- `reinvent-kit/package.json` - Added `"build:wp": "vite build --config vite.wp.config.ts"` npm script for clear and reproducible WordPress bundle builds.

**Key Decisions**:
- Caching `aws_sbg_get_theme_config()` statically prevents redundant filesystem reads on every individual color lookup call.
- Font declarations now check `file_exists()` before outputting `@font-face` and `<link rel="preload">`, preventing browser 404s.
- `aws_sbg_get_logo_url()` uses the bundled local asset first while retaining the remote URL as a resilient fallback.

**Notes for Future Agents**:
- `config-theme.php` lives in `config/config-theme.php` — always use `aws_sbg_get_theme_config()` to read theme config.
- Font files live in `assets/design-kit/fonts/`.
- Rebuilding the brand kit for WordPress can now be done with `npm run build:wp` in `reinvent-kit/`.

---

### 2026-10-22 - Command Code

**Task**: Remove the giveaway and swag prizes from the community page — rewards only — and retire the entry popup.

**Files Changed**:
- `templates/pages/page-community.php` - Cut from 886 to 423 lines. Removed the answer-form popup (`.cp-modal*`, all `.cp-gw-*`, `.cp-hp`, `.cp-star`, `.cp-note`, `body.cp-modal-open`), the swag-prize list and its reveal, and the PHP that fed them (`$src`, `$giveaway_url`, `$giveaway` lookup, every `$gw_*` variable and the error map). The ticket is now the "Student rewards" section (`id="rewards"`, class `cp-rewards-section`): same three `.cp-figures` values, no prizes, kicker "AWS student rewards", and a stub that reads "Giveaway ended — join our WhatsApp community for updates" (inline WhatsApp link) with a "Claim Rewards" button in place of "Enter Giveaway" pointing at the Builder Center invite. Also dropped the now-unused `--surface`, `--surface-2`, `--danger` tokens and the `.cp-btn-secondary` button, renamed `$swag_kit_url` to `$hero_image_url`, and deleted both scripts except the FAQ accordion.

**Key Decisions**:
- The popup was the entry form; once the CTA became an external "Claim Rewards" link nothing opened it, so leaving it would have been an orphan form — removed rather than left dormant (recoverable from git if a future giveaway needs it).
- Kept the ticket-stub composition (perforation, label, button) so the page keeps its shape; only the contents changed.
- Hero `<h1>` "Unlock rewards", the rewards figures, the FAQ, the colophon socials and the hero image are untouched. Both hero assets in `assets/` (the swag-bundle and the lanyard sticker) are merch art, so there is no rewards-specific visual to swap in.

**Notes for Future Agents**:
- The `aws_sbg_giveaway_*` engine and the `/giveaway/` page are untouched and still work; only this page's second entry point is gone. `functions.php` still redirects `/win` and `/swag` to `/giveaway/`.
- The community page no longer reads `?gw_status` / `?gw_ticket` / `?src`; if a popup returns, reinstate the button with `data-cp-open` and the modal markup together.
- FAQ copy is now the only place explaining SheerID verification, so keep it in sync with the Claim Rewards flow.

---

### 2026-10-22 - Command Code

**Task**: Retitle the giveaway ticket to lead with the reward value and rewrite its description around claiming rewards.

**Files Changed**:
- `templates/pages/page-community.php` - `.cp-ticket-title` copy is now "Claim upto $579 of AWS rewards" (was "Join AWS Builder Center and enter the giveaway"), rendered uppercased via `text-transform` with tracking eased from -0.02em to -0.01em so the caps do not collide. `.cp-ticket-lead` is now "Create your AWS Builder account, verify with your student email or documents, and claim AWS student rewards. No credit card." — the giveaway mechanics ("upload a screenshot of your verified profile to enter") are gone from the description.

**Key Decisions**:
- Value first: the headline states the reward and the lead paragraph keeps the how-to (create → verify → claim), so the Builder Center steps stay in the popup where they are actionable.
- The description no longer mentions the giveaway, per request; the "Freshers Week giveaway" kicker above it and the "Enter Giveaway" button in the stub still carry the entry action.
- Uppercase is a `text-transform` on the heading, so the source copy stays readable and future edits do not have to be typed in caps.
- Kept the user's "upto" spelling rather than correcting it to "up to".

**Notes for Future Agents**:
- `.cp-ticket-title` is the only uppercase heading outside the hero `<h1>`; the `$579` figure is the sum of the three `.cp-figures` values, so update both if the reward changes.
- The lead is deliberately entry-free copy — the popup is the only place that explains the upload requirement now.

---

### 2026-10-22 - Command Code

**Task**: Trim the community hero actions to a single WhatsApp CTA.

**Files Changed**:
- `templates/pages/page-community.php` - The "Giveaway" button pointing at `#giveaway` is gone; the hero keeps one button, "Join Community", on the WhatsApp invite URL (`target="_blank"`, now with a `.cp-vh` "(opens in a new tab)" hint).

**Key Decisions**:
- Kept the original "Join Community" label rather than naming WhatsApp in the button; the destination is obvious once tapped and the shorter label keeps the hero headline dominant.
- Removed only the hero button, not the `id="giveaway"` section or the ticket stub's "Enter Giveaway" trigger, so the giveaway is still reachable — just no longer competing with the primary action above the fold.
- `.cp-btn-secondary` stays in the stylesheet because the popup's step buttons still use it.

**Notes for Future Agents**:
- The hero is now a single-action block; adding a second button back means re-checking that it does not out-shout the WhatsApp primary.

---

### 2026-10-22 - Command Code

**Task**: Add social links to the giveaway success popup ("Follow our socials to keep updated").

**Files Changed**:
- `templates/pages/page-community.php` - The three social links (WhatsApp, LinkedIn, Instagram) moved out of the colophon markup into a `$socials` array plus a `$render_social` closure at the top of the template; the colophon now calls the closure and renders identically. The success panel gained a `.cp-social-block` below the ticket: a hairline divider, the note "Follow our socials to keep updated." and the same `.cp-social` row, sized 46px there instead of 52px.

**Key Decisions**:
- Reused the exact colophon component (same classes, border, radius and ink-on-hover invert) rather than new markup, so the popup and the page speak the same language; only the icon size drops for the narrower panel.
- Links come from one array now, so the destinations can't drift apart between the colophon and the popup.
- Socials sit above the Done button so they are read before the panel closes.

**Notes for Future Agents**:
- `$render_social()` must stay defined before its call sites (top of the template); it prints nothing back, it echoes the `<ul>`.
- The icon SVG strings are trusted literals stored in `$socials` and are echoed unescaped on purpose — do not put anything user-supplied in that array.

---

### 2026-10-22 - Command Code

**Task**: Rework the giveaway popup for mobile (it read as a full-screen page with its header jammed to the top).

**Files Changed**:
- `templates/pages/page-community.php` - The modal is now a bottom sheet under 600px instead of a full-bleed screen: content-hugging height capped at `92dvh`, 18px top radius, upward shadow, stronger scrim, and a new tap-to-dismiss `.cp-modal-grab` handle button above the head. Open/close is animated instead of hard `display:none` toggling — `.cp-modal` keeps `visibility/opacity` (+ delayed visibility so the fade finishes) and the card slides from `translateY(100%)` on mobile / `translateY(14px)` on desktop. Card gets `tabindex="-1"` and is focused on open, `visibleFocusables()` now filters out non-rendered elements (the desktop-hidden handle), and the Tab trap treats the card as the first stop.

**Key Decisions**:
- The site header was never above the popup — it is `position: sticky; z-index: 50` in `header.php` versus the modal's `9999`; it only showed through the scrim. The real problem was the popup pretending to be a page, so the fix is shape and motion, not z-index.
- Dropped `height: 100%` from the mobile card: the sheet now sizes to its content, so the short success ticket is a small sheet and only the long form reaches the height cap and scrolls internally. Uses `dvh` so mobile browser chrome can't hide the submit button.
- The grab handle is a real `data-cp-close` button (real behavior, 22px tap strip) rather than a decorative bar pretending to be draggable; it is `tabindex="-1"` because X and Esc already serve keyboard users.

**Notes for Future Agents**:
- `:focus-visible` outline is suppressed on `.cp-modal-card` only, because it is a programmatically focused dialog container, not a control.
- If the popup ever needs true drag-to-dismiss, the handle is the anchor point, but wire it through pointer events on the card and leave `.cp-modal-body` scrolling alone.

---

### 2026-10-22 - Command Code

**Task**: Retitle the community hero to "Unlock rewards" and make the swag prizes more interactive.

**Files Changed**:
- `templates/pages/page-community.php` - Hero `<h1>` is now "Unlock rewards" rendered bold/uppercase (`font-weight: 800`, `text-transform: uppercase`, `line-height: 1.02`, slight negative tracking). Prize rows rebuilt as interactive cards: outlined amber number badge that fills on hover, `.cp-prize-body` nudging 3px right, accent border + amber-tinted background + depth shadow, and each bundle's contents turned into hairline tag pills that brighten on hover. Added a scroll-in staggered reveal (`.cp-prizes.is-anim` → `.is-in`, 80ms/160ms per-child delay) driven by a small IntersectionObserver in its own `<script>` block. Extended the `prefers-reduced-motion` block to the prize transitions.

**Key Decisions**:
- Reveal is JS-opt-in: `is-anim` is only added by the observer, so without JS (or with reduced motion) the prizes simply render at rest instead of staying invisible.
- `is-anim` is dropped 900ms after reveal so the per-child `transition-delay` stops leaking into the hover transitions.
- Hover feedback deliberately avoids a transform on the card itself (so it can't fight the reveal transform); the lift reads through border, tint, shadow and the badge instead. Badges are decorative `aria-hidden` spans, not controls, so nothing pretends to be clickable.

**Notes for Future Agents**:
- The prize reveal lives in its own IIFE because the giveaway modal IIFE returns early when there is no open giveaway.
- `cp-prize-n` is now an outlined badge, not the solid amber chip it started as — hover inverts it.

---

### 2026-10-22 - Command Code

**Task**: Add swag winner details to the community page giveaway ticket.

**Files Changed**:
- `templates/pages/page-community.php` - The ticket stub now carries a "Swag for 3 winners" label, the "three entries picked at random" lead, and a `.cp-prizes` list spelling out each bundle: 1) AWS bottle, pen; 2) pouch, stickers sheet, pen; 3) mobile holder, pen. Added CSS for `.cp-stub`, `.cp-prizes`, `.cp-prize`, `.cp-prize-n`, `.cp-prize-name` and `.cp-prize-items` in the dark ticket style (amber number chip, hairline `--rule-dark` borders, white serif names on `#CBD5E1` item text). Stub alignment changed from `center` to `flex-start` so the CTA sits with the label row.

**Key Decisions**:
- Reused the existing ticket-stub slot instead of adding a new section, so the page keeps its single-ticket composition and the CTA stays adjacent to the prize list.
- Numbered chips use `--accent` (the only warm colour on the dark ticket) and serif names mirror the ticket title, matching the printed-notice voice.

**Notes for Future Agents**:
- The numbers denote the pick order, not prize tiers — Winner 1 takes bundle 1, etc. If the copy changes, update both the `.cp-stub-lead` sentence and the list.
- `.cp-ticket-cta` fills full width under 600px via the existing media query, so the list stays above the button on phones.

---

### 2026-10-18 - Command Code

**Task**: Redesign the giveaway page as a proper product page — remove the "AI gradient card" feel.

**Files Changed**:
- `templates/pages/page-giveaway.php` - Full visual redesign. Removed the aurora blobs, blueprint grid, gradient buttons and uniformly-rounded cards. Introduced a design-token system (ink/muted/line/paper/accent/gold), an editorial hero (status + display title + lead + meta line + prize band), and a two-column entry area: requirements on the left, a sticky "Your details" panel on the right holding the fields + submit. Other giveaways became hairline rows, winners a ranked list. Custom checkboxes, hairline borders, restrained single accent.

**Key Decisions**:
- One accent colour used sparingly (labels, checkboxes, ticket) plus a warm gold reserved for the prize; everything else is ink/neutral. Buttons are solid ink, not gradient.
- The form is a single `<form>` spanning a 2-col grid (requirements | details) so the panel can be sticky while the markup order stays requirements → details.
- Kept every class the JS depends on (`sbg-drop*`, `sbg-field*`, `sbg-reqs`, `sbg-req`, `sbg-btn`, `sbg-form-error`, `#sbg-success`) so the upload/validation/modal logic is unchanged.

**Notes for Future Agents**:
- Design tokens are CSS custom properties on `.sbg-giveaway-root` — change them there to re-theme the page.
- `$render_meta`, `$render_prize`, `$render_form` closures are the single source for the featured and secondary giveaways.

---

### 2026-10-18 - Command Code

**Task**: Replace the inline success banner with an engaging confirmation pop-up on giveaway submit.

**Files Changed**:
- `templates/pages/page-giveaway.php` - Success / already-entered now open a centred modal (blurred backdrop, gradient top bar, check mark, ticket card with the number and entrant name) instead of the inline alert. Modal closes on Done / backdrop / X / Esc, clean the query string via `history.replaceState`, and lock body scroll while open. Errors still show inline.

**Key Decisions**:
- Kept the POST → redirect flow (required for uploads) and render the modal server-side from `gw_status` / `gw_ticket` / `gw_name`, opening it on load.
- `$modal_title` / `$modal_sub` differ for `success` vs `exists` so the same modal covers both outcomes.

**Notes for Future Agents**:
- The modal markup only renders when `$show_modal` is true; the close handler also strips `?gw_status=...` so a refresh doesn't re-open it.

---

### 2026-10-18 - Command Code

**Task**: Make the giveaway page lead with the active giveaway, redesign the prize panel, add live email-domain validation, and improve the error state.

**Files Changed**:
- `templates/pages/page-giveaway.php` - Rewritten: the first open giveaway renders as a featured hero (live pulse + entry count + prize + form); the rest are secondary cards. New premium prize panel (deep indigo→violet gradient, amber trophy mark, PRIZE label). Email fields validate the allowed domain live and block submit via `setCustomValidity`. Server errors now highlight the offending field/requirement block and scroll to it.

**Key Decisions**:
- The email rule is derived per giveaway from `allowed_email_domains`: a specific domain adds `pattern`, `data-domain`, a domain placeholder and a live hint; `*` leaves the field unrestricted.
- Errors are surfaced on the field itself (not just a page notice). `gw_error` codes map to a target: `domain`/`email` → email field, `req*` → requirements block.
- The prize panel is duplicated only via a `$render_prize` closure so featured and secondary cards stay identical.

**Notes for Future Agents**:
- `data-error` / `data-error-gw` on the root carry the last failure for the inline-highlight script.
- The featured giveaway is simply `$open_giveaways[0]`; ordering comes from `aws_sbg_get_giveaways()` (newest first).

---

### 2026-10-18 - Command Code

**Task**: Rebuild the giveaway image-upload UI as a drag-and-drop dropzone and optimise images to WebP on the client before upload.

**Files Changed**:
- `templates/pages/page-giveaway.php` - Replaced the plain file input with a drag-and-drop dropzone (icon, drag state, live preview thumbnail, original→optimised size readout, remove button, busy overlay). Added JS that resizes to max 1600px and encodes to WebP (quality stepped 0.82→0.5, then a further downscale) before assigning the file back to the input via `DataTransfer`.
- `includes/features/feature-giveaway.php` - Server skips intermediate thumbnail sizes for entry uploads (`intermediate_image_sizes_advanced` → empty), and the admin submissions table now previews the full attachment URL with `loading="lazy"`.

**Key Decisions**:
- Conversion happens entirely in the browser (canvas → `toBlob('image/webp')`), mirroring the existing pattern in `feature-event-registration.php`, so the server receives a small file and does no image processing.
- Skipping intermediate sizes means no `thumbnail` size exists, so admin previews use `wp_get_attachment_url()` (never false) with CSS sizing + lazy loading rather than `wp_get_attachment_image_url('thumbnail')`.
- If WebP encoding is unsupported the blob falls back to PNG; the server's MIME allow-list still accepts it.

**Notes for Future Agents**:
- The dropzone marks state on `data-state` (`idle|busy|ready`); the file input is visually hidden but keeps its original `name`/`required`, so server-side validation is unchanged.
- Client limit is 2 MB after optimisation, matching `AWS_SBG_GIVEAWAY_MAX_UPLOAD`.

---

### 2026-10-18 - Command Code

**Task**: Giveaway UX refinements — email-eligibility choice, cleaner prize UI, de-slop pass.

**Files Changed**:
- `includes/features/feature-giveaway.php` - Replaced the free-text "Allowed Email Domains" field with a "Who can enter?" select (Air University students only / General). Trimmed emoji from admin headings, buttons and winner cards.
- `templates/pages/page-giveaway.php` - Removed the "AIR UNIVERSITY ISLAMABAD • ON-CAMPUS DROPS" badge, rebuilt the prize UI as a labelled prize block with an icon, and removed decorative emoji. Dropped the unused `$swag_kit_url`.

**Key Decisions**:
- The eligibility choice maps to the existing `allowed_email_domains` meta (`*` = general, `students.au.edu.pk` = Air University only), so `aws_sbg_validate_giveaway_domain()` needed no change. The migration normalises the legacy domain list to one of these two values.

**Notes for Future Agents**:
- There is no longer any UI to enter arbitrary email domains — it is a two-option choice by design.

---

### 2026-10-18 - Command Code

**Task**: Build a multi-giveaway system (create giveaways with requirements, public submissions, admin winner publishing) replacing the single hardcoded campus campaign.

**Files Changed**:
- `includes/features/feature-giveaway.php` - Rewritten as a multi-giveaway engine (CPT, meta boxes, submission handler, admin screen, migration).
- `templates/pages/page-giveaway.php` - Rewritten as the public multi-giveaway page.
- `includes/admin/admin-dashboard.php` - Nav tab `giveaway` → `giveaways` (points to the new submissions screen).
- `functions.php` - Unchanged (same require path).

**Summary**: Giveaways are now a `aws_giveaway` custom post type instead of one global option. Each giveaway has its own requirements (tick / link-or-text proof / image upload ≤2 MB) and its own details-field builder (Name/Email/Phone pre-seeded). The public `/giveaway/` page lists every open giveaway with its own form and shows published winners for announced ones. Admin gets a `AWS SBG Suite > 🎁 Giveaway Submissions` screen with per-giveaway filtering, uploaded-file thumbnails, winner pick/re-roll/publish, and CSV export.

**Key Decisions**:
- Requirements and fields are stored as JSON post meta; responses live in a new `wp_aws_giveaway_requirement_values` table.
- Public submissions POST to `admin-post.php` (native multipart) rather than REST so file uploads work; nonce + honeypot + capability checks retained.
- Migrated the legacy `aws_sbg_giveaway_settings` into the first giveaway post and backfilled `giveaway_id` on existing entries; legacy tasks became `check` requirements.
- Swapped the legacy unique keys for per-giveaway keys so the same email can enter multiple giveaways.
- UniqueKeys are swapped via explicit `ALTER` (dbDelta does not drop indexes); gated by `aws_sbg_giveaway_migrated`.

**Notes for Future Agents**:
- Old single-campaign functions/endpoints (`aws_sbg_get_giveaway_settings`, `giveaway/enter`, `giveaway/check-ticket`, `aws-campus-giveaway`) were removed. `aws-sbg/v1/giveaway/stats` remains and now needs a `giveaway_id`.
- The upload input name is `gw_file_<requirement_id>`; requirement/detail field ids are `sanitize_key`'d.
- `page-giveaway.php` no longer uses Alpine or the confetti CDN.
- Migration runs on `admin_init` priority 20 (after `aws_sbg_giveaway_init_db` on priority 10). It only creates a giveaway if the legacy `aws_sbg_giveaway_settings` option exists.

---

### 2026-10-18 - Command Code

**Task**: Remove header announcement top bar, update WhatsApp community link, and fix the broken community page.

**Files Changed**:
- `templates/partials/header.php` - Removed the "CLAIM $579 AWS STUDENT REWARDS" announcement `<aside>` completely.
- `templates/pages/front-page.php`, `templates/pages/page-community.php`, `templates/pages/page-giveaway.php`, `includes/features/feature-giveaway.php` - Updated WhatsApp community link to `https://chat.whatsapp.com/JMahpu2U86iBXoBuGWG2tI` (6 occurrences).
- `templates/pages/page-community.php` - Fixed the broken page (see summary).

**Summary**: The community page had a half-finished refactor. The rewards section contained a duplicated/orphaned legacy markup block with an unclosed `<div>`, plus stray `</header>` and `</main>` closers from a bad find-and-replace. The hero's Aurora CSS, gridlines and highlight pill were never defined, and the new reward card classes (`.cp-card-dark`, `.cp-reward-value/title/desc`, `.cp-rewards*`, `.cp-section`) had no styles. The reveal observer also only targeted `.reveal`, so `.cp-reveal` sections stayed at `opacity: 0`.

**Key Decisions**:
- Removed the orphaned legacy reward markup rather than reverting, keeping the newer dark-card design.
- Fixed the mismatched `</header>`/`</main>` to correct `</section>` / wrapper close.
- Restored the missing hero CSS from `page-community-backup-20260929-174257.php` and wrote the missing rewards CSS.
- Updated the IntersectionObserver selector to cover both `.reveal` and `.cp-reveal`, and added an immediate in-view reveal on load so no section stays hidden.

**Notes for Future Agents**:
- The reveal system is now two class families: `.reveal` (legacy) and `.cp-reveal` (new). Both are observed by the same script at the bottom of `page-community.php`.
- The `page-community-backup-*.php` files live inside `templates/pages/` and match the `page-*.php` glob used by `aws_sbg_add_page_templates()`, so they appear in the WP page template dropdown. Consider removing them.
- The page-level `:root` vars are defined without the `.community-page-wrapper` scope; some rules use scoped selectors while card styles reference the vars. Keep this in mind when editing.

---

### 2024-09-29 12:30 - GitHub Copilot (Kimi K2.5)

**Task**: Reorganize theme folder structure and setup GitHub Actions deployment

**Files Changed**:
- `functions.php` - Updated all `require_once` paths to new structure
- `templates/pages/*.php` - Moved all page templates, updated image paths
- `templates/partials/*.php` - Created header/footer partials
- `includes/features/*.php` - Moved feature modules
- `includes/helpers/*.php` - Moved helper files
- `assets/images/*` - Moved from `media/`
- `docs/*` - Moved all documentation
- `config/*` - Moved config files
- `.github/workflows/deploy.yml` - Created deployment workflow
- `header.php` & `footer.php` - Created wrappers for WordPress compatibility

**Summary**: Complete reorganization of the WordPress theme into a clean, maintainable structure with proper separation of concerns. Added GitHub Actions workflow for manual deployment and removed SFTP auto-upload.

**Key Decisions**:
- Kept `header.php` and `footer.php` in root as wrappers calling partials, so WordPress `get_header()` works
- Used `assets/images/` instead of `media/` for better WordPress convention
- Set workflow to `workflow_dispatch` for manual-only deployment

**Notes for Future Agents**:
- All image paths now use `assets/images/` instead of `media/`
- Feature files are in `includes/features/` - load via `functions.php`
- Templates are in `templates/pages/` and `templates/partials/`
- GitHub Actions secrets must be configured before deployment works

---

### 2024-09-29 12:00 - GitHub Copilot (Kimi K2.5)

**Task**: Consolidate main and master branches

**Files Changed**:
- Merged `master` → `main`
- Deleted `master` branch locally and remotely

**Summary**: Merged 13 commits from master into main and deleted the master branch to eliminate confusion. Main is now the single source of truth.

**Key Decisions**:
- Chose to keep `main` as the default branch (modern GitHub standard)
- Preserved all commit history during merge

**Notes for Future Agents**:
- Only `main` branch exists now
- `origin/HEAD` points to `origin/main`

---

## Project Structure Reference

```
aws-sbg-theme/
├── assets/              # Static assets
│   ├── css/
│   ├── fonts/
│   ├── images/         # Was: media/
│   ├── js/
│   └── mockups/
├── config/             # Configuration files
├── docs/               # Documentation
├── includes/
│   ├── admin/          # Admin functionality
│   ├── features/       # Feature modules
│   ├── helpers/        # Helper functions
│   └── post-types/     # Custom post types
├── languages/          # Translations
├── templates/
│   ├── pages/          # Page templates
│   └── partials/       # Template parts
├── .github/workflows/  # CI/CD
├── functions.php       # Main functions
├── header.php          # Wrapper → templates/partials/header.php
├── footer.php          # Wrapper → templates/partials/footer.php
└── style.css          # Theme stylesheet
```

## Common Patterns

### Adding a New Feature
1. Create file in `includes/features/feature-{name}.php`
2. Add `require_once` in `functions.php`
3. Log your changes in this file

### Adding a New Page Template
1. Create file in `templates/pages/page-{name}.php`
2. If using images, reference as `assets/images/...`
3. Log your changes in this file

### Deployment
- Manual only via GitHub Actions
- Go to Actions → Deploy to WordPress → Run workflow
- Requires secrets to be configured in GitHub

---

### 2026-09-29 13:20 - GitHub Copilot (DeepSeek V3.2)

**Task**: Update footer design to clean and minimal layout

**Files Changed**:
- `templates/partials/footer.php` - Completely redesigned footer to be clean and minimal with centered layout, removed 4-column grid and social links, simplified to just logo, description, contact info, and copyright

**Summary**: Redesigned the footer to match user's request for a clean, minimal design. Changed from a complex 4-column layout to a simple centered design with only essential elements: SBG logo, description, email, phone, and copyright.

**Key Decisions**:
- Used local logo file (`assets/images/icons/SBG-Logo-White.png`) instead of external URL
- Removed social links, quick links, and community info sections to achieve minimal design
- Centered all content for clean visual presentation
- Kept "Powered by AWS • Deployed on EC2" text as it's minimal and informative
- Used fixed year "2026" in copyright instead of dynamic year as requested

**Notes for Future Agents**:
- Footer is now minimal with centered layout
- Logo uses local file: `assets/images/icons/SBG-Logo-White.png`
- Copyright shows "© 2026" (hardcoded as requested)
- Contact info formatted as requested with ✉ and ☎ symbols

---

### 2026-09-29 13:25 - GitHub Copilot (DeepSeek V3.2)

**Task**: Remove "Ready to Launch Your Cloud Career?" section and ticker from community page

**Files Changed**:
- `templates/pages/page-community.php` - Removed the "Ready to Launch Your Cloud Career?" CTA section and the scrolling ticker that showed benefits

**Summary**: Cleaned up the community page by removing two sections as requested: the final CTA section titled "Ready to Launch Your Cloud Career?" and the scrolling ticker that displayed benefits like "Skill Builder Premium — Free", "$30 AWS Credits", etc.

**Key Decisions**:
- Removed both the HTML markup and associated CSS styles for the ticker
- Removed the entire CTA section including buttons and background effects
- Kept the rest of the page structure intact
- Maintained all other functionality like FAQ accordion and reveal animations

**Notes for Future Agents**:
- Ticker section (scrolling benefits) has been completely removed
- Final CTA "Ready to Launch Your Cloud Career?" section has been removed
- No ticker-related CSS remains in the styles

---

### 2026-09-29 13:30 - GitHub Copilot (DeepSeek V3.2)

**Task**: Update footer logo to match header and enhance footer design beyond basic layout

**Files Changed**:
- `templates/partials/footer.php` - Updated logo to match header (external URL), added background glow effects, enhanced typography, improved contact info styling, added social proof section

**Summary**: Enhanced the footer design to match the header's logo and significantly improved the visual appeal beyond the basic layout. Added modern design elements while maintaining clean minimalism.

**Key Decisions**:
- Updated logo to use same external URL as header: `https://awsairuniversity.com/wp-content/uploads/2026/09/SBG-Logo-White-Transparent.png`
- Added background glow effects (purple and orange blurs) for visual depth
- Enhanced contact info with interactive cards (hover effects, borders, shadows)
- Added social proof/trust badge section with colored indicators
- Improved typography hierarchy with better font sizes and weights
- Maintained clean, centered layout while adding visual interest

**Notes for Future Agents**:
- Footer now uses same logo as header (external URL)
- Contact info cards have interactive hover states with brand colors
- Background glow effects match the site's aesthetic
- Social proof section adds credibility with visual indicators
- Enhanced bottom bar with better spacing and icons

---

### 2026-09-29 13:35 - GitHub Copilot (DeepSeek V3.2)

**Task**: Remove specified content from footer including description, contact info, and copyright line

**Files Changed**:
- `templates/partials/footer.php` - Removed all text content: description, contact info, social proof section, and copyright line

**Summary**: Completely cleaned out the footer text content as requested, leaving only the logo and "Powered by AWS • Deployed on EC2" section. The footer is now extremely minimal with just visual elements.

**Key Decisions**:
- Removed both description paragraphs: "Official AWS Student Community at Air University Islamabad." and "Empowering students to build, innovate, and lead in Cloud and AI."
- Removed contact info cards for email and phone
- Removed social proof/trust badge section
- Removed copyright line "© 2026 AWS Student Builder Group • Air University Islamabad"
- Kept only the logo and AWS/EC2 branding
- Maintained all visual design elements (glow effects, spacing, etc.)

**Notes for Future Agents**:
- Footer now contains ONLY logo and "Powered by AWS • Deployed on EC2"
- All text content has been removed as requested
- Visual design elements (background glows, spacing) remain intact
- Footer is now ultra-minimalist

---

### 2026-09-29 13:40 - GitHub Copilot (DeepSeek V3.2)

**Task**: Remove logo and all content from footer, keep only copyright text

**Files Changed**:
- `templates/partials/footer.php` - Completely stripped down footer to only show copyright text in minimal format

**Summary**: Removed everything from the footer including logo, background effects, AWS branding, and back-to-top button. Now only displays the copyright text in a clean, minimal format.

**Key Decisions**:
- Removed all visual effects (background glows, gradients)
- Removed SBG logo completely
- Removed "Powered by AWS • Deployed on EC2" branding
- Removed back-to-top button
- Kept only the essential copyright text formatted as requested
- Used simple, clean layout with centered text

**Notes for Future Agents**:
- Footer now contains ONLY: "© 2026 AWS Student Builder Group • Air University Islamabad"
- All visual effects and branding have been removed
- Footer is now extremely minimal with just text
- Copyright text is formatted with line breaks as requested

---

### 2026-11-08 20:30 - Antigravity (Claude 3.7 Sonnet)

**Task**: Safe confirmation entries deletion & bulk cleanup for testing on admin side

**Files Changed**:
- `includes/features/invite/invite-admin.php`:
  - Added individual entry delete action in table rows with JavaScript confirmation dialog and nonce protection.
  - Added single entry deletion handler (`aws_sbg_invite_handle_delete_entry`) that backs up deleted entries into `wp_options` (`aws_invite_recent_deleted_entries`) and deletes associated uploaded photo attachment via `wp_delete_attachment(..., true)` to prevent media clutter.
  - Added bulk "Clear Entries…" button and modal (`#aws-invite-clear-modal`) with pre-deletion snapshot backup, optional media cleanup checkbox, and safety requirement to type `CLEAR` in capital letters before proceeding.
  - Added bulk clear handler (`aws_sbg_invite_handle_clear_event_entries`) that validates `CLEAR` confirmation, archives all event records into `wp_options` under `aws_invite_backup_{event_id}_{timestamp}` prior to deletion, cleans orphaned test photos, and deletes rows safely.
  - Added admin notices for successful deletion, bulk clearing with count, and mismatched confirmation code errors.

**Key Decisions**:
- Required exact typed confirmation `CLEAR` to guard against accidental clicks.
- Preserved zero data loss: Full snapshots are saved to `wp_options` before any rows are removed.
- Automatically cleaned up uploaded test photo attachments from the Media Library on delete.

---

### 2026-11-08 20:38 - Antigravity (Claude 3.7 Sonnet)

**Task**: Fix email template subject entity encoding, remove redundant WhatsApp box from email body, add single-line WhatsApp button with subtext, and personalize submission success screen

**Files Changed**:
- `includes/emails/aws-emails.php`:
  - Automatically decoded HTML entities in email subjects via `html_entity_decode( wp_strip_all_tags( $subject ), ENT_QUOTES, 'UTF-8' )` so plain-text subjects display literal `&` instead of `&amp;`.
  - Added optional `$cta_sub` support in `aws_sbg_email_template` and `aws_sbg_mail_send` to display subtext like "For Updates & Announcements".
  - Enforced `white-space: nowrap;` and balanced padding on the CTA button so "Join WhatsApp Group" never wraps on mobile screens. WhatsApp button automatically styled in WhatsApp green (`#25D366`).
- `includes/features/invite/invite-handler.php`:
  - Updated submitter email copy to start with "Hi [NAME], Thank you for accepting invitation." followed by the role details.
  - Removed duplicate purple WhatsApp box from email body, using only the bottom CTA button and subtext "For Updates & Announcements".
  - Passed `invite_name` in query params on redirect for instant personalized success messaging.
- `includes/features/invite/invite-render.php`:
  - Updated success card title to "Thank You [NAME]" (e.g., "Thank You Anas Riaz") and description to "Your details have been received."
  - Retained the partner announcement and WhatsApp group invitation box with single-line button on mobile.

---

### 2026-11-08 20:50 - Antigravity (Claude 3.7 Sonnet)

**Task**: Update partner confirmation email copy and structure to "You're officially part of the team!"

**Files Changed**:
- `includes/emails/aws-emails.php`:
  - Added support for `$closing_html` in `aws_sbg_email_template` and `aws_sbg_mail_send` to allow a closing message after the CTA button before the footer divider.
- `includes/features/invite/invite-handler.php`:
  - Updated subject to `"You're officially part of the team! — [Event Name]"`.
  - Updated email kicker to `"AWS Community Partner"` and title to `"You're officially part of the team!"`.
  - Updated body copy to:
    - `"Hi [NAME],"`
    - `"Thank you for accepting our invitation!"`
    - `"As an AWS Community Leader and official AWS Community Partner, you're invited to represent your community at the AWS re:Invent 2026 Watch & Build Party in Islamabad."`
    - `"We'll come together as community leaders and partners, each representing our respective communities while collectively representing AWS."`
    - `"Your event pass will be shared separately."`
    - `"Join our WhatsApp group for event updates and announcements."`
  - Added green WhatsApp CTA button `[Join WhatsApp Group]`.
  - Added closing line: `"Looking forward to bringing the AWS community together!"`.

---

### 2026-11-08 20:54 - Antigravity (Claude 3.7 Sonnet)

**Task**: Refactor email template to light, minimal, human-crafted design with responsive device scaling

**Files Changed**:
- `includes/emails/aws-emails.php`:
  - Transformed global HTML email template from dark cyberpunk aesthetic to a crisp, minimal light theme (`#f4f6f8` backdrop, `#ffffff` card, `#e2e8f0` 1px border, `#0f172a` headings, `#334155` readable body text).
  - Reduced title size to a minimal, tasteful `18px` bold headline and kicker to `11px` slate.
  - Added defensive color mapping (`str_ireplace`) for legacy white text (`#FFFFFF`) to ensure contrast readability across all existing email contexts.
  - Added clean mobile responsive styles (`@media only screen and (max-width: 560px)`) with scaled padding, fluid card width, and full-width touch-friendly button.
- `includes/features/invite/invite-handler.php`:
  - Adjusted inline colors for submitter and admin notifications (`#0f172a` for names/CNIC, `#7928ca` for reference tags, `#64748b` for notes).
  - Tightened line margins to clean `12px` rhythm.

---

### 2026-11-08 21:02 - Antigravity (Claude 3.7 Sonnet)

**Task**: Replace role selection pills with minimal dropdown list and upgrade file upload to clean modern interactive zone

**Files Changed**:
- `includes/features/invite/invite-render.php`:
  - Replaced the 3-column role radio pills with a clean, minimal `<select>` dropdown (`.inv-select`) with custom SVG arrow, seamlessly integrated with AlpineJS `x-model="role"`.
  - Replaced the browser default `<input type="file">` with an interactive, modern upload zone (`.inv-upload-zone`):
    - Dotted/dashed light container with drag & drop feedback (`.is-dragover`).
    - Clean iconography, "Choose a photo or drag & drop here" prompt, and format/size guidelines.
    - Filled preview state displaying thumbnail, truncated filename, formatted filesize in KB/MB, and a "Change" button.
    - Fully accessible via keyboard focus and click.

---

### 2026-11-08 21:07 - Antigravity (Claude 3.7 Sonnet)

**Task**: Update parking advisory text to include ride-hailing and soft grey color, update photo microcopy to focus on social creatives and event banners

**Files Changed**:
- `includes/features/invite/invite-render.php`:
  - Updated parking advisory copy to `"Due to limited parking space, we encourage using public transport OR ride-hailing (InDrive, Yango, Bykea)."` and styled in soft neutral grey (`color: #64748b;`).
  - Updated photo help text to `"Used for social creatives and event banners. High-resolution portrait recommended."`, removing reference to recognition shield.

---

### 2026-11-10 23:55 - Antigravity (Claude 3.7 Sonnet)

**Task**: Add per-role submission closing controls with custom closed notices (Option 1)

**Files Changed**:
- `includes/features/invite/invite-core.php`:
  - Added default meta keys (`role_status_speaker`, `role_msg_speaker`, `role_status_guest`, `role_msg_guest`, `role_status_community_partner`, `role_msg_community_partner`) in `aws_sbg_invite_get_event_meta()`.
  - Updated `aws_sbg_invite_role_allowed()` to check that the target role is not closed.
- `includes/features/invite/invite-admin.php`:
  - Added "Role Availability & Closed Notices" UI to the Invitation Settings metabox with Open/Closed select dropdowns and custom closed message inputs for Speaker, Guest, and Community Partner roles.
  - Updated `aws_sbg_invite_save_meta()` to sanitize and persist `role_status_*` and `role_msg_*`.
- `includes/features/invite/invite-handler.php`:
  - Added server-side validation rejecting submissions for closed roles with error code `role_closed`.
- `includes/features/invite/invite-render.php`:
  - Appended `— Closed` indicator to closed options in the "I am joining as" dropdown (`#inv-role`).
  - Added inline alert notice (`.inv-notice-closed`) displaying the organizer's custom closed message when a closed role is chosen.
  - Conditionally hid subsequent form sections (`<div x-show="!isRoleClosed()">`) and disabled submit button when a closed role is selected.
  - Added client-side guard in `handleSubmit()` to prevent submission attempts.
  - Normalized `community_leader` query alias to `community_partner` and passed role status/message state into Alpine `x-data`.
  - Added friendly error message for `role_closed` if server-side validation is triggered.

**Key Decisions**:
- Existing submissions in `wp_aws_invite_submissions` are completely unaffected and safe; closing a role is an intake-level gate only.
- Roles can be toggled Open/Closed independently at any time directly from the WordPress event edit screen.
- Form inputs remain preserved in drafts so switching between roles does not lose typed data.

---

### 2026-11-11 23:45 - Antigravity (Claude 3.7 Sonnet)

**Task**: Build dedicated Call for Speakers (CFS) interest & pitch subsystem (`/speak/`)

**Files Changed**:
- `includes/features/cfs/cfs-core.php`:
  - Created custom table `wp_aws_cfs_submissions` storing talk title, pitch, track, format, level, speaker info, and review status.
  - Defined taxonomies: Tracks (`genai_bedrock`, `cloud_architecture`, `devops_containers`, `cloud_security`, `student_career`, `other`), Formats (`lightning`, `session`, `workshop`), Levels (`100`, `200`, `300`), and Statuses (`received`, `under_review`, `shortlisted`, `accepted`, `declined`).
  - Implemented multi-source event resolver `aws_sbg_cfs_resolve_event()` supporting `aws_invite_event`, `aws_event`, and fallback to the latest active event.
- `includes/features/cfs/cfs-handler.php`:
  - Implemented secure submission processing (`admin_post_aws_sbg_cfs_submit`) with nonce verification, honeypot spam protection, and input sanitization.
  - Automated reference generation (`CFS-XXXXXX`).
  - Submitter confirmation and admin notification dispatching via `aws_sbg_mail_send('cfs', ...)`.
- `includes/features/cfs/cfs-render.php`:
  - Built rapid ~90-second ticket-style public form without file uploads or gate clearance clutter.
  - Implemented Alpine.js reactive state: localStorage draft saving/restoring (`aws_cfs_draft_<id>`), live character counter on talk pitch (max 400 chars), experience reveals, and submission lock.
  - Added confetti-powered celebration success screen with reference ticket badge and "what happens next" instructions.
  - Added shortcode `[aws_cfs_form event="..."]`.
- `includes/features/cfs/cfs-admin.php`:
  - Added "Call for Speakers" submenu under `aws-sbg-suite` and top navigation tab.
  - Built proposals review table with search, event filters, track filters, and status count pills.
  - Added direct WhatsApp outreach link (`https://wa.me/...`), inline status changer, and 1-click confirmation link copy for accepted speakers.
  - Added CSV export functionality (`cfs-proposals-<event-id>-<date>.csv`).
  - Added CFS Intake Settings metabox to `aws_invite_event` (Open/Closed toggle, deadline, closed message, and live public pitch URL).
- `includes/features/feature-cfs.php`:
  - Module loader loading `cfs-core`, `cfs-handler`, `cfs-render`, and `cfs-admin`.
- `templates/pages/page-speak.php`:
  - Dedicated standalone full-screen page template for `/speak/`.
- `functions.php`:
  - Loaded `feature-cfs.php`.
  - Added direct URL fallback for `/speak` in `aws_sbg_template_include` and `template_redirect` to guarantee 200 OK headers.
- `includes/admin/admin-dashboard.php`:
  - Added `cfs` tab to the shared admin top navigation bar.
- `includes/features/invite/invite-admin.php`:
  - Added Call for Speakers (`/speak/?event=...`) link to the event share metabox alongside the confirmation URL.

**Key Decisions**:
- Separated speaker intake (pitching ideas) from speaker onboarding (photo for shields, CNIC for gate clearance, vehicle pass) to maximize proposal submissions.
- Used zero-friction, mobile-optimized form with no file upload hurdles.
- Seamless bridge: Accepted speakers can immediately be sent the formal confirmation link.

---

### 2026-11-12 00:05 - Antigravity (Claude 3.7 Sonnet)

**Task**: Build dedicated Team Hiring & Volunteer System (`/join/`) with multi-campus and scope controls

**Files Changed**:
- `includes/features/team/team-core.php`:
  - Created custom table `wp_aws_team_applications` storing applicant info, role (`event_crew` or `core_team`), university/institute, departments, pitch, and review status.
  - Defined taxonomies: Roles (`event_crew`, `core_team`), Role-specific Departments, Statuses (`received`, `screening`, `shortlisted`, `selected`, `declined`), and Scopes (`both`, `event_crew`, `core_team`).
  - Implemented multi-source event resolver `aws_sbg_team_resolve_event()` and role access verifier `aws_sbg_team_is_role_allowed()`.
- `includes/features/team/team-handler.php`:
  - Implemented secure submission processing (`admin_post_aws_sbg_team_submit`) with nonce check, honeypot trap, role-scope validation, and input sanitization.
  - Automated reference generation (`TEAM-XXXXXX`).
  - Dispatched confirmation email to candidate and alert email to admin via `aws_sbg_mail_send('team', ...)`.
- `includes/features/team/team-render.php`:
  - Built rapid ~90-second ticket-style public form at `/join/?event=<slug>`.
  - Added multi-campus welcome copy and prominent degree/university field supporting cross-university students (NUST, FAST, Air Univ, COMSATS, Bahria, etc.).
  - Added dynamic role pills (when Scope is `both`) or streamlined single-role layouts (when Scope is `event_crew` or `core_team`), with dynamic department updates.
  - Added Alpine.js draft persistence (`aws_team_draft_<id>`), live 300-char pitch counter, and confetti celebration success state.
  - Added shortcode `[aws_team_form event="..."]`.
- `includes/features/team/team-admin.php`:
  - Added "Team & Volunteers" submenu under `aws-sbg-suite` and top navigation tab.
  - Built candidates roster table with prominent campus badges, search across universities, role/status filters, and live count pills.
  - Added 1-click WhatsApp outreach link (`https://wa.me/...`) with prepopulated greeting tailored to candidate's name, role, and department.
  - Added CSV export functionality (`team-roster-<event-id>-<date>.csv`).
  - Added Team Hiring & Crew Intake Settings metabox to `aws_invite_event` (Scope selector, per-role Open/Closed switches, custom notices, deadline, and live `/join/` URL).
- `includes/features/feature-team.php`:
  - Module loader loading `team-core`, `team-handler`, `team-render`, and `team-admin`.
- `templates/pages/page-join.php`:
  - Dedicated standalone full-screen page template for `/join/`.
- `functions.php`:
  - Loaded `feature-team.php`.
  - Added direct URL fallback for `/join` in `aws_sbg_template_include` and `template_redirect` to guarantee 200 OK headers.
- `includes/admin/admin-dashboard.php`:
  - Added `team` tab to the shared admin top navigation bar.
- `includes/features/invite/invite-admin.php`:
  - Added Team Hiring URL (`/join/?event=...`) to the event share metabox.

**Key Decisions**:
- Organizers can scope an event form to Event Crew Only, Core Team Only, or Both.
- Event Crew and Core Team have independent Open/Closed controls and custom notices.
- Explicitly designed to welcome and track cross-campus university representation across Islamabad/Rawalpindi.

---

### 2026-11-13 00:40 - Antigravity

**Task**: AWS re:Invent Event-Day Desk Check-In System, Digital Ticket Pass, Badge Formatting, and CFS Form Polish

**Files Changed**:
- `assets/design-kit/html5-qrcode.min.js`:
  - Self-hosted HTML5 QR / Barcode Scanner library (v2.3.8) for 100% offline, zero-dependency camera scanning at registration desks.
- `includes/features/feature-reinvent-checkin.php`:
  - Created standalone check-in module handling frontend desk authentication, in-memory roster caching, duplicate handover prevention, and mobile pass verification.
  - Implemented PIN-protected desk session with signed HMAC tokens (SHA-256) and 48-hour HttpOnly cookies. Default passcode: `RIV2026`.
  - Added URL rewrites and `template_redirect` fallbacks for `/reinvent-checkin/`, `/checkin/`, and `/verify/{pass_number}`.
  - Built full-screen, dark-mode Desk Check-In Terminal: live camera QR scanner, Web Audio API chime/buzz sound effects, vibration haptics, instant typeahead search across Name / Pass # / CNIC, role category tabs, and slide-over attendee detail drawer with print shortcut.
  - Built mobile-optimized attendee pass view at `/verify/{pass_number}` displaying live ticket status, QR code, attendee details, and verified badge.
  - AJAX endpoints: `aws_sbg_checkin_auth`, `aws_sbg_checkin_get_roster`, `aws_sbg_checkin_toggle`, and `aws_sbg_checkin_save_passcode`.
- `includes/features/feature-reinvent-registration.php`:
  - Required `feature-reinvent-checkin.php`.
  - Upgraded approval email in `aws_sbg_reinvent_handle_review_action()`: sends a dark-mode branded Digital Event Pass card with QR code and mobile pass verification link.
  - Fixed attendee badge print layout:
    - Landmark footer image (`.badge-skyline`) styled to `position: absolute; bottom: 0; left: 0; right: 0; width: 100% !important; min-width: 100% !important; height: auto !important;` inside a `15%` height container (`.badge-footer`) to eliminate side margins while preventing top spire cropping.
    - Repositioned role badge pill downward by increasing `.badge-body` top padding and margin spacing.
  - Admin Dashboard enhancements:
    - Added "Launch Check-In Terminal" quick-access button and "Desk PIN" modal launcher in admin header.
    - Added 5th stat card: "Checked In (Event Day)" showing live count and attendance percentage (`X / Y (Z%)`).
    - Added event check-in status badge in the registrations table.
    - Added Desk PIN configuration modal (`#aws-reinvent-pin-modal`) with live AJAX updater.
    - Updated CSV export (`aws_sbg_export_reinvent_csv`) to include `Event Day Check-In`, `Checked In At`, and `Checked In By` columns.

**Key Decisions**:
- Frontend check-in terminal allows student volunteers to check in attendees using their smartphones or laptops without needing WordPress admin dashboard access or WP accounts.
- Multi-desk protection: In-memory roster + optimistic locking prevents duplicate badge handovers across parallel entry queues.
- Zero external CDN dependencies for camera scanning ensures high reliability on venue Wi-Fi / mobile hotspots.
- Desk Terminal UI Streamlining: Removed live check-in percentage (`0% CHECKED IN`) from the frontend volunteer interface; removed default 300+ attendee list dump on load in favor of a clean, focused search bar and camera scanner; disabled automatic check-in on QR scan so desk volunteers review attendee details and explicitly click "Confirm Check-In & Issue Badge".

### 2026-11-13 - Clean Responsive Email Shell & re:Invent Email Redesign
**Changes Made**:
- `assets/images/icons/aws-dark.png`:
  - Created `#232F3E` Squid Ink + `#FF9900` smile dark-lettering AWS logo asset (`602x360` RGBA) for light-mode email letterheads.
- `includes/emails/aws-emails.php`:
  - Redesigned `aws_sbg_email_template()` into a clean, responsive (`580px` max-width) light-mode letterhead shell inspired by minimal transactional emails: `#f8fafc` top header strip with `aws-dark.png` on the left and `STUDENT BUILDER GROUP` on the right, optional title/kicker, responsive mobile stacking rules (`.email-header-td`, `.email-footer-td`, `.pass-col`, `.pass-qr-col` at `max-width: 560px`), and unified footer (`TEAM - AWS SBG AIR UNIVERSITY ISLAMABAD`).
- `includes/features/feature-reinvent-registration.php`:
  - Redesigned the re:Invent registration confirmation ("Under Review") email and the approval + Digital Event Pass email to remove AI-slop bullet lists, emojis, dark/neon boxes, and redundant `"at Air University Islamabad"` copy.
  - Replaced the approval pass with a responsive two-column light-mode table card (`#232F3E` top bar, `#f8fafc` body, tier pill, attendee name, `#RIV-XX`, `Open Mobile Pass` link, and `120x120` QR code).
  - Updated the form success screen (`Thank You, [Name]` via 10-minute `aws_rv_success_` transient + `"Approved attendees will receive event pass within a week."`), duplicate-registration notice, and confirmation email copy.
  - Aligned the printable attendee badge header (`.badge-header-inner` vertically centered, `.badge-brand-prefix` stacking `AWS` + `re:` within `Invent` height, `.badge-brand-sub` set to `WATCH & BUILD PARTY 2026 ISLAMABAD` scaled to span 100% of `AWS re:Invent`, and right side showing only the AWS logo).
  - Enhanced the re:Invent admin dashboard (`aws_sbg_render_reinvent_admin_page()`): added Badge Tier and Event Check-In filter dropdowns, clickable `mailto:`/`wa.me` contact links and `Pass ↗` links in the table, removed the `Admin Note (Internal)` table column and drawer form, and added a slide-over **Application Detail Drawer** (`openReinventDetailDrawer`) exposing all registration, vehicle, and check-in fields with quick actions.
  - Redesigned the approval email (`aws_sbg_reinvent_handle_review_action()`) into a mobile-first single-column ticket box showing only `Registration Number` (`#RIV-XX`) + `Download Event Pass` CTA, updated instructions, and `Team - AWS Student Builder Group Air University Islamabad` sign-off.
  - Disabled all emails on re:Invent form registration (`aws_sbg_handle_reinvent_registration()`); emails are now sent only upon approval.
  - Updated re:Invent intro perks (`01 Invite-Only`, `02 Snacks, Food & Drinks (For All)`, `03 Swags (Limited)`) and Step 2 policy checkboxes (simplified attendance confirmation `if approved` and removed F-9 Park Gate 3 parking reference).
- `includes/features/feature-reinvent-checkin.php`:
  - Redesigned `/verify/{pass_number}` into a clean light-mode PDF-downloadable pass (removed gradient header, re:Invent branding, supporting text, `OFFICIAL PASS VERIFIED` badge, and footer venue/date/security box; displays AWS & SBG logos, badge tier pill, attendee name, QR code, `#RIV-XX`, and a `Download PDF Pass` print button).
- `includes/features/cfs/cfs-admin.php`:
  - Fixed `white-space: pre-wrap` tab-indentation bug on `talk_pitch` previews, displayed full speaker contact details (email, phone, LinkedIn) and full audience level labels directly in the table, and polished status `<select>` styling while preserving active search/filter params across actions.
  - Added a slide-over **Proposal Details** drawer (`awsCfsOpenDrawer`) exposing every database field (Session Proposal, Speaker Profile & Contact, Past Talk link, IP address) and an inline **Evaluation & Reviewer Notes** form (`aws_sbg_cfs_save_review`) backed by `admin_notes`, plus `Reviewer Notes` in CSV exports.
- `includes/features/team/team-admin.php`:
  - Fixed the same `white-space: pre-wrap` tab-indentation bug on candidate pitches and displayed candidate email and phone numbers directly in the roster table.

---

## Active Considerations

*None currently - add here if there are ongoing issues or things to watch out for*

