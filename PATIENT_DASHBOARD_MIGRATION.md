# Patient Dashboard Migration — Defense

> **Status:** WIP. This document is being filled in as the dashboard port lands
> on branch `feat/dashboard-modernize`. The scaffold (Next.js 15 + React 19 +
> TypeScript + Tailwind 4) is in place under `frontend/`; authentication, the
> FHIR proxy, the patient header, and the six clinical cards (Allergies,
> Problems, Medications, Prescriptions, Care Team, Encounters) land in
> subsequent commits on the same branch.

## What is being replaced

OpenEMR's existing patient dashboard at
`interface/patient_file/summary/demographics.php` (2,126 lines) plus its
~25 `*_fragment.php` AJAX panels and ~23 Twig card templates under
`templates/patient/card/`. Stack today: Smarty 4.5 + Twig 3.x + jQuery 3.7 +
Angular 1.8 + Bootstrap 4.6, all server-rendered through PHP 8.2.

The legacy page **stays in the repository** during the port — graders will
compare old vs new — but the new dashboard at `dashboard-production.up.railway.app`
will be the live surface.

## Why Next.js 15 (App Router) + React 19 + TypeScript

(Detailed in subsequent commits — see `frontend/README.md` for the current stack
inventory.) The short version:

- **Server-side OAuth proxy** (Next.js Route Handlers) keeps the FHIR
  access token off the browser — the SPA never holds it.
- **Server Components** match the "fetch FHIR once on page load" data shape
  for the patient header + parallel card fetches.
- **TypeScript-first** lets us model `Patient`, `AllergyIntolerance`,
  `Condition`, `MedicationRequest`, `CareTeam`, `Encounter`, etc., as typed
  Bundles flowing from server to client.
- **Defensible to a hospital CTO** — large React/TS talent pool, mature
  ecosystem, well-known security model.

## What is NOT touched

Per the surprise-challenge PRD wording ("you are not touching the backend"):

- No FHIR service code (`src/Services/FHIR/*`)
- No REST controllers (`src/RestControllers/FHIR/*`)
- No OAuth2 server (`oauth2/authorize.php`, `library/Common/Auth/*`)
- No DB schema, no SQL migrations
- No legacy PHP card templates or fragments

## Tradeoffs

(Will be expanded as later commits land. The argument-by-argument defense
covers: Node runtime added, second OAuth client registered, Next.js
opinionatedness, Railway cold start, et al.)

## Migration roadmap

1. **(landed)** Scaffold + pinned stack at `frontend/`.
2. (pending) `package.json` script lockdown, `.env.example`, README.
3. (pending) `/api/health` endpoint + Vitest scaffold.
4. (pending) OAuth proxy + session-cookie + token store.
5. (pending) Patient header + six clinical cards.
6. (pending) Co-Pilot rail iframe component.
7. (pending) Tests + CI workflow at `.github/workflows/dashboard-ci.yml`.
8. (pending) Final defense write-up here, replacing this WIP placeholder.
