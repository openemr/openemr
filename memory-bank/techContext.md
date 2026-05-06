# Tech Context

## Versions

| Component | Version | Notes |
|---|---|---|
| OpenEMR (upstream base) | flex (rolling, on `flex-3.17` images) | Last upstream merge: `11a80ba26` (gitlab/master, 2026-04-30). Project is between releases — schema patches for 8.1.x are visible in commit history (`278910e24`). |
| PHP | **8.2+ required**, prod runs **8.5.4** on Railway | Composer `platform.php = 8.2`. Strict types required on every new file. |
| MariaDB (local Docker dev) | **11.8.6** | `docker/development-easy` |
| MySQL (Railway prod) | (Railway-managed) | Same SQL dialect / driver / InnoDB — audit findings apply identically |
| CouchDB (documents) | **3.5** | Unstructured clinical documents |
| Node | **>= 24.0.0** | Required for build (`npm run build`) |
| Python (copilot service) | **3.11** | FastAPI 0.136 |
| Composer | latest | `composer install --no-dev` for prod build |

## Key dependencies

### OpenEMR PHP (`composer.json`)

- **Frameworks:** Laminas MVC 3.8, Symfony 7.3 components (config, console, DI, event-dispatcher, http-foundation, http-kernel)
- **Templates:** Twig 3.22, Smarty 4.5
- **DB:** Doctrine DBAL 4.x + Doctrine Migrations 3.9, ADODB 5.22 (legacy surface API)
- **HTTP:** Guzzle 7.10, Nyholm PSR-7 + PSR-7 server
- **Auth / SMART on FHIR:** `league/oauth2-server` 8.4, `lcobucci/jwt` 4.3, `steverhoades/oauth2-openid-connect-server` 3.0
- **PDF / docs:** dompdf 3.1, mpdf 8.2, knp-snappy 1.5
- **Other notable:** Monolog 3.9, ramsey/uuid 4.9, phpseclib 3.0
- **Dev:** PHPUnit 11, PHPStan 2.1 + strict + deprecation rules + phpunit, Rector 2.1, slevomat/coding-standard 8.28, squizlabs/php_codesniffer 4.0

### Frontend (`package.json`)

- jQuery 3.7, Angular 1.8, Bootstrap 4.6, Chart.js 4.5, CKEditor 5, DataTables 1.13, dompurify 3.4, dropzone 5.9, hotkeys-js 3.13, i18next 24.2, interactjs 1.10, jspdf 4.2, jszip 3.10, knockout 3.5, moment 2.30, select2 4.0, sortablejs 1.15, summernote 0.9
- Build: Gulp 4.0.2, Dart Sass, autoprefixer, postcss
- Lint: ESLint 9.39, stylelint 16.26
- Test: Jest 29.7

### Co-Pilot Python (`copilot/pyproject.toml`)

- FastAPI, uvicorn, httpx, pydantic
- `anthropic` (primary), `openai` (fallback)
- `langfuse>=2.50,<3` — **pinned away from v3** (v3 dropped `Langfuse.trace()`)
- `python-multipart` (pinned for Form/File parameter parsing)
- pytest, ruff

---

## Local development environment

### Quick start (OpenEMR via Docker — the recommended path)

```bash
cd docker/development-easy
docker compose up --detach --wait
```

- App URL: `http://localhost:8300/` or `https://localhost:9300/`
- Login: `admin / pass`
- phpMyAdmin: `http://localhost:8310/`

### From-source build (non-Docker)

```bash
composer install --no-dev
npm install
npm run build
composer dump-autoload -o
```

### Co-Pilot service (local, Docker)

```bash
cd copilot
cp .env.example .env
# Fill in: ANTHROPIC_API_KEY (or OPENAI_API_KEY + LLM_PROVIDER=openai),
#          OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET,
#          OPENEMR_FHIR_BASE, OPENEMR_OAUTH_BASE
docker compose up --build
# → http://localhost:8080/healthz
# → http://localhost:8080/         (standalone chat UI)
```

### OAuth2 client registration (one-time)

```bash
curl -X POST 'https://<openemr-host>/oauth2/default/registration' \
  -H 'Content-Type: application/json' \
  -d '{
    "application_type": "confidential",
    "client_name": "Clinical Co-Pilot",
    "scope": "openid offline_access api:fhir user/Patient.read user/Observation.read user/MedicationRequest.read user/Condition.read user/Encounter.read user/AllergyIntolerance.read"
  }'
```

Then in OpenEMR Admin → System → API Clients, enable the client and approve its scopes. Put `client_id` / `client_secret` into `copilot/.env`.

---

## Common commands

### OpenEMR — testing (inside Docker via devtools)

Run from `docker/development-easy/`:

```bash
docker compose exec openemr /root/devtools clean-sweep-tests   # all tests
docker compose exec openemr /root/devtools unit-test
docker compose exec openemr /root/devtools api-test
docker compose exec openemr /root/devtools e2e-test            # live view at http://localhost:7900 (pw: openemr123)
docker compose exec openemr /root/devtools services-test
docker compose exec openemr /root/devtools fixtures-test
docker compose exec openemr /root/devtools validators-test
docker compose exec openemr /root/devtools controllers-test
docker compose exec openemr /root/devtools common-test
docker compose exec openemr /root/devtools php-log             # tail PHP error log
```

Tip: install `openemr-cmd` for shorter commands (e.g. `openemr-cmd ut` for unit tests).

### OpenEMR — isolated tests (no Docker required)

```bash
composer phpunit-isolated                                    # all isolated tests
composer phpunit-isolated -- --filter ClassName              # one class
composer phpunit-isolated -- --filter ClassName::testMethod  # one method
composer update-twig-fixtures                                # regenerate Twig render fixtures
```

### OpenEMR — code quality (host, not Docker)

```bash
composer code-quality          # full suite
composer phpstan               # level 10 / max
composer phpstan-baseline      # regenerate baseline
composer phpcs                 # PHP CodeSniffer
composer phpcbf                # auto-fix
composer rector-check          # dry-run
composer rector-fix            # apply
composer require-checker       # undeclared deps
composer codespell             # spell check
composer conventional-commits:check
composer php-syntax-check
npm run lint:js                # ESLint
npm run lint:js-fix
npm run stylelint
```

### OpenEMR — build

```bash
npm run build         # production
npm run dev           # dev with watch
npm run gulp-build    # build only, no watch
```

### Co-Pilot service — testing

From `copilot/`:

```bash
make test         # PHI + tool integration tests only (no live LLM)
make eval         # full suite, mocked LLM
make eval-live    # full suite, real LLM (needs ANTHROPIC_API_KEY + ANTHROPIC_LIVE=1)
ruff check .      # lint
pytest evals -v   # raw pytest
```

`make eval` writes `evals/RESULTS.md` with pass/fail counts.

### Pre-commit hooks

```bash
prek install                # or `pre-commit install` if prek unavailable
prek run --all-files        # run hooks (phpstan, rector, phpcs, codespell, ...)
```

---

## Debugging methods

| Symptom | Tool / file |
|---|---|
| OpenEMR PHP error | `docker compose exec openemr /root/devtools php-log` |
| Co-Pilot agent error in prod | Railway logs for `copilot` service; Langfuse Cloud trace at `cloud.langfuse.com` (project *AgentForge Co-Pilot*) |
| FHIR roundtrip from copilot is failing | `app/fhir/oauth.py` for token; `app/fhir/client.py` for HTTP. Toggle `OPENEMR_VERIFY_TLS=false` for local self-signed certs |
| Verification gate stripped a claim | `app/verification/attribution.py` logs the stripped claim + the union of tool-result IDs |
| PHI showing up where it shouldn't | `app/phi/minimizer.py` — `dosageInstruction` already defensive; other fields use `[0].get(...)` and may break on non-spec FHIR shapes |
| LLM API failing | `app/agent/llm.py:FallbackAdapter` — Anthropic primary, OpenAI fallback per turn. Live `LLM_PROVIDER` is on Railway env |
| Eval suite failing in CI | `.github/workflows/copilot-ci.yml`; tests run with dummy keys; `evals/conftest.py` skips `@pytest.mark.live_llm` unless `ANTHROPIC_LIVE=1` |
| OpenEMR UI shows "Call to undefined method" after deploy | The `awk` injection of `copilot-rail-fragment.php` failed or the upstream image version changed. The Dockerfile has a `grep -q copilot-rail` post-check; it should fail the build before this reaches users |
| Slow first turn | `app/agent/prewarm.py` — pre-fetch on `/v1/sessions`. Cold turn = ~15s, warm = ~3s |

---

## Deployment — Railway

**Project:** `refreshing-empathy`. Three services:

| Service | URL | What it is |
|---|---|---|
| `openemr` | https://openemr-production-0c8c.up.railway.app/ | OpenEMR fork with `awk`-injected iframe rail |
| MySQL | (internal `*.railway.internal`) | OpenEMR DB |
| `copilot` | https://copilot-production-b532.up.railway.app/ | FastAPI agent + standalone chat UI at `/` |

- CI/CD: `.github/workflows/copilot-ci.yml` runs ruff + pytest on `copilot/**`. Deploy job dropped (`f88ed610a`) — Railway native GitHub auto-deploy is the deploy path.
- TLS for OpenEMR: cert regenerated on every container boot via `railway-entrypoint.sh` (idempotent).

---

## Environment variables (names only — never values)

OpenEMR side: standard upstream `.env`. Copilot side (`copilot/.env`):

- LLM: `ANTHROPIC_API_KEY`, `OPENAI_API_KEY`, `LLM_PROVIDER`
- OpenEMR: `OPENEMR_FHIR_BASE`, `OPENEMR_OAUTH_BASE`, `OPENEMR_VERIFY_TLS`, `OPENEMR_ADMIN_PASSWORD`
- OAuth client: `OAUTH_CLIENT_ID`, `OAUTH_CLIENT_SECRET`
- Scope: `PHYSICIAN_PATIENT_PANEL`
- Observability: `LANGFUSE_PUBLIC_KEY`, `LANGFUSE_SECRET_KEY`, `LANGFUSE_HOST`
- Eval/CI: `ANTHROPIC_LIVE`

`.env` is gitignored. `.env.example` is the canonical reference.

---

## Patient roster (10 Synthea CCDA imports on Railway, 2026-04-30)

Best demo patients:
- **Mariela** (47F) — UC1/UC2 hero: 33 encounters, LDL 190, chlorpheniramine, creatinine 2.72
- **Dana** (2y) — UC3 hard-block hero: 17 encounters, 10 allergies including aspirin, Layer-2 fires cleanly

Tools window labs/vitals at **5 years** (`LOOKBACK_DAYS = 1825`), not 90 days, because Synthea timelines are lifetime-spanning.
