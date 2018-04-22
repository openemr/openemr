#!/usr/bin/env php
<?php
/**
 * @link      http://github.com/zendframework/zend-view for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

$help = <<< EOH
Generate template maps.

Usage:

templatemap_generator.php [-h|--help] templatepath <files...>

--help|-h                    Print this usage message.
templatepath                 Path to templates relative to current working
                             path; used to identify what to strip from
                             template names. Must be a directory.
<files...>                   List of files to include in the template
                             map, relative to the current working path.

The script assumes that paths included in the template map are relative
to the current working directory.

The script will output a PHP script that will return the template map
on successful completion. You may save this to a file using standard
piping operators; use ">" to write to/ovewrite a file, ">>" to append
to a file (which may have unexpected and/or intended results; you will
need to edit the file after generation to ensure it contains valid
PHP).

We recommend you then include the generated file within your module
configuration:

  'template_map' => include __DIR__ . '/template_map.config.php',

If only the templatepath argument is provided, the script will look for
all .phtml files under that directory, creating a map for you.

If you want to specify a specific list of files -- for instance, if you
are using an extension other than .phtml -- we recommend one of the
following constructs:

For any shell, you can pipe the results of `find`:

    $(find ../view -name '*.phtml')

For zsh, or bash where you have enabled globstar (`shopt  -s globstar` in
either your bash profile or from within your terminal):

    ../view/**/*.phtml

Examples:

  # Using only a templatepath argument, which will match any .phtml
  # files found under the provided path:
  $ cd module/Application/config/
  $ ../../../vendor/bin/templatemap_generator.php ../view > template_map.config.php

  # Create a template_map.config.php file in the Application module's
  # config directory, relative to the view directory, and only containing
  # .html.php files; overwrite any existing files:
  $ cd module/Application/config/
  $ ../../../vendor/bin/templatemap_generator.php ../view ../view/**/*.html.php > template_map.config.php

  # OR using find:
  $ ../../../vendor/bin/templatemap_generator.php \
  > ../view \
  > $(find ../view -name '*.html.php') > template_map.config.php
EOH;

// Called without arguments
if ($argc < 2) {
    fwrite(STDERR, 'No arguments provided.' . PHP_EOL . PHP_EOL);
    fwrite(STDERR, $help . PHP_EOL);
    exit(2);
}

// Requested help
if (in_array($argv[1], ['-h', '--help'], true)) {
    echo $help, PHP_EOL;
    exit(0);
}

// Invalid path argument
if (! is_dir($argv[1])) {
    fwrite(STDERR, 'templatepath argument is not a directory.' . PHP_EOL . PHP_EOL);
    fwrite(STDERR, $help . PHP_EOL);
    exit(2);
}

$basePath = $argv[1];
$files = ($argc < 3)
    ? findTemplateFilesInTemplatePath($basePath)
    : array_slice($argv, 2);

// No files provided
if (empty($files)) {
    fwrite(STDERR, 'No files specified.' . PHP_EOL . PHP_EOL);
    fwrite(STDERR, $help . PHP_EOL);
    exit(2);
}

$map = [];
$realPath = realpath($basePath);

$entries = array_map(function ($file) use ($basePath, $realPath) {
    $file = str_replace('\\', '/', $file);

    $template = (0 === strpos($file, $realPath))
        ? substr($file, strlen($realPath))
        : $file;

    $template = (0 === strpos($template, $basePath))
        ? substr($template, strlen($basePath))
        : $template;

    $template = preg_match('#(?P<template>.*?)\.[a-z0-9]+$#i', $template, $matches)
        ? $matches['template']
        : $template;

    $template = preg_replace('#^\.*/#', '', $template);

    return sprintf("    '%s' => __DIR__ . '/%s',", $template, $file);
}, $files);

echo '<' . "?php\nreturn [\n"
    . implode("\n", $entries) . "\n"
    . '];';

exit(0);

function findTemplateFilesInTemplatePath($templatePath)
{
    $rdi = new RecursiveDirectoryIterator(
        $templatePath,
        RecursiveDirectoryIterator::FOLLOW_SYMLINKS | RecursiveDirectoryIterator::SKIP_DOTS
    );
    $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::LEAVES_ONLY);

    $files = [];
    foreach ($rii as $file) {
        if (strtolower($file->getExtension()) != 'phtml') {
            continue;
        }

        $files[] = $file->getPathname();
    }

    return $files;
}
