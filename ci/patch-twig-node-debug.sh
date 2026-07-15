#!/bin/bash
# TEMPORARY (issue #12423) — patch vendor/twig/twig/src/Node/Node.php so
# Node::setSourceContext skips non-Node children (logging a diagnostic
# line) instead of throwing the TypeError.
#
# The prior version of this script also added an always-on error_log at
# the top of setSourceContext to compare fresh vs upgrade traces. Two
# consecutive passing runs (3fcc0e4, f7ea791) showed the array child
# scenario never manifested — the extra I/O appeared to widen a race
# window enough to consistently avoid the failure. This trimmed variant
# keeps only the skip branch so we can isolate whether the fix is
# behavioral (skip branch fires and rescues the render) or timing
# (either the skip branch or extra I/O — dropping I/O tells us which).
#
# Remove alongside the other debug hooks once root cause is identified.

set -euo pipefail

file="vendor/twig/twig/src/Node/Node.php"
if [[ ! -f "$file" ]]; then
    echo "PATCH SKIPPED: $file not found (composer install has not run?)" >&2
    exit 0
fi

python3 - "$file" <<'PYEOF'
import sys, re, pathlib
path = pathlib.Path(sys.argv[1])
src = path.read_text()

needle = """    public function setSourceContext(Source $source): void
    {
        $this->sourceContext = $source;
        foreach ($this->nodes as $node) {
            $node->setSourceContext($source);
        }
    }
"""

replacement = """    public function setSourceContext(Source $source): void
    {
        $this->sourceContext = $source;
        // TEMPORARY (issue #12423) debug hook: skip non-Node children with
        // a diagnostic log line instead of throwing the TypeError. The
        // always-on setSourceContext log was dropped to isolate whether the
        // fix is behavioral (this skip branch) or timing-based (extra I/O
        // widening the race window).
        foreach ($this->nodes as $key => $node) {
            if (!is_object($node)) {
                error_log(sprintf(
                    '[TWIG NODE DEBUG] NON-OBJECT CHILD: parent=%s source=%s key=%s type=%s value=%s',
                    static::class,
                    $source->getName(),
                    var_export($key, true),
                    gettype($node),
                    substr(var_export($node, true), 0, 500)
                ));
                continue;
            }
            $node->setSourceContext($source);
        }
    }
"""

if replacement in src:
    print(f"PATCH ALREADY APPLIED: {path}")
    sys.exit(0)

if needle not in src:
    print(f"PATCH NOT APPLIED: needle not found in {path}", file=sys.stderr)
    sys.exit(1)

new_src = src.replace(needle, replacement)
path.write_text(new_src)
print(f"PATCH APPLIED: {path}")
PYEOF
