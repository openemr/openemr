# Zend\\Config\\Processor

`Zend\Config\Processor` provides the ability to perform operations on a
`Zend\Config\Config` object. `Zend\Config\Processor` is itself an interface that
defining two methods: `process()` and `processValue()`.

zend-config provides the following concrete implementations:

- `Zend\Config\Processor\Constant`: manage PHP constant values.
- `Zend\Config\Processor\Filter`: filter the configuration data using `Zend\Filter`.
- `Zend\Config\Processor\Queue`: manage a queue of operations to apply to configuration data.
- `Zend\Config\Processor\Token`: find and replace specific tokens.
- `Zend\Config\Processor\Translator`: translate configuration values in other languages using `Zend\I18n\Translator`.

## Zend\\Config\\Processor\\Constant

### Using Zend\\Config\\Processor\\Constant

This example illustrates the basic usage of `Zend\Config\Processor\Constant`:

```php
define ('TEST_CONST', 'bar');

// Provide the second parameter as boolean true to allow modifications:
$config = new Zend\Config\Config(['foo' => 'TEST_CONST'], true);
$processor = new Zend\Config\Processor\Constant();

echo $config->foo . ',';
$processor->process($config);
echo $config->foo;
```

This example returns the output: `TEST_CONST,bar`.

## Zend\\Config\\Processor\\Filter

### Using Zend\\Config\\Processor\\Filter

This example illustrates basic usage of `Zend\Config\Processor\Filter`:

```php
use Zend\Filter\StringToUpper;
use Zend\Config\Processor\Filter as FilterProcessor;
use Zend\Config\Config;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['foo' => 'bar'], true);
$upper = new StringToUpper();

$upperProcessor = new FilterProcessor($upper);

echo $config->foo . ',';
$upperProcessor->process($config);
echo $config->foo;
```

This example returns the output: `bar,BAR`.

## Zend\\Config\\Processor\\Queue

### Using Zend\\Config\\Processor\\Queue

This example illustrates basic usage of `Zend\Config\Processor\Queue`:

```php
use Zend\Filter\StringToLower;
use Zend\Filter\StringToUpper;
use Zend\Config\Processor\Filter as FilterProcessor;
use Zend\Config\Processor\Queue;
use Zend\Config\Config;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['foo' => 'bar'], true);
$upper  = new StringToUpper();
$lower  = new StringToLower();

$lowerProcessor = new FilterProcessor($lower);
$upperProcessor = new FilterProcessor($upper);

$queue = new Queue();
$queue->insert($upperProcessor);
$queue->insert($lowerProcessor);
$queue->process($config);

echo $config->foo;
```

This example returns the output: `bar`. The filters in the queue are applied in
*FIFO* (First In, First Out) order .

## Zend\\Config\\Processor\\Token

### Using Zend\\Config\\Processor\\Token

This example illustrates basic usage of `Zend\Config\Processor\Token`:

```php
// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['foo' => 'Value is TOKEN'], true);
$processor = new TokenProcessor();

$processor->addToken('TOKEN', 'bar');
echo $config->foo . ',';
$processor->process($config);
echo $config->foo;
```

This example returns the output: `Value is TOKEN,Value is bar`.

## Zend\\Config\\Processor\\Translator

### Using Zend\\Config\\Processor\\Translator

This example illustrates basic usage of `Zend\Config\Processor\Translator`:

```php
use Zend\Config\Config;
use Zend\Config\Processor\Translator as TranslatorProcessor;
use Zend\I18n\Translator\Translator;

// Provide the second parameter as boolean true to allow modifications:
$config = new Config(['animal' => 'dog'], true);

/*
 * The following mapping is used for the translation
 * loader provided to the translator instance:
 *
 * $italian = [
 *     'dog' => 'cane'
 * ];
 */

$translator = new Translator();
// ... configure the translator ...
$processor = new TranslatorProcessor($translator);

echo "English: {$config->animal}, ";
$processor->process($config);
echo "Italian: {$config->animal}";
```

This example returns the output: `English: dog,Italian: cane`.
