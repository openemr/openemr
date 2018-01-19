<?php
/**
 * HTML2PDF Library - HTML2PDF Locale
 *
 * HTML => PDF convertor
 * distributed under the LGPL License
 *
 * @package   Html2pdf
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @copyright 2016 Laurent MINGUET
 */
class HTML2PDF_locale
{
    /**
     * code of the current used locale
     * @var string
     */
    static protected $_code = null;

    /**
     * texts of the current used locale
     * @var array
     */
    static protected $_list = array();

    /**
     * directory where locale files are
     * @var string
     */
    static protected $_directory = null;

    /**
     * load the locale
     *
     * @access public
     * @param  string $code
     */
    static public function load($code)
    {
        if (self::$_directory===null) {
            self::$_directory = dirname(dirname(__FILE__)).'/locale/';
        }

        // must be in lower case
        $code = strtolower($code);

        // must be [a-z-0-9]
        if (!preg_match('/^([a-z0-9]+)$/isU', $code)) {
            throw new HTML2PDF_exception(0, 'invalid language code ['.self::$_code.']');
        }

        // save the code
        self::$_code = $code;

        // get the name of the locale file
        $file = self::$_directory.self::$_code.'.csv';

        // the file must exist
        if (!is_file($file)) {
            throw new HTML2PDF_exception(0, 'language code ['.self::$_code.'] unknown. You can create the translation file ['.$file.'] and send it to the webmaster of html2pdf in order to integrate it into a future release');
        }

        // load the file
        self::$_list = array();
        $handle = fopen($file, 'r');
        while (!feof($handle)) {
            $line = fgetcsv($handle);
            if (count($line)!=2) continue;
            self::$_list[trim($line[0])] = trim($line[1]);
        }
        fclose($handle);
    }

    /**
     * clean the locale
     *
     * @access public static
     */
    static public function clean()
    {
        self::$_code = null;
        self::$_list = array();
    }

    /**
     * get a text
     *
     * @access public static
     * @param  string $key
     * @return string
     */
    static public function get($key, $default='######')
    {
        return (isset(self::$_list[$key]) ? self::$_list[$key] : $default);
    }
}