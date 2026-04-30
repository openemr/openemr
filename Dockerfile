FROM openemr/openemr:latest

# Overlay our fork's modified PHP files on top of the upstream image. The
# upstream image bakes the full OpenEMR PHP tree at build time; our local
# edits never reach Railway unless we COPY them explicitly here. Add one
# entry per modified file to keep the diff against upstream auditable.
COPY interface/patient_file/summary/demographics.php \
     /var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php
RUN chown www:www /var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php \
 && chmod 400 /var/www/localhost/htdocs/openemr/interface/patient_file/summary/demographics.php

# Ensure the Apache TLS cert exists on every container boot — see
# railway-entrypoint.sh header for why the upstream image's first-boot-only
# cert generation breaks Railway redeploys with persisted volumes.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
