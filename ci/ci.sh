#!/bin/bash

if [ -z "$1" ] || [ "$1" == "-h" ] || [ "$1" == "--help" ] ; then
  printf "OpenEMR Continuous Integration\n\n"
  printf "  Overview:\n"
  printf "    This is the script that is ran by our Continuous Integration server found at\n"
  printf "    https://travis-ci.org/openemr/openemr. Each time you push code to your Pull Request\n"
  printf "    branch, this script will be ran on the server. You'll be able to see the state\n"
  printf "    (green/yellow/red) right on the Github UI.\n\n"
  printf "    In order to proactively test out these checks while you develop, please consult the\n"
  printf "    following wiki page: http://www.open-emr.org/wiki/index.php/Continuous_Integration.\n\n"
  printf "  Arguments:\n"
  printf "    -h, --help | Information on using this script\n"
  printf "    -d, --dir  | The codebase directory for OpenEMR\n"
  exit 0
fi

if [ "$1" == "-d" ] || [ "$1" == "--dir" ] ; then

    # collect the directory where global composer bin's are stored
    BIN_DIR=$HOME/.composer/vendor/bin
    if [ -d $HOME/$XDG_CONFIG_HOME/composer ]; then
        BIN_DIR="$HOME/$XDG_CONFIG_HOME/composer/vendor/bin"
    fi
    if [ -d $HOME/.config/composer ]; then
        BIN_DIR="$HOME/.config/composer/vendor/bin"
    fi

    case "$CI_JOB" in

        "build_test")
            echo "Checking build and tests"
            cd $2

            echo "build openemr (mimick standard build steps for production package)"
            composer install
            npm install
            npm run build
            composer global require phing/phing
            $BIN_DIR/phing vendor-clean
            $BIN_DIR/phing assets-clean
            composer global remove phing/phing
            composer dump-autoload -o
            rm -fr node_modules

            echo "also install ccdaservice to allow ccdaservice testing (this step is not part of production build)"
            cd ccdaservice
            npm install
            cd ../

            echo "install/configure active openemr instance"
            chmod 666 sites/default/sqlconf.php
            sudo chown -R www-data:www-data sites/default/documents
            sed -e 's@^exit;@ @' < contrib/util/installScripts/InstallerAuto.php > contrib/util/installScripts/InstallerAutoTemp.php
            php -f contrib/util/installScripts/InstallerAutoTemp.php
            rm -f contrib/util/installScripts/InstallerAutoTemp.php

            echo "turn on the api to allow api testing"
            mysql -u openemr --password="openemr" -h localhost -e "UPDATE globals SET gl_value = 1 WHERE gl_name = 'rest_api'" openemr

            echo "run phpunit testing"
            composer global require "phpunit/phpunit=8.*"
            $BIN_DIR/phpunit --testdox
            ;;
        "lint_syntax")
            echo "Checking for PHP syntax errors"
            cd $2
            failSyntax=false;
            if find . -type f -name "*.php" -exec php -d error_reporting=32767 -l {} \; 2>&1 >&- | grep "^"; then failSyntax=true; fi;
            if find . -type f -name "*.inc" -exec php -d error_reporting=32767 -l {} \; 2>&1 >&- | grep "^"; then failSyntax=true; fi;
            if $failSyntax; then
                exit 1;
            fi
            ;;
        "lint_style")
            echo "Checking for PHP styling (PSR2) issues"
            cd $2
            composer global require "squizlabs/php_codesniffer=3.*"
            $BIN_DIR/phpcs -p -n --extensions=php,inc --report-width=120 --standard=ci/phpcs.xml --report=full .
            ;;
        *)
            echo "Error: not a valid CI_JOB"

            ;;
    esac
fi