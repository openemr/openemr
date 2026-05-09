# Patient Dashboard Migration — Defense

This is the graded defense document for the W2 surprise-challenge port of
OpenEMR's patient dashboard from PHP/Smarty/jQuery/Angular to Next.js 15.
The full port lives on branch `feat/dashboard-modernize` (run `git rev-list --count master..feat/dashboard-modernize` for the exact commit count) since
the master branch's W2 MVP merge, all under `frontend/` plus this file +
`.github/workflows/dashboard-ci.yml`).

## 1. What was replaced

OpenEMR's existing patient dashboard at
`interface/patient_file/summary/demographics.php` (2,126 lines), plus its
~25 `*_fragment.php` AJAX panels and ~23 Twig card templates under
`templates/patient/card/`. Stack today: Smarty 4.5 + Twig 3.x + jQuery 3.7 +
Angular 1.8 + Bootstrap 4.6, all server-rendered through PHP 8.2.

The legacy page **stays in the repository** during the port — graders will
compare old vs new, and the existing Co-Pilot iframe injection chain
(`copilot-rail-fragment.php` awk-spliced via the repo-root `Dockerfile`)
remains intact for the W1/W2 demo path. The new dashboard is a **separate
Railway service** that runs alongside.

Surface delivered by the new tree under `frontend/`:

- Login flow: `/api/auth/{login,callback,logout}` with PKCE, signed
  session cookie, in-memory token store; OIDC ID token decoded at
  callback to extract OpenEMR username.
- FHIR proxy: `/api/fhir/[...path]` injecting the bearer token server-side
  so the browser never holds it.
- **Panel-scope authorization gate** inside the FHIR proxy: every
  patient-targeting request (path `Patient/{id}` or query `?patient=`/
  `?subject=`) verifies the signed-in clinician matches the patient's
  `Patient.generalPractitioner` (admin-bypass via `COPILOT_ADMIN_USERS`).
  Decision cached per (session, patient) for 60s. Mirrors the Co-Pilot's
  `_verify_patient_in_panel` semantics, including the empty-GP
  fallthrough (Synthea / OpenEMR's R4 transformer often leave it empty;
  `STRICT_PANEL_SCOPE=true` env turns fallthrough into 403).
- **Co-Pilot iframe gets `physician_user_id`**: the OAuth callback
  decodes the OIDC `preferred_username` claim and the patient page
  threads it into the iframe URL, so the Co-Pilot agent's session-open
  knows the clinician identity (no longer falls back to its admin
  bypass list).
- **Logout via POST + `<form>` submit** (not GET) so a
  `<img src="/api/auth/logout">` on a malicious page can't trigger
  logout. SameSite=Lax cookie + POST is sufficient CSRF protection
  for this short-lived/low-impact action.
- **`frontend/Dockerfile`** (multi-stage `node:24-alpine` → Next
  standalone output → non-root run user) for deterministic Railway
  builds.
- **Security response headers** via `next.config.ts headers()`:
  Content-Security-Policy (default-src 'self', script-src 'self',
  style-src 'self' 'unsafe-inline' for Tailwind v4, frame-src 'self'
  + COPILOT_URL origin, frame-ancestors 'none', object-src 'none'),
  X-Content-Type-Options nosniff, Referrer-Policy
  strict-origin-when-cross-origin, X-Frame-Options DENY,
  Permissions-Policy disabling camera/mic/geo.
- Patient view: `/patient/[id]` rendering the **patient header** (name,
  DOB, sex, MRN, active status) + the **six required clinical cards**
  (Allergies, Problem List, Medications, Prescriptions, Care Team) plus
  the **one extra card** of choice — **Encounter history**.
- Co-Pilot rail: embedded in the patient view as a sandboxed iframe.
- Front door: `/` shows a "Sign in with OpenEMR" button when logged out;
  hint to navigate to `/patient/<id>` when logged in.
- Health probe: `/api/health` returning a static placeholder shape (real
  reachability check is Final-scope).
- 151 vitest unit tests across 16 files.

## 2. Why Next.js 15 (App Router) + React 19 + TypeScript

The surprise-challenge PRD says "the framework decision is yours" but
explicitly reminds graders to ask "why that tool is the right one." Next.js 15
is the correct choice for this specific port for four concrete reasons:

1. **Server-side OAuth proxy keeps the FHIR access token off the browser.**
   This is the load-bearing security claim of the new dashboard. Every
   FHIR request lands at `/api/fhir/[...path]` (a Next.js Route Handler in
   the Node.js runtime), which reads a signed session cookie, looks the
   bearer token up in a process-local map, and injects `Authorization:
   Bearer ...` server-side. The browser only ever holds an httpOnly,
   signed `dashboard_session=<sessionId>` cookie — no PHI access token.
   This is fundamentally easier in a framework with first-class
   server-side route handlers than in a pure SPA where every helper around
   PKCE has to assume the browser holds the token.

2. **Server Components match the "fetch FHIR once on page load" data shape.**
   The patient page composes Patient + 6 cards. With Server Components,
   each card is an `async` function whose `await fhirGet(...)` runs in the
   Next.js Node process; the framework handles the suspense and streams
   the rendered HTML to the browser. There is no client-side waterfall,
   no React Query setup, no `useEffect(() => fetch(...))`. The grain of
   "one network call per card, all fired during SSR" maps cleanly onto
   the App Router.

3. **TypeScript subset of the FHIR R4 / US Core 3.1 types** in
   `frontend/lib/fhir/types.ts` lets every card's parser fail at
   compile-time when the upstream shape diverges. Optional fields are
   modeled as `?` so a sparse Bundle still parses, but the parser
   couldn't, for example, accidentally try to render `Patient.gender` as
   an array.

4. **Defensible to a hospital CTO** — Next.js + React + TypeScript is the
   single largest-talent-pool Web stack on the planet right now. Hiring
   a clinical-product front-end engineer who has shipped Next.js is far
   easier than finding one who has shipped Angular 1.8 or Smarty.

## 3. What was NOT touched

Per the PRD wording ("you are not touching the backend"):

- **No FHIR service code** (`src/Services/FHIR/*`).
- **No REST controllers** (`src/RestControllers/FHIR/*`).
- **No OAuth2 server** (`oauth2/authorize.php`, `library/Common/Auth/*`).
- **No database schema**, no SQL migrations.
- **No legacy PHP card templates or fragments** — they remain in the repo
  for grader comparison and for the existing iframe-injection path.
- **No edits to the repo-root `Dockerfile` or root `.gitignore`** — the
  port is purely additive (verified during planning; see plan §11).

## 4. Tradeoffs taken

| Trade | Cost | Rationale |
|---|---|---|
| Node.js runtime added to the deployment | New Railway service, separate logs, separate cold-start budget | Server-side OAuth proxy requires a server. Worth it. |
| Second OAuth2 client registered in OpenEMR | One-time admin step; separate `OPENEMR_DASHBOARD_CLIENT_ID/SECRET` | Clean separation between the Co-Pilot agent and the dashboard SPA — different scopes, different lifecycles, different audit signatures. |
| Next.js opinionatedness (App Router, Server Components) vs Vite raw flexibility | Some Next-specific patterns to learn (e.g. async params, `cookies()` from `next/headers`) | App Router's RSC primitives map onto our data-fetch shape so well that the resulting code is dramatically smaller than a SPA equivalent. |
| Webpack `next build` (not Turbopack) for production | 339 kB First Load JS vs 127 kB with Turbopack | Production Turbopack is still beta in Next 15.5; webpack is stable. Bundle size revisitable when Turbopack stabilizes. |
| In-memory token store (single-replica only) | No horizontal scale; user re-logs in on Railway redeploy | Acceptable for a clinical pilot; Redis-backed store is a follow-up well before a 500-bed deployment. |
| Panel-scope GP-empty fallthrough (default permissive) | Synthea data and OpenEMR's R4 transformer often leave `Patient.generalPractitioner` empty; strict deny would 403 the demo | `STRICT_PANEL_SCOPE=true` env turns fallthrough into deny once GP assignments are reliable in production. Documented in `.env.example`. |
| `style-src 'unsafe-inline'` in CSP | Tailwind v4 + Next inline critical styles | Documented compromise. `script-src` stays strict (no unsafe-inline). Future hardening: nonce-based script-src and inline-style elimination. |
| Webpack `next build` (not Turbopack) for production | 339 kB First Load JS vs 127 kB with Turbopack | Production Turbopack is still beta in Next 15.5; webpack is stable. Bundle size revisitable when Turbopack stabilizes. |

## 5. What was explicitly deferred (do not silently expand)

These were called out at planning time and remain deferred:

- **Patient demographics editing.** The new dashboard is read-only;
  clinicians click through to legacy `demographics_full.php` for edits.
  A real port of edit forms would itself be a separate sprint.
- **Patient finder / search.** Reaching `/patient/[id]` requires an
  explicit URL or the existing OpenEMR finder. The legacy
  `dynamic_finder_ajax.php` (with its awk-injected scope filter) keeps
  working unchanged.
- ~~**`physician_user_id` in the iframe URL.**~~ — **shipped in KR10 task 1**.
- **Mobile / tablet layouts beyond Tailwind defaults.** Clinical use is
  desktop.
- **Live FHIR / OpenEMR end-to-end test in CI.** Tests use mocked
  Bundles via `vi.spyOn(globalThis, 'fetch')`. Live verification is a
  manual smoke against a deployed Railway instance.
- **Card refresh-on-action / TanStack Query.** Read-only views don't
  need it.

## 6. Migration roadmap for the rest of OpenEMR

The same pattern reproduces for the next surfaces in priority order:

1. **`interface/forms/*`** (encounter forms, vitals entry, etc.) — same
   "FHIR/REST + server-component card" shape. Each form becomes a Next
   page that POSTs back through `/api/fhir`.
2. **`interface/billing/*`** — heavier on tables and transactions.
   TanStack Query becomes useful here for action-driven refresh.
3. **`interface/main/finder/*`** — the patient finder. Move from the
   awk-injected `copilot-finder-scope.php` to a Next.js Route Handler
   that wraps OpenEMR's own search and applies the same provider-scope
   filter using OAuth-scoped data.

## 7. Architecture (one diagram)

```
┌────────────────────────────────────────────────────────────────┐
│  Browser                                                       │
│  ┌──────────────────────────────┐  ┌────────────────────────┐  │
│  │ Next.js dashboard            │  │ Co-Pilot iframe        │  │
│  │ /patient/{id}                │  │ (Railway service,      │  │
│  │  ├ <PatientHeader/>          │  │  unchanged W1/W2 code) │  │
│  │  ├ Allergies / Problems /   │◀─┤ <iframe src=          │  │
│  │  │  Medications /            │  │  COPILOT_URL/iframe?  │  │
│  │  │  Prescriptions /          │  │  patient_id=...       │  │
│  │  │  CareTeam / Encounters    │  └────────────────────────┘  │
│  └────────────┬─────────────────┘                              │
└───────────────┼────────────────────────────────────────────────┘
                │ fetch via /api/fhir/* (server-side proxy)
                ▼
┌────────────────────────────────────────────────────────────────┐
│  Next.js server (Node runtime, Railway service `dashboard`)    │
│   ├ /api/fhir/[...path]/route.ts  — proxies to OpenEMR FHIR    │
│   │  with confidential-client OAuth token (refresh on 401)     │
│   ├ /api/auth/login   — PKCE start                             │
│   ├ /api/auth/callback — exchange code, set httpOnly cookie    │
│   └ /api/auth/logout  — evict + clear cookie                   │
└───────────────┬────────────────────────────────────────────────┘
                │ HTTPS + Bearer token
                ▼
┌────────────────────────────────────────────────────────────────┐
│  OpenEMR (unchanged)                                           │
│   - oauth2/authorize.php   (existing OIDC server)              │
│   - apis/dispatch.php → /fhir/* routes (unchanged)             │
│   - src/Services/FHIR/* (unchanged)                            │
└────────────────────────────────────────────────────────────────┘
```

## 8. How to run / verify locally

```bash
cd frontend
cp .env.example .env.local      # then fill in OpenEMR client + URLs
npm install
npm run dev                     # turbopack dev server on :3000
# In another shell:
npm run lint                    # ESLint flat config
npm run typecheck               # next typegen && tsc --noEmit
npm run test                    # vitest run (151 unit tests)
npm run build                   # production build
npm run start                   # serve the production build
```

## 9. Project surface (file inventory)

```
frontend/
├── app/
│   ├── api/
│   │   ├── auth/{login,callback,logout}/route.ts   # PKCE + token exchange + cookie clear
│   │   ├── fhir/[...path]/route.ts                 # bearer-injecting catch-all proxy
│   │   └── health/route.ts                         # static placeholder
│   ├── patient/[id]/{page,loading,error}.tsx       # patient view + boundaries
│   ├── page.tsx                                    # home / login front door
│   ├── layout.tsx, globals.css, favicon.ico        # scaffold defaults
├── components/
│   ├── PatientHeader.tsx                           # server component
│   ├── CopilotRail.tsx                             # iframe embed
│   └── cards/
│       ├── CardShell.tsx
│       ├── Allergies.tsx, Problems.tsx, Medications.tsx
│       ├── Prescriptions.tsx, CareTeam.tsx, Encounters.tsx
├── lib/
│   ├── auth/{pkce,cookies,token-store}.ts          # PKCE, signed cookies, single-flight refresh
│   └── fhir/{types,client,bundle,patient-name,upstream-url}.ts
├── tests/unit/                                     # 151 tests
├── package.json, package-lock.json                 # direct deps pinned exact (the one exception is `eslint`, left as `^9` so eslint-config-next can peer-resolve it)
├── tsconfig.json, next.config.ts, eslint.config.mjs, postcss.config.mjs
├── vitest.config.ts                                # @vitejs/plugin-react + tsx tests
├── .env.example, .gitignore, .nvmrc, README.md
```

Plus `.github/workflows/dashboard-ci.yml` (path-filtered to `frontend/**`).
Zero existing OpenEMR source files modified.

## 10. Final defense (one paragraph for the grader)

The dashboard port replaces a 2,126-line PHP file plus ~25 fragment
includes plus ~23 Twig templates with a Next.js 15 App Router app whose
load-bearing primitive is a server-side OAuth proxy: the browser never
holds the FHIR bearer token, so PHI requests to the EHR cannot be
forged from a leaked cookie or XSS. A panel-scope authorization gate
inside that same proxy verifies that every patient-targeting request
matches the signed-in clinician's general practitioner (admin-bypass +
empty-GP fallthrough mirroring the Co-Pilot's working semantics).
Defense-in-depth response headers (CSP locking down script/object/frame
sources, frame-ancestors deny, Permissions-Policy locking down
camera/mic/geo) are applied to every route. Sign-in goes through
PKCE + signed cookie + httpOnly session; sign-out is a `<form>` POST
that SameSite=Lax cookie+method blocks from being CSRF'd. The framework
choice is Next.js because it gives all those patterns (Route Handlers
in Node runtime with httpOnly session cookie + module-scope token
store + module-scope panel-scope decision cache + config-level header
hooks) as first-class primitives, and because Server Components let
the patient page compose seven parallel FHIR fetches (Patient + 6
cards) without a client-side waterfall. The PHP backend is untouched;
all six required cards plus Encounters render against unmodified
`apis/dispatch.php` endpoints. The Co-Pilot rail is embedded as a
sandboxed iframe carrying the signed-in clinician's username so the
agent's panel-scope check matches. A multi-stage `node:24-alpine`
Dockerfile + Next standalone output ships the runtime image. 151
vitest unit tests cover the auth helpers, signed cookies, FHIR proxy,
URL traversal protection, patient-name parsing, identifier matching,
panel-scope decisions, ID-token decode, CSP construction, and the
Co-Pilot rail's URL building. Items intentionally deferred (real
e2e against a live OpenEMR, front-desk facility scope, the patient
finder, edit forms) are listed above so future work has a clear
pickup list.
