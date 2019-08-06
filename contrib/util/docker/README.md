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
- http://localhost:8082 (with Apache and PHP 7.3)
- http://localhost:8083 (with Alpine Edge (Apache and now PHP 7.3))
- http://localhost:8084 (with Apache and PHP 7.1 with redis)
- http://localhost:8085 (with Apache and PHP 7.2 with redis)
- http://localhost:8086 (with Apache and PHP 7.3 with redis)
- http://localhost:8087 (with Alpine Edge (Apache and now PHP 7.3) with redis)
- http://localhost:8100 (with Nginx and PHP-FPM 5.6)
- http://localhost:8101 (with Nginx and PHP-FPM 7.0)
- http://localhost:8102 (with Nginx and PHP-FPM 7.1)
- http://localhost:8103 (with Nginx and PHP-FPM 7.2)
- http://localhost:8104 (with Nginx and PHP-FPM 7.3)
- http://localhost:8105 (with Nginx and PHP-FPM 7.4)
- http://localhost:8150 (with Nginx and PHP-FPM 5.6 with redis)
- http://localhost:8151 (with Nginx and PHP-FPM 7.0 with redis)
- http://localhost:8152 (with Nginx and PHP-FPM 7.1 with redis)
- http://localhost:8153 (with Nginx and PHP-FPM 7.2 with redis)
- http://localhost:8154 (with Nginx and PHP-FPM 7.3 with redis)
- http://localhost:8155 (with Nginx and PHP-FPM 7.4 with redis)
- https://localhost:9080 with SSL (with Apache and PHP 7.1)
- https://localhost:9081 with SSL (with Apache and PHP 7.2)
- https://localhost:9082 with SSL (with Apache and PHP 7.3)
- https://localhost:9083 (with Alpine Edge (Apache and now PHP 7.3))
- https://localhost:9084 with SSL (with Apache and PHP 7.1 with redis)
- https://localhost:9085 with SSL (with Apache and PHP 7.2 with redis)
- https://localhost:9086 with SSL (with Apache and PHP 7.3 with redis)
- https://localhost:9087 (with Alpine Edge (Apache and now PHP 7.3) with redis)
- https://localhost:9100 with SSL (with Nginx and PHP-FPM 5.6)
- https://localhost:9101 with SSL (with Nginx and PHP-FPM 7.0)
- https://localhost:9102 with SSL (with Nginx and PHP-FPM 7.1)
- https://localhost:9103 with SSL (with Nginx and PHP-FPM 7.2)
- https://localhost:9104 with SSL (with Nginx and PHP-FPM 7.3)
- https://localhost:9105 with SSL (with Nginx and PHP-FPM 7.4)
- https://localhost:9150 with SSL (with Nginx and PHP-FPM 5.6 with redis)
- https://localhost:9151 with SSL (with Nginx and PHP-FPM 7.0 with redis)
- https://localhost:9152 with SSL (with Nginx and PHP-FPM 7.1 with redis)
- https://localhost:9153 with SSL (with Nginx and PHP-FPM 7.2 with redis)
- https://localhost:9154 with SSL (with Nginx and PHP-FPM 7.3 with redis)
- https://localhost:9154 with SSL (with Nginx and PHP-FPM 7.4 with redis)

**Step 4.** Setup up OpenEMR. The first time you run OpenEMR (and whenever you clear and replace your
synchronized openemr directory and restart the development docker). On the main
setup input screen:
 - for `Server Host`, use either `mariadb` or `mysql` or `mariadb-very-old` or
   `mariadb-very-very-old` or `mysql-old` or `mysql-very-old` (you have all
   mariadb/mysql/mariadb-\*/mysql-\* dockers ready to go to make testing either one easy;
   `mysql` is version 8.0; `mysql-old` is version 5.7; `mysql-very-old` is version 5.6;
   `mariadb` is version 10.4; `mariadb-old` is version 10.3; `mariadb-very-old` is
   version 10.2; `mariadb-very-very-old` is version 10.1)
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
CONTAINER ID        IMAGE                           COMMAND                  CREATED              STATUS              PORTS                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        NAMES
02fbe38dbbc1        openemr/dev-nginx               "nginx -g 'daemon ..."   About a minute ago   Up About a minute   0.0.0.0:8100->80/tcp, 0.0.0.0:8101->81/tcp, 0.0.0.0:8102->82/tcp, 0.0.0.0:8103->83/tcp, 0.0.0.0:8104->84/tcp, 0.0.0.0:8105->85/tcp, 0.0.0.0:8150->90/tcp, 0.0.0.0:8151->91/tcp, 0.0.0.0:8152->92/tcp, 0.0.0.0:8153->93/tcp, 0.0.0.0:8154->94/tcp, 0.0.0.0:8155->95/tcp, 0.0.0.0:9100->440/tcp, 0.0.0.0:9101->441/tcp, 0.0.0.0:9102->442/tcp, 0.0.0.0:9103->443/tcp, 0.0.0.0:9104->444/tcp, 0.0.0.0:9105->445/tcp, 0.0.0.0:9150->450/tcp, 0.0.0.0:9151->451/tcp, 0.0.0.0:9152->452/tcp, 0.0.0.0:9153->453/tcp, 0.0.0.0:9154->454/tcp, 0.0.0.0:9155->455/tcp   docker_nginx_1
77de86fb9968        mysql:5.7                       "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp, 33060/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          docker_mysql-old_1
a43082ad4f6d        openemr/dev-php-fpm:7.3         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-3_1
91a7a5688a0a        openemr/openemr:flex-3.10       "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8082->80/tcp, 0.0.0.0:9082->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-7-3_1
1a3a709dfdf3        openemr/openemr:flex-edge       "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8083->80/tcp, 0.0.0.0:9083->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-edge_1
d62eba7109d0        mariadb:10.2                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_mariadb-old_1
cfe9ec793c6f        openemr/dev-php-fpm:5.6         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-5-6_1
67a3b42d0482        couchdb                         "tini -- /docker-e..."   About a minute ago   Up About a minute   0.0.0.0:5984->5984/tcp, 4369/tcp, 9100/tcp, 0.0.0.0:6984->6984/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           docker_couchdb_1
62eba51d7625        redis                           "docker-entrypoint..."   About a minute ago   Up About a minute   6379/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_redis_1
07d1f9593ba6        openemr/openemr:flex-3.7        "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8084->80/tcp, 0.0.0.0:9084->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-7-1-redis_1
99b9e8c8d9b9        openemr/openemr:flex-3.7        "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8080->80/tcp, 0.0.0.0:9080->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-7-1_1
37bd6a60977d        openemr/openemr:flex-3.10       "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8086->80/tcp, 0.0.0.0:9086->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-7-3-redis_1
8ded4f104df7        mariadb:10.4                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_mariadb-dev_1
90f405cbb103        mysql:8                         "docker-entrypoint..."   About a minute ago   Up About a minute   33060/tcp, 0.0.0.0:8220->3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_mysql_1
465b9c36e3ba        jodogne/orthanc-plugins         "Orthanc /etc/orth..."   About a minute ago   Up About a minute   0.0.0.0:4242->4242/tcp, 0.0.0.0:8042->8042/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_orthanc_1
f7291908f602        openemr/dev-php-fpm:7.4-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-4-redis_1
b20d56d031d8        openemr/dev-php-fpm:7.0         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-0_1
551507eed0e4        openemr/dev-php-fpm:7.2         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-2_1
cf9df72a5cbb        mysql:5.6                       "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_mysql-very-old_1
5c862ac2da7c        openemr/dev-php-fpm:7.3-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-3-redis_1
9143459d2278        mariadb:10.3                    "docker-entrypoint..."   About a minute ago   Up About a minute   0.0.0.0:8210->3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       docker_mariadb_1
25c7fd7e7f08        openemr/dev-php-fpm:7.4         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-4_1
c730a6b5578f        openemr/openemr:flex-edge       "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8087->80/tcp, 0.0.0.0:9087->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-edge-redis_1
df5f7bb4d5df        openemr/openemr:flex-3.9        "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8085->80/tcp, 0.0.0.0:9085->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-7-2-redis_1
3b74f3b33f0b        openemr/dev-php-fpm:5.6-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-5-6-redis_1
0e374a825a8a        phpmyadmin/phpmyadmin           "/run.sh phpmyadmin"     About a minute ago   Up About a minute   0.0.0.0:8200->80/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         docker_phpmyadmin_1
51d039ec6408        openemr/openemr:flex-3.9        "./run_openemr.sh"       About a minute ago   Up About a minute   0.0.0.0:8081->80/tcp, 0.0.0.0:9081->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  docker_openemr-7-2_1
1a43484ce178        openemr/dev-php-fpm:7.1-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-1-redis_1
305e2889a657        openemr/dev-php-fpm:7.1         "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-1_1
60a6d6eee71b        openemr/dev-php-fpm:7.2-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-2-redis_1
0c245666c40a        mariadb:10.1                    "docker-entrypoint..."   About a minute ago   Up About a minute   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_mariadb-very-old_1
7156517ee574        openemr/dev-php-fpm:7.0-redis   "docker-php-entryp..."   About a minute ago   Up About a minute   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_dev-php-fpm-7-0-redis_1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   docker_mysql-very-very-old_1                                                                                                                                                                                                                                                                                                                                                                openemr_mariadb-old_1
```
 - Note the `NAMES` column is extremely important and how you run docker commands
on specific containers. For example, to go into a shell script in the
`openemr_openemr-7-2_1` container, would use:
```bash
docker exec -it openemr_openemr-7-2_1 bash
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
