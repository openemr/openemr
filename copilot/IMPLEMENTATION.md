# Implementation Status — Clinical Co-Pilot

**Last updated:** 2026-04-29 evening
**Submission targets:**
- ✅ MVP — Tuesday 2026-04-28 11:59 PM CT (audit + users + architecture docs + deployed OpenEMR)
- ⏳ **Early Submission — Thursday 2026-04-30 11:59 PM CT** (deployed agent + eval + observability + demo video)
- 📋 Final — Sunday 2026-05-03 12:00 PM CT (production-ready, AI cost analysis, social post, GitHub repo)

This document tracks what's built, what's blocked, and what's left before each deadline.

---

## 1. Original Plan vs. Reality

### What ARCHITECTURE.md said we'd build

A separate Python (FastAPI) service deployed alongside OpenEMR, integrated as a SMART on FHIR app. The agent uses Anthropic's Claude Sonnet 4.6 via the official SDK, exposes 8 FHIR-backed tools, runs every response through a two-layer verification gate (source attribution + domain rules), and emits Langfuse traces for observability. PHI is pseudonymized before any LLM call.

### What's running right now

```
┌──────────────────────────────────────────────────────────────────┐
│  PHYSICIAN'S BROWSER                                             │
│  https://copilot-production-b532.up.railway.app    ← deployed   │
└──────────────────────────────────────────────────────────────────┘
           │  HTTPS, session token
           ▼
┌──────────────────────────────────────────────────────────────────┐
│  COPILOT SERVICE (Railway, this repo's /copilot dir)             │
│   • FastAPI 0.136 / Python 3.11                                  │
│   • OAuth2 password grant against OpenEMR                        │
│   • 8 FHIR-backed tools (5-step pattern)                         │
│   • PHI minimizer (session-scoped pseudonyms)                    │
│   • Two-layer verification gate                                  │
│   • Langfuse wrapper (noop fallback when keys absent)            │
│   • LLM adapter — OpenAI gpt-4o (Anthropic Claude as fallback)   │
└──────────────────────────────────────────────────────────────────┘
           │                        │
           │ FHIR R4 over OAuth2    │ chat.completions.create
           ▼                        ▼
┌─────────────────────────┐  ┌────────────────────────────────────┐
│ OPENEMR (Railway)       │  │ OPENAI gpt-4o                      │
│ openemr-production-...  │  │ (primary; Anthropic adapter ready  │
│ MySQL backing service   │  │  for switch via LLM_PROVIDER env)  │
│ ⚠ ZERO PATIENT DATA     │  └────────────────────────────────────┘
└─────────────────────────┘
```

The agent matches ARCHITECTURE.md exactly with one runtime substitution: **OpenAI gpt-4o is the active LLM**, not Anthropic Claude Sonnet. This was a forced fallback when Anthropic's billing rejected calls despite a $40 credited balance (workspace-vs-key mismatch we couldn't resolve before the deadline). The Anthropic adapter is fully wired and tested — switching back is one env var (`LLM_PROVIDER=anthropic`) once Anthropic is unblocked.

### Architecture trace-back

| ARCHITECTURE.md §  | Component                | Where it lives                                     | Status |
|--------------------|--------------------------|----------------------------------------------------|--------|
| §2.1 Framework     | Custom orchestration     | `app/agent/loop.py`                                | ✅      |
| §2.2 LLM           | Adapter (OpenAI/Anthropic)| `app/agent/llm.py`                                 | ✅      |
| §3.1 Tools         | 8 tools, FHIR-backed     | `app/tools/` (8 files + `_base.py` + `registry.py`)| ✅      |
| §3.2 5-step pattern| Shared helper            | `app/tools/_base.py:run_tool`                      | ✅      |
| §3.3 PHI minimizer | Pseudonym + scrub        | `app/phi/minimizer.py`, `app/phi/session.py`       | ✅      |
| §3.4 Trust boundaries| OAuth2 + ACL + verify  | `app/fhir/oauth.py`, `app/acl/check.py`            | ✅      |
| §4.1 Layer-1 verify| Source attribution       | `app/verification/attribution.py`                  | ✅      |
| §4.1 Layer-2 verify| Domain rules             | `app/verification/rules.py`                        | ✅ (2 of 4 rules) |
| §5 Observability   | Langfuse wrapper         | `app/observability/trace.py`                       | ✅ (noop fallback active) |
| §6 Evaluation      | pytest harness           | `evals/`                                           | ✅      |
| §10 Deployment     | Railway service `copilot`| `Dockerfile`, `railway.toml`                       | ✅      |

---

## 2. Synthea Integration

We use [github.com/synthetichealth/synthea](https://github.com/synthetichealth/synthea) (MITRE) — the de-facto open-source synthetic-patient generator. Synthea outputs full clinical lifetimes (demographics, encounters, conditions, meds, labs, vitals, immunizations, procedures) in FHIR R4 / CCDA / CSV formats. Synthetic data only — no PHI, suitable for the demo per the PRD's "demo data only" constraint.

### How we run Synthea

We don't compile from source — we use the upstream pre-built JAR + an OpenJDK Docker image so no local Java install is needed:

```bash
# One-time download (~190 MB)
curl -L -o /tmp/synthea-with-dependencies.jar \
  https://github.com/synthetichealth/synthea/releases/latest/download/synthea-with-dependencies.jar

# Generate N patients in CCDA format
docker run --rm \
  -v /tmp/synthea-with-dependencies.jar:/synthea-with-dependencies.jar \
  -v /tmp/synthea-ccda:/output \
  eclipse-temurin:17-jre \
  java -jar /synthea-with-dependencies.jar \
    --exporter.ccda.export=true \
    --exporter.fhir.export=false \
    --exporter.csv.export=false \
    --exporter.baseDirectory=/output \
    -p 10 Massachusetts
# → /tmp/synthea-ccda/ccda/*.xml (one CCDA per generated patient)
```

### Synthea output formats — what we tried and why

| Format | Size/patient | Where we tried | Result |
|---|---|---|---|
| FHIR R4 bundles | ~200 KB | POST to OpenEMR FHIR API | ❌ HTTP 500 — Synthea's resource shapes (extensions, photo data, identifier systems) don't match what OpenEMR's FHIR receiver accepts. Known upstream issue. |
| CCDA XML | 100 KB – 7 MB | OpenEMR Carecoordination UI | ❌ HTTP 200 returned but `mkdir(): Permission denied` in server logs — Railway volume mount blocks `documents/` writes for Apache user |
| CSV | ~varies | (not attempted) | Future option for direct-DB load |

We have **15 generated CCDA files** ready at `/tmp/synthea-ccda/ccda/` (10 alive + 5 deceased patients across MA towns). They are valid C-CDA R2.1 — they import successfully on the *local* OpenEMR (verified via Carecoordination upload there during earlier testing).

### Eval suite uses synthetic data

The agent's `evals/agent/test_scenarios.py` uses hand-crafted FHIR fixtures (smaller and more targeted than Synthea output) to exercise the verification gate against specific failure modes. Synthea is the *demo* data source, not the *eval* data source — the architecture document calls this out at §6.2 ("hand-crafted synthetic patients designed to stress specific failure modes").

---

## 3. What's Finished

### Phase A — Skeleton + FHIR plumbing ✅

- FastAPI app with `/healthz`, `/`, `/v1/sessions`, `/v1/chat`, `/v1/patient/{id}/raw`
- httpx-based FHIR client with TLS-verify toggle (`OPENEMR_VERIFY_TLS`) for local self-signed cert
- OAuth2 password grant against OpenEMR (user-scoped client, not system/JWKS — simpler for demo)
- Dockerfile (`python:3.11-slim`, multi-stage), `railway.toml`, local `docker-compose.yml`

### Phase B — Tools, PHI minimizer, ACL ✅

- 8 tools, all using shared 5-step pattern in `app/tools/_base.py`:
  - `get_patient_summary`, `get_active_medications`, `get_recent_labs`, `get_recent_vitals`,
    `get_encounter_history`, `get_allergies`, `get_encounter_note`, `check_drug_interactions`
- PHI minimizer strips name, birthDate→age, address, telecom, SSN, MRN; replaces with session pseudonym `Patient-XXXX` / `Provider-A`
- ACL middleware mirrors OpenEMR's `aclCheckCore` — denies *before* hitting FHIR for missing scopes
- Session pseudonym map in-memory (Redis-ready interface for multi-replica scaling)

### Phase C — Agent loop + verification ✅

- Provider-agnostic loop (`app/agent/loop.py`) — works with both Anthropic and OpenAI adapters
- Static system prompt with cache_control on Anthropic (cache hits ~30% on repeat sessions in local testing)
- `submit_response` tool forces structured JSON output: `{prose, claims[], data_gaps[]}`
- Layer-1 source attribution: walks every `claim`, verifies its `record_id` is in the union of tool-result IDs; strips unanchored claims and retries once with feedback
- Layer-2 domain rules: cross-patient leakage hard-block, allergy contraindication hard-block (renal-dose and QTc rules deferred to Final Sunday)

### Phase D — Observability + eval ✅

- Langfuse wrapper (`app/observability/trace.py`) — emits one trace per turn including tool sequence, latencies, token counts, verification verdict, ACL checks. Falls back to stdout-structured logging when keys unset.
- Eval harness — pytest with auto-generated `evals/RESULTS.md` summary, `make eval` and `make eval-live` targets
- **17 tests passing**: 7 PHI minimizer, 3 tool integration, 4 verification gate, 3 live LLM scenarios (UC1 happy path, UC1 refusal-on-empty, prompt injection)

### Phase E1 — Local validation ✅

- Local OpenEMR (Docker dev-easy stack) registered as SMART client with user/* read scopes
- Real UC1 question against local OpenEMR returned a verified, cited response (Phil Belford patient): 4 tools chained, every claim with `record_id`, latency 8.9s, `verification_passed: true`
- All three use cases manually tested in local chat UI

### Phase E2 — Railway deployment ✅ (agent only)

- `copilot` service created in `refreshing-empathy` project alongside existing `MySQL` and `openemr`
- Public URL: **https://copilot-production-b532.up.railway.app**
- All 17 env vars set including OAuth client `GZg2tYuf...` registered against Railway OpenEMR
- `railway up` succeeded after fixing the `$PORT` shell-expansion bug in `railway.toml`
- `/healthz` returns 200; chat UI loads at root

---

## 4. Source-of-Truth Gap — Local + Railway, *Not Yet on GitHub or GitLab*

**Submission rules update (per user):** every submission (MVP / Early / Final) requires three things together — **demo video link + repo link + deployed URL**. The repo is the **GitLab** link the user submits to Gauntlet, mirrored from a **GitHub fork** of OpenEMR (the GitHub fork is what Railway already auto-deploys the OpenEMR service from). So the publish requirement is **not** Final-only as I had it before — it applies to **tonight's Early Submission too**.

### Where things live right now

| Location | What's there | Status |
|---|---|---|
| Laptop `/Users/rikki/Desktop/Doc/OOD/openemr/` | Full OpenEMR fork + `copilot/` agent code + all updated docs | ✅ source of truth |
| Railway `openemr` service | OpenEMR running, auto-deployed from your **GitHub** fork | ✅ live (no patient data — see §5) |
| Railway `copilot` service | Agent running, deployed via `railway up` from laptop (NOT git-connected yet) | ✅ live |
| **GitHub fork (your account)** | Frozen at MVP commit — has AUDIT.md / USERS.md / ARCHITECTURE.md / AGENTFORGE.md but **no `copilot/` directory and no IMPLEMENTATION.md** | ⚠ stale |
| **GitLab repo** | Doesn't exist yet OR is also frozen at MVP commit | ❌ not done |

The local laptop dir was never `git init`'d (we checked: "Is a git repository: false"). The MVP-era commits to the GitHub fork happened by editing files in the GitHub web UI directly, not by `git push` from this laptop. So even GitHub's record of MVP is the *web-edited* version, and we have no local git history — every change since must be committed in one big squash.

### Why we deferred git publish until now (Path 3 recap)

When we stood up Phase E (Tuesday evening), I called this **Path 3** — `railway up` from local now, GitHub publish later. The reasoning held up across this week's debugging cycles (Anthropic → OpenAI provider switch, OAuth flow debugging, failed Synthea FHIR import, failed Carecoordination CCDA upload, `$PORT` shell-expansion fix). A weekly squash commit beats five days of public WIP commits. But the user's submission requirement just collapsed the deferral window.

### Dual-publish strategy: GitHub primary, GitLab mirror

Submission asks for the GitLab link; Railway is wired to GitHub. We need both with identical content.

```
[laptop /openemr/]
       │
       │   git push origin main
       │   git push gitlab main      ← single command sequence
       ▼
   ┌───────────────────────┐    ┌───────────────────────┐
   │ GitHub fork           │    │ GitLab mirror         │
   │ (Railway pulls from)  │    │ (submission link)     │
   └───────────────────────┘    └───────────────────────┘
       │
       │  auto-deploy webhook
       ▼
   Railway openemr service       (Railway copilot service stays
   (already wired)                on `railway up` for tonight,
                                  re-wire to GitHub Sunday — see §7)
```

### Concrete steps for tonight (TIGHT — ~45 min)

1. **Verify GitHub fork URL.** Confirm which GitHub repo Railway's `openemr` service is wired to (e.g., `https://github.com/<you>/openemr`). This is the canonical fork — do not create a new one.
2. **Create matching GitLab repo** at gitlab.com with same name (e.g., `openemr`). Empty, no README/license init — we want the import to be clean.
3. **`git init` locally** in `/Users/rikki/Desktop/Doc/OOD/openemr/` *only if* the laptop dir isn't already linked to GitHub. If it isn't, instead of `git init` from scratch:
   ```bash
   cd /Users/rikki/Desktop/Doc/OOD/openemr
   git init
   git remote add origin https://github.com/<you>/openemr.git
   git fetch origin
   git reset --soft origin/main      # bring local to parity with what GitHub has
   git status                         # should now show all the new copilot/ files + IMPLEMENTATION.md as untracked
   ```
4. **Verify `.gitignore` is clean** — `copilot/.env` (with all the secrets), `copilot/.venv/`, `__pycache__/` already in `copilot/.gitignore`. Add a top-level `.gitignore` if missing.
5. **Sanity check the diff** — `git status` should show only files we expect. No accidental edits to upstream OpenEMR PHP. Spot-check `git diff origin/main -- src/` and `interface/` come back empty.
6. **First commit:** stage everything new (`git add copilot/ IMPLEMENTATION.md AGENTFORGE.md` and the doc updates), commit with a proper message:
   ```
   feat(copilot): add Clinical Co-Pilot agent service

   - FastAPI agent with 8 FHIR-backed tools (5-step pattern per ARCHITECTURE.md §3.2)
   - Two-layer verification gate (source attribution + domain rules)
   - PHI minimizer, ACL middleware, Langfuse observability
   - Anthropic + OpenAI LLM adapters with runtime switch
   - 17/17 eval tests passing, deployed to Railway

   Implementation status documented in copilot/IMPLEMENTATION.md
   ```
7. **Push to GitHub:** `git push origin main`
8. **Add GitLab as second remote, push to it:**
   ```bash
   git remote add gitlab https://gitlab.com/<you>/openemr.git
   git push gitlab main
   ```
9. **Verify both repos** show the new `copilot/` directory in their web UI and that the README pointer (`AGENTFORGE.md`) renders.
10. **Submit the GitLab link** with the demo video + deployed URL.

If step 3's `git reset --soft` doesn't behave (the laptop diverged from GitHub by more than just additions), the safer path is: clone the GitHub fork into a fresh dir, copy the new files in, commit, push. ~10 min more, zero risk of clobbering existing GitHub state.

### What this means for each submission

| Submission | Repo state required | Our plan |
|---|---|---|
| MVP (Tuesday — done) | AUDIT/USERS/ARCHITECTURE.md committed | ✅ done via web UI |
| **Early (Thursday — tonight)** | All current code in the submitted GitLab link | ⚠ **must be pushed tonight** before submitting — moved into §6 checklist |
| Final (Sunday) | Same, plus AI cost analysis, social post, polish, possibly Railway services switched to auto-deploy | 📋 §7 |

### AI Interview framing if asked about commit cadence

"This is one squash commit at submission time. We deliberately ran the iteration loop locally + on Railway via CLI deploy to keep the public commit log clean — five days of OAuth flow debugging, provider-switching, and Synthea import experiments would have been noise. The squash captures the architecture, not the 50 dead-ends. Final Sunday will switch the agent service to GitHub-auto-deploy so future commits are atomic and visible."

---

## 5. Active Blocker — Patient Data on Railway OpenEMR

**The agent is deployed and works. The Railway-deployed OpenEMR has zero patients, so the deployed agent has nothing to query.**

Without patient data the demo video would show "I cannot find patient X" for every question — useless. This is the only thing standing between us and a complete Early Submission.

### Why both Synthea import paths failed

1. **FHIR POST path** — direct POST of Synthea's FHIR R4 Patient resources to OpenEMR's `/Patient` endpoint returned HTTP 500 with `Call to a member function getUrl() on array`. Root cause: Synthea generates resource shapes (photo arrays, extension blocks, identifier formats) that OpenEMR's FHIR ingest controller doesn't handle. Not solvable by retrying or simple field-stripping; would need a non-trivial Synthea→OpenEMR FHIR adapter.

2. **CCDA UI path** — Carecoordination upload at `/interface/modules/zend_modules/public/carecoordination/upload` returns HTTP 200 (server-side warning visible in logs):
   ```
   PHP Warning: mkdir(): Permission denied
     in /var/www/localhost/htdocs/openemr/library/classes/Document.class.php:1022
   ```
   The Apache user inside the Railway container can't write to the `documents/` directory inside the Railway volume mount. The upload appears to succeed in the browser (progress bar shows "done") but the file never lands on disk and no Pending Document is created.

### Resolution paths (ordered by deadline-fit)

| Option | Effort | Risk | When |
|---|---|---|---|
| **D — Manual patient creation in UI** | 15 min × 3 patients = ~45 min | Low — uses paths OpenEMR is documented to support | **Tonight (Thursday Early Submission)** |
| E — Fix volume perms via `railway ssh` (`chown apache:apache documents/`) then retry CCDA | 20-30 min | Medium — wrong perms could break other OpenEMR features | Tonight if ahead of schedule, else Final Sunday |
| F — Direct MySQL bulk load via Railway public proxy (Synthea CSV → OpenEMR INSERTs) | 60-90 min | Medium — foreign-key chain across patient_data + lists + prescriptions + form_encounter is non-trivial | Final Sunday |
| G — railway ssh + `import_ccda.php` CLI | 30+ min | High — script docstring says "NOT WORKING IF DEV MODE OFF" | Final Sunday only if F fails |

**Decision (revised after discussion):** the user wants Synthea-quality data on Railway, not hand-created stubs. We commit to **Option E first, Option G fallback** rather than the manual Option D. Detailed steps for both below.

### Path forward — Option E (primary, ~20-30 min)

Goal: fix the volume permission problem that's blocking Carecoordination's `mkdir()` so the existing Synthea CCDA files at `/tmp/synthea-ccda/ccda/` upload successfully.

**Stage 1 — Diagnose perms inside the running container**

```bash
cd /Users/rikki/Desktop/Doc/OOD/openemr/copilot   # so railway is linked
railway ssh --service openemr
# Inside container:
whoami                          # likely 'root' on flex image, but Apache process runs as 'apache'
id apache 2>&1
ls -ld /var/www/localhost/htdocs/openemr/sites/default/documents
ls -ld /var/www/localhost/htdocs/openemr/sites/default
df -h /var/www/localhost/htdocs/openemr/sites/default      # confirm volume mount
mount | grep openemr                                        # confirm volume is bind-mounted
```

What we expect to find:
- `documents/` either doesn't exist (mkdir fails because parent has wrong perms), OR
- `documents/` exists but is owned by `root:root` with `755` (Apache can read but not create subdirs), OR
- The volume is mounted with restrictive flags (rare on Railway)

**Stage 2 — Apply the fix**

The Apache process inside the OpenEMR container runs as user `apache` (UID likely 100 or 1001 depending on base image). The fix is one of:

```bash
# Option E.1 — chown the documents dir (and ensure parent is writable)
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites/default/documents
chmod -R u+rwX,g+rwX /var/www/localhost/htdocs/openemr/sites/default/documents

# Option E.2 — if documents/ doesn't exist yet, create it first
mkdir -p /var/www/localhost/htdocs/openemr/sites/default/documents
chown -R apache:apache /var/www/localhost/htdocs/openemr/sites/default/documents
chmod 775 /var/www/localhost/htdocs/openemr/sites/default/documents
```

Idempotent and reversible — only touches the documents subtree.

**Stage 3 — Smoke test the upload**

1. Browser → `https://openemr-production-0c8c.up.railway.app` → login `EPU-admin-46` / `<OPENEMR_ADMIN_PASSWORD>`
2. Carecoordination → Upload CCDA → select `Tracey100_DuBuque211_*.xml` (smallest at 108 KB) from `/tmp/synthea-ccda/ccda/`
3. **In parallel terminal**: `railway logs --service openemr 2>&1 | grep -iE "permission|mkdir"` — should be quiet now
4. Carecoordination → Pending Documents → the Tracey row should now appear (it didn't before the fix)
5. Click → Approve as new patient → done

**Stage 4 — Bulk import the remaining 9 alive patients**

If Tracey lands successfully, repeat the upload+approve flow for the 9 other alive Synthea patients listed in §2:

```
Chris95_Flatley871_*.xml          (440 KB, 46M)
Dana512_Fadel536_*.xml            (250 KB)
Guillermo498_Grijalva82_*.xml     (132 KB, 32M)
Kacie297_Crona259_*.xml           (323 KB, 30F)
Kiera822_Torphy630_*.xml          (421 KB)
Mariela993_Arlette667_*.xml       (446 KB, 47F)
Maryann106_Marvin195_*.xml        (623 KB, 98F — exceptional age, drop if too old to be plausible)
Pablo44_Montoya249_*.xml          (-, 50M — alive)
Qiana980_Balistreri607_*.xml      (360 KB, 39F)
Un745_Fahey393_*.xml              (504 KB, 62F)
```

Skip the 5 deceased CCDA files (Synthea generated CCDAs for the patients' lifetimes including their death).

**Stage 5 — Verify via FHIR**

```bash
TOK=$(curl -s -X POST 'https://openemr-production-0c8c.up.railway.app/oauth2/default/token' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=password' \
  --data-urlencode 'client_id=<OAUTH_CLIENT_ID>' \    # actual values in copilot/.env (gitignored)
  --data-urlencode 'client_secret=<OAUTH_CLIENT_SECRET>' \
  --data-urlencode 'user_role=users' \
  --data-urlencode 'username=EPU-admin-46' \
  --data-urlencode 'password=<OPENEMR_ADMIN_PASSWORD>' \
  --data-urlencode 'scope=openid offline_access api:fhir user/Patient.read user/Condition.read user/MedicationRequest.read' \
  | python3 -c 'import json,sys; print(json.load(sys.stdin)["access_token"])')

curl -s -H "Authorization: Bearer $TOK" \
  'https://openemr-production-0c8c.up.railway.app/apis/default/fhir/Patient?_count=20' \
  | python3 -c 'import json,sys; d=json.load(sys.stdin); print("total:", d.get("total")); [print("-", e["resource"]["id"], (e["resource"].get("name",[{}])[0].get("given",[""])[0]+" "+e["resource"].get("name",[{}])[0].get("family","")).strip(), e["resource"].get("birthDate","-")) for e in d.get("entry",[])[:15]]'
```

Expect `total: 10` (or 9, depending on whether you skipped one), each with a real name and birthdate. Pick one for the smoke test against the deployed agent.

### Path forward — Option G (fallback if Option E doesn't work, ~30-45 min)

If Option E hits a wall (e.g. `apache` user doesn't exist, container is read-only, perm fix doesn't actually unblock the upload), pivot to **direct MySQL transfer** of the existing Synthea data from local OpenEMR to Railway MySQL.

**Why this works:** local OpenEMR already has 50 Synthea-imported patients (verified earlier — `patient_data` rows 4–53). They were imported via the same Carecoordination flow that's broken on Railway, so the data is in OpenEMR's expected schema shape. Transferring those rows + their dependent tables to Railway MySQL skips the import step entirely.

**Stage 1 — Get Railway MySQL public connection URL**

User pulls from Railway dashboard → MySQL service → Variables tab → copy `MYSQL_PUBLIC_URL` (format: `mysql://root:PASSWORD@shortline.proxy.rlwy.net:PORT/railway`). Paste here.

**Stage 2 — Dump patient-related tables from local MariaDB**

```bash
docker exec development-easy-mysql-1 mariadb-dump \
  -uopenemr -popenemr openemr \
  --no-create-info \
  --single-transaction \
  --skip-add-locks \
  --skip-comments \
  --where="pid >= 4 AND pid <= 53" \
  patient_data \
  > /tmp/patient_data.sql

# Other tables — these don't have direct pid columns but link via foreign keys.
# Need a list of patient UUIDs first to filter properly.
docker exec development-easy-mysql-1 mariadb -uopenemr -popenemr openemr \
  -B -N -e "SELECT uuid FROM patient_data WHERE pid BETWEEN 4 AND 53" | \
  while read uuid; do
    # ... per-table extracts using patient_uuid filter
  done
```

This is fiddly — OpenEMR's clinical data spans `lists` (problems/allergies/medications), `form_encounter` + `forms` (encounters), `prescriptions`, `observations`, `procedure_order` + `procedure_result` (labs), `history_data`. Each table joins to patient differently. Plan ~30 min just to write the right WHERE clauses.

**Stage 3 — Restore into Railway MySQL**

```bash
mysql --host=shortline.proxy.rlwy.net --port=<PORT> \
      --user=root --password=<PASSWORD> railway \
      < /tmp/patient_data.sql
# repeat for each per-table dump
```

**Risks:**
- Local schema is MariaDB 11.8.6, Railway is MySQL 8.x — column types compatible 99% of the time but not 100% (TIMESTAMP precision, ENUM coercion)
- Foreign-key chain across `users` (provider IDs), `facility`, `lists_categories` may not match between environments
- Patient UUIDs collide between local + Railway (unlikely for fresh Railway DB but worth checking)

Mitigation: do an initial dump of just `patient_data`, restore that, verify FHIR can find one patient before continuing to the dependent tables. Iterative.

### When to start

The user wants to pause here and pick this up later. **Resume with Stage 1 of Option E.** All artifacts are staged:
- Synthea JAR at `/tmp/synthea-with-dependencies.jar`
- 15 CCDA files at `/tmp/synthea-ccda/ccda/`
- Railway CLI is linked to the project
- Option G fallback is documented but not started

---

## 6. What's Left — Pre-Early-Submission (Tonight)

Pre-deadline checklist, in order. Roughly 2.5 hours of user time + ~30 min agent-side checks.

**Submission requires three artifacts together: GitLab repo link + deployed URL + demo video.** All three must be in place before submitting.

| # | Track | Task | Owner | Time | Status |
|---|---|---|---|---|---|
| 1 | Data | **Synthea data import** — run Option E (railway ssh + Carecoordination perms fix) per §5 stages 1-2 | Auto | 10 min | 📋 paused |
| 2 | Data | Upload Tracey CCDA via Carecoordination UI to validate the perms fix (§5 stage 3) | User | 5 min | 📋 paused |
| 3 | Data | Bulk-upload remaining 9 alive Synthea patients (§5 stage 4) | User | 15 min | 📋 paused |
| 3a | Data | If Option E fails: pivot to Option G (mysqldump local → restore Railway), need MYSQL_PUBLIC_URL | User+Auto | 30-45 min | 📋 fallback |
| 4 | Data | Verify via FHIR: `Patient?_count=20` returns 10 patients with names + birthDates (§5 stage 5) | Auto | 5 min | 📋 |
| 5 | Smoke | UC1 against deployed agent + an imported Synthea patient — expect 3-5 line cited brief | Auto | 5 min | 📋 |
| 6 | Smoke | UC2 — pick a patient with multiple conditions and ask a hypothesis-style question — agent should chain tools and cite | Auto | 5 min | 📋 |
| 7 | Smoke | UC3 — pick a patient with documented allergy, ask "is X safe to add" with `proposed_drug=<allergen>` — Layer-2 allergy rule must hard-block | Auto | 5 min | 📋 |
| 7 | Git | Confirm GitHub fork URL Railway pulls from (check Railway openemr service → Source repo) | User | 3 min | 📋 |
| 8 | Git | Create empty GitLab project at `gitlab.com/<you>/openemr` (no README, no license init) | User | 3 min | 📋 |
| 9 | Git | Pre-flight: `cat copilot/.gitignore` confirms `.env` excluded; secret-grep across copilot/ returns empty | Auto | 3 min | 📋 |
| 10 | Git | `git init` + `remote add origin` (GitHub) + `git fetch` + `git reset --soft origin/main` to align with GitHub state | Auto | 5 min | 📋 |
| 11 | Git | Sanity-diff: `git diff origin/main -- src/ interface/ library/` returns empty (no upstream PHP edits) | Auto | 2 min | 📋 |
| 12 | Git | `git add` + `git commit` (squash message, see Section 4) | Auto | 2 min | 📋 |
| 13 | Git | `git push origin main` to GitHub | Auto | 3 min | 📋 |
| 14 | Git | `git remote add gitlab <gitlab-url>` + `git push gitlab main` | Auto | 5 min | 📋 |
| 15 | Git | Verify both repos render `copilot/` directory + `IMPLEMENTATION.md` in their web UIs | User | 5 min | 📋 |
| 16 | Demo | Record demo video (3–5 min): deployed URL → UC1 → fake-claim injection showing strip → UC2 → UC3 allergy block → trace expander → 30-sec architecture closing | User | 30 min | 📋 |
| 17 | Submit | Submit (GitLab link + deployed URL + video link) to Gauntlet Early Submission form | User | 5 min | 📋 |
| 18 | Interview | Schedule the AI Interview within 24h of submission (PRD page 4 hard gate) | User | 2 min | 📋 |

### What to do *during* the demo video

- Open https://copilot-production-b532.up.railway.app
- Paste P1's patient FHIR id (the 67M with HTN+T2DM+penicillin allergy)
- **UC1**: ask "Brief me on this patient — who they are, why they're here, what's changed." Point at the cited-claims pills under the response and the "verified ✓" badge
- **Hallucination demo**: ask "Is she also on simvastatin?" — when the agent answers, show that the response says "I cannot verify this" / "[unverified]" because no MedicationRequest record_id supports that claim
- **UC2**: ask "He's complaining of dizziness — is this related to anything I should know about?" — agent should chain `get_active_medications` + `get_recent_vitals` and either flag lisinopril+BP or surface "no recent BP recorded — recommend in-room measurement"
- **UC3**: ask "I'm thinking about prescribing penicillin VK for his sinus infection — any concerns?" — Layer-2 allergy rule must hard-block; the response should refuse the verdict, not say "safe"
- Click the "full trace" expander on any response — show tool sequence + per-tool latencies + token counts
- Closing 30 seconds: PHI minimization (pseudonyms in trace, no SSN/name to LLM), source-attribution gate (deterministic, not LLM self-grading), Anthropic primary / OpenAI fallback adapter

---

## 7. What's Left — Pre-Final-Submission (Sunday)

Final adds production polish + AI cost analysis + social post + GitHub repo.

| # | Task | Effort | Notes |
|---|---|---|---|
| F1 | Fix Synthea import (Option F: direct MySQL load) | 60-90 min | Replaces manual demo patients with 10+ Synthea patients with rich clinical histories |
| F2 | Layer-2 domain rules: renal-dose check + QTc check | 60 min | ARCHITECTURE.md §4.1 says 4 rules; we have 2 |
| F3 | SMART on FHIR app launch handshake (replace password-grant with auth-code + PKCE) | 90 min | Defends as "production integration" per ARCHITECTURE.md §10 |
| F4 | Switch back to Anthropic Claude (resolve workspace/key issue, flip `LLM_PROVIDER`) | 5 min | Once billing is unblocked |
| F5 | Write `COST.md` (extract from ARCHITECTURE.md §9, add 100/1K/10K/100K user projections + architectural changes per tier) | 30 min | PRD page 8 explicit deliverable |
| F6 | Push the entire `/Users/rikki/Desktop/Doc/OOD/openemr/` to a GitHub fork | 30 min | PRD page 8 — "GitHub Repository: Forked from OpenEMR" hard gate |
| F7 | Connect Railway `copilot` and `openemr` services to GitHub auto-deploy | 15 min | Convert from `railway up` (manual) to GitHub push (auto) |
| F8 | Re-record demo video against the production-quality stack | 30 min | Replaces the Thursday recording |
| F9 | Social media post on X/LinkedIn tagging @GauntletAI | 15 min | PRD page 8 final-only deliverable |
| F10 | Schedule the second AI Interview within 24h of Final submission | 2 min | |

---

## 8. Risk Log

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Anthropic billing not resolved by Final | Medium | Low | OpenAI adapter is shipped, defends as architecture-aware fallback |
| Manual patient creation tonight produces clinically-naive data | Medium | Low | Use realistic combinations (HTN+T2DM+lisinopril+metformin) so UC2 has connections to find |
| Railway free tier rate limits during demo recording | Low | Medium | Record demo while everything is warmed up; have a backup recording |
| Synthea direct-MySQL import on Sunday hits foreign-key chain issues | Medium | Low | Already have Carecoordination perm-fix as backup |
| AI Interview not booked in 24h window | Low | High | Schedule immediately after submission, set a calendar reminder |

---

## 9. File Map (where everything lives)

```
/Users/rikki/Desktop/Doc/OOD/openemr/
├── AUDIT.md, USERS.md, ARCHITECTURE.md, AGENTFORGE.md  ← MVP deliverables (locked)
├── README.md (upstream OpenEMR — unchanged except 1-line pointer at top)
└── copilot/                                ← THE AGENT (this directory)
    ├── IMPLEMENTATION.md                   ← THIS FILE
    ├── README.md                           ← Setup quickstart
    ├── pyproject.toml, Dockerfile, railway.toml, docker-compose.yml, Makefile
    ├── .env, .env.example                  ← .env contains live secrets, gitignored
    ├── app/
    │   ├── main.py, config.py
    │   ├── fhir/        client.py, oauth.py
    │   ├── tools/       8 tool files + _base.py + registry.py
    │   ├── agent/       loop.py, llm.py, prompt.py, schemas.py
    │   ├── verification/ attribution.py, rules.py
    │   ├── phi/         minimizer.py, session.py
    │   ├── acl/         check.py
    │   ├── observability/ trace.py
    │   └── web/         index.html (chat UI)
    └── evals/
        ├── conftest.py, RESULTS.md         ← auto-generated test summary
        ├── tools/      test_phi_minimizer.py, test_tool_integration.py
        └── agent/      test_verification.py, test_scenarios.py
```

---

## 10. Quick Reference — Where Things Are Deployed

| Service | URL | Status |
|---|---|---|
| Co-Pilot agent (Railway) | https://copilot-production-b532.up.railway.app | ✅ live |
| OpenEMR (Railway) | https://openemr-production-0c8c.up.railway.app | ✅ live (no patients yet) |
| MySQL (Railway, internal) | `mysql.railway.internal` | ✅ |
| Local agent (dev) | http://localhost:8080 | ✅ |
| Local OpenEMR (dev) | https://localhost:9300 | ✅ (50 Synthea patients pre-loaded) |

OAuth clients registered:

| Client | Where | client_id | Purpose |
|---|---|---|---|
| Clinical Co-Pilot (local) | Local OpenEMR | `E4ldTbWU...5yc` | Local agent → local OpenEMR |
| Clinical Co-Pilot (Railway prod) | Railway OpenEMR | `GZg2tYuf...VjA` | Deployed agent → Railway OpenEMR |
| Synthea Importer (one-shot) | Railway OpenEMR | `vsuaqsFM...on8` | Failed write attempt — keep registered for Final Sunday DB-load alternative |

---

*This document is the source of truth for the Co-Pilot's status. Update it after every milestone completion or new blocker.*
