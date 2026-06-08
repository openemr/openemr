# PostCalendar Template Data Contract

Reference catalog of what the 6 main calendar-rendering Smarty templates do inside their `[-php-]` blocks, gathered before designing the `PostCalendarViewModel` service.

Source-of-truth files (read in full):
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/day/ajax_template.html`
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/week/ajax_template.html`
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/month/ajax_template.html`
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/day_print/outlook_ajax_template.html`
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/week_print/outlook_ajax_template.html`
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/month_print/outlook_ajax_template.html`

Referenced but not cataloged:
- `interface/main/calendar/modules/PostCalendar/pntemplates/default/views/monthSelector.php` (included by all 3 on-screen ajax templates with `$caldate`/`$cMonth`/`$cYear`/`$cDay` in scope).

## Common data needed across all 6 templates

These appear in every template's setup block — view-model top-level fields:

- **`DOWlist`** — `int[7]`, weekdays in display order starting at `pcFirstDayOfWeek` from `pnModGetVar(__POSTCALENDAR__, 'pcFirstDayOfWeek')`, auto-corrected to 0 when out of range
- **`Date`** — current YYYYMMDD string from `postcalendar_getDate()` plus `y`/`m`/`d` substrings
- **`A_EVENTS`** — date-keyed array of event arrays (`YYYY-MM-DD` → events list); pre-loaded by the controller
- **`A_CATEGORY`** — ordered category array (id, name, color, desc, event_duration)
- **`A_SHORT_DAY_NAMES`** — language-localized short DOW names indexed 0..6
- **`providers`** — list of providers to render (id, username, fname, lname)
- **`times`** — list of timeslots (hour, minute, mer)
- **`interval`** — slot interval in minutes
- **`viewtype`** — `day`/`week`/`month`
- **Six nav URLs** — `PREV_DAY_URL`, `NEXT_DAY_URL`, `PREV_WEEK_URL`, `NEXT_WEEK_URL`, `PREV_MONTH_URL`, `NEXT_MONTH_URL`
- **`TPL_IMAGE_PATH`** — image-path prefix

On-screen ajax templates only (not print views):
- `session`, `authUserID`, `pc_facility`, `language_direction`, `authorizeduser`
- `facilities` from `getUserFacilities($authUserID)` or `getFacilities()` if authorizeduser
- `provinfo` from `getProviderInfo(...)` (facility-filtered when `$pc_facility` is set)
- `chevron_icon_left/right` — LTR/RTL FA classes

Day + week only:
- `openhour`, `closehour` from `$GLOBALS['schedule_start']` / `['schedule_end']`

Day, week, day_print, week_print (timed views):
- `timeslotHeightVal=20`, `timeslotHeightUnit="px"` — must match `ajax_calendar.css`

## Per-event decoration

Each template builds these per-event:

- **`evtClass`** — derived from `catid`: `event_appointment` (default) | `event_noshow` (1) | `event_in` (2) | `event_out` (3) | `event_reserved` (4, 8, 11) | `event_holiday` (6, week's 99). Month adds `hiddenevent` to catid 6. Overridable by `$event['eventViewClass']`.
- **`pccattype`** — `'true'` if `pc_cattype == 1` else `''`, looked up via `sqlQuery` per event (screen views only)
- **`displayTime`** (month/month_print) — `g` + (`a` if min==00 else `:ia`) format
- **`dispstarth`** (day/week/day_print) — int hour shifted into 12-hour display
- **`startTime`/`duration`** all-day normalization — startTime forced to `$times[0]`, duration = full schedule window
- **IN event duration** — lookahead for matching OUT (same eid) and extend; else to `$calEndMin`
- **Geometry** — `eStartMin`, `evtTop`, `evtHeight`, `divWidth`, `divLeft` (% of column)
- **`eventPositions[$eid]->width/->leftpos`** — overlap-aware width/left from per-timeslot scan
- **Name parts** — `preg_split('/,\s*/', $event['patient_name'], 2) + ['', '']` → `lname`, `fname`
- **`address`** — `$event['patient_address']`
- **`patient_dob`** — `oeFormatShortDate(...)` on screen views, raw on print
- **`patient_age`** — `$event['patient_age']`
- **`catname`** — overridden by `xl("IN")/xl("OUT")/xl("VACATION")/xl("LUNCH")/xl("RESERVED")` for catids 2/3/4/8/11
- **`groupcounselors`** — `getUserNameById(...)\n` per counselor in `$event['group_counselors']`
- **`row`** facility lookup — `sqlStatement('SELECT name,id,color FROM facility WHERE id=(SELECT pc_facility FROM openemr_postcalendar_events WHERE pc_eid=?)')` (screen only)
- **`color`** — `$event['catcolor']`, overridden by `$row['color']` when `$GLOBALS['event_color'] == 2`
- **`content`** HTML — large built string, see Patterns below
- **`divTitle`** — multi-line tooltip
- **`eventdate`** — YYYYMMDD form of date key

## Per-view derived data

- **Day**: `MULTIDAY` bool, single-table layout, overlap skips only IN, filters by `[openhour, closehour]`, OUT events unshift to front of timeslot stack
- **Week**: per-provider table with 7 day-cols, column-header links to day view, overlap skips both IN and OUT, catid 99 → `event_holiday`, `currentWeek` class in mini-cal, patient appts include `<a class="show-appointment shown">` toggle, `$apptToggle` var
- **Month**: per-provider 7-col day-cell grid (not time-positioned), compact `displayTime`, skips IN/OUT entirely, catid 6 → `event_holiday hiddenevent`, catid 7 (Closed) renders `$event['title']`, no clinic-hours filter, `nyear/nmonth` from `strtotime($Date)`
- **Day-print**: no session/picker/nav, two side-by-side mini-cals, no facility lookup, no `pccattype`, no group, no patient links, `nyear/nmonth` from first `A_EVENTS` key, inline `PrintDatePicker`
- **Week-print**: per-provider with pagebreak, 4×2 day layout (`dateplusfour = strtotime($eventdate) + 4*24*60*60`), inline `PrintEvents` + `PrintDatePicker`, no overlap detection but `eventPositions` referenced (latent dead code), `date("h:i", ...)` format, `<font color='green'>` styling
- **Month-print**: per-provider with pagebreak, calls `getProviderInfo()` (no facility filter, unused?), `nyear/nmonth` from `strtotime($Date)`, inline `PrintDatePicker`, no group, no closed/holiday branch, compact content

## Pure presentation (stays in template)

`attr/attr_js/text/xl/xla/xlt/js_escape/oeFormatShortDate/dateformat/xl_appt_category/urlencode/date/strtotime/sprintf/substr/is_weekend_day/is_holiday`

## DB lookups during render (highest priority to extract)

1. `getUserFacilities($authUserID)` — day/week/month setup + provider-picker block (twice each)
2. `getFacilities()` — day/week/month provider-picker when authorizeduser
3. `getProviderInfo()` / `getProviderInfo('%', true, $pc_facility)` — day/week/month setup + month_print
4. `sqlQuery("SELECT pc_cattype ...")` — **once per event** in day/week/month main loops
5. `sqlStatement("SELECT name,id,color FROM facility ...")` + `sqlFetchArray` — **once per event** in day/week/month main loops
6. `getUserNameById($counselor)` — per group counselor in day/week/month
7. `getTypeName($grouptype)` — per group event in day/week/month

Print views perform zero per-event DB lookups — they consume what the controller pre-loaded. That's the cleaner pattern screen views should adopt.

## Behavioral quirks to preserve

1. DOW list ordering + auto-correction — centralize once
2. **Overlap rules differ by view**: day skips only IN, week + day_print skip IN+OUT, month + month_print skip IN+OUT entirely from rendering
3. catid 6 holiday: `event_holiday` everywhere; month-screen adds `hiddenevent`; tooltip "(double click to edit)" suppressed in month-screen
4. catid 7 (Clinic closed): renders `$event['title']` in month/week-screen, not in print views
5. catid 99 → `event_holiday` only in week-screen (latent extension)
6. **`eventViewClass` override** — upstream dispatcher hook
7. **IN event lookahead** — scans same provider's events for next OUT with same eid; needs full event list, not single event
8. **Overlap algorithm** — start-in/end-in/span test per timeslot; width = 100/N; day unshifts catid-3 (OUT) to front of stack
9. **Three-way facility-filter output**: `pc_facility == 0` (all) vs `== $row['id']` (match) vs greyed-out (mismatch, shows `$row['name']` in red bold). Day's "all" sub-branches catid 6 → hiddenevent. Week's "all" sub-branches catid 4 and 6 → hiddenevent.
10. **Color resolution**: `catcolor`, overridden by `row.color` when `$GLOBALS['event_color'] == 2`
11. **calendar_appt_style** drives content build: 1=lname / 2=lname,fname / 3=+title / 4=+hometext (green) / 5=+address. `<4` controls whether comment appended to title. Print uses `<font color='green'>`, screen uses `<span class='text-success'>`.
12. **Group sessions**: when `gid` non-empty, alternate content with group icon + counselors. Wrapping div adds `groups` class. Print views skip entirely.
13. **Patient picture hover** — `onmouseover="ShowImage(...)"` with `urlencode($patientid)` and webroot
14. All-day event normalization — happens before geometry math
15. Print `PrintDatePicker` emits `<td></td>` for out-of-month days; screen mini-cal uses `tdOtherMonthDay-small`. Not equivalent.
16. Week-print's 4×2 layout — `dateplusfour = +4 days`, loop breaks at iter 4
17. `openhour`/`closehour` filtering in day + week only (events outside this range silently disappear)
18. "Today" button gated on `$Date <> date('Ymd')`
19. Mini-cal prev/next-month "stay on same day" — decrement until `checkdate` succeeds
20. `monthSelector.php` include with `$caldate/$cMonth/$cYear/$cDay` in scope
21. `pnModURL` for nav — takes `tplview`, `viewtype`, `Date`, `pc_username`, `pc_category`, `pc_topic`
22. `pccattype` flows into the JS DIV id as the third dash-separated segment (`{eventdate}-{eventid}-{pccattype}`). Print views skip.
23. Smarty `[-assign-]` directives — `dayname`/`day`/`month`/`year` from `$DATE|date_format` — surface as Twig vars

## Latent bugs to fix or preserve (decide explicitly)

- Month-screen + month_print reference `$calEndMin`/`$calStartMin` inside the all-day branch but never initialize these in the month context
- Week-print's `PrintEvents` references `$date` without parameter/global — relies on PHP lexical leakage from the surrounding `foreach`
- Week-print references `$eventPositions` but never populates it (always empty)
- Month_print fetches `$provinfo` but doesn't visibly use it
