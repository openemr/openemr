# Disposable native HL7 receiver tests

This suite calls the actual `receive_hl7_results()` implementation with a full OpenEMR schema, globals, writable Symfony session, ADODB/QueryUtils and the independent production audit connection. All patients and messages are synthetic. It complements the automatically discovered isolated `Hl7PlacerOrderIdTest`.

The suite is deliberately opt-in rather than part of the general integration database: it creates fault-injection triggers, temporarily renames two tables, and kills its own main database connection at COMMIT. The bootstrap rejects any database except `hl7_fixture` on `127.0.0.1:13387`; fixture ownership is checked before cleanup. Never point this suite at a clinical or shared development database.

## Prepare a separate local installation

Use a separate checkout with its Composer dependencies and an **empty**, dedicated MariaDB data directory. The following example uses disposable local credentials, listens only on loopback, and does not start the system MariaDB service:

```sh
fixture_dir="$HOME/.cache/openemr-hl7-fixture"
mkdir -p "$fixture_dir"
chmod 700 "$fixture_dir"
# Run the initialization only for a new, empty data directory.
mariadb-install-db --no-defaults --datadir="$fixture_dir" --auth-root-authentication-method=normal --skip-test-db
mariadbd --no-defaults --datadir="$fixture_dir" --socket="$fixture_dir/server.sock" --pid-file="$fixture_dir/server.pid" --log-error="$fixture_dir/server.log" --bind-address=127.0.0.1 --port=13387 &
# Wait until mariadb-admin --socket="$fixture_dir/server.sock" -u root ping succeeds.
OPENEMR_ENABLE_INSTALLER_AUTO=1 php contrib/util/installScripts/InstallerAuto.php server=127.0.0.1 port=13387 loginhost=localhost root=root rootpass=BLANK login=hl7_fixture pass=hl7_fixture_only dbname=hl7_fixture site=default iuser=hl7_fixture_admin iuname=Fixture iuserpass=Hl7FixtureOnly-2026 igroup=Fixture
```

The installer imports the production schema and initializes settings, GACL and a synthetic administrator. Its schema import is destructive to the selected database; confirm that the server/data directory and `hl7_fixture` are dedicated and empty before running it. Verify `sites/default/sqlconf.php` has `config=1` and matches the guarded database and port. The fixture account needs `TRIGGER` privileges; the COMMIT-disconnect case uses the dedicated server's local root account to kill only the test's captured connection ID.

## Run

```sh
php vendor/bin/phpunit -c phpunit-isolated.xml tests/Tests/Isolated/Common/Orders/Hl7PlacerOrderIdTest.php
php vendor/bin/phpunit -c tests/Tests/Integration/Hl7Receiver/phpunit.xml
mariadb-admin --socket="$fixture_dir/server.sock" -u root shutdown
```

On the unpatched receiver, `--filter 'testCompound|testEi'` produces one expected failure: the compound identifier selects order 11545596 instead of 175. The plain EI namespace test is already correct in upstream; it guards against taking a suffix from an authority component.

The full suite exercises placement, conflict rejection, dry-run purity, reflex insertion, late rejection after successful preflight, SQL read/write failures, caller-owned transaction preservation, independent audit persistence, and a real server-side connection kill just before COMMIT. The fault hook delegates normal statements and the failed COMMIT to the real driver. No SQL result or production class is mocked.

File/CouchDB/remote document effects are outside the clinical database transaction. The suite uses numeric OBX results, not document attachments. It does not establish clinical effectiveness or resolve the ambiguous outcome when the server accepted COMMIT but its response was lost; the importer must not automatically replay that case.

Local validation: PHP 8.4.26, PHPUnit 11.5.56, MariaDB 12.3.2; 15 native tests and 31 isolated identifier cases passed. The full repository test matrix was not run locally.
