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
 * @version 2.0 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
//In order to allow xl function this js scrpit is being echoed out from a php file
require_once('../../globals.php');

// all strings that need to be translated
	  	$lang=array( 'please_select_a_payment_method' => htmlspecialchars(xl('Please select a payment method'), ENT_NOQUOTES),
					 'please_enter_a_check_number' => htmlspecialchars(xl('Please enter a check number'), ENT_NOQUOTES),
		
		);
		

$js_str = <<<EOF
<script>

function validateCheckNo(){
			var message = '';
			var selectedPayMethod = '';
			
			var thisForm = document.getElementById('form-amount');
			var payMethod = thisForm.elements['pay_method'];
			var checkNumber = document.getElementById('check_number')
			
			
			 // loop through list of radio buttons
			for (var i=0; i<payMethod.length; i++) {
				if (payMethod[i].checked) { // radio checked?
					selectedPayMethod = payMethod[i].value; // if so, hold its value 
					break; // and break out of for loop
				}
				else{selectedPayMethod = '';}
			}
			
			//test for no selected payment method radio buttons			
			if(selectedPayMethod ===''){
				message = '{$lang['please_select_a_payment_method']}.';
				document.getElementById('pay-method-div').style.backgroundColor = '#FFB6C1' ;		 
				alert(message);
				return false;
			 }
			 
			 //test for no check number if check radio button is selected
			 if(selectedPayMethod == 'Q' && !checkNumber.value){
				message = '{$lang['please_enter_a_check_number']}.';
				checkNumber.style.backgroundColor = '#FFB6C1' ;
				alert(message);
				return false;				
			 }
			 
			return true;
		}
</script>
EOF;
echo $js_str;		
?>		

	
	