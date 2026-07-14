#!/bin/bash
# ci-run-2
# TEMPORARY (issue #12423) — patch vendor/twig/twig/src/Node/Node.php so
# Node::setSourceContext logs the failing child instead of throwing when it
# hits a non-Node array in $this->nodes. Also log every setSourceContext
# call so we can diff the fresh-install e2e log (which passes) against the
# upgrade-e2e log (which fails on relogin.html.twig include).
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
        // TEMPORARY (issue #12423) debug hook.
        error_log(sprintf('[TWIG NODE DEBUG] setSourceContext parent=%s source=%s node_count=%d', static::class, $source->getName(), count($this->nodes)));
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
