#!/usr/bin/env bash
# ============================================================================
# OpenEMR Flex Kcov Coverage Wrapper Script
# ============================================================================
# This script wraps the OpenEMR startup script (openemr.sh) with kcov
# code coverage instrumentation. It's used for generating code coverage
# reports during testing.
#
# Kcov is a code coverage tool that instruments code execution and generates
# coverage reports showing which lines of code were executed during tests.
#
# Usage:
#   Called automatically when using the kcov build target:
#   docker build --target kcov -t openemr-flex:kcov .
#
# Coverage Reports:
#   Coverage reports are written to: /var/www/localhost/htdocs/coverage/
#   These reports can be accessed via HTTP after the container starts.
# ============================================================================

set -euo pipefail

# ============================================================================
# CREATE COVERAGE OUTPUT DIRECTORY
# ============================================================================
# Create the directory where kcov will write coverage reports
mkdir -p /var/www/localhost/htdocs/coverage

# ============================================================================
# TWO-PASS BUILD-THEN-INSTRUMENT (openemr-devops#797)
# ============================================================================
# kcov uses ptrace with PTRACE_O_TRACECLONE/FORK/VFORK, which unconditionally
# follows every fork in the traced process tree. There is no kcov flag to
# disable this (--include-path only filters which lines get reported, not
# which processes get traced — see kcov issue #149, open since 2016).
#
# That means anything openemr.sh shells out to during startup — composer,
# npm install, napa, webpack workers, libuv threadpool — gets ptrace-
# stop/resumed on every clone(). For webpack 5 specifically this produces a
# ~18x slowdown, pushing the 8.5 cell past the 10m healthcheck start_period.
#
# Workaround: run the heavy build OUTSIDE kcov, then run the rest UNDER kcov.
# Both passes use the same openemr.sh; env vars gate the behavior.
#   Pass 1 (FLEX_BUILD_ONLY=yes):    runs the early setup (swarm coord,
#                                    ssl.sh background fork, source fetch)
#                                    PLUS the heavy build block, then waits
#                                    for ssl.sh and exits before the
#                                    sqlconf / auto_setup / redis / apache
#                                    tail.
#   Pass 2 (FORCE_NO_BUILD_MODE=yes  + FLEX_SKIP_APACHE_EXEC=yes): build
#                                    block short-circuits, everything else
#                                    runs under kcov; pass 2 returns cleanly
#                                    instead of exec-ing apache so kcov can
#                                    finalize the coverage report (this
#                                    wrapper then execs apache itself).
# Coverage on the build-block bash branches is dropped, but those lines are
# mostly bare command invocations; the meaningful orchestration paths
# (sqlconf, redis, AUTHORITY tail, apache start) are fully covered.

# Pass 1: build outside kcov
FLEX_BUILD_ONLY=yes /var/www/localhost/htdocs/openemr.sh

# Pass 2: orchestration under kcov, build already done. FLEX_SKIP_APACHE_EXEC
# is critical here: if openemr.sh's `exec /usr/sbin/httpd -D FOREGROUND`
# succeeds, bash gets replaced by apache and kcov never sees an exit signal
# from its tracee, so the coverage buffer never finalizes. We saw this on
# the first iteration of this PR — only 1/497 lines recorded. Setting
# FLEX_SKIP_APACHE_EXEC=yes makes openemr.sh exit cleanly; kcov writes the
# full report; the wrapper then execs apache itself below.
FORCE_NO_BUILD_MODE=yes FLEX_SKIP_APACHE_EXEC=yes kcov \
    --include-path=/var/www/localhost/htdocs/openemr.sh,/root/devtoolsLibrary.source \
    /var/www/localhost/htdocs/coverage \
    /var/www/localhost/htdocs/openemr.sh

# ============================================================================
# START APACHE SERVER
# ============================================================================
# Pass 2's openemr.sh skips its own apache exec (see FLEX_SKIP_APACHE_EXEC
# above) so kcov can finalize. Wrapper takes over here.
exec /usr/sbin/httpd -D FOREGROUND
