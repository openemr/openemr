<?php

namespace OpenEMR\Services\Cda;

use DOMNode;
use DOMXPath;

/**
 * DOMXPath envelope.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SchematronXPath extends DOMXPath
{
    /**
     * ($registerNodeNS is FALSE in opposition to DOMXPath default value)
     */
    public function query($expression, DOMNode $context = null, $registerNodeNS = false)
    {
        return parent::query($expression, $context, $registerNodeNS);
    }


    /**
     * ($registerNodeNS is FALSE in opposition to DOMXPath default value)
     */
    public function evaluate($expression, DOMNode $context = null, $registerNodeNS = false)
    {
        return parent::evaluate($expression, $context, $registerNodeNS);
    }


    public function queryContext($expression, DOMNode $context = null, $registerNodeNS = false)
    {
        if (isset($expression[0]) && $expression[0] !== '.' && $expression[0] !== '/') {
            $expression = "//$expression";
        }
        return $this->query($expression, $context, $registerNodeNS);
    }
}
