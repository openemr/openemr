#!/usr/bin/env php
<?php

/**
 * Print the newest openemr-static-binary-forge release the binary Docker
 * image can be bumped to, as `<OPENEMR_VERSION>/<BINARY_RELEASE_DATE>`
 * (e.g. `8_3_0/08232026`).
 *
 * This is the source resource for .github/updatecli/binary-image.yaml.
 * When nothing newer qualifies it prints the pin already in the
 * Dockerfile, so updatecli sees no change and writes nothing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

// OpenEMR\Release\ classes live under autoload-dev so composer-require-checker
// does not demand conductor-only deps in production. Anything invoking this
// script needs a `composer install` that includes dev dependencies.
if (!class_exists(\OpenEMR\Release\BinaryForgeResolver::class)) {
    fwrite(
        STDERR,
        "OpenEMR\\Release\\ classes are not autoloadable; rerun composer install with dev dependencies.\n",
    );
    exit(2);
}

use OpenEMR\Release\BinaryForgePinReader;
use OpenEMR\Release\BinaryForgeResolver;
use OpenEMR\Release\GitHubApi;
use OpenEMR\Release\OptionReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('resolve-binary-forge')
    ->setDescription('Print the newest adoptable forge release pin for the binary Docker image')
    ->addOption(
        'dockerfile',
        null,
        InputOption::VALUE_REQUIRED,
        'Dockerfile holding the current pin',
        BinaryForgePinReader::DEFAULT_DOCKERFILE,
    )
    ->addOption(
        'field',
        null,
        InputOption::VALUE_REQUIRED,
        'Which part of the pin to print: pin, version, or date',
        'pin',
    )
    ->addOption(
        'verify',
        null,
        InputOption::VALUE_NONE,
        "Assert the Dockerfile's current pin is fully published, instead of resolving a new one",
    )
    ->addOption(
        'explain',
        null,
        InputOption::VALUE_NONE,
        'Also write the current pin and the outcome to stderr',
    )
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $options = new OptionReader($input);
        $dockerfile = $options->string('dockerfile', BinaryForgePinReader::DEFAULT_DOCKERFILE);
        if ($dockerfile === '') {
            $output->writeln('<error>--dockerfile must be a non-empty path</error>');
            return 2;
        }

        $field = $options->string('field', 'pin');
        if (!in_array($field, ['pin', 'version', 'date'], true)) {
            $output->writeln("<error>Unknown --field: {$field} (expected pin, version, or date)</error>");
            return 2;
        }

        try {
            $current = (new BinaryForgePinReader($dockerfile))->read();
            $resolver = new BinaryForgeResolver(
                new GitHubApi(BinaryForgeResolver::FORGE_REPO),
                new GitHubApi(),
            );
            // --verify is a post-condition on the Dockerfile as written,
            // run after updatecli applies its targets. The two forge
            // sources resolve independently, so in principle a release
            // finishing publication between them could pair one
            // candidate's version with another's date; this refuses to
            // let that pair — or any other malformed pin — reach a PR.
            if ($options->bool('verify')) {
                if (!$resolver->isPublished($current)) {
                    $output->writeln("<error>Pin {$current} is not fully published on the forge</error>");
                    return 1;
                }
                $output->writeln((string)$current);
                return 0;
            }
            $resolved = $resolver->resolve($current);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        if ($options->bool('explain')) {
            fwrite(STDERR, sprintf(
                "current=%s resolved=%s (%s)\n",
                $current,
                $resolved,
                (string)$resolved === (string)$current ? 'no newer fully published release' : 'bump available',
            ));
        }

        // updatecli's shell source takes the whole of stdout as the
        // resource value, so this line is the only thing allowed on it.
        // --explain diagnostics go to stderr for that reason.
        // No default arm: the in_array() guard above narrows $field to the
        // three literals, so PHPStan proves this match exhaustive and a
        // fourth --field value would fail the guard rather than fall
        // through to a silently wrong answer.
        $output->writeln(match ($field) {
            'pin' => (string)$resolved,
            'version' => $resolved->openemrVersion,
            'date' => $resolved->releaseDate,
        });

        return 0;
    })
    ->run();
