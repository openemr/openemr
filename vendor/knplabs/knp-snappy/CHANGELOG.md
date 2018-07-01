## 1.0.4

* Support cache-dir for Image generation  (see [#297](https://github.com/KnpLabs/snappy/pull/297)).

Thank you @dimitrilahaye for their work.

## 1.0.3

* Add support to Symfony 4 ([#290](https://github.com/KnpLabs/snappy/pull/290))
* Use PHPUnit\Framework\TestCase instead of PHPUnit_Framework_TestCase ([#287](https://github.com/KnpLabs/snappy/pull/287))

Credits go to @michaelperrin and @carusogabriel.

## 1.0.2

*A BC break was introduced in v1.0.0: using objects castable to string with a cyclic dependency to the generator 
as option value would break `setOption()` / `setOptions()` methods.* 

* Use logger context rather than `var_export` to log option values (see [#283](https://github.com/KnpLabs/snappy/pull/283))

Credits go to: @barryvdh.

## 1.0.1

* Fix `Call to a member function debug() on null` logger (see [#270](https://github.com/KnpLabs/snappy/pull/270))

## 1.0.0

* Don't check if it's a file when the path is bigger than `PHP_MAXPATHLEN` (see [#224](https://github.com/KnpLabs/snappy/pull/224))
* Pass `image-dpi` and `image-quality` options as integer (see [#251](https://github.com/KnpLabs/snappy/pull/251))
* Improve documentation readability (see [#255](https://github.com/KnpLabs/snappy/pull/255))
* Add logging capabilities to generators (see [#264](https://github.com/KnpLabs/snappy/pull/264))
* Add some more frequent questions/issues to the FAQ (see [#263](https://github.com/KnpLabs/snappy/pull/263), [#265](https://github.com/KnpLabs/snappy/pull/265), [#266](https://github.com/KnpLabs/snappy/pull/266))

Credits go to: @wouterbulten, @martinssipenko, @Herz3h, @akovalyov, @NiR-.
