# Processors

**_Processors_** allow you to provide additional information to logs in an
automated fashion. They are called from the logger before the event is passed
to the writers; they receive the event array, and return an event array on
completion.

Use cases include:

- Providing exception backtrace information.
- Injecting substitutions into the message.
- Injecting a request identifier (in order to later inspect logs for a specific
  identifier)

## Processor interface

All processors must implement `Zend\Log\Processor\ProcessorInterface`:

```php
namespace Zend\Log\Processor;

interface ProcessorInterface
{
    /**
     * Processes a log message before it is given to the writers
     *
     * @param  array $event
     * @return array
     */
    public function process(array $event);
}
```

## Adding processors

To add a processor to a `Logger` instance, inject it using the `addProcessor()` method:

```php
$logger->addProcessor(new Zend\Log\Processor\Backtrace());
```

## Available processors

The following processors are available.

### Backtrace

`Zend\Log\Processor\Backtrace` calls `debug_backtrace()` for every log event,
injecting the details into the event's `extra` array:

```php
$event = [
    // ... standard elements ...
    'extra' => [
        'file' => 'SomeFile.php',
        'line' => 1337,
        'class' => 'Foo\MyClass',
        'function' => 'myMethod',
    ],
];
```

### PsrPlaceholder

`Zend\Log\Processor\PsrPlaceholder` replaces [PSR-3](http://www.php-fig.org/psr/psr-3/)-formatted
message placeholders with the values found in the `extra` array.

As an example:

```php
$logger->addProcessor(new Zend\Log\Processor\PsrPlaceholder());
$logger->warn('Invalid plugin {plugin}', ['plugin' => 'My\Plugins\FooPlugin']);
```

will output:

```
Invalid plugin My\Plugins\FooPlugin
```

This feature allows compatibility with PSR-3, and provides a simple way to
provide string substitutions without needing to resort to `sprintf()` in
your userland code.

### ReferenceId

`Zend\Log\Processor\ReferenceId` allows you to specify a static reference
identifier to inject in all log messages; typically, you will generate a new
one for each request, to allow querying logs for the given reference
identifier later.

Given the following:

```php
$processor = new Zend\Log\Processor\ReferenceId();
$processor->setIdentifier(microtime(true) . '_' . uniqid());
$logger->addProcessor($processor);
$logger->info('Log event');
```

The event will contain:

```php
$event = [
    /* ... standard values ... */
    'extra' => [
        'referenceId' => '1455057110.6284_56ba68ebe1244',
    ],
];
```

### RequestId

`Zend\Log\Processor\RequestId` is similar to `ReferenceId` with one key
difference: if you do not set an identifier, one is automatically
generated for you using hashed information from `$_SERVER`, including
`REQUEST_TIME_FLOAT`, `HTTP_X_FORWARDED_FOR`, and/or `REMOTE_ADDR`.
