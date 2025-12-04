<?php

# https://getrector.com/documentation

declare(strict_types=1);

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Php55\Rector\ClassConstFetch\StaticToSelfOnFinalClassRector;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Php70\Rector\Assign\ListSplitStringRector;
use Rector\Php70\Rector\Assign\ListSwapArrayOrderRector;
use Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use Rector\Php70\Rector\ClassMethod\Php4ConstructorRector;
use Rector\Php70\Rector\FuncCall\CallUserMethodRector;
use Rector\Php70\Rector\FuncCall\EregToPregMatchRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;
use Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Rector\Php70\Rector\If_\IfToSpaceshipRector;
use Rector\Php70\Rector\List_\EmptyListRector;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector;
use Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
use Rector\Php72\Rector\Assign\ListEachRector;
use Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;
use Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use Rector\Php72\Rector\Unset_\UnsetCastRector;
use Rector\Php72\Rector\While_\WhileEachToForeachRector;
use Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Php73\Rector\FuncCall\RegexDashEscapeRector;
use Rector\Php73\Rector\FuncCall\SensitiveDefineRector;
use Rector\Php73\Rector\FuncCall\SetCookieRector;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\Php73\Rector\String_\SensitiveHereNowDocRector;
use Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Rector\Php74\Rector\FuncCall\HebrevcToNl2brHebrevRector;
use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector;
use Rector\Php74\Rector\FuncCall\RestoreIncludePathToIniRestoreRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use Rector\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\ClassConstFetch\ClassOnThisVariableObjectRector;
use Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector;
use Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector;
use Rector\Php80\Rector\ClassMethod\SetStateToStaticRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Rector\Php81\Rector\MethodCall\RemoveReflectionSetAccessibleCallsRector;
use Rector\Php81\Rector\MethodCall\SpatieEnumMethodCallToEnumConstRector;
use Rector\Php81\Rector\New_\MyCLabsConstructorCallToEnumFromRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector;
use Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector;
use Rector\Php82\Rector\New_\FilesystemIteratorSkipDotsRector;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Renaming\Rector\Cast\RenameCastRector;
use Rector\Transform\Rector\StaticCall\StaticCallToFuncCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withBootstrapFiles([
        __DIR__ . '/rector-bootstrap.php',
    ])
    ->withPaths([
        __DIR__ . '/Documentation',
        __DIR__ . '/apis',
        __DIR__ . '/ccdaservice',
        __DIR__ . '/ccr',
        __DIR__ . '/contrib',
        __DIR__ . '/controllers',
        __DIR__ . '/custom',
        __DIR__ . '/gacl',
        __DIR__ . '/interface',
        __DIR__ . '/library',
        __DIR__ . '/modules',
        __DIR__ . '/oauth2',
        __DIR__ . '/portal',
        __DIR__ . '/sites',
        __DIR__ . '/sphere',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withCache(
        // ensure file system caching is used instead of in-memory
        cacheClass: FileCacheStorage::class,
        // specify a path that works locally as well as on CI job runners
        cacheDirectory: '/tmp/rector'
    )
    ->withCodeQualityLevel(5)
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        'allow_model_based_classes' => true,
        'inline_public' => false,
        'rename_property' => true,
    ])
    ->withDeadCodeLevel(5)
    // https://getrector.com/documentation/troubleshooting-parallel
    ->withParallel(
        timeoutSeconds: 120,
        maxNumberOfProcess: 12,
        jobSize: 12
    )
    // FIXME rector should pick the php version from composer.json
    // but that doesn't seem to be working, so hard-coding for now.
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withRules([
        // add rules one at a time until we can replace them with a named ruleset
        AddParamBasedOnParentClassMethodRector::class,
        ArgumentAdderRector::class,
        ArrayKeyExistsOnPropertyRector::class, // one of the withPhpSets rules
        ArrayKeyFirstLastRector::class, // one of the withPhpSets rules
        AssignArrayToStringRector::class, // one of the withPhpSets rules
        BinaryOpBetweenNumberAndStringRector::class, // one of the withPhpSets rules
        BreakNotInLoopOrSwitchToReturnRector::class,
        CallUserMethodRector::class,
        ChangeSwitchToMatchRector::class, // one of the withPhpSets rules
        ClassConstantToSelfClassRector::class, // one of the withPhpSets rules
        ClassOnObjectRector::class, // one of the withPhpSets rules
        ClassOnThisVariableObjectRector::class, // one of the withPhpSets rules
        ClosureToArrowFunctionRector::class, // one of the withPhpSets rules
        ConsistentImplodeRector::class, // one of the withPhpSets rules
        ContinueToBreakInSwitchRector::class,
        CreateFunctionToAnonymousFunctionRector::class, // one of the withPhpSets rules
        CurlyToSquareBracketArrayStringRector::class,
        DirNameFileConstantToDirConstantRector::class, // one of the withPhpSets rules
        EmptyListRector::class,
        EregToPregMatchRector::class, // one of the withPhpSets rules
        ExceptionHandlerTypehintRector::class,
        ExportToReflectionFunctionRector::class,
        FilesystemIteratorSkipDotsRector::class,
        FilterVarToAddSlashesRector::class,
        FinalPrivateToPrivateVisibilityRector::class,
        FirstClassCallableRector::class, // one of the withPhpSets rules
        FunctionArgumentDefaultValueReplacerRector::class,
        GetCalledClassToSelfClassRector::class,
        GetCalledClassToStaticClassRector::class, // one of the withPhpSets rules
        GetClassOnNullRector::class,
        GetDebugTypeRector::class,
        HebrevcToNl2brHebrevRector::class,
        IfIssetToCoalescingRector::class, // one of the withPhpSets rules
        IfToSpaceshipRector::class, // one of the withPhpSets rules
        IsCountableRector::class,
        IsIterableRector::class,
        ListEachRector::class,
        ListSplitStringRector::class,
        ListSwapArrayOrderRector::class,
        ListToArrayDestructRector::class, // one of the withPhpSets rules
        LongArrayToShortArrayRector::class, // one of the withPhpSets rules
        MbStrrposEncodingArgumentPositionRector::class,
        MoneyFormatToNumberFormatRector::class,
        MultiDirnameRector::class, // one of the withPhpSets rules
        MultiExceptionCatchRector::class, // one of the withPhpSets rules
        MyCLabsClassToEnumRector::class,
        MyCLabsConstructorCallToEnumFromRector::class,
        MyCLabsMethodCallToEnumConstRector::class,
        NullCoalescingOperatorRector::class, // one of the withPhpSets rules
        NullToStrictStringFuncCallArgRector::class, // one of the withPhpSets rules
        OptionalParametersAfterRequiredRector::class,
        ParenthesizeNestedTernaryRector::class,
        ParseStrWithResultArgumentRector::class,
        Php4ConstructorRector::class,
        PowToExpRector::class, // one of the withPhpSets rules
        PregReplaceEModifierRector::class,
        RandomFunctionRector::class, // one of the withPhpSets rules
        ReadOnlyClassRector::class,
        ReadOnlyPropertyRector::class, // one of the withPhpSets rules
        ReduceMultipleDefaultSwitchRector::class,
        RegexDashEscapeRector::class,
        RemoveFuncCallArgRector::class,
        RemoveParentCallWithoutParentRector::class, // one of the withPhpSets rules
        RemoveReferenceFromCallRector::class,
        RemoveReflectionSetAccessibleCallsRector::class, // one of the withPhpSets rules
        RemoveUnusedVariableInCatchRector::class, // one of the withPhpSets rules
        RemoveZeroBreakContinueRector::class,
        RenameCastRector::class,
        RenameMktimeWithoutArgsToTimeRector::class,
        ReplaceEachAssignmentWithKeyCurrentRector::class,
        ReplaceHttpServerVarsByServerRector::class, // one of the withPhpSets rules
        RestoreDefaultNullToNullableTypePropertyRector::class, // one of the withPhpSets rules
        RestoreIncludePathToIniRestoreRector::class,
        ReturnNeverTypeRector::class, // one of the withPhpSets rules
        SensitiveConstantNameRector::class, // one of the withPhpSets rules
        SensitiveDefineRector::class,
        SensitiveHereNowDocRector::class, // one of the withPhpSets rules
        SetCookieRector::class, // one of the withPhpSets rules
        SetStateToStaticRector::class,
        SimplifyIfElseToTernaryRector::class,
        SpatieEnumClassToEnumRector::class,
        SpatieEnumMethodCallToEnumConstRector::class,
        StaticCallOnNonStaticToInstanceCallRector::class,
        StaticCallToFuncCallRector::class,
        StaticToSelfOnFinalClassRector::class,
        StrContainsRector::class, // one of the withPhpSets rules
        StrEndsWithRector::class, // one of the withPhpSets rules
        StrStartsWithRector::class, // one of the withPhpSets rules
        StringClassNameToClassConstantRector::class, // one of the withPhpSets rules
        StringableForToStringRector::class, // one of the withPhpSets rules
        StringifyDefineRector::class,
        StringifyStrNeedlesRector::class, // one of the withPhpSets rules
        StringsAssertNakedRector::class,
        TernaryToElvisRector::class, // one of the withPhpSets rules
        TernaryToNullCoalescingRector::class, // one of the withPhpSets rules
        TernaryToSpaceshipRector::class,
        ThisCallOnStaticMethodToStaticCallRector::class, // one of the withPhpSets rules
        UnsetCastRector::class,
        Utf8DecodeEncodeToMbConvertEncodingRector::class, // one of the withPhpSets rules
        VarToPublicPropertyRector::class, // one of the withPhpSets rules
        VariableInStringInterpolationFixerRector::class,
        WhileEachToForeachRector::class, // one of the withPhpSets rules
        WrapVariableVariableNameInCurlyBracesRector::class, // one of the withPhpSets rules
    ])
    ->withSkip([
        __DIR__ . '/sites/default/documents/smarty'
    ])
    ->withTypeCoverageLevel(5);
