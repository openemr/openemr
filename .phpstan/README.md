# PHPStan Rules for OpenEMR

PHPStan rules are provided by the [`opencoreemr/openemr-phpstan-rules`](https://packagist.org/packages/opencoreemr/openemr-phpstan-rules) package.

See the [package documentation](https://github.com/opencoreemr/openemr-phpstan-rules) for:
- Rule descriptions and rationale
- Code examples (before/after)
- Configuration options

## Migration Guides

- [Migrating from $GLOBALS to OEGlobalsBag](MIGRATION_GUIDE.md)
- [Migrating from curl to GuzzleHttp](MIGRATION_GUIDE_CURL.md)

## Running PHPStan

```bash
composer phpstan
```

## Baseline

Existing violations are recorded in `baseline/` and won't cause errors. New code must follow the rules.
