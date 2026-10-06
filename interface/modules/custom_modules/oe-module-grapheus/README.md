# Grapheus for OpenEMR

`oe-module-grapheus` adds **Grapheus by Exetazo** to OpenEMR. This branch (`main`) is for **OpenEMR 8.x** (tested on 8.4.1); for OpenEMR 7.0.x use the `openemr-7.0` branch (tested on 7.0.3). Licensed GPL-3.0-or-later like OpenEMR; the paid part (transcription, AI drafting, the Assistant) runs on the Grapheus service and needs a Grapheus subscription.

## What it does
**In every encounter — "Grapheus" tab (clinicians):**
- Connect your Grapheus account (one click; key stored encrypted with OpenEMR's CryptoGen).
- **Start recording** opens a small recorder window (browsers block the microphone inside OpenEMR's frames). Pause, auto-stop after 5 silent minutes or 90 minutes.
- Drafts from this encounter, the Chrome extension or the phone app (last 48 h).
- **Review and add**: SOAP note (editable), problems (ICD-10), allergies, **prescriptions entered with request_intent "proposal" and never transmitted**, **fee-sheet lines with billed = 0** justified by the diagnoses, time statement for time-based billing.

**Main menu — "Grapheus" (everyone, by permission):**
- *Set it up like…* (administrators): practice/facility, globals, lists, fee schedule, visit types, forms — proposed, approved, logged, undoable.
- *Show me how to…*: step-by-step OpenEMR directions.
- *Schedule Molly with Dr Bob at 2:30*: the patient is found in OpenEMR's own records and confirmed before booking.
- Authority follows OpenEMR ACLs: admin = everything; clinician/scheduler = daily tasks + how-to; others = how-to. Option: administrators only.
- Billed to the practice's Grapheus account at 2× AI cost per request. Security settings, passwords and integrations are blocked.

## Install
**Any OpenEMR 8.x (or 7.0.x with the `openemr-7.0` branch):**
```
cd /var/www/localhost/htdocs/openemr/interface/modules/custom_modules
git clone https://github.com/mikebirkheadmd-maker/oe-module-grapheus.git            # OpenEMR 8.x
# git clone -b openemr-7.0 https://github.com/mikebirkheadmd-maker/oe-module-grapheus.git   # OpenEMR 7.0.x
```
then *Modules > Manage Modules* → Install → Enable (or, as the web user: `su -s /bin/sh apache -c "php oe-module-grapheus/install/autoinstall.php"`).

**New practice — OpenEMR with Grapheus built in:** download `distribution/docker-compose.yml`, change the passwords, run `docker compose up -d`. It uses the image `ghcr.io/mikebirkheadmd-maker/openemr-with-grapheus:8.4.1`; Grapheus installs and enables itself on first start.

Sign up at https://scribe.exetazohealth.com, then press **Connect** in the Grapheus tab.

## License
GPL-3.0-or-later (see LICENSE), the same license as OpenEMR. The Grapheus service it connects to is a separate, paid product of Exetazo Health.

## Support
support@exetazohealth.com
