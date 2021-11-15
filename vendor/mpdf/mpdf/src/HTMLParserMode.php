<?php

namespace Mpdf;

class HTMLParserMode
{
	/**
	 * Parses a whole $html document
	 */
	const DEFAULT_MODE = 0;

	/**
	 * Parses the $html as styles and stylesheets only
	 */
	const HEADER_CSS = 1;

	/**
	 * Parses the $html as output elements only
	 */
	const HTML_BODY = 2;

	/**
	 * (For internal use only - parses the $html code without writing to document)
	 *
	 * @internal
	 */
	const HTML_PARSE_NO_WRITE = 3;

	/**
	 * (For internal use only - writes the $html code to a buffer)
	 *
	 * @internal
	 */
	const HTML_HEADER_BUFFER = 4;

	public static function getAllModes()
	{
		return [
			self::DEFAULT_MODE,
			self::HEADER_CSS,
			self::HTML_BODY,
			self::HTML_PARSE_NO_WRITE,
			self::HTML_HEADER_BUFFER,
		];
	}
}
