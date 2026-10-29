<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^You should use assertCount\\(\\$expectedCount, \\$variable\\) instead of assertSame\\(\\$expectedCount, count\\(\\$variable\\)\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/tests/Unit/Translation/Hl7XmlToTextTest.php',
];
$ignoreErrors[] = [
    'message' => '#^You should use assertCount\\(\\$expectedCount, \\$variable\\) instead of assertSame\\(\\$expectedCount, count\\(\\$variable\\)\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/tests/Service/UatFixtureCorpusTest.php',
];
$ignoreErrors[] = [
    'message' => '#^You should use assertCount\\(\\$expectedCount, \\$variable\\) instead of assertSame\\(\\$expectedCount, count\\(\\$variable\\)\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/tests/Uat/TestCaseMatrixTest.php',
];
$ignoreErrors[] = [
    'message' => '#^You should use assertCount\\(\\$expectedCount, \\$variable\\) instead of assertSame\\(\\$expectedCount, count\\(\\$variable\\)\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/tests/Tests/Unit/Services/Reports/PatientList/PatientListQueryBuilderTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
