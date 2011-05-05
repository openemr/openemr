<?php
/*
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @file FormPainMap.php
 *
 * @brief This file ontains the FormPainMap class, used to model the data contents of a clickmap based form.
 */
/* include the class we are extending. */
require_once ($GLOBALS['fileroot'] . "/interface/clickmap/AbstractClickmapModel.php");

/**
 * @class FormPainMap
 *
 * @brief This class extends the AbstractClickmapModel class, to create a class for modelling the data in a pain form.
 */
class FormPainMap extends AbstractClickmapModel {

    /**
     * The database table to place form data in/read form data from.
     *
     * @var TABLE_NAME
     */
    static $TABLE_NAME = "form_painmap";

    /* Initializer. just alles parent's initializer. */
    function FormPainMap($id="") {
    	parent::AbstractClickmapModel(FormPainMap::$TABLE_NAME, $id);
    }

    /**
     * @brief Return the Title of the form, Useful when calling addform().
     */
    public function getTitle() {
        return C_FormPainMap::$FORM_TITLE;
    }

    /**
     * @brief Return the 'Code' of the form. Again, used when calling addform().
     */
    public function getCode() {
        return C_FormPainMap::$FORM_CODE;
    }
}
?>
