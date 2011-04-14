<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of Code
 *
 * @author aron
 */
class Code {
    //put your code here

    var $id;
    var $code;
    var $text;
    var $codeType;

    function __construct( $id, $code, $text, $codeType ) {
        $this->id = $id;
        $this->code = $code;
        $this->text = $text;
        $this->codeType = $codeType;
    }

    function display() {
        return $this->codeType . ":" . $this->id . " - " . $this->code . " " . $this->text;
    }
    
}

?>
