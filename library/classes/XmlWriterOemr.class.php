<?php
 // Copyright (C) 2011 Ensoftek 
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This program is the base class to implement XML writer. 

class XmlWriterOemr {
    var $xml;
    var $indent;
    var $stack = array();
    function XmlWriterOemr($indent = '  ') {
        $this->indent = $indent;
        $this->xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
    }
    function _indent() {
        for ($i = 0, $j = count($this->stack); $i < $j; $i++) {
            $this->xml .= $this->indent;
        }
    }
    function push($element, $attributes = array()) {
        $this->_indent();
        $this->xml .= '<'.$element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
        }
        $this->xml .= ">\n";
        $this->stack[] = htmlspecialchars($element);
    }
    function element($element, $content, $attributes = array()) {
        $this->_indent();
        $this->xml .= '<'.$element;
        foreach ($attributes as $key => $value) {
            $this->xml .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
        }
        $this->xml .= '>'.htmlspecialchars($content).'</'.htmlspecialchars($element).'>'."\n";
    }
    function emptyelement($element, $attributes = array()) {
        $this->_indent();
        $this->xml .= '<'.htmlspecialchars($element);
        foreach ($attributes as $key => $value) {
            $this->xml .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
        }
        $this->xml .= " />\n";
    }
    function pop() {
        $element = array_pop($this->stack);
        $this->_indent();
        $this->xml .= "</".htmlspecialchars($element).">"."\n";
    }
    function getXml() {
        return $this->xml;
    }
}

?>