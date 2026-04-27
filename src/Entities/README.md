# Doctrine ORM Entities

Transitional Usage Guidelines

## Definition

Properties will automatically be converted from `camelCase` (PHP) to `snake_case` (DB).

```php
<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping;

#[Mapping\Entity]
#[Mapping\Table(name: 'foo_bars')]
class FooBar
{
    #[Mapping\Column(type: Types::BIGINT)]
    #[Mapping\Id]
    #[Mapping\GeneratedValue]
    public readonly string $id;

    #[Mapping\Column(name: 'date_created', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Attributes\CreatedAt]
    public readonly DateTimeImmutable $createdAt;

    #[Mapping\Column(name: 'last_updated')]
    #[Attributes\UpdatedAt]
    public DateTimeImmutable $updatedAt;
}
```

### Common Fields & Conventions

All ORM models MUST have a primary key; most SHOULD have columns for creation and last update time.
Some may also use optimistic version locking.

Not all of these are the same in the database.
Stick with the following PHP-side names; remap if needed:

- `public readonly string $id`: primary key. Try to use `string` even if the underlying column is an `int`. You don't do math on ids.
- `public readonly DateTimeImmutable $createdAt` When the row was created
- `public private(set) DateTimeImmutable $updatedAt` When the record was last saved[^avis]

[^avis]: This syntax is only supported in PHP 8.4+. Leave updatedAt private unless something needs to read it externally. Only Doctrine itself should ever write to it.


### Columns

Don't blindly add all columns that exist when adapting a table.
Only map the ones that are needed to deal with the task at hand.

Rationale:
There are probably columns no longer needed or in use.
When all of a model/table's data goes through the ORM, it should be safe to delete those now-unneeded columns.

### Relations

Don't set up ORM relations at all yet.
Limit to mapping the relational column as its native type (`public ?string $fooId = null;`, etc).
For now, it's just an "OM" :)

Rationale:
It's easy to create performance pitfalls (N+1 queries, etc) with automatic joins and proxied data loading.
We may walk this back eventually if it becomes overly burdensome, but more explicit loading with a couple of small tools makes it easy to prevent problems.
