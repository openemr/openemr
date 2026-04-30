FROM openemr/openemr:latest

# Overlay our fork's modified PHP. Must be COPY'd as apache:apache —
# OpenEMR's runtime hardening (in openemr.sh) resets mode to 400 but does
# not re-chown overlay files, so a default root-owned COPY would fail with
# "PHP Fatal error: Failed opening required ... Permission denied" once
# the runtime hardening fires.
COPY --chown=apache:apache interface/patient_file/summary/demographics.php /var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php

# Ensure the Apache TLS cert exists on every container boot.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
