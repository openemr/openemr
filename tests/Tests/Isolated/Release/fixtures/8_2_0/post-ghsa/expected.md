## [8.2.0](https://github.com/openemr/openemr/compare/v8_1_0...v8_2_0) - 2026-07-08

### Security Fixes

  - [High] OpenEMR FaxSMS module: insecure staging of decrypted patient documents in webroot (CWE-552/CWE-200) ([GHSA-vv5j-6gjw-ffx9](https://github.com/openemr/openemr/security/advisories/GHSA-vv5j-6gjw-ffx9))

### Fixed

  - add php-posix to the flex image apk install list ([#12548](https://github.com/openemr/openemr/pull/12548))
  - allow save for timed In/Out of Office events ([#12444](https://github.com/openemr/openemr/pull/12444))
  - backtick `rank` column in ContactTelecomService for MySQL 8+ compat ([#12329](https://github.com/openemr/openemr/pull/12329))
  - backtick bare `system` in ContactTelecomService for MySQL 8+ compat ([#12340](https://github.com/openemr/openemr/pull/12340))
  - bypass max_input_vars truncation in edit_layout and edit_payment ([#12170](https://github.com/openemr/openemr/pull/12170))
  - CCDA to pass ONC USCDI V3 test scenarios. ([#12815](https://github.com/openemr/openemr/pull/12815))
  - checkout repo before flex build to dodge submodule recursion ([#12547](https://github.com/openemr/openemr/pull/12547))
  - clear phone_numbers before re-insert in legacy persist + cleanup migration ([#12538](https://github.com/openemr/openemr/pull/12538))
  - correct deleteItem target and readMessage issue ([#12144](https://github.com/openemr/openemr/pull/12144))
  - dispatch PatientCreatedEvent from legacy new_patient_save.php ([#12083](https://github.com/openemr/openemr/pull/12083))
  - docker-validate-release-targets reads master version.php from PR checkout ([#12597](https://github.com/openemr/openemr/pull/12597))
  - don't run sql_upgrade as root in Inferno test ([#12472](https://github.com/openemr/openemr/pull/12472))
  - drop redundant categories_seq UPDATE in eye form laser migration ([#12011](https://github.com/openemr/openemr/pull/12011))
  - expired password warning not displaying ([#12473](https://github.com/openemr/openemr/pull/12473))
  - Fix ethnicity decline setting ([#12189](https://github.com/openemr/openemr/pull/12189))
  - Fix invalid property access in gacl ([#12653](https://github.com/openemr/openemr/pull/12653))
  - handle table-qualified columns in escape_sql_column_name() ([#12019](https://github.com/openemr/openemr/pull/12019))
  - Harden dependencies by pulling in security meta-package ([#12524](https://github.com/openemr/openemr/pull/12524))
  - irp imports 0 patients because synthea output is in root-only /root/ ([#12632](https://github.com/openemr/openemr/pull/12632))
  - make generated Observation value\[x\] PHPDoc nullable ([#12399](https://github.com/openemr/openemr/pull/12399))
  - Move module loading after OPENEMR_GLOBALS_LOADED ([#12691](https://github.com/openemr/openemr/pull/12691))
  - normalize categories_seq instead of unsafe multi-row UP… ([#12194](https://github.com/openemr/openemr/pull/12194))
  - Numerous bug fixes ([#12400](https://github.com/openemr/openemr/pull/12400))
  - only require release-targets.yml in master context for byte-identical canary ([#12585](https://github.com/openemr/openemr/pull/12585))
  - prevent PHP 8 TypeError in Claim::payerCount() and procCount() ([#12525](https://github.com/openemr/openemr/pull/12525))
  - prevent {headerTemplate} from rendering the page header twice ([#12821](https://github.com/openemr/openemr/pull/12821))
  - repair trailing comma in Pain_Initial_Assessment.json ([#12570](https://github.com/openemr/openemr/pull/12570))
  - Restore Inferno after docker rearranging ([#12604](https://github.com/openemr/openemr/pull/12604))
  - Restore windows webserver_root normalization. ([#12140](https://github.com/openemr/openemr/pull/12140))
  - Run additional web workers in API integration tests ([#11959](https://github.com/openemr/openemr/pull/11959))
  - skip phones the legacy parts schema cannot represent (insurance + pharmacy) ([#12529](https://github.com/openemr/openemr/pull/12529))
  - stop forcing E_ALL error reporting and add reporting options ([#12273](https://github.com/openemr/openemr/pull/12273))
  - sync-byte-identical handles rename + removed-from-config ([#12594](https://github.com/openemr/openemr/pull/12594))
  - use filesystem-targeted encryption for cached auth ([#12050](https://github.com/openemr/openemr/pull/12050))
  - use spec-compliant `invalid_request` error code (remove stray space) ([#12327](https://github.com/openemr/openemr/pull/12327))

#### ASTP/ONC Certification

  - add NPI to user to qualify as Practitioner in Inferno tests ([#11916](https://github.com/openemr/openemr/pull/11916))
  - correct Inferno test group IDs for body height/weight ([#11917](https://github.com/openemr/openemr/pull/11917))
  - Inferno testsuite setup fixes ([#11897](https://github.com/openemr/openemr/pull/11897))
  - Turn off redis persistence in inferno tests ([#11909](https://github.com/openemr/openemr/pull/11909))

#### Authentication

  - remove unused redirect_token from OneTimeAuth ([#11972](https://github.com/openemr/openemr/pull/11972))
  - restore brief-lock pattern on long-running pages ([#11953](https://github.com/openemr/openemr/pull/11953))
  - restore default tab loading after login ([#11947](https://github.com/openemr/openemr/pull/11947))
  - stop rotating CSRF private key on every main_screen.php load ([#11888](https://github.com/openemr/openemr/pull/11888))

#### Backend Modernization Project

  - default login_page_layout when globals row is missing ([#11949](https://github.com/openemr/openemr/pull/11949))
  - drain file-not-found baseline entries ([#11802](https://github.com/openemr/openemr/pull/11802))
  - drain function-not-found baseline entries ([#11813](https://github.com/openemr/openemr/pull/11813))
  - drain PHPStan class.notFound baseline for portal/patient ([#11877](https://github.com/openemr/openemr/pull/11877))
  - isolate run-all-due behind subprocess boundary ([#11801](https://github.com/openemr/openemr/pull/11801))
  - repair legacy parse errors across the codebase ([#11904](https://github.com/openemr/openemr/pull/11904))
  - replace die() with exception for missing session site_id ([#11618](https://github.com/openemr/openemr/pull/11618))

#### Calendar

  - restore appointments hidden after 8.0→8.1 upgrade ([#12519](https://github.com/openemr/openemr/pull/12519))

#### Database Migrations & Schema Changes

  - convert declne_to_specfy in patient_data language and ethnicity ([#11876](https://github.com/openemr/openemr/pull/11876))
  - Fix SQL upgrade syntax ([#11866](https://github.com/openemr/openemr/pull/11866))
  - Log all "helpfuldie" sql errors ([#11864](https://github.com/openemr/openemr/pull/11864))

#### DevOps

  - Allow sql_upgrade to work on the cli ([#11906](https://github.com/openemr/openemr/pull/11906))
  - simplify Codecov flags to fix "Multiple flags detected" error ([#11547](https://github.com/openemr/openemr/pull/11547))

#### Hardening

  - clean up callers that re-open read_and_close session ([#11940](https://github.com/openemr/openemr/pull/11940))
  - guard undefined keys and legacy PHP warnings flagged in production logs ([#11939](https://github.com/openemr/openemr/pull/11939))

#### Module Support

  - lazy-init AppDispatch session to survive static factory path ([#12224](https://github.com/openemr/openemr/pull/12224))
  - surface notification background-task failures ([#11846](https://github.com/openemr/openemr/pull/11846))

#### PHP

  - clinicians can edit medications ([#8087](https://github.com/openemr/openemr/pull/8087))
  - resolve PHP CLI binary via PhpExecutableFinder ([#11948](https://github.com/openemr/openemr/pull/11948))
  - resolve PHP deprecation warnings and undefined variable errors ([#11369](https://github.com/openemr/openemr/pull/11369))
  - use getBoolean for inhouse_pharmacy check in visit summary ([#11987](https://github.com/openemr/openemr/pull/11987))

#### Patient Portal

  - add CSRF protection to payment handler ([#11958](https://github.com/openemr/openemr/pull/11958))

#### Security

  - add CSRF check + tighten input on fee_sheet review/justify ([#12031](https://github.com/openemr/openemr/pull/12031))
  - physical_exam edit_diagnoses ACL gate is dead code ([#12018](https://github.com/openemr/openemr/pull/12018))
  - validate db parameter in standard_tables_manage ([#11951](https://github.com/openemr/openemr/pull/11951))

#### UI/UX

  - disable patient birthday alert in e2e compose stack ([#11983](https://github.com/openemr/openemr/pull/11983))
  - Fix crash from uncaught class in birthday popup ([#11999](https://github.com/openemr/openemr/pull/11999))

#### billing & payments

  - block negative or zero patient payment amounts ([#10989](https://github.com/openemr/openemr/pull/10989))
  - cast 835 monetary fields to float for type-strict comparisons ([#11868](https://github.com/openemr/openemr/pull/11868))
  - edit payment sql error ([#12353](https://github.com/openemr/openemr/pull/12353))
  - move use statement out of docblock ([#12223](https://github.com/openemr/openemr/pull/12223))
  - session crashes on posting page ([#11691](https://github.com/openemr/openemr/pull/11691))

#### communications

  - catch up missed appointment-reminder ticks ([#11907](https://github.com/openemr/openemr/pull/11907))
  - require appointments lib; log background-service errors ([#11922](https://github.com/openemr/openemr/pull/11922))
  - tighten oe_faxsms_queue schema for utf8mb4 compatibility ([#11962](https://github.com/openemr/openemr/pull/11962))

#### e-Prescribe

  - set linkMethod for Ensora eRx prescription button ([#11882](https://github.com/openemr/openemr/pull/11882))

#### encounter

  - correct i18formatting asset name typo ([#11937](https://github.com/openemr/openemr/pull/11937))
  - drop duplicate growth-chart buttons from Vitals History ([#12007](https://github.com/openemr/openemr/pull/12007))
  - growth chart fatals on missing patient sex; drain variable.undefined ([#11976](https://github.com/openemr/openemr/pull/11976))
  - handle missing row and null uuid in encounter view form ([#11883](https://github.com/openemr/openemr/pull/11883))
  - unify blank-row template through foreach ([#11985](https://github.com/openemr/openemr/pull/11985))

#### javascript

  - sync package-lock.json with xmldom 0.9.10 update ([#11803](https://github.com/openemr/openemr/pull/11803))

#### practice settings

  - save admin practice practice settings ([#11807](https://github.com/openemr/openemr/pull/11807))

#### testing

  - capture diagnostics for user-add modal timeout flake ([#11858](https://github.com/openemr/openemr/pull/11858))

### Added

  - add byte-identical canary + config to FILES_ALL with context-aware diff logic ([#12580](https://github.com/openemr/openemr/pull/12580))
  - Add DBAL-based key storage and extract loadWithEngines ([#12093](https://github.com/openemr/openemr/pull/12093))
  - add encryption status check methods and conditional database encryption ([#12096](https://github.com/openemr/openemr/pull/12096))
  - add fsupgrade-11.sh for 8.1.1 release (master sync) ([#12609](https://github.com/openemr/openemr/pull/12609))
  - add input validation for numeric fields ([#12163](https://github.com/openemr/openemr/pull/12163))
  - Add REPL tool for dev envs ([#12539](https://github.com/openemr/openemr/pull/12539))
  - Add view only mode to portal appointments ([#12822](https://github.com/openemr/openemr/pull/12822))
  - auto-merge dependabot docker-compose updates ([#12035](https://github.com/openemr/openemr/pull/12035))
  - auto-sync byte-identical files from master to rel branches ([#12577](https://github.com/openemr/openemr/pull/12577))
  - client-side phone/fax validation on insurance + pharmacy edit forms ([#12534](https://github.com/openemr/openemr/pull/12534))
  - declare HOST_UID/HOST_GID env in dev compose files ([#12647](https://github.com/openemr/openemr/pull/12647))
  - declare HOST_UID/HOST_GID env in insane dev compose ([#12651](https://github.com/openemr/openemr/pull/12651))
  - encrypt unassigned inbound faxes at rest ([#12484](https://github.com/openemr/openemr/pull/12484))
  - extract (b)(10) EHI table set to b10-tables.yml ([#12397](https://github.com/openemr/openemr/pull/12397))
  - honor HOST_UID/HOST_GID for apache uid alignment ([#12642](https://github.com/openemr/openemr/pull/12642))
  - Modernized DBAL/ORM service path for code type syncing ([#12603](https://github.com/openemr/openemr/pull/12603))
  - refuse to run OpenEMR CLI as root ([#12267](https://github.com/openemr/openemr/pull/12267))
  - SqlReservedWordRule + automated MySQL/MariaDB drift detection ([#12335](https://github.com/openemr/openemr/pull/12335))
  - Symfony-routing strangler seam for zend_modules ([#12481](https://github.com/openemr/openemr/pull/12481))

#### Authentication

  - add audit logging for failed TOTP, U2F, and OAuth2 MFA attempts ([#11912](https://github.com/openemr/openemr/pull/11912))

#### Backend Modernization Project

  - add CacheDirectory for secure cache paths ([#11797](https://github.com/openemr/openemr/pull/11797))
  - add database encryption opt-out setting ([#11973](https://github.com/openemr/openemr/pull/11973))
  - add encryptForDatabase and decryptFromDatabase methods ([#11946](https://github.com/openemr/openemr/pull/11946))
  - Refactor filesystem encryption ([#12000](https://github.com/openemr/openemr/pull/12000))

#### DevOps

  - add API enablement flags to install command ([#11830](https://github.com/openemr/openemr/pull/11830))

#### billing & payments

  - install ClaimRev Connect module as Composer dependency ([#11265](https://github.com/openemr/openemr/pull/11265))

#### encounter

  - show lock icon for signed/locked encounters ([#8220](https://github.com/openemr/openemr/pull/8220))

#### patient admin

  - add email_direct validation and native email input type (#10866) ([#10904](https://github.com/openemr/openemr/pull/10904))

### Changed

  - add apache_{82,85}_118_upgrade variants exercising 5.0.0→current sql_upgrade chain ([#12210](https://github.com/openemr/openemr/pull/12210))
  - add ext-redis to composer config.platform ([#12381](https://github.com/openemr/openemr/pull/12381))
  - add integration test for CcdaGenerator::socket_get ([#12471](https://github.com/openemr/openemr/pull/12471))
  - add isolated tests for Claim::procCount and payerCount ([#12530](https://github.com/openemr/openemr/pull/12530))
  - Add multi-sink destination ([#12655](https://github.com/openemr/openemr/pull/12655))
  - add reusable + caller workflow for PR reviews ([#12700](https://github.com/openemr/openemr/pull/12700))
  - Adopt DTO for settings & tidy cookie path ([#12689](https://github.com/openemr/openemr/pull/12689))
  - align site_addr_oath with baseUrl in GroupExportFhirApiTest setUp ([#12259](https://github.com/openemr/openemr/pull/12259))
  - allow-list GitHub comment tools + surface transcript ([#12704](https://github.com/openemr/openemr/pull/12704))
  - also ignore codecov/patch in All Checks Passed aggregate ([#12341](https://github.com/openemr/openemr/pull/12341))
  - apply pretty-format-yaml across repo-owned configs ([#12571](https://github.com/openemr/openemr/pull/12571))
  - authenticate Composer with GITHUB_TOKEN to dodge API throttle ([#12658](https://github.com/openemr/openemr/pull/12658))
  - authenticate to Docker Hub before image pulls to lift rate limits ([#12431](https://github.com/openemr/openemr/pull/12431))
  - broaden workflow to validate every upstream hook on --all-files ([#12574](https://github.com/openemr/openemr/pull/12574))
  - bump actions/cache from 5 to 6 ([#12663](https://github.com/openemr/openemr/pull/12663))
  - bump actions/checkout from 6 to 7 ([#12558](https://github.com/openemr/openemr/pull/12558))
  - bump actions/create-github-app-token from 1 to 3 ([#12040](https://github.com/openemr/openemr/pull/12040))
  - bump actions/download-artifact from 7 to 8 ([#11833](https://github.com/openemr/openemr/pull/11833))
  - bump codecov/codecov-action from 6 to 7 ([#12406](https://github.com/openemr/openemr/pull/12406))
  - bump https://github.com/codespell-project/codespell from v2.4.1 to 2.4.2 ([#12560](https://github.com/openemr/openemr/pull/12560))
  - bump https://github.com/macisamuele/language-formatters-pre-commit-hooks from v2.14.0 to 2.16.0 ([#12561](https://github.com/openemr/openemr/pull/12561))
  - bump https://github.com/pre-commit/pre-commit-hooks from v4.5.0 to 6.0.0 ([#12557](https://github.com/openemr/openemr/pull/12557))
  - bump lewagon/wait-on-check-action from 1.7.0 to 1.8.0 ([#12446](https://github.com/openemr/openemr/pull/12446))
  - bump marocchino/sticky-pull-request-comment from 2 to 3 ([#11832](https://github.com/openemr/openemr/pull/11832))
  - bump peter-evans/create-pull-request from 7 to 8 ([#12039](https://github.com/openemr/openemr/pull/12039))
  - Centralize and simplify "should log" logic ([#12698](https://github.com/openemr/openemr/pull/12698))
  - clean up js_escape() patterns ([#12145](https://github.com/openemr/openemr/pull/12145))
  - clear arrayValues.list + smaller.invalid PHPStan baseline entries ([#12245](https://github.com/openemr/openemr/pull/12245))
  - docker migration master-side (phases 1a + 1b + 1c) ([#12482](https://github.com/openemr/openemr/pull/12482))
  - document Claude Code-driven Selenium debugging ([#12463](https://github.com/openemr/openemr/pull/12463))
  - document docker-routed pre-commit workflow + isolate PHPStan tmpDir via named volume ([#12147](https://github.com/openemr/openemr/pull/12147))
  - document new openemr-cmd worktree add -b default and --base flag ([#12613](https://github.com/openemr/openemr/pull/12613))
  - document new openemr-cmd worktree subcommands and AI-assistant rules ([#12021](https://github.com/openemr/openemr/pull/12021))
  - document openemr-cmd phpstan-generate-reset and update-layout-field-fixtures ([#12250](https://github.com/openemr/openemr/pull/12250))
  - document openemr-cmd worktree prune + graceful remove ([#12369](https://github.com/openemr/openemr/pull/12369))
  - document release-targets.yml as master-authoritative ([#12586](https://github.com/openemr/openemr/pull/12586))
  - drop show_full_output debug flag ([#12707](https://github.com/openemr/openemr/pull/12707))
  - drop unused laminas-xmlrpc and confirm 8.5 resolution path ([#12468](https://github.com/openemr/openemr/pull/12468))
  - drop vestigial Remove Rector step ([#12659](https://github.com/openemr/openemr/pull/12659))
  - exclude generated EHI schema docs from release archives ([#12317](https://github.com/openemr/openemr/pull/12317))
  - exclude non-logic dirs from phpunit coverage ([#12268](https://github.com/openemr/openemr/pull/12268))
  - exclude NotificationCronEmailTest from e2e suite (it lives in email suite) ([#12258](https://github.com/openemr/openemr/pull/12258))
  - Exit with a nonzero code if bootstrap fails ([#12532](https://github.com/openemr/openemr/pull/12532))
  - ext-posix in `require-dev` breaks `composer install` on Windows ([#12566](https://github.com/openemr/openemr/pull/12566))
  - externalize byte-identical FILES_ALL to a dedicated config file ([#12575](https://github.com/openemr/openemr/pull/12575))
  - extract PatientPortalLoginController, behavior-preserving ([#12231](https://github.com/openemr/openemr/pull/12231))
  - extract sync-byte-identical bash into a tested script ([#12578](https://github.com/openemr/openemr/pull/12578))
  - Fix SignalWire fax support in oe-module-faxsms ([#12766](https://github.com/openemr/openemr/pull/12766))
  - gitignore .worktrees.json.lock/ directory ([#12614](https://github.com/openemr/openemr/pull/12614))
  - Groundwork for connecting to ORM and migration events ([#12522](https://github.com/openemr/openemr/pull/12522))
  - Instruct CodeRabbit to not edit the main PR body ([#12531](https://github.com/openemr/openemr/pull/12531))
  - migrate dependabot-auto-merge to client-id / vars ([#12372](https://github.com/openemr/openemr/pull/12372))
  - migrate PostCalendar from Smarty to Twig ([#12435](https://github.com/openemr/openemr/pull/12435))
  - migrate reserved-word bot to client-id / vars ([#12371](https://github.com/openemr/openemr/pull/12371))
  - Move doctrine/migrations config ([#12633](https://github.com/openemr/openemr/pull/12633))
  - move isolated-compatible tests to isolated suite ([#12010](https://github.com/openemr/openemr/pull/12010))
  - normalize YAML indentation in dev-easy-redis compose ([#12464](https://github.com/openemr/openemr/pull/12464))
  - note HOST_UID auto-export for transparent host-uid alignment ([#12650](https://github.com/openemr/openemr/pull/12650))
  - phpstan fixes and other unrelated fixes ([#12523](https://github.com/openemr/openemr/pull/12523))
  - pin express version range in ccdaservice lockfile ([#12465](https://github.com/openemr/openemr/pull/12465))
  - pin orchestrator to Sonnet, drop the premium Opus default ([#12708](https://github.com/openemr/openemr/pull/12708))
  - pin runner to ubuntu-24.04 ([#12383](https://github.com/openemr/openemr/pull/12383))
  - Prepare BreakglassChecker(Interface) for autowiring ([#12654](https://github.com/openemr/openemr/pull/12654))
  - relocate docker/compose.yml to .github/docker/ ([#12590](https://github.com/openemr/openemr/pull/12590))
  - relocate migration doc + refresh post-migration documentation ([#12551](https://github.com/openemr/openemr/pull/12551))
  - Remove redundant encryption from audit log writes ([#12123](https://github.com/openemr/openemr/pull/12123))
  - remove redundant encryption from OneTimeAuth tokens ([#12066](https://github.com/openemr/openemr/pull/12066))
  - remove unimplemented VoiceClient ([#12020](https://github.com/openemr/openemr/pull/12020))
  - remove unnecessary encryption from one-time tokens ([#12062](https://github.com/openemr/openemr/pull/12062))
  - Remove unused client SSL certificate auth feature ([#12510](https://github.com/openemr/openemr/pull/12510))
  - remove unused Laminas\Mvc import in FHIRSearchFieldFactory ([#12405](https://github.com/openemr/openemr/pull/12405))
  - replace grunt/jshint with eslint+mocha in oe-cda-schematron ([#12453](https://github.com/openemr/openemr/pull/12453))
  - replace vendored SearchHighlight.js with vanilla TreeWalker helper ([#12146](https://github.com/openemr/openemr/pull/12146))
  - retry Docker image pulls with backoff to survive registry timeouts ([#12425](https://github.com/openemr/openemr/pull/12425))
  - revert(faxsms): restore VoiceClient and in-browser RingCentral softphone ([#12229](https://github.com/openemr/openemr/pull/12229))
  - skip auto-review on bot-authored PRs ([#12706](https://github.com/openemr/openemr/pull/12706))
  - split pretty-format-json into per-ecosystem 2-space/4-space blocks ([#12573](https://github.com/openemr/openemr/pull/12573))
  - switch to Anthropic code-review plugin, on-demand only ([#12705](https://github.com/openemr/openemr/pull/12705))
  - track pre-commit hook revs ([#12553](https://github.com/openemr/openemr/pull/12553))
  - trigger orchestrator on push to release-targets.yml ([#12720](https://github.com/openemr/openemr/pull/12720))
  - trim docker/.gitignore + docker/COVERAGE.md from byte-identical set ([#12576](https://github.com/openemr/openemr/pull/12576))
  - use GitHub App for reserved-word refresh PRs so CI runs ([#12368](https://github.com/openemr/openemr/pull/12368))
  - use session storage instead of encryption for payment data ([#12069](https://github.com/openemr/openemr/pull/12069))
  - wall off webpack cache in dev-easy compose envs ([#12466](https://github.com/openemr/openemr/pull/12466))
  - warn against git fetch --update-head-ok in the primary repo ([#12595](https://github.com/openemr/openemr/pull/12595))
  - wire hadolint into local hooks ([#12552](https://github.com/openemr/openemr/pull/12552))

#### ASTP/ONC Certification

  - skip data_absent_reason test in US Core 3.1.1 suite ([#11918](https://github.com/openemr/openemr/pull/11918))

#### Backend Modernization Project

  - add composer phpstan-baseline-reset to wipe and rebuild ([#11816](https://github.com/openemr/openemr/pull/11816))
  - cap phpDoc.parseError baseline at zero ([#11914](https://github.com/openemr/openemr/pull/11914))
  - Clean up some issues in Document ([#12001](https://github.com/openemr/openemr/pull/12001))
  - clean up stale $GLOBALS references in comments ([#11791](https://github.com/openemr/openemr/pull/11791))
  - drain Carecoordination module class.notFound + method.notFound ([#11859](https://github.com/openemr/openemr/pull/11859))
  - drain confident nonObject baselines (21 → 0) ([#11821](https://github.com/openemr/openemr/pull/11821))
  - drain constant.notFound baseline (123 → 0) ([#11823](https://github.com/openemr/openemr/pull/11823))
  - drain edihistory baseline (method.notFound, variable.undefined) ([#11878](https://github.com/openemr/openemr/pull/11878))
  - drain eRx method.notFound entries (253 → 189) ([#11825](https://github.com/openemr/openemr/pull/11825))
  - drain return.missing baseline (29 → 0) ([#11820](https://github.com/openemr/openemr/pull/11820))
  - drain variable.undefined across interface/patient_file ([#11902](https://github.com/openemr/openemr/pull/11902))
  - drain variable.undefined baseline (3064 → 2927) ([#11887](https://github.com/openemr/openemr/pull/11887))
  - drain variable.undefined baseline for edihistory, modules, reports, services ([#11903](https://github.com/openemr/openemr/pull/11903))
  - drain variable.undefined baseline for interface/forms/eye_mag (1835 → 800) ([#11920](https://github.com/openemr/openemr/pull/11920))
  - drain variable.undefined baseline in interface/main ([#11901](https://github.com/openemr/openemr/pull/11901))
  - drain variable.undefined for canonical-globals form files (800 → 708) ([#11954](https://github.com/openemr/openemr/pull/11954))
  - drain variable.undefined for interface/forms (708 → 586) ([#11963](https://github.com/openemr/openemr/pull/11963))
  - drain variable.undefined in gacl/admin (3446 → 3399) ([#11826](https://github.com/openemr/openemr/pull/11826))
  - drain variable.undefined PHPStan baseline entries ([#11895](https://github.com/openemr/openemr/pull/11895))
  - extract storage path calculation for testability ([#11778](https://github.com/openemr/openemr/pull/11778))
  - gate fatal-category baseline entries from ever growing ([#11796](https://github.com/openemr/openemr/pull/11796))
  - lift edih_x12_file to OpenEMR\Billing\EdiHistory\X12File ([#11879](https://github.com/openemr/openemr/pull/11879))
  - make baseline-diff PR lookup tolerant of API failures ([#11847](https://github.com/openemr/openemr/pull/11847))
  - migrate call sites to encryptForDatabase/decryptFromDatabase helpers ([#11956](https://github.com/openemr/openemr/pull/11956))
  - Move Smarty and mPDF temp directories to system temp ([#11779](https://github.com/openemr/openemr/pull/11779))
  - move to src/ services and fix calendar import + N+1 ([#12195](https://github.com/openemr/openemr/pull/12195))
  - narrow broad exception catches to specific types ([#11619](https://github.com/openemr/openemr/pull/11619))
  - regenerate baseline for phpstan 2.1.54 ([#11996](https://github.com/openemr/openemr/pull/11996))
  - remove return/store polymorphism from pnHTML ([#8587](https://github.com/openemr/openemr/pull/8587))
  - rename 'Default Password Expiration Days' to 'Password Expiration Days' ([#11817](https://github.com/openemr/openemr/pull/11817))
  - replace literal preg_match prefix/suffix checks with native string functions ([#11884](https://github.com/openemr/openemr/pull/11884))
  - Simplify and speed up the integration test build matrix ([#11701](https://github.com/openemr/openemr/pull/11701))

#### CCDA Service

  - rebuild libxmljs2 from source ([#11852](https://github.com/openemr/openemr/pull/11852))
  - rename cache key to invalidate stale v127 binaries ([#11862](https://github.com/openemr/openemr/pull/11862))
  - Revert "ci(ccdaservice): rebuild libxmljs2 from source (#11852)" ([#11861](https://github.com/openemr/openemr/pull/11861))

#### Calendar

  - cover recurring event repeat-type branching ([#11795](https://github.com/openemr/openemr/pull/11795))

#### Database Migrations & Schema Changes

  - Fix typo ([#11867](https://github.com/openemr/openemr/pull/11867))

#### DevOps

  - capture openemr log and apache coredump in e2e failure artifact ([#12445](https://github.com/openemr/openemr/pull/12445))

#### Documentation

  - add end-to-end RELEASE_PROCESS.md for automated release flow ([#12117](https://github.com/openemr/openemr/pull/12117))
  - plan for openemr release-automation conductor (openemr-devops#664) ([#11896](https://github.com/openemr/openemr/pull/11896))

#### Hardening

  - replace raw assert() calls with real runtime checks ([#12246](https://github.com/openemr/openemr/pull/12246))
  - route superglobal access through HttpRestRequest in src/ ([#11348](https://github.com/openemr/openemr/pull/11348))

#### Infrastructure

  - post baseline diff as a sticky PR comment ([#11828](https://github.com/openemr/openemr/pull/11828))

#### PHP

  - bump claimrevolution/oe-module-claimrev-connect from 2.1.3 to 2.1.4 ([#12415](https://github.com/openemr/openemr/pull/12415))
  - bump claimrevolution/oe-module-claimrev-connect from 2.1.4 to 2.1.5 ([#12556](https://github.com/openemr/openemr/pull/12556))
  - bump claimrevolution/oe-module-claimrev-connect from 2.1.5 to 2.1.6 ([#12685](https://github.com/openemr/openemr/pull/12685))
  - bump digitickets/lalit from 3.4.1 to 3.4.2 ([#12417](https://github.com/openemr/openemr/pull/12417))
  - bump doctrine/migrations from 3.9.6 to 3.9.7 ([#11839](https://github.com/openemr/openemr/pull/11839))
  - bump doctrine/orm from 3.6.3 to 3.6.5 ([#12114](https://github.com/openemr/openemr/pull/12114))
  - bump doctrine/orm from 3.6.5 to 3.6.6 ([#12237](https://github.com/openemr/openemr/pull/12237))
  - bump doctrine/orm from 3.6.6 to 3.6.7 ([#12359](https://github.com/openemr/openemr/pull/12359))
  - bump ergebnis/composer-normalize from 2.51.0 to 2.52.0 ([#12158](https://github.com/openemr/openemr/pull/12158))
  - bump firehed/container from 1.0.0 to 1.1.0 ([#12449](https://github.com/openemr/openemr/pull/12449))
  - bump giggsey/libphonenumber-for-php from 9.0.28 to 9.0.29 ([#11840](https://github.com/openemr/openemr/pull/11840))
  - bump giggsey/libphonenumber-for-php from 9.0.29 to 9.0.30 ([#12112](https://github.com/openemr/openemr/pull/12112))
  - bump giggsey/libphonenumber-for-php from 9.0.30 to 9.0.31 ([#12364](https://github.com/openemr/openemr/pull/12364))
  - bump giggsey/libphonenumber-for-php from 9.0.31 to 9.0.32 ([#12413](https://github.com/openemr/openemr/pull/12413))
  - bump giggsey/libphonenumber-for-php from 9.0.32 to 9.0.33 ([#12683](https://github.com/openemr/openemr/pull/12683))
  - bump google/apiclient from 2.19.2 to 2.19.3 ([#12044](https://github.com/openemr/openemr/pull/12044))
  - bump guzzlehttp/guzzle from 7.10.0 to 7.10.4 ([#12264](https://github.com/openemr/openemr/pull/12264))
  - bump guzzlehttp/guzzle from 7.10.4 to 7.10.6 ([#12360](https://github.com/openemr/openemr/pull/12360))
  - bump guzzlehttp/guzzle from 7.10.6 to 7.11.1 ([#12411](https://github.com/openemr/openemr/pull/12411))
  - bump guzzlehttp/guzzle from 7.11.1 to 7.12.1 ([#12544](https://github.com/openemr/openemr/pull/12544))
  - bump guzzlehttp/guzzle from 7.11.1 to 7.12.1 in /interface/modules/custom_modules/oe-module-faxsms ([#12545](https://github.com/openemr/openemr/pull/12545))
  - bump guzzlehttp/guzzle from 7.12.1 to 7.12.3 ([#12686](https://github.com/openemr/openemr/pull/12686))
  - bump guzzlehttp/psr7 from 2.10.1 to 2.10.4 ([#12362](https://github.com/openemr/openemr/pull/12362))
  - bump guzzlehttp/psr7 from 2.10.4 to 2.11.0 ([#12414](https://github.com/openemr/openemr/pull/12414))
  - bump guzzlehttp/psr7 from 2.11.0 to 2.12.1 ([#12542](https://github.com/openemr/openemr/pull/12542))
  - bump guzzlehttp/psr7 from 2.11.0 to 2.12.1 in /interface/modules/custom_modules/oe-module-faxsms ([#12543](https://github.com/openemr/openemr/pull/12543))
  - bump guzzlehttp/psr7 from 2.12.1 to 2.12.3 ([#12684](https://github.com/openemr/openemr/pull/12684))
  - bump guzzlehttp/psr7 from 2.7.0 to 2.11.0 in /interface/modules/custom_modules/oe-module-faxsms ([#12467](https://github.com/openemr/openemr/pull/12467))
  - bump guzzlehttp/psr7 from 2.9.0 to 2.10.1 ([#12234](https://github.com/openemr/openemr/pull/12234))
  - bump justinrainbow/json-schema from 6.8.2 to 6.9.0 ([#12412](https://github.com/openemr/openemr/pull/12412))
  - bump justinrainbow/json-schema from 6.9.0 to 6.10.0 ([#12554](https://github.com/openemr/openemr/pull/12554))
  - bump knplabs/knp-snappy from 1.6.0 to 1.7.2 ([#12161](https://github.com/openemr/openemr/pull/12161))
  - bump league/flysystem from 3.33.0 to 3.34.0 ([#12157](https://github.com/openemr/openemr/pull/12157))
  - bump league/flysystem from 3.34.0 to 3.35.1 ([#12687](https://github.com/openemr/openemr/pull/12687))
  - bump moneyphp/money from 4.8.0 to 4.9.0 ([#12042](https://github.com/openemr/openemr/pull/12042))
  - bump phpoffice/phpspreadsheet from 5.7.0 to 5.8.0 ([#12416](https://github.com/openemr/openemr/pull/12416))
  - bump phpseclib/phpseclib from 3.0.51 to 3.0.52 ([#11841](https://github.com/openemr/openemr/pull/11841))
  - bump phpseclib/phpseclib from 3.0.52 to 3.0.53 ([#12448](https://github.com/openemr/openemr/pull/12448))
  - bump phpseclib/phpseclib from 3.0.53 to 3.0.55 ([#12499](https://github.com/openemr/openemr/pull/12499))
  - bump phpstan/phpstan from 2.1.50 to 2.1.51 in the development group across 1 directory ([#11838](https://github.com/openemr/openemr/pull/11838))
  - bump ramsey/uuid from 4.9.2 to 4.9.3 ([#12555](https://github.com/openemr/openemr/pull/12555))
  - bump setasign/fpdi from 2.6.6 to 2.6.7 ([#12169](https://github.com/openemr/openemr/pull/12169))
  - bump slevomat/coding-standard from 8.28.1 to 8.29.0 ([#12113](https://github.com/openemr/openemr/pull/12113))
  - bump slevomat/coding-standard from 8.29.0 to 8.30.1 ([#12682](https://github.com/openemr/openemr/pull/12682))
  - bump symfony/dom-crawler from 7.4.8 to 7.4.12 ([#12292](https://github.com/openemr/openemr/pull/12292))
  - bump the development group across 1 directory with 2 updates ([#12156](https://github.com/openemr/openemr/pull/12156))
  - bump the development group with 2 updates ([#11991](https://github.com/openemr/openemr/pull/11991))
  - bump the symfony group across 1 directory with 4 updates ([#12270](https://github.com/openemr/openemr/pull/12270))
  - bump the symfony group with 11 updates ([#12679](https://github.com/openemr/openemr/pull/12679))
  - bump the symfony group with 6 updates ([#12041](https://github.com/openemr/openemr/pull/12041))
  - bump the symfony group with 7 updates ([#11990](https://github.com/openemr/openemr/pull/11990))
  - bump the symfony group with 8 updates ([#12356](https://github.com/openemr/openemr/pull/12356))
  - bump twig/twig from 3.24.0 to 3.25.0 ([#12160](https://github.com/openemr/openemr/pull/12160))
  - bump twig/twig from 3.25.0 to 3.26.0 ([#12227](https://github.com/openemr/openemr/pull/12227))
  - bump twig/twig from 3.26.0 to 3.27.1 ([#12363](https://github.com/openemr/openemr/pull/12363))
  - bump twilio/sdk from 8.11.4 to 8.11.5 ([#12043](https://github.com/openemr/openemr/pull/12043))
  - bump twilio/sdk from 8.11.5 to 8.11.6 ([#12111](https://github.com/openemr/openemr/pull/12111))
  - bump zircote/swagger-php from 6.1.1 to 6.1.2 ([#11993](https://github.com/openemr/openemr/pull/11993))
  - bump zircote/swagger-php from 6.1.2 to 6.2.0 ([#12498](https://github.com/openemr/openemr/pull/12498))
  - restore file timestamps to enable PHPStan caching ([#10387](https://github.com/openemr/openemr/pull/10387))

#### UI Modernization

  - remove AngularJS from secure_chat and messages ([#11521](https://github.com/openemr/openemr/pull/11521))
  - replace gulp with webpack for theme compilation ([#11231](https://github.com/openemr/openemr/pull/11231))

#### billing & payments

  - extract DaySheetAggregator from print_daysheet_report_num1 ([#11860](https://github.com/openemr/openemr/pull/11860))

#### javascript

  - bump @tootallnate/once and jest-environment-jsdom ([#12164](https://github.com/openemr/openemr/pull/12164))
  - bump @xmldom/xmldom from 0.9.9 to 0.9.10 in /ccdaservice/packages/oe-cda-schematron ([#11775](https://github.com/openemr/openemr/pull/11775))
  - bump autoprefixer from 10.5.0 to 10.5.2 ([#12677](https://github.com/openemr/openemr/pull/12677))
  - bump body-parser from 1.20.4 to 1.20.5 in /ccdaservice ([#11844](https://github.com/openemr/openemr/pull/11844))
  - bump dompurify from 3.4.0 to 3.4.1 ([#11842](https://github.com/openemr/openemr/pull/11842))
  - bump dompurify from 3.4.1 to 3.4.2 ([#11992](https://github.com/openemr/openemr/pull/11992))
  - bump dompurify from 3.4.10 to 3.4.11 ([#12533](https://github.com/openemr/openemr/pull/12533))
  - bump dompurify from 3.4.2 to 3.4.5 ([#12159](https://github.com/openemr/openemr/pull/12159))
  - bump dompurify from 3.4.5 to 3.4.7 ([#12361](https://github.com/openemr/openemr/pull/12361))
  - bump dompurify from 3.4.7 to 3.4.8 ([#12418](https://github.com/openemr/openemr/pull/12418))
  - bump dompurify from 3.4.8 to 3.4.9 ([#12451](https://github.com/openemr/openemr/pull/12451))
  - bump dompurify from 3.4.9 to 3.4.10 ([#12502](https://github.com/openemr/openemr/pull/12502))
  - bump eslint from 10.4.1 to 10.5.0 in /ccdaservice ([#12500](https://github.com/openemr/openemr/pull/12500))
  - bump express from 4.22.1 to 4.22.2 in /ccdaservice ([#12162](https://github.com/openemr/openemr/pull/12162))
  - bump fast-uri from 3.0.6 to 3.1.2 ([#12092](https://github.com/openemr/openemr/pull/12092))
  - bump fast-uri from 3.1.0 to 3.1.2 in /interface/modules/custom_modules/oe-module-comlink-telehealth/public/assets/js ([#12095](https://github.com/openemr/openemr/pull/12095))
  - bump globals from 17.6.0 to 17.7.0 in /ccdaservice ([#12676](https://github.com/openemr/openemr/pull/12676))
  - bump grunt from 1.6.1 to 1.6.2 in /ccdaservice ([#11722](https://github.com/openemr/openemr/pull/11722))
  - bump ip-address from 10.1.0 to 10.2.0 in /ccdaservice ([#12037](https://github.com/openemr/openemr/pull/12037))
  - bump js-yaml from 4.1.1 to 4.2.0 in /ccdaservice ([#12505](https://github.com/openemr/openemr/pull/12505))
  - bump mini-css-extract-plugin from 2.10.1 to 2.10.2 ([#12452](https://github.com/openemr/openemr/pull/12452))
  - bump mocha from 11.7.5 to 11.7.6 in /ccdaservice ([#12240](https://github.com/openemr/openemr/pull/12240))
  - bump mongoose from 7.8.8 to 7.8.9 in /ccdaservice ([#12038](https://github.com/openemr/openemr/pull/12038))
  - bump postcss from 8.5.10 to 8.5.12 ([#11843](https://github.com/openemr/openemr/pull/11843))
  - bump postcss from 8.5.12 to 8.5.13 ([#11994](https://github.com/openemr/openemr/pull/11994))
  - bump postcss from 8.5.13 to 8.5.14 ([#12045](https://github.com/openemr/openemr/pull/12045))
  - bump postcss from 8.5.14 to 8.5.15 ([#12241](https://github.com/openemr/openemr/pull/12241))
  - bump postcss from 8.5.15 to 8.5.16 ([#12680](https://github.com/openemr/openemr/pull/12680))
  - bump sass from 1.100.0 to 1.101.0 ([#12501](https://github.com/openemr/openemr/pull/12501))
  - bump sass from 1.86.0 to 1.100.0 ([#12450](https://github.com/openemr/openemr/pull/12450))
  - bump select2 from 4.0.13 to 4.1.0 in the ui-components group ([#12355](https://github.com/openemr/openemr/pull/12355))
  - bump tar from 7.5.11 to 7.5.16 in /ccdaservice ([#12504](https://github.com/openemr/openemr/pull/12504))
  - bump uuid from 11.1.0 to 14.0.0 in /ccdaservice ([#11780](https://github.com/openemr/openemr/pull/11780))
  - bump uuid from 14.0.0 to 14.0.1 in /ccdaservice ([#12600](https://github.com/openemr/openemr/pull/12600))
  - bump webpack from 5.105.4 to 5.107.2 in the build-tools group ([#12447](https://github.com/openemr/openemr/pull/12447))
  - bump webpack from 5.107.2 to 5.108.1 in the build-tools group ([#12675](https://github.com/openemr/openemr/pull/12675))

#### phpstan

  - refresh RESERVED_WORD_SUPPLEMENT ([#12373](https://github.com/openemr/openemr/pull/12373))
  - refresh RESERVED_WORD_SUPPLEMENT ([#12601](https://github.com/openemr/openemr/pull/12601))

#### testing

  - capture diagnostics for InvalidSessionIdException flake ([#11857](https://github.com/openemr/openemr/pull/11857))
  - harden e2e test step against rerun cascade failure ([#11986](https://github.com/openemr/openemr/pull/11986))
  - snapshot rendered HTML for every layout field type ([#12232](https://github.com/openemr/openemr/pull/12232))

