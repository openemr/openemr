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

**Step 3.** Open up OpenEMR in the latest Chrome or Firefox! You have several
options to choose from:
- http://localhost:8080 (with PHP 7.1)
- http://localhost:8081 (with PHP 7.2)
- https://localhost:8090 with SSL (with PHP 7.1)
- https://localhost:8091 with SSL (with PHP 7.2)

**Step 4.** Setup up OpenEMR. The first time you run OpenEMR (and whenever you clear and replace your
synchronized openemr directory and restart the development docker). On the main
setup input screen:
 - for `Server Host`, use either `mariadb` or `mysql` or `mariadb-dev` or `mysql-dev` or `mysql-old`
 or `mysql-very-old` (you have all mariadb/mysql/mariadb-\*/mysql-\* dockers ready to go to make
 testing either one easy; `mysql` is version 5.7 and `mysql-dev` is version 8; `mysql-old` is
 version 5.6; `mysql-very-old` is version 5.5;`mariadb` is version 10.2 and `mariadb-dev` is
 version 10.3)
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
CONTAINER ID        IMAGE                       COMMAND                  CREATED             STATUS              PORTS                                         NAMES
b68254d4bcff        mysql:5.7                   "docker-entrypoint..."   28 seconds ago      Up 25 seconds       3306/tcp                                                             openemr_mysql_1
9526a2515ede        openemr/openemr:flex-edge   "./run_openemr.sh"       28 seconds ago      Up 25 seconds       0.0.0.0:8081->80/tcp, 0.0.0.0:8091->443/tcp                          openemr_openemr-7-2_1
8eefa9405711        openemr/openemr:flex        "./run_openemr.sh"       28 seconds ago      Up 24 seconds       0.0.0.0:8080->80/tcp, 0.0.0.0:8090->443/tcp                          openemr_openemr-7-1_1
5c776d276b0f        mariadb:10.2                "docker-entrypoint..."   28 seconds ago      Up 26 seconds       3306/tcp                                                             openemr_mariadb_1
c3a8f9124f45        mariadb:10.3                "docker-entrypoint..."   28 seconds ago      Up 24 seconds       3306/tcp                                                             openemr_mariadb-dev_1
09591bfd9682        phpmyadmin/phpmyadmin       "/run.sh phpmyadmin"     28 seconds ago      Up 26 seconds       0.0.0.0:8100->80/tcp                                                 openemr_phpmyadmin_1
ec8bce53a671        mysql:8                     "docker-entrypoint..."   28 seconds ago      Up 26 seconds       3306/tcp                                                             openemr_mysql-dev_1
d27c90dc8d46        jodogne/orthanc-plugins     "Orthanc /etc/orth..."   28 seconds ago      Up 25 seconds       0.0.0.0:4242->4242/tcp, 0.0.0.0:8042->8042/tcp                       openemr_orthanc_1
08480e3e8d53        mysql:5.5                   "docker-entrypoint..."   28 seconds ago      Up 26 seconds       3306/tcp                                                             openemr_mysql-very-old_1
d257e8415361        couchdb                     "tini -- /docker-e..."   28 seconds ago      Up 26 seconds       0.0.0.0:5984->5984/tcp, 4369/tcp, 9100/tcp, 0.0.0.0:6984->6984/tcp   openemr_couchdb_1
a1eab82d90be        mysql:5.6                   "docker-entrypoint..."   28 seconds ago      Up 27 seconds       3306/tcp                                                             openemr_mysql-old_1
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
GUI can be accessed via the phpMyAdmin at http://localhost:8100

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
[MySQL Workbench](https://dev.mysql.com/downloads/workbench/) offers a smooth experience.

Many helpful tips and development "rules of thumb" can be found by reviewing
[OpenEMR Development](http://open-emr.org/wiki/index.php/OpenEMR_Wiki_Home_Page#Development).
Remember that learning to code against a very large and complex system is not a
task that will be completed over night. Feel free to post on
[the development forums](https://community.open-emr.org/c/openemr-development)
if you have any questions after reviewing the wiki.

### Ports

- HTTP is running on port 80 in the OpenEMR containers and port 8080 on the
PHP 7.1 host machine and port 8081 on the PHP 7.2 host machine.
- HTTPS is running on port 443 in the OpenEMR containers and port 8090 on the
PHP 7.1 host machine and port 8091 on the PHP 7.2 host machine.
- HTTP is running on port 80 in the PhpMyADMIN container and port 8100 on the
host machine.
- MySQL is running on port 3306 in the MariaDB/MySQL/MariaDB-\*/MySQL-\* containers.

All host machine ports can be changed by editing the `docker-compose.yml` file.
Host ports differ from the internal container ports by default to avoid conflicts
services potentially running on the host machine (a web server such as Nginx,
Tomcat, or Apache2 could be installed on the host machine that makes use of
port 80, for instance).

### Additional Build Tools

Programmers looking to use OpenEMR's [Bower](http://www.open-emr.org/wiki/index.php/Bower)
and [Composer](http://www.open-emr.org/wiki/index.php/Composer) build tools can
simply `bash` into the OpenEMR container and use them as expected.

### Reset Workspace

There are frequently times where you will want to remove the dockers and start anew.
For example, when you change github branches and start testing/developing on a
different github branch. This is done by first running a command or script
to delete and replace the synchronized directory (ie. remove the /var/www/openemr
directory) and then restart the development docker:
```bash
docker-compose down -v
docker-compose up -d
```
### Work in progress

This is an ongoing work in progress and feel free to join the super exciting
OpenEMR container projects. Feel free to post PR's to update the
docker-compose.yml script or this documentation. Also feel free to post
updates on the openemr/openemr:flex or openemr/openemr:flex-edge dockers
which can be found at
https://github.com/openemr/openemr-devops/tree/master/docker/openemr
