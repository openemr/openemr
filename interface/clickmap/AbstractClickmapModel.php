<?php

/*
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @file AbstractClickmapModel.php
 *
 * @brief This file contains the AbstractClickmapModel class, used to model form contents.
 *
 */

/* for $GLOBALS['srcdir','pid'] */
/* remember that include paths are calculated relative to the including script, not this file. */
require_once(dirname(__FILE__) . '/../globals.php');

use OpenEMR\Common\ORDataObject\ORDataObject;

/**
 * @class AbstractClickmapModel
 *
 * @brief code This class extends the OrDataObject class, which is used to model form data for a smarty generated form.
 *
 * This class extends the ORDataObject class, to model the contents of an image-based form.
 */
abstract class AbstractClickmapModel extends ORDataObject
{
    /**
     * FIXME: either last modification date OR creation date?
     *
     * @var date
     */
    var $date;
    /**
     * The unique identifier of the patient this form belongs to.
     *
     * @var pid
     */
    var $pid;
    /**
     * required field in database table. not used, always defaulted to NULL.
     *
     * @var user
     */
    var $user;
    /**
     * required field in database table. not used, always defaulted to NULL.
     *
     * @var groupname
     */
    var $groupname;
    /**
     *
     * required field in the database table. always defaulted to NULL.
     *
     * @var authorized
     */
    var $authorized;
    /**
     *
     * required field in the database table. always defaulted to NULL.
     *
     * @var activity
     */
    var $activity;
    /**
     *
     * The contents of our form, in one field.
     *
     * @var data
     */
    var $data;

    /**
     * Initialize a newly created object belonging to this class
     *
     * @return void
     */
    protected function init(): void
    {
        $this->date = date("Y-m-d H:i:s");
        $this->data = "";
        $this->pid = $GLOBALS['pid'];
    }

    /**
     * @brief Override this abstract function with your implementation of getTitle.
     *
     * @return The title of this form.
     */
    abstract function getTitle();

    /**
     * @brief Override this abstract function with your implementation of getCode.
     *
     * @return A string thats a 'code' for this form.
     */
    abstract function getCode();

    /**
     * @brief Store the current structure members representing the form into the database.
     */
    function persist()
    {
        /* Run our parent's implementation. */
        parent::persist();
    }

    /* The rest of this object consists of set_ and get_ pairs, for setting and getting the value of variables that are members of this object. */
    function get_pid()
    {
        return $this->pid;
    }

    function set_pid($pid)
    {
        if (!empty($pid) && is_numeric($pid)) {
            $this->pid = $pid;
        } else {
            trigger_error('API violation: set function called with empty or non numeric string.', E_USER_WARNING);
        }
    }

    function get_activity()
    {
        return $this->activity;
    }

    function set_activity($tf)
    {
        if (!empty($tf) && is_numeric($tf)) {
            $this->activity = $tf;
        } else {
            trigger_error('API violation: set function called with empty or non numeric string.', E_USER_WARNING);
        }
    }

    /* get_date()
     *
     */
    function get_date()
    {
        return $this->date;
    }

    /* set_date()
     *
     */
    function set_date($dt)
    {
        if (!empty($dt)) {
            $this->date = $dt;
        } else {
            trigger_error('API violation: set function called with empty string.', E_USER_WARNING);
        }
    }

    function get_user()
    {
        return $this->user;
    }

    function set_user($u)
    {
        if (!empty($u)) {
            $this->user = $u;
        } else {
            trigger_error('API violation: set function called with empty string.', E_USER_WARNING);
        }
    }

    function get_data()
    {
        return $this->data;
    }

    function set_data($data)
    {
        if (!empty($data)) {
            $this->data = $data;
        } else {
            trigger_error('API violation: set function called with empty string.', E_USER_WARNING);
        }
    }
}
