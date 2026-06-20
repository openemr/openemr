#!/bin/bash
# Upgrade number 1 for OpenEMR docker
#  From prior version 5.0.1 (needed for the sql upgrade script).
priorOpenemrVersion="5.0.1"

echo "Start: Upgrade to docker-version 1"

# Perform codebase upgrade on each directory in sites/
for dir in /var/www/localhost/htdocs/openemr/sites/*/; do
    sitename="${dir%/}"
    sitename="${sitename##*/}"

    # Ensure have all directories
    echo "Start: Ensure have all directories in ${sitename}"
    mkdir -p "${dir}/documents/"{certificates,couchdb,custom_menus/patient_menus,edi,era,letter_templates,logs_and_misc/methods,mpdf/pdf_tmp,onsite_portal_documents/templates,procedure_results,smarty/gacl,smarty/main,temp}
    echo "Completed: Ensure have all directories in ${sitename}"

    # Update new directory structure
    echo "Start: Update new directory structure in ${sitename}"
    mv -f "${dir}/era"/* "${dir}/documents/era/" 2>/dev/null || true
    rm -rf "${dir}/era" 2>/dev/null || true
    mv -f "${dir}/edi"/* "${dir}/documents/edi/" 2>/dev/null || true
    rm -rf "${dir}/edi" 2>/dev/null || true
    mv -f "${dir}/letter_templates/custom_pdf.php" "${dir}/" 2>/dev/null || true
    mv -f "${dir}/letter_templates"/* "${dir}/documents/letter_templates/" 2>/dev/null || true
    rm -rf "${dir}/letter_templates" 2>/dev/null || true
    mv -f "${dir}/procedure_results"/* "${dir}/documents/procedure_results/" 2>/dev/null || true
    rm -rf "${dir}/procedure_results" 2>/dev/null || true
    echo "Completed: Update new directory structure in ${sitename}"

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
    } | sed -e "/input type='submit'/d" \
            -e "s/!empty(\$_POST\['form_submit'\])/empty(\$_POST\['form_submit'\])/" \
            -e "s/^[   ]*\$form_old_version[   =].*$/\$form_old_version = \"${priorOpenemrVersion}\";/" \
            > /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    php -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    echo "Completed: Upgrade database for ${sitename} from ${priorOpenemrVersion}"
done

echo "Completed: Upgrade to docker-version 1"
