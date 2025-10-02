<?php

/**
 *
 */
class Savant3_Plugin_studlycaps extends Savant3_Plugin
{
    public function studlycaps($string)
    {
        return ucwords((string) preg_replace_callback("/(\_(.))/", create_function('$matches', 'return strtoupper($matches[2]);'), strtolower((string) $string)));
    }
}
