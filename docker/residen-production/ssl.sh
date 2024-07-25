#!/bin/sh
#
# configures SSL
# optionally configures Let's Encrypt
#
# TODO: Swarm members who aren't the leader won't be told to pick up new LE certs, although
# container restarts will fix that.
#
set -e

 if ! [ -f /etc/ssl/private/selfsigned.key.pem ]; then
    openssl req -x509 -newkey rsa:4096 \
    -keyout /etc/ssl/private/selfsigned.key.pem \
    -out /etc/ssl/certs/selfsigned.cert.pem \
    -days 365 -nodes \
    -subj "/C=xx/ST=x/L=x/O=x/OU=x/CN=localhost"
fi

if [ ! -f /etc/ssl/docker-selfsigned-configured ]; then
    rm -f /etc/ssl/certs/webserver.cert.pem
    rm -f /etc/ssl/private/webserver.key.pem
    ln -s /etc/ssl/certs/selfsigned.cert.pem /etc/ssl/certs/webserver.cert.pem
    ln -s /etc/ssl/private/selfsigned.key.pem /etc/ssl/private/webserver.key.pem
    touch /etc/ssl/docker-selfsigned-configured
fi

if [ "$DOMAIN" != "" ]; then
        if [ "$EMAIL" != "" ]; then
        EMAIL="-m $EMAIL"
    else
        echo "WARNING: SETTING AN EMAIL VIA \$EMAIL is HIGHLY RECOMMENDED IN ORDER TO"
        echo "         RECEIVE ALERTS FROM LETSENCRYPT ABOUT YOUR SSL CERTIFICATE."
    fi
    # if a domain has been set, set up LE and target those certs

    if ! [ -f /etc/letsencrypt/live/$DOMAIN/fullchain.pem ]; then
        /usr/sbin/httpd -k start
        sleep 2
        certbot certonly --webroot -n -w /var/www/localhost/htdocs/openemr/ -d $DOMAIN $EMAIL --agree-tos
        /usr/sbin/httpd -k stop
        echo "1 23  *   *   *   certbot renew -q --post-hook \"httpd -k graceful\"" >> /etc/crontabs/root
    fi

    # run letsencrypt as a daemon and reference the correct cert
    if [ ! -f /etc/ssl/docker-letsencrypt-configured ]; then
        rm -f /etc/ssl/certs/webserver.cert.pem
        rm -f /etc/ssl/private/webserver.key.pem
        ln -s /etc/letsencrypt/live/$DOMAIN/fullchain.pem /etc/ssl/certs/webserver.cert.pem
        ln -s /etc/letsencrypt/live/$DOMAIN/privkey.pem /etc/ssl/private/webserver.key.pem
        touch /etc/ssl/docker-letsencrypt-configured
    fi

    # run cron to service LE renewals
    if [ "$OPERATOR" == "yes" ]; then
        crond
    fi
fi