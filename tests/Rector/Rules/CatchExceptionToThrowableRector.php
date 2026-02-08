<?php

/**
 * Rector rule to replace catch (Exception) with catch (Throwable)
 *
 * Catching the generic Exception class misses Error subclasses. This rule
 * auto-fixes violations by replacing Exception with Throwable in catch blocks.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Catch_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \OpenEMR\Rector\Rules\CatchExceptionToThrowableRectorTest
 */
class CatchExceptionToThrowableRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace catch (Exception) with catch (Throwable) to also catch Error subclasses',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
try {
    doSomething();
} catch (\Exception $e) {
    handleError($e);
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
try {
    doSomething();
} catch (\Throwable $e) {
    handleError($e);
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $changed = false;

        foreach ($node->types as $index => $type) {
            if (!$this->isGlobalException($type)) {
                continue;
            }

            $node->types[$index] = new FullyQualified('Throwable');
            $changed = true;
        }

        if (!$changed) {
            return null;
        }

        return $node;
    }

    /**
     * Check if a catch type refers to the global \Exception class.
     *
     * After Rector's name resolution, unqualified names in a namespace
     * become fully qualified with the namespace prefix. Only match
     * names that resolve to exactly \Exception (no namespace prefix),
     * or unqualified 'Exception' in the global namespace.
     */
    private function isGlobalException(Name $type): bool
    {
        // FullyQualified nodes: match only \Exception (toString returns 'Exception')
        if ($type instanceof FullyQualified) {
            return strcasecmp($type->toString(), 'Exception') === 0;
        }

        // Unqualified Name: only possible in global namespace (no namespace prefix added)
        return strcasecmp($type->toString(), 'Exception') === 0;
    }
}
