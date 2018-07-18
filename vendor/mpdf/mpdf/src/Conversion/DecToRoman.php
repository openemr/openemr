<?php

namespace Mpdf\Conversion;

/**
 * @link https://github.com/JeroenDeDauw/RomanNumbers
 * @license GNU GPL v2+
 */
class DecToRoman
{

	private $symbolMap;

	public function __construct(array $symbolMap = [])
	{
		if ($symbolMap !== []) {
			$this->symbolMap = $symbolMap;
		} else {
			$this->symbolMap = [['I', 'V'], ['X', 'L'], ['C', 'D'], ['M']];
		}
	}

	public function convert($number)
	{
		$this->ensureNumberIsAnInteger($number);
		$this->ensureNumberIsWithinBounds($number);

		return $this->constructRomanString($number);
	}

	private function ensureNumberIsAnInteger($number)
	{
		if (!is_int($number)) {
			throw new \InvalidArgumentException('Can only translate integers to roman');
		}
	}

	private function ensureNumberIsWithinBounds($number)
	{
		if ($number < 1) {
			throw new \OutOfRangeException('Numbers under one cannot be translated to roman');
		}

		if ($number > $this->getUpperBound()) {
			throw new \OutOfBoundsException('The provided number is to big to be fully translated to roman');
		}
	}

	public function getUpperBound()
	{
		$symbolGroupCount = count($this->symbolMap);
		$valueOfOne = 10 ** ($symbolGroupCount - 1);

		$hasFiveSymbol = array_key_exists(1, $this->symbolMap[$symbolGroupCount - 1]);

		return $valueOfOne * ($hasFiveSymbol ? 9 : 4) - 1;
	}

	private function constructRomanString($number)
	{
		$romanNumber = '';

		$symbolMapCount = count($this->symbolMap);
		for ($i = 0; $i < $symbolMapCount; $i++) {
			$divisor = 10 ** ($i + 1);
			$remainder = $number % $divisor;
			$digit = $remainder / (10 ** $i);

			$number -= $remainder;
			$romanNumber = $this->formatDigit($digit, $i) . $romanNumber;

			if ($number === 0) {
				break;
			}
		}

		return $romanNumber;
	}

	private function formatDigit($digit, $orderOfMagnitude)
	{
		if ($digit === 0) {
			return '';
		}

		if ($digit === 4 || $digit === 9) {
			return $this->formatFourOrNine($digit, $orderOfMagnitude);
		}

		$romanNumber = '';

		if ($digit >= 5) {
			$digit -= 5;
			$romanNumber .= $this->getFiveSymbol($orderOfMagnitude);
		}

		$romanNumber .= $this->formatOneToThree($orderOfMagnitude, $digit);

		return $romanNumber;
	}

	private function formatFourOrNine($digit, $orderOfMagnitude)
	{
		$firstSymbol = $this->getOneSymbol($orderOfMagnitude);
		$secondSymbol = $digit === 4
			? $this->getFiveSymbol($orderOfMagnitude)
			: $this->getTenSymbol($orderOfMagnitude);

		return $firstSymbol . $secondSymbol;
	}

	private function formatOneToThree($orderOfMagnitude, $digit)
	{
		return str_repeat($this->getOneSymbol($orderOfMagnitude), $digit);
	}

	private function getOneSymbol($orderOfMagnitude)
	{
		return $this->symbolMap[$orderOfMagnitude][0];
	}

	private function getFiveSymbol($orderOfMagnitude)
	{
		return $this->symbolMap[$orderOfMagnitude][1];
	}

	private function getTenSymbol($orderOfMagnitude)
	{
		return $this->symbolMap[$orderOfMagnitude + 1][0];
	}

}
