<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {counter} function plugin
 *
 * Type:     function<br>
 * Name:     counter<br>
 * Purpose:  print out a counter value
 * @author Monte Ohrt <monte at ohrt dot com>
 * @link http://smarty.php.net/manual/en/language.function.counter.php {counter}
 *       (Smarty online manual)
 * @param array parameters
 * @param Smarty
 * @return string|null
 */
function smarty_function_counter($params, &$smarty)
{
    static $counters = [];

    $name = $params['name'] ?? 'default';
    if (!isset($counters[$name])) {
        $counters[$name] = [
            'start'=>1,
            'skip'=>1,
            'direction'=>'up',
            'count'=>1
            ];
    }
    $counter =& $counters[$name];

    if (isset($params['start'])) {
        $counter['start'] = $counter['count'] = (int)$params['start'];
    }

    if (!empty($params['assign'])) {
        $counter['assign'] = $params['assign'];
    }

    if (isset($counter['assign'])) {
        $smarty->assign($counter['assign'], $counter['count']);
    }

    $print = isset($params['print']) ? (bool)$params['print'] : empty($counter['assign']);

    $retval = $print ? $counter['count'] : null;

    if (isset($params['skip'])) {
        $counter['skip'] = $params['skip'];
    }

    if (isset($params['direction'])) {
        $counter['direction'] = $params['direction'];
    }

    if ($counter['direction'] == "down")
        $counter['count'] -= $counter['skip'];
    else
        $counter['count'] += $counter['skip'];

    return $retval;

}

/* vim: set expandtab: */

?>
