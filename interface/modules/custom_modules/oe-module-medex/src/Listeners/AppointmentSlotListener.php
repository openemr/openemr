<?php
/**
 * Appointment Slot Listener
 *
 * Listens for OpenEMR appointment events and manages MedEx slot registry
 * Links patient appointments to consumed template slots
 *
 * @package   OpenEMR\Modules\MedEx
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Listeners;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Events\Appointments\AppointmentSetEvent;
use OpenEMR\Core\OEGlobalsBag;

class AppointmentSlotListener
{
    /**
     * Handle appointment creation/update and link to slot registry
     *
     * @param AppointmentSetEvent $event
     * @return void
     */
    public function onAppointmentSet(AppointmentSetEvent $event): void
    {
        try {
            $eid = $event->eid ?? null;
            if (empty($eid)) {
                return;
            }

            // Check if we have a pending slot consumption from the session
            $pending = $_SESSION['medex_pending_slot_consumption'] ?? null;
            if (empty($pending)) {
                return;
            }

            // Verify the pending consumption isn't stale (older than 5 minutes)
            if ((time() - $pending['timestamp']) > 300) {
                unset($_SESSION['medex_pending_slot_consumption']);
                return;
            }

            // Get the appointment details
            $appt = QueryUtils::querySingleRow(
                "SELECT pc_aid, pc_eventDate, pc_startTime, pc_catid, pc_pid
                 FROM openemr_postcalendar_events
                 WHERE pc_eid = ?",
                [$eid]
            );

            if (empty($appt)) {
                return;
            }

            // Verify this appointment matches the pending slot
            if (
                $appt['pc_aid'] == $pending['provider_id'] &&
                $appt['pc_eventDate'] == $pending['event_date'] &&
                $appt['pc_startTime'] == $pending['start_time']
            ) {
                // Update the slot registry to link this appointment
                $this->completeSlotConsumption($pending, $eid, $appt['pc_pid']);

                // Clear the pending consumption
                unset($_SESSION['medex_pending_slot_consumption']);
            }

        } catch (\Exception $e) {
            error_log('[MedEx] AppointmentSlotListener error: ' . $e->getMessage());
        }
    }

    /**
     * Complete the slot consumption by linking patient appointment
     *
     * @param array $pending
     * @param int $patient_pc_eid
     * @param string $patient_id
     * @return void
     */
    private function completeSlotConsumption(array $pending, int $patient_pc_eid, $patient_id): void
    {
        try {
            $tableExists = QueryUtils::querySingleRow("SHOW TABLES LIKE 'medex_slot_registry'");
            if (empty($tableExists)) {
                return;
            }

            // Check if there's already a pending record
            $existing = QueryUtils::querySingleRow(
                "SELECT slot_id FROM medex_slot_registry
                 WHERE open_slot_eid = ? AND slot_state = 'pending_consumption'",
                [$pending['open_slot_eid']]
            );

            if (!empty($existing['slot_id'])) {
                // Update existing pending record to consumed
                QueryUtils::sqlStatementThrowException(
                    "UPDATE medex_slot_registry
                     SET patient_pc_eid = ?, patient_id = ?, slot_state = 'consumed', consumed_at = NOW()
                     WHERE slot_id = ?",
                    [$patient_pc_eid, $patient_id, (int)$existing['slot_id']]
                );
            } else {
                // Create new consumed record
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO medex_slot_registry
                     (open_slot_eid, patient_pc_eid, patient_id, provider_id, event_date, start_time, end_time,
                      category_id, slot_state, slot_source, reschedulable, consumed_at, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'consumed', 'medex', 1, NOW(), NOW())",
                    [
                        $pending['open_slot_eid'],
                        $patient_pc_eid,
                        $patient_id,
                        $pending['provider_id'],
                        $pending['event_date'],
                        $pending['start_time'],
                        $pending['end_time'] ?? $pending['start_time'],
                        $pending['category_id']
                    ]
                );
            }

            error_log("[MedEx] Slot consumption completed: open_slot_eid={$pending['open_slot_eid']}, patient_pc_eid={$patient_pc_eid}");

        } catch (\Exception $e) {
            error_log('[MedEx] completeSlotConsumption error: ' . $e->getMessage());
        }
    }

    /**
     * Subscribe to events
     *
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AppointmentSetEvent::EVENT_HANDLE => 'onAppointmentSet',
        ];
    }
}
