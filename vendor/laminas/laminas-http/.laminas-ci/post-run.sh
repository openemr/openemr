#!/bin/bash

set -e

EXIT_STATUS=$1
JOB=$4
COMMAND=$(echo "${JOB}" | jq -r '.command')

if [[ "${EXIT_STATUS}" == "0" || ! ${COMMAND} =~ phpunit ]]; then
    exit 0
fi

cat /var/log/apache2/error.log
cat /var/log/apache2/access.log
