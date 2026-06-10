[![Syntax Status](https://github.com/openemr/openemr/actions/workflows/syntax.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/syntax.yml)
[![Styling Status](https://github.com/openemr/openemr/actions/workflows/styling.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/styling.yml)
[![Testing Status](https://github.com/openemr/openemr/actions/workflows/test.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/test.yml)
[![JS Unit Testing Status](https://github.com/openemr/openemr/actions/workflows/js-test.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/js-test.yml)
[![PHPStan](https://github.com/openemr/openemr/actions/workflows/phpstan.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/phpstan.yml)
[![Rector](https://github.com/openemr/openemr/actions/workflows/rector.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/rector.yml)
[![ShellCheck](https://github.com/openemr/openemr/actions/workflows/shellcheck.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/shellcheck.yml)
[![Docker Compose Linting](https://github.com/openemr/openemr/actions/workflows/docker-compose-lint.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/docker-compose-lint.yml)
[![Dockerfile Linting](https://github.com/openemr/openemr/actions/workflows/hadolint.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/hadolint.yml)
[![Isolated Tests](https://github.com/openemr/openemr/actions/workflows/isolated-tests.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/isolated-tests.yml)
[![Inferno Certification Test](https://github.com/openemr/openemr/actions/workflows/inferno-test.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/inferno-test.yml)
[![Composer Checks](https://github.com/openemr/openemr/actions/workflows/composer.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/composer.yml)
[![Composer Require Checker](https://github.com/openemr/openemr/actions/workflows/composer-require-checker.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/composer-require-checker.yml)
[![API Docs Freshness Checks](https://github.com/openemr/openemr/actions/workflows/api-docs.yml/badge.svg)](https://github.com/openemr/openemr/actions/workflows/api-docs.yml)
[![codecov](https://codecov.io/gh/openemr/openemr/graph/badge.svg?token=7Eu3U1Ozdq)](https://codecov.io/gh/openemr/openemr)

[![Backers on Open Collective](https://opencollective.com/openemr/backers/badge.svg)](#backers) [![Sponsors on Open Collective](https://opencollective.com/openemr/sponsors/badge.svg)](#sponsors)

# OpenEMR

[OpenEMR](https://open-emr.org) is a Free and Open Source electronic health records and medical practice management application. It features fully integrated electronic health records, practice management, scheduling, electronic billing, internationalization, free support, a vibrant community, and a whole lot more. It runs on Windows, Linux, Mac OS X, and many other platforms.

### Contributing

OpenEMR is a leader in healthcare open source software and comprises a large and diverse community of software developers, medical providers and educators with a very healthy mix of both volunteers and professionals. [Join us and learn how to start contributing today!](https://open-emr.org/wiki/index.php/FAQ#How_do_I_begin_to_volunteer_for_the_OpenEMR_project.3F)

> Already comfortable with git? Check out [CONTRIBUTING.md](CONTRIBUTING.md) for quick setup instructions and requirements for contributing to OpenEMR by resolving a bug or adding an awesome feature 😊.

### Support

Community and Professional support can be found [here](https://open-emr.org/wiki/index.php/OpenEMR_Support_Guide).

Extensive documentation and forums can be found on the [OpenEMR website](https://open-emr.org) that can help you to become more familiar about the project 📖.

### Reporting Issues and Bugs

Report these on the [Issue Tracker](https://github.com/openemr/openemr/issues). If you are unsure if it is an issue/bug, then always feel free to use the [Forum](https://community.open-emr.org/) and [Chat](https://www.open-emr.org/chat/) to discuss about the issue 🪲.

### Reporting Security Vulnerabilities

Check out [SECURITY.md](.github/SECURITY.md)

### API

Check out [API_README.md](API_README.md)

### Docker

Check out [DOCKER_README.md](DOCKER_README.md)

### FHIR

Check out [FHIR_README.md](FHIR_README.md)

### For Developers

If using OpenEMR directly from the code repository, then the following commands will build OpenEMR (Node.js version 24.* is required) :

```shell
composer install --no-dev
npm install
npm run build
composer dump-autoload -o
```

### Running Code Quality and CI Checks Locally

OpenEMR includes Composer and npm scripts that mirror many of the checks run in GitHub Actions CI. Running these tools locally can significantly reduce development turnaround time by providing immediate feedback before opening a pull request.

Examples below assume project dependencies have already been installed via Composer and npm.

#### Run All PHP Code Quality Checks

```bash
composer run code-quality
```

This command runs the following checks, in order:

- Codespell
- PHP syntax checking
- PHP Code Beautifier and Fixer (PHPCBF)
- PHP CodeSniffer (PHPCS)
- PHPStan
- Rector (dry-run analysis)
- Composer Require Checker

> **Note:** `codespell` requires a separate installation and may be skipped if it is not available in your environment.
>
> Install with:
>
> ```bash
> brew install codespell
> ```
>
> or
>
> ```bash
> pipx install codespell
> ```

#### Individual PHP Checks

| Tool | Purpose | Command |
| ---- | ------- | ------- |
| Codespell | Spell checking (requires separate install) | `composer run codespell` |
| PHPStan | Static analysis | `composer run phpstan` |
| PHPCS | Coding standards validation | `composer run phpcs` |
| PHPCBF | Automatically fix coding standards violations | `composer run phpcbf` |
| Rector | Modernization and refactoring analysis | `composer run rector-check` |
| Rector (Fix) | Automatically apply Rector fixes | `composer run rector-fix` |
| PHP Syntax Check | PHP linting | `composer run php-syntax-check` |
| Composer Require Checker | Dependency validation | `composer run require-checker` |
| Conventional Commits | Commit message validation (not included in `code-quality`; run separately before pushing) | `composer run conventional-commits:check` |
| Composer Checks | Composer validation and normalization | `composer run checks` |
| PHPUnit | Run isolated test suite | `composer run phpunit-isolated` |

#### Frontend Checks

```bash
# JavaScript linting
npm run lint:js

# Automatically fix JavaScript issues
npm run lint:js-fix

# CSS/SCSS linting
npm run stylelint

# Automatically fix CSS/SCSS issues
npm run stylelint-fix
```

> **Note:** Frontend tooling requires Node.js 24 or later as specified in `package.json`.

#### IDE Integration

Many of the validation tools used by OpenEMR can be integrated directly into editors and IDEs to provide immediate feedback while developing.

##### Visual Studio Code

Recommended extensions:

| Tool | Extension |
| ---- | --------- |
| PHPStan | https://marketplace.visualstudio.com/items?itemName=SanderRonde.phpstan-vscode |
| PHP_CodeSniffer (PHPCS) | https://marketplace.visualstudio.com/items?itemName=johnrdorazio.vscode-phpcs |
| ESLint | https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint |
| Stylelint | https://marketplace.visualstudio.com/items?itemName=stylelint.vscode-stylelint |

##### PhpStorm / JetBrains IDEs

JetBrains IDEs provide native support for many of these tools.

**PHPStan / PHPCS**

```text
Preferences → Languages & Frameworks → PHP → Quality Tools
```

**ESLint / Stylelint**

```text
Preferences → Languages & Frameworks → JavaScript → Code Quality Tools
```

#### Configuration Files

The following configuration files may be useful when configuring IDE integrations or AI-assisted development tools:

| Tool | Configuration File |
| ---- | ------------------ |
| PHPStan | `phpstan.neon.dist` |
| Rector | `rector.php` |
| PHP_CodeSniffer | `phpcs.xml.dist` |
| Composer Require Checker | `.composer-require-checker.json` |

### Contributors

This project exists thanks to all the people who have contributed. [[Contribute]](CONTRIBUTING.md).
<a href="https://github.com/openemr/openemr/graphs/contributors"><img src="https://opencollective.com/openemr/contributors.svg?width=890" /></a>


### Sponsors

Thanks to our [ONC Certification Major Sponsors](https://www.open-emr.org/wiki/index.php/OpenEMR_Certification_Stage_III_Meaningful_Use#Major_sponsors)!


### License

[GNU GPL](LICENSE)
