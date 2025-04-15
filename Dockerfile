FROM openemr/openemr:7.0.3

# Install OpenSSL if not already installed
RUN apk add --no-cache openssl

# Generate self-signed SSL certificates
RUN mkdir -p /etc/ssl/certs/ /etc/ssl/private/ && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/webserver.key.pem \
    -out /etc/ssl/certs/webserver.cert.pem \
    -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Create entrypoint script to handle permissions
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Use custom entrypoint
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["sh", "-c", "httpd -D FOREGROUND"]