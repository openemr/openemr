#!/bin/bash

if [ -z "$1" ] || [ "$1" == "-h" ] || [ "$1" == "--help" ] ; then
  printf "OpenEMR Parallel Linter\n\n"
  printf "  Arguments\n"
  printf "    -h, --help | Information on using this script\n"
  printf "    -d, --dir  | The codebase directory for OpenEMR\n"
  exit 0
fi


#takes list of files/folders to sniff as its only argument(s)
function sniff {
    INI=$HOME/.phpenv/versions/$(phpenv version-name 2> /dev/null)/etc/php.ini
    if [ -f $INI ]; then
        grep -q "extension = ldap.so" $INI || echo "extension = ldap.so" >> $INI
    fi
    BIN_DIR=$HOME/.composer/vendor/bin
    if [ -d $HOME/$XDG_CONFIG_HOME/composer ]; then
        BIN_DIR="$HOME/$XDG_CONFIG_HOME/composer/vendor/bin"
    fi
    if [ -d $HOME/.config/composer ]; then
        BIN_DIR="$HOME/.config/composer/vendor/bin"
    fi
    cd $DIR
    $BIN_DIR/phpcs -p -n --extensions=php,inc --report-width=120 $@
}

if [ "$1" == "-d" ] || [ "$1" == "--dir" ] ; then

    DIR=$2

    case "$CI_JOB" in

        "lint_syntax")
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
            ;;
        "lint_style")
            sniff . --standard=ci/phpcs.xml --report=summary --report=source --report=info
            ;;
        "lint_style_new_commit")
            MODIFIED_FILES=$(git diff-tree --no-commit-id --name-only -r HEAD | tr "\n" " ")
            sniff "$MODIFIED_FILES" --standard=ci/phpcs_strict.xml --report=summary
            ;;
        "lint_style_staged")
            MODIFIED_FILES=$(git diff --cached --name-only | tr "\n" " ")
            sniff "$MODIFIED_FILES" --standard=ci/phpcs_strict.xml --report=summary
            ;;
        *)
            echo "Error: not a valid CI_JOB"

            ;;
    esac
fi