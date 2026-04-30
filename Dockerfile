FROM openemr/openemr:latest

# Ensure the Apache TLS cert exists on every container boot — see
# railway-entrypoint.sh header for why the upstream image's first-boot-only
# cert generation breaks Railway redeploys with persisted volumes.
COPY railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh
RUN chmod +x /usr/local/bin/railway-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
