# Gestion des Absences: secure NFC attendance platform

## Purpose

Upgrade the existing Laravel application into a secure administration portal and shared NFC attendance terminal. Administrators use email and password to manage employees, attendance, absences, planning, reports, NFC cards, terminals, and settings. Employees never access the web administration portal and never enter a PIN; attendance comes from a terminal scanning a secure NFC card.

The supplied reference screens define the visual direction: a premium blue and navy SaaS interface with a persistent desktop sidebar, responsive controls, status cards, structured data panels, and a dedicated mobile terminal interface.

## Scope and compatibility

Existing employee, attendance, absence, and planning rows remain intact. Changes use forward-only migrations, so an installed database is upgraded without editing historical migrations. The old PIN entry route and employee browser attendance pages are removed only after email/password admin access and NFC terminal flows replace them.

The initial mobile terminal is a browser/PWA experience at `/terminal`. It uses Web NFC only when the browser reports support and the page is in a secure context. Its isolated API client and token handling are structured for a Capacitor Android wrapper; production Android hardware access requires a native NFC bridge.

## Data model

### Identity

`users` becomes an administrator-only identity table with `name`, unique `email`, nullable legacy `pin_code` for migration compatibility, `password`, role (`SUPER_ADMIN` or `ADMIN`), and Laravel’s remember token. Existing employee-linked users are retained only as legacy records during migration; access control permits web authentication only to administrator roles.

`employees` remains the HR profile table and receives a stable employee reference and relationships to attendance, absences, planning, NFC cards, and scans. Employee contact email is not an authentication credential.

### NFC entities

- `nfc_cards`: public UUID, encrypted/hashed server-side secret, optional employee relation, assignment, last scan, status (`UNASSIGNED`, `ACTIVE`, `DISABLED`, `LOST`, `BLOCKED`, `REPLACED`).
- `nfc_card_history`: status and assignment transitions, actor, and metadata.
- `nfc_terminals`: public UUID, name, location, department/zone, one-way device credential hash, platform, version, network/status, battery, last seen, and last sync.
- `nfc_scans`: unique client `request_id`, card/employee/terminal references, requested and calculated action, result, scanned/processed timestamps, and limited metadata. The unique request ID is the idempotency boundary.
- `system_settings`: key/value settings including duplicate window, late threshold, terminal timeout, and terminal behavior.
- `audit_logs`: administrator action, target type/id, request IP, and sanitized metadata.

All externally queried IDs use UUIDs. Foreign keys, unique constraints, and indexes protect parent relations, request idempotency, terminal lookup, and recent-activity queries.

## Authentication and authorization

`/login` provides a rate-limited Laravel email/password form with remember-me and password-reset routes. Successful authentication checks an administrator role, regenerates the session, and redirects to `/admin/dashboard`. Laravel’s `auth` middleware protects every `/admin/*` route and an admin-role middleware rejects employees and unauthenticated visitors. Logout invalidates the session and regenerates the CSRF token.

The terminal does not use an administrator cookie or session. Each terminal supplies its public UUID and device credential over HTTPS. The backend verifies the hashed credential, terminal status, request data, and timestamp before it processes a scan.

## NFC scan workflow

1. The terminal generates a UUID `request_id` and reads only an opaque card token.
2. It sends the token, terminal UUID, timestamp, and `AUTO` or allowed explicit action to `POST /api/v1/nfc/scan`.
3. The API authenticates the terminal and checks whether that request ID already has a result. A replay returns the original result without another attendance write.
4. The API validates the card, card status, employee assignment, employee status, duplicate-scan window, schedule, and action.
5. The first valid scan creates check-in; a later valid scan closes the day and calculates worked minutes. Raw scan data is persisted separately.
6. The API returns a minimal response: outcome, display name, action, and time. It never returns employee contacts or internal IDs.

`POST /api/v1/terminals/heartbeat` updates terminal availability. Online/offline state is derived from `last_seen_at` and a configured timeout. The terminal’s IndexedDB queue stores only request data needed to replay failed scans, including the original request ID. It retries on reconnect.

## Web interface

A shared Blade component system supplies the logo, SVG icons, navigation, cards, metric tiles, badges, buttons, tables, forms, alerts, empty states, and responsive drawer behavior. The global layout uses the approved navy/blue palette and contains the sidebar, global search, notifications, administrator menu, and logout.

### Premium visual system and motion

The entire website adopts one professional enterprise visual language rather than page-specific styling. It uses the supplied navy, blue, cyan, success, warning, danger, and purple tokens; a high-legibility sans-serif type scale; 12–18px radii; layered white surfaces; restrained shadows; and subtle blue background illumination. The CA identity appears consistently in the sidebar, login, terminal, empty states, and navigation without duplicating visual treatments.

Motion serves feedback and orientation: page sections use short staggered entrance transitions, buttons and table rows have restrained hover and press responses, dropdowns and dialogs animate opacity and scale, and live-scan/terminal updates use a brief highlight state. Animation duration stays within 150–240ms and respects `prefers-reduced-motion`, which disables nonessential transitions. There are no looping decorative animations, excessive gradients, or motion that hides state changes.

The desktop shell keeps a fixed, branded navigation rail and a high-information top bar. Tablet navigation collapses to icon-first mode, and mobile uses an accessible drawer. Data-heavy screens keep table headers and action controls legible with scroll containers, responsive summary cards, touch-sized controls, and clear loading, empty, error, and success states.

Each page maps to the supplied visual references and real database data:

- Dashboard: daily metrics, recent NFC scans, terminal health, daily presence grid, absence reasons, and weekly attendance.
- Employees: filterable employee table, create/edit/profile views, profile activity, and card assignment.
- Attendance and absences: live-looking activity panels, filters, data-backed summaries, charts, and safe management actions.
- Planning and reports: responsive schedules, conflict indicators, real aggregations, and export-ready tables.
- NFC cards and terminals: status metrics, management tables, details panels, history, heartbeat state, and credential lifecycle actions.
- Settings: persisted attendance, terminal, organization, and security settings with administrator-only forms.
- `/terminal`: mobile scanner, explicit unsupported-NFC message, offline queue status, scan feedback, and recent scans.

Charts receive only server-calculated JSON through a shared Chart.js initializer. Tables retain horizontal scrolling at narrow widths and controls have visible focus states, labels, and semantic status text.

## Application structure

Controllers coordinate requests and views. Form Requests own validation. Services own attendance calculation, scan processing, terminal authentication, audit logging, and dashboard/report aggregation. Eloquent models own relationships, casts, and allowed attributes. Policies/middleware enforce administrator access. Blade components and versioned CSS/JS remove duplicated inline page styling.

## Error handling

Forms retain user input and show field-level validation. Terminal responses use stable JSON error codes for unsupported device, invalid credential, unknown/blocked card, inactive employee, duplicate scan, invalid action, and network recovery. Destructive administration actions require confirmation and audit records. NFC and password secrets are never placed in HTML or JavaScript.

## Quality, performance, and accessibility baseline

Every existing route, form, link, and visible action is audited during implementation. Working business flows are retained or replaced with an equivalent secure flow; disconnected mock buttons and hard-coded dashboard values are not introduced. Database access uses eager loading, pagination, indexed queries, transactions for multi-record writes, and Form Requests or equivalent server-side validation.

All page templates use semantic headings, associated form labels, descriptive empty/error states, keyboard-accessible controls, visible focus rings, adequate color contrast, and non-color status text. Tables support search/filter state, pagination, row actions, and mobile overflow handling. Destructive operations use accessible confirmations; forms prevent duplicate submissions and show pending feedback.

The shared CSS and JavaScript are consolidated to remove duplicate inline styles and dead behavior. Assets and images use suitable sizes and alt text. Public login/error pages receive complete metadata, while administration routes are excluded from indexing. The visual system is tested at 320, 375, 390, 430, 768, 1024, 1280, 1440, and 1920 pixel widths.

## Verification

Feature tests cover admin login, invalid credentials, authorization, employee management, attendance/absence/planning access, NFC card assignment, terminal heartbeat, unknown and blocked cards, check-in/check-out, duplicate scans, idempotent replay, inactive employees, and terminal rejection. Unit tests cover scan action selection and attendance calculation. Route and view checks cover navigation, forms, links, filters, pagination, empty states, and authorization. The complete suite, migrations, route list, logs, and Vite build are run before delivery.
