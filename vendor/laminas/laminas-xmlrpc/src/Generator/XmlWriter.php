<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Generator;

/**
 * XML generator adapter based on XMLWriter
 */
class XmlWriter extends AbstractGenerator
{
    /**
     * XMLWriter instance
     *
     * @var XMLWriter
     */
    protected $xmlWriter;

    /**
     * Initialized XMLWriter instance
     *
     * @return void
     */
    protected function init()
    {
        $this->xmlWriter = new \XMLWriter();
        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument('1.0', $this->encoding);
    }

    /**
     * Open a new XML element
     *
     * @param string $name XML element name
     * @return void
     */
    protected function openXmlElement($name)
    {
        $this->xmlWriter->startElement($name);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text XML text data
     * @return void
     */
    protected function writeTextData($text)
    {
        $this->xmlWriter->text($text);
    }

    /**
     * Close a previously opened XML element
     *
     * @param string $name
     * @return XmlWriter
     */
    protected function closeXmlElement($name)
    {
        $this->xmlWriter->endElement();

        return $this;
    }

    /**
     * Emit XML document
     *
     * @return string
     */
    public function saveXml()
    {
        return $this->xmlWriter->flush(false);
    }
}
