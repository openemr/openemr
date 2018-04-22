# Identical Validator

`Zend\Validator\Identical` allows you to validate if a given value is identical
with a set token.

## Supported options

The following options are supported for `Zend\Validator\Identical`:

- `strict`: Defines if the validation should be strict (i.e., if types should
  also be considered). The default value is `true`.
- `token`: Sets the token against which the input will be validated.
- `literal`: If set to `true`, the validation will skip the lookup for elements
  in the form context, and validate the token just the way it was provided. The
  default value is `false`.

## Basic usage

To validate if two values are identical, you need to set the original value as
the token, as demonstrated in the following example:
token.

```php
$valid = new Zend\Validator\Identical('origin');

if ($valid->isValid($value)) {
    return true;
}
```

The validation will only then return `true` when both values are 100% identical.
In our example, when `$value` is `'origin'`.

You can set the token after instantiation by using the method `setToken()`.

## Identical objects

`Zend\Validator\Identical` can validate not only strings, but any other variable
type, such as booleans, integers, floats, arrays, or even objects. As already
noted, the token and value must be identical.

```php
$valid = new Zend\Validator\Identical(123);

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

> ### Type comparison
>
> You should be aware of the variable type used for validation. This means that
> the string `'3'` is not identical to integer `3`. When you want non-strict
> validation, you must set the `strict` option to `false`.

## Form elements

`Zend\Validator\Identical` supports the comparison of form elements. This can be
done by using the element's name as the `token`:

```php
$form->add([
    'name' => 'elementOne',
    'type' => 'Password',
]);
$form->add([
    'name'       => 'elementTwo',
    'type'       => 'Password',
    'validators' => [
        [
            'name'    => 'Identical',
            'options' => [
                'token' => 'elementOne',
            ],
        ],
    ],
]);
```

By using the element's name from the first element as the `token` for the second
element, the validator validates if the second element is equal with the first
element. In the case your user does not enter two identical values, you will get
a validation error.

### Validating a Value From a Fieldset

Sometimes you will need to validate an input that lives inside a fieldset, and
this can be accomplished as follows:

```php
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

$userFieldset = new Fieldset('user'); // (1)
$userFieldset->add([
    'name' => 'email', // (2)
    'type' => 'Email',
]);

// Let's add one fieldset inside the 'user' fieldset,
// so we can see how to manage the token in a different deepness
$deeperFieldset = new Fieldset('deeperFieldset'); // (3)
$deeperFieldset->add([
    'name'    => 'deeperFieldsetInput', // (4)
    'type'    => 'Text',
    'options' => [
        'label' => 'What validator are we testing?',
    ],
]);
$userFieldset->add($deeperFieldset);

$signUpForm = new Form('signUp');
$signUpForm->add($userFieldset);

// Add an input that will validate the 'email' input from 'user' fieldset
$signUpForm->add([
    'name' => 'confirmEmail', // (5)
    'type' => 'Email',
]);

// Add an input that will validate the 'deeperFieldsetInput' from
// 'deeperFieldset' that lives inside the 'user' fieldset
$signUpForm->add([
    'name' => 'confirmTestingValidator', // (6)
    'type' => 'Text',
]);

// This will ensure the user enter the same email in 'email' (2) and
// 'confirmEmail' (5)
$inputFilter = new InputFilter();
$inputFilter->add([
    'name' => 'confirmEmail', // references (5)
    'validators' => [
        [
            'name' => 'Identical',
            'options' => [
                // 'user' key references 'user' fieldset (1), and 'email'
                // references 'email' element inside 'user' fieldset (2)
                'token' => ['user' => 'email'],
            ],
        ],
    ],
]);

// This will ensure the user enter the same string in 'deeperFieldsetInput' (4)
// and 'confirmTestingValidator' (6)
$inputFilter->add([
    'name' => 'confirmTestingValidator', // references (6)
    'validators' => [
        [
            'name' => 'Identical',
            'options' => [
                'token' => [
                    'user' => [ // references 'user' fieldset (1)
                        // 'deeperFieldset' key references 'deeperFieldset'
                        // fieldset (3); 'deeperFieldsetInput' references
                        // 'deeperFieldsetInput' element (4)
                        'deeperFieldset' => 'deeperFieldsetInput',
                    ],
                ],
            ],
        ],
    ],
]);

$signUpForm->setInputFilter($inputFilter);
```

> #### Use one token per leaf
>
> Aways make sure that your token array have just one key per level all the way
> till the leaf, otherwise you can end up with unexpected results.

## Strict validation

As mentioned before, `Zend\Validator\Identical` validates tokens using strict
typing. You can change this behaviour by using the `strict` option. The default
value for this property is `true`.

```php
$valid = new Zend\Validator\Identical(['token' => 123, 'strict' => false]);
$input = '123';
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

The difference to the previous example is that the validation returns in this
case `true`, even if you compare a integer with string value as long as the
content is identical but not the type.

For convenience you can also use `setStrict()` and `getStrict()`.

## Configuration

As all other validators, `Zend\Validator\Identical` also supports the usage of
configuration settings during instantiation. This means that you can configure
this validator with a `Traversable` object.

There is a case which you should be aware of. If you are using an array as
token, and it contains a `token` key, you should wrap it within another
`token` key. See the examples below to undestand this situation.

```php
// This will not validate ['token' => 123], it will actually validate the
// integer 123
$valid = new Zend\Validator\Identical(['token' => 123]);
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

The reason for this special case is that you can configure the token which has
to be used by giving the `token` key.

So, when you are using an array as the token, and it contains one element with a
`token` key, then you have to wrap it as shown in the example below:

```php
// Unlike the previous example, this will validate ['token' => 123]
$valid = new Zend\Validator\Identical(['token' => ['token' => 123]]);
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

If the array you are willing to validate does not have a `token` key, you do not
need to wrap it.
