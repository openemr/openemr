#!/usr/bin/env bash

##
# SMART-launch smoke orchestrator (openemr/openemr#14529).
#
# Boots the ci/smart-launch compose stack, installs openemr via
# install_configure (shared with inferno), stages the pinned-SHA release
# of openemr/oe-smart-questionnaire into the openemr container, enables
# OpenEMR's REST + FHIR API globals, loads demo data (patients the
# `launch/patient` scope requires), registers an OAuth client via the
# app's own `bin/register.php`, enables that client, and runs the
# smart-launch PHPUnit test which drives the SMART launch end-to-end
# via Panther + selenium.
#
# Shape modeled on ci/inferno/run.sh. See CLAUDE.md "Browser debugging
# via Selenium" section for the Panther-via-selenium pattern this uses.
#
# Required env: none (CI invokes with no args).
# Optional env: SMART_LAUNCH_DRY_RUN=1 to skip the phpunit step and leave
#   the stack up for inspection.
#
# @package   OpenEMR
# @link      https://www.open-emr.org
# @author    Brady Miller <brady.g.miller@gmail.com>
# @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
# @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3

set -xeuo pipefail

initialize_openemr() {
    echo 'Initializing OpenEMR'
    local -x DOCKER_DIR=smart-launch
    local -x OPENEMR_DIR=/var/www/localhost/htdocs/openemr
    local repo_root
    repo_root="$(git rev-parse --show-toplevel)"
    cd -P "${repo_root}"
    # shellcheck source-path=../..
    . ci/ciLibrary.source
    composer_install
    npm_build
    cd -
    dockers_env_start
    install_configure
    # post_install_upgrade seeds the demo data (patients the
    # launch/patient scope requires) via the flex image's demoData
    # function; its post-reseed re-apply of globals is still just
    # rest_api/rest_fhir_api/..., so site_addr_oath remains unset
    # until enable_openemr_api runs below.
    post_install_upgrade
    echo 'OpenEMR initialized'
}

stage_smart_questionnaire_app() {
    echo 'Staging oe-smart-questionnaire pinned-SHA tarball (pre-compose)'
    local repo_root stage_dir
    repo_root="$(git rev-parse --show-toplevel)"
    stage_dir="${repo_root}/ci/smart-launch/stage"
    # Can't use a shellcheck source= directive here because the path
    # resolves through a runtime-set variable; SC1091 is a dynamic-path
    # info note, not a real issue (pin.sh is in-tree next to this script).
    # shellcheck disable=SC1091
    . "${repo_root}/ci/smart-launch/pin.sh"
    local tarball="${stage_dir}/pin.tar.gz"
    local appdir="${stage_dir}/app"

    # Bind pin as a local so a stale env (or a pin.sh that forgot to set
    # the variable) fails loudly here instead of silently fetching an
    # empty-URL tarball. The sourced pin.sh declaration is marked
    # SC2034-disable there because this is the only consumer and shellcheck
    # cannot statically follow the source path.
    local pin="${SMART_QUESTIONNAIRE_PIN:?pin.sh must set SMART_QUESTIONNAIRE_PIN}"

    # Clear contents of appdir without removing the dir itself — the
    # compose stack bind-mounts the dir path, and removing-then-recreating
    # racily can leave docker holding a stale inode.
    find "${appdir}" -mindepth 1 -delete

    # GitHub's tarball endpoint returns a repo tarball at the SHA. The
    # top-level directory inside the archive is
    # openemr-oe-smart-questionnaire-<short-sha>/ which strip-components=1
    # peels off, leaving public/, src/, bin/, etc. directly under appdir.
    curl -sSfL \
        "https://api.github.com/repos/openemr/oe-smart-questionnaire/tarball/${pin}" \
        -o "${tarball}"
    tar -xzf "${tarball}" --strip-components=1 -C "${appdir}"
    rm -f "${tarball}"

    # Drop the smoke config into the app tree. The app's bootstrap looks
    # for config.php next to the src/ dir.
    cp "${repo_root}/ci/smart-launch/config.sq.php" "${appdir}/config.php"

    echo 'SMART app staged on host'
}

configure_smart_questionnaire_in_container() {
    echo 'Configuring staged SMART app inside the openemr container'
    # var/ must be writable by apache so bin/register.php can write
    # clients.json. Flex image's apache user has uid=1000 (= host CI
    # user), so files apache writes are host-readable without further
    # dance.
    docker compose exec -T openemr sh -c '
        chown -R apache:apache /opt/oe-smart-questionnaire/app/var
        chmod 0700 /opt/oe-smart-questionnaire/app/var
    '

    # Reload apache to pick up the alias conf dropped into
    # /etc/apache2/conf.d by the compose bind mount. apachectl graceful
    # is the no-dropped-request reload; httpd -k graceful is the
    # fallback path (name differs across Apache builds).
    docker compose exec -T openemr sh -c 'apachectl -k graceful 2>&1 || httpd -k graceful 2>&1'

    # Quick sanity: launch.php with no params should render (not 404 /
    # 500). Clients are not registered yet so it will show the "No FHIR
    # servers registered" notice; 200 proves the Alias + PHP both work.
    local probe_status
    probe_status=$(docker compose exec -T openemr sh -c \
        'curl -sSk -o /dev/null -w "%{http_code}" https://openemr/smart-questionnaire/launch.php')
    if [[ "${probe_status}" != '200' ]]; then
        echo "ERROR: /smart-questionnaire/launch.php returned ${probe_status} after apache reload"
        return 1
    fi
    echo 'SMART app reachable inside the stack'
}

set_site_addr_oath() {
    echo 'Setting site_addr_oath to https://openemr'
    # install_configure already enabled rest_api / rest_fhir_api /
    # rest_portal_api / oauth_password_grant / rest_system_scopes_api
    # (see ciLibrary.source). The one global it does not set is
    # site_addr_oath — the OAuth2 issuer URL — because dev workflows
    # use whatever the local host-port maps to. For this smoke we
    # need the issuer URL to match what both the browser (via
    # selenium) and the SMART app's server-side PHP use to reach
    # OpenEMR, which is the compose-network service alias.
    docker compose exec -T mysql mysql -u root --password=root openemr -e "
        INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('site_addr_oath', 0, 'https://openemr')
          ON DUPLICATE KEY UPDATE gl_value=VALUES(gl_value);
    "
}

register_smart_client() {
    echo 'Running oe-smart-questionnaire bin/register.php --all'
    # DCR against the running OpenEMR via the app's own CLI entry point.
    # Non-interactive; no admin password needed (bin/register.php never
    # consults admin.json — that gate is only for public/admin.php).
    docker compose exec -T openemr sh -c '
        cd /opt/oe-smart-questionnaire/app && php bin/register.php --all
        chown -R apache:apache var
    '
    # OpenEMR registers all clients disabled. Flip is_enabled=1 so the
    # SMART launch can actually use it. Match on client_name (the only
    # stable identifier we have — register.php chooses the client_id).
    docker compose exec -T mysql mysql -u root --password=root openemr -e "
        UPDATE oauth_clients SET is_enabled=1
         WHERE client_name LIKE 'OpenEMR SMART Questionnaire smoke%';
    "
}

run_smart_launch_test() {
    echo 'Running SMART-launch acceptance smoke'
    local -x DOCKER_DIR=smart-launch
    local -x OPENEMR_DIR=/var/www/localhost/htdocs/openemr
    local repo_root
    repo_root="$(git rev-parse --show-toplevel)"
    local compose_file="${repo_root}/ci/smart-launch/compose.yml"
    export COMPOSE_FILE="${compose_file}"
    cd -P "${repo_root}"
    # shellcheck source-path=../..
    . ci/ciLibrary.source

    phpunit --testsuite smart-launch \
            --log-junit junit-smart-launch.xml \
            -c "${OPENEMR_DIR}/phpunit.xml"
}

main() {
    export COMPOSE_BAKE=1
    export DOCKER_BUILDKIT=1
    local repo_root
    repo_root="$(git rev-parse --show-toplevel)"
    # shellcheck source-path=SCRIPTDIR/..
    . "${repo_root}/ci/ciLibrary.source"

    echo 'Pulling Docker images...'
    # shellcheck disable=SC2310
    if ! with_retry 4 5 docker compose pull --ignore-buildable; then
        echo 'ERROR: Failed to pull Docker images'
        exit 1
    fi

    # Stage the SMART app BEFORE bringing up the stack so the compose
    # bind-mount source (ci/smart-launch/stage/app) is already a host-
    # owned directory. If we let docker create it on demand it comes
    # up root-owned, which blocks the host-side find -delete on
    # subsequent runs. .gitkeep in stage/app/ keeps the dir around
    # across a fresh clone.
    stage_smart_questionnaire_app
    initialize_openemr
    configure_smart_questionnaire_in_container
    set_site_addr_oath
    register_smart_client

    if [[ "${SMART_LAUNCH_DRY_RUN:-}" == '1' ]]; then
        echo 'SMART_LAUNCH_DRY_RUN=1 — stack left up for inspection; skipping phpunit.'
        return 0
    fi

    # Let set -e carry a non-zero exit straight out — no `if !` wrap,
    # which SC2310-disables set -e inside the body and silently drops
    # any further failures that come after.
    run_smart_launch_test
    echo 'SUCCESS: SMART-launch smoke passed.'
}

main "$@"
