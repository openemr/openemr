# Zend\\Config\\Writer

`Zend\Config\Writer` provides the ability to write config files from an array,
`Zend\Config\Config` instance, or any `Traversable` object. `Zend\Config\Writer`
is itself only an interface that defining the methods `toFile()` and
`toString()`.

We have five writers implementing the interface:

- `Zend\Config\Writer\Ini`
- `Zend\Config\Writer\Xml`
- `Zend\Config\Writer\PhpArray`
- `Zend\Config\Writer\Json`
- `Zend\Config\Writer\Yaml`

## Zend\\Config\\Writer\\Ini

The INI writer has two modes for rendering with regard to sections. By default, the top-level
configuration is always written into section names. By calling
`$writer->setRenderWithoutSectionsFlags(true);` all options are written into the global namespace of
the INI file and no sections are applied.

`Zend\Config\Writer\Ini` has an additional option parameter, `nestSeparator`,
which defines with which character the single nodes are separated. The default
is a single dot (`.`), such as is accepted by `Zend\Config\Reader\Ini` by
default.

When modifying or creating a `Zend\Config\Config` object, there are several
considerations to keep in mind. To create or modify a value, you simply say set
the parameter of the `Config` object via the parameter accessor (`->`). To
create a section in the root or to create a branch, just create a new array
(`$config->branch = [];`).

### Using Zend\\Config\\Writer\\Ini

Consider the following code, which creates a configuration structure:

```php
// Create the config object
$config = new Zend\Config\Config([], true);
$config->production = [];

$config->production->webhost = 'www.example.com';
$config->production->database = [];
$config->production->database->params = [];
$config->production->database->params->host = 'localhost';
$config->production->database->params->username = 'production';
$config->production->database->params->password = 'secret';
$config->production->database->params->dbname = 'dbproduction';

$writer = new Zend\Config\Writer\Ini();
echo $writer->toString($config);
```

The result of this code is the following INI string:

```ini
[production]
webhost = "www.example.com"
database.params.host = "localhost"
database.params.username = "production"
database.params.password = "secret"
database.params.dbname = "dbproduction"
```

You can use the method `toFile()` to store the INI data to a file instead.

## Zend\\Config\\Writer\\Xml

`Zend\Config\Writer\Xml` can be used to generate an XML string or file.

### Using Zend\\Config\\Writer\\Xml

Consider the following code, which creates a configuration structure:

```php
// Create the config object
$config = new Zend\Config\Config([], true);
$config->production = [];

$config->production->webhost = 'www.example.com';
$config->production->database = [];
$config->production->database->params = [];
$config->production->database->params->host = 'localhost';
$config->production->database->params->username = 'production';
$config->production->database->params->password = 'secret';
$config->production->database->params->dbname = 'dbproduction';

$writer = new Zend\Config\Writer\Xml();
echo $writer->toString($config);
```

The result of this code is the following XML string:

```markup
<?xml version="1.0" encoding="UTF-8"?>
<zend-config>
    <production>
        <webhost>www.example.com</webhost>
        <database>
            <params>
                <host>localhost</host>
                <username>production</username>
                <password>secret</password>
                <dbname>dbproduction</dbname>
            </params>
        </database>
    </production>
</zend-config>
```

You can use the method `toFile()` to store the XML data to a file.

## Zend\\Config\\Writer\\PhpArray

`Zend\Config\Writer\PhpArray` can be used to generate a PHP script that
represents and returns configuration.

### Using Zend\\Config\\Writer\\PhpArray

Consider the following code, which creates a configuration structure:

```php
// Create the config object
$config = new Zend\Config\Config([], true);
$config->production = [];

$config->production->webhost = 'www.example.com';
$config->production->database = [];
$config->production->database->params = [];
$config->production->database->params->host = 'localhost';
$config->production->database->params->username = 'production';
$config->production->database->params->password = 'secret';
$config->production->database->params->dbname = 'dbproduction';

$writer = new Zend\Config\Writer\PhpArray();
echo $writer->toString($config);
```

The result of this code is the following PHP script:

```php
<?php
return array (
  'production' =>
  array (
    'webhost' => 'www.example.com',
    'database' =>
    array (
      'params' =>
      array (
        'host' => 'localhost',
        'username' => 'production',
        'password' => 'secret',
        'dbname' => 'dbproduction',
      ),
    ),
  ),
);
```

You can use the method `toFile()` to save the PHP script to a file.

## Zend\\Config\\Writer\\Json

`Zend\Config\Writer\Json` can be used to generate a JSON representation of
configuration.

### Using Zend\\Config\\Writer\\Json

Consider the following code, which creates a configuration structure:

```php
// Create the config object
$config = new Zend\Config\Config([], true);
$config->production = [];

$config->production->webhost = 'www.example.com';
$config->production->database = [];
$config->production->database->params = [];
$config->production->database->params->host = 'localhost';
$config->production->database->params->username = 'production';
$config->production->database->params->password = 'secret';
$config->production->database->params->dbname = 'dbproduction';

$writer = new Zend\Config\Writer\Json();
echo $writer->toString($config);
```

The result of this code is the following JSON string:

```javascript
{
  "webhost": "www.example.com",
  "database": {
    "params": {
      "host": "localhost",
      "username": "production",
      "password": "secret",
      "dbname": "dbproduction"
    }
  }
}
```

You can use the method `toFile()` to save the JSON data to a file.

`Zend\Config\Writer\Json` uses the zend-json component to convert the data to
JSON.

## Zend\\Config\\Writer\\Yaml

`Zend\Config\Writer\Yaml` can be used to generate a PHP code that returns the YAML
representation of configuration. In order to use the YAML writer, we need to pass a
callback to an external PHP library, or use the
[YAML PECL extension](http://www.php.net/manual/en/book.yaml.php).

### Using Zend\\Config\\Writer\\Yaml

Consider the following code, which creates a configuration structure using the
YAML PECL extension:

```php
// Create the config object
$config = new Zend\Config\Config([], true);
$config->production = [];

$config->production->webhost = 'www.example.com';
$config->production->database = [];
$config->production->database->params = [];
$config->production->database->params->host = 'localhost';
$config->production->database->params->username = 'production';
$config->production->database->params->password = 'secret';
$config->production->database->params->dbname = 'dbproduction';

$writer = new Zend\Config\Writer\Yaml();
echo $writer->toString($config);
```

The result of this code is the following YAML string contains the following value:

```yaml
webhost: www.example.com
database:
    params:
      host:     localhost
      username: production
      password: secret
      dbname:   dbproduction
```

You can use the method `toFile()` to save the YAML data to a file.

If you want to use an external YAML writer library, pass the callback function
that will generate the YAML from the configuration when instantiating the
writer.  For instance, to use the [Spyc](http://code.google.com/p/spyc/)
library:

```php
// include the Spyc library
require_once 'path/to/spyc.php';

$writer = new Zend\Config\Writer\Yaml(['Spyc', 'YAMLDump']);
echo $writer->toString($config);
```
