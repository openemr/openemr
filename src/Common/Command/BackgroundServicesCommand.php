<?php

/**
 * CLI interface for listing, running, and generating crontab entries
 * for OpenEMR background services.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://www.opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class BackgroundServicesCommand extends Command implements IGlobalsAware
{
    use GlobalInterfaceTrait;

    protected function configure(): void
    {
        $this
            ->setName('background:services')
            ->setDescription('List, run, or generate crontab entries for background services')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('action', InputArgument::REQUIRED, 'Action to perform: list, run, or crontab'),
                    new InputOption('name', null, InputOption::VALUE_REQUIRED, 'Service name (required for "run")'),
                    new InputOption('force', 'f', InputOption::VALUE_NONE, 'Bypass interval check (for "run")'),
                    new InputOption('php', null, InputOption::VALUE_REQUIRED, 'PHP binary path (for "crontab")', PHP_BINARY),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        if (!is_string($action)) {
            $io->error('Action argument is required.');
            return Command::FAILURE;
        }

        return match ($action) {
            'list' => $this->handleList($io),
            'run' => $this->handleRun($input, $io),
            'crontab' => $this->handleCrontab($input, $io),
            default => $this->handleUnknownAction($action, $io),
        };
    }

    private function handleList(SymfonyStyle $io): int
    {
        $services = $this->fetchServices();

        if ($services === []) {
            $io->info('No background services registered.');
            return Command::SUCCESS;
        }

        $io->table(
            ['Name', 'Title', 'Active', 'Running', 'Interval', 'Next Run'],
            array_map(fn(array $s) => [
                $s['name'],
                $s['title'],
                (int) $s['active'] !== 0 ? 'yes' : 'no',
                (int) $s['running'] === 1 ? 'yes' : 'no',
                $this->formatInterval((int) $s['execute_interval']),
                $s['next_run'],
            ], $services),
        );

        return Command::SUCCESS;
    }

    private function handleRun(InputInterface $input, SymfonyStyle $io): int
    {
        $name = $input->getOption('name');
        if (!is_string($name) || $name === '') {
            $io->error('The --name option is required for the "run" action.');
            return Command::FAILURE;
        }

        $force = (bool) $input->getOption('force');
        $runner = new BackgroundServiceRunner();
        $results = $runner->run($name, $force);

        foreach ($results as $result) {
            match ($result['status']) {
                'executed' => $io->success("Service '{$result['name']}' executed successfully."),
                'skipped' => $io->warning("Service '{$result['name']}' skipped (inactive, already running, or requires --force for manual mode)."),
                'locked' => $io->warning("Service '{$result['name']}' is locked (not yet due to run)."),
                'not_found' => $io->error("Service '{$result['name']}' not found."),
                'error' => $io->error("Service '{$result['name']}' encountered an error."),
                default => $io->note("Service '{$result['name']}': {$result['status']}"),
            };
        }

        $status = $results[0]['status'] ?? 'not_found';
        return match ($status) {
            'executed' => Command::SUCCESS,
            'skipped', 'locked' => 2,
            default => Command::FAILURE,
        };
    }

    private function handleCrontab(InputInterface $input, SymfonyStyle $io): int
    {
        $php = $input->getOption('php');
        if (!is_string($php)) {
            $php = PHP_BINARY;
        }
        $fileroot = $this->getGlobalsBag()->getString('fileroot');
        if ($fileroot === '') {
            $io->error('Global "fileroot" is not configured.');
            return Command::FAILURE;
        }
        $consolePath = $fileroot . '/bin/console';

        $services = $this->fetchActiveServices();

        if ($services === []) {
            $io->info('No active background services to schedule.');
            return Command::SUCCESS;
        }

        $io->writeln('# OpenEMR Background Services');
        $io->writeln('# Generated by: php bin/console background:services crontab');
        $io->writeln('');

        foreach ($services as $service) {
            $name = $service['name'];
            $interval = (int) $service['execute_interval'];
            if ($interval <= 0) {
                continue;
            }

            $cron = $this->minutesToCron($interval);
            $io->writeln(sprintf(
                '%s	%s %s background:services run --name=%s',
                $cron,
                escapeshellarg($php),
                escapeshellarg($consolePath),
                escapeshellarg($name),
            ));
        }

        return Command::SUCCESS;
    }

    private function handleUnknownAction(string $action, SymfonyStyle $io): int
    {
        $io->error("Unknown action '{$action}'. Valid actions: list, run, crontab");
        return Command::FAILURE;
    }

    /**
     * @return list<BackgroundServicesRow>
     */
    protected function fetchServices(): array
    {
        /** @var list<BackgroundServicesRow> */
        return QueryUtils::fetchRecordsNoLog(
            'SELECT * FROM background_services ORDER BY sort_order',
            [],
        );
    }

    /**
     * @return list<BackgroundServicesRow>
     */
    protected function fetchActiveServices(): array
    {
        /** @var list<BackgroundServicesRow> */
        return QueryUtils::fetchRecordsNoLog(
            'SELECT * FROM background_services WHERE active = 1 AND execute_interval > 0 ORDER BY sort_order',
            [],
        );
    }

    private function formatInterval(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'manual';
        }
        if ($minutes < 60) {
            return "{$minutes} min";
        }
        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;
        return $rem > 0 ? "{$hours}h {$rem}m" : "{$hours}h";
    }

    /**
     * Convert a minute interval to a cron expression.
     *
     * Only intervals that can be represented as a single valid 5-field cron
     * entry are emitted. For other intervals, a commented line explaining that
     * the interval cannot be represented is returned instead.
     */
    private function minutesToCron(int $minutes): string
    {
        if ($minutes <= 0) {
            return '# manual';
        }
        if ($minutes < 60) {
            if (60 % $minutes !== 0) {
                return "# cannot represent interval of {$minutes} minutes as a single cron entry";
            }
            return "*/{$minutes} * * * *";
        }

        if ($minutes % 60 !== 0) {
            return "# cannot represent interval of {$minutes} minutes as a single cron entry";
        }

        $hours = intdiv($minutes, 60);

        if ($hours < 24) {
            if (24 % $hours !== 0) {
                return "# cannot represent interval of {$minutes} minutes as a single cron entry";
            }
            return "0 */{$hours} * * *";
        }

        if ($hours % 24 !== 0) {
            return "# cannot represent interval of {$minutes} minutes as a single cron entry";
        }

        $days = intdiv($hours, 24);
        // Only 1-day intervals can be represented accurately; */N in the
        // day-of-month field resets at each calendar month boundary.
        if ($days === 1) {
            return "0 0 * * *";
        }
        return "# cannot represent interval of {$minutes} minutes as a single cron entry";
    }
}
