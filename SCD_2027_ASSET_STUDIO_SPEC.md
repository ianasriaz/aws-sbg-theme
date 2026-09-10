# AWS Student Community Day Islamabad 2027: Creative Asset Studio Specification

**Document Version:** 1.0.0  
**Target Event:** AWS Student Community Day (SCD) Islamabad 2027  
**Host Organization:** AWS Student Builder Group (SBG) at Air University Islamabad  
**Target Platform:** `awsairuniversity.com` (AWS EC2 / CloudPanel, `aws-sbg-theme`, SFTP synced)  
**Reference Mockup:** [`mockups/scd-isb-2027-concept.html`](file:///c:/Users/youra/Pictures/aws-sbg-theme/mockups/scd-isb-2027-concept.html)

---

## 1. Executive Summary & Design System

This document outlines the complete architectural and design specification for the **AWS SCD 2027 Asset Studio**, a lightweight, zero-server-load creative generator inspired by GitHub's Dev Days banner studio. It allows community organizers, speakers, sponsors, and attendees to generate high-resolution, on-brand event assets in seconds.

### Approved Design Tokens & Visual Language
- **Background**: Deep `#0a0d14` obsidian slate with scale-adaptive 60px builder compute grid (`rgba(255, 255, 255, 0.04)`).
- **Sunlit Builder Purple Gradient**:
  `linear-gradient(225deg, #fff8ec 0%, #f9a8d4 18%, #c084fc 42%, #B15CFC 65%, #6a22d6 100%)`
  (Warm champagne sunlight at top-right fading into royal violet at bottom-left).
- **Compute Grid Blocks**:
  - Top-right corner block: Col 5 / Row 1 (1×1).
  - Satellite block: Col 4 / Row 3 (1×1).
  - Main core block: 2×2 block starting at Row 4, Col 2, touching satellite corner-to-corner.
  - Solid fills, **zero outer borders**, **zero drop-shadow glows**.
- **Typography Hierarchy**:
  - **Headlines (`'Space Grotesk', sans-serif`)**: Event name (`AWS STUDENT COMMUNITY DAY ISLAMABAD 2027`), Speaker Name (`Anwaar Hussain`). Geometric sans with distinct technical character.
  - **Body & Labels (`'Inter', sans-serif`)**: Date & Venue metadata rows, announcement badges, talk descriptions, and general card copy.
  - **Data-Style Labels (`'JetBrains Mono', monospace`)**: Tabs, speaker role line (`Cloud Infra. Architect @ AWS`), session card kicker (`SESSION TOPIC`), event title tag, and SBG footer chapter bar.
- **Shared SBG Footer**:
  - Left box: Dedicated `#0b0e14` container with transparent SBG chip logo.
  - Right bar: Solid, non-gradient off-white titanium `#eaeff5` with dark monospace chapter text (`AWS STUDENT BUILDER GROUP at AIR UNIVERSITY ISLAMABAD`).
- **AWS Logo Sizing**: Standardized at **32px height** across all creatives (1:1 Main Banner, 4:5 Speaker Banner, and all future formats) with pure white fill (`filter: brightness(0) invert(1)`).
- **Strict Brand Constraint**: **NO extraneous pseudo-technical micro-stamps** (no artificial `NODE // 01`, `AP-SOUTH-1`, or coordinate crosshairs).

---

## 2. Server Architecture: Zero-Load (0%) Guarantee

The Asset Studio runs entirely within the client's browser using native HTML5 and JavaScript APIs:

| Component | Architecture | Server Impact on AWS EC2 |
| :--- | :--- | :--- |
| **Page Template** | `page-asset-generator.php` in `aws-sbg-theme` | **0% CPU** (Static PHP file, cached by CloudPanel Nginx) |
| **Image Uploads** | HTML5 `FileReader` & `URL.createObjectURL` | **0 MB Disk Storage** (Kept in client RAM, never uploaded to server) |
| **Live Sync** | Reactive DOM mutation on `input` events | **0 Network Requests** (Instant 60 FPS in-browser reactivity) |
| **High-Res Export** | Client-side `html2canvas` at 3x scale | **0 Server Rendering** (Rendered directly by user's GPU/CPU) |
| **Database** | Independent client-side tool | **0 Database Queries** |

---

## 3. Studio UX & Layout (Dev Days Inspired)

### A. Left Sidebar: Control Panel
- **Header**: AWS SBG Logo + "Asset Studio" + "Reset to Defaults".
- **Template Switcher Dropdown**:
  1. `🌟 Main Event Banner` (1:1 Cover — 1080×1080)
  2. `🎙️ Speaker Announcement` (4:5 Social Creative — 1080×1350)
  3. `🤝 Sponsor Announcement` (4:5 / 1:1 — Tier & Partner Logo)
  4. `🌐 Community Partner` (4:5 / 1:1 — Community Badge & Logo)
  5. `🎟️ Attendee Badge ("I'm Attending!")` (4:5 / 1:1 — Headshot & Affiliation)
- **Universal Metadata Accordion** (Available for all formats):
  - Event Line 1: `AWS STUDENT`
  - Event Line 2: `COMMUNITY DAY`
  - Event Highlight: `ISLAMABAD 2027` (all caps, gradient)
  - Date: `March 2027` (with calendar icon)
  - Venue: `Air University E9 Campus` (with location pin icon)
  - Chapter Footer Text: `AWS STUDENT BUILDER GROUP at AIR UNIVERSITY ISLAMABAD`
  - Toggle footer bar visibility.
- **Contextual Accordions** (Auto-displayed based on active template):
  - **Speaker Fields**:
    - Full Name (e.g. `Anwaar Hussain`)
    - Role & Company (e.g. `Cloud Infra. Architect @ AWS`)
    - Session Category (e.g. `SESSION TOPIC`, `KEYNOTE`, `WORKSHOP`)
    - Talk Title (e.g. `"Architecting Autonomous Multi-Agent Workflows on AWS Bedrock"`)
    - Photo Upload: Drag & drop file picker + zoom/pan slider + remove button.
  - **Sponsor Fields**:
    - Sponsor Tier (`Title Sponsor`, `Platinum Sponsor`, `Gold Sponsor`)
    - Company Name & Logo Upload
  - **Community Partner Fields (Architectural Spine Layout)**:
    - Layout: Left 20% artwork spine (pure wireframe 60px grid lines without filled purple blocks, aligned with SBG footer box) + Right 80% clean stage.
    - Partner Community Name (`Space Grotesk`, e.g. `Google Developer Groups`) & Chapter (`JetBrains Mono`, `ISLAMABAD`).
    - Partner Logo: Clean, unboxed centered emblem directly on the dark canvas (no dark background container or box fill).
    - Top Header: Clean announcement badge (`[Icon] COMMUNITY PARTNER` / `[Icon] SPEAKER ANNOUNCEMENT`).
    - Event Title Strip: Prominent, high-visibility `AWS STUDENT COMMUNITY DAY ISLAMABAD 2027` positioned directly above Date & Venue, styled in bold `Space Grotesk` with Sunlit Builder Purple gradient, justified to align flush from start to end of the line.
    - Bottom Row: Clean Date & Venue row (`March 2027` • `Air University E9 Campus`).
  - **Attendee Badge Fields**:
    - Attendee Name & University/Company
    - Headshot Upload
    - Custom Quote ("Excited for Cloud Computing & AI!")
- **Action Footer**:
  - `Download High-Res PNG` (3x high-DPI export with semantic naming)
  - `Copy to Clipboard`

### B. Right Viewport: Canvas Workspace
- Dark `#070a10` studio environment with subtle blueprint grid pattern.
- Centered live canvas preview rendering the pixel-perfect SCD 2027 design.
- Floating viewport toolbar: Zoom In (`+`), Zoom Out (`-`), Fit to Viewport (`Reset Scale`), and Quick Download.

---

## 4. Implementation Checklist (For Future Execution)

When ready to implement, follow these steps:
1. **Create Template**: Add [`page-asset-generator.php`](file:///c:/Users/youra/Pictures/aws-sbg-theme/page-asset-generator.php) to the root of `aws-sbg-theme`.
2. **Assign in WordPress**: In WP Admin (`awsairuniversity.com/wp-admin`), create a page titled `SCD Asset Studio` with slug `/asset-generator/` and assign the template `AWS SCD Asset Studio`.
3. **Verify SFTP Sync**: Ensure the file automatically syncs to the AWS EC2 instance.
4. **Test Exports**: Validate that 1:1, 4:5, and future creative formats download crisp 3x PNGs with custom dates, venues, and photos.
