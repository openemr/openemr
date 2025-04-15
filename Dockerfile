FROM openemr/openemr:7.0.3

# Create entrypoint script to handle permissions
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Use custom entrypoint
ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["sh", "-c", "httpd -D FOREGROUND"]