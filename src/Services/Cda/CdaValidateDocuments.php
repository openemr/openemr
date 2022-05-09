<?php

namespace OpenEMR\Services\Cda;

/**
 * CDA QRDA Validation Class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use DOMDocument;

class CdaValidateDocuments
{
    private $schematron;
    private $reportType;

    public function __construct($reportType = 'simple')
    {
        $this->reportType = Schematron::RESULT_SIMPLE;
        if ($reportType == 'complex') {
            $this->reportType = Schematron::RESULT_COMPLEX;
        }
        $this->schematron = new Schematron();
    }

    public function validateXmlXsd($document, $type)
    {
        libxml_use_internal_errors(true);
        $dom = new DomDocument();
        $dom->loadXML($document);
        $xsd = __DIR__ . '/../../../interface/modules/zend_modules/public/xsd/Schema/CDA2/infrastructure/cda/CDA_SDTC.xsd';
        $result = $dom->schemaValidate($xsd);
        // TODO phase implementation for schematron
        $this->validateSchematron($document, $type);
        if ($result) {
            return true;
        } else {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                error_log($this->formatXsdError($error));
            }
            libxml_clear_errors();

            return false;
        }
    }

    private function validateSchematron($xml, $type = 'ccda')
    {
        libxml_use_internal_errors(true);
        $schema_qrda = __DIR__ . '/../../../interface/modules/zend_modules/public/schematrons/qrda1/2022_CMS_QRDA_I.sch';
        $schema_qrda3 = __DIR__ . '/../../../interface/modules/zend_modules/public/schematrons/qrda3/2022_CMS_QRDA_III.sch';
        $schema = __DIR__ . '/../../../interface/modules/zend_modules/public/schematrons/ccda/Consolidation.sch';

        if ($type == 'qrda') {
            $schema = $schema_qrda;
        } elseif ($type == 'qrda3') {
            $schema = $schema_qrda3;
        }

        try {
            $this->schematron->setOptions(Schematron::INCLUDE_ALL);
            $this->schematron->load($schema);
            $document = new DOMDocument();
            $document->loadXML($xml);
            $result = $this->schematron->validate($document, $this->reportType);
            return $result;
        } catch (SchematronException $e) {
            $e = $e->getMessage();
            error_log($e);
        }
    }

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
        if ($error->file) {
            $error_str .=    " in $error->file";
        }
        $error_str .= " on line $error->line\n";

        return $error_str;
    }
}
