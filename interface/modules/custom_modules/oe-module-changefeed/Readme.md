# Change Feed module for OpenEMR

Adds an incremental **change feed** to OpenEMR: ask "what clinical-core rows
changed since cursor X?" and get back a list of FHIR resource references
(including deletions), so an integrator can sync incrementally instead of
repeatedly full-scan polling every resource.

Drop-in module — no core changes. Change capture is done with database triggers,
so it records **every** write to a watched table, including direct SQL and
soft-deletes, not just writes that go through the application layer.

## Install

1. Copy `oe-module-changefeed/` into
   `interface/modules/custom_modules/`.
2. In OpenEMR: **Modules → Manage Modules**, register/install the module, then
   **Enable** it.
   - Install creates the `changefeed_log` table.
   - Enable installs the capture triggers (this is why enable, not just install,
     is required).
   - Disable drops the triggers; the log table is left in place until the module
     is reset/removed.

The database user OpenEMR connects as needs the MySQL `TRIGGER` privilege
(standard on the bundled/dev stack).

## Endpoint

```
GET /api/changefeed?_since=<cursor>&_limit=<n>
```

- `_since` — cursor from your last poll (default `0` = from the beginning).
- `_limit` — max changes to return (default `100`, max `1000`).

Requires the same authorization as viewing patient demographics
(`patients` / `demo`) and a valid API session/token.

### Response

```json
{
  "since": 0,
  "cursor": 1240,
  "watermark": 1240,
  "count": 2,
  "changes": [
    { "resourceType": "Patient",   "id": "9c2…-uuid", "operation": "update", "cursor": 1239, "changedAt": "2026-09-02 10:15:03" },
    { "resourceType": "Encounter", "id": "4a1…-uuid", "operation": "delete", "cursor": 1240, "changedAt": "2026-09-02 10:15:07" }
  ]
}
```

Persist `cursor` and pass it as `_since` on the next poll. For `insert`/`update`,
fetch the current resource via the normal FHIR API (e.g.
`GET /fhir/Patient/<id>`); `delete` means the resource is gone.

## Watched tables

Out of the box:

| Table | FHIR resource |
|-------|---------------|
| `patient_data` | `Patient` |
| `form_encounter` | `Encounter` |

Extend the set in `TriggerManager::defaultWatched()` — add a `WatchedResource`
with the table, FHIR resource type, primary-key column, uuid column, and
(for soft-delete tables) the activity column that going to `0` means "deleted".
Re-enable the module to (re)install triggers.

## How it works

- One `AFTER INSERT/UPDATE/DELETE` trigger per watched table writes a row into
  `changefeed_log` (`resource_table`, `resource_type`, `row_pk`, `row_uuid`,
  `op`, `changed_at`). Each trigger body is a single `INSERT`, so no `DELIMITER`
  handling is needed.
- The endpoint serves rows with `id` in `(_since, watermark]`, oldest first.

## Known limitations

- **Watermark lag.** To avoid skipping a change whose auto-increment id becomes
  visible only after a later id, the feed only serves rows at least a couple of
  seconds old. A change is therefore visible to consumers up to ~2s after it
  commits.
- **uuid at insert time.** If a row is created before its uuid is assigned, the
  `insert` change may carry no uuid; the feed resolves it from the source row
  (for insert/update) or, failing that, skips the entry — a later `update`
  carrying the uuid still reports it. A `delete` with no captured uuid cannot be
  resolved and is skipped.
- **Single consumer.** The cursor is a client-side value; there is no per-consumer
  acknowledgement. `ChangeFeedRepository::pruneUpTo()` can trim acknowledged rows
  if you run a single consumer.
- **Mapping scope.** Only the watched tables that map cleanly 1:1 to a FHIR
  resource are included. Polymorphic tables (e.g. `lists`) are intentionally out
  of scope for now.
