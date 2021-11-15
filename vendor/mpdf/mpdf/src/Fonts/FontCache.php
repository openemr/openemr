<?php

namespace Mpdf\Fonts;

use Mpdf\Cache;

class FontCache
{

	private $memoryCache = [];

	private $cache;

	public function __construct(Cache $cache)
	{
		$this->cache = $cache;
	}

	public function tempFilename($filename)
	{
		return $this->cache->tempFilename($filename);
	}

	public function has($filename)
	{
		return $this->cache->has($filename);
	}

	public function jsonHas($filename)
	{
		return (isset($this->memoryCache[$filename]) || $this->has($filename));
	}

	public function load($filename)
	{
		return $this->cache->load($filename);
	}

	public function jsonLoad($filename)
	{
		if (isset($this->memoryCache[$filename])) {
			return $this->memoryCache[$filename];
		}

		$this->memoryCache[$filename] = json_decode($this->load($filename), true);
		return $this->memoryCache[$filename];
	}

	public function write($filename, $data)
	{
		return $this->cache->write($filename, $data);
	}

	public function binaryWrite($filename, $data)
	{
		return $this->cache->write($filename, $data);
	}

	public function jsonWrite($filename, $data)
	{
		return $this->cache->write($filename, json_encode($data));
	}

	public function remove($filename)
	{
		return $this->cache->remove($filename);
	}

	public function jsonRemove($filename)
	{
		if (isset($this->memoryCache[$filename])) {
			unset($this->memoryCache[$filename]);
		}

		$this->remove($filename);
	}
}
