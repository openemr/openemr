# Introduction

zend-validator provides a set of commonly needed validators. It also provides a
simple validator chaining mechanism by which multiple validators may be applied
to a single datum in a user-defined order.

## What is a validator?

A validator examines its input with respect to some requirements and produces a
boolean result indicating whether the input successfully validates against the
requirements. If the input does not meet the requirements, a validator may
additionally provide information about which requirement(s) the input does not
meet.

For example, a web application might require that a username be between six and
twelve characters in length, and may only contain alphanumeric characters. A
validator can be used for ensuring that a username meets these requirements. If
a chosen username does not meet one or both of the requirements, it would be
useful to know which of the requirements the username fails to meet.

## Basic usage of validators

Having defined validation in this way provides the foundation for
`Zend\Validator\ValidatorInterface`, which defines two methods, `isValid()` and
`getMessages()`. The `isValid()` method performs validation upon the provided
value, returning `true` if and only if the value passes against the validation
criteria.

If `isValid()` returns `false`, the `getMessages()` method will return an array
of messages explaining the reason(s) for validation failure. The array keys are
short strings that identify the reasons for validation failure, and the array
values are the corresponding human-readable string messages. The keys and values
are class-dependent; each validation class defines its own set of validation
failure messages and the unique keys that identify them. Each class also has a
`const` definition that matches each identifier for a validation failure cause.

> ### Stateful validators
>
> The `getMessages()` methods return validation failure information only for the
> most recent `isValid()` call. Each call to `isValid()` clears any messages and
> errors caused by a previous `isValid()` call, because it's likely that each
> call to `isValid()` is made for a different input value.

The following example illustrates validation of an e-mail address:

```php
use Zend\Validator\EmailAddress;

$validator = new EmailAddress();

if ($validator->isValid($email)) {
    // email appears to be valid
} else {
    // email is invalid; print the reasons
    foreach ($validator->getMessages() as $messageId => $message) {
        printf("Validation failure '%s': %s\n", $messageId, $message);
    }
}
```

## Customizing messages

Validator classes provide a `setMessage()` method with which you can specify the
format of a message returned by `getMessages()` in case of validation failure.
The first argument of this method is a string containing the error message. You
can include tokens in this string which will be substituted with data relevant
to the validator. The token `%value%` is supported by all validators; this is
substituted with the value you passed to `isValid()`. Other tokens may be
supported on a case-by-case basis in each validation class. For example, `%max%`
is a token supported by `Zend\Validator\LessThan`. The `getMessageVariables()`
method returns an array of variable tokens supported by the validator.

The second optional argument is a string that identifies the validation failure
message template to be set, which is useful when a validation class defines more
than one cause for failure. If you omit the second argument, `setMessage()`
assumes the message you specify should be used for the first message template
declared in the validation class. Many validation classes only have one error
message template defined, so there is no need to specify which message template
you are changing.

```php
use Zend\Validator\StringLength;

$validator = new StringLength(8);

$validator->setMessage(
    'The string \'%value%\' is too short; it must be at least %min% characters',
    StringLength::TOO_SHORT
);

if (! $validator->isValid('word')) {
    $messages = $validator->getMessages();
    echo current($messages);

    // "The string 'word' is too short; it must be at least 8 characters"
}
```

You can set multiple messages using the `setMessages()` method. Its argument is
an array containing key/message pairs.

```php
use Zend\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);

$validator->setMessages([
    StringLength::TOO_SHORT => 'The string \'%value%\' is too short',
    StringLength::TOO_LONG  => 'The string \'%value%\' is too long',
]);
```

If your application requires even greater flexibility with which it reports
validation failures, you can access properties by the same name as the message
tokens supported by a given validation class. The `value` property is always
available in a validator; it is the value you specified as the argument of
`isValid()`. Other properties may be supported on a case-by-case basis in each
validation class.

```php
use Zend\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);

if (! $validator->isValid('word')) {
    printf(
        "Word failed: %s; its length is not between %d and %d\n",
        $validator->value,
        $validator->min,
        $validator->max
    );
}
```

## Translating messages

> ### Translation compatibility
>
> In versions 2.0 - 2.1, `Zend\Validator\AbstractValidator` implemented
> `Zend\I18n\Translator\TranslatorAwareInterface` and accepted instances of
> `Zend\I18n\Translator\Translator`. Starting in version 2.2.0, zend-validator
> now defines a translator interface, > `Zend\Validator\Translator\TranslatorInterface`,
> as well as it's own -aware variant, > `Zend\Validator\Translator\TranslatorAwareInterface`.
> This was done to reduce dependencies for the component, and follows the
> principal of Separated Interfaces.
>
> The upshot is that if you are migrating from a pre-2.2 version, and receiving
> errors indicating that the translator provided does not implement
> `Zend\Validator\Translator\TranslatorInterface`, you will need to make a
> change to your code.
>
> An implementation of `Zend\Validator\Translator\TranslatorInterface` is
> provided in `Zend\Mvc\I18n\Translator`, which also extends
> `Zend\I18n\Translator\Translator`. This version can be instantiated and used
> just as the original `Zend\I18n` version.
>
> A new service has also been registered with the MVC, `MvcTranslator`, which
> will return this specialized, bridge instance.
>
> Most users should see no issues, as `Zend\Validator\ValidatorPluginManager`
> has been modified to use the `MvcTranslator` service internally, which is how
> most developers were getting the translator instance into validators in the
> first place. You will only need to change code if you were manually injecting
> the instance previously.

Validator classes provide a `setTranslator()` method with which you can specify
an instance of `Zend\Validator\Translator\TranslatorInterface` which will
translate the messages in case of a validation failure. The `getTranslator()`
method returns the translator instance. `Zend\Mvc\I18n\Translator` provides an
implementation compatible with the validator component.

```php
use Zend\Mvc\I18n\Translator;
use Zend\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);
$translate = new Translator();
// configure the translator...

$validator->setTranslator($translate);
```

With the static `AbstractValidator::setDefaultTranslator()` method you can set a
instance of `Zend\Validator\Translator\TranslatorInterface` which will be used
for all validation classes, and can be retrieved with `getDefaultTranslator()`.
This prevents the need for setting a translator manually with each validator.

```php
use Zend\Mvc\I18n\Translator;
use Zend\Validator\AbstractValidator;

$translate = new Translator();
// configure the translator...

AbstractValidator::setDefaultTranslator($translate);
```

Sometimes it is necessary to disable the translator within a validator. To
achieve this you can use the `setDisableTranslator()` method, which accepts a
boolean parameter, and `isTranslatorDisabled()` to get the set value.

```php
use Zend\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);
if (! $validator->isTranslatorDisabled()) {
    $validator->setDisableTranslator();
}
```

It is also possible to use a translator instead of setting own messages with
`setMessage()`. But doing so, you should keep in mind, that the translator works
also on messages you set your own.
