<?php

/**
 * CdaValidateDocumentObject will validate the xsd schema, schematron rules, and MDHT rules for a valid CDA that is stored
 * in a Document class object.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Cda;

use Document;
use XMLReader;

class CdaValidateDocumentObject
{
    public function isCdaDocument(Document $document)
    {

//        // we check the mime extension
        if ($this->isZipDocument($document)) {
            // TODO: if we want to do any zip file validations we can do that here.
            // The following link is the validation that ETT does for their XDM format:
            // https://github.com/usnistgov/iheos-toolkit2/blob/0d8efacc00daaba27845f3c0e1d4f4bb37bb72c6/validators-registry-message/src/main/java/gov/nist/toolkit/valregmsg/xdm/XdmDecoder.java
            // the validation is pretty primitive and it looks like they threw out trying to validate CDA 2.1 docs.
        }

        $cdaMimeTypes = ['text/xml', 'application/xml'];
        if (in_array($document->get_mimetype(), $cdaMimeTypes)) {
            // now we can open the file and check if we actually have a cda document...
            return true;
        }
        return false;
    }

    public function getValidationErrorsForDocument(Document $document)
    {
        $cdaValidateDocuments = new CdaValidateDocuments();
        // TODO: @adunsulag do we need to cache this get_data here?  also need to figure out the doc type
        $errors = $cdaValidateDocuments->validateDocument($document->get_data(), 'ccda');
        return $errors;
    }

    private function isZipDocument(Document $document)
    {
        return in_array($document->get_mimetype(), ['application/zip', 'application/octet-stream']);
    }
}
