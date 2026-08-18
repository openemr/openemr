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
#   2. The full OpenEMR webroot -- sites/ (documents, config, SSL material)
#      plus custom modules, UI-installed modules, and any local code changes
#      (matching the scope of the removed backup.php routine)
#   3. The Compose configuration (original files, .env, and the fully
#      resolved config as compose-resolved.yml)
#   4. A manifest recording image versions in use at backup time
#
# What it does NOT capture:
#   - CouchDB document storage (only if you have enabled it; see notes at bottom)
#   - The containers/images themselves (they are reproducible from the registry)
#
# CONSISTENCY NOTE: by default the database dump and the sites/ archive are
# taken back-to-back while the application stays online, so a chart uploaded
# in the seconds between the two can appear in one but not the other. For
# most practices running this at 2 AM that window is acceptable. If you need
# a strictly coordinated point-in-time backup, set QUIESCE_APP=true below:
# the app container is stopped for the duration of the backup (users see an
# outage) and restarted afterward -- even if the backup fails mid-way.
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

# Backup artifacts contain PHI: make every file we create private to the
# invoking user (root) regardless of the host's default umask.
umask 077

# ---------------------------------------------------------------------------
# Configuration -- adjust these for your deployment
# ---------------------------------------------------------------------------

# Directory containing your Compose configuration
COMPOSE_DIR="${1:-/opt/openemr}"

# Where backups are written. Should be on a different disk/volume than the
# Docker data if at all possible, and MUST be included in your offsite sync.
BACKUP_ROOT="/var/backups/openemr"

# Compose service names as defined in your Compose file.
# The official openemr-devops examples use "mysql" and "openemr".
DB_SERVICE="mysql"
APP_SERVICE="openemr"

# Database credentials. The official compose files set MYSQL_ROOT_PASSWORD;
# prefer a dedicated backup user with SELECT, LOCK TABLES, SHOW VIEW, TRIGGER,
# EVENT, and PROCESS privileges if you want least-privilege.
DB_USER="root"
DB_PASS_ENV="MYSQL_ROOT_PASSWORD"   # env var name INSIDE the db container
DB_NAME="openemr"

# Path INSIDE the app container to archive. Default is the full OpenEMR
# webroot, which captures sites/ AND custom/UI-installed modules
# (interface/modules/custom_modules), composer changes, and local patches --
# the same scope as the removed backup.php. Note: in the official images
# only sites/ lives on a persistent volume; the rest of the webroot is the
# image's own layer and is replaced on every container recreation/upgrade.
# This backup captures that state for disaster recovery, but modules
# installed outside sites/ still need reinstalling after an image upgrade
# (or bake them into a custom image layer / bind mount). If your deployment
# is strictly stock (no modules, no local changes), you can narrow this to
# .../openemr/sites for much smaller backups.
FILES_PATH="/var/www/localhost/htdocs/openemr"

# Stop the app container during the backup for a strictly coordinated
# point-in-time snapshot (see CONSISTENCY NOTE above). Users cannot access
# OpenEMR while the backup runs.
QUIESCE_APP="false"

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
APP_STOPPED="false"
STAGING_DIR=""   # tracked globally so on_exit can clean it up on failure

log() {
    local now
    now="$(date '+%Y-%m-%d %H:%M:%S')"
    printf '%s %s\n' "${now}" "$*"
}

# Human-readable byte count; falls back to raw bytes if numfmt is absent.
human_size() {
    local pretty
    if pretty="$(numfmt --to=iec "$1" 2>/dev/null)"; then
        printf '%s' "${pretty}"
    else
        printf '%s bytes' "$1"
    fi
}

fail() {
    log "ERROR: $*"
    exit 1
}

# Runs on EVERY exit. Guarantees that (a) a quiesced app container is
# restarted no matter what, and (b) monitoring markers reflect reality even
# when set -e aborts us before fail() is ever called.
on_exit() {
    local status=$?
    # The staging copy contains PHI; never leave it behind under /tmp,
    # even when set -e aborts us mid-archive.
    if [[ -n "${STAGING_DIR}" && -d "${STAGING_DIR}" ]]; then
        rm -rf "${STAGING_DIR}"
        STAGING_DIR=""
    fi
    if [[ "${APP_STOPPED}" == "true" ]]; then
        log "restarting app service '${APP_SERVICE}' after backup"
        ${COMPOSE} start "${APP_SERVICE}" || log "ERROR: could not restart '${APP_SERVICE}' -- intervene manually"
    fi
    if [[ ${status} -ne 0 ]]; then
        log "backup FAILED (exit ${status})"
        date '+%Y-%m-%d %H:%M:%S' > "${BACKUP_ROOT}/LAST_BACKUP_FAILED" 2>/dev/null || true
    fi
}

# Backup root must exist (with tight permissions) before anything can fail,
# so the failure marker always has somewhere to land.
mkdir -p "${BACKUP_ROOT}"
chmod 700 "${BACKUP_ROOT}"

trap on_exit EXIT

# Prevent overlapping runs (a slow dump colliding with the next cron fire).
exec 9>"${LOCKFILE}"
if ! flock -n 9; then
    fail "another backup is already running (lock: ${LOCKFILE})"
fi

command -v docker >/dev/null 2>&1 || fail "docker not found in PATH"
[[ -d "${COMPOSE_DIR}" ]] || fail "compose directory not found: ${COMPOSE_DIR}"
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
# 0. Optional quiesce for a coordinated point-in-time snapshot
# ---------------------------------------------------------------------------

if [[ "${QUIESCE_APP}" == "true" ]]; then
    log "QUIESCE_APP=true: stopping '${APP_SERVICE}' for the duration of the backup"
    ${COMPOSE} stop "${APP_SERVICE}"
    APP_STOPPED="true"
fi

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
DB_SIZE="$(stat -c%s "${BACKUP_DIR}/openemr-db.sql.gz")"
[[ "${DB_SIZE}" -gt 10240 ]] || fail "database dump suspiciously small (${DB_SIZE} bytes) -- check credentials"
DB_SIZE_HUMAN="$(human_size "${DB_SIZE}")"
log "database dump complete (${DB_SIZE_HUMAN})"

# ---------------------------------------------------------------------------
# 2. OpenEMR files (webroot)
# ---------------------------------------------------------------------------
# sites/ holds sqlconf.php, uploaded documents, letter templates, and per-site
# SSL material -- the other half of your PHI. The rest of the webroot holds
# custom modules and local code changes that a fresh image will NOT contain.
# Taken immediately after the dump to keep the inconsistency window small
# when QUIESCE_APP=false (see CONSISTENCY NOTE in the header).
# Note: 'compose exec' works against a stopped service's container only via
# 'docker run --volumes-from' semantics on some engines; to stay portable we
# read the files through the container filesystem with 'docker compose cp'
# fallback if exec fails while quiesced.

log "archiving OpenEMR files (${FILES_PATH}) from service '${APP_SERVICE}'"
FILES_BASE="$(basename "${FILES_PATH}")"
if [[ "${APP_STOPPED}" == "true" ]]; then
    # Container is stopped: exec is unavailable. Copy out via the engine,
    # then tar. The archive root must keep the webroot's own basename so
    # both branches produce identically-structured archives and the restore
    # procedure's extract-into-parent step works for either.
    STAGING_DIR="$(mktemp -d)"
    ${COMPOSE} cp "${APP_SERVICE}:${FILES_PATH}" "${STAGING_DIR}/${FILES_BASE}"
    tar -czf "${BACKUP_DIR}/openemr-files.tar.gz" -C "${STAGING_DIR}" "${FILES_BASE}"
    rm -rf "${STAGING_DIR}"
    STAGING_DIR=""
else
    ${COMPOSE} exec -T "${APP_SERVICE}" sh -c \
        "tar -czf - -C '$(dirname "${FILES_PATH}")' '${FILES_BASE}'" \
        > "${BACKUP_DIR}/openemr-files.tar.gz"
fi

FILES_SIZE="$(stat -c%s "${BACKUP_DIR}/openemr-files.tar.gz")"
[[ "${FILES_SIZE}" -gt 10240 ]] || fail "files archive suspiciously small (${FILES_SIZE} bytes)"
FILES_SIZE_HUMAN="$(human_size "${FILES_SIZE}")"
log "files archive complete (${FILES_SIZE_HUMAN})"

# Restart the app as early as possible if we quiesced; nothing below needs it stopped.
if [[ "${APP_STOPPED}" == "true" ]]; then
    log "restarting app service '${APP_SERVICE}'"
    ${COMPOSE} start "${APP_SERVICE}"
    APP_STOPPED="false"
fi

# ---------------------------------------------------------------------------
# 3. Compose configuration -- archive what Compose ACTUALLY uses
# ---------------------------------------------------------------------------
# Deployments variously use docker-compose.yml, compose.yaml, override files,
# COMPOSE_FILE, and .env. 'compose config' resolves all of that (including
# interpolated values -- which is why umask 077 above matters: the rendered
# file can contain passwords). We archive the rendered config plus any
# original files present, so the restore procedure always has what it needs.

log "archiving compose configuration"
if ! ${COMPOSE} config > "${BACKUP_DIR}/compose-resolved.yml" 2>/dev/null; then
    log "WARNING: 'compose config' failed; only copying original files"
    rm -f "${BACKUP_DIR}/compose-resolved.yml"
fi
for f in docker-compose.yml docker-compose.yaml compose.yml compose.yaml \
         docker-compose.override.yml docker-compose.override.yaml .env; do
    if [[ -f "${f}" ]]; then
        cp "${f}" "${BACKUP_DIR}/${f}"
    fi
done
if [[ ! -f "${BACKUP_DIR}/compose-resolved.yml" ]] && \
   ! ls "${BACKUP_DIR}"/*compose*.y*ml >/dev/null 2>&1; then
    fail "no compose configuration could be archived -- restore would be incomplete"
fi

# ---------------------------------------------------------------------------
# 4. Manifest -- record what was running, so restores match versions
# ---------------------------------------------------------------------------

{
    echo "backup_timestamp: ${TIMESTAMP}"
    echo "compose_dir: ${COMPOSE_DIR}"
    echo "quiesced: ${QUIESCE_APP}"
    echo "images:"
    ${COMPOSE} images 2>/dev/null || docker ps --format '  {{.Names}}: {{.Image}}'
} > "${BACKUP_DIR}/manifest.txt"

# ---------------------------------------------------------------------------
# 5. Verify integrity of what we just wrote
# ---------------------------------------------------------------------------

gzip -t "${BACKUP_DIR}/openemr-db.sql.gz"    || fail "db dump failed gzip integrity check"
gzip -t "${BACKUP_DIR}/openemr-files.tar.gz" || fail "files archive failed gzip integrity check"

( cd "${BACKUP_DIR}" && sha256sum ./* > SHA256SUMS )
log "integrity checks passed"

# ---------------------------------------------------------------------------
# 6. Retention
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
#   1. Restore the compose configuration and bring up ONLY the database.
#      The backup directory contains whichever Compose file(s) your
#      deployment actually used. From inside the backup directory, copy
#      every archived original -- preserving each filename -- with the
#      same file list the backup step uses:
#        for f in docker-compose.yml docker-compose.yaml compose.yml compose.yaml \
#                 docker-compose.override.yml docker-compose.override.yaml .env; do
#            [ -f "$f" ] && cp "$f" /opt/openemr/
#        done
#      If NONE of the originals were archived and only compose-resolved.yml
#      exists, copy it as compose.yaml -- and keep its permissions tight,
#      it contains interpolated secrets:
#        install -m 600 compose-resolved.yml /opt/openemr/compose.yaml
#      Then:
#        cd /opt/openemr
#        docker compose up -d mysql
#        # wait for it to be healthy: docker compose logs -f mysql
#
#   2. Restore the database:
#        gunzip -c openemr-db.sql.gz | \
#          docker compose exec -T mysql sh -c \
#            'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" openemr'
#
#   3. Bring up the app container (pin the image tag to the version in
#      manifest.txt), then restore the archived files over the top. The
#      archive's top-level directory matches the webroot's name, so extract
#      into the webroot's PARENT:
#        docker compose up -d openemr
#        docker compose exec -T openemr sh -c \
#          'tar -xzf - -C /var/www/localhost/htdocs' \
#          < openemr-files.tar.gz
#        docker compose restart openemr
#      (If you narrowed FILES_PATH to sites/, extract into the webroot
#      itself instead: -C /var/www/localhost/htdocs/openemr)
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
#   step (or snapshot its volume) -- the files tarball will not include
#   those documents.
# * Encryption at rest: backup files are created mode 600 under a mode-700
#   directory (umask 077). If ${BACKUP_ROOT} is not on an encrypted
#   filesystem, consider additionally piping the archives through gpg/age.
#   The dump contains PHI.
# ===========================================================================
