FROM openemr/openemr:7.0.3

# Create a script to copy the sites
COPY copy-sites.sh /copy-sites.sh
RUN chmod +x /copy-sites.sh

# Add a command to run our script when the container starts up
# This will add it to the end of /etc/profile so it runs on container start
RUN echo "/copy-sites.sh" >> /etc/profile.d/copy-sites.sh && chmod +x /etc/profile.d/copy-sites.sh