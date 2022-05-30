### Insane Development Docker Environment ###
The Insane Development Docker Environment will load up about 37 separate dockers and allow you to
test almost any version of mysql/mariadb/php, however it is not nearly as easy to use as the above Easy Development
Docker Environment. See below for instructions of use of the Insane Development Docker Environment.

#### Setup

**Step 1.** Install [git](https://git-scm.com/downloads),
[docker](https://www.docker.com/get-docker) and
[compose](https://docs.docker.com/compose/install/) for your system. Also, make
sure you have a [fork](https://help.github.com/articles/fork-a-repo/) of OpenEMR.
- If you want to set up the base services(e.g. git, docker, docker-compose, openemr-cmd) easily, please try [openemr-env-installer](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-env-installer)
- If you want to troubleshoot with the below steps easier, please also [install openemr-cmd](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-cmd) for your system
- If you want to monitor and easily manage the docker environment, please also [install openemr-monitor](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-monitor) and [install portainer](https://github.com/openemr/openemr-devops/tree/master/utilities/portainer) for your system
- If you want to migrator the running docker environment, please try [openemr-env-migrator](https://github.com/openemr/openemr-devops/tree/master/utilities/openemr-env-migrator)

**Step 2.** Start OpenEMR.
```bash
$ git clone git@github.com:YOUR_USERNAME/openemr.git
```
There are 2 different schools of thought on where to run the docker from.
- Option 1. Run the docker from within your git repository.(this is also where you edit
scripts in your editor)
```bash
$ cd openemr/docker/development-insane
$ docker-compose up -d
```
- Option 2. Run the docker from a separate directory that is synchronized with your git
repository. For example, if used /var/www/openemr.
```bash
 $ cd /var/www/openemr/docker/development-insane
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
- http://localhost:8080 (with Alpine 3.12 (Apache and PHP 7.3))
- http://localhost:8081 (with Alpine 3.15 (Apache and PHP 7.4))
- http://localhost:8082 (with Alpine 3.15 (Apache and PHP 8.0))
- http://localhost:8083 (with Alpine 3.16 (Apache and PHP 8.1))
- http://localhost:8084 (with Alpine Edge (Apache and now PHP 8.1))
- http://localhost:8090 (with Alpine 3.12 (Apache and PHP 7.3) with redis)
- http://localhost:8091 (with Alpine 3.15 (Apache and PHP 7.4) with redis)
- http://localhost:8092 (with Alpine 3.15 (Apache and PHP 8.0) with redis)
- http://localhost:8093 (with Alpine 3.16 (Apache and PHP 8.1) with redis)
- http://localhost:8094 (with Alpine Edge (Apache and now PHP 8.1) with redis)
- http://localhost:8100 (with Nginx and PHP-FPM 7.3)
- http://localhost:8101 (with Nginx and PHP-FPM 7.4)
- http://localhost:8102 (with Nginx and PHP-FPM 8.0)
- http://localhost:8103 (with Nginx and PHP-FPM 8.1)
- http://localhost:8104 (with Nginx and PHP-FPM 8.2)
- http://localhost:8150 (with Nginx and PHP-FPM 7.3 with redis)
- http://localhost:8151 (with Nginx and PHP-FPM 7.4 with redis)
- http://localhost:8152 (with Nginx and PHP-FPM 8.0 with redis)
- http://localhost:8153 (with Nginx and PHP-FPM 8.1 with redis)
- http://localhost:8154 (with Nginx and PHP-FPM 8.2 with redis)
- https://localhost:9080 with SSL and Alpine 3.12 (with Apache and PHP 7.3)
- https://localhost:9081 with SSL and Alpine 3.15 (with Apache and PHP 7.4)
- https://localhost:9082 with SSL and Alpine 3.15 (with Apache and PHP 8.0)
- https://localhost:9083 with SSL and Alpine 3.16 (with Apache and PHP 8.1)
- https://localhost:9084 with SSL and Alpine Edge (with Apache and now PHP 8.1)
- https://localhost:9090 with SSL and Alpine 3.12 (with Apache and PHP 7.3 with redis)
- https://localhost:9091 with SSL and Alpine 3.15 (with Apache and PHP 7.4 with redis)
- https://localhost:9092 with SSL and Alpine 3.15 (with Apache and PHP 8.0 with redis)
- https://localhost:9093 with SSL and Alpine 3.16 (with Apache and PHP 8.1 with redis)
- https://localhost:9094 with SSL and Alpine Edge (with Apache and now PHP 8.1 with redis)
- https://localhost:9100 with SSL (with Nginx and PHP-FPM 7.3)
- https://localhost:9101 with SSL (with Nginx and PHP-FPM 7.4)
- https://localhost:9102 with SSL (with Nginx and PHP-FPM 8.0)
- https://localhost:9103 with SSL (with Nginx and PHP-FPM 8.1)
- https://localhost:9104 with SSL (with Nginx and PHP-FPM 8.2)
- https://localhost:9150 with SSL (with Nginx and PHP-FPM 7.3 with redis)
- https://localhost:9151 with SSL (with Nginx and PHP-FPM 7.4 with redis)
- https://localhost:9152 with SSL (with Nginx and PHP-FPM 8.0 with redis)
- https://localhost:9153 with SSL (with Nginx and PHP-FPM 8.1 with redis)
- https://localhost:9154 with SSL (with Nginx and PHP-FPM 8.2 with redis)

**Step 4.** Setup up OpenEMR. The first time you run OpenEMR (and whenever you clear and replace your
synchronized openemr directory and restart the development docker). On the main
setup input screen:
 - for `Server Host`, use either `mariadb` or `mariadb-ssl` or `mysql` or `mariadb-old` or `mariadb-very-old` or
   `mariadb-very-very-old` or `mariadb-very-very-very-old` or `mysql-old` (you have all
   mariadb/mysql/mariadb-\*/mysql-\* dockers ready to go to make testing either one easy;
   `mysql` is version 8.0; `mysql-old` is version 5.7;
   `mariadb` is version 10.6; `mariadb-ssl` is version 10.6 with support for ssl; `mariadb-old` is version 10.5; `mariadb-very-old` is
   version 10.4; `mariadb-very-very-old` is version 10.3; `mariadb-very-very-very-old` is version 10.2)
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

#### Updating Dockers
To ensure you are using the most recent dockers, recommend running below set of commands intermittently:
```console
docker pull openemr/openemr:flex-edge
docker pull openemr/openemr:flex-3.16
docker pull openemr/openemr:flex-3.15
docker pull openemr/openemr:flex-3.15-8
docker pull openemr/openemr:flex-3.12
docker pull openemr/dev-php-fpm:8.2
docker pull openemr/dev-php-fpm:8.1
docker pull openemr/dev-php-fpm:8.0
docker pull openemr/dev-php-fpm:7.4
docker pull openemr/dev-php-fpm:7.3
docker pull openemr/dev-php-fpm:8.2-redis
docker pull openemr/dev-php-fpm:8.1-redis
docker pull openemr/dev-php-fpm:8.0-redis
docker pull openemr/dev-php-fpm:7.4-redis
docker pull openemr/dev-php-fpm:7.3-redis
docker pull openemr/dev-nginx
docker pull mariadb:10.6
docker pull mariadb:10.5
docker pull mariadb:10.4
docker pull mariadb:10.3
docker pull mariadb:10.2
docker pull mysql:8
docker pull mysql:5.7
docker pull phpmyadmin/phpmyadmin
docker pull couchdb
docker pull jodogne/orthanc-plugins
docker pull openemr/dev-ldap:insane
docker pull redis
docker pull ibmcom/ibm-fhir-server

```

#### Usage

##### Examine Containers

Run `$ docker ps` to see the OpenEMR and MySQL containers in the following format:

```
CONTAINER ID        IMAGE                           COMMAND                  CREATED             STATUS              PORTS                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  NAMES
21919d2661bf        openemr/dev-nginx               "nginx -g 'daemon ..."   13 minutes ago      Up 13 minutes       0.0.0.0:8100->80/tcp, 0.0.0.0:8101->81/tcp, 0.0.0.0:8102->82/tcp, 0.0.0.0:8103->83/tcp, 0.0.0.0:8104->84/tcp, 0.0.0.0:8105->85/tcp, 0.0.0.0:8106->86/tcp, 0.0.0.0:8150->90/tcp, 0.0.0.0:8151->91/tcp, 0.0.0.0:8152->92/tcp, 0.0.0.0:8153->93/tcp, 0.0.0.0:8154->94/tcp, 0.0.0.0:8155->95/tcp, 0.0.0.0:8156->96/tcp, 0.0.0.0:9100->440/tcp, 0.0.0.0:9101->441/tcp, 0.0.0.0:9102->442/tcp, 0.0.0.0:9103->443/tcp, 0.0.0.0:9104->444/tcp, 0.0.0.0:9105->445/tcp, 0.0.0.0:9106->446/tcp, 0.0.0.0:9150->450/tcp, 0.0.0.0:9151->451/tcp, 0.0.0.0:9152->452/tcp, 0.0.0.0:9153->453/tcp, 0.0.0.0:9154->454/tcp, 0.0.0.0:9155->455/tcp, 0.0.0.0:9156->456/tcp   docker_nginx_1
f9b3844b16b4        openemr/openemr:flex-3.10       "./run_openemr.sh"       14 minutes ago      Up 13 minutes       0.0.0.0:8087->80/tcp, 0.0.0.0:9087->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-3-redis-310_1
33561685efcf        openemr/dev-php-fpm:5.6-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-5-6-redis_1
84c1b8514556        mariadb:10.4                    "docker-entrypoint..."   14 minutes ago      Up 13 minutes       0.0.0.0:8210->3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 docker_mariadb_1
6d55960d12ee        couchdb                         "tini -- /docker-e..."   14 minutes ago      Up 13 minutes       0.0.0.0:5984->5984/tcp, 4369/tcp, 9100/tcp, 0.0.0.0:6984->6984/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     docker_couchdb_1
217b51921613        mariadb:10.2                    "docker-entrypoint..."   14 minutes ago      Up 14 minutes       3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_mariadb-very-old_1
715782908b7b        mysql:8                         "docker-entrypoint..."   14 minutes ago      Up 13 minutes       33060/tcp, 0.0.0.0:8220->3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      docker_mysql_1
621069bc0bba        redis                           "docker-entrypoint..."   14 minutes ago      Up 14 minutes       6379/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_redis_1
48d1998f19a2        openemr/openemr:flex-3.10       "./run_openemr.sh"       14 minutes ago      Up 13 minutes       0.0.0.0:8082->80/tcp, 0.0.0.0:9082->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-3-310_1
ca615ad4a021        ibmcom/ibm-fhir-server          "/opt/ol/helpers/r..."   14 minutes ago      Up 13 minutes       9080/tcp, 0.0.0.0:9443->9443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       docker_fhir_1
2cfba3013358        openemr/openemr:flex-3.11       "./run_openemr.sh"       14 minutes ago      Up 13 minutes       0.0.0.0:8083->80/tcp, 0.0.0.0:9083->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-3-311_1
534aa394a3f6        openemr/openemr:flex-3.9        "./run_openemr.sh"       14 minutes ago      Up 14 minutes       0.0.0.0:8081->80/tcp, 0.0.0.0:9081->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-2_1
5833b84a791c        openemr/dev-php-fpm:8.0-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-8-0-redis_1
9922f2b50e4d        openemr/dev-php-fpm:7.2-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-2-redis_1
d2fe53bb3177        openemr/dev-php-fpm:7.1-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-1-redis_1
b5918e344113        openemr/openemr:flex-edge       "./run_openemr.sh"       14 minutes ago      Up 14 minutes       0.0.0.0:8089->80/tcp, 0.0.0.0:9089->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-edge-redis_1
653184128fce        openemr/dev-php-fpm:7.2         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-2_1
97e664c8f555        jodogne/orthanc-plugins         "Orthanc /etc/orth..."   14 minutes ago      Up 13 minutes       0.0.0.0:4242->4242/tcp, 0.0.0.0:8042->8042/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         docker_orthanc_1
56e2e2bec925        osixia/openldap                 "/container/tool/run"    14 minutes ago      Up 14 minutes       389/tcp, 636/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       docker_openldap_1
f5009868ab52        openemr/dev-php-fpm:7.0-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-0-redis_1
e3fbde1539ab        openemr/dev-php-fpm:8.0         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-8-0_1
95fbfe06c740        mariadb:10.5                    "docker-entrypoint..."   14 minutes ago      Up 14 minutes       3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_mariadb-dev_1
07c1fb11e3a1        openemr/dev-php-fpm:7.4         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-4_1
9382e3f1b8be        mariadb:10.1                    "docker-entrypoint..."   14 minutes ago      Up 14 minutes       3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_mariadb-very-very-old_1
33ba9403b218        mariadb:10.3                    "docker-entrypoint..."   14 minutes ago      Up 14 minutes       3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_mariadb-old_1
6e0bd890cd30        openemr/openemr:flex-3.7        "./run_openemr.sh"       14 minutes ago      Up 14 minutes       0.0.0.0:8080->80/tcp, 0.0.0.0:9080->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-1_1
a81e7e2cc6c2        openemr/dev-php-fpm:7.1         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-1_1
77b9a1c21867        openemr/dev-php-fpm:5.6         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-5-6_1
3f9c03757d16        mysql:5.7                       "docker-entrypoint..."   14 minutes ago      Up 14 minutes       3306/tcp, 33060/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    docker_mysql-old_1
76e9cef725cd        openemr/dev-php-fpm:7.0         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-0_1
2f6e268e7411        openemr/openemr:flex-3.9        "./run_openemr.sh"       14 minutes ago      Up 13 minutes       0.0.0.0:8086->80/tcp, 0.0.0.0:9086->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-2-redis_1
e55ea8b540d8        openemr/openemr:flex-edge       "./run_openemr.sh"       14 minutes ago      Up 14 minutes       0.0.0.0:8084->80/tcp, 0.0.0.0:9084->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-edge_1
3a95311da546        openemr/openemr:flex-3.11       "./run_openemr.sh"       14 minutes ago      Up 13 minutes       0.0.0.0:8088->80/tcp, 0.0.0.0:9088->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-3-redis-311_1
8706b8495b41        openemr/dev-php-fpm:7.4-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-4-redis_1
31df6d708395        phpmyadmin/phpmyadmin           "/docker-entrypoin..."   14 minutes ago      Up 14 minutes       0.0.0.0:8200->80/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   docker_phpmyadmin_1
b044c08bf02b        openemr/openemr:flex-3.7        "./run_openemr.sh"       14 minutes ago      Up 14 minutes       0.0.0.0:8085->80/tcp, 0.0.0.0:9085->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            docker_openemr-7-1-redis_1
ba8362e6ecdc        openemr/dev-php-fpm:7.3         "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-3_1
16eebdbfb00f        openemr/dev-php-fpm:7.3-redis   "docker-php-entryp..."   14 minutes ago      Up 14 minutes       9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_dev-php-fpm-7-3-redis_1
24c8a1614707        mysql:5.6                       "docker-entrypoint..."   14 minutes ago      Up 14 minutes       3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               docker_mysql-very-old_1
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
GUI directly via http://localhost:5984/_utils/ or https://localhost:6984/_utils/ with
username `admin` and password `password`. You can configure OpenEMR to use this CouchDB
docker for patient document storage in OpenEMR at Administration->Globals->Documents:
- Document Storage Method->CouchDB
- CouchDB HostName->couchdb
- CouchDB UserName->admin
- CouchDB Password->password
- CouchDB Port->6984
- CouchDB Database can be set to any name you want

##### OpenLDAP
In OpenEMR, LDAP is an option for user authentication. You can configure OpenEMR to use the
OpenLDAP docker patient authentication in OpenEMR at Administration->Globals->Security:
- LDAP - Server Name or URI : ldap://openldap:389
- LDAP - Distinguished Name of User : cn={login},dc=example,dc=org
- LDAP - Login Exclusions : (place whatever your admin login is; warning, do not use "admin" for your openemr admin login)

(note that using 'cn' rather than 'uid' in this case for the distinguished name since the default openldap docker hasn't assigned a uid to the "admin" user)

Then create a user named “admin” in OpenEMR. When you log in as that user, the password is “admin”.

#### Ongoing Development

##### Orthanc
Developers are currently working on integrating the Orthanc PACS server into OpenEMR. This
feature is currently under development. Although it is not yet integrated with OpenEMR yet,
you can connect to the Orthanc application gui via http://localhost:8042/ with username `orthanc`
and password `orthanc`. The nginx docker has also been set up to work as a reverse proxy
with orthanc to allow ongoing development via http://localhost:8090/orthanc/ (Note this reverse
proxy is still a work in progress)

##### FHIR
A FHIR server is included to make it easier to test on a bona fide FHIR server as developers
work towards supporting FHIR via OpenEMR's API. The FHIR server is docker from
https://hub.docker.com/r/ibmcom/ibm-fhir-server and see there for instructions of use.

#### The Insane Docker Development Environment is a work in progress

This is an ongoing work in progress and feel free to join the super exciting
OpenEMR container projects. Feel free to post PR's to update the
docker-compose.yml script or this documentation. Also feel free to post
updates on the openemr/openemr:flex or openemr/openemr:flex-edge dockers
which can be found at
https://github.com/openemr/openemr-devops/tree/master/docker/openemr

#### Stuff that needs fixing
1. The reverse proxy for orthanc
