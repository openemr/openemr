# OpenEMR Official Docker Image

The docker image is maintained at https://hub.docker.com/r/openemr/openemr/
(see there for more details)

## Performance Optimizations

This image includes significant performance optimizations that reduce startup time by **~5x** and memory usage by **~70%** compared to previous builds.

### Benchmark Results (November 2025)

| Metric | Optimized Image | Previous Build | Improvement |
|--------|-----------------|----------------|-------------|
| **Startup Time** | 15.0s | 73.1s | **4.9x faster** |
| **Memory (Average)** | 92.8 MB | 304.2 MB | **69% reduction** |
| **Memory (Peak)** | 117.1 MB | 326.6 MB | **64% reduction** |
| **Performance** | 114.9 req/s | 117.2 req/s | Equivalent |

*Measured using Docker's internal timestamps (`State.StartedAt` → first healthy `State.Health.Log`)*

### Technical Explanation

#### 1. Build-Time File Permissions (Primary Optimization)

**Problem**: The original image set file permissions at runtime during container startup. This required scanning ~15,000+ files with `find` and `chmod`/`chown` commands, which took 40-60 seconds on each container start.

**Solution**: File permissions are now set during the Docker image build process:

```dockerfile
# During build (Dockerfile)
RUN ... \
    && find openemr -type d -exec chmod 500 {} + \
    && find openemr -type f -exec chmod 400 {} + \
    && chmod 666 openemr/sites/default/sqlconf.php \
    && chmod 700 openemr/sites/default \
    && find openemr/sites/default/documents -type d -exec chmod 700 {} + \
    && find openemr/sites/default/documents -type f -exec chmod 600 {} + \
    && chown -R apache:apache openemr/
```

**Result**: Container startup no longer needs to scan and modify permissions for the entire codebase. Only files that change during setup (like `sqlconf.php`) need permission adjustments at runtime.

#### 2. Memory Reduction via Linux Page Cache

**Problem**: Runtime file scanning caused the Linux kernel to cache all scanned files in the page cache, inflating memory usage to 300+ MB even when the application wasn't using that memory.

**Technical Detail**: When `find` traverses directories and `chmod`/`chown` access file metadata, the kernel loads file inodes and directory entries into the page cache. For ~15,000 files, this creates substantial memory overhead that persists for the container's lifetime.

**Solution**: By moving file operations to build time, the page cache remains minimal at runtime. Only actively-used application files are cached.

**Result**: ~70% reduction in memory footprint (305 MB → 92 MB average).

#### 3. Simplified Runtime Entrypoint

The `openemr.sh` entrypoint script was optimized to:
- Skip redundant permission operations that are now handled at build time
- Use efficient `find -exec {} +` syntax instead of `xargs` pipelines
- Only modify permissions for files that actually change during setup

### Permission Scheme

| Path | Permissions | Rationale |
|------|-------------|-----------|
| Directories | `500` (r-x------) | Execute needed for traversal, no write |
| Files | `400` (r--------) | Read-only for PHP execution |
| `sites/default/` | `700` (rwx------) | Writable for site configuration |
| `sites/default/documents/` | `700`/`600` | Writable for document uploads |
| `sqlconf.php` | `666` | Writable during initial setup |

### Reproducing the Benchmarks

To verify these results on your system:

```bash
cd utilities/container_benchmarking
./benchmark.sh
```

See [Container Benchmarking Utility](../../utilities/container_benchmarking/README.md) for detailed instructions.

## Tags

See the https://hub.docker.com/r/openemr/openemr/ page for documentation of tags and their current aliases.

It is recommended to specify a version number in production, to ensure your build process pulls what you expect it to.

## How can I just spin up OpenEMR?

*You **need** to run an instance of mysql/mariadb as well and connect it to this container! You can then either use auto-setup with environment variables (see below) or you can manually set up, telling the server where to find the db.* The easiest way is to use `docker-compose`. The following `docker-compose.yml` file is a good example:
  - If you are using Raspberry Pi, then change the `mariadb:10.11` to `jsurf/rpi-mariadb`.
```yaml
# Use admin/pass as user/password credentials to login to openemr (from OE_USER and OE_PASS below)
# MYSQL_HOST and MYSQL_ROOT_PASS are required for openemr
# MYSQL_USER, MYSQL_PASS, OE_USER, MYSQL_PASS are optional for openemr and
#   if not provided, then default to openemr, openemr, admin, and pass respectively.
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
    image: openemr/openemr:8.1.1
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
[![Try it!](https://github.com/play-with-docker/stacks/raw/cff22438cb4195ace27f9b15784bbb497047afa7/assets/images/button.png)](http://play-with-docker.com/?stack=https://gist.githubusercontent.com/bradymiller/d285f4ae8d845b3e8a019d1efc44c9ad/raw/8436975f31dc6fea31908f1c533e77ac0e7d2a55/openemr-705-docker-example-docker-compose.yml)

## Environment Variables

See the https://hub.docker.com/r/openemr/openemr/ page for documentation of environment variables.

## Support on Raspberry Pi

Both 32 bit and 64 bit architectures are supported on Raspberry Pi. If you are using Raspberry Pi, then you need to change the `mariadb:10.11` to `jsurf/rpi-mariadb` in the above docker-compose.yml example.

## Where to get help?

For general knowledge, our [wiki](https://www.open-emr.org/wiki) is a repository of helpful information. The [Forum](https://community.open-emr.org/) are a great source for assistance and news about emerging features. We also have a [Chat](https://www.open-emr.org/chat/) system for real-time advice and to coordinate our development efforts.

## How can I contribute?

The OpenEMR community is a vibrant and active group, and people from any background can contribute meaningfully, whether they are optimizing our DB calls, or they're doing translations to their native tongue. Feel free to reach out to us at via [Chat](https://www.open-emr.org/chat/)!
