<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once("RsPatient.php");
/*  Defines a population of patients
 *
 */
class RsPopulation implements Countable, Iterator, ArrayAccess
{
    protected $_patients = array();

    /*
     * initialize the patient population
     */
    public function __construct(array $patientIdArray)
    {
        foreach ($patientIdArray as $patientId) {
            $this->_patients[] = new RsPatient($patientId);
        }
    }

    /*
     * Countable Interface
     */
    public function count(): int
    {
        return count($this->_patients);
    }

    /*
     * Iterator Interface
     */
    public function rewind(): void
    {
        reset($this->_patients);
    }

    /**
     * @return RsPatient
     */
    public function current()
    {
        return current($this->_patients);
    }

    public function key()
    {
        return key($this->_patients);
    }

    public function next()
    {
        return next($this->_patients);
    }

    public function valid(): bool
    {
        return $this->current() !== false;
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
            throw new Exception("Value must be an instance of RsPatient");
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->_patients[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->_patients[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_patients[$offset]) ? $this->container[$offset] : null;
    }
}
