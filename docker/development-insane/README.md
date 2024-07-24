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
- http://localhost:8082 (with Alpine 3.15 (Apache and PHP 8.0))
- http://localhost:8083 (with Alpine 3.17 (Apache and PHP 8.1))
- http://localhost:8084 (with Alpine 3.18 (Apache and PHP 8.2))
- http://localhost:8085 (with Alpine 3.19 (Apache and PHP 8.3))
- http://localhost:8086 (with Alpine Edge (Apache and now PHP 8.3))
- http://localhost:8092 (with Alpine 3.15 (Apache and PHP 8.0) with redis)
- http://localhost:8093 (with Alpine 3.17 (Apache and PHP 8.1) with redis)
- http://localhost:8094 (with Alpine 3.18 (Apache and PHP 8.2) with redis)
- http://localhost:8095 (with Alpine 3.19 (Apache and PHP 8.3) with redis)
- http://localhost:8096 (with Alpine Edge (Apache and now PHP 8.3) with redis)
- http://localhost:8102 (with Nginx and PHP-FPM 8.0)
- http://localhost:8103 (with Nginx and PHP-FPM 8.1)
- http://localhost:8104 (with Nginx and PHP-FPM 8.2)
- http://localhost:8105 (with Nginx and PHP-FPM 8.3)
- http://localhost:8106 (with Nginx and PHP-FPM 8.4)
- http://localhost:8152 (with Nginx and PHP-FPM 8.0 with redis)
- http://localhost:8153 (with Nginx and PHP-FPM 8.1 with redis)
- http://localhost:8154 (with Nginx and PHP-FPM 8.2 with redis)
- http://localhost:8155 (with Nginx and PHP-FPM 8.3 with redis)
- http://localhost:8156 (with Nginx and PHP-FPM 8.4 with redis)
- https://localhost:9082 with SSL and Alpine 3.15 (with Apache and PHP 8.0)
- https://localhost:9083 with SSL and Alpine 3.17 (with Apache and PHP 8.1)
- https://localhost:9084 with SSL and Alpine 3.18 (with Apache and PHP 8.2)
- https://localhost:9085 with SSL and Alpine 3.19 (with Apache and PHP 8.3)
- https://localhost:9086 with SSL and Alpine Edge (with Apache and now PHP 8.3)
- https://localhost:9092 with SSL and Alpine 3.15 (with Apache and PHP 8.0 with redis)
- https://localhost:9093 with SSL and Alpine 3.17 (with Apache and PHP 8.1 with redis)
- https://localhost:9094 with SSL and Alpine 3.18 (with Apache and PHP 8.2 with redis)
- https://localhost:9095 with SSL and Alpine 3.19 (with Apache and PHP 8.3 with redis)
- https://localhost:9096 with SSL and Alpine Edge (with Apache and now PHP 8.3 with redis)
- https://localhost:9102 with SSL (with Nginx and PHP-FPM 8.0)
- https://localhost:9103 with SSL (with Nginx and PHP-FPM 8.1)
- https://localhost:9104 with SSL (with Nginx and PHP-FPM 8.2)
- https://localhost:9105 with SSL (with Nginx and PHP-FPM 8.3)
- https://localhost:9106 with SSL (with Nginx and PHP-FPM 8.4)
- https://localhost:9152 with SSL (with Nginx and PHP-FPM 8.0 with redis)
- https://localhost:9153 with SSL (with Nginx and PHP-FPM 8.1 with redis)
- https://localhost:9154 with SSL (with Nginx and PHP-FPM 8.2 with redis)
- https://localhost:9155 with SSL (with Nginx and PHP-FPM 8.3 with redis)
- https://localhost:9156 with SSL (with Nginx and PHP-FPM 8.3 with redis)

**Step 4.** Setup up OpenEMR. The first time you run OpenEMR (and whenever you clear and replace your
synchronized openemr directory and restart the development docker). On the main
setup input screen:
 - for `Server Host`, use either `mariadb` or `mariadb-ssl` or `mysql` or `mariadb-old` or `mariadb-very-old` or
   `mariadb-very-very-old` or `mysql-old` (you have all
   mariadb/mysql/mariadb-\*/mysql-\* dockers ready to go to make testing either one easy;
   `mysql` is version 8.4; `mysql-old` is version 8.0; `mysql-old-old` is version 5.7;
   `mariadb` is version 11.4; `mariadb-ssl` is version 11.4 with support for ssl; `mariadb-old` is version 10.11; `mariadb-very-old` is
   version 10.6; `mariadb-very-very-old` is version 10.5)
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
docker pull openemr/openemr:flex-3.20
docker pull openemr/openemr:flex-3.18
docker pull openemr/openemr:flex-3.17
docker pull openemr/openemr:flex-3.15-8
docker pull openemr/dev-php-fpm:8.4
docker pull openemr/dev-php-fpm:8.3
docker pull openemr/dev-php-fpm:8.2
docker pull openemr/dev-php-fpm:8.1
docker pull openemr/dev-php-fpm:8.0
docker pull openemr/dev-php-fpm:8.4-redis
docker pull openemr/dev-php-fpm:8.3-redis
docker pull openemr/dev-php-fpm:8.2-redis
docker pull openemr/dev-php-fpm:8.1-redis
docker pull openemr/dev-php-fpm:8.0-redis
docker pull openemr/dev-nginx
docker pull mariadb:11.4
docker pull mariadb:10.11
docker pull mariadb:10.6
docker pull mariadb:10.5
docker pull mysql:8.4
docker pull mysql:8.0
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
CONTAINER ID   IMAGE                           COMMAND                  CREATED         STATUS         PORTS                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    NAMES
8bcb524f484a   openemr/dev-nginx               "/docker-entrypoint.…"   3 minutes ago   Up 3 minutes   80/tcp, 0.0.0.0:8102->82/tcp, :::8102->82/tcp, 0.0.0.0:8103->83/tcp, :::8103->83/tcp, 0.0.0.0:8104->84/tcp, :::8104->84/tcp, 0.0.0.0:8105->85/tcp, :::8105->85/tcp, 0.0.0.0:8106->86/tcp, :::8106->86/tcp, 0.0.0.0:8152->92/tcp, :::8152->92/tcp, 0.0.0.0:8153->93/tcp, :::8153->93/tcp, 0.0.0.0:8154->94/tcp, :::8154->94/tcp, 0.0.0.0:8155->95/tcp, :::8155->95/tcp, 0.0.0.0:8156->96/tcp, :::8156->96/tcp, 0.0.0.0:9102->442/tcp, :::9102->442/tcp, 0.0.0.0:9103->443/tcp, :::9103->443/tcp, 0.0.0.0:9104->444/tcp, :::9104->444/tcp, 0.0.0.0:9105->445/tcp, :::9105->445/tcp, 0.0.0.0:9106->446/tcp, :::9106->446/tcp, 0.0.0.0:9152->452/tcp, :::9152->452/tcp, 0.0.0.0:9153->453/tcp, :::9153->453/tcp, 0.0.0.0:9154->454/tcp, :::9154->454/tcp, 0.0.0.0:9155->455/tcp, :::9155->455/tcp, 0.0.0.0:9156->456/tcp, :::9156->456/tcp   development-insane_nginx_1
1c834d588a8f   mariadb:10.11                   "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_mariadb_1
de9c3fcd2e27   ibmcom/ibm-fhir-server          "/opt/ibm-fhir-serve…"   3 minutes ago   Up 3 minutes   9080/tcp, 0.0.0.0:9443->9443/tcp, :::9443->9443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      development-insane_fhir_1
f6367de7091b   openemr/dev-php-fpm:8.1-redis   "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-1-redis_1
28d0490d09c8   openemr/dev-php-fpm:8.1         "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-1_1
54a2d8335204   jodogne/orthanc-plugins         "Orthanc /etc/orthan…"   3 minutes ago   Up 3 minutes   0.0.0.0:4242->4242/tcp, :::4242->4242/tcp, 0.0.0.0:8042->8042/tcp, :::8042->8042/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     development-insane_orthanc_1
09d38061a11f   openemr/dev-php-fpm:8.2-redis   "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-2-redis_1
5a5947acb8cc   mariadb:10.11                   "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_mariadb-ssl_1
7daaed1175a2   openemr/dev-ldap:insane         "/container/tool/run"    3 minutes ago   Up 3 minutes   389/tcp, 636/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         development-insane_openldap_1
1138fda82d9b   openemr/openemr:flex-3.19       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8095->80/tcp, :::8095->80/tcp, 0.0.0.0:9095->443/tcp, :::9095->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-3-redis_1
85c1540401d8   phpmyadmin                      "/docker-entrypoint.…"   3 minutes ago   Up 3 minutes   0.0.0.0:8200->80/tcp, :::8200->80/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    development-insane_phpmyadmin_1
5c6dcc59a84b   openemr/openemr:flex-3.17       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8083->80/tcp, :::8083->80/tcp, 0.0.0.0:9083->443/tcp, :::9083->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-1_1
0d4b88f04817   openemr/openemr:flex-3.15-8     "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8082->80/tcp, :::8082->80/tcp, 0.0.0.0:9082->443/tcp, :::9082->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-0_1
ab8ece240f93   openemr/openemr:flex-3.17       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8093->80/tcp, :::8093->80/tcp, 0.0.0.0:9093->443/tcp, :::9093->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-1-redis_1
abb2140d7f84   couchdb                         "tini -- /docker-ent…"   3 minutes ago   Up 3 minutes   0.0.0.0:5984->5984/tcp, :::5984->5984/tcp, 4369/tcp, 9100/tcp, 0.0.0.0:6984->6984/tcp, :::6984->6984/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_couchdb_1
1752a74a766e   mariadb:10.5                    "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_mariadb-very-old_1
d48be0653cf5   openemr/dev-php-fpm:8.0-redis   "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-0-redis_1
8c9296c9f384   openemr/dev-php-fpm:8.2         "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-2_1
f87791947a38   openemr/dev-php-fpm:8.0         "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-0_1
7ab25e9a232e   openemr/openemr:flex-3.18       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8094->80/tcp, :::8094->80/tcp, 0.0.0.0:9094->443/tcp, :::9094->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-2-redis_1
858404745dc4   mysql:8.0                       "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   33060/tcp, 0.0.0.0:8220->3306/tcp, :::8220->3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     development-insane_mysql_1
911d79119271   openemr/openemr:flex-3.19       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8085->80/tcp, :::8085->80/tcp, 0.0.0.0:9085->443/tcp, :::9085->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-3_1
84ac5d16b2c1   openemr/openemr:flex-edge       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8086->80/tcp, :::8086->80/tcp, 0.0.0.0:9086->443/tcp, :::9086->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-edge_1
0263a171f6d3   redis                           "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   6379/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_redis_1
63c8b4ee029d   openemr/dev-php-fpm:8.3         "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-3_1
d138ccc08523   openemr/dev-php-fpm:8.4         "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-4_1
73f1d940b1ee   openemr/openemr:flex-3.18       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8084->80/tcp, :::8084->80/tcp, 0.0.0.0:9084->443/tcp, :::9084->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-2_1
f1db0bb99cf9   mariadb:10.4                    "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_mariadb-very-very-old_1
c3bbab538f0a   openemr/openemr:flex-3.15-8     "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8092->80/tcp, :::8092->80/tcp, 0.0.0.0:9092->443/tcp, :::9092->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-8-0-redis_1
23118397b6a3   openemr/dev-php-fpm:8.4-redis   "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-4-redis_1
3895440b61ca   openemr/openemr:flex-edge       "./openemr.sh"           3 minutes ago   Up 3 minutes   0.0.0.0:8096->80/tcp, :::8096->80/tcp, 0.0.0.0:9096->443/tcp, :::9096->443/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           development-insane_openemr-edge-redis_1
ec002a7a520b   mariadb:10.6                    "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   0.0.0.0:8210->3306/tcp, :::8210->3306/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                development-insane_mariadb-old_1
e0190a994782   openemr/dev-php-fpm:8.3-redis   "docker-php-entrypoi…"   3 minutes ago   Up 3 minutes   9000/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 development-insane_dev-php-fpm-8-3-redis_1
408391ca6398   mysql:5.7                       "docker-entrypoint.s…"   3 minutes ago   Up 3 minutes   3306/tcp, 33060/tcp                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      development-insane_mysql-old_1                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      docker_mysql-very-old_1
```
 - Note the `NAMES` column is extremely important and how you run docker commands
on specific containers. For example, to go into a shell script in the
`development-insane_openemr-8-3_1` container, would use:
```bash
docker exec -it development-insane_openemr-8-3_1 bash
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
