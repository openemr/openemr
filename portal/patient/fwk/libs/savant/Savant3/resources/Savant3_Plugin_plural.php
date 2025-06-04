<?php

/**
 *
 */
class Savant3_Plugin_plural extends Savant3_Plugin
{
    public function plural($string)
    {
        $lastletter = substr($string, - 1);
        if ($lastletter == 'y') {
            return substr($string, 0, - 1) . 'ies';
        }

        if ($lastletter == 's' || $lastletter == 'x' || $lastletter == 'z') {
            return $string . 'es';
        }

        $last2letters = substr($string, - 2);
        if ($last2letters == 'sh' || $last2letters == 'ch') {
            return $string . 'es';
        }

        return $string . 's';
    }
}
