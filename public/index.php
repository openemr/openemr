<?php

require_once __DIR__ . '/../vendor/autoload.php';

function debugGlobals(): never
{
    header('Content-type: text/plain');

    echo "Hello from front controller";

    ksort($_SERVER);
    print_r($_SERVER);
    print_r($_GET);
    print_r($_POST);

    /*
    [CONTEXT_DOCUMENT_ROOT] => /var/www/localhost/htdocs/openemr
    [CONTEXT_PREFIX] =>
    [DOCUMENT_ROOT] => /var/www/localhost/htdocs/openemr
    [GATEWAY_INTERFACE] => CGI/1.1
    [PHP_SELF] => /interface/login/login.php
    [QUERY_STRING] => site=default
    [REQUEST_METHOD] => GET
    [REQUEST_SCHEME] => http
    [REQUEST_URI] => /interface/login/login.php?site=default
    [SCRIPT_FILENAME] => /var/www/localhost/htdocs/openemr/interface/login/login.php
    [SCRIPT_NAME] => /interface/login/login.php
)
     */

    exit;
}

/**
 * @return string The absolute path to the legacy file to include
 */
function performLegacyRouting($serverGlobals): string
{
    error_log("PERFORMING FRONT CONTROLLER ROUTING");
    // debugGlobals();
    ksort($serverGlobals);
    $requestUri = $serverGlobals['REQUEST_URI'];
    if ($requestUri === '/') {
        // Special-case the document root
        $requestUri = '/index.php';
    }
    error_log("REQUEST_URI=$requestUri");

    // PHP-equivalent to `.htaccess` mod_rewrite rules
    $path = match (true) {
        str_starts_with($requestUri, '/apis') => '/apis/dispatch.php',
        default => parse_url($requestUri, PHP_URL_PATH),
    };

    error_log("path=$path");
    // Normalize the included file to the webroot
    $path = realpath(__DIR__ . '/../' . $path);

    $fileDirectory = pathinfo($path, PATHINFO_DIRNAME);
    chdir($fileDirectory);

    // This seems fairly reliably to be $_SERVER['DOCUMENT_ROOT']
    $installationRoot = dirname(__DIR__);
    if ($path === false || !str_starts_with(needle: $installationRoot, haystack: $path)) {
        header('HTTP/1.1 404 Not Found');
        echo 'Invalid path';
        exit(1);
    }

    overrideGlobals($path, $installationRoot);
    error_log("include=$path");
    // unscopedRequire($path);
    // exit(0);
    return $path;
}

function overrideGlobals(string $targetFile, string $root): void
{
    /*
    [CONTEXT_DOCUMENT_ROOT] => /var/www/localhost/htdocs/openemr
    [CONTEXT_PREFIX] =>
    [DOCUMENT_ROOT] => /var/www/localhost/htdocs/openemr
    [GATEWAY_INTERFACE] => CGI/1.1
    [PHP_SELF] => /public/index.php
    [QUERY_STRING] => site=default
    [REDIRECT_QUERY_STRING] => site=default
    [REDIRECT_STATUS] => 200
    [REDIRECT_URL] => /interface/login/login.php
    [REQUEST_METHOD] => GET
    [REQUEST_SCHEME] => http
    [REQUEST_URI] => /interface/login/login.php?site=default
    [SCRIPT_FILENAME] => /var/www/localhost/htdocs/openemr/public/index.php
    [SCRIPT_NAME] => /public/index.php
     */

    $_SERVER['SCRIPT_FILENAME'] = $targetFile;

    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($targetFile, strlen($root));
    // SCRIPT_NAME = PHP_SELF = docroot-relative
    // DOCUMENT_ROOT seems ok
}

/**
 * This uses overloading to `require` a file without introducing any new
 * variables into its scope.
 */
function unscopedRequire(/*string $path*/): void
{
    error_log(sprintf('About to require %s, r= %s', func_get_arg(0), $_SERVER['REQUEST_URI']));
    // $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = func_get_arg(0);
    require func_get_arg(0);
    error_log('require done');
}

// Future scope: Put a router ahead of the fallback routing; any well-formed
// new routes will be executed without touching the existing systems.

// For global variables to get the correct scoping, this needs to be done at
// the file root level instead of inside a function. GLOBALS and OEGlobalsBag
// are fine, but the raw variables don't get defined when called from a function
$file = performLegacyRouting($_SERVER);
require $file;
