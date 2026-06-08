# PostCalendar Smarty → Twig Migration — Working Notes

**Branch**: `postcalendar-twig`
**Goal**: Delete `library/smarty_legacy/` (516 KB) and convert PostCalendar's 13 non-empty Smarty templates + 10 plugins + 3 consumers to Twig.

**NOT in scope**: vendor/smarty/smarty (Smarty 4, used by `library/classes/Controller.class.php` for the rest of OpenEMR). Stays.

## Why a fresh stab and not #8528

PR #8528 (juggernautsei + Warp Oz agent) tried this. Got 19 Twig templates done, but:
- Twig functions in `PostCalendarTwigExtensions.php` echo HTML instead of returning strings → date picker doesn't render (confirmed broken in their UFG demo)
- `PrintEvents` is a TODO stub returning empty string → print button does nothing
- `monthSelector` Twig function includes back into `pntemplates/default/views/monthSelector.php` → coupled to the directory we want to delete
- Adds ~1234 lines of new PHPStan baselines (CLAUDE.md violation)
- Tests in `tests/Tests/Unit/` with `run_tests.php` mock-bootstrap; never run in CI
- PR description claims a Smarty fallback that doesn't exist in code
- Scope drift: bundles a global modal a11y JS with `autoload: true`

Salvage cost ≈ rewrite cost. Starting clean.

## Audit (current master)

### Consumers (3 PHP files, 2847 lines)

| File | LoC | Role |
|---|---|---|
| `interface/main/calendar/modules/PostCalendar/pnuserapi.php` | 1544 | View builder; assigns most template vars; `$tpl->fetch()` returns calendar HTML |
| `interface/main/calendar/modules/PostCalendar/pnuser.php` | 438 | Routes function calls (view, list, search, submit) |
| `interface/main/calendar/modules/PostCalendar/pnadmin.php` | 865 | Admin functions (category management) |
| `interface/main/calendar/modules/PostCalendar/pcSmarty.class.php` | 124 | `class pcSmarty extends Smarty_Legacy` — to be replaced/deleted |

### Templates (24 files; 11 are zero-byte `index.html` placeholders)

Non-empty (13):

| File | Lines | Has `[-php-]`? |
|---|---|---|
| `pntemplates/default/views/footer.html` | 2 | 0 |
| `pntemplates/default/views/day/default.html` | 5 | 0 |
| `pntemplates/default/views/month/default.html` | 8 | 0 |
| `pntemplates/default/views/week/default.html` | 8 | 0 |
| `pntemplates/default/views/header.html` | 52 | 3 |
| `pntemplates/default/user/ajax_search.html` | 266 | 14 (audit was wrong — original claim of 0) |
| `pntemplates/default/admin/submit_category.html` | 395 | 0 |
| `pntemplates/default/views/month_print/outlook_ajax_template.html` | 534 | 2 |
| `pntemplates/default/views/week_print/outlook_ajax_template.html` | 634 | 3 |
| `pntemplates/default/views/day_print/outlook_ajax_template.html` | 717 | 4 |
| `pntemplates/default/views/month/ajax_template.html` | 792 | 17 |
| `pntemplates/default/views/week/ajax_template.html` | 1044 | 17 |
| `pntemplates/default/views/day/ajax_template.html` | 1068 | 16 |

Total: 5525 lines. 76 `[-php-]` blocks across 8 of 13 templates. Heaviest concentration is the 6 ajax_templates (50 + 5 + 4 + 17 + 17 + 16 = ~50 in main ajax, ~25 in print). The `user/ajax_search.html` had 14 blocks despite the initial audit claiming zero — corrected above.

### Plugins (10 files, 1089 lines; all return void / echo)

| Plugin | LoC | Used? | Notes |
|---|---|---|---|
| `function.pc_date_format.php` | 49 | **dead** | Zero template references — Smarty's built-in `\|date_format` is what templates actually use. |
| `function.pc_date_select.php` | 146 | **dead** | Zero template references. Date picker (if reachable) renders elsewhere. |
| `function.pc_filter.php` | 171 | **dead** | Zero template references. |
| `function.pc_form_nav_close.php` | 61 | **dead** | Zero template references. |
| `function.pc_form_nav_open.php` | 40 | **dead** | Zero template references. |
| `function.pc_popup.php` | 235 | **dead** | Zero template references. Event-detail popup (visible in UI) rendered by other code. |
| `function.pc_sort_events.php` | 95 | **live** | Invoked from 3 templates: `views/{day,week_print,day_print}/...ajax_template.html` as `[-pc_sort_events var="S_EVENTS" sort="time" order="asc" value=$A_EVENTS-]`. Curiously NOT used in `week/ajax_template.html` or `month/ajax_template.html` — needs verification when porting those views (search for an equivalent inline sort). |
| `function.pc_url.php` | 163 | **dead** | Zero template references. |
| `function.pc_view_select.php` | 79 | **dead** | Zero template references. |
| `modifier.pc_date_format.php` | 50 | **dead** | Filter is `\|pc_date_format`, zero references — templates use Smarty's built-in `\|date_format`. |

**Dead-plugin verification**: `grep -rn '\\[-pc_' interface/main/calendar/modules/PostCalendar/pntemplates/` returns only `pc_sort_events` invocations. Cross-checked against all `*.php`, `*.html`, `*.twig`, `*.js`, `*.tsx`, `*.ts` files under `interface/` — only the plugin definitions themselves and PHPStan baseline entries reference these names. Dead plugins get deleted in Phase 10 (legacy removal) along with the rest of `pntemplates/` and `library/smarty_legacy/`. No new Twig methods needed for them.

**Live-plugin port target**: `pc_sort_events` → method on `PostCalendarTwigExtension`. Returns the sorted array as a Twig function: `{% set S_EVENTS = pc_sort_events(A_EVENTS, 'time', 'asc') %}` (vs. Smarty's "assigns the result back to the template var named in `var=`" pattern, which Twig doesn't model — the caller binds the return value with `{% set %}` instead).

### Smarty library to remove

- `library/smarty_legacy/` (516 KB)

## Migration architecture (target state)

### Foundation classes (new, modern style)

- `src/PostCalendar/PostCalendarTwigExtension.php` — `class PostCalendarTwigExtension extends AbstractExtension` with `declare(strict_types=1)`. Each plugin becomes one method: `pc_date_format`, `pc_url`, `pc_filter`, etc. **Every method returns string. No echo. No global state. No reaching into pntemplates/.**
- `src/PostCalendar/CalendarRenderer.php` — `class CalendarRenderer` with `declare(strict_types=1)`. Owns Twig Environment, registers extension, exposes `render(string $template, array $vars): string`.

### Templates location

- `templates/calendar/default/` — matches the existing OpenEMR Twig template convention. Same paths #8528 chose.

### Tests location

- `tests/Tests/Isolated/PostCalendar/` — NOT `tests/Tests/Unit/`. The Isolated suite is what `composer phpunit-isolated` runs in CI.
- Use OpenEMR's existing render-fixture pattern: render with known params, compare to `tests/Tests/Isolated/PostCalendar/fixtures/render/*.html`. Regen via `composer update-twig-fixtures` (need to extend the existing fixture-update script to include the calendar dir).
- TwigTemplateCompilationTest is global — picks up the new templates automatically.

### `[-php-]` extraction strategy

Concentrated in 7 templates (day/week/month ajax_template + their _print variants + header). Each block needs analysis: most should move to `pnuserapi.php` (or a new `PostCalendarViewModel` service) and pass results as template variables. A few might be appropriate as Twig functions IF they're used by multiple templates AND have no side effects.

**Hard rule**: no Twig function may `echo`. Functions that need to "output HTML" return a string; the caller `{{ }}` it.

### Order of operations

1. **Foundation classes** (PostCalendarTwigExtension + CalendarRenderer) — empty methods initially. ✅ session 1.
2. **Smallest templates first** (footer + 3 view defaults) — get the compile pipeline working. ✅ session 1.
3. **Golden snapshot harness (Phase 0 follow-up)** — capture pre-migration rendered HTML against the current master Smarty output for every reachable view. Pre-requisite for everything below. ✅ session 2 first task — `.ai-notes/snapshots/capture.sh` + 8 baseline fixtures in `.ai-notes/snapshots/baseline/`.
4. **Plugins → Twig functions** — only `pc_sort_events` needs porting (the other 9 are dead code, see audit above). Port as a method on `PostCalendarTwigExtension`; deletion of all 10 plugin files happens in Phase 10.
5. **Header template** — first template with `[-php-]` blocks. Extract `create_event_time_anchor` to a method on PostCalendarTwigExtension, move session reads to PHP caller, pass body_class / title / HEADER_SCRIPTS / HEADER_STYLES as template vars.
6. **Medium templates** (user/ajax_search, admin/submit_category) — no `[-php-]` blocks, mechanical conversion.
7. **Print views** — `[-php-]` extraction (2-4 blocks per print view).
8. **The big 3** (day/week/month ajax_template) — most `[-php-]` blocks (16-17 each). Hardest. Plan per block.
9. **Switch consumers** — replace `new pcSmarty()` with `new CalendarRenderer()` in pnuserapi/pnuser/pnadmin.
10. **Delete legacy**: `library/smarty_legacy/`, `pcSmarty.class.php`, `pntemplates/`, all 10 plugins.

## Verification harness (load-bearing — DO before phase 4)

No automated calendar render tests exist in master today. The harness must be built from scratch before any plugin port or template conversion that produces user-visible HTML. Without it every subsequent phase relies on manual eyeballing.

**Targets**: 8 fixture endpoints covering the surface that gets converted:
- `views/day/ajax_template.html` (day view)
- `views/week/ajax_template.html` (week view)
- `views/month/ajax_template.html` (month view)
- `views/day_print/outlook_ajax_template.html`
- `views/week_print/outlook_ajax_template.html`
- `views/month_print/outlook_ajax_template.html`
- `admin/submit_category.html`
- `user/ajax_search.html`

**Capture strategy** — HTTP capture against the running dev-easy stack with admin credentials, fixed date (20260601 — past seed-data window, so view templates render the "empty event list" branch). Out-of-band sed normalisation strips per-request variability (cache-busting `?v=N`/`?t=N`, CSRF tokens, nonces). Script at `.ai-notes/snapshots/capture.sh`; baseline fixtures in `.ai-notes/snapshots/baseline/`.

**Why HTTP and not direct `pcSmarty::fetch()`**: the consumer files (pnuserapi/pnuser/pnadmin) build the assignment array from session state, GET params, DB lookups, and module function dispatch. Replicating that scaffolding in isolated PHPUnit would have been its own project. HTTP capture exercises the production code path end-to-end, with the trade-off that the baseline is tied to specific seed data and one running stack. After conversion, re-run capture.sh against the Twig output and `diff -r` the two trees.

**Output normalization**: cache-busting `?v=N` / `?t=N` query params, CSRF tokens (16-byte hex), session-derived `nonce-…` identifiers. Other per-request randomness (if any surfaces in the diff later) gets added to the sed pipeline in capture.sh.

**Storage**: `.ai-notes/snapshots/baseline/{view}.html`, gitignored — point-in-time captures, regeneratable via capture.sh from current master. Contain upstream-Smarty HTML with typos that trip codespell (one variant of "modal" recurs 16 times in `admin_categories`), which is the proximate reason for not committing. If render verification proves load-bearing for CI later, promote to `tests/Tests/Isolated/PostCalendar/fixtures/render/` and integrate with `composer update-twig-fixtures` (with codespell skip for that path).

**To use across sessions**: re-run `.ai-notes/snapshots/capture.sh .ai-notes/snapshots/baseline` against pre-migration master, then check out the work branch — the baseline persists in the working tree (it's gitignored, not git-clean'd). To capture post-migration state for diffing, target a different dir: `capture.sh .ai-notes/snapshots/post-migration` (also gitignored), then `diff -r baseline/ post-migration/`.

**Coverage**: 8 views — day, week, month, day_print, week_print, month_print, user_search, admin_categories. Print views and the 3 main views together exercise all 6 ajax_template families; user_search covers user/ajax_search.html; admin_categories covers admin/submit_category.html.

**Actual effort**: built and run in one session (~30 min including login-form CSRF dead-end and a `set -u` foot-gun in normalise()). Faster than the ½-day estimate because HTTP capture sidestepped the PHPUnit fixture-construction work entirely.

**Why session-2-first**: session 1's commits added foundation classes + 4 dormant template files. None of those affect rendered output, so the missing harness didn't bite yet. The moment session 2 starts porting plugins (which produce HTML strings that templates emit), every plugin port becomes "does this match what Smarty did?" — and without the harness, the only answer is manual eyeballing the rendered page.

## CLAUDE.md compliance gates

Before pushing any commit on this branch:

- `composer phpstan` must pass. No new baseline entries. Existing baseline entries in touched files must shrink, not grow.
- `composer phpunit-isolated` includes the new tests and they pass.
- No `empty()` constructs introduced (project rule).
- No `global $X` registry patterns in new files.
- Every new file has `declare(strict_types=1)`.
- PR description (when eventually written) must accurately describe what the code does.

## Open questions to resolve as work progresses

1. **Does any code outside PostCalendar invoke `pcSmarty`?** Need to confirm with `grep -r "new pcSmarty\|extends pcSmarty\|extends Smarty_Legacy"`. If anything outside our 4 files uses it, scope grows.
2. **What does the `{site}/documents/smarty/` cache dir get used for after `pcSmarty` is gone?** Twig manages its own cache. Need to check install/upgrade scripts for any references.
3. **Localizations (`config_load file="lang.$USER_LANG"`)**: Smarty has lang `.conf` files. Need to find them and decide: pass as Twig vars from PHP, or use Symfony Translation, or hardcode-with-xl().
