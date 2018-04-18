# Db\\RecordExists and Db\\NoRecordExists Validators

`Zend\Validator\Db\RecordExists` and `Zend\Validator\Db\NoRecordExists` provide
a means to test whether a record exists in a given table of a database, with a
given value.

## Supported options

The following options are supported for `Zend\Validator\Db\NoRecordExists` and
`Zend\Validator\Db\RecordExists`:

- `adapter`: The database adapter that will be used for the search.
- `exclude`: Sets records that will be excluded from the search.
- `field`: The database field within this table that will be searched for the record.
- `schema`: Sets the schema that will be used for the search.
- `table`: The table that will be searched for the record.

## Basic usage

An example of basic usage of the validators:

```php
// Check that the email address exists in the database
$validator = new Zend\Validator\Db\RecordExists([
    'table'   => 'users',
    'field'   => 'emailaddress',
    'adapter' => $dbAdapter,
]);

if ($validator->isValid($emailaddress)) {
    // email address appears to be valid
} else {
    // email address is invalid; print the reasons
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

The above will test that a given email address is in the database table. If no
record is found containing the value of `$emailaddress` in the specified column,
then an error message is displayed.

```php
// Check that the username is not present in the database
$validator = new Zend\Validator\Db\NoRecordExists([
    'table'   => 'users',
    'field'   => 'username',
    'adapter' => $dbAdapter,
]);

if ($validator->isValid($username)) {
    // username appears to be valid
} else {
    // username is invalid; print the reason
    $messages = $validator->getMessages();
    foreach ($messages as $message) {
        echo "$message\n";
    }
}
```

The above will test that a given username is *not* in the database table. If a
record is found containing the value of `$username` in the specified column,
then an error message is displayed.

## Excluding records

`Zend\Validator\Db\RecordExists` and `Zend\Validator\Db\NoRecordExists` also
provide a means to test the database, excluding a part of the table, either by
providing a `WHERE` clause as a string, or an array with the keys `field` and
`value`.

When providing an array for the exclude clause, the `!=` operator is used, so
you can check the rest of a table for a value before altering a record (for
example on a user profile form)

```php
// Check no other users have the username
$user_id   = $user->getId();
$validator = new Zend\Validator\Db\NoRecordExists([
    'table' => 'users',
    'field' => 'username',
    'exclude' => [
        'field' => 'id',
        'value' => $user_id,
    ],
]);

if ($validator->isValid($username)) {
    // username appears to be valid
} else {
    // username is invalid; print the reason
    $messages = $validator->getMessages();
    foreach ($messages as $message) {
        echo "$message\n";
    }
}
```

The above example will check the table to ensure no records other than the one
where `id = $user_id` contains the value `$username`.

You can also provide a string to the exclude clause so you can use an operator
other than `!=`. This can be useful for testing against composite keys.

```php
$email     = 'user@example.com';
$clause    = $dbAdapter->quoteIdentifier('email') . ' = ' . $dbAdapter->quoteValue($email);
$validator = new Zend\Validator\Db\RecordExists([
    'table'   => 'users',
    'field'   => 'username',
    'adapter' => $dbAdapter,
    'exclude' => $clause,
]);

if ($validator->isValid($username)) {
    // username appears to be valid
} else {
    // username is invalid; print the reason
    $messages = $validator->getMessages();
    foreach ($messages as $message) {
        echo "$message\n";
    }
}
```

The above example will check the `users` table to ensure that only a record with
both the username `$username` and with the email `$email` is valid.

## Database Schemas

You can specify a schema within your database for adapters such as PostgreSQL
and DB/2 by supplying an array with `table` and `schema` keys, as demonstrated
below:

```php
$validator = new Zend\Validator\Db\RecordExists([
    'table'  => 'users',
    'schema' => 'my',
    'field'  => 'id',
]);
```

## Using a Select object

It is also possible to supply the validators with a `Zend\Db\Sql\Select` object
in place of options. The validator then uses this object instead of building its
own. This allows for greater flexibility with selection of records used for
validation.

```php
use Zend\Db\Sql\Select;
use Zend\Validator\Db\RecordExists;

$select = new Select();
$select
    ->from('users')
    ->where->equalTo('id', $user_id)
    ->where->equalTo('email', $email);

$validator = new RecordExists($select);

// We still need to set our database adapter
$validator->setAdapter($dbAdapter);

// Validation is then performed as usual
if ($validator->isValid($username)) {
    // username appears to be valid
} else {
    // username is invalid; print the reason
    $messages = $validator->getMessages();
    foreach ($messages as $message) {
        echo "$message\n";
    }
}
```

The above example will check the `users` table to ensure that only a record with
both the username `$username` and with the email `$email` is valid.
