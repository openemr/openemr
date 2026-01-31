<?php

/**
 * Patient Photo Service
 *
 * Handles saving and managing patient photographs as documents.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI-Generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Document;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;

class PatientPhotoService
{
    /**
     * Save a patient photo from base64 encoded data
     *
     * @param int $pid Patient ID
     * @param string $base64Data Base64 encoded image data (with or without data URL prefix)
     * @return array Result with 'success' boolean, 'document_id' on success, 'error' on failure
     */
    public function saveFromBase64(int $pid, string $base64Data): array
    {
        if (empty($base64Data)) {
            return ['success' => false, 'error' => 'No image data provided'];
        }

        if ($pid <= 0) {
            return ['success' => false, 'error' => 'Invalid patient ID'];
        }

        try {
            // Extract base64 data and image type (remove data URL prefix if present)
            $photoData = $base64Data;
            $imageType = 'jpeg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $imageType = $matches[1];
                $photoData = substr($base64Data, strpos($base64Data, ',') + 1);
            }

            // Decode base64 to binary
            $binaryData = base64_decode($photoData);

            if ($binaryData === false || strlen($binaryData) === 0) {
                return ['success' => false, 'error' => 'Invalid base64 data'];
            }

            // Get the Patient Photograph category ID
            $categoryId = $this->getPhotoCategoryId();
            if ($categoryId === null) {
                return ['success' => false, 'error' => 'Patient Photograph category not found'];
            }

            // Generate filename
            $filename = 'patient_photo_' . $pid . '_' . date('Ymd_His') . '.' . $imageType;
            $mimetype = 'image/' . $imageType;

            // Create and save the document
            require_once(__DIR__ . "/../../library/classes/Document.class.php");
            $doc = new Document();
            $result = $doc->createDocument(
                $pid,
                $categoryId,
                $filename,
                $mimetype,
                $binaryData
            );

            if (!empty($result)) {
                (new SystemLogger())->error(
                    "Failed to save patient photo document",
                    ['pid' => $pid, 'error' => $result]
                );
                return ['success' => false, 'error' => $result];
            }

            return [
                'success' => true,
                'document_id' => $doc->get_id()
            ];
        } catch (\Exception $e) {
            (new SystemLogger())->error(
                "Exception while saving patient photo",
                ['pid' => $pid, 'error' => $e->getMessage()]
            );
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get the category ID for Patient Photograph documents
     *
     * @return int|null Category ID or null if not found
     */
    public function getPhotoCategoryId(): ?int
    {
        $categoryName = OEGlobalsBag::getInstance()->get('patient_photo_category_name') ?? 'Patient Photograph';
        $categoryResult = QueryUtils::querySingleRow(
            "SELECT id FROM categories WHERE name = ? LIMIT 1",
            [$categoryName]
        );

        return !empty($categoryResult['id']) ? (int)$categoryResult['id'] : null;
    }
}
