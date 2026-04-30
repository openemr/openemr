#!/bin/sh
# Railway-side wrapper around the upstream openemr/openemr entrypoint.
#
# Why this exists: the upstream image's bootstrap generates the Apache TLS cert
# only on first-ever boot. When Railway redeploys, a fresh container instance
# mounts the persisted volume, sees "already initialized" markers, and skips
# cert generation — but the cert lives outside the volume mount, so Apache then
# fails to start with:
#
#   AH00526: Syntax error on line 73 of /etc/apache2/conf.d/openemr.conf:
#   SSLCertificateFile: file '/etc/ssl/certs/webserver.cert.pem' does not exist
#
# This wrapper makes cert generation idempotent: it runs every container boot
# and is a no-op when the cert is already present.

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

exec ./openemr.sh "$@"
