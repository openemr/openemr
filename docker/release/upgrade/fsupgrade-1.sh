#!/bin/sh
# Upgrade number 1 for OpenEMR docker
#  From prior version 5.0.1 (needed for the sql upgrade script).
priorOpenemrVersion="5.0.1"

echo "Start: Upgrade to docker-version 1"

# Perform codebase upgrade on each directory in sites/
for dir in /var/www/localhost/htdocs/openemr/sites/*/; do
    dir="${dir%/}"
    sitename=${dir##*/}

    # Ensure have all directories
    echo "Start: Ensure have all directories in ${sitename}"
    mkdir -p \
        "${dir}/documents/certificates" \
        "${dir}/documents/couchdb" \
        "${dir}/documents/custom_menus/patient_menus" \
        "${dir}/documents/edi" \
        "${dir}/documents/era" \
        "${dir}/documents/letter_templates" \
        "${dir}/documents/logs_and_misc/methods" \
        "${dir}/documents/mpdf/pdf_tmp" \
        "${dir}/documents/onsite_portal_documents/templates" \
        "${dir}/documents/procedure_results" \
        "${dir}/documents/smarty/gacl" \
        "${dir}/documents/smarty/main" \
        "${dir}/documents/temp"
    echo "Completed: Ensure have all directories in ${sitename}"

    # Update new directory structure
    echo "Start: Update new directory structure in ${sitename}"
    # letter_templates/custom_pdf.php must be hoisted before the subdir is migrated
    if [ -f "${dir}/letter_templates/custom_pdf.php" ]; then
        mv -f "${dir}/letter_templates/custom_pdf.php" "${dir}/"
    fi
    for sub in era edi letter_templates procedure_results; do
        if [ -d "${dir}/${sub}" ]; then
            find "${dir}/${sub}/." ! -name . -prune -exec mv -f {} "${dir}/documents/${sub}/" +
            rmdir "${dir}/${sub}"
        fi
    done
    echo "Completed: Update new directory structure in ${sitename}"

    # Clear smarty cache
    echo "Start: Clear smarty cache in ${sitename}"
    rm -fr "${dir}/documents/smarty/gacl"/* "${dir}/documents/smarty/main"/*
    echo "Completed: Clear smarty cache in ${sitename}"
done

# Fix permissions
echo "Start: Fix permissions"
chown -R apache:root /var/www/localhost/htdocs/openemr/sites/
echo "Completed: Fix permissions"

# Perform database upgrade on each directory in sites/
for dirdata in /var/www/localhost/htdocs/openemr/sites/*/; do
    dirdata="${dirdata%/}"
    sitename=${dirdata##*/}

    # Upgrade database
    echo "Start: Upgrade database for ${sitename} from ${priorOpenemrVersion}"
    echo "<?php \$_GET['site'] = '${sitename}'; ?>" > /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    cat /var/www/localhost/htdocs/openemr/sql_upgrade.php >> /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "/input type='submit'/d" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "s/!empty(\$_POST\['form_submit'\])/empty(\$_POST\['form_submit'\])/" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "s/^[   ]*\$form_old_version[   =].*$/\$form_old_version = \"${priorOpenemrVersion}\";/" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    php -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    echo "Completed: Upgrade database for ${sitename} from ${priorOpenemrVersion}"
done

echo "Completed: Upgrade to docker-version 1"
