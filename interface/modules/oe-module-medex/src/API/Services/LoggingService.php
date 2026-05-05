<?php

/**
 * Logging Service - Handles debug logging for MedEx operations
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Services;

class LoggingService extends BaseService
{
    /**
     * Log data to debug file
     *
     * @param mixed $data
     * @param string $label
     * @return bool
     */
    public function log_this(mixed $data, string $label = ''): bool
    {
        // Debug function for end user servers
        $log = "/tmp/medex.log";
        $std_log = fopen($log, 'a');

        if ($std_log === false) {
            return false;
        }

        $timed = date('Y-m-d H:i:s');
        fwrite($std_log, "**********************\nMedEx API log_this():  " . $timed . "\n");

        if ($label !== '') {
            fwrite($std_log, "Label: " . $label . "\n");
        }

        try {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    fwrite($std_log, $key . ": " . (string)$value . "\n");
                }
            } else {
                fwrite($std_log, "\nDATA= " . (string)$data . "\n");
            }
        } catch (\Exception $e) {
            fwrite($std_log, $e->getMessage() . "\n");
        }

        fclose($std_log);
        return true;
    }
}
