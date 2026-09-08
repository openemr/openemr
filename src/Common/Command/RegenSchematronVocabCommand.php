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
        $outputRoot = dirname(__DIR__, 2) . '/Services/Cda/Schematron/schemas';

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
        $anyMissingOid = false;

        foreach (self::TARGETS as $type => $schFile) {
            $schPath = "$sourceRoot/schematron/$type/$schFile";
            $vocPath = "$sourceRoot/schematron/$type/voc.xml";

            $sch = file_get_contents($schPath);
            $voc = file_get_contents($vocPath);
            if ($sch === false || $voc === false) {
                $io->error("$type: failed to read source files");
                return Command::FAILURE;
            }
            $result = $extractor->extract($sch, $voc);
            $outSchDir = "$outputRoot/$type";
            if (!is_dir($outSchDir) && !mkdir($outSchDir, 0755, true) && !is_dir($outSchDir)) {
                $io->error("$type: failed to create output directory $outSchDir");
                return Command::FAILURE;
            }

            // Write both outputs to temp files first, then swap into place, so a
            // failed write or second rename cannot leave the shipped .sch and
            // vocab.php out of sync. Snapshot the existing pair (if any) so a
            // failure on the second rename can restore the prior schema.
            $schDest = "$outSchDir/$schFile";
            $vocabDest = "$outSchDir/vocab.php";
            $schTmp = $schDest . '.tmp';
            $vocabTmp = $vocabDest . '.tmp';
            $vocabBody = $extractor->renderPhpFile($result['resolved'], $schFile, 'voc.xml');
            $schBackup = is_file($schDest) ? file_get_contents($schDest) : null;

            if (
                file_put_contents($schTmp, $sch) !== strlen($sch)
                || file_put_contents($vocabTmp, $vocabBody) !== strlen($vocabBody)
            ) {
                @unlink($schTmp);
                @unlink($vocabTmp);
                $io->error("$type: failed to write temp files");
                return Command::FAILURE;
            }
            if (!rename($schTmp, $schDest)) {
                @unlink($schTmp);
                @unlink($vocabTmp);
                $io->error("$type: failed to swap in new .sch");
                return Command::FAILURE;
            }
            if (!rename($vocabTmp, $vocabDest)) {
                // Vocab swap failed after .sch was already updated - restore prior
                // .sch (or remove it if there wasn't one) so the pair stays coherent.
                if ($schBackup !== null) {
                    file_put_contents($schDest, $schBackup);
                } else {
                    @unlink($schDest);
                }
                @unlink($vocabTmp);
                $io->error("$type: failed to swap in new vocab.php; prior .sch restored");
                return Command::FAILURE;
            }

            $io->text(sprintf(
                '[%s] %d OIDs (%d resolved, %d missing), wrote %s + vocab.php',
                $type,
                count($result['oids']),
                count($result['resolved']),
                count($result['missing']),
                $schFile,
            ));
            if ($result['missing'] !== []) {
                $anyMissingOid = true;
                foreach ($result['missing'] as $oid) {
                    $io->text("    missing OID: $oid");
                }
            }
        }

        if ($anyMissingOid) {
            $io->note('Some OIDs were referenced by .sch but not found in the corresponding voc.xml. This is expected when an OID lives in a different schema type\'s voc.xml.');
        }

        return Command::SUCCESS;
    }
}
