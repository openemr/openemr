#!/bin/sh
# Upgrade number 2 for OpenEMR docker
#  From prior version 5.0.2 (needed for the sql upgrade script).
priorOpenemrVersion="5.0.2"

echo "Start: Upgrade to docker-version 2"

# Perform codebase upgrade on each directory in sites/
for dir in $(find /var/www/localhost/htdocs/openemr/sites/* -maxdepth 0 -type d ); do
    sitename=$(basename "$dir")

    # Ensure have all directories
    echo "Start: Ensure have all directories in $sitename"
    if [ ! -d $dir/documents/certificates ]; then
        mkdir -p $dir/documents/certificates
    fi
    if [ ! -d $dir/documents/couchdb ]; then
        mkdir -p $dir/documents/couchdb
    fi
    if [ ! -d $dir/documents/custom_menus/patient_menus ]; then
        mkdir -p $dir/documents/custom_menus/patient_menus
    fi
    if [ ! -d $dir/documents/edi ]; then
        mkdir -p $dir/documents/edi
    fi
    if [ ! -d $dir/documents/era ]; then
        mkdir -p $dir/documents/era
    fi
    if [ ! -d $dir/documents/letter_templates ]; then
        mkdir -p $dir/documents/letter_templates
    fi
    if [ ! -d $dir/documents/logs_and_misc/methods ]; then
        mkdir -p $dir/documents/logs_and_misc/methods
    fi
    if [ ! -d $dir/documents/mpdf/pdf_tmp ]; then
        mkdir -p $dir/documents/mpdf/pdf_tmp
    fi
    if [ ! -d $dir/documents/onsite_portal_documents/templates ]; then
        mkdir -p $dir/documents/onsite_portal_documents/templates
    fi
    if [ ! -d $dir/documents/procedure_results ]; then
        mkdir -p $dir/documents/procedure_results
    fi
    if [ ! -d $dir/documents/smarty/gacl ]; then
        mkdir -p $dir/documents/smarty/gacl
    fi
    if [ ! -d $dir/documents/smarty/main ]; then
        mkdir -p $dir/documents/smarty/main
    fi
    if [ ! -d $dir/documents/temp ]; then
        mkdir -p $dir/documents/temp
    fi
    echo "Completed: Ensure have all directories in $sitename"

    # Clear smarty cache
    echo "Start: Clear smarty cache in $sitename"
    rm -fr $dir/documents/smarty/gacl/*
    rm -fr $dir/documents/smarty/main/*
    echo "Completed: Clear smarty cache in $sitename"
done

# Fix permissions
echo "Start: Fix permissions"
chown -R apache:root /var/www/localhost/htdocs/openemr/sites/
echo "Completed: Fix permissions"

# Perform database upgrade on each directory in sites/
for dirdata in $(find /var/www/localhost/htdocs/openemr/sites/* -maxdepth 0 -type d ); do
    sitename=$(basename "$dirdata")

    # Upgrade database
    echo "Start: Upgrade database for $sitename from $priorOpenemrVersion"
    sed -e "s@!empty(\$_POST\['form_submit'\])@true@" < /var/www/localhost/htdocs/openemr/sql_upgrade.php > /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "s@\$form_old_version = \$_POST\['form_old_version'\];@\$form_old_version = '$priorOpenemrVersion';@" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "1s@^@<?php \$_GET['site'] = '$sitename'; ?>@" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    php -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    echo "Completed: Upgrade database for $sitename from $priorOpenemrVersion"
done

echo "Completed: Upgrade to docker-version 2"
