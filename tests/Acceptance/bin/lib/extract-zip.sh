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
    local zip_roots
    shopt -s nullglob
    zip_roots=("${zip_tmp}"/*)
    shopt -u nullglob
    if [[ ${#zip_roots[@]} -ne 1 || ! -d "${zip_roots[0]}" ]]; then
        echo "::error::expected exactly one top-level directory in ${zip_path}, found ${#zip_roots[@]}:" >&2
        printf '  %s\n' "${zip_roots[@]}" >&2
        exit 1
    fi
    shopt -s dotglob
    mv "${zip_roots[0]}"/* "${dest_dir}"/
    shopt -u dotglob
}
