# Configuration

Most of the configuration for both the setup of `Definition`s as well as the
setup of the `InstanceManager` can be attained by a configuration file. This
file will produce an array (typically) and have an iterable structure.

The top two keys are 'definition' and 'instance', each specifying values for
the definition setup and instance manager setup, respectively.

The definition section expects the following information expressed as a PHP
array:

```php
$config = [
    'definition' => [
        'compiler' => [/* @todo compiler information */],
        'runtime'  => [/* @todo runtime information */],
        'class' => [
            'instantiator' => '', // the name of the instantiator, by default this is __construct
            'supertypes'   => [], // an array of supertypes the class implements
            'methods'      => [
                'setSomeParameter' => [ // a method name
                    'parameterName' => [
                        'name',        // string parameter name
                        'type',        // type or null
                        'is-required', // bool
                    ),
                ),
            ),
        ),
    ),
);
```
