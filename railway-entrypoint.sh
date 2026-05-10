#!/bin/sh
# Railway-side wrapper around the upstream openemr/openemr entrypoint.
#
# Two responsibilities beyond the upstream entrypoint:
#
#   1. Make Apache TLS cert generation idempotent. The upstream image
#      generates the cert only on first-ever boot; persistent volumes
#      then mark the install as initialized, so cert regeneration is
#      skipped on subsequent container instances even though the cert
#      itself lives outside the volume mount. This is the original
#      reason this wrapper exists.
#
#   2. Start the co-resident Next.js dashboard on loopback :3000 before
#      handing off to Apache. Apache mod_proxy (configured by
#      /etc/apache2/conf.d/dashboard-proxy.conf) forwards /modern/* to
#      the Node process. The dashboard is built with basePath="/modern"
#      so paths round-trip cleanly.

set -e

CERT=/etc/ssl/certs/webserver.cert.pem
KEY=/etc/ssl/private/webserver.key.pem

if [ ! -s "$CERT" ] || [ ! -s "$KEY" ]; then
    mkdir -p /etc/ssl/certs /etc/ssl/private
    openssl req -x509 -nodes -days 730 -newkey rsa:2048 \
        -keyout "$KEY" -out "$CERT" \
        -subj "/CN=openemr-production-0c8c.up.railway.app" \
        -addext "subjectAltName = DNS:openemr-production-0c8c.up.railway.app" \
        > /dev/null 2>&1
    chmod 600 "$KEY"
    chmod 644 "$CERT"
    echo "railway-entrypoint: generated self-signed TLS cert at $CERT"
fi

# Start the modern dashboard on 127.0.0.1:3000. Apache mod_proxy fronts
# it at /modern/*. Run in the background so this script can hand off to
# the upstream openemr entrypoint (Apache foreground) as PID 1.
if [ -f /opt/dashboard/server.js ]; then
    (cd /opt/dashboard && \
        NODE_ENV=production HOSTNAME=127.0.0.1 PORT=3000 \
        node server.js \
        >&2 2>&1) &
    DASHBOARD_PID=$!
    echo "railway-entrypoint: started modern dashboard (pid $DASHBOARD_PID) on 127.0.0.1:3000"
    # Reap the Node child if Apache exits (so the container actually
    # stops on shutdown instead of lingering on the orphaned Node).
    trap 'kill "$DASHBOARD_PID" 2>/dev/null || true' TERM INT EXIT
else
    echo "railway-entrypoint: WARN — /opt/dashboard/server.js missing; /modern/* will 502" >&2
fi

exec ./openemr.sh "$@"
