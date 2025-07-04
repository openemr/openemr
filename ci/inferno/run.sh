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
    (
    	repo_root="$(git rev-parse --show-toplevel)" || exit 1
	cd -P "${repo_root}"
        . ci/ciLibrary.source
        main_build
        ccda_build
        cd -
        dockers_env_start
        install_configure
	~/bin/openemr-cmd pc inferno-files/files/resources/openemr-snapshots/2025-06-25-inferno-baseline.tgz
	~/bin/openemr-cmd rs 2025-06-25-inferno-baseline
        #configure_coverage
        echo 'OpenEMR initialized'
    ) || exit 1
}
run_testsuite() {
    local -x DOCKER_DIR=inferno
    local -x OPENEMR_DIR=/var/www/localhost/htdocs/openemr
    (
    	repo_root="$(git rev-parse --show-toplevel)" || exit 1
	cd -P "${repo_root}"
        . ci/ciLibrary.source
	cd -
	phpunit --testsuite certification -c ${OPENEMR_DIR}/phpunit.xml
	#merge_coverage
	echo 'Certification Tests Executed'
    ) || exit 1
}

fix_redis_permissions() {
     docker run --rm -v ${PWD}/onc-certification-g10-test-kit/data/redis:/data redis chown -R redis:redis /data
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
    
    if ! run_testsuite; then
        local exit_code=$?
        echo "FAILURE: Inferno certification tests failed with exit code: ${exit_code}"
        exit "${exit_code}"
    fi
    echo 'SUCCESS: All Inferno certification tests completed successfully!'
}

main "$@"
