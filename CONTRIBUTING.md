Thank you for your contribution. OpenEMR (and global healthcare) continues to get better because of people like you!

The maintainers of OpenEMR want to get your pull request in as seamlessly as possible, so please ensure your code is consistent with our [development policies](http://open-emr.org/wiki/index.php/Development_Policies).

## Code Contributions (local development)
You will need a "local" version of OpenEMR to make changes to the source code. The easiest way to do this is with [Docker](https://hub.docker.com/r/openemr/openemr/):

1. [Create your own fork of OpenEMR](https://github.com/openemr/openemr/fork) (you will need a GitHub account) and `git clone` it to your local machine.
    - It's best to also add an `upstream` origin to keep your local fork up to date. [Check out this guide](https://oneemptymind.wordpress.com/2018/07/11/keeping-a-fork-up-to-date/) for more info.
2. `cd openemr` (the directory you cloned the code into)
    - If you haven't already, [install Docker](https://docs.docker.com/install/) for your system
3. Edit `docker-compose.yml` to use your fork's git url and branch name for `FLEX_REPOSITORY` and `FLEX_REPOSITORY_BRANCH`
    - For example:
    ```
    FLEX_REPOSITORY: https://github.com/[my_user_name]/openemr.git
    FLEX_REPOSITORY_BRANCH: master
    ```
4. Run `docker-compose up` from your command line
    - When the build is done, you'll see the following message:
    ```
    openemr_1  | Love OpenEMR? You can now support the project via the open collective:
    openemr_1  |  > https://opencollective.com/openemr/donate
    openemr_1  |
    openemr_1  | Starting cron daemon!
    openemr_1  | Starting apache!
    ```
5. Navigate to `http://localhost:8300/` to login as `admin`. Password is `pass`.
6. Make changes to any files on your local file system. Most changes will appear after a refresh of the page or iFrame you're working on...
    - Note that changes to SCSS files will not automatically show unless the node build process is running. For more info on this, see the [README.md in /interface](interface/README.md)
7. If you wish to connect to the sql database, this docker environment provides the following 2 options:
    - Navigate to `http://localhost:8310/` where you can login into phpMyAdmin.
    - Or you can directly connect to port 8320 via your favorite sql tool (Mysql Workbench etc.).
8. When you're done, it's best to clean up after yourself with `docker-compose down -v`
    - If you don't want to build from scratch every time, just use `docker-compose down` so your next `docker-compose up` will use the cached volumes.
9. [Submit a PR](https://github.com/openemr/openemr/compare) from your fork into `openemr/openemr#master`!

We look forward to your contribution...

If you do not want to use Docker, you can always install OpenEMR directly on your local environment. This will require installing additional dependencies for your operating system. For more info see [OpenEMR Development Versions](https://open-emr.org/wiki/index.php/OpenEMR_Installation_Guides#OpenEMR_Development_Versions) on the wiki.

## Financial contributions

We also welcome financial contributions in full transparency on our [open collective](https://opencollective.com/openemr).
Anyone can file an expense. If the expense makes sense for the development of the community, it will be "merged" in the ledger of our open collective by the core contributors and the person who filed the expense will be reimbursed.


## Credits


### Contributors

Thank you to all the people who have already contributed to openemr!
<a href="graphs/contributors"><img src="https://opencollective.com/openemr/contributors.svg?width=890" /></a>


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
