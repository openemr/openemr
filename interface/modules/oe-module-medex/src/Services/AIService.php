<?php

/**
 * AI Service - MedExBank Integration
 *
 * This service calls MedExBank SaaS API for AI-powered features.
 * ALL INTELLIGENCE STAYS AT MEDEXBANK - IP PROTECTED
 *
 * OpenEMR only receives predictions/suggestions, never the algorithms.
 */

namespace OpenEMR\Modules\MedEx\Services;

class AIService
{
    private $medexBankUrl;
    private $apiKey;

    public function __construct()
    {
        // Get MedExBank API credentials from database
        $prefs = sqlQuery("SELECT ME_api_key FROM medex_prefs LIMIT 1");
        $this->apiKey = $prefs['ME_api_key'] ?? null;
        $this->medexBankUrl = $GLOBALS['medex_api_host'] ?? \OpenEMR\Modules\MedEx\MedExConfig::DEFAULT_BASE_URL;
    }

    /**
     * Predict no-show probability for appointment
     *
     * PROPRIETARY: Algorithm at MedExBank
     *
     * @param int $eventId Event ID
     * @param int $patientId Patient ID
     * @return array ['risk' => float 0-1, 'factors' => [...]]
     */
    public function predictNoShow(int $eventId, int $patientId): array
    {
        if (!$this->apiKey) {
            return ['risk' => 0.0, 'factors' => []];
        }

        // Get appointment details
        $event = sqlQuery(
            "SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$eventId]
        );

        // Get patient history
        $patientHistory = $this->getPatientHistory($patientId);

        // Call MedExBank AI API
        $response = $this->callMedExBank('/api/v2/ai/predict-noshow', [
            'event' => $event,
            'patient_history' => $patientHistory
        ]);

        return $response ?? ['risk' => 0.0, 'factors' => []];
    }

    /**
     * Get AI-generated schedule template suggestions
     *
     * PROPRIETARY: Pattern analysis at MedExBank
     *
     * @param int $providerId Provider ID
     * @return array Suggested templates
     */
    public function suggestScheduleTemplates(int $providerId): array
    {
        if (!$this->apiKey) {
            return [];
        }

        // Get provider's appointment history
        $history = sqlStatement(
            "SELECT pc_eventDate, pc_startTime, pc_duration, pc_catid, pc_pid
             FROM openemr_postcalendar_events
             WHERE pc_aid = ?
             AND pc_eventDate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
             ORDER BY pc_eventDate, pc_startTime",
            [$providerId]
        );

        $appointments = [];
        while ($row = sqlFetchArray($history)) {
            $appointments[] = $row;
        }

        // Call MedExBank AI for pattern analysis
        $response = $this->callMedExBank('/api/v2/ai/suggest-templates', [
            'provider_id' => $providerId,
            'appointment_history' => $appointments
        ]);

        return $response ?? [];
    }

    /**
     * Find optimal rescheduling slots
     *
     * PROPRIETARY: Optimization algorithm at MedExBank
     *
     * @param int $eventId Original event ID
     * @param array $preferences Patient preferences
     * @return array Suggested time slots
     */
    public function suggestRescheduleSlots(int $eventId, array $preferences = []): array
    {
        if (!$this->apiKey) {
            return [];
        }

        // Get current appointment details
        $event = sqlQuery(
            "SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?",
            [$eventId]
        );

        // Get available slots for provider
        $availableSlots = $this->getAvailableSlots(
            $event['pc_aid'],
            date('Y-m-d'),
            date('Y-m-d', strtotime('+2 weeks'))
        );

        // Call MedExBank AI for optimal slot selection
        $response = $this->callMedExBank('/api/v2/ai/suggest-reschedule', [
            'original_event' => $event,
            'available_slots' => $availableSlots,
            'preferences' => $preferences
        ]);

        return $response ?? [];
    }

    /**
     * Calculate revenue optimization suggestions
     *
     * PROPRIETARY: Revenue model at MedExBank
     *
     * @param int $providerId Provider ID
     * @param string $date Date to analyze
     * @return array Revenue insights
     */
    public function getRevenueInsights(int $providerId, string $date): array
    {
        if (!$this->apiKey) {
            return [];
        }

        // Get day's schedule
        $schedule = sqlStatement(
            "SELECT e.*, cat.pc_catname
             FROM openemr_postcalendar_events e
             LEFT JOIN openemr_postcalendar_categories cat ON e.pc_catid = cat.pc_catid
             WHERE e.pc_aid = ? AND e.pc_eventDate = ?",
            [$providerId, $date]
        );

        $events = [];
        while ($row = sqlFetchArray($schedule)) {
            $events[] = $row;
        }

        // Call MedExBank for revenue analysis
        $response = $this->callMedExBank('/api/v2/ai/revenue-insights', [
            'provider_id' => $providerId,
            'date' => $date,
            'schedule' => $events
        ]);

        return $response ?? [];
    }

    /**
     * Call MedExBank API (private - IP protected)
     */
    private function callMedExBank(string $endpoint, array $data): ?array
    {
        try {
            $ch = curl_init($this->medexBankUrl . $endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey
                ],
                CURLOPT_TIMEOUT => 10
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                return json_decode($response, true);
            }

            return null;
        } catch (\Exception $e) {
            error_log('MedExBank API error: ' . $e->getMessage());
            return null;
        }
    }

    private function getPatientHistory(int $patientId): array
    {
        $history = sqlStatement(
            "SELECT pc_eventDate, pc_apptstatus
             FROM openemr_postcalendar_events
             WHERE pc_pid = ?
             ORDER BY pc_eventDate DESC
             LIMIT 10",
            [$patientId]
        );

        $appointments = [];
        while ($row = sqlFetchArray($history)) {
            $appointments[] = $row;
        }

        return $appointments;
    }

    private function getAvailableSlots(int $providerId, string $start, string $end): array
    {
        // Find In Office blocks without appointments
        $slots = sqlStatement(
            "SELECT pc_eventDate, pc_startTime, pc_endTime, pc_prefcatid
             FROM openemr_postcalendar_events
             WHERE pc_aid = ? AND pc_catid = 2
             AND pc_eventDate BETWEEN ? AND ?
             AND pc_pid = 0
             ORDER BY pc_eventDate, pc_startTime",
            [$providerId, $start, $end]
        );

        $available = [];
        while ($row = sqlFetchArray($slots)) {
            $available[] = $row;
        }

        return $available;
    }
}
