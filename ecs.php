<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use PhpCsFixer\Fixer\Alias\EregToPregFixer;
use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\Alias\PowToExponentiationFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseKeywordsFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\ModernizeTypesCastingFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoPhp4ConstructorFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\ElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSemicolonToColonFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchCaseSpaceFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoSpacesAfterFunctionNameFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\Import\SingleLineAfterImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DirConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ErrorSuppressionFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\BlankLineAfterNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Naming\NoHomoglyphNamesFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\IncrementStyleFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocTagRenameFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTagTypeFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoClosingTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\IndentationTypeFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesInsideParenthesisFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        // __DIR__ . '/_rest_routes.inc.php',
        // __DIR__ . '/acl_upgrade.php',
        // __DIR__ . '/admin.php',
        // __DIR__ . '/controller.php',
        __DIR__ . '/ecs.php',
        // __DIR__ . '/index.php',
        // __DIR__ . '/ippf_upgrade.php',
        // __DIR__ . '/phpstan_panther_alias.php',
        // __DIR__ . '/rector.php',
        // __DIR__ . '/setup.php',
        // __DIR__ . '/sql_patch.php',
        // __DIR__ . '/sql_upgrade.php',
        // __DIR__ . '/version.php',
        // __DIR__ . '/apis',
        // __DIR__ . '/apis/routes',
        // __DIR__ . '/ccr',
        // __DIR__ . '/controllers',
        // __DIR__ . '/custom',
        // __DIR__ . '/gacl',
        // __DIR__ . '/interface',
        // __DIR__ . '/library',
        // __DIR__ . '/modules',
        // __DIR__ . '/oauth2',
        // __DIR__ . '/portal',
        // __DIR__ . '/sites',
        // __DIR__ . '/sphere',
        // __DIR__ . '/src',
        // __DIR__ . '/src/Billing',
        // __DIR__ . '/src/Common',
        // __DIR__ . '/src/Controllers',
        // __DIR__ . '/src/Core',
        // __DIR__ . '/src/Cqm',
        // __DIR__ . '/src/Easpiro',
        // __DIR__ . '/src/Entity',
        // __DIR__ . '/src/FHIR',
        // __DIR__ . '/src/Gacl',
        // __DIR__ . '/src/MedicalDevice',
        // __DIR__ . '/src/Menu',
        // __DIR__ . '/src/OeUI',
        // __DIR__ . '/src/Patient',
        // __DIR__ . '/src/PaymentProcessing',
        // __DIR__ . '/src/Pdf',
        // __DIR__ . '/src/Pharmacy',
        // __DIR__ . '/src/Reminder',
        // __DIR__ . '/src/Reports',
        // __DIR__ . '/src/RestControllers',
        // __DIR__ . '/src/Rx',
        // __DIR__ . '/src/Services',
        // __DIR__ . '/src/Tabs',
        // __DIR__ . '/src/Telemetry',
        // __DIR__ . '/src/Tools',
        // __DIR__ . '/src/USPS',
        // __DIR__ . '/src/Validators',
        // __DIR__ . '/tests',
        // __DIR__ . '/tests/Tests/Api',
    ])
    ->withRules([
        BinaryOperatorSpacesFixer::class,
        BlankLineAfterNamespaceFixer::class,
        BlankLineAfterOpeningTagFixer::class,
        BlankLineBeforeStatementFixer::class,
        CastSpacesFixer::class,
        ClassAttributesSeparationFixer::class,
        CombineConsecutiveIssetsFixer::class,
        CombineConsecutiveUnsetsFixer::class,
        DeclareEqualNormalizeFixer::class,
        DirConstantFixer::class,
        ElseifFixer::class,
        EncodingFixer::class,
        EregToPregFixer::class,
        ErrorSuppressionFixer::class,
        FullOpeningTagFixer::class,
        FunctionDeclarationFixer::class,
        FunctionToConstantFixer::class,
        FunctionTypehintSpaceFixer::class,
        GeneralPhpdocTagRenameFixer::class,
        IncludeFixer::class,
        IndentationTypeFixer::class,
        IsNullFixer::class,
        LineEndingFixer::class,
        LowercaseCastFixer::class,
        LowercaseKeywordsFixer::class,
        LowercaseStaticReferenceFixer::class,
        MagicConstantCasingFixer::class,
        MethodArgumentSpaceFixer::class,
        ModernizeTypesCastingFixer::class,
        NativeConstantInvocationFixer::class,
        NativeFunctionCasingFixer::class,
        NewWithBracesFixer::class,
        NoAliasFunctionsFixer::class,
        NoBlankLinesAfterClassOpeningFixer::class,
        NoBlankLinesAfterPhpdocFixer::class,
        NoBreakCommentFixer::class,
        NoClosingTagFixer::class,
        NoEmptyCommentFixer::class,
        NoEmptyPhpdocFixer::class,
        NoEmptyStatementFixer::class,
        NoHomoglyphNamesFixer::class,
        NoLeadingImportSlashFixer::class,
        NoLeadingNamespaceWhitespaceFixer::class,
        NoMultilineWhitespaceAroundDoubleArrowFixer::class,
        NonPrintableCharacterFixer::class,
        NoNullPropertyInitializationFixer::class,
        NoPhp4ConstructorFixer::class,
        NormalizeIndexBraceFixer::class,
        NoShortBoolCastFixer::class,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        NoSpacesAfterFunctionNameFixer::class,
        NoSpacesAroundOffsetFixer::class,
        NoSpacesInsideParenthesisFixer::class,
        NoSuperfluousElseifFixer::class,
        NoTrailingCommaInListCallFixer::class,
        NoTrailingCommaInSinglelineArrayFixer::class,
        NoTrailingWhitespaceFixer::class,
        NoTrailingWhitespaceInCommentFixer::class,
        NoUnneededControlParenthesesFixer::class,
        NoUnneededCurlyBracesFixer::class,
        NoUnneededFinalMethodFixer::class,
        NoUnusedImportsFixer::class,
        NoUselessElseFixer::class,
        NoWhitespaceBeforeCommaInArrayFixer::class,
        NoWhitespaceInBlankLineFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        OrderedImportsFixer::class,
        PhpdocIndentFixer::class,
        PhpdocInlineTagNormalizerFixer::class,
        PhpdocNoAccessFixer::class,
        PhpdocNoEmptyReturnFixer::class,
        PhpdocNoUselessInheritdocFixer::class,
        PhpdocReturnSelfReferenceFixer::class,
        PhpdocScalarFixer::class,
        PhpdocSeparationFixer::class,
        PhpdocSingleLineVarSpacingFixer::class,
        PhpdocTagTypeFixer::class,
        PhpdocTrimFixer::class,
        PhpdocTypesFixer::class,
        PhpdocVarWithoutNameFixer::class,
        PhpUnitDedicateAssertFixer::class,
        PhpUnitFqcnAnnotationFixer::class,
        PowToExponentiationFixer::class,
        ProtectedToPrivateFixer::class,
        ReturnTypeDeclarationFixer::class,
        SelfAccessorFixer::class,
        ShortScalarCastFixer::class,
        SingleBlankLineAtEofFixer::class,
        SingleBlankLineBeforeNamespaceFixer::class,
        SingleClassElementPerStatementFixer::class,
        SingleImportPerStatementFixer::class,
        SingleLineAfterImportsFixer::class,
        SingleQuoteFixer::class,
        SpaceAfterSemicolonFixer::class,
        StandardizeNotEqualsFixer::class,
        SwitchCaseSemicolonToColonFixer::class,
        SwitchCaseSpaceFixer::class,
        TernaryOperatorSpacesFixer::class,
        TernaryToNullCoalescingFixer::class,
        TrimArraySpacesFixer::class,
        UnaryOperatorSpacesFixer::class,
        WhitespaceAfterCommaInArrayFixer::class,
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(BracesFixer::class, ['allow_single_line_closure' => true])
    ->withConfiguredRule(ClassDefinitionFixer::class, ['single_item_single_line' => true, 'multi_line_extends_each_single_line' => true])
    ->withConfiguredRule(ConcatSpaceFixer::class, ['spacing' => 'one'])
    ->withConfiguredRule(ConstantCaseFixer::class, ['case' => 'lower'])
    ->withConfiguredRule(IncrementStyleFixer::class, ['style' => 'pre'])
    ->withConfiguredRule(ListSyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(NoExtraBlankLinesFixer::class, ['tokens' => ['break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use']])
    ->withConfiguredRule(NoMixedEchoPrintFixer::class, ['use' => 'echo'])
    ->withConfiguredRule(NoSuperfluousPhpdocTagsFixer::class, ['allow_mixed' => true])
    ->withConfiguredRule(OperatorLinebreakFixer::class, ['only_booleans' => true, 'position' => 'end'])
    ->withConfiguredRule(PhpdocTypesOrderFixer::class, ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'])
    ->withConfiguredRule(SingleLineCommentStyleFixer::class, ['comment_types' => ['hash']])
    ->withConfiguredRule(TrailingCommaInMultilineFixer::class, ['elements' => ['arrays', 'arguments', 'parameters']])
    ->withConfiguredRule(VisibilityRequiredFixer::class, ['elements' => ['const', 'property', 'method']])
    ->withParallel(maxNumberOfProcess: 4, jobSize: 10)
;
