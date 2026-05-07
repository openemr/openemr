#!/usr/bin/env php
<?php

/**
 * CLI entry point for TagCreator. Invoked by the conductor workflow on
 * merge of the release-prep PR.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

// OpenEMR\Release\ classes live under autoload-dev so composer-require-checker
// does not demand conductor-only deps in production. Anything invoking this
// script needs a `composer install` that includes dev dependencies.
if (!class_exists(\OpenEMR\Release\TagCreator::class)) {
    fwrite(
        STDERR,
        "OpenEMR\\Release\\ classes are not autoloadable; rerun composer install with dev dependencies.\n",
    );
    exit(2);
}

use OpenEMR\Release\OptionReader;
use OpenEMR\Release\TagCreationRequest;
use OpenEMR\Release\TagCreator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\HttpClient\HttpClient;

(new SingleCommandApplication())
    ->setName('create-tag')
    ->setDescription('Create an annotated release tag via the GitHub API')
    ->addOption('repo', null, InputOption::VALUE_REQUIRED, 'owner/name of the target repo')
    ->addOption('version', null, InputOption::VALUE_REQUIRED, 'MAJOR.MINOR.PATCH release version')
    ->addOption('commit-sha', null, InputOption::VALUE_REQUIRED, '40-hex merge commit SHA')
    ->addOption('conductor-pr-url', null, InputOption::VALUE_REQUIRED, 'URL of the conductor release-prep PR')
    ->addOption(
        'app-token',
        null,
        InputOption::VALUE_REQUIRED,
        'GitHub App installation token (or set RELEASE_APP_TOKEN)',
    )
    ->addOption('date', null, InputOption::VALUE_REQUIRED, 'Release date (YYYY-MM-DD; defaults to today UTC)')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $opts = new OptionReader($input);
        $token = $opts->string('app-token');
        if ($token === '') {
            $envToken = getenv('RELEASE_APP_TOKEN');
            $token = is_string($envToken) ? $envToken : '';
        }
        $date = $opts->string('date');
        if ($date === '') {
            $date = gmdate('Y-m-d');
        }

        try {
            $request = new TagCreationRequest(
                repo: $opts->string('repo'),
                version: $opts->string('version'),
                commitSha: $opts->string('commit-sha'),
                conductorPrUrl: $opts->string('conductor-pr-url'),
                appToken: $token,
                date: $date,
            );
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 2;
        }

        $creator = new TagCreator(HttpClient::create());
        try {
            $result = $creator->create($request);
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        $output->writeln(sprintf('<info>Created tag %s (sha: %s)</info>', $result->tagName, $result->tagSha));
        return 0;
    })
    ->run();
