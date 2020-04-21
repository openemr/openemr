<?php

/**
 * DocumentRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\DocumentService;
use OpenEMR\RestControllers\RestControllerHelper;

class DocumentRestController
{
    private $documentService;

    public function __construct()
    {
        $this->documentService = new DocumentService();
    }

    public function getAllAtPath($pid, $path)
    {
        $serviceResult = $this->documentService->getAllAtPath($pid, $path);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function postWithPath($pid, $path, $fileData)
    {
        $serviceResult = $this->documentService->insertAtPath($pid, $path, $fileData);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function downloadFile($pid, $did)
    {
        $results = $this->documentService->getFile($pid, $did);

        if (!empty($results)) {
            header('Content-Description: File Transfer');
            header("Content-Type: " . $results['mimetype']);
            header('Content-Disposition: attachment; filename=' . $results['filename']);
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . strlen($results['file']));
            echo $results['file'];
            exit;
        } else {
            http_response_code(400);
        }
    }
}
