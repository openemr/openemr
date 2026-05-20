# 2.1.2
Bug fixes:
- Send `serviceTypeCodes` as a JSON array (`List<string>`) instead of a comma-separated string. The ClaimRev API tightened request validation and started rejecting the old shape with HTTP 400, breaking Check Now eligibility requests. Empty configuration still asks for all benefits.
- Always emit `isRevenueToolsPayerId: false` on each payer in the eligibility request so the API can disambiguate ClaimRev-internal payer IDs from clearinghouse payer numbers.
- Make MBI Finder mutually exclusive with Eligibility (matches Coverage Discovery). Drop the `payers` array from the request when only non-eligibility products are selected, since the API ignores it for those products and the presence corrupts MBI Finder results. When MBI Finder is requested, copy the subscriber number to the top-level `subscriberId` field.
- Render Coverage Discovery results with the full Quick Info / Deductibles / Benefits / Medicare / Validations layout used by Eligibility. The API returns the same `SharpRevenueEligibilityResponse` shape for both products, but the old Coverage Discovery view only showed the flat top-level coverage fields and dropped the nested `mapped271` data.
- Allow Coverage Discovery, Demographics, and MBI Finder to run on a patient with no insurance on file. These products query the payer using patient demographics and don't need a payer row, but the form previously rendered nothing without insurance and the backend returned "No insurance data found for patient" if a check was somehow submitted. The form now shows a "No Insurance" tab that exposes those three products (without the Eligibility option), and `EligibilityObjectCreator::buildObject` falls back to a patient-data-only request in that case.
- Stop intermittently popping "Error communicating with server" on `Check Now`. The eligibility AJAX endpoint can sit through a Cloud Run cold start (~60s) plus a `retryLater` poll loop (~60s) on Coverage Discovery, which exceeded PHP's default 30 second `max_execution_time`; PHP killed the script mid-flight and the browser saw a non-JSON response. Bump `set_time_limit` to 180s on the eligibility and appointment Check Now endpoints, set explicit Guzzle `connect_timeout`/`timeout` (30s/60s) on the auth and main API clients so a stuck call can't burn the whole budget, and retry the OAuth token POST up to two extra times with brief backoff to absorb transient B2C hiccups.
- Add a "Reset" button next to "Check Now" on the patient's eligibility tab. Clicking it (after a confirm prompt) deletes every cached eligibility row for that patient across all payer responsibilities, so testers can re-run a check from a clean slate without poking the database directly.

# 2.1.1
Maintenance release: apply phpcbf style fixes, rector modernization, refresh PHPStan baseline, and refactor CSV downloads + migration helpers to avoid Semgrep XSS/SQLi false positives. No functional changes.

# 2.1.0
Adds patient balance, KPI dashboard, AR aging report, denial analytics, recoupment report, eligibility sweep with calendar indicators and appointment filters, payment-advice posting, claim status dashboard with timeline, reconciliation page, and OpenEMR 7.x compatibility shims.

# 1.0.12
Added new setup helpers to stop the sftp service from interfering with the file sending service of this module.
