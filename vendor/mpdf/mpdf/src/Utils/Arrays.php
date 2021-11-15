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

	/**
	 * Returns an array of all k-combinations from an input array of n elements, where k equals 1..n.
	 * Elements will be sorted and unique in every combination.
	 *
	 * Example: array[one, two] will give:
	 * [
	 *     [one],
	 *     [two],
	 *     [one, two]
	 * ]
	 * @param array $array
	 * @return array
	 */
	public static function allUniqueSortedCombinations($array)
	{
		$input = array_unique($array);
		if (count($input) <= 1) {
			return [$input];
		}

		sort($input);
		$combinations = [];
		foreach ($input as $value) {
			$combinations[] = [$value];
		}

		$n = count($input);
		for ($k = 2; $k <= $n; $k++) {
			$combinations = array_merge($combinations, self::combinations($input, $k));
		}

		return $combinations;
	}

	/**
	 * Returns an array of unique k-combinations from an input array.
	 *
	 * Example: array=[one, two, three] and k=2 will give:
	 * [
	 *     [one, two],
	 *     [one, three]
	 * ]
	 * @param array $array
	 * @param int $k
	 * @return array
	 */
	public static function combinations($array, $k)
	{
		$n = count($array);
		$combinations = [];
		$indexes = range(0, $k - 1);
		$maxIndexes = range($n - $k, $n - 1);
		do {
			$combination = [];
			foreach ($indexes as $index) {
				$combination[] = $array[$index];
			}
			$combinations[] = $combination;

			$anotherCombination = false;
			for ($i = $k - 1; $i >= 0; $i--) {
				if ($indexes[$i] < $maxIndexes[$i]) {
					$indexes[$i]++;
					$anotherCombination = true;
					break;
				}
			}
		} while ($anotherCombination);

		return $combinations;
	}
}
