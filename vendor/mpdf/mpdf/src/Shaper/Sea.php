<?php

namespace Mpdf\Shaper;

class Sea
{
	// South East Asian shaper
	// sea_category
	const OT_X = 0;

	const OT_C = 1;

	const OT_IV = 2;  # Independent Vowel

	const OT_T = 3;  # Tone Marks

	const OT_H = 4;  # Halant

	const OT_A = 10; # Anusvara

	const OT_GB = 12; # Generic Base	(OT_DOTTEDCIRCLE in Indic)

	const OT_CM = 17; # Consonant Medial

	const OT_MR = 22; # Medial Ra

	const OT_VABV = 26;

	const OT_VBLW = 27;

	const OT_VPRE = 28;

	const OT_VPST = 29;

	// ? From Indic categories
	const OT_ZWNJ = 5;

	const OT_ZWJ = 6;

	const OT_M = 7;

	const OT_SM = 8;

	const OT_VD = 9;

	const OT_NBSP = 11;

	const OT_RS = 13;

	const OT_COENG = 14;

	const OT_REPHA = 15;

	const OT_RA = 16;

	/* Visual positions in a syllable from left to right. */
	// sea_position
	const POS_START = 0;

	const POS_RA_TO_BECOME_REPH = 1;

	const POS_PRE_M = 2;

	const POS_PRE_C = 3;

	const POS_BASE_C = 4;

	const POS_AFTER_MAIN = 5;

	const POS_ABOVE_C = 6;

	const POS_BEFORE_SUB = 7;

	const POS_BELOW_C = 8;

	const POS_AFTER_SUB = 9;

	const POS_BEFORE_POST = 10;

	const POS_POST_C = 11;

	const POS_AFTER_POST = 12;

	const POS_FINAL_C = 13;

	const POS_SMVD = 14;

	const POS_END = 15;

	// Based on sea_category used to make string to find syllables
	// OT_ to string character (using e.g. OT_C from INDIC) hb-ot-shape-complex-sea-private.hh
	public static $sea_category_char = [
		'x',
		'C',
		'V',
		'T',
		'H',
		'x',
		'x',
		'x',
		'x',
		'x',
		'A',
		'x',
		'G',
		'x',
		'x',
		'x',
		'x',
		'M',
		'x',
		'x',
		'x',
		'x',
		'R',
		'x',
		'x',
		'x',
		'a',
		'b',
		'p',
		't',
	];

	public static function set_sea_properties(&$info, $scriptblock)
	{
		$u = $info['uni'];
		$type = self::sea_get_categories($u);
		$cat = ($type & 0x7F);
		$pos = ($type >> 8);

		/*
		 * Re-assign category
		 */
		// Medial Ra
		if ($u == 0x1A55 || $u == 0xAA34) {
			$cat = self::OT_MR;
		}

		/*
		 * Re-assign position.
		 */
		if ($cat == self::OT_M) { // definitely "OT_M" in HarfBuzz - although this does not seem to have been defined ? should be OT_MR
			switch ($pos) {
				case self::POS_PRE_C:
					$cat = self::OT_VPRE;
					break;
				case self::POS_ABOVE_C:
					$cat = self::OT_VABV;
					break;
				case self::POS_BELOW_C:
					$cat = self::OT_VBLW;
					break;
				case self::POS_POST_C:
					$cat = self::OT_VPST;
					break;
			}
		}

		$info['sea_category'] = $cat;
		$info['sea_position'] = $pos;
	}

	// syllable_type
	const CONSONANT_SYLLABLE = 0;

	const BROKEN_CLUSTER = 1;

	const NON_SEA_CLUSTER = 2;

	public static function set_syllables(&$o, $s, &$broken_syllables)
	{
		$ptr = 0;
		$syllable_serial = 1;
		$broken_syllables = false;
		while ($ptr < strlen($s)) {
			$match = '';
			$syllable_length = 1;
			$syllable_type = self::NON_SEA_CLUSTER;

			// CONSONANT_SYLLABLE Consonant syllable
			if (preg_match('/^(C|V|G)(p|a|b|t|HC|M|R|T|A)*/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::CONSONANT_SYLLABLE;
			} // BROKEN_CLUSTER syllable
			elseif (preg_match('/^(p|a|b|t|HC|M|R|T|A)+/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::BROKEN_CLUSTER;
				$broken_syllables = true;
			}

			for ($i = $ptr; $i < $ptr + $syllable_length; $i++) {
				$o[$i]['syllable'] = ($syllable_serial << 4) | $syllable_type;
			}
			$ptr += $syllable_length;
			$syllable_serial++;
			if ($syllable_serial == 16) {
				$syllable_serial = 1;
			}
		}
	}

	public static function initial_reordering(&$info, $GSUBdata, $broken_syllables, $scriptblock, $dottedcircle)
	{

		if ($broken_syllables && $dottedcircle) {
			self::insert_dotted_circles($info, $dottedcircle);
		}

		$count = count($info);
		if (!$count) {
			return;
		}
		$last = 0;
		$last_syllable = $info[0]['syllable'];
		for ($i = 1; $i < $count; $i++) {
			if ($last_syllable != $info[$i]['syllable']) {
				self::initial_reordering_syllable($info, $GSUBdata, $scriptblock, $last, $i);
				$last = $i;
				$last_syllable = $info[$last]['syllable'];
			}
		}
		self::initial_reordering_syllable($info, $GSUBdata, $scriptblock, $last, $count);
	}

	public static function insert_dotted_circles(&$info, $dottedcircle)
	{
		$idx = 0;
		$last_syllable = 0;
		while ($idx < count($info)) {
			$syllable = $info[$idx]['syllable'];
			$syllable_type = ($syllable & 0x0F);
			if ($last_syllable != $syllable && $syllable_type == self::BROKEN_CLUSTER) {
				$last_syllable = $syllable;
				$dottedcircle[0]['syllable'] = $info[$idx]['syllable'];
				array_splice($info, $idx, 0, $dottedcircle);
			} else {
				$idx++;
			}
		}
	}

	public static function initial_reordering_syllable(&$info, $GSUBdata, $scriptblock, $start, $end)
	{
		/* broken_cluster: We already inserted dotted-circles, so just call the standalone_cluster. */

		$syllable_type = ($info[$start]['syllable'] & 0x0F);
		if ($syllable_type == self::NON_SEA_CLUSTER) {
			return;
		}
		if ($syllable_type == self::BROKEN_CLUSTER) {
			/* For dotted-circle, this is what Uniscribe does:
			 * If dotted-circle is the last glyph, it just does nothing. */
			if ($info[$end - 1]['sea_category'] == self::OT_GB) {
				return;
			}
		}

		$base = $start;
		$i = $start;
		for (; $i < $base; $i++) {
			$info[$i]['sea_position'] = self::POS_PRE_C;
		}
		if ($i < $end) {
			$info[$i]['sea_position'] = self::POS_BASE_C;
			$i++;
		}
		for (; $i < $end; $i++) {
			if (isset($info[$i]['sea_category']) && $info[$i]['sea_category'] == self::OT_MR) { /* Pre-base reordering */
				$info[$i]['sea_position'] = self::POS_PRE_C;
				continue;
			}
			if (isset($info[$i]['sea_category']) && $info[$i]['sea_category'] == self::OT_VPRE) { /* Left matra */
				$info[$i]['sea_position'] = self::POS_PRE_M;
				continue;
			}
			$info[$i]['sea_position'] = self::POS_AFTER_MAIN;
		}

		/* Sit tight, rock 'n roll! */
		self::bubble_sort($info, $start, $end - $start);
	}

	public static function final_reordering(&$info, $GSUBdata, $scriptblock)
	{
		$count = count($info);
		if (!$count) {
			return;
		}
		$last = 0;
		$last_syllable = $info[0]['syllable'];
		for ($i = 1; $i < $count; $i++) {
			if ($last_syllable != $info[$i]['syllable']) {
				self::final_reordering_syllable($info, $GSUBdata, $scriptblock, $last, $i);
				$last = $i;
				$last_syllable = $info[$last]['syllable'];
			}
		}
		self::final_reordering_syllable($info, $GSUBdata, $scriptblock, $last, $count);
	}

	public static function final_reordering_syllable(&$info, $GSUBdata, $scriptblock, $start, $end)
	{
		/*
		 * Nothing to do here at present!
		 */
	}

	public static $sea_table = [
		/* New Tai Lue  (1980..19DF) */

		/* 1980 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1988 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1990 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1998 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 19A0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 19A8 */ 3841, 3841, 3841, 3841, 3840, 3840, 3840, 3840,
		/* 19B0 */ 2823, 2823, 2823, 2823, 2823, 775, 775, 775,
		/* 19B8 */ 2823, 2823, 775, 2823, 2823, 2823, 2823, 2823,
		/* 19C0 */ 2823, 3857, 3857, 3857, 3857, 3857, 3857, 3857,
		/* 19C8 */ 3843, 3843, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 19D0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 19D8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Tai Tham  (1A20..1AAF) */

		/* 1A20 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1A28 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1A30 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1A38 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1A40 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1A48 */ 3841, 3841, 3841, 3841, 3841, 3842, 3842, 3842,
		/* 1A50 */ 3842, 3842, 3842, 3841, 3841, 3857, 3857, 3857,
		/* 1A58 */ 3857, 3857, 3857, 3857, 3857, 3857, 3857, 3840,
		/* 1A60 */ 3844, 2823, 1543, 2823, 2823, 1543, 1543, 1543,
		/* 1A68 */ 1543, 2055, 2055, 1543, 2055, 2823, 775, 775,
		/* 1A70 */ 775, 775, 775, 1543, 1543, 3843, 3843, 3843,
		/* 1A78 */ 3843, 3843, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1A80 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1A88 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1A90 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1A98 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1AA0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1AA8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Cham  (AA00..AA5F) */

		/* AA00 */ 3842, 3842, 3842, 3842, 3842, 3842, 3841, 3841,
		/* AA08 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* AA10 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* AA18 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* AA20 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* AA28 */ 3841, 1543, 1543, 1543, 1543, 2055, 1543, 775,
		/* AA30 */ 775, 1543, 2055, 3857, 3857, 3857, 3857, 3840,
		/* AA38 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* AA40 */ 3857, 3857, 3857, 3857, 3857, 3857, 3857, 3857,
		/* AA48 */ 3857, 3857, 3857, 3857, 3857, 3857, 3840, 3840,
		/* AA50 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* AA58 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
	];

	public static function sea_get_categories($u)
	{
		if (0x1980 <= $u && $u <= 0x19DF) {
			return self::$sea_table[$u - 0x1980]; // offset 0 for New Tai Lue
		}
		if (0x1A20 <= $u && $u <= 0x1AAF) {
			return self::$sea_table[$u - 0x1A20 + 96]; // offset for Tai Tham
		}
		if (0xAA00 <= $u && $u <= 0xAA5F) {
			return self::$sea_table[$u - 0xAA00 + 96 + 144];  // Cham
		}
		if ($u == 0x00A0) {
			return 3851; // (ISC_CP | (IMC_x << 8))
		}
		if ($u == 0x25CC) {
			return 3851; // (ISC_CP | (IMC_x << 8))
		}
		return 3840; // (ISC_x | (IMC_x << 8))
	}

	public static function bubble_sort(&$arr, $start, $len)
	{
		if ($len < 2) {
			return;
		}
		$k = $start + $len - 2;
		while ($k >= $start) {
			for ($j = $start; $j <= $k; $j++) {
				if ($arr[$j]['sea_position'] > $arr[$j + 1]['sea_position']) {
					$t = $arr[$j];
					$arr[$j] = $arr[$j + 1];
					$arr[$j + 1] = $t;
				}
			}
			$k--;
		}
	}
}
