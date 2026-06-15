#!/usr/bin/env bash
# ============================================================================
# OpenEMR SSL/TLS Configuration Script
# ============================================================================
# This script configures SSL/TLS certificates for the Apache web server.
# It supports two modes:
#   1. Self-signed certificates (default) - for development and testing
#   2. Let's Encrypt certificates (when DOMAIN is set) - for production
#
# Environment Variables:
#   DOMAIN    - Domain name for Let's Encrypt certificate (optional)
#   EMAIL     - Email address for Let's Encrypt notifications (recommended)
#   OPERATOR  - Set to "yes" to enable cron for certificate renewal
#
# Usage:
#   Called automatically by openemr.sh during container startup
# ============================================================================

set -e

# ============================================================================
# SELF-SIGNED CERTIFICATE GENERATION
# ============================================================================
# Generate a self-signed certificate if one doesn't exist.
# This is used as a fallback when Let's Encrypt is not configured.
# Self-signed certificates are suitable for development/testing but will
# trigger browser security warnings in production.

if ! [[ -f /etc/ssl/private/selfsigned.key.pem ]]; then
    echo "Generating self-signed SSL certificate..."
    # Ensure directories exist and are writable
    mkdir -p /etc/ssl/private /etc/ssl/certs
    # Try to generate certificate, but don't fail if /etc/ssl is read-only
    if openssl req -x509 -newkey rsa:4096 \
        -keyout /etc/ssl/private/selfsigned.key.pem \
        -out /etc/ssl/certs/selfsigned.cert.pem \
        -days 365 -nodes \
        -subj "/C=xx/ST=x/L=x/O=x/OU=x/CN=localhost" 2>/dev/null; then
        echo "Self-signed certificate generated"
    else
        echo "Warning: Could not generate self-signed certificate (read-only filesystem?)"
        echo "SSL will be disabled if certificates are not available"
    fi
fi

# ============================================================================
# CONFIGURE SELF-SIGNED CERTIFICATE AS DEFAULT
# ============================================================================
# Link the self-signed certificate as the default webserver certificate
# if Let's Encrypt hasn't been configured yet. This ensures Apache can
# start even without a domain configured.

if [[ ! -f /etc/ssl/docker-selfsigned-configured ]]; then
    # Only configure if certificate files exist
    if [[ -f /etc/ssl/private/selfsigned.key.pem ]] && [[ -f /etc/ssl/certs/selfsigned.cert.pem ]]; then
        echo "Configuring self-signed certificate as default..."
        rm -f /etc/ssl/certs/webserver.cert.pem
        rm -f /etc/ssl/private/webserver.key.pem
        ln -sf /etc/ssl/certs/selfsigned.cert.pem /etc/ssl/certs/webserver.cert.pem
        ln -sf /etc/ssl/private/selfsigned.key.pem /etc/ssl/private/webserver.key.pem
        touch /etc/ssl/docker-selfsigned-configured
        echo "Self-signed certificate configured"
    else
        echo "Warning: Self-signed certificates not available, SSL may be disabled"
    fi
fi

# ============================================================================
# LET'S ENCRYPT CERTIFICATE CONFIGURATION
# ============================================================================
# If a domain is specified, obtain and configure Let's Encrypt certificates.
# Let's Encrypt provides free, trusted SSL certificates suitable for production.
# Certificates are automatically renewed via cron.

if [[ "${DOMAIN:-}" != "" ]]; then
    # Validate email address (recommended but not required)
    if [[ "${EMAIL:-}" != "" ]]; then
        EMAIL_ARG="-m ${EMAIL}"
    else
        echo "WARNING: Setting an email via \$EMAIL is HIGHLY RECOMMENDED in order to"
        echo "         receive alerts from Let's Encrypt about your SSL certificate."
    fi

    # ========================================================================
    # OBTAIN LET'S ENCRYPT CERTIFICATE
    # ========================================================================
    # Request a certificate from Let's Encrypt if one doesn't exist.
    # This requires:
    #   - Apache to be running (for webroot validation)
    #   - The domain to be publicly accessible
    #   - Port 80 to be open for HTTP validation
    if ! [[ -f "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" ]]; then
        echo "Obtaining Let's Encrypt certificate for domain: ${DOMAIN}"
        # Start Apache temporarily for Let's Encrypt validation
        /usr/sbin/httpd -k start
        sleep 2
        
        # Request certificate using webroot validation method
        if [[ -n "${EMAIL_ARG:-}" ]]; then
            certbot certonly --webroot -n -w /var/www/localhost/htdocs/openemr/ -d "${DOMAIN}" "${EMAIL_ARG}" --agree-tos
        else
            certbot certonly --webroot -n -w /var/www/localhost/htdocs/openemr/ -d "${DOMAIN}" --agree-tos
        fi
        
        # Stop Apache (it will be started again by openemr.sh)
        /usr/sbin/httpd -k stop
        
        # Schedule automatic renewal via cron (runs daily at 23:01)
        # The renewal uses graceful restart to avoid downtime
        echo "1 23  *   *   *   certbot renew -q --post-hook \"httpd -k graceful\"" >> /etc/crontabs/root
        echo "Let's Encrypt certificate obtained and renewal scheduled"
    fi

    # ========================================================================
    # CONFIGURE LET'S ENCRYPT CERTIFICATE AS DEFAULT
    # ========================================================================
    # Link the Let's Encrypt certificate as the default webserver certificate.
    # This replaces the self-signed certificate with the trusted Let's Encrypt one.
    if [[ ! -f /etc/ssl/docker-letsencrypt-configured ]]; then
        echo "Configuring Let's Encrypt certificate as default..."
        rm -f /etc/ssl/certs/webserver.cert.pem
        rm -f /etc/ssl/private/webserver.key.pem
        ln -s "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" /etc/ssl/certs/webserver.cert.pem
        ln -s "/etc/letsencrypt/live/${DOMAIN}/privkey.pem" /etc/ssl/private/webserver.key.pem
        touch /etc/ssl/docker-letsencrypt-configured
        echo "Let's Encrypt certificate configured"
    fi

    # ========================================================================
    # START CRON FOR CERTIFICATE RENEWAL
    # ========================================================================
    # Start the cron daemon to handle automatic certificate renewal.
    # Only start cron if this container is an operator (serves web requests).
    # Non-operator containers (e.g., Kubernetes admin jobs) don't need cron.
    if [[ "${OPERATOR:-}" = "yes" ]]; then
        echo "Starting cron daemon for certificate renewal..."
        crond
    fi
fi

echo "SSL configuration completed"
