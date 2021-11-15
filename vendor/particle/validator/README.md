[![image](https://cloud.githubusercontent.com/assets/6495166/7207286/8b48105e-e538-11e4-9dfa-97c7fb2398aa.png)](http://validator.particle-php.com)
===

[![Travis-CI](https://img.shields.io/travis/particle-php/Validator/master.svg)](https://travis-ci.org/particle-php/Validator)
[![Packagist](https://img.shields.io/packagist/v/particle/validator.svg)](https://packagist.org/packages/particle/validator)
[![Packagist downloads](https://img.shields.io/packagist/dt/particle/validator.svg)](https://packagist.org/packages/particle/validator)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/particle-php/Validator.svg)](https://scrutinizer-ci.com/g/particle-php/Validator/?branch=master)
[![Scrutinizer](https://img.shields.io/scrutinizer/coverage/g/particle-php/Validator/master.svg)](https://scrutinizer-ci.com/g/particle-php/Validator/?branch=master)

*Particle\Validator* is a very small validation library, with the easiest and most usable API we could possibly create.

## Install
To easily include *Particle\Validator* into your project, install it via [composer](https://getcomposer.org) using the command line:

```bash
composer require particle/validator
```

## Small usage example

```php
use Particle\Validator\Validator;

$v = new Validator;

$v->required('user.first_name')->lengthBetween(2, 50)->alpha();
$v->required('user.last_name')->lengthBetween(2, 50)->alpha();
$v->required('newsletter')->bool();

$result = $v->validate([
    'user' => [
        'first_name' => 'John',
        'last_name' => 'D',
    ],
    'newsletter' => true,
]);

$result->isValid(); // bool(false).
$result->getMessages();
/**
 * array(1) {
 *     ["user.last_name"]=> array(1) {
 *         ["Length::TOO_SHORT"]=> string(53) "last_name is too short and must be 2 characters long."
 *     }
 * }
 */
```

## Functional features

* Validate an array of data
* Get an array of error messages
* Overwrite the default error messages on rules, or error messages on specific values
* Get the validated values of an array
* Validate different contexts (insert, update, etc.) inheriting validations of the default context
* [A large set of default validation rules](http://validator.particle-php.com/en/latest/rules/)
* Ability to extend the validator to add your own custom rules

## Non functional features

* Easy to write (IDE auto-completion for easy development)
* Easy to read (improves peer review)
* Ability to separate controller and view logic
* Fully documented: [validator.particle-php.com](http://validator.particle-php.com)
* Fully tested: [Scrutinizer](https://scrutinizer-ci.com/g/particle-php/Validator/)
* Zero dependencies

===

Find more information and advanced usage examples at [validator.particle-php.com](http://validator.particle-php.com)
