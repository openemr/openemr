# OpenEMR Patient Dashboard (Next.js port)

Modern Next.js 15 reimplementation of the OpenEMR patient dashboard. Consumes the
existing OpenEMR REST + FHIR R4 APIs as its data layer; no backend changes.

This directory currently contains the **bootstrapped scaffold only**. The
authentication flow, FHIR proxy, patient header, and clinical cards land
incrementally on the `feat/dashboard-modernize` branch — see the migration
defense at `../PATIENT_DASHBOARD_MIGRATION.md` for the framework choice and
roadmap.

## Stack

- **Next.js 15.5.18** (App Router, Turbopack)
- **React 19.2.6** + TypeScript 5.9.3
- **Tailwind CSS 4.3** (CSS-first config)
- **Vitest 4** + jsdom 29 for unit/component tests
- **Node >= 20.19** (jsdom requires `^20.19.0 || ^22.13.0 || >=24.0.0`)

## Local development

```bash
cd frontend
npm install
npm run dev          # turbopack dev server on :3000
```

## Available scripts (locked set — do not rename)

| Script | Purpose |
|---|---|
| `npm run dev` | Turbopack dev server |
| `npm run build` | Production build (turbopack) |
| `npm run start` | Serve the production build |
| `npm run lint` | ESLint (flat config) |
| (more land in subsequent tasks) | typecheck, test, test:watch |

## Environment variables

See `.env.example` (populated in a follow-up task).

## Deploy

Railway service `dashboard` (sibling to `openemr` and `copilot` services in the
same Railway project). Auto-deploys from `master` on `frontend/**` changes.

## Status

Active development on branch `feat/dashboard-modernize`. Scaffold landed; the
authentication, FHIR proxy, and clinical cards are tracked in subsequent tasks
of the same branch.
