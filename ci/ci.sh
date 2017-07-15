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

case "$CI_JOB" in

"lint_syntax")
  if [ "$1" == "-d" ] || [ "$1" == "--dir" ] ; then
    cd $2
    find . -type d \( -path ./vendor \
                      -o -path ./interface/main/calendar/modules \
                      -o -path ./interface/reports \
                      -o -path ./contrib/util \
                      -o -path ./library/html2pdf/vendor/tecnickcom \
                      -o -path ./library/classes/fpdf \
                      -o -path ./library/html2pdf \
                      -o -path ./gacl \
                      -o -path ./library/edihistory \) -prune -o \
          -name "*.php" -print0 | xargs -0 -n1 -P8 php -l
  fi
  ;;
"lint_style")
  if [ "$1" == "-d" ] || [ "$1" == "--dir" ] ; then
    cd $2
    echo "extension = ldap.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    composer require "squizlabs/php_codesniffer=3.0.*"
    ./vendor/bin/phpcs -p -n --extensions=php,inc --standard=ci/phpcs.xml --report-width=120 --report=summary --report=source --report=info .
  fi
  ;;
*)
  echo "Error: not a valid CI_JOB"
  ;;
esac
