#!/usr/bin/env bash
# ============================================================================
# OpenEMR Kcov Coverage Wrapper Script
# ============================================================================
# This script wraps the OpenEMR startup script with kcov for code coverage
# analysis. Kcov is a code coverage tool that tracks which lines of code
# are executed during test runs.
#
# This script is used in the kcov build target of the Dockerfile to generate
# coverage reports for the OpenEMR startup scripts.
#
# Usage:
#   Called automatically by the kcov Docker build target
# ============================================================================

set -e

# ============================================================================
# SETUP COVERAGE DIRECTORY
# ============================================================================
# Create the directory where kcov will store coverage reports.
# Coverage reports are HTML files that can be viewed in a browser.

echo "Setting up coverage directory..."
mkdir -p /var/www/localhost/htdocs/coverage

# ============================================================================
# RUN OPENEMR.SH UNDER KCOV
# ============================================================================
# Execute the OpenEMR startup script under kcov to generate coverage data.
# The --include-path option tells kcov which files to include in the coverage
# report. Only the specified files will be tracked.
#
# Included files:
#   - openemr.sh: Main startup script
#   - devtoolsLibrary.source: Helper functions library
#
# Coverage data is written to /var/www/localhost/htdocs/coverage/

echo "Running OpenEMR startup script under kcov..."
kcov --include-path=/var/www/localhost/htdocs/openemr/openemr.sh,/root/devtoolsLibrary.source \
     /var/www/localhost/htdocs/coverage \
     /var/www/localhost/htdocs/openemr/openemr.sh

# ============================================================================
# START APACHE SERVER
# ============================================================================
# After coverage collection is complete, start Apache to serve the coverage
# reports and the OpenEMR application. The coverage reports will be available
# at /coverage/ in the web root.

echo "Coverage collection completed, starting Apache server..."
exec /usr/sbin/httpd -D FOREGROUND
