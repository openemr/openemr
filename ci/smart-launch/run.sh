#!/usr/bin/env bash

##
# SMART-launch smoke orchestrator (openemr/openemr#14529).
#
# Boots the ci/smart-launch compose stack, installs openemr via
# install_configure (shared with inferno), stages upstream master of
# openemr/oe-smart-questionnaire into the openemr container, enables
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
# Local iteration: this orchestrator brings up its own dedicated compose
# stack (openemr + mysql + selenium), so it doesn't share state with a
# dev-easy worktree. For faster iteration on just the test file against
# an already-up stack, drive phpunit directly inside the worktree's
# openemr container — the stack needs selenium, so use
# `openemr-cmd worktree add <branch> -b --start --env easy` (NOT
# easy-light, which drops Selenium/CouchDB/Mailpit and leaves this
# smoke unable to reach the WebDriver endpoint it depends on).
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
    echo 'Staging oe-smart-questionnaire master tarball (pre-compose)'
    local repo_root stage_dir
    repo_root="$(git rev-parse --show-toplevel)"
    stage_dir="${repo_root}/ci/smart-launch/stage"
    local tarball="${stage_dir}/upstream.tar.gz"
    local appdir="${stage_dir}/app"

    # Clear contents of appdir without removing the dir itself, and
    # without removing the committed .gitkeep stub. The compose stack
    # bind-mounts appdir, and removing-then-recreating racily can leave
    # docker holding a stale inode. .gitkeep stays so a local run
    # doesn't leave it as a staged deletion in `git status`.
    find "${appdir}" -mindepth 1 ! -name .gitkeep -delete

    # Pulls upstream master's tip. No pin — rolling-latest trades off
    # pin maintenance for occasional transient CI red from upstream
    # churn, which we accept over the per-bump review tax. If upstream
    # breaks compat, this smoke flags it on the next PR.
    #
    # GitHub's tarball endpoint returns the repo tarball at the ref.
    # The top-level directory inside the archive is
    # openemr-oe-smart-questionnaire-<short-sha>/, which strip-components=1
    # peels off, leaving public/, src/, bin/, etc. directly under appdir.
    curl -sSfL \
        "https://api.github.com/repos/openemr/oe-smart-questionnaire/tarball/master" \
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

set_smoke_globals() {
    echo 'Setting smoke-specific globals (site_addr_oath + smart_context_test_launches)'
    # install_configure already enabled rest_api / rest_fhir_api /
    # rest_portal_api / oauth_password_grant / rest_system_scopes_api
    # (see ciLibrary.source). Two globals it does not set that this
    # smoke needs:
    #   - site_addr_oath: the OAuth2 issuer URL. For this smoke it
    #     must match what both the browser (via selenium) and the SMART
    #     app's server-side PHP use to reach OpenEMR — the compose-
    #     network service alias.
    #   - smart_context_test_launches: toggles the "Test Launches" card
    #     on interface/patient_file/assessment/fhir_assessments.php
    #     which renders the EHR-launch buttons (patient / encounter /
    #     main-tab / appointment intents). The EHR-launch test posts to
    #     interface/smart/ehr-launch-client.php with the client_id +
    #     csrf_token + intent those buttons expose; off-by-default
    #     elsewhere so no production footprint from flipping it here.
    docker compose exec -T mysql mariadb -u root --password=root openemr -e "
        INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('site_addr_oath', 0, 'https://openemr')
          ON DUPLICATE KEY UPDATE gl_value=VALUES(gl_value);
        INSERT INTO globals (gl_name, gl_index, gl_value) VALUES ('smart_context_test_launches', 0, '1')
          ON DUPLICATE KEY UPDATE gl_value=VALUES(gl_value);
    "
}

seed_smoke_questionnaire() {
    echo 'Seeding a minimal Questionnaire fixture for the write-round-trip test'
    # The write-round-trip test needs a Questionnaire to pick on the
    # app's index.php. The demo-data load ships no Questionnaire rows,
    # so insert a minimal one with a single required-string item. The
    # row is picked up by the FHIR Questionnaire search the app runs
    # after landing on index.php, and the string item renders as a
    # plain <input type=text> that the test fills + submits.
    #
    # Fixed questionnaire_id='smart-smoke-q' + name='smart-smoke-
    # questionnaire' so the test can match by stable identifier. UUID
    # is MySQL-generated per run (not written to the FHIR body
    # overrides — the service layer uses the row's binary uuid).
    docker compose exec -T mysql mariadb -u root --password=root openemr -e "
        INSERT INTO questionnaire_repository
          (uuid, questionnaire_id, name, version, type, active, status, questionnaire)
        VALUES (
          UNHEX(REPLACE(UUID(),'-','')),
          'smart-smoke-q',
          'smart-smoke-questionnaire',
          1,
          'Questionnaire',
          1,
          'active',
          '{\"resourceType\":\"Questionnaire\",\"id\":\"smart-smoke-q\",\"url\":\"http://example.com/q/smart-smoke\",\"version\":\"1.0.0\",\"name\":\"smartsmoke\",\"title\":\"SMART Launch Smoke Questionnaire\",\"status\":\"active\",\"item\":[{\"linkId\":\"1\",\"type\":\"string\",\"text\":\"How are you feeling?\",\"required\":true}]}'
        );
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
    docker compose exec -T mysql mariadb -u root --password=root openemr -e "
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
    set_smoke_globals
    seed_smoke_questionnaire
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
