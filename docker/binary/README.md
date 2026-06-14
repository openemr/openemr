# OpenEMR Binary Static Build Docker Image

This Docker image builds a production-ready OpenEMR container using **static PHP binaries** from [openemr-static-binary-forge](https://github.com/Jmevorach/openemr-static-binary-forge). Unlike the standard containers that install PHP packages, this container uses statically compiled PHP binaries that include all necessary extensions.

## Key Differences from Standard Containers

- **Static PHP Binaries**: Uses pre-compiled static PHP binaries (php-fpm, php-cli) instead of Alpine PHP packages
- **OpenEMR from Binary Package**: Uses the packaged OpenEMR binary/PHAR instead of building from source
- **PHP-FPM Required**: Explicitly starts PHP-FPM as a separate process (Apache proxies to it via FastCGI)
- **No XDebug Support**: XDebug is not available with static binaries and is disabled
- **Smaller Image Size**: Potentially smaller image size due to not installing PHP packages and dependencies

## Architecture

The container runs:
1. **PHP-FPM**: Processes PHP requests (listens on 127.0.0.1:9000)
2. **Apache**: Web server that proxies PHP requests to PHP-FPM via FastCGI

## Building the Image

```bash
docker build -t openemr-binary:latest .
```

### Build Arguments

- `OPENEMR_VERSION`: OpenEMR version (default: `7_0_4`)
- `BINARY_RELEASE_DATE`: Release date for binary package (default: `12282025`)
- `PHP_VERSION`: PHP version used in binaries (default: `8.5`)
- `ALPINE_VERSION`: Alpine Linux version (default: `3.22`)

Example:
```bash
docker build \
  --build-arg OPENEMR_VERSION=7_0_4 \
  --build-arg BINARY_RELEASE_DATE=12292025 \
  -t openemr-binary:latest .
```

## Usage

### Basic Usage with docker-compose

```yaml
version: '3.1'
services:
  mysql:
    restart: always
    image: mariadb:11.8
    command: ['mariadbd','--character-set-server=utf8mb4']
    volumes:
    - databasevolume:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
  openemr:
    restart: always
    image: openemr-binary:latest
    ports:
    - 80:80
    - 443:443
    volumes:
    - logvolume01:/var/log
    - sitevolume:/var/www/localhost/htdocs/openemr/sites
    environment:
      MYSQL_HOST: mysql
      MYSQL_ROOT_PASS: root
      MYSQL_USER: openemr
      MYSQL_PASS: openemr
      OE_USER: admin
      OE_PASS: pass
    depends_on:
    - mysql
volumes:
  logvolume01: {}
  sitevolume: {}
  databasevolume: {}
```

### Environment Variables

See the main [OpenEMR Docker documentation](../README.md) for a complete list of environment variables. The binary container supports the same environment variables as the standard containers.

## Limitations

- **XDebug Not Supported**: XDebug cannot be used with static PHP binaries
- **Architecture Support**: The container supports multiple architectures (amd64, arm64) and automatically detects the build architecture. Binary releases must be available for the target architecture in the [openemr-static-binary-forge](https://github.com/Jmevorach/openemr-static-binary-forge) repository.
- **PHP Version Fixed**: The PHP version is determined by the binary release (currently PHP 8.5)

## Technical Details

### Static Binary Location

- PHP-FPM: `/usr/local/bin/php-fpm` (used for FastCGI processing)
- PHP CLI: `/usr/local/bin/php` (used for command-line scripts)

### Configuration Files

- PHP Configuration: `/usr/local/etc/php/php.ini`
- PHP-FPM Configuration: `/usr/local/etc/php-fpm.conf`
- PHP-FPM Pool Configuration: `/usr/local/etc/php-fpm.d/www.conf`
- Apache Configuration: `/etc/apache2/conf.d/openemr.conf`

### Process Management

- PHP-FPM is started as a background process before Apache
- Apache runs as the main process (PID 1) via `exec`
- PHP-FPM listens on `127.0.0.1:9000` for FastCGI requests

## Comparison with Standard Containers

| Feature | Standard Container (7.0.5) | Binary Container |
|---------|---------------------------|------------------|
| PHP Installation | Alpine PHP packages | Static binaries |
| Image Size | Larger (includes PHP packages) | Smaller (static binaries) |
| PHP-FPM | System service | Explicitly started |
| XDebug Support | Yes | No |
| Build Time | Longer (builds from source) | Shorter (uses pre-built binaries) |
| Architecture | Multi-arch support | Multi-arch support (amd64, arm64) |

## Performance Benchmarks

Benchmark results comparing the binary container against the standard 7.0.4 container (OpenEMR 7.0.4):

### Startup Time
- **Binary Container**: 14.6 seconds
- **Standard Container (7.0.4)**: 37.0 seconds
- **Result**: Binary container starts **2.5x faster**

### Request Handling Performance
- **Binary Container**: 163.47 requests/second, 61.175ms average response time
- **Standard Container (7.0.4)**: 165.30 requests/second, 60.496ms average response time
- **Result**: Very similar performance (difference < 2 req/s, negligible)

### Resource Utilization
- **Binary Container**:
  - CPU: 0.509% average
  - Memory: 139.9MB average, 145.4MB peak
- **Standard Container (7.0.4)**:
  - CPU: 0.776% average
  - Memory: 330.4MB average, 368MB peak
- **Result**: Binary container uses **57.7% less memory** and **~34% less CPU**

### Summary
The binary container provides significant improvements in startup time and resource efficiency while maintaining comparable runtime performance. This makes it particularly well-suited for environments where fast container startup times and lower resource consumption are priorities.

*Benchmark conducted: December 29, 2025 (1000 requests, 10 concurrent connections)*

## Benefits for High-Load Production Sites

The PHP-FPM + static binary approach provides several advantages for OpenEMR deployments under heavy load:

### Process Isolation and Resource Management
- **Separate PHP Process Pool**: PHP-FPM maintains a pool of worker processes that are isolated from the web server, allowing better resource allocation and isolation
- **Dynamic Process Scaling**: PHP-FPM can automatically scale the number of worker processes based on load (configurable via `pm.max_children`, `pm.start_servers`, etc.)
- **Process Recycling**: PHP-FPM can recycle worker processes after a certain number of requests, preventing memory leaks from accumulating
- **Better Stability**: If a PHP process crashes, it doesn't bring down the web server, and PHP-FPM automatically spawns a replacement

### Memory Efficiency
- **Shared Nothing Architecture**: Each PHP-FPM worker process is independent, allowing better memory management and garbage collection
- **Lower Memory Footprint**: As demonstrated in benchmarks, the binary container uses significantly less memory (~58% less), allowing more concurrent users per server
- **Predictable Memory Usage**: With proper PHP-FPM pool configuration, memory usage is more predictable and easier to monitor

### Performance Under Load
- **Concurrent Request Handling**: PHP-FPM handles multiple concurrent PHP requests more efficiently than traditional mod_php
- **Optimized Static Binaries**: Statically compiled PHP binaries are optimized at compile time, potentially offering better performance than dynamically loaded extensions
- **Reduced Overhead**: No dynamic extension loading overhead at runtime, as all extensions are compiled into the binary

### Scalability
- **Horizontal Scaling**: Lower memory usage means you can run more container instances on the same hardware
- **Faster Container Startup**: 2.5x faster startup time enables faster auto-scaling response in container orchestration environments (Kubernetes, Docker Swarm, etc.)
- **Resource Density**: More efficient resource utilization allows for higher container density per host

### Production Reliability
- **Separation of Concerns**: Web server (Apache) and application runtime (PHP-FPM) are decoupled, allowing independent optimization and troubleshooting
- **Better Monitoring**: PHP-FPM provides status pages and metrics that can be monitored separately from Apache
- **Graceful Degradation**: PHP-FPM can be configured with maximum request limits to prevent resource exhaustion

### Configuration Recommendations for High Load

For production deployments expecting heavy traffic, consider adjusting PHP-FPM pool settings in `/usr/local/etc/php-fpm.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50          # Adjust based on available memory
pm.start_servers = 10         # Start with more workers
pm.min_spare_servers = 5      # Keep more idle workers ready
pm.max_spare_servers = 15     # Allow more spare workers
pm.max_requests = 1000        # Recycle workers to prevent memory leaks
```

These settings should be tuned based on your server's available memory, CPU cores, and expected concurrent user load.

## Source

This container uses binaries from the [openemr-static-binary-forge](https://github.com/Jmevorach/openemr-static-binary-forge) project, which provides statically compiled PHP binaries for OpenEMR.

## Support

For issues related to:
- **Container setup/configuration**: See the main [OpenEMR Docker documentation](../README.md)
- **Static binaries**: See the [openemr-static-binary-forge](https://github.com/Jmevorach/openemr-static-binary-forge) repository
- **OpenEMR application**: See the [OpenEMR wiki](https://www.open-emr.org/wiki) and [forums](https://community.open-emr.org/)
