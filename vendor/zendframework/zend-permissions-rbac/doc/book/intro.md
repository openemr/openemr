# Introduction

zend-permissions-rbac provides a lightweight role-based access control (RBAC)
implementation based around PHP's `RecursiveIterator` and
`RecursiveIteratorIterator`. RBAC differs from access control lists (ACL) by
putting the emphasis on roles and their permissions rather than objects
(resources).

For the purposes of this documentation:

- an **identity** has one or more roles.
- a **role** requests access to a permission.
- a **permission** is given to a role.

Thus, RBAC has the following model:

- many to many relationship between **identities** and **roles**.
- many to many relationship between **roles** and **permissions**.
- **roles** can have a parent role.

## Roles

To create a role, extend the abstract class `Zend\Permission\Rbac\AbstractRole`
or use the default role class, `Zend\Permission\Rbac\Role`. You can instantiate
a role and add it to the RBAC container or add a role directly using the RBAC
container `addRole()` method.

## Permissions

Each role can have zero or more permissions and can be set directly to the role
or by first retrieving the role from the RBAC container. Any parent role will
inherit the permissions of their children.

## Dynamic Assertions

In certain situations simply checking a permission key for access may not be
enough. For example, assume two users, Foo and Bar, both have `article.edit`
permission. What's to stop Bar from editing Foo's articles? The answer is
dynamic assertions which allow you to specify extra runtime credentials that
must pass for access to be granted.
