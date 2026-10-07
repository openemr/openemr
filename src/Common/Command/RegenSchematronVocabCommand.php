<?php

/**
 * Regenerate the committed schematron vocab.php files from source .sch + voc.xml.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Services\Cda\Schematron\VocabularyExtractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'openemr:regen-schematron-vocab',
    description: 'Regenerate committed schematron vocab.php files from source .sch + voc.xml'
)]
class RegenSchematronVocabCommand extends Command
{
    private const TARGETS = [
        'ccda' => 'Consolidation.sch',
        'qrda1' => '2022_CMS_QRDA_I.sch',
        'qrda3' => '2022_CMS_QRDA_Category_III.sch',
    ];

    /**
     * @param string|null $outputRoot Destination for the generated schema set. Defaults to the
     *                                shipped src/Services/Cda/Schematron/schemas directory.
     *                                Overridden only by tests, which must not write into the
     *                                committed schema set. The console runner instantiates
     *                                commands with no arguments, so the default is what ships.
     */
    public function __construct(private readonly ?string $outputRoot = null)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'source-root',
                InputArgument::REQUIRED,
                'Path to a checkout of openemr/oe-schematron-service containing schematron/<type>/{*.sch,voc.xml}'
            )
            ->setHelp(<<<'HELP'
                Regenerate committed schematron vocab.php files from source .sch + voc.xml.

                The vocab files live at src/Services/Cda/Schematron/schemas/<type>/vocab.php
                and are extracted from a fresh checkout of the openemr/oe-schematron-service
                repo. Run this whenever the schematron IG revision bumps.

                Expected source layout:
                  <source-root>/schematron/ccda/{Consolidation.sch,voc.xml}
                  <source-root>/schematron/qrda1/{2022_CMS_QRDA_I.sch,voc.xml}
                  <source-root>/schematron/qrda3/{2022_CMS_QRDA_Category_III.sch,voc.xml}

                Examples:
                  <info>%command.name% /tmp/oe-schematron-service --skip-globals</info>
                HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $sourceRootArg */
        $sourceRootArg = $input->getArgument('source-root');
        $sourceRoot = rtrim($sourceRootArg, '/');
        $outputRoot = $this->outputRoot ?? dirname(__DIR__, 2) . '/Services/Cda/Schematron/schemas';

        // Preflight: fail before writing anything if any expected input is missing,
        // so the shipped set stays internally consistent.
        $missingSources = [];
        foreach (self::TARGETS as $type => $schFile) {
            $schPath = "$sourceRoot/schematron/$type/$schFile";
            $vocPath = "$sourceRoot/schematron/$type/voc.xml";
            if (!is_file($schPath)) {
                $missingSources[] = $schPath;
            }
            if (!is_file($vocPath)) {
                $missingSources[] = $vocPath;
            }
        }
        if ($missingSources !== []) {
            $io->error(array_merge(
                ['Missing required source files under ' . $sourceRoot . ':'],
                $missingSources,
            ));
            return Command::FAILURE;
        }

        $extractor = new VocabularyExtractor();

        // Stage every target before swapping any of them into place. Swapping each pair
        // as it is extracted leaves the shipped set mixed-revision when a later target
        // fails - ccda regenerated against the new IG while qrda1 and qrda3 still hold
        // the old one, so documents get validated against two revisions at once.
        /**
         * @var list<array{
         *     type: string, schFile: string, schDest: string, vocabDest: string,
         *     schTmp: string, vocabTmp: string, oids: int, resolved: int, missing: list<string>
         * }> $staged
         */
        $staged = [];
        /** @var list<string> $tempPaths */
        $tempPaths = [];

        foreach (self::TARGETS as $type => $schFile) {
            $schPath = "$sourceRoot/schematron/$type/$schFile";
            $vocPath = "$sourceRoot/schematron/$type/voc.xml";

            $sch = file_get_contents($schPath);
            $voc = file_get_contents($vocPath);
            if ($sch === false || $voc === false) {
                self::removeAll($tempPaths);
                $io->error("$type: failed to read source files");
                return Command::FAILURE;
            }
            $result = $extractor->extract($sch, $voc);
            $outSchDir = "$outputRoot/$type";
            if (!is_dir($outSchDir) && !mkdir($outSchDir, 0755, true) && !is_dir($outSchDir)) {
                self::removeAll($tempPaths);
                $io->error("$type: failed to create output directory $outSchDir");
                return Command::FAILURE;
            }

            $schDest = "$outSchDir/$schFile";
            $vocabDest = "$outSchDir/vocab.php";
            $schTmp = $schDest . '.tmp';
            $vocabTmp = $vocabDest . '.tmp';
            $vocabBody = $extractor->renderPhpFile($result['resolved'], $schFile, 'voc.xml');

            if (
                @file_put_contents($schTmp, $sch) !== strlen($sch)
                || @file_put_contents($vocabTmp, $vocabBody) !== strlen($vocabBody)
            ) {
                self::removeAll(array_merge($tempPaths, [$schTmp, $vocabTmp]));
                $io->error("$type: failed to write temp files");
                return Command::FAILURE;
            }

            $tempPaths[] = $schTmp;
            $tempPaths[] = $vocabTmp;
            $staged[] = [
                'type' => $type,
                'schFile' => $schFile,
                'schDest' => $schDest,
                'vocabDest' => $vocabDest,
                'schTmp' => $schTmp,
                'vocabTmp' => $vocabTmp,
                'oids' => count($result['oids']),
                'resolved' => count($result['resolved']),
                'missing' => $result['missing'],
            ];
        }

        // Snapshot every shipped pair before touching any of them, so a failure part-way
        // through the swaps can put the whole set back the way it was.
        /** @var array<string, string|null> $backups */
        $backups = [];
        foreach ($staged as $entry) {
            foreach ([$entry['schDest'], $entry['vocabDest']] as $dest) {
                $prior = is_file($dest) ? file_get_contents($dest) : false;
                $backups[$dest] = $prior === false ? null : $prior;
            }
        }

        /** @var list<string> $swapped */
        $swapped = [];
        foreach ($staged as $entry) {
            $swaps = [
                ['.sch', $entry['schTmp'], $entry['schDest']],
                ['vocab.php', $entry['vocabTmp'], $entry['vocabDest']],
            ];
            foreach ($swaps as [$label, $tmp, $dest]) {
                if (@rename($tmp, $dest)) {
                    $swapped[] = $dest;
                    continue;
                }
                $type = $entry['type'];
                $unrestored = self::restore(array_intersect_key($backups, array_flip($swapped)));
                self::removeAll($tempPaths);
                if ($unrestored === []) {
                    $io->error([
                        "$type: failed to swap in new $label",
                        'Rolled back - the shipped schema set is unchanged.',
                    ]);
                } else {
                    $io->error(array_merge(
                        [
                            "$type: failed to swap in new $label",
                            'Rollback incomplete. These files could not be put back and the shipped'
                            . ' schema set is now inconsistent - restore them from version control:',
                        ],
                        $unrestored,
                    ));
                }
                return Command::FAILURE;
            }
        }

        $anyMissingOid = false;
        foreach ($staged as $entry) {
            $io->text(sprintf(
                '[%s] %d OIDs (%d resolved, %d missing), wrote %s + vocab.php',
                $entry['type'],
                $entry['oids'],
                $entry['resolved'],
                count($entry['missing']),
                $entry['schFile'],
            ));
            if ($entry['missing'] !== []) {
                $anyMissingOid = true;
                foreach ($entry['missing'] as $oid) {
                    $io->text("    missing OID: $oid");
                }
            }
        }

        if ($anyMissingOid) {
            $io->note('Some OIDs were referenced by .sch but not found in the corresponding voc.xml. This is expected when an OID lives in a different schema type\'s voc.xml.');
        }

        return Command::SUCCESS;
    }

    /**
     * Delete any of the given paths that still exist. Used to clear staged temp files.
     *
     * @param list<string> $paths
     */
    private static function removeAll(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Put the given destinations back to their prior contents. A null backup means the
     * file did not exist beforehand, so it is removed instead.
     *
     * Every write is checked: claiming a restore that silently failed is worse than
     * reporting the original error alone, because it tells the operator the shipped
     * set is coherent when it is not.
     *
     * @param array<string, string|null> $backups destination path => prior contents
     * @return list<string> destinations that could not be put back
     */
    private static function restore(array $backups): array
    {
        $failed = [];
        foreach ($backups as $path => $prior) {
            if ($prior === null) {
                if (is_file($path) && !@unlink($path)) {
                    $failed[] = $path;
                }
                continue;
            }
            if (@file_put_contents($path, $prior) !== strlen($prior)) {
                $failed[] = $path;
            }
        }
        return $failed;
    }
}
