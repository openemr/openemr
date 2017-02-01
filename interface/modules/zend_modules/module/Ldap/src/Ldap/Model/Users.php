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
*    @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
* +------------------------------------------------------------------------------+
 *
 */

namespace Ldap\Model;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class Ldap implements InputFilterAwareInterface
{
    protected $inputFilter;
    public $id;
    public $username;
    public $fname;
    public $lname;

    public function exchangeArray($data)
    {

        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->username     = (!empty($data['username'])) ? $data['username'] : null;
        $this->fname = (!empty($data['fname'])) ? $data['fname'] : null;
        $this->lname = (!empty($data['lname'])) ? $data['lname'] : null;


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
            'name'     => 'username',
            'required' => true,

        ),

        array(
            'name'     => 'fname',
            'required' => true,

        ),

        array(
            'name'     => 'lname',
            'required' => true,

        ),


    );


    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            foreach(self::$inputsValidations as $input) {
                $inputFilter->add($input);
            }

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}