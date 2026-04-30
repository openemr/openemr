FROM openemr/openemr:latest

# NOTE: We previously overlaid interface/patient_file/summary/demographics.php
# from this fork to inject a Co-Pilot iframe rail. The fork's PHP is newer
# than what's in openemr/openemr:latest (e.g. SessionWrapperFactory has
# methods the image lacks), so the overlay 500'd at runtime. For Early
# Submission we revert to the stock image's PHP and use the standalone
# Co-Pilot URL for the demo. Plan for Sunday: pin a matching image tag
# (or inject the iframe via a less invasive event-listener hook).

# Ensure the Apache TLS cert exists on every container boot.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
