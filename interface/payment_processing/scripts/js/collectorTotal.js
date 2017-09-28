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

/*







*/
	  	$lang=array( 'all_collections' => htmlspecialchars(xl('All Collections'), ENT_NOQUOTES),
					 'currency' => htmlspecialchars(xl('$'), ENT_NOQUOTES),
					 'total_cash_collections' => htmlspecialchars(xl('Total Cash Collections'), ENT_NOQUOTES),
					 'total_check_collections' => htmlspecialchars(xl('Total Check Collections'), ENT_NOQUOTES),
					 'total_credit_collections' => htmlspecialchars(xl('Total Credit Collections'), ENT_NOQUOTES),
					 'all_collections_by' => htmlspecialchars(xl('All collections by'), ENT_NOQUOTES),
					 'for' => htmlspecialchars(xl('for'), ENT_NOQUOTES)
		
		);
		
 
 

function collectorTotal(payDate, payCollector){
	// To calculate the total of a each payment method for a particular collector
	var payDate = payDate.trim();
	var payCollector = payCollector;
	
	var ccc = document.getElementById('credit-cash-check');
	var cellTotal = 0;
	var cashTotal = 0;
	var checkTotal = 0;
	var creditTotal = 0;
	
	for(var i=1;i<ccc.rows.length;i++) {// make i = 1 to not count the thead row
		var trs = ccc.getElementsByTagName("tr")[i];
		
		if(trs.cells.length > 2 ){ // !!IMPORTANT: remember to look for cells that exist in a VALID row, adjust logic accordingly
			//alert("cells Length: " + trs.cells.length + "\n" + "cells8: " + parseFloat(trs.cells[8].firstChild.data) + "\n" + "cells9: " + trs.cells[9].firstChild.nodeValue);
			var cellValDate= trs.cells[1].firstChild.data;
			//var cellValMethod=trs.cells[5].firstChild.innerHTML;
			
			var cellValCollector = trs.cells[4].firstChild.data;
			
			
			if(trs.cells.length == 10 ){
				var cellValAmount= parseFloat(trs.cells[8].firstChild.data);
				var cellValMethod=trs.cells[9].firstChild.nodeValue;// changed on 1/27/2016 to accomodate for foreign languages thae chenge value of Pmt Method Cell
			}
			else if(trs.cells.length > 10 ){
				var cellValAmount= parseFloat(trs.cells[9].firstChild.data);
				var cellValMethod=trs.cells[10].firstChild.nodeValue;// changed on 1/27/2016 to accomodate for foreign languages thae chenge value of Pmt Method Cell
			}
			
			cellValDate = cellValDate.trim();
			cellValMethod = cellValMethod.trim();
			
			if (!isNaN(cellValAmount) && cellValCollector == payCollector &&  cellValDate == payDate){// criteria to limit the rows used
				
				cellTotal += cellValAmount;
				
				if(cellValMethod == "Cash" ){
					cashTotal += cellValAmount;
				}
				else if (cellValMethod == "Check" ){
					checkTotal += cellValAmount;
				}
				else if (cellValMethod == "Credit" ){
					creditTotal += cellValAmount;
				}
				
			}
		}
	} 

	
	
	//conditional selection of modal color 
	var modalBackground = "collector-light";
	var modalHeaderBackground = "collector-dark";
	
	
	// defining the various elements that need to be worked on 
	var displayModal = document.getElementById("modalWrapper");
	var popupModal = document.getElementById("popupModal");
	var modalHeader = document.getElementById("modalHeader");
	var modalBody = document.getElementById("modalBody");   
	
	var modalBodyLine1Left = document.getElementById("line1-left");
	var modalBodyLine1Right = document.getElementById("line1-right");
	
	var modalBodyLine2Left = document.getElementById("line2-left");
	var modalBodyLine2Right = document.getElementById("line2-right");
	
	var modalBodyLine3Left = document.getElementById("line3-left");
	var modalBodyLine3Right = document.getElementById("line3-right");
	
	var modalBodyLine4Left = document.getElementById("line4-left");
	var modalBodyLine4Right = document.getElementById("line4-right");

	/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	Total Cash Collections: $100.00
	Total Check Collections: $89.75
	Total Credit Collections: $125.00
	Total collections for 2015-12-09: $756.93
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
	
	// styling the color of the modal
	popupModal.className = "popup-modal " + modalBackground;
	modalHeader.className = "modal-header " + modalHeaderBackground;
	modalBodyLine1Right.className = "line-right cash-dark";
	modalBodyLine2Right.className = "line-right  check-dark";
	modalBodyLine3Right.className = "line-right  credit-dark";
	modalBodyLine4Right.className = "line-right highlight";
	
	// content of the modal	
	modalHeader.innerHTML = payCollector + " - " + "All Collections" + " - " + payDate ;
			
	modalBodyLine1Left.innerHTML = "Total Cash Collections:";
	modalBodyLine1Right.innerHTML = "$"+ cashTotal.toFixed(2);
	
	modalBodyLine2Left.innerHTML = "Total Check Collections:";
	modalBodyLine2Right.innerHTML = "$"+ checkTotal.toFixed(2);
	
	modalBodyLine3Left.innerHTML = "Total Credit Collections:";
	modalBodyLine3Right.innerHTML = "$"+ creditTotal.toFixed(2);
							
	modalBodyLine4Left.innerHTML = "All collections by " + payCollector + " for <span style='font-weight:bold'>" + payDate +": </span> ";
	modalBodyLine4Right.innerHTML = "$"+ cellTotal.toFixed(2);
	
	//diplaying the modal
	displayModal.style.display = "block";
			
}