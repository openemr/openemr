# Doctrine Migrations

## CreateTableTrait

Use `CreateTableTrait` for migrations that create tables. It sets appropriate charset/collation and generates platform-specific SQL.

```php
final class Version20260000000001 extends AbstractMigration
{
    use CreateTableTrait;

    public function up(Schema $schema): void
    {
        $table = Table::editor()
            ->setUnquotedName('foos')
            ->addColumn(
                Column::editor()
                    ->setUnquotedName('id')
                    ->setTypeName(Types::INTEGER)
                    ->setDefaultValue(0)
                    ->create()
            )
            // ...
            ->create();
        $table = $this->addPrimaryKey($table, 'id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE foos');
    }
}
```

### Why not use `$schema->createTable()`?

The standard Doctrine approach requires schema introspection to diff current vs desired state.
This adds overhead and complexity, and will cause migrations to get steadily slower (minutes vs milliseconds) as more exist.

### Methods

- `createTable(Table $table)` - Sets charset/collation options (keeping the table's other options) and adds the CREATE TABLE SQL
- `addPrimaryKey(Table $table, string $column, string ...$otherColumns): Table` - Returns the table with the primary key constraint added; the table given is not changed, so assign the result

Tables are built and changed through their editors (`Table::editor()`, `Table::edit()`, `Column::editor()`): DBAL 4.5 deprecates the `Table` mutators such as `addColumn()` and `addOption()`.
