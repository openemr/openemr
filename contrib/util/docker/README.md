# OpenEMR Local Development Docker

This is a development Docker Compose solution for programming OpenEMR. New and
existing contributors can enjoy the benefits of simply running/testing their
local code with a single command!

_Note: This is only to be used for local development purposes. For
production-grade docker deployment options, please check out
[openemr-devops](https://github.com/openemr/openemr-devops)._

## Setup

**Step 1.** Install [git](https://git-scm.com/downloads),
[docker](https://www.docker.com/get-docker) and
[compose](https://docs.docker.com/compose/install/) for your system. Also, make
sure you have a [fork](https://help.github.com/articles/fork-a-repo/) of OpenEMR.

**Step 2.** Start OpenEMR.
```bash
$ git clone git@github.com:YOUR_USERNAME/openemr.git
```
There are 2 different schools of thought on where to run the docker from.
- Option 1. Run the docker from within your git repository.(this is also where you edit
scripts in your editor)
```bash
$ cd openemr
$ docker-compose up -d
```
- Option 2. Run the docker from a separate directory that is synchronized with your git
repository. For example, if used /var/www/openemr.
```bash
 $ cd /var/www/openemr
 $ docker-compose up -d
```
- At this time, I highly recommend option 2 since running OpenEMR will change
scripts, add files, add cache files, thus making it very tough to track your
code change. Modern GUI Editors support this; for example PHPStorm can be
set up to do this every time you save a script via
[PHP Storm Customizing Upload](https://www.jetbrains.com/help/phpstorm/customizing-upload.html).
 - Option 2 also allows support to quickly change branches on a repository to
develop/test other code. This is done by first running a command or script
to delete and replace the synchronized directory (ie. remove the /var/www/openemr
directory) and then restart the development docker (see below for how to do this)

**Step 3.** Open up OpenEMR in the latest Chrome or Firefox! You have many
options to choose from:
- http://localhost:8080 (with Apache and PHP 7.1)
- http://localhost:8081 (with Apache and PHP 7.2)
- http://localhost:8090 (with Nginx and PHP-FPM 5.6)
- http://localhost:8091 (with Nginx and PHP-FPM 7.0)
- http://localhost:8092 (with Nginx and PHP-FPM 7.1)
- http://localhost:8093 (with Nginx and PHP-FPM 7.2)
- http://localhost:8094 (with Nginx and PHP-FPM 5.6 with redis)
- http://localhost:8095 (with Nginx and PHP-FPM 7.0 with redis)
- http://localhost:8096 (with Nginx and PHP-FPM 7.1 with redis)
- http://localhost:8097 (with Nginx and PHP-FPM 7.2 with redis)
- https://localhost:9080 with SSL (with Apache and PHP 7.1)
- https://localhost:9081 with SSL (with Apache and PHP 7.2)
- https://localhost:9090 with SSL (with Nginx and PHP-FPM 5.6)
- https://localhost:9091 with SSL (with Nginx and PHP-FPM 7.0)
- https://localhost:9092 with SSL (with Nginx and PHP-FPM 7.1)
- https://localhost:9093 with SSL (with Nginx and PHP-FPM 7.2)
- https://localhost:9094 with SSL (with Nginx and PHP-FPM 5.6 with redis)
- https://localhost:9095 with SSL (with Nginx and PHP-FPM 7.0 with redis)
- https://localhost:9096 with SSL (with Nginx and PHP-FPM 7.1 with redis)
- https://localhost:9097 with SSL (with Nginx and PHP-FPM 7.2 with redis)

**Step 4.** Setup up OpenEMR. The first time you run OpenEMR (and whenever you clear and replace your
synchronized openemr directory and restart the development docker). On the main
setup input screen:
 - for `Server Host`, use either `mariadb` or `mysql` or `mariadb-dev` or `mysql-dev` or
 `mariadb-old` or `mariadb-very-old` or `mariadb-very-very-old` or `mysql-old` or
 `mysql-very-old` (you have all mariadb/mysql/mariadb-\*/mysql-\* dockers ready to go to make
 testing either one easy; `mysql` is version 5.7 and `mysql-dev` is version 8; `mysql-old` is
 version 5.6; `mysql-very-old` is version 5.5;`mariadb` is version 10.2 and `mariadb-dev` is
 version 10.3; `mariadb-old` is version 10.1; `mariadb-very-old` is version 10.0;
 `mariadb-very-very-old` is version 5.5)
 - for `Root Pass`, use `root`
 - for `User Hostname`, use `%`

## Stop/Clean Out Dockers
There are frequently times where you will want to remove the dockers and start anew.
For example, when you change github branches and start testing/developing on a
different github branch. This is done by first running a command or script
to delete and replace the synchronized directory (ie. remove the /var/www/openemr
directory) and then restart the development docker:
```bash
docker-compose down -v
docker-compose up -d
```

## Usage

### Examine Containers

Run `$ docker ps` to see the OpenEMR and MySQL containers in the following format:

```
CONTAINER ID        IMAGE                           COMMAND                  CREATED             STATUS              PORTS                                                                                                                                                                                                                                                                                                                                                                    NAMES
28a3f7e33838        openemr/dev-nginx               "nginx -g 'daemon ..."   3 minutes ago       Up 3 minutes        0.0.0.0:8090->80/tcp, 0.0.0.0:8091->81/tcp, 0.0.0.0:8092->82/tcp, 0.0.0.0:8093->83/tcp, 0.0.0.0:8094->84/tcp, 0.0.0.0:8095->85/tcp, 0.0.0.0:8096->86/tcp, 0.0.0.0:8097->87/tcp, 0.0.0.0:9090->443/tcp, 0.0.0.0:9091->444/tcp, 0.0.0.0:9092->445/tcp, 0.0.0.0:9093->446/tcp, 0.0.0.0:9094->447/tcp, 0.0.0.0:9095->448/tcp, 0.0.0.0:9096->449/tcp, 0.0.0.0:9097->450/tcp   openemr_nginx_1
219a30c59aa3        openemr/openemr:flex-edge       "./run_openemr.sh"       4 minutes ago       Up 3 minutes        0.0.0.0:8081->80/tcp, 0.0.0.0:9081->443/tcp                                                                                                                                                                                                                                                                                                                              openemr_openemr-7-2_1
50b79294ca5d        mysql:5.6                       "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql-old_1
7804b224ce55        openemr/dev-php-fpm:7.2-redis   "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-2-redis_1
a221cec1c5da        openemr/dev-php-fpm:5.6-redis   "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-5-6-redis_1
c1c6b0ef49f8        openemr/dev-php-fpm:5.6         "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-5-6_1
4435de4482cb        mariadb:5.5                     "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-very-very-old_1
2546a11ac9cc        redis                           "docker-entrypoint..."   4 minutes ago       Up 3 minutes        6379/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_redis_1
d01c3859f091        openemr/dev-php-fpm:7.0-redis   "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-0-redis_1
6d1515d30c2a        openemr/dev-php-fpm:7.1         "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-1_1
e3543abb4c13        mariadb:10.3                    "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-dev_1
052b7c1f8c04        jodogne/orthanc-plugins         "Orthanc /etc/orth..."   4 minutes ago       Up 3 minutes        0.0.0.0:4242->4242/tcp, 0.0.0.0:8042->8042/tcp                                                                                                                                                                                                                                                                                                                           openemr_orthanc_1
62034a6b49d4        phpmyadmin/phpmyadmin           "/run.sh phpmyadmin"     4 minutes ago       Up 3 minutes        0.0.0.0:8200->80/tcp                                                                                                                                                                                                                                                                                                                                                     openemr_phpmyadmin_1
071d20b70d98        mariadb:10.2                    "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb_1
49bded026d9d        openemr/dev-php-fpm:7.2         "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-2_1
52773d5218da        mysql:5.7                       "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql_1
f8fce8034868        mariadb:10.1                    "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-old_1
b9ad8a646651        mariadb:10.0                    "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-very-old_1
2413ad02518e        couchdb                         "tini -- /docker-e..."   4 minutes ago       Up 3 minutes        0.0.0.0:5984->5984/tcp, 4369/tcp, 9100/tcp, 0.0.0.0:6984->6984/tcp                                                                                                                                                                                                                                                                                                       openemr_couchdb_1
c9d45662214a        openemr/dev-php-fpm:7.1-redis   "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-1-redis_1
bcf1a325404e        mysql:8                         "docker-entrypoint..."   4 minutes ago       Up 4 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql-dev_1
e191ca135c0b        openemr/dev-php-fpm:7.0         "docker-php-entryp..."   4 minutes ago       Up 3 minutes        9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-0_1
c36b158b0a4b        mysql:5.5                       "docker-entrypoint..."   4 minutes ago       Up 3 minutes        3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql-very-old_1
be2bd9d5230e        openemr/openemr:flex            "./run_openemr.sh"       4 minutes ago       Up 4 minutes        0.0.0.0:8080->80/tcp, 0.0.0.0:9080->443/tcp                                                                                                                                                                                                                                                                                                                              openemr_openemr-7-1_1
```
 - Note the `NAMES` column is extremely important and how you run docker commands
on specific containers. For example, to go into a shell script in the
`openemr_openemr-7-1_1` container, would use:
```bash
docker exec -it openemr_openemr-7-1_1 bash
```

### Bash Access

```
$ docker exec -it <container_NAME> bash
```

### MySQL Client Access
GUI can be accessed via the phpMyAdmin at http://localhost:8200

If you are interested in using the MySQL client line as opposed to a GUI program, execute the following (password is passed in/is simple because this is for local development purposes):

```
$ docker exec -it <container_NAME> mysql -u root --password=root openemr
```

### Apache Error Log Tail

```
$ docker exec -it <container_NAME> tail -f /var/log/apache2/error.log
```
...if you want the `access.log`, you can use this approach as well.

### Recommended Development Setup

While there is no officially recommended toolset for programming OpenEMR,
many in the community have found
[PhpStorm](https://www.jetbrains.com/phpstorm/),
[Sublime Text](https://www.sublimetext.com/),
and [Vim](http://www.vim.org/) to be useful for coding. For database work,
[MySQL Workbench](https://dev.mysql.com/downloads/workbench/) or PhpMyAdmin
offers a smooth experience.

Many helpful tips and development "rules of thumb" can be found by reviewing
[OpenEMR Development](http://open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#Development).
Remember that learning to code against a very large and complex system is not a
task that will be completed over night. Feel free to post on
[the development forums](https://community.open-emr.org/c/openemr-development)
if you have any questions after reviewing the wiki.

### Ports

See the `docker-compose.yml` file in the base directory for port details.

All host machine ports can be changed by editing the `docker-compose.yml` file.
Host ports differ from the internal container ports by default to avoid conflicts
services potentially running on the host machine (a web server such as Nginx,
Tomcat, or Apache2 could be installed on the host machine that makes use of
port 80, for instance).

### Additional Build Tools

Programmers looking to use OpenEMR's [Bower](http://www.open-emr.org/wiki/index.php/Bower)
and [Composer](http://www.open-emr.org/wiki/index.php/Composer) build tools can
simply `bash` into the OpenEMR container and use them as expected.

### CouchDB
In OpenEMR, CouchDB is an option for the patients document storage. For this reason, a CouchDB
docker is included in this OpenEMR docker development environment. You can visit the CouchDB
GUI directly via http://localhost:5984/_utils/ with username `admin` and password `password`.
You can configure OpenEMR to use this CouchDB docker for patient document storage in OpenEMR
at Administration->Globals->Documents:
- Document Storage Method->CouchDB
- CouchDB HostName->admin
- CouchDB Password->password
- CouchDB Database can be set to any name you want

## Ongoing Development

### Orthanc
Developers are currently working on integrating the Orthanc PACS server into OpenEMR. This
feature is currently under development. Although it is not yet integrated with OpenEMR yet,
you can connect to the Orthanc application gui via http://localhost:8042/ with username `orthanc`
and password `orthanc`.

## The Docker Development Environment is a work in progress

This is an ongoing work in progress and feel free to join the super exciting
OpenEMR container projects. Feel free to post PR's to update the
docker-compose.yml script or this documentation. Also feel free to post
updates on the openemr/openemr:flex or openemr/openemr:flex-edge dockers
which can be found at
https://github.com/openemr/openemr-devops/tree/master/docker/openemr
