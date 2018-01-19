# Query Builder

## Index

- [Selects](#selects)
- [Wheres](#wheres)
- [Or Wheres](#or-wheres)
- [Dynamic Wheres](#dynamic-wheres)
- [Raw Filters](#raw-filters)
- [Sorting](#sorting)
- [Pagination](#paginating)
- [Scopes](#scopes)
- [Base DN](#base-dn)
- [Search Options](#search-options)

The Adldap2 query builder makes building LDAP queries feel effortless. Let's get started.

## Opening a Query

To open a search query, call the `search()` method on your provider instance:

```php
$search = $provider->search();
```

Or you can chain all your methods if you'd prefer:

```php
$results = $provider->search()->where('cn', '=', 'John Doe')->get();
```

## Selects

#### Selecting attributes

Selecting only the LDAP attributes you need will increase the speed of your queries.

```php
// Passing in an array of attributes
$search->select(['cn', 'samaccountname', 'telephone', 'mail']);

// Passing in each attribute as an argument
$search->select('cn', 'samaccountname', 'telephone', 'mail');
```

#### Finding a specific record

If you're trying to find a single record, but not sure what the record might be, use the `find()` method:

```php
$record = $search->find('John Doe');

if ($record) {
    // Record was found!    
} else {
    // Hmm, looks like we couldn't find anything...
}
```

> **Note**: Using the `find()` method will search for LDAP records using ANR (ambiguous name resolution).
> For a more fine-tuned search, use the `findBy()` method below.

##### Finding a specific record (or failing)

If you'd like to try and find a single record and throw an exception when it hasn't been
found, use the `findOrFail()` method:

```php
try {
    $record = $search->findOrFail('John Doe');
} catch (\Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

#### Finding a specific record by a specific attribute

If you're looking for a single record with a specific attribute, use the `findBy()` method:

```php
// We're looking for a record with the 'samaccountname' of 'jdoe'.
$record = $search->findBy('samaccountname', 'jdoe');
```

##### Finding a specific record by a specific attribute (or failing)

If you'd like to try and find a single record by a specific attribute and throw
an exception when it hasn't been found, use the `findByOrFail()` method:

```php
try {
    $record = $search->findByOrFail('samaccountname', 'jdoe');
} catch (\Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

#### Finding a specific record by its distinguished name

If you're looking for a single record with a specific DN, use the `findByDn()` method:

```php
$record = $search->findByDn('cn=John Doe,dc=corp,dc=org');
```

###### Finding a specific record by its distinguished name (or failing)

If you'd like to try and find a single record by a specific DN and throw
an exception when it hasn't been found, use the `findByDnOrFail()` method:

```php
try {
    $record = $search->findByDnOrFail('cn=John Doe,dc=corp,dc=org');
} catch (\Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

#### Retrieving results

To get the results from a search, simply call the `get()` method:

```php
$results = $search->select(['cn', 'samaccountname'])->get();
```

##### Retrieving all LDAP records

To get all records from LDAP, call the `all()` method:

```php
$results = $search->all();
```

##### Retrieving the first record

To retrieve the first record of a search, call the `first()` method:

```php
$record = $search->first();
```

###### Retrieving the first record (or failing)

To retrieve the first record of a search or throw an exception when one isn't found, call the `firstOrFail()` method:

```php
try {
    $record = $search->firstOrFail();
} catch (\Adldap\Models\ModelNotFoundException $e) {
    // Record wasn't found!
}
```

## Wheres

> **Tips**:
> Fields are case insensitive, so it doesn't matter if you use `->where('CN', '*')` or `->where('cn', '*')`,
> they would return the same result.
> 
> It's also good to know that all values inserted into a where, or an orWhere method,
> <b>are escaped</b> by default into a hex string, so you don't need to worry about escaping them. For example:
>
>
>```php
>// Returns '(cn=\2f\25\70\6f\73\73\69\62\6c\79\2d\68\61\72\6d\66\75\6c\25\5c\5e\5c\2f\2f)'
>$query = $provider->search()->where('cn', '=', '/%possibly-harmful%\^\//')->getQuery();
>```

To perform a where clause on the search object, use the `where()` function:

```php
$search->where('cn', '=', 'John Doe');
```

This query would look for a record with the common name of 'John Doe' and return the results.

We can also perform a 'where equals' without including the operator:

```php
$search->whereEquals('cn', 'John Doe');
```

Or we can supply an array of key - value pairs to quickly add multiple wheres:

```php
$wheres = [
    'cn' => 'John Doe',
    'samaccountname' => 'jdoe',
];

$search->where($wheres);
```

#### Where Starts With

We could also perform a search for all objects beginning with the common name of 'John' using the `starts_with` operator:

```php
$results = $provider->search()->where('cn', 'starts_with', 'John')->get();

// Or use the method whereStartsWith($attribute, $value)

$results = $provider->search()->whereStartsWith('cn', 'John')->get();
```

#### Where Ends With
    
We can also search for all objects that end with the common name of `Doe` using the `ends_with` operator:

```php
$results = $provider->search()->where('cn', 'ends_with', 'Doe')->get();

// Or use the method whereEndsWith($attribute, $value)

$results = $provider->search()->whereEndsWith('cn', 'Doe')->get();
```

#### Where Contains

We can also search for all objects with a common name that contains `John Doe` using the `contains` operator:

```php
$results = $provider->search()->where('cn', 'contains', 'John Doe')->get();

// Or use the method whereContains($attribute, $value)

$results = $provider->search()->whereContains('cn', 'John Doe')->get();
```

##### Where Not Contains

You can use a 'where not contains' to perform the inverse of a 'where contains':

```php
$results = $provider->search()->where('cn', 'not_contains', 'John Doe')->get();

// Or use the method whereNotContains($attribute, $value)

$results = $provider->search()->whereNotContains('cn', 'John Doe');
```

#### Where Has

Or we can retrieve all objects that have a common name attribute using the wildcard operator (`*`):

```php
$results = $provider->search()->where('cn', '*')->get();

// Or use the method whereHas($field)
$results = $provider->search()->whereHas('cn')->get();
```

This type of filter syntax allows you to clearly see what your searching for.

##### Where Not Has

You can use a 'where not has' to perform the inverse of a 'where has':

```php
$results = $provider->search->where('cn', '!*')->get();

// Or use the method whereNotHas($field)
$results = $provider->search()->whereNotHas($field)->get();
```

## Or Wheres

To perform an 'or where' clause on the search object, use the `orWhere()` function. However, please be aware this
function performs differently than it would on a database. For example:

```php
$results = $search
            ->where('cn', '=', 'John Doe')
            ->orWhere('cn' '=', 'Suzy Doe')
            ->get();
```
    
This query would return no results, because we're already defining that the common name (`cn`) must equal `John Doe`. Applying
the `orWhere()` does not amount to 'Look for an object with the common name as "John Doe" OR "Suzy Doe"'. This query would
actually amount to 'Look for an object with the common name that <b>equals</b> "John Doe" OR "Suzy Doe"

To solve the above problem, we would use `orWhere()` for both fields. For example:

```php
$results = $search
        ->orWhere('cn', '=', 'John Doe')
        ->orWhere('cn' '=', 'Suzy Doe')
        ->get();
```

Now, we'll retrieve both John and Suzy's AD records, because the common name can equal either.

> **Note**: You can also use all `where` methods as an or where, for example:
`orWhereHas()`, `orWhereContains()`, `orWhereStartsWith()`, `orWhereEndsWith()`

## Dynamic Wheres

> **Note**: This feature was introduced in `v6.0.16`.

To perform a dynamic where, simply suffix a `where` with the field you're looking for.

This feature was directly ported from Laravel's Eloquent.

Here's an example:

```php
// This query:
$result = $search->where('cn', '=', 'John Doe')->first();

// Can be converted to:
$result = $search->whereCn('John Doe')->first();
```

You can perform this on **any** attribute:

```php
$result = $search->whereTelephonenumber('555-555-5555')->first();
```

You can also chain them:

```php
$result = $search
    ->whereTelephonenumber('555-555-5555')
    ->whereGivenname('John Doe')
    ->whereSn('Doe')
    ->first();
```

You can even perform multiple dynamic wheres by separating your fields by an `And`:

```php
// This would perform a search for a user with the
// first name of 'John' and last name of 'Doe'.
$result = $search->whereGivennameAndSn('John', 'Doe')->first();
```

## Raw Filters

> **Note**: Raw filters are not escaped. Do not accept user input into the raw filter method.

Sometimes you might just want to add a raw filter without using the query builder.
You can do so by using the `rawFilter()` method:

```php
$filter = '(samaccountname=jdoe)';

$results = $search->rawFilter($filter)->get();

// Or use an array
$filters = [
    '(samaccountname=jdoe)',
    '(surname=Doe)',
];

$results = $search->rawFilter($filters)->get();

// Or use multiple arguments
$results = $search->rawFilter($filters[0], $filters[1])->get();
```

## Sorting

Sorting is really useful when your displaying tabular AD results. You can
easily perform sorts on any AD attribute by using the `sortBy()` method:

```php
$results = $search->whereHas('cn')->sortBy('cn', 'asc')->get();
```

You can also sort paginated results:

```php
$results = $search->whereHas('cn')->sortBy('cn', 'asc')->paginate(25);
```

## Paginating

Paginating your search results will allow you to return more results than your AD cap
(usually 1000) and display your results in pages.

To perform this, call the `paginate()` method instead of the `get()` method:

```php
$recordsPerPage = 50;
$currentPage = $_GET['page'];

// This would retrieve all records from AD inside a new Adldap\Objects\Paginator instance.
$paginator = $search->paginate($recordsPerPage, $currentPage);

// Returns total number of pages, int
$paginator->getPages();

// Returns current page number, int
$paginator->getCurrentPage();

// Returns the amount of entries allowed per page, int
$paginator->getPerPage();

// Returns all of the results in the entire paginated result
$paginator->getResults();

// Returns the total amount of retrieved entries, int
$paginator->count();

// Iterate over the results like normal
foreach($paginator as $result)
{
    echo $result->getCommonName();
}
```

## Scopes

Search scopes allow you to easily retrieve common models of a particular 'group'. Here is how you utilize them:

```php
// Retrieve all users.
$results = $search->users()->get();

// Retrieve all printers.
$results = $search->printers()->get();

// Retrieve all organizational units.
$results = $search->ous()->get();

// Retrieve all groups.
$results = $search->groups()->get();

// Retrieve all containers.
$results = $search->containers()->get();

// Retrieve all contacts.
$results = $search->contacts()->get();

// Retrieve all computers.
$results = $search->computers()->get();
```

## Base DN

To set the base DN of your search you can use one of two methods:

```php
// Using the `in()` method:
$results = $provider->search()
    ->in('ou=Accounting,dc=acme,dc=org')
    ->get();
    
// Using the `setDn()` method:
$results = $provider->search()
    ->setDn('ou=Accounting,dc=acme,dc=org')
    ->get();
```

Either option will return the same results. Use which ever method you prefer to be more readable.

## Search Options

#### Recursive

By default, all searches performed are recursive. If you'd like to disable recursive search, use the `recursive()` method:

```php
$result = $provider->search()->recursive(false)->all();
```
    
This would perform an `ldap_listing()` instead of an `ldap_search()`.

#### Read

If you'd like to perform a read instead of a listing or a recursive search, use the `read()` method:

```php
$result = $provider->search()->read(true)->where('objectClass', '*')->get();
```

This would perform an `ldap_read()` instead of an `ldap_listing()` or an `ldap_search()`.

#### Raw

If you'd like to retrieve the raw LDAP results, use the `raw()` method:

```php
$rawResults = $provider->search()->raw()->where('cn', '=', 'John Doe')->get();

var_dump($rawResults); // Returns an array
```

## Retrieving the ran query

If you'd like to retrieve the current query to save or run it at another time, use the `getQuery()` method
on the search instance, then on the query builder itself:

```php
$query = $provider->search()->where('cn', '=', 'John Doe')->getQuery();

$filter = $query->getQuery();

echo $filter; // Returns '(cn=\4a\6f\68\6e\20\44\6f\65)'
```

