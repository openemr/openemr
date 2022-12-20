OpenEMR Module Composer Plugin installer that installs either Custom or Zend modules into the correct directory path using composer for an OpenEMR codebase.

Developers who wish to use composer to install their plugins can include in their composer.json the following composer types

For Custom Modules

```
/** composer.json ***/
{
    "name": "openemr/SomeCustomModule",
    "type": "openemr-module",
    "require": {
        "openemr/oe-module-installer-plugin": "^0.1.0"
    }
}
```
The above module will install in the *interface/modules/custom_modules/SomeCustomModule/* directory

For Zend Framework Modules

```
/** composer.json ***/
{
    "name": "openemr/SomeCustomModule",
    "type": "openemr-zend-module",
    "require": {
        "openemr/oe-module-installer-plugin": "^0.1.0"
    }
}
```
The above module will install in the *interface/modules/zend/modules/SomeCustomModule/* directory
