<?php

/** Bootstrap only for the disposable native HL7 receiver fixture. */

declare(strict_types=1);

$project = dirname(__DIR__, 4);
/** @return array<mixed> */
$readSettings = static function (string $file): array {
    require $file;
    $locals = get_defined_vars();
    $settings = $locals['sqlconf'] ?? null;
    if (!is_array($settings)) {
        throw new RuntimeException('Fixture database settings are unavailable');
    }
    return $settings;
};
$sqlconf = $readSettings($project . '/sites/default/sqlconf.php');
$fixturePort = $sqlconf['port'] ?? null;
if ((!is_string($fixturePort) && !is_int($fixturePort)) ||
    ($sqlconf['host'] ?? '') !== '127.0.0.1' || (int) $fixturePort !== 13387 ||
    ($sqlconf['dbase'] ?? '') !== 'hl7_fixture') {
    throw new RuntimeException('This suite requires the disposable hl7_fixture database on 127.0.0.1:13387');
}

$sessionDirectory = sys_get_temp_dir() . '/openemr-hl7-fixture-' . substr(hash('sha256', $project), 0, 12);
if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0700, true)) {
    throw new RuntimeException('Cannot create the fixture session directory');
}
ini_set('session.save_path', $sessionDirectory);
$sessionAllowWrite = true;
$_SERVER['DOCUMENT_ROOT'] = $project;
$_SERVER['SCRIPT_NAME'] = '/interface/login/login.php';
$_SERVER['SCRIPT_FILENAME'] = $project . '/interface/login/login.php';
$_SERVER['REQUEST_URI'] = '/interface/login/login.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
require $project . '/tests/bootstrap.php';
