<?php

class MYANMAR
{
	/* FROM hb-ot-shape-complex-indic-private.hh */

	// indic_category
	const OT_X = 0;
	const OT_C = 1;
	const OT_V = 2;
	const OT_N = 3;
	const OT_H = 4;
	const OT_ZWNJ = 5;
	const OT_ZWJ = 6;
	const OT_M = 7;  /* Matra or Dependent Vowel */
	const OT_SM = 8;
	const OT_VD = 9;
	const OT_A = 10;
	const OT_NBSP = 11;
	const OT_DOTTEDCIRCLE = 12; /* Not in the spec, but special in Uniscribe. /Very very/ special! */
	const OT_RS = 13;   /* Register Shifter, used in Khmer OT spec */
	const OT_Coeng = 14;
	const OT_Repha = 15;
	const OT_Ra = 16;   /* Not explicitly listed in the OT spec, but used in the grammar. */
	const OT_CM = 17;

	/* FROM hb-ot-shape-complex-myanmar.hh */

	// myanmar_category
	const OT_DB = 3;  // same as INDIC::OT_N; /* Dot below */
	const OT_GB = 12;  // same as INDIC::OT_DOTTEDCIRCLE;
	const OT_As = 18; /* Asat */
	const OT_D = 19; /* Digits except zero */
	const OT_D0 = 20; /* Digit zero */
	const OT_MH = 21; /* Various consonant medial types */
	const OT_MR = 22; /* Various consonant medial types */
	const OT_MW = 23; /* Various consonant medial types */
	const OT_MY = 24; /* Various consonant medial types */
	const OT_PT = 25; /* Pwo and other tones */

	const OT_VAbv = 26;
	const OT_VBlw = 27;
	const OT_VPre = 28;
	const OT_VPst = 29;
	const OT_VS = 30; /* Variation selectors */

	// Based on myanmar_category used to make string to find syllables
	// OT_ to string character (using e.g. OT_C from MYANMAR) hb-ot-shape-complex-myanmar-private.hh
	public static $myanmar_category_char = array(
		'x',
		'C',
		'V',
		'N',
		'H',
		'Z',
		'J',
		'x',
		'S',
		'x',
		'A',
		'x',
		'D',
		'x',
		'x',
		'x',
		'R',
		'x',
		'a', /* As Asat */
		'd', /* Digits except zero */
		'o', /* Digit zero */
		'k', /* Medial types */
		'l', /* Medial types */
		'm', /* Medial types */
		'n', /* Medial types */
		'p', /* Pwo and other tones */
		'v', /* Vowel aboVe */
		'b', /* Vowel Below */
		'e', /* Vowel prE */
		't', /* Vowel posT */
		's', /* variation Selector */
	);

	/* Visual positions in a syllable from left to right. */
	/* FROM hb-ot-shape-complex-myanmar-private.hh */

	// myanmar_position
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

	public static function set_myanmar_properties(&$info)
	{
		$u = $info['uni'];
		$type = self::myanmar_get_categories($u);
		$cat = ($type & 0x7F);
		$pos = ($type >> 8);
		/*
		 * Re-assign category
		 * http://www.microsoft.com/typography/OpenTypeDev/myanmar/intro.htm#analyze
		 */
		if (self::in_range($u, 0xFE00, 0xFE0F))
			$cat = self::OT_VS;
		else if ($u == 0x200C)
			$cat = self::OT_ZWNJ;
		else if ($u == 0x200D)
			$cat = self::OT_ZWJ;

		switch ($u) {
			case 0x002D: case 0x00A0: case 0x00D7: case 0x2012:
			case 0x2013: case 0x2014: case 0x2015: case 0x2022:
			case 0x25CC: case 0x25FB: case 0x25FC: case 0x25FD:
			case 0x25FE:
				$cat = self::OT_GB;
				break;

			case 0x1004: case 0x101B: case 0x105A:
				$cat = self::OT_Ra;
				break;

			case 0x1032: case 0x1036:
				$cat = self::OT_A;
				break;

			case 0x103A:
				$cat = self::OT_As;
				break;

			case 0x1041: case 0x1042: case 0x1043: case 0x1044:
			case 0x1045: case 0x1046: case 0x1047: case 0x1048:
			case 0x1049: case 0x1090: case 0x1091: case 0x1092:
			case 0x1093: case 0x1094: case 0x1095: case 0x1096:
			case 0x1097: case 0x1098: case 0x1099:
				$cat = self::OT_D;
				break;

			case 0x1040:
				$cat = self::OT_D; /* XXX The spec says D0, but Uniscribe doesn't seem to do. */
				break;

			case 0x103E: case 0x1060:
				$cat = self::OT_MH;
				break;

			case 0x103C:
				$cat = self::OT_MR;
				break;

			case 0x103D: case 0x1082:
				$cat = self::OT_MW;
				break;

			case 0x103B: case 0x105E: case 0x105F:
				$cat = self::OT_MY;
				break;

			case 0x1063: case 0x1064: case 0x1069: case 0x106A:
			case 0x106B: case 0x106C: case 0x106D: case 0xAA7B:
				$cat = self::OT_PT;
				break;

			case 0x1038: case 0x1087: case 0x1088: case 0x1089:
			case 0x108A: case 0x108B: case 0x108C: case 0x108D:
			case 0x108F: case 0x109A: case 0x109B: case 0x109C:
				$cat = self::OT_SM;
				break;
		}

		if ($cat == self::OT_M) {
			switch ($pos) {
				case self::POS_PRE_C:
					$cat = self::OT_VPre;
					$pos = self::POS_PRE_M;
					break;
				case self::POS_ABOVE_C: $cat = self::OT_VAbv;
					break;
				case self::POS_BELOW_C: $cat = self::OT_VBlw;
					break;
				case self::POS_POST_C: $cat = self::OT_VPst;
					break;
			}
		}
		$info['myanmar_category'] = $cat;
		$info['myanmar_position'] = $pos;
	}

	// syllable_type
	const CONSONANT_SYLLABLE = 0;
	const BROKEN_CLUSTER = 3;
	const NON_MYANMAR_CLUSTER = 4;

	public static function set_syllables(&$o, $s, &$broken_syllables)
	{
		$ptr = 0;
		$syllable_serial = 1;
		$broken_syllables = false;

		while ($ptr < strlen($s)) {
			$match = '';
			$syllable_length = 1;
			$syllable_type = self::NON_MYANMAR_CLUSTER;
			// CONSONANT_SYLLABLE Consonant syllable
			// From OT spec:
			if (preg_match('/^(RaH)?([C|R]|V|d|D)[s]?(H([C|R|V])[s]?)*(H|[a]*[n]?[l]?((m[k]?|k)[a]?)?[e]*[v]*[b]*[A]*(N[a]?)?(t[k]?[a]*[v]*[A]*(N[a]?)?)*(p[A]*(N[a]?)?)*S*[J|Z]?)/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::CONSONANT_SYLLABLE;
			}

			// BROKEN_CLUSTER syllable
			else if (preg_match('/^(RaH)?s?(H|[a]*[n]?[l]?((m[k]?|k)[a]?)?[e]*[v]*[b]*[A]*(N[a]?)?(t[k]?[a]*[v]*[A]*(N[a]?)?)*(p[A]*(N[a]?)?)*S*[J|Z]?)/', substr($s, $ptr), $ma)) {
				if (strlen($ma[0])) { // May match blank
					$syllable_length = strlen($ma[0]);
					$syllable_type = self::BROKEN_CLUSTER;
					$broken_syllables = true;
				}
			}
			for ($i = $ptr; $i < $ptr + $syllable_length; $i++) {
				$o[$i]['syllable'] = ($syllable_serial << 4) | $syllable_type;
			}
			$ptr += $syllable_length;
			$syllable_serial++;
			if ($syllable_serial == 16)
				$syllable_serial = 1;
		}
	}

	public static function reordering(&$info, $GSUBdata, $broken_syllables, $dottedcircle)
	{
		if ($broken_syllables && $dottedcircle) {
			self::insert_dotted_circles($info, $dottedcircle);
		}
		$count = count($info);
		if (!$count)
			return;
		$last = 0;
		$last_syllable = $info[0]['syllable'];
		for ($i = 1; $i < $count; $i++) {
			if ($last_syllable != $info[$i]['syllable']) {
				self::reordering_syllable($info, $GSUBdata, $last, $i);
				$last = $i;
				$last_syllable = $info[$last]['syllable'];
			}
		}
		self::reordering_syllable($info, $GSUBdata, $last, $count);
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
			} else
				$idx++;
		}
		// In case of final bloken cluster...
		$syllable = $info[$idx]['syllable'];
		$syllable_type = ($syllable & 0x0F);
		if ($last_syllable != $syllable && $syllable_type == self::BROKEN_CLUSTER) {
			$dottedcircle[0]['syllable'] = $info[$idx]['syllable'];
			array_splice($info, $idx, 0, $dottedcircle);
		}
	}

	/* Rules from:
	 * https://www.microsoft.com/typography/otfntdev/devanot/shaping.aspx */

	public static function reordering_syllable(&$info, $GSUBdata, $start, $end)
	{
		/* vowel_syllable: We made the vowels look like consonants. So uses the consonant logic! */
		/* broken_cluster: We already inserted dotted-circles, so just call the standalone_cluster. */

		$syllable_type = ($info[$start]['syllable'] & 0x0F);
		if ($syllable_type == self::NON_MYANMAR_CLUSTER) {
			return;
		}
		if ($syllable_type == self::BROKEN_CLUSTER) {
			//if ($uniscribe_bug_compatible) {
			/* For dotted-circle, this is what Uniscribe does:
			 * If dotted-circle is the last glyph, it just does nothing.
			 * i.e. It doesn't form Reph. */
			if ($info[$end - 1]['myanmar_category'] == self::OT_DOTTEDCIRCLE) {
				return;
			}
		}

		$base = $end;
		$has_reph = false;
		$limit = $start;

		if (($start + 3 <= $end) &&
			$info[$start]['myanmar_category'] == self::OT_Ra &&
			$info[$start + 1]['myanmar_category'] == self::OT_As &&
			$info[$start + 2]['myanmar_category'] == self::OT_H) {
			$limit += 3;
			$base = $start;
			$has_reph = true;
		}

		if (!$has_reph)
			$base = $limit;

		for ($i = $limit; $i < $end; $i++) {
			if (self::is_consonant($info[$i])) {
				$base = $i;
				break;
			}
		}


		/* Reorder! */
		$i = $start;
		for (; $i < $start + ($has_reph ? 3 : 0); $i++)
			$info[$i]['myanmar_position'] = self::POS_AFTER_MAIN;
		for (; $i < $base; $i++)
			$info[$i]['myanmar_position'] = self::POS_PRE_C;
		if ($i < $end) {
			$info[$i]['myanmar_position'] = self::POS_BASE_C;
			$i++;
		}
		$pos = self::POS_AFTER_MAIN;
		/* The following loop may be ugly, but it implements all of
		 * Myanmar reordering! */
		for (; $i < $end; $i++) {
			if ($info[$i]['myanmar_category'] == self::OT_MR) /* Pre-base reordering */ {
				$info[$i]['myanmar_position'] = self::POS_PRE_C;
				continue;
			}
			if ($info[$i]['myanmar_position'] < self::POS_BASE_C) /* Left matra */ {
				continue;
			}

			if ($pos == self::POS_AFTER_MAIN && $info[$i]['myanmar_category'] == self::OT_VBlw) {
				$pos = self::POS_BELOW_C;
				$info[$i]['myanmar_position'] = $pos;
				continue;
			}

			if ($pos == self::POS_BELOW_C && $info[$i]['myanmar_category'] == self::OT_A) {
				$info[$i]['myanmar_position'] = self::POS_BEFORE_SUB;
				continue;
			}
			if ($pos == self::POS_BELOW_C && $info[$i]['myanmar_category'] == self::OT_VBlw) {
				$info[$i]['myanmar_position'] = $pos;
				continue;
			}
			if ($pos == self::POS_BELOW_C && $info[$i]['myanmar_category'] != self::OT_A) {
				$pos = self::POS_AFTER_SUB;
				$info[$i]['myanmar_position'] = $pos;
				continue;
			}
			$info[$i]['myanmar_position'] = $pos;
		}


		/* Sit tight, rock 'n roll! */
		self::bubble_sort($info, $start, $end - $start);
	}

	public static function is_one_of($info, $flags)
	{
		if (isset($info['is_ligature']) && $info['is_ligature'])
			return false; /* If it ligated, all bets are off. */
		return !!(self::FLAG($info['myanmar_category']) & $flags);
	}

	/* Vowels and placeholders treated as if they were consonants. */

	public static function is_consonant($info)
	{
		return self::is_one_of($info, (self::FLAG(self::OT_C) | self::FLAG(self::OT_CM) | self::FLAG(self::OT_Ra) | self::FLAG(self::OT_V) | self::FLAG(self::OT_NBSP) | self::FLAG(self::OT_GB)));
	}

// From hb-private.hh
	public static function in_range($u, $lo, $hi)
	{
		if ((($lo ^ $hi) & $lo) == 0 && (($lo ^ $hi) & $hi) == ($lo ^ $hi) && (($lo ^ $hi) & (($lo ^ $hi) + 1)) == 0)
			return ($u & ~($lo ^ $hi)) == $lo;
		else
			return $lo <= $u && $u <= $hi;
	}

// From hb-private.hh
	public static function FLAG($x)
	{
		return (1 << ($x));
	}

	public static function FLAG_RANGE($x, $y)
	{
		self::FLAG(y + 1) - self::FLAG(x);
	}

// BELOW from hb-ot-shape-complex-indic.cc
// see INDIC for details

	public static $myanmar_table = array(
		/* Myanmar  (1000..109F) */

		/* 1000 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1008 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1010 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1018 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1020 */ 3841, 3842, 3842, 3842, 3842, 3842, 3842, 3842,
		/* 1028 */ 3842, 3842, 3842, 2823, 2823, 1543, 1543, 2055,
		/* 1030 */ 2055, 775, 1543, 1543, 1543, 1543, 3848, 3843,
		/* 1038 */ 3848, 3844, 1540, 3857, 3857, 3857, 3857, 3841,
		/* 1040 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1048 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1050 */ 3841, 3841, 3842, 3842, 3842, 3842, 2823, 2823,
		/* 1058 */ 2055, 2055, 3841, 3841, 3841, 3841, 3857, 3857,
		/* 1060 */ 3857, 3841, 2823, 3843, 3843, 3841, 3841, 2823,
		/* 1068 */ 2823, 3843, 3843, 3843, 3843, 3843, 3841, 3841,
		/* 1070 */ 3841, 1543, 1543, 1543, 1543, 3841, 3841, 3841,
		/* 1078 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1080 */ 3841, 3841, 3857, 2823, 775, 1543, 1543, 3843,
		/* 1088 */ 3843, 3843, 3843, 3843, 3843, 3843, 3841, 3843,
		/* 1090 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1098 */ 3840, 3840, 3843, 3843, 2823, 1543, 3840, 3840,
		/* Myanmar Extended-A  (AA60..AA7F) */

		/* AA60 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* AA68 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* AA70 */ 3840, 3841, 3841, 3841, 3840, 3840, 3840, 3840,
		/* AA78 */ 3840, 3840, 3841, 3843, 3840, 3840, 3840, 3840,
	);

// from "hb-ot-shape-complex-indic-table.cc"
	public static function myanmar_get_categories($u)
	{
		if (0x1000 <= $u && $u <= 0x109F)
			return self::$myanmar_table[$u - 0x1000 + 0]; // offset 0 for Most "myanmar"
		if (0xAA60 <= $u && $u <= 0xAA7F)
			return self::$myanmar_table[$u - 0xAA60 + 160]; // offset for extensions
		if ($u == 0x00A0)
			return 3851; // (ISC_CP | (IMC_x << 8))
		if ($u == 0x25CC)
			return 3851; // (ISC_CP | (IMC_x << 8))
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
				if ($arr[$j]['myanmar_position'] > $arr[$j + 1]['myanmar_position']) {
					$t = $arr[$j];
					$arr[$j] = $arr[$j + 1];
					$arr[$j + 1] = $t;
				}
			}
			$k--;
		}
	}

}
