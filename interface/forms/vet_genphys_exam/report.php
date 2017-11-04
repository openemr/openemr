<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
// Copyright (C) 2017 Roland Wick <ronhen_at_yandex_com>
// version 0.9
// TO DO :- bg colour change if meas.values out of normal limits
// 		 - internationalize Weight + Temperature once activated in new/edit form
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
//

function vet_genphys_exam_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_vet_genphys_exam", $id);
  
    if ($data) {
        print "<table cellpadding='1' border='0'>";
  
  // table heading

 echo "<tr>";
        echo "<td colspan='4'><span class='bold'>" . xlt('Provider') . "</span>";
       if ( $data["user"] != "" ) {
           echo " : <span class='text'>${data['user']}</span>";
         }
			else {
           echo " : <span class='text'>" . xlt('Nothing Recorded') . "</span>";
            }
 	echo "</td></tr>";
       
// start form fields        
        echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Date/Time') . ":</span></td>";
       if ( $data["date"] != "" ) {
           echo "<td><span class='text'>${data['date']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Pres Complaint') . ": </span></td>";
 		 if ( $data["presenting_complaint"] != "" ) {
             echo "<td><span class='text'>${data['presenting_complaint']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
        }

	// --- report row	

 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Consciousness') . ":</span></td>";
       if ( $data["consciousness"] != "" ) {
           echo "<td><span class='text'>${data['consciousness']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Behaviour') . ":</span></td>";
 		 if ( $data["behaviour"] != "" ) {
             echo "<td><span class='text'>${data['behaviour']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";

	// --- report row	

		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Gait') . ":</span></td>";
       if ( $data["gait"] != "" ) {
           echo "<td><span class='text'>${data['gait']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Notes') . ":</span></td>";
 		 if ( $data["general_notes"] != "" ) {
             echo "<td><span class='text'>${data['general_notes']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
   
  // --- report row	
  
    echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Gen.Condition') . ":</span></td>";
       if ( $data["general_condition"] != "" ) {
				 echo "<td><span class='text'>${data['general_condition']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('BWt Score') . ":</span></td>";
 		 if ( $data["bws_bodyscore"] != "" ) {
             echo "<td><span class='text'>${data['bws_bodyscore']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
   
   // --- report row	
   
   echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Posture') . ":</span></td>";
       if ( $data["body_posture"] != "" ) {
           echo "<td><span class='text'>${data['body_posture']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Care Cond') . ":</span></td>";
 		 if ( $data["care_condition"] != "" ) {
             echo "<td><span class='text'>${data['care_condition']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
    
  
   // --- report row	
   
   	 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Weight') . ":</span></td>";
     if ( $data["vitals_weight"] != "0" ) {
           echo "<td><span class='text'>${data['vitals_weight']} Kg./Lb</span></td>";
        }
			else {
           echo "<td><span class='text'>" . xlt('n/a') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Temp') . ":</span></td>";
 		 if ( $data["vitals_temperature"] != "0" ) {
             echo "<td><span class='text'>${data['vitals_temperature']} &deg; C/F</span></td>";
		  }            
           else {
                echo "<td><span class='text'>" . xlt('n/a') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
		
		 echo "<tr>";
        echo "<td style='background-color:cornsilk'><span class='bold'>" . xlt('Anamnesis') . ":</span></td>";
       if ( $data["anamnesis"] != "" ) {
           echo "<td style='background-color:cornsilk' colspan=3><span class='text'>${data['anamnesis']}</span></td>";
         }
			else {
           echo "<td colspan=3><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "</tr>";

  // --- report row	
            
     echo "<tr>";
     echo "<td colspan=2><span class='bold'><u>" . xlt('Circulation') . "</u></span></td>";
     echo "<td colspan=2><span class='bold'><u>" . xlt('Respiration') . "</u></span></td>";
     echo "</tr>";       
   
  // --- report row	 
           
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Heart Freq.') . ":</span></td>";
       if ( $data["heart_frequency"] != "" ) {
           echo "<td><span class='text'>${data['heart_frequency']} b/min</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Resp. Freq.') . ":</span></td>";
 		 if ( $data["respiration_frequency"] != "" ) {
             echo "<td><span class='text'>${data['respiration_frequency']}/min</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";      
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Heart_Tones') . "</span></td>";
       if ( $data["heart_tones"] != "" ) {
           echo "<td><span class='text'>${data['heart_tones']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Resp.Type') . ":</span></td>";
 		 if ( $data["respiration_type"] != "" ) {
             echo "<td><span class='text'>${data['respiration_type']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('H.Murmur Gr.') . ":</span></td>";
       if ( $data["heart_murmur"] != "" ) {
           echo "<td><span class='text'>${data['heart_murmur']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Resp.Character') . "</span></td>";
 		 if ( $data["respiration_character"] != "" ) {
             echo "<td><span class='text'>${data['respiration_character']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
		
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('H.Murm.Phase') . "</span></td>";
       if ( $data["heart_murmur_phase"] != "" ) {
           echo "<td><span class='text'>${data['heart_murmur_phase']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Lung Ausc.') . "</span></td>";
 		 	echo "<td><span class='text'>";
		// tests for values <> "" in lungf_ausc_*, if nothing present then print Nothing Recorded  			 
 			 $i = 1;
				while($i <= 7) {				 
				 if ($data["lungf_ausc_$i"] !="") {
					   	echo "${data["lungf_ausc_$i"]}" . " | ";
					   	}					   	
						else {
							echo "";
							}
				$i++;
					}		
				  if ($data["lungf_ausc_1"]='' && $data["lungf_ausc_2"]='' && $data["lungf_ausc_3"]='' && $data["lungf_ausc_4"]='' && $data["lungf_ausc_5"]='' && $data["lungf_ausc_6"]='' && $data["lungf_ausc_7"]='') { 
							 echo "xlt('Nothing Recorded')";
				$i = 0;
							 }
	 	 	echo "</span></td>";
		    echo "</tr>";    
            	 

	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Pulse Freq.') . ":</span></td>";
       if ( $data["pulse_frequency"] != "" ) {
           echo "<td><span class='text'>${data['pulse_frequency']}/min</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Percussion') . "</span></td>";
 		 if ( $data["lungfield_percussion"] != "" ) {
             echo "<td><span class='text'>${data['lungfield_percussion']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Pulse_Intensity') . ":</span></td>";
       if ( $data["pulse_intensity"] != "" ) {
           echo "<td><span class='text'>${data['pulse_intensity']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
	  echo "<td><span class='bold'>" . xlt('Upp.Airw.') . "</span></td>";
 		 	echo "<td><span class='text'>";
		// tests for values <> "" in up_airways_*, if nothing present then print Nothing Recorded  			 
 			 $i = 1;
				while($i <= 5) {				 
				 if ($data["up_airways_$i"] !="") {
					   	echo "${data["up_airways_$i"]}" . " | ";
					   	}					   	
						else {
							echo "";
							}
				$i++;
					}		
				  if ($data["up_airways_1"]='' && $data["up_airways_2"]='' && $data["up_airways_3"]='' && $data["up_airways_4"]='' && $data["up_airways_5"]='') { 
							 echo "xlt('Nothing Recorded')";
				$i = 0;
							 }
	 	 	echo "</span></td>";
		    echo "</tr>";  
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Pulse_Regularity') . ":</span></td>";
       if ( $data["pulse_regularity"] != "" ) {
           echo "<td><span class='text'>${data['pulse_regularity']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Upp.Airw.Notes') . "</span></td>";
 		 if ( $data["upper_airways_2descr"] != "" ) {
             echo "<td><span class='text'>${data['upper_airways_2descr']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Pulse_equality') . ":</span></td>";
       if ( $data["pulse_equality"] != "" ) {
           echo "<td><span class='text'>${data['pulse_equality']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		   echo "<td colspan=2><span class='bold'>----------------------------</span></td>";
 		  echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Mucosae colour') . ":</span></td>";
       if ( $data["mucosae_colour"] != "" ) {
           echo "<td><span class='text'>${data['mucosae_colour']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'><u>" . xlt('Palp. abdomen') . "</u></span></td>";
 		  echo "<td>&nbsp;</td>";
 		    echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Mucosae status') . "</span></td>";
       if ( $data["mucosae_status"] != "" ) {
           echo "<td><span class='text'>${data['mucosae_status']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Elasticity') . "</span></td>";
 		 if ( $data["elasticity_abdomen"] != "" ) {
             echo "<td><span class='text'>${data['elasticity_abdomen']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Muc. CRT') . "</span></td>";
       if ( $data["mucosae_CRT"] != "" ) {
           echo "<td><span class='text'>${data['mucosae_CRT']} sec.</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Sensibility abd') . "</span></td>";
 		 if ( $data["sensibility_abdomen"] != "" ) {
             echo "<td><span class='text'>${data['sensibility_abdomen']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td colspan=2><span class='bold'>--------------</span></td>";
      
		  echo "<td><span class='bold'>" . xlt('Other finding') . "</span></td>";
 		 if ( $data["palp_abdomen_other"] != "" ) {
             echo "<td><span class='text'>${data['palp_abdomen_other']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
echo "<tr><td colspan=4 align='left'><span class='bold'><u>" . xlt('Skin') . ":</u></span></tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Turgor') . ":</span></td>";
       if ( $data["skin_turgor"] != "" ) {
           echo "<td><span class='text'>${data['skin_turgor']} sec.</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Thickness') . "</span></td>";
 		 if ( $data["skin_thickness"] != "" ) {
             echo "<td><span class='text'>${data['skin_thickness']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('WNL') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Colour') . ":</span></td>";
       if ( $data["skin_colour"] != "" ) {
           echo "<td><span class='text'>${data['skin_colour']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Mobility') . ":</span></td>";
 		 if ( $data["skin_mobility"] != "" ) {
             echo "<td><span class='text'>${data['skin_mobility']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
	
// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Integ. Gener.') . ":</span></td>";
       if ( $data["skin_colour"] != "" ) {
           echo "<td><span class='text'>${data['integ_general']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Integ. Local') . ":</span></td>";
 		 if ( $data["skin_mobility"] != "" ) {
             echo "<td><span class='text'>${data['integ_local']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";
		
	
	
	// --- report row	
	
echo "<tr><td colspan=4 align='left'><span class='bold'>-----------------</span></tr>";
echo "<tr><td colspan=4 align='left'><span class='bold'><u>" . xlt('Palpation') . " LNN</u></span></tr>";	

	// --- report row	

		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Mandibul.') . "</span></td>";
       if ( $data["lnn_mandibulares"] != "" ) {
           echo "<td><span class='text'>${data['lnn_mandibulares']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Axillares') . "</span></td>";
 		 if ( $data["lnn_axillares"] != "" ) {
             echo "<td><span class='text'>${data['lnn_axillares']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
            echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Parotidei') . "</span></td>";
       if ( $data["lnn_parotidei"] != "" ) {
           echo "<td><span class='text'>${data['lnn_parotidei']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Axillares_access.') . "</span></td>";
 		 if ( $data["lnn_axill_access"] != "" ) {
             echo "<td><span class='text'>${data['lnn_axill_access']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
            echo "</tr>";

	// --- report row	

		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Retropharyng') . "</span></td>";
       if ( $data["lnn_retropharyng"] != "" ) {
           echo "<td><span class='text'>${data['lnn_retropharyng']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Inguinales') . "</span></td>";
 		 if ( $data["lnn_inguinales"] != "" ) {
             echo "<td><span class='text'>${data['lnn_inguinales']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
            echo "</tr>";

	// --- report row	

		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Cervicales') . "</span></td>";
       if ( $data["lnn_cervicales"] != "" ) {
           echo "<td><span class='text'>${data['lnn_cervicales']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Poplitei') . "</span></td>";
 		 if ( $data["lnn_poplitei"] != "" ) {
             echo "<td><span class='text'>${data['lnn_poplitei']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('WNL/NP') . "</span></td>";
            }
            echo "</tr>";

	// --- report row	

echo "<tr><td colspan=4 align='left'><span class='bold'>-----------------</span></tr>";

	// --- report row	

		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Other findings') . ":</span></td>";
       if ( $data["genphys_other_findings"] != "" ) {
           echo "<td colspan=3><span class='text'>${data['genphys_other_findings']}</span></td>";
         }
			else {
           echo "<td colspan=3><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		 echo "</tr>";

	// --- report row	
	
		 echo "<tr>";
        echo "<td colspan=4><span class='bold'>" . xlt('Problem List') . ": </span></td>";
       echo "</tr>";
	
	// --- report row	
	
		 echo "<tr>";
        echo "<td><span class='bold'>" . xlt('Pres Dx') . ":</span></td>";
       if ( $data["presumptive_diagnosis"] != "" ) {
           echo "<td><span class='text'>${data['presumptive_diagnosis']}</span></td>";
         }
			else {
           echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
		  echo "<td><span class='bold'>" . xlt('Differentials') . ":</span></td>";
 		 if ( $data["differential_diagnosis"] != "" ) {
             echo "<td><span class='text'>${data['differential_diagnosis']}</span></td>";
		  }            
            else {
                echo "<td><span class='text'>" . xlt('Nothing Recorded') . "</span></td>";
            }
            echo "</tr>";

  // -------------- end of fields cd ----------------
        print "</table>";
    }


function endsWith($FullStr, $EndStr)
{
    $StrLen = strlen($EndStr);
    $FullStrEnd = substr($FullStr, strlen($FullStr) - $StrLen);
    return $FullStrEnd == $EndStr;
}
?>
