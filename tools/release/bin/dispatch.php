#!/usr/bin/env php
<?php

/**
 * CLI entry point for Dispatcher. Sends a repository_dispatch envelope
 * matching the cross-repo dispatch.schema.json to the consumer repos.
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
if (!class_exists(\OpenEMR\Release\Dispatcher::class)) {
    fwrite(
        STDERR,
        "OpenEMR\\Release\\ classes are not autoloadable; rerun composer install with dev dependencies.\n",
    );
    exit(2);
}

use OpenEMR\Release\DispatchDataBuilder;
use OpenEMR\Release\Dispatcher;
use OpenEMR\Release\DispatchRequest;
use OpenEMR\Release\OptionReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\HttpClient\HttpClient;

(new SingleCommandApplication())
    ->setName('dispatch')
    ->setDescription('Send a repository_dispatch envelope to consumer repos')
    ->addOption('event', null, InputOption::VALUE_REQUIRED, 'Dispatch event name')
    ->addOption(
        'repo',
        null,
        InputOption::VALUE_REQUIRED,
        'Source repo (owner/name) to record in the envelope; defaults to openemr/openemr',
    )
    ->addOption('sha', null, InputOption::VALUE_REQUIRED, '40-hex commit SHA the event refers to')
    ->addOption('actor', null, InputOption::VALUE_REQUIRED, 'Identity dispatching the event')
    ->addOption(
        'app-token',
        null,
        InputOption::VALUE_REQUIRED,
        'GitHub App installation token (or set RELEASE_APP_TOKEN)',
    )
    ->addOption(
        'target-repos',
        null,
        InputOption::VALUE_REQUIRED,
        'Comma-separated owner/name list',
        'openemr/openemr-devops,openemr/website-openemr',
    )
    ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'rel-* branch name (rel-cut/update/tag events)')
    ->addOption('version', null, InputOption::VALUE_REQUIRED, 'MAJOR.MINOR.PATCH release version')
    ->addOption('prev-release', null, InputOption::VALUE_REQUIRED, 'Previous release version (rel-cut/update)')
    ->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Tag name (openemr-tag event)')
    ->addOption('probe', null, InputOption::VALUE_NONE, 'Bypass schema validation (permissions-check probe)')
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $opts = new OptionReader($input);
        $token = $opts->string('app-token');
        if ($token === '') {
            $envToken = getenv('RELEASE_APP_TOKEN');
            $token = is_string($envToken) ? $envToken : '';
        }

        $event = $opts->string('event');
        $data = (new DispatchDataBuilder($opts))->build($event);

        try {
            $request = new DispatchRequest(
                event: $event,
                repo: $opts->string('repo', 'openemr/openemr'),
                sha: $opts->string('sha'),
                actor: $opts->string('actor', 'openemr-release-bot'),
                dispatchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                appToken: $token,
                data: $data,
                probe: $opts->bool('probe'),
            );
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 2;
        }

        $schemaPath = dirname(__DIR__) . '/contracts/dispatch.schema.json';
        $dispatcher = new Dispatcher(HttpClient::create(), $schemaPath);
        $targets = $opts->commaList('target-repos');

        try {
            $results = $dispatcher->dispatch($request, $targets);
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        foreach ($results as $result) {
            $output->writeln(sprintf(
                '<info>dispatched %s -> %s (HTTP %d)</info>',
                $request->event,
                $result->repo,
                $result->statusCode,
            ));
        }
        return 0;
    })
    ->run();
