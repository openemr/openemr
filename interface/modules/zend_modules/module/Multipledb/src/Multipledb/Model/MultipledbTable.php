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

use OpenEMR\Common\Crypto\CryptoGen;
use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Predicate;
use Application\Model\ApplicationTable;
use Laminas\Db\Adapter\Adapter;

class MultipledbTable
{
    protected $tableGateway;
    protected $adapter;


    /**
     * MultipledbTable constructor.
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $adapter = \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
        $this->adapter = $adapter;
    }

    public function fetchAll()
    {
        /* $resultSet = $this->tableGateway->select();
         return $resultSet;*/

        $rsArray = array();
        $rs = $this->tableGateway->select();
        foreach ($rs as $r) {
            $rsArray[] = get_object_vars($r);
        }

        return $rsArray;
    }

    public function checknamespace($namespace)
    {
        $rowset = $this->tableGateway->select(array('namespace' => $namespace));
        $count = $rowset->count();

        if ($count and $_SESSION['multiple_edit_id'] == 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function storeMultipledb($id = 0, $db = array())
    {

        if ($db['password']) {
            $cryptoGen = new CryptoGen();
            $db['password'] = $cryptoGen->encryptStandard($db['password']);
        } else {
            unset($db['password']);
        }

        if ($id) {
            $this->tableGateway->update($db, array('id' => $id));
        } else {
            $this->tableGateway->insert($db);
        }
    }

    public function deleteMultidbById($id)
    {
        $this->tableGateway->delete(array('id' => (int)$id));
    }

    public function getMultipledbById($id)
    {

        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return false;
            //throw new \Exception("Could not find row $serial_no");
        }

        return $row;
    }


    public function randomSafeKey()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890$%&#@(){}[]<>~=?.*+-!';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 32; $i++) {
            $n = mt_rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass); //turn the array into a string
    }
}
