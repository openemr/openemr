FROM openemr/openemr:7.0.3

# Add a script to initialize the sites directory
COPY init-openemr.sh /root/
RUN chmod +x /root/init-openemr.sh

# Override the default entrypoint to run our custom script
ENTRYPOINT ["/root/init-openemr.sh"]