# eCQM measure bundle builder

This Docker project converts CMS Eligible Clinician eCQM measure packages into
the JSON measure bundles used by OpenEMR's PHP eCQM engine. It also generates
the SQL that registers a performance period's measures in OpenEMR.

The builder is intentionally separate from the generated measure data.
OpenEMR continues to install bundles from the `openemr/oe-cqm-parsers` Composer
package; this directory contains only the tool. Do not commit CMS packages,
generated JSON, generated SQL, or your VSAC API key here.

## Requirements

- Docker with Compose v2.24 or later.
- A free [UMLS account](https://uts.nlm.nih.gov/uts/signup-login) and its API
  key from the [UTS profile](https://uts.nlm.nih.gov/uts/profile).
- The CMS Eligible Clinician eCQM zip packages for the performance period,
  downloaded from the [eCQI Resource Center](https://ecqi.healthit.gov/).

## Build a performance period

1. Check out the bundle package repository used by OpenEMR and copy
   `.env.sample` to `.env` in this builder directory. Set `VSAC_API_KEY` and
   `BUNDLE_HOST_DIR` to the absolute path of the bundle checkout.
2. Put the downloaded CMS zip packages under
   `{year}_reporting_period/cms_measures/` in that checkout.
3. From this directory, build the measures:

   ```bash
   docker compose run --rm builder ruby build_bundle.rb --year 2026
   ```

The script expands each package's value sets from VSAC and writes the measure
JSON and `value_sets.json` to
`{year}_reporting_period/json_measures/{cms_id}/`. Directory names always use
the measure's `cms_id`, even when the CMS package filename has a different
format. Already-built measures are skipped, so another run retries failures;
use `--rebuild` to rebuild all packages. Failures are printed and recorded in
a timestamped `failed_measures_*.log` file. A run with missing packages fails
instead of reporting an empty successful build.

Value-set expansion uses the eCQM Annual Update released in the year before
the performance period:

| Performance period | VSAC profile |
| --- | --- |
| 2026 | eCQM Update 2025-05-08 |
| 2025 | eCQM Update 2024-05-02 |
| 2024 | eCQM Update 2023-05-04 |
| 2023 | eCQM Update 2022-05-05 |
| 2022 | eCQM Update 2021-05-06 |

The profile is printed for every build. For a period not in this table, pass
the correct `--profile` explicitly.

To correct existing output directory names without contacting VSAC:

```bash
docker compose run --rm builder ruby build_bundle.rb --year 2026 --rename-existing
```

## Generate the measure list SQL

After building the bundles, generate the list used by OpenEMR's QRDA screens:

```bash
docker compose run --rm builder ruby generate_ecqm_list.rb --year 2026
```

The generated `ecqm_{year}_reporting.sql` is sorted deterministically and
guarded with `#IfNotRow`, so it can be included in an upgrade script. The
default active measures match the existing certified list; pass
`--defaults CMS122v,CMS165v` to choose a different set of CMS ID prefixes.
Review the SQL and add it to OpenEMR's `sql/database.sql` and the current
upgrade script only when releasing that performance period.

## Annual OpenEMR release steps

The builder's output does not change the bundles OpenEMR currently installs.
For a new performance period:

1. Build the bundle in the Composer package checkout, verify the failures log
   is empty, then commit and publish that bundle.
2. In a separate OpenEMR change, pin the package's new commit and add the
   generated measure list SQL.
3. Verify the PHP engine and parity results:

   ```bash
   vendor/bin/phpunit -c phpunit-isolated.xml tests/Tests/Isolated/Services/Qdm/MeasureCalculationMethodTest.php
   vendor/bin/phpunit -c phpunit-isolated.xml tests/Tests/Isolated/Cqm/Parity/PhpEngineParityTest.php
   OPENEMR_ENABLE_CQM_TEST_PATIENTS=1 php bin/console openemr:cqm-load-test-patients --year=2026 --verify
   ```

   The Cypress-generated QRDA fixtures and their expected population counts
   are documented in [tests/Tests/ECQM/README](../../tests/Tests/ECQM/README).
   Run that suite against an installed test database with
   `vendor/bin/phpunit -c phpunit.xml --testsuite ECQM`.

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for OpenEMR development guidance.
