#!/bin/bash
# Upgrade number 4 for OpenEMR docker
#  From prior version 6.1.0 (needed for the sql upgrade script).
priorOpenemrVersion="6.1.0"

echo "Start: Upgrade to docker-version 4"

# Perform codebase upgrade on each directory in sites/
for dir in /var/www/localhost/htdocs/openemr/sites/*/; do
    sitename="${dir%/}"
    sitename="${sitename##*/}"

    # Ensure have all directories
    echo "Start: Ensure have all directories in ${sitename}"
    mkdir -p "${dir}/documents/"{certificates,couchdb,custom_menus/patient_menus,edi,era,letter_templates,logs_and_misc/methods,mpdf/pdf_tmp,onsite_portal_documents/templates,procedure_results,smarty/gacl,smarty/main,temp}
    echo "Completed: Ensure have all directories in ${sitename}"

    # Clear smarty cache
    echo "Start: Clear smarty cache in ${sitename}"
    rm -fr "${dir}/documents/smarty/gacl"/*
    rm -fr "${dir}/documents/smarty/main"/*
    echo "Completed: Clear smarty cache in ${sitename}"
done

# Fix permissions
echo "Start: Fix permissions"
chown -R apache:root /var/www/localhost/htdocs/openemr/sites/
echo "Completed: Fix permissions"

# Perform database upgrade on each directory in sites/
for dirdata in /var/www/localhost/htdocs/openemr/sites/*/; do
    sitename="${dirdata%/}"
    sitename="${sitename##*/}"

    # Upgrade database
    echo "Start: Upgrade database for ${sitename} from ${priorOpenemrVersion}"
    {
        echo "<?php \$_GET['site'] = '${sitename}'; ?>"
        cat /var/www/localhost/htdocs/openemr/sql_upgrade.php || true
    } | sed -e "s@!empty(\$_POST\['form_submit'\])@true@" \
            -e "s@\$form_old_version = \$_POST\['form_old_version'\];@\$form_old_version = '${priorOpenemrVersion}';@" \
            > /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    php -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    echo "Completed: Upgrade database for ${sitename} from ${priorOpenemrVersion}"
done

echo "Completed: Upgrade to docker-version 4"
