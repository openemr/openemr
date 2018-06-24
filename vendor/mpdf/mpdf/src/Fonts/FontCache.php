<?php

namespace Mpdf\Fonts;

use Mpdf\Cache;

class FontCache
{

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

	public function load($filename)
	{
		return $this->cache->load($filename);
	}

	public function write($filename, $data)
	{
		return $this->cache->write($filename, $data);
	}

	public function binaryWrite($filename, $data)
	{
		$handle = fopen($this->tempFilename($filename), 'wb');
		fwrite($handle, $data);
		fclose($handle);
	}

	public function remove($filename)
	{
		return $this->cache->remove($filename);
	}
}
