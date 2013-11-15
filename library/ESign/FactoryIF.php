<?php

namespace ESign;

/**
 * FactoryIF interface represents an object that is capable
 * of creating a complete ESign object. Used by the Api class
 * to assemble the ESign object. 
 * 
 * @see \Esign\Api::createESign( FactoryIF $factory )
 * 
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

interface FactoryIF
{
    /**
     * Returns an instance of ConfigurationIF
     */
    public function createConfiguration();
    
    /**
     * Returns an instance of SignableIF
     */
    public function createSignable();
    
    /**
     * Returns an instance of ButtonIF
     */
    public function createButton();
    
    /**
     * Returns an instance of LogIF
     */
    public function createLog();
}

?>