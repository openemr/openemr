<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Processor;

/**
 * Processes an event message according to PSR-3 rules.
 *
 * This processor replaces `{foo}` with the value from `$extra['foo']`.
 */
class PsrPlaceholder implements ProcessorInterface
{
    /**
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        if (false === strpos($event['message'], '{')) {
            return $event;
        }

        $replacements = [];
        foreach ($event['extra'] as $key => $val) {
            if (is_null($val)
                || is_scalar($val)
                || (is_object($val) && method_exists($val, "__toString"))
            ) {
                $replacements['{'.$key.'}'] = $val;
                continue;
            }

            if (is_object($val)) {
                $replacements['{'.$key.'}'] = '[object '.get_class($val).']';
                continue;
            }

            $replacements['{'.$key.'}'] = '['.gettype($val).']';
        }

        $event['message'] = strtr($event['message'], $replacements);
        return $event;
    }
}
