# Overview

`Zend\Log\Logger` is a component for general purpose logging. It supports
multiple log backends, formatting messages sent to the log, and filtering
messages from being logged. These functions are divided into the following
objects:

- A **logger** (instance of `Zend\Log\Logger`) is the object that your application
  uses the most. You can have as many logger objects as you like; they do not
  interact. A logger object must contain at least one *writer*, and can optionally
  contain one or more *filters*.
- A **writer** (inherits from `Zend\Log\Writer\AbstractWriter`) writes data to
  an underlying storage implementation.
- A **filter** (implements `Zend\Log\Filter\FilterInterface`) filters (blocks)
  log data from being saved. A filter is applied to an individual writer.
  Filters can be chained.
- A **formatter** (implements `Zend\Log\Formatter\FormatterInterface`) formats
  the log data before it is written by a writer. Each writer has exactly one
  formatter.
- A **processor** (implements `Zend\Log\Processor\ProcessorInterface`) processes
  the log event prior to filtering or writing, allowing the ability to
  substitute, add, remove, or modify data to be logged.

## Creating a Log

To get started logging, instantiate a writer and then pass it to a logger instance:

```php
$logger = new Zend\Log\Logger;
$writer = new Zend\Log\Writer\Stream('php://output');

$logger->addWriter($writer);
```

It is important to note that the logger must have at least one writer. You can
add any number of writers using the logger's `addWriter()` method.

You can also add a priority to each writer. The priority is specified as an
integer and passed as the second argument in the `addWriter()` method.

Another way to add a writer to a logger is to use the name of the writer as
follow:

```php
$logger = new Zend\Log\Logger;

$logger->addWriter('stream', null, ['stream' => 'php://output']);
```

In this example we passed the stream `php://output` as a parameter (via an
options array).

## Logging Messages

To log a message, call the `log()` method of a `Logger` instance and pass it the
message priority and the message:

```php
$logger->log(Zend\Log\Logger::INFO, 'Informational message');
```

The first parameter of the `log()` method is the integer `priority` and the
second parameter is the string `message`. The priority must be one of the
priorities recognized by the `Logger` instance (explained in the next section).
There is also an optional third parameter used to pass extra
information/metadata to the writer.

Instead of using the `log()` method, you can optionally call methods named after
the various supported priorities, which allows you to omit the `priority`
argument:

```php
$logger->log(Zend\Log\Logger::INFO, 'Informational message');
$logger->info('Informational message');

$logger->log(Zend\Log\Logger::EMERG, 'Emergency message');
$logger->emerg('Emergency message');
```

## Destroying a Log

If the `Logger` instance is no longer needed, set the variable containing it to
`NULL` to destroy it.  This will automatically call the `shutdown()` instance
method of each attached writer before the `Logger` instance is destroyed.

```php
$logger = null;
```

Explicitly destroying the log in this way is optional and is performed
automatically at PHP shutdown.

## Using Built-in Priorities

The `Zend\Log\Logger` class defines the following priorities:

```php
EMERG   = 0;  // Emergency: system is unusable
ALERT   = 1;  // Alert: action must be taken immediately
CRIT    = 2;  // Critical: critical conditions
ERR     = 3;  // Error: error conditions
WARN    = 4;  // Warning: warning conditions
NOTICE  = 5;  // Notice: normal but significant condition
INFO    = 6;  // Informational: informational messages
DEBUG   = 7;  // Debug: debug messages
```

These priorities are always available, and a convenience method of the same name
(but lowercased) is available for each one.

The priorities are not arbitrary. They come from the BSD syslog protocol, which
is described in [RFC-3164](http://tools.ietf.org/html/rfc3164). The names and
corresponding priority numbers are also compatible with another PHP logging
system, [PEAR Log](http://pear.php.net/package/log), which perhaps promotes
interoperability between it and `Zend\Log\Logger`;
[PSR-3](http://www.php-fig.org/psr/psr-3/) uses similar semantics, but without
the explicit priority integers.

Priority numbers descend in order of importance. `EMERG` (0) is the most
important priority. `DEBUG` (7) is the least important priority of the built-in
priorities. You may define priorities of lower importance than `DEBUG`. When
selecting the priority for your log message, be aware of this priority hierarchy
and choose appropriately.

## Understanding Log Events

When you call the `log()` method or one of its shortcuts, a log event is
created. This is simply an associative array with data describing the event that
is passed to the writers. The following keys are always created in this array:

- `timestamp`
- `message`
- `priority`
- `priorityName`

The creation of the `event` array is an internal detail of implementation.

## Log PHP Errors

`Zend\Log\Logger` can also be used to log PHP errors and intercept exceptions.
Calling the static method `registerErrorHandler($logger)` will register the
`$logger` instance to log errors; it returns a boolean `false` ensuring that it
returns delegation to any other error handlers registered, including the default
PHP error handler.

```php
$logger = new Zend\Log\Logger;
$writer = new Zend\Log\Writer\Stream('php://output');

$logger->addWriter($writer);

Zend\Log\Logger::registerErrorHandler($logger);
```

If you want to unregister the error handler, can use the `unregisterErrorHandler()` static
method.

You can also configure a logger to intercept exceptions using the static method
`registerExceptionHandler($logger)`.
