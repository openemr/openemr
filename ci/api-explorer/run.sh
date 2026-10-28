#!/usr/bin/env bash
#
# Runs the oe-module-api-explorer round-trip smoke against an already-booted
# development-easy stack. Clones the explorer at the current main HEAD, waits
# for OpenEMR to be responsive, seeds demo data + globals, and invokes the
# Guzzle driver inside the openemr container.
#
# Env:
#   OPENEMR_READY_TIMEOUT  -- seconds to wait for OpenEMR to respond (default 300)
#
# Exits 0 on green, nonzero on any failure (the workflow dumps docker logs
# on exit).

set -euo pipefail

ready_timeout="${OPENEMR_READY_TIMEOUT:-300}"

repo_root="$(git rev-parse --show-toplevel)"
cd "${repo_root}"

# --------------------------------------------------------------------------
# 1. Clone the explorer at current main HEAD into devtools/ on the host side.
#    The dev-easy compose bind-mounts the openemr root into the container,
#    so devtools/oe-module-api-explorer appears at /var/www/.../devtools
#    inside the openemr container.
# --------------------------------------------------------------------------
stage="$(mktemp -d)"
trap 'rm -rf "${stage}"' EXIT

echo "==> Cloning oe-module-api-explorer (main HEAD)"
git clone --quiet --depth 1 --branch main https://github.com/openemr/oe-module-api-explorer.git "${stage}/explorer"
rm -rf "${stage}/explorer/.git"

mkdir -p devtools
rm -rf devtools/oe-module-api-explorer
mv "${stage}/explorer" devtools/oe-module-api-explorer
mkdir -p devtools/oe-module-api-explorer/clients_keys

# --------------------------------------------------------------------------
# 2. CI-only explorer config: single 'docker' site reachable at https://openemr
#    inside the compose network. The explorer disables TLS verification by
#    design for self-signed dev certificates (SECURITY.md), which is why the
#    driver can talk to https://openemr without pre-trusting the cert.
# --------------------------------------------------------------------------
cat > devtools/oe-module-api-explorer/config.local.php <<'EOF'
<?php

return [
    'sites' => [
        'docker' => 'https://openemr',
    ],
    'default_site' => 'docker',
    'app_url' => 'https://openemr/devtools/oe-module-api-explorer',
];
EOF

# --------------------------------------------------------------------------
# 3. Wait for OpenEMR to be responsive. The dev-easy compose up --wait only
#    gates on the container's healthcheck, which some versions mark healthy
#    before apache finishes starting.
# --------------------------------------------------------------------------
echo "==> Waiting for OpenEMR to respond (timeout ${ready_timeout}s)"
start="$(date +%s)"
until openemr-cmd e 'curl -skf -o /dev/null https://localhost/'; do
    now="$(date +%s)"
    if (( now - start >= ready_timeout )); then
        echo "::error::OpenEMR did not become responsive within ${ready_timeout}s"
        exit 1
    fi
    sleep 5
done
echo "    responsive"

# --------------------------------------------------------------------------
# 4. In-container setup: chown + chmod the explorer dir so apache can write
#    clients_keys/, seed demo data via drid (patients the FHIR Patient read
#    expects), then re-seed the globals the explorer needs (drid rewrites
#    site_addr_oath so this must happen AFTER drid).
# --------------------------------------------------------------------------
echo "==> Fixing explorer file ownership + permissions"
openemr-cmd e 'chown -R apache:apache /var/www/localhost/htdocs/openemr/devtools'
openemr-cmd e 'chmod 775 /var/www/localhost/htdocs/openemr/devtools/oe-module-api-explorer/clients_keys'

echo "==> Seeding demo data (drid)"
openemr-cmd drid >/dev/null

echo "==> Seeding globals"
openemr-cmd e 'mariadb -h mysql -u openemr -popenemr openemr < /var/www/localhost/htdocs/openemr/ci/api-explorer/seed-globals.sql'

# --------------------------------------------------------------------------
# 5. Run the Guzzle driver (JWT client-credentials path -- fast, server-to-
#    server, no browser), then the Panther driver (confidential auth-code +
#    public PKCE paths -- browser login + consent click-through), then the
#    password-grant driver (admin-credentialed token exchange -- no browser,
#    no explorer client, DCR + DB flip + token + FHIR read), then the FHIR
#    write-path driver (same DCR + DB-flip + password-grant lane, but
#    exercising POST / PUT / GET round-trip against /fhir/CareTeam), then
#    the portal-api driver (portal patient fixture + password grant +
#    /apis/default/portal/patient read -- closes the external-realism gap
#    for the patient-role / portal-api surface distinct from FHIR). Exits
#    nonzero on any failure; the workflow uploads docker logs + driver-
#    browser screenshots on failure for post-mortem.
# --------------------------------------------------------------------------
echo "==> Running driver (JWT)"
openemr-cmd e 'php /var/www/localhost/htdocs/openemr/ci/api-explorer/driver.php'

echo "==> Running driver-browser (confidential auth-code + public PKCE)"
openemr-cmd e 'php /var/www/localhost/htdocs/openemr/ci/api-explorer/driver-browser.php'

echo "==> Running driver-password (password grant, user_role=users)"
openemr-cmd e 'php /var/www/localhost/htdocs/openemr/ci/api-explorer/driver-password.php'

echo "==> Running driver-fhir-writes (FHIR CareTeam POST + PUT + GET)"
openemr-cmd e 'php /var/www/localhost/htdocs/openemr/ci/api-explorer/driver-fhir-writes.php'

echo "==> Running driver-portal (portal patient password grant + /portal/patient read)"
# driver-portal.php bootstraps interface/globals.php so it can call
# PortalPatientFixtureManager; globals.php's RootCliGuard refuses to run
# under uid=0. Drop to apache (same uid phpunit tests run under).
openemr-cmd e 'su -s /bin/sh apache -c "php /var/www/localhost/htdocs/openemr/ci/api-explorer/driver-portal.php"'
