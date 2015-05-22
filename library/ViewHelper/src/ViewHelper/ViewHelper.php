<?php
/**
 * 
 */

namespace OpenEMR\ViewHelper;

class ViewHelper
{

    private $_globals;

    public function __construct()
    {
    }

    public function setGlobals($globals = null)
    {
        $this->_globals = ($globals) ? $globals : $GLOBALS;
    }

    /**
     * Return a nicely formatted money string
     * 
     * @param  int     $amount Amount to format
     * @return bool    $symbol Prepend currency symbol to amount
     */
    static public function money($amount, $symbol = false)
    {
        $s = number_format($amount,
        $this->_globals['currency_decimals'],
        $this->_globals['currency_dec_point'],
        $this->_globals['currency_thousands_sep']);

        // If the currency symbol exists and is requested, prepend it.
        if ($symbol && !empty($GLOBALS['gbl_currency_symbol'])) {
            $s = $GLOBALS['gbl_currency_symbol'] . ' ' . $s;
        }

        return $s;
    }

    /**
     * Transform date from yyyy-mm-dd to either mm/dd/yyyy or dd/mm/yyyy
     * 
     * @param  string $date Date in yyyy-mm-dd format. If empty, today is used
     * @return string       Transformed date
     */
    static public function shortDate($date = null, $format = 1)
    {
        $date = ($date != null) ? $date : date('Y-m-d');

        if (strlen($date) == 10) {
            // assume input is yyyy-mm-dd
            switch ($format) {
                case 2:
                    $date = substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
                    break;

                case 1:
                default:
                    $date = substr($date, 5, 2) . '/' . substr($date, 8, 2) . '/' . substr($date, 0, 4);
                    break;

            }
        }
        return $date;
    }
}
