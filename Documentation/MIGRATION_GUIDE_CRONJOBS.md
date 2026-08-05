# Migration Guide: From `library/allow_cronjobs.php` to `bin/console`

`library/allow_cronjobs.php` and the two legacy scripts it bootstrapped have been removed. The same functionality is now provided by Symfony console commands dispatched through `bin/console`.

This guide is for operators who schedule OpenEMR cron jobs, and for developers with custom integrations that invoked the old scripts.

## Why Migrate?

1. **No superglobal shim.** The old entry points required `library/allow_cronjobs.php` to stuff CLI arguments into `$_REQUEST`/`$_GET`/`$_POST` so that scripts written for HTTP contexts could run from cron. The new commands take real CLI arguments.
2. **Standard argument parsing.** Options are declared via Symfony `InputOption`, so `--help`, `--quiet`, `--no-interaction`, etc. behave consistently.
3. **Safer defaults.** Commands fail fast with a clear message when required globals are missing, instead of silently short-circuiting.
4. **Testable.** The commands have isolated unit tests; the legacy scripts had none.

## Command Mapping

| Legacy script                                         | Replacement command                                 |
|-------------------------------------------------------|-----------------------------------------------------|
| `interface/batchcom/batch_phone_notification.php`     | `php bin/console notifications:phone`               |
| `custom/zutil.cli.doc_import.php`                     | `php bin/console documents:import`                  |

Both commands accept the standard `--site=<site>` option (defaults to `default`).

## Updating Your Crontab

### Before

```cron
0 * * * *  /usr/bin/php -f /var/www/html/openemr/interface/batchcom/batch_phone_notification.php site=default
30 * * * * /usr/bin/php -f /var/www/html/openemr/custom/zutil.cli.doc_import.php site=default
```

### After

```cron
0 * * * *  /usr/bin/php /var/www/html/openemr/bin/console --site=default notifications:phone
30 * * * * /usr/bin/php /var/www/html/openemr/bin/console --site=default documents:import
```

Notes:

- `bin/console` is not invoked with `-f`; it is a regular PHP script that parses its own arguments.
- The `--site=<site>` option replaces the `site=<site>` positional argument that the legacy scripts consumed from `$_GET`.
- On multi-site installations, schedule one cron entry per site.

## `notifications:phone`

Sends hourly phone reminders for upcoming appointments via the configured Maviq gateway.

Configuration is read from the same globals as before:

- `phone_gateway_url`, `phone_gateway_username`, `phone_gateway_password` — required; the command exits with `FAILURE` if any are empty.
- `phone_notification_hour` — trigger window in hours; falls back to `72` when unset or `<= 0`.
- `phone_time_range` — time range string passed through to the gateway.
- `phone_appt_message` — array of per-facility messages. The `Default` entry is used when a facility has no override.
- `phone_reminder_log_dir` — optional directory for per-day HTML cron logs (`phone_reminder_cronlog_YYYYMMDD.html`).

The command takes no options beyond the global `bin/console` flags.

## `documents:import`

Imports files from the configured scanner directory into the document store.

The source path is read from the `scanner_output_directory` global. Unlike the legacy script, this is not overridable on the command line — set it in Administration → Globals.

Options:

| Option       | Default | Description                                                                                   |
|--------------|---------|-----------------------------------------------------------------------------------------------|
| `--pid`      | `00`    | Patient ID to assign to each imported document.                                               |
| `--category` | `1`     | Category name (url-encoded) or numeric category id.                                           |
| `--owner`    | `0`     | Owner user id to attribute the import to.                                                     |
| `--limit`    | `10`    | Maximum number of files to import per invocation.                                             |
| `--in-situ`  | *off*   | Create document records pointing at files in place instead of moving them into document store. |

Example:

```bash
php bin/console --site=default documents:import --pid=00 --category=Scans --limit=50
```

## Migrating Custom Callers

If you have custom PHP code that included `library/allow_cronjobs.php` or directly executed the two legacy scripts, invoke the new commands instead.

### Before

```php
// Custom wrapper that required allow_cronjobs.php and then included the legacy script.
$_GET['site'] = 'default';
require __DIR__ . '/library/allow_cronjobs.php';
require __DIR__ . '/interface/batchcom/batch_phone_notification.php';
```

### After

```php
// Shell out to the console command, or use the Symfony Application directly.
passthru(escapeshellcmd(PHP_BINARY)
    . ' ' . escapeshellarg(__DIR__ . '/bin/console')
    . ' --site=default notifications:phone');
```

For in-process invocation (e.g., from a test or another command), resolve the command via `SymfonyCommandRunner` and run it through a Symfony `Application`.

## Troubleshooting

- **`Phone gateway is not configured`** — one of `phone_gateway_url`, `phone_gateway_username`, `phone_gateway_password` is empty. Set them in Administration → Globals → Notifications.
- **`Global "scanner_output_directory" is not configured`** — set the scanner output directory in Administration → Globals → Miscellaneous before scheduling `documents:import`.
- **Command not listed by `bin/console list`** — ensure `composer dump-autoload` has run since the upgrade; `src/Common/Command/` commands are auto-discovered from the autoloader.

## References

- PR: [openemr/openemr#11707](https://github.com/openemr/openemr/pull/11707)
- Tracking issue: [openemr/openemr#11668](https://github.com/openemr/openemr/issues/11668)
