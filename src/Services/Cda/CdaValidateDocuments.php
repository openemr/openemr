<?php

/**
 * CDA QRDA Validation Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use DOMDocument;
use Exception;
use OpenEMR\Common\System\System;

class CdaValidateDocuments
{
    public function __construct()
    {
    }

    /**
     * @param $document
     * @param $type
     * @return array|bool|null
     * @throws Exception
     */
    public function validateDocument($document, $type)
    {
        $xsd = $this->validateXmlXsd($document, $type);
        $schema_results = $this->validateSchematron($document, $type);
        $totals = array_merge($xsd, $schema_results);

        return $totals;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function startValidationService($port = '6662'): bool
    {
        $system = new System();
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception("Socket Creation Failed");
        }
        $serverActive = @socket_connect($socket, "localhost", $port);

        if ($serverActive === false) {
            $path = $GLOBALS['fileroot'] . "/ccdaservice/node_modules/oe-schematron-service";
            if (IS_WINDOWS) {
                $redirect_errors = " > ";
                $redirect_errors .= $system->escapeshellcmd($GLOBALS['temporary_files_dir'] . "/schematron_server.log") . " 2>&1";
                $cmd = $system->escapeshellcmd("node " . $path . "/app.js") . $redirect_errors;
                $pipeHandle = popen("start /B " . $cmd, "r");
                if ($pipeHandle === false) {
                    throw new Exception("Failed to start local schematron service");
                }
                if (pclose($pipeHandle) === -1) {
                    error_log("Failed to close pipehandle for schematron service");
                }
            } else {
                $command = 'nodejs';
                if (!$system->command_exists($command)) {
                    if ($system->command_exists('node')) {
                        $command = 'node';
                    } else {
                        error_log("Node is not installed on the system.  Connection failed");
                        throw new Exception('Connection Failed.');
                    }
                }
                $cmd = $system->escapeshellcmd("$command " . $path . "/app.js");
                exec($cmd . " > /dev/null &");
            }
            sleep(2); // give cpu a rest
            $serverActive = socket_connect($socket, "localhost", $port);
            if ($serverActive === false) {
                error_log("Failed to start and connect to local schematron service server on port 6662");
                throw new Exception("Connection Failed");
            }
        }
        socket_close($socket);

        return $serverActive;
    }

    /**
     * @param $xml
     * @param $type
     * @return mixed|null
     * @throws Exception
     */
    function schematronValidateDocument($xml, $type = 'ccda')
    {
        $service = $this->startValidationService();
        $reply = [];
        $headers = array(
            "Content-Type: application/xml",
            "Accept: application/json",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1?type=' . attr_url($type));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_PORT, 6662);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        curl_close($ch);

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($status == '200') {
            $reply = json_decode($response, true);
        }

        return $reply;
    }

    /**
     * @param $document
     * @param $type
     * @return bool
     */
    public function validateXmlXsd($document, $type)
    {
        libxml_use_internal_errors(true);
        $dom = new DomDocument();
        $dom->loadXML($document);
        $xsd = __DIR__ . '/../../../interface/modules/zend_modules/public/xsd/Schema/CDA2/infrastructure/cda/CDA_SDTC.xsd';

        $xsd_log['xsd'] = [];
        if (!$dom->schemaValidate($xsd)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $detail = $this->formatXsdError($error);
                $xsd_log['xsd'][] = $detail;
                error_log($detail);
            }
            libxml_clear_errors();
        }

        return $xsd_log;
    }

    /**
     * @param $xml
     * @param $type
     * @return mixed|null
     * @throws Exception
     */
    private function validateSchematron($xml, $type = 'ccda')
    {
        try {
            $result = $this->schematronValidateDocument($xml, $type);
        } catch (Exception $e) {
            $e = $e->getMessage();
            error_log($e);
        }
        return $result;
    }

    /**
     * @param $error
     * @return string
     */
    private function formatXsdError($error): string
    {
        $error_str = "\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $error_str .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $error_str .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $error_str .= "Fatal Error $error->code: ";
                break;
        }
        $error_str .= trim($error->message);
        $error_str .= " on line $error->line\n";

        return $error_str;
    }

    public function createSchematronHtml($amid)
    {
        $errors = $this->fetchValidationLog($amid);
        $xsd = $errors['xsd'];
        $schema = $errors['errors'];

        $html = "<div class='control-group'>\n" .
            "<h5 class='text-info' style='padding: 0 0;'>" . xlt('Schema Definition Errors') . "</h5><hr style='margin: 0 0 10px;' />\n";
        if (empty($xsd)) {
            $html .= "<p class='text-success'>" . xlt("Passed XSD testing.") . "</p><hr style='margin: 0 0 10px;' />\n";
        }
        foreach ($xsd as $error) {
            $html .= "<blockquote style='margin: 0 0 2px;padding: 0px 5px 0 5px;'>" .
                "<p class='text-error' style='font-size:12px;'>" . text($error) . "</p>" .
                "</blockquote><hr style='margin: 0 0 10px;' />\n";
        }
        $html .= "<h5 class='text-info'>" . xlt('Schematron Errors') . "</h5><hr style='margin: 0 0 10px;' />\n";
        if (empty($errors['errorCount'])) {
            $html .= "<p class='text-success'>" . xlt("Passed Schematron testing.") . "</p><hr style='margin: 0 0 10px;' />\n";
        }
        foreach ($schema as $error) {
            $html .= "<blockquote style='margin: 0 0 2px;padding: 0px 5px 0 5px;'>" .
                "<p class='text-error' style='font-size:12px;'>" .
                "<span style='color: red;padding-right: 2px;'>" . xlt('Error') . ": </span>" . text($error['description']) .
                "<br />" . "<span style='color: red;margin-right: 2px;'>" . xlt('Error Context') . ": </span>" . text($error['context']) .
                "<br />" . "<span style='color: red;margin-right: 2px;'>" . xlt('Where') . ": </span>" . text($error['path']) .
                "<br />" . "<span style='color: red;margin-right: 2px;'>" . xlt('Line') . "# " . "</span>" . text($error['line']) .
                "</p></blockquote><hr style='margin: 0 0 10px;' />\n";
        }
        $html .= "</div>\n";

        return $html;
    }

    /**
     * @param $docId
     * @param $log
     * @return void
     */
    public function saveValidationLog($docId, $log)
    {
        $content = json_encode($log ?? []);
        sqlStatement("UPDATE `documents` SET `document_data` = ? WHERE `id` = ?", array($content, $docId));
    }

    /**
     * @param $docId
     * @return mixed
     */
    public function fetchValidationLog($audit_id)
    {
        $log = sqlQuery("SELECT `document_data` FROM `documents` WHERE `audit_master_id` = ?", array($audit_id))['document_data'];
        return json_decode($log ?? [], true);
    }
}
