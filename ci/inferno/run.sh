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

    if [[ -d "$INFERNO_FILES_DIR" && "$(ls -A $INFERNO_FILES_DIR 2>/dev/null)" ]]; then
        echo "Copying files from $INFERNO_FILES_DIR to $TARGET_DIR..."
        cp -r "$INFERNO_FILES_DIR/"* "$TARGET_DIR/"
        return 0
    fi

    echo "WARNING: $INFERNO_FILES_DIR directory is empty or missing."
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
        [[ "$line" =~ .*Expected\ codes:\ ([0-9]+)\ Actual\ codes:\ ([0-9]+).* ]] || continue
        expected="${BASH_REMATCH[1]}"
        actual="${BASH_REMATCH[2]}"
        (( actual >= expected )) && continue
        echo "ISSUE: $line (Actual codes less than Expected)"
        is_healthy=0
    done <<< "$output"

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
        cd -P "$(git rev-parse --show-toplevel)"
        . ci/ciLibrary.source
        main_build
        ccda_build
        cd -
        dockers_env_start
        install_configure
        echo 'OpenEMR initialized'
    )
}

main() {
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
    docker compose pull
    docker compose build
    if (( ! use_cloned_files )); then
      initialize_terminology
      # A second build is needed after the terminology initialization
      docker compose build
    fi

    initialize_inferno
    check_inferno
    initialize_openemr
}

main "$@"
