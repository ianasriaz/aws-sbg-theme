# AI Instruction & Feature Log

This document serves as a persistent brain for AI agents working on this project. It tracks shipped features, architectural decisions, and tracking logic.

## 1. Development Priorities
1. Minimum Server Load (Avoid unnecessary API calls, use caching)
2. Robust Data Fetching (Optimistic UI, Sync queues)
3. Instant UI Updates (Client-side first)
4. Clean Architecture (Feature-first structure)
5. Optimized App Experience (Fluid animations, premium UI/UX)

## 2. Server Architecture Constraints
- Ensure all admin dashboard queries use pagination.
- Disable intermediate image size generation for user uploads.
- Enforce strict server-side file size limits.

## 3. Core Implemented Features & Tracking Logic
- **Event Registration Form (`feature-event-registration.php`)**: Fully responsive, uses Alpine.js for interactive state (Free vs Pro tiers).
- **Mobile Header Fix**: Adjusted flexbox logic in `header.php` to prevent the "Join Community" CTA from overflowing off-screen.
- **Top Announcement Bar**: A vibrant brand-purple topbar (`#ad5cff`) injected into `header.php` with an animated pulsating Live dot.
- **Sequential Registration ID Logic**: Replaced standard IDs with sequential pure numbers (offset by 1000) generated automatically during the form submission using `wp_count_posts('aws_event_reg')->publish`.
- **Robust Horizontal Card Layout**: Refactored the ticket selection cards from a side-by-side grid to a stacked full-width horizontal layout on desktop, removing awkward whitespace in the Free tier and smoothly integrating the Earlybird and Covers Food & Venue badges.
- **Premium Success Modal**: Removed inline banner and replaced with a full-screen blurred backdrop Alpine.js modal that dynamically addresses the user by their first name via URL query params and displays the generated Registration ID, accompanied by a zero-server-load CDN-powered confetti burst animation using brand colors.
- **Admin Dashboard & Export (`admin-dashboard.php`)**: Created a dedicated `Reg #` column, built an integrated search bar using `meta_query` against `registration_id`, added robust `WP_Query` pagination (50 items/page), and injected specialized `@media print` CSS to completely strip WP admin chrome and dark themes for a pristine, 5-column PDF export when clicking "Export Data".
- **Upload Optimization**: Enforced a 2MB max upload limit on the backend and wrapped `wp_generate_attachment_metadata` with a filter to disable automatic WordPress thumbnail generation to severely cut CPU usage during peak registrations.
- **Dynamic Submit Button**: Enhanced the Alpine.js form handler to trigger native HTML5 validation on click, animate a shake effect on invalid fields, and dynamically cycle through "Verifying Details..." and "Confirming Spot..." texts before locking the form and submitting to prevent double clicks.
- **AirCert Page Redesign (`page-aircert.php`)**: Completely overhauled the landing page using a dark-theme Tailwind UI. Implemented a 3-step interactive timeline, an overlapping image grid showcasing Swags and the Custom ID Card using `animate-float`, and seamlessly embedded the `[aws_event_form]` shortcode that dynamically binds to the AIRCERT event ID.
- **Pro Tier Pricing Update**: Updated pricing for the pro tier to Rs. 499 (earlybird) and Rs. 799 (regular), and updated the grey badge to "Covers Food & Venue".
- **Form Layout Simplification**: Removed the "Live Custom ID Card Preview" side-by-side component and refactored the PRO checkout section to a centered, single-column layout for better focus on payment instructions.
- **Real-Time Field Validation**: Implemented Alpine.js reactivity to check native HTML5 `$el.checkValidity()` on the `@input` event, triggering a smooth green checkmark icon to scale in when a field is correctly filled out.
- **Client-Side WebP Compression**: Intercepted the file upload `@change` event using Alpine.js and HTML5 Canvas to instantly compress and convert heavy screenshots to highly optimized WebP format *before* form submission, eliminating server-side image processing load.
- **Super Community Supporter Tier (`feature-event-registration.php`)**: Added an accordion VIP option allowing attendees to become a Super Supporter (Rs. 1,999). Integrates seamlessly with Alpine.js state (`isSuperSupporter`) and stores `"super"` in the existing ACF `ticket_tier` field without requiring schema changes.
- **Handcrafted UI Refinement (`feature-event-registration.php`)**: Stripped AI-cliché design elements (excessive blurred radial gradients, generic drop shadows, popping checkmark overlays on input fields). Replaced with a clean, high-contrast, professional design featuring crisp stacked form labels, subtle slate borders (`border-slate-200/80`), refined tier cards, structured payment details table, and an understated dark VIP accordion block.
- **2-Track AirCert Bootcamp Redesign (`page-aircert.php`, `feature-event-registration.php`)**: Overhauled the landing page architecture to distinctly support a 2-Track system. Track 1 features a responsive 3-column grid for Virtual Masterclasses with direct external "RSVP on Meetup" routing (offloading server registration load for free attendees). Track 2 embeds the registration form in an immersive, dedicated section strictly for the In-Person AWS Certification Day in Islamabad. Simplified `[aws_event_form]` by removing the free option, making the PRO tier (In-Person access) the default baseline.
- **AWS Certification Exam Portal & Question Bank (`feature-mock-exam.php`, `page-exam-portal.php`, `seed-foundational-questions.json`)**: Pearson VUE style distraction-free full-screen exam simulator powered by Alpine.js & Tailwind CSS. Includes custom database endpoints, real-time timer, question review flag grid, instant domain breakdown analytics, and authentic CLF-C02 exam seed data.
- **Universal Modular Event Certificate System (`feature-events.php`, `page-certificate-generator.php`)**: Attendee self-service portal (`/claim-certificate/`) backed by custom MySQL tables (`wp_aws_sbg_events` & `wp_aws_sbg_event_issued_certs`) enforcing mandatory survey/referral gates before generating verified certificates.
- **Master Theme Configuration & Helpers (`config-theme.php`, `helpers-colors.php`, `helpers-fonts.php`, `setup-fonts.sh`)**: Centralized site constants, custom post types (`rsvp`, `contact_submission`), ACF fields, 20+ color helper functions (WCAG compliant contrast, `#ad5cff` purple & `#00ef88` neon green), and self-hosted Amazon Ember & Inter font setup.
- **Temporary Registration Closure Toggle**: Implemented site-wide status check logic to temporarily close/disable event registrations and display friendly maintenance state notices.
- **Static Pages & Global UI Components (`front-page.php`, `page-contact.php`, `footer.php`, `singular.php`, `index.php`)**: Custom front page hero layout, interactive contact form handler, responsive global footer with social branding, and fallback single/archive templates.
- **Universal Activity Engine & Custom Perks System (`feature-event-registration.php`, `admin-dashboard.php`, `single-aws_event.php`, `page-events.php`, `archive-aws_event.php`)**: Features 2 dynamic activity modes in WP Admin (`Free` vs `Paid`). Organizers configure event mode, registration status, main post content description, pricing fields, bank details, and a single consolidated perks list included across both Earlybird and Regular passes. Features interactive payment account card with 1-click **Copy Details 📋** button, interactive drag-drop receipt upload with client-side **WebP image optimization** (replaces payload before upload), and a dedicated **Events Portal (`/events/`)**.
- **Mock Exam Refinement & Preparation Guide**: Replaced complex scenario-based CLF-C02 questions with 75 straightforward foundational questions. Automatically defaults to 65 questions per mock exam, removing the dummy limit rule.
- **Event Registrations Admin Table Modification**: Removed the "Check-In" column in the admin dashboard and replaced it with a custom "University / Org" field directly sourced from participant registration metadata.
