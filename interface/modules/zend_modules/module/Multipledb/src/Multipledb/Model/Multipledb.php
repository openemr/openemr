<?php

/* +-----------------------------------------------------------------------------+
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL
*    @author  Oshri Rozmarin <oshri.rozmarin@gmail.com>
* +------------------------------------------------------------------------------+
 *
 */

namespace Multipledb\Model;

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class Multipledb implements InputFilterAwareInterface
{

    const FIELD_ID = "id";
    public $id;
    public $namespace;
    public $username;
    public $password;
    public $dbname;
    public $host;
    public $port;
    public $date;

    public function exchangeArray($data)
    {

        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->namespace = (!empty($data['namespace'])) ? $data['namespace'] : null;
        $this->username = (!empty($data['username'])) ? $data['username'] : null;
        $this->password = (!empty($data['password'])) ? $data['password'] : null;
        $this->dbname = (!empty($data['dbname'])) ? $data['dbname'] : null;
        $this->host = (!empty($data['host'])) ? $data['host'] : null;
        $this->port = (!empty($data['port'])) ? $data['port'] : null;
        $this->date = (!empty($data['date'])) ? $data['date'] : null;
    }



    public static $inputsValidations = array(
        array(
            'name'     => 'id',
            'required' => true,
            'filters'  => array(
                array('name' => 'Int'),
            ),
        ),
        array(
            'name'     => 'namespace',
            'required' => true,

        ),
        array(
            'name'     => 'username',
            'required' => true,
        ),

        array(
            'name'     => 'password',
            'required' => true,
        ),
        array(
            'name'     => 'dbname',
            'required' => true,
        ),
        array(
            'name'     => 'host',
            'required' => true,
        ),
        array(
            'name'     => 'port',
            'required' => true,
            'filters'  => array(
                array('name' => 'Int'),
            ),
        )

    );


    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            foreach (self::$inputsValidations as $input) {
                $inputFilter->add($input);
            }

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
