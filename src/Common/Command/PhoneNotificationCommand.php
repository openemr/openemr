<?php

/**
 * CLI command to send hourly phone reminders for upcoming appointments.
 *
 * Replaces the legacy interface/batchcom/batch_phone_notification.php script
 * that was invoked via library/allow_cronjobs.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2010 Maviq
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use MaviqClient;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use RestResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PhoneNotificationCommand extends Command implements IGlobalsAware
{
    use GlobalInterfaceTrait;

    private const DEFAULT_TRIGGER_HOURS = 72;
    private const NOTIFICATION_TYPE = 'Phone';

    protected function configure(): void
    {
        $this
            ->setName('notifications:phone')
            ->setDescription(
                'Send phone reminders for upcoming appointments. '
                . 'Intended to run hourly from cron.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loadHelpers();

        $io = new SymfonyStyle($input, $output);
        $globals = $this->getGlobalsBag();

        $triggerHours = $globals->getInt('phone_notification_hour');
        if ($triggerHours <= 0) {
            $triggerHours = self::DEFAULT_TRIGGER_HOURS;
        }

        $phoneUrl = $globals->getString('phone_gateway_url');
        $phoneId = $globals->getString('phone_gateway_username');
        $encryptedPassword = $globals->getString('phone_gateway_password');

        if ($phoneUrl === '' || $phoneId === '' || $encryptedPassword === '') {
            $io->error('Phone gateway is not configured (phone_gateway_url / _username / _password).');
            return Command::FAILURE;
        }

        $phoneToken = $this->decryptPassword($encryptedPassword);
        $phoneTimeRange = $globals->get('phone_time_range');

        $facilities = $this->fetchFacilitiesMap();
        $facPhoneMap = $facilities['phone_map'];
        $facMsgMap = $facilities['msg_map'];
        $defaultMessage = $this->defaultMessage($globals);

        $patients = $this->fetchPendingPatients(self::NOTIFICATION_TYPE, $triggerHours);
        $io->writeln(sprintf('%s: %d', xlt('Total Records Found'), count($patients)));

        $client = $this->createMaviqClient($phoneId, $phoneToken, $phoneUrl);
        $logDir = $globals->get('phone_reminder_log_dir');
        $logPath = is_string($logDir) && $logDir !== ''
            ? $logDir . '/phone_reminder_cronlog_' . date('Ymd') . '.html'
            : null;

        $errors = 0;
        foreach ($patients as $row) {
            $result = $this->sendReminder(
                $row,
                $client,
                $facMsgMap,
                $facPhoneMap,
                $defaultMessage,
                $phoneTimeRange,
                $phoneUrl,
            );
            $this->writeLog($logPath, $result['log']);
            if ($result['error']) {
                $errors++;
            }
        }

        if ($errors > 0) {
            $io->warning(sprintf('%d phone reminder(s) failed; see log.', $errors));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Pulls in legacy procedural helpers. Extracted so tests can override
     * with a no-op instead of loading files that assume a live runtime.
     *
     * @codeCoverageIgnore Overridden in tests; the real implementation can't
     *     run outside a full OpenEMR bootstrap.
     */
    protected function loadHelpers(): void
    {
        // Helper functions live in legacy procedural files. interface/globals.php
        // loads global_functions.inc.php, but maviq_phone_api.php is only loaded
        // where needed; pull both in explicitly to be safe.
        require_once __DIR__ . '/../../../library/global_functions.inc.php';
        require_once __DIR__ . '/../../../library/maviq_phone_api.php';
    }

    /**
     * @codeCoverageIgnore Crypto seam overridden in tests.
     */
    protected function decryptPassword(string $encrypted): string
    {
        $decrypted = ServiceContainer::getCrypto()->decryptStandard($encrypted);
        return is_string($decrypted) ? $decrypted : '';
    }

    /**
     * @return array{phone_map: array<int, string>, msg_map: array<int, string>}
     *
     * @codeCoverageIgnore DB seam overridden in tests.
     */
    protected function fetchFacilitiesMap(): array
    {
        /** @var array{phone_map: array<int, string>, msg_map: array<int, string>} $map */
        $map = \cron_getFacilitiesMap(new FacilityService());
        return $map;
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @codeCoverageIgnore DB seam overridden in tests.
     */
    protected function fetchPendingPatients(string $type, int $triggerHours): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = \cron_getPhoneAlertpatientData($type, $triggerHours);
        return $rows;
    }

    /**
     * @codeCoverageIgnore HTTP client seam overridden in tests.
     */
    protected function createMaviqClient(string $id, string $token, string $url): MaviqClient
    {
        return new MaviqClient($id, $token, $url);
    }

    /**
     * @codeCoverageIgnore DB seam overridden in tests.
     */
    protected function markReminderSent(string $type, string $pid, string $pcEid): void
    {
        \cron_updateentry($type, $pid, $pcEid);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @codeCoverageIgnore DB seam overridden in tests.
     */
    protected function insertLogEntry(array $row, string $phoneMsg, string $phoneGateway): void
    {
        $title = $this->stringField($row, 'title');
        $fname = $this->stringField($row, 'fname');
        $mname = $this->stringField($row, 'mname');
        $lname = $this->stringField($row, 'lname');
        $phoneHome = $this->stringField($row, 'phone_home');

        $patientInfo = "{$title} {$fname} {$mname} {$lname}|||{$phoneHome}";

        $sql = 'INSERT INTO `notification_log` (`iLogId`, `pid`, `pc_eid`, `message`, `type`, `patient_info`, `smsgateway_info`, `pc_eventDate`, `pc_endDate`, `pc_startTime`, `pc_endTime`, `dSentDateTime`) '
            . "VALUES (NULL, ?, ?, ?, 'Phone', ?, ?, ?, ?, ?, ?, ?)";
        QueryUtils::sqlStatementThrowException($sql, [
            $row['pid'] ?? null,
            $row['pc_eid'] ?? null,
            $phoneMsg,
            $patientInfo,
            $phoneGateway,
            $row['pc_eventDate'] ?? null,
            $row['pc_endDate'] ?? null,
            $row['pc_startTime'] ?? null,
            $row['pc_endTime'] ?? null,
            date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @codeCoverageIgnore Filesystem seam overridden in tests.
     */
    protected function writeLog(?string $path, string $data): void
    {
        if ($path === null || $data === '') {
            return;
        }
        $fp = @fopen($path, 'a');
        if ($fp === false) {
            return;
        }
        $separator = "\n====================================================================\n";
        @fwrite($fp, $data . $separator);
        fclose($fp);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $facMsgMap
     * @param array<int, string> $facPhoneMap
     * @return array{log: string, error: bool}
     */
    private function sendReminder(
        array $row,
        MaviqClient $client,
        array $facMsgMap,
        array $facPhoneMap,
        string $defaultMessage,
        mixed $phoneTimeRange,
        string $phoneUrl,
    ): array {
        $apptDate = $this->formatApptDate($this->stringField($row, 'pc_eventDate'));
        $apptTime = $this->stringField($row, 'pc_startTime');
        $firstName = $this->stringField($row, 'fname');
        $lastName = $this->stringField($row, 'lname');
        $phoneHome = $this->stringField($row, 'phone_home');

        $facilityId = $this->intField($row, 'pc_facility');
        $greeting = $facMsgMap[$facilityId] ?? '';
        if ($greeting === '') {
            $greeting = $defaultMessage;
        }

        $data = $this->buildRequestData(
            firstName: $firstName,
            lastName: $lastName,
            phoneHome: $phoneHome,
            apptDate: $apptDate,
            apptTime: $apptTime,
            doctor: $row['pc_aid'] ?? '',
            greeting: $greeting,
            phoneTimeRange: $phoneTimeRange,
            callerId: $facPhoneMap[$facilityId] ?? '',
        );

        $response = $client->sendRequest('appointment', 'POST', $data);

        if ($response instanceof RestResponse && $response->IsError) {
            return [
                'error' => true,
                'log' => $this->formatErrorLog($response, $firstName, $lastName, $phoneHome, $apptDate, $apptTime),
            ];
        }

        $this->insertLogEntry($row, $greeting, $phoneUrl);
        $this->markReminderSent(
            self::NOTIFICATION_TYPE,
            $this->stringField($row, 'pid'),
            $this->stringField($row, 'pc_eid'),
        );

        return [
            'error' => false,
            'log' => $this->formatSuccessLog($firstName, $lastName, $phoneHome, $apptDate, $apptTime),
        ];
    }

    private function formatApptDate(string $eventDate): string
    {
        $pieces = explode('-', $eventDate);
        if (count($pieces) !== 3) {
            return '';
        }
        return date('m/d/Y', (int) mktime(0, 0, 0, (int) $pieces[1], (int) $pieces[2], (int) $pieces[0]));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRequestData(
        string $firstName,
        string $lastName,
        string $phoneHome,
        string $apptDate,
        string $apptTime,
        mixed $doctor,
        string $greeting,
        mixed $phoneTimeRange,
        string $callerId,
    ): array {
        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => $phoneHome,
            'apptDate' => $apptDate,
            'apptTime' => $apptTime,
            'doctor' => $doctor,
            'greeting' => $greeting,
            'timeRange' => $phoneTimeRange,
            'type' => 'appointment',
            'timeZone' => date('P'),
            'callerId' => $callerId,
        ];
    }

    private function formatErrorLog(
        RestResponse $response,
        string $firstName,
        string $lastName,
        string $phoneHome,
        string $apptDate,
        string $apptTime,
    ): string {
        $errorMsg = is_string($response->ErrorMessage) ? $response->ErrorMessage : 'unknown error';
        return sprintf(
            "Error starting phone call for %s | %s | %s | %s | %s | %s\n",
            $firstName,
            $lastName,
            $phoneHome,
            $apptDate,
            $apptTime,
            $errorMsg,
        );
    }

    private function formatSuccessLog(
        string $firstName,
        string $lastName,
        string $phoneHome,
        string $apptDate,
        string $apptTime,
    ): string {
        return sprintf(
            "\n========================%s || %s=========================\n"
            . "Phone reminder sent successfully: %s | %s |\t| %s | %s | %s",
            self::NOTIFICATION_TYPE,
            date('Y-m-d H:i:s'),
            $firstName,
            $lastName,
            $phoneHome,
            $apptDate,
            $apptTime,
        );
    }

    private function defaultMessage(OEGlobalsBag $globals): string
    {
        $messages = $globals->get('phone_appt_message');
        if (is_array($messages) && isset($messages['Default']) && is_string($messages['Default'])) {
            return $messages['Default'];
        }
        return '';
    }

    /**
     * @param array<string, mixed> $row
     */
    private function stringField(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return '';
    }

    /**
     * @param array<string, mixed> $row
     */
    private function intField(array $row, string $key): int
    {
        $value = $row[$key] ?? null;
        if (is_numeric($value)) {
            return (int) $value;
        }
        return 0;
    }
}
