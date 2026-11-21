<?php

/**
 * openemr:seed-demo — loads a demo data set described by a seed file.
 *
 * Runs Synthea for the patients and their history, imports them through
 * the CCDA new-patient import, and writes a manifest of pid ↔ Synthea id.
 * Then adds what Synthea doesn't make: facilities, providers, related
 * people, extra phones and addresses, insurance, appointments and Visit
 * Lists. Development only: needs OPENEMR_ENABLE_DEMO_SEED=1 unless
 * --validate-only.
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
use OpenEMR\Appointment\VisitList\VisitListService;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\DemoSeed\Import\CcdaPatientImporter;
use OpenEMR\DemoSeed\Import\ImportDatabase;
use OpenEMR\DemoSeed\Import\ImportedPatient;
use OpenEMR\DemoSeed\Import\PatientManifest;
use OpenEMR\DemoSeed\Load\CalendarCategory;
use OpenEMR\DemoSeed\Load\PlanWriter;
use OpenEMR\DemoSeed\Plan\PlanException;
use OpenEMR\DemoSeed\Plan\Planner;
use OpenEMR\DemoSeed\SeedFile\SeedFile;
use OpenEMR\DemoSeed\SeedFile\SeedFileException;
use OpenEMR\DemoSeed\SeedFile\SeedFileParser;
use OpenEMR\DemoSeed\Synthea\SyntheaOutputReader;
use OpenEMR\DemoSeed\Synthea\SyntheaRunner;
use OpenEMR\DemoSeed\Validation\DatabaseListOptions;
use OpenEMR\DemoSeed\Validation\ListValidator;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\ContactRelationService;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\PersonPatientLinkService;
use OpenEMR\Services\PractitionerService;
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
            ->addUsage('--site=default contrib/demo-seed/demo-seed.yaml')
            ->addUsage('--validate-only contrib/demo-seed/demo-seed.yaml')
            ->addArgument('seed-file', InputArgument::REQUIRED, 'Seed file (YAML)')
            ->addOption('site', null, InputOption::VALUE_REQUIRED, 'OpenEMR site id', 'default')
            ->addOption('work-dir', null, InputOption::VALUE_REQUIRED, 'Where Synthea output, the jar and the manifest go', self::DEFAULT_WORK_DIRECTORY)
            ->addOption('synthea-jar', null, InputOption::VALUE_REQUIRED, 'Synthea jar (downloaded here if missing); default <work-dir>/synthea-with-dependencies.jar')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Override synthea.count, e.g. for a quick trial run')
            ->addOption('validate-only', null, InputOption::VALUE_NONE, 'Check the seed file and stop; writes nothing')
            ->addOption('allow-existing-patients', null, InputOption::VALUE_NONE, 'Load even though the database already has patients')
            ->addOption('reuse-import', null, InputOption::VALUE_NONE, 'Skip Synthea and the import; add the extras to the patients in <work-dir>/patients.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $seedFile = $this->loadSeedFile($input);
            (new ListValidator(new DatabaseListOptions()))->validate($seedFile->listReferences);
            $this->assertUsernamesFree($seedFile);
            $this->assertInsuranceTypesExist($seedFile);
            $categories = $this->calendarCategories($seedFile);
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

        $workDirectory = rtrim($this->stringOption($input, 'work-dir') ?? self::DEFAULT_WORK_DIRECTORY, '/');
        $manifestPath = $workDirectory . '/patients.json';
        $filesystem = new Filesystem();

        if ($input->getOption('reuse-import') === true) {
            $manifest = new PatientManifest($filesystem);
            $imported = $this->rereadSynthea($manifest->read($manifestPath));
            $manifest->write($manifestPath, $imported);
            $io->writeln(sprintf('Reusing the %d imported patients in %s.', count($imported), $manifestPath));
        } else {
            $imported = $this->generateAndImport($input, $io, $output, $seedFile, $workDirectory, $filesystem);
            if ($imported === null) {
                return Command::FAILURE;
            }
            (new PatientManifest($filesystem))->write($manifestPath, $imported);
            $io->writeln('Manifest: ' . $manifestPath);
        }

        $io->section('Adding facilities, providers, related people, phones, addresses, insurance and appointments');
        try {
            $plan = (new Planner(
                $seedFile,
                ServiceContainer::getClock()->now(),
                array_map(static fn (CalendarCategory $category): int => $category->minutes, $categories),
            ))->plan($imported);
        } catch (PlanException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
        $writer = new PlanWriter(
            new FacilityService(),
            new PractitionerService(),
            new PatientService(),
            new ContactService(),
            new ContactRelationService(),
            new PersonPatientLinkService(),
            new InsuranceCompanyService(),
            new InsuranceService(),
            new AppointmentService(),
            new VisitListService(),
            $categories,
        );
        $writer->write($plan, static function (string $line) use ($io): void {
            $io->writeln('  ' . $line);
        });
        foreach ($plan->casePatients as $key => $pid) {
            $io->writeln(sprintf('  case patient %-12s pid %d', $key, $pid));
        }

        $io->success(sprintf('Loaded %d patients.', count($imported)));
        return Command::SUCCESS;
    }

    /**
     * Reads each patient's Synthea FHIR file again where it is still there,
     * so a manifest from an older loader gets what this one reads.
     *
     * @param list<ImportedPatient> $imported
     * @return list<ImportedPatient>
     */
    private function rereadSynthea(array $imported): array
    {
        $reader = new SyntheaOutputReader();
        return array_map(
            static fn (ImportedPatient $patient): ImportedPatient => is_readable($patient->synthea->fhirFile)
                ? new ImportedPatient(
                    $patient->pid,
                    $reader->readFhir($patient->synthea->syntheaId, $patient->synthea->ccdaFile, $patient->synthea->fhirFile),
                )
                : $patient,
            $imported,
        );
    }

    /**
     * @return ?list<ImportedPatient> null when the database already has
     *         patients and the user didn't allow it
     */
    private function generateAndImport(
        InputInterface $input,
        SymfonyStyle $io,
        OutputInterface $output,
        SeedFile $seedFile,
        string $workDirectory,
        Filesystem $filesystem,
    ): ?array {
        $database = new ImportDatabase();
        $existing = $database->patientCount();
        if ($existing > 0 && $input->getOption('allow-existing-patients') !== true) {
            $io->error(sprintf(
                'The database already has %d patient(s). The loader is meant for a fresh install; pass --allow-existing-patients to load anyway.',
                $existing,
            ));
            return null;
        }

        $jarPath = $this->stringOption($input, 'synthea-jar') ?? $workDirectory . '/synthea-with-dependencies.jar';
        $outputDirectory = $workDirectory . '/output';

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
        $reader = new SyntheaOutputReader();
        $patients = $reader->read($outputDirectory);
        $io->writeln(sprintf('Synthea generated %d patients with a CCDA.', count($patients)));
        $withoutCcda = $reader->fhirWithoutCcda($outputDirectory);
        if ($withoutCcda !== []) {
            $io->warning(sprintf(
                "Synthea's CCDA export failed for %d patient(s), who are not imported: %s. Raise synthea.count if you need more.",
                count($withoutCcda),
                implode(', ', $withoutCcda),
            ));
        }

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
        $io->writeln(sprintf('Imported %d patients in %d s.', count($imported), (int) round(microtime(true) - $started)));
        return $imported;
    }

    /**
     * @throws SeedFileException
     */
    private function assertUsernamesFree(SeedFile $seedFile): void
    {
        $taken = [];
        foreach ($seedFile->providers as $provider) {
            $count = QueryUtils::fetchSingleValue(
                'SELECT COUNT(*) AS `n` FROM `users` WHERE `username` = ?',
                'n',
                [$provider->username],
            );
            if (is_numeric($count) && (int) $count > 0) {
                $taken[] = $provider->username;
            }
        }
        if ($taken !== []) {
            throw new SeedFileException('providers: username(s) already in use: ' . implode(', ', $taken));
        }
    }

    /**
     * @throws SeedFileException
     */
    private function assertInsuranceTypesExist(SeedFile $seedFile): void
    {
        foreach ($seedFile->insuranceCompanies as $index => $company) {
            $count = QueryUtils::fetchSingleValue(
                'SELECT COUNT(*) AS `n` FROM `insurance_type_codes` WHERE `id` = ?',
                'n',
                [$company->typeCode],
            );
            if (!is_numeric($count) || (int) $count === 0) {
                throw SeedFileException::at('insurance_companies[' . $index . '].type', 'no insurance_type_codes row has id ' . $company->typeCode);
            }
        }
    }

    /**
     * The active calendar categories, after checking that every category the
     * appointments section names is one for patient appointments, and that
     * the Visit List category exists when there are Visit Lists.
     *
     * @return array<string, CalendarCategory> by pc_constant_id
     * @throws SeedFileException
     */
    private function calendarCategories(SeedFile $seedFile): array
    {
        $categories = CalendarCategory::allActive();
        foreach ($seedFile->appointments->categoryReferences() as $path => $constantId) {
            $category = $categories[$constantId] ?? null;
            if ($category === null) {
                throw SeedFileException::at($path, 'no active calendar category has pc_constant_id "' . $constantId . '"');
            }
            if ($category->type !== CalendarCategory::PATIENT_TYPE) {
                throw SeedFileException::at($path, '"' . $constantId . '" is not a category for patient appointments');
            }
        }
        if ($seedFile->appointments->hasVisitLists() && !isset($categories[VisitListService::CATEGORY_CONSTANT_ID])) {
            throw SeedFileException::at('appointments.visit_lists', 'this site has no active Visit List calendar category');
        }
        return $categories;
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
    }

    private function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function openemrPath(): string
    {
        $fileRoot = $this->getGlobalsBag()->getString('fileroot');
        return $fileRoot !== '' ? $fileRoot : dirname(__DIR__, 3);
    }
}
