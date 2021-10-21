<?php

use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segment;
use Aranyasen\HL7\Segments\MSH;

class Parser_HL7v2
{

    protected $message;
    protected $options;

    public function __construct($message, $_options = null)
    {
        $this->message = new Message($message);
        $this->options = [];
        if (is_array($_options)) {
            $this->options = $_options;
        }
    }

    public function parse()
    {
        $segments = $this->message->getSegments();

        // Fail if there are no or one segments
        if (count($segments) <= 1) {
            return false;
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

    // https://gist.github.com/jimmygle/2564610#gistcomment-3634215
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
