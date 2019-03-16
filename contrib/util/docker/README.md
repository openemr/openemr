# OpenEMR Development Docker Environments

## There are 2 different development docker environments

### Easy Development Docker Environment ###
The Easy Development Docker Environment is what we highly recommend. It makes testing, development, and use
of a git repository very easy. The instructions for The Easy Development Docker environment can be found here:
https://github.com/openemr/openemr/blob/master/CONTRIBUTING.md#code-contributions-local-development

---

### Insane Development Docker Environment ###
The Insane Development Docker Environment will load up about 30 separate dockers and allow you to
test almost any version of mysql/mariadb/php, however it is not nearly as easy to use as the above Easy Development
Docker Environment. See below for instructions of use of the Insane Development Docker Environment.

#### Setup

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
$ cd openemr/contrib/util/docker
$ docker-compose up -d
```
- Option 2. Run the docker from a separate directory that is synchronized with your git
repository. For example, if used /var/www/openemr.
```bash
 $ cd /var/www/openemr/contrib/util/docker
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
- http://localhost:8082 (with Apache and PHP 7.1 with redis)
- http://localhost:8083 (with Apache and PHP 7.2 with redis)
- http://localhost:8090 (with Nginx and PHP-FPM 5.6)
- http://localhost:8091 (with Nginx and PHP-FPM 7.0)
- http://localhost:8092 (with Nginx and PHP-FPM 7.1)
- http://localhost:8093 (with Nginx and PHP-FPM 7.2)
- http://localhost:8094 (with Nginx and PHP-FPM 7.3)
- http://localhost:8095 (with Nginx and PHP-FPM 5.6 with redis)
- http://localhost:8096 (with Nginx and PHP-FPM 7.0 with redis)
- http://localhost:8097 (with Nginx and PHP-FPM 7.1 with redis)
- http://localhost:8098 (with Nginx and PHP-FPM 7.2 with redis)
- http://localhost:8099 (with Nginx and PHP-FPM 7.3 with redis)
- https://localhost:9080 with SSL (with Apache and PHP 7.1)
- https://localhost:9081 with SSL (with Apache and PHP 7.2)
- https://localhost:9082 with SSL (with Apache and PHP 7.1 with redis)
- https://localhost:9083 with SSL (with Apache and PHP 7.2 with redis)
- https://localhost:9090 with SSL (with Nginx and PHP-FPM 5.6)
- https://localhost:9091 with SSL (with Nginx and PHP-FPM 7.0)
- https://localhost:9092 with SSL (with Nginx and PHP-FPM 7.1)
- https://localhost:9093 with SSL (with Nginx and PHP-FPM 7.2)
- https://localhost:9094 with SSL (with Nginx and PHP-FPM 7.3)
- https://localhost:9095 with SSL (with Nginx and PHP-FPM 5.6 with redis)
- https://localhost:9096 with SSL (with Nginx and PHP-FPM 7.0 with redis)
- https://localhost:9097 with SSL (with Nginx and PHP-FPM 7.1 with redis)
- https://localhost:9098 with SSL (with Nginx and PHP-FPM 7.2 with redis)
- https://localhost:9099 with SSL (with Nginx and PHP-FPM 7.3 with redis)

**Step 4.** Setup up OpenEMR. The first time you run OpenEMR (and whenever you clear and replace your
synchronized openemr directory and restart the development docker). On the main
setup input screen:
 - for `Server Host`, use either `mariadb` or `mysql` or `mariadb-dev` or
 `mariadb-old` or `mariadb-very-old` or `mariadb-very-very-old` or `mariadb-very-very-very-old` or `mysql-old` or
 `mysql-very-old` or `mysql-very-very-old` (you have all mariadb/mysql/mariadb-\*/mysql-\* dockers ready to go to make
 testing either one easy; `mysql` is version 8.0; `mysql-old` is version 5.7; `mysql-very-old` is
 version 5.6; `mysql-very-very-old` is version 5.5;`mariadb` is version 10.3 and `mariadb-dev` is
 version 10.4; `mariadb-old` is version 10.2; `mariadb-very-old` is version 10.1; `mariadb-very-very-old` is
 version 10.0; `mariadb-very-very-very-old` is version 5.5)
 - for `Root Pass`, use `root`
 - for `User Hostname`, use `%`

#### Stop/Clean Out Dockers
There are frequently times where you will want to remove the dockers and start anew.
For example, when you change github branches and start testing/developing on a
different github branch. This is done by first running a command or script
to delete and replace the synchronized directory (ie. remove the /var/www/openemr
directory) and then restart the development docker:
```bash
docker-compose down -v
docker-compose up -d
```

#### Usage

##### Examine Containers

Run `$ docker ps` to see the OpenEMR and MySQL containers in the following format:

```
CONTAINER ID        IMAGE                           COMMAND                  CREATED              STATUS              PORTS                                                                                                                                                                                                                                                                                                                                                                    NAMES
5b63503bd31c        openemr/dev-nginx               "nginx -g 'daemon ..."   About a minute ago   Up About a minute   0.0.0.0:8090->80/tcp, 0.0.0.0:8091->81/tcp, 0.0.0.0:8092->82/tcp, 0.0.0.0:8093->83/tcp, 0.0.0.0:8094->84/tcp, 0.0.0.0:8095->85/tcp, 0.0.0.0:8096->86/tcp, 0.0.0.0:8097->87/tcp, 0.0.0.0:9090->443/tcp, 0.0.0.0:9091->444/tcp, 0.0.0.0:9092->445/tcp, 0.0.0.0:9093->446/tcp, 0.0.0.0:9094->447/tcp, 0.0.0.0:9095->448/tcp, 0.0.0.0:9096->449/tcp, 0.0.0.0:9097->450/tcp   openemr_nginx_1
a630bd9804b6        openemr/dev-php-fpm:5.6         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-5-6_1
94da8fec5b26        openemr/dev-php-fpm:7.1-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-1-redis_1
63724064e28e        openemr/openemr:flex-edge       "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8081->80/tcp, 0.0.0.0:9081->443/tcp                                                                                                                                                                                                                                                                                                                              openemr_openemr-7-2_1
a5a80ca375f3        mysql:8                         "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql-dev_1
8cb752b8494f        mysql:5.6                       "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql-old_1
633fbcf206a2        openemr/dev-php-fpm:5.6-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-5-6-redis_1
9e66c5cfd7a6        openemr/openemr:flex            "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8080->80/tcp, 0.0.0.0:9080->443/tcp                                                                                                                                                                                                                                                                                                                              openemr_openemr-7-1_1
d439e9920001        mysql:5.5                       "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql-very-old_1
a3166ebde805        mariadb:10.3                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-dev_1
67dbc84627c6        openemr/dev-php-fpm:7.2         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-2_1
c188a3da7212        mariadb:5.5                     "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-very-very-old_1
6b7d7ded8223        mysql:5.7                       "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mysql_1
2d6a9cba4a80        phpmyadmin/phpmyadmin           "/run.sh phpmyadmin"     About a minute ago   Up About a minute   0.0.0.0:8200->80/tcp                                                                                                                                                                                                                                                                                                                                                     openemr_phpmyadmin_1
b78c6a616812        openemr/dev-php-fpm:7.0         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-0_1
c50d8ecdc7df        openemr/openemr:flex            "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8082->80/tcp, 0.0.0.0:9082->443/tcp                                                                                                                                                                                                                                                                                                                              openemr_openemr-7-1-redis_1
72967883635c        jodogne/orthanc-plugins         "Orthanc /etc/orth..."   About a minute ago   Up About a minute   0.0.0.0:4242->4242/tcp, 0.0.0.0:8042->8042/tcp                                                                                                                                                                                                                                                                                                                           openemr_orthanc_1
bfde9c076a18        mariadb:10.0                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-very-old_1
498badea0c41        couchdb                         "tini -- /docker-e..."   About a minute ago   Up About a minute   0.0.0.0:5984->5984/tcp, 4369/tcp, 9100/tcp, 0.0.0.0:6984->6984/tcp                                                                                                                                                                                                                                                                                                       openemr_couchdb_1
3be06850a69f        openemr/dev-php-fpm:7.1         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-1_1
2d64832cd49f        redis                           "docker-entrypoint..."   About a minute ago   Up About a minute   6379/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_redis_1
b8f712411f6c        openemr/dev-php-fpm:7.2-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-2-redis_1
00051535e11d        openemr/openemr:flex-edge       "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8083->80/tcp, 0.0.0.0:9083->443/tcp                                                                                                                                                                                                                                                                                                                              openemr_openemr-7-2-redis_1
72b5472e0437        openemr/dev-php-fpm:7.0-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_dev-php-fpm-7-0-redis_1
47f9d56a16c6        mariadb:10.2                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb_1
274490c06b08        mariadb:10.1                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                 openemr_mariadb-old_1
```
 - Note the `NAMES` column is extremely important and how you run docker commands
on specific containers. For example, to go into a shell script in the
`openemr_openemr-7-1_1` container, would use:
```bash
docker exec -it openemr_openemr-7-1_1 bash
```

##### Bash Access

```
$ docker exec -it <container_NAME> bash
```

##### MySQL Client Access
There are 2 options for gui access:
 - GUI can be accessed via the phpMyAdmin at http://localhost:8200 for all sql dockers
 - Or you can directly connect to port 8210 (`mariadb` server only) or 8220 (`mysql` server only) via your favorite sql tool (Mysql Workbench etc.). Note this option is limited to the `mysql` and `mariadb` servers.
If you are interested in using the MySQL client line as opposed to a GUI program, execute the following (password is passed in/is simple because this is for local development purposes):

```
$ docker exec -it <container_NAME> mysql -u root --password=root openemr
```

##### Apache Error Log Tail

```
$ docker exec -it <container_NAME> tail -f /var/log/apache2/error.log
```
...if you want the `access.log`, you can use this approach as well.

##### Recommended Development Setup

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

##### Ports

See the `docker-compose.yml` file in the contrib/util/docker directory for port details.

All host machine ports can be changed by editing the `docker-compose.yml` file.
Host ports differ from the internal container ports by default to avoid conflicts
services potentially running on the host machine (a web server such as Nginx,
Tomcat, or Apache2 could be installed on the host machine that makes use of
port 80, for instance).

##### Additional Build Tools

Programmers looking to use OpenEMR's and [Composer and NPM](http://www.open-emr.org/wiki/index.php//Composer_and_NPM)
build tools can simply `bash` into the OpenEMR container and use them as expected.

##### CouchDB
In OpenEMR, CouchDB is an option for the patients document storage. For this reason, a CouchDB
docker is included in this OpenEMR docker development environment. You can visit the CouchDB
GUI directly via http://localhost:5984/_utils/ with username `admin` and password `password`.
You can configure OpenEMR to use this CouchDB docker for patient document storage in OpenEMR
at Administration->Globals->Documents:
- Document Storage Method->CouchDB
- CouchDB HostName->couchdb
- CouchDB UserName->admin
- CouchDB Password->password
- CouchDB Database can be set to any name you want

#### Ongoing Development

##### Orthanc
Developers are currently working on integrating the Orthanc PACS server into OpenEMR. This
feature is currently under development. Although it is not yet integrated with OpenEMR yet,
you can connect to the Orthanc application gui via http://localhost:8042/ with username `orthanc`
and password `orthanc`. The nginx docker has also been set up to work as a reverse proxy
with orthanc to allow ongoing development via http://localhost:8090/orthanc/ (Note this reverse
proxy is still a work in progress)

#### The Insane Docker Development Environment is a work in progress

This is an ongoing work in progress and feel free to join the super exciting
OpenEMR container projects. Feel free to post PR's to update the
docker-compose.yml script or this documentation. Also feel free to post
updates on the openemr/openemr:flex or openemr/openemr:flex-edge dockers
which can be found at
https://github.com/openemr/openemr-devops/tree/master/docker/openemr

#### Stuff that needs fixing
1. The reverse proxy for orthanc
2. zip packages in the php 7.3 fpm dockers are not working. Will try to bring in zip intermittently.
