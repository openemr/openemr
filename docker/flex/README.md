# OpenEMR Official Docker Image

The docker image is maintained at https://hub.docker.com/r/openemr/openemr/
(see there for more details)

## How can I just spin up OpenEMR?

*You **need** to run an instance of mysql/mariadb as well and connect it to this container! You can then either use auto-setup with environment variables (see below) or you can manually set up, telling the server where to find the db.* The easiest way is to use `docker-compose`. The following `docker-compose.yml` file is a good example:
```yaml
# Use admin/pass as user/password credentials to login to openemr (from OE_USER and OE_PASS below)
# MYSQL_HOST and MYSQL_ROOT_PASS are required for openemr
# FLEX_REPOSITORY and (FLEX_REPOSITORY_BRANCH or FLEX_REPOSITORY_TAG) are required for flex openemr
# MYSQL_USER, MYSQL_PASS, OE_USER, MYSQL_PASS are optional for openemr and
#   if not provided, then default to openemr, openemr, admin, and pass respectively.
version: '3.1'
services:
  mysql:
    restart: always
    image: mariadb:11.8
    command: ['mariadbd','--character-set-server=utf8mb4']
    volumes:
    - databasevolume:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
  openemr:
    restart: always
    image: openemr/openemr:flex-<alpine version>-php-<php version>
    ports:
    - 80:80
    - 443:443
    volumes:
    - logvolume01:/var/log
    - sitevolume:/var/www/localhost/htdocs/openemr/sites
    environment:
      MYSQL_HOST: mysql
      MYSQL_ROOT_PASS: root
      MYSQL_USER: openemr
      MYSQL_PASS: openemr
      OE_USER: admin
      OE_PASS: pass
      FLEX_REPOSITORY: https://github.com/openemr/openemr.git
      FLEX_REPOSITORY_BRANCH: master
    depends_on:
    - mysql
volumes:
  logvolume01: {}
  sitevolume: {}
  databasevolume: {}
```

## Environment Variables

See the https://hub.docker.com/r/openemr/openemr/ page for documentation of environment variables.

## Support on Raspberry Pi

64 bit architecture is supported on Raspberry Pi.

## Where to get help?

For general knowledge, our [wiki](https://www.open-emr.org/wiki) is a repository of helpful information. The [Forum](https://community.open-emr.org/) are a great source for assistance and news about emerging features. We also have a [Chat](https://www.open-emr.org/chat/) system for real-time advice and to coordinate our development efforts.

## How can I contribute?

The OpenEMR community is a vibrant and active group, and people from any background can contribute meaningfully, whether they are optimizing our DB calls, or they're doing translations to their native tongue. Feel free to reach out to us at via [Chat](https://www.open-emr.org/chat/)!
