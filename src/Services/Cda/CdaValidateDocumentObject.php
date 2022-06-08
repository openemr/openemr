<?php

namespace OpenEMR\Services\Cda;

use Document;
use XMLReader;

class CdaValidateDocumentObject
{
    public function isCdaDocument(Document $document)
    {
//        // we check the mime extension
//        // if its a zip file we will peak inside the zip to determine if this is an IHE XDM file.
//        if ($this->isZipDocument($document)) {
//
//        }

        //$cdaMimeTypes = ['text/xml', 'application/xml', 'application/zip'];
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

    public function getCdaFromDocument(Document $document): ?string
    {
        // given a document find the string contents representing a document
        if ($this->isZipDocument($document)) {
            return $this->getCdaFromZip($document);
        } else if ($this->isXmlDocument($document)) {
            return null;
        }
        return "";
    }

    private function isZipDocument(Document $document)
    {
        return false;
    }

    private function getCdaFromZip(Document $document)
    {
        return null;
    }

    private function isXmlDocument(Document $document)
    {
        /**
         *
        if ($d->get_mimetype() == 'text/xml' || $d->get_mimetype() == 'application/xml') {
        // we will do our check here
        $z = new XMLReader();
        $z->XML($d->get_data());
        while ($z->read() && $z->name != '')
        }
         */
        return true;
    }
}
