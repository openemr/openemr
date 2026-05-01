FROM openemr/openemr:latest

# Inject the Clinical Co-Pilot iframe rail into the stock image's
# demographics.php. We can't COPY our fork's full file (its PHP class
# library expects newer methods than openemr:latest ships); instead we
# append a self-contained fragment just before </body> via awk. The
# fragment uses only sqlQuery() + UuidRegistry, both present upstream.
COPY copilot-rail-fragment.php /tmp/copilot-rail-fragment.php
RUN DEMO=/var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php \
 && awk '/<\/body>/ && !done {while ((getline line < "/tmp/copilot-rail-fragment.php") > 0) print line; done=1} {print}' "$DEMO" > "${DEMO}.new" \
 && chown apache:apache "${DEMO}.new" \
 && chmod 444 "${DEMO}.new" \
 && mv "${DEMO}.new" "$DEMO" \
 && rm /tmp/copilot-rail-fragment.php \
 && grep -q "copilot-rail" "$DEMO" || (echo "FATAL: copilot rail injection failed" && exit 1)

# Ensure the Apache TLS cert exists on every container boot.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
