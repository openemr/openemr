<?php

/**
 * CLI interface for listing, running, and generating crontab entries
 * for OpenEMR background services.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;
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
 * @phpstan-import-type BackgroundServicesQueryRow from BackgroundServiceDefinition
 */
class BackgroundServicesCommand extends Command implements IGlobalsAware
{
    use GlobalInterfaceTrait;

    protected function configure(): void
    {
        $this
            ->setName('background:services')
            ->setDescription('List, run, unlock, or generate crontab entries for background services')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('action', InputArgument::REQUIRED, 'Action to perform: list, run, unlock, or crontab'),
                    new InputOption('name', null, InputOption::VALUE_REQUIRED, 'Service name (required for "unlock"; for "run", if omitted, runs all services that are due)'),
                    new InputOption('force', 'f', InputOption::VALUE_NONE, 'Bypass interval check (for "run"; ignored without --name)'),
                    new InputOption('json', null, InputOption::VALUE_NONE, 'Emit a single JSON result line on stdout (for "run" with --name); suppresses human-readable output'),
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
            'unlock' => $this->handleUnlock($input, $io),
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
                // Prefer the SQL-computed liveness flag over the legacy
                // `running` column so stuck locks from crashed workers
                // don't display as "yes" indefinitely.
                (int) ($s['lease_is_live'] ?? $s['running']) === 1 ? 'yes' : 'no',
                $this->formatInterval((int) $s['execute_interval']),
                $s['next_run'],
            ], $services),
        );

        return Command::SUCCESS;
    }

    private function handleRun(InputInterface $input, SymfonyStyle $io): int
    {
        $nameRaw = $input->getOption('name');
        $name = is_string($nameRaw) && $nameRaw !== '' ? $nameRaw : null;
        $forceRequested = (bool) $input->getOption('force');
        $json = (bool) $input->getOption('json');

        // --json is the wire format the run-all-due orchestrator uses to
        // parse child subprocess results (see BackgroundServiceRunner and
        // SymfonyBackgroundServiceSpawner). It is only meaningful when
        // running a single named service. The run-all-due path doesn't
        // go through this command's stdout.
        if ($json && $name === null) {
            $io->error('--json requires --name (it is only meaningful for a single-service run).');
            return Command::FAILURE;
        }

        // --force is only meaningful when targeting a specific --name. Without
        // a name, honoring --force would switch BackgroundServiceRunner into
        // "all services including manual-mode, ignore intervals" mode, which
        // contradicts the documented "runs all services that are due"
        // semantics and the help text. Warn and drop the flag so the run-all
        // path is always a pure cron-equivalent advance.
        if ($name === null && $forceRequested) {
            $io->warning('--force is ignored without --name; running only services that are due.');
        }
        $force = $name !== null && $forceRequested;

        // Capture the name as a non-nullable local before the run so the
        // JSON emit path below has a definite string fallback for the
        // "service not found / empty results" case. The early return
        // above guarantees name is non-null whenever $json is true.
        if ($json && $name !== null) {
            $result = $this->createRunner()->run($name, $force)[0] ?? ['name' => $name, 'status' => 'error'];
            return $this->emitJsonResult($result, $io);
        }

        $results = $this->createRunner()->run($name, $force);

        if ($results === []) {
            $io->info('No background services were due to run.');
            return Command::SUCCESS;
        }

        foreach ($results as $result) {
            match ($result['status']) {
                'executed' => $io->success("Service '{$result['name']}' executed successfully."),
                'skipped' => $io->warning("Service '{$result['name']}' skipped (inactive, already running, or requires --force for manual mode)."),
                'already_running' => $io->warning("Service '{$result['name']}' is already running (another process holds the lock)."),
                'not_due' => $io->warning("Service '{$result['name']}' is not yet due to run (interval has not elapsed)."),
                'not_found' => $io->error("Service '{$result['name']}' not found."),
                'error' => $io->error("Service '{$result['name']}' encountered an error."),
                default => $io->note("Service '{$result['name']}': {$result['status']}"),
            };
        }

        // Non-error outcomes are success at the process level. not_due,
        // already_running, and skipped are expected states during any cron
        // tick denser than the service interval (or when a prior run is
        // still in progress), not failures. Only hard errors and not_found
        // produce a non-zero exit code so Kubernetes CronJobs, systemd, and
        // cron MAILTO don't treat routine no-ops as failures. See #11664,
        // #11677, opencoreemr/chart-oce-openemr#114.
        foreach ($results as $result) {
            if ($result['status'] === 'error' || $result['status'] === 'not_found') {
                return Command::FAILURE;
            }
        }
        return Command::SUCCESS;
    }

    /**
     * Emit the single-service result as one JSON line for consumption by
     * the run-all-due orchestrator. The exit code matches the
     * human-readable path so a child process's success/failure is
     * observable both in its JSON line and its exit code.
     *
     * Reflects the per-invocation nonce the parent orchestrator passed
     * via OPENEMR_BG_NONCE so the parent can authenticate this status
     * line as its own and reject any forged line printed by the
     * service's own code (e.g. from register_shutdown_function). The
     * nonce is absent when the command is invoked directly (CLI, or
     * via the REST single-service path, which doesn't use the JSON
     * wire format); in that case we emit an empty string and the
     * child-path consumer never checks it.
     *
     * Uses JSON_THROW_ON_ERROR so an encoding failure (e.g. a service
     * name containing invalid UTF-8) surfaces as a non-zero exit with
     * a visible JsonException rather than the parent silently coercing
     * the result to 'error' because `(string) false` is an empty line.
     *
     * @param array{name: string, status: string} $result
     */
    private function emitJsonResult(array $result, SymfonyStyle $io): int
    {
        $nonceEnv = getenv('OPENEMR_BG_NONCE');
        $payload = [
            'name' => $result['name'],
            'status' => $result['status'],
            'nonce' => is_string($nonceEnv) ? $nonceEnv : '',
        ];
        $io->writeln(json_encode($payload, JSON_THROW_ON_ERROR));
        return ($result['status'] === 'error' || $result['status'] === 'not_found')
            ? Command::FAILURE
            : Command::SUCCESS;
    }

    protected function createRunner(): BackgroundServiceRunner
    {
        return new BackgroundServiceRunner();
    }

    private function handleCrontab(InputInterface $input, SymfonyStyle $io): int
    {
        $php = $input->getOption('php');
        if (!is_string($php)) {
            $php = PHP_BINARY;
        }
        $globalsBag = $this->getGlobalsBag();
        $fileroot = $globalsBag->getProjectDir();
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

    /**
     * Clear a service's lease unconditionally.
     *
     * Normally not needed — crashed workers' leases expire and are stolen
     * automatically on the next tick. Use this when a service is genuinely
     * hung mid-run and an operator has verified it is not making progress
     * but doesn't want to wait out the lease (see GH #11661).
     */
    private function handleUnlock(InputInterface $input, SymfonyStyle $io): int
    {
        $name = $input->getOption('name');
        if (!is_string($name) || $name === '') {
            $io->error('The --name option is required for the "unlock" action.');
            return Command::FAILURE;
        }

        if (!$this->clearLease($name)) {
            $io->error("Service '{$name}' not found.");
            return Command::FAILURE;
        }

        $io->success("Lease cleared for service '{$name}'.");
        return Command::SUCCESS;
    }

    private function handleUnknownAction(string $action, SymfonyStyle $io): int
    {
        $io->error("Unknown action '{$action}'. Valid actions: list, run, unlock, crontab");
        return Command::FAILURE;
    }

    /**
     * Clear the lease (and legacy running flag) for a service. The UPDATE
     * is executed unconditionally; callers that need to distinguish "already
     * clear" from "actively cleared" should check lock state beforehand.
     *
     * Returns true when the service exists (its lease is now clear either
     * way), or false when no service with that name exists.
     */
    protected function clearLease(string $name): bool
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `background_services` SET `running` = 0, `lock_expires_at` = NULL WHERE `name` = ?',
            [$name],
            true,
        );
        // The UPDATE runs unconditionally; its affected-row count would
        // report 0 for "already clear" and 1 for "actively cleared",
        // which is a distinction the caller doesn't care about. A
        // dedicated existence check lets us report "not found" vs
        // "service exists (lease is clear either way)".
        $exists = QueryUtils::fetchRecordsNoLog(
            'SELECT 1 FROM `background_services` WHERE `name` = ? LIMIT 1',
            [$name],
        );
        return $exists !== [];
    }

    /**
     * @return list<BackgroundServicesQueryRow>
     */
    protected function fetchServices(): array
    {
        /** @var list<BackgroundServicesQueryRow> */
        return QueryUtils::fetchRecordsNoLog(
            BackgroundServiceRegistry::SELECT_WITH_LEASE_LIVE . ' ORDER BY sort_order',
            [],
        );
    }

    /**
     * @return list<BackgroundServicesQueryRow>
     */
    protected function fetchActiveServices(): array
    {
        /** @var list<BackgroundServicesQueryRow> */
        return QueryUtils::fetchRecordsNoLog(
            BackgroundServiceRegistry::SELECT_WITH_LEASE_LIVE . ' WHERE `active` = 1 AND `execute_interval` > 0 ORDER BY `sort_order`',
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
