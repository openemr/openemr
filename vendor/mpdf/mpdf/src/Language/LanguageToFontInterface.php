<?php

namespace Mpdf\Language;

/**
 * mPDF recognises IETF language tags as:
 * - a single primary language subtag composed of a two letter language code from ISO 639-1 (2002),
 *     or a three letter code from ISO 639-2 (1998), ISO 639-3 (2007) or ISO 639-5 (2008) (usually written in lower case);
 * - an optional script subtag, composed of a four letter script code from ISO 15924 (usually written in title case);
 * - an optional region subtag composed of a two letter country code from ISO 3166-1 alpha-2 (usually written in upper case),
 *     or a three digit code from UN M.49 for geographical regions;
 *
 * Subtags are not case sensitive, but the specification recommends using the same case as in the Language Subtag Registry,
 *     where region subtags are uppercase, script subtags are titlecase and all other subtags are lowercase.
 *
 * Region subtags are often deprecated by the registration of specific primary language subtags from ISO 639-3 which are now
 *    "preferred values". For example, "ar-DZ" is deprecated with the preferred value "arq" for Algerian Spoken Arabic;
 *
 * Example: Serbian written in the Cyrillic (sr-Cyrl) or Latin (sr-Latn) script
 *
 * und (for undetermined or undefined) is used in situations in which a script must be indicated but the language cannot be identified.
 * e.g. und-Cyrl is an undefined language written in Cyrillic script.
 */
interface LanguageToFontInterface
{

	public function getLanguageOptions($llcc, $adobeCJK);

}
