# Advanced Usage

## Storing ACL Data for Persistence

zend-permissions-acl was designed in such a way that it does not require any
particular backend technology such as a database or cache server for storage of the ACL data. Its
complete PHP implementation enables customized administration tools to be built upon
`Zend\Permissions\Acl\Acl` with relative ease and flexibility. Many situations require some form of
interactive maintenance of the ACL, and `Zend\Permissions\Acl\Acl` provides methods for setting
up, and querying against, the access controls of an application.

Storage of ACL data is therefore left as a task for the developer, since use cases are expected to
vary widely for various situations. Because `Zend\Permissions\Acl\Acl` is serializable, ACL
objects may be serialized with PHP's [serialize() function](http://php.net/serialize), and the
results may be stored anywhere the developer should desire, such as a file, database, or caching
mechanism.

## Writing Conditional ACL Rules with Assertions

Sometimes a rule for allowing or denying a role access to a resource should not
be absolute, but dependent upon various criteria. For example, suppose that
certain access should be allowed, but only between the hours of 8:00am and
5:00pm. Another example would be denying access because a request comes from an
IP address that has been flagged as a source of abuse.
`Zend\Permissions\Acl\Acl` has built-in support for implementing rules based
on whatever conditions the developer needs.

`Zend\Permissions\Acl\Acl` provides support for conditional rules with
`Zend\Permissions\Acl\Assertion\AssertionInterface`. In order to use the rule assertion interface, a
developer writes a class that implements the `assert()` method of the interface:

```php
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class CleanIPAssertion implements AssertionInterface
{
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $resource = null,
        $privilege = null
    ) {
        return $this->_isCleanIP($_SERVER['REMOTE_ADDR']);
    }

    protected function _isCleanIP($ip)
    {
        // ...
    }
}
```

Once an assertion class is available, the developer must supply an instance of
the assertion class when assigning conditional rules. A rule that is created
with an assertion only applies when the assertion method returns `TRUE`.

```php
use Zend\Permissions\Acl\Acl;

$acl = new Acl();
$acl->allow(null, null, null, new CleanIPAssertion());
```

The above code creates a conditional allow rule that allows access to all
privileges on everything by everyone, except when the requesting IP is
"blacklisted". If a request comes in from an IP that is not considered "clean",
then the allow rule does not apply. Since the rule applies to all roles, all
resources, and all privileges, an "unclean" IP would result in a denial of
access. This is a special case, however, and it should be understood that in all
other cases (i.e., where a specific role, resource, or privilege is specified
for the rule), a failed assertion results in the rule not applying, and other
rules would be used to determine whether access is allowed or denied.

The `assert()` method of an assertion object is passed the ACL, role,
resource, and privilege to which the authorization query (i.e., `isAllowed()`)
applies, in order to provide a context for the assertion class to determine its
conditions where needed.
