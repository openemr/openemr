<?php

/**
 * Calculates eCQMs for patients in the database and prints the population
 * counts, through the same code the QRDA Category III export uses. Used to
 * check results against Cypress expected results or another installation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\IndividualResult;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRequestAll;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qrda\ExportCat3Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CqmCalculateCommand extends Command
{
    private const POPULATIONS = ['STRAT', 'IPP', 'DENOM', 'DENEX', 'DENEXCEP', 'NUMER', 'NUMEX', 'MSRPOPL', 'MSRPOPLEX'];

    protected function configure(): void
    {
        $this
            ->setName('openemr:cqm-calculate')
            ->setDescription('Calculate eCQMs for patients in the database and print the population counts')
            ->addUsage('--measure=CMS122v13 --measure=CMS165v13')
            ->addUsage('--measure=CMS122v13 --pid=12 --pid=15 --by-patient')
            ->addUsage('--year=2025 --format=csv --by-patient')
            ->addOption('measure', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Measure to calculate, e.g. CMS122v13 (repeatable; default: the active measures)')
            ->addOption('pid', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Patient to include (repeatable; default: every patient)')
            ->addOption('year', null, InputOption::VALUE_REQUIRED, 'Performance period year (default: the eCQM Performance Period global)')
            ->addOption('by-patient', null, InputOption::VALUE_NONE, 'Print each patient\'s populations as well as the totals')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'table, csv or json', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!in_array($format, ['table', 'csv', 'json'], true)) {
            $io->error('--format must be table, csv or json');
            return Command::INVALID;
        }
        $year = $input->getOption('year');
        if (is_string($year)) {
            if (preg_match('/^\d{4}$/', $year) !== 1) {
                $io->error('--year must be a four-digit year');
                return Command::INVALID;
            }
            // The export and measure paths read the performance period from this global.
            OEGlobalsBag::getInstance()->set('cqm_performance_period', $year);
        }

        $measures = self::stringList($input->getOption('measure'));
        if ($measures === []) {
            $measures = self::activeMeasures();
        }
        if ($measures === []) {
            $io->error('No measures given and none are active for the performance period');
            return Command::FAILURE;
        }
        $measuresPath = MeasureService::fetchMeasuresPath();
        $paths = array_map(static fn (string $measure): string => $measuresPath . DIRECTORY_SEPARATOR . $measure, $measures);

        $pids = self::stringList($input->getOption('pid'));
        $request = $pids === [] ? new QdmRequestAll() : new QdmRequestSome($pids);
        $rows = $this->calculate($paths, $request);

        $byPatient = $input->getOption('by-patient') === true;
        $totals = self::totals($rows);
        match ($format) {
            'json' => $output->writeln(json_encode(['totals' => $totals, 'patients' => $byPatient ? $rows : []], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)),
            'csv' => self::writeCsv($output, $byPatient ? $rows : $totals),
            default => self::writeTables($io, $totals, $byPatient ? $rows : []),
        };
        return Command::SUCCESS;
    }

    /**
     * One row per patient, measure and population set.
     *
     * @param list<string> $paths
     * @return list<array<string, int|string>>
     */
    private function calculate(array $paths, QdmRequestInterface $request): array
    {
        $export = new ExportCat3Service(new QdmBuilder(), new CqmCalculator(), $request);
        $results = $export->export($paths, true);
        $rows = [];
        foreach (is_array($results) ? $results : [] as $hqmfId => $individualResults) {
            foreach (is_array($individualResults) ? $individualResults : [] as $result) {
                if (!$result instanceof IndividualResult) {
                    continue;
                }
                $patientId = $result->patient_id->value ?? '';
                $row = [
                    'measure' => self::measureName($result->measure, (string) $hqmfId),
                    'populationSet' => is_string($result->population_set_key) ? $result->population_set_key : '',
                    'pid' => is_scalar($patientId) ? (string) $patientId : '',
                ];
                foreach (self::POPULATIONS as $population) {
                    $count = $result->{$population};
                    if (is_int($count)) {
                        $row[$population] = $count;
                    }
                }
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Population counts summed per measure and population set.
     *
     * @param list<array<string, int|string>> $rows
     * @return list<array<string, int|string>>
     */
    private static function totals(array $rows): array
    {
        $totals = [];
        foreach ($rows as $row) {
            $key = $row['measure'] . "\0" . $row['populationSet'];
            $totals[$key] ??= ['measure' => $row['measure'], 'populationSet' => $row['populationSet'], 'patients' => 0];
            $totals[$key]['patients']++;
            foreach (self::POPULATIONS as $population) {
                if (isset($row[$population]) && is_int($row[$population])) {
                    $totals[$key][$population] = ($totals[$key][$population] ?? 0) + $row[$population];
                }
            }
        }
        return array_values($totals);
    }

    /**
     * @param list<array<string, int|string>> $totals
     * @param list<array<string, int|string>> $rows
     */
    private static function writeTables(SymfonyStyle $io, array $totals, array $rows): void
    {
        $io->section('Totals');
        $io->table(self::headers($totals), self::cells($totals));
        if ($rows !== []) {
            $io->section('By patient');
            $io->table(self::headers($rows), self::cells($rows));
        }
    }

    /**
     * @param list<array<string, int|string>> $rows
     */
    private static function writeCsv(OutputInterface $output, array $rows): void
    {
        $handle = fopen('php://memory', 'w+');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open a memory stream');
        }
        $headers = self::headers($rows);
        fputcsv($handle, $headers, escape: '');
        foreach (self::cells($rows) as $cells) {
            fputcsv($handle, $cells, escape: '');
        }
        rewind($handle);
        $output->write((string) stream_get_contents($handle));
        fclose($handle);
    }

    /**
     * The columns the rows use, populations in their usual order.
     *
     * @param list<array<string, int|string>> $rows
     * @return list<string>
     */
    private static function headers(array $rows): array
    {
        $present = [];
        foreach ($rows as $row) {
            $present += array_flip(array_keys($row));
        }
        $leading = array_values(array_filter(['measure', 'populationSet', 'pid', 'patients'], static fn (string $column): bool => isset($present[$column])));
        $populations = array_values(array_filter(self::POPULATIONS, static fn (string $column): bool => isset($present[$column])));
        return [...$leading, ...$populations];
    }

    /**
     * @param list<array<string, int|string>> $rows
     * @return list<list<string>>
     */
    private static function cells(array $rows): array
    {
        $headers = self::headers($rows);
        return array_map(
            static fn (array $row): array => array_map(static fn (string $column): string => (string) ($row[$column] ?? ''), $headers),
            $rows
        );
    }

    /** The CMS id of an IndividualResult's measure, or its HQMF id. */
    private static function measureName(mixed $measure, string $hqmfId): string
    {
        if (is_object($measure) && isset($measure->cms_id) && is_string($measure->cms_id)) {
            return $measure->cms_id;
        }
        return $hqmfId;
    }

    /**
     * The measures active for the performance period.
     *
     * @return list<string>
     */
    private static function activeMeasures(): array
    {
        $year = trim(OEGlobalsBag::getInstance()->getString('cqm_performance_period'));
        $ids = QueryUtils::fetchTableColumn(
            "SELECT `option_id` FROM `list_options` WHERE `list_id` = ? AND `activity` = 1 ORDER BY `seq`, `option_id`",
            'option_id',
            ['ecqm_' . $year . '_reporting']
        );
        return self::stringList($ids);
    }

    /**
     * @return list<string>
     */
    private static function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }
        $list = [];
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                $list[] = trim($value);
            }
        }
        return $list;
    }
}
