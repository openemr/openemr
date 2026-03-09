<?php

/**
 * PHPStan rule to discourage OEGlobalsBag->get() for known-typed globals
 *
 * When a global setting has a known type in $GLOBALS_METADATA, callers should
 * use the appropriate typed getter (getBoolean, getInt, getString) instead of
 * the untyped get() method. This rule reports violations with a suggestion.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PHPStan\Rules;

use OpenEMR\Core\OEGlobalsBag;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
class OEGlobalsBagTypedGetterRule implements Rule
{
    /**
     * Map of metadata type → [method, label]
     */
    private const TYPE_TO_GETTER = [
        'bool' => ['getBoolean', 'boolean'],
        // Uncomment these when the Rector rule is expanded to handle them:
        // 'num' => ['getInt', 'integer'],
        // 'hour' => ['getInt', 'integer'],
        // 'text' => ['getString', 'string'],
        // 'encrypted' => ['getString', 'string'],
        // 'encrypted_hash' => ['getString', 'string'],
        // 'color_code' => ['getString', 'string'],
        // 'css' => ['getString', 'string'],
        // 'tabs_css' => ['getString', 'string'],
        // 'lang' => ['getString', 'string'],
        // 'all_code_types' => ['getString', 'string'],
        // 'default_visit_category' => ['getString', 'string'],
        // 'if_empty_create_random_uuid' => ['getString', 'string'],
        // 'm_lang' => ['getString', 'string'],
        // 'm_dashboard_cards' => ['getString', 'string'],
    ];

    /**
     * Num globals whose defaults are floats — getInt() would truncate
     */
    private const FLOAT_NUM_GLOBALS = [
        'env_x_width' => true,
        'env_y_height' => true,
        'minimum_amount_to_print' => true,
    ];

    /**
     * Map of global name → [method, label]
     *
     * @var array<string, array{string, string}>
     */
    private array $settingGetterMap;

    public function __construct()
    {
        $this->settingGetterMap = self::buildSettingGetterMap();
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier || $node->name->name !== 'get') {
            return [];
        }

        // Verify the receiver is OEGlobalsBag
        $callerType = $scope->getType($node->var);
        if (!(new ObjectType(OEGlobalsBag::class))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        if (count($args) < 1) {
            return [];
        }

        $firstArg = $args[0]->value;
        if (!$firstArg instanceof String_) {
            return [];
        }

        $key = $firstArg->value;
        if (!isset($this->settingGetterMap[$key])) {
            return [];
        }

        [$method, $label] = $this->settingGetterMap[$key];

        return [
            RuleErrorBuilder::message(
                sprintf(
                    "Use %s('%s') instead of get('%s') for %s globals.",
                    $method,
                    $key,
                    $key,
                    $label,
                )
            )
                ->identifier('openemr.untypedGlobalGet')
                ->tip(sprintf('OEGlobalsBag extends ParameterBag which provides typed getters: getBoolean(), getInt(), getString()'))
                ->build(),
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    private static function buildSettingGetterMap(): array
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
                $assign->var instanceof \PhpParser\Node\Expr\Variable
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
                $dataTypeNode = $dataTypeItem->value;
                $dataType = self::resolveStringValue($dataTypeNode);
                if ($dataType === null) {
                    continue;
                }

                // Skip float num globals — getInt() would truncate
                if (isset(self::FLOAT_NUM_GLOBALS[$settingName])) {
                    continue;
                }

                if (!isset(self::TYPE_TO_GETTER[$dataType])) {
                    continue;
                }

                $map[$settingName] = self::TYPE_TO_GETTER[$dataType];
            }
        }

        return $map;
    }

    private static function resolveStringValue(?Node $node): ?string
    {
        if ($node instanceof String_) {
            return $node->value;
        }
        if ($node instanceof \PhpParser\Node\Scalar\Int_) {
            return (string) $node->value;
        }
        return null;
    }
}
