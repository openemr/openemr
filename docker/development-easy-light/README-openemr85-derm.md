# OpenEMR 8.5 — General Dermatology migration stack

Dedicated Docker project for modernizing the custom `general_dermatology`
encounter form against OpenEMR **8.5** (PHP 8.5). It is intentionally separate
from any other local stack (for example Tarevo on 8300/9300).

**Do not replace core OpenEMR with a full H.E. 7.0.2 tree.** Only the form
directory is ported and refactored in place under:

`interface/forms/general_dermatology`

## Ports and URLs

| Service | Host port | URL |
|---------|-----------|-----|
| App HTTP | 8400 | http://localhost:8400/ |
| App HTTPS | 9400 | https://localhost:9400/ |
| phpMyAdmin | 8410 | http://localhost:8410/ |
| MySQL | 8420 | localhost:8420 (DB credentials from flex image defaults) |

Default flex image login: `admin` / `pass`.

## Start / stop

```bash
cd docker/development-easy-light
cp -n .env.openemr85.example .env.openemr85
# Edit OPENEMR_DIR to your absolute openemr checkout path.

docker compose --env-file .env.openemr85 -p openemr85 up -d
docker compose -p openemr85 ps
docker compose -p openemr85 down   # when finished
```

Leave other compose projects alone so port and volume mounts do not collide.

## Migration steps (H.E. 7.0.2 → OpenEMR 8.5)

### 1. Bring up OpenEMR 8.5 only

1. Use this repo’s `docker/development-easy-light` compose with `.env.openemr85`.
2. Confirm a clean 8.5 install boots, login works, and encounters can be opened.
3. Do **not** mount or overwrite core with H.E.Project_v3.

### 2. Copy only the custom form

From the H.E. 7.0.2 reference tree:

```bash
# Example paths — adjust to your machines.
SRC=/path/to/H.E.Project_v3/interface/forms/general_dermatology
DST=/path/to/openemr/interface/forms/general_dermatology

rsync -a --exclude vendor --exclude tmp --exclude .phpunit.cache "$SRC/" "$DST/"
```

Install PHP dependencies **inside** the 8.5 container (form-local Composer):

```bash
docker exec -w /var/www/localhost/htdocs/openemr/interface/forms/general_dermatology \
  openemr85-openemr-1 composer install --no-interaction
```

Form-local mPDF was bumped to **8.3.1** for PHP 8.5 compatibility. Prefer
core OpenEMR mPDF in a later cleanup once PDF generation is re-homed.

### 3. Create tables and register the form

Apply install SQL (or run the form installer path your site uses):

- `interface/forms/general_dermatology/table.sql` — creates `general_dermatology`
  and legacy `form_general_dermatology` if missing.
- Ensure a `registry` row exists:
  - **name:** General Dermatology
  - **directory:** `general_dermatology`
  - **state:** `1` (active)

Phase 2 schema alignment (nullable `pid` for templates, indexes) lives in:

- `interface/forms/general_dermatology/sql/2_0_0-to-2_1_0_upgrade.sql`
- `interface/forms/general_dermatology/sql/upgrade_schema.sql`

Apply those scripts on existing databases that already had the HE form tables.

### 4. Structural modernization phases (completed on this branch)

| Phase | Focus | Outcome |
|-------|--------|---------|
| **1** | Security / integrity | `FormContext`, `NoteAccessService`, `ApiRequestGuard`; ownership on load/save/PDF; session-typed CSRF; claim key normalize; QueryUtils/Twig charge keys |
| **2** | Treatment document | Versioned JSON v2 (`schema_version` + `findings`); `TreatmentDocument`; repository; nullable `pid` + indexes; dual-table fallback removed from treatment save path |
| **3** | Catalogs + DTO | `ProcedureCatalog`, `LocationCatalog` + `resources/locations.php`; `Finding` DTO; QueryUtils sweep; PDF via `OEGlobalsBag`; report via Finding/TreatmentDocument |
| **4** | Map ↔ treatment | Map region IDs/markers into treatment `location` objects; JS bootstrap of catalog + saved findings; restore ImageMapster highlights on load |

OpenEMR 8.5 still ships Bootstrap **4.6.x** — do not force Bootstrap 5 on this form.

### 5. Verify

Form-local PHPUnit (no DB required for unit suite):

```bash
docker exec -w /var/www/localhost/htdocs/openemr openemr85-openemr-1 \
  vendor/bin/phpunit -c interface/forms/general_dermatology/phpunit.xml
```

Manual smoke (logged-in UI):

1. Create/open an encounter → add **General Dermatology**.
2. Click body/face map regions → finding cards show location text and hidden
   `location_json` (id, laterality, view, markers).
3. Save and reopen → map highlights restore; treatment JSON is schema v2.
4. Generate PDF via printable path if used clinically.

### 6. Production cutover checklist

1. Backup DB and `sites/*/documents`.
2. Deploy form directory **without** committing `vendor/` (run `composer install` on target).
3. Run `table.sql` / upgrade SQL as needed for the target schema version.
4. Confirm registry entry and ACL/category placement for the form.
5. Spot-check save/load/PDF and billing charge attachment on a non-prod clone first.
6. Keep HE 7.0.2 available as read-only reference until clinical sign-off.

## Treatment JSON v2 (storage)

```json
{
  "schema_version": 2,
  "findings": [
    {
      "finding_location": "Left Alar Crease",
      "location": {
        "id": "left_alar_crease",
        "label": "Left Alar Crease",
        "laterality": "L",
        "view": "face",
        "markers": [{ "x": 0.41, "y": 0.52 }]
      },
      "dx_s": "…",
      "procedure_plans": "…"
    }
  ]
}
```

Legacy v1 bare arrays are migrated on read by `TreatmentDocument::fromStorage()`.

## Branch / layout

- Branch: `feat/general-dermatology-8.5-phase1` (phases 1–4 land here).
- Form path: `interface/forms/general_dermatology`
- HE reference (not mounted into 8.5): `H.E.Project_v3/interface/forms/general_dermatology`

## Still open (post–Phase 4)

- Logged-in E2E / clinical golden fixtures
- Full Twig `xlt` migration
- SVG map rewrite and map HTML dedupe
- Drop legacy top-level `areas_*` once procedure maps use location IDs
- Prefer core mPDF over form-local vendor
