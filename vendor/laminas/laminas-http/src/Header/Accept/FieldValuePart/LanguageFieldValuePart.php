<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header\Accept\FieldValuePart;

/**
 * Field Value Part
 *
 * @see        http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
 */
class LanguageFieldValuePart extends AbstractFieldValuePart
{
    public function getLanguage()
    {
        return $this->getInternalValues()->typeString;
    }

    public function getPrimaryTag()
    {
        return $this->getInternalValues()->type;
    }

    public function getSubTag()
    {
        return $this->getInternalValues()->subtype;
    }
}
