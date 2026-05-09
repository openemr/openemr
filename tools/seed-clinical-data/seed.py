#!/usr/bin/env python3
"""
seed.py — Load realistic clinical data into existing OpenEMR patients.

Authenticates via OAuth2 password grant using the dashboard's confidential
OAuth client. For each patient returned by FHIR `GET /Patient`, POSTs:

  - 2 allergies   (POST /api/patient/{puuid}/allergy)
  - 2 problems    (POST /api/patient/{puuid}/medical_problem)
  - 2 medications (POST /api/patient/{pid}/medication)
  - 1 encounter   (POST /api/patient/{puuid}/encounter)
  - 1 vitals set  (POST /api/patient/{pid}/encounter/{eid}/vital)

Uses only Python stdlib so it runs anywhere with python3 — no `pip install`.

Usage:
    export OPENEMR_BASE='https://openemr-production-0c8c.up.railway.app'
    export OPENEMR_CLIENT_ID='<dashboard client id>'
    export OPENEMR_CLIENT_SECRET='<dashboard client secret>'
    export OPENEMR_USERNAME='admin'
    export OPENEMR_PASSWORD='<your admin password>'
    python3 seed.py            # full run
    python3 seed.py --first    # just the first patient (probe mode)
    python3 seed.py --dry-run  # auth + list patients, no writes

If OpenEMR rejects the password grant or specific scopes, edit the
OAuth client in OpenEMR Admin -> System -> API Clients to allow the
'password' grant type AND tick the lowercase write scopes
(user/allergy.write, user/medical_problem.write, user/medication.write,
user/encounter.write, user/vital.write).
"""
from __future__ import annotations

import argparse
import base64
import json
import os
import random
import sys
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timedelta

# -- realistic-but-fake clinical data -----------------------------------

ALLERGIES = [
    "Penicillin", "Peanuts", "Latex", "Sulfa drugs", "Shellfish",
    "Eggs", "Aspirin", "Bee venom", "Iodinated contrast", "Codeine",
]

# (display title, ICD10 code)
PROBLEMS = [
    ("Essential hypertension", "ICD10:I10"),
    ("Type 2 diabetes mellitus, unspecified", "ICD10:E11.9"),
    ("Asthma, unspecified", "ICD10:J45.909"),
    ("Hyperlipidemia, unspecified", "ICD10:E78.5"),
    ("GERD, without esophagitis", "ICD10:K21.9"),
    ("Migraine, unspecified", "ICD10:G43.909"),
    ("Major depressive disorder, single episode, unspecified", "ICD10:F32.9"),
    ("Generalized anxiety disorder", "ICD10:F41.1"),
    ("Hypothyroidism, unspecified", "ICD10:E03.9"),
    ("Obesity, unspecified", "ICD10:E66.9"),
]

MEDS = [
    "Lisinopril 10mg PO daily",
    "Metformin 500mg PO twice daily",
    "Atorvastatin 20mg PO daily",
    "Albuterol HFA 90mcg/inh, 2 puffs PRN",
    "Omeprazole 20mg PO daily",
    "Sertraline 50mg PO daily",
    "Levothyroxine 50mcg PO daily",
    "Amlodipine 5mg PO daily",
    "Hydrochlorothiazide 25mg PO daily",
    "Ibuprofen 400mg PO every 6h PRN",
]

# OAuth scope set the loader needs. OpenEMR's lowercase scopes are the
# OpenEMR-REST-API scope namespace; the FHIR ones are needed for the
# patient list lookup.
SCOPES = " ".join([
    "openid",
    "api:oemr",
    "api:fhir",
    "user/Patient.read",
    "user/facility.read",
    "user/allergy.write",
    "user/medical_problem.write",
    "user/medication.write",
    "user/encounter.write",
    "user/vital.write",
])


# -- HTTP helpers (urllib only, no requests) ----------------------------

class HttpError(RuntimeError):
    def __init__(self, status: int, body: str):
        super().__init__(f"HTTP {status}: {body[:500]}")
        self.status = status
        self.body = body


def http_request(method: str, url: str, *,
                 headers: dict[str, str] | None = None,
                 body_bytes: bytes | None = None) -> tuple[int, bytes]:
    req = urllib.request.Request(url, method=method)
    for k, v in (headers or {}).items():
        req.add_header(k, v)
    if body_bytes is not None:
        req.data = body_bytes
    try:
        with urllib.request.urlopen(req) as resp:
            return resp.status, resp.read()
    except urllib.error.HTTPError as e:
        # Read body for diagnostics; re-raise as HttpError so callers
        # can decide whether to swallow per-row failures.
        return e.code, e.read()


def get_token(base: str, client_id: str, client_secret: str,
              username: str, password: str) -> str:
    body = urllib.parse.urlencode({
        "grant_type": "password",
        "username": username,
        "password": password,
        "scope": SCOPES,
    }).encode()
    basic = base64.b64encode(f"{client_id}:{client_secret}".encode()).decode()
    status, raw = http_request(
        "POST", f"{base}/oauth2/default/token",
        headers={
            "Content-Type": "application/x-www-form-urlencoded",
            "Authorization": f"Basic {basic}",
        },
        body_bytes=body,
    )
    if status != 200:
        raise HttpError(status, raw.decode("utf-8", "replace"))
    return json.loads(raw)["access_token"]


def api(method: str, base: str, path: str, token: str,
        body: dict | None = None) -> tuple[int, object]:
    headers = {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json",
    }
    body_bytes = None
    if body is not None:
        headers["Content-Type"] = "application/json"
        body_bytes = json.dumps(body).encode()
    status, raw = http_request(
        method, f"{base}{path}", headers=headers, body_bytes=body_bytes,
    )
    parsed: object = None
    if raw:
        try:
            parsed = json.loads(raw)
        except json.JSONDecodeError:
            parsed = raw.decode("utf-8", "replace")
    return status, parsed


# -- patient & facility lookup ------------------------------------------

def list_patients(base: str, token: str, max_count: int = 50) -> list[tuple[str, str]]:
    status, bundle = api("GET", base,
                         f"/apis/default/fhir/Patient?_count={max_count}", token)
    if status != 200 or not isinstance(bundle, dict):
        raise HttpError(status, str(bundle))
    out: list[tuple[str, str]] = []
    for entry in bundle.get("entry", []) or []:
        res = entry.get("resource") or {}
        puuid = res.get("id") or ""
        names = res.get("name") or [{}]
        n = names[0]
        text = n.get("text")
        if not text:
            text = " ".join(filter(None, [
                " ".join(n.get("given", [])),
                n.get("family", ""),
            ])).strip() or "(no name)"
        if puuid:
            out.append((puuid, text))
    return out


def get_pid(base: str, token: str, puuid: str) -> str | None:
    status, resp = api("GET", base, f"/apis/default/api/patient/{puuid}", token)
    if status == 200 and isinstance(resp, dict):
        data = resp.get("data") or {}
        if isinstance(data, list) and data:
            data = data[0]
        pid = data.get("pid") if isinstance(data, dict) else None
        return str(pid) if pid is not None else None
    return None


def first_facility_id(base: str, token: str) -> str | None:
    status, resp = api("GET", base, "/apis/default/api/facility", token)
    if status == 200 and isinstance(resp, dict):
        data = resp.get("data")
        if isinstance(data, list) and data:
            return str(data[0].get("id") or "")
    return None


# -- per-patient seed ---------------------------------------------------

def seed_one(base: str, token: str, puuid: str, name: str,
             facility_id: str | None) -> None:
    pid = get_pid(base, token, puuid)
    if not pid:
        print(f"   ! could not resolve pid for {puuid}; skipping pid-based writes")
    a_year_ago = (datetime.now() - timedelta(days=365)).strftime("%Y-%m-%d")
    today = datetime.now().strftime("%Y-%m-%d")

    # Allergies (puuid endpoint)
    for a in random.sample(ALLERGIES, 2):
        s, r = api("POST", base, f"/apis/default/api/patient/{puuid}/allergy", token,
                   {"title": a, "begdate": a_year_ago})
        flag = "✓" if s in (200, 201) else "✗"
        extra = "" if s in (200, 201) else f" — {str(r)[:120]}"
        print(f"   {flag} Allergy '{a}': {s}{extra}")

    # Problems (puuid endpoint)
    for title, dx in random.sample(PROBLEMS, 2):
        s, r = api("POST", base, f"/apis/default/api/patient/{puuid}/medical_problem", token,
                   {"title": title, "begdate": a_year_ago, "diagnosis": dx})
        flag = "✓" if s in (200, 201) else "✗"
        extra = "" if s in (200, 201) else f" — {str(r)[:120]}"
        print(f"   {flag} Problem '{title}': {s}{extra}")

    # Medications (pid endpoint)
    if pid:
        for med in random.sample(MEDS, 2):
            s, r = api("POST", base, f"/apis/default/api/patient/{pid}/medication", token,
                       {"title": med, "begdate": a_year_ago})
            flag = "✓" if s in (200, 201) else "✗"
            extra = "" if s in (200, 201) else f" — {str(r)[:120]}"
            print(f"   {flag} Medication '{med}': {s}{extra}")

    # Encounter (puuid endpoint)
    enc_payload = {
        "date": today,
        "onset_date": today,
        "reason": "Routine office visit (clinical data seeded by tools/seed-clinical-data)",
        "facility_id": facility_id or "3",
        "provider_id": "1",
        "pc_catid": "5",
        "class_code": "AMB",
    }
    s, enc_resp = api("POST", base, f"/apis/default/api/patient/{puuid}/encounter", token, enc_payload)
    flag = "✓" if s in (200, 201) else "✗"
    extra = "" if s in (200, 201) else f" — {str(enc_resp)[:160]}"
    print(f"   {flag} Encounter: {s}{extra}")
    eid = None
    if isinstance(enc_resp, dict):
        data = enc_resp.get("data") or {}
        if isinstance(data, dict):
            eid = data.get("id") or data.get("eid") or data.get("encounter")

    # Vitals (pid + eid)
    if pid and eid:
        vital_payload = {
            "bps": str(random.randint(110, 145)),
            "bpd": str(random.randint(70, 92)),
            "pulse": str(random.randint(58, 92)),
            "respiration": str(random.randint(12, 20)),
            "temperature": f"{random.uniform(97.5, 99.4):.1f}",
            "weight": str(random.randint(140, 220)),
            "height": str(random.randint(60, 75)),
            "oxygen_saturation": str(random.randint(95, 100)),
        }
        s, r = api("POST", base, f"/apis/default/api/patient/{pid}/encounter/{eid}/vital", token, vital_payload)
        flag = "✓" if s in (200, 201) else "✗"
        extra = "" if s in (200, 201) else f" — {str(r)[:160]}"
        print(f"   {flag} Vitals: {s}{extra}")


# -- main ---------------------------------------------------------------

def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--first", action="store_true",
                    help="seed only the first patient (probe mode)")
    ap.add_argument("--dry-run", action="store_true",
                    help="auth + list patients only; no writes")
    args = ap.parse_args()

    base = os.environ["OPENEMR_BASE"].rstrip("/")
    client_id = os.environ["OPENEMR_CLIENT_ID"]
    client_secret = os.environ["OPENEMR_CLIENT_SECRET"]
    username = os.environ["OPENEMR_USERNAME"]
    password = os.environ["OPENEMR_PASSWORD"]

    print(f"→ Auth via password grant on {base} ...")
    try:
        token = get_token(base, client_id, client_secret, username, password)
    except HttpError as e:
        print(f"FATAL: token endpoint returned {e.status}\n{e.body[:1000]}", file=sys.stderr)
        return 1
    print(f"✓ token acquired ({len(token)} chars)")

    print("→ Listing patients via FHIR ...")
    patients = list_patients(base, token, max_count=50)
    print(f"✓ {len(patients)} patient(s) returned")
    for puuid, name in patients[:20]:
        print(f"   - {name}  ({puuid})")

    facility_id = first_facility_id(base, token)
    print(f"→ default facility_id: {facility_id or '3 (fallback)'}")

    if args.dry_run:
        print("\n(dry-run — exiting without writes)")
        return 0
    if args.first:
        patients = patients[:1]
        print(f"\n(--first set; seeding only {patients[0][1] if patients else '(none)'})")

    for puuid, name in patients:
        print(f"\n=== {name}  ({puuid[:8]}...) ===")
        try:
            seed_one(base, token, puuid, name, facility_id)
        except Exception as e:
            print(f"   ! exception: {e}")

    print("\n✓ Done. Sign out and back in on the dashboard, then visit a patient page.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
