# Refining Access Controls

## Precise Access Controls

The basic ACL as defined in the \[previous section\](zend.permissions.acl.introduction) shows how
various privileges may be allowed upon the entire ACL (all resources). In practice, however,
access controls tend to have exceptions and varying degrees of complexity.
`Zend\Permissions\Acl\Acl` allows to you accomplish these refinements in a straightforward and
flexible manner.

For the example CMS, it has been determined that whilst the 'staff' group covers the needs of the
vast majority of users, there is a need for a new 'marketing' group that requires access to the
newsletter and latest news in the CMS. The group is fairly self-sufficient and will have the
ability to publish and archive both newsletters and the latest news.

In addition, it has also been requested that the 'staff' group be allowed to view news stories but
not to revise the latest news. Finally, it should be impossible for anyone (administrators included)
to archive any 'announcement' news stories since they only have a lifespan of 1-2 days.

First we revise the role registry to reflect these changes. We have determined that the 'marketing'
group has the same basic permissions as 'staff', so we define 'marketing' in such a way that it
inherits permissions from 'staff':

```php
// The new marketing group inherits permissions from staff
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

$acl = new Acl();

$acl->addRole(new Role('marketing'), 'staff');
```

Next, note that the above access controls refer to specific resources (e.g., "newsletter", "latest
news", "announcement news"). Now we add these resources:

```php
// Create Resources for the rules

// newsletter
$acl->addResource(new Resource('newsletter'));

// news
$acl->addResource(new Resource('news'));

// latest news
$acl->addResource(new Resource('latest'), 'news');

// announcement news
$acl->addResource(new Resource('announcement'), 'news');
```

Then it is simply a matter of defining these more specific rules on the target areas of the ACL:

```php
// Marketing must be able to publish and archive newsletters and the
// latest news
$acl->allow(
    'marketing',
    ['newsletter', 'latest'],
    ['publish', 'archive']
);

// Staff (and marketing, by inheritance), are denied permission to
// revise the latest news
$acl->deny('staff', 'latest', 'revise');

// Everyone (including administrators) are denied permission to
// archive news announcements
$acl->deny(null, 'announcement', 'archive');
```

We can now query the ACL with respect to the latest changes:

```php
echo $acl->isAllowed('staff', 'newsletter', 'publish')
    ? 'allowed'
    : 'denied';
// denied

echo $acl->isAllowed('marketing', 'newsletter', 'publish')
    ? 'allowed'
    : 'denied';
// allowed

echo $acl->isAllowed('staff', 'latest', 'publish')
    ? 'allowed'
    : 'denied';
// denied

echo $acl->isAllowed('marketing', 'latest', 'publish')
    ? 'allowed'
    : 'denied';
// allowed

echo $acl->isAllowed('marketing', 'latest', 'archive')
    ? 'allowed'
    : 'denied';
// allowed

echo $acl->isAllowed('marketing', 'latest', 'revise')
    ? 'allowed'
    : 'denied';
// denied

echo $acl->isAllowed('editor', 'announcement', 'archive')
    ? 'allowed'
    : 'denied';
// denied

echo $acl->isAllowed('administrator', 'announcement', 'archive')
    ? 'allowed'
    : 'denied';
// denied
```

## Removing Access Controls

To remove one or more access rules from the ACL, simply use the available `removeAllow()` or
`removeDeny()` methods. As with `allow()` and `deny()`, you may provide a `NULL` value to indicate
application to all roles, resources, and/or privileges:

```php
// Remove the denial of revising latest news to staff (and marketing,
// by inheritance)
$acl->removeDeny('staff', 'latest', 'revise');

echo $acl->isAllowed('marketing', 'latest', 'revise')
    ? 'allowed'
    : 'denied';
// allowed

// Remove the allowance of publishing and archiving newsletters to
// marketing
$acl->removeAllow(
    'marketing',
    'newsletter',
    ['publish', 'archive']
);

echo $acl->isAllowed('marketing', 'newsletter', 'publish')
    ? 'allowed'
    : 'denied';
// denied

echo $acl->isAllowed('marketing', 'newsletter', 'archive')
    ? 'allowed'
    : 'denied';
// denied
```

Privileges may be modified incrementally as indicated above, but a `NULL` value for the privileges
overrides such incremental changes:

```php
// Allow marketing all permissions upon the latest news
$acl->allow('marketing', 'latest');

echo $acl->isAllowed('marketing', 'latest', 'publish')
    ? 'allowed'
    : 'denied';
// allowed

echo $acl->isAllowed('marketing', 'latest', 'archive')
    ? 'allowed'
    : 'denied';
// allowed

echo $acl->isAllowed('marketing', 'latest', 'anything')
    ? 'allowed'
    : 'denied';
// allowed
```
