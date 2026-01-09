<?php

/**
 * Fax Document Service
 * Centralized service for managing fax documents in OpenEMR
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\FileUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Exception\FaxDocumentException;
use OpenEMR\Modules\FaxSMS\Exception\FaxNotFoundException;

class FaxDocumentService
{
    private readonly string $siteId;
    private readonly string $sitePath;
    private readonly string $receivedFaxesPath;

    public function __construct(?string $siteId = null)
    {
        $globals = OEGlobalsBag::getInstance();
        $this->siteId = $siteId ?? ($_SESSION['site_id'] ?? 'default');
        $this->sitePath = $globals->get('OE_SITE_DIR') ?? ($globals->get('OE_SITES_BASE') . '/' . $this->siteId);
        $this->receivedFaxesPath = $this->sitePath . '/documents/received_faxes';

        // Ensure received faxes directory exists
        if (!file_exists($this->receivedFaxesPath)) {
            mkdir($this->receivedFaxesPath, 0770, true);
        }

        $unassignedPath = $this->receivedFaxesPath . '/unassigned';
        if (!file_exists($unassignedPath)) {
            mkdir($unassignedPath, 0770, true);
        }
    }

    /**
     * Store fax document in OpenEMR document system
     *
     * @param string $faxSid SignalWire Fax SID
     * @param string $mediaContent Binary content of fax media
     * @param string $fromNumber Sender's fax number
     * @param int $patientId Patient ID (0 for unassigned)
     * @param string $mimeType MIME type of document
     * @return array Result with document_id and media_path
     * @throws FaxDocumentException
     */
    public function storeFaxDocument(
        string $faxSid,
        string $mediaContent,
        string $fromNumber,
        int $patientId = 0,
        string $mimeType = 'application/pdf'
    ): array {
        try {
            $timestamp = date('YmdHis');
            $extension = FileUtils::getExtensionFromMimeType($mimeType);
            $filename = "fax_{$faxSid}_{$timestamp}.{$extension}";

            // Determine storage location
            if ($patientId > 0) {
                // Get FAX category ID
                $categoryResult = QueryUtils::querySingleRow("SELECT id FROM categories WHERE name = 'FAX'");
                $categoryId = $categoryResult['id'] ?? 1;

                $formattedFrom = $this->formatPhoneDisplay($fromNumber);
                $owner = $_SESSION['authUserID'] ?? 0;

                // Create and save document using OpenEMR's standard method
                $document = new Document();
                $error = $document->createDocument(
                    $patientId,
                    $categoryId,
                    $filename,
                    $mimeType,
                    $mediaContent,
                    '',  // higher_level_path
                    1,   // path_depth
                    $owner
                );

                if (!empty($error)) {
                    throw new FaxDocumentException("Failed to create document: {$error}");
                }

                // Set document name
                $document->set_name("Fax from {$formattedFrom} - {$timestamp}");
                $document->persist();

                $documentId = $document->get_id();
                $mediaPath = $document->get_url();

                error_log("FaxDocumentService: Stored fax {$faxSid} as document {$documentId} for patient {$patientId}");

                return [
                    'success' => true,
                    'document_id' => $documentId,
                    'media_path' => $mediaPath,
                    'patient_id' => $patientId
                ];
            } else {
                // Store in unassigned directory
                $mediaPath = $this->receivedFaxesPath . '/unassigned/' . $filename;
                file_put_contents($mediaPath, $mediaContent);

                error_log("FaxDocumentService: Stored unassigned fax {$faxSid} at {$mediaPath}");

                return [
                    'success' => true,
                    'document_id' => null,
                    'media_path' => $mediaPath,
                    'patient_id' => 0
                ];
            }
        } catch (FaxDocumentException $e) {
            error_log("FaxDocumentService: Error storing fax document: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign unassigned fax to a patient
     *
     * @param string $faxSid Fax SID
     * @param int $patientId Patient ID to assign to
     * @return array Result with success status
     * @throws FaxDocumentException
     * @throws FaxNotFoundException
     */
    public function assignFaxToPatient(string $faxSid, int $patientId): array
    {
        try {
            // Get fax from queue
            $fax = QueryUtils::querySingleRow(
                "SELECT * FROM oe_faxsms_queue WHERE job_id = ? AND site_id = ?",
                [$faxSid, $this->siteId]
            );

            if (empty($fax)) {
                throw new FaxNotFoundException("Fax not found: {$faxSid}");
            }

            if (!empty($fax['patient_id'])) {
                throw new FaxDocumentException("Fax already assigned to patient " . $fax['patient_id']);
            }

            // Read the file from unassigned directory
            $mediaPath = $fax['media_path'];
            if (empty($mediaPath) || !file_exists($mediaPath)) {
                throw new FaxDocumentException("Fax media file not found: {$mediaPath}");
            }

            $mediaContent = file_get_contents($mediaPath);
            $details = json_decode($fax['details_json'] ?? '{}', true);
            $fromNumber = $details['from'] ?? $fax['calling_number'] ?? 'Unknown';

            // Determine mime type from file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $mediaPath);
            finfo_close($finfo);

            // Store as patient document
            $result = $this->storeFaxDocument($faxSid, $mediaContent, $fromNumber, $patientId, $mimeType);

            // Update queue record
            QueryUtils::sqlStatementThrowException(
                "UPDATE oe_faxsms_queue
                 SET patient_id = ?, document_id = ?, media_path = ?
                 WHERE job_id = ? AND site_id = ?",
                [$patientId, $result['document_id'], $result['media_path'], $faxSid, $this->siteId]
            );

            // Delete old unassigned file
            if (file_exists($mediaPath)) {
                unlink($mediaPath);
            }

            error_log("FaxDocumentService: Assigned fax {$faxSid} to patient {$patientId}");

            return [
                'success' => true,
                'message' => 'Fax successfully assigned to patient',
                'document_id' => $result['document_id']
            ];
        } catch (FaxDocumentException | FaxNotFoundException $e) {
            error_log("FaxDocumentService: Error assigning fax to patient: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get list of unassigned faxes
     *
     * @return array List of unassigned faxes
     */
    public function getUnassignedFaxes(): array
    {
        $sql = "SELECT * FROM oe_faxsms_queue
                WHERE site_id = ?
                AND (patient_id IS NULL OR patient_id = 0)
                AND direction = 'inbound'
                AND deleted = 0
                ORDER BY date DESC";

        $rows = QueryUtils::fetchRecords($sql, [$this->siteId]);
        $faxes = [];

        foreach ($rows as $row) {
            $details = json_decode($row['details_json'] ?? '{}', true);
            $faxes[] = [
                'id' => $row['id'],
                'job_id' => $row['job_id'],
                'from' => $row['calling_number'] ?? $details['from'] ?? 'Unknown',
                'to' => $row['called_number'] ?? $details['to'] ?? 'Unknown',
                'date' => $row['date'],
                'status' => $row['status'] ?? $details['status'] ?? 'unknown',
                'num_pages' => $details['num_pages'] ?? 0,
                'media_path' => $row['media_path'],
                'details' => $details
            ];
        }

        return $faxes;
    }

    /**
     * Get fax document details
     *
     * @param string $faxSid Fax SID
     * @return array|null Fax details or null if not found
     */
    public function getFaxDocument(string $faxSid): ?array
    {
        $fax = QueryUtils::querySingleRow(
            "SELECT * FROM oe_faxsms_queue WHERE job_id = ? AND site_id = ?",
            [$faxSid, $this->siteId]
        );

        if (empty($fax)) {
            return null;
        }

        $details = json_decode($fax['details_json'] ?? '{}', true);

        return [
            'id' => $fax['id'],
            'job_id' => $fax['job_id'],
            'from' => $fax['calling_number'] ?? $details['from'] ?? 'Unknown',
            'to' => $fax['called_number'] ?? $details['to'] ?? 'Unknown',
            'date' => $fax['date'],
            'status' => $fax['status'] ?? $details['status'] ?? 'unknown',
            'patient_id' => $fax['patient_id'],
            'document_id' => $fax['document_id'],
            'media_path' => $fax['media_path'],
            'direction' => $fax['direction'] ?? 'inbound',
            'details' => $details
        ];
    }

    /**
     * Delete fax document
     *
     * @param string $faxSid Fax SID
     * @param bool $deleteFile Whether to delete the physical file
     * @return bool Success status
     */
    public function deleteFaxDocument(string $faxSid, bool $deleteFile = false): bool
    {
        try {
            $fax = $this->getFaxDocument($faxSid);

            if (empty($fax)) {
                return false;
            }

            // Mark as deleted in queue
            QueryUtils::sqlStatementThrowException(
                "UPDATE oe_faxsms_queue SET deleted = 1 WHERE job_id = ? AND site_id = ?",
                [$faxSid, $this->siteId]
            );

            // Optionally delete physical file
            if ($deleteFile && !empty($fax['media_path']) && file_exists($fax['media_path'])) {
                unlink($fax['media_path']);
            }

            error_log("FaxDocumentService: Deleted fax {$faxSid}");
            return true;
        } catch (FaxDocumentException $e) {
            error_log("FaxDocumentService: Error deleting fax: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Format phone number for display
     *
     * @param string $phone
     * @return string
     */
    private function formatPhoneDisplay(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (strlen((string) $cleaned) === 11 && $cleaned[0] === '1') {
            $cleaned = substr((string) $cleaned, 1);
        }

        if (strlen((string) $cleaned) === 10) {
            return sprintf("(%s) %s-%s", substr((string) $cleaned, 0, 3), substr((string) $cleaned, 3, 3), substr((string) $cleaned, 6));
        }

        return $phone;
    }

    /**
     * Attempt to auto-match fax to patient by phone number
     *
     * @param string $fromNumber Sender's phone number
     * @return int Patient ID if found, 0 otherwise
     */
    public function findPatientByPhone(string $fromNumber): int
    {
        $cleaned = preg_replace('/[^0-9]/', '', $fromNumber);

        if (strlen((string) $cleaned) === 11 && $cleaned[0] === '1') {
            $cleaned = substr((string) $cleaned, 1);
        }

        // Try exact match first
        $patterns = [
            $cleaned,
            '+1' . $cleaned,
            '1' . $cleaned,
            substr((string) $cleaned, 0, 3) . '-' . substr((string) $cleaned, 3, 3) . '-' . substr((string) $cleaned, 6),
            '(' . substr((string) $cleaned, 0, 3) . ') ' . substr((string) $cleaned, 3, 3) . '-' . substr((string) $cleaned, 6)
        ];

        foreach ($patterns as $pattern) {
            $result = QueryUtils::querySingleRow(
                "SELECT pid FROM patient_data
                 WHERE (phone_cell LIKE ? OR phone_home LIKE ? OR phone_biz LIKE ?)
                 LIMIT 1",
                ["%{$pattern}%", "%{$pattern}%", "%{$pattern}%"]
            );

            if (!empty($result['pid'])) {
                return (int)$result['pid'];
            }
        }

        return 0;
    }
}
