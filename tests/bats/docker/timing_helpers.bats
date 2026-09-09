# BATS: Docker entrypoint timing helpers

load 'helpers'

setup() {
    ROOT="$(get_repo_root)"
}

@test "docker devtools libraries: timing helpers use EPOCHREALTIME microseconds" {
    local lib
    for lib in \
        "${ROOT}/docker/binary/utilities/devtoolsLibrary.source" \
        "${ROOT}/docker/release/utilities/devtoolsLibrary.source" \
        "${ROOT}/docker/flex/utilities/devtoolsLibrary.source"; do
        (
            # shellcheck source=/dev/null
            source "${lib}"

            grep -Fq '${EPOCHREALTIME/[.,]/}' "${lib}" || exit 1
            [[ "$(current_time_us)" =~ ^[0-9]+$ ]] || exit 1
            [[ "$(elapsed_time_us 1000000 1234567)" == "234567" ]] || exit 1
            [[ "$(elapsed_time_us 1234567 1000000)" == "0" ]] || exit 1
            [[ "$(format_elapsed_seconds 4999)" == "0.00" ]] || exit 1
            [[ "$(format_elapsed_seconds 5000)" == "0.01" ]] || exit 1
            [[ "$(format_elapsed_seconds 234567)" == "0.23" ]] || exit 1
        )
    done
}

@test "docker entrypoints: startup timing does not use busybox date nanoseconds or python" {
    local script
    for script in \
        "${ROOT}/docker/binary/openemr.sh" \
        "${ROOT}/docker/release/openemr.sh" \
        "${ROOT}/docker/flex/openemr.sh"; do
        ! grep -q 'date +%s\.%N' "${script}" || exit 1
        ! grep -q 'python3 -c "print(round' "${script}" || exit 1
    done
}
