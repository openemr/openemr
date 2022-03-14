<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Installer\Controller;


/**
 * the purpose for this class is to allow users to import modules to the system with no technical knowledge
 * All they need is a zip file location from the module author
 */
class ModuleImport
{
    private $url;
    private $name;
    public $importDir;

    /**
     * @param $url
     * @param $name
     * @param $import_dir
     */
    public function __construct($url, $name, $import_dir)
    {
        $this->url = $url;
        $this->name = $name;
        $this->importDir = $import_dir;
        return self::download();
    }

    /**
     * @return mixed|string
     */
    private function download()
    {
        $zipResource = fopen($this->importDir . $this->name, "w");
        // Get The Zip File From Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $page = curl_exec($ch);
        if(!$page) {
            return "Error :- ".curl_error($ch);
        } else {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);
        return $status;
    }

    /**
     * @return string
     */
    public static function createImportDir(): string
    {
        $import_dir = dirname(__DIR__, 8) . DIRECTORY_SEPARATOR . "sites" . $_SESSION['site_id'] .
            DIRECTORY_SEPARATOR . "documents" . DIRECTORY_SEPARATOR . 'imports/';
        if (!is_dir($import_dir)) {
            $import_dir = dirname(__DIR__, 8) . DIRECTORY_SEPARATOR . "sites" .
                DIRECTORY_SEPARATOR . $_SESSION['site_id'] .
                DIRECTORY_SEPARATOR . "documents" . DIRECTORY_SEPARATOR;
                mkdir($import_dir . DIRECTORY_SEPARATOR . "imports");

            return $import_dir  . "imports/";
        } else {
            return $import_dir;
        }
    }

    /**
     * @param $destination
     * @return string
     */
    public static function createDestinationFolder($destination): string
    {
        //if this is the first time installing the module create directory for unzipping
        $makeDir = mkdir($destination);
        if (isset($destination)  && !is_dir($destination)) {
            $makeDir;
        } else {
            // if the folder exist and this could be a re-installation or update to the module.
            array_map('unlink', glob($destination . DIRECTORY_SEPARATOR . "*.*"));
            rmdir($destination);
            $makeDir;
        }
        return "created";
    }
}
