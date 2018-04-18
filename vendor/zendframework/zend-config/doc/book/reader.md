# Zend\\Config\\Reader

`Zend\Config\Reader` gives you the ability to read a config file. It works with
concrete implementations for different file formats. `Zend\Config\Reader` itself
is only an interface, defining the methods `fromFile()` and `fromString()`. The
concrete implementations of this interface are:

- `Zend\Config\Reader\Ini`
- `Zend\Config\Reader\Xml`
- `Zend\Config\Reader\Json`
- `Zend\Config\Reader\Yaml`
- `Zend\Config\Reader\JavaProperties`

`fromFile()` and `fromString()` are expected to return a PHP array containing
the data from the specified configuration.

> ## Differences from ZF1
>
> The `Zend\Config\Reader` component no longer supports the following features:
>
> - Inheritance of sections.
> - Reading of specific sections.

## Zend\\Config\\Reader\\Ini

`Zend\Config\Reader\Ini` enables developers to store configuration data in a
familiar INI format, and then to read them in the application by using an array
syntax.

`Zend\Config\Reader\Ini` utilizes the [`parse_ini_file()`](http://php.net/parse_ini_file) PHP
function. Please review this documentation to be aware of its specific behaviors, which propagate to
`Zend\Config\Reader\Ini`, such as how the special values of `TRUE`, `FALSE`, "yes", "no", and
`NULL` are handled.

> ### Key Separator
>
> By default, the key separator character is the period character (`.`). This can be changed,
> however, using the `setNestSeparator()` method. For example:
>
> ```php
> $reader = new Zend\Config\Reader\Ini();
> $reader-setNestSeparator('-');
> ```

The following example illustrates basic usage of `Zend\Config\Reader\Ini` for
loading configuration data from an INI file. In this example, configuration data
for both a production system and for a staging system exists.

```ini
webhost                  = 'www.example.com'
database.adapter         = 'pdo_mysql'
database.params.host     = 'db.example.com'
database.params.username = 'dbuser'
database.params.password = 'secret'
database.params.dbname   = 'dbproduction'
```

We can use `Zend\Config\Reader\Ini` to read this INI file:

```php
$reader = new Zend\Config\Reader\Ini();
$data   = $reader->fromFile('/path/to/config.ini');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

`Zend\Config\Reader\Ini` supports a feature to include the content of a INI file
in a specific section of another INI file. For instance, suppose we have an INI
file with the database configuration:

```ini
database.adapter         = 'pdo_mysql'
database.params.host     = 'db.example.com'
database.params.username = 'dbuser'
database.params.password = 'secret'
database.params.dbname   = 'dbproduction'
```

We can include this configuration in another INI file by using the `@include`
notation:

```ini
webhost  = 'www.example.com'
@include = 'database.ini'
```

If we read this file using the component `Zend\Config\Reader\Ini`, we will obtain the same
configuration data structure as in the previous example.

The `@include = 'file-to-include.ini'` notation can be used also in a subelement
of a value. For instance we can have an INI file like the following:

```ini
adapter         = 'pdo_mysql'
params.host     = 'db.example.com'
params.username = 'dbuser'
params.password = 'secret'
params.dbname   = 'dbproduction'
```

And assign the `@include` as a subelement of the `database` value:

```ini
webhost           = 'www.example.com'
database.@include = 'database.ini'
```

## Zend\\Config\\Reader\\Xml

`Zend\Config\Reader\Xml` enables developers to provide configuration data in a
familiar XML format and consume it in the application using an array syntax.
The root element of the XML file or string is irrelevant and may be named
arbitrarily.

The following example illustrates basic usage of `Zend\Config\Reader\Xml` for loading configuration
data from an XML file. First, our XML configuration in the file `config.xml`:

```markup
<?xml version="1.0" encoding="utf-8"?>
<config>
    <webhost>www.example.com</webhost>
    <database>
        <adapter value="pdo_mysql"/>
        <params>
            <host value="db.example.com"/>
            <username value="dbuser"/>
            <password value="secret"/>
            <dbname value="dbproduction"/>
        </params>
    </database>
</config>
```

We can use the `Zend\Config\Reader\Xml` to read the XML configuration:

```php
$reader = new Zend\Config\Reader\Xml();
$data   = $reader->fromFile('/path/to/config.xml');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname']['value'];  // prints "dbproduction"
```

`Zend\Config\Reader\Xml` utilizes PHP's [XMLReader](http://php.net/xmlreader) class. Please
review its documentation to be aware of its specific behaviors, which propagate to
`Zend\Config\Reader\Xml`.

Using `Zend\Config\Reader\Xml`, we can include the content of XML files in a
specific XML element.  This is provided using the standard
[XInclude](http://www.w3.org/TR/xinclude/) functionality of XML. To use this
functionality, you must add the namespace
`xmlns:xi="http://www.w3.org/2001/XInclude"` to the XML file.

Suppose we have an XML file that contains only the database configuration:

```markup
<?xml version="1.0" encoding="utf-8"?>
<config>
    <database>
        <adapter>pdo_mysql</adapter>
        <params>
            <host>db.example.com</host>
            <username>dbuser</username>
            <password>secret</password>
            <dbname>dbproduction</dbname>
        </params>
    </database>
</config>
```

We can include this configuration in another XML file using an xinclude:

```markup
<?xml version="1.0" encoding="utf-8"?>
<config xmlns:xi="http://www.w3.org/2001/XInclude">
    <webhost>www.example.com</webhost>
    <xi:include href="database.xml"/>
</config>
```

The syntax to include an XML file in a specific element is `<xi:include
href="file-to-include.xml"/>`

## Zend\\Config\\Reader\\Json

`Zend\Config\Reader\Json` enables developers to consume configuration data in
JSON, and read it in the application by using an array syntax.

The following example illustrates a basic use of `Zend\Config\Reader\Json` for
loading configuration data from a JSON file.

Consider the following JSON configuration file:

```javascript
{
  "webhost"  : "www.example.com",
  "database" : {
    "adapter" : "pdo_mysql",
    "params"  : {
      "host"     : "db.example.com",
      "username" : "dbuser",
      "password" : "secret",
      "dbname"   : "dbproduction"
    }
  }
}
```

We can use `Zend\Config\Reader\Json` to read the file:

```php
$reader = new Zend\Config\Reader\Json();
$data   = $reader->fromFile('/path/to/config.json');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

`Zend\Config\Reader\Json` utilizes [zend-json](https://github.com/zendframework/zend-json).

Using `Zend\Config\Reader\Json`, we can include the content of a JSON file in a
specific JSON section or element. This is provided using the special syntax
`@include`. Suppose we have a JSON file that contains only the database
configuration:

```javascript
{
  "database" : {
    "adapter" : "pdo_mysql",
    "params"  : {
      "host"     : "db.example.com",
      "username" : "dbuser",
      "password" : "secret",
      "dbname"   : "dbproduction"
    }
  }
}
```

Now let's include it via another configuration file:

```javascript
{
    "webhost"  : "www.example.com",
    "@include" : "database.json"
}
```

## Zend\\Config\\Reader\\Yaml

`Zend\Config\Reader\Yaml` enables developers to consume configuration data in a
YAML format, and read them in the application by using an array syntax. In order
to use the YAML reader, we need to pass a callback to an external PHP library or
use the [YAML PECL extension](http://www.php.net/manual/en/book.yaml.php).

The following example illustrates basic usage of `Zend\Config\Reader\Yaml`,
using the YAML PECL extension.

Consider the following YAML file:

```yaml
webhost: www.example.com
database:
    adapter: pdo_mysql
    params:
      host:     db.example.com
      username: dbuser
      password: secret
      dbname:   dbproduction
```

We can use `Zend\Config\Reader\Yaml` to read this YAML file:

```php
$reader = new Zend\Config\Reader\Yaml();
$data   = $reader->fromFile('/path/to/config.yaml');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

If you want to use an external YAML reader, you must pass a callback function to
the class constructor.  For instance, if you want to use the
[Spyc](http://code.google.com/p/spyc/) library:

```php
// include the Spyc library
require_once 'path/to/spyc.php';

$reader = new Zend\Config\Reader\Yaml(['Spyc', 'YAMLLoadString']);
$data   = $reader->fromFile('/path/to/config.yaml');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database']['params']['dbname'];  // prints "dbproduction"
```

You can also instantiate `Zend\Config\Reader\Yaml` without any parameters, and
specify the YAML reader using the `setYamlDecoder()` method.

Using `Zend\Config\ReaderYaml`, we can include the content of another YAML file
in a specific YAML section or element. This is provided using the special syntax
`@include`.

Consider the following YAML file containing only database configuration:

```yaml
database:
    adapter: pdo_mysql
    params:
      host:     db.example.com
      username: dbuser
      password: secret
      dbname:   dbproduction
```

We can include this configuration in another YAML file:

```yaml
webhost:  www.example.com
@include: database.yaml
```

## Zend\\Config\\Reader\\JavaProperties

`Zend\Config\Reader\JavaProperties` enables developers to provide configuration
data in the popular JavaProperties format, and read it in the application by
using array syntax.

The following example illustrates basic usage of `Zend\Config\Reader\JavaProperties`
for loading configuration data from a JavaProperties file.

Suppose we have the following JavaProperties configuration file:

```ini
#comment
!comment
webhost:www.example.com
database.adapter:pdo_mysql
database.params.host:db.example.com
database.params.username:dbuser
database.params.password:secret
database.params.dbname:dbproduction
```

We can use `Zend\Config\Reader\JavaProperties` to read it:

```php
$reader = new Zend\Config\Reader\JavaProperties();
$data   = $reader->fromFile('/path/to/config.properties');

echo $data['webhost'];  // prints "www.example.com"
echo $data['database.params.dbname'];  // prints "dbproduction"
```
