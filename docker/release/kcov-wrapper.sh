#!/bin/sh

set -e

# Create coverage directory if it doesn't exist
mkdir -p /var/www/localhost/htdocs/coverage

# Run openemr.sh under kcov
kcov --include-path=/var/www/localhost/htdocs/openemr/openemr.sh,/root/devtoolsLibrary.source \
     /var/www/localhost/htdocs/coverage \
     /var/www/localhost/htdocs/openemr/openemr.sh

# When kcov is done, the script will have been executed
# and we're ready to serve requests
exec /usr/sbin/httpd -D FOREGROUND
