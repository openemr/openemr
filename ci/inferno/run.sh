#!/usr/bin/env bash

set -xeuo pipefail

clone_files() {
    echo 'Cloning files'
    git submodule update --init --recursive
    # We need to copy the contents of the files directory from the private
    # submodule in inferno-files into the `onc-certification-g10-test-kit` directory.
    # If the inferno-files directory is empty at this point, the user probably
    # does not have access to the inferno-files repository. In that case
    # print a warning that the process may take several hours and continue.

    INFERNO_FILES_DIR="inferno-files/files"
    TARGET_DIR="onc-certification-g10-test-kit"

    if [[ -d "${INFERNO_FILES_DIR}" ]] && ls -A "${INFERNO_FILES_DIR}" 2>/dev/null; then
        echo "Copying files from ${INFERNO_FILES_DIR} to ${TARGET_DIR}..."
        cp -r "${INFERNO_FILES_DIR}/"* "${TARGET_DIR}/"
        return 0
    fi

    echo "WARNING: ${INFERNO_FILES_DIR} directory is empty or missing."
    echo 'You may not have access to the private repository.'
    echo 'The process may take several hours to download all required terminology files.'
    return 1
}

initialize_terminology() {
    echo 'Initializing Terminology'
    time docker compose run --rm terminology_builder
    echo 'Terminology initialized'
}

check_inferno() {
    echo 'Checking Inferno terminology'
    # The output of the below command contains lines that look like this:
    # http://hl7.org/fhir/ValueSet/mimetypes: Expected codes: 2578 Actual codes: 2629
    # urn:ietf:bcp:13: Expected codes: 2578 Actual codes: 2629
    # As long as the count of Actual codes is greater than or equal to the count of Expected codes, we are good.

    local output
    output=$(docker compose run --rm inferno bundle exec rake terminology:check_built_terminology) || return

    local is_healthy=1

    while IFS= read -r line; do
        [[ "${line}" =~ .*Expected\ codes:\ ([0-9]+)\ Actual\ codes:\ ([0-9]+).* ]] || continue
        expected="${BASH_REMATCH[1]}"
        actual="${BASH_REMATCH[2]}"
        (( actual >= expected )) && continue
        echo "ISSUE: ${line} (Actual codes less than Expected)"
        is_healthy=0
    done <<< "${output}"

    if (( ! is_healthy )); then
        echo 'ERROR: Terminology check failed - some Actual codes are insufficient'
        return 1
    fi
    echo 'SUCCESS: Terminology check passed'
}

initialize_inferno() {
    echo 'Initializing Inferno'
    time docker compose run --rm inferno bundle exec inferno migrate
    echo 'Inferno lit'
}

configure_api_globals() {
    docker compose exec -T openemr mysql -u openemr --password=openemr -h mysql openemr <<'SQL'
        INSERT INTO globals (gl_name, gl_index, gl_value) VALUES
            ('rest_api', 0, '1'),
            ('rest_fhir_api', 0, '1'),
            ('rest_portal_api', 0, '1'),
            ('oauth_password_grant', 0, '3'),
            ('rest_system_scopes_api', 0, '1'),
            ('ccda_alt_service_enable', 0, '3')
        ON DUPLICATE KEY UPDATE gl_value = VALUES(gl_value);
SQL
}

initialize_openemr() {
    echo 'Initializing OpenEMR'
    local -x DOCKER_DIR=inferno
    local -x OPENEMR_DIR=/var/www/localhost/htdocs/openemr
    local repo_root
    repo_root="$(git rev-parse --show-toplevel)"
    cd -P "${repo_root}"
    # shellcheck source-path=../..
    . ci/ciLibrary.source
    composer_install
    npm_build
    ccda_build
    cd -
    dockers_env_start
    install_configure
    "${HOME}/bin/openemr-cmd" pc inferno-files/files/resources/openemr-snapshots/2025-06-25-inferno-baseline.tgz
    "${HOME}/bin/openemr-cmd" rs 2025-06-25-inferno-baseline
    #  Snapshot is from 7.0.3; run migrations to create any new tables
    docker compose exec -T openemr php "${OPENEMR_DIR}/sql_upgrade.php" --from=7.0.3
    # Snapshot may not have API globals configured; ensure they're set
    # configure_api_globals
    # Prevent password expiration from blocking OAuth password grant
    docker compose exec -T openemr mysql -u openemr --password=openemr -h mysql openemr \
        -e "UPDATE users_secure SET last_update_password = NOW()"
    # Remove care_team_member record for user who doesn't qualify as Practitioner
    # Snapshot predates commit 4af4c827f which added username/abook_type filtering
    # See https://github.com/openemr/openemr/issues/11831#issuecomment-4341049367
    docker compose exec -T openemr mysql -u openemr --password=openemr -h mysql openemr \
        -e "DELETE FROM care_team_member WHERE user_id = (SELECT id FROM users WHERE uuid = UNHEX(REPLACE('96889cb7-0f90-4d9e-9a6c-ac0e70c01cb1', '-', '')))"
    # Fix procedure name to match LOINC 24357-6 official display
    # Snapshot has "Urinanalysis macro (dipstick) panel" (typo + missing suffix)
    # LOINC requires "Urinalysis macro (dipstick) panel - Urine"
    docker compose exec -T openemr mysql -u openemr --password=openemr -h mysql openemr \
        -e "UPDATE procedure_order_code SET procedure_name = 'Urinalysis macro (dipstick) panel - Urine' WHERE procedure_code = '24357-6'"

    # Configure coverage after containers are running and OpenEMR is initialized
    if [[ ${ENABLE_COVERAGE:-false} = true ]]; then
        # Set COMPOSE_FILE so docker compose commands work from any directory
        local compose_file="${repo_root}/ci/inferno/compose.yml"
        export COMPOSE_FILE="${compose_file}"

        cd -P "${repo_root}"
        configure_coverage
        setup_e2e_bookends apache
        # Stay in repo root - we have write permissions here
        echo "COVERAGE_RAW_TMPDIR=${RUNNER_TEMP:-/tmp}/coverage-inferno-raw"
    fi

    echo 'OpenEMR initialized'
}
run_testsuite() {
    local -x DOCKER_DIR=inferno
    local -x OPENEMR_DIR=/var/www/localhost/htdocs/openemr
    local repo_root
    repo_root="$(git rev-parse --show-toplevel)"

    # Set COMPOSE_FILE so docker compose commands work from any directory
    local compose_file="${repo_root}/ci/inferno/compose.yml"
    export COMPOSE_FILE="${compose_file}"

    cd -P "${repo_root}"
    # shellcheck source-path=../..
    . ci/ciLibrary.source
    # Stay in repo root - we have write permissions here and COMPOSE_FILE is set

    # Run PHPUnit tests with coverage if enabled
    local exit_code=0
    if [[ ${ENABLE_COVERAGE:-false} = true ]]; then
        # shellcheck disable=SC2310 # Intentionally capture exit code without triggering errexit
        phpunit --testsuite certification \
                --coverage-clover coverage.inferno-phpunit.clover.xml \
                --log-junit junit-inferno.xml \
                -c "${OPENEMR_DIR}/phpunit.xml" || exit_code=$?
    else
        # shellcheck disable=SC2310 # Intentionally capture exit code without triggering errexit
        phpunit --testsuite certification \
                --log-junit junit-inferno.xml \
                -c "${OPENEMR_DIR}/phpunit.xml" || exit_code=$?
    fi

    echo 'Certification Tests Executed'
    return "${exit_code}"
}

collect_inferno_coverage() {
    local -x DOCKER_DIR=inferno
    local -x OPENEMR_DIR=/var/www/localhost/htdocs/openemr
    local repo_root
    repo_root="$(git rev-parse --show-toplevel)"

    # Set COMPOSE_FILE so docker compose commands work from any directory
    local compose_file="${repo_root}/ci/inferno/compose.yml"
    export COMPOSE_FILE="${compose_file}"

    cd -P "${repo_root}"
    # shellcheck source-path=../..
    . ci/ciLibrary.source
    # Stay in repo root - we have write permissions here and COMPOSE_FILE is set

    echo 'Collecting Inferno coverage...'

    # Convert HTTP request coverage to clover.xml format
    # Run inside the container so file paths resolve correctly
    convert_coverage /tmp/openemr-coverage/inferno \
                     /dev/null \
                     --clover=coverage.inferno-http.clover.xml
    ls -lah coverage.inferno-http.clover.xml || true

    # Note: Individual coverage files (coverage.inferno-phpunit.clover.xml and
    # coverage.inferno-http.clover.xml) are uploaded separately to Codecov,
    # which handles server-side merging. No local merge needed.

    echo 'Inferno coverage collection completed'
}

fix_redis_permissions() {
     # The cloned submodule has pre-existing Redis data files owned by the CI runner.
     # Redis runs as the 'redis' user inside the container and needs write access.
     # Using 777 is acceptable here since this is ephemeral CI infrastructure.
     chmod -R 777 "${PWD}/onc-certification-g10-test-kit/data/redis"
}

main() {
    # Compose Bake will either be ignored or it will make builds faster.
    export COMPOSE_BAKE=1
    # BuildKit accepts platform arguments.
    # The classic Docker builder does not.
    # We need platform arguments.
    export DOCKER_BUILDKIT=1
    local use_cloned_files
    # shellcheck disable=SC2310
    if clone_files; then
      use_cloned_files=1
    else
      use_cloned_files=0
    fi
    echo 'Pulling Docker images...'
    if ! docker compose pull; then
        echo 'ERROR: Failed to pull Docker images'
        exit 1
    fi
    echo 'Building Docker images...'
    if ! docker compose build; then
        echo 'ERROR: Failed to build Docker images'
        exit 1
    fi
    if (( ! use_cloned_files )); then
      initialize_terminology
      # A second build is needed after the terminology initialization
      echo 'Rebuilding Docker images after terminology initialization...'
      if ! docker compose build; then
          echo 'ERROR: Failed to rebuild Docker images'
          exit 1
      fi
    fi

    fix_redis_permissions
    initialize_inferno
    check_inferno
    initialize_openemr

    # Run the test suite and capture exit code
    local exit_code=0
    # shellcheck disable=SC2310 # Intentionally capture exit code without triggering errexit
    run_testsuite || exit_code=$?
    if (( exit_code != 0 )); then
        echo "FAILURE: Inferno certification tests failed with exit code: ${exit_code}"
        # Still try to collect coverage even on failure
        if [[ ${ENABLE_COVERAGE:-false} = true ]]; then
            # shellcheck disable=SC2310 # Intentionally ignore coverage failure
            collect_inferno_coverage || echo "Warning: Coverage collection failed"
        fi
        exit "${exit_code}"
    fi

    # Collect coverage if enabled
    if [[ ${ENABLE_COVERAGE:-false} = true ]]; then
        collect_inferno_coverage
    fi

    echo 'SUCCESS: All Inferno certification tests completed successfully!'
}

main "$@"
