#!/bin/bash

if [ -z "$1" ] || [ "$1" == "-h" ] || [ "$1" == "--help" ] ; then
  printf "OpenEMR Parallel Linter\n\n"
  printf "  Arguments\n"
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
    composer global require "squizlabs/php_codesniffer=3.0.*"
    composer global exec -- cd $2 && phpcs -p -n --extensions=php,inc --standard=ci/phpcs.xml --report-width=120 --report=summary --report=source --report=info .
  fi
  ;;
"lint_style_new_code")
  if [ "$1" == "-d" ] || [ "$1" == "--dir" ] ; then
    cd $2
    echo "extension = ldap.so" >> $HOME/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    composer global require "squizlabs/php_codesniffer=3.0.*"
    MODIFIED_FILES=$(git diff-tree --no-commit-id --name-only -r HEAD |  tr "\n" " ")
    composer global exec -- cd $2 && phpcs -p -n --extensions=php,inc --standard=ci/phpcs_strict.xml --report-width=120 --report=summary --report=source --report=info $MODIFIED_FILES
  fi
  ;;
*)
  echo "Error: not a valid CI_JOB"
  ;;
esac