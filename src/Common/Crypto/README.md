# OpenEMR Crypto Strategy System

This directory contains OpenEMR's install-time encryption strategy selection system, which allows for flexible encryption implementations while maintaining data integrity and preventing runtime strategy changes.

## Motivation

OpenEMR ships with robust internal encryption (CryptoGenStrategy) for HIPAA compliance, but deployment needs vary. Cloud providers often provide HIPAA-compliant encryption at the infrastructure level (disk encryption, database encryption, network encryption), which may reduce the need for application-layer encryption complexity and performance overhead.

The strategy pattern ensures consistent behavior: while OpenEMR has multiple encryption disable flags for different subsystems (filesystem, CouchDB, etc.), modules may not respect these flags consistently. The crypto strategy system guarantees that all encryption calls—even direct CryptoGen usage—follow the selected approach, whether that's full encryption, cloud-managed encryption via the Null strategy, or custom implementations.

## Overview

The crypto strategy system is built around the [Strategy Pattern](https://refactoring.guru/design-patterns/strategy) with **install-time selection** to ensure data accessibility and prevent configuration drift.

### Core Components

- **`CryptoGen`** - Main encryption facade that loads strategy from database
- **`EncryptionStrategyInterface`** - Contract that all strategies must implement
- **`CryptoGenStrategy`** - Default AES-256-CBC + HMAC-SHA384 implementation for on-premises HIPAA compliance
- **`NullEncryptionStrategy`** - Pass-through strategy for cloud deployments with infrastructure-level HIPAA encryption
- **`EncryptionStrategySelector`** - Tool for strategy discovery and selection during installation
- **`EncryptionStrategyRegistrationEvent`** - Event for modules to register available strategies
- **`CryptoGenException`** - Exception for critical crypto errors

## How the Strategy System Works

### Install-Time Strategy Selection

The encryption strategy is selected **once during installation** and stored in the database. This ensures:

1. **Data accessibility** - Strategy remains available even if modules are removed
2. **Configuration immutability** - No runtime strategy changes that could cause data loss
3. **Consistency** - All encryption uses the same strategy throughout the system

### Strategy Loading Priority

When `CryptoGen` is instantiated, it loads the strategy in this order:

1. **Database-stored strategy** (from installation)
2. Default `CryptoGenStrategy` (fallback if no strategy stored)

### Strategy Interface

All encryption strategies must implement `EncryptionStrategyInterface`:

```php
interface EncryptionStrategyInterface
{
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive');

    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string;

    public function cryptCheckStandard(?string $value): bool;
}
```

## Installation Integration

The strategy system integrates with OpenEMR's installation process to allow strategy selection during setup.

### Installation Flow

1. **Strategy Discovery** - `EncryptionStrategySelector` dispatches `EncryptionStrategyRegistrationEvent::STRATEGY_REGISTRATION`
2. **Module Registration** - Modules can register available strategies via event listeners
3. **Strategy Selection** - User/installer selects strategy (default: `cryptogen`)
4. **Storage** - Selected strategy name is stored in `keys.encryption_strategy_name`
5. **Runtime Loading** - `CryptoGen` loads strategy by name from keys table

### Available Installation Options

- **Web Installation** - Uses default `cryptogen` strategy only (strategy selection not available)
- **CLI Installation** - Use `encryption_strategy=strategyname` parameter to select custom strategies
- **Automated Installation** - Defaults to `cryptogen` strategy

## Creating a Custom Encryption Strategy

### Step 1: Implement the Interface

```php
<?php

namespace YourModule\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

class CustomEncryptionStrategy implements EncryptionStrategyInterface
{
    private string $version = '001';

    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        // Your encryption implementation
        if ($value === null) return null;

        // Implement your encryption logic here
        return $encryptedValue;
    }

    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        // Your decryption implementation
        if (empty($value)) return "";

        // Implement your decryption logic here
        return $decryptedValue;
    }

    public function cryptCheckStandard(?string $value): bool
    {
        // Check if value was encrypted with your strategy
        return !empty($value) && str_starts_with($value, 'YOUR_PREFIX');
    }

    public function getId(): string
    {
        return 'custom_encryption';
    }

    public function getName(): string
    {
        return 'Custom Encryption Strategy';
    }

    public function getDescription(): string
    {
        return 'Advanced encryption for compliance';
    }
}
```

### Step 2: Key Implementation Requirements

1. **Handle null values** - Return null for null input in `encryptStandard`
2. **Version compatibility** - Support the `$minimumVersion` parameter
3. **Error handling** - Return `false` from `decryptStandard` on failure
4. **Unique identification** - `cryptCheckStandard` should identify your encrypted data
5. **Security** - Use cryptographically secure methods (AES, authenticated encryption, etc.)
6. **Strategy identification** - Implement `getId()`, `getName()`, and `getDescription()` methods

## Registering Your Strategy for Installation

### Step 3: Register Strategy During Installation

```php
<?php

use OpenEMR\Events\Crypto\EncryptionStrategyRegistrationEvent;
use YourModule\Crypto\CustomEncryptionStrategy;

// In your module's event listener registration
$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();

$eventDispatcher->addListener(
    EncryptionStrategyRegistrationEvent::STRATEGY_REGISTRATION,
    function (EncryptionStrategyRegistrationEvent $event) {
        $event->registerStrategy(
            'custom_encryption',                    // Unique strategy ID
            'Custom Encryption Strategy',           // Human-readable name
            'Advanced encryption for compliance',   // Description
            new CustomEncryptionStrategy()          // Strategy instance
        );
    }
);
```

### Installation Usage Examples

#### Web Installation

The web installer uses the default `cryptogen` strategy and does not provide strategy selection options. If you need a custom encryption strategy, you must use the CLI installer.

#### CLI Installation with Custom Strategy

```bash
# Install with custom strategy
php -f InstallerAuto.php encryption_strategy=custom_encryption

# Install with default strategy (cryptogen)
php -f InstallerAuto.php

# Install with no encryption
php -f InstallerAuto.php encryption_strategy=null
```

**Note:** Custom encryption strategies can only be selected during CLI installation. The web installer automatically uses the default `cryptogen` strategy.

#### Web Installation

#### Web Installation

The web installer uses the default `cryptogen` strategy and does not provide strategy selection options. If you need a custom encryption strategy, you must use the CLI installer.

During web installation, your custom strategy will appear in the encryption strategy selection dropdown with the name and description you provided.

### Strategy Validation

The system validates that:
1. Strategy ID is unique
2. Strategy implements `EncryptionStrategyInterface`
3. Strategy provides proper identification methods (`getId()`, `getName()`, `getDescription()`)
4. Strategy can encrypt/decrypt data correctly

## ⚠️ CRITICAL WARNING: Install-Time Strategy Selection

### 🚨 STRATEGY CANNOT BE CHANGED AFTER INSTALLATION 🚨

**The encryption strategy is selected once during installation and cannot be changed afterward without data loss.**

### Why Strategy Changes Are Prevented

1. **Data integrity** - Existing encrypted data cannot be decrypted with a different strategy
2. **No automatic migration** - The system doesn't automatically re-encrypt existing data
3. **Silent failures** - Applications may fail to decrypt critical patient data
4. **Compliance violations** - Loss of encrypted PHI can violate HIPAA and other regulations

### Install-Time Protection

The new system prevents strategy changes by:
- **Storing strategy name** in the keys table during installation so it remains available even if modules are removed
- **Loading from keys table** at runtime instead of allowing dynamic selection
- **Immutable configuration** that cannot be changed through the UI or events

**Choose your encryption strategy carefully during installation. This decision cannot be easily reversed.**

## Default Strategy: CryptoGenStrategy

CryptoGenStrategy is OpenEMR's default encryption implementation, providing healthcare-compliant security designed to protect patient health information (PHI) and meet regulatory requirements.

### Technical Specifications

- **Algorithm**: AES-256-CBC (Advanced Encryption Standard, 256-bit key, Cipher Block Chaining)
- **Authentication**: HMAC-SHA384 (Hash-based Message Authentication Code with SHA-384)
- **Key Derivation** (password-based): PBKDF2 (100,000 iterations) + HKDF
- **Random Generation**: Cryptographically secure random bytes for IVs and salts
- **Version Support**: Backward compatibility with 6 encryption versions (001-006)

### Key Management Architecture

CryptoGenStrategy uses a hybrid key storage approach combining filesystem and database storage:

#### **Filesystem Keys** (Default: `keySource = 'drive'`)
- **Location**: `$GLOBALS['OE_SITE_DIR']/documents/logs_and_misc/methods/`
- **Format**: Base64-encoded for older versions (001-004), encrypted for newer versions (005-006)
- **Security**: Newer versions encrypt filesystem keys using database keys for enhanced protection
- **File Names**: `{version}{subkey}` (e.g., `sixa`, `sixb` for encryption and HMAC keys)

#### **Database Keys** (Alternative: `keySource = 'database'`)
- **Location**: `keys` table with `name` column as identifier
- **Format**: Base64-encoded 32-byte random keys
- **Auto-generation**: Creates keys automatically if missing
- **Cache**: In-memory caching prevents repeated database queries

### Key Version Evolution

| Version | Year | Key Storage | Key Protection | HMAC Algorithm |
|---------|------|-------------|----------------|----------------|
| 001-002 | Early | Filesystem | Plain base64 | SHA-256 |
| 003-004 | Mid | Filesystem | Plain base64 | SHA-256 |
| 005-006 | Current | Filesystem + Database | Encrypted with DB keys | SHA-384 |

### Security Features

#### **Authenticated Encryption**
- Every encrypted value includes HMAC for tamper detection
- HMAC calculated over IV + encrypted data to prevent manipulation
- Constant-time comparison prevents timing attacks

#### **Password-Based Encryption**
- PBKDF2 with 100,000 iterations for slow key derivation
- HKDF for domain separation (separate encryption and HMAC keys)
- Unique salt per encryption prevents rainbow table attacks

#### **Version Compatibility**
- Automatic detection of encryption version from data prefix
- Graceful decryption of legacy encrypted data
- Minimum version enforcement for security policies

### Pros and Cons

#### **✅ Advantages**

1. **Healthcare-Compliant Security**
   - AES-256 meets HIPAA encryption requirements and NIST standards
   - HMAC-SHA384 ensures data integrity for patient records
   - Designed for healthcare privacy regulations and audit requirements

2. **Hybrid Key Storage**
   - Filesystem storage survives database corruption
   - Database storage enables centralized key management
   - Newer versions encrypt filesystem keys for defense-in-depth

3. **Backward Compatibility**
   - Supports 6 encryption versions for smooth upgrades
   - Legacy data remains accessible after system updates
   - Gradual migration path for security improvements

4. **Performance Optimized**
   - Key caching reduces database/filesystem access
   - Efficient OpenSSL integration
   - Minimal computational overhead

5. **Flexible Usage**
   - Standard key-based encryption for system data
   - Custom password-based encryption for user data
   - Configurable key sources (drive/database)

#### **⚠️ Considerations**

1. **Key Management Complexity**
   - Multiple key storage locations require careful backup
   - Lost filesystem keys render data unrecoverable
   - Key rotation requires manual intervention

2. **Filesystem Dependencies**
   - Default mode requires filesystem write access
   - Docker/container deployments need persistent volumes
   - File permissions must be properly secured

3. **Database Key Limitations**
   - Database-only keys eliminate filesystem backup advantage
   - Requires database connectivity for all encryption operations
   - No automatic key escrow or recovery mechanisms

4. **Legacy Version Support**
   - Older versions use weaker HMAC (SHA-256 vs SHA-384)
   - Cannot force upgrade of existing encrypted data
   - Security improvements limited by backward compatibility

### When to Use CryptoGenStrategy

**✅ Recommended for:**
- Standard healthcare organizations and clinics
- On-premises deployments with file system access
- Environments requiring HIPAA compliance with proven encryption
- Organizations needing backward compatibility with existing data
- Mixed key storage requirements for operational flexibility

**❌ Consider alternatives for:**
- Cloud-native deployments preferring HSM/KMS integration
- Organizations with enhanced privacy requirements beyond HIPAA
- Specialized compliance needs (e.g., international privacy laws)
- Deployments requiring automatic key rotation policies

## Best Practices

1. **Stick with the default** - `CryptoGenStrategy` is healthcare-tested and HIPAA-compliant
2. **Test extensively** - Any custom strategy must pass comprehensive security testing
3. **Document everything** - Custom strategies need thorough documentation
4. **Test thoroughly** - Ensure strategies encrypt/decrypt correctly across all scenarios
5. **Plan for the long term** - Strategy choice is permanent once data is encrypted
6. **Regular backups** - Encrypted data is only as safe as your backups

## Security Considerations

- Custom strategies should use **authenticated encryption** (encryption + authentication)
- Implement proper **key derivation** for password-based encryption
- Use **cryptographically secure random** number generation
- **Never hardcode keys** - Use proper key management
- **Validate all inputs** - Prevent injection attacks
- **Constant-time comparisons** - Prevent timing attacks
- **Stateless design** - Don't store sensitive data in strategy instances

## Debugging

To debug strategy loading:

```php
// Check what strategy name is stored in keys table
$strategyName = CryptoGen::getEncryptionStrategyName();
error_log("Stored strategy name: " . ($strategyName ?? 'null'));

// Check what strategy is loaded from database
$cryptoGen = new CryptoGen();
error_log("Loaded strategy: " . get_class($cryptoGen->getEncryptionStrategy()));

// Check available strategies during installation
$selector = new EncryptionStrategySelector();
$strategies = $selector->getAvailableStrategies();
foreach ($strategies as $id => $strategy) {
    error_log("Available strategy: {$id} - {$strategy['name']}");
}
```

## Diagnostics

The encryption strategy information is displayed in the Admin->System->Diagnostics page, showing:
- Strategy name stored in the keys table
- Implementation class being used
- Any errors encountered during strategy loading

## Legacy Support

The system maintains backward compatibility with older encryption versions through:
- Version-aware decryption in `CryptoGenStrategy`
- Legacy methods like `aes256DecryptTwo()` and `aes256DecryptOne()`
- Automatic version detection in encrypted data

## Further Reading

- [OWASP Cryptographic Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cryptographic_Storage_Cheat_Sheet.html)
- [PHP Cryptography Documentation](https://www.php.net/manual/en/book.openssl.php)
- [Symfony Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html)
