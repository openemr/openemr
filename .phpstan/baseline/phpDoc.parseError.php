<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(Smarty\\)\\: Unexpected token "\\\\r\\\\n ", expected variable at offset 236 on line 13$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.xl.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(array\\)\\: Unexpected token "\\\\r\\\\n \\* ", expected variable at offset 218 on line 12$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty/plugins/function.xl.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @return has invalid value \\(\\)\\: Unexpected token "\\\\r\\\\n     ", expected type at offset 130 on line 5$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Database/AbstractModuleActionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(int\\)\\: Unexpected token "\\\\r\\\\n     \\* ", expected variable at offset 123 on line 4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/FileUtils.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(USPSAddress object \\$data\\)\\: Unexpected token "object", expected variable at offset 60 on line 3$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSAddressVerify.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(CurlHandler optional initialized curl handle\\)\\: Unexpected token "optional", expected variable at offset 213 on line 6$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSBase.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(integer the error code number\\)\\: Unexpected token "the", expected variable at offset 63 on line 4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSBase.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(mixed the response returned from the call\\)\\: Unexpected token "the", expected variable at offset 52 on line 4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSBase.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc tag @param has invalid value \\(string the error message\\)\\: Unexpected token "the", expected variable at offset 58 on line 4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/USPS/USPSBase.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
