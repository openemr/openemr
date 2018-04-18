<?php

namespace ZendBench\Cache;

use Zend\Cache\StorageFactory;

/**
 * @Revs(100)
 * @Iterations(10)
 * @Warmup(1)
 */
class FilesystemStorageAdapterBench extends CommonStorageAdapterBench
{
    private $tmpCacheDir;

    public function __construct()
    {
        $this->tmpCacheDir = @tempnam(sys_get_temp_dir(), 'zend_cache_test_');
        if (! $this->tmpCacheDir) {
            $err = error_get_last();
            $this->fail("Can't create temporary cache directory-file: {$err['message']}");
        } elseif (! @unlink($this->tmpCacheDir)) {
            $err = error_get_last();
            $this->fail("Can't remove temporary cache directory-file: {$err['message']}");
        } elseif (! @mkdir($this->tmpCacheDir, 0777)) {
            $err = error_get_last();
            $this->fail("Can't create temporary cache directory: {$err['message']}");
        }

        $this->storage = StorageFactory::adapterFactory('filesystem', [
            'cache_dir' => $this->tmpCacheDir,
        ]);

        parent::__construct();
    }

    public function __destruct()
    {
        $this->removeRecursive($this->tmpCacheDir);
    }

    private function removeRecursive($dir)
    {
        if (file_exists($dir)) {
            $dirIt = new \DirectoryIterator($dir);
            foreach ($dirIt as $entry) {
                $fname = $entry->getFilename();
                if ($fname == '.' || $fname == '..') {
                    continue;
                }

                if ($entry->isFile()) {
                    unlink($entry->getPathname());
                } else {
                    $this->removeRecursive($entry->getPathname());
                }
            }

            rmdir($dir);
        }
    }
}
