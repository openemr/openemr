#!/bin/bash
# Upgrade number 11 for OpenEMR docker
#  From prior version 8.1.0 (needed for the sql upgrade script).
# shellcheck disable=SC2034 # priorOpenemrVersion is part of the fsupgrade-*.sh schema; this is a test fixture that doesn't exercise the body.
priorOpenemrVersion="8.1.0"
echo "Start: Upgrade to docker-version 11"
# Real upgrade work would go here.
echo "Completed: Upgrade to docker-version 11"
