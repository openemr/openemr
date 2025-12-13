<?php

 // Copyright (C) 2011 Ensoftek
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This program is the base class to implement XML writer.

class XmlWriterOemr
{
    public $xml;
    public $stack = [];
    function __construct(public $indent = '  ')
    {
        $this->xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    }
    function _indent()
    {
        for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
            $this->xml .= $this->indent;
        }
    }
    function push($element, $attributes = [])
    {
        $this->_indent();
        $this->xml .= '<' . $element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' ' . htmlspecialchars((string) $key) . '="' . htmlspecialchars((string) $value) . '"';
        }

        $this->xml .= ">\n";
        $this->stack[] = htmlspecialchars((string) $element);
    }
    function element($element, $content, $attributes = [])
    {
        $this->_indent();
        $this->xml .= '<' . $element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' ' . htmlspecialchars((string) $key) . '="' . htmlspecialchars((string) $value) . '"';
        }

        $this->xml .= '>' . htmlspecialchars((string) $content) . '</' . htmlspecialchars((string) $element) . '>' . "\n";
    }
    function emptyelement($element, $attributes = [])
    {
        $this->_indent();
        $this->xml .= '<' . htmlspecialchars((string) $element);
        foreach ($attributes as $key => $value) {
            $this->xml .= ' ' . htmlspecialchars((string) $key) . '="' . htmlspecialchars((string) $value) . '"';
        }

        $this->xml .= " />\n";
    }
    function pop()
    {
        $element = array_pop($this->stack);
        $this->_indent();
        $this->xml .= "</" . htmlspecialchars((string) $element) . ">" . "\n";
    }
    function getXml()
    {
        return $this->xml;
    }
}
