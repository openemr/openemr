<?php

/**
 * openemr:seed-demo — loads a demo data set described by a seed file.
 *
 * Runs Synthea for the patients and their history, imports them through
 * the CCDA new-patient import, and writes a manifest of pid ↔ Synthea id.
 * Later versions add what Synthea doesn't make (providers, related people,
 * insurance, appointments). Development only: needs
 * OPENEMR_ENABLE_DEMO_SEED=1 unless --validate-only.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use GuzzleHttp\Client;
use OpenEMR\DemoSeed\Import\CcdaPatientImporter;
use OpenEMR\DemoSeed\Import\ImportDatabase;
use OpenEMR\DemoSeed\Import\ImportedPatient;
use OpenEMR\DemoSeed\Import\PatientManifest;
use OpenEMR\DemoSeed\SeedFile\SeedFile;
use OpenEMR\DemoSeed\SeedFile\SeedFileException;
use OpenEMR\DemoSeed\SeedFile\SeedFileParser;
use OpenEMR\DemoSeed\Synthea\SyntheaOutputReader;
use OpenEMR\DemoSeed\Synthea\SyntheaRunner;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class DemoSeedCommand extends Command implements IGlobalsAware
{
    use GlobalInterfaceTrait;

    private const DEFAULT_WORK_DIRECTORY = '/tmp/demo-seed';

    protected function configure(): void
    {
        $this
            ->setName('openemr:seed-demo')
            ->setDescription('Load demo data described by a seed file (Synthea patients plus extras). Development only.')
            ->addUsage('--site=default tmp/demo-seed.yaml')
            ->addUsage('--validate-only tmp/demo-seed.yaml')
            ->addArgument('seed-file', InputArgument::REQUIRED, 'Seed file (YAML)')
            ->addOption('site', null, InputOption::VALUE_REQUIRED, 'OpenEMR site id', 'default')
            ->addOption('work-dir', null, InputOption::VALUE_REQUIRED, 'Where Synthea output, the jar and the manifest go', self::DEFAULT_WORK_DIRECTORY)
            ->addOption('synthea-jar', null, InputOption::VALUE_REQUIRED, 'Synthea jar (downloaded here if missing); default <work-dir>/synthea-with-dependencies.jar')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Override synthea.count, e.g. for a quick trial run')
            ->addOption('validate-only', null, InputOption::VALUE_NONE, 'Check the seed file and stop; writes nothing')
            ->addOption('allow-existing-patients', null, InputOption::VALUE_NONE, 'Load even though the database already has patients');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $seedFile = $this->loadSeedFile($input);
        } catch (SeedFileException $e) {
            $io->error($e->getMessage());
            return Command::INVALID;
        }
        $this->describe($io, $seedFile);
        if ($input->getOption('validate-only') === true) {
            $io->success('Seed file is valid.');
            return Command::SUCCESS;
        }

        // Dev-only gate, same shape as openemr:fhir-import.
        if (getenv('OPENEMR_ENABLE_DEMO_SEED') !== '1') {
            $io->error('Set OPENEMR_ENABLE_DEMO_SEED=1 to load demo data (development only).');
            return Command::INVALID;
        }

        $database = new ImportDatabase();
        $existing = $database->patientCount();
        if ($existing > 0 && $input->getOption('allow-existing-patients') !== true) {
            $io->error(sprintf(
                'The database already has %d patient(s). The loader is meant for a fresh install; pass --allow-existing-patients to load anyway.',
                $existing,
            ));
            return Command::FAILURE;
        }

        $workDirectory = rtrim($this->stringOption($input, 'work-dir') ?? self::DEFAULT_WORK_DIRECTORY, '/');
        $jarPath = $this->stringOption($input, 'synthea-jar') ?? $workDirectory . '/synthea-with-dependencies.jar';
        $outputDirectory = $workDirectory . '/output';
        $filesystem = new Filesystem();

        $runner = new SyntheaRunner(new Client(), $filesystem);
        $runner->assertJavaInstalled();
        if (!is_file($jarPath)) {
            $io->writeln('Downloading the Synthea jar (about 200 MB) to ' . $jarPath . ' ...');
        }
        $runner->ensureJar($jarPath);

        $io->section('Generating ' . $seedFile->synthea->count . ' patients with Synthea');
        $runner->run($seedFile->synthea, $jarPath, $outputDirectory, static function (string $buffer) use ($output): void {
            if ($output->isVerbose()) {
                $output->write($buffer);
            }
        });
        $patients = (new SyntheaOutputReader())->read($outputDirectory);
        $io->writeln(sprintf('Synthea generated %d patients.', count($patients)));

        $io->section('Importing the patients (CCDA)');
        $importer = new CcdaPatientImporter(
            $database,
            $this->openemrPath(),
            $this->stringOption($input, 'site') ?? 'default',
        );
        $started = microtime(true);
        $imported = $importer->importAll(
            $patients,
            static function (ImportedPatient $patient, int $position, int $total) use ($io): void {
                $io->writeln(sprintf(
                    '  %d/%d  pid %d  %s %s',
                    $position,
                    $total,
                    $patient->pid,
                    $patient->synthea->firstName,
                    $patient->synthea->lastName,
                ));
            },
        );

        $manifestPath = $workDirectory . '/patients.json';
        (new PatientManifest($filesystem))->write($manifestPath, $imported);

        $io->success(sprintf(
            'Imported %d patients in %d s. Manifest: %s',
            count($imported),
            (int) round(microtime(true) - $started),
            $manifestPath,
        ));
        return Command::SUCCESS;
    }

    private function loadSeedFile(InputInterface $input): SeedFile
    {
        $path = $input->getArgument('seed-file');
        if (!is_string($path) || $path === '') {
            throw new SeedFileException('Give the seed file path');
        }
        $seedFile = (new SeedFileParser())->parseFile($path);

        $count = $this->stringOption($input, 'count');
        if ($count === null) {
            return $seedFile;
        }
        if (!ctype_digit($count) || (int) $count < 1) {
            throw new SeedFileException('--count must be a whole number of at least 1');
        }
        return $seedFile->withSynthea($seedFile->synthea->withCount((int) $count));
    }

    private function describe(SymfonyStyle $io, SeedFile $seedFile): void
    {
        $synthea = $seedFile->synthea;
        $io->writeln(sprintf(
            'Synthea: %d patients, seed %d, reference date %s, %d years of history, %s names.',
            $synthea->count,
            $synthea->seed,
            $synthea->referenceDate->format('Y-m-d'),
            $synthea->yearsOfHistory,
            $synthea->plainNames ? 'plain' : 'numbered',
        ));
        if ($seedFile->sectionsNotLoaded !== []) {
            $io->note('Not loaded by this version of the loader yet: ' . implode(', ', $seedFile->sectionsNotLoaded));
        }
    }

    private function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function openemrPath(): string
    {
        $fileRoot = $this->getGlobalsBag()?->getString('fileroot') ?? '';
        return $fileRoot !== '' ? $fileRoot : dirname(__DIR__, 3);
    }
}
