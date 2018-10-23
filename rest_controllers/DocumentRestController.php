<?php
/**
 * DocumentRestController
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
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

    public function downloadFile($pid, $did)
    {
        $results = $this->documentService->getFile($pid, $did);
        $file = $results['url'];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Type: application/force-download");
            header('Content-Disposition: attachment; filename=' . urlencode(basename($file)));
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        } else {
            http_response_code(400);
        }
    }
}