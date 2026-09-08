# Common helpers for BATS tests of OpenEMR Docker bash scripts.
# Source this in test files: load 'helpers'

# Repo root (directory containing docker/, utilities/, tests/)
get_repo_root() {
    local dir
    dir="${BATS_TEST_FILENAME%/*}"
    while [[ -n "$dir" ]] && [[ "$dir" != "/" ]]; do
        [[ -d "${dir}/docker" ]] && [[ -d "${dir}/tests/bats/docker" ]] && echo "$dir" && return 0
        dir="${dir%/*}"
    done
    echo ""
}

# Script directory for a version (8.1.0, binary, flex)
get_script_dir() {
    local version="$1"
    local root
    root="$(get_repo_root)"
    [[ -z "$root" ]] && return 1
    echo "${root}/docker/${version}"
}

# Assert a script exists and has valid syntax for its shebang
assert_script_syntax() {
    local script_path="$1"
    [[ -f "$script_path" ]] || { echo "Script not found: $script_path"; return 1; }
    local shebang
    shebang=$(head -1 "$script_path")
    if [[ "$shebang" =~ ^#!/usr/bin/env\ bash ]] || [[ "$shebang" =~ ^#!/bin/bash ]]; then
        bash -n "$script_path" || { echo "Bash syntax check failed: $script_path"; return 1; }
    elif [[ "$shebang" =~ ^#!/bin/sh ]] || [[ "$shebang" =~ ^#!/usr/bin/env\ sh ]]; then
        # Prefer sh -n; fall back to bash -n only if sh isn't on PATH.
        # `2>/dev/null || true` would have swallowed a real syntax failure here.
        if command -v sh >/dev/null 2>&1; then
            sh -n "$script_path" || { echo "sh syntax check failed: $script_path"; return 1; }
        else
            bash -n "$script_path" || { echo "Bash (sh fallback) syntax check failed: $script_path"; return 1; }
        fi
    else
        bash -n "$script_path" || { echo "Bash syntax check failed: $script_path"; return 1; }
    fi
    return 0
}

# Assert file exists and is readable
assert_file_exists() {
    local path="$1"
    [[ -f "$path" ]] || { echo "File not found: $path"; return 1; }
    [[ -r "$path" ]] || { echo "File not readable: $path"; return 1; }
    return 0
}

# Assert script contains a given pattern
assert_script_contains() {
    local script_path="$1"
    local pattern="$2"
    [[ -f "$script_path" ]] || { echo "Script not found: $script_path"; return 1; }
    grep -q "$pattern" "$script_path" || { echo "Pattern not found in $script_path: $pattern"; return 1; }
    return 0
}

# Assert file (any text file) contains a given pattern
assert_file_contains() {
    local path="$1"
    local pattern="$2"
    [[ -f "$path" ]] || { echo "File not found: $path"; return 1; }
    grep -q "$pattern" "$path" || { echo "Pattern not found in $path: $pattern"; return 1; }
    return 0
}

# Assert directory exists
assert_dir_exists() {
    local path="$1"
    [[ -d "$path" ]] || { echo "Directory not found: $path"; return 1; }
    return 0
}

# Count lines matching pattern in file; assert count >= min
assert_pattern_count_ge() {
    local path="$1"
    local pattern="$2"
    local min="$3"
    [[ -f "$path" ]] || { echo "File not found: $path"; return 1; }
    local count
    # `grep -c` returns exit 1 when it prints "0" matches. Without the `|| true`
    # the prior form `|| echo 0` appended a second "0" producing a non-numeric
    # multi-line count that always failed the -ge comparison.
    count=$(grep -c "$pattern" "$path" 2>/dev/null) || true
    [[ "$count" -ge "$min" ]] || { echo "Pattern $pattern found $count times in $path (expected >= $min)"; return 1; }
    return 0
}

# Create an executable command stub in a directory.
# Usage: create_stub_command <stub_dir> <command_name> <script_body>
create_stub_command() {
    local stub_dir="$1"
    local command_name="$2"
    local script_body="$3"
    mkdir -p "$stub_dir" || return 1
    printf '%s\n' "$script_body" > "${stub_dir}/${command_name}" || return 1
    chmod +x "${stub_dir}/${command_name}" || return 1
}

# Run a command with PATH prefixed by the given stub directory.
# Usage: run_with_stubbed_path <stub_dir> <cmd> [args...]
run_with_stubbed_path() {
    local stub_dir="$1"
    shift
    run env PATH="${stub_dir}:$PATH" "$@"
}
