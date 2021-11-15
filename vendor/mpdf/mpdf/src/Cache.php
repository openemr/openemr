<?php

namespace Mpdf;

use DirectoryIterator;

class Cache
{

	private $basePath;

	private $cleanupInterval;

	public function __construct($basePath, $cleanupInterval = 3600)
	{
		if (!is_int($cleanupInterval) && false !== $cleanupInterval) {
			throw new \Mpdf\MpdfException('Cache cleanup interval has to be an integer or false');
		}

		if (!$this->createBasePath($basePath)) {
			throw new \Mpdf\MpdfException(sprintf('Temporary files directory "%s" is not writable', $basePath));
		}

		$this->basePath = $basePath;
		$this->cleanupInterval = $cleanupInterval;
	}

	protected function createBasePath($basePath)
	{
		if (!file_exists($basePath)) {
			if (!$this->createBasePath(dirname($basePath))) {
				return false;
			}

			if (!$this->createDirectory($basePath)) {
				return false;
			}
		}

		if (!is_writable($basePath) || !is_dir($basePath)) {
			return false;
		}

		return true;
	}

	protected function createDirectory($basePath)
	{
		if (!mkdir($basePath)) {
			return false;
		}

		if (!chmod($basePath, 0777)) {
			return false;
		}

		return true;
	}

	public function tempFilename($filename)
	{
		return $this->getFilePath($filename);
	}

	public function has($filename)
	{
		return file_exists($this->getFilePath($filename));
	}

	public function load($filename)
	{
		return file_get_contents($this->getFilePath($filename));
	}

	public function write($filename, $data)
	{
		$tempFile = tempnam($this->basePath, 'cache_tmp_');
		file_put_contents($tempFile, $data);

		$path = $this->getFilePath($filename);
		rename($tempFile, $path);

		return $path;
	}

	public function remove($filename)
	{
		return unlink($this->getFilePath($filename));
	}

	public function clearOld()
	{
		$iterator = new DirectoryIterator($this->basePath);

		/** @var \DirectoryIterator $item */
		foreach ($iterator as $item) {
			if (!$item->isDot()
					&& $item->isFile()
					&& !$this->isDotFile($item)
					&& $this->isOld($item)) {
				unlink($item->getPathname());
			}
		}
	}

	private function getFilePath($filename)
	{
		return $this->basePath . '/' . $filename;
	}

	private function isOld(DirectoryIterator $item)
	{
		return $this->cleanupInterval
			? $item->getMTime() + $this->cleanupInterval < time()
			: false;
	}

	public function isDotFile(DirectoryIterator $item)
	{
		return substr($item->getFilename(), 0, 1) === '.';
	}
}
