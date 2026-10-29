# FHIR API write audit ledger

OpenEMR records successful FHIR creates and updates made with OAuth2 access
tokens in the `api_write_audit` table. Local API calls and unsuccessful writes
are not recorded there. When API request logging is enabled, the ledger row
links to its corresponding `api_log` entry.

The ledger records the client, user and patient when available, resource type
and UUID, action, authorized scope, access-token identifier, and timestamp.
Administrators can choose the snapshot level under **Globals > Logging**:

- **Minimal Logging** (default) records the audit row without resource
  snapshots.
- **Full Logging** also stores the FHIR resource before an update and after a
  create or update. These snapshots may contain PHI; enable this level only
  when needed and apply the site's audit-data access and retention policies.

The current ledger is the storage foundation for API write auditing. A
dedicated administration report and app-aware FHIR Provenance are not included
in this change.
