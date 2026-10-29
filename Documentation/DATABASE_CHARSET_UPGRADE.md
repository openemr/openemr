# Database character-set upgrade

OpenEMR installations created before 6.0.0 may still have tables or individual
text columns using `utf8`/`utf8mb3` or `latin1`. The application connects using
`utf8mb4`, but that does not change the character set of existing database
columns. Saving a character that needs four bytes (for example, some CJK
characters or emoji) in a three-byte `utf8mb3` column can silently replace it
with `?` when the database connection is not using strict SQL mode.

After a successful database upgrade, OpenEMR reports tables whose default
character set or text columns are not `utf8mb4`. This check is informational:
OpenEMR does not automatically convert those tables because `ALTER TABLE`
conversions can rebuild large tables, consume substantial disk space, and
change column types if run using a blanket `CONVERT TO CHARACTER SET`.

## Before converting a database

1. Make a complete, restorable backup of the database and OpenEMR files.
2. Schedule a maintenance window and test the conversion on a restored copy
   using the same database engine and version as production.
3. Review all affected tables and columns, including their types, nullability,
   defaults, comments, indexes, and any triggers or custom code that depend on
   them.
4. Generate and review explicit `ALTER TABLE` statements that change the table
   default and each legacy character column to `utf8mb4` while preserving the
   existing column definitions. Do not assume that `ALTER TABLE ... CONVERT TO
   CHARACTER SET utf8mb4` preserves the schema: it may widen `TEXT` types to
   preserve character capacity.
5. Verify the resulting schema against a fresh install and test writing and
   reading representative four-byte characters before repeating the reviewed
   procedure in production.

Database engines expose column defaults and generated expressions differently.
Do not use a generated migration script without confirming that it supports
your database engine and version. In particular, scripts based on MariaDB's
`information_schema.COLUMNS.COLUMN_DEFAULT` representation must not be assumed
to work unchanged on MySQL.

Converting the schema cannot restore characters that were already replaced
with `?`; those values require recovery from a source that still contains the
original text.
