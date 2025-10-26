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
    if [[ ${ENABLE_COVERAGE:-false} = true ]]; then
        phpunit --testsuite certification \
                --coverage-php coverage/coverage.inferno-phpunit.cov \
                --log-junit junit-inferno.xml \
                -c "${OPENEMR_DIR}/phpunit.xml"
    else
        phpunit --testsuite certification \
                --log-junit junit-inferno.xml \
                -c "${OPENEMR_DIR}/phpunit.xml"
    fi

    echo 'Certification Tests Executed'
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

    # Copy HTTP request coverage from container
    local coverage_raw_tmpdir="${RUNNER_TEMP:-/tmp}/coverage-inferno-raw"
    mkdir -p "${coverage_raw_tmpdir}"
    # shellcheck disable=SC2310
    if dc cp openemr:/tmp/openemr-coverage/inferno "${coverage_raw_tmpdir}"; then
        echo "Successfully copied coverage files from container"
    else
        echo "Warning: Failed to copy coverage files (may not exist yet)"
    fi

    # Count raw coverage files
    find "${coverage_raw_tmpdir}" -type f -name '*.php' | wc -l | xargs echo 'Found raw Inferno coverage files:'

    # Convert HTTP request coverage to .cov format
    if [[ -d "${coverage_raw_tmpdir}/inferno" ]]; then
        mkdir -p coverage
        sudo chmod -R 777 coverage
        ./ci/convert-coverage "${coverage_raw_tmpdir}/inferno" \
                              coverage/coverage.inferno-http.cov \
                              --clover=coverage.inferno-http.clover.xml
        ls -lah coverage.inferno-http.clover.xml coverage/coverage.inferno-http.cov || true
    else
        echo "Warning: No Inferno HTTP coverage files found"
    fi

    # Merge all Inferno coverage (PHPUnit + HTTP requests)
    cd -P "${repo_root}"
    . ci/ciLibrary.source
    merge_coverage

    echo 'Inferno coverage collection completed'
}

fix_redis_permissions() {
     docker run --rm -v "${PWD}/onc-certification-g10-test-kit/data/redis:/data" redis chown -R redis:redis /data
}

cleanup() {
    echo 'Performing cleanup...'
    docker compose down -v || true
    echo 'Cleanup completed'
}

main() {
    # Set up trap for cleanup on exit
    trap cleanup EXIT
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

    initialize_inferno
    check_inferno
    initialize_openemr
    fix_redis_permissions

    # Run the test suite and capture exit code
    # shellcheck disable=SC2310
    if ! run_testsuite; then
        local exit_code=$?
        echo "FAILURE: Inferno certification tests failed with exit code: ${exit_code}"
        # Still try to collect coverage even on failure
        if [[ ${ENABLE_COVERAGE:-false} = true ]]; then
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
