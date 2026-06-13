#!/usr/bin/env bash
# ============================================================================
# OpenEMR Container Functionality Test Runner
# ============================================================================
# Wrapper that runs the container_benchmarking test_suite.sh for a given
# OpenEMR container type (8.1.0, binary, flex). Used by CI to ensure all
# three container variants perform as expected.
#
# Usage:
#   ./test_functionality.sh <version>
#   ./test_functionality.sh 8.1.0
#   ./test_functionality.sh binary
#   ./test_functionality.sh flex
#
# Optional: pass through any test_suite.sh arguments after the version:
#   ./test_functionality.sh 8.1.0 --test fresh_installation
#   ./test_functionality.sh flex --verbose
#
# Environment (optional):
#   FLEX_REPOSITORY       - Git URL for flex container (default: openemr/openemr)
#   FLEX_REPOSITORY_BRANCH - Branch for flex container (default: master)
# ============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TEST_SUITE="${SCRIPT_DIR}/test_suite.sh"

usage() {
    echo "Usage: $0 <version> [test_suite.sh options...]"
    echo ""
    echo "  version    One of: 8.1.0, binary, flex"
    echo ""
    echo "  Options (passed to test_suite.sh):"
    echo "    --test TEST_NAME    Run only the named test"
    echo "    --verbose           Verbose output"
    echo "    --keep-containers   Do not remove containers after tests"
    echo ""
    echo "Examples:"
    echo "  $0 8.1.0"
    echo "  $0 binary --test fresh_installation"
    echo "  $0 flex --verbose"
    exit 1
}

if [[ $# -lt 1 ]]; then
    usage
fi

VERSION="$1"
shift

case "${VERSION}" in
    8.1.0)
        export DOCKERFILE_CONTEXT="${DOCKERFILE_CONTEXT:-${SCRIPT_DIR}/../../docker/openemr/8.1.0}"
        export IMAGE_TAG="${IMAGE_TAG:-openemr:8.1.0-test}"
        ;;
    binary)
        export DOCKERFILE_CONTEXT="${DOCKERFILE_CONTEXT:-${SCRIPT_DIR}/../../docker/openemr/binary}"
        export IMAGE_TAG="${IMAGE_TAG:-openemr:binary-test}"
        ;;
    flex)
        export DOCKERFILE_CONTEXT="${DOCKERFILE_CONTEXT:-${SCRIPT_DIR}/../../docker/openemr/flex}"
        export IMAGE_TAG="${IMAGE_TAG:-openemr:flex-test}"
        export FLEX_REPOSITORY="${FLEX_REPOSITORY:-https://github.com/openemr/openemr.git}"
        export FLEX_REPOSITORY_BRANCH="${FLEX_REPOSITORY_BRANCH:-master}"
        ;;
    *)
        echo "Error: unknown version '${VERSION}'. Use 8.1.0, binary, or flex."
        usage
        ;;
esac

export VERSION

if [[ ! -x "${TEST_SUITE}" ]]; then
    echo "Error: test_suite.sh not found or not executable: ${TEST_SUITE}"
    exit 1
fi

exec "${TEST_SUITE}" "$@"
