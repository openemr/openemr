FROM openemr/openemr:7.0.3

# Copy our custom entrypoint script
COPY docker-entrypoint.sh /docker-entrypoint.sh

# Make it executable
RUN chmod +x /docker-entrypoint.sh

# Set as the entrypoint
ENTRYPOINT ["/docker-entrypoint.sh"]