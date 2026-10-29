<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for constant League\\\\MimeTypeDetection\\\\GeneratedExtensionToMimeTypeMap\\:\\:EXTENSIONS_FOR_MIME_TIMES with type array\\<string, string\\> is incompatible with value non\\-empty\\-array\\<literal\\-string&non\\-falsy\\-string, non\\-empty\\-list\\<literal\\-string&non\\-falsy\\-string\\>\\>\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/league/mime-type-detection/src/GeneratedExtensionToMimeTypeMap.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for constant SebastianBergmann\\\\CodeCoverage\\\\Report\\\\Html\\\\File\\:\\:KEYWORD_TOKENS with type array\\<int, true\\> is not subtype of value array\\{322\\: true, 341\\: true, 301\\: true, 307\\: true, 342\\: true, 304\\: true, 315\\: true, 333\\: true, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/phpunit/php-code-coverage/src/Report/Html/Renderer/File.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for constant SebastianBergmann\\\\CodeCoverage\\\\Report\\\\Html\\\\File\\:\\:KEYWORD_TOKENS with type array\\<int, true\\> is not subtype of value array\\{322\\: true, 341\\: true, 301\\: true, 307\\: true, 342\\: true, 304\\: true, 315\\: true, 333\\: true, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/phpunit/php-code-coverage/src/Report/Html/Renderer/File.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for constant SebastianBergmann\\\\CodeCoverage\\\\Report\\\\Html\\\\File\\:\\:KEYWORD_TOKENS with type array\\<int, true\\> is not subtype of value array\\{322\\: true, 341\\: true, 301\\: true, 307\\: true, 342\\: true, 304\\: true, 315\\: true, 333\\: true, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/phpunit/php-code-coverage/src/Report/Html/Renderer/File.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for constant SebastianBergmann\\\\CodeCoverage\\\\Report\\\\Html\\\\File\\:\\:KEYWORD_TOKENS with type array\\<int, true\\> is not subtype of value array\\{322\\: true, 341\\: true, 301\\: true, 307\\: true, 342\\: true, 304\\: true, 315\\: true, 333\\: true, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/phpunit/php-code-coverage/src/Report/Html/Renderer/File.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @var for constant SebastianBergmann\\\\CodeCoverage\\\\Report\\\\Html\\\\File\\:\\:KEYWORD_TOKENS with type array\\<int, true\\> is not subtype of value array\\{322\\: true, 341\\: true, 301\\: true, 307\\: true, 342\\: true, 304\\: true, 315\\: true, 333\\: true, \\.\\.\\.\\}\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/phpunit/php-code-coverage/src/Report/Html/Renderer/File.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
