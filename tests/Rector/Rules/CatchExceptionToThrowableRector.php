<?php

/**
 * Rector rule to replace catch (Exception) with catch (Throwable)
 *
 * Catching the generic Exception class misses Error subclasses. This rule
 * auto-fixes violations by replacing Exception with Throwable in catch blocks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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
     * Compare the *resolved* name, never the name as written. A bare
     * `Exception` does not imply `\Exception`: with an import in scope
     * (`use Doctrine\DBAL\Exception;`, or `use Foo\Bar as Exception;`) it
     * names an unrelated class, and rewriting that catch to `\Throwable`
     * silently widens it to swallow `\Error`. The written form carries no
     * information at all once Rector's name importing is enabled, since
     * that turns every fully-qualified catch type into a bare name.
     */
    private function isGlobalException(Name $type): bool
    {
        return strcasecmp((string) $this->getName($type), 'Exception') === 0;
    }
}
