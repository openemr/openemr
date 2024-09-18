# Developers
## Supported PHP versions
We support PHP >= 8.0

## Supported platforms
We maintain and test our SDK using Travis.CI and Ubuntu.
Windows/MacOS/BSD platforms support was verified only once after SDK v4.0 release. We do not test the new releases with these platforms.

## Subscription
The important thing to notice is that the subscription loop in PHP SDK is synchronous.
You can find that PHP support threads and event loop libraries, but all of them are not stable. Anyway, the language wasn't designed for asynchronous tasks. So the main purpose for PHP SDK is invoking the synchronous endpoint calls.

## 3rd Party Libraries
### Requests
Requests library [https://github.com/rmccue/Requests] is a wrapper over raw cURL requests.

### Monolog (logging library)
Monolog has been removed from `PubNub` instance and has been replaced by `Psr\Log\NullLogger`. Now any logger that implements `Psr\Log\LoggerInterface` can be used after setting it to existing `PubNub` instance through a `setLogger(LoggerInterface $logger)` method.

## Tests
There are 3 type of tests:
* Unit tests
* Functional
* Integration

We use PHPUnit framework for all test types.
