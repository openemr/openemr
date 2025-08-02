# OpenEMR Crypto Strategy System

This directory contains OpenEMR's install-time encryption strategy selection system, which allows for flexible encryption implementations while maintaining data integrity and preventing runtime strategy changes.

## Overview

The crypto strategy system is built around the [Strategy Pattern](https://refactoring.guru/design-patterns/strategy) with **install-time selection** to ensure data accessibility and prevent configuration drift.

### Core Components

- **`CryptoGen`** - Main encryption facade that loads strategy from database
- **`EncryptionStrategyInterface`** - Contract that all strategies must implement (extends `Serializable`)
- **`CryptoGenStrategy`** - Default AES-256-CBC + HMAC-SHA384 implementation
- **`NullEncryptionStrategy`** - Identity function (no encryption)
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

All encryption strategies must implement `EncryptionStrategyInterface` and `Serializable`:

```php
interface EncryptionStrategyInterface extends \Serializable
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
4. **Serialization** - Selected strategy is serialized and stored in `globals.encryption_strategy_serialized`
5. **Runtime Loading** - `CryptoGen` loads and deserializes strategy from database

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

    // Required serialization methods
    public function serialize(): string
    {
        return serialize(['version' => $this->version]);
    }

    public function unserialize(string $data): void
    {
        $unserialized = unserialize($data);
        $this->version = $unserialized['version'] ?? '001';
    }

    // Modern PHP serialization methods
    public function __serialize(): array
    {
        return ['version' => $this->version];
    }

    public function __unserialize(array $data): void
    {
        $this->version = $data['version'] ?? '001';
    }
}
```

### Step 2: Key Implementation Requirements

1. **Handle null values** - Return null for null input in `encryptStandard`
2. **Version compatibility** - Support the `$minimumVersion` parameter
3. **Error handling** - Return `false` from `decryptStandard` on failure
4. **Unique identification** - `cryptCheckStandard` should identify your encrypted data
5. **Security** - Use cryptographically secure methods (AES, authenticated encryption, etc.)
6. **Serialization** - Implement both legacy and modern PHP serialization methods

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
3. Strategy implements `Serializable` interface
4. Strategy can be serialized and deserialized correctly

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
- **Serializing the strategy** during installation so it remains available even if modules are removed
- **Loading from database** at runtime instead of allowing dynamic selection
- **Immutable configuration** that cannot be changed through the UI or events

**Choose your encryption strategy carefully during installation. This decision cannot be easily reversed.**

## Best Practices

1. **Stick with the default** - `CryptoGenStrategy` is battle-tested and secure
2. **Test extensively** - Any custom strategy must pass comprehensive security testing
3. **Document everything** - Custom strategies need thorough documentation
4. **Test serialization** - Ensure strategies serialize/deserialize correctly
5. **Plan for the long term** - Strategy choice is permanent once data is encrypted
6. **Regular backups** - Encrypted data is only as safe as your backups

## Security Considerations

- Custom strategies should use **authenticated encryption** (encryption + authentication)
- Implement proper **key derivation** for password-based encryption
- Use **cryptographically secure random** number generation
- **Never hardcode keys** - Use proper key management
- **Validate all inputs** - Prevent injection attacks
- **Constant-time comparisons** - Prevent timing attacks
- **Secure serialization** - Don't serialize sensitive data like keys

## Debugging

To debug strategy loading:

```php
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

## Legacy Support

The system maintains backward compatibility with older encryption versions through:
- Version-aware decryption in `CryptoGenStrategy`
- Legacy methods like `aes256DecryptTwo()` and `aes256DecryptOne()`
- Automatic version detection in encrypted data

## Further Reading

- [OWASP Cryptographic Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cryptographic_Storage_Cheat_Sheet.html)
- [PHP Cryptography Documentation](https://www.php.net/manual/en/book.openssl.php)
- [Symfony Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html)
