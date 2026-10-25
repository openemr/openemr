# General Dermatology (OpenEMR encounter form)

Custom SOAP + procedure documentation form for dermatology encounters.
Modernized for **OpenEMR 8.5** (PHP 8.5) from the H.E. Project 7.0.2 form.

## Quick links

- Dev stack + migration steps:  
  [`docker/development-easy-light/README-openemr85-derm.md`](../../docker/development-easy-light/README-openemr85-derm.md)
- Install schema: `table.sql`
- Schema upgrades: `sql/2_0_0-to-2_1_0_upgrade.sql`, `sql/upgrade_schema.sql`
- Location catalog: `resources/locations.php`
- Unit tests: `phpunit.xml` + `src/Tests/`

## Install (8.5)

1. Place this directory at `interface/forms/general_dermatology`.
2. `composer install` in the form directory (or inside the openemr85 container).
3. Apply `table.sql` (and upgrade scripts if upgrading an existing HE database).
4. Register in **Administration → Forms** / `registry` as directory `general_dermatology`.

## Architecture (phases 1–4)

| Layer | Location |
|-------|----------|
| Entry | `new.php`, `save.php`, `view.php`, `report.php`, `printable.php`, `templates.php` |
| Domain | `src/Domain/TreatmentDocument.php`, `Finding.php` |
| Catalogs | `src/Catalog/ProcedureCatalog.php`, `LocationCatalog.php` |
| Security | `src/Support/FormContext.php`, `NoteAccessService.php`, `ApiRequestGuard.php` |
| UI | `views/dermatology_main_template.twig`, `views/js/image_mapster_block.js` |
| Maps | `views/map_code.html`, `public/images/body-map.jpg`, `face-map.png` |

Treatment payload is **schema version 2**: versioned JSON with `findings[]` and
structured `location` (`id`, `label`, `laterality`, `view`, `markers`).

Map clicks write catalog IDs and normalized markers into `location_json` on each
finding card; save normalizes via `TreatmentDocument`. Reload restores map
highlights from saved findings.

## Tests

```bash
# From OpenEMR root inside openemr85 container:
vendor/bin/phpunit -c interface/forms/general_dermatology/phpunit.xml
```

## PDF

`printable.php` builds the clinical PDF. Form-local mPDF (`composer.json`) is
currently required for PHP 8.5; longer-term preference is core OpenEMR mPDF.

Query parameters commonly used: `formid`, encounter/visit context from session.
