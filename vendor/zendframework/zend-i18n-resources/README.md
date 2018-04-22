# zend-i18n-resources

This "component" provides translation resources, specifically for `zendframework/zend-validate` and
`zendframework/zend-captcha`, for use with `zendframework/zend-i18n`'s Translator subcomponent.

- File issues at https://github.com/zendframework/zend-i18n-resources/issues
- Documentation is at http://framework.zend.com/docs

## Installation

```console
$ composer require zendframework/zend-i18n-resources
```

## Usage

To use the resources, you need to use the provided `Zend\I18n\Translator\Resources` class to
retrieve the path and pattern to provide to
`Zend\I18n\Translator\Translator::addTranslationFilePattern()`:

```php
use Zend\I18n\Translator\Resources;
use Zend\I18n\Translator\Translator;

$translator = new Translator();
$translator->addTranslationFilePattern(
    'phpArray',
    Resources::getBasePath(),
    Resources::getPatternForValidator()
);

echo $translator->translate('Invalid type given. String expected', 'default', 'es');
```

You can also use the `getPatternForCaptcha()` method to setup translation messages for
`zend-captcha`:

```php
$translator->addTranslationFilePattern(
    'phpArray',
    Resources::getBasePath(),
    Resources::getPatternForCaptcha()
);
```

## Automating resource injection

If you are using `Zend\I18n\Translator\Translator` via the `zend-servicemanager`, you may want to
automate injecting the translation messages. This can be done using `zend-servicemanager`'s
[delegator factories](http://framework.zend.com/manual/current/en/modules/zend.service-manager.delegator-factories.html).

As an example, consider the following delegator factory:

```php
use Zend\I18n\Translator\Resources;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TranslatorDelegator implements DelegatorFactoryInterface
{
    public function createDelegatorWithName(
        ServiceLocatorInterface $services,
        $name,
        $requestedName,
        $callback
    ) {
        $translator = $callback();
        $translator->addTranslationFilePattern(
            'phpArray',
            Resources::getBasePath(),
            Resources::getPatternForValidator()
        );
        $translator->addTranslationFilePattern(
            'phpArray',
            Resources::getBasePath(),
            Resources::getPatternForCaptcha()
        );
        
        return $translator;
    }
}
```

You would then register this in your configuration:

```php
return [
    'service_manager' => [
        'delegators' => [
            'MvcTranslator' => [
                'TranslatorDelegator',
            ],
        ],
    ],
];
```
