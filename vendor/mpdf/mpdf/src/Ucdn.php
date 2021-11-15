<?php

namespace Mpdf;

class Ucdn
{

	/* HarfBuzz ucdn/unicodedata_db.h */
	/* HarfBuzz ucdn/ucdn.c */
	/* HarfBuzz ucdn/ucdn.h */

	const SCRIPT_COMMON = 0;
	const SCRIPT_LATIN = 1;
	const SCRIPT_GREEK = 2;
	const SCRIPT_CYRILLIC = 3;
	const SCRIPT_ARMENIAN = 4;
	const SCRIPT_HEBREW = 5;
	const SCRIPT_ARABIC = 6;
	const SCRIPT_SYRIAC = 7;
	const SCRIPT_THAANA = 8;
	const SCRIPT_DEVANAGARI = 9;
	const SCRIPT_BENGALI = 10;
	const SCRIPT_GURMUKHI = 11;
	const SCRIPT_GUJARATI = 12;
	const SCRIPT_ORIYA = 13;
	const SCRIPT_TAMIL = 14;
	const SCRIPT_TELUGU = 15;
	const SCRIPT_KANNADA = 16;
	const SCRIPT_MALAYALAM = 17;
	const SCRIPT_SINHALA = 18;
	const SCRIPT_THAI = 19;
	const SCRIPT_LAO = 20;
	const SCRIPT_TIBETAN = 21;
	const SCRIPT_MYANMAR = 22;
	const SCRIPT_GEORGIAN = 23;
	const SCRIPT_HANGUL = 24;
	const SCRIPT_ETHIOPIC = 25;
	const SCRIPT_CHEROKEE = 26;
	const SCRIPT_CANADIAN_ABORIGINAL = 27;
	const SCRIPT_OGHAM = 28;
	const SCRIPT_RUNIC = 29;
	const SCRIPT_KHMER = 30;
	const SCRIPT_MONGOLIAN = 31;
	const SCRIPT_HIRAGANA = 32;
	const SCRIPT_KATAKANA = 33;
	const SCRIPT_BOPOMOFO = 34;
	const SCRIPT_HAN = 35;
	const SCRIPT_YI = 36;
	const SCRIPT_OLD_ITALIC = 37;
	const SCRIPT_GOTHIC = 38;
	const SCRIPT_DESERET = 39;
	const SCRIPT_INHERITED = 40;
	const SCRIPT_TAGALOG = 41;
	const SCRIPT_HANUNOO = 42;
	const SCRIPT_BUHID = 43;
	const SCRIPT_TAGBANWA = 44;
	const SCRIPT_LIMBU = 45;
	const SCRIPT_TAI_LE = 46;
	const SCRIPT_LINEAR_B = 47;
	const SCRIPT_UGARITIC = 48;
	const SCRIPT_SHAVIAN = 49;
	const SCRIPT_OSMANYA = 50;
	const SCRIPT_CYPRIOT = 51;
	const SCRIPT_BRAILLE = 52;
	const SCRIPT_BUGINESE = 53;
	const SCRIPT_COPTIC = 54;
	const SCRIPT_NEW_TAI_LUE = 55;
	const SCRIPT_GLAGOLITIC = 56;
	const SCRIPT_TIFINAGH = 57;
	const SCRIPT_SYLOTI_NAGRI = 58;
	const SCRIPT_OLD_PERSIAN = 59;
	const SCRIPT_KHAROSHTHI = 60;
	const SCRIPT_BALINESE = 61;
	const SCRIPT_CUNEIFORM = 62;
	const SCRIPT_PHOENICIAN = 63;
	const SCRIPT_PHAGS_PA = 64;
	const SCRIPT_NKO = 65;
	const SCRIPT_SUNDANESE = 66;
	const SCRIPT_LEPCHA = 67;
	const SCRIPT_OL_CHIKI = 68;
	const SCRIPT_VAI = 69;
	const SCRIPT_SAURASHTRA = 70;
	const SCRIPT_KAYAH_LI = 71;
	const SCRIPT_REJANG = 72;
	const SCRIPT_LYCIAN = 73;
	const SCRIPT_CARIAN = 74;
	const SCRIPT_LYDIAN = 75;
	const SCRIPT_CHAM = 76;
	const SCRIPT_TAI_THAM = 77;
	const SCRIPT_TAI_VIET = 78;
	const SCRIPT_AVESTAN = 79;
	const SCRIPT_EGYPTIAN_HIEROGLYPHS = 80;
	const SCRIPT_SAMARITAN = 81;
	const SCRIPT_LISU = 82;
	const SCRIPT_BAMUM = 83;
	const SCRIPT_JAVANESE = 84;
	const SCRIPT_MEETEI_MAYEK = 85;
	const SCRIPT_IMPERIAL_ARAMAIC = 86;
	const SCRIPT_OLD_SOUTH_ARABIAN = 87;
	const SCRIPT_INSCRIPTIONAL_PARTHIAN = 88;
	const SCRIPT_INSCRIPTIONAL_PAHLAVI = 89;
	const SCRIPT_OLD_TURKIC = 90;
	const SCRIPT_KAITHI = 91;
	const SCRIPT_BATAK = 92;
	const SCRIPT_BRAHMI = 93;
	const SCRIPT_MANDAIC = 94;
	const SCRIPT_CHAKMA = 95;
	const SCRIPT_MEROITIC_CURSIVE = 96;
	const SCRIPT_MEROITIC_HIEROGLYPHS = 97;
	const SCRIPT_MIAO = 98;
	const SCRIPT_SHARADA = 99;
	const SCRIPT_SORA_SOMPENG = 100;
	const SCRIPT_TAKRI = 101;
	const SCRIPT_UNKNOWN = 102;

	public static function get_ucd_record($code)
	{
		if ($code >= 0x110000) {
			$index = 0;
		} else {
			$index = self::$index0[$code >> (8)] << 5;
			$offset = ($code >> 3) & ((1 << 5) - 1);
			$index = self::$index1[$index + $offset] << 3;
			$offset = $code & ((1 << 3) - 1);
			$index = self::$index2[$index + $offset];
		}
		return self::$ucd_records[$index];
	}

	public static function get_general_category($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[0];
	}

	public static function get_combining_class($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[1];
	}

	public static function get_bidi_class($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[2];
	}

	public static function get_mirrored($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[3];
	}

	public static function get_east_asian_width($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[4];
	}

	public static function get_normalization_check($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[5];
	}

	public static function get_script($code)
	{
		$ucd_record = self::get_ucd_record($code);
		return $ucd_record[6];
	}

	// mPDF added
	public static $uni_scriptblock = [
		/* SCRIPT_COMMON */ 0 => '',
		/* SCRIPT_LATIN */ 1 => 'latn',
		/* SCRIPT_GREEK */ 2 => 'grek',
		/* SCRIPT_CYRILLIC */ 3 => 'cyrl',
		/* SCRIPT_ARMENIAN */ 4 => 'armn',
		/* SCRIPT_HEBREW */ 5 => 'hebr',
		/* SCRIPT_ARABIC */ 6 => 'arab',
		/* SCRIPT_SYRIAC */ 7 => 'syrc',
		/* SCRIPT_THAANA */ 8 => 'thaa',
		/* SCRIPT_DEVANAGARI */ 9 => 'dev2',
		/* SCRIPT_BENGALI */ 10 => 'bng2',
		/* SCRIPT_GURMUKHI */ 11 => 'gur2',
		/* SCRIPT_GUJARATI */ 12 => 'gjr2',
		/* SCRIPT_ORIYA */ 13 => 'ory2',
		/* SCRIPT_TAMIL */ 14 => 'tml2',
		/* SCRIPT_TELUGU */ 15 => 'tel2',
		/* SCRIPT_KANNADA */ 16 => 'knd2',
		/* SCRIPT_MALAYALAM */ 17 => 'mlm2',
		/* SCRIPT_SINHALA */ 18 => 'sinh',
		/* SCRIPT_THAI */ 19 => 'thai',
		/* SCRIPT_LAO */ 20 => 'lao ',
		/* SCRIPT_TIBETAN */ 21 => 'tibt',
		/* SCRIPT_MYANMAR */ 22 => 'mym2',
		/* SCRIPT_GEORGIAN */ 23 => 'geor',
		/* SCRIPT_HANGUL */ 24 => 'jamo', /* there is also a hang tag, but we want to activate jamo features if present */
		/* SCRIPT_ETHIOPIC */ 25 => 'ethi',
		/* SCRIPT_CHEROKEE */ 26 => 'cher',
		/* SCRIPT_CANADIAN_ABORIGINAL */ 27 => 'cans',
		/* SCRIPT_OGHAM */ 28 => 'ogam',
		/* SCRIPT_RUNIC */ 29 => 'runr',
		/* SCRIPT_KHMER */ 30 => 'khmr',
		/* SCRIPT_MONGOLIAN */ 31 => 'mong',
		/* SCRIPT_HIRAGANA */ 32 => 'kana',
		/* SCRIPT_KATAKANA */ 33 => 'kana',
		/* SCRIPT_BOPOMOFO */ 34 => 'bopo',
		/* SCRIPT_HAN */ 35 => 'hani',
		/* SCRIPT_YI */ 36 => 'yi  ',
		/* SCRIPT_OLD_ITALIC */ 37 => 'ital',
		/* SCRIPT_GOTHIC */ 38 => 'goth',
		/* SCRIPT_DESERET */ 39 => 'dsrt',
		/* SCRIPT_INHERITED */ 40 => '',
		/* SCRIPT_TAGALOG */ 41 => 'tglg',
		/* SCRIPT_HANUNOO */ 42 => 'hano',
		/* SCRIPT_BUHID */ 43 => 'buhd',
		/* SCRIPT_TAGBANWA */ 44 => 'tagb',
		/* SCRIPT_LIMBU */ 45 => 'limb',
		/* SCRIPT_TAI_LE */ 46 => 'tale',
		/* SCRIPT_LINEAR_B */ 47 => 'linb',
		/* SCRIPT_UGARITIC */ 48 => 'ugar',
		/* SCRIPT_SHAVIAN */ 49 => 'shaw',
		/* SCRIPT_OSMANYA */ 50 => 'osma',
		/* SCRIPT_CYPRIOT */ 51 => 'cprt',
		/* SCRIPT_BRAILLE */ 52 => 'brai',
		/* SCRIPT_BUGINESE */ 53 => 'bugi',
		/* SCRIPT_COPTIC */ 54 => 'copt',
		/* SCRIPT_NEW_TAI_LUE */ 55 => 'talu',
		/* SCRIPT_GLAGOLITIC */ 56 => 'glag',
		/* SCRIPT_TIFINAGH */ 57 => 'tfng',
		/* SCRIPT_SYLOTI_NAGRI */ 58 => 'sylo',
		/* SCRIPT_OLD_PERSIAN */ 59 => 'xpeo',
		/* SCRIPT_KHAROSHTHI */ 60 => 'khar',
		/* SCRIPT_BALINESE */ 61 => 'bali',
		/* SCRIPT_CUNEIFORM */ 62 => 'xsux',
		/* SCRIPT_PHOENICIAN */ 63 => 'phnx',
		/* SCRIPT_PHAGS_PA */ 64 => 'phag',
		/* SCRIPT_NKO */ 65 => 'nko ',
		/* SCRIPT_SUNDANESE */ 66 => 'sund',
		/* SCRIPT_LEPCHA */ 67 => 'lepc',
		/* SCRIPT_OL_CHIKI */ 68 => 'olck',
		/* SCRIPT_VAI */ 69 => 'vai ',
		/* SCRIPT_SAURASHTRA */ 70 => 'saur',
		/* SCRIPT_KAYAH_LI */ 71 => 'kali',
		/* SCRIPT_REJANG */ 72 => 'rjng',
		/* SCRIPT_LYCIAN */ 73 => 'lyci',
		/* SCRIPT_CARIAN */ 74 => 'cari',
		/* SCRIPT_LYDIAN */ 75 => 'lydi',
		/* SCRIPT_CHAM */ 76 => 'cham',
		/* SCRIPT_TAI_THAM */ 77 => 'lana',
		/* SCRIPT_TAI_VIET */ 78 => 'tavt',
		/* SCRIPT_AVESTAN */ 79 => 'avst',
		/* SCRIPT_EGYPTIAN_HIEROGLYPHS */ 80 => 'egyp',
		/* SCRIPT_SAMARITAN */ 81 => 'samr',
		/* SCRIPT_LISU */ 82 => 'lisu',
		/* SCRIPT_BAMUM */ 83 => 'bamu',
		/* SCRIPT_JAVANESE */ 84 => 'java',
		/* SCRIPT_MEETEI_MAYEK */ 85 => 'mtei',
		/* SCRIPT_IMPERIAL_ARAMAIC */ 86 => 'armi',
		/* SCRIPT_OLD_SOUTH_ARABIAN */ 87 => 'sarb',
		/* SCRIPT_INSCRIPTIONAL_PARTHIAN */ 88 => 'prti',
		/* SCRIPT_INSCRIPTIONAL_PAHLAVI */ 89 => 'phli',
		/* SCRIPT_OLD_TURKIC */ 90 => 'orkh',
		/* SCRIPT_KAITHI */ 91 => 'kthi',
		/* SCRIPT_BATAK */ 92 => 'batk',
		/* SCRIPT_BRAHMI */ 93 => 'brah',
		/* SCRIPT_MANDAIC */ 94 => 'mand',
		/* SCRIPT_CHAKMA */ 95 => 'cakm',
		/* SCRIPT_MEROITIC_CURSIVE */ 96 => 'merc',
		/* SCRIPT_MEROITIC_HIEROGLYPHS */ 97 => 'mero',
		/* SCRIPT_MIAO */ 98 => 'plrd',
		/* SCRIPT_SHARADA */ 99 => 'shrd',
		/* SCRIPT_SORA_SOMPENG */ 100 => 'sora',
		/* SCRIPT_TAKRI */ 101 => 'takr',
		/* SCRIPT_UNKNOWN */ 102 => '',
	];

	public static $ot_languages = [
		'aa' => 'AFR ', /* Afar */
		'ab' => 'ABK ', /* Abkhazian */
		'abq' => 'ABA ', /* Abaza */
		'ada' => 'DNG ', /* Dangme */
		'ady' => 'ADY ', /* Adyghe */
		'af' => 'AFK ', /* Afrikaans */
		'aii' => 'SWA ', /* Swadaya Aramaic */
		'aiw' => 'ARI ', /* Aari */
		'alt' => 'ALT ', /* [Southern] Altai */
		'am' => 'AMH ', /* Amharic */
		'amf' => 'HBN ', /* Hammer-Banna */
		'ar' => 'ARA ', /* Arabic */
		'arn' => 'MAP ', /* Mapudungun */
		'as' => 'ASM ', /* Assamese */
		'ath' => 'ATH ', /* Athapaskan [family] */
		'atv' => 'ALT ', /* [Northern] Altai */
		'av' => 'AVR ', /* Avaric */
		'awa' => 'AWA ', /* Awadhi */
		'ay' => 'AYM ', /* Aymara */
		'az' => 'AZE ', /* Azerbaijani */
		'ba' => 'BSH ', /* Bashkir */
		'bai' => 'BML ', /* Bamileke [family] */
		'bal' => 'BLI ', /* Baluchi */
		'bci' => 'BAU ', /* Baule */
		'bcq' => 'BCH ', /* Bench */
		'be' => 'BEL ', /* Belarussian */
		'bem' => 'BEM ', /* Bemba (Zambia) */
		'ber' => 'BER ', /* Berber [family] */
		'bfq' => 'BAD ', /* Badaga */
		'bft' => 'BLT ', /* Balti */
		'bfy' => 'BAG ', /* Baghelkhandi */
		'bg' => 'BGR ', /* Bulgarian */
		'bhb' => 'BHI ', /* Bhili */
		'bho' => 'BHO ', /* Bhojpuri */
		'bik' => 'BIK ', /* Bikol */
		'bin' => 'EDO ', /* Bini */
		'bjt' => 'BLN ', /* Balanta-Ganja */
		'bla' => 'BKF ', /* Blackfoot */
		'ble' => 'BLN ', /* Balanta-Kentohe */
		'bm' => 'BMB ', /* Bambara */
		'bn' => 'BEN ', /* Bengali */
		'bo' => 'TIB ', /* Tibetan */
		'br' => 'BRE ', /* Breton */
		'bra' => 'BRI ', /* Braj Bhasha */
		'brh' => 'BRH ', /* Brahui */
		'bs' => 'BOS ', /* Bosnian */
		'btb' => 'BTI ', /* Beti (Cameroon) */
		'bxr' => 'RBU ', /* Russian Buriat */
		'byn' => 'BIL ', /* Bilen */
		'ca' => 'CAT ', /* Catalan */
		'ce' => 'CHE ', /* Chechen */
		'ceb' => 'CEB ', /* Cebuano */
		'chp' => 'CHP ', /* Chipewyan */
		'chr' => 'CHR ', /* Cherokee */
		'ckt' => 'CHK ', /* Chukchi */
		'cop' => 'COP ', /* Coptic */
		'cr' => 'CRE ', /* Cree */
		'crh' => 'CRT ', /* Crimean Tatar */
		'crj' => 'ECR ', /* [Southern] East Cree */
		'crl' => 'ECR ', /* [Northern] East Cree */
		'crm' => 'MCR ', /* Moose Cree */
		'crx' => 'CRR ', /* Carrier */
		'cs' => 'CSY ', /* Czech */
		'cu' => 'CSL ', /* Church Slavic */
		'cv' => 'CHU ', /* Chuvash */
		'cwd' => 'DCR ', /* Woods Cree */
		'cy' => 'WEL ', /* Welsh */
		'da' => 'DAN ', /* Danish */
		'dap' => 'NIS ', /* Nisi (India) */
		'dar' => 'DAR ', /* Dargwa */
		'de' => 'DEU ', /* German */
		'din' => 'DNK ', /* Dinka */
		'dje' => 'DJR ', /* Djerma */
		'dng' => 'DUN ', /* Dungan */
		'doi' => 'DGR ', /* Dogri */
		'dsb' => 'LSB ', /* Lower Sorbian */
		'dv' => 'DIV ', /* Dhivehi */
		'dyu' => 'JUL ', /* Jula */
		'dz' => 'DZN ', /* Dzongkha */
		'ee' => 'EWE ', /* Ewe */
		'efi' => 'EFI ', /* Efik */
		'el' => 'ELL ', /* Modern Greek (1453-) */
		'grc' => 'PGR ', /* Polytonic Greek */
		'en' => 'ENG ', /* English */
		'eo' => 'NTO ', /* Esperanto */
		'eot' => 'BTI ', /* Beti (Côte d'Ivoire) */
		'es' => 'ESP ', /* Spanish */
		'et' => 'ETI ', /* Estonian */
		'eu' => 'EUQ ', /* Basque */
		'eve' => 'EVN ', /* Even */
		'evn' => 'EVK ', /* Evenki */
		'fa' => 'FAR ', /* Persian */
		'ff' => 'FUL ', /* Fulah */
		'fi' => 'FIN ', /* Finnish */
		'fil' => 'PIL ', /* Filipino */
		'fj' => 'FJI ', /* Fijian */
		'fo' => 'FOS ', /* Faroese */
		'fon' => 'FON ', /* Fon */
		'fr' => 'FRA ', /* French */
		'fur' => 'FRL ', /* Friulian */
		'fy' => 'FRI ', /* Western Frisian */
		'ga' => 'IRI ', /* Irish */
		'gaa' => 'GAD ', /* Ga */
		'gag' => 'GAG ', /* Gagauz */
		'gbm' => 'GAW ', /* Garhwali */
		'gd' => 'GAE ', /* Scottish Gaelic */
		'gez' => 'GEZ ', /* Ge'ez */
		'gl' => 'GAL ', /* Galician */
		'gld' => 'NAN ', /* Nanai */
		'gn' => 'GUA ', /* Guarani */
		'gon' => 'GON ', /* Gondi */
		'grt' => 'GRO ', /* Garo */
		'gru' => 'SOG ', /* Sodo Gurage */
		'gu' => 'GUJ ', /* Gujarati */
		'guk' => 'GMZ ', /* Gumuz */
		'gv' => 'MNX ', /* Manx Gaelic */
		'ha' => 'HAU ', /* Hausa */
		'har' => 'HRI ', /* Harari */
		'haw' => 'HAW ', /* Hawaiin */
		'he' => 'IWR ', /* Hebrew */
		'hi' => 'HIN ', /* Hindi */
		'hil' => 'HIL ', /* Hiligaynon */
		'hnd' => 'HND ', /* [Southern] Hindko */
		'hne' => 'CHH ', /* Chattisgarhi */
		'hno' => 'HND ', /* [Northern] Hindko */
		'hoc' => 'HO  ', /* Ho */
		'hoj' => 'HAR ', /* Harauti */
		'hr' => 'HRV ', /* Croatian */
		'hsb' => 'USB ', /* Upper Sorbian */
		'ht' => 'HAI ', /* Haitian */
		'hu' => 'HUN ', /* Hungarian */
		'hy' => 'HYE ', /* Armenian */
		'id' => 'IND ', /* Indonesian */
		'ig' => 'IBO ', /* Igbo */
		'igb' => 'EBI ', /* Ebira */
		'ijo' => 'IJO ', /* Ijo [family] */
		'ilo' => 'ILO ', /* Ilokano */
		'inh' => 'ING ', /* Ingush */
		'is' => 'ISL ', /* Icelandic */
		'it' => 'ITA ', /* Italian */
		'iu' => 'INU ', /* Inuktitut */
		'ja' => 'JAN ', /* Japanese */
		'jv' => 'JAV ', /* Javanese */
		'ka' => 'KAT ', /* Georgian */
		'kaa' => 'KRK ', /* Karakalpak */
		'kam' => 'KMB ', /* Kamba (Kenya) */
		'kar' => 'KRN ', /* Karen [family] */
		'kbd' => 'KAB ', /* Kabardian */
		'kdr' => 'KRM ', /* Karaim */
		'kdt' => 'KUY ', /* Kuy */
		'kex' => 'KKN ', /* Kokni */
		'kfr' => 'KAC ', /* Kachchi */
		'kfy' => 'KMN ', /* Kumaoni */
		'kha' => 'KSI ', /* Khasi */
		'khb' => 'XBD ', /* Tai Lue */
		'khw' => 'KHW ', /* Khowar */
		'ki' => 'KIK ', /* Kikuyu */
		'kjh' => 'KHA ', /* Khakass */
		'kk' => 'KAZ ', /* Kazakh */
		'kl' => 'GRN ', /* Kalaallisut */
		'kln' => 'KAL ', /* Kalenjin */
		'km' => 'KHM ', /* Central Khmer */
		'kmb' => 'MBN ', /* [North] Mbundu */
		'kmw' => 'KMO ', /* Komo (Democratic Republic of Congo) */
		'kn' => 'KAN ', /* Kannada */
		'ko' => 'KOR ', /* Korean */
		'koi' => 'KOP ', /* Komi-Permyak */
		'kok' => 'KOK ', /* Konkani */
		'kpe' => 'KPL ', /* Kpelle */
		'kpv' => 'KOZ ', /* Komi-Zyrian */
		'kpy' => 'KYK ', /* Koryak */
		'kqy' => 'KRT ', /* Koorete */
		'kr' => 'KNR ', /* Kanuri */
		'kri' => 'KRI ', /* Krio */
		'krl' => 'KRL ', /* Karelian */
		'kru' => 'KUU ', /* Kurukh */
		'ks' => 'KSH ', /* Kashmiri */
		'ku' => 'KUR ', /* Kurdish */
		'kum' => 'KUM ', /* Kumyk */
		'kvd' => 'KUI ', /* Kui (Indonesia) */
		'kxc' => 'KMS ', /* Komso */
		'kxu' => 'KUI ', /* Kui (India) */
		'ky' => 'KIR ', /* Kirghiz */
		'la' => 'LAT ', /* Latin */
		'lad' => 'JUD ', /* Ladino */
		'lb' => 'LTZ ', /* Luxembourgish */
		'lbe' => 'LAK ', /* Lak */
		'lbj' => 'LDK ', /* Ladakhi */
		'lez' => 'LEZ ', /* Lezgi */
		'lg' => 'LUG ', /* Luganda */
		'lif' => 'LMB ', /* Limbu */
		'lld' => 'LAD ', /* Ladin */
		'lmn' => 'LAM ', /* Lambani */
		'ln' => 'LIN ', /* Lingala */
		'lo' => 'LAO ', /* Lao */
		'lt' => 'LTH ', /* Lithuanian */
		'lu' => 'LUB ', /* Luba-Katanga */
		'lua' => 'LUB ', /* Luba-Kasai */
		'luo' => 'LUO ', /* Luo (Kenya and Tanzania) */
		'lus' => 'MIZ ', /* Mizo */
		'luy' => 'LUH ', /* Luhya [macrolanguage] */
		'lv' => 'LVI ', /* Latvian */
		'lzz' => 'LAZ ', /* Laz */
		'mai' => 'MTH ', /* Maithili */
		'mdc' => 'MLE ', /* Male (Papua New Guinea) */
		'mdf' => 'MOK ', /* Moksha */
		'mdy' => 'MLE ', /* Male (Ethiopia) */
		'men' => 'MDE ', /* Mende (Sierra Leone) */
		'mg' => 'MLG ', /* Malagasy */
		'mhr' => 'LMA ', /* Low Mari */
		'mi' => 'MRI ', /* Maori */
		'mk' => 'MKD ', /* Macedonian */
		'ml' => 'MLR ', /* Malayalam reformed  (MAL is Malayalam Traditional) */
		'mn' => 'MNG ', /* Mongolian */
		'mnc' => 'MCH ', /* Manchu */
		'mni' => 'MNI ', /* Manipuri */
		'mnk' => 'MND ', /* Mandinka */
		'mns' => 'MAN ', /* Mansi */
		'mnw' => 'MON ', /* Mon */
		'mo' => 'MOL ', /* Moldavian */
		'moh' => 'MOH ', /* Mohawk */
		'mpe' => 'MAJ ', /* Majang */
		'mr' => 'MAR ', /* Marathi */
		'mrj' => 'HMA ', /* High Mari */
		'ms' => 'MLY ', /* Malay */
		'mt' => 'MTS ', /* Maltese */
		'mwr' => 'MAW ', /* Marwari */
		'my' => 'BRM ', /* Burmese */
		'mym' => 'MEN ', /* Me'en */
		'myv' => 'ERZ ', /* Erzya */
		'nag' => 'NAG ', /* Naga-Assamese */
		'nb' => 'NOR ', /* Norwegian Bokmål */
		'nco' => 'SIB ', /* Sibe */
		'nd' => 'NDB ', /* [North] Ndebele */
		'ne' => 'NEP ', /* Nepali */
		'new' => 'NEW ', /* Newari */
		'ng' => 'NDG ', /* Ndonga */
		'ngl' => 'LMW ', /* Lomwe */
		'niu' => 'NIU ', /* Niuean */
		'niv' => 'GIL ', /* Gilyak */
		'nl' => 'NLD ', /* Dutch */
		'nn' => 'NYN ', /* Norwegian Nynorsk */
		'no' => 'NOR ', /* Norwegian (deprecated) */
		'nod' => 'NTA ', /* Northern Tai */
		'nog' => 'NOG ', /* Nogai */
		'nqo' => 'NKO ', /* N'Ko */
		'nr' => 'NDB ', /* [South] Ndebele */
		'nsk' => 'NAS ', /* Naskapi */
		'nso' => 'SOT ', /* [Northern] Sotho */
		'ny' => 'CHI ', /* Nyanja */
		'nyn' => 'NKL ', /* Nkole */
		'oc' => 'OCI ', /* Occitan (post 1500) */
		'oj' => 'OJB ', /* Ojibwa */
		'ojs' => 'OCR ', /* Oji-Cree */
		'om' => 'ORO ', /* Oromo */
		'or' => 'ORI ', /* Oriya */
		'os' => 'OSS ', /* Ossetian */
		'pa' => 'PAN ', /* Panjabi */
		'pce' => 'PLG ', /* [Ruching] Palaung */
		'pi' => 'PAL ', /* Pali */
		'pl' => 'PLK ', /* Polish */
		'pll' => 'PLG ', /* [Shwe] Palaung */
		'plp' => 'PAP ', /* Palpa */
		'prs' => 'DRI ', /* Dari */
		'ps' => 'PAS ', /* Pushto */
		'pt' => 'PTG ', /* Portuguese */
		'raj' => 'RAJ ', /* Rajasthani */
		'rbb' => 'PLG ', /* [Rumai] Palaung */
		'ria' => 'RIA ', /* Riang (India) */
		'ril' => 'RIA ', /* Riang (Myanmar) */
		'rki' => 'ARK ', /* Arakanese */
		'rm' => 'RMS ', /* Rhaeto-Romanic */
		'ro' => 'ROM ', /* Romanian */
		'rom' => 'ROY ', /* Romany */
		'ru' => 'RUS ', /* Russian */
		'rue' => 'RSY ', /* Rusyn */
		'rw' => 'RUA ', /* Ruanda */
		'sa' => 'SAN ', /* Sanskrit */
		'sah' => 'YAK ', /* Yakut */
		'sat' => 'SAT ', /* Santali */
		'sck' => 'SAD ', /* Sadri */
		'scs' => 'SLA ', /* [North] Slavey */
		'sd' => 'SND ', /* Sindhi */
		'se' => 'NSM ', /* Northern Sami */
		'seh' => 'SNA ', /* Sena */
		'sel' => 'SEL ', /* Selkup */
		'sg' => 'SGO ', /* Sango */
		'shn' => 'SHN ', /* Shan */
		'si' => 'SNH ', /* Sinhala */
		'sid' => 'SID ', /* Sidamo */
		'sjd' => 'KSM ', /* Kildin Sami */
		'sk' => 'SKY ', /* Slovak */
		'skr' => 'SRK ', /* Seraiki */
		'sl' => 'SLV ', /* Slovenian */
		'sm' => 'SMO ', /* Samoan */
		'sma' => 'SSM ', /* Southern Sami */
		'smj' => 'LSM ', /* Lule Sami */
		'smn' => 'ISM ', /* Inari Sami */
		'sms' => 'SKS ', /* Skolt Sami */
		'snk' => 'SNK ', /* Soninke */
		'so' => 'SML ', /* Somali */
		'sq' => 'SQI ', /* Albanian */
		'sr' => 'SRB ', /* Serbian */
		'srr' => 'SRR ', /* Serer */
		'ss' => 'SWZ ', /* Swazi */
		'st' => 'SOT ', /* [Southern] Sotho */
		'suq' => 'SUR ', /* Suri */
		'sv' => 'SVE ', /* Swedish */
		'sva' => 'SVA ', /* Svan */
		'sw' => 'SWK ', /* Swahili */
		'swb' => 'CMR ', /* Comorian */
		'syr' => 'SYR ', /* Syriac */
		'ta' => 'TAM ', /* Tamil */
		'tab' => 'TAB ', /* Tabasaran */
		'tcy' => 'TUL ', /* Tulu */
		'te' => 'TEL ', /* Telugu */
		'tem' => 'TMN ', /* Temne */
		'tg' => 'TAJ ', /* Tajik */
		'th' => 'THA ', /* Thai */
		'ti' => 'TGY ', /* Tigrinya */
		'tig' => 'TGR ', /* Tigre */
		'tk' => 'TKM ', /* Turkmen */
		'tn' => 'TNA ', /* Tswana */
		'to' => 'TGN ', /* Tonga (Tonga Islands) */
		'tr' => 'TRK ', /* Turkish */
		'tru' => 'TUA ', /* Turoyo Aramaic */
		'ts' => 'TSG ', /* Tsonga */
		'tt' => 'TAT ', /* Tatar */
		'tw' => 'TWI ', /* Twi */
		'ty' => 'THT ', /* Tahitian */
		'tyv' => 'TUV ', /* Tuvin */
		'udm' => 'UDM ', /* Udmurt */
		'ug' => 'UYG ', /* Uighur */
		'uk' => 'UKR ', /* Ukrainian */
		'umb' => 'MBN ', /* [South] Mbundu */
		'unr' => 'MUN ', /* Mundari */
		'ur' => 'URD ', /* Urdu */
		'uz' => 'UZB ', /* Uzbek */
		've' => 'VEN ', /* Venda */
		'vi' => 'VIT ', /* Vietnamese */
		'vmw' => 'MAK ', /* Makua */
		'wbm' => 'WA  ', /* Wa */
		'wbr' => 'WAG ', /* Wagdi */
		'wo' => 'WLF ', /* Wolof */
		'xal' => 'KLM ', /* Kalmyk */
		'xh' => 'XHS ', /* Xhosa */
		'xom' => 'KMO ', /* Komo (Sudan) */
		'xsl' => 'SSL ', /* South Slavey */
		'yi' => 'JII ', /* Yiddish */
		'yid' => 'JII ', /* Yiddish */
		'yo' => 'YBA ', /* Yoruba */
		'yso' => 'NIS ', /* Nisi (China) */
		'zne' => 'ZND ', /* Zande */
		'zu' => 'ZUL ', /* Zulu */
		'zh-cn' => 'ZHS ', /* Chinese (China) */
		'zh-hk' => 'ZHH ', /* Chinese (Hong Kong) */
		'zh-mo' => 'ZHT ', /* Chinese (Macao) */
		'zh-sg' => 'ZHS ', /* Chinese (Singapore) */
		'zh-tw' => 'ZHT ', /* Chinese (Taiwan) */
	];

	// hb-unicode.h
	const UNICODE_GENERAL_CATEGORY_CONTROL = 0;   /* Cc */
	const UNICODE_GENERAL_CATEGORY_FORMAT = 1;   /* Cf */
	const UNICODE_GENERAL_CATEGORY_UNASSIGNED = 2;   /* Cn */
	const UNICODE_GENERAL_CATEGORY_PRIVATE_USE = 3;   /* Co */
	const UNICODE_GENERAL_CATEGORY_SURROGATE = 4;   /* Cs */
	const UNICODE_GENERAL_CATEGORY_LOWERCASE_LETTER = 5;  /* Ll */
	const UNICODE_GENERAL_CATEGORY_MODIFIER_LETTER = 6;  /* Lm */
	const UNICODE_GENERAL_CATEGORY_OTHER_LETTER = 7;  /* Lo */
	const UNICODE_GENERAL_CATEGORY_TITLECASE_LETTER = 8;  /* Lt */
	const UNICODE_GENERAL_CATEGORY_UPPERCASE_LETTER = 9;  /* Lu */
	const UNICODE_GENERAL_CATEGORY_SPACING_MARK = 10;  /* Mc */
	const UNICODE_GENERAL_CATEGORY_ENCLOSING_MARK = 11;  /* Me */
	const UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK = 12;  /* Mn */
	const UNICODE_GENERAL_CATEGORY_DECIMAL_NUMBER = 13;  /* Nd */
	const UNICODE_GENERAL_CATEGORY_LETTER_NUMBER = 14;  /* Nl */
	const UNICODE_GENERAL_CATEGORY_OTHER_NUMBER = 15;  /* No */
	const UNICODE_GENERAL_CATEGORY_CONNECT_PUNCTUATION = 16; /* Pc */
	const UNICODE_GENERAL_CATEGORY_DASH_PUNCTUATION = 17;  /* Pd */
	const UNICODE_GENERAL_CATEGORY_CLOSE_PUNCTUATION = 18; /* Pe */
	const UNICODE_GENERAL_CATEGORY_FINAL_PUNCTUATION = 19; /* Pf */
	const UNICODE_GENERAL_CATEGORY_INITIAL_PUNCTUATION = 20; /* Pi */
	const UNICODE_GENERAL_CATEGORY_OTHER_PUNCTUATION = 21; /* Po */
	const UNICODE_GENERAL_CATEGORY_OPEN_PUNCTUATION = 22;  /* Ps */
	const UNICODE_GENERAL_CATEGORY_CURRENCY_SYMBOL = 23;  /* Sc */
	const UNICODE_GENERAL_CATEGORY_MODIFIER_SYMBOL = 24;  /* Sk */
	const UNICODE_GENERAL_CATEGORY_MATH_SYMBOL = 25;  /* Sm */
	const UNICODE_GENERAL_CATEGORY_OTHER_SYMBOL = 26;  /* So */
	const UNICODE_GENERAL_CATEGORY_LINE_SEPARATOR = 27;  /* Zl */
	const UNICODE_GENERAL_CATEGORY_PARAGRAPH_SEPARATOR = 28; /* Zp */
	const UNICODE_GENERAL_CATEGORY_SPACE_SEPARATOR = 29;  /* Zs */

	function general_category_is_mark($gen_cat)
	{
		return $gen_cat == self::UNICODE_GENERAL_CATEGORY_SPACING_MARK || $gen_cat == self::UNICODE_GENERAL_CATEGORY_ENCLOSING_MARK ||
			$gen_cat == self::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK;
		// define UNICODE_GENERAL_CATEGORY_IS_MARK(gen_cat)
		//if (FLAG(gen_cat) & (FLAG(UNICODE_GENERAL_CATEGORY_SPACING_MARK) | FLAG(UNICODE_GENERAL_CATEGORY_ENCLOSING_MARK) | FLAG(UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK))) { return true; }
	}

	const BIDI_CLASS_L = 0;
	const BIDI_CLASS_LRE = 1;
	const BIDI_CLASS_LRO = 2;
	const BIDI_CLASS_R = 3;
	const BIDI_CLASS_AL = 4;
	const BIDI_CLASS_RLE = 5;
	const BIDI_CLASS_RLO = 6;
	const BIDI_CLASS_PDF = 7;
	const BIDI_CLASS_EN = 8;
	const BIDI_CLASS_ES = 9;
	const BIDI_CLASS_ET = 10;
	const BIDI_CLASS_AN = 11;
	const BIDI_CLASS_CS = 12;
	const BIDI_CLASS_NSM = 13;
	const BIDI_CLASS_BN = 14;
	const BIDI_CLASS_B = 15;
	const BIDI_CLASS_S = 16;
	const BIDI_CLASS_WS = 17;
	const BIDI_CLASS_ON = 18;

	// UNIDATA_VERSION 6.2.0
	/* a list of unique database records */
	/* struct {
	  category;
	  combining;
	  bidi_class;
	  mirrored;
	  east_asian_width;
	  normalization_check;
	  script;
	  }
	 */
	private static $ucd_records = [
		[2, 0, 18, 0, 5, 0, 102],
		[0, 0, 14, 0, 5, 0, 0],
		[0, 0, 16, 0, 5, 0, 0],
		[0, 0, 15, 0, 5, 0, 0],
		[0, 0, 17, 0, 5, 0, 0],
		[29, 0, 17, 0, 3, 0, 0],
		[21, 0, 18, 0, 3, 0, 0],
		[21, 0, 10, 0, 3, 0, 0],
		[23, 0, 10, 0, 3, 0, 0],
		[22, 0, 18, 1, 3, 0, 0],
		[18, 0, 18, 1, 3, 0, 0],
		[25, 0, 9, 0, 3, 0, 0],
		[21, 0, 12, 0, 3, 0, 0],
		[17, 0, 9, 0, 3, 0, 0],
		[13, 0, 8, 0, 3, 0, 0],
		[25, 0, 18, 1, 3, 0, 0],
		[25, 0, 18, 0, 3, 0, 0],
		[9, 0, 0, 0, 3, 0, 1],
		[24, 0, 18, 0, 3, 0, 0],
		[16, 0, 18, 0, 3, 0, 0],
		[5, 0, 0, 0, 3, 0, 1],
		[29, 0, 12, 0, 5, 0, 0],
		[21, 0, 18, 0, 4, 0, 0],
		[23, 0, 10, 0, 4, 0, 0],
		[26, 0, 18, 0, 3, 0, 0],
		[24, 0, 18, 0, 4, 0, 0],
		[26, 0, 18, 0, 5, 0, 0],
		[7, 0, 0, 0, 4, 0, 1],
		[20, 0, 18, 1, 5, 0, 0],
		[1, 0, 14, 0, 4, 0, 0],
		[26, 0, 18, 0, 4, 0, 0],
		[26, 0, 10, 0, 4, 0, 0],
		[25, 0, 10, 0, 4, 0, 0],
		[15, 0, 8, 0, 4, 0, 0],
		[5, 0, 0, 0, 5, 0, 0],
		[19, 0, 18, 1, 5, 0, 0],
		[15, 0, 18, 0, 4, 0, 0],
		[9, 0, 0, 0, 5, 0, 1],
		[9, 0, 0, 0, 4, 0, 1],
		[25, 0, 18, 0, 4, 0, 0],
		[5, 0, 0, 0, 4, 0, 1],
		[5, 0, 0, 0, 5, 0, 1],
		[7, 0, 0, 0, 5, 0, 1],
		[8, 0, 0, 0, 5, 0, 1],
		[6, 0, 0, 0, 5, 0, 1],
		[6, 0, 18, 0, 5, 0, 0],
		[6, 0, 0, 0, 5, 0, 0],
		[24, 0, 18, 0, 5, 0, 0],
		[6, 0, 18, 0, 4, 0, 0],
		[6, 0, 0, 0, 4, 0, 0],
		[24, 0, 18, 0, 5, 0, 34],
		[12, 230, 13, 0, 4, 0, 40],
		[12, 232, 13, 0, 4, 0, 40],
		[12, 220, 13, 0, 4, 0, 40],
		[12, 216, 13, 0, 4, 0, 40],
		[12, 202, 13, 0, 4, 0, 40],
		[12, 1, 13, 0, 4, 0, 40],
		[12, 240, 13, 0, 4, 0, 40],
		[12, 0, 13, 0, 4, 0, 40],
		[12, 233, 13, 0, 4, 0, 40],
		[12, 234, 13, 0, 4, 0, 40],
		[9, 0, 0, 0, 5, 0, 2],
		[5, 0, 0, 0, 5, 0, 2],
		[24, 0, 18, 0, 5, 0, 2],
		[2, 0, 18, 0, 5, 0, 102],
		[6, 0, 0, 0, 5, 0, 2],
		[21, 0, 18, 0, 5, 0, 0],
		[9, 0, 0, 0, 4, 0, 2],
		[5, 0, 0, 0, 4, 0, 2],
		[9, 0, 0, 0, 5, 0, 54],
		[5, 0, 0, 0, 5, 0, 54],
		[25, 0, 18, 0, 5, 0, 2],
		[9, 0, 0, 0, 5, 0, 3],
		[9, 0, 0, 0, 4, 0, 3],
		[5, 0, 0, 0, 4, 0, 3],
		[5, 0, 0, 0, 5, 0, 3],
		[26, 0, 0, 0, 5, 0, 3],
		[12, 230, 13, 0, 5, 0, 3],
		[12, 230, 13, 0, 5, 0, 40],
		[11, 0, 13, 0, 5, 0, 3],
		[9, 0, 0, 0, 5, 0, 4],
		[6, 0, 0, 0, 5, 0, 4],
		[21, 0, 0, 0, 5, 0, 4],
		[5, 0, 0, 0, 5, 0, 4],
		[21, 0, 0, 0, 5, 0, 0],
		[17, 0, 18, 0, 5, 0, 4],
		[23, 0, 10, 0, 5, 0, 4],
		[12, 220, 13, 0, 5, 0, 5],
		[12, 230, 13, 0, 5, 0, 5],
		[12, 222, 13, 0, 5, 0, 5],
		[12, 228, 13, 0, 5, 0, 5],
		[12, 10, 13, 0, 5, 0, 5],
		[12, 11, 13, 0, 5, 0, 5],
		[12, 12, 13, 0, 5, 0, 5],
		[12, 13, 13, 0, 5, 0, 5],
		[12, 14, 13, 0, 5, 0, 5],
		[12, 15, 13, 0, 5, 0, 5],
		[12, 16, 13, 0, 5, 0, 5],
		[12, 17, 13, 0, 5, 0, 5],
		[12, 18, 13, 0, 5, 0, 5],
		[12, 19, 13, 0, 5, 0, 5],
		[12, 20, 13, 0, 5, 0, 5],
		[12, 21, 13, 0, 5, 0, 5],
		[12, 22, 13, 0, 5, 0, 5],
		[17, 0, 3, 0, 5, 0, 5],
		[12, 23, 13, 0, 5, 0, 5],
		[21, 0, 3, 0, 5, 0, 5],
		[12, 24, 13, 0, 5, 0, 5],
		[12, 25, 13, 0, 5, 0, 5],
		[7, 0, 3, 0, 5, 0, 5],
		[1, 0, 11, 0, 5, 0, 6],
		[25, 0, 18, 0, 5, 0, 6],
		[25, 0, 4, 0, 5, 0, 6],
		[21, 0, 10, 0, 5, 0, 6],
		[23, 0, 4, 0, 5, 0, 6],
		[21, 0, 12, 0, 5, 0, 0],
		[21, 0, 4, 0, 5, 0, 6],
		[26, 0, 18, 0, 5, 0, 6],
		[12, 230, 13, 0, 5, 0, 6],
		[12, 30, 13, 0, 5, 0, 6],
		[12, 31, 13, 0, 5, 0, 6],
		[12, 32, 13, 0, 5, 0, 6],
		[21, 0, 4, 0, 5, 0, 0],
		[7, 0, 4, 0, 5, 0, 6],
		[6, 0, 4, 0, 5, 0, 0],
		[12, 27, 13, 0, 5, 0, 40],
		[12, 28, 13, 0, 5, 0, 40],
		[12, 29, 13, 0, 5, 0, 40],
		[12, 30, 13, 0, 5, 0, 40],
		[12, 31, 13, 0, 5, 0, 40],
		[12, 32, 13, 0, 5, 0, 40],
		[12, 33, 13, 0, 5, 0, 40],
		[12, 34, 13, 0, 5, 0, 40],
		[12, 220, 13, 0, 5, 0, 40],
		[12, 220, 13, 0, 5, 0, 6],
		[13, 0, 11, 0, 5, 0, 0],
		[21, 0, 11, 0, 5, 0, 6],
		[12, 35, 13, 0, 5, 0, 40],
		[1, 0, 11, 0, 5, 0, 0],
		[6, 0, 4, 0, 5, 0, 6],
		[13, 0, 8, 0, 5, 0, 6],
		[26, 0, 4, 0, 5, 0, 6],
		[21, 0, 4, 0, 5, 0, 7],
		[1, 0, 4, 0, 5, 0, 7],
		[7, 0, 4, 0, 5, 0, 7],
		[12, 36, 13, 0, 5, 0, 7],
		[12, 230, 13, 0, 5, 0, 7],
		[12, 220, 13, 0, 5, 0, 7],
		[7, 0, 4, 0, 5, 0, 8],
		[12, 0, 13, 0, 5, 0, 8],
		[13, 0, 3, 0, 5, 0, 65],
		[7, 0, 3, 0, 5, 0, 65],
		[12, 230, 13, 0, 5, 0, 65],
		[12, 220, 13, 0, 5, 0, 65],
		[6, 0, 3, 0, 5, 0, 65],
		[26, 0, 18, 0, 5, 0, 65],
		[21, 0, 18, 0, 5, 0, 65],
		[7, 0, 3, 0, 5, 0, 81],
		[12, 230, 13, 0, 5, 0, 81],
		[6, 0, 3, 0, 5, 0, 81],
		[21, 0, 3, 0, 5, 0, 81],
		[7, 0, 3, 0, 5, 0, 94],
		[12, 220, 13, 0, 5, 0, 94],
		[21, 0, 3, 0, 5, 0, 94],
		[12, 27, 13, 0, 5, 0, 6],
		[12, 28, 13, 0, 5, 0, 6],
		[12, 29, 13, 0, 5, 0, 6],
		[12, 0, 13, 0, 5, 0, 9],
		[10, 0, 0, 0, 5, 0, 9],
		[7, 0, 0, 0, 5, 0, 9],
		[12, 7, 13, 0, 5, 0, 9],
		[12, 9, 13, 0, 5, 0, 9],
		[12, 230, 13, 0, 5, 0, 9],
		[13, 0, 0, 0, 5, 0, 9],
		[21, 0, 0, 0, 5, 0, 9],
		[6, 0, 0, 0, 5, 0, 9],
		[12, 0, 13, 0, 5, 0, 10],
		[10, 0, 0, 0, 5, 0, 10],
		[7, 0, 0, 0, 5, 0, 10],
		[12, 7, 13, 0, 5, 0, 10],
		[12, 9, 13, 0, 5, 0, 10],
		[13, 0, 0, 0, 5, 0, 10],
		[23, 0, 10, 0, 5, 0, 10],
		[15, 0, 0, 0, 5, 0, 10],
		[26, 0, 0, 0, 5, 0, 10],
		[12, 0, 13, 0, 5, 0, 11],
		[10, 0, 0, 0, 5, 0, 11],
		[7, 0, 0, 0, 5, 0, 11],
		[12, 7, 13, 0, 5, 0, 11],
		[12, 9, 13, 0, 5, 0, 11],
		[13, 0, 0, 0, 5, 0, 11],
		[12, 0, 13, 0, 5, 0, 12],
		[10, 0, 0, 0, 5, 0, 12],
		[7, 0, 0, 0, 5, 0, 12],
		[12, 7, 13, 0, 5, 0, 12],
		[12, 9, 13, 0, 5, 0, 12],
		[13, 0, 0, 0, 5, 0, 12],
		[21, 0, 0, 0, 5, 0, 12],
		[23, 0, 10, 0, 5, 0, 12],
		[12, 0, 13, 0, 5, 0, 13],
		[10, 0, 0, 0, 5, 0, 13],
		[7, 0, 0, 0, 5, 0, 13],
		[12, 7, 13, 0, 5, 0, 13],
		[12, 9, 13, 0, 5, 0, 13],
		[13, 0, 0, 0, 5, 0, 13],
		[26, 0, 0, 0, 5, 0, 13],
		[15, 0, 0, 0, 5, 0, 13],
		[12, 0, 13, 0, 5, 0, 14],
		[7, 0, 0, 0, 5, 0, 14],
		[10, 0, 0, 0, 5, 0, 14],
		[12, 9, 13, 0, 5, 0, 14],
		[13, 0, 0, 0, 5, 0, 14],
		[15, 0, 0, 0, 5, 0, 14],
		[26, 0, 18, 0, 5, 0, 14],
		[23, 0, 10, 0, 5, 0, 14],
		[10, 0, 0, 0, 5, 0, 15],
		[7, 0, 0, 0, 5, 0, 15],
		[12, 0, 13, 0, 5, 0, 15],
		[12, 9, 13, 0, 5, 0, 15],
		[12, 84, 13, 0, 5, 0, 15],
		[12, 91, 13, 0, 5, 0, 15],
		[13, 0, 0, 0, 5, 0, 15],
		[15, 0, 18, 0, 5, 0, 15],
		[26, 0, 0, 0, 5, 0, 15],
		[10, 0, 0, 0, 5, 0, 16],
		[7, 0, 0, 0, 5, 0, 16],
		[12, 7, 13, 0, 5, 0, 16],
		[12, 0, 0, 0, 5, 0, 16],
		[12, 0, 13, 0, 5, 0, 16],
		[12, 9, 13, 0, 5, 0, 16],
		[13, 0, 0, 0, 5, 0, 16],
		[10, 0, 0, 0, 5, 0, 17],
		[7, 0, 0, 0, 5, 0, 17],
		[12, 0, 13, 0, 5, 0, 17],
		[12, 9, 13, 0, 5, 0, 17],
		[13, 0, 0, 0, 5, 0, 17],
		[15, 0, 0, 0, 5, 0, 17],
		[26, 0, 0, 0, 5, 0, 17],
		[10, 0, 0, 0, 5, 0, 18],
		[7, 0, 0, 0, 5, 0, 18],
		[12, 9, 13, 0, 5, 0, 18],
		[12, 0, 13, 0, 5, 0, 18],
		[21, 0, 0, 0, 5, 0, 18],
		[7, 0, 0, 0, 5, 0, 19],
		[12, 0, 13, 0, 5, 0, 19],
		[12, 103, 13, 0, 5, 0, 19],
		[12, 9, 13, 0, 5, 0, 19],
		[23, 0, 10, 0, 5, 0, 0],
		[6, 0, 0, 0, 5, 0, 19],
		[12, 107, 13, 0, 5, 0, 19],
		[21, 0, 0, 0, 5, 0, 19],
		[13, 0, 0, 0, 5, 0, 19],
		[7, 0, 0, 0, 5, 0, 20],
		[12, 0, 13, 0, 5, 0, 20],
		[12, 118, 13, 0, 5, 0, 20],
		[6, 0, 0, 0, 5, 0, 20],
		[12, 122, 13, 0, 5, 0, 20],
		[13, 0, 0, 0, 5, 0, 20],
		[7, 0, 0, 0, 5, 0, 21],
		[26, 0, 0, 0, 5, 0, 21],
		[21, 0, 0, 0, 5, 0, 21],
		[12, 220, 13, 0, 5, 0, 21],
		[13, 0, 0, 0, 5, 0, 21],
		[15, 0, 0, 0, 5, 0, 21],
		[12, 216, 13, 0, 5, 0, 21],
		[22, 0, 18, 1, 5, 0, 21],
		[18, 0, 18, 1, 5, 0, 21],
		[10, 0, 0, 0, 5, 0, 21],
		[12, 129, 13, 0, 5, 0, 21],
		[12, 130, 13, 0, 5, 0, 21],
		[12, 0, 13, 0, 5, 0, 21],
		[12, 132, 13, 0, 5, 0, 21],
		[12, 230, 13, 0, 5, 0, 21],
		[12, 9, 13, 0, 5, 0, 21],
		[26, 0, 0, 0, 5, 0, 0],
		[7, 0, 0, 0, 5, 0, 22],
		[10, 0, 0, 0, 5, 0, 22],
		[12, 0, 13, 0, 5, 0, 22],
		[12, 7, 13, 0, 5, 0, 22],
		[12, 9, 13, 0, 5, 0, 22],
		[13, 0, 0, 0, 5, 0, 22],
		[21, 0, 0, 0, 5, 0, 22],
		[12, 220, 13, 0, 5, 0, 22],
		[26, 0, 0, 0, 5, 0, 22],
		[9, 0, 0, 0, 5, 0, 23],
		[7, 0, 0, 0, 5, 0, 23],
		[6, 0, 0, 0, 5, 0, 23],
		[7, 0, 0, 0, 2, 0, 24],
		[7, 0, 0, 0, 5, 0, 24],
		[7, 0, 0, 0, 5, 0, 25],
		[12, 230, 13, 0, 5, 0, 25],
		[21, 0, 0, 0, 5, 0, 25],
		[15, 0, 0, 0, 5, 0, 25],
		[26, 0, 18, 0, 5, 0, 25],
		[7, 0, 0, 0, 5, 0, 26],
		[17, 0, 18, 0, 5, 0, 27],
		[7, 0, 0, 0, 5, 0, 27],
		[21, 0, 0, 0, 5, 0, 27],
		[29, 0, 17, 0, 5, 0, 28],
		[7, 0, 0, 0, 5, 0, 28],
		[22, 0, 18, 1, 5, 0, 28],
		[18, 0, 18, 1, 5, 0, 28],
		[7, 0, 0, 0, 5, 0, 29],
		[14, 0, 0, 0, 5, 0, 29],
		[7, 0, 0, 0, 5, 0, 41],
		[12, 0, 13, 0, 5, 0, 41],
		[12, 9, 13, 0, 5, 0, 41],
		[7, 0, 0, 0, 5, 0, 42],
		[12, 0, 13, 0, 5, 0, 42],
		[12, 9, 13, 0, 5, 0, 42],
		[7, 0, 0, 0, 5, 0, 43],
		[12, 0, 13, 0, 5, 0, 43],
		[7, 0, 0, 0, 5, 0, 44],
		[12, 0, 13, 0, 5, 0, 44],
		[7, 0, 0, 0, 5, 0, 30],
		[12, 0, 13, 0, 5, 0, 30],
		[10, 0, 0, 0, 5, 0, 30],
		[12, 9, 13, 0, 5, 0, 30],
		[21, 0, 0, 0, 5, 0, 30],
		[6, 0, 0, 0, 5, 0, 30],
		[23, 0, 10, 0, 5, 0, 30],
		[12, 230, 13, 0, 5, 0, 30],
		[13, 0, 0, 0, 5, 0, 30],
		[15, 0, 18, 0, 5, 0, 30],
		[21, 0, 18, 0, 5, 0, 31],
		[17, 0, 18, 0, 5, 0, 31],
		[12, 0, 13, 0, 5, 0, 31],
		[29, 0, 17, 0, 5, 0, 31],
		[13, 0, 0, 0, 5, 0, 31],
		[7, 0, 0, 0, 5, 0, 31],
		[6, 0, 0, 0, 5, 0, 31],
		[12, 228, 13, 0, 5, 0, 31],
		[7, 0, 0, 0, 5, 0, 45],
		[12, 0, 13, 0, 5, 0, 45],
		[10, 0, 0, 0, 5, 0, 45],
		[12, 222, 13, 0, 5, 0, 45],
		[12, 230, 13, 0, 5, 0, 45],
		[12, 220, 13, 0, 5, 0, 45],
		[26, 0, 18, 0, 5, 0, 45],
		[21, 0, 18, 0, 5, 0, 45],
		[13, 0, 0, 0, 5, 0, 45],
		[7, 0, 0, 0, 5, 0, 46],
		[7, 0, 0, 0, 5, 0, 55],
		[10, 0, 0, 0, 5, 0, 55],
		[13, 0, 0, 0, 5, 0, 55],
		[15, 0, 0, 0, 5, 0, 55],
		[26, 0, 18, 0, 5, 0, 55],
		[26, 0, 18, 0, 5, 0, 30],
		[7, 0, 0, 0, 5, 0, 53],
		[12, 230, 13, 0, 5, 0, 53],
		[12, 220, 13, 0, 5, 0, 53],
		[10, 0, 0, 0, 5, 0, 53],
		[21, 0, 0, 0, 5, 0, 53],
		[7, 0, 0, 0, 5, 0, 77],
		[10, 0, 0, 0, 5, 0, 77],
		[12, 0, 13, 0, 5, 0, 77],
		[12, 9, 13, 0, 5, 0, 77],
		[12, 230, 13, 0, 5, 0, 77],
		[12, 220, 13, 0, 5, 0, 77],
		[13, 0, 0, 0, 5, 0, 77],
		[21, 0, 0, 0, 5, 0, 77],
		[6, 0, 0, 0, 5, 0, 77],
		[12, 0, 13, 0, 5, 0, 61],
		[10, 0, 0, 0, 5, 0, 61],
		[7, 0, 0, 0, 5, 0, 61],
		[12, 7, 13, 0, 5, 0, 61],
		[10, 9, 0, 0, 5, 0, 61],
		[13, 0, 0, 0, 5, 0, 61],
		[21, 0, 0, 0, 5, 0, 61],
		[26, 0, 0, 0, 5, 0, 61],
		[12, 230, 13, 0, 5, 0, 61],
		[12, 220, 13, 0, 5, 0, 61],
		[12, 0, 13, 0, 5, 0, 66],
		[10, 0, 0, 0, 5, 0, 66],
		[7, 0, 0, 0, 5, 0, 66],
		[10, 9, 0, 0, 5, 0, 66],
		[12, 9, 13, 0, 5, 0, 66],
		[13, 0, 0, 0, 5, 0, 66],
		[7, 0, 0, 0, 5, 0, 92],
		[12, 7, 13, 0, 5, 0, 92],
		[10, 0, 0, 0, 5, 0, 92],
		[12, 0, 13, 0, 5, 0, 92],
		[10, 9, 0, 0, 5, 0, 92],
		[21, 0, 0, 0, 5, 0, 92],
		[7, 0, 0, 0, 5, 0, 67],
		[10, 0, 0, 0, 5, 0, 67],
		[12, 0, 13, 0, 5, 0, 67],
		[12, 7, 13, 0, 5, 0, 67],
		[21, 0, 0, 0, 5, 0, 67],
		[13, 0, 0, 0, 5, 0, 67],
		[13, 0, 0, 0, 5, 0, 68],
		[7, 0, 0, 0, 5, 0, 68],
		[6, 0, 0, 0, 5, 0, 68],
		[21, 0, 0, 0, 5, 0, 68],
		[21, 0, 0, 0, 5, 0, 66],
		[12, 1, 13, 0, 5, 0, 40],
		[10, 0, 0, 0, 5, 0, 0],
		[7, 0, 0, 0, 5, 0, 0],
		[6, 0, 0, 0, 5, 0, 3],
		[12, 234, 13, 0, 5, 0, 40],
		[12, 214, 13, 0, 5, 0, 40],
		[12, 202, 13, 0, 5, 0, 40],
		[12, 233, 13, 0, 5, 0, 40],
		[8, 0, 0, 0, 5, 0, 2],
		[29, 0, 17, 0, 5, 0, 0],
		[1, 0, 14, 0, 5, 0, 0],
		[1, 0, 14, 0, 5, 0, 40],
		[1, 0, 0, 0, 5, 0, 0],
		[1, 0, 3, 0, 5, 0, 0],
		[17, 0, 18, 0, 4, 0, 0],
		[17, 0, 18, 0, 5, 0, 0],
		[20, 0, 18, 0, 4, 0, 0],
		[19, 0, 18, 0, 4, 0, 0],
		[22, 0, 18, 0, 5, 0, 0],
		[20, 0, 18, 0, 5, 0, 0],
		[27, 0, 17, 0, 5, 0, 0],
		[28, 0, 15, 0, 5, 0, 0],
		[1, 0, 1, 0, 5, 0, 0],
		[1, 0, 5, 0, 5, 0, 0],
		[1, 0, 7, 0, 5, 0, 0],
		[1, 0, 2, 0, 5, 0, 0],
		[1, 0, 6, 0, 5, 0, 0],
		[21, 0, 10, 0, 4, 0, 0],
		[21, 0, 10, 0, 5, 0, 0],
		[16, 0, 18, 0, 5, 0, 0],
		[25, 0, 12, 0, 5, 0, 0],
		[22, 0, 18, 1, 5, 0, 0],
		[18, 0, 18, 1, 5, 0, 0],
		[25, 0, 18, 0, 5, 0, 0],
		[15, 0, 8, 0, 5, 0, 0],
		[25, 0, 9, 0, 5, 0, 0],
		[6, 0, 0, 0, 4, 0, 1],
		[23, 0, 10, 0, 1, 0, 0],
		[11, 0, 13, 0, 5, 0, 40],
		[9, 0, 0, 0, 5, 0, 0],
		[5, 0, 0, 0, 4, 0, 0],
		[26, 0, 10, 0, 5, 0, 0],
		[25, 0, 18, 1, 5, 0, 0],
		[15, 0, 18, 0, 5, 0, 0],
		[14, 0, 0, 0, 4, 0, 1],
		[14, 0, 0, 0, 5, 0, 1],
		[25, 0, 18, 1, 4, 0, 0],
		[25, 0, 10, 0, 5, 0, 0],
		[22, 0, 18, 1, 2, 0, 0],
		[18, 0, 18, 1, 2, 0, 0],
		[26, 0, 0, 0, 4, 0, 0],
		[26, 0, 0, 0, 5, 0, 52],
		[9, 0, 0, 0, 5, 0, 56],
		[5, 0, 0, 0, 5, 0, 56],
		[26, 0, 18, 0, 5, 0, 54],
		[12, 230, 13, 0, 5, 0, 54],
		[21, 0, 18, 0, 5, 0, 54],
		[15, 0, 18, 0, 5, 0, 54],
		[5, 0, 0, 0, 5, 0, 23],
		[7, 0, 0, 0, 5, 0, 57],
		[6, 0, 0, 0, 5, 0, 57],
		[21, 0, 0, 0, 5, 0, 57],
		[12, 9, 13, 0, 5, 0, 57],
		[26, 0, 18, 0, 2, 0, 35],
		[26, 0, 18, 0, 2, 0, 0],
		[29, 0, 17, 0, 0, 0, 0],
		[21, 0, 18, 0, 2, 0, 0],
		[6, 0, 0, 0, 2, 0, 35],
		[7, 0, 0, 0, 2, 0, 0],
		[14, 0, 0, 0, 2, 0, 35],
		[17, 0, 18, 0, 2, 0, 0],
		[22, 0, 18, 0, 2, 0, 0],
		[18, 0, 18, 0, 2, 0, 0],
		[12, 218, 13, 0, 2, 0, 40],
		[12, 228, 13, 0, 2, 0, 40],
		[12, 232, 13, 0, 2, 0, 40],
		[12, 222, 13, 0, 2, 0, 40],
		[10, 224, 0, 0, 2, 0, 24],
		[6, 0, 0, 0, 2, 0, 0],
		[7, 0, 0, 0, 2, 0, 32],
		[12, 8, 13, 0, 2, 0, 40],
		[24, 0, 18, 0, 2, 0, 0],
		[6, 0, 0, 0, 2, 0, 32],
		[7, 0, 0, 0, 2, 0, 33],
		[6, 0, 0, 0, 2, 0, 33],
		[7, 0, 0, 0, 2, 0, 34],
		[26, 0, 0, 0, 2, 0, 0],
		[15, 0, 0, 0, 2, 0, 0],
		[26, 0, 0, 0, 2, 0, 24],
		[26, 0, 18, 0, 2, 0, 24],
		[15, 0, 0, 0, 4, 0, 0],
		[15, 0, 18, 0, 2, 0, 0],
		[26, 0, 0, 0, 2, 0, 33],
		[7, 0, 0, 0, 2, 0, 35],
		[2, 0, 18, 0, 2, 0, 35],
		[2, 0, 18, 0, 2, 0, 102],
		[7, 0, 0, 0, 2, 0, 36],
		[6, 0, 0, 0, 2, 0, 36],
		[26, 0, 18, 0, 2, 0, 36],
		[7, 0, 0, 0, 5, 0, 82],
		[6, 0, 0, 0, 5, 0, 82],
		[21, 0, 0, 0, 5, 0, 82],
		[7, 0, 0, 0, 5, 0, 69],
		[6, 0, 0, 0, 5, 0, 69],
		[21, 0, 18, 0, 5, 0, 69],
		[13, 0, 0, 0, 5, 0, 69],
		[7, 0, 0, 0, 5, 0, 3],
		[21, 0, 18, 0, 5, 0, 3],
		[6, 0, 18, 0, 5, 0, 3],
		[7, 0, 0, 0, 5, 0, 83],
		[14, 0, 0, 0, 5, 0, 83],
		[12, 230, 13, 0, 5, 0, 83],
		[21, 0, 0, 0, 5, 0, 83],
		[24, 0, 0, 0, 5, 0, 0],
		[7, 0, 0, 0, 5, 0, 58],
		[12, 0, 13, 0, 5, 0, 58],
		[12, 9, 13, 0, 5, 0, 58],
		[10, 0, 0, 0, 5, 0, 58],
		[26, 0, 18, 0, 5, 0, 58],
		[15, 0, 0, 0, 5, 0, 0],
		[7, 0, 0, 0, 5, 0, 64],
		[21, 0, 18, 0, 5, 0, 64],
		[10, 0, 0, 0, 5, 0, 70],
		[7, 0, 0, 0, 5, 0, 70],
		[12, 9, 13, 0, 5, 0, 70],
		[21, 0, 0, 0, 5, 0, 70],
		[13, 0, 0, 0, 5, 0, 70],
		[13, 0, 0, 0, 5, 0, 71],
		[7, 0, 0, 0, 5, 0, 71],
		[12, 0, 13, 0, 5, 0, 71],
		[12, 220, 13, 0, 5, 0, 71],
		[21, 0, 0, 0, 5, 0, 71],
		[7, 0, 0, 0, 5, 0, 72],
		[12, 0, 13, 0, 5, 0, 72],
		[10, 0, 0, 0, 5, 0, 72],
		[10, 9, 0, 0, 5, 0, 72],
		[21, 0, 0, 0, 5, 0, 72],
		[12, 0, 13, 0, 5, 0, 84],
		[10, 0, 0, 0, 5, 0, 84],
		[7, 0, 0, 0, 5, 0, 84],
		[12, 7, 13, 0, 5, 0, 84],
		[10, 9, 0, 0, 5, 0, 84],
		[21, 0, 0, 0, 5, 0, 84],
		[6, 0, 0, 0, 5, 0, 84],
		[13, 0, 0, 0, 5, 0, 84],
		[7, 0, 0, 0, 5, 0, 76],
		[12, 0, 13, 0, 5, 0, 76],
		[10, 0, 0, 0, 5, 0, 76],
		[13, 0, 0, 0, 5, 0, 76],
		[21, 0, 0, 0, 5, 0, 76],
		[6, 0, 0, 0, 5, 0, 22],
		[7, 0, 0, 0, 5, 0, 78],
		[12, 230, 13, 0, 5, 0, 78],
		[12, 220, 13, 0, 5, 0, 78],
		[6, 0, 0, 0, 5, 0, 78],
		[21, 0, 0, 0, 5, 0, 78],
		[7, 0, 0, 0, 5, 0, 85],
		[10, 0, 0, 0, 5, 0, 85],
		[12, 0, 13, 0, 5, 0, 85],
		[21, 0, 0, 0, 5, 0, 85],
		[6, 0, 0, 0, 5, 0, 85],
		[12, 9, 13, 0, 5, 0, 85],
		[13, 0, 0, 0, 5, 0, 85],
		[2, 0, 18, 0, 2, 0, 24],
		[4, 0, 0, 0, 5, 0, 102],
		[3, 0, 0, 0, 4, 0, 102],
		[2, 0, 18, 0, 4, 0, 102],
		[12, 26, 13, 0, 5, 0, 5],
		[25, 0, 9, 0, 5, 0, 5],
		[24, 0, 4, 0, 5, 0, 6],
		[18, 0, 18, 0, 5, 0, 0],
		[16, 0, 18, 0, 2, 0, 0],
		[21, 0, 12, 0, 2, 0, 0],
		[21, 0, 10, 0, 2, 0, 0],
		[25, 0, 9, 0, 2, 0, 0],
		[17, 0, 9, 0, 2, 0, 0],
		[25, 0, 18, 1, 2, 0, 0],
		[25, 0, 18, 0, 2, 0, 0],
		[23, 0, 10, 0, 2, 0, 0],
		[21, 0, 18, 0, 0, 0, 0],
		[21, 0, 10, 0, 0, 0, 0],
		[23, 0, 10, 0, 0, 0, 0],
		[22, 0, 18, 1, 0, 0, 0],
		[18, 0, 18, 1, 0, 0, 0],
		[25, 0, 9, 0, 0, 0, 0],
		[21, 0, 12, 0, 0, 0, 0],
		[17, 0, 9, 0, 0, 0, 0],
		[13, 0, 8, 0, 0, 0, 0],
		[25, 0, 18, 1, 0, 0, 0],
		[25, 0, 18, 0, 0, 0, 0],
		[9, 0, 0, 0, 0, 0, 1],
		[24, 0, 18, 0, 0, 0, 0],
		[16, 0, 18, 0, 0, 0, 0],
		[5, 0, 0, 0, 0, 0, 1],
		[21, 0, 18, 0, 1, 0, 0],
		[22, 0, 18, 1, 1, 0, 0],
		[18, 0, 18, 1, 1, 0, 0],
		[7, 0, 0, 0, 1, 0, 33],
		[6, 0, 0, 0, 1, 0, 0],
		[7, 0, 0, 0, 1, 0, 24],
		[26, 0, 18, 0, 0, 0, 0],
		[26, 0, 18, 0, 1, 0, 0],
		[25, 0, 18, 0, 1, 0, 0],
		[1, 0, 18, 0, 5, 0, 0],
		[7, 0, 0, 0, 5, 0, 47],
		[14, 0, 18, 0, 5, 0, 2],
		[15, 0, 18, 0, 5, 0, 2],
		[26, 0, 18, 0, 5, 0, 2],
		[7, 0, 0, 0, 5, 0, 73],
		[7, 0, 0, 0, 5, 0, 74],
		[7, 0, 0, 0, 5, 0, 37],
		[15, 0, 0, 0, 5, 0, 37],
		[7, 0, 0, 0, 5, 0, 38],
		[14, 0, 0, 0, 5, 0, 38],
		[7, 0, 0, 0, 5, 0, 48],
		[21, 0, 0, 0, 5, 0, 48],
		[7, 0, 0, 0, 5, 0, 59],
		[21, 0, 0, 0, 5, 0, 59],
		[14, 0, 0, 0, 5, 0, 59],
		[9, 0, 0, 0, 5, 0, 39],
		[5, 0, 0, 0, 5, 0, 39],
		[7, 0, 0, 0, 5, 0, 49],
		[7, 0, 0, 0, 5, 0, 50],
		[13, 0, 0, 0, 5, 0, 50],
		[7, 0, 3, 0, 5, 0, 51],
		[7, 0, 3, 0, 5, 0, 86],
		[21, 0, 3, 0, 5, 0, 86],
		[15, 0, 3, 0, 5, 0, 86],
		[7, 0, 3, 0, 5, 0, 63],
		[15, 0, 3, 0, 5, 0, 63],
		[21, 0, 18, 0, 5, 0, 63],
		[7, 0, 3, 0, 5, 0, 75],
		[21, 0, 3, 0, 5, 0, 75],
		[7, 0, 3, 0, 5, 0, 97],
		[7, 0, 3, 0, 5, 0, 96],
		[7, 0, 3, 0, 5, 0, 60],
		[12, 0, 13, 0, 5, 0, 60],
		[12, 220, 13, 0, 5, 0, 60],
		[12, 230, 13, 0, 5, 0, 60],
		[12, 1, 13, 0, 5, 0, 60],
		[12, 9, 13, 0, 5, 0, 60],
		[15, 0, 3, 0, 5, 0, 60],
		[21, 0, 3, 0, 5, 0, 60],
		[7, 0, 3, 0, 5, 0, 87],
		[15, 0, 3, 0, 5, 0, 87],
		[21, 0, 3, 0, 5, 0, 87],
		[7, 0, 3, 0, 5, 0, 79],
		[21, 0, 18, 0, 5, 0, 79],
		[7, 0, 3, 0, 5, 0, 88],
		[15, 0, 3, 0, 5, 0, 88],
		[7, 0, 3, 0, 5, 0, 89],
		[15, 0, 3, 0, 5, 0, 89],
		[7, 0, 3, 0, 5, 0, 90],
		[15, 0, 11, 0, 5, 0, 6],
		[10, 0, 0, 0, 5, 0, 93],
		[12, 0, 13, 0, 5, 0, 93],
		[7, 0, 0, 0, 5, 0, 93],
		[12, 9, 13, 0, 5, 0, 93],
		[21, 0, 0, 0, 5, 0, 93],
		[15, 0, 18, 0, 5, 0, 93],
		[13, 0, 0, 0, 5, 0, 93],
		[12, 0, 13, 0, 5, 0, 91],
		[10, 0, 0, 0, 5, 0, 91],
		[7, 0, 0, 0, 5, 0, 91],
		[12, 9, 13, 0, 5, 0, 91],
		[12, 7, 13, 0, 5, 0, 91],
		[21, 0, 0, 0, 5, 0, 91],
		[1, 0, 0, 0, 5, 0, 91],
		[7, 0, 0, 0, 5, 0, 100],
		[13, 0, 0, 0, 5, 0, 100],
		[12, 230, 13, 0, 5, 0, 95],
		[7, 0, 0, 0, 5, 0, 95],
		[12, 0, 13, 0, 5, 0, 95],
		[10, 0, 0, 0, 5, 0, 95],
		[12, 9, 13, 0, 5, 0, 95],
		[13, 0, 0, 0, 5, 0, 95],
		[21, 0, 0, 0, 5, 0, 95],
		[12, 0, 13, 0, 5, 0, 99],
		[10, 0, 0, 0, 5, 0, 99],
		[7, 0, 0, 0, 5, 0, 99],
		[10, 9, 0, 0, 5, 0, 99],
		[21, 0, 0, 0, 5, 0, 99],
		[13, 0, 0, 0, 5, 0, 99],
		[7, 0, 0, 0, 5, 0, 101],
		[12, 0, 13, 0, 5, 0, 101],
		[10, 0, 0, 0, 5, 0, 101],
		[10, 9, 0, 0, 5, 0, 101],
		[12, 7, 13, 0, 5, 0, 101],
		[13, 0, 0, 0, 5, 0, 101],
		[7, 0, 0, 0, 5, 0, 62],
		[14, 0, 0, 0, 5, 0, 62],
		[21, 0, 0, 0, 5, 0, 62],
		[7, 0, 0, 0, 5, 0, 80],
		[7, 0, 0, 0, 5, 0, 98],
		[10, 0, 0, 0, 5, 0, 98],
		[12, 0, 13, 0, 5, 0, 98],
		[6, 0, 0, 0, 5, 0, 98],
		[10, 216, 0, 0, 5, 0, 0],
		[10, 226, 0, 0, 5, 0, 0],
		[12, 230, 13, 0, 5, 0, 2],
		[25, 0, 0, 0, 5, 0, 0],
		[13, 0, 8, 0, 5, 0, 0],
		[26, 0, 0, 0, 2, 0, 32],
	];

	/* Mirror unicode characters. Bidirectional Algorithm, at http://www.unicode.org/unicode/reports/tr9/  */

	public static $mirror_pairs = [
		40 => 41,
		41 => 40,
		60 => 62,
		62 => 60,
		91 => 93,
		93 => 91,
		123 => 125,
		125 => 123,
		171 => 187,
		187 => 171,
		3898 => 3899,
		3899 => 3898,
		3900 => 3901,
		3901 => 3900,
		5787 => 5788,
		5788 => 5787,
		8249 => 8250,
		8250 => 8249,
		8261 => 8262,
		8262 => 8261,
		8317 => 8318,
		8318 => 8317,
		8333 => 8334,
		8334 => 8333,
		8712 => 8715,
		8713 => 8716,
		8714 => 8717,
		8715 => 8712,
		8716 => 8713,
		8717 => 8714,
		8725 => 10741,
		8764 => 8765,
		8765 => 8764,
		8771 => 8909,
		8786 => 8787,
		8787 => 8786,
		8788 => 8789,
		8789 => 8788,
		8804 => 8805,
		8805 => 8804,
		8806 => 8807,
		8807 => 8806,
		8808 => 8809,
		8809 => 8808,
		8810 => 8811,
		8811 => 8810,
		8814 => 8815,
		8815 => 8814,
		8816 => 8817,
		8817 => 8816,
		8818 => 8819,
		8819 => 8818,
		8820 => 8821,
		8821 => 8820,
		8822 => 8823,
		8823 => 8822,
		8824 => 8825,
		8825 => 8824,
		8826 => 8827,
		8827 => 8826,
		8828 => 8829,
		8829 => 8828,
		8830 => 8831,
		8831 => 8830,
		8832 => 8833,
		8833 => 8832,
		8834 => 8835,
		8835 => 8834,
		8836 => 8837,
		8837 => 8836,
		8838 => 8839,
		8839 => 8838,
		8840 => 8841,
		8841 => 8840,
		8842 => 8843,
		8843 => 8842,
		8847 => 8848,
		8848 => 8847,
		8849 => 8850,
		8850 => 8849,
		8856 => 10680,
		8866 => 8867,
		8867 => 8866,
		8870 => 10974,
		8872 => 10980,
		8873 => 10979,
		8875 => 10981,
		8880 => 8881,
		8881 => 8880,
		8882 => 8883,
		8883 => 8882,
		8884 => 8885,
		8885 => 8884,
		8886 => 8887,
		8887 => 8886,
		8905 => 8906,
		8906 => 8905,
		8907 => 8908,
		8908 => 8907,
		8909 => 8771,
		8912 => 8913,
		8913 => 8912,
		8918 => 8919,
		8919 => 8918,
		8920 => 8921,
		8921 => 8920,
		8922 => 8923,
		8923 => 8922,
		8924 => 8925,
		8925 => 8924,
		8926 => 8927,
		8927 => 8926,
		8928 => 8929,
		8929 => 8928,
		8930 => 8931,
		8931 => 8930,
		8932 => 8933,
		8933 => 8932,
		8934 => 8935,
		8935 => 8934,
		8936 => 8937,
		8937 => 8936,
		8938 => 8939,
		8939 => 8938,
		8940 => 8941,
		8941 => 8940,
		8944 => 8945,
		8945 => 8944,
		8946 => 8954,
		8947 => 8955,
		8948 => 8956,
		8950 => 8957,
		8951 => 8958,
		8954 => 8946,
		8955 => 8947,
		8956 => 8948,
		8957 => 8950,
		8958 => 8951,
		8968 => 8969,
		8969 => 8968,
		8970 => 8971,
		8971 => 8970,
		9001 => 9002,
		9002 => 9001,
		10088 => 10089,
		10089 => 10088,
		10090 => 10091,
		10091 => 10090,
		10092 => 10093,
		10093 => 10092,
		10094 => 10095,
		10095 => 10094,
		10096 => 10097,
		10097 => 10096,
		10098 => 10099,
		10099 => 10098,
		10100 => 10101,
		10101 => 10100,
		10179 => 10180,
		10180 => 10179,
		10181 => 10182,
		10182 => 10181,
		10184 => 10185,
		10185 => 10184,
		10187 => 10189,
		10189 => 10187,
		10197 => 10198,
		10198 => 10197,
		10205 => 10206,
		10206 => 10205,
		10210 => 10211,
		10211 => 10210,
		10212 => 10213,
		10213 => 10212,
		10214 => 10215,
		10215 => 10214,
		10216 => 10217,
		10217 => 10216,
		10218 => 10219,
		10219 => 10218,
		10220 => 10221,
		10221 => 10220,
		10222 => 10223,
		10223 => 10222,
		10627 => 10628,
		10628 => 10627,
		10629 => 10630,
		10630 => 10629,
		10631 => 10632,
		10632 => 10631,
		10633 => 10634,
		10634 => 10633,
		10635 => 10636,
		10636 => 10635,
		10637 => 10640,
		10638 => 10639,
		10639 => 10638,
		10640 => 10637,
		10641 => 10642,
		10642 => 10641,
		10643 => 10644,
		10644 => 10643,
		10645 => 10646,
		10646 => 10645,
		10647 => 10648,
		10648 => 10647,
		10680 => 8856,
		10688 => 10689,
		10689 => 10688,
		10692 => 10693,
		10693 => 10692,
		10703 => 10704,
		10704 => 10703,
		10705 => 10706,
		10706 => 10705,
		10708 => 10709,
		10709 => 10708,
		10712 => 10713,
		10713 => 10712,
		10714 => 10715,
		10715 => 10714,
		10741 => 8725,
		10744 => 10745,
		10745 => 10744,
		10748 => 10749,
		10749 => 10748,
		10795 => 10796,
		10796 => 10795,
		10797 => 10798,
		10798 => 10797,
		10804 => 10805,
		10805 => 10804,
		10812 => 10813,
		10813 => 10812,
		10852 => 10853,
		10853 => 10852,
		10873 => 10874,
		10874 => 10873,
		10877 => 10878,
		10878 => 10877,
		10879 => 10880,
		10880 => 10879,
		10881 => 10882,
		10882 => 10881,
		10883 => 10884,
		10884 => 10883,
		10891 => 10892,
		10892 => 10891,
		10897 => 10898,
		10898 => 10897,
		10899 => 10900,
		10900 => 10899,
		10901 => 10902,
		10902 => 10901,
		10903 => 10904,
		10904 => 10903,
		10905 => 10906,
		10906 => 10905,
		10907 => 10908,
		10908 => 10907,
		10913 => 10914,
		10914 => 10913,
		10918 => 10919,
		10919 => 10918,
		10920 => 10921,
		10921 => 10920,
		10922 => 10923,
		10923 => 10922,
		10924 => 10925,
		10925 => 10924,
		10927 => 10928,
		10928 => 10927,
		10931 => 10932,
		10932 => 10931,
		10939 => 10940,
		10940 => 10939,
		10941 => 10942,
		10942 => 10941,
		10943 => 10944,
		10944 => 10943,
		10945 => 10946,
		10946 => 10945,
		10947 => 10948,
		10948 => 10947,
		10949 => 10950,
		10950 => 10949,
		10957 => 10958,
		10958 => 10957,
		10959 => 10960,
		10960 => 10959,
		10961 => 10962,
		10962 => 10961,
		10963 => 10964,
		10964 => 10963,
		10965 => 10966,
		10966 => 10965,
		10974 => 8870,
		10979 => 8873,
		10980 => 8872,
		10981 => 8875,
		10988 => 10989,
		10989 => 10988,
		10999 => 11000,
		11000 => 10999,
		11001 => 11002,
		11002 => 11001,
		11778 => 11779,
		11779 => 11778,
		11780 => 11781,
		11781 => 11780,
		11785 => 11786,
		11786 => 11785,
		11788 => 11789,
		11789 => 11788,
		11804 => 11805,
		11805 => 11804,
		11808 => 11809,
		11809 => 11808,
		11810 => 11811,
		11811 => 11810,
		11812 => 11813,
		11813 => 11812,
		11814 => 11815,
		11815 => 11814,
		11816 => 11817,
		11817 => 11816,
		12296 => 12297,
		12297 => 12296,
		12298 => 12299,
		12299 => 12298,
		12300 => 12301,
		12301 => 12300,
		12302 => 12303,
		12303 => 12302,
		12304 => 12305,
		12305 => 12304,
		12308 => 12309,
		12309 => 12308,
		12310 => 12311,
		12311 => 12310,
		12312 => 12313,
		12313 => 12312,
		12314 => 12315,
		12315 => 12314,
		65113 => 65114,
		65114 => 65113,
		65115 => 65116,
		65116 => 65115,
		65117 => 65118,
		65118 => 65117,
		65124 => 65125,
		65125 => 65124,
		65288 => 65289,
		65289 => 65288,
		65308 => 65310,
		65310 => 65308,
		65339 => 65341,
		65341 => 65339,
		65371 => 65373,
		65373 => 65371,
		65375 => 65376,
		65376 => 65375,
		65378 => 65379,
		65379 => 65378,
	];


	/* index tables for the database records */

	private static $index0 = [
		0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
		21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38,
		39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 54, 52, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 55, 56, 57, 57, 57, 58,
		59, 60, 61, 62, 63, 64, 65, 66, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67,
		67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67,
		67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 68, 69, 70, 70,
		71, 69, 70, 70, 72, 73, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 75, 76, 77, 78, 79, 80, 81,
		82, 83, 84, 85, 86, 87, 70, 70, 70, 88, 89, 90, 91, 92, 70, 93, 70, 94,
		95, 70, 70, 70, 70, 96, 70, 70, 70, 70, 70, 70, 70, 70, 70, 97, 97, 97,
		98, 99, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 100, 100, 100, 100,
		101, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 102, 102,
		103, 70, 70, 70, 70, 104, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 105, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 106, 107, 108, 109, 110,
		111, 112, 113, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 114, 70, 115, 116, 117, 118, 119, 120,
		121, 122, 70, 70, 70, 70, 70, 70, 70, 70, 52, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 123, 52, 53, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 124, 125, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 76, 76, 127, 126, 126, 126, 126, 128, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126, 126,
		126, 128, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 129, 130, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 70,
		70, 70, 70, 70, 70, 70, 70, 70, 70, 70, 73, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 131, 73, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74,
		74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 74, 131,
	];

	private static $index1 = [
		0, 1, 0, 2, 3, 4, 5, 6, 7, 8, 8, 9, 10, 11, 11, 12, 13, 0, 0, 0, 14, 15,
		16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 29, 31, 32,
		33, 34, 35, 27, 30, 29, 27, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46,
		47, 48, 27, 27, 49, 27, 27, 27, 27, 27, 27, 27, 50, 51, 52, 27, 53, 54,
		53, 54, 54, 54, 54, 54, 55, 54, 54, 54, 56, 57, 58, 59, 60, 61, 62, 63,
		64, 64, 65, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 65, 77, 78,
		79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96,
		97, 97, 97, 97, 98, 98, 98, 98, 99, 100, 101, 101, 101, 101, 102, 103,
		101, 101, 101, 101, 101, 101, 104, 105, 101, 101, 101, 101, 101, 101,
		101, 101, 101, 101, 101, 106, 107, 108, 108, 108, 109, 110, 111, 112,
		112, 112, 112, 113, 114, 115, 116, 117, 118, 119, 120, 106, 121, 121,
		121, 122, 123, 106, 124, 125, 126, 127, 128, 128, 128, 128, 129, 130,
		131, 132, 133, 134, 135, 128, 128, 128, 128, 128, 128, 128, 128, 128,
		128, 128, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 145, 145,
		146, 147, 148, 149, 128, 128, 128, 128, 128, 128, 150, 150, 150, 150,
		151, 152, 153, 106, 154, 155, 156, 156, 156, 157, 158, 159, 160, 160,
		161, 162, 163, 164, 165, 166, 167, 167, 167, 168, 106, 106, 106, 106,
		106, 106, 106, 106, 169, 170, 106, 106, 106, 106, 106, 106, 171, 172,
		173, 174, 175, 176, 176, 176, 176, 176, 176, 177, 178, 179, 180, 176,
		181, 182, 183, 184, 185, 186, 187, 188, 188, 189, 190, 191, 192, 193,
		194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 203, 204, 205, 206,
		207, 208, 209, 210, 211, 212, 213, 106, 214, 215, 216, 217, 217, 218,
		219, 220, 221, 222, 223, 106, 224, 225, 226, 106, 227, 228, 229, 230,
		230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 106, 241, 242,
		243, 244, 245, 242, 246, 247, 248, 249, 250, 106, 251, 252, 253, 254,
		255, 256, 257, 258, 258, 257, 259, 260, 261, 262, 263, 264, 265, 266,
		106, 267, 268, 269, 270, 271, 271, 270, 272, 273, 274, 275, 276, 277,
		278, 279, 280, 106, 281, 282, 283, 284, 284, 284, 284, 285, 286, 287,
		288, 106, 289, 290, 291, 292, 293, 294, 295, 296, 294, 294, 297, 298,
		295, 299, 300, 301, 106, 106, 302, 106, 303, 304, 304, 304, 304, 304,
		305, 306, 307, 308, 309, 310, 106, 106, 106, 106, 311, 312, 313, 314,
		315, 316, 317, 318, 319, 320, 321, 322, 106, 106, 106, 106, 323, 324,
		325, 326, 327, 328, 329, 330, 331, 332, 331, 331, 331, 333, 334, 335,
		336, 337, 338, 339, 338, 338, 338, 340, 341, 342, 343, 344, 106, 106,
		106, 106, 345, 345, 345, 345, 345, 346, 347, 348, 349, 350, 351, 352,
		353, 354, 355, 345, 356, 357, 349, 358, 359, 359, 359, 359, 360, 361,
		362, 362, 362, 362, 362, 363, 364, 364, 364, 364, 364, 364, 364, 364,
		364, 364, 364, 364, 365, 365, 365, 365, 365, 365, 365, 365, 365, 365,
		365, 365, 365, 365, 365, 365, 365, 365, 365, 365, 366, 366, 366, 366,
		366, 366, 366, 366, 366, 367, 368, 367, 366, 366, 366, 366, 366, 367,
		366, 366, 366, 366, 367, 368, 367, 366, 368, 366, 366, 366, 366, 366,
		366, 366, 367, 366, 366, 366, 366, 366, 366, 366, 366, 369, 370, 371,
		372, 373, 366, 366, 374, 375, 376, 376, 376, 376, 376, 376, 376, 376,
		376, 376, 377, 106, 378, 379, 379, 379, 379, 379, 379, 379, 379, 379,
		379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379,
		379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379,
		379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379,
		379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379,
		379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 379, 380, 379, 379,
		381, 382, 382, 383, 384, 384, 384, 384, 384, 384, 384, 384, 384, 385,
		386, 106, 387, 388, 389, 106, 390, 390, 391, 106, 392, 392, 393, 106,
		394, 395, 396, 106, 397, 397, 397, 397, 397, 397, 398, 399, 400, 401,
		402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 412, 412, 412,
		413, 412, 412, 412, 412, 412, 412, 106, 412, 412, 412, 412, 412, 414,
		379, 379, 379, 379, 379, 379, 379, 379, 415, 106, 416, 416, 416, 417,
		418, 419, 420, 421, 422, 423, 424, 424, 424, 425, 426, 106, 427, 427,
		427, 427, 427, 428, 429, 429, 430, 431, 432, 433, 434, 434, 434, 434,
		435, 435, 436, 437, 438, 438, 438, 438, 438, 438, 439, 440, 441, 442,
		443, 444, 445, 446, 445, 446, 447, 448, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 449, 450, 450, 450, 450, 450, 451, 452, 453, 454,
		455, 456, 457, 458, 459, 460, 461, 462, 462, 462, 463, 464, 465, 466,
		467, 467, 467, 467, 468, 469, 470, 471, 472, 472, 472, 472, 473, 474,
		475, 476, 477, 478, 479, 480, 481, 481, 481, 482, 106, 106, 106, 106,
		106, 106, 106, 106, 483, 106, 484, 485, 486, 487, 488, 106, 54, 54, 54,
		54, 489, 490, 56, 56, 56, 56, 56, 491, 492, 493, 54, 494, 54, 54, 54,
		495, 56, 56, 56, 496, 497, 498, 499, 500, 501, 106, 106, 502, 27, 27, 27,
		27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 503, 504, 27,
		27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 27, 505, 506, 507, 508, 505, 506,
		505, 506, 507, 508, 505, 509, 505, 506, 505, 507, 505, 510, 505, 510,
		505, 510, 511, 512, 513, 514, 515, 516, 505, 517, 518, 519, 520, 521,
		522, 523, 524, 525, 526, 527, 528, 529, 530, 531, 532, 533, 534, 535,
		536, 537, 56, 538, 539, 540, 539, 541, 106, 106, 542, 543, 544, 545, 546,
		106, 547, 548, 549, 550, 551, 552, 553, 554, 555, 556, 557, 558, 559,
		560, 559, 561, 562, 563, 564, 565, 566, 567, 568, 569, 568, 570, 571,
		568, 572, 568, 573, 574, 575, 576, 577, 578, 579, 580, 581, 582, 583,
		584, 585, 586, 587, 588, 583, 583, 589, 590, 591, 592, 593, 583, 583,
		594, 574, 595, 596, 583, 583, 597, 583, 583, 568, 598, 599, 568, 600,
		601, 602, 603, 603, 603, 603, 603, 603, 603, 603, 604, 568, 568, 605,
		606, 574, 574, 607, 568, 568, 568, 568, 573, 608, 568, 609, 106, 568,
		568, 568, 568, 610, 106, 106, 106, 568, 611, 106, 106, 612, 612, 612,
		612, 612, 613, 613, 614, 615, 615, 615, 615, 615, 615, 615, 615, 615,
		616, 612, 612, 617, 617, 617, 617, 617, 617, 617, 617, 617, 618, 617,
		617, 617, 617, 618, 568, 617, 617, 619, 568, 620, 569, 621, 622, 623,
		624, 569, 568, 619, 572, 568, 574, 625, 626, 622, 627, 568, 568, 568,
		568, 628, 568, 568, 568, 629, 630, 568, 568, 568, 568, 568, 631, 568,
		632, 568, 631, 633, 634, 617, 617, 635, 617, 617, 617, 636, 568, 568,
		568, 568, 568, 568, 637, 568, 568, 572, 568, 568, 638, 639, 612, 640,
		640, 641, 568, 568, 568, 568, 568, 642, 643, 644, 645, 646, 647, 574,
		574, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648,
		648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648,
		648, 648, 648, 648, 648, 574, 574, 574, 574, 574, 574, 574, 574, 574,
		574, 574, 574, 574, 574, 574, 574, 649, 650, 650, 651, 583, 583, 574,
		652, 597, 653, 654, 655, 656, 657, 658, 659, 574, 660, 583, 661, 662,
		663, 664, 645, 574, 574, 586, 652, 664, 665, 666, 667, 583, 583, 583,
		583, 668, 669, 583, 583, 583, 583, 670, 671, 672, 645, 673, 674, 568,
		568, 568, 568, 568, 568, 574, 574, 675, 676, 677, 678, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 679, 679, 679, 679, 679, 680, 681, 681, 681, 681, 681,
		682, 683, 684, 685, 686, 92, 92, 92, 92, 92, 92, 92, 92, 92, 92, 92, 92,
		687, 688, 689, 690, 691, 691, 691, 691, 692, 693, 694, 694, 694, 694,
		694, 694, 694, 695, 696, 697, 366, 366, 368, 106, 368, 368, 368, 368,
		368, 368, 368, 368, 698, 698, 698, 698, 699, 700, 701, 702, 703, 704,
		529, 705, 106, 106, 106, 106, 106, 106, 106, 106, 706, 706, 706, 707,
		706, 706, 706, 706, 706, 706, 706, 706, 706, 706, 708, 106, 706, 706,
		706, 706, 706, 706, 706, 706, 706, 706, 706, 706, 706, 706, 706, 706,
		706, 706, 706, 706, 706, 706, 706, 706, 706, 706, 709, 106, 106, 106,
		710, 711, 712, 713, 714, 715, 716, 717, 718, 719, 720, 721, 721, 721,
		721, 721, 721, 721, 721, 721, 722, 723, 724, 725, 725, 725, 725, 725,
		725, 725, 725, 725, 725, 726, 727, 728, 728, 728, 728, 729, 730, 364,
		364, 364, 364, 364, 364, 364, 364, 364, 364, 731, 732, 733, 728, 728,
		728, 734, 710, 710, 710, 710, 711, 106, 725, 725, 735, 735, 735, 736,
		737, 738, 733, 733, 733, 739, 740, 741, 735, 735, 735, 742, 737, 738,
		733, 733, 733, 733, 743, 741, 733, 744, 745, 745, 745, 745, 745, 746,
		745, 745, 745, 745, 745, 745, 745, 745, 745, 745, 745, 733, 733, 733,
		747, 748, 733, 733, 733, 733, 733, 733, 733, 733, 733, 733, 733, 749,
		733, 733, 733, 747, 750, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 752, 753, 568, 568, 568, 568, 568, 568,
		568, 568, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 754,
		753, 753, 753, 753, 753, 753, 755, 755, 756, 755, 755, 755, 755, 755,
		755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755,
		755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755,
		755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755,
		755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755,
		755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755, 755,
		755, 755, 755, 757, 758, 758, 758, 758, 758, 758, 759, 106, 760, 760,
		760, 760, 760, 761, 762, 762, 762, 762, 762, 762, 762, 762, 762, 762,
		762, 762, 762, 762, 762, 762, 762, 762, 762, 762, 762, 762, 762, 762,
		762, 762, 762, 762, 762, 762, 762, 762, 762, 763, 762, 762, 764, 765,
		106, 106, 101, 101, 101, 101, 101, 766, 767, 768, 101, 101, 101, 769,
		770, 770, 770, 770, 770, 770, 770, 770, 771, 772, 773, 106, 64, 64, 774,
		775, 776, 27, 777, 27, 27, 27, 27, 27, 27, 27, 778, 779, 27, 780, 781,
		106, 27, 782, 106, 106, 106, 106, 106, 106, 106, 106, 106, 783, 784, 785,
		786, 786, 787, 788, 789, 790, 791, 791, 791, 791, 791, 791, 792, 106,
		793, 794, 794, 794, 794, 794, 795, 796, 797, 798, 799, 800, 801, 801,
		802, 803, 804, 805, 806, 806, 807, 808, 809, 809, 810, 811, 812, 813,
		364, 364, 364, 814, 815, 816, 816, 816, 816, 816, 817, 818, 819, 820,
		821, 822, 106, 106, 106, 106, 823, 823, 823, 823, 823, 824, 825, 106,
		826, 827, 828, 829, 345, 345, 830, 831, 832, 832, 832, 832, 832, 832,
		833, 834, 835, 106, 106, 836, 837, 838, 839, 106, 840, 840, 840, 106,
		368, 368, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 837, 837, 837, 837, 841, 842, 843, 844,
		845, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846,
		846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846,
		846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846,
		846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846,
		846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846,
		846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846, 846,
		847, 106, 365, 365, 848, 849, 365, 365, 365, 365, 365, 850, 851, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 852, 851, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 852, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 852,
		853, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 855, 856, 856,
		856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856,
		856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856,
		856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856,
		856, 857, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856, 856,
		856, 858, 753, 753, 753, 753, 859, 106, 860, 861, 121, 862, 863, 864,
		865, 121, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128,
		866, 867, 868, 106, 869, 128, 128, 128, 128, 128, 128, 128, 128, 128,
		128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128,
		128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128,
		128, 128, 128, 128, 128, 128, 128, 870, 106, 106, 128, 128, 128, 128,
		128, 128, 128, 128, 871, 128, 128, 128, 128, 128, 128, 106, 106, 106,
		106, 106, 128, 872, 873, 873, 874, 875, 501, 106, 876, 877, 878, 879,
		880, 881, 882, 883, 884, 128, 128, 128, 128, 128, 128, 128, 128, 128,
		128, 128, 128, 128, 128, 128, 128, 885, 886, 887, 888, 889, 890, 891,
		891, 892, 893, 894, 894, 895, 896, 897, 898, 897, 897, 897, 897, 899,
		900, 900, 900, 901, 902, 902, 902, 903, 904, 905, 106, 906, 907, 908,
		907, 907, 909, 907, 907, 910, 907, 911, 907, 911, 106, 106, 106, 106,
		907, 907, 907, 907, 907, 907, 907, 907, 907, 907, 907, 907, 907, 907,
		907, 912, 913, 914, 914, 914, 914, 914, 915, 603, 916, 916, 916, 916,
		916, 916, 917, 918, 919, 920, 568, 609, 106, 106, 106, 106, 106, 106,
		603, 603, 603, 603, 603, 921, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 922, 922, 922, 923, 924, 924,
		924, 924, 924, 924, 925, 106, 106, 106, 106, 106, 926, 926, 926, 927,
		928, 106, 929, 929, 930, 931, 106, 106, 106, 106, 106, 106, 932, 932,
		932, 933, 934, 934, 934, 934, 935, 934, 936, 106, 106, 106, 106, 106,
		937, 937, 937, 937, 937, 938, 938, 938, 938, 938, 939, 939, 939, 939,
		939, 939, 940, 940, 940, 941, 942, 943, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 944, 945, 946, 946, 946, 946, 947, 948, 949, 949,
		950, 951, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 952, 952, 953, 954, 955, 955,
		955, 956, 106, 106, 106, 106, 106, 106, 106, 106, 957, 957, 957, 957,
		958, 958, 958, 959, 106, 106, 106, 106, 106, 106, 106, 106, 960, 961,
		962, 963, 964, 964, 965, 966, 967, 106, 968, 969, 970, 970, 970, 971,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 972, 972, 972, 972, 972, 972, 973, 974, 975, 975, 976, 977,
		978, 978, 979, 980, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 981, 981, 981, 981, 981, 981, 981, 981,
		981, 982, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 983, 983, 983, 984, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		985, 986, 986, 986, 986, 986, 986, 987, 988, 989, 990, 991, 992, 993,
		106, 106, 994, 995, 995, 995, 995, 995, 996, 997, 998, 106, 999, 999,
		999, 1000, 1001, 1002, 1003, 1004, 1004, 1004, 1005, 1006, 1007, 1008,
		1009, 106, 106, 106, 106, 106, 106, 106, 1010, 1011, 1011, 1011, 1011,
		1011, 1012, 1013, 1014, 1015, 1016, 1017, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		1018, 1018, 1018, 1018, 1018, 1019, 1020, 106, 1021, 1022, 106, 106, 106,
		106, 106, 106, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023,
		1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023,
		1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023,
		1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023, 1023,
		1024, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 1025, 1025, 1025, 1025, 1025, 1025, 1025, 1025,
		1025, 1025, 1025, 1025, 1026, 106, 1027, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 1028, 1028, 1028,
		1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028,
		1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028,
		1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1028, 1029, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 770, 770, 770,
		770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770,
		770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770, 770,
		770, 770, 770, 770, 770, 770, 770, 770, 1030, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 1031, 1031, 1031, 1031, 1031, 1031, 1031, 1031,
		1032, 106, 1033, 1034, 1034, 1034, 1034, 1035, 106, 1036, 1037, 1038,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 1039, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603,
		603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603,
		603, 603, 603, 603, 1040, 106, 603, 603, 603, 603, 1041, 1042, 603, 603,
		603, 603, 603, 603, 1043, 1044, 1045, 1046, 1047, 1048, 603, 603, 603,
		1049, 603, 603, 603, 603, 603, 1040, 106, 106, 106, 106, 919, 919, 919,
		919, 919, 919, 919, 919, 1050, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 568, 568, 568, 568, 568, 568, 568, 568, 568, 568, 610, 106, 914,
		914, 1051, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 1052, 1052, 1052, 1053, 1054, 1054, 1055, 1052,
		1052, 1056, 1057, 1054, 1054, 1052, 1052, 1052, 1053, 1054, 1054, 1058,
		1059, 1060, 1056, 1061, 1062, 1054, 1052, 1052, 1052, 1053, 1054, 1054,
		1063, 1064, 1065, 1066, 1054, 1054, 1054, 1067, 1068, 1069, 1070, 1054,
		1054, 1055, 1052, 1052, 1056, 1054, 1054, 1054, 1052, 1052, 1052, 1053,
		1054, 1054, 1055, 1052, 1052, 1056, 1054, 1054, 1054, 1052, 1052, 1052,
		1053, 1054, 1054, 1055, 1052, 1052, 1056, 1054, 1054, 1054, 1052, 1052,
		1052, 1053, 1054, 1054, 1071, 1052, 1052, 1052, 1072, 1054, 1054, 1073,
		1074, 1052, 1052, 1075, 1054, 1054, 1076, 1055, 1052, 1052, 1077, 1054,
		1054, 1078, 1079, 1052, 1052, 1080, 1054, 1054, 1054, 1081, 1052, 1052,
		1052, 1072, 1054, 1054, 1073, 1082, 1083, 1083, 1083, 1083, 1083, 1083,
		1084, 128, 128, 128, 1085, 1086, 1087, 1088, 1089, 1090, 1085, 1091,
		1085, 1087, 1087, 1092, 128, 1093, 128, 1094, 1095, 1093, 128, 1094, 106,
		106, 106, 106, 106, 106, 1096, 106, 568, 568, 568, 568, 568, 609, 568,
		568, 568, 568, 568, 568, 568, 568, 568, 568, 568, 568, 609, 106, 568,
		610, 636, 610, 636, 568, 636, 568, 106, 106, 106, 106, 613, 1097, 615,
		615, 615, 1098, 615, 615, 615, 615, 615, 615, 615, 1099, 615, 615, 615,
		615, 615, 1100, 106, 106, 106, 106, 106, 106, 106, 106, 1101, 603, 603,
		603, 1102, 106, 733, 733, 733, 733, 733, 1103, 733, 1104, 1105, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 568, 568, 568, 568, 1106, 106, 1107, 568, 568,
		568, 568, 568, 568, 568, 568, 1108, 568, 568, 609, 106, 568, 568, 568,
		568, 1109, 611, 106, 106, 568, 568, 1106, 106, 568, 568, 568, 568, 568,
		568, 568, 610, 1110, 568, 568, 568, 568, 568, 568, 568, 568, 568, 568,
		568, 568, 568, 568, 568, 568, 568, 568, 568, 568, 568, 568, 1111, 568,
		568, 568, 568, 568, 568, 568, 1112, 609, 106, 568, 568, 568, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		106, 106, 1113, 568, 568, 568, 568, 568, 568, 568, 568, 1114, 568, 106,
		106, 106, 106, 106, 106, 568, 568, 568, 568, 568, 568, 568, 568, 1112,
		106, 106, 106, 106, 106, 106, 106, 568, 568, 568, 568, 568, 568, 568,
		568, 568, 568, 568, 568, 568, 568, 609, 106, 106, 106, 106, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 1115, 753, 753, 753, 753,
		753, 751, 751, 751, 751, 751, 751, 754, 753, 750, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751, 751,
		751, 751, 751, 751, 751, 751, 751, 751, 752, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 856,
		856, 856, 857, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753, 753,
		753, 753, 753, 753, 753, 753, 1116, 1117, 106, 106, 106, 1118, 1118,
		1118, 1118, 1118, 1118, 1118, 1118, 1118, 1118, 1118, 1118, 106, 106,
		106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106, 106,
		873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873,
		873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873, 873,
		873, 873, 106, 106, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854, 854,
		854, 854, 854, 854, 854, 854, 854, 1119,
	];

	private static $index2 = [
		1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 3, 2, 4, 3, 1, 1, 1, 1, 1, 1, 3, 3, 3, 2,
		5, 6, 6, 7, 8, 7, 6, 6, 9, 10, 6, 11, 12, 13, 12, 12, 14, 14, 14, 14, 14,
		14, 14, 14, 14, 14, 12, 6, 15, 16, 15, 6, 6, 17, 17, 17, 17, 17, 17, 17,
		17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 17, 9, 6, 10, 18, 19, 18, 20, 20,
		20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 9, 16,
		10, 16, 1, 1, 1, 1, 1, 1, 3, 1, 1, 21, 22, 8, 8, 23, 8, 24, 22, 25, 26,
		27, 28, 16, 29, 30, 18, 31, 32, 33, 33, 25, 34, 22, 22, 25, 33, 27, 35,
		36, 36, 36, 22, 37, 37, 37, 37, 37, 37, 38, 37, 37, 37, 37, 37, 37, 37,
		37, 37, 38, 37, 37, 37, 37, 37, 37, 39, 38, 37, 37, 37, 37, 37, 38, 40,
		40, 40, 41, 41, 41, 41, 40, 41, 40, 40, 40, 41, 40, 40, 41, 41, 40, 41,
		40, 40, 41, 41, 41, 39, 40, 40, 40, 41, 40, 41, 40, 41, 37, 40, 37, 41,
		37, 41, 37, 41, 37, 41, 37, 41, 37, 41, 37, 41, 37, 40, 37, 40, 37, 41,
		37, 41, 37, 41, 37, 40, 37, 41, 37, 41, 37, 41, 37, 41, 37, 41, 38, 40,
		37, 40, 38, 40, 37, 41, 37, 41, 40, 37, 41, 37, 41, 37, 41, 38, 40, 38,
		40, 37, 40, 37, 41, 37, 40, 40, 38, 40, 37, 40, 37, 41, 37, 41, 38, 40,
		37, 41, 37, 41, 37, 37, 41, 37, 41, 37, 41, 41, 41, 37, 37, 41, 37, 41,
		37, 37, 41, 37, 37, 37, 41, 41, 37, 37, 37, 37, 41, 37, 37, 41, 37, 37,
		37, 41, 41, 41, 37, 37, 41, 37, 37, 41, 37, 41, 37, 41, 37, 37, 41, 37,
		41, 41, 37, 41, 37, 37, 41, 37, 37, 37, 41, 37, 41, 37, 37, 41, 41, 42,
		37, 41, 41, 41, 42, 42, 42, 42, 37, 43, 41, 37, 43, 41, 37, 43, 41, 37,
		40, 37, 40, 37, 40, 37, 40, 37, 40, 37, 40, 37, 40, 37, 40, 41, 37, 41,
		41, 37, 43, 41, 37, 41, 37, 37, 37, 41, 37, 41, 41, 41, 41, 41, 41, 41,
		37, 37, 41, 37, 37, 41, 41, 37, 41, 37, 37, 37, 37, 41, 41, 40, 41, 41,
		41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41, 42, 41,
		41, 41, 44, 44, 44, 44, 44, 44, 44, 44, 44, 45, 45, 46, 46, 46, 46, 46,
		46, 46, 47, 47, 25, 47, 45, 48, 45, 48, 48, 48, 45, 48, 45, 45, 49, 46,
		47, 47, 47, 47, 47, 47, 25, 25, 25, 25, 47, 25, 47, 25, 44, 44, 44, 44,
		44, 47, 47, 47, 47, 47, 50, 50, 45, 47, 46, 47, 47, 47, 47, 47, 47, 47,
		47, 47, 51, 51, 51, 51, 51, 51, 51, 51, 51, 51, 51, 51, 51, 52, 53, 53,
		53, 53, 52, 54, 53, 53, 53, 53, 53, 55, 55, 53, 53, 53, 53, 55, 55, 53,
		53, 53, 53, 53, 53, 53, 53, 53, 53, 53, 56, 56, 56, 56, 56, 53, 53, 53,
		53, 51, 51, 51, 51, 51, 51, 51, 51, 57, 51, 53, 53, 53, 51, 51, 51, 53,
		53, 58, 51, 51, 51, 53, 53, 53, 53, 51, 52, 53, 53, 51, 59, 60, 60, 59,
		60, 60, 59, 51, 51, 51, 51, 51, 61, 62, 61, 62, 45, 63, 61, 62, 64, 64,
		65, 62, 62, 62, 66, 64, 64, 64, 64, 64, 63, 47, 61, 66, 61, 61, 61, 64,
		61, 64, 61, 61, 62, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67, 67,
		67, 67, 67, 67, 64, 67, 67, 67, 67, 67, 67, 67, 61, 61, 62, 62, 62, 62,
		62, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68, 68,
		62, 68, 68, 68, 68, 68, 68, 68, 62, 62, 62, 62, 62, 61, 62, 62, 61, 61,
		61, 62, 62, 62, 61, 62, 61, 62, 61, 62, 61, 62, 61, 62, 69, 70, 69, 70,
		69, 70, 69, 70, 69, 70, 69, 70, 69, 70, 62, 62, 62, 62, 61, 62, 71, 61,
		62, 61, 61, 62, 62, 61, 61, 61, 72, 73, 72, 72, 72, 72, 72, 72, 72, 72,
		72, 72, 72, 72, 72, 72, 73, 73, 73, 73, 73, 73, 73, 73, 74, 74, 74, 74,
		74, 74, 74, 74, 75, 74, 75, 75, 75, 75, 75, 75, 75, 75, 75, 75, 75, 75,
		75, 75, 72, 75, 72, 75, 72, 75, 72, 75, 72, 75, 76, 77, 77, 78, 78, 77,
		79, 79, 72, 75, 72, 75, 72, 75, 72, 72, 75, 72, 75, 72, 75, 72, 75, 72,
		75, 72, 75, 72, 75, 75, 64, 64, 64, 64, 64, 64, 64, 64, 64, 80, 80, 80,
		80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80,
		80, 64, 64, 81, 82, 82, 82, 82, 82, 82, 64, 83, 83, 83, 83, 83, 83, 83,
		83, 83, 83, 83, 83, 83, 83, 83, 64, 84, 85, 64, 64, 64, 64, 86, 64, 87,
		88, 88, 88, 88, 87, 88, 88, 88, 89, 87, 88, 88, 88, 88, 88, 88, 87, 87,
		87, 87, 87, 87, 88, 88, 87, 88, 88, 89, 90, 88, 91, 92, 93, 94, 95, 96,
		97, 98, 99, 100, 100, 101, 102, 103, 104, 105, 106, 107, 108, 106, 88,
		87, 106, 99, 109, 109, 109, 109, 109, 109, 109, 109, 109, 109, 109, 64,
		64, 64, 64, 64, 109, 109, 109, 106, 106, 64, 64, 64, 110, 110, 110, 110,
		110, 64, 111, 111, 112, 113, 113, 114, 115, 116, 117, 117, 118, 118, 118,
		118, 118, 118, 118, 118, 119, 120, 121, 122, 64, 64, 116, 122, 123, 123,
		123, 123, 123, 123, 123, 123, 124, 123, 123, 123, 123, 123, 123, 123,
		123, 123, 123, 125, 126, 127, 128, 129, 130, 131, 132, 78, 78, 133, 134,
		118, 118, 118, 118, 118, 134, 118, 118, 134, 135, 135, 135, 135, 135,
		135, 135, 135, 135, 135, 113, 136, 136, 116, 123, 123, 137, 123, 123,
		123, 123, 123, 123, 123, 123, 123, 123, 123, 116, 123, 118, 118, 118,
		118, 118, 118, 118, 138, 117, 118, 118, 118, 118, 134, 118, 139, 139,
		118, 118, 117, 134, 118, 118, 134, 123, 123, 140, 140, 140, 140, 140,
		140, 140, 140, 140, 140, 123, 123, 123, 141, 141, 123, 142, 142, 142,
		142, 142, 142, 142, 142, 142, 142, 142, 142, 142, 142, 64, 143, 144, 145,
		144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144,
		146, 147, 146, 146, 147, 146, 146, 147, 147, 147, 146, 147, 147, 146,
		147, 146, 146, 146, 147, 146, 147, 146, 147, 146, 147, 146, 146, 64, 64,
		144, 144, 144, 148, 148, 148, 148, 148, 148, 148, 148, 148, 148, 148,
		148, 148, 148, 149, 149, 149, 149, 149, 149, 149, 149, 149, 149, 149,
		148, 64, 64, 64, 64, 64, 64, 150, 150, 150, 150, 150, 150, 150, 150, 150,
		150, 151, 151, 151, 151, 151, 151, 151, 151, 151, 151, 151, 151, 151,
		151, 151, 151, 151, 152, 152, 152, 152, 152, 152, 152, 153, 152, 154,
		154, 155, 156, 156, 156, 154, 64, 64, 64, 64, 64, 157, 157, 157, 157,
		157, 157, 157, 157, 157, 157, 157, 157, 157, 157, 158, 158, 158, 158,
		159, 158, 158, 158, 158, 158, 158, 158, 158, 158, 159, 158, 158, 158,
		159, 158, 158, 158, 158, 158, 64, 64, 160, 160, 160, 160, 160, 160, 160,
		160, 160, 160, 160, 160, 160, 160, 160, 64, 161, 161, 161, 161, 161, 161,
		161, 161, 161, 162, 162, 162, 64, 64, 163, 64, 123, 64, 123, 123, 123,
		123, 123, 123, 123, 123, 123, 123, 123, 64, 64, 64, 64, 64, 64, 64, 118,
		118, 134, 118, 118, 134, 118, 118, 118, 134, 134, 134, 164, 165, 166,
		118, 118, 118, 134, 118, 118, 134, 134, 118, 118, 118, 118, 64, 167, 167,
		167, 168, 169, 169, 169, 169, 169, 169, 169, 169, 169, 169, 169, 169,
		169, 169, 167, 168, 170, 169, 168, 168, 168, 167, 167, 167, 167, 167,
		167, 167, 167, 168, 168, 168, 168, 171, 168, 168, 169, 78, 133, 172, 172,
		167, 167, 167, 169, 169, 167, 167, 84, 84, 173, 173, 173, 173, 173, 173,
		173, 173, 173, 173, 174, 175, 169, 169, 169, 169, 169, 169, 64, 169, 169,
		169, 169, 169, 169, 169, 64, 176, 177, 177, 64, 178, 178, 178, 178, 178,
		178, 178, 178, 64, 64, 178, 178, 64, 64, 178, 178, 178, 178, 178, 178,
		178, 178, 178, 178, 178, 178, 178, 178, 64, 178, 178, 178, 178, 178, 178,
		178, 64, 178, 64, 64, 64, 178, 178, 178, 178, 64, 64, 179, 178, 177, 177,
		177, 176, 176, 176, 176, 64, 64, 177, 177, 64, 64, 177, 177, 180, 178,
		64, 64, 64, 64, 64, 64, 64, 64, 177, 64, 64, 64, 64, 178, 178, 64, 178,
		178, 178, 176, 176, 64, 64, 181, 181, 181, 181, 181, 181, 181, 181, 181,
		181, 178, 178, 182, 182, 183, 183, 183, 183, 183, 183, 184, 182, 64, 64,
		64, 64, 64, 185, 185, 186, 64, 187, 187, 187, 187, 187, 187, 64, 64, 64,
		64, 187, 187, 64, 64, 187, 187, 187, 187, 187, 187, 187, 187, 187, 187,
		187, 187, 187, 187, 64, 187, 187, 187, 187, 187, 187, 187, 64, 187, 187,
		64, 187, 187, 64, 187, 187, 64, 64, 188, 64, 186, 186, 186, 185, 185, 64,
		64, 64, 64, 185, 185, 64, 64, 185, 185, 189, 64, 64, 64, 185, 64, 64, 64,
		64, 64, 64, 64, 187, 187, 187, 187, 64, 187, 64, 64, 64, 64, 64, 64, 64,
		190, 190, 190, 190, 190, 190, 190, 190, 190, 190, 185, 185, 187, 187,
		187, 185, 64, 64, 64, 191, 191, 192, 64, 193, 193, 193, 193, 193, 193,
		193, 193, 193, 64, 193, 193, 193, 64, 193, 193, 193, 193, 193, 193, 193,
		193, 193, 193, 193, 193, 193, 193, 64, 193, 193, 193, 193, 193, 193, 193,
		64, 193, 193, 64, 193, 193, 193, 193, 193, 64, 64, 194, 193, 192, 192,
		192, 191, 191, 191, 191, 191, 64, 191, 191, 192, 64, 192, 192, 195, 64,
		64, 193, 64, 64, 64, 64, 64, 64, 64, 193, 193, 191, 191, 64, 64, 196,
		196, 196, 196, 196, 196, 196, 196, 196, 196, 197, 198, 64, 64, 64, 64,
		64, 64, 64, 199, 200, 200, 64, 201, 201, 201, 201, 201, 201, 201, 201,
		64, 64, 201, 201, 64, 64, 201, 201, 201, 201, 201, 201, 201, 201, 201,
		201, 201, 201, 201, 201, 64, 201, 201, 201, 201, 201, 201, 201, 64, 201,
		201, 64, 201, 201, 201, 201, 201, 64, 64, 202, 201, 200, 199, 200, 199,
		199, 199, 199, 64, 64, 200, 200, 64, 64, 200, 200, 203, 64, 64, 64, 64,
		64, 64, 64, 64, 199, 200, 64, 64, 64, 64, 201, 201, 64, 201, 201, 201,
		199, 199, 64, 64, 204, 204, 204, 204, 204, 204, 204, 204, 204, 204, 205,
		201, 206, 206, 206, 206, 206, 206, 64, 64, 207, 208, 64, 208, 208, 208,
		208, 208, 208, 64, 64, 64, 208, 208, 208, 64, 208, 208, 208, 208, 64, 64,
		64, 208, 208, 64, 208, 64, 208, 208, 64, 64, 64, 208, 208, 64, 64, 64,
		208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 64, 64, 64, 64, 209,
		209, 207, 209, 209, 64, 64, 64, 209, 209, 209, 64, 209, 209, 209, 210,
		64, 64, 208, 64, 64, 64, 64, 64, 64, 209, 64, 64, 64, 64, 64, 64, 211,
		211, 211, 211, 211, 211, 211, 211, 211, 211, 212, 212, 212, 213, 213,
		213, 213, 213, 213, 214, 213, 64, 64, 64, 64, 64, 64, 215, 215, 215, 64,
		216, 216, 216, 216, 216, 216, 216, 216, 64, 216, 216, 216, 64, 216, 216,
		216, 216, 216, 216, 216, 216, 216, 216, 216, 216, 216, 216, 216, 216,
		216, 216, 64, 216, 216, 216, 216, 216, 64, 64, 64, 216, 217, 217, 217,
		215, 215, 215, 215, 64, 217, 217, 217, 64, 217, 217, 217, 218, 64, 64,
		64, 64, 64, 64, 64, 219, 220, 64, 216, 216, 64, 64, 64, 64, 64, 64, 216,
		216, 217, 217, 64, 64, 221, 221, 221, 221, 221, 221, 221, 221, 221, 221,
		222, 222, 222, 222, 222, 222, 222, 223, 64, 64, 224, 224, 64, 225, 225,
		225, 225, 225, 225, 225, 225, 64, 225, 225, 225, 64, 225, 225, 225, 225,
		225, 225, 225, 225, 225, 225, 225, 225, 225, 225, 225, 225, 225, 225, 64,
		225, 225, 225, 225, 225, 64, 64, 226, 225, 224, 227, 224, 224, 224, 224,
		224, 64, 227, 224, 224, 64, 224, 224, 228, 229, 64, 64, 64, 64, 64, 64,
		64, 224, 224, 64, 64, 64, 64, 64, 64, 64, 225, 64, 225, 225, 228, 228,
		64, 64, 230, 230, 230, 230, 230, 230, 230, 230, 230, 230, 64, 225, 225,
		64, 64, 64, 64, 64, 64, 64, 231, 231, 64, 232, 232, 232, 232, 232, 232,
		232, 232, 64, 232, 232, 232, 64, 232, 232, 232, 232, 232, 232, 232, 232,
		232, 232, 232, 232, 232, 232, 232, 232, 232, 64, 64, 232, 231, 231, 231,
		233, 233, 233, 233, 64, 231, 231, 231, 64, 231, 231, 231, 234, 232, 64,
		64, 64, 64, 64, 64, 64, 64, 231, 232, 232, 233, 233, 64, 64, 235, 235,
		235, 235, 235, 235, 235, 235, 235, 235, 236, 236, 236, 236, 236, 236, 64,
		64, 64, 237, 232, 232, 232, 232, 232, 232, 64, 64, 238, 238, 64, 239,
		239, 239, 239, 239, 239, 239, 239, 239, 239, 239, 239, 239, 239, 239,
		239, 239, 239, 64, 64, 64, 239, 239, 239, 239, 239, 239, 239, 239, 64,
		239, 239, 239, 239, 239, 239, 239, 239, 239, 64, 239, 64, 64, 64, 64,
		240, 64, 64, 64, 64, 238, 238, 238, 241, 241, 241, 64, 241, 64, 238, 238,
		238, 238, 238, 238, 238, 238, 64, 64, 238, 238, 242, 64, 64, 64, 64, 243,
		243, 243, 243, 243, 243, 243, 243, 243, 243, 243, 243, 243, 243, 243,
		243, 244, 243, 243, 244, 244, 244, 244, 245, 245, 246, 64, 64, 64, 64,
		247, 243, 243, 243, 243, 243, 243, 248, 244, 249, 249, 249, 249, 244,
		244, 244, 250, 251, 251, 251, 251, 251, 251, 251, 251, 251, 251, 250,
		250, 64, 64, 64, 64, 64, 252, 252, 64, 252, 64, 64, 252, 252, 64, 252,
		64, 64, 252, 64, 64, 64, 64, 64, 64, 252, 252, 252, 252, 64, 252, 252,
		252, 252, 252, 252, 252, 64, 252, 252, 252, 64, 252, 64, 252, 64, 64,
		252, 252, 64, 252, 252, 252, 252, 253, 252, 252, 253, 253, 253, 253, 254,
		254, 64, 253, 253, 252, 64, 64, 252, 252, 252, 252, 252, 64, 255, 64,
		256, 256, 256, 256, 253, 253, 64, 64, 257, 257, 257, 257, 257, 257, 257,
		257, 257, 257, 64, 64, 252, 252, 252, 252, 258, 259, 259, 259, 260, 260,
		260, 260, 260, 260, 260, 260, 260, 260, 260, 260, 260, 260, 260, 259,
		260, 259, 259, 259, 261, 261, 259, 259, 259, 259, 259, 259, 262, 262,
		262, 262, 262, 262, 262, 262, 262, 262, 263, 263, 263, 263, 263, 263,
		263, 263, 263, 263, 259, 261, 259, 261, 259, 264, 265, 266, 265, 266,
		267, 267, 258, 258, 258, 258, 258, 258, 258, 258, 64, 258, 258, 258, 258,
		258, 258, 258, 258, 258, 258, 258, 258, 64, 64, 64, 64, 268, 269, 270,
		271, 270, 270, 270, 270, 270, 269, 269, 269, 269, 270, 267, 269, 270,
		272, 272, 273, 260, 272, 272, 258, 258, 258, 258, 258, 270, 270, 270,
		270, 270, 270, 270, 270, 270, 270, 270, 64, 270, 270, 270, 270, 270, 270,
		270, 270, 270, 270, 270, 270, 64, 259, 259, 259, 259, 259, 259, 259, 259,
		261, 259, 259, 259, 259, 259, 259, 64, 259, 259, 260, 260, 260, 260, 260,
		274, 274, 274, 274, 260, 260, 64, 64, 64, 64, 64, 275, 275, 275, 275,
		275, 275, 275, 275, 275, 275, 275, 276, 276, 277, 277, 277, 277, 276,
		277, 277, 277, 277, 277, 278, 276, 279, 279, 276, 276, 277, 277, 275,
		280, 280, 280, 280, 280, 280, 280, 280, 280, 280, 281, 281, 281, 281,
		281, 281, 275, 275, 275, 275, 275, 275, 276, 276, 277, 277, 275, 275,
		275, 275, 277, 277, 277, 275, 276, 276, 276, 275, 275, 276, 276, 276,
		276, 276, 276, 276, 275, 275, 275, 277, 277, 277, 277, 275, 275, 275,
		275, 275, 277, 276, 276, 277, 277, 276, 276, 276, 276, 276, 276, 282,
		275, 276, 280, 280, 276, 276, 276, 277, 283, 283, 284, 284, 284, 284,
		284, 284, 284, 284, 284, 284, 284, 284, 284, 284, 64, 284, 64, 64, 64,
		64, 64, 284, 64, 64, 285, 285, 285, 285, 285, 285, 285, 285, 285, 285,
		285, 84, 286, 285, 285, 285, 287, 287, 287, 287, 287, 287, 287, 287, 288,
		288, 288, 288, 288, 288, 288, 288, 289, 289, 289, 289, 289, 289, 289,
		289, 289, 64, 289, 289, 289, 289, 64, 64, 289, 289, 289, 289, 289, 289,
		289, 64, 289, 289, 289, 64, 64, 290, 290, 290, 291, 291, 291, 291, 291,
		291, 291, 291, 291, 292, 292, 292, 292, 292, 292, 292, 292, 292, 292,
		292, 292, 292, 292, 292, 292, 292, 292, 292, 292, 64, 64, 64, 293, 293,
		293, 293, 293, 293, 293, 293, 293, 293, 64, 64, 64, 64, 64, 64, 294, 294,
		294, 294, 294, 294, 294, 294, 294, 294, 294, 294, 294, 64, 64, 64, 295,
		296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296,
		296, 296, 296, 296, 296, 296, 297, 297, 296, 298, 299, 299, 299, 299,
		299, 299, 299, 299, 299, 299, 299, 299, 299, 299, 299, 299, 299, 299,
		300, 301, 64, 64, 64, 302, 302, 302, 302, 302, 302, 302, 302, 302, 302,
		302, 84, 84, 84, 303, 303, 303, 64, 64, 64, 64, 64, 64, 64, 304, 304,
		304, 304, 304, 304, 304, 304, 304, 304, 304, 304, 304, 64, 304, 304, 304,
		304, 305, 305, 306, 64, 64, 64, 307, 307, 307, 307, 307, 307, 307, 307,
		307, 307, 308, 308, 309, 84, 84, 64, 310, 310, 310, 310, 310, 310, 310,
		310, 310, 310, 311, 311, 64, 64, 64, 64, 312, 312, 312, 312, 312, 312,
		312, 312, 312, 312, 312, 312, 312, 64, 312, 312, 312, 64, 313, 313, 64,
		64, 64, 64, 314, 314, 314, 314, 314, 314, 314, 314, 314, 314, 314, 314,
		315, 315, 316, 315, 315, 315, 315, 315, 315, 315, 316, 316, 316, 316,
		316, 316, 316, 316, 315, 316, 316, 315, 315, 315, 315, 315, 315, 315,
		315, 315, 317, 315, 318, 318, 318, 319, 318, 318, 318, 320, 314, 321, 64,
		64, 322, 322, 322, 322, 322, 322, 322, 322, 322, 322, 64, 64, 64, 64, 64,
		64, 323, 323, 323, 323, 323, 323, 323, 323, 323, 323, 64, 64, 64, 64, 64,
		64, 324, 324, 66, 66, 324, 66, 325, 324, 324, 324, 324, 326, 326, 326,
		327, 64, 328, 328, 328, 328, 328, 328, 328, 328, 328, 328, 64, 64, 64,
		64, 64, 64, 329, 329, 329, 329, 329, 329, 329, 329, 329, 329, 329, 330,
		329, 329, 329, 329, 329, 331, 329, 64, 64, 64, 64, 64, 296, 296, 296,
		296, 296, 296, 64, 64, 332, 332, 332, 332, 332, 332, 332, 332, 332, 332,
		332, 332, 332, 64, 64, 64, 333, 333, 333, 334, 334, 334, 334, 333, 333,
		334, 334, 334, 64, 64, 64, 64, 334, 334, 333, 334, 334, 334, 334, 334,
		334, 335, 336, 337, 64, 64, 64, 64, 338, 64, 64, 64, 339, 339, 340, 340,
		340, 340, 340, 340, 340, 340, 340, 340, 341, 341, 341, 341, 341, 341,
		341, 341, 341, 341, 341, 341, 341, 341, 64, 64, 341, 341, 341, 341, 341,
		64, 64, 64, 342, 342, 342, 342, 342, 342, 342, 342, 342, 342, 342, 342,
		64, 64, 64, 64, 343, 343, 343, 343, 343, 343, 343, 343, 343, 342, 342,
		342, 342, 342, 342, 342, 343, 343, 64, 64, 64, 64, 64, 64, 344, 344, 344,
		344, 344, 344, 344, 344, 344, 344, 345, 64, 64, 64, 346, 346, 347, 347,
		347, 347, 347, 347, 347, 347, 348, 348, 348, 348, 348, 348, 348, 348,
		348, 348, 348, 348, 348, 348, 348, 349, 350, 351, 351, 351, 64, 64, 352,
		352, 353, 353, 353, 353, 353, 353, 353, 353, 353, 353, 353, 353, 353,
		354, 355, 354, 355, 355, 355, 355, 355, 355, 355, 64, 356, 354, 355, 354,
		354, 355, 355, 355, 355, 355, 355, 355, 355, 354, 354, 354, 354, 354,
		354, 355, 355, 357, 357, 357, 357, 357, 357, 357, 357, 64, 64, 358, 359,
		359, 359, 359, 359, 359, 359, 359, 359, 359, 64, 64, 64, 64, 64, 64, 360,
		360, 360, 360, 360, 360, 360, 361, 360, 360, 360, 360, 360, 360, 64, 64,
		362, 362, 362, 362, 363, 364, 364, 364, 364, 364, 364, 364, 364, 364,
		364, 364, 364, 364, 364, 364, 365, 363, 362, 362, 362, 362, 362, 363,
		362, 363, 363, 363, 363, 363, 362, 363, 366, 364, 364, 364, 364, 364,
		364, 364, 64, 64, 64, 64, 367, 367, 367, 367, 367, 367, 367, 367, 367,
		367, 368, 368, 368, 368, 368, 368, 368, 369, 369, 369, 369, 369, 369,
		369, 369, 369, 369, 370, 371, 370, 370, 370, 370, 370, 370, 370, 369,
		369, 369, 369, 369, 369, 369, 369, 369, 64, 64, 64, 372, 372, 373, 374,
		374, 374, 374, 374, 374, 374, 374, 374, 374, 374, 374, 374, 374, 373,
		372, 372, 372, 372, 373, 373, 372, 372, 375, 376, 373, 373, 374, 374,
		377, 377, 377, 377, 377, 377, 377, 377, 377, 377, 374, 374, 374, 374,
		374, 374, 378, 378, 378, 378, 378, 378, 378, 378, 378, 378, 378, 378,
		378, 378, 379, 380, 381, 381, 380, 380, 380, 381, 380, 381, 381, 381,
		382, 382, 64, 64, 64, 64, 64, 64, 64, 64, 383, 383, 383, 383, 384, 384,
		384, 384, 384, 384, 384, 384, 384, 384, 384, 384, 385, 385, 385, 385,
		385, 385, 385, 385, 386, 386, 386, 386, 386, 386, 386, 386, 385, 385,
		386, 387, 64, 64, 64, 388, 388, 388, 388, 388, 389, 389, 389, 389, 389,
		389, 389, 389, 389, 389, 64, 64, 64, 384, 384, 384, 390, 390, 390, 390,
		390, 390, 390, 390, 390, 390, 391, 391, 391, 391, 391, 391, 391, 391,
		391, 391, 391, 391, 391, 391, 392, 392, 392, 392, 392, 392, 393, 393,
		394, 394, 394, 394, 394, 394, 394, 394, 78, 78, 78, 84, 395, 133, 133,
		133, 133, 133, 78, 78, 133, 133, 133, 133, 78, 396, 395, 395, 395, 395,
		395, 395, 395, 397, 397, 397, 397, 133, 397, 397, 397, 397, 396, 396, 78,
		397, 397, 64, 41, 41, 41, 41, 41, 41, 62, 62, 62, 62, 62, 75, 44, 44, 44,
		44, 44, 44, 44, 44, 44, 65, 65, 65, 65, 65, 44, 44, 44, 44, 65, 65, 65,
		65, 65, 41, 41, 41, 41, 41, 398, 41, 41, 41, 41, 41, 41, 41, 41, 41, 41,
		44, 44, 44, 44, 44, 44, 44, 44, 44, 44, 44, 44, 65, 78, 78, 133, 78, 78,
		78, 78, 78, 78, 78, 133, 78, 78, 399, 400, 133, 401, 78, 78, 78, 78, 78,
		78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 78, 64,
		64, 64, 64, 64, 402, 133, 78, 133, 37, 41, 37, 41, 37, 41, 41, 41, 41,
		41, 41, 41, 41, 41, 37, 41, 62, 62, 62, 62, 62, 62, 62, 62, 61, 61, 61,
		61, 61, 61, 61, 61, 62, 62, 62, 62, 62, 62, 64, 64, 61, 61, 61, 61, 61,
		61, 64, 64, 64, 61, 64, 61, 64, 61, 64, 61, 403, 403, 403, 403, 403, 403,
		403, 403, 62, 62, 62, 62, 62, 64, 62, 62, 61, 61, 61, 61, 403, 63, 62,
		63, 63, 63, 62, 62, 62, 64, 62, 62, 61, 61, 61, 61, 403, 63, 63, 63, 62,
		62, 62, 62, 64, 64, 62, 62, 61, 61, 61, 61, 64, 63, 63, 63, 61, 61, 61,
		61, 61, 63, 63, 63, 64, 64, 62, 62, 62, 64, 62, 62, 61, 61, 61, 61, 403,
		63, 63, 64, 404, 404, 404, 404, 404, 404, 404, 404, 404, 404, 404, 405,
		406, 406, 407, 408, 409, 410, 410, 409, 409, 409, 22, 66, 411, 412, 413,
		414, 411, 412, 413, 414, 22, 22, 22, 66, 22, 22, 22, 22, 415, 416, 417,
		418, 419, 420, 421, 21, 422, 423, 422, 422, 423, 22, 66, 66, 66, 28, 35,
		22, 66, 66, 22, 424, 424, 66, 66, 66, 425, 426, 427, 66, 66, 66, 66, 66,
		66, 66, 66, 66, 66, 66, 428, 66, 424, 66, 66, 66, 66, 66, 66, 66, 66, 66,
		66, 404, 405, 405, 405, 405, 405, 64, 64, 64, 64, 64, 405, 405, 405, 405,
		405, 405, 429, 44, 64, 64, 33, 429, 429, 429, 429, 429, 430, 430, 428,
		426, 427, 431, 429, 33, 33, 33, 33, 429, 429, 429, 429, 429, 430, 430,
		428, 426, 427, 64, 44, 44, 44, 44, 44, 64, 64, 64, 247, 247, 247, 247,
		247, 247, 247, 247, 247, 432, 247, 247, 23, 247, 247, 247, 247, 247, 247,
		64, 64, 64, 64, 64, 78, 78, 395, 395, 78, 78, 78, 78, 395, 395, 395, 78,
		78, 433, 433, 433, 433, 78, 433, 433, 433, 395, 395, 78, 133, 78, 395,
		395, 133, 133, 133, 133, 78, 64, 64, 64, 64, 64, 64, 64, 26, 26, 434, 30,
		26, 30, 26, 434, 26, 30, 34, 434, 434, 434, 34, 34, 434, 434, 434, 435,
		26, 434, 30, 26, 428, 434, 434, 434, 434, 434, 26, 26, 26, 30, 30, 26,
		434, 26, 67, 26, 434, 26, 37, 38, 434, 434, 436, 34, 434, 434, 37, 434,
		34, 397, 397, 397, 397, 34, 26, 26, 34, 34, 434, 434, 437, 428, 428, 428,
		428, 434, 34, 34, 34, 34, 26, 428, 26, 26, 41, 274, 438, 438, 438, 36,
		36, 438, 438, 438, 438, 438, 438, 36, 36, 36, 36, 438, 439, 439, 439,
		439, 439, 439, 439, 439, 439, 439, 439, 439, 440, 440, 440, 440, 439,
		439, 440, 440, 440, 440, 440, 440, 440, 440, 440, 37, 41, 440, 440, 440,
		440, 36, 64, 64, 64, 64, 64, 64, 39, 39, 39, 39, 39, 30, 30, 30, 30, 30,
		428, 428, 26, 26, 26, 26, 428, 26, 26, 428, 26, 26, 428, 26, 26, 26, 26,
		26, 26, 26, 428, 26, 26, 26, 26, 26, 26, 26, 26, 26, 30, 30, 26, 26, 26,
		26, 26, 26, 26, 26, 26, 26, 26, 26, 428, 428, 26, 26, 39, 26, 39, 26, 26,
		26, 26, 26, 26, 26, 26, 26, 26, 30, 26, 26, 26, 26, 428, 428, 428, 428,
		428, 428, 428, 428, 428, 428, 428, 428, 39, 437, 441, 441, 437, 428, 428,
		39, 441, 437, 437, 441, 437, 437, 428, 39, 428, 441, 430, 442, 428, 441,
		437, 428, 428, 428, 441, 437, 437, 441, 39, 441, 441, 437, 437, 39, 437,
		39, 437, 39, 39, 39, 39, 441, 441, 437, 441, 437, 437, 437, 437, 437, 39,
		39, 39, 39, 428, 437, 428, 437, 441, 441, 437, 437, 437, 437, 437, 437,
		437, 437, 437, 437, 441, 437, 437, 437, 441, 428, 428, 428, 428, 428,
		441, 437, 437, 437, 428, 428, 428, 428, 428, 428, 428, 428, 428, 437,
		441, 39, 437, 428, 441, 441, 441, 441, 437, 437, 441, 441, 428, 428, 441,
		441, 437, 437, 441, 441, 437, 437, 441, 441, 437, 437, 437, 437, 437,
		428, 428, 437, 437, 437, 437, 428, 428, 39, 428, 428, 437, 39, 428, 428,
		428, 428, 428, 428, 428, 428, 437, 437, 428, 39, 437, 437, 437, 428, 428,
		428, 428, 428, 437, 441, 428, 437, 437, 437, 437, 437, 428, 428, 437,
		437, 428, 428, 428, 428, 437, 437, 437, 437, 437, 437, 437, 437, 428,
		428, 437, 437, 437, 437, 26, 26, 26, 26, 26, 26, 30, 26, 26, 26, 26, 26,
		437, 437, 26, 26, 26, 26, 26, 26, 26, 443, 444, 26, 26, 26, 26, 26, 26,
		26, 26, 26, 26, 26, 274, 274, 274, 274, 274, 274, 274, 274, 274, 274,
		274, 274, 274, 26, 428, 26, 26, 26, 26, 26, 26, 26, 26, 274, 26, 26, 26,
		26, 26, 428, 428, 428, 428, 428, 428, 428, 428, 428, 26, 26, 26, 26, 428,
		428, 26, 26, 26, 26, 26, 26, 26, 26, 26, 26, 64, 64, 64, 64, 26, 26, 26,
		26, 26, 26, 26, 64, 26, 26, 26, 64, 64, 64, 64, 64, 36, 36, 36, 36, 36,
		36, 36, 36, 33, 33, 33, 33, 33, 33, 33, 33, 33, 33, 33, 33, 445, 445,
		445, 445, 445, 445, 445, 445, 445, 445, 445, 445, 445, 445, 438, 36, 36,
		36, 36, 36, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 26, 26, 26,
		26, 26, 26, 30, 30, 30, 30, 26, 26, 30, 30, 26, 30, 30, 30, 30, 30, 26,
		26, 30, 30, 26, 26, 30, 39, 26, 26, 26, 26, 30, 30, 26, 26, 30, 39, 26,
		26, 26, 26, 30, 30, 30, 26, 26, 30, 26, 26, 30, 30, 26, 26, 26, 26, 26,
		30, 30, 26, 26, 30, 26, 26, 26, 26, 30, 30, 26, 26, 26, 26, 30, 26, 30,
		26, 30, 26, 30, 26, 26, 26, 26, 26, 30, 30, 26, 30, 30, 30, 26, 30, 30,
		30, 30, 26, 30, 30, 26, 39, 26, 26, 26, 26, 26, 26, 30, 30, 26, 26, 26,
		26, 274, 26, 26, 26, 26, 26, 26, 26, 30, 30, 30, 30, 30, 30, 30, 30, 30,
		30, 26, 30, 30, 30, 26, 30, 26, 26, 26, 26, 64, 26, 26, 26, 26, 26, 26,
		26, 26, 26, 26, 26, 26, 30, 26, 26, 426, 427, 426, 427, 426, 427, 426,
		427, 426, 427, 426, 427, 426, 427, 36, 36, 438, 438, 438, 438, 438, 438,
		438, 438, 438, 438, 438, 438, 26, 26, 26, 26, 437, 428, 428, 437, 437,
		426, 427, 428, 437, 437, 428, 437, 437, 437, 428, 428, 428, 428, 428,
		437, 437, 437, 437, 428, 428, 428, 428, 428, 437, 437, 437, 428, 428,
		428, 437, 437, 437, 437, 9, 10, 9, 10, 9, 10, 9, 10, 426, 427, 446, 446,
		446, 446, 446, 446, 446, 446, 428, 428, 428, 426, 427, 9, 10, 426, 427,
		426, 427, 426, 427, 426, 427, 426, 427, 428, 428, 437, 437, 437, 437,
		437, 437, 428, 428, 428, 428, 428, 428, 428, 428, 437, 428, 428, 428,
		428, 437, 437, 437, 437, 437, 428, 437, 437, 428, 428, 426, 427, 426,
		427, 437, 428, 428, 428, 428, 437, 428, 437, 437, 437, 428, 428, 437,
		437, 428, 428, 428, 428, 428, 428, 428, 428, 428, 428, 437, 437, 437,
		437, 437, 437, 428, 428, 426, 427, 428, 428, 428, 428, 437, 437, 437,
		437, 437, 437, 437, 437, 437, 437, 437, 428, 437, 437, 437, 437, 428,
		428, 437, 428, 437, 428, 428, 437, 428, 437, 437, 437, 437, 428, 428,
		428, 428, 428, 437, 437, 428, 428, 428, 428, 437, 437, 437, 437, 428,
		437, 437, 428, 428, 437, 437, 428, 428, 428, 428, 437, 437, 437, 437,
		437, 437, 437, 437, 437, 437, 437, 428, 428, 437, 437, 437, 437, 437,
		437, 437, 437, 428, 437, 437, 437, 437, 437, 437, 437, 437, 428, 428,
		428, 428, 428, 437, 428, 437, 428, 428, 428, 437, 437, 437, 437, 437,
		428, 428, 428, 428, 437, 428, 428, 428, 437, 437, 437, 437, 437, 428,
		437, 428, 428, 428, 428, 428, 428, 428, 26, 26, 428, 428, 428, 428, 428,
		428, 64, 64, 64, 26, 26, 26, 26, 26, 30, 30, 30, 30, 30, 64, 64, 64, 64,
		64, 64, 447, 447, 447, 447, 447, 447, 447, 447, 447, 447, 447, 447, 447,
		447, 447, 64, 448, 448, 448, 448, 448, 448, 448, 448, 448, 448, 448, 448,
		448, 448, 448, 64, 37, 41, 37, 37, 37, 41, 41, 37, 41, 37, 41, 37, 41,
		37, 37, 37, 37, 41, 37, 41, 41, 37, 41, 41, 41, 41, 41, 41, 44, 44, 37,
		37, 69, 70, 69, 70, 70, 449, 449, 449, 449, 449, 449, 69, 70, 69, 70,
		450, 450, 450, 69, 70, 64, 64, 64, 64, 64, 451, 451, 451, 451, 452, 451,
		451, 453, 453, 453, 453, 453, 453, 453, 453, 453, 453, 453, 453, 453,
		453, 64, 453, 64, 64, 64, 64, 64, 453, 64, 64, 454, 454, 454, 454, 454,
		454, 454, 454, 64, 64, 64, 64, 64, 64, 64, 455, 456, 64, 64, 64, 64, 64,
		64, 64, 64, 64, 64, 64, 64, 64, 64, 457, 77, 77, 77, 77, 77, 77, 77, 77,
		66, 66, 28, 35, 28, 35, 66, 66, 66, 28, 35, 66, 28, 35, 66, 66, 66, 66,
		66, 66, 66, 66, 66, 410, 66, 66, 410, 66, 28, 35, 66, 66, 28, 35, 426,
		427, 426, 427, 426, 427, 426, 427, 66, 66, 66, 66, 66, 45, 66, 66, 410,
		410, 64, 64, 64, 64, 458, 458, 458, 458, 458, 458, 458, 458, 458, 458,
		64, 458, 458, 458, 458, 458, 458, 458, 458, 458, 64, 64, 64, 64, 458,
		458, 458, 458, 458, 458, 64, 64, 459, 459, 459, 459, 459, 459, 459, 459,
		459, 459, 459, 459, 64, 64, 64, 64, 460, 461, 461, 461, 459, 462, 463,
		464, 443, 444, 443, 444, 443, 444, 443, 444, 443, 444, 459, 459, 443,
		444, 443, 444, 443, 444, 443, 444, 465, 466, 467, 467, 459, 464, 464,
		464, 464, 464, 464, 464, 464, 464, 468, 469, 470, 471, 472, 472, 465,
		473, 473, 473, 473, 473, 459, 459, 464, 464, 464, 462, 463, 461, 459, 26,
		64, 474, 474, 474, 474, 474, 474, 474, 474, 474, 474, 474, 474, 474, 474,
		474, 474, 474, 474, 474, 474, 474, 474, 64, 64, 475, 475, 476, 476, 477,
		477, 474, 465, 478, 478, 478, 478, 478, 478, 478, 478, 478, 478, 478,
		478, 478, 478, 478, 478, 478, 478, 461, 473, 479, 479, 478, 64, 64, 64,
		64, 64, 480, 480, 480, 480, 480, 480, 480, 480, 480, 480, 480, 480, 480,
		480, 480, 480, 480, 64, 64, 64, 287, 287, 287, 287, 287, 287, 287, 287,
		287, 287, 287, 287, 287, 287, 64, 481, 481, 482, 482, 482, 482, 481, 481,
		481, 481, 481, 481, 481, 481, 481, 481, 480, 480, 480, 64, 64, 64, 64,
		64, 483, 483, 483, 483, 483, 483, 483, 483, 483, 483, 483, 483, 483, 484,
		484, 64, 482, 482, 482, 482, 482, 482, 482, 482, 482, 482, 481, 481, 481,
		481, 481, 481, 485, 485, 485, 485, 485, 485, 485, 485, 459, 486, 486,
		486, 486, 486, 486, 486, 486, 486, 486, 486, 486, 486, 486, 486, 483,
		483, 483, 483, 484, 484, 484, 481, 481, 486, 486, 486, 486, 486, 486,
		486, 481, 481, 481, 481, 459, 459, 459, 459, 487, 487, 487, 487, 487,
		487, 487, 487, 487, 487, 487, 487, 487, 487, 487, 64, 481, 481, 481, 481,
		481, 481, 481, 459, 459, 459, 459, 481, 481, 481, 481, 481, 481, 481,
		481, 481, 481, 481, 459, 459, 488, 489, 489, 489, 489, 489, 489, 489,
		489, 489, 489, 489, 489, 489, 489, 489, 489, 489, 489, 489, 489, 488,
		490, 490, 490, 490, 490, 490, 490, 490, 490, 490, 489, 489, 489, 489,
		488, 490, 490, 490, 491, 491, 491, 491, 491, 491, 491, 491, 491, 491,
		491, 491, 491, 492, 491, 491, 491, 491, 491, 491, 491, 64, 64, 64, 493,
		493, 493, 493, 493, 493, 493, 493, 493, 493, 493, 493, 493, 493, 493, 64,
		494, 494, 494, 494, 494, 494, 494, 494, 495, 495, 495, 495, 495, 495,
		496, 496, 497, 497, 497, 497, 497, 497, 497, 497, 497, 497, 497, 497,
		498, 499, 499, 499, 500, 500, 500, 500, 500, 500, 500, 500, 500, 500,
		497, 497, 64, 64, 64, 64, 72, 75, 72, 75, 72, 75, 501, 77, 79, 79, 79,
		502, 77, 77, 77, 77, 77, 77, 77, 77, 77, 77, 502, 503, 64, 64, 64, 64,
		64, 64, 64, 77, 504, 504, 504, 504, 504, 504, 504, 504, 504, 504, 504,
		504, 504, 504, 505, 505, 505, 505, 505, 505, 505, 505, 505, 505, 506,
		506, 507, 507, 507, 507, 507, 507, 47, 47, 47, 47, 47, 47, 47, 45, 45,
		45, 45, 45, 45, 45, 45, 45, 47, 47, 37, 41, 37, 41, 37, 41, 41, 41, 37,
		41, 37, 41, 37, 41, 44, 41, 41, 41, 41, 41, 41, 41, 41, 37, 41, 37, 41,
		37, 37, 41, 45, 508, 508, 37, 41, 37, 41, 64, 37, 41, 37, 41, 64, 64, 64,
		64, 37, 41, 37, 64, 64, 64, 64, 64, 44, 44, 41, 42, 42, 42, 42, 42, 509,
		509, 510, 509, 509, 509, 511, 509, 509, 509, 509, 510, 509, 509, 509,
		509, 509, 509, 509, 509, 509, 509, 509, 509, 509, 509, 509, 512, 512,
		510, 510, 512, 513, 513, 513, 513, 64, 64, 64, 64, 514, 514, 514, 514,
		514, 514, 274, 274, 247, 436, 64, 64, 64, 64, 64, 64, 515, 515, 515, 515,
		515, 515, 515, 515, 515, 515, 515, 515, 516, 516, 516, 516, 517, 517,
		518, 518, 518, 518, 518, 518, 518, 518, 518, 518, 518, 518, 518, 518,
		518, 518, 518, 518, 517, 517, 517, 517, 517, 517, 517, 517, 517, 517,
		517, 517, 517, 517, 517, 517, 519, 64, 64, 64, 64, 64, 64, 64, 64, 64,
		520, 520, 521, 521, 521, 521, 521, 521, 521, 521, 521, 521, 64, 64, 64,
		64, 64, 64, 172, 172, 172, 172, 172, 172, 172, 172, 172, 172, 169, 169,
		169, 169, 169, 169, 174, 174, 174, 169, 64, 64, 64, 64, 522, 522, 522,
		522, 522, 522, 522, 522, 522, 522, 523, 523, 523, 523, 523, 523, 523,
		523, 523, 523, 523, 523, 523, 523, 523, 523, 523, 523, 523, 523, 524,
		524, 524, 524, 524, 525, 525, 525, 526, 526, 527, 527, 527, 527, 527,
		527, 527, 527, 527, 527, 527, 527, 527, 527, 527, 528, 528, 528, 528,
		528, 528, 528, 528, 528, 528, 528, 529, 530, 64, 64, 64, 64, 64, 64, 64,
		64, 64, 64, 64, 531, 287, 287, 287, 287, 287, 64, 64, 64, 532, 532, 532,
		533, 534, 534, 534, 534, 534, 534, 534, 534, 534, 534, 534, 534, 534,
		534, 534, 535, 533, 533, 532, 532, 532, 532, 533, 533, 532, 533, 533,
		533, 536, 537, 537, 537, 537, 537, 537, 537, 537, 537, 537, 537, 537,
		537, 64, 538, 539, 539, 539, 539, 539, 539, 539, 539, 539, 539, 64, 64,
		64, 64, 537, 537, 540, 540, 540, 540, 540, 540, 540, 540, 540, 541, 541,
		541, 541, 541, 541, 542, 542, 541, 541, 542, 542, 541, 541, 64, 540, 540,
		540, 541, 540, 540, 540, 540, 540, 540, 540, 540, 541, 542, 64, 64, 543,
		543, 543, 543, 543, 543, 543, 543, 543, 543, 64, 64, 544, 544, 544, 544,
		545, 275, 275, 275, 275, 275, 275, 283, 283, 283, 275, 276, 64, 64, 64,
		64, 546, 546, 546, 546, 546, 546, 546, 546, 547, 546, 547, 547, 548, 546,
		546, 547, 547, 546, 546, 546, 546, 546, 547, 547, 546, 547, 546, 64, 64,
		64, 64, 64, 64, 64, 64, 546, 546, 549, 550, 550, 551, 551, 551, 551, 551,
		551, 551, 551, 551, 551, 551, 552, 553, 553, 552, 552, 554, 554, 551,
		555, 555, 552, 556, 64, 64, 289, 289, 289, 289, 289, 289, 64, 551, 551,
		551, 552, 552, 553, 552, 552, 553, 552, 552, 554, 552, 556, 64, 64, 557,
		557, 557, 557, 557, 557, 557, 557, 557, 557, 64, 64, 64, 64, 64, 64, 287,
		558, 558, 558, 558, 558, 558, 558, 558, 558, 558, 558, 558, 558, 558,
		558, 558, 558, 558, 287, 64, 64, 64, 64, 288, 288, 288, 288, 288, 288,
		288, 64, 64, 64, 64, 288, 288, 288, 288, 288, 288, 288, 288, 288, 64, 64,
		64, 64, 559, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 559,
		560, 561, 561, 561, 561, 561, 561, 561, 561, 561, 561, 561, 561, 561,
		561, 561, 561, 561, 561, 561, 561, 561, 561, 560, 488, 488, 488, 488,
		488, 488, 488, 488, 488, 488, 488, 488, 488, 488, 490, 490, 488, 488,
		490, 490, 490, 490, 490, 490, 41, 41, 41, 41, 41, 41, 41, 64, 64, 64, 64,
		83, 83, 83, 83, 83, 64, 64, 64, 64, 64, 109, 562, 109, 109, 563, 109,
		109, 109, 109, 109, 109, 109, 109, 109, 109, 109, 109, 109, 64, 109, 109,
		109, 109, 109, 64, 109, 64, 109, 109, 64, 109, 109, 64, 109, 109, 123,
		123, 564, 564, 564, 564, 564, 564, 564, 564, 564, 564, 564, 564, 564,
		564, 564, 564, 64, 64, 64, 64, 64, 64, 64, 64, 64, 123, 123, 123, 123,
		123, 123, 123, 123, 123, 123, 123, 413, 565, 64, 64, 123, 123, 123, 123,
		123, 123, 123, 123, 123, 123, 114, 26, 64, 64, 58, 58, 58, 58, 58, 58,
		58, 58, 461, 461, 461, 461, 461, 461, 461, 466, 467, 461, 64, 64, 64, 64,
		64, 64, 461, 465, 465, 566, 566, 466, 467, 466, 467, 466, 467, 466, 467,
		466, 467, 466, 467, 466, 467, 466, 467, 461, 461, 466, 467, 461, 461,
		461, 461, 566, 566, 566, 567, 461, 567, 64, 461, 567, 461, 461, 465, 443,
		444, 443, 444, 443, 444, 568, 461, 461, 569, 570, 571, 571, 572, 64, 461,
		573, 568, 461, 64, 64, 64, 64, 123, 123, 123, 123, 123, 64, 123, 123,
		123, 123, 123, 123, 123, 64, 64, 405, 64, 574, 574, 575, 576, 575, 574,
		574, 577, 578, 574, 579, 580, 581, 580, 580, 582, 582, 582, 582, 582,
		582, 582, 582, 582, 582, 580, 574, 583, 584, 583, 574, 574, 585, 585,
		585, 585, 585, 585, 585, 585, 585, 585, 585, 585, 585, 585, 585, 585,
		585, 585, 577, 574, 578, 586, 587, 586, 588, 588, 588, 588, 588, 588,
		588, 588, 588, 588, 588, 588, 588, 588, 588, 588, 588, 588, 577, 584,
		578, 584, 577, 578, 589, 590, 591, 589, 589, 592, 592, 592, 592, 592,
		592, 592, 592, 592, 592, 593, 592, 592, 592, 592, 592, 592, 592, 592,
		592, 592, 592, 592, 592, 593, 593, 594, 594, 594, 594, 594, 594, 594,
		594, 594, 594, 594, 594, 594, 594, 594, 64, 64, 64, 594, 594, 594, 594,
		594, 594, 64, 64, 594, 594, 594, 64, 64, 64, 576, 576, 584, 586, 595,
		576, 576, 64, 596, 597, 597, 597, 597, 596, 596, 64, 64, 598, 598, 598,
		26, 30, 64, 64, 599, 599, 599, 599, 599, 599, 599, 599, 599, 599, 599,
		599, 64, 599, 599, 599, 599, 599, 599, 599, 599, 599, 599, 64, 599, 599,
		599, 64, 599, 599, 64, 599, 599, 599, 599, 599, 599, 599, 64, 64, 599,
		599, 599, 64, 64, 64, 64, 64, 84, 66, 84, 64, 64, 64, 64, 514, 514, 514,
		514, 514, 514, 514, 514, 514, 514, 514, 514, 514, 64, 64, 64, 274, 600,
		600, 600, 600, 600, 600, 600, 600, 600, 600, 600, 600, 600, 601, 601,
		601, 601, 602, 602, 602, 602, 602, 602, 602, 602, 602, 602, 602, 602,
		602, 602, 602, 602, 602, 601, 64, 64, 64, 64, 64, 274, 274, 274, 274,
		274, 133, 64, 64, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603, 603,
		603, 603, 64, 64, 64, 604, 604, 604, 604, 604, 604, 604, 604, 604, 64,
		64, 64, 64, 64, 64, 64, 605, 605, 605, 605, 605, 605, 605, 605, 605, 605,
		605, 605, 605, 605, 605, 64, 606, 606, 606, 606, 64, 64, 64, 64, 607,
		607, 607, 607, 607, 607, 607, 607, 607, 608, 607, 607, 607, 607, 607,
		607, 607, 607, 608, 64, 64, 64, 64, 64, 609, 609, 609, 609, 609, 609,
		609, 609, 609, 609, 609, 609, 609, 609, 64, 610, 611, 611, 611, 611, 611,
		611, 611, 611, 611, 611, 611, 611, 64, 64, 64, 64, 612, 613, 613, 613,
		613, 613, 64, 64, 614, 614, 614, 614, 614, 614, 614, 614, 615, 615, 615,
		615, 615, 615, 615, 615, 616, 616, 616, 616, 616, 616, 616, 616, 617,
		617, 617, 617, 617, 617, 617, 617, 617, 617, 617, 617, 617, 617, 64, 64,
		618, 618, 618, 618, 618, 618, 618, 618, 618, 618, 64, 64, 64, 64, 64, 64,
		619, 619, 619, 619, 619, 619, 64, 64, 619, 64, 619, 619, 619, 619, 619,
		619, 619, 619, 619, 619, 619, 619, 619, 619, 619, 619, 619, 619, 619,
		619, 64, 619, 619, 64, 64, 64, 619, 64, 64, 619, 620, 620, 620, 620, 620,
		620, 620, 620, 620, 620, 620, 620, 620, 620, 64, 621, 622, 622, 622, 622,
		622, 622, 622, 622, 623, 623, 623, 623, 623, 623, 623, 623, 623, 623,
		623, 623, 623, 623, 624, 624, 624, 624, 624, 624, 64, 64, 64, 625, 626,
		626, 626, 626, 626, 626, 626, 626, 626, 626, 64, 64, 64, 64, 64, 627,
		628, 628, 628, 628, 628, 628, 628, 628, 629, 629, 629, 629, 629, 629,
		629, 629, 64, 64, 64, 64, 64, 64, 629, 629, 630, 631, 631, 631, 64, 631,
		631, 64, 64, 64, 64, 64, 631, 632, 631, 633, 630, 630, 630, 630, 64, 630,
		630, 630, 64, 630, 630, 630, 630, 630, 630, 630, 630, 630, 630, 630, 630,
		630, 630, 630, 630, 630, 630, 630, 64, 64, 64, 64, 633, 634, 632, 64, 64,
		64, 64, 635, 636, 636, 636, 636, 636, 636, 636, 636, 637, 637, 637, 637,
		637, 637, 637, 637, 637, 64, 64, 64, 64, 64, 64, 64, 638, 638, 638, 638,
		638, 638, 638, 638, 638, 638, 638, 638, 638, 639, 639, 640, 641, 641,
		641, 641, 641, 641, 641, 641, 641, 641, 641, 641, 641, 641, 64, 64, 64,
		642, 642, 642, 642, 642, 642, 642, 643, 643, 643, 643, 643, 643, 643,
		643, 643, 643, 643, 643, 643, 643, 64, 64, 644, 644, 644, 644, 644, 644,
		644, 644, 645, 645, 645, 645, 645, 645, 645, 645, 645, 645, 645, 64, 64,
		64, 64, 64, 646, 646, 646, 646, 646, 646, 646, 646, 647, 647, 647, 647,
		647, 647, 647, 647, 647, 64, 64, 64, 64, 64, 64, 64, 648, 648, 648, 648,
		648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 648, 64, 649, 650, 649,
		651, 651, 651, 651, 651, 651, 651, 651, 651, 651, 651, 651, 651, 650,
		650, 650, 650, 650, 650, 650, 650, 650, 650, 650, 650, 650, 650, 652,
		653, 653, 653, 653, 653, 653, 653, 64, 64, 64, 64, 654, 654, 654, 654,
		654, 654, 654, 654, 654, 654, 654, 654, 654, 654, 654, 654, 654, 654,
		654, 654, 655, 655, 655, 655, 655, 655, 655, 655, 655, 655, 656, 656,
		657, 658, 658, 658, 658, 658, 658, 658, 658, 658, 658, 658, 658, 658,
		657, 657, 657, 656, 656, 656, 656, 657, 657, 659, 660, 661, 661, 662,
		661, 661, 661, 661, 64, 64, 64, 64, 64, 64, 663, 663, 663, 663, 663, 663,
		663, 663, 663, 64, 64, 64, 64, 64, 64, 64, 664, 664, 664, 664, 664, 664,
		664, 664, 664, 664, 64, 64, 64, 64, 64, 64, 665, 665, 665, 666, 666, 666,
		666, 666, 666, 666, 666, 666, 666, 666, 666, 666, 666, 666, 666, 666,
		666, 666, 666, 667, 667, 667, 667, 667, 668, 667, 667, 667, 667, 667,
		667, 669, 669, 64, 670, 670, 670, 670, 670, 670, 670, 670, 670, 670, 671,
		671, 671, 671, 64, 64, 64, 64, 672, 672, 673, 674, 674, 674, 674, 674,
		674, 674, 674, 674, 674, 674, 674, 674, 674, 674, 674, 673, 673, 673,
		672, 672, 672, 672, 672, 672, 672, 672, 672, 673, 675, 674, 674, 674,
		674, 676, 676, 676, 676, 64, 64, 64, 64, 64, 64, 64, 677, 677, 677, 677,
		677, 677, 677, 677, 677, 677, 64, 64, 64, 64, 64, 64, 678, 678, 678, 678,
		678, 678, 678, 678, 678, 678, 678, 679, 680, 679, 680, 680, 679, 679,
		679, 679, 679, 679, 681, 682, 683, 683, 683, 683, 683, 683, 683, 683,
		683, 683, 64, 64, 64, 64, 64, 64, 684, 684, 684, 684, 684, 684, 684, 684,
		684, 684, 684, 684, 684, 684, 684, 64, 685, 685, 685, 685, 685, 685, 685,
		685, 685, 685, 685, 64, 64, 64, 64, 64, 686, 686, 686, 686, 64, 64, 64,
		64, 687, 687, 687, 687, 687, 687, 687, 687, 687, 687, 687, 687, 687, 687,
		687, 64, 504, 64, 64, 64, 64, 64, 64, 64, 688, 688, 688, 688, 688, 688,
		688, 688, 688, 688, 688, 688, 688, 64, 64, 64, 688, 689, 689, 689, 689,
		689, 689, 689, 689, 689, 689, 689, 689, 689, 689, 689, 689, 689, 689,
		689, 689, 689, 689, 64, 64, 64, 64, 64, 64, 64, 64, 690, 690, 690, 690,
		691, 691, 691, 691, 691, 691, 691, 691, 691, 691, 691, 691, 691, 478,
		474, 64, 64, 64, 64, 64, 64, 274, 274, 274, 274, 274, 274, 64, 64, 274,
		274, 274, 274, 274, 274, 274, 64, 64, 274, 274, 274, 274, 274, 274, 274,
		274, 274, 274, 274, 274, 692, 692, 395, 395, 395, 274, 274, 274, 693,
		692, 692, 692, 692, 692, 405, 405, 405, 405, 405, 405, 405, 405, 133,
		133, 133, 133, 133, 133, 133, 133, 274, 274, 78, 78, 78, 78, 78, 133,
		133, 274, 274, 274, 274, 274, 274, 78, 78, 78, 78, 274, 274, 602, 602,
		694, 694, 694, 602, 64, 64, 514, 514, 64, 64, 64, 64, 64, 64, 434, 434,
		434, 434, 434, 434, 434, 434, 434, 434, 34, 34, 34, 34, 34, 34, 34, 34,
		34, 34, 34, 34, 34, 34, 34, 34, 34, 34, 434, 434, 434, 434, 434, 434,
		434, 434, 434, 434, 34, 34, 34, 34, 34, 34, 34, 64, 34, 34, 34, 34, 34,
		34, 434, 64, 434, 434, 64, 64, 434, 64, 64, 434, 434, 64, 64, 434, 434,
		434, 434, 64, 434, 434, 34, 34, 64, 34, 64, 34, 34, 34, 34, 34, 34, 34,
		64, 34, 34, 34, 34, 34, 34, 34, 434, 434, 64, 434, 434, 434, 434, 64, 64,
		434, 434, 434, 434, 434, 434, 434, 434, 64, 434, 434, 434, 434, 434, 434,
		434, 64, 34, 34, 434, 434, 64, 434, 434, 434, 434, 64, 434, 434, 434,
		434, 434, 64, 434, 64, 64, 64, 434, 434, 434, 434, 434, 434, 434, 64, 34,
		34, 34, 34, 34, 34, 34, 34, 34, 34, 34, 34, 64, 64, 434, 695, 34, 34, 34,
		34, 34, 34, 34, 34, 34, 437, 34, 34, 34, 34, 34, 34, 434, 434, 434, 434,
		434, 434, 434, 434, 434, 695, 34, 34, 34, 34, 34, 34, 34, 34, 34, 437,
		34, 34, 434, 434, 434, 434, 434, 695, 34, 34, 34, 34, 34, 34, 34, 34, 34,
		437, 34, 34, 34, 34, 34, 34, 434, 434, 434, 434, 434, 434, 434, 434, 434,
		695, 34, 437, 34, 34, 34, 34, 34, 34, 34, 34, 434, 34, 64, 64, 696, 696,
		696, 696, 696, 696, 696, 696, 696, 696, 123, 123, 123, 123, 64, 123, 123,
		123, 64, 123, 123, 64, 123, 64, 64, 123, 64, 123, 123, 123, 123, 123,
		123, 123, 123, 123, 123, 64, 123, 123, 123, 123, 64, 123, 64, 123, 64,
		64, 64, 64, 64, 64, 123, 64, 64, 64, 64, 123, 64, 123, 64, 123, 64, 123,
		123, 123, 64, 123, 64, 123, 64, 123, 64, 123, 64, 123, 123, 123, 123, 64,
		123, 64, 123, 123, 64, 123, 123, 123, 123, 123, 123, 123, 123, 123, 64,
		64, 64, 64, 64, 123, 123, 123, 64, 123, 123, 123, 111, 111, 64, 64, 64,
		64, 64, 64, 33, 33, 33, 64, 64, 64, 64, 64, 445, 445, 445, 445, 445, 445,
		274, 64, 445, 445, 26, 26, 64, 64, 64, 64, 445, 445, 445, 64, 64, 64, 64,
		64, 64, 64, 64, 64, 64, 64, 274, 274, 697, 481, 481, 64, 64, 64, 64, 64,
		481, 481, 481, 64, 64, 64, 64, 64, 481, 64, 64, 64, 64, 64, 64, 64, 481,
		481, 64, 64, 64, 64, 64, 64, 26, 64, 64, 64, 64, 64, 64, 64, 26, 26, 26,
		26, 26, 26, 64, 26, 26, 26, 26, 26, 26, 64, 64, 64, 26, 26, 26, 26, 26,
		64, 26, 26, 26, 64, 26, 26, 26, 26, 26, 26, 64, 26, 26, 26, 26, 64, 64,
		64, 26, 26, 26, 26, 26, 26, 64, 64, 64, 64, 64, 26, 26, 26, 26, 26, 26,
		64, 64, 64, 64, 26, 26, 26, 489, 489, 489, 489, 489, 489, 488, 490, 490,
		490, 490, 490, 490, 490, 64, 64, 64, 405, 64, 64, 64, 64, 64, 64, 405,
		405, 405, 405, 405, 405, 405, 405, 561, 561, 561, 561, 561, 560, 64, 64,
	];
}
