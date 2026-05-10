# seed-clinical-data — load fake-but-realistic clinical data into OpenEMR

For each patient already in OpenEMR (returned by FHIR `GET /Patient`),
the loader POSTs:

- 2 allergies
- 2 medical problems (with ICD10 codes)
- 2 medications
- 1 encounter (today's date)
- 1 vitals reading (BP, pulse, temp, weight, height, SpO2)

Uses OpenEMR's **non-FHIR REST API** (`/apis/default/api/`) because OpenEMR's
FHIR write surface is limited to Patient, Practitioner, Organization.

Auths via OAuth2 `password` grant using your existing dashboard
confidential client. No new client registration needed.

## Run

```
cd tools/seed-clinical-data

export OPENEMR_BASE='https://openemr-production-0c8c.up.railway.app'
export OPENEMR_CLIENT_ID='<dashboard client id from Railway env>'
export OPENEMR_CLIENT_SECRET='<dashboard client secret from Railway env>'
export OPENEMR_USERNAME='admin'
export OPENEMR_PASSWORD='<your admin password>'

# 1. probe — confirms auth and lists patients without writing
python3 seed.py --dry-run

# 2. one patient — verify per-endpoint POSTs succeed
python3 seed.py --first

# 3. full run — seed every patient
python3 seed.py
```

## After seeding

Sign out and back in on the dashboard (your existing access token does
not change, but the redirect refreshes the session). Then visit any
`/patient/<uuid>` page — Allergies, Problems, Medications, Encounters
cards should now show the seeded entries.

## Troubleshooting

| Error | Fix |
|---|---|
| `HTTP 401: invalid_grant` from `/oauth2/default/token` | Wrong username/password, or password grant disabled on the client. Check OpenEMR Admin → System → API Clients → your dashboard client → enable `password` grant if there's a checkbox. |
| `HTTP 403: invalid_scope` from `/oauth2/default/token` | The lowercase `user/<resource>.write` scopes aren't granted to your client. Edit the client in API Clients → tick all the write scopes the script requests. |
| Per-endpoint `400` with a validation error | OpenEMR's controller rejected one field — most often `facility_id` not matching a real facility. Look at the error body printed by `seed.py`; it's usually self-explanatory. |
| `HTTP 401` on a per-resource POST mid-run | Token expired (an hour into a long run). Re-run; the loader is idempotent in the sense that re-running just adds more entries. |

## Idempotency note

The loader is **additive**. Running it twice gives every patient
4 allergies / 4 problems / 4 medications / 2 encounters. Run once for
a clean demo, or delete entries via OpenEMR's UI between runs.
