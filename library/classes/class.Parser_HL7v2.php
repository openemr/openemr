<?php

/**
 * Parser_HL7v2 Class.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;

class Parser_HL7v2
{
    /**
    * @var Message
    */
    protected $message;

    public function __construct(string $message)
    {
        $this->message = new Message($message);
    }

    public function parse(): array
    {
        $segments = $this->message->getSegments();

        // Fail if there are no or one segments
        if (count($segments) <= 1) {
            return [];
        }

        // create return array
        $cmp = array();

        // Loop through messages
        $segmentMethods = get_class_methods(new Segment('XYZ'));
        foreach ($segments as $key => $segment) {
            $type = $segment->getName();

            $classMethods = get_class_methods($segment);

            foreach ($classMethods as $index => $method) {
                if (
                    substr($method, 0, 3) == "get" &&
                    !in_array($method, $segmentMethods) &&
                    $segment->$method()
                ) {
                    $data = $segment->$method();
                    if (is_array($data)) {
                        $data = $this->implode_recursive(', ', $segment->$method());
                    }
                    $cmp[$type][] = substr($method, 3) . " " . $data;
                }
            }
        }
        return $cmp;
    }


    /**
     * Recursively implode arrays in the hl7 fields.
     *
     * https://gist.github.com/jimmygle/2564610#gistcomment-3634215
     */
    private function implode_recursive(string $separator, array $array): string
    {
        $string = '';
        foreach ($array as $i => $a) {
            if (is_array($a)) {
                $string .= $this->implode_recursive($separator, $a);
            } else {
                $string .= $a;
                if ($i < count($array) - 1) {
                    $string .= $separator;
                }
            }
        }

        return $string;
    }
}
