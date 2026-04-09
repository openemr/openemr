# Storage Abstraction Layer

This module provides a unified interface for filesystem operations across OpenEMR, built on [Flysystem](https://flysystem.thephpleague.com/).

## Goals

1. **Fail loudly** — Flysystem throws exceptions on failure, eliminating sentinel-value bugs (`file_put_contents` returning `false` vs `0`)
2. **Centralized paths** — Eliminate scattered `OE_SITE_DIR` concatenation throughout the codebase
3. **Testability** — Code using `FilesystemOperator` can be tested with in-memory adapters
4. **Future extensibility** — Enable remote storage (S3, etc.) via configuration without code changes

## Design

### Location enum

Semantic identifiers for storage areas. Each case knows its default path relative to `OE_SITE_DIR`:

```php
enum Location
{
    case Documents;     // Patient documents (encrypted)
    case Billing;       // EDI/ERA claim files
    case Certificates;  // OAuth keys, TLS certs
    case SiteConfig;    // Custom menus, logos
    
    public function getDefaultPath(): string
    {
        return match ($this) {
            self::Documents => 'documents',
            self::Billing => 'documents/edi',
            self::Certificates => 'documents/certificates',
            self::SiteConfig => 'documents/custom_menus',
        };
    }
}
```

### Manager

Provides `FilesystemOperator` instances per location. Lazy-initialized and cached:

```php
class Manager
{
    public function __construct(
        private readonly string $siteDir,
    ) {}

    public function getStorage(Location $location): FilesystemOperator;
}
```

Usage:

```php
$storage = $manager->getStorage(Location::Documents);
$storage->write('patient-123/report.pdf', $content);
$content = $storage->read('patient-123/report.pdf');
```

## Migration Strategy

### Phase 1: Infrastructure (current)

- Stub out `Manager` and `Location`
- Wire into DI container
- Document design intent

### Phase 2: First adoption

Pick a self-contained call site to validate the pattern:

- `BillingLogger` — single file read/write, clear boundaries
- `PatientMenuRole` / `MainMenuRole` — simple JSON reads

### Phase 3: Document storage

Migrate `Document.class.php` — the primary user-facing storage:

- Introduce `DocumentStorageInterface` with `store()`, `retrieve()`, `delete()`
- Implement using `Manager::getStorage(Location::Documents)`
- Encryption stays at the service layer (encrypt before write, decrypt after read)
- Maintain `file://` URL format in database for backwards compatibility

### Phase 4: Broader adoption

- EDI/ERA file operations
- QRDA report generation
- Certificate management

### Out of scope

These should NOT use this abstraction:

- **Temp files** (`sys_get_temp_dir()`) — ephemeral, local-only, often need native paths for external tools
- **Source code reads** — templates, config files shipped with the application

## Future: Custom Configuration

The design supports future configuration of locations and drivers without code changes:

```php
// Hypothetical future config
return [
    Location::Documents => [
        'driver' => 's3',
        'bucket' => 'openemr-documents',
        'prefix' => 'patients/',
    ],
    Location::Certificates => [
        'driver' => 'local',
        'path' => '/etc/openemr/certs',  // outside web root
    ],
];
```

The `Manager` would read this config and construct appropriate adapters. Until then, defaults point to current locations for backwards compatibility.

## Related

- GitHub issue: openemr/openemr#11505
- Flysystem docs: https://flysystem.thephpleague.com/docs/
