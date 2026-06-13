#!/usr/bin/env bash
# ============================================================================
# OpenEMR Flex Kcov Coverage Wrapper Script
# ============================================================================
# This script wraps the OpenEMR startup script (openemr.sh) with kcov
# code coverage instrumentation. It's used for generating code coverage
# reports during testing.
#
# Kcov is a code coverage tool that instruments code execution and generates
# coverage reports showing which lines of code were executed during tests.
#
# Usage:
#   Called automatically when using the kcov build target:
#   docker build --target kcov -t openemr-flex:kcov .
#
# Coverage Reports:
#   Coverage reports are written to: /var/www/localhost/htdocs/coverage/
#   These reports can be accessed via HTTP after the container starts.
# ============================================================================

set -euo pipefail

# ============================================================================
# CREATE COVERAGE OUTPUT DIRECTORY
# ============================================================================
# Create the directory where kcov will write coverage reports
mkdir -p /var/www/localhost/htdocs/coverage

# ============================================================================
# RUN OPENEMR STARTUP WITH KCOV INSTRUMENTATION
# ============================================================================
# Execute openemr.sh under kcov with specific include paths.
# Only the startup script and devtoolsLibrary are instrumented to focus
# coverage on the container orchestration logic rather than all PHP code.
kcov --include-path=/var/www/localhost/htdocs/openemr.sh,/root/devtoolsLibrary.source \
     /var/www/localhost/htdocs/coverage \
     /var/www/localhost/htdocs/openemr.sh

# ============================================================================
# START APACHE SERVER
# ============================================================================
# After kcov completes, openemr.sh has finished all setup tasks.
# Start Apache to serve the application and coverage reports.
exec /usr/sbin/httpd -D FOREGROUND
