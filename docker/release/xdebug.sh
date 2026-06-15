#!/bin/sh

set -e

if [ "${XDEBUG_IDE_KEY:-}" = "" ] && [ "${XDEBUG_ON:-}" != 1 ]; then
   echo bad context for xdebug.sh launch
   exit 1
fi

if [ ! -f /etc/php-xdebug-configured ]; then
    # install xdebug library
    apk update
    apk add --no-cache "php${PHP_VERSION_ABBR?}-pecl-xdebug"

    # set up xdebug in php.ini
    {
        echo "; start xdebug configuration"
        echo "zend_extension=/usr/lib/php${PHP_VERSION_ABBR}/modules/xdebug.so"
        echo "xdebug.output_dir=/tmp"
        echo "xdebug.start_with_request=trigger"
        echo "xdebug.remote_handler=dbgp"
        echo "xdebug.log=/tmp/xdebug.log"
        echo "xdebug.discover_client_host=1"
        if [ "${XDEBUG_PROFILER_ON:-}" = 1 ]; then
            # set up xdebug profiler
            echo "xdebug.mode=debug,profile"
            echo "xdebug.profiler_output_name=cachegrind.out.%s"
        else
            echo "xdebug.mode=debug"
        fi
        # manually set up host port, if set (or set to default 9003)
        echo "xdebug.client_port=${XDEBUG_CLIENT_PORT:-9003}"
        if [ "${XDEBUG_CLIENT_HOST:-}" != "" ]; then
            # manually set up host, if set
            echo "xdebug.client_host=${XDEBUG_CLIENT_HOST}"
        fi
        if [ "${XDEBUG_IDE_KEY:-}" != "" ]; then
            # set up ide key, if set
            echo "xdebug.idekey=${XDEBUG_IDE_KEY}"
        fi
        echo "; end xdebug configuration"
    } >> "/etc/php${PHP_VERSION_ABBR}/php.ini"

    # Ensure only configure this one time
    touch /etc/php-xdebug-configured
fi

# to prevent the 'Xdebug: [Log Files] File '/tmp/xdebug.log' could not be opened.' messages
#  (need to keep doing this since /tmp may be cleared)
if [ ! -f /tmp/xdebug.log ]; then
    touch /tmp/xdebug.log;
fi
chmod 666 /tmp/xdebug.log;
