<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Generator;

/**
 * XML generator adapter interface
 */
interface GeneratorInterface
{
    public function getEncoding();
    public function setEncoding($encoding);
    public function openElement($name, $value = null);
    public function closeElement($name);

    /**
     * Return XML as a string
     *
     * @return string
     */
    public function saveXml();

    public function stripDeclaration($xml);
    public function flush();
    public function __toString();
}
