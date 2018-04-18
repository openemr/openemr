# Filters

A *filter* prevents a message from being written to the log.

You can add a filter to a specific writer using the `addFilter()` method of the
writer:

```php
use Zend\Log\Logger;

$logger = new Logger();

$writer1 = new Zend\Log\Writer\Stream('/path/to/first/logfile');
$logger->addWriter($writer1);

$writer2 = new Zend\Log\Writer\Stream('/path/to/second/logfile');
$logger->addWriter($writer2);

// add a filter only to writer2
$filter = new Zend\Log\Filter\Priority(Logger::CRIT);
$writer2->addFilter($filter);

// logged to writer1, blocked from writer2
$logger->info('Informational message');

// logged by both writers
$logger->emerg('Emergency message');
```

## Available filters

Filter Class | Short Name | Description
------------ | ---------- | -----------
`Zend\Log\Filter\Priority` | Priority | Filter logging by `$priority`. By default, it will accept any log event whose priority value is less than or equal to `$priority`.
`Zend\Log\Filter\Regex` | Regex | Filter out any log messages not matching the regex pattern. This filter uses the `preg_match()` function.
`Zend\Log\Filter\Timestamp` | Timestamp | Filters log events based on the time when they were triggered. It can be configured by specifying either `idate()`-compliant format characters along with the desired value, or a full `DateTime` instance. An appropriate comparison operator must be supplied in either case.
`Zend\Log\Filter\SuppressFilter` | SuppressFilter | A simple boolean filter; a boolean `true` value passed to the constructor suppresses all log events, while a boolean `false` value accepts all log events.
`Zend\Log\Filter\Validator` | Validator | Filter any log messages that fail validaton by the composed `Zend\Validator\ValidatorInterface` implementation.  
