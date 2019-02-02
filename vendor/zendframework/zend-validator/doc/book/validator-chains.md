# Validator Chains

Often, multiple validations should be applied to some value in a particular
order. The following code demonstrates a way to solve the example from the
[introduction](intro.md), where a username must be between 6 and 12 alphanumeric
characters:

```php
use Zend\I18n\Validator\Alnum;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorChain;

// Create a validator chain and add validators to it
$chain = new ValidatorChain();
$chain->attach(new StringLength(['min' => 6, 'max' => 12]));
$chain->attach(new Alnum());

// Validate the username
if ($validatorChain->isValid($username)) {
    // username passed validation
} else {
    // username failed validation; print reasons
    foreach ($validatorChain->getMessages() as $message) {
        echo "$message\n";
    }
}
```

Validators are run in the order they were added to the `ValidatorChain`. In the
above example, the username is first checked to ensure that its length is
between 6 and 12 characters, and then it is checked to ensure that it contains
only alphanumeric characters. The second validation, for alphanumeric
characters, is performed regardless of whether the first validation, for length
between 6 and 12 characters, succeeds. This means that if both validations fail,
`getMessages()` will return failure messages from both validators.

In some cases, it makes sense to have a validator *break the chain* if its
validation process fails. `ValidatorChain` supports such use cases with the
second parameter to the `attach()` method. By setting `$breakChainOnFailure` to
`true`, if the validator fails, it will short-circuit execution of the chain,
preventing subsequent validators from executing.  If the above example were
written as follows, then the alphanumeric validation would not occur if the
string length validation fails:

```php
$chain->attach(new StringLength(['min' => 6, 'max' => 12], true));
$chain->attach(new Alnum());
```

Any object that implements `Zend\Validator\ValidatorInterface` may be used in a
validator chain.

## Setting Validator Chain Order

For each validator added to the `ValidatorChain`, you can set a *priority* to
define the chain order. The default value is `1`. Higher values indicate earlier
execution, while lower values execute later; use negative values to force late
execution.

In the following example, the username is first checked to ensure that its
length is between 7 and 9 characters, and then it is checked to ensure that its
length is between 3 and 5 characters.

```php
use Zend\I18n\Validator\Alnum;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorChain;

$username = 'ABCDFE';

// Create a validator chain and add validators to it
$chain = new ValidatorChain();
$chain->attach(
    new StringLength(['min' => 3, 'max' => 5]),
    true, // break chain on failure
    1
);
$chain->attach(
    new StringLength(['min' => 7, 'max' => 9]),
    true, // break chain on failure
    2     // higher priority!
);

// Validate the username
if ($validatorChain->isValid($username)) {
    // username passed validation
    echo "Success";
} else {
    // username failed validation; print reasons
    foreach ($validatorChain->getMessages() as $message) {
        echo "$message\n";
    }
}

// This first example will display: The input is less than 7 characters long
```
