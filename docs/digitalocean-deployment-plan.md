# DigitalOcean Deployment Plan — OpenEMR Demo

**Goal:** Railway's Safety Sentinel (`https://safety-sentinel-production.up.railway.app`) talks to a
real OpenEMR instance running on a DigitalOcean droplet, with the Safety Check module UI visible in
the browser pointing at the Railway backend.

**Budget:** ~$32/month droplet, destroyed within 7 days after recording the demo video.
**Time estimate:** ~60-90 minutes of active work.

---

## Setup Summary (from interview)

| Item | Value |
|---|---|
| Droplet size | 4 GB RAM / 2 vCPU ($32/month) |
| Source data | 103 Synthea patients + Alice Smith, Bob Jones, Carol White |
| DB method | Full `mysqldump` from local Docker, restored on cloud |
| Module | `oe-module-safety-sentinel` (in repo, auto-mounted by dev-easy) |
| Railway URL | `https://safety-sentinel-production.up.railway.app` |
| OAuth2 client | Existing local client — credentials from `agents/safety-sentinel/.env` |
| Only Railway env var to update | `OPENEMR_BASE_URL` → `http://<DROPLET_IP>:8300` |

---

## 1. Pre-flight Checklist

Complete all of these **before** running any commands.

- [ ] `doctl auth list` shows your account as active
- [ ] `doctl compute ssh-key list` shows your SSH key fingerprint (note it — you need it in Phase 2)
- [ ] You have your Railway dashboard open and can edit environment variables
- [ ] `agents/safety-sentinel/.env` is open — you'll need `OPENEMR_CLIENT_ID` and
      `OPENEMR_CLIENT_SECRET` to confirm they carry over
- [ ] Your local OpenEMR Docker stack is running:
  ```bash
  cd docker/development-easy && docker compose ps
  # All services should show "healthy"
  ```
- [ ] You know your current public IP (needed for firewall rule):
  ```bash
  curl -s https://checkip.amazonaws.com
  # Note this output — replace <MY_IP> throughout this doc
  ```

---

## 2. Step-by-Step Commands

### Phase 1 — Create the Database Dump (local workstation, ~10 min)

Run from the **repo root**:

```bash
# Dump the full OpenEMR database from the running Docker MySQL container
cd docker/development-easy

docker compose exec mysql mysqldump \
  -u root -proot \
  --single-transaction \
  --routines \
  --triggers \
  --add-drop-table \
  openemr > ~/openemr_demo.sql

# Verify it has data
echo "Dump size: $(du -sh ~/openemr_demo.sql)"
# Expected: 50–300 MB depending on how much Synthea data is present

# Quick sanity check — should see patient table rows
head -50 ~/openemr_demo.sql | grep -i "INSERT INTO \`patient_data\`" || \
  grep -c "INSERT INTO" ~/openemr_demo.sql
```

**Expected output:** File exists, size is at least 10 MB, contains INSERT statements.

> **Note:** This dump includes the `oauth_clients` table with your registered client
> (`_YzrI4EhcCZFfPkarAAKPhhGK78CkDtXof9vt7slZNQ`). The cloud instance will accept the
> same `OPENEMR_CLIENT_ID` and `OPENEMR_CLIENT_SECRET` — no re-registration needed.

---

### Phase 2 — Provision the Droplet (~10 min)

```bash
# 1. Find your SSH key fingerprint
doctl compute ssh-key list
# Note the "FingerPrint" column value for your key — looks like aa:bb:cc:dd:...

# 2. Create the droplet (s-2vcpu-4gb = $24, s-4vcpu-8gb = $48)
#    If DO shows $32 for your selected size, check available sizes:
#    doctl compute size list | grep -E "2vcpu|4vcpu"
#    Then substitute the correct slug below.
doctl compute droplet create openemr-demo \
  --size s-2vcpu-4gb \
  --image ubuntu-22-04-x64 \
  --region nyc1 \
  --ssh-keys <YOUR_SSH_KEY_FINGERPRINT> \
  --wait

# 3. Get the droplet's public IP
doctl compute droplet list --format Name,PublicIPv4
# Note: replace <DROPLET_IP> with this value throughout the rest of this doc
```

**Expected output:** A line like `openemr-demo    167.x.x.x`

---

### Phase 3 — Configure the Firewall (~5 min)

```bash
# Get your current public IP first (if you haven't already)
MY_IP=$(curl -s https://checkip.amazonaws.com)
echo "My IP: $MY_IP"

# Get the droplet ID
DROPLET_ID=$(doctl compute droplet list --format ID,Name --no-header | grep openemr-demo | awk '{print $1}')
echo "Droplet ID: $DROPLET_ID"

# Create firewall:
# - Port 22 (SSH): open to all
# - Port 8300 (OpenEMR HTTP): open to all (Railway needs this)
# - Port 9300 (OpenEMR HTTPS): open to all (optional, self-signed cert)
# - Port 8310 (phpMyAdmin): restricted to YOUR IP only
doctl compute firewall create \
  --name openemr-demo-fw \
  --inbound-rules \
    "protocol:tcp,ports:22,address:0.0.0.0/0,address:::/0 \
     protocol:tcp,ports:8300,address:0.0.0.0/0,address:::/0 \
     protocol:tcp,ports:9300,address:0.0.0.0/0,address:::/0 \
     protocol:tcp,ports:8310,address:${MY_IP}/32" \
  --outbound-rules \
    "protocol:tcp,ports:all,address:0.0.0.0/0,address:::/0 \
     protocol:udp,ports:all,address:0.0.0.0/0,address:::/0"

# Get the firewall ID from the output above, then apply it to the droplet:
FIREWALL_ID=$(doctl compute firewall list --format ID,Name --no-header | grep openemr-demo-fw | awk '{print $1}')
doctl compute firewall add-droplets $FIREWALL_ID --droplet-ids $DROPLET_ID

echo "Firewall applied. phpMyAdmin (port 8310) restricted to $MY_IP only."
```

---

### Phase 4 — Install Docker on the Droplet (~10 min)

```bash
# SSH in with agent forwarding enabled (needed to git clone from GitHub)
ssh -A root@<DROPLET_IP>
```

Once on the droplet:

```bash
# Install Docker (official script — fine for demo environments)
curl -fsSL https://get.docker.com | sh

# Verify
docker --version       # Expected: Docker version 26.x or later
docker compose version # Expected: Docker Compose version v2.x
```

---

### Phase 5 — Clone the Repo and Start OpenEMR (~20 min, mostly waiting)

Still on the droplet:

```bash
# Clone your repo (SSH agent forwarding from Phase 4 allows this without
# adding a new key to GitHub)
git clone git@github.com:ryoiwata/openemr.git /opt/openemr

# Checkout the correct branch
cd /opt/openemr
git checkout deploy/openemr-cloud

# Navigate to dev-easy (this is where all docker compose commands run)
cd /opt/openemr/docker/development-easy

# Start OpenEMR — first run pulls Docker images and initializes the database.
# This takes 5-15 minutes on first start.
docker compose up --detach

echo "Waiting for OpenEMR to initialize..."
echo "Monitor with: docker compose logs -f openemr"
echo "Ready when you see: 'Apache is running' or the health check turns green."

# Watch progress (Ctrl+C to stop watching once you see it's healthy)
docker compose logs -f openemr 2>&1 | grep -E "Apache|complete|error|Error" &
LOG_PID=$!

# Poll until OpenEMR responds on port 8300
until curl -sf http://localhost:8300/login.php -o /dev/null; do
  echo "$(date): Waiting for OpenEMR..."
  sleep 15
done
kill $LOG_PID 2>/dev/null
echo "OpenEMR is up at http://localhost:8300"

# Confirm from outside (run this on your LOCAL machine, not the droplet):
# curl -sI http://<DROPLET_IP>:8300/login.php | head -5
# Expected: HTTP/1.1 200 OK
```

> **Important:** Do NOT restore the database dump until OpenEMR has fully initialized and responded
> on port 8300. The initialization creates the schema that the dump will populate.

---

### Phase 6 — Transfer and Restore the Database Dump (~15 min)

**Step 6a: Transfer the dump from your local machine to the droplet.**

Run this on your **local machine** (new terminal, not the SSH session):

```bash
scp ~/openemr_demo.sql root@<DROPLET_IP>:/root/openemr_demo.sql
echo "Transfer complete: $(du -sh ~/openemr_demo.sql)"
```

**Step 6b: Restore the dump.** Back on the **droplet**:

```bash
cd /opt/openemr/docker/development-easy

# Restore (this overwrites the freshly initialized DB with your patient data)
# Takes 2-10 minutes depending on dump size
echo "Restoring database dump..."
docker compose exec -T mysql mysql -u root -proot openemr < /root/openemr_demo.sql
echo "Restore complete."

# Quick sanity check — count patients
docker compose exec mysql mysql -u root -proot openemr -e \
  "SELECT COUNT(*) AS patient_count FROM patient_data;"
# Expected: ~106 rows (103 Synthea + Alice + Bob + Carol)

# Confirm test patients are present
docker compose exec mysql mysql -u root -proot openemr -e \
  "SELECT pid, fname, lname FROM patient_data WHERE lname IN ('Smith','Jones','White') ORDER BY lname;"
```

---

### Phase 7 — Configure the Cloud Instance (~10 min)

**Step 7a: Point the Safety Sentinel module at Railway.**

The iframe URL is read from `$GLOBALS['safety_sentinel_url']` (falls back to `localhost:8001`).
After dump restore, this is still set to `localhost:8001`. Update it to the Railway URL:

```bash
cd /opt/openemr/docker/development-easy

docker compose exec mysql mysql -u root -proot openemr -e "
  INSERT INTO globals (gl_name, gl_value)
  VALUES ('safety_sentinel_url', 'https://safety-sentinel-production.up.railway.app')
  ON DUPLICATE KEY UPDATE gl_value = 'https://safety-sentinel-production.up.railway.app';
"

# Verify
docker compose exec mysql mysql -u root -proot openemr -e \
  "SELECT gl_name, gl_value FROM globals WHERE gl_name = 'safety_sentinel_url';"
# Expected: safety_sentinel_url | https://safety-sentinel-production.up.railway.app
```

**Step 7b: Restart OpenEMR to flush any cached globals.**

```bash
docker compose restart openemr

# Wait for it to come back
until curl -sf http://localhost:8300/login.php -o /dev/null; do
  echo "Waiting for restart..."; sleep 5
done
echo "OpenEMR is back up."
```

**Step 7c: Change the default admin password.**

```bash
# Log in to OpenEMR at http://<DROPLET_IP>:8300 in your browser
# Admin > Administration > Users > admin → change password
# Pick something you'll remember for the demo (not 'pass')
echo "Reminder: change admin password at http://<DROPLET_IP>:8300"
echo "Admin > Administration > Users > admin"
```

---

### Phase 8 — Test OAuth2 from the Droplet (~5 min)

Confirm OAuth2 works with the **same credentials** from your local `.env` before touching Railway:

```bash
# On the droplet — substitute the values from agents/safety-sentinel/.env
CLIENT_ID="_YzrI4EhcCZFfPkarAAKPhhGK78CkDtXof9vt7slZNQ"
CLIENT_SECRET="<YOUR_CLIENT_SECRET_FROM_ENV>"
ADMIN_PASS="<YOUR_NEW_ADMIN_PASSWORD>"

curl -s -X POST http://localhost:8300/oauth2/default/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=${CLIENT_ID}" \
  -d "client_secret=${CLIENT_SECRET}" \
  -d "user_role=users" \
  -d "username=admin" \
  -d "password=${ADMIN_PASS}" \
  -d "scope=openid offline_access api:oemr user/allergy.read user/medication.read user/patient.read user/medical_problem.read" \
  | python3 -m json.tool
```

**Expected:** JSON with `access_token`, `token_type: "Bearer"`, `expires_in`.

If you see `{"error":"invalid_client"}` → the client credentials didn't carry over from the dump.
See **Rollback Plan > OAuth2 fails** below.

**Test a patient API call:**

```bash
# Use the access_token from above
TOKEN="<access_token_from_above>"

curl -s http://localhost:8300/api/patient/a127903f-6859-4921-8910-bd1872393103/allergy \
  -H "Authorization: Bearer ${TOKEN}" \
  | python3 -m json.tool
# Expected: JSON array (empty or with Alice's allergies)

curl -s "http://localhost:8300/api/patient?uuid=a127903f-6859-4921-8910-bd1872393103" \
  -H "Authorization: Bearer ${TOKEN}" \
  | python3 -m json.tool
# Expected: Alice Smith's patient record
```

---

### Phase 9 — Update Railway and Verify End-to-End (~10 min)

**Step 9a: Update Railway environment variables** (via Railway dashboard).

Only ONE variable needs to change — everything else carries over from your local setup:

| Variable | Old Value | New Value |
|---|---|---|
| `OPENEMR_BASE_URL` | `http://localhost:8300` (or unset) | `http://<DROPLET_IP>:8300` |
| `OPENEMR_CLIENT_ID` | _(unchanged)_ | _(unchanged)_ |
| `OPENEMR_CLIENT_SECRET` | _(unchanged)_ | _(unchanged)_ |
| `OPENEMR_USERNAME` | `admin` | `admin` |
| `OPENEMR_PASSWORD` | `pass` | _(update to your new admin password)_ |

**Step 9b: Redeploy Railway.**

```bash
# From your local machine, in the agents/safety-sentinel directory:
cd agents/safety-sentinel
railway up
```

**Step 9c: Verify Railway can reach OpenEMR.**

```bash
# Health check
curl -s https://safety-sentinel-production.up.railway.app/health | python3 -m json.tool
# Expected: {"status": "healthy", "data_source": "openemr", ...}
# If data_source is "mock", Railway isn't reaching the droplet — see Rollback Plan

# Full safety check against Alice Smith (drug interaction demo)
curl -s -X POST https://safety-sentinel-production.up.railway.app/api/v1/safety-check \
  -H "Content-Type: application/json" \
  -d '{
    "patient_uuid": "a127903f-6859-4921-8910-bd1872393103",
    "drug_name": "ibuprofen",
    "drug_rxnorm": "5640"
  }' | python3 -m json.tool
# Expected: severity: "major" or "moderate" (warfarin + ibuprofen interaction)
```

---

## 3. Security Hardening (Quick — Demo Environment)

These take 5 minutes and protect the droplet during the demo window.

**a) Admin password** — covered in Phase 7c. Do NOT leave `pass` as the password on a public IP.

**b) phpMyAdmin restricted to your IP** — covered by the firewall rule in Phase 3.
Port 8310 is only accessible from `<MY_IP>`. Verify:
```bash
# From a different machine/network, this should time out:
curl --connect-timeout 5 http://<DROPLET_IP>:8310/
```

**c) Rotate the firewall rule when your IP changes** (e.g., if you're on a different network on demo day):
```bash
MY_NEW_IP=$(curl -s https://checkip.amazonaws.com)
FIREWALL_ID=$(doctl compute firewall list --format ID,Name --no-header | grep openemr-demo-fw | awk '{print $1}')
# Update the firewall via the DO dashboard (Networking > Firewalls > openemr-demo-fw)
# or use doctl to recreate the inbound rule for port 8310
```

**d) No action needed for the demo:** TLS, secrets management, rate limiting. This is a
temporary ephemeral environment destroyed within 7 days — keep hardening proportional.

---

## 4. Rollback Plan

### If OpenEMR never becomes healthy (Phase 5)

```bash
# Check logs for errors
docker compose logs openemr --tail=100 | grep -iE "error|fatal|fail"
docker compose logs mysql --tail=50

# Common fix: give it more time (initialization can take 15+ min on first pull)
# Nuclear option: destroy and retry
docker compose down -v  # WARNING: wipes the database volume
docker compose up --detach
```

### If OAuth2 returns `invalid_client` (Phase 8)

The dump restore didn't carry over the OAuth client. Re-register a fresh one:

```bash
cd /opt/openemr/docker/development-easy
docker compose exec openemr /root/devtools register-oauth2-client
# Outputs: new CLIENT_ID and CLIENT_SECRET — update Railway with these values
```

Then update Railway's `OPENEMR_CLIENT_ID` and `OPENEMR_CLIENT_SECRET` to the new values and redeploy.

### If Railway shows `data_source: mock` (Phase 9)

Railway isn't reaching the droplet. Debug in order:

```bash
# 1. Confirm OpenEMR is actually accessible from the public internet
curl -I http://<DROPLET_IP>:8300/login.php
# If this fails → firewall or Docker port binding issue

# 2. Check Railway env vars — is OPENEMR_BASE_URL set correctly?
#    Go to Railway dashboard → Variables → confirm http://<DROPLET_IP>:8300

# 3. Check Railway logs for the connection error
#    Railway dashboard → Deployments → latest deploy → View Logs

# 4. Test OAuth2 from your local machine (not the droplet)
#    This simulates what Railway does
curl -s -X POST http://<DROPLET_IP>:8300/oauth2/default/token \
  -d "grant_type=password&client_id=...&client_secret=...&..."
```

### If the module tab is missing from patient chart

The module is already registered in the dump's `modules` table. If it's missing:

```bash
# Check module registration
docker compose exec mysql mysql -u root -proot openemr -e \
  "SELECT mod_directory, mod_active FROM modules WHERE mod_directory LIKE '%sentinel%';"

# If not present, register it via the UI:
# OpenEMR → Admin > Modules > Manage Modules → find oe-module-safety-sentinel → Install → Activate

# If present but inactive:
docker compose exec mysql mysql -u root -proot openemr -e \
  "UPDATE modules SET mod_active = 1 WHERE mod_directory = 'oe-module-safety-sentinel';"
```

### Emergency: Revert Railway to mock data

If the cloud connection is broken and you need Railway working immediately:

```bash
# In Railway dashboard → Variables:
# Remove OPENEMR_BASE_URL (or set to a non-existent URL)
# The agent falls back to mock data automatically (per src/agent/tools.py)
railway up
```

---

## 5. Verification Checklist

Run these in order. Each must pass before moving to the next.

### ✅ Check 1: OpenEMR login page loads from droplet IP

```bash
curl -sI http://<DROPLET_IP>:8300/login.php | head -3
# Expected: HTTP/1.1 200 OK
```

### ✅ Check 2: OAuth2 token from your local machine (simulates Railway's call)

```bash
curl -s -X POST http://<DROPLET_IP>:8300/oauth2/default/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=_YzrI4EhcCZFfPkarAAKPhhGK78CkDtXof9vt7slZNQ" \
  -d "client_secret=<YOUR_CLIENT_SECRET>" \
  -d "user_role=users" \
  -d "username=admin" \
  -d "password=<YOUR_ADMIN_PASSWORD>" \
  -d "scope=openid offline_access api:oemr user/allergy.read user/medication.read user/patient.read user/medical_problem.read" \
  | python3 -m json.tool | grep access_token
# Expected: "access_token": "<long JWT string>"
```

### ✅ Check 3: Alice Smith's patient data is accessible

```bash
TOKEN="<access_token from Check 2>"

curl -s "http://<DROPLET_IP>:8300/api/patient?uuid=a127903f-6859-4921-8910-bd1872393103" \
  -H "Authorization: Bearer ${TOKEN}" \
  | python3 -m json.tool | grep -E "fname|lname"
# Expected: "fname": "Alice", "lname": "Smith"
```

### ✅ Check 4: Bob Jones has penicillin allergy

```bash
curl -s "http://<DROPLET_IP>:8300/api/patient/a127906a-95df-4da5-b8b4-3d8665fbd939/allergy" \
  -H "Authorization: Bearer ${TOKEN}" \
  | python3 -m json.tool | grep -i "penicillin"
# Expected: a result containing "Penicillin"
```

### ✅ Check 5: Railway reports live OpenEMR data source

```bash
curl -s https://safety-sentinel-production.up.railway.app/health | python3 -m json.tool
# Expected: "data_source": "openemr"  (NOT "mock")
```

### ✅ Check 6: Safety check for warfarin + ibuprofen returns major/moderate

```bash
curl -s -X POST https://safety-sentinel-production.up.railway.app/api/v1/safety-check \
  -H "Content-Type: application/json" \
  -d '{
    "patient_uuid": "a127903f-6859-4921-8910-bd1872393103",
    "drug_name": "ibuprofen",
    "drug_rxnorm": "5640"
  }' | python3 -m json.tool | grep severity
# Expected: "severity": "major" or "moderate"
```

### ✅ Check 7: Safety Sentinel tab appears in browser for a patient

1. Open `http://<DROPLET_IP>:8300` in browser
2. Log in as admin
3. Find → select Alice Smith
4. Click **Safety Check** tab in patient chart
5. iframe should load `https://safety-sentinel-production.up.railway.app/?patient_id=a127903f...`

---

## 6. Teardown Instructions

After the demo is recorded:

### Step 1: Revert Railway to mock data

In the Railway dashboard:
- Set `OPENEMR_BASE_URL` to empty or remove it
- `railway up` to redeploy

### Step 2: Destroy the droplet and firewall

```bash
# Get droplet ID
DROPLET_ID=$(doctl compute droplet list --format ID,Name --no-header | grep openemr-demo | awk '{print $1}')
FIREWALL_ID=$(doctl compute firewall list --format ID,Name --no-header | grep openemr-demo-fw | awk '{print $1}')

# Destroy firewall first
doctl compute firewall delete $FIREWALL_ID --force

# Destroy droplet (IRREVERSIBLE — all data gone)
doctl compute droplet delete $DROPLET_ID --force

echo "Droplet and firewall destroyed."

# Confirm it's gone
doctl compute droplet list | grep openemr-demo
# Should return nothing
```

### Step 3: Clean up local dump file

```bash
rm ~/openemr_demo.sql
echo "Local dump deleted."
```

**Cost:** Even if you forget for a full week, the maximum charge is $32 × (7/30) ≈ $7.50.
DigitalOcean bills by the hour, so destroying promptly saves money.

---

## Appendix: Key Values Quick Reference

| Item | Value |
|---|---|
| Droplet name | `openemr-demo` |
| OpenEMR HTTP | `http://<DROPLET_IP>:8300` |
| OpenEMR HTTPS | `https://<DROPLET_IP>:9300` (self-signed, use `-k` with curl) |
| phpMyAdmin | `http://<DROPLET_IP>:8310` (your IP only) |
| Railway app | `https://safety-sentinel-production.up.railway.app` |
| MySQL root | `root` / `root` |
| MySQL openemr user | `openemr` / `openemr` |
| OAuth2 client ID | `_YzrI4EhcCZFfPkarAAKPhhGK78CkDtXof9vt7slZNQ` |
| Alice Smith UUID | `a127903f-6859-4921-8910-bd1872393103` |
| Bob Jones UUID | `a127906a-95df-4da5-b8b4-3d8665fbd939` |
| Carol White UUID | `a127906b-5c08-4517-a235-16785217b84a` |
| Repo root on droplet | `/opt/openemr` |
| Docker compose dir | `/opt/openemr/docker/development-easy` |
