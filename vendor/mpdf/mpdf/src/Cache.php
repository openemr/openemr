<?php

namespace Mpdf;

use DirectoryIterator;

class Cache
{

	private $basePath;

	private $cleanupInterval;

	public function __construct($basePath, $cleanupInterval = 3600)
	{
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
		$path = $this->getFilePath($filename);

		file_put_contents($path, $data);

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
		return $item->getMTime() + $this->cleanupInterval < time();
	}

	public function isDotFile(DirectoryIterator $item)
	{
		return substr($item->getFilename(), 0, 1) === '.';
	}
}
