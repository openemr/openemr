<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

/**
 * Description of Option
 *
 * @author aron
 */
class Option {

    var $id;
    var $label;
    
    function __construct( $id, $label ) {
        $this->id = $id;
        $this->label = $label;
    }
}
?>
