FROM openemr/openemr:latest

# Overlay our fork's modified PHP. Upstream image bakes the OpenEMR tree;
# without explicit COPY our fork edits never reach Railway. World-readable
# (444) is fine — PHP files only need to be readable by the apache process.
COPY interface/patient_file/summary/demographics.php /var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php
RUN chmod 444 /var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php

# Ensure the Apache TLS cert exists on every container boot.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
