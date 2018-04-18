# Identity

The `Identity` helper allows retrieving the identity from the
`AuthenticationService`.

For the `Identity` helper to work, a `Zend\Authentication\AuthenticationService`
name or alias must be defined and recognized by the `ServiceManager`.

`Identity` returns the identity discovered in the `AuthenticationService`, or
`null` if no identity is available.

## Basic Usage

```php
<?php
    if ($user = $this->identity()) {
        echo 'Logged in as ' . $this->escapeHtml($user->getUsername());
    } else {
        echo 'Not logged in';
    }
?>
```

## Using with ServiceManager

When invoked, the `Identity` plugin will look for a service by the name or alias
`Zend\Authentication\AuthenticationService` in the `ServiceManager`. You can
provide this service to the `ServiceManager` in a configuration file:

```php
// In a configuration file...
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'aliases' => [
            'my_auth_service' => AuthenticationService::class,
        ],
        'factories' => [
            AuthenticationService::class => InvokableFactory::class,
        ],
    ],
];
```
