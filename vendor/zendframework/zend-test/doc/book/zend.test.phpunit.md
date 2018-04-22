# Unit testing with PHPUnit

`Zend\Test\PHPUnit` provides a TestCase for MVC applications that contains assertions for testing
against a variety of responsibilities. Probably the easiest way to understand what it can do is to
see an example.

The following is a simple test case for a IndexController to verify things like HTTP code,
controller and action name :

```php
<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include '/path/to/application/config/test/application.config.php'
        );
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('application');
        $this->assertControllerName('application_index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }
}
```

The setup of the test case can to define the application config. You can use several config to test
modules dependencies or your current application config.

## Setup your TestCase

As noted in the previous example, all MVC test cases should extend AbstractHttpControllerTestCase.
This class in turn extends `PHPUnit_Framework_TestCase`, and gives you all the structure and
assertions you'd expect from PHPUnit -- as well as some scaffolding and assertions specific to Zend
Framework's MVC implementation.

In order to test your MVC application, you will need to setup the application config. Use simply the
the `setApplicationConfig` method :

```php
public function setUp()
{
    $this->setApplicationConfig(
        include '/path/to/application/config/test/application.config.php'
    );
    parent::setUp();
}
```

Once the application is set up, you can write your tests. To help debug tests, you can activate the
flag `traceError` to throw MVC exception during the tests writing :

```php
<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
}
```

## Testing your Controllers and MVC Applications

Once you have your application config in place, you can begin testing. Testing is basically as you
would expect in an PHPUnit test suite, with a few minor differences.

First, you will need to dispatch a URL to test, using the `dispatch` method of the TestCase:

```php
public function testIndexAction()
{
    $this->dispatch('/');
}
```

There will be times, however, that you need to provide extra information -- GET and POST variables,
COOKIE information, etc. You can populate the request with that information:

```php
public function testIndexAction()
{
    $this->getRequest()
        ->setMethod('POST')
        ->setPost(new Parameters(array('argument' => 'value')));
    $this->dispatch('/');
}
```

You can populate GET or POST variables directly with the `dispatch` method :

```php
public function testIndexAction()
{
    $this->dispatch('/', 'POST', array('argument' => 'value'));
}
```

You can use directly yours query args in the url :

```php
public function testIndexAction()
{
    $this->dispatch('/tests?foo=bar&baz=foo');
}
```

Now that the request is made, it's time to start making assertions against it.

### Assertions

Assertions are at the heart of Unit Testing; you use them to verify that the results are what you
expect. To this end, `Zend\Test\PHPUnit\AbstractControllerTestCase` provides a number of assertions
to make testing your MVC apps and controllers simpler.

**Request Assertions**

It's often useful to assert against the last run action, controller, and module; additionally, you
may want to assert against the route that was matched. The following assertions can help you in this
regard:

- `assertModulesLoaded(array $modules)`: Assert that the given modules was loaded by the
application.
- `assertModuleName($module)`: Assert that the given module was used in the last dispatched action.
- `assertControllerName($controller)`: Assert that the given controller identifier was selected in
the last dispatched action.
- `assertControllerClass($controller)`: Assert that the given controller class was selected in the
last dispatched action.
- `assertActionName($action)`: Assert that the given action was last dispatched.
- `assertMatchedRouteName($route)`: Assert that the given named route was matched by the router.

Each also has a 'Not' variant for negative assertions.

**CSS Selector Assertions**

CSS selectors are an easy way to verify that certain artifacts are present in the response content.
They also make it trivial to ensure that items necessary for Javascript UIs and/or AJAX integration
will be present; most JS toolkits provide some mechanism for pulling DOM elements based on CSS
selectors, so the syntax would be the same.

This functionality is provided via `Zend\Dom\Query`, and integrated into a set of 'Query'
assertions. Each of these assertions takes as their first argument a CSS selector, with optionally
additional arguments and/or an error message, based on the assertion type. You can find the rules
for writing the CSS selectors in the `Zend\Dom\Query` \[Theory of
Operation\](zend.dom.query.operation) chapter. Query assertions include:

- `assertQuery($path)`: assert that one or more DOM elements matching the given CSS selector are
present.
- `assertQueryContentContains($path, $match)`: assert that one or more DOM elements matching the
given CSS selector are present, and that at least one contains the content provided in $match.
- `assertQueryContentRegex($path, $pattern)`: assert that one or more DOM elements matching the
given CSS selector are present, and that at least one matches the regular expression provided in
$pattern. If a $message is present, it will be prepended to any failed assertion message.
- `assertQueryCount($path, $count)`: assert that there are exactly $count DOM elements matching the
given CSS selector present.
- `assertQueryCountMin($path, $count)`: assert that there are at least $count DOM elements matching
the given CSS selector present.
- `assertQueryCountMax($path, $count)`: assert that there are no more than $count DOM elements
matching the given CSS selector present.

Additionally, each of the above has a 'Not' variant that provides a negative assertion:
assertNotQuery(), assertNotQueryContentContains(), assertNotQueryContentRegex(), and
assertNotQueryCount(). (Note that the min and max counts do not have these variants, for what should
be obvious reasons.)

**XPath Assertions**

Some developers are more familiar with XPath than with CSS selectors, and thus XPath variants of all
the Query assertions are also provided. These are:

- `assertXpathQuery($path)`
- `assertNotXpathQuery($path)`
- `assertXpathQueryCount($path, $count)`
- `assertNotXpathQueryCount($path, $count)`
- `assertXpathQueryCountMin($path, $count)`
- `assertXpathQueryCountMax($path, $count)`
- `assertXpathQueryContentContains($path, $match)`
- `assertNotXpathQueryContentContains($path, $match)`
- `assertXpathQueryContentRegex($path, $pattern)`
- `assertNotXpathQueryContentRegex($path, $pattern)`

**Redirect Assertions**

Often an action will redirect. Instead of following the redirect,
`Zend\Test\PHPUnit\ControllerTestCase` allows you to test for redirects with a handful of
assertions.

- `assertRedirect()`: assert simply that a redirect has occurred.
- `assertRedirectTo($url)`: assert that a redirect has occurred, and that the value of the Location
header is the $url provided.
- `assertRedirectRegex($pattern)`: assert that a redirect has occurred, and that the value of the
Location header matches the regular expression provided by $pattern.

Each also has a 'Not' variant for negative assertions.

**Response Header Assertions**

In addition to checking for redirect headers, you will often need to check for specific HTTP
response codes and headers -- for instance, to determine whether an action results in a 404 or 500
response, or to ensure that JSON responses contain the appropriate Content-Type header. The
following assertions are available.

- `assertResponseStatusCode($code)`: assert that the response resulted in the given HTTP response
code.
- `assertResponseHeader($header)`: assert that the response contains the given header.
- `assertResponseHeaderContains($header, $match)`: assert that the response contains the given
header and that its content contains the given string.
- `assertResponseHeaderRegex($header, $pattern)`: assert that the response contains the given header
and that its content matches the given regex.

Additionally, each of the above assertions have a 'Not' variant for negative assertions.
