<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

use Zend\Http\Client;
use Zend\Validator\Hostname;

require __DIR__ . '/../vendor/autoload.php';

define('IANA_URL', 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt');
define('ZF2_HOSTNAME_VALIDATOR_FILE', __DIR__.'/../src/Hostname.php');


if (! file_exists(ZF2_HOSTNAME_VALIDATOR_FILE) || ! is_readable(ZF2_HOSTNAME_VALIDATOR_FILE)) {
    printf("Error: cannont read file '%s'%s", ZF2_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

if (! is_writable(ZF2_HOSTNAME_VALIDATOR_FILE)) {
    printf("Error: Cannot update file '%s'%s", ZF2_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

$newFileContent   = [];     // new file content
$insertDone       = false; // becomes 'true' when we find start of $validTlds declaration
$insertFinish     = false; // becomes 'true' when we find end of $validTlds declaration
$checkOnly        = isset($argv[1]) ? $argv[1] === '--check-only' : false;
$response         = getOfficialTLDs();
$ianaVersion      = getVersionFromString('Version', strtok($response->getBody(), "\n"));
$validatorVersion = getVersionFromString('IanaVersion', file_get_contents(ZF2_HOSTNAME_VALIDATOR_FILE));

if ($checkOnly && $ianaVersion > $validatorVersion) {
    printf(
        'TLDs must be updated, please run `php bin/update_hostname_validator.php` and push your changes%s',
        PHP_EOL
    );
    exit(1);
}

if ($checkOnly) {
    printf('TLDs are up-to-date%s', PHP_EOL);
    exit(0);
}

foreach (file(ZF2_HOSTNAME_VALIDATOR_FILE) as $line) {
    // Replace old version number with new one
    if (preg_match('/\*\s+IanaVersion\s+\d+/', $line, $matches)) {
        $newFileContent[] = sprintf("     * IanaVersion %s\n", $ianaVersion);
        continue;
    }

    if ($insertDone === $insertFinish) {
        // Outside of $validTlds definition; keep line as-is
        $newFileContent[] = $line;
    }

    if ($insertFinish) {
        continue;
    }

    if ($insertDone) {
        // Detect where the $validTlds declaration ends
        if (preg_match('/^\s+\];\s*$/', $line)) {
            $newFileContent[] = $line;
            $insertFinish     = true;
        }

        continue;
    }

    // Detect where the $validTlds declaration begins
    if (preg_match('/^\s+protected\s+\$validTlds\s+=\s+\[\s*$/', $line)) {
        $newFileContent = array_merge($newFileContent, getNewValidTlds($response->getBody()));
        $insertDone     = true;
    }
}

if (! $insertDone) {
    printf('Error: cannot find line with "protected $validTlds"%s', PHP_EOL);
    exit(1);
}

if (!$insertFinish) {
    printf('Error: cannot find end of $validTlds declaration%s', PHP_EOL);
    exit(1);
}

if (false === @file_put_contents(ZF2_HOSTNAME_VALIDATOR_FILE, $newFileContent)) {
    printf('Error: cannot write info file "%s"%s', ZF2_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

printf('Validator TLD file updated.%s', PHP_EOL);
exit(0);

/**
 * Get Official TLDs
 *
 * @return \Zend\Http\Response
 * @throws Exception
 */
function getOfficialTLDs()
{
    $client = new Client();
    $client->setOptions([
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
    ]);
    $client->setUri(IANA_URL);
    $client->setMethod('GET');

    $response = $client->send();
    if (! $response->isSuccess()) {
        throw new \Exception(sprintf("Error: cannot get '%s'%s", IANA_URL, PHP_EOL));
    }
    return $response;
}

/**
 * Extract the first match of a string like
 * "Version 2015072300" from the given string
 *
 * @param string $prefix
 * @param string $string
 * @return string
 * @throws Exception
 */
function getVersionFromString($prefix, $string)
{
    $matches = [];
    if (! preg_match(sprintf('/%s\s+((\d+)?)/', $prefix), $string, $matches)) {
        throw new Exception('Error: cannot get last update date');
    }

    return $matches[1];
}

/**
 * Extract new Valid TLDs from a string containing one per line.
 *
 * @param string $string
 * @return array
 */
function getNewValidTlds($string)
{
    $decodePunycode = getPunycodeDecoder();

    // Get new TLDs from the list previously fetched
    $newValidTlds = [];
    foreach (preg_grep('/^[^#]/', preg_split("#\r?\n#", $string)) as $line) {
        $newValidTlds []= sprintf(
            "%s'%s',\n",
            str_repeat(' ', 8),
            $decodePunycode(strtolower($line))
        );
    }

    return $newValidTlds;
}

/**
 * Retrieve and return a punycode decoder.
 *
 * TLDs are puny encoded.
 *
 * We need a decodePunycode function to translate TLDs to UTF-8:
 *
 * - use idn_to_utf8 if available
 * - otherwise, use Hostname::decodePunycode()
 *
 * @return callable
 */
function getPunycodeDecoder()
{
    if (function_exists('idn_to_utf8')) {
        return function ($domain) {
            return idn_to_utf8($domain, 0, INTL_IDNA_VARIANT_UTS46);
        };
    }

    $hostnameValidator = new Hostname();
    $reflection        = new ReflectionClass(get_class($hostnameValidator));
    $decodePunyCode    = $reflection->getMethod('decodePunycode');
    $decodePunyCode->setAccessible(true);

    return function ($encode) use ($hostnameValidator, $decodePunyCode) {
        if (strpos($encode, 'xn--') === 0) {
            return $decodePunyCode->invokeArgs($hostnameValidator, [substr($encode, 4)]);
        }
        return $encode;
    };
}
