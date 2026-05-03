FROM openemr/openemr:latest

# Inject Co-Pilot per-physician scope enforcement into the stock image.
# We can't COPY our fork's PHP files (the fork's class library expects
# methods newer than openemr:latest ships); instead we surgically patch
# the upstream files at build time via awk:
#
#   1. demographics.php gets TWO injections in one awk pass:
#      - copilot-demographics-gate.php: panel access gate inserted right
#        after `require_once globals.php` so URL-poking a patient outside
#        the clinician's panel returns 403.
#      - copilot-rail-fragment.php: Co-Pilot iframe rail inserted before
#        </body> so every chart view gets the side-panel.
#
#   2. dynamic_finder_ajax.php gets ONE injection:
#      - copilot-finder-scope.php: appends `AND providerID = <user_id>`
#        to $customWhere so the patient-finder only returns patients in
#        the logged-in clinician's panel.
#
# All three fragments use only sqlQuery() + UuidRegistry, both present in
# openemr:latest. 'admin' bypasses every gate (sees everything).
COPY copilot-rail-fragment.php /tmp/copilot-rail-fragment.php
COPY copilot-demographics-gate.php /tmp/copilot-demographics-gate.php
COPY copilot-finder-scope.php /tmp/copilot-finder-scope.php
RUN DEMO=/var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php \
 && FIND=/var/www/localhost/htdocs/openemr/interface/main/finder/dynamic_finder_ajax.php \
 && awk ' \
      /require_once\("\.\.\/\.\.\/globals\.php"\);/ && !gate_done { \
        print; \
        while ((getline line < "/tmp/copilot-demographics-gate.php") > 0) print line; \
        gate_done=1; next \
      } \
      /<\/body>/ && !rail_done { \
        while ((getline line < "/tmp/copilot-rail-fragment.php") > 0) print line; \
        rail_done=1 \
      } \
      {print} \
    ' "$DEMO" > "${DEMO}.new" \
 && chown apache:apache "${DEMO}.new" \
 && chmod 444 "${DEMO}.new" \
 && mv "${DEMO}.new" "$DEMO" \
 && awk ' \
      /Get total number of rows in the table\./ && !done { \
        while ((getline line < "/tmp/copilot-finder-scope.php") > 0) print line; \
        done=1 \
      } \
      {print} \
    ' "$FIND" > "${FIND}.new" \
 && chown apache:apache "${FIND}.new" \
 && chmod 444 "${FIND}.new" \
 && mv "${FIND}.new" "$FIND" \
 && rm /tmp/copilot-rail-fragment.php /tmp/copilot-demographics-gate.php /tmp/copilot-finder-scope.php \
 && (grep -q "copilot-rail" "$DEMO" || (echo "FATAL: copilot rail injection failed" && exit 1)) \
 && (grep -q "copilotPanelGate" "$DEMO" || (echo "FATAL: demographics gate injection failed" && exit 1)) \
 && (grep -q "copilotProviderFilter" "$FIND" || (echo "FATAL: finder scope injection failed" && exit 1))

# Ensure the Apache TLS cert exists on every container boot.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
