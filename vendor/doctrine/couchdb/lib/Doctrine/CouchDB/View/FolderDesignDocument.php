<?php

namespace Doctrine\CouchDB\View;

class FolderDesignDocument implements DesignDocument
{
    /**
     * @var string
     */
    private $folderPath;

    /**
     * @var array
     */
    private $data;

    public function __construct($folderPath)
    {
        $this->folderPath = realpath($folderPath);
    }

    public function getData()
    {
        if ($this->data === null) {
            $rdi = new \RecursiveDirectoryIterator($this->folderPath, \FilesystemIterator::CURRENT_AS_FILEINFO);
            $ri = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::LEAVES_ONLY);

            $this->data = array();
            foreach ($ri AS $path) {
                $fileData = $this->getFileData($path);
                if ($fileData !== null) {
                    $parts = explode(DIRECTORY_SEPARATOR, ltrim(str_replace($this->folderPath, '', $fileData["key"]), DIRECTORY_SEPARATOR));

                    if (count($parts) == 3) {
                        $this->data[$parts[0]][$parts[1]][$parts[2]] = $fileData["data"];
                    } else if (count($parts) == 2) {
                        $this->data[$parts[0]][$parts[1]] = $fileData["data"];
                    } else if (count($parts) == 1) {
                        $this->data[$parts[0]] = $fileData["data"];
                    }
                }
            }

            $this->data['language'] = 'javascript';
        }

        return $this->data;
    }

    private function getFileData($path)
    {
        $result = null;
        if (substr($path, -3) === ".js") {
            $result = array("key" => str_replace(".js", "", $path),
                            "data"=> file_get_contents($path));
        } else if (substr($path, -5) === ".json") {
            $result = array("key" => str_replace(".json", "", $path),
                            "data"=> json_decode(file_get_contents($path), true));
        }
        return $result;
    }
}
