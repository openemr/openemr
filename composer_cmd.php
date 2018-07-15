<?php
/* Tool to manage composer dependent packages locally
 * Based on gist ahsankhatri/webComposer.php
 *
 * Optional parameters: cmd=update 
 * 
 */

// This is just a sample code, do not use it on production as this is insecure
// For security, you may use .htaccess IP Access or HTTP Basic Aauthentication

require 'vendor/autoload.php';

$allowedCommands = [
    'update',
    'install',
    'dump-autoload',
    'dump-autoload -o',
];

showOptions($allowedCommands);

if ( !isset($_GET['cmd']) ) {
    exit('<br />');
}

$cmdRaw = base64_decode($_GET['cmd']);

if ( !in_array($cmdRaw, $allowedCommands) ) {
    exit;
}

$cmdRawArray = explode(' ', $cmdRaw);
$inputArray = ['command' => array_shift($cmdRawArray) ] + $cmdRawArray;

ini_set('memory_limit', '1G');
set_time_limit(300); // 5 minutes execution

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput as Output;
use Symfony\Component\Console\Output\OutputInterface;

$isDebug = isset($_GET['debug']) ? true : false;

// set COMPOSER_HOME environment
putenv('COMPOSER_HOME=' . __DIR__ . '/vendor/bin/composer');
putenv('COMPOSER_CACHE_DIR=' . __DIR__ . '/vendor/composer/cache');

$output = new Output(
    $isDebug ? OutputInterface::VERBOSITY_DEBUG : OutputInterface::VERBOSITY_NORMAL
    );

$input = new ArrayInput( $inputArray );
$application = new Application();
$application->setAutoExit(false);
$application->run($input, $output);

echo '<pre>' . $output->fetch() . '</pre>';

function showOptions($allowedCommands) {
    $buttons = [];
    foreach ($allowedCommands as $cmd) {
        $buttons[] = '<button type="button" onclick="window.location=\'' . $_SERVER['SCRIPT_NAME'] . '?cmd=' . base64_encode($cmd) . '\'">composer ' . $cmd . '</button>';
    }
    
    echo implode('&nbsp;', $buttons) . '<hr>';
}
