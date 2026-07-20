<?php

/**
 * Build the full distribution tarball + zip for an official OpenEMR release.
 *
 * Ports the long-standing manual release-package process: export a checked-out
 * release branch, install production-only dependencies, build front-end assets,
 * prune dev/test cruft, and emit `openemr-<version>.tar.gz` and
 * `openemr-<version>.zip` ready to attach to the GitHub release.
 *
 * The staging tree is produced with `git archive`, so `export-ignore` entries in
 * openemr/openemr's .gitattributes (.github, ci, docker, tests, tools, large
 * docs, …) are the single source of truth for what ships — no exclude list is
 * duplicated here.
 *
 * Unlike PatchAssembler (a changed-files overlay), this produces a complete,
 * standalone install that end users extract and run without composer or npm.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final readonly class PackageAssembler
{
    public function __construct(
        private string $version,
        private string $openemrDir,
        private string $outputDir,
        private OutputInterface $output,
    ) {
    }

    public function assemble(): int
    {
        if (!is_dir($this->openemrDir)) {
            $this->output->writeln("<error>OpenEMR directory not found: {$this->openemrDir}</error>");
            return 1;
        }
        if (!is_file("{$this->openemrDir}/build.xml")) {
            $this->output->writeln("<error>build.xml not found in {$this->openemrDir} — wrong checkout?</error>");
            return 1;
        }

        // `git archive HEAD` below ships the committed tree, so an uncommitted
        // version bump would silently package stale content. Fail fast on a dirty
        // checkout. (The workflow commits the bump locally before this step, even
        // in dry runs, so the package validates the exact tree a release ships.)
        $status = new Process(['git', 'status', '--porcelain'], $this->openemrDir);
        $status->mustRun();
        $dirty = trim($status->getOutput());
        if ($dirty !== '') {
            $this->output->writeln("<error>Refusing to package a dirty checkout in {$this->openemrDir}:</error>");
            $this->output->writeln($dirty);
            return 1;
        }

        $packageName = "openemr-{$this->version}";

        // Resolve the output dir to an absolute path before any command runs.
        // The git archive below runs with cwd = openemrDir, so a relative output
        // path (the default ./release-output) would resolve against the openemr
        // checkout — where the dir does not exist — and fail to open. Create the
        // dir, then canonicalize, so every command writes here regardless of its
        // own cwd.
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
        $outputDir = realpath($this->outputDir);
        if ($outputDir === false) {
            $this->output->writeln("<error>Could not resolve output directory: {$this->outputDir}</error>");
            return 1;
        }

        $stageDir = "{$outputDir}/{$packageName}";

        // Fresh staging directory.
        if (is_dir($stageDir)) {
            $this->run(['rm', '-rf', $stageDir]);
        }

        // Export the committed tree (honoring .gitattributes export-ignore) into
        // a fresh openemr-<version>/ staging dir. git archive can't pipe through
        // our no-shell Process runner, so stage via an intermediate tar. HEAD is
        // the release commit — the dirty-checkout guard above guarantees the
        // version bump is committed (in every run, dry included).
        $sourceTar = "{$outputDir}/{$packageName}-source.tar";
        $this->run(
            ['git', 'archive', '--format=tar', '--prefix', "{$packageName}/", '-o', $sourceTar, 'HEAD'],
            $this->openemrDir,
        );
        $this->run(['tar', '-xf', $sourceTar, '-C', $outputDir]);
        unlink($sourceTar);

        // Production dependencies + built front-end assets.
        $this->run(['composer', 'install', '--no-dev', '--no-interaction', '--no-progress'], $stageDir);
        $this->run(['npm', 'ci'], $stageDir);
        $this->run(['npm', 'run', 'build'], $stageDir);

        // Prune vendor/asset cruft via the openemr build.xml targets. Driving
        // these through phing keeps the prune list owned by openemr/openemr
        // (single source of truth) rather than duplicated here. Point
        // COMPOSER_HOME at a throwaway dir so `composer global` installs phing
        // there instead of mutating the caller's real global environment — no
        // `remove` cleanup step that could be skipped on a clean-target failure.
        $composerHome = "{$outputDir}/{$packageName}-composer-home";
        mkdir($composerHome, 0755, true);
        $composerEnv = ['COMPOSER_HOME' => $composerHome];
        $this->run(
            ['composer', 'global', 'require', 'phing/phing', '--no-interaction', '--no-progress'],
            null,
            $composerEnv,
        );
        // build.xml is export-ignore'd in openemr's .gitattributes, so git
        // archive strips it from the staged tree. Its prune targets resolve
        // ${project.basedir}/vendor and .../public/assets against the staged
        // tree (phing runs there), so copy the buildfile in for the prune run
        // and remove it afterward — it must not ship in the distribution.
        $buildXml = "{$stageDir}/build.xml";
        if (!copy("{$this->openemrDir}/build.xml", $buildXml)) {
            $this->output->writeln("<error>Failed to stage build.xml for prune: {$buildXml}</error>");
            return 1;
        }
        $phing = "{$composerHome}/vendor/bin/phing";
        $this->run([$phing, 'vendor-clean'], $stageDir);
        $this->run([$phing, 'assets-clean'], $stageDir);
        // Fail hard if the staged copy can't be removed: otherwise it would ship
        // in the archives below, defeating the export-ignore intent.
        if (!unlink($buildXml)) {
            $this->output->writeln("<error>Failed to remove staged build.xml; refusing to ship: {$buildXml}</error>");
            return 1;
        }
        $this->run(['rm', '-rf', $composerHome]);

        // Drop node_modules and regenerate the optimized production autoloader.
        $this->run(['rm', '-rf', "{$stageDir}/node_modules"]);
        $this->run(['composer', 'dump-autoload', '--optimize', '--no-dev'], $stageDir);

        // Standardize permissions; make the install-writable paths writable.
        $this->run(['chmod', '-R', 'u+w', $stageDir]);
        $documentsDir = "{$stageDir}/sites/default/documents";
        if (is_dir($documentsDir)) {
            $this->run(['chmod', '-R', 'a+w', $documentsDir]);
        }
        $sqlconf = "{$stageDir}/sites/default/sqlconf.php";
        if (is_file($sqlconf)) {
            $this->run(['chmod', 'a+w', $sqlconf]);
        }

        // Build archives from the output dir so each unpacks to openemr-<version>/.
        $this->run(['tar', '-zcpf', "{$packageName}.tar.gz", $packageName], $outputDir);
        $this->run(['zip', '-r', '-q', "{$packageName}.zip", $packageName], $outputDir);

        // Drop the staging tree; only the archives need to survive.
        $this->run(['rm', '-rf', $stageDir]);

        foreach (["{$packageName}.tar.gz", "{$packageName}.zip"] as $artifact) {
            $path = "{$outputDir}/{$artifact}";
            $size = filesize($path);
            $this->output->writeln(sprintf(
                '<info>Built</info> %s (%s bytes)',
                $path,
                is_int($size) ? number_format($size) : '?',
            ));
        }

        return 0;
    }

    /**
     * Run a command, streaming its output. No timeout — builds are slow.
     *
     * @param list<string>              $command
     * @param array<string, string>|null $env extra environment, merged over the inherited environment
     */
    private function run(array $command, ?string $cwd = null, ?array $env = null): void
    {
        $this->output->writeln('<comment>$ ' . implode(' ', $command) . '</comment>');
        $process = new Process($command, $cwd, $env, null, null);
        $process->mustRun(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });
    }
}
