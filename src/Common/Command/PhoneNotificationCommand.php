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

final class PhoneNotificationCommand extends Command implements IGlobalsAware
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
        // Helper functions live in legacy procedural files. interface/globals.php
        // loads global_functions.inc.php, but maviq_phone_api.php is only loaded
        // where needed; pull both in explicitly to be safe.
        require_once __DIR__ . '/../../../library/global_functions.inc.php';
        require_once __DIR__ . '/../../../library/maviq_phone_api.php';

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

        $cryptoGen = ServiceContainer::getCrypto();
        $phoneToken = $cryptoGen->decryptStandard($encryptedPassword);
        $phoneTimeRange = $globals->get('phone_time_range');

        $facilities = \cron_getFacilitiesMap(new FacilityService());
        $facPhoneMap = $facilities['phone_map'];
        $facMsgMap = $facilities['msg_map'];
        $defaultMessage = $this->defaultMessage($globals);

        $patients = \cron_getPhoneAlertpatientData(self::NOTIFICATION_TYPE, $triggerHours);
        $io->writeln(sprintf('%s: %d', xlt('Total Records Found'), count($patients)));

        $client = new MaviqClient($phoneId, $phoneToken, $phoneUrl);
        $logDir = $globals->get('phone_reminder_log_dir');
        $logPath = is_string($logDir) && $logDir !== ''
            ? $logDir . '/phone_reminder_cronlog_' . date('Ymd') . '.html'
            : null;

        $errors = 0;
        foreach ($patients as $row) {
            if (!is_array($row)) {
                continue;
            }
            /** @var array<string, mixed> $typedRow */
            $typedRow = $row;
            $result = $this->sendReminder(
                $typedRow,
                $client,
                $facMsgMap,
                $facPhoneMap,
                $defaultMessage,
                $phoneTimeRange,
                $phoneUrl,
            );
            $this->appendLog($logPath, $result['log']);
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
        $eventDate = $this->stringField($row, 'pc_eventDate');
        $pieces = explode('-', $eventDate);
        $apptDate = count($pieces) === 3
            ? date('m/d/Y', (int) mktime(0, 0, 0, (int) $pieces[1], (int) $pieces[2], (int) $pieces[0]))
            : '';
        $apptTime = $this->stringField($row, 'pc_startTime');

        $facilityId = $this->intField($row, 'pc_facility');
        $greeting = $facMsgMap[$facilityId] ?? '';
        if ($greeting === '') {
            $greeting = $defaultMessage;
        }

        $firstName = $this->stringField($row, 'fname');
        $lastName = $this->stringField($row, 'lname');
        $phoneHome = $this->stringField($row, 'phone_home');
        $doctor = $row['pc_aid'] ?? '';

        $data = [
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
            'callerId' => $facPhoneMap[$facilityId] ?? '',
        ];

        $response = $client->sendRequest('appointment', 'POST', $data);

        if ($response instanceof RestResponse && $response->IsError) {
            $errorMsg = is_string($response->ErrorMessage) ? $response->ErrorMessage : 'unknown error';
            return [
                'error' => true,
                'log' => sprintf(
                    "Error starting phone call for %s | %s | %s | %s | %s | %s\n",
                    $firstName,
                    $lastName,
                    $phoneHome,
                    $apptDate,
                    $apptTime,
                    $errorMsg,
                ),
            ];
        }

        $this->insertNotificationLogEntry($row, $greeting, $phoneUrl);
        \cron_updateentry(
            self::NOTIFICATION_TYPE,
            $this->stringField($row, 'pid'),
            $this->stringField($row, 'pc_eid'),
        );

        return [
            'error' => false,
            'log' => sprintf(
                "\n========================%s || %s=========================\n"
                . "Phone reminder sent successfully: %s | %s |\t| %s | %s | %s",
                self::NOTIFICATION_TYPE,
                date('Y-m-d H:i:s'),
                $firstName,
                $lastName,
                $phoneHome,
                $apptDate,
                $apptTime,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertNotificationLogEntry(array $row, string $phoneMsg, string $phoneGateway): void
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

    private function defaultMessage(OEGlobalsBag $globals): string
    {
        $messages = $globals->get('phone_appt_message');
        if (is_array($messages) && isset($messages['Default']) && is_string($messages['Default'])) {
            return $messages['Default'];
        }
        return '';
    }

    private function appendLog(?string $path, string $data): void
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
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }
        return 0;
    }
}
