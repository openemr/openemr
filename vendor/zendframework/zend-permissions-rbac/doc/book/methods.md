# Methods

## `Zend\Permissions\Rbac\AbstractIterator`

The `AbstractIterator` is used as the basis for both the primary `Rbac` class
and the `AbstractRole`.

Method signature                    | Description
----------------------------------- | -----------
`current() : RoleInterface`         | Return the current role instance.
`getChildren() : RecursiveIterator` | Returns a recursive iterator of all children of the current role.
`hasChildren() : bool`              | Indicates if the current role has children.
`key() : int`                       | Index of the current role instance.
`next() : void`                     | Advance to the next role instance.
`rewind() : void`                   | Seek to the first item in the iterator.
`valid() : bool`                    | Is the current index valid?

## `Zend\Permissions\Rbac\AbstractRole`

The `AbstractRole` provides the base functionality required by the
`RoleInterface`, and is the foundation for the `Role` class.

Method signature                               | Description
---------------------------------------------- | -----------
`addChild(string|RoleInterface $child) : void` | Add a child role to the current instance.
`addPermission(string $name) : void`           | Add a permission for the current role.
`getName() : string`                           | Retrieve the name assigned to this role.
`hasPermission(string $name) : bool`           | Does the role have the given permission?
`setParent(RoleInterface $parent) : void`      | Assign the provided role as the current role's parent.
`addParent(RoleInterface $parent) : Role`      | Add a parent role to the current instance.
`getParent() null|RoleInterface|array`         | Retrieve the current role's parent, or array of parents if more that one exists.

## `Zend\Permissions\Rbac\AssertionInterface`

Custom assertions can be provided to `Rbac::isGranted()` (see below); such
assertions are provided the `Rbac` instance on invocation.

Method signature            | Description
--------------------------- | -----------
`assert(Rbac $rbac) : bool` | Given an RBAC, determine if permission is granted.

## `Zend\Permissions\Rbac\Rbac`

`Rbac` is the object with which you will interact within your application in
order to query for permissions. It extends `AbstractIterator`.

Method signature                                                            | Description
--------------------------------------------------------------------------- | -----------
`addRole(string|RoleInterface $child, array|RoleInterface $parents = null)` | Add a role to the RBAC. If `$parents` is non-null, the `$child` is also added to any parents provided.
`getRole(string|RoleInterface $role) : RoleInterface`                       | Recursively queries the RBAC for the given role, returning it if found, and raising an exception otherwise.
`hasRole(string|RoleInterface $role) : bool`                                | Recursively queries the RBAC for the given role, returning `true` if found, `false` otherwise.
`getCreateMissingRoles() : bool`                                            | Retrieve the flag that determines whether or not `$parent` roles are added automatically if not present when calling `addRole()`.
`setCreateMissingRoles(bool $flag) : void`                                  | Set the flag that determines whether or not `$parent` roles are added automatically if not present when calling `addRole()`.
`isGranted(string|RoleInterface $role, string $permission, $assert = null)` | Determine if the role has the given permission. If `$assert` is provided and either an `AssertInterface` instance or callable, it will be queried before checking against the given role.

## `Zend\Permissions\Rbac\Role`

`Role` inherits from `AbstractRole` and `AbstractIterator`.

Method signature                   | Description
---------------------------------- | -----------
`__construct(string $name) : void` | Create a new instance with the provided name.
