# OpenEMR Patient Dashboard (Next.js port)

Modern Next.js 15 reimplementation of the OpenEMR patient dashboard.
Consumes the existing OpenEMR REST + FHIR R4 APIs as its data layer; no
backend changes.

For the full graded defense (why Next.js, what was replaced, what was
deferred, architecture diagram), see `../PATIENT_DASHBOARD_MIGRATION.md`.

## Stack

- **Next.js 15.5.18** (App Router; webpack for production, Turbopack for dev)
- **React 19.2.6** + TypeScript 5.9.3
- **Tailwind CSS 4.3** (CSS-first config)
- **Vitest 4** + jsdom 29 for unit tests (151 tests across 16 files)
- **Node `>= 20.19`** (jsdom requires `^20.19.0 || ^22.13.0 || >=24.0.0`)

## What's inside

| Surface | What it does | Key files |
|---|---|---|
| `/` | Sign-in front door (logged-in: hint to navigate; logged-out: sign-in button) | `app/page.tsx` |
| `/api/auth/login` | PKCE OAuth start; redirects to OpenEMR `oauth2/default/authorize` with `response_type=code`, `aud=OPENEMR_FHIR_BASE`, scope set covering Patient + 6 cards | `app/api/auth/login/route.ts` |
| `/api/auth/callback` | Validates state cookie (constant-time HMAC), POSTs token exchange (urlencoded + Basic auth), decodes OIDC ID-token's `preferred_username`, mints session, sets two `Set-Cookie` headers (session + clear-PKCE) | `app/api/auth/callback/route.ts` |
| `/api/auth/logout` | POST (not GET — CSRF defense); evicts token-store entry; clears cookie | `app/api/auth/logout/route.ts` |
| `/api/fhir/[...path]` | Bearer-injecting FHIR proxy. Catch-all path. Rejects path traversal (incl. encoded `.` / `..` / control chars / `/` in segments). Refresh-on-401 single-flight. Forces `Cache-Control: no-store, private`. Strips `Content-Encoding`/`Length` (undici pre-decompresses). Rewrites Bulk FHIR `Content-Location` URLs back through this proxy. Panel-scope gate runs before forwarding. | `app/api/fhir/[...path]/route.ts` |
| `/api/health` | Static `{ ok: true, openemr_reachable: null, … }` (live probe is post-MVP) | `app/api/health/route.ts` |
| `/patient/[id]` | Server component: fetches `Patient/{id}`, renders header + 6 cards in parallel; embeds Co-Pilot iframe with patient_id + physician_user_id | `app/patient/[id]/page.tsx` |

### Cards (server components, each fetches its own FHIR Bundle)

`AllergyIntolerance` · `Condition?category=problem-list-item` · `MedicationRequest?status=active` · `MedicationRequest` (history) · `CareTeam` · `Encounter?_sort=-date&_count=10`. Each card has per-card error isolation — a single FHIR error renders an inline "Could not load X" instead of bringing down the page.

### Security (defense-in-depth)

- **Server-side OAuth proxy**: FHIR access token never reaches the browser; only an HMAC-signed httpOnly `dashboard_session` cookie does.
- **Panel-scope gate**: every patient-targeting `/api/fhir` request fetches `Patient/{id}.generalPractitioner` and matches against the session's `preferred_username`. Admin allowlist via `COPILOT_ADMIN_USERS` env. Empty-GP fallthrough (mirrors Co-Pilot's behavior); flip `STRICT_PANEL_SCOPE=true` for hard deny.
- **CSP** (default-src 'self', script-src 'self', frame-ancestors 'none'); X-Frame-Options DENY; Permissions-Policy locks down camera/mic/geo. See `lib/security/csp.ts`.
- **PKCE state cookie**: 5-min TTL, server-side `exp` enforcement (not just browser Max-Age), constant-time HMAC compare.
- **Logout via POST + form** so `<img src="">` CSRF can't trigger it.

## Local development

```bash
cd frontend
cp .env.example .env.local      # fill in OpenEMR client + URLs
npm install
npm run dev                     # turbopack dev server on :3000
```

## Available scripts

| Script | Purpose |
|---|---|
| `npm run dev` | Turbopack dev server |
| `npm run build` | Production build (webpack — Turbopack prod is still beta in Next 15.5) |
| `npm run start` | Serve the production build |
| `npm run lint` | ESLint (flat config) |
| `npm run typecheck` | `next typegen && tsc --noEmit` |
| `npm run test` | Vitest run (151 tests) |
| `npm run test:unit` | Same — CI alias |
| `npm run test:watch` | Vitest watch (dev) |

## Environment variables

See `.env.example`. The 9 vars cover OAuth (OPENEMR_OAUTH_BASE,
OPENEMR_DASHBOARD_CLIENT_ID/SECRET), FHIR (OPENEMR_FHIR_BASE,
OPENEMR_VERIFY_TLS), self-identity (DASHBOARD_PUBLIC_URL), Co-Pilot
embed (COPILOT_URL), session crypto (SESSION_COOKIE_SECRET), admin
allowlist (COPILOT_ADMIN_USERS), and panel-scope strictness
(STRICT_PANEL_SCOPE).

## Deploy

Railway service `dashboard` (sibling to `openemr` and `copilot` services
in the same Railway project). Auto-deploys from `master` on
`frontend/**` changes via Next.js standalone output + the multi-stage
`Dockerfile` (node:24-alpine, non-root user). CI gate at
`.github/workflows/dashboard-ci.yml` runs lint + typecheck + tests +
build on every PR touching `frontend/**`.

## Branch / status

`feat/dashboard-modernize` (24 commits ahead of master after the
night-shift run `2026-05-09-0213`). Not yet pushed/merged. Manual
follow-up steps are listed in `memory-bank/assignments/week2.md §7`.
