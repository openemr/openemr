FROM openemr/openemr:7.0.3

# Copy our fix-permissions script
COPY fix-permissions.sh /fix-permissions.sh
RUN chmod +x /fix-permissions.sh

# Add it to run when container starts
RUN echo '#!/bin/sh' > /etc/local.d/fix-permissions.start && \
    echo '(sleep 60 && /fix-permissions.sh) &' >> /etc/local.d/fix-permissions.start && \
    chmod +x /etc/local.d/fix-permissions.start && \
    rc-update add local default