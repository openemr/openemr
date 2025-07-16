# OpenEMR Crypto Strategy System

This directory contains OpenEMR's pluggable encryption strategy system, which allows for flexible encryption implementations while maintaining backward compatibility and data integrity.

## Overview

The crypto strategy system is built around the [Strategy Pattern](https://refactoring.guru/design-patterns/strategy) and integrates with OpenEMR's event system to allow modules to provide custom encryption implementations.

### Core Components

- **`CryptoGen`** - Main encryption facade that delegates to strategies
- **`EncryptionStrategyInterface`** - Contract that all strategies must implement
- **`CryptoGenStrategy`** - Default AES-256-CBC + HMAC-SHA384 implementation
- **`NullEncryptionStrategy`** - Identity function (no encryption)
- **`EncryptionStrategyEvent`** - Event for strategy selection
- **`CryptoGenException`** - Exception for critical crypto errors

## How the Strategy System Works

### Strategy Selection Priority

When `CryptoGen` is instantiated, strategy selection follows this priority order:

1. **Event-dispatched strategy** (highest priority)
2. Constructor parameter
3. Default `CryptoGenStrategy` (fallback)

This design allows modules to override encryption system-wide, even when code explicitly provides a strategy.

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

## Event System Integration

The strategy system integrates with OpenEMR's Symfony event dispatcher to allow dynamic strategy selection.

### Event Flow

1. `CryptoGen` constructor dispatches `EncryptionStrategyEvent::STRATEGY_SELECT`
2. Event listeners can call `$event->setStrategy($customStrategy)`
3. First strategy set wins (unless event propagation is stopped)
4. If no strategy is set via events, falls back to constructor parameter or default

### Event Priority

Event-based selection **always takes precedence** over constructor parameters. This allows:
- System administrators to enforce encryption policies
- Compliance modules to override encryption methods
- Testing frameworks to inject mock strategies

## Creating a Custom Encryption Strategy

### Step 1: Implement the Interface

```php
<?php

namespace YourModule\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

class CustomEncryptionStrategy implements EncryptionStrategyInterface
{
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
}
```

### Step 2: Key Implementation Requirements

1. **Handle null values** - Return null for null input in `encryptStandard`
2. **Version compatibility** - Support the `$minimumVersion` parameter
3. **Error handling** - Return `false` from `decryptStandard` on failure
4. **Unique identification** - `cryptCheckStandard` should identify your encrypted data
5. **Security** - Use cryptographically secure methods (AES, authenticated encryption, etc.)

## Registering Your Strategy via Events

### Method 1: Event Listener

```php
<?php

use OpenEMR\Common\Crypto\EncryptionStrategyEvent;
use YourModule\Crypto\CustomEncryptionStrategy;

// In your module's event listener registration
$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();

$eventDispatcher->addListener(
    EncryptionStrategyEvent::STRATEGY_SELECT,
    function (EncryptionStrategyEvent $event) {
        // Only set if no other strategy has been set
        if (!$event->hasStrategy()) {
            $event->setStrategy(new CustomEncryptionStrategy());
        }
    }
);
```

### Method 2: High-Priority Listener (Override Others)

```php
$eventDispatcher->addListener(
    EncryptionStrategyEvent::STRATEGY_SELECT,
    function (EncryptionStrategyEvent $event) {
        // Force your strategy (overrides others)
        $event->setStrategy(new CustomEncryptionStrategy());
        $event->stopPropagation(); // Prevent other listeners
    },
    100 // High priority
);
```

### Method 3: Conditional Strategy

```php
$eventDispatcher->addListener(
    EncryptionStrategyEvent::STRATEGY_SELECT,
    function (EncryptionStrategyEvent $event) {
        // Only use custom strategy under certain conditions
        if ($GLOBALS['enable_custom_crypto'] ?? false) {
            $event->setStrategy(new CustomEncryptionStrategy());
        }
    }
);
```

## ⚠️ CRITICAL WARNING: Changing Encryption Strategies

### 🚨 CATASTROPHIC DATA LOSS RISK 🚨

**Changing the global encryption strategy after encrypted data exists can cause PERMANENT DATA LOSS.**

### Why Strategy Changes Are Dangerous

1. **Existing encrypted data becomes unreadable** - Data encrypted with Strategy A cannot be decrypted by Strategy B
2. **No automatic migration** - The system doesn't automatically re-encrypt existing data
3. **Silent failures** - Applications may fail to decrypt critical patient data
4. **Compliance violations** - Loss of encrypted PHI can violate HIPAA and other regulations

**This is an extremely serious problem that requires careful planning and expert consultation. Do not attempt to change encryption strategies without fully understanding the implications and having a comprehensive data migration plan. Do not install modules that introduce a new encryption strategy into an install that already has data!**

## Best Practices

1. **Stick with the default** - `CryptoGenStrategy` is battle-tested and secure
2. **Test extensively** - Any custom strategy must pass comprehensive security testing
3. **Document everything** - Custom strategies need thorough documentation
4. **Monitor for failures** - Log and alert on decryption failures
5. **Plan for migration** - Have a strategy change plan before you need it
6. **Regular backups** - Encrypted data is only as safe as your backups

## Security Considerations

- Custom strategies should use **authenticated encryption** (encryption + authentication)
- Implement proper **key derivation** for password-based encryption
- Use **cryptographically secure random** number generation
- **Never hardcode keys** - Use proper key management
- **Validate all inputs** - Prevent injection attacks
- **Constant-time comparisons** - Prevent timing attacks

## Debugging

To debug strategy selection:

```php
// Add logging to see which strategy is selected
$cryptoGen = new CryptoGen();
error_log("Selected strategy: " . get_class($cryptoGen->getEncryptionStrategy()));
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