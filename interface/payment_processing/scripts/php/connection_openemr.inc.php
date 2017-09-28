<?php

/**
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 * 
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>, Ranganath Pathak
 * @copyright Copyright (c) 2016, Sherwin Gaddis, Ranganath Pathak
 * @version 1.0 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


	require "../scripts/php/connection.inc.php" ;
	
	 try {
			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//echo "Connected to : " . $dbname . "<br>";
		 } 
	 catch(PDOException $e) {
			$error = $e->getMessage();
			echo "Database Error: $error <br />";
		}
?>