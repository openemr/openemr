#!/bin/sh
# Allows customization of openemr credentials, preventing the need for manual setup
#  (Note can force a manual setup by setting MANUAL_SETUP to 'yes')
#  - Required settings for auto installation are MYSQL_HOST and MYSQL_ROOT_PASS
#  -  (note that can force MYSQL_ROOT_PASS to be empty by passing as 'BLANK' variable)
#  - Optional settings for auto installation are:
#    - Setting db parameters MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE
#    - Setting openemr parameters OE_USER, OE_PASS
# TODO: xdebug options should be given here
set -e

source /root/devtoolsLibrary.source

swarm_wait() {
    if [ ! -f /var/www/localhost/htdocs/openemr/sites/docker-completed ]; then
        # true
        return 0
    else
        # false
        return 1
    fi
}

auto_setup() {
    prepareVariables

    find . -not -perm 600 -exec chmod 600 {} \+

    #create temporary file cache directory for auto_configure.php to use
    TMP_FILE_CACHE_LOCATION="/tmp/php-file-cache"
    mkdir $TMP_FILE_CACHE_LOCATION

    #create auto_configure.ini to be able to leverage opcache for operations
    touch auto_configure.ini
    echo "opcache.enable=1" >> auto_configure.ini
    echo "opcache.enable_cli=1" >> auto_configure.ini
    echo "opcache.file_cache=$TMP_FILE_CACHE_LOCATION" >> auto_configure.ini
    echo "opcache.file_cache_only=1" >> auto_configure.ini
    echo "opcache.file_cache_consistency_checks=1" >> auto_configure.ini
    echo "opcache.enable_file_override=1" >> auto_configure.ini
    echo "opcache.max_accelerated_files=1000000" >> auto_configure.ini

    #run auto_configure
    php auto_configure.php -c auto_configure.ini -f ${CONFIGURATION} || return 1

    #remove temporary file cache directory and auto_configure.ini
    rm -r $TMP_FILE_CACHE_LOCATION
    rm auto_configure.ini

    echo "OpenEMR configured."
    CONFIG=$(php -r "require_once('/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php'); echo \$config;")
    if [ "$CONFIG" == "0" ]; then
        echo "Error in auto-config. Configuration failed."
        exit 2
    fi

    setGlobalSettings
}

# AUTHORITY is the right to change OpenEMR's configured state
# - true for singletons, swarm leaders, and the Kubernetes startup job
# - false for swarm members and Kubernetes workers
# OPERATOR is the right to launch Apache and serve OpenEMR
# - true for singletons, swarm members (leader or otherwise), and Kubernetes workers
# - false for the Kubernetes startup job and manual image runs
AUTHORITY=yes
OPERATOR=yes
if [ "$K8S" == "admin" ]; then
    OPERATOR=no
elif [ "$K8S" == "worker" ]; then
    AUTHORITY=no
fi

if [ "$SWARM_MODE" == "yes" ]; then
    # atomically test for leadership
    set -o noclobber
    { > /var/www/localhost/htdocs/openemr/sites/docker-leader ; } &> /dev/null || AUTHORITY=no
    set +o noclobber
    
    if [ "$AUTHORITY" == "no" ] &&
       [ ! -f /var/www/localhost/htdocs/openemr/sites/docker-completed ]; then
        while swarm_wait; do
            echo "Waiting for the docker-leader to finish configuration before proceeding."
            sleep 10;
        done
    fi

    if [ "$AUTHORITY" == "yes" ]; then       
        touch /var/www/localhost/htdocs/openemr/sites/docker-initiated
        if [ ! -f /etc/ssl/openssl.cnf ]; then
            # Restore the emptied /etc/ssl directory
            echo "Restoring empty /etc/ssl directory."
            rsync --owner --group --perms --recursive --links /swarm-pieces/ssl /etc/
        fi
        if [ ! -d /var/www/localhost/htdocs/openemr/sites/default ]; then
            # Restore the emptied /var/www/localhost/htdocs/openemr/sites directory
            echo "Restoring empty /var/www/localhost/htdocs/openemr/sites directory."
            rsync --owner --group --perms --recursive --links /swarm-pieces/sites /var/www/localhost/htdocs/openemr/
        fi
    fi
fi

if [ "$AUTHORITY" == "yes" ]; then
    sh ssl.sh
fi

UPGRADE_YES=false;
if [ "$AUTHORITY" == "yes" ]; then
    # Figure out if need to do upgrade
    if [ -f /root/docker-version ]; then
        DOCKER_VERSION_ROOT=$(cat /root/docker-version)
    else
        DOCKER_VERSION_ROOT=0
    fi
    if [ -f /var/www/localhost/htdocs/openemr/docker-version ]; then
        DOCKER_VERSION_CODE=$(cat /var/www/localhost/htdocs/openemr/docker-version)
    else
        DOCKER_VERSION_CODE=0
    fi
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/docker-version ]; then
        DOCKER_VERSION_SITES=$(cat /var/www/localhost/htdocs/openemr/sites/default/docker-version)
    else
        DOCKER_VERSION_SITES=0
    fi

    # Only perform upgrade if the sites dir is shared and not entire openemr directory
    if [ "$DOCKER_VERSION_ROOT" == "$DOCKER_VERSION_CODE" ] &&
       [ "$DOCKER_VERSION_ROOT" -gt "$DOCKER_VERSION_SITES" ]; then
        echo "Plan to try an upgrade from $DOCKER_VERSION_SITES to $DOCKER_VERSION_ROOT"
        UPGRADE_YES=true;
    fi
fi

CONFIG=$(php -r "require_once('/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php'); echo \$config;")
if [ "$AUTHORITY" == "no" ] &&
    [ "$CONFIG" == "0" ]; then
    echo "Critical failure! An OpenEMR worker is trying to run on a missing configuration."
    echo " - Is this due to a Kubernetes grant hiccup?"
    echo "The worker will now terminate."
    exit 1
fi

# key/cert management (if key/cert exists in /root/certs/.. and not in sites/defauly/documents/certificates, then it will be copied into it)
#  current use case is bringing in as secret(s) in kubernetes, but can bring in as shared volume or directly brought in during docker build
#   dir structure:
#    /root/certs/mysql/server/mysql-ca (supported)
#    /root/certs/mysql/client/mysql-cert (supported)
#    /root/certs/mysql/client/mysql-key (supported)
#    /root/certs/couchdb/couchdb-ca (supported)
#    /root/certs/couchdb/couchdb-cert (supported)
#    /root/certs/couchdb/couchdb-key (supported)
#    /root/certs/ldap/ldap-ca (supported)
#    /root/certs/ldap/ldap-cert (supported)
#    /root/certs/ldap/ldap-key (supported)
#    /root/certs/redis/redis-ca (supported)
if [ -f /root/certs/mysql/server/mysql-ca ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca ]; then
    echo "copied over mysql-ca"
    cp /root/certs/mysql/server/mysql-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca
fi
if [ -f /root/certs/mysql/server/mysql-cert ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert ]; then
    echo "copied over mysql-cert"
    cp /root/certs/mysql/server/mysql-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert
fi
if [ -f /root/certs/mysql/server/mysql-key ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key ]; then
    echo "copied over mysql-key"
    cp /root/certs/mysql/server/mysql-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key
fi
if [ -f /root/certs/couchdb/couchdb-ca ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-ca ]; then
    echo "copied over couchdb-ca"
    cp /root/certs/couchdb/couchdb-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-ca
fi
if [ -f /root/certs/couchdb/couchdb-cert ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-cert ]; then
    echo "copied over couchdb-cert"
    cp /root/certs/couchdb/couchdb-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-cert
fi
if [ -f /root/certs/couchdb/couchdb-key ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-key ]; then
    echo "copied over couchdb-key"
    cp /root/certs/couchdb/couchdb-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/couchdb-key
fi
if [ -f /root/certs/ldap/ldap-ca ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-ca ]; then
    echo "copied over ldap-ca"
    cp /root/certs/ldap/ldap-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-ca
fi
if [ -f /root/certs/ldap/ldap-cert ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-cert ]; then
    echo "copied over ldap-cert"
    cp /root/certs/ldap/ldap-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-cert
fi
if [ -f /root/certs/ldap/ldap-key ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-key ]; then
    echo "copied over ldap-key"
    cp /root/certs/ldap/ldap-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/ldap-key
fi
if [ -f /root/certs/redis/redis-ca ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-ca ]; then
    echo "copied over redis-ca"
    cp /root/certs/redis/redis-ca /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-ca
fi
if [ -f /root/certs/redis/redis-cert ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-cert ]; then
    echo "copied over redis-cert"
    cp /root/certs/redis/redis-cert /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-cert
fi
if [ -f /root/certs/redis/redis-key ] &&
   [ ! -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-key ]; then
    echo "copied over redis-key"
    cp /root/certs/redis/redis-key /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-key
fi

if [ "$AUTHORITY" == "yes" ]; then
    if [ "$CONFIG" == "0" ] &&
       [ "$MYSQL_HOST" != "" ] &&
       [ "$MYSQL_ROOT_PASS" != "" ] &&
       [ "$MANUAL_SETUP" != "yes" ]; then

        echo "Running quick setup!"
        while ! auto_setup; do
            echo "Couldn't set up. Any of these reasons could be what's wrong:"
            echo " - You didn't spin up a MySQL container or connect your OpenEMR container to a mysql instance"
            echo " - MySQL is still starting up and wasn't ready for connection yet"
            echo " - The Mysql credentials were incorrect"
            sleep 1;
        done
        echo "Setup Complete!"
    fi
fi

if 
   [ "$AUTHORITY" == "yes" ] &&
   [ "$CONFIG" == "1" ] &&
   [ "$MANUAL_SETUP" != "yes" ]; then
    # OpenEMR has been configured

    if $UPGRADE_YES; then
        # Need to do the upgrade
        echo "Attempting upgrade"
        c=$DOCKER_VERSION_SITES
        while [ "$c" -le "$DOCKER_VERSION_ROOT" ]; do
            if [ "$c" -gt "$DOCKER_VERSION_SITES" ] ; then
                echo "Start: Processing fsupgrade-$c.sh upgrade script"
                sh /root/fsupgrade-$c.sh
                echo "Completed: Processing fsupgrade-$c.sh upgrade script"
            fi
            c=$(( c + 1 ))
        done
        echo -n $DOCKER_VERSION_ROOT > /var/www/localhost/htdocs/openemr/sites/default/docker-version
        echo "Completed upgrade"
    fi
fi

if [ "$REDIS_SERVER" != "" ] &&
   [ ! -f /etc/php-redis-configured ]; then
    # Doing this redis section before the below openemr file config section since both these sections take some time
    #  and in swarm mode the docker will be functional after this redis section (ie. if do the below config section first
    #  then the breakage time of the pod will be markedly less).

    # Support phpredis build
    #   This will allow building phpredis towards either most recent development version "develop",
    #    or a specific sha1 commit id, such as "e571a81f8d3009aab38cbb88dde865edeb0607ac".
    #    This allows support for tls (ie. encrypted connections) since not available in production
    #    version 5.3.7 .
    if [ "$PHPREDIS_BUILD" != "" ]; then
      apk update
      apk del --no-cache php83-redis
      apk add --no-cache git php83-dev php83-pecl-igbinary gcc make g++
      mkdir /tmpredis
      cd /tmpredis
      git clone https://github.com/phpredis/phpredis.git
      cd /tmpredis/phpredis
      if [ "$PHPREDIS_BUILD" != "develop" ]; then
          git reset --hard "$PHPREDIS_BUILD"
      fi
      # note for php 8.3, needed to change from 'phpize' to:
      phpize83
      # note for php 8.3, needed to change from './configure --enable-redis-igbinary' to:
      ./configure --with-php-config=/usr/bin/php-config83 --enable-redis-igbinary
      make -j $(nproc --all)
      make install
      echo "extension=redis" > /etc/php83/conf.d/20_redis.ini
      rm -fr /tmpredis/phpredis
      apk del --no-cache git php83-dev gcc make g++
      cd /var/www/localhost/htdocs/openemr
    fi

    # Support the following redis auth:
    #   No username and No password set (using redis default user with nopass set)
    #   Both username and password set (using the redis user and pertinent password)
    #   Only password set (using redis default user and pertinent password)
    #   NOTE that only username set is not supported (in this case will ignore the username
    #      and use no username and no password set mode)
    REDIS_PATH="$REDIS_SERVER:6379"
    if [ "$REDIS_USERNAME" != "" ] &&
       [ "$REDIS_PASSWORD" != "" ]; then
        echo "redis setup with username and password"
        REDIS_PATH="$REDIS_PATH?auth[user]=$REDIS_USERNAME\&auth[pass]=$REDIS_PASSWORD"
        GET_CONNECTOR="\&"
    elif [ "$REDIS_PASSWORD" != "" ]; then
        echo "redis setup with password"
        # only a password, thus using the default user which redis has set a password for
        REDIS_PATH="$REDIS_PATH?auth[pass]=$REDIS_PASSWORD"
        GET_CONNECTOR="\&"
    else
        # no user or password, thus using the default user which is set to nopass in redis
        # so just keeping original REDIS_PATH: REDIS_PATH="$REDIS_PATH"
        echo "redis setup"
        GET_CONNECTOR="?"
    fi

    if [ "$REDIS_X509" == "yes" ]; then
        echo "redis x509"
        REDIS_PATH="tls://${REDIS_PATH}${GET_CONNECTOR}stream[cafile]=file:///var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-ca\&stream[local_cert]=file:///var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-cert\&stream[local_pk]=file:///var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-key"
    elif [ "$REDIS_TLS" == "yes" ]; then
        echo "redis tls"
        REDIS_PATH="tls://${REDIS_PATH}${GET_CONNECTOR}stream[cafile]=file:///var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-ca"
    else
        echo "redis tcp"
        REDIS_PATH="tcp://$REDIS_PATH"
    fi

    sed -i "s@session.save_handler = files@session.save_handler = redis@" /etc/php83/php.ini
    sed -i "s@;session.save_path = \"/tmp\"@session.save_path = \"$REDIS_PATH\"@" /etc/php83/php.ini
    # Ensure only configure this one time
    touch /etc/php-redis-configured
fi

if
   [ "$AUTHORITY" == "yes" ] ||
   [ "$SWARM_MODE" == "yes" ]; then
    if
    [ "$CONFIG" == "1" ] &&
    [ "$MANUAL_SETUP" != "yes" ]; then
    # OpenEMR has been configured

        if [ -f auto_configure.php ]; then
            # This section only runs once after per docker since auto_configure.php gets removed after this script

            echo "Setting user 'www' as owner of openemr/ and setting file/dir permissions to 400/500"

            #set all directories to 500 (note that sites/default/documents is dealt with below which need to skip here to prevent breakage in swarm mode)
            find . -type d -not -path "./sites/default/documents/*" -not -perm 500 -exec chmod 500 {} \+
            #set all file access to 400 (note that sites/default/documents is dealt with below which need to skip here to prevent breakage in swarm mode)
            find . -type f -not -path "./sites/default/documents/*" -not -path './openemr.sh' -not -perm 400 -exec chmod 400 {} \+

            echo "Default file permissions and ownership set, allowing writing to specific directories"
            chmod 700 openemr.sh

            # Set file and directory permissions
            #  Note this is only done once in swarm mode (to prevent breakage) since is a shared volume.
            if
               [ "$SWARM_MODE" != "yes" ] ||
               [ ! -f /var/www/localhost/htdocs/openemr/sites/docker-completed ]; then
                echo "Setting sites/default/documents permissions to 700"
                find sites/default/documents -not -perm 700 -exec chmod 700 {} \+
            fi

            echo "Removing remaining setup scripts"
            #remove all setup scripts
            rm -f admin.php
            rm -f acl_upgrade.php
            rm -f setup.php
            rm -f sql_patch.php
            rm -f sql_upgrade.php
            rm -f ippf_upgrade.php
            rm -f auto_configure.php
            echo "Setup scripts removed, we should be ready to go now!"
        fi
    fi
fi

#  Note this is only done once in swarm mode (to prevent breakage) since is a shared volume.
if
   [ "$SWARM_MODE" != "yes" ] ||
   [ ! -f /var/www/localhost/htdocs/openemr/sites/docker-completed ]; then
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca ]; then
        # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
        echo "adjusted permissions for mysql-ca"
        chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-ca
    fi
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert ]; then
        # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
        echo "adjusted permissions for mysql-cert"
        chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-cert
    fi
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key ]; then
        # for specific issue in docker and kubernetes that is required for successful openemr adodb/laminas connections
        echo "adjusted permissions for mysql-key"
        chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/mysql-key
    fi
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-ca ]; then
        # for specific issue in docker and kubernetes that is required for successful openemr redis connections
        echo "adjusted permissions for redis-ca"
        chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-ca
    fi
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-cert ]; then
        # for specific issue in docker and kubernetes that is required for successful openemr redis connections
        echo "adjusted permissions for redis-cert"
        chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-cert
    fi
    if [ -f /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-key ]; then
        # for specific issue in docker and kubernetes that is required for successful openemr redis connections
        echo "adjusted permissions for redis-key"
        chmod 744 /var/www/localhost/htdocs/openemr/sites/default/documents/certificates/redis-key
    fi
fi

if [ "$XDEBUG_IDE_KEY" != "" ] ||
   [ "$XDEBUG_ON" == 1 ]; then
   sh xdebug.sh
   #also need to turn off opcache since it can not be turned on with xdebug
   if [ ! -f /etc/php-opcache-jit-configured ]; then
      echo "opcache.enable=0" >> /etc/php83/php.ini
      touch /etc/php-opcache-jit-configured
   fi
else
   # Configure opcache jit if Xdebug is not being used (note opcache is already on, so just need to add setting(s) to php.ini that are different from the default setting(s))
   if [ ! -f /etc/php-opcache-jit-configured ]; then
      echo "opcache.jit=tracing" >> /etc/php83/php.ini
      echo "opcache.jit_buffer_size=100M" >> /etc/php83/php.ini
      touch /etc/php-opcache-jit-configured
   fi
fi

if [ "$AUTHORITY" == "yes" ] &&
   [ "$SWARM_MODE" == "yes" ]; then
    # Set flag that the docker-leader configuration is complete
    touch /var/www/localhost/htdocs/openemr/sites/docker-completed
    rm -f /var/www/localhost/htdocs/openemr/sites/docker-leader
fi

if [ "$SWARM_MODE" == "yes" ]; then
    # Set flag that the instance is ready when in swarm mode
    echo ""
    echo "swarm mode on: this instance is ready"
    echo ""
    touch /root/instance-swarm-ready
fi

echo ""
echo "Love OpenEMR? You can now support the project via the open collective:"
echo " > https://opencollective.com/openemr/donate"
echo ""

if [ "$OPERATOR" == "yes" ]; then
    echo "Starting apache!"
    /usr/sbin/httpd -D FOREGROUND
else
    echo "OpenEMR configuration tasks have concluded."
    exit 0
fi
