<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once("CqmPatient.php");
/*  Defines a population of patients
 *
 */
class CqmPopulation extends RsPopulation
{
    /*
     * initialize the patient population
     */
    public function __construct(array $patientIdArray)
    {
        foreach ($patientIdArray as $patientId) {
            $this->_patients[] = new CqmPatient($patientId);
        }
    }

    /*
     * ArrayAccess Interface
     */
    public function offsetSet($offset, $value): void
    {
        if ($value instanceof CqmPatient) {
            if ($offset == "") {
                $this->_patients[] = $value;
            } else {
                $this->_patients[$offset] = $value;
            }
        } else {
            throw new Exception("Value must be an instance of CqmPatient");
        }
    }
}
