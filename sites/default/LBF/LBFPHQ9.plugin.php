<?php
// Copyright (C) 2017 Sherwin Gaddis <sherwin@openmedpractice.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the referral (REF) form.
// It is invoked by interface/patient_file/transaction/add_transaction.php.

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
// @author Sherwin Gaddis <sherwin@openmedpractice.com>
// @edited by Joyce Boyd <jboyd13@masonlive.gmu.edu>
//   added code to update Referral Answer 
//



function LBFPHQ9_javascript() {

echo "

$(document).ready(function(){
    $('#form_total_score').click(function(){

       var e0 = document.getElementById('form_interest');
       var score0 = e0.options[e0.selectedIndex].value;

       var e1 = document.getElementById('form_dep_score');
       var score1 = e1.options[e1.selectedIndex].value;

       var e2 = document.getElementById('form_sleep_score');
       var score2 = e2.options[e2.selectedIndex].value;

       var e3 = document.getElementById('form_energy_score');
       var score3 = e3.options[e3.selectedIndex].value;       

       var e4 = document.getElementById('form_app_score');
       var score4 = e4.options[e4.selectedIndex].value;

       var e5 = document.getElementById('form_esteem_score');
       var score5 = e5.options[e5.selectedIndex].value;

       var e6 = document.getElementById('form_conc_score');
       var score6 = e6.options[e6.selectedIndex].value;

       var e7 = document.getElementById('form_PM_score');
       var score7 = e7.options[e7.selectedIndex].value;

       var e8 = document.getElementById('form_SI_score');
       var score8 = e8.options[e8.selectedIndex].value; 

       var e9 = document.getElementById('form_impact_score');
       var score9 = e9.options[e9.selectedIndex].value; 
       
       //Loading all the scores into an array (SG, 2/8/17)
       var scores = [score0, score1, score2, score3, score4, score5, score6, score7, score8, score9];

       //looping through the array to see if any values equal 0, which is the default score
       for (i = 0; i<scores.length; i++){
            if(+scores[i] == 0){
                  alert('Please answer question '+ ++i);
                  return false;
            }
       }


       var total = (+score0 + +score1 + +score2 + +score3 + +score4 + +score5 + +score6+ + +score7 + +score8)-9  ;

       document.getElementById('form_total_score').value = total;

       var severity = document.getElementById('form_severity');

       if(total <= 4){
       	severity.value = 1;
       } else if (total <= 9){
       	severity.value = 2;
       } else if (total <= 14){
       	severity.value = 3;
       } else if (total <= 19){
       	severity.value = 4;
       } else {
       	severity.value = 5;
       }

//   Update Referral Answer on textbox click
       var refer = document.getElementById('form_provider_ref');
       if(severity.value > 2){
            refer.value = 'YES';
       } else { 
            refer.value = 'NO';
       }
  });
});
";

}


?>
