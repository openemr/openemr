<?php

use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\MSH;

class Parser_HL7v2
{

    var $field_separator;
    var $map;
    var $message;
    var $message_type;

    var $MSH;
    var $EVN;

    function __construct($message, $_options = null)
    {
        $this->message = new Message($message);
        $this->options = [];
        if (is_array($_options)) {
            $this->options = $_options;
        }
    }

    function parse()
    {
        $segments = $this->message->getSegments();

        // Fail if there are no or one segments
        if (count($segments) <= 1) {
            return false;
        }

        // create return array
        $cmp = array();

        // Loop through messages
        $count = 0;
        $segmentMethods = get_class_methods(new Segment('XYZ'));
        foreach ($segments as $key => $segment) {
            $type = $segment->getName();
            $cmp["$type"] = array();

            $classMethods = get_class_methods($segment);

            foreach ($classMethods as $index => $method) {
                if (
                    substr($method, 0, 3) == "get" &&
                    (!in_array($method, $segmentMethods)) &&
                    ($segment->$method())
                ) {
                    $cmp[$type][] = substr($method, 3) . " " . $segment->$method();
                }
            }
            $count++;
        }
        return $cmp;
    }
}
