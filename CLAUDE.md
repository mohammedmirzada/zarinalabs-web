# ZARINALABS — conventions

Web platform managing IT training for Lions Fort. Public users register for courses and
events, get a QR code, and are checked in at the door by the single admin. Website only,
English only, **no payments of any kind**.

## Stack (do not substitute)

| Thing | Version | Notes |
| --- | --- | --- |
| Laravel | 13.19 | Verify syntax against Laravel 13 docs, not memory of older versions. |
| PHP | 8.5 | |
| MySQL | 8.1 | Database `zarinalabs`. Client lives at `/usr/local/mysql-8.1.0-macos13-x86_64/bin/mysql`. |
| Livewire | 4.3 | Spec said v3, but Filament v5 requires `livewire/livewire ^4.1`. v4 it is. Use v4 docs. |
| Filament | 5.6 | Admin panel at `/admin`. Check https://filamentphp.com/docs/5.x — v5 APIs differ from v3. |
| Tailwind | 4 | CSS-first. Theme lives in `resources/css/app.css` under `@theme`. There is no `tailwind.config.js`. |
| QR codes | `endroid/qr-code` 6.1 | Server-side SVG only. |
| Icons | `blade-ui-kit/blade-heroicons` 2.7 | Solid only: `<x-heroicon-s-name />`. |

No starter kit (no Breeze/Jetstream/Flux) — auth is hand-built with Livewire.
**Never install a package that is not listed here without asking first.**

## Hard rules

- **No JS HTTP calls.** No axios, no `fetch()`, no jQuery. All interactivity is Livewire.
  Alpine (bundled with Livewire) is allowed only for tiny UI toggles like the mobile menu.
- **No emoji anywhere in the UI.** Icons are Heroicons solid.
- **No social media links.** The only external link is `https://lionsfortco.com/` in the
  footer, as "Managed by Lions Fort".
- Timezone `Asia/Baghdad`, locale `en` only.
- All outgoing mail is **queued** (database driver). Mail is `log` locally, SMTP in production.
- Only two emails exist: verify email, and registration confirmed. Nothing else, ever.

## Code style

Simple and boring. Livewire components + Eloquent models + form requests. No repository
pattern, no interfaces for one implementation, no DTO layers, no service class wrapping a
single line. Extract a small Action class only when logic is genuinely reused (e.g.
`RegisterUserForCourse`). Comment only where the logic is not obvious.

## Option lists live in config, never in the database

One file per list in `config/options/`, each returning `'snake_case_key' => 'Label'`.
Laravel loads nested config directories, so the key is `options.<file>` — verified to work
under `config:cache` too.

```php
// validation
'city' => ['required', Rule::in(array_keys(config('options.cities')))],

// every <select> loops the same config
@foreach (config('options.cities') as $key => $label)
```

Files: `genders`, `cities` (19 Iraqi cities), `education_levels`, `it_interests`,
`categories` (mirrors `it_interests`, kept separate so it can diverge), `course_types`,
`levels`, `formats`.

Keys are what land in the database. Labels are what the UI shows. No lookup tables.

## Design tokens

Light theme only. Defined in `resources/css/app.css`:

- `--color-brand: #720a0f` (red) — buttons, links, accents. Hover uses `--color-brand-dark: #520709`.
- `--color-ink: #173b45` — text. Secondary text is `text-ink/70`.
- `--color-paper: #fbf9fa` — page background.
- `--color-line: #e6e1e2` — subtle borders.
- `--font-display` (Michroma) — **logo, h1/h2 and nav items only**.
- `--font-sans` (Inter) — everything else. Body copy is never Michroma.

Both fonts load from Google Fonts with preconnect in the layout head.

Flat and smooth: no gradients, minimal shadows, clear borders and whitespace.
Radius is `rounded-lg` / `rounded-xl`. **Never `rounded-full`** — not on buttons, not on
avatars (instructor photos are rounded squares). Mobile-first; test at 375 / 768 / 1280.

Michroma is very wide (about 1.3em per glyph). The desktop nav switches at `lg`, not `md`,
because logo plus five Michroma nav items need roughly 860px. Keep that in mind before adding
a nav item or a long heading.

`resources/views/errors/{403,404,500}.blade.php` keep errors inside the brand.

Filament stays close to its default styling. Only the primary color is overridden, to the
brand red. Do not fight Filament's design.

## Gotchas already hit

- **Never leave a cached config in local dev.** `bootstrap/cache/config.php` is a frozen
  array that ignores the `env()` overrides in `phpunit.xml`, so `database.default` stays
  `mysql` and `RefreshDatabase` runs `migrate:fresh` on the **real** database. This wiped
  the seed once. `tests/TestCase::createApplication()` now refuses to boot if that file
  exists. A cached `routes-v7.php` silently serves stale routes the same way. Something in
  this environment periodically runs `php artisan optimize` — if anything behaves strangely,
  run `php artisan optimize:clear` first.
- Laravel ships a `sessions` table for the session driver. Course sessions are
  `course_sessions` (model `CourseSession`).
- Livewire only auto-injects its assets on pages that render a Livewire component, so the
  layout calls `@livewireStyles` / `@livewireScripts` explicitly. The nav's Alpine toggle
  depends on this.
- Livewire v4's `make:livewire` defaults to a single-file component with a `⚡` in the
  filename. Always pass `--class`. Page routes use `Route::livewire(...)`.
- Tailwind v4 must be told to scan Livewire's pagination views, or the pager renders
  unstyled. See the `@source` lines in `resources/css/app.css`.
- `<x-logo />` holds the only copy of the wordmark (`public/media/logo.svg`).
- The HTML and plain-text mail views are separate files under `resources/views/vendor/mail/`.
  Rebrand both; a test asserts each MIME part.

## Tests

`composer test` (it clears the config cache first). Pest and PHPUnit coexist: the money paths
live in `tests/Feature/MoneyPathsTest.php` as Pest, everything else is PHPUnit classes.

- `Model::preventLazyLoading()` is on outside production, so an N+1 is a test failure, not a
  slow page. `QueryBudgetTest` additionally asserts that query counts do not grow as rows are
  added to a page.
- `tests/TestCase.php` refuses to boot with a cached config, or against a connection whose
  database is `zarinalabs`.

## Filament panel

- Access is `User::canAccessPanel()` → `is_admin`. Nothing else guards it.
- The panel serves its own scoped CSS built from `fi-*` classes. Plain Tailwind utilities
  used in custom pages only compile because of the custom theme at
  `resources/css/filament/admin/theme.css` (wired via `->viteTheme(...)`). Without it,
  `bg-primary-600`, `text-gray-500` and friends silently render unstyled.
- The panel middleware sets `blade-icons.components.disabled`, so `<x-heroicon-* />` does
  not work inside `/admin`. Use the `@svg('heroicon-s-check', '...')` directive there.
- Hidden Filament fields are **not saved** (`isHiddenAndNotDehydratedWhenHidden`). The
  course form uses `->dehydratedWhenHidden()` on `meeting_link`, `city` and `location` so
  flipping the format clears the stale ones — which in turn means `required()` must be a
  closure, or the invisible field gets validated.

## Registration

`App\Actions\RegisterUserForCourse` is the only way a registration is created. It enforces
every rule (verified, published, `is_accepting`, deadline, no duplicate). There is no
capacity limit — registration is open while the admin's `is_accepting` toggle is on and the
deadline has not passed; the DB unique index on `(user_id, course_id)` blocks duplicates.
The confirmation email is queued inside a transaction, so a rollback discards the job too.

The QR code encodes `URL::signedRoute('admin.check-in', ['registration' => $uuid])`. That
route lives at `/check-in/{registration}` — deliberately outside `/admin` so it cannot
collide with the Filament panel. Phase 4 replaces its placeholder view with the real page.

## Non-goals — do not build

Payments, certificates, CV builder, instructor accounts or approval
flows, marketing or reminder emails, multi-language, charts and analytics, roles beyond
admin/user, social login, comments or reviews, dark mode, public API, mobile app, video
hosting (links only). Course completion is a human decision made by the admin from the
attendance matrix — never automated.

**Exception to "no file uploads":** users may upload a profile photo on `/profile` (image
only, max 2 MB, stored on the `public` disk under `avatars/`, old file deleted on replace).
This is the *only* user upload. No other file uploads by users.
