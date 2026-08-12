# shellcheck shell=bash
#
# Shared ZIP-extraction helper for the acceptance package/upgrade scripts.
#
# Sourced by:
#   tests/Acceptance/bin/boot-package.sh
#   tests/Acceptance/bin/upgrade-package.sh
#
# Rationale: PackageAssembler ships both a .tar.gz and a .zip artifact.
# tar natively supports --strip-components=1; unzip does not, so both
# callers had to extract into a scratch dir, enforce exactly one
# top-level directory inside the ZIP, and mv its contents up into
# ${TARBALL_DIR}. That ~30-line dance was byte-identical between the
# two scripts; this helper hosts the one copy.
#
# Behavior — MUST match the pre-extraction inline code exactly (release-
# mechanism scripts are byte-identical-synced to rel branches; any
# behavioral drift here changes what installs on production tags):
#
#   1. If unzip is not on PATH, fail with an ::error:: message including
#      the caller-supplied noun (so the message stays consistent with
#      each caller's flag naming — "--local-zip" vs "--to-local-zip").
#   2. mktemp -d a scratch dir (caller-supplied template prefix). trap
#      cleanup on EXIT.
#   3. unzip -qo the artifact into the scratch dir.
#   4. Enforce exactly one top-level directory inside the ZIP via a
#      nullglob-guarded array — reject anything else with an ::error::
#      listing what was found.
#   5. mv the single top-level dir's contents (including dotfiles) up
#      into the destination.
#
# Trap-composition note: this helper installs an EXIT cleanup for the
# scratch dir it creates. Callers may also have their own EXIT trap for
# a downloaded artifact path. Callers' ARTIFACT_PATH cleanup only exists
# on the download branch (mutually exclusive with the local-zip branch
# that reaches this helper), so the bare `trap ... EXIT` here doesn't
# clobber a live caller trap. If a future refactor introduces coexisting
# EXIT cleanups, revisit both call sites.
#
# Not exported as an env-invoked script — sourcing keeps the trap
# installed in the caller's shell (an external script's trap would
# fire when the subshell exits, before the caller's `mv` completes,
# defeating the whole point).

# Extract a ZIP archive whose contents are wrapped in a single top-level
# directory (the shape PackageAssembler produces), flattening that
# wrapper into the destination directory.
#
# Usage:
#   extract_zip_flattening_single_top_level_dir \
#       <zip_path> <dest_dir> <mktemp_template> <error_context>
#
# Args:
#   zip_path         Path to the .zip file to extract. Must exist.
#   dest_dir         Existing directory the flattened contents move into.
#   mktemp_template  Prefix for the scratch mktemp -d template. Each
#                    caller passes a distinct prefix so overlapping
#                    invocations (e.g. debugging with two scripts
#                    concurrently) get distinct scratch dirs.
#   error_context    Human-readable phrase describing the flag that
#                    produced this zip (e.g. "--local-zip / zip
#                    extraction" or "--to-local-zip / zip extraction").
#                    Interpolated into the "unzip is required for X but
#                    was not found on PATH" error to keep each caller's
#                    diagnostic message flag-accurate.
#
# Exit: returns via a non-zero exit (not just a `return`) because the
# calling scripts run under `set -euo pipefail` and expect failures here
# to abort the whole boot/upgrade rather than continue past a broken
# extraction.
extract_zip_flattening_single_top_level_dir() {
    local zip_path="$1"
    local dest_dir="$2"
    local mktemp_template="$3"
    local error_context="$4"

    # Fail early if unzip isn't on PATH. The subsequent extract
    # would blow up mid-flight AFTER the scratch dir is prepared,
    # producing a confusing "no such file or directory" from the
    # `mv` below rather than a clear "install unzip" signal.
    # GHA ubuntu-24.04 runners ship unzip pre-installed; guard is
    # for minimal-image / self-hosted-runner future callers.
    if ! command -v unzip >/dev/null 2>&1; then
        echo "::error::unzip is required for ${error_context} but was not found on PATH" >&2
        exit 1
    fi

    # unzip has no --strip-components; extract to a temp dir first,
    # then move the single top-level `openemr-<version>/` contents up
    # into ${dest_dir} to mirror the tarball layout.
    local zip_tmp
    zip_tmp="$(mktemp -d -t "${mktemp_template}")"
    # shellcheck disable=SC2064  # want zip_tmp captured at trap-install time, not on EXIT
    trap "rm -rf '${zip_tmp}'" EXIT

    # -q quiet; -o overwrite (no interactive prompt).
    unzip -qo "${zip_path}" -d "${zip_tmp}"

    # Enforce exactly one top-level dir inside the ZIP. `mv .../*/*`
    # would silently merge if PackageAssembler ever regressed to
    # multiple top-level entries — surface that as a hard error
    # instead of a subtly-broken web root. Glob-based check avoids
    # find + process-substitution + shellcheck SC2312.
    # Helper is sourced — save the caller's nullglob+dotglob state so
    # we can restore it before returning. `shopt -p` emits shopt
    # commands that recreate the current state; eval replays them.
    #
    # `|| true` because `shopt -p X Y` exits 1 when any listed option
    # is disabled — the DEFAULT bash state, and the state every real
    # caller (boot-package.sh, upgrade-package.sh) runs under. Without
    # `|| true` the caller's `set -e` kills the script silently right
    # here (stderr is empty because the exit status is the only signal
    # and stdout of shopt is captured into the variable). Landed in
    # #13286 (Phase 10d shared helper) + #13287 shopt-leak fix — sat
    # undetected because the auto-fire acceptance push matrix uses the
    # published-artifact path and never reaches this helper; only the
    # build_locally paths (workflow_dispatch, or docker/release/
    # tools/release/** PRs) do. First exercised on today's rel-830
    # release-prep dispatch. See extract.bats "caller runs under set
    # -euo pipefail" for the regression guard.
    local shopt_saved
    shopt_saved="$(shopt -p nullglob dotglob || true)"

    local zip_roots
    shopt -s nullglob dotglob
    zip_roots=("${zip_tmp}"/*)
    if [[ ${#zip_roots[@]} -ne 1 || ! -d "${zip_roots[0]}" ]]; then
        echo "::error::expected exactly one top-level directory in ${zip_path}, found ${#zip_roots[@]}:" >&2
        printf '  %s\n' "${zip_roots[@]}" >&2
        # No shopt restore here — `exit` terminates the whole shell
        # (callers run under set -euo pipefail), so caller-visible
        # shell state is moot.
        exit 1
    fi

    # Keep nullglob+dotglob on for the flatten mv. An empty top-level
    # dir would otherwise expand `${zip_roots[0]}/*` to a literal `*`
    # and error; with nullglob the array is simply empty and we skip
    # the mv (dest stays empty — callers will fail their own presence
    # checks downstream if they expected content).
    local zip_entries
    zip_entries=("${zip_roots[0]}"/*)
    if [[ ${#zip_entries[@]} -gt 0 ]]; then
        # `|| exit 1` makes the helper's failure contract portable —
        # current callers run under set -euo pipefail so mv failure
        # would already abort, but a future caller sourcing this
        # without set -e would otherwise see eval return zero after a
        # failed mv and continue past a broken extraction.
        mv "${zip_entries[@]}" "${dest_dir}"/ || exit 1
    fi
    eval "${shopt_saved}"
}
