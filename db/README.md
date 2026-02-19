# Database Migrations

This tooling is built on top of [`doctrine/migrations`](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/introduction.html#introduction).

The tool's built-in help system (passing `--help` to any of the commands) is ground truth.
Use `vendor/bin/doctrine-migrations list` to show all available commands.

We are in the process of overhauling CLI and module-related tooling, so this covers the current state of things.

> [!IMPORTANT]
> The Doctrine Migrations system is NOT fully integrated into OpenEMR yet.
> Don't make database changes using this until [#10708](https://github.com/openemr/openemr/issues/10708) is completed (at minimum).

## Creating a Migration Script

Run `vendor/bin/doctrine-migrations migration:generate`.
Edit the file it creates.

Migrations will automatically have a timestamp applied to the file name, and will be run in sequential order.
Be aware of the order of operations.

Aim to limit any given migration to a single table if possible.
Multiple, smaller migrations are easier to test and review.
Exercise good judgment when to make mode widespread changes within a single migration.

## Applying Migrations

> [!WARNING]
> This will result in schema changes to your database.
> Certain changes may take a long time to execute, which could lead to service interruptions.

Run `vendor/bin/doctrine-migrations migrate`, and follow the prompts.

### Reverting a Migration

> [!CAUTION]
> Reverting (rolling back) a migration is _highly likely_ to lead to data loss!

Under some circumstances, you may need to roll back a migration.
This is _mostly_ used during development where you are iterating on the final schema changes, and should be very rare in production environments.

Run `vendor/bin/doctrine-migrations migrate prev` to run the "down" path of the most recent migration.

> [!NOTE]
> Not all migrations will support a roll back procedure.

To continue rolling back changes, you may run the same command more than one time.
The CLI also supports migrating to a specific version; see its built-in help system for details.

## Changing a Migration

Once a migration script has made it into a tagged release, **it may not be altered again**[^change].
Doing so will lead to different installations having inconsistent schemas, which makes management and development _extremely_ error-prone.

Make changes with a new migration that runs on top of the released migration to "roll forward" to the desired state.

[^change]: Minor syntactical adjustments are OK as long as the produced schema does not change. Usually updating class imports due to a refactor, etc. This should still be fairly rare.
