## Mission

Build **ZARINALABS**, a web platform that manages IT training for Lions Fort Company. Website only (no mobile app), English only, no payments of any kind (no cash, no credit, nothing). Public users create accounts, verify email, browse courses/events, register for them, and get a confirmation email. Each registration has a QR code used for session check-in at the door. One admin manages everything from a dashboard. Starting scale: ~10 courses, so keep everything simple.

## How you must work

1. Work in the phases listed at the end, in order. When a phase is done: run migrations/build, click through the happy path yourself, give me a summary of max 10 lines, then STOP and wait for me to write "continue". Never jump ahead.
2. Write simple, boring, readable code — like a careful human wrote it, not a code generator. Add comments only where logic is not obvious. Do not over-comment.
3. No over-engineering. No repository pattern, no unnecessary interfaces, no DTO layers, no service classes for one-line logic. Livewire components + Eloquent models + form requests. Extract a small Action class only when logic is reused (e.g. `RegisterUserForCourse`).
4. If something in this spec is unclear, ask me ONE short question. Do not invent features.
5. Never install a composer or npm package that is not listed here without asking me first.
6. Never use emoji anywhere in the UI. Icons must be Heroicons **solid** only, via the `blade-ui-kit/blade-heroicons` package or inline SVG copied from heroicons.com.
7. Never mention social media anywhere. The only external link on the site is `https://lionsfortco.com/` in the footer, as "Managed by Lions Fort".
8. App timezone: `Asia/Baghdad`. Locale: `en` only.

## Tech stack (non-negotiable)

- **Laravel latest stable** (Laravel 13 at the time of writing; install with `laravel new` or `composer create-project laravel/laravel`, confirm the installed version, and use the docs for THAT version — do not write syntax from memory of older versions).
- **PHP latest stable** (8.4/8.5), latest Composer.
- **MySQL 8**. Database name: `zarinalabs`.
- **Livewire v3 latest** for ALL public-site interactivity. No axios, no fetch(), no jQuery, no JS HTTP calls of any kind. The Alpine.js bundled inside Livewire may be used only for tiny UI toggles (mobile menu open/close, etc.).
- **Filament v5** for the admin panel at `/admin`. Filament is built on Livewire, so it respects the rule above. IMPORTANT: Filament changed its APIs across major versions — do not guess Filament v3 syntax from memory. Check the installed version and its docs (https://filamentphp.com/docs/5.x). If Filament v5 does not yet support the installed Laravel version, pin Laravel to the newest version Filament v5 supports and tell me.
- **Tailwind CSS v4 latest**. Tailwind v4 is CSS-first: the theme lives in `resources/css/app.css` using `@theme` — there is no `tailwind.config.js` by default. Configure it that way.
- **Queue**: database driver. All outgoing mail must be queued.
- **QR codes**: `endroid/qr-code` (server-side SVG generation). No JS QR library.
- Do NOT install a starter kit (no Breeze, no Jetstream, no Flux). Auth is hand-built with Livewire (spec below) so the brand stays fully under our control.

## Option lists live in config, never in the database

Create one file per list under `config/options/`, each returning a `'key' => 'Label'` array. Keys are snake_case strings stored in the DB; labels are shown in the UI. Validation always uses `Rule::in(array_keys(config('options.xxx')))`, and every `<select>` loops the same config. No lookup tables, no dynamic option management.

- `config/options/genders.php`: male, female
- `config/options/cities.php`: Iraqi cities only — Baghdad, Erbil, Sulaymaniyah, Duhok, Halabja, Kirkuk, Mosul, Basra, Ramadi, Hilla, Karbala, Najaf, Kut, Amarah, Nasiriyah, Samawah, Diwaniyah, Tikrit, Baqubah
- `config/options/education_levels.php`: high_school, diploma, bachelor, master, phd, professional_certificate, other
- `config/options/it_interests.php`: software_development, networking, cyber_security, artificial_intelligence, cloud_computing, database, ui_ux_design, data_science, other
- `config/options/categories.php`: same values as it_interests (kept as a separate file so course categories can diverge later)
- `config/options/course_types.php`: course, workshop, webinar, event, seminar
- `config/options/levels.php`: beginner, intermediate, advanced
- `config/options/formats.php`: offline, online

## Database schema

Warning: Laravel already ships a `sessions` table (session driver). Course sessions MUST be named `course_sessions` (model `CourseSession`) to avoid the collision.

**users**: name, gender, date_of_birth (date), email (unique), phone, password, city, education_level, it_interest, is_admin (bool, default false), email_verified_at, timestamps

**instructors**: name, photo_path (nullable), bio (text), timestamps

**locations**: name, address, city, timestamps

**courses** (one table for all types): title, slug (unique), description (text), video_url (nullable — YouTube or Vimeo URL only, validated), type, category, level, instructor_id (FK, nullable), format (offline|online), meeting_link (nullable — required when online), location_id (FK, nullable — required when offline), start_date, end_date, capacity (unsigned int), registration_deadline (date, valid through end of that day), is_published (bool, default false — admin needs drafts), timestamps

**course_sessions**: course_id (FK), session_date, start_time, end_time (nullable), location_id (FK, nullable — falls back to the course location), timestamps

**registrations**: user_id (FK), course_id (FK), uuid (unique — used in the QR code), timestamps. Unique index on (user_id, course_id).

**attendances**: registration_id (FK), course_session_id (FK), checked_in_at (datetime), timestamps. Unique index on (registration_id, course_session_id). "Present" = a row exists.

**announcements**: title, body (text), published_at (nullable — null means draft), timestamps

Derived values (never stored): number of sessions = count of course_sessions; seats left = capacity − registrations count.

## Business rules

- A user can register for a course only if ALL are true: logged in, email verified, today ≤ registration_deadline, seats left > 0, not already registered, course is published. Enforce in the backend (form request / action), not just by hiding the button. Wrap the seat check + insert in a DB transaction to avoid overbooking on the last seat.
- Successful registration: create the row with a fresh UUID, then queue the "Registration confirmed" email.
- The online `meeting_link` is shown ONLY to logged-in, registered users (on the course page and inside their confirmation email). Never render it publicly.
- QR check-in flow: the QR encodes a **signed URL** → `URL::signedRoute('admin.check-in', ['registration' => uuid])`. At the door, the admin (logged in on their phone) scans the QR with the normal camera app; the link opens a check-in page protected by `signed` + admin middleware. The page shows the student's name, the course, and today's session(s) preselected — one tap marks present (records checked_in_at). If already checked in, show "Already checked in at HH:MM" instead of a button. If no session is today, allow picking a session manually with a small warning. No JS scanner library — the phone camera does the scanning.
- Course completion is a human decision: the admin looks at the attendance matrix and decides. Do not automate completion, do not generate certificates.

## Auth (public users) — hand-built with Livewire

- Register with exactly: full name, gender, date of birth, email, phone, password (confirmed), city, highest education level, IT interest field. Every dropdown reads from `config/options/*`.
- After registering, send the verification email (Laravel's built-in `MustVerifyEmail`, with the notification mail template rebranded). The user must verify before using the site: login is allowed, but everything except the verify-notice page requires the `verified` middleware.
- Login (with throttle), logout, password reset — all standard Laravel, Livewire UI.
- Profile page `/profile`: shows personal info; user can edit phone, city, education level, IT interest, and change password. Name, gender, date of birth, and email are read-only in v1 (email change would need re-verification — out of scope).

## Public pages

- **Layout**: sticky top nav (logo, Courses, Announcements, Login/Profile), footer with "Managed by Lions Fort" → lionsfortco.com. Mobile burger menu (Alpine toggle).
- **Home `/`**: short hero with the brand statement, latest 3 announcements, next 6 upcoming published courses/events, CTA to browse all.
- **Courses `/courses`**: card grid of published courses/events, paginated. Livewire filters: text search (title), category, city (of the course location), level, and date (show items starting on/after the chosen date; default = upcoming). Filters update the list without page reload. Clear empty state.
- **Course detail `/courses/{slug}`**: title, type + level + category badges, description, embedded intro video if set (YouTube via youtube-nocookie / Vimeo player, parsed from the stored URL), instructor card (photo, name, bio), format, location or "Online", dates, sessions list (date, time, location), seats left, registration deadline, and the Register button. Button states: Register / Log in to register / Verify your email / Full / Deadline passed / You are registered (with link to My Registrations). Meeting link block visible only to registered users.
- **Announcements `/announcements`**: list (title, date, excerpt) → detail page with full body. Only published ones.
- **My Registrations `/my-registrations`**: for each registration — course info, the QR code rendered as SVG, and the user's own attendance per session (present / absent per past session).

## Admin panel — Filament v5 at `/admin`

- Access: only users with `is_admin = true` (implement `FilamentUser::canAccessPanel`).
- Everything is simple lists/tables. No charts, no stats widgets.
- **Courses resource**: full CRUD, with relation managers for Sessions and Registrations. Conditional fields: meeting_link only when format = online, location only when offline. From a course, the admin can open an **Attendance page**: a simple matrix of registered students (rows) × sessions (columns) with present marks, plus the ability to toggle attendance manually. This is how the admin decides who completed the course.
- **Instructors resource**: name, photo upload (public disk), short bio.
- **Locations resource**: name, address, city (config dropdown).
- **Announcements resource**: title, body, published_at.
- **Users**: read-only list (name, email, phone, city, verified, registration count) with a view page. No editing users' personal data.
- **Check-in page**: the signed-URL target described in Business rules — build it as a simple Filament custom page or plain Livewire page under admin middleware.
- Keep Filament close to default styling; just set the primary color to the brand red. Do not fight Filament's design.

## Emails — these two only, nothing else

1. **Verify email** (rebranded Laravel verification notification).
2. **Registration confirmed** — course title, type, dates, sessions summary, location or meeting link, and a link to My Registrations.

Both queued. Simple branded mail layout: ZARINALABS wordmark in red on the paper background, readable body font, footer "Managed by Lions Fort". Mail driver: `log` in local, SMTP via `.env` in production. From name: ZARINALABS. No marketing emails, no reminders, no newsletters.

## Design system

- **Light theme only.** Define in Tailwind v4 `@theme`: `--color-brand: #720A0F` (red), `--color-ink: #173B45` (dark), `--color-paper: #FBF9FA` (off-white). Page background = paper, text = ink, primary buttons/links/accents = brand red (darker shade on hover). Add subtle neutral borders and an ink/70 tone for secondary text.
- **Fonts**: Michroma (Google Fonts) is used ONLY for the logo, page headings (h1/h2), and nav items. All body text, paragraphs, and course/event descriptions use **Inter** — readable, never Michroma. Load both with proper preconnect.
- **Logo**: a reusable Blade component `<x-logo />` containing a placeholder inline SVG — vertical, text-only wordmark "ZARINALABS" (full caps, no space) in brand red, Michroma style. It will be replaced later, so keep it in one component.
- **Style**: flat and smooth. No gradients, minimal shadows, clear borders and whitespace. Border radius: `rounded-lg` / `rounded-xl` everywhere. Never `rounded-full` — not on buttons, not on avatars (instructor photos are rounded squares).
- **Responsive**: mobile-first. Card grids collapse to one column, filters stack, nav becomes burger, admin is Filament (already responsive). Test at 375px, 768px, 1280px.
- **UX**: obvious button states, success flash messages, empty states with a helpful line, disabled Register button always shows the reason. Simplicity wins every time.

## Seed data

- Admin user: `admin@zarinalabs.test` / password `password` (README must say to change it in production).
- 4 instructors (with placeholder photos), 4 locations (Erbil, Sulaymaniyah, Duhok, Baghdad), 10 published courses/events covering all five types and both formats, each with realistic sessions, 3 announcements, ~15 fake verified users with registrations and some attendance rows. Factories for every model.

## Non-goals — do NOT build any of this

Payments, certificates, CV builder, file uploads by users, instructor accounts or approval flows, marketing/reminder emails, multi-language, charts/analytics, extra roles beyond admin/user, social login, comments or reviews, dark mode, public API, mobile app, video hosting (links only).

## Phases

- **Phase 0 — Scaffold**: create the Laravel app, connect MySQL, install Livewire + Filament v5 + blade-heroicons + endroid/qr-code, set up Tailwind v4 with the `@theme` brand tokens and fonts, create all `config/options/*` files, base layout + `<x-logo />` + footer, set timezone, and write a `CLAUDE.md` at the project root summarizing the conventions from this prompt (stack rules, option-config pattern, design tokens, non-goals).
- **Phase 1 — Database**: all migrations, models, relationships, factories, seeders. `migrate:fresh --seed` must run clean.
- **Phase 2 — Auth**: register, login, logout, email verification, password reset, profile page with the allowed edits.
- **Phase 3 — Public site**: home, courses index with filters, course detail with the full registration flow + confirmation email, announcements, My Registrations with QR codes.
- **Phase 4 — Admin**: Filament resources, users list, attendance matrix, QR check-in page end to end (generate QR → scan link → mark present).
- **Phase 5 — Polish & handover**: responsive pass on every page, empty/error states, fix N+1 queries (eager loading), a few Pest feature tests for the money paths (registration rules, duplicate registration blocked, capacity race, signed check-in), and a README covering local setup (macOS) plus Ubuntu VPS deployment: nginx server block pointing to `/public`, PHP-FPM, `.env` production values, `php artisan migrate --force`, `php artisan storage:link`, queue worker as a systemd service, and building assets locally/CI with `npm run build` before deploy (the server does not need Node).
