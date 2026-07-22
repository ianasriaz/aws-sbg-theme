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
- **Premium Success Modal**: Removed inline banner and replaced with a full-screen blurred backdrop Alpine.js modal that displays the generated Registration ID using `x-show="open"`.
- **Admin Dashboard Search & Pagination (`admin-dashboard.php`)**: Created a dedicated `Reg #` column, built an integrated search bar using `meta_query` against `registration_id`, and added robust `WP_Query` pagination (50 items/page) to prevent database N+1 crashes.
- **Upload Optimization**: Enforced a 2MB max upload limit on the backend and wrapped `wp_generate_attachment_metadata` with a filter to disable automatic WordPress thumbnail generation to severely cut CPU usage during peak registrations.
- **Double Click Prevention**: Built Alpine.js driven double-click prevention on the main submit button (`@click="isSubmitting = true"`).
