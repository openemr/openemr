<?php

namespace Mpdf\Utils;

class PdfDate
{

	/**
	 * PDF documents use the internal date format: (D:YYYYMMDDHHmmSSOHH'mm'). The date format has these parts:
	 *
	 *   YYYY The full four-digit year. (For example, 2004)
	 *   MM   The month from 01 to 12.
	 *   DD   The day from 01 to 31.
	 *   HH   The hour from 00 to 23.
	 *   mm   The minute from 00 to 59.
	 *   SS   The seconds from 00 to 59.
	 *   O    The relationship of local time to Universal Time (UT), as denoted by one of the characters +, -, or Z.
	 *   HH   The absolute value of the offset from UT in hours specified as 00 to 23.
	 *   mm   The absolute value of the offset from UT in minutes specified as 00 to 59.
	 *
	 * @return string
	 */
	public static function format($date)
	{
		$z = date('O'); // +0200
		$offset = substr($z, 0, 3) . "'" . substr($z, 3, 2) . "'"; // +02'00'
		return date('YmdHis', $date) . $offset;
	}

}
