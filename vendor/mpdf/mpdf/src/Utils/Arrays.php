<?php

namespace Mpdf\Utils;

class Arrays
{

	public static function get($array, $key, $default = null)
	{
		if (is_array($array) && array_key_exists($key, $array)) {
			return $array[$key];
		}

		if (func_num_args() < 3) {
			throw new \InvalidArgumentException(sprintf('Array does not contain key "%s"', $key));
		}

		return $default;
	}
}
