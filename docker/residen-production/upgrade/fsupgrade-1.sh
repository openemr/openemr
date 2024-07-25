#!/bin/sh
# Upgrade number 1 for OpenEMR docker
#  From prior version 5.0.1 (needed for the sql upgrade script).
priorOpenemrVersion="5.0.1"

echo "Start: Upgrade to docker-version 1"

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

    # Update new directory structure
    echo "Start: Update new directory structure in $sitename"
    if [ -d $dir/era ]; then
        if [ "$(ls $dir/era)" ]; then
            mv -f $dir/era/* $dir/documents/era/
        fi
        rm -rf $dir/era
    fi
    if [ -d $dir/edi ]; then
        if [ "$(ls $dir/edi)" ]; then
            mv -f $dir/edi/* $dir/documents/edi/
        fi
        rm -rf $dir/edi
    fi
    if [ -d $dir/letter_templates ]; then
        if [ "$(ls $dir/letter_templates)" ]; then
            if [ -f $dir/letter_templates/custom_pdf.php ]; then
                mv -f $dir/letter_templates/custom_pdf.php $dir/
            fi
            mv -f $dir/letter_templates/* $dir/documents/letter_templates/
        fi
        rm -rf $dir/letter_templates
    fi
    if [ -d $dir/procedure_results ]; then
        if [ "$(ls $dir/procedure_results)" ]; then
            mv -f $dir/procedure_results/* $dir/documents/procedure_results/
        fi
        rm -rf $dir/procedure_results
    fi
    echo "Completed: Update new directory structure in $sitename"

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
    echo "<?php \$_GET['site'] = '$sitename'; ?>" > /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    cat /var/www/localhost/htdocs/openemr/sql_upgrade.php >> /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "/input type='submit'/d" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "s/!empty(\$_POST\['form_submit'\])/empty(\$_POST\['form_submit'\])/" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    sed -i "s/^[   ]*\$form_old_version[   =].*$/\$form_old_version = \"$priorOpenemrVersion\";/" /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    php -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    rm -f /var/www/localhost/htdocs/openemr/TEMPsql_upgrade.php
    echo "Completed: Upgrade database for $sitename from $priorOpenemrVersion"
done

echo "Completed: Upgrade to docker-version 1"
