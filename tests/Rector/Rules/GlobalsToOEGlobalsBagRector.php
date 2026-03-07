<?php

/**
 * Rector rule to replace $GLOBALS['key'] with OEGlobalsBag calls
 *
 * Transforms direct $GLOBALS access to use OEGlobalsBag:
 * - $GLOBALS['key'] (read) → \OpenEMR\Core\OEGlobalsBag::getInstance()->get('key')
 * - $GLOBALS['key'] = expr → \OpenEMR\Core\OEGlobalsBag::getInstance()->set('key', expr)
 * - isset($GLOBALS['key']) → \OpenEMR\Core\OEGlobalsBag::getInstance()->has('key')
 * - "..{$GLOBALS['key']}.." → ".." . \OpenEMR\Core\OEGlobalsBag::getInstance()->get('key') . ".."
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rector\Rules;

use OpenEMR\Core\OEGlobalsBag;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class GlobalsToOEGlobalsBagRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace $GLOBALS["key"] access with OEGlobalsBag::getInstance()->get("key")',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$value = $GLOBALS['srcdir'];
$GLOBALS['key'] = 'value';
if (isset($GLOBALS['flag'])) {}
$path = "{$GLOBALS['webroot']}/index.php";
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$value = OEGlobalsBag::getInstance()->get('srcdir');
OEGlobalsBag::getInstance()->set('key', 'value');
if (OEGlobalsBag::getInstance()->has('flag')) {}
$path = OEGlobalsBag::getInstance()->get('webroot') . "/index.php";
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
        return [
            Assign::class,
            Isset_::class,
            InterpolatedString::class,
            ArrayDimFetch::class,
        ];
    }

    /**
     * @param Assign|Isset_|InterpolatedString|ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isExcludedFile()) {
            return null;
        }

        if ($node instanceof Assign) {
            return $this->refactorAssign($node);
        }

        if ($node instanceof Isset_) {
            return $this->refactorIsset($node);
        }

        if ($node instanceof InterpolatedString) {
            return $this->refactorEncapsed($node);
        }

        if ($node instanceof ArrayDimFetch) {
            return $this->refactorRead($node);
        }

        return null;
    }

    /**
     * $GLOBALS['key'] = expr → OEGlobalsBag::getInstance()->set('key', expr)
     */
    private function refactorAssign(Assign $node): ?Node
    {
        $left = $node->var;

        if (!$left instanceof ArrayDimFetch) {
            return null;
        }

        if (!$this->isGlobalsAccess($left)) {
            return null;
        }

        $dim = $left->dim;
        if ($dim === null) {
            return null;
        }

        return new MethodCall(
            $this->buildGetInstanceCall(),
            new Identifier('set'),
            [
                new Arg($dim),
                new Arg($node->expr),
            ]
        );
    }

    /**
     * isset($GLOBALS['key']) → OEGlobalsBag::getInstance()->has('key')
     */
    private function refactorIsset(Isset_ $node): ?Node
    {
        $changed = false;

        foreach ($node->vars as $i => $var) {
            if (!$var instanceof ArrayDimFetch) {
                continue;
            }

            if ($this->isGlobalsAccess($var) && $var->dim !== null) {
                if (count($node->vars) === 1) {
                    return new MethodCall(
                        $this->buildGetInstanceCall(),
                        new Identifier('has'),
                        [new Arg($var->dim)]
                    );
                }

                $node->vars[$i] = new MethodCall(
                    $this->buildGetInstanceCall(),
                    new Identifier('has'),
                    [new Arg($var->dim)]
                );
                $changed = true;
            } elseif ($var->var instanceof ArrayDimFetch && $this->isGlobalsAccess($var->var) && $var->var->dim !== null) {
                $var->var = new MethodCall(
                    $this->buildGetInstanceCall(),
                    new Identifier('get'),
                    [new Arg($var->var->dim)]
                );
                $changed = true;
            }
        }

        return $changed ? $node : null;
    }

    /**
     * Convert interpolated strings containing $GLOBALS to concatenation.
     *
     * "prefix{$GLOBALS['key']}suffix" → "prefix" . BAG->get('key') . "suffix"
     */
    private function refactorEncapsed(InterpolatedString $node): ?Node
    {
        // Check if any part is a $GLOBALS access
        $hasGlobals = false;
        foreach ($node->parts as $part) {
            if ($part instanceof ArrayDimFetch && $this->isGlobalsAccess($part) && $part->dim !== null) {
                $hasGlobals = true;
                break;
            }
        }

        if (!$hasGlobals) {
            return null;
        }

        // Build concatenation expression from parts
        $expressions = [];
        foreach ($node->parts as $part) {
            if ($part instanceof InterpolatedStringPart) {
                $expressions[] = new String_($part->value, ['kind' => String_::KIND_DOUBLE_QUOTED]);
            } elseif ($part instanceof ArrayDimFetch && $this->isGlobalsAccess($part) && $part->dim !== null) {
                $expressions[] = new MethodCall(
                    $this->buildGetInstanceCall(),
                    new Identifier('get'),
                    [new Arg($part->dim)]
                );
            } else {
                // Other expressions (variables, etc.) — keep as-is
                $expressions[] = $part;
            }
        }

        // Filter out empty strings
        $expressions = array_values(array_filter($expressions, fn($expr) => !($expr instanceof String_ && $expr->value === '')));

        if (count($expressions) === 0) {
            return new String_('');
        }

        if (count($expressions) === 1) {
            return $expressions[0];
        }

        // Build left-associative concatenation chain
        $result = $expressions[0];
        for ($i = 1; $i < count($expressions); $i++) {
            $result = new Concat($result, $expressions[$i]);
        }

        return $result;
    }

    /**
     * $GLOBALS['key'] (read context) → OEGlobalsBag::getInstance()->get('key')
     */
    private function refactorRead(ArrayDimFetch $node): ?Node
    {
        if (!$this->isGlobalsAccess($node)) {
            return null;
        }

        if ($node->dim === null) {
            return null;
        }

        $parent = $node->getAttribute('parent');
        if ($parent instanceof Assign && $parent->var === $node) {
            return null;
        }

        if ($parent instanceof Isset_) {
            return null;
        }

        // Skip if inside an Encapsed node — handled by refactorEncapsed
        if ($parent instanceof InterpolatedString) {
            return null;
        }

        return new MethodCall(
            $this->buildGetInstanceCall(),
            new Identifier('get'),
            [new Arg($node->dim)]
        );
    }

    private function isGlobalsAccess(ArrayDimFetch $node): bool
    {
        return $node->var instanceof Variable
            && $this->isName($node->var, 'GLOBALS');
    }

    private function buildGetInstanceCall(): StaticCall
    {
        return new StaticCall(
            new FullyQualified(OEGlobalsBag::class),
            new Identifier('getInstance')
        );
    }

    private function isExcludedFile(): bool
    {
        $filePath = $this->file->getFilePath();

        foreach ([
            'interface/globals.php',
            'library/sql.inc.php',
            'library/classes/Installer.class.php',
            'library/ajax/sql_server_status.php',
            'library/smarty_legacy/smarty/internals/core.assign_smarty_interface.php',
            'sites/default/config.php',
            'src/Core/OEGlobalsBag.php',
        ] as $suffix) {
            if (str_ends_with($filePath, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
