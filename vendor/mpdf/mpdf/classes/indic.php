<?php

require_once __DIR__ . '/../MpdfException.php';

class INDIC
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
	const OT_M = 7; /* Matra or Dependent Vowel */
	const OT_SM = 8;
	const OT_VD = 9;
	const OT_A = 10;
	const OT_NBSP = 11;
	const OT_DOTTEDCIRCLE = 12; /* Not in the spec, but special in Uniscribe. /Very very/ special! */
	const OT_RS = 13; /* Register Shifter, used in Khmer OT spec */
	const OT_Coeng = 14;
	const OT_Repha = 15;

	const OT_Ra = 16; /* Not explicitly listed in the OT spec, but used in the grammar. */
	const OT_CM = 17;

	// Based on indic_category used to make string to find syllables
	// OT_ to string character (using e.g. OT_C from INDIC) hb-ot-shape-complex-indic-private.hh
	public static $indic_category_char = array(
		'x',
		'C',
		'V',
		'N',
		'H',
		'Z',
		'J',
		'M',
		'S',
		'v',
		'A', /* Spec gives Andutta U+0952 as OT_A. However, testing shows that Uniscribe
		 * treats U+0951..U+0952 all as OT_VD - see set_indic_properties */
		's',
		'D',
		'F', /* Register shift Khmer only */
		'G', /* Khmer only */
		'r', /* 0D4E (dot reph) only one in Malayalam */
		'R',
		'm', /* Consonant medial only used in Indic 0A75 in Gurmukhi  (0A00..0A7F)  : also in Lao, Myanmar, Tai Tham, Javanese & Cham  */
	);

	/* Visual positions in a syllable from left to right. */
	/* FROM hb-ot-shape-complex-indic-private.hh */

	// indic_position
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

	/*
	 * Basic features.
	 * These features are applied in order, one at a time, after initial_reordering.
	 */
	/*
	 * Must be in the same order as the indic_features array. Ones starting with _ are F_GLOBAL
	 * Ones without the _ are only applied where the mask says!
	 */

	const _NUKT = 0;
	const _AKHN = 1;
	const RPHF = 2;
	const _RKRF = 3;
	const PREF = 4;
	const BLWF = 5;
	const HALF = 6;
	const ABVF = 7;
	const PSTF = 8;
	const CFAR = 9; // Khmer only
	const _VATU = 10;
	const _CJCT = 11;
	const INIT = 12;

	public static function set_indic_properties(&$info, $scriptblock)
	{
		$u = $info['uni'];
		$type = self::indic_get_categories($u);
		$cat = ($type & 0x7F);
		$pos = ($type >> 8);

		/*
		 * Re-assign category
		 */

		if ($u == 0x17D1)
			$cat = self::OT_X;

		if ($cat == self::OT_X && self::in_range($u, 0x17CB, 0x17D3)) { /* Khmer Various signs */
			/* These are like Top Matras. */
			$cat = self::OT_M;
			$pos = self::POS_ABOVE_C;
		}

		if ($u == 0x17C6)
			$cat = self::OT_N; /* Khmer Bindu doesn't like to be repositioned. */

		if ($u == 0x17D2)
			$cat = self::OT_Coeng; /* Khmer coeng */

		/* The spec says U+0952 is OT_A.	However, testing shows that Uniscribe
		 * treats U+0951..U+0952 all as OT_VD.
		 * TESTS:
		 * U+092E,U+0947,U+0952
		 * U+092E,U+0952,U+0947
		 * U+092E,U+0947,U+0951
		 * U+092E,U+0951,U+0947
		 * */
		//if ($u == 0x0952) $cat = self::OT_A;
		if (self::in_range($u, 0x0951, 0x0954))
			$cat = self::OT_VD;

		if ($u == 0x200C)
			$cat = self::OT_ZWNJ;
		else if ($u == 0x200D)
			$cat = self::OT_ZWJ;
		else if ($u == 0x25CC)
			$cat = self::OT_DOTTEDCIRCLE;
		else if ($u == 0x0A71)
			$cat = self::OT_SM; /* GURMUKHI ADDAK.	More like consonant medial. like 0A75. */

		if ($cat == self::OT_Repha) {
			/* There are two kinds of characters marked as Repha:
			 * - The ones that are GenCat=Mn are already positioned visually, ie. after base. (eg. Khmer)
			 * - The ones that are GenCat=Lo is encoded logically, ie. beginning of syllable. (eg. Malayalam)
			 *
			 * We recategorize the first kind to look like a Nukta and attached to the base directly.
			 */
			if ($info['general_category'] == UCDN::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK)
				$cat = self::OT_N;
		}

		/*
		 * Re-assign position.
		 */

		if ((self::FLAG($cat) & (self::FLAG(self::OT_C) | self::FLAG(self::OT_CM) | self::FLAG(self::OT_Ra) | self::FLAG(self::OT_V) | self::FLAG(self::OT_NBSP) | self::FLAG(self::OT_DOTTEDCIRCLE)))) { // = CONSONANT_FLAGS like is_consonant
			if ($scriptblock == UCDN::SCRIPT_KHMER)
				$pos = self::POS_BELOW_C; /* Khmer differs from Indic here. */
			else
				$pos = self::POS_BASE_C; /* Will recategorize later based on font lookups. */

			if (self::is_ra($u))
				$cat = self::OT_Ra;
		}
		else if ($cat == self::OT_M) {
			$pos = self::matra_position($u, $pos);
		} else if ($cat == self::OT_SM || $cat == self::OT_VD) {
			$pos = self::POS_SMVD;
		}

		if ($u == 0x0B01)
			$pos = self::POS_BEFORE_SUB; /* Oriya Bindu is BeforeSub in the spec. */

		$info['indic_category'] = $cat;
		$info['indic_position'] = $pos;
	}

	// syllable_type
	const CONSONANT_SYLLABLE = 0;
	const VOWEL_SYLLABLE = 1;
	const STANDALONE_CLUSTER = 2;
	const BROKEN_CLUSTER = 3;
	const NON_INDIC_CLUSTER = 4;

	public static function set_syllables(&$o, $s, &$broken_syllables)
	{
		$ptr = 0;
		$syllable_serial = 1;
		$broken_syllables = false;

		while ($ptr < strlen($s)) {
			$match = '';
			$syllable_length = 1;
			$syllable_type = self::NON_INDIC_CLUSTER;
			// CONSONANT_SYLLABLE Consonant syllable
			// From OT spec:
			if (preg_match('/^([CR]m*[N]?(H[ZJ]?|[ZJ]H))*[CR]m*[N]?[A]?(H[ZJ]?|[M]*[N]?[H]?)?[S]?[v]{0,2}/', substr($s, $ptr), $ma)) {
				// From HarfBuzz:
				//if (preg_match('/^r?([CR]J?(Z?[N]{0,2})?[ZJ]?H(J[N]?)?){0,4}[CR]J?(Z?[N]{0,2})?A?((([ZJ]?H(J[N]?)?)|HZ)|(HJ)?([ZJ]{0,3}M[N]?(H|JHJR)?){0,4})?(S[Z]?)?[v]{0,2}/', substr($s,$ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::CONSONANT_SYLLABLE;
			}
			// VOWEL_SYLLABLE Vowel-based syllable
			// From OT spec:
			else if (preg_match('/^(RH|r)?V[N]?([ZJ]?H[CR]m*|J[CR]m*)?([M]*[N]?[H]?)?[S]?[v]{0,2}/', substr($s, $ptr), $ma)) {
				// From HarfBuzz:
				//else if (preg_match('/^(RH|r)?V(Z?[N]{0,2})?(J|([ZJ]?H(J[N]?)?[CR]J?(Z?[N]{0,2})?){0,4}((([ZJ]?H(J[N]?)?)|HZ)|(HJ)?([ZJ]{0,3}M[N]?(H|JHJR)?){0,4})?(S[Z]?)?[v]{0,2})/', substr($s,$ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::VOWEL_SYLLABLE;
			}

			/* Apply only if it's a word start. */
			// STANDALONE_CLUSTER Stand Alone syllable at start of word
			// From OT spec:
			else if (($ptr == 0 ||
				$o[$ptr - 1]['general_category'] < UCDN::UNICODE_GENERAL_CATEGORY_LOWERCASE_LETTER ||
				$o[$ptr - 1]['general_category'] > UCDN::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK
				) && (preg_match('/^(RH|r)?[sD][N]?([ZJ]?H[CR]m*)?([M]*[N]?[H]?)?[S]?[v]{0,2}/', substr($s, $ptr), $ma))) {
				// From HarfBuzz:
				// && (preg_match('/^(RH|r)?[sD](Z?[N]{0,2})?(([ZJ]?H(J[N]?)?)[CR]J?(Z?[N]{0,2})?){0,4}((([ZJ]?H(J[N]?)?)|HZ)|(HJ)?([ZJ]{0,3}M[N]?(H|JHJR)?){0,4})?(S[Z]?)?[v]{0,2}/', substr($s,$ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::STANDALONE_CLUSTER;
			}

			// BROKEN_CLUSTER syllable
			else if (preg_match('/^(RH|r)?[N]?([ZJ]?H[CR])?([M]*[N]?[H]?)?[S]?[v]{0,2}/', substr($s, $ptr), $ma)) {
				// From HarfBuzz:
				//else if (preg_match('/^(RH|r)?(Z?[N]{0,2})?(([ZJ]?H(J[N]?)?)[CR]J?(Z?[N]{0,2})?){0,4}((([ZJ]?H(J[N]?)?)|HZ)|(HJ)?([ZJ]{0,3}M[N]?(H|JHJR)?){0,4})(S[Z]?)?[v]{0,2}/', substr($s,$ptr), $ma)) {
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

	public static function set_syllables_sinhala(&$o, $s, &$broken_syllables)
	{
		$ptr = 0;
		$syllable_serial = 1;
		$broken_syllables = false;

		while ($ptr < strlen($s)) {
			$match = '';
			$syllable_length = 1;
			$syllable_type = self::NON_INDIC_CLUSTER;
			// CONSONANT_SYLLABLE Consonant syllable
			// From OT spec:
			if (preg_match('/^([CR]HJ|[CR]JH){0,8}[CR][HM]{0,3}[S]{0,1}/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::CONSONANT_SYLLABLE;
			}
			// VOWEL_SYLLABLE Vowel-based syllable
			// From OT spec:
			else if (preg_match('/^V[S]{0,1}/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::VOWEL_SYLLABLE;
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

	public static function set_syllables_khmer(&$o, $s, &$broken_syllables)
	{
		$ptr = 0;
		$syllable_serial = 1;
		$broken_syllables = false;

		while ($ptr < strlen($s)) {
			$match = '';
			$syllable_length = 1;
			$syllable_type = self::NON_INDIC_CLUSTER;
			// CONSONANT_SYLLABLE Consonant syllable
			if (preg_match('/^r?([CR]J?((Z?F)?[N]{0,2})?[ZJ]?G(JN?)?){0,4}[CR]J?((Z?F)?[N]{0,2})?A?((([ZJ]?G(JN?)?)|GZ)|(GJ)?([ZJ]{0,3}MN?(H|JHJR)?){0,4})?(G([CR]J?((Z?F)?[N]{0,2})?|V))?(SZ?)?[v]{0,2}/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::CONSONANT_SYLLABLE;
			}
			// VOWEL_SYLLABLE Vowel-based syllable
			else if (preg_match('/^(RH|r)?V((Z?F)?[N]{0,2})?(J|([ZJ]?G(JN?)?[CR]J?((Z?F)?[N]{0,2})?){0,4}((([ZJ]?G(JN?)?)|GZ)|(GJ)?([ZJ]{0,3}MN?(H|JHJR)?){0,4})?(G([CR]J?((Z?F)?[N]{0,2})?|V))?(SZ?)?[v]{0,2})/', substr($s, $ptr), $ma)) {
				$syllable_length = strlen($ma[0]);
				$syllable_type = self::VOWEL_SYLLABLE;
			}


			// BROKEN_CLUSTER syllable
			else if (preg_match('/^(RH|r)?((Z?F)?[N]{0,2})?(([ZJ]?G(JN?)?)[CR]J?((Z?F)?[N]{0,2})?){0,4}((([ZJ]?G(JN?)?)|GZ)|(GJ)?([ZJ]{0,3}MN?(H|JHJR)?){0,4})(G([CR]J?((Z?F)?[N]{0,2})?|V))?(SZ?)?[v]{0,2}/', substr($s, $ptr), $ma)) {
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

	public static function initial_reordering(&$info, $GSUBdata, $broken_syllables, $indic_config, $scriptblock, $is_old_spec, $dottedcircle)
	{

		self::update_consonant_positions($info, $GSUBdata);

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
				self::initial_reordering_syllable($info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec, $last, $i);
				$last = $i;
				$last_syllable = $info[$last]['syllable'];
			}
		}
		self::initial_reordering_syllable($info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec, $last, $count);
	}

	public static function update_consonant_positions(&$info, $GSUBdata)
	{
		$count = count($info);
		for ($i = 0; $i < $count; $i++) {
			if ($info[$i]['indic_position'] == self::POS_BASE_C) {
				$c = $info[$i]['uni'];
				// If would substitute...
				if (isset($GSUBdata['pref'][$c])) {
					$info[$i]['indic_position'] = self::POS_POST_C;
				} else if (isset($GSUBdata['blwf'][$c])) {
					$info[$i]['indic_position'] = self::POS_BELOW_C;
				} else if (isset($GSUBdata['pstf'][$c])) {
					$info[$i]['indic_position'] = self::POS_POST_C;
				}
			}
		}
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

				/* Insert dottedcircle after possible Repha. */
				while ($idx < count($info) && $last_syllable == $info[$idx]['syllable'] && $info[$idx]['indic_category'] == self::OT_Repha)
					$idx++;
				array_splice($info, $idx, 0, $dottedcircle);
			} else {
				$idx++;
			}
		}

		// I am not sue how this code below got in here, since $idx should now be > count($info) and thus invalid.
		// In case I am missing something(!) I'll leave a warning here for now:
		if (isset($info[$idx])) {
			throw new MpdfException('Unexpected error occured in Indic processing');
		}
		// In case of final bloken cluster...
		//$syllable = $info[$idx]['syllable'];
		//$syllable_type = ($syllable & 0x0F);
		//if ($last_syllable != $syllable && $syllable_type == self::BROKEN_CLUSTER) {
		//	$dottedcircle[0]['syllable'] = $info[$idx]['syllable'];
		//	array_splice($info, $idx, 0, $dottedcircle);
		//}
	}

	/* Rules from:
	 * https://www.microsoft.com/typography/otfntdev/devanot/shaping.aspx */

	public static function initial_reordering_syllable(&$info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec, $start, $end)
	{
		/* vowel_syllable: We made the vowels look like consonants. So uses the consonant logic! */
		/* broken_cluster: We already inserted dotted-circles, so just call the standalone_cluster. */
		/* standalone_cluster: We treat NBSP/dotted-circle as if they are consonants, so we should just chain. */

		$syllable_type = ($info[$start]['syllable'] & 0x0F);
		if ($syllable_type == self::NON_INDIC_CLUSTER) {
			return;
		}
		if ($syllable_type == self::BROKEN_CLUSTER || $syllable_type == self::STANDALONE_CLUSTER) {
			//if ($uniscribe_bug_compatible) {
			/* For dotted-circle, this is what Uniscribe does:
			 * If dotted-circle is the last glyph, it just does nothing.
			 * i.e. It doesn't form Reph. */
			if ($info[$end - 1]['indic_category'] == self::OT_DOTTEDCIRCLE) {
				return;
			}
		}

		/* 1. Find base consonant:
		 *
		 * The shaping engine finds the base consonant of the syllable, using the
		 * following algorithm: starting from the end of the syllable, move backwards
		 * until a consonant is found that does not have a below-base or post-base
		 * form (post-base forms have to follow below-base forms), or that is not a
		 * pre-base reordering Ra, or arrive at the first consonant. The consonant
		 * stopped at will be the base.
		 *
		 * 	o If the syllable starts with Ra + Halant (in a script that has Reph)
		 * 	and has more than one consonant, Ra is excluded from candidates for
		 * 	base consonants.
		 */

		$base = $end;
		$has_reph = false;
		$limit = $start;

		if ($scriptblock != UCDN::SCRIPT_KHMER) {
			/* -> If the syllable starts with Ra + Halant (in a script that has Reph)
			 * 	and has more than one consonant, Ra is excluded from candidates for
			 * 	base consonants. */
			if (count($GSUBdata['rphf']) /* ?? $indic_plan->mask_array[RPHF] */ && $start + 3 <= $end &&
				(
				($indic_config[4] == self::REPH_MODE_IMPLICIT && !self::is_joiner($info[$start + 2])) ||
				($indic_config[4] == self::REPH_MODE_EXPLICIT && $info[$start + 2]['indic_category'] == self::OT_ZWJ)
				)) {
				/* See if it matches the 'rphf' feature. */
				//$glyphs = array($info[$start]['uni'], $info[$start + 1]['uni']);
				//if ($indic_plan->rphf->would_substitute ($glyphs, count($glyphs), true, face)) {
				if (isset($GSUBdata['rphf'][$info[$start]['uni']]) && self::is_halant_or_coeng($info[$start + 1])) {
					$limit += 2;
					while ($limit < $end && self::is_joiner($info[$limit]))
						$limit++;
					$base = $start;
					$has_reph = true;
				}
			} else if ($indic_config[4] == self::REPH_MODE_LOG_REPHA && $info[$start]['indic_category'] == self::OT_Repha) {
				$limit += 1;
				while ($limit < $end && self::is_joiner($info[$limit]))
					$limit++;
				$base = $start;
				$has_reph = true;
			}
		}

		switch ($indic_config[2]) { // base_pos
			case self::BASE_POS_LAST:
				/* -> starting from the end of the syllable, move backwards */
				$i = $end;
				$seen_below = false;
				do {
					$i--;
					/* -> until a consonant is found */
					if (self::is_consonant($info[$i])) {
						/* -> that does not have a below-base or post-base form
						 * (post-base forms have to follow below-base forms), */
						if ($info[$i]['indic_position'] != self::POS_BELOW_C && ($info[$i]['indic_position'] != self::POS_POST_C || $seen_below)) {
							$base = $i;
							break;
						}
						if ($info[$i]['indic_position'] == self::POS_BELOW_C)
							$seen_below = true;

						/* -> or that is not a pre-base reordering Ra,
						 *
						 * IMPLEMENTATION NOTES:
						 *
						 * Our pre-base reordering Ra's are marked POS_POST_C, so will be skipped
						 * by the logic above already.
						 */

						/* -> or arrive at the first consonant. The consonant stopped at will
						 * be the base. */
						$base = $i;
					}
					else {
						/* A ZWJ after a Halant stops the base search, and requests an explicit
						 * half form.
						 * [A ZWJ before a Halant, requests a subjoined form instead, and hence
						 * search continues. This is particularly important for Bengali
						 * sequence Ra,H,Ya that should form Ya-Phalaa by subjoining Ya] */
						if ($start < $i && $info[$i]['indic_category'] == self::OT_ZWJ && $info[$i - 1]['indic_category'] == self::OT_H) {
							if (!defined("OMIT_INDIC_FIX_1") || OMIT_INDIC_FIX_1 != 1) {
								$base = $i;
							} // INDIC_FIX_1
							break;
						}
						// ZKI8
						if ($start < $i && $info[$i]['indic_category'] == self::OT_ZWNJ) {
							break;
						}
					}
				} while ($i > $limit);
				break;

			case self::BASE_POS_FIRST:
				/* In scripts without half forms (eg. Khmer), the first consonant is always the base. */

				if (!$has_reph)
					$base = $limit;

				/* Find the last base consonant that is not blocked by ZWJ.	If there is
				 * a ZWJ right before a base consonant, that would request a subjoined form. */
				for ($i = $limit; $i < $end; $i++) {
					if (self::is_consonant($info[$i]) && $info[$i]['indic_position'] == self::POS_BASE_C) {
						if ($limit < $i && $info[$i - 1]['indic_category'] == self::OT_ZWJ)
							break;
						else
							$base = $i;
					}
				}

				/* Mark all subsequent consonants as below. */
				for ($i = $base + 1; $i < $end; $i++) {
					if (self::is_consonant($info[$i]) && $info[$i]['indic_position'] == self::POS_BASE_C)
						$info[$i]['indic_position'] = self::POS_BELOW_C;
				}
				break;
			//default:
			//assert (false);
			/* fallthrough */
		}

		/* -> If the syllable starts with Ra + Halant (in a script that has Reph)
		 * 	and has more than one consonant, Ra is excluded from candidates for
		 * 	base consonants.
		 *
		 * 	Only do this for unforced Reph. (ie. not for Ra,H,ZWJ. */
		if ($scriptblock != UCDN::SCRIPT_KHMER) {
			if ($has_reph && $base == $start && $limit - $base <= 2) {
				/* Have no other consonant, so Reph is not formed and Ra becomes base. */
				$has_reph = false;
			}
		}

		/* 2. Decompose and reorder Matras:
		 *
		 * Each matra and any syllable modifier sign in the cluster are moved to the
		 * appropriate position relative to the consonant(s) in the cluster. The
		 * shaping engine decomposes two- or three-part matras into their constituent
		 * parts before any repositioning. Matra characters are classified by which
		 * consonant in a conjunct they have affinity for and are reordered to the
		 * following positions:
		 *
		 * 		o Before first half form in the syllable
		 * 		o After subjoined consonants
		 * 		o After post-form consonant
		 * 		o After main consonant (for above marks)
		 *
		 * IMPLEMENTATION NOTES:
		 *
		 * The normalize() routine has already decomposed matras for us, so we don't
		 * need to worry about that.
		 */


		/* 3.	Reorder marks to canonical order:
		 *
		 * Adjacent nukta and halant or nukta and vedic sign are always repositioned
		 * if necessary, so that the nukta is first.
		 *
		 * IMPLEMENTATION NOTES:
		 *
		 * Use the combining Class from Unicode categories? to bubble_sort.
		 */

		/* Reorder characters */

		for ($i = $start; $i < $base; $i++)
			$info[$i]['indic_position'] = min(self::POS_PRE_C, $info[$i]['indic_position']);

		if ($base < $end)
			$info[$base]['indic_position'] = self::POS_BASE_C;

		/* Mark final consonants. A final consonant is one appearing after a matra,
		 * ? only in Khmer. */
		for ($i = $base + 1; $i < $end; $i++)
			if ($info[$i]['indic_category'] == self::OT_M) {
				for ($j = $i + 1; $j < $end; $j++)
					if (self::is_consonant($info[$j])) {
						$info[$j]['indic_position'] = self::POS_FINAL_C;
						break;
					}
				break;
			}

		/* Handle beginning Ra */
		if ($scriptblock != UCDN::SCRIPT_KHMER) {
			if ($has_reph)
				$info[$start]['indic_position'] = self::POS_RA_TO_BECOME_REPH;
		}


		/* For old-style Indic script tags, move the first post-base Halant after
		 * last consonant.	Only do this if there is *not* a Halant after last
		 * consonant. Otherwise it becomes messy. */
		if ($is_old_spec) {
			for ($i = $base + 1; $i < $end; $i++) {
				if ($info[$i]['indic_category'] == self::OT_H) {
					for ($j = $end - 1; $j > $i; $j--) {
						if (self::is_consonant($info[$j]) || $info[$j]['indic_category'] == self::OT_H) {
							break;
						}
					}
					if ($info[$j]['indic_category'] != self::OT_H && $j > $i) {
						/* Move Halant to after last consonant. */
						self::_move_info_pos($info, $i, $j + 1);
					}
					break;
				}
			}
		}

		/* Attach misc marks to previous char to move with them. */
		$last_pos = self::POS_START;
		for ($i = $start; $i < $end; $i++) {
			if ((self::FLAG($info[$i]['indic_category']) & (self::FLAG(self::OT_ZWJ) | self::FLAG(self::OT_ZWNJ) | self::FLAG(self::OT_N) | self::FLAG(self::OT_RS) | self::FLAG(self::OT_H) | self::FLAG(self::OT_Coeng) ))) {
				$info[$i]['indic_position'] = $last_pos;
				if ($info[$i]['indic_category'] == self::OT_H && $info[$i]['indic_position'] == self::POS_PRE_M) {
					/*
					 * Uniscribe doesn't move the Halant with Left Matra.
					 * TEST: U+092B,U+093F,U+094DE
					 * We follow.	This is important for the Sinhala
					 * U+0DDA split matra since it decomposes to U+0DD9,U+0DCA
					 * where U+0DD9 is a left matra and U+0DCA is the virama.
					 * We don't want to move the virama with the left matra.
					 * TEST: U+0D9A,U+0DDA
					 */
					for ($j = $i; $j > $start; $j--)
						if ($info[$j - 1]['indic_position'] != self::POS_PRE_M) {
							$info[$i]['indic_position'] = $info[$j - 1]['indic_position'];
							break;
						}
				}
			} else if ($info[$i]['indic_position'] != self::POS_SMVD) {
				$last_pos = $info[$i]['indic_position'];
			}
		}

		/* Re-attach ZWJ, ZWNJ, and halant to next char, for after-base consonants. */
		$last_halant = $end;
		for ($i = $base + 1; $i < $end; $i++) {
			if (self::is_halant_or_coeng($info[$i]))
				$last_halant = $i;
			else if (self::is_consonant($info[$i])) {
				for ($j = $last_halant; $j < $i; $j++)
					if ($info[$j]['indic_position'] != self::POS_SMVD)
						$info[$j]['indic_position'] = $info[$i]['indic_position'];
			}
		}


		if ($scriptblock == UCDN::SCRIPT_KHMER) {
			/* KHMER_FIX_2 */
			/* Move Coeng+RO (Halant,Ra) sequence before base consonant. */
			for ($i = $base + 1; $i < $end; $i++) {
				if (self::is_halant_or_coeng($info[$i]) && self::is_ra($info[$i + 1]['uni'])) {
					$info[$i]['indic_position'] = self::POS_PRE_C;
					$info[$i + 1]['indic_position'] = self::POS_PRE_C;
					break;
				}
			}
		}


		/*
		  if (!defined("OMIT_INDIC_FIX_2") || OMIT_INDIC_FIX_2 != 1) {
		  // INDIC_FIX_2
		  $ZWNJ_found = false;
		  $POST_ZWNJ_c_found = false;
		  for ($i = $base + 1; $i < $end; $i++) {
		  if ($info[$i]['indic_category'] == self::OT_ZWNJ) { $ZWNJ_found = true; }
		  else if ($ZWNJ_found && $info[$i]['indic_category'] == self::OT_C) { $POST_ZWNJ_c_found = true; }
		  else if ($POST_ZWNJ_c_found && $info[$i]['indic_position'] == self::POS_BEFORE_SUB) { $info[$i]['indic_position'] = self::POS_AFTER_SUB; }
		  }
		  }
		 */

		/* Setup masks now */
		for ($i = $start; $i < $end; $i++) {
			$info[$i]['mask'] = 0;
		}


		if ($scriptblock == UCDN::SCRIPT_KHMER) {
			/* Find a Coeng+RO (Halant,Ra) sequence and mark it for pre-base processing. */
			$mask = self::FLAG(self::PREF);
			for ($i = $base; $i < $end - 1; $i++) { /* KHMER_FIX_1 From $start (not base) */
				if (self::is_halant_or_coeng($info[$i]) && self::is_ra($info[$i + 1]['uni'])) {

					$info[$i]['mask'] |= self::FLAG(self::PREF);
					$info[$i + 1]['mask'] |= self::FLAG(self::PREF);

					/* Mark the subsequent stuff with 'cfar'.  Used in Khmer.
					 * Read the feature spec.
					 * This allows distinguishing the following cases with MS Khmer fonts:
					 * U+1784,U+17D2,U+179A,U+17D2,U+1782  [C+Coeng+RO+Coeng+C] => Should activate CFAR
					 * U+1784,U+17D2,U+1782,U+17D2,U+179A  [C+Coeng+C+Coeng+RO] => Should NOT activate CFAR
					 */
					for ($j = ($i + 2); $j < $end; $j++)
						$info[$j]['mask'] |= self::FLAG(self::CFAR);

					break;
				}
			}
		}



		/* Sit tight, rock 'n roll! */
		self::bubble_sort($info, $start, $end - $start);

		/* Find base again */
		$base = $end;
		for ($i = $start; $i < $end; $i++) {
			if ($info[$i]['indic_position'] == self::POS_BASE_C) {
				$base = $i;
				break;
			}
		}

		if ($scriptblock != UCDN::SCRIPT_KHMER) {
			/* Reph */
			for ($i = $start; $i < $end; $i++) {
				if ($info[$i]['indic_position'] == self::POS_RA_TO_BECOME_REPH) {
					$info[$i]['mask'] |= self::FLAG(self::RPHF);
				}
			}

			/* Pre-base */
			$mask = self::FLAG(self::HALF);
			for ($i = $start; $i < $base; $i++) {
				$info[$i]['mask'] |= $mask;
			}
		}

		/* Post-base */
		$mask = (self::FLAG(self::BLWF) | self::FLAG(self::ABVF) | self::FLAG(self::PSTF));
		for ($i = $base + 1; $i < $end; $i++) {
			$info[$i]['mask'] |= $mask;
		}


		if ($scriptblock != UCDN::SCRIPT_KHMER) {
			if (!defined("OMIT_INDIC_FIX_3") || OMIT_INDIC_FIX_3 != 1) {
				/* INDIC_FIX_3 */
				/* Find a (pre-base) Consonant, Halant,Ra sequence and mark Halant|Ra for below-base BLWF processing. */
				// TEST CASE &#x995;&#x9cd;&#x9b0;&#x9cd;&#x995; in FreeSans versus Vrinda
				if (($base - $start) >= 3) {
					for ($i = $start; $i < ($base - 2); $i++) {
						if (self::is_consonant($info[$i])) {
							if (self::is_halant_or_coeng($info[$i + 1]) && self::is_ra($info[$i + 2]['uni'])) {
								// If would substitute Halant+Ra...BLWF
								if (isset($GSUBdata['blwf'][$info[$i + 2]['uni']])) {
									$info[$i + 1]['mask'] |= self::FLAG(self::BLWF);
									$info[$i + 2]['mask'] |= self::FLAG(self::BLWF);
								}
								/* If would not substitute as blwf, mark Ra+Halant for RPHF using following Halant (if present) */ else if (self::is_halant_or_coeng($info[$i + 3])) {
									$info[$i + 2]['mask'] |= self::FLAG(self::RPHF);
									$info[$i + 3]['mask'] |= self::FLAG(self::RPHF);
								}
								break;
							}
						}
					}
				}
			}
		}



		if ($is_old_spec && $scriptblock == UCDN::SCRIPT_DEVANAGARI) {
			/* Old-spec eye-lash Ra needs special handling.	From the spec:
			 * "The feature 'below-base form' is applied to consonants
			 * having below-base forms and following the base consonant.
			 * The exception is vattu, which may appear below half forms
			 * as well as below the base glyph. The feature 'below-base
			 * form' will be applied to all such occurrences of Ra as well."
			 *
			 * Test case: U+0924,U+094D,U+0930,U+094d,U+0915
			 * with Sanskrit 2003 font.
			 *
			 * However, note that Ra,Halant,ZWJ is the correct way to
			 * request eyelash form of Ra, so we wouldbn't inhibit it
			 * in that sequence.
			 *
			 * Test case: U+0924,U+094D,U+0930,U+094d,U+200D,U+0915
			 */
			for ($i = $start; ($i + 1) < $base; $i++) {
				if ($info[$i]['indic_category'] == self::OT_Ra && $info[$i + 1]['indic_category'] == self::OT_H &&
					($i + 2 == $base || $info[$i + 2]['indic_category'] != self::OT_ZWJ)) {
					$info[$i]['mask'] |= self::FLAG(self::BLWF);
					$info[$i + 1]['mask'] |= self::FLAG(self::BLWF);
				}
			}
		}

		if ($scriptblock != UCDN::SCRIPT_KHMER) {
			if (count($GSUBdata['pref']) && $base + 2 < $end) {
				/* Find a Halant,Ra sequence and mark it for pre-base processing. */
				for ($i = $base + 1; $i + 1 < $end; $i++) {
					// If old_spec find Ra-Halant...
					if ((isset($GSUBdata['pref'][$info[$i + 1]['uni']]) && self::is_halant_or_coeng($info[$i]) && self::is_ra($info[$i + 1]['uni']) ) ||
						($is_old_spec && isset($GSUBdata['pref'][$info[$i]['uni']]) && self::is_halant_or_coeng($info[$i + 1]) && self::is_ra($info[$i]['uni']) )
					) {
						$info[$i++]['mask'] |= self::FLAG(self::PREF);
						$info[$i++]['mask'] |= self::FLAG(self::PREF);
						break;
					}
				}
			}
		}


		/* Apply ZWJ/ZWNJ effects */
		for ($i = $start + 1; $i < $end; $i++) {
			if (self::is_joiner($info[$i])) {
				$non_joiner = ($info[$i]['indic_category'] == self::OT_ZWNJ);
				$j = $i;
				while ($j > $start) {
					if (defined("OMIT_INDIC_FIX_4") && OMIT_INDIC_FIX_4 == 1) {
						// INDIC_FIX_4 = do nothing - carry on //
						// ZWNJ should block H C from forming blwf post-base - need to unmask backwards beyond first consonant arrived at //
						if (!self::is_consonant($info[$j])) {
							break;
						}
					}
					$j--;

					/* ZWJ/ZWNJ should disable CJCT.	They do that by simply
					 * being there, since we don't skip them for the CJCT
					 * feature (ie. F_MANUAL_ZWJ) */

					/* A ZWNJ disables HALF. */
					if ($non_joiner) {
						$info[$j]['mask'] &= ~(self::FLAG(self::HALF) | self::FLAG(self::BLWF));
					}
				}
			}
		}
	}

	public static function final_reordering(&$info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec)
	{
		$count = count($info);
		if (!$count)
			return;
		$last = 0;
		$last_syllable = $info[0]['syllable'];
		for ($i = 1; $i < $count; $i++) {
			if ($last_syllable != $info[$i]['syllable']) {
				self::final_reordering_syllable($info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec, $last, $i);
				$last = $i;
				$last_syllable = $info[$last]['syllable'];
			}
		}
		self::final_reordering_syllable($info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec, $last, $count);
	}

	public static function final_reordering_syllable(&$info, $GSUBdata, $indic_config, $scriptblock, $is_old_spec, $start, $end)
	{

		/* 4. Final reordering:
		 *
		 * After the localized forms and basic shaping forms GSUB features have been
		 * applied (see below), the shaping engine performs some final glyph
		 * reordering before applying all the remaining font features to the entire
		 * cluster.
		 */

		/* Find base again */
		for ($base = $start; $base < $end; $base++)
			if ($info[$base]['indic_position'] >= self::POS_BASE_C) {
				if ($start < $base && $info[$base]['indic_position'] > self::POS_BASE_C)
					$base--;
				break;
			}
		if ($base == $end && $start < $base && $info[$base - 1]['indic_category'] != self::OT_ZWJ)
			$base--;
		while ($start < $base && isset($info[$base]) && ($info[$base]['indic_category'] == self::OT_H || $info[$base]['indic_category'] == self::OT_N))
			$base--;


		/* 	o Reorder matras:
		 *
		 * 	If a pre-base matra character had been reordered before applying basic
		 * 	features, the glyph can be moved closer to the main consonant based on
		 * 	whether half-forms had been formed. Actual position for the matra is
		 * 	defined as "after last standalone halant glyph, after initial matra
		 * 	position and before the main consonant". If ZWJ or ZWNJ follow this
		 * 	halant, position is moved after it.
		 */


		if ($start + 1 < $end && $start < $base) { /* Otherwise there can't be any pre-base matra characters. */
			/* If we lost track of base, alas, position before last thingy. */
			$new_pos = ($base == $end) ? $base - 2 : $base - 1;

			/* Malayalam / Tamil do not have "half" forms or explicit virama forms.
			 * The glyphs formed by 'half' are Chillus or ligated explicit viramas.
			 * We want to position matra after them.
			 */
			if ($scriptblock != UCDN::SCRIPT_MALAYALAM && $scriptblock != UCDN::SCRIPT_TAMIL) {
				while ($new_pos > $start && !(self::is_one_of($info[$new_pos], (self::FLAG(self::OT_M) | self::FLAG(self::OT_H) | self::FLAG(self::OT_Coeng)))))
					$new_pos--;

				/* If we found no Halant we are done.
				 * Otherwise only proceed if the Halant does
				 * not belong to the Matra itself! */
				if (self::is_halant_or_coeng($info[$new_pos]) && $info[$new_pos]['indic_position'] != self::POS_PRE_M) {
					/* -> If ZWJ or ZWNJ follow this halant, position is moved after it. */
					if ($new_pos + 1 < $end && self::is_joiner($info[$new_pos + 1]))
						$new_pos++;
				} else
					$new_pos = $start; /* No move. */
			}

			if ($start < $new_pos && $info[$new_pos]['indic_position'] != self::POS_PRE_M) {
				/* Now go see if there's actually any matras... */
				for ($i = $new_pos; $i > $start; $i--)
					if ($info[$i - 1]['indic_position'] == self::POS_PRE_M) {
						$old_pos = $i - 1;
						//memmove (&info[$old_pos], &info[$old_pos + 1], ($new_pos - $old_pos) * sizeof ($info[0]));
						self::_move_info_pos($info, $old_pos, $new_pos + 1);

						if ($old_pos < $base && $base <= $new_pos) /* Shouldn't actually happen. */
							$base--;
						$new_pos--;
					}
			}
		}


		/* 	o Reorder reph:
		 *
		 * 	Reph's original position is always at the beginning of the syllable,
		 * 	(i.e. it is not reordered at the character reordering stage). However,
		 * 	it will be reordered according to the basic-forms shaping results.
		 * 	Possible positions for reph, depending on the script, are; after main,
		 * 	before post-base consonant forms, and after post-base consonant forms.
		 */

		/* If there's anything after the Ra that has the REPH pos, it ought to be halant.
		 * Which means that the font has failed to ligate the Reph.	In which case, we
		 * shouldn't move. */
		if ($start + 1 < $end &&
			$info[$start]['indic_position'] == self::POS_RA_TO_BECOME_REPH && $info[$start + 1]['indic_position'] != self::POS_RA_TO_BECOME_REPH) {
			$reph_pos = $indic_config[3];
			$skip_to_reph_step_5 = false;
			$skip_to_reph_move = false;

			/* 	1. If reph should be positioned after post-base consonant forms,
			 * 	proceed to step 5.
			 */
			if ($reph_pos == self::REPH_POS_AFTER_POST) {
				$skip_to_reph_step_5 = true;
			}

			/* 	2. If the reph repositioning class is not after post-base: target
			 * 	position is after the first explicit halant glyph between the
			 * 	first post-reph consonant and last main consonant. If ZWJ or ZWNJ
			 * 	are following this halant, position is moved after it. If such
			 * 	position is found, this is the target position. Otherwise,
			 * 	proceed to the next step.
			 *
			 * 	Note: in old-implementation fonts, where classifications were
			 * 	fixed in shaping engine, there was no case where reph position
			 * 	will be found on this step.
			 */

			if (!$skip_to_reph_step_5) {

				$new_reph_pos = $start + 1;

				while ($new_reph_pos < $base && !self::is_halant_or_coeng($info[$new_reph_pos]))
					$new_reph_pos++;

				if ($new_reph_pos < $base && self::is_halant_or_coeng($info[$new_reph_pos])) {
					/* ->If ZWJ or ZWNJ are following this halant, position is moved after it. */
					if ($new_reph_pos + 1 < $base && self::is_joiner($info[$new_reph_pos + 1]))
						$new_reph_pos++;
					$skip_to_reph_move = true;
				}
			}

			/* 	3. If reph should be repositioned after the main consonant: find the
			 * 	first consonant not ligated with main, or find the first
			 * 	consonant that is not a potential pre-base reordering Ra.
			 */
			if ($reph_pos == self::REPH_POS_AFTER_MAIN && !$skip_to_reph_move && !$skip_to_reph_step_5) {
				$new_reph_pos = $base;
				/* XXX Skip potential pre-base reordering Ra. */
				while ($new_reph_pos + 1 < $end && $info[$new_reph_pos + 1]['indic_position'] <= self::POS_AFTER_MAIN)
					$new_reph_pos++;
				if ($new_reph_pos < $end)
					$skip_to_reph_move = true;
			}

			/* 	4. If reph should be positioned before post-base consonant, find
			 * 	first post-base classified consonant not ligated with main. If no
			 * 	consonant is found, the target position should be before the
			 * 	first matra, syllable modifier sign or vedic sign.
			 */
			/* This is our take on what step 4 is trying to say (and failing, BADLY). */
			if ($reph_pos == self::REPH_POS_AFTER_SUB && !$skip_to_reph_move && !$skip_to_reph_step_5) {
				$new_reph_pos = $base;
				while ($new_reph_pos < $end && isset($info[$new_reph_pos + 1]['indic_position']) &&
				!( self::FLAG($info[$new_reph_pos + 1]['indic_position']) & (self::FLAG(self::POS_POST_C) | self::FLAG(self::POS_AFTER_POST) | self::FLAG(self::POS_SMVD)))) {
					$new_reph_pos++;
				}
				if ($new_reph_pos < $end) {
					$skip_to_reph_move = true;
				}
			}

			/* 	5. If no consonant is found in steps 3 or 4, move reph to a position
			 * 		immediately before the first post-base matra, syllable modifier
			 * 		sign or vedic sign that has a reordering class after the intended
			 * 		reph position. For example, if the reordering position for reph
			 * 		is post-main, it will skip above-base matras that also have a
			 * 		post-main position.
			 */
			if (!$skip_to_reph_move) {
				/* Copied from step 2. */
				$new_reph_pos = $start + 1;
				while ($new_reph_pos < $base && !self::is_halant_or_coeng($info[$new_reph_pos]))
					$new_reph_pos++;

				if ($new_reph_pos < $base && self::is_halant_or_coeng($info[$new_reph_pos])) {
					/* ->If ZWJ or ZWNJ are following this halant, position is moved after it. */
					if ($new_reph_pos + 1 < $base && self::is_joiner($info[$new_reph_pos + 1]))
						$new_reph_pos++;
					$skip_to_reph_move = true;
				}
			}


			/* 	6. Otherwise, reorder reph to the end of the syllable.
			 */
			if (!$skip_to_reph_move) {
				$new_reph_pos = $end - 1;
				while ($new_reph_pos > $start && $info[$new_reph_pos]['indic_position'] == self::POS_SMVD)
					$new_reph_pos--;

				/*
				 * If the Reph is to be ending up after a Matra,Halant sequence,
				 * position it before that Halant so it can interact with the Matra.
				 * However, if it's a plain Consonant,Halant we shouldn't do that.
				 * Uniscribe doesn't do this.
				 * TEST: U+0930,U+094D,U+0915,U+094B,U+094D
				 */
				//if (!$hb_options.uniscribe_bug_compatible && self::is_halant_or_coeng($info[$new_reph_pos])) {
				if (self::is_halant_or_coeng($info[$new_reph_pos])) {
					for ($i = $base + 1; $i < $new_reph_pos; $i++)
						if ($info[$i]['indic_category'] == self::OT_M) {
							/* Ok, got it. */
							$new_reph_pos--;
						}
				}
			}


			/* Move */
			self::_move_info_pos($info, $start, $new_reph_pos + 1);

			if ($start < $base && $base <= $new_reph_pos) {
				$base--;
			}
		}


		/* 	o Reorder pre-base reordering consonants:
		 *
		 * 	If a pre-base reordering consonant is found, reorder it according to
		 * 	the following rules:
		 */


		if (count($GSUBdata['pref']) && $base + 1 < $end) { /* Otherwise there can't be any pre-base reordering Ra. */
			for ($i = $base + 1; $i < $end; $i++) {
				if ($info[$i]['mask'] & self::FLAG(self::PREF)) {
					/* 	1. Only reorder a glyph produced by substitution during application
					 * 	of the <pref> feature. (Note that a font may shape a Ra consonant with
					 * 	the feature generally but block it in certain contexts.)
					 */
// ??? Need to TEST if actual substitution has occurred
					if ($i + 1 == $end || ($info[$i + 1]['mask'] & self::FLAG(self::PREF)) == 0) {
						/*
						 * 	2. Try to find a target position the same way as for pre-base matra.
						 * 	If it is found, reorder pre-base consonant glyph.
						 *
						 * 	3. If position is not found, reorder immediately before main
						 * 	consonant.
						 */
						$new_pos = $base;
						/* Malayalam / Tamil do not have "half" forms or explicit virama forms.
						 * The glyphs formed by 'half' are Chillus or ligated explicit viramas.
						 * We want to position matra after them.
						 */
						if ($scriptblock != UCDN::SCRIPT_MALAYALAM && $scriptblock != UCDN::SCRIPT_TAMIL) {
							while ($new_pos > $start &&
							!(self::is_one_of($info[$new_pos - 1], self::FLAG(self::OT_M) | self::FLAG(self::OT_H) | self::FLAG(self::OT_Coeng))))
								$new_pos--;

							/* In Khmer coeng model, a V,Ra can go *after* matras. If it goes after a
							 * split matra, it should be reordered to *before* the left part of such matra. */
							if ($new_pos > $start && $info[$new_pos - 1]['indic_category'] == self::OT_M) {
								$old_pos = i;
								for ($i = $base + 1; $i < $old_pos; $i++)
									if ($info[$i]['indic_category'] == self::OT_M) {
										$new_pos--;
										break;
									}
							}
						}

						if ($new_pos > $start && self::is_halant_or_coeng($info[$new_pos - 1])) {
							/* -> If ZWJ or ZWNJ follow this halant, position is moved after it. */
							if ($new_pos < $end && self::is_joiner($info[$new_pos]))
								$new_pos++;
						}

						$old_pos = $i;
						self::_move_info_pos($info, $old_pos, $new_pos);

						if ($new_pos <= $base && $base < $old_pos)
							$base++;
					}

					break;
				}
			}
		}


		/* Apply 'init' to the Left Matra if it's a word start. */
		if ($info[$start]['indic_position'] == self::POS_PRE_M &&
			($start == 0 ||
			($info[$start - 1]['general_category'] < UCDN::UNICODE_GENERAL_CATEGORY_FORMAT || $info[$start - 1]['general_category'] > UCDN::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK)
			)) {
			$info[$start]['mask'] |= self::FLAG(self::INIT);
		}


		/*
		 * Finish off and go home!
		 */
	}

	public static function _move_info_pos(&$info, $from, $to)
	{
		$t = array();
		$t[0] = $info[$from];
		if ($from > $to) {
			array_splice($info, $from, 1);
			array_splice($info, $to, 0, $t);
		} else {
			array_splice($info, $to, 0, $t);
			array_splice($info, $from, 1);
		}
	}

	public static $ra_chars = array(
		0x0930 => 1, /* Devanagari */
		0x09B0 => 1, /* Bengali */
		0x09F0 => 1, /* Bengali (Assamese) */
		0x0A30 => 1, /* Gurmukhi */ /* No Reph */
		0x0AB0 => 1, /* Gujarati */
		0x0B30 => 1, /* Oriya */
		0x0BB0 => 1, /* Tamil */ /* No Reph */
		0x0C30 => 1, /* Telugu */ /* Reph formed only with ZWJ */
		0x0CB0 => 1, /* Kannada */
		0x0D30 => 1, /* Malayalam */ /* No Reph, Logical Repha */
		0x0DBB => 1, /* Sinhala */ /* Reph formed only with ZWJ */
		0x179A => 1, /* Khmer */ /* No Reph, Visual Repha */
	);

	public static function is_ra($u)
	{
		if (isset(self::$ra_chars[$u]))
			return true;
		return false;
	}

	public static function is_one_of($info, $flags)
	{
		if (isset($info['is_ligature']) && $info['is_ligature'])
			return false; /* If it ligated, all bets are off. */
		return !!(self::FLAG($info['indic_category']) & $flags);
	}

	public static function is_joiner($info)
	{
		return self::is_one_of($info, (self::FLAG(self::OT_ZWJ) | self::FLAG(self::OT_ZWNJ)));
	}

	/* Vowels and placeholders treated as if they were consonants. */

	public static function is_consonant($info)
	{
		return self::is_one_of($info, (self::FLAG(self::OT_C) | self::FLAG(self::OT_CM) | self::FLAG(self::OT_Ra) | self::FLAG(self::OT_V) | self::FLAG(self::OT_NBSP) | self::FLAG(self::OT_DOTTEDCIRCLE)));
	}

	public static function is_halant_or_coeng($info)
	{
		return self::is_one_of($info, (self::FLAG(self::OT_H) | self::FLAG(self::OT_Coeng)));
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

	// BELOW from hb-ot-shape-complex-indic.cc

	/*
	 * Indic configurations.
	 */

	// base_position
	const BASE_POS_FIRST = 0;
	const BASE_POS_LAST = 1;

	// reph_position
	const REPH_POS_DEFAULT = 10; // POS_BEFORE_POST,

	const REPH_POS_AFTER_MAIN = 5; // POS_AFTER_MAIN,

	const REPH_POS_BEFORE_SUB = 7; // POS_BEFORE_SUB,
	const REPH_POS_AFTER_SUB = 9; // POS_AFTER_SUB,
	const REPH_POS_BEFORE_POST = 10; // POS_BEFORE_POST,
	const REPH_POS_AFTER_POST = 12; // POS_AFTER_POST

	// reph_mode
	const REPH_MODE_IMPLICIT = 0;  /* Reph formed out of initial Ra,H sequence. */
	const REPH_MODE_EXPLICIT = 1;  /* Reph formed out of initial Ra,H,ZWJ sequence. */
	const REPH_MODE_VIS_REPHA = 2; /* Encoded Repha character, no reordering needed. */
	const REPH_MODE_LOG_REPHA = 3; /* Encoded Repha character, needs reordering. */

	/*
	  struct of indic_configs{
	  KEY - script;
	  0 - has_old_spec;
	  1 - virama;
	  2 - base_pos;
	  3 - reph_pos;
	  4 - reph_mode;
	  };
	 */

	public static $indic_configs = array(/* index is SCRIPT_number from UCDN */
		9 => array(true, 0x094D, 1, 10, 0),
		10 => array(true, 0x09CD, 1, 9, 0),
		11 => array(true, 0x0A4D, 1, 7, 0),
		12 => array(true, 0x0ACD, 1, 10, 0),
		13 => array(true, 0x0B4D, 1, 5, 0),
		14 => array(true, 0x0BCD, 1, 12, 0),
		15 => array(true, 0x0C4D, 1, 12, 1),
		16 => array(true, 0x0CCD, 1, 12, 0),
		17 => array(true, 0x0D4D, 1, 5, 3),
		18 => array(false, 0x0DCA, 0, 5, 1), /* Sinhala */
		30 => array(false, 0x17D2, 0, 10, 2), /* Khmer */
		84 => array(false, 0xA9C0, 1, 10, 0), /* Javanese */
	);



	/*

	  // from "hb-ot-shape-complex-indic-table.cc"


	  const ISC_A	 = 0; //	INDIC_SYLLABIC_CATEGORY_AVAGRAHA		Avagraha
	  const ISC_Bi = 8; //	INDIC_SYLLABIC_CATEGORY_BINDU			Bindu
	  const ISC_C	 = 1; //	INDIC_SYLLABIC_CATEGORY_CONSONANT		Consonant
	  const ISC_CD = 1; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_DEAD		Consonant_Dead
	  const ISC_CF = 17; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_FINAL		Consonant_Final
	  const ISC_CHL = 1; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_HEAD_LETTER	Consonant_Head_Letter
	  const ISC_CM = 17; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_MEDIAL		Consonant_Medial
	  const ISC_CP = 11; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_PLACEHOLDER	Consonant_Placeholder
	  const ISC_CR = 15; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_REPHA		Consonant_Repha
	  const ISC_CS = 1; //	INDIC_SYLLABIC_CATEGORY_CONSONANT_SUBJOINED	Consonant_Subjoined
	  const ISC_ML = 0; //	INDIC_SYLLABIC_CATEGORY_MODIFYING_LETTER	Modifying_Letter
	  const ISC_N	 = 3; //	INDIC_SYLLABIC_CATEGORY_NUKTA			Nukta
	  const ISC_x	 = 0; //	INDIC_SYLLABIC_CATEGORY_OTHER			Other
	  const ISC_RS = 13; //	INDIC_SYLLABIC_CATEGORY_REGISTER_SHIFTER	Register_Shifter
	  const ISC_TL = 0; //	INDIC_SYLLABIC_CATEGORY_TONE_LETTER		Tone_Letter
	  const ISC_TM = 3; //	INDIC_SYLLABIC_CATEGORY_TONE_MARK		Tone_Mark
	  const ISC_V	 = 4; //	INDIC_SYLLABIC_CATEGORY_VIRAMA		Virama
	  const ISC_Vs = 8; //	INDIC_SYLLABIC_CATEGORY_VISARGA		Visarga
	  const ISC_Vo = 2; //	INDIC_SYLLABIC_CATEGORY_VOWEL			Vowel
	  const ISC_M	 = 7; //	INDIC_SYLLABIC_CATEGORY_VOWEL_DEPENDENT	Vowel_Dependent
	  const ISC_VI = 2; //	INDIC_SYLLABIC_CATEGORY_VOWEL_INDEPENDENT	Vowel_Independent

	  const IMC_B	 = 8; //	INDIC_MATRA_CATEGORY_BOTTOM			Bottom
	  const IMC_BR = 11; //	INDIC_MATRA_CATEGORY_BOTTOM_AND_RIGHT	Bottom_And_Right
	  const IMC_I	 = 15; //	INDIC_MATRA_CATEGORY_INVISIBLE		Invisible
	  const IMC_L	 = 3; //	INDIC_MATRA_CATEGORY_LEFT			Left
	  const IMC_LR = 11; //	INDIC_MATRA_CATEGORY_LEFT_AND_RIGHT		Left_And_Right
	  const IMC_x	 = 15; //	INDIC_MATRA_CATEGORY_NOT_APPLICABLE		Not_Applicable
	  const IMC_O	 = 5; //	INDIC_MATRA_CATEGORY_OVERSTRUCK		Overstruck
	  const IMC_R	 = 11; //	INDIC_MATRA_CATEGORY_RIGHT			Right
	  const IMC_T	 = 6; //	INDIC_MATRA_CATEGORY_TOP			Top
	  const IMC_TB = 8; //	INDIC_MATRA_CATEGORY_TOP_AND_BOTTOM		Top_And_Bottom
	  const IMC_TBR = 11; //	INDIC_MATRA_CATEGORY_TOP_AND_BOTTOM_AND_RIGHT	Top_And_Bottom_And_Right
	  const IMC_TL = 6; //	INDIC_MATRA_CATEGORY_TOP_AND_LEFT		Top_And_Left
	  const IMC_TLR = 11; //	INDIC_MATRA_CATEGORY_TOP_AND_LEFT_AND_RIGHT	Top_And_Left_And_Right
	  const IMC_TR = 11; //	INDIC_MATRA_CATEGORY_TOP_AND_RIGHT		Top_And_Right
	  const IMC_VOL = 2; //	INDIC_MATRA_CATEGORY_VISUAL_ORDER_LEFT		Visual_Order_Left

	  If in original table = _(C,x), that = ISC_C,IMC_x
	  Value is IMC_x << 8 (or IMC_x * 256) = 3840
	  plus ISC_C = 1, so = 3841

	 */

	public static $indic_table = array(
		/* Devanagari  (0900..097F) */

		/* 0900 */ 3848, 3848, 3848, 3848, 3842, 3842, 3842, 3842,
		/* 0908 */ 3842, 3842, 3842, 3842, 3842, 3842, 3842, 3842,
		/* 0910 */ 3842, 3842, 3842, 3842, 3842, 3841, 3841, 3841,
		/* 0918 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0920 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0928 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0930 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0938 */ 3841, 3841, 1543, 2823, 3843, 3840, 2823, 775,
		/* 0940 */ 2823, 2055, 2055, 2055, 2055, 1543, 1543, 1543,
		/* 0948 */ 1543, 2823, 2823, 2823, 2823, 2052, 775, 2823,
		/* 0950 */ 3840, 3840, 3840, 3840, 3840, 1543, 2055, 2055,
		/* 0958 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0960 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 0968 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0970 */ 3840, 3840, 3842, 3842, 3842, 3842, 3842, 3842,
		/* 0978 */ 3840, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* Bengali  (0980..09FF) */

		/* 0980 */ 3840, 3848, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0988 */ 3842, 3842, 3842, 3842, 3842, 3840, 3840, 3842,
		/* 0990 */ 3842, 3840, 3840, 3842, 3842, 3841, 3841, 3841,
		/* 0998 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 09A0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 09A8 */ 3841, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 09B0 */ 3841, 3840, 3841, 3840, 3840, 3840, 3841, 3841,
		/* 09B8 */ 3841, 3841, 3840, 3840, 3843, 3840, 2823, 775,
		/* 09C0 */ 2823, 2055, 2055, 2055, 2055, 3840, 3840, 775,
		/* 09C8 */ 775, 3840, 3840, 2823, 2823, 2052, 3841, 3840,
		/* 09D0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 2823,
		/* 09D8 */ 3840, 3840, 3840, 3840, 3841, 3841, 3840, 3841,
		/* 09E0 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 09E8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 09F0 */ 3841, 3841, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 09F8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Gurmukhi  (0A00..0A7F) */

		/* 0A00 */ 3840, 3848, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0A08 */ 3842, 3842, 3842, 3840, 3840, 3840, 3840, 3842,
		/* 0A10 */ 3842, 3840, 3840, 3842, 3842, 3841, 3841, 3841,
		/* 0A18 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0A20 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0A28 */ 3841, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0A30 */ 3841, 3840, 3841, 3841, 3840, 3841, 3841, 3840,
		/* 0A38 */ 3841, 3841, 3840, 3840, 3843, 3840, 2823, 775,
		/* 0A40 */ 2823, 2055, 2055, 3840, 3840, 3840, 3840, 1543,
		/* 0A48 */ 1543, 3840, 3840, 1543, 1543, 2052, 3840, 3840,
		/* 0A50 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0A58 */ 3840, 3841, 3841, 3841, 3841, 3840, 3841, 3840,
		/* 0A60 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0A68 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0A70 */ 3848, 3840, 13841, 13841, 3840, 3857, 3840, 3840,
		/* 0A78 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Gujarati  (0A80..0AFF) */

		/* 0A80 */ 3840, 3848, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0A88 */ 3842, 3842, 3842, 3842, 3842, 3842, 3840, 3842,
		/* 0A90 */ 3842, 3842, 3840, 3842, 3842, 3841, 3841, 3841,
		/* 0A98 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0AA0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0AA8 */ 3841, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0AB0 */ 3841, 3840, 3841, 3841, 3840, 3841, 3841, 3841,
		/* 0AB8 */ 3841, 3841, 3840, 3840, 3843, 3840, 2823, 775,
		/* 0AC0 */ 2823, 2055, 2055, 2055, 2055, 1543, 3840, 1543,
		/* 0AC8 */ 1543, 2823, 3840, 2823, 2823, 2052, 3840, 3840,
		/* 0AD0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0AD8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0AE0 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 0AE8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0AF0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0AF8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Oriya  (0B00..0B7F) */

		/* 0B00 */ 3840, 3848, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0B08 */ 3842, 3842, 3842, 3842, 3842, 3840, 3840, 3842,
		/* 0B10 */ 3842, 3840, 3840, 3842, 3842, 3841, 3841, 3841,
		/* 0B18 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0B20 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0B28 */ 3841, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0B30 */ 3841, 3840, 3841, 3841, 3840, 3841, 3841, 3841,
		/* 0B38 */ 3841, 3841, 3840, 3840, 3843, 3840, 2823, 1543,
		/* 0B40 */ 2823, 2055, 2055, 2055, 2055, 3840, 3840, 775,
		/* 0B48 */ 1543, 3840, 3840, 2823, 2823, 2052, 3840, 3840,
		/* 0B50 */ 3840, 3840, 3840, 3840, 3840, 3840, 1543, 2823,
		/* 0B58 */ 3840, 3840, 3840, 3840, 3841, 3841, 3840, 3841,
		/* 0B60 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 0B68 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0B70 */ 3840, 3841, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0B78 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Tamil  (0B80..0BFF) */

		/* 0B80 */ 3840, 3840, 3848, 3840, 3840, 3842, 3842, 3842,
		/* 0B88 */ 3842, 3842, 3842, 3840, 3840, 3840, 3842, 3842,
		/* 0B90 */ 3842, 3840, 3842, 3842, 3842, 3841, 3840, 3840,
		/* 0B98 */ 3840, 3841, 3841, 3840, 3841, 3840, 3841, 3841,
		/* 0BA0 */ 3840, 3840, 3840, 3841, 3841, 3840, 3840, 3840,
		/* 0BA8 */ 3841, 3841, 3841, 3840, 3840, 3840, 3841, 3841,
		/* 0BB0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0BB8 */ 3841, 3841, 3840, 3840, 3840, 3840, 2823, 2823,
		/* 0BC0 */ 1543, 2055, 2055, 3840, 3840, 3840, 775, 775,
		/* 0BC8 */ 775, 3840, 2823, 2823, 2823, 1540, 3840, 3840,
		/* 0BD0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 2823,
		/* 0BD8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0BE0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0BE8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0BF0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0BF8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Telugu  (0C00..0C7F) */

		/* 0C00 */ 3840, 3848, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0C08 */ 3842, 3842, 3842, 3842, 3842, 3840, 3842, 3842,
		/* 0C10 */ 3842, 3840, 3842, 3842, 3842, 3841, 3841, 3841,
		/* 0C18 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0C20 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0C28 */ 3841, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0C30 */ 3841, 3841, 3841, 3841, 3840, 3841, 3841, 3841,
		/* 0C38 */ 3841, 3841, 3840, 3840, 3840, 3840, 1543, 1543,
		/* 0C40 */ 1543, 2823, 2823, 2823, 2823, 3840, 1543, 1543,
		/* 0C48 */ 2055, 3840, 1543, 1543, 1543, 1540, 3840, 3840,
		/* 0C50 */ 3840, 3840, 3840, 3840, 3840, 1543, 2055, 3840,
		/* 0C58 */ 3841, 3841, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0C60 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 0C68 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0C70 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0C78 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Kannada  (0C80..0CFF) */

		/* 0C80 */ 3840, 3840, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0C88 */ 3842, 3842, 3842, 3842, 3842, 3840, 3842, 3842,
		/* 0C90 */ 3842, 3840, 3842, 3842, 3842, 3841, 3841, 3841,
		/* 0C98 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0CA0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0CA8 */ 3841, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0CB0 */ 3841, 3841, 3841, 3841, 3840, 3841, 3841, 3841,
		/* 0CB8 */ 3841, 3841, 3840, 3840, 3843, 3840, 2823, 1543,
		/* 0CC0 */ 2823, 2823, 2823, 2823, 2823, 3840, 1543, 2823,
		/* 0CC8 */ 2823, 3840, 2823, 2823, 1543, 1540, 3840, 3840,
		/* 0CD0 */ 3840, 3840, 3840, 3840, 3840, 2823, 2823, 3840,
		/* 0CD8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3841, 3840,
		/* 0CE0 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 0CE8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0CF0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0CF8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Malayalam  (0D00..0D7F) */

		/* 0D00 */ 3840, 3840, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0D08 */ 3842, 3842, 3842, 3842, 3842, 3840, 3842, 3842,
		/* 0D10 */ 3842, 3840, 3842, 3842, 3842, 3841, 3841, 3841,
		/* 0D18 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0D20 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0D28 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0D30 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0D38 */ 3841, 3841, 3841, 3840, 3840, 3840, 2823, 2823,
		/* 0D40 */ 2823, 2823, 2823, 2055, 2055, 3840, 775, 775,
		/* 0D48 */ 775, 3840, 2823, 2823, 2823, 1540, 3855, 3840,
		/* 0D50 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 2823,
		/* 0D58 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0D60 */ 3842, 3842, 2055, 2055, 3840, 3840, 3840, 3840,
		/* 0D68 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0D70 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0D78 */ 3840, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* Sinhala  (0D80..0DFF) */

		/* 0D80 */ 3840, 3840, 3848, 3848, 3840, 3842, 3842, 3842,
		/* 0D88 */ 3842, 3842, 3842, 3842, 3842, 3842, 3842, 3842,
		/* 0D90 */ 3842, 3842, 3842, 3842, 3842, 3842, 3842, 3840,
		/* 0D98 */ 3840, 3840, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0DA0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0DA8 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 0DB0 */ 3841, 3841, 3840, 3841, 3841, 3841, 3841, 3841,
		/* 0DB8 */ 3841, 3841, 3841, 3841, 3840, 3841, 3840, 3840,
		/* 0DC0 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3840,
		/* 0DC8 */ 3840, 3840, 1540, 3840, 3840, 3840, 3840, 2823,
		/* 0DD0 */ 2823, 2823, 1543, 1543, 2055, 3840, 2055, 3840,
		/* 0DD8 */ 2823, 775, 1543, 775, 2823, 2823, 2823, 2823,
		/* 0DE0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0DE8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 0DF0 */ 3840, 3840, 2823, 2823, 3840, 3840, 3840, 3840,
		/* 0DF8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* Vedic Extensions  (1CD0..1CFF) */

		/* 1CD0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1CD8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1CE0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1CE8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 1CF0 */ 3840, 3840, 3848, 3848, 3840, 3840, 3840, 3840,
		/* 1CF8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
	);

	public static $khmer_table = array(
		/* Khmer  (1780..17FF) */

		/* 1780 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1788 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1790 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 1798 */ 3841, 3841, 3841, 3841, 3841, 3841, 3841, 3841,
		/* 17A0 */ 3841, 3841, 3841, 3842, 3842, 3842, 3842, 3842,
		/* 17A8 */ 3842, 3842, 3842, 3842, 3842, 3842, 3842, 3842,
		/* 17B0 */ 3842, 3842, 3842, 3842, 3840, 3840, 2823, 1543,
		/* 17B8 */ 1543, 1543, 1543, 2055, 2055, 2055, 1543, 2823,
		/* 17C0 */ 2823, 775, 775, 775, 2823, 2823, 3848, 3848,
		/* 17C8 */ 2823, 3853, 3853, 3840, 3855, 3840, 3840, 3840,
		/* 17D0 */ 3840, 1540, 3844, 3840, 3840, 3840, 3840, 3840,
		/* 17D8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 17E0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 17E8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 17F0 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
		/* 17F8 */ 3840, 3840, 3840, 3840, 3840, 3840, 3840, 3840,
	);

	// from "hb-ot-shape-complex-indic-table.cc"
	public static function indic_get_categories($u)
	{
		if (0x0900 <= $u && $u <= 0x0DFF)
			return self::$indic_table[$u - 0x0900 + 0]; // offset 0 for Most "indic"
		if (0x1CD0 <= $u && $u <= 0x1D00)
			return self::$indic_table[$u - 0x1CD0 + 1152]; // offset for Vedic extensions
		if (0x1780 <= $u && $u <= 0x17FF)
			return self::$khmer_table[$u - 0x1780];  // Khmer
		if ($u == 0x00A0)
			return 3851; // (ISC_CP | (IMC_x << 8))
		if ($u == 0x25CC)
			return 3851; // (ISC_CP | (IMC_x << 8))
		return 3840; // (ISC_x | (IMC_x << 8))
	}

	// BELOW from hb-ot-shape-complex-indic.cc
	/*
	 * Indic shaper.
	 */

	public static function IN_HALF_BLOCK($u, $Base)
	{
		return (($u & ~0x7F) == $Base);
	}

	public static function IS_DEVA($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0900);
	}

	public static function IS_BENG($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0980);
	}

	public static function IS_GURU($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0A00);
	}

	public static function IS_GUJR($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0A80);
	}

	public static function IS_ORYA($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0B00);
	}

	public static function IS_TAML($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0B80);
	}

	public static function IS_TELU($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0C00);
	}

	public static function IS_KNDA($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0C80);
	}

	public static function IS_MLYM($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0D00);
	}

	public static function IS_SINH($u)
	{
		return self::IN_HALF_BLOCK($u, 0x0D80);
	}

	public static function IS_KHMR($u)
	{
		return self::IN_HALF_BLOCK($u, 0x1780);
	}

	public static function MATRA_POS_LEFT($u)
	{
		return self::POS_PRE_M;
	}

	public static function MATRA_POS_RIGHT($u)
	{
		return
			(self::IS_DEVA($u) ? self::POS_AFTER_SUB :
				(self::IS_BENG($u) ? self::POS_AFTER_POST :
					(self::IS_GURU($u) ? self::POS_AFTER_POST :
						(self::IS_GUJR($u) ? self::POS_AFTER_POST :
							(self::IS_ORYA($u) ? self::POS_AFTER_POST :
								(self::IS_TAML($u) ? self::POS_AFTER_POST :
									(self::IS_TELU($u) ? ($u <= 0x0C42 ? self::POS_BEFORE_SUB : self::POS_AFTER_SUB) :
										(self::IS_KNDA($u) ? ($u < 0x0CC3 || $u > 0xCD6 ? self::POS_BEFORE_SUB : self::POS_AFTER_SUB) :
											(self::IS_MLYM($u) ? self::POS_AFTER_POST :
												(self::IS_SINH($u) ? self::POS_AFTER_SUB :
													(self::IS_KHMR($u) ? self::POS_AFTER_POST :
														self::POS_AFTER_SUB))))))))))); /* default */
	}

	public static function MATRA_POS_TOP($u)
	{
		return /* BENG and MLYM don't have top matras. */
			(self::IS_DEVA($u) ? self::POS_AFTER_SUB :
				(self::IS_GURU($u) ? self::POS_AFTER_POST : /* Deviate from spec */
					(self::IS_GUJR($u) ? self::POS_AFTER_SUB :
						(self::IS_ORYA($u) ? self::POS_AFTER_MAIN :
							(self::IS_TAML($u) ? self::POS_AFTER_SUB :
								(self::IS_TELU($u) ? self::POS_BEFORE_SUB :
									(self::IS_KNDA($u) ? self::POS_BEFORE_SUB :
										(self::IS_SINH($u) ? self::POS_AFTER_SUB :
											(self::IS_KHMR($u) ? self::POS_AFTER_POST :
												self::POS_AFTER_SUB))))))))); /* default */
	}

	public static function MATRA_POS_BOTTOM($u)
	{
		return
			(self::IS_DEVA($u) ? self::POS_AFTER_SUB :
				(self::IS_BENG($u) ? self::POS_AFTER_SUB :
					(self::IS_GURU($u) ? self::POS_AFTER_POST :
						(self::IS_GUJR($u) ? self::POS_AFTER_POST :
							(self::IS_ORYA($u) ? self::POS_AFTER_SUB :
								(self::IS_TAML($u) ? self::POS_AFTER_POST :
									(self::IS_TELU($u) ? self::POS_BEFORE_SUB :
										(self::IS_KNDA($u) ? self::POS_BEFORE_SUB :
											(self::IS_MLYM($u) ? self::POS_AFTER_POST :
												(self::IS_SINH($u) ? self::POS_AFTER_SUB :
													(self::IS_KHMR($u) ? self::POS_AFTER_POST :
														self::POS_AFTER_SUB))))))))))); /* default */
	}

	public static function matra_position($u, $side)
	{
		switch ($side) {
			case self::POS_PRE_C: return self::MATRA_POS_LEFT($u);
			case self::POS_POST_C: return self::MATRA_POS_RIGHT($u);
			case self::POS_ABOVE_C: return self::MATRA_POS_TOP($u);
			case self::POS_BELOW_C: return self::MATRA_POS_BOTTOM($u);
		}
		return $side;
	}

	// vowel matras that have to be split into two parts.
	// From Harfbuzz (old)
	// New HarfBuzz uses /src/hb-ucdn/ucdn.c and unicodedata_db.h for full method of decomposition for all characters
	// Should always fully decompose and then recompose back, but we will just do the split matras
	public static function decompose_indic($ab)
	{
		$sub = array();
		switch ($ab) {
			/*
			 * Decompose split matras.
			 */
			/* bengali */
			case 0x9cb : $sub[0] = 0x9c7;
				$sub[1] = 0x9be;
				return $sub;
			case 0x9cc : $sub[0] = 0x9c7;
				$sub[1] = 0x9d7;
				return $sub;
			/* oriya */
			case 0xb48 : $sub[0] = 0xb47;
				$sub[1] = 0xb56;
				return $sub;
			case 0xb4b : $sub[0] = 0xb47;
				$sub[1] = 0xb3e;
				return $sub;
			case 0xb4c : $sub[0] = 0xb47;
				$sub[1] = 0xb57;
				return $sub;
			/* tamil */
			case 0xbca : $sub[0] = 0xbc6;
				$sub[1] = 0xbbe;
				return $sub;
			case 0xbcb : $sub[0] = 0xbc7;
				$sub[1] = 0xbbe;
				return $sub;
			case 0xbcc : $sub[0] = 0xbc6;
				$sub[1] = 0xbd7;
				return $sub;
			/* telugu */
			case 0xc48 : $sub[0] = 0xc46;
				$sub[1] = 0xc56;
				return $sub;
			/* kannada */
			case 0xcc0 : $sub[0] = 0xcbf;
				$sub[1] = 0xcd5;
				return $sub;
			case 0xcc7 : $sub[0] = 0xcc6;
				$sub[1] = 0xcd5;
				return $sub;
			case 0xcc8 : $sub[0] = 0xcc6;
				$sub[1] = 0xcd6;
				return $sub;
			case 0xcca : $sub[0] = 0xcc6;
				$sub[1] = 0xcc2;
				return $sub;
			case 0xccb : $sub[0] = 0xcc6;
				$sub[1] = 0xcc2;
				$sub[2] = 0xcd5;
				return $sub;
			/* malayalam */
			case 0xd4a : $sub[0] = 0xd46;
				$sub[1] = 0xd3e;
				return $sub;
			case 0xd4b : $sub[0] = 0xd47;
				$sub[1] = 0xd3e;
				return $sub;
			case 0xd4c : $sub[0] = 0xd46;
				$sub[1] = 0xd57;
				return $sub;
			/* sinhala */
			// NB Some fonts break with these Sinhala decomps (although this is Uniscribe spec)
			// Can check if character would be substituted by pstf and only decompose if true
			// e.g. if (isset($GSUBdata['pstf'][$ab])) - would need to pass $GSUBdata as parameter to this function
			case 0xdda : $sub[0] = 0xdd9;
				$sub[1] = 0xdca;
				return $sub;
			case 0xddc : $sub[0] = 0xdd9;
				$sub[1] = 0xdcf;
				return $sub;
			case 0xddd : $sub[0] = 0xdd9;
				$sub[1] = 0xdcf;
				$sub[2] = 0xdca;
				return $sub;
			case 0xdde : $sub[0] = 0xdd9;
				$sub[1] = 0xddf;
				return $sub;
			/* khmer */
			case 0x17be : $sub[0] = 0x17c1;
				$sub[1] = 0x17be;
				return $sub;
			case 0x17bf : $sub[0] = 0x17c1;
				$sub[1] = 0x17bf;
				return $sub;
			case 0x17c0 : $sub[0] = 0x17c1;
				$sub[1] = 0x17c0;
				return $sub;

			case 0x17c4 : $sub[0] = 0x17c1;
				$sub[1] = 0x17c4;
				return $sub;
			case 0x17c5 : $sub[0] = 0x17c1;
				$sub[1] = 0x17c5;
				return $sub;
			/* tibetan - included here although does not use Inidc shaper in other ways  */
			case 0xf73 : $sub[0] = 0xf71;
				$sub[1] = 0xf72;
				return $sub;
			case 0xf75 : $sub[0] = 0xf71;
				$sub[1] = 0xf74;
				return $sub;
			case 0xf76 : $sub[0] = 0xfb2;
				$sub[1] = 0xf80;
				return $sub;
			case 0xf77 : $sub[0] = 0xfb2;
				$sub[1] = 0xf81;
				return $sub;
			case 0xf78 : $sub[0] = 0xfb3;
				$sub[1] = 0xf80;
				return $sub;
			case 0xf79 : $sub[0] = 0xfb3;
				$sub[1] = 0xf71;
				$sub[2] = 0xf80;
				return $sub;
			case 0xf81 : $sub[0] = 0xf71;
				$sub[1] = 0xf80;
				return $sub;
		}
		return false;
	}

	public static function bubble_sort(&$arr, $start, $len)
	{
		if ($len < 2) {
			return;
		}
		$k = $start + $len - 2;
		while ($k >= $start) {
			for ($j = $start; $j <= $k; $j++) {
				if ($arr[$j]['indic_position'] > $arr[$j + 1]['indic_position']) {
					$t = $arr[$j];
					$arr[$j] = $arr[$j + 1];
					$arr[$j + 1] = $t;
				}
			}
			$k--;
		}
	}

}
