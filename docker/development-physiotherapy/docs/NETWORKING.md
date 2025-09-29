# OpenEMR Docker Network Configuration

This document outlines the networking setup for the OpenEMR hybrid development environment.

## Network Architecture

### Docker Network
- **Network Name**: `openemr_network`
- **Subnet**: `172.20.0.0/16`
- **Driver**: `bridge`

### Service IP Addresses

| Service | Container Name | IP Address | Ports |
|---------|----------------|------------|-------|
| MariaDB | `openemr_mariadb_dev` | `172.20.0.10` | 3306:3306 |
| phpMyAdmin | `openemr_phpmyadmin_dev` | `172.20.0.11` | 8080:80 |
| Redis | `openemr_redis_dev` | `172.20.0.12` | 6379:6379 |
| MailHog | `openemr_mailhog_dev` | `172.20.0.13` | 1025:1025, 8025:8025 |
| Adminer | `openemr_adminer_dev` | `172.20.0.14` | 8081:8080 |

## Local PHP Integration

### Database Connection
Local PHP applications should connect to:
```php
$config = [
    'host' => 'localhost',    // or 127.0.0.1
    'port' => 3306,
    'database' => 'openemr',
    'username' => 'openemr',
    'password' => 'openemr'
];
```

### Redis Connection
```php
$redis_config = [
    'host' => 'localhost',
    'port' => 6379,
    'password' => 'openemr_redis'
];
```

### Email (MailHog) Connection
```php
$email_config = [
    'smtp_host' => 'localhost',
    'smtp_port' => 1025,
    'smtp_auth' => false
];
```

## Firewall Considerations

### macOS
No additional firewall configuration required. Docker Desktop handles port forwarding automatically.

### Linux
Ensure the following ports are accessible:
- 3306 (MariaDB)
- 6379 (Redis) 
- 8080 (phpMyAdmin)
- 8081 (Adminer)
- 1025 (MailHog SMTP)
- 8025 (MailHog Web UI)

### Windows
Docker Desktop with WSL2 handles networking automatically.

## Service Discovery

### From Host to Container
Use `localhost` or `127.0.0.1` with the mapped ports:
- `localhost:3306` → MariaDB
- `localhost:6379` → Redis
- `localhost:8080` → phpMyAdmin

### Container to Container
Use service names or fixed IP addresses:
- `mariadb:3306` or `172.20.0.10:3306`
- `redis:6379` or `172.20.0.12:6379`

### From Container to Host
Use `host.docker.internal` to reach host services:
- `host.docker.internal:80` → Host web server
- `host.docker.internal:443` → Host HTTPS

## Network Troubleshooting

### Check Network Status
```bash
docker network ls
docker network inspect openemr-physiotherapy_openemr_network
```

### Test Container Connectivity
```bash
# Test from host to container
telnet localhost 3306

# Test from container to container
docker exec openemr_phpmyadmin_dev ping mariadb

# Check container IP addresses
docker exec openemr_mariadb_dev ip addr show
```

### Port Conflicts
If ports are already in use, modify `.env` file:
```env
DB_PORT=3307
PHPMYADMIN_PORT=8090
REDIS_PORT=6380
```

## Security Considerations

### Development Environment
- Services bind to `0.0.0.0` for development convenience
- Default passwords are used (change for production)
- No TLS/SSL encryption (add for production)

### Production Deployment
- Use internal networks only
- Implement proper authentication
- Enable SSL/TLS encryption
- Use secrets management
- Restrict port access

## Performance Optimization

### Network Performance
- Use fixed IP addresses for faster resolution
- Enable connection pooling in applications
- Monitor network latency with `docker stats`

### Database Connections
- Configure connection pooling
- Use persistent connections where appropriate
- Monitor connection counts

## Vietnamese Locale Support

### Character Encoding
- All services configured for UTF-8mb4
- MariaDB uses `utf8mb4_vietnamese_ci` collation
- Redis handles UTF-8 natively
- Web interfaces support Vietnamese characters

### Timezone
- All services set to `Asia/Ho_Chi_Minh`
- Database and application timestamps synchronized
- Consistent date/time formatting across services

## Example Configurations

### PHP Database Connection
```php
try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=openemr;charset=utf8mb4',
        'openemr',
        'openemr',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Set Vietnamese collation
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_vietnamese_ci");
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
}
```

### Redis Session Configuration
```php
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://localhost:6379?auth=openemr_redis&database=0');
ini_set('session.gc_maxlifetime', 3600);
```

### Email Testing with MailHog
```php
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'localhost';
$mail->Port = 1025;
$mail->SMTPAuth = false;

// Send test email - view at http://localhost:8025
```