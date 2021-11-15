CHANGELOG
=========

1.0.1
-----

* Fix storing screenshots in the wrong directory when `PANTHER_ERROR_SCREENSHOT_DIR` is enabled

1.0.0
-----

* Add `Client::waitForEnabled()`, `Client::waitForDisabled()`, `Client::waitForAttributeToContain()` and  `Client::waitForAttributeToNotContain()` methods
* Add `PantherTestCase::assertSelectorAttributeContains()`, `PantherTestCase::assertSelectorAttributeNotContains()`, `PantherTestCase::assertSelectorWillExist()`,
  `PantherTestCase::assertSelectorWillNotExist()`, `PantherTestCase::assertSelectorWillBeVisible()`, `PantherTestCase::assertSelectorWillNotBeVisible()`,
  `PantherTestCase::assertSelectorWillContain()`, `PantherTestCase::assertSelectorWillNotContain()`, `PantherTestCase::assertSelectorWillBeEnabled()`,
  `PantherTestCase::assertSelectorWillBeDisabled`, `PantherTestCase::assertSelectorAttributeWillContain()`, and `PantherTestCase::assertSelectorAttributeWillNotContain()`
  assertions
* Automatically take a screenshot when a test fail and if the `PANTHER_ERROR_SCREENSHOT_DIR` environment variable is set
* Add missing return types
* **Breaking Change**: Remove the deprecated PHPUnit listener, use the PHPUnit extension instead
* **Breaking Change**: Remove deprecated support for Goutte, use `HttpBrowser` instead
* **Breaking Change**: Remove deprecated support for `PANTHER_CHROME_DRIVER_BINARY` and `PANTHER_GECKO_DRIVER_BINARY` environment variables, add the binaries in your `PATH` instead
* Don't allow unserializing classes with a destructor

0.9.0
-----

* **Breaking Change**: ChromeDriver and geckodriver binaries are not included in the archive anymore and must be installed separately, [refer to the documentation](README.md#installing-chromedriver-and-geckodriver)
* PHP 8 compatibility
* Add `Client::waitForStaleness()` method to wait for an element to be removed from the DOM
* Add `Client::waitForInvisibility()` method to wait for an element to be invisible
* Add `Client::waitForElementToContain()` method to wait for an element containing the given parameter
* Add `Client::waitForElementToNotContain()` method to wait for an element to not contain the given parameter
* Add `PantherTestCase::assertSelectorIsVisible()`, `PantherTestCase::assertSelectorIsNotVisible()`, `PantherTestCase::assertSelectorIsEnabled()` and `PantherTestCase::assertSelectorIsDisabled()` assertions
* Fix `baseUri` not taken into account when using Symfony HttpBrowser

0.8.0
-----

* Upgrade ChromeDriver to version 85.0.4183.87
* Upgrade geckodriver to version 0.27.0
* Add a `Client::waitForVisibility()` method to wait for an element to appear
* Allow passing options to the browser manager from `PantherTestCase::createPantherClient()`
* Add a `Client::ping()` method to check if the WebDriver connection is still active
* Fix setting a new value to an input field when there is an existing value
* Improve the error message when the web server crashes
* Throw an explanative `LogicException` when driver is not started yet
* Prevent timeouts caused by the integrated web server
* Fix the value of cookie secure flags
* Throw an exception when getting history (unsupported feature)
* Add docs to use Panther with GitHub Actions
* Various bug fixes and documentation improvements

0.7.1
-----

* Fix some inconsistencies between Chrome and Firefox 

0.7.0
-----

* Add built-in support for Firefox (using GeckoDriver)
* Add support for Symfony HttpBrowser
* Deprecate Goutte support (use HttpBrowser instead)
* Allow configuring `RemoteWebDriver` timeouts when using Selenium
* Allow passing custom environment variables to the built-in web server
* Fix some compatibility issues with PHP WebDriver 1.8
* Upgrade ChromeDriver to version 80.0.3987.106
* Prevent access to fixture files even if the web server is misconfigured

0.6.1
-----

* Upgrade ChromeDriver to version 79.0.3945.36
* Allow passing custom timeouts as options of `ChromeManager` (`connection_timeout_in_ms` and `request_timeout_in_ms`)

0.6.0
-----

* Add compatibility with Symfony 5
* Allow using `Client::waitFor()` to wait for invisible elements
* Add support to pass XPath expressions as parameters of `Client::waitFor()`
* Fix `Crawler::attr()` signature (it can return `null`)
* Deprecate `ServerListener` (use `ServerExtension` instead)
* Upgrade ChromeDriver to version 78.0.3904.70
* New logo
* Various docs fixes and improvements

0.5.2
-----

* Fix a bug occurring when using a non-fresh client

0.5.1
-----

* Allow to override the `APP_ENV` environment variable passed to the web server by setting `PANTHER_APP_ENV`
* Fix using assertions with a client created through `PantherTestCase::createClient()`
* Don't call `PantherTestCase::getClient()` if this method isn't `static`
* Fix remaining deprecations

0.5.0
-----

* Add support for [Crawler test assertions](https://symfony.com/doc/current/testing/functional_tests_assertions.html#crawler)
* Add the `PantherTestCase::createAdditionalPantherClient()` to retrieve additional isolated browsers, useful to test applications using [Mercure](https://mercure.rocks) or [WebSocket](https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API)   
* Improved support for non-standard web server directories
* Allow the integrated web server to start even if the homepage doesn't return a 200 HTTP status code
* Increase default timeouts from 5 seconds to 30 seconds
* Improve error messages
* Add compatibility with Symfony 4.3
* Upgrade ChromeDriver to version 76.0.3809.68
* Various quality improvements

0.4.1
-----

* Remove the direct dependency to `symfony/contracts`

0.4.0
-----

* Speed up the boot sequence
* Add basic support for file uploads
* Add a `readinessPath` option to use a custom path for server readiness detection
* Fix the behavior of `ChoiceFormField::getValue()` to be consistent with other BrowserKit implementations
* Ensure to clean the previous content of field when using `TextareaFormField::setValue()` and `InputFormField::setValue()`

0.3.0
-----

* Add a new API to manipulate the mouse
* Keep the browser window open on fail, when running in non-headless mode
* Automatically open Chrome DevTools when running in non-headless mode
* PHPUnit 8 compatibility
* Add a PHPUnit extension to keep alive the web server, and the client between tests 
* Change the default port of the web server to `9080` to prevent a conflict with Xdebug
* Allow to use an external web server instead of the built-in one for testing
* Allow to use a custom router script
* Allow to use a custom Chrome binary

0.2.0
-----

* Add JS execution capabilities to `Client`
* Allow keeping the web server and client active even after test teardown
* Add a method to refresh the crawler (`Client::refreshCrawler()`)
* Add options to configure the web server and ChromeDriver
* PHP 7.1 compatibility
