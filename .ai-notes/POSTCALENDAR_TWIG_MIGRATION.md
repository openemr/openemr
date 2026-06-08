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
| `pntemplates/default/user/ajax_search.html` | 266 | 0 |
| `pntemplates/default/admin/submit_category.html` | 395 | 0 |
| `pntemplates/default/views/month_print/outlook_ajax_template.html` | 534 | 2 |
| `pntemplates/default/views/week_print/outlook_ajax_template.html` | 634 | 3 |
| `pntemplates/default/views/day_print/outlook_ajax_template.html` | 717 | 4 |
| `pntemplates/default/views/month/ajax_template.html` | 792 | 17 |
| `pntemplates/default/views/week/ajax_template.html` | 1044 | 17 |
| `pntemplates/default/views/day/ajax_template.html` | 1068 | 16 |

Total: 5525 lines. 62 `[-php-]` blocks concentrated in the 6 ajax_templates (50 of 62) + header (3).

### Plugins (10 files, 1089 lines; all return void / echo)

| Plugin | LoC | Notes |
|---|---|---|
| `function.pc_date_format.php` | 49 | Echoes a formatted date |
| `function.pc_date_select.php` | 146 | Echoes a date-picker form widget |
| `function.pc_filter.php` | 171 | Filters/echoes event list per category/topic/etc. |
| `function.pc_form_nav_close.php` | 61 | Closes a form-nav block |
| `function.pc_form_nav_open.php` | 40 | Opens a form-nav block |
| `function.pc_popup.php` | 235 | Renders the event-detail popup |
| `function.pc_sort_events.php` | 95 | Sorts events in-place (assigns back to Smarty) |
| `function.pc_url.php` | 163 | Builds a PostCalendar URL |
| `function.pc_view_select.php` | 79 | Day/week/month/year toggle |
| `modifier.pc_date_format.php` | 50 | Filter for `\|pc_date_format` |

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

1. **Foundation classes** (PostCalendarTwigExtension + CalendarRenderer) — empty methods initially.
2. **Smallest templates first** (footer, defaults, header) — get rendering pipeline working end-to-end with a tiny scope.
3. **Plugins → Twig functions** — port `pc_date_format`, `pc_url`, etc. as Twig methods, one at a time. Verify each via the rendered output.
4. **Medium templates** (user/ajax_search, admin/submit_category) — no `[-php-]` blocks, mechanical conversion.
5. **Print views** — first ones with `[-php-]` blocks; extract those to PHP caller.
6. **The big 3** (day/week/month ajax_template) — most `[-php-]` blocks. Hardest. Plan carefully.
7. **Switch consumers** — replace `new pcSmarty()` with `new CalendarRenderer()` in pnuserapi/pnuser/pnadmin.
8. **Delete legacy**: `library/smarty_legacy/`, `pcSmarty.class.php`, `pntemplates/`, all 10 plugins.

## Verification harness

- No automated calendar render tests exist in master today. Need to build.
- Plan: for each view (day, week, month, day_print, week_print, month_print), capture HTML output via a test-driven render with known fixture data (specific date, specific provider, specific events).
- Fixture comparison: normalize whitespace + comments; compare structural HTML.
- Manual: dev-easy stack — navigate to calendar, click around. Visual parity check against master.

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
