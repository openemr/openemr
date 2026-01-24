# OpenEMR Development Rules

## Exception Handling
When throwing exceptions in a module, use custom exception types specific to that module instead of generic Exception. This helps scope exceptions to identify the source and nature of errors more clearly.

## HTTP Clients
In the interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php file, specifically in the downloadAndStoreFaxMedia function, use oeHttpRequest or the Guzzle Http client for HTTP requests instead of curl. Using multiple different HTTP clients can lead to trouble and security vulnerabilities.

## Data Privacy
No patient demographics or identifiable patient information are to be included in any data sent to ChatGPT to ensure data is deidentified.

## PHP Enums
When you have a related set of class constants that represent a fixed set of values (especially numeric or string constants that work together), refactor them into a PHP enum instead. This applies to PHP 8.1+ codebases.

For example:
Instead of:
```php
public const DATE_MODE_PAYMENT = 0;
public const DATE_MODE_SERVICE = 1;
public const DATE_MODE_ENTRY = 2;
```

Create an enum:
```php
namespace OpenEMR\Reports\CashReceipts\Enums;

enum DateMode: int
{
    case PAYMENT = 0;
    case SERVICE = 1;
    case ENTRY = 2;
}
```

Then import and use it:
```php
use OpenEMR\Reports\CashReceipts\Enums\DateMode;

// Usage in switch statements:
case DateMode::PAYMENT->value:
```

Benefits:
- Provides compile-time type safety and validation
- Better IDE support and autocomplete
- Makes the relationship between values explicit
- Improves code organization and readability
- Prevents invalid values from being passed

## Database Schema
There are two places to check table schema in OpenEMR. One is Documentation/EHI_Export/docs/tables folder there are html files of every table that is native to openemr. There is also the sql folder that contains the database.sql which has all of the native tables. If you are given a local database in the instructions given, use that local database to check the table schema so that you will know what columns exist and don't write bad queries with columns which don't exist.

## First-Class Callables
When creating callbacks that simply pass arguments directly to another function without modification, use PHP's first-class callable syntax (...) instead of anonymous closures. This applies to PHP 8.1+ codebases.

For example:
Instead of:
```php
$callback = function ($code) {
    return is_clinic($code);
};
```

Use:
```php
$callback = is_clinic(...);
```

This improves code readability, reduces boilerplate, and aligns with modern PHP standards. The first-class callable syntax is more concise and performs better than closures for simple function references.

## Themes
The OpenEMR UI is governed in part by themes that are located in the interface/themes folder. You need to figure out if the theme is being applied to the code UI when building twig components and such like. Always take into account the themes before building new CSS. If the theme can be used do so if not override the theme with new CSS or SCSS.

## Method Verification
Before referencing a method or function in a script, check that the method exists. If the method does not exist, don't use it.

## ACL and Session Management
If not using a browser, skip the ACL or use a browser session to have the site id and other session variables needed. When need to execute a script and you want to include the globals.php script, use `$ignoreAuth=true` like in the login/login.php.

## OpenEMR Architecture Overview
OpenEMR has no one framework.
The program has Laminas framework which is used to manage the modules.
The program is migrating toward twig for the front end view.
There is a lot of legacy code in the program that is over 10 years old.
The /src directory is where all of the new classes are being built.
The system uses symfony components to build the dispatch system for decoupling and application communication.
The /src/Common is where the core components of the program live.
The local databases are not connected to the repo code.
There is only one database that is connected to a local codebase which is openemr703. The localhost/openemr703 code is located in the /var/www/html/openemr703.

To access the local mysql/mariadb database use: `mysql -u local_openemr -p 5qy3xkMjP4A2US1u7Qv`

Prompt me as to which database to use during sprint to build a module or feature.

The /Documentation/EHI_Export/docs/tables contain the *.html file for every native table in the program.

When writing code it must meet the PSR (PHP Standards Recommendations). Always write code to this standard.

## Twig Templates
When building twig templates, you cannot use php in twig templates.
Use the $GLOBALS['webroot']/templates folder to learn how to build twig templates in OpenEMR.
Twig templates use OpenEMR header `{{ setupHeader() }}` to bring in standardized header system. Example: `{{ setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']) }}`

The header is built in the $GLOBALS['webroot']/src/Core/Header.php class and is pushed into the TwigContainer through the $GLOBALS['webroot']/src/Common/Twig/TwigExtension.php line 91.

Always escape text in twig and translate like this: `{{ 'Text Here' | xlt }}`

Review the twig templates in the {webroot}/templates folder to learn how twig templates are constructed in the program. Always visit {webroot}/src/Common/Twig/TwigExtension.php to know which twig extensions are active and not guess.

When creating a new twig in a new folder model it after:
- {webroot}/templates/portal/base.html.twig
- Add {webroot}/templates/portal/header.html.twig

This should make the foundation for the twig build.

Always double check twig syntax and that all `{% %}` have a closing tag.

Always check the other templates for the CSRF proper use.

When adding a new twig template in modules there should be a base.twig, always extend the base.twig to keep the templates uniform.

In the class tag the datepicker has to come before form-control. Review the datepicker structure by finding a template with a datepicker entry and follow that pattern.

## Database Access
Don't use PDO unless the globals.php is not accessible or unable to extend namespace. Database connections are maintained in the globals.php or webroot/src/Database/QueryUtils.php. The common class used throughout the database is `sqlStatement()` and `sqlQuery()`. The difference between the two is that the sqlQuery is for a single item to be returned from the database. The sqlStatement has to be coupled with the sqlFetchArray and a while loop.

When using the globals.php without logging into the interface, the variable `$ignoreAuth` has to be set to true. If sessions are needed from the command line add the variable `$sessionAllowWrite = true` and set the session variable. The site id has to be set. `$_SESSION['site_id'] = 'default'` works the majority of the time.

### Modern QueryUtils Usage
When working with database queries in OpenEMR, always use the QueryUtils class methods instead of deprecated legacy functions:
- Use `QueryUtils::querySingleRow()` instead of `sqlQuery()`
- Use `QueryUtils::fetchRecords()` instead of `sqlStatement()` + `sqlFetchArray()` loops
- Use `QueryUtils::sqlStatementThrowException()` for queries that should throw exceptions on error
- Use `QueryUtils::fetchArrayFromResultSet()` if you need to process result sets manually

Import with: `use OpenEMR\Common\Database\QueryUtils;`

The legacy functions (sqlQuery, sqlStatement, sqlFetchArray) are deprecated and will cause PHPStan errors in CI builds.

## Background Services
Background services are executed by the OpenEMR core program. Inserting into the background_service table will execute the function call which is in a php script.

Example:
```sql
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('MedEx', 'MedEx Messaging Service', 0, 0, '2017-05-09 21:39:10', 0, 'start_MedEx', '/library/MedEx/MedEx_background.php', 100);
```

Find this script in the openemr root library folder. When building a module, put the background service script in a script folder inside the module root directory.

This will show you how to leverage the background services in place of doing cronjob when applicable.

## Report Structure
The email_queue_report.php should be used as the front controller. Services related to this report should be stored in src/Reports/Email, and the Twig templates for the report should be stored in template/reports/email.

## Report Conversion (MVC Architecture)
When converting legacy reports, refactor the code to follow the MVC (Model-View-Controller) architecture. Separate data access and business logic into models, handle user input and control flow in controllers, and use views (such as Twig templates) for presentation. This improves maintainability, testability, and aligns with modern OpenEMR development practices.

The front controller will remain in the {webroot}/interface/reports

The controllers will go in the {webroot}/src/Reports/{Report Name Camel Case}

Twig goes in the {webroot}/templates/reports/{report_name_snake_case}

If there is a CSV component to the report, convert the output to:

```php
use League\Csv\Writer;

$csv = Writer::createFromString('');
$csv->setOutputBOM(Writer::BOM_UTF8);
$csv->insertOne(['Procedure Code', 'Units', ...]);
foreach ($procedureCodes as $code) {
    $csv->insertOne([...]);
}
echo $csv->getContent();
```

## Security Best Practices
When building or modifying code, always follow secure coding practices to avoid introducing security vulnerabilities. This includes avoiding use of multiple HTTP clients unnecessarily, encrypting sensitive data such as API keys and passwords, validating inputs, avoiding deprecated or insecure functions, and reviewing code for potential security risks before submission.

When storing API keys or secrets or passwords in a database table, always encrypt the data. Never store such information as clear text.

## Modern JavaScript
Use `const` or `let` instead of `var` unless the variable has a global scope.
Example: `const currentUrl = tabData.url?.() ?? '';`

Code in a more contemporary ECMAScript 2015+ (ES6+) syntax.

### Array Literals and Iteration
Use `Array.prototype.some()` and `String.prototype.includes()` for cleaner, more readable code.

Instead of:
```javascript
currentUrl.indexOf('/interface/eRx.php') !== -1
```

Use:
```javascript
currentUrl.includes(marker)
```

## Modern PHP Syntax
When working with older sections of the code, scan for `array()` and see if you can convert it to `[]` modern syntax without a lot of effort or breaking the code.

## Configuration Classes
When you have hardcoded configuration values (colors, constants, settings) that are used across multiple methods or classes, extract them into a dedicated configuration class instead of keeping them as private constants.

For example:
Instead of:
```php
class ChartDataService
{
    private const COLORS = [
        '#E69F00', // Orange
        '#56B4E9', // Sky Blue
        ...
    ];
    
    public function buildChart()
    {
        $color = self::COLORS[0];
    }
}
```

Create a config class:
```php
class ChartColorConfig
{
    private static array $colors = [
        '#E69F00', // Orange
        '#56B4E9', // Sky Blue
        ...
    ];
    
    public static function getColor(int $index): string
    {
        return self::$colors[$index % count(self::$colors)];
    }
    
    public static function setColors(array $colors): void
    {
        self::$colors = $colors;
    }
}
```

Benefits:
- Centralizes configuration and makes it reusable across services
- Allows runtime customization and theming without code changes
- Improves maintainability and reduces duplication
- Follows the single responsibility principle
- Makes the code more testable and extensible

## Multi-Site Support
When implementing multi-site features, always use the variable `$GLOBALS['OE_SITES_DIR']` to reference the path for site directories.

Also use `$GLOBALS['OE_SITE_DIR']` to reference the path for site directories.

## Session Variables
The session variable site_id should never be set like this:
```php
$siteId = $_SESSION['site_id'] ?? 'default';
```

Never use the null coalescing operator for site_id.

Never use fallback values for `$_SESSION` variables; always ensure the session variables are explicitly set and accessed without default fallbacks.

## Site ID Usage
Site ID is important if the table will be accessed by multiple different sites. In the case of most modules, the site_id is only important to inbound requests from the outside of the OpenEMR ecosystem. If the workflow is only being accessed by the internal functions and no outside data is being introduced, any table data being saved does not have to be correlated to a site id. Ask if unsure when to include site id in table data.

## Globals Access
**IMPORTANT:** In OpenEMR code, NEVER use direct access to the `$GLOBALS` array when building new code or refactoring existing code. Always use the `OEGlobalsBag` singleton instead.

### Using OEGlobalsBag
The `OEGlobalsBag` class (located at `src/Core/OEGlobalsBag.php`) provides a modern, type-safe wrapper around the global state. It extends Symfony's `ParameterBag` and maintains backwards compatibility while providing a cleaner API.

**Reading values:**
```php
use OpenEMR\Core\OEGlobalsBag;

// Get a value
$webroot = OEGlobalsBag::getInstance()->get('webroot');
$kernel = OEGlobalsBag::getInstance()->get('kernel');

// Get with default fallback
$value = OEGlobalsBag::getInstance()->get('some_key', 'default_value');

// Check if key exists
if (OEGlobalsBag::getInstance()->has('webroot')) {
    // ...
}
```

**Setting values:**
```php
use OpenEMR\Core\OEGlobalsBag;

// Set a value (automatically syncs to $GLOBALS for backwards compatibility)
OEGlobalsBag::getInstance()->set('my_key', 'my_value');
```

**Migration Rules:**
- When writing new code, ALWAYS use `OEGlobalsBag::getInstance()` instead of `$GLOBALS`
- When refactoring existing code, replace direct `$GLOBALS` access with `OEGlobalsBag`
- This applies to all commonly accessed globals like: `webroot`, `kernel`, `OE_SITE_DIR`, `OE_SITES_DIR`, etc.
- Exception: Very low-level bootstrap code that runs before `OEGlobalsBag` is initialized may still use `$GLOBALS` directly

**Benefits:**
- Type-safe access to global state
- Better IDE support and autocomplete
- Prevents undefined index errors
- Maintains backwards compatibility during migration
- Aligns with modern PHP and Symfony best practices
- Facilitates future refactoring away from global state

## Fax/SMS Preferences
User prefers to use the SignalWire SDK for sending faxes and explicitly does not want to use Twilio for any part of the fax sending process.

## SQL Versioning
When adding SQL changes or adding new tables to an existing module, if an sql folder does not exist, create it. If one exists add to it a file with the next version number from the version number that is in the version.php file. Like `3_0_0-to3_1_0_upgrade.sql`. Use the `interface/modules/custom_modules/oe-module-faxsms/sql/3_0_0-to3_1_0_upgrade.sql` as an example.

## Coding Standards
Always build to PSR-12 Extended Coding Style. Use https://php-dictionary.readthedocs.io as a reference manual to build code that is meeting the current coding standards.

### Automated Code Quality Tools Workflow
Before submitting branches, always run the following tools in order:

1. **Rector** - Automatically refactors and modernizes code:
   ```bash
   ./vendor/bin/rector process
   ```
   - Applies PHP 8.2+ features (constructor promotion, readonly properties)
   - Modernizes legacy syntax
   - Configuration: `rector.php` in project root

2. **PHPStan** - Static analysis for type safety and code quality:
   ```bash
   ./vendor/bin/phpstan analyse --memory-limit=1G
   ```
   - Checks for type errors and deprecated function usage
   - Enforces modern database query methods (QueryUtils)
   - Configuration: `phpstan.neon.dist` in project root
   - Must have zero errors in your changed code before committing

3. **Review and commit** - After both tools pass:
   - Review the changes made by Rector
   - Ensure PHPStan reports no errors in your code
   - Stage and commit all changes together

This workflow ensures code quality and consistency across branches before integration.

### PHPStan Error Prevention
When building or migrating code, always refer to the PHPStan migration documentation and check the code to ensure it avoids PHPStan errors. This helps maintain code quality and compatibility with static analysis tools.

Common PHPStan errors to avoid:
- Using deprecated `sqlQuery()` - use `QueryUtils::querySingleRow()` instead
- Using deprecated `sqlStatement()` - use `QueryUtils::fetchRecords()` instead
- Missing type hints on method parameters and return types
- Undefined functions or methods
- Missing imports for used classes

## Unit Testing
Do not use individual @covers annotations on test methods. They restrict code coverage reporting to only the explicitly listed methods, which can hide unintended side effects and mask incomplete test coverage.

Example of what to avoid:
```php
/**
 * @covers ::getInvoiceAmount
 */
public function testGetInvoiceAmountReturnsFloat(): void
{
    $result = $this->repository->getInvoiceAmount(1, 1, 'CPT4', '99213');
    $this->assertIsFloat($result);
}
```

Instead, use descriptive comments:
```php
/**
 * Test getInvoiceAmount returns float
 */
public function testGetInvoiceAmountReturnsFloat(): void
{
    $result = $this->repository->getInvoiceAmount(1, 1, 'CPT4', '99213');
    $this->assertIsFloat($result);
}
```

Or use @coversDefaultClass at the class level if needed.

Benefits:
- PHPUnit automatically detects all code executed by the test
- Provides accurate and comprehensive code coverage metrics
- Helps identify unintended side effects and incomplete coverage
- Follows PHPUnit best practices for coverage reporting

## General Development Principles
Do the simplest thing that works first!

Always code to an interface if possible.

Use the SOLID principle for code in new projects and when adding to existing projects.

Always use a singleton for database connections in new projects if no other framework exists and we are not working in OpenEMR codebase.

Don't change any existing code without express written consent so that nothing existing is overwritten because you don't know why it is there.

## Billing Tables
Look in the ar_session, and ar_activity table along with the billing table. Those three tables store most of the claim billing information. The payment table is for payments that are taken through the front_payment.php UI.

## Composer Autoload
Never run `composer dump-autoload` in the root of the project as it will cause the application to break. Each module has its own composer autoload specific to it.

## Development Environment
When the working directory is /home/sherwin/PhpStorm/PhpstormProjects/ check if there is a .env.dev file at the start of the task. I should always include in my prompt "to start our next task".

Check if a php webserver is running for that directory. Using the .env.dev file to find the port number for that subfolder. An example to follow is /home/sherwin/PhpStorm/PhpstormProjects/Ace702/.env.dev

In the file you will find the webserver port to use `DEV_PORT=8001`

If webserver is not started launch `php -S localhost:` with port number from env.dev file.
This will allow us to do TDD. Install what is needed to facilitate the development process.

## Error Messages
When a subscription is already in use, display an error message on the screen to inform users that the entered subscription cannot be used again, replacing debug code with a user-friendly error message.
