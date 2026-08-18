#!/usr/bin/env bash
#
# openemr-docker-backup.sh
#
# Nightly backup for a Docker Compose OpenEMR deployment.
# Replaces the legacy Administration -> System -> Backup routine,
# which was removed in OpenEMR 8.3.0.
#
# IMPORTANT: Run this on the Docker HOST, not inside the OpenEMR container.
# This file ships with the OpenEMR codebase (contrib/util/), so a copy exists
# inside the container's webroot -- that copy is for reference only. Download
# or copy it to the host (e.g. /opt/openemr/) and run it there:
#   curl -o /opt/openemr/openemr-docker-backup.sh \
#     https://raw.githubusercontent.com/openemr/openemr/master/contrib/util/openemr-docker-backup.sh
#   chmod 700 /opt/openemr/openemr-docker-backup.sh
#
# What it captures:
#   1. Full MySQL/MariaDB dump (--single-transaction, includes routines/triggers)
#   2. The sites/ directory (documents, config, custom code, SSL material)
#   3. A manifest recording image versions in use at backup time
#
# What it does NOT capture:
#   - CouchDB document storage (only if you have enabled it; see notes at bottom)
#   - The containers/images themselves (they are reproducible from the registry)
#
# Usage:
#   ./openemr-docker-backup.sh [/path/to/compose/directory]
#
#   If no argument is given, COMPOSE_DIR below is used.
#
# Recommended cron entry on Linux hosts (2:15 AM daily, log to file):
#   15 2 * * * /opt/openemr/openemr-docker-backup.sh >> /var/log/openemr-backup.log 2>&1
#
# WINDOWS / DOCKER DESKTOP HOSTS (no cron):
#   Docker Desktop runs via WSL2, so this script runs inside your WSL distro
#   (e.g. Ubuntu). Schedule it with Windows Task Scheduler instead of cron:
#
#     1. Place this script inside the WSL filesystem, e.g. /opt/openemr/,
#        and confirm it runs there manually first:
#          wsl.exe -d Ubuntu -u root bash /opt/openemr/openemr-docker-backup.sh
#     2. Task Scheduler -> Create Task (not "Basic Task"):
#          - Run whether user is logged on or not; run with highest privileges
#          - Trigger: Daily, 2:15 AM
#          - Action: Start a program
#              Program:   C:\Windows\System32\wsl.exe
#              Arguments: -d Ubuntu -u root bash -lc "/opt/openemr/openemr-docker-backup.sh >> /var/log/openemr-backup.log 2>&1"
#     3. Keep BACKUP_ROOT on the WSL/Linux filesystem (as configured below),
#        NOT under /mnt/c/... -- the Windows-mount filesystem is slow and
#        loses ownership/permission fidelity. Sync offsite (or to a Windows
#        folder as a second copy) as a separate step after the backup runs.
#
#   Caveats: wsl.exe will start the distro if it isn't running, but Docker
#   Desktop's engine must also be running for the compose commands to work.
#   Docker Desktop is per-user by default; enable "Start Docker Desktop when
#   you sign in" and keep the machine signed in / auto-logon to a service
#   account, or -- more robustly -- run OpenEMR on a Linux VM where none of
#   this applies. Verify the first few scheduled runs by checking the
#   LAST_BACKUP_OK timestamp under BACKUP_ROOT.
#
# Restore instructions are at the bottom of this file.

set -euo pipefail

# ---------------------------------------------------------------------------
# Configuration -- adjust these for your deployment
# ---------------------------------------------------------------------------

# Directory containing your docker-compose.yml
COMPOSE_DIR="${1:-/opt/openemr}"

# Where backups are written. Should be on a different disk/volume than the
# Docker data if at all possible, and MUST be included in your offsite sync.
BACKUP_ROOT="/var/backups/openemr"

# Compose service names as defined in your docker-compose.yml.
# The official openemr-devops examples use "mysql" and "openemr".
DB_SERVICE="mysql"
APP_SERVICE="openemr"

# Database credentials. The official compose files set MYSQL_ROOT_PASSWORD;
# prefer a dedicated backup user with SELECT, LOCK TABLES, SHOW VIEW, TRIGGER,
# EVENT, and PROCESS privileges if you want least-privilege.
DB_USER="root"
DB_PASS_ENV="MYSQL_ROOT_PASSWORD"   # env var name INSIDE the db container
DB_NAME="openemr"

# Path to the sites directory INSIDE the app container.
# Official images: /var/www/localhost/htdocs/openemr/sites
SITES_PATH="/var/www/localhost/htdocs/openemr/sites"

# How many days of local backups to keep.
RETENTION_DAYS=14

# Compose command -- use "docker-compose" on older installs.
COMPOSE="docker compose"

# ---------------------------------------------------------------------------
# End configuration
# ---------------------------------------------------------------------------

TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="${BACKUP_ROOT}/${TIMESTAMP}"
LOCKFILE="/var/run/openemr-backup.lock"

log() { printf '%s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"; }

fail() {
    log "ERROR: $*"
    # Leave a marker so monitoring can detect a failed run.
    touch "${BACKUP_ROOT}/LAST_BACKUP_FAILED" 2>/dev/null || true
    exit 1
}

# Prevent overlapping runs (a slow dump colliding with the next cron fire).
exec 9>"${LOCKFILE}"
if ! flock -n 9; then
    fail "another backup is already running (lock: ${LOCKFILE})"
fi

command -v docker >/dev/null 2>&1 || fail "docker not found in PATH"
[ -d "${COMPOSE_DIR}" ] || fail "compose directory not found: ${COMPOSE_DIR}"
cd "${COMPOSE_DIR}"

# Verify the services exist and are running before we do anything.
for svc in "${DB_SERVICE}" "${APP_SERVICE}"; do
    if ! ${COMPOSE} ps --status running --services 2>/dev/null | grep -qx "${svc}"; then
        fail "compose service '${svc}' is not running in ${COMPOSE_DIR}"
    fi
done

mkdir -p "${BACKUP_DIR}"
log "backup started -> ${BACKUP_DIR}"

# ---------------------------------------------------------------------------
# 1. Database dump
# ---------------------------------------------------------------------------
# --single-transaction gives a consistent InnoDB snapshot without locking
# the practice out of the system mid-dump. --routines/--triggers/--events
# matter: OpenEMR uses triggers, and sites add stored procedures over time.
# -T on compose exec is required for non-interactive (cron) use.

log "dumping database '${DB_NAME}' from service '${DB_SERVICE}'"
${COMPOSE} exec -T "${DB_SERVICE}" sh -c \
    "exec mysqldump --single-transaction --quick --routines --triggers --events \
        -u'${DB_USER}' -p\"\$${DB_PASS_ENV}\" '${DB_NAME}'" \
    | gzip > "${BACKUP_DIR}/openemr-db.sql.gz"

# gzip of an empty/failed stream is ~20 bytes; a real OpenEMR dump is never
# this small. Guard against silently archiving nothing.
DB_SIZE=$(stat -c%s "${BACKUP_DIR}/openemr-db.sql.gz")
[ "${DB_SIZE}" -gt 10240 ] || fail "database dump suspiciously small (${DB_SIZE} bytes) -- check credentials"
log "database dump complete ($(numfmt --to=iec ${DB_SIZE} 2>/dev/null || echo ${DB_SIZE} bytes))"

# ---------------------------------------------------------------------------
# 2. sites/ directory
# ---------------------------------------------------------------------------
# This contains sqlconf.php, uploaded documents, letter templates, custom
# forms, and per-site SSL material. It is the other half of your PHI.

log "archiving sites directory from service '${APP_SERVICE}'"
${COMPOSE} exec -T "${APP_SERVICE}" sh -c \
    "tar -czf - -C '$(dirname "${SITES_PATH}")' '$(basename "${SITES_PATH}")'" \
    > "${BACKUP_DIR}/openemr-sites.tar.gz"

SITES_SIZE=$(stat -c%s "${BACKUP_DIR}/openemr-sites.tar.gz")
[ "${SITES_SIZE}" -gt 10240 ] || fail "sites archive suspiciously small (${SITES_SIZE} bytes)"
log "sites archive complete ($(numfmt --to=iec ${SITES_SIZE} 2>/dev/null || echo ${SITES_SIZE} bytes))"

# ---------------------------------------------------------------------------
# 3. Manifest -- record what was running, so restores match versions
# ---------------------------------------------------------------------------

{
    echo "backup_timestamp: ${TIMESTAMP}"
    echo "compose_dir: ${COMPOSE_DIR}"
    echo "images:"
    ${COMPOSE} images 2>/dev/null || docker ps --format '  {{.Names}}: {{.Image}}'
} > "${BACKUP_DIR}/manifest.txt"

# Keep a copy of the compose file itself -- it IS your infrastructure config.
cp docker-compose.yml "${BACKUP_DIR}/docker-compose.yml" 2>/dev/null || \
    log "note: docker-compose.yml not found to archive (compose v2 project?)"

# ---------------------------------------------------------------------------
# 4. Verify integrity of what we just wrote
# ---------------------------------------------------------------------------

gzip -t "${BACKUP_DIR}/openemr-db.sql.gz"   || fail "db dump failed gzip integrity check"
gzip -t "${BACKUP_DIR}/openemr-sites.tar.gz" || fail "sites archive failed gzip integrity check"

( cd "${BACKUP_DIR}" && sha256sum ./* > SHA256SUMS )
log "integrity checks passed"

# ---------------------------------------------------------------------------
# 5. Retention
# ---------------------------------------------------------------------------

log "pruning backups older than ${RETENTION_DAYS} days"
find "${BACKUP_ROOT}" -mindepth 1 -maxdepth 1 -type d \
    -name '20*' -mtime +"${RETENTION_DAYS}" -exec rm -rf {} \;

rm -f "${BACKUP_ROOT}/LAST_BACKUP_FAILED"
date '+%Y-%m-%d %H:%M:%S' > "${BACKUP_ROOT}/LAST_BACKUP_OK"
log "backup finished successfully"

# ===========================================================================
# RESTORE PROCEDURE
# ===========================================================================
#
# On a fresh host with Docker installed:
#
#   1. Restore the compose file and bring up ONLY the database:
#        cp docker-compose.yml /opt/openemr/ && cd /opt/openemr
#        docker compose up -d mysql
#        # wait for it to be healthy: docker compose logs -f mysql
#
#   2. Restore the database:
#        gunzip -c openemr-db.sql.gz | \
#          docker compose exec -T mysql sh -c \
#            'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" openemr'
#
#   3. Bring up the app container, then restore sites/ over the top:
#        docker compose up -d openemr
#        docker compose exec -T openemr sh -c \
#          'tar -xzf - -C /var/www/localhost/htdocs/openemr' \
#          < openemr-sites.tar.gz
#        docker compose restart openemr
#
#   4. Verify: log in, open a recent patient, open a recent document,
#      and check Administration -> Other -> Logs for errors.
#
# TEST YOUR RESTORES. A backup that has never been restored is a hope,
# not a backup. Do a full restore drill to a scratch VM at least quarterly
# and document it -- this also satisfies the HIPAA Security Rule's
# contingency plan testing requirement (45 CFR 164.308(a)(7)).
#
# NOTES
# -----
# * Offsite: this script writes locally. Pair it with rclone/restic/borg to
#   push ${BACKUP_ROOT} to encrypted offsite storage. Local-only backups do
#   not survive the ransomware scenario they exist for.
# * CouchDB: if you enabled CouchDB document storage, add a couchdb dump
#   step (or snapshot its volume) -- the sites/ tarball will not include
#   those documents.
# * Encryption at rest: if ${BACKUP_ROOT} is not on an encrypted filesystem,
#   consider piping the archives through gpg/age. The dump contains PHI.
# ===========================================================================
