<?php

/**
 * Rector rule to replace OEGlobalsBag->get() with typed getters
 *
 * Parses $GLOBALS_METADATA from library/globals.inc.php at construction time
 * to learn the data type of each global setting, then transforms:
 * - ->get('bool_key')              → ->getBoolean('bool_key')
 * - ->get('bool_key') === '1'      → ->getBoolean('bool_key')
 * - ->get('bool_key') != '1'       → !->getBoolean('bool_key')
 * - (bool)->get('bool_key')        → ->getBoolean('bool_key')
 * - !empty(->get('bool_key'))      → ->getBoolean('bool_key')
 * - empty(->get('bool_key'))       → !->getBoolean('bool_key')
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_ as BoolCast;
use PhpParser\Node\Expr\Cast\Int_ as IntCast;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class OEGlobalsBagTypedGettersRector extends AbstractRector
{
    /**
     * Map of metadata type → ParameterBag method name
     *
     * Only types where we can safely narrow the getter are included.
     * The 'num' type maps to getInt(); globals with float defaults
     * (env_x_width, minimum_amount_to_print, etc.) are excluded via
     * FLOAT_NUM_GLOBALS below.
     */
    private const TYPE_TO_METHOD = [
        'bool' => 'getBoolean',
        'num' => 'getInt',
        'hour' => 'getInt',
        'text' => 'getString',
        'encrypted' => 'getString',
        'encrypted_hash' => 'getString',
        'color_code' => 'getString',
        'css' => 'getString',
        'tabs_css' => 'getString',
        'lang' => 'getString',
        'all_code_types' => 'getString',
        'default_visit_category' => 'getString',
        'if_empty_create_random_uuid' => 'getString',
        'm_lang' => 'getString',
        'm_dashboard_cards' => 'getString',
    ];

    /**
     * Num globals whose defaults are floats — skip getInt() for these
     */
    private const FLOAT_NUM_GLOBALS = [
        'env_x_width',
        'env_y_height',
        'minimum_amount_to_print',
    ];

    /**
     * Map of global setting name → typed getter method name
     *
     * @var array<string, string>
     */
    private array $settingMethodMap;

    public function __construct()
    {
        $this->settingMethodMap = self::buildSettingMethodMap();
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace OEGlobalsBag->get() with typed getters based on $GLOBALS_METADATA types',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$enabled = OEGlobalsBag::getInstance()->get('enable_cdr');
$check = OEGlobalsBag::getInstance()->get('enable_cdr') === '1';
$off = OEGlobalsBag::getInstance()->get('disable_calendar') != '1';
$flag = (bool) OEGlobalsBag::getInstance()->get('enable_cdr');
$on = !empty(OEGlobalsBag::getInstance()->get('enable_cdr'));
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$enabled = OEGlobalsBag::getInstance()->getBoolean('enable_cdr');
$check = OEGlobalsBag::getInstance()->getBoolean('enable_cdr');
$off = !OEGlobalsBag::getInstance()->getBoolean('disable_calendar');
$flag = OEGlobalsBag::getInstance()->getBoolean('enable_cdr');
$on = OEGlobalsBag::getInstance()->getBoolean('enable_cdr');
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
            // Comparison patterns: ->get('bool') == '1', != '1', === '1', !== '0', etc.
            BinaryOp\Equal::class,
            BinaryOp\NotEqual::class,
            BinaryOp\Identical::class,
            BinaryOp\NotIdentical::class,
            // (bool) ->get('bool')
            BoolCast::class,
            // (int) ->get('num')
            IntCast::class,
            // !empty(->get('bool')) — handle at BooleanNot level to avoid double negation
            BooleanNot::class,
            // empty(->get('bool')) without ! prefix
            Empty_::class,
            // Standalone ->get('key') calls not in a comparison
            MethodCall::class,
        ];
    }

    /**
     * @param BinaryOp|BoolCast|IntCast|BooleanNot|Empty_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof BinaryOp) {
            return $this->refactorComparison($node);
        }

        if ($node instanceof BoolCast) {
            return $this->refactorBoolCast($node);
        }

        if ($node instanceof IntCast) {
            return $this->refactorIntCast($node);
        }

        if ($node instanceof BooleanNot) {
            return $this->refactorBooleanNot($node);
        }

        if ($node instanceof Empty_) {
            return $this->refactorEmpty($node);
        }

        if ($node instanceof MethodCall) {
            return $this->refactorStandaloneGet($node);
        }

        return null;
    }

    /**
     * ->get('bool') == '1' → ->getBoolean('bool')
     * ->get('bool') != '1' → !->getBoolean('bool')
     * ->get('bool') === '1' → ->getBoolean('bool')
     * ->get('bool') !== '0' → ->getBoolean('bool')
     * '1' == ->get('bool') → ->getBoolean('bool')  (reversed operand order)
     *
     * Only applies to boolean globals — integer globals in comparisons
     * are left for the standalone handler.
     */
    private function refactorComparison(BinaryOp $node): ?Node
    {
        // Find which side is the get() call and which is the literal
        $getCall = null;
        $literal = null;

        if ($this->isTypedGlobalGet($node->left, 'getBoolean')) {
            $getCall = $node->left;
            $literal = $node->right;
        } elseif ($this->isTypedGlobalGet($node->right, 'getBoolean')) {
            $getCall = $node->right;
            $literal = $node->left;
        }

        if ($getCall === null || $literal === null) {
            return null;
        }

        $literalIsTruthy = $this->isTruthyLiteral($literal);
        $literalIsFalsy = $this->isFalsyLiteral($literal);

        // Only handle cases where the literal is a clear bool-ish value
        if (!$literalIsTruthy && !$literalIsFalsy) {
            return null;
        }

        $typedCall = $this->convertToTypedGetter($getCall);

        // Determine if the result should be negated
        $isEquality = ($node instanceof BinaryOp\Equal || $node instanceof BinaryOp\Identical);
        // For equality: truthy literal → positive, falsy literal → negated
        // For inequality: truthy literal → negated, falsy literal → positive
        $negate = $isEquality ? $literalIsFalsy : $literalIsTruthy;

        if ($negate) {
            return new BooleanNot($typedCall);
        }

        return $typedCall;
    }

    /**
     * (bool) ->get('bool') → ->getBoolean('bool')
     */
    private function refactorBoolCast(BoolCast $node): ?Node
    {
        if (!$this->isTypedGlobalGet($node->expr, 'getBoolean')) {
            return null;
        }

        return $this->convertToTypedGetter($node->expr);
    }

    /**
     * (int) ->get('num') → ->getInt('num')
     */
    private function refactorIntCast(IntCast $node): ?Node
    {
        if (!$this->isTypedGlobalGet($node->expr, 'getInt')) {
            return null;
        }

        return $this->convertToTypedGetter($node->expr);
    }

    /**
     * !empty(->get('bool')) → ->getBoolean('bool')
     * !(->get('bool') == '0') → ->getBoolean('bool')
     */
    private function refactorBooleanNot(BooleanNot $node): ?Node
    {
        // !empty(->get('bool')) → getBoolean('bool')
        if ($node->expr instanceof Empty_ && $this->isTypedGlobalGet($node->expr->expr, 'getBoolean')) {
            return $this->convertToTypedGetter($node->expr->expr);
        }

        return null;
    }

    /**
     * empty(->get('bool')) → !->getBoolean('bool')
     * Only handles standalone empty() — the !empty() case is in refactorBooleanNot
     */
    private function refactorEmpty(Empty_ $node): ?Node
    {
        if (!$this->isTypedGlobalGet($node->expr, 'getBoolean')) {
            return null;
        }

        // Skip if parent is BooleanNot — handled by refactorBooleanNot
        $parent = $node->getAttribute('parent');
        if ($parent instanceof BooleanNot) {
            return null;
        }

        $typedCall = $this->convertToTypedGetter($node->expr);
        return new BooleanNot($typedCall);
    }

    /**
     * Standalone ->get('key') not in a comparison/cast/empty context
     */
    private function refactorStandaloneGet(MethodCall $node): ?Node
    {
        if (!$this->isKnownGlobalGet($node)) {
            return null;
        }

        // Skip if this node will be handled by a parent-level rule
        $parent = $node->getAttribute('parent');
        if (
            $parent instanceof BinaryOp\Equal
            || $parent instanceof BinaryOp\NotEqual
            || $parent instanceof BinaryOp\Identical
            || $parent instanceof BinaryOp\NotIdentical
            || $parent instanceof BoolCast
            || $parent instanceof IntCast
            || $parent instanceof Empty_
        ) {
            // Check if the other side of the comparison is a bool-ish literal
            if ($parent instanceof BinaryOp) {
                $otherSide = ($parent->left === $node) ? $parent->right : $parent->left;
                if ($this->isTruthyLiteral($otherSide) || $this->isFalsyLiteral($otherSide)) {
                    return null; // Parent comparison handler will deal with it
                }
            } else {
                return null;
            }
        }

        return $this->convertToTypedGetter($node);
    }

    /**
     * Check if a node is an OEGlobalsBag->get() call for a known global
     */
    private function isKnownGlobalGet(Expr $node): bool
    {
        if (!$node instanceof MethodCall) {
            return false;
        }

        if (!$node->name instanceof Identifier || $node->name->name !== 'get') {
            return false;
        }

        if (!$this->isOEGlobalsBagReceiver($node->var)) {
            return false;
        }

        $args = $node->getArgs();
        if (count($args) < 1) {
            return false;
        }

        $firstArg = $args[0]->value;
        if (!$firstArg instanceof String_) {
            return false;
        }

        return isset($this->settingMethodMap[$firstArg->value]);
    }

    /**
     * Check if a node is an OEGlobalsBag->get() call for a global with a specific target method
     */
    private function isTypedGlobalGet(Expr $node, string $expectedMethod): bool
    {
        if (!$this->isKnownGlobalGet($node)) {
            return false;
        }

        assert($node instanceof MethodCall);
        $key = $node->getArgs()[0]->value;
        assert($key instanceof String_);

        return ($this->settingMethodMap[$key->value] ?? null) === $expectedMethod;
    }

    /**
     * Convert ->get('key') or ->get('key', default) to the typed getter
     */
    private function convertToTypedGetter(MethodCall $node): MethodCall
    {
        $key = $node->getArgs()[0]->value;
        assert($key instanceof String_);
        $method = $this->settingMethodMap[$key->value];

        $node->name = new Identifier($method);

        // Convert or drop default argument
        $args = $node->getArgs();
        if (count($args) >= 2) {
            $converted = $this->convertDefault($args[1]->value, $method);
            if ($converted !== null) {
                $node->args[1] = new Node\Arg($converted);
            } else {
                // Can't safely convert the default — drop it
                unset($node->args[1]);
                $node->args = array_values($node->args);
            }
        }

        return $node;
    }

    private function isOEGlobalsBagReceiver(Expr $expr): bool
    {
        // OEGlobalsBag::getInstance()
        if ($expr instanceof StaticCall) {
            if ($expr->name instanceof Identifier && $expr->name->name === 'getInstance') {
                if ($expr->class instanceof Node\Name) {
                    $className = $expr->class->toString();
                    return str_contains($className, 'OEGlobalsBag');
                }
            }
            return false;
        }

        // Common variable names used after $x = OEGlobalsBag::getInstance()
        if ($expr instanceof Variable && is_string($expr->name)) {
            return in_array($expr->name, ['globalsBag', 'globals_bag'], true);
        }

        return false;
    }

    /**
     * Is this a truthy bool-ish literal? ('1', 1, '1 ', true)
     */
    private function isTruthyLiteral(Expr $node): bool
    {
        if ($node instanceof String_ && $node->value === '1') {
            return true;
        }
        if ($node instanceof Int_ && $node->value === 1) {
            return true;
        }
        if ($node instanceof Node\Expr\ConstFetch && $node->name->toLowerString() === 'true') {
            return true;
        }
        return false;
    }

    /**
     * Is this a falsy bool-ish literal? ('0', 0, '', false, null)
     */
    private function isFalsyLiteral(Expr $node): bool
    {
        if ($node instanceof String_ && ($node->value === '0' || $node->value === '')) {
            return true;
        }
        if ($node instanceof Int_ && $node->value === 0) {
            return true;
        }
        if ($node instanceof Node\Expr\ConstFetch) {
            $name = $node->name->toLowerString();
            return in_array($name, ['false', 'null'], true);
        }
        return false;
    }

    /**
     * Convert a default value node to the appropriate type for the getter
     */
    private function convertDefault(Expr $node, string $method): ?Expr
    {
        if ($method === 'getBoolean') {
            return $this->convertToBoolNode($node);
        }

        if ($method === 'getInt') {
            return $this->convertToIntNode($node);
        }

        if ($method === 'getString') {
            if ($node instanceof String_) {
                return $node;
            }
            return null;
        }

        return null;
    }

    private function convertToBoolNode(Expr $node): ?Expr
    {
        if ($node instanceof String_) {
            $val = in_array($node->value, ['1', 'true'], true);
            return new Node\Expr\ConstFetch(new Node\Name($val ? 'true' : 'false'));
        }
        if ($node instanceof Int_) {
            return new Node\Expr\ConstFetch(new Node\Name($node->value !== 0 ? 'true' : 'false'));
        }
        if ($node instanceof Node\Expr\ConstFetch) {
            $name = $node->name->toLowerString();
            if (in_array($name, ['true', 'false'], true)) {
                return $node;
            }
            // null → false (getBoolean default)
            if ($name === 'null') {
                return new Node\Expr\ConstFetch(new Node\Name('false'));
            }
        }
        return null;
    }

    private function convertToIntNode(Expr $node): ?Expr
    {
        if ($node instanceof Int_) {
            return $node;
        }
        if ($node instanceof String_ && ctype_digit($node->value)) {
            return new Int_((int) $node->value);
        }
        if ($node instanceof Node\Expr\ConstFetch) {
            $name = $node->name->toLowerString();
            if ($name === 'null') {
                return new Int_(0);
            }
        }
        return null;
    }

    /**
     * Parse $GLOBALS_METADATA from library/globals.inc.php and build a map
     * of setting name → typed getter method name.
     *
     * @return array<string, string>
     */
    private static function buildSettingMethodMap(): array
    {
        $globalsFile = dirname(__DIR__, 3) . '/library/globals.inc.php';
        if (!file_exists($globalsFile)) {
            return [];
        }

        $code = file_get_contents($globalsFile);
        if ($code === false) {
            return [];
        }
        $parser = (new \PhpParser\ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($code);
        if ($ast === null) {
            return [];
        }

        $finder = new \PhpParser\NodeFinder();
        $assignments = $finder->findInstanceOf($ast, \PhpParser\Node\Expr\Assign::class);

        $metadataExpr = null;
        foreach ($assignments as $assign) {
            if (
                $assign->var instanceof Variable
                && $assign->var->name === 'GLOBALS_METADATA'
                && $assign->expr instanceof \PhpParser\Node\Expr\Array_
            ) {
                $metadataExpr = $assign->expr;
                break;
            }
        }

        if ($metadataExpr === null) {
            return [];
        }

        $map = [];
        $floatSet = array_flip(self::FLOAT_NUM_GLOBALS);

        // $GLOBALS_METADATA[tab][setting] = [label, dataType, default, ...]
        foreach ($metadataExpr->items as $tabItem) {
            if (!$tabItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $tabArray = $tabItem->value;
            if (!$tabArray instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }

            foreach ($tabArray->items as $settingItem) {
                if (!$settingItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }

                $settingName = self::resolveStringValue($settingItem->key);
                if ($settingName === null) {
                    continue;
                }

                $settingArray = $settingItem->value;
                if (!$settingArray instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }

                $dataTypeItem = $settingArray->items[1] ?? null;
                if (!$dataTypeItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                $dataType = self::resolveStringValue($dataTypeItem->value);
                if ($dataType === null) {
                    continue;
                }

                // Skip float num globals
                if (isset($floatSet[$settingName])) {
                    continue;
                }

                if (!isset(self::TYPE_TO_METHOD[$dataType])) {
                    continue;
                }

                $map[$settingName] = self::TYPE_TO_METHOD[$dataType];
            }
        }

        return $map;
    }

    private static function resolveStringValue(?Node $node): ?string
    {
        if ($node instanceof String_) {
            return $node->value;
        }
        if ($node instanceof Int_) {
            return (string) $node->value;
        }
        return null;
    }
}
