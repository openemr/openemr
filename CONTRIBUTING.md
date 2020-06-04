Thank you for your contribution. OpenEMR (and global healthcare) continues to get better because of people like you!

The maintainers of OpenEMR want to get your pull request in as seamlessly as possible, so please ensure your code is consistent with our [development policies](https://open-emr.org/wiki/index.php/Development_Policies).

## Code Contributions (local development)

You will need a "local" version of OpenEMR to make changes to the source code. The easiest way to do this is with [Docker](https://hub.docker.com/r/openemr/openemr/):

1. [Create your own fork of OpenEMR](https://github.com/openemr/openemr/fork) (you will need a GitHub account) and `git clone` it to your local machine.
    - It's best to also add an `upstream` origin to keep your local fork up to date. [Check out this guide](https://oneemptymind.wordpress.com/2018/07/11/keeping-a-fork-up-to-date/) for more info.
2. `cd openemr` (the directory you cloned the code into)
    - If you haven't already, [install Docker](https://docs.docker.com/install/) for your system
3. Run `docker-compose up` from your command line
    - When the build is done, you'll see the following message:
    ```sh
    openemr_1  | Love OpenEMR? You can now support the project via the open collective:
    openemr_1  |  > https://opencollective.com/openemr/donate
    openemr_1  |
    openemr_1  | Starting cron daemon!
    openemr_1  | Starting apache!
    ```
4. Navigate to `http://localhost:8300/` to login as `admin`. Password is `pass`.
5. Make changes to any files on your local file system. Most changes will appear after a refresh of the page or iFrame you're working on.
    - An exception to this is if making changes to styling scripts in interface/themes/. In that case will need to clear web browser cache and run the following command to rebuild the theme files:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools build-themes'
      ```
6. If you wish to connect to the sql database, this docker environment provides the following 2 options:
    - Navigate to `http://localhost:8310/` where you can login into phpMyAdmin.
    - Or you can directly connect to port 8320 via your favorite sql tool (Mysql Workbench etc.).
    - Use `username/user`: openemr, `password`: openemr .
7. Developer tools and tricks.
    - To check PHP error logs:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools php-log'
      ```
    - To create a report of PSR12 code styling issues (this takes several minutes):
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools psr12-report'
      ```
    - To fix PSR12 code styling issues (this takes several minutes):
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools psr12-fix'
      ```
    - To create a report of theme styling issues:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools lint-themes-report'
      ```
    - To fix theme styling issues:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools lint-themes-fix'
      ```
    - To check PHP parsing errors (this takes several minutes):
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools php-parserror'
      ```
    - To run unit testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools unit-test'
      ```
    - To run api testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools api-test'
      ```
    - To run e2e testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools e2e-test'
      ```
    - To run services testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools services-test'
      ```
    - To run fixtures testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools fixtures-test'
      ```
    - To run validators testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools validators-test'
      ```
    - To run controllers testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools controllers-test'
      ```
    - To run common testing:
      ```sh
      docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools common-test'
      ```
8. To run the entire dev tool suite (PSR12 fix, lint themes fix, PHP parse error, unit/API/e2e/services/fixtures/validators/controllers/common tests) in one command, run
    ```sh
    docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools clean-sweep'
    ```
9. To run only all the automated tests (unit/API/e2e/services/fixtures/validators/controllers/common tests) in one command, run
    ```sh
    docker exec -i $(docker ps | grep _openemr | cut -f 1 -d " ") sh -c '/root/devtools clean-sweep-tests'
    ```
10. Xdebug and profiling is also supported for PHPStorm.
    - Firefox install xdebug helper add on (configure for PHPSTORM)
    - PHPStorm Settings->Language & Frameworks->PHP->Debug
        - Start listening
        - Untoggle "Break at first line in PHP scripts"
        - Untoggle both settings that start with "Force Break at first line..."
     - Make sure port 9000 is open on your host operating system
     - Profiling output can be found in /tmp directory in the docker
11. When you're done, it's best to clean up after yourself with `docker-compose down -v`
    - If you don't want to build from scratch every time, just use `docker-compose down` so your next `docker-compose up` will use the cached volumes.
12. [Submit a PR](https://github.com/openemr/openemr/compare) from your fork into `openemr/openemr#master`!

We look forward to your contribution...

If you do not want to use Docker, you can always install OpenEMR directly on your local environment. This will require installing additional dependencies for your operating system. For more info see [OpenEMR Development Versions](https://open-emr.org/wiki/index.php/OpenEMR_Installation_Guides#OpenEMR_Development_Versions) on the wiki.

## Financial contributions

We also welcome financial contributions in full transparency on our [open collective](https://opencollective.com/openemr).
Anyone can file an expense. If the expense makes sense for the development of the community, it will be "merged" in the ledger of our open collective by the core contributors and the person who filed the expense will be reimbursed.

## Credits

### Contributors

Thank you to all the people who have already contributed to openemr!
<a href="https://github.com/openemr/openemr/graphs/contributors"><img src="https://opencollective.com/openemr/contributors.svg?width=890" /></a>

### Backers

Thank you to all our backers! [[Become a backer](https://opencollective.com/openemr#backer)]

<a href="https://opencollective.com/openemr#backers" target="_blank"><img src="https://opencollective.com/openemr/backers.svg?width=890"></a>

### Sponsors

Thank you to all our sponsors! (please ask your company to also support this open source project by [becoming a sponsor](https://opencollective.com/openemr#sponsor))

<a href="https://opencollective.com/openemr/sponsor/0/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/0/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/1/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/1/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/2/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/2/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/3/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/3/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/4/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/4/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/5/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/5/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/6/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/6/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/7/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/7/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/8/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/8/avatar.svg"></a>
<a href="https://opencollective.com/openemr/sponsor/9/website" target="_blank"><img src="https://opencollective.com/openemr/sponsor/9/avatar.svg"></a>
