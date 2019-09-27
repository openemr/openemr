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

#takes list of files/folders to sniff as its only argument(s)
function sniff {
    BIN_DIR=$HOME/.composer/vendor/bin
    if [ -d $HOME/$XDG_CONFIG_HOME/composer ]; then
        BIN_DIR="$HOME/$XDG_CONFIG_HOME/composer/vendor/bin"
    fi
    if [ -d $HOME/.config/composer ]; then
        BIN_DIR="$HOME/.config/composer/vendor/bin"
    fi
    composer global require "squizlabs/php_codesniffer=3.4.2"
    cd $DIR
    $BIN_DIR/phpcs -p -n --extensions=php,inc --report-width=120 $@
}

if [ "$1" == "-d" ] || [ "$1" == "--dir" ] ; then

    DIR=$2

    case "$CI_JOB" in

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
            sniff . --standard=ci/phpcs.xml --report=full
            ;;
        "lint_style_new_commit")
            MODIFIED_FILES=$(git diff-tree --no-commit-id --name-only -r HEAD | tr "\n" " ")
            sniff "$MODIFIED_FILES" --standard=ci/phpcs_strict.xml --report=full
            ;;
        "lint_style_staged")
            MODIFIED_FILES=$(git diff --cached --name-only | tr "\n" " ")
            sniff "$MODIFIED_FILES" --standard=ci/phpcs_strict.xml --report=full
            ;;
        *)
            echo "Error: not a valid CI_JOB"

            ;;
    esac
fi
