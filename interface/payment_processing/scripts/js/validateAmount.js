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
<?php
require_once("../../globals.php");
?>
function validateAmount(){
			var message = "";
			
			var chargeAmount  = document.getElementById("amount").value;
				
				if(!chargeAmount){
					message = "Please enter an amount to be charged. \n";
					amount.style.backgroundColor="#FFB6C1" ;
					
					alert(message);
					return false;
				}
				
				if(isNaN(chargeAmount) || chargeAmount.match(/^\s+|\s+$/gm)){
					message = "This field sholud contain a number. \n";
					amount.style.backgroundColor="#FFB6C1" ;
					
					alert(message);
					return false;
					
				}
				
						
			return true;
		}
		