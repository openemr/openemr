<?php

/**
 * User.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** import supporting libraries */
require_once("DAO/UserDAO.php");
require_once("UserCriteria.php");

/**
 * The User class extends UserDAO which provides the access
 * to the datastore.
 *
 * @package Openemr::Model
 * @author ClassBuilder
 * @version 1.0
 */
class User extends UserDAO
{
    /**
     * Override default validation
     * @see Phreezable::Validate()
     */
    public function Validate()
    {
        // example of custom validation
        // $this->ResetValidationErrors();
        // $errors = $this->GetValidationErrors();
        // if ($error == true) $this->AddValidationError('FieldName', 'Error Information');
        // return !$this->HasValidationErrors();

        return parent::Validate();
    }

    /**
     * @see Phreezable::OnSave()
     */
    public function OnSave($insert)
    {
        // the controller create/update methods validate before saving.  this will be a
        // redundant validation check, however it will ensure data integrity at the model
        // level based on validation rules.  comment this line out if this is not desired
        if (!$this->Validate()) {
            throw new Exception('Unable to Save User: ' .  implode(', ', $this->GetValidationErrors()));
        }

        // OnSave must return true or Phreeze will cancel the save operation
        return true;
    }
}
