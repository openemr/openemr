<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function approved_physical_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $data = formFetch("form_approved_physical", $id);
  //echo "${data['general_headache']}";
  if ($data) {

  print "<table cellpadding='3px' cellspacing='0px' border=0px>";

echo "<tr><td colspan='3'><span class='bold'><u>Constitutional</u></span></td></tr>";
if ( ($data["col_1"] != "N/A" && $data["col_1"] != "" && $data["col_1"] != "--") || ( $data["col_1_textbox"] != "" && $data["col_1_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_1'] != null && $data['col_1'] != 'N/A' ) {
			echo "<span class='text'>${data['col_1']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>General Appearance (eg, development, nutrition, body habitus, deformities, attention to grooming)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_1_textbox'] != null ) {
			echo "<span class='text'>${data['col_1_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Eyes</u></span></td></tr>";
if ( ($data["col_2"] != "N/A" && $data["col_2"] != "" && $data["col_2"] != "--") || ( $data["col_2_textbox"] != "" && $data["col_2_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_2'] != null && $data['col_2'] != 'N/A' ) {
			echo "<span class='text'>${data['col_2']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection of conjunctiva and lids</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_2_textbox'] != null ) {
			echo "<span class='text'>${data['col_2_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_3"] != "N/A" && $data["col_3"] != "" && $data["col_3"] != "--") || ( $data["col_3_textbox"] != "" && $data["col_3_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_3'] != null && $data['col_3'] != 'N/A' ) {
			echo "<span class='text'>${data['col_3']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of pupils and irises (eg. Reaction to light and accommodation, size and symmetry)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_3_textbox'] != null ) {
			echo "<span class='text'>${data['col_3_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_4"] != "N/A" && $data["col_4"] != "" && $data["col_4"] != "--") || ( $data["col_4_textbox"] != "" && $data["col_4_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_4'] != null && $data['col_4'] != 'N/A' ) {
			echo "<span class='text'>${data['col_4']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Othalmoscopic examination of optic discs (eg size, C/D ratio, appearance) and posterior segments (eg, vessel changes, exudates, hemorrhages)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_4_textbox'] != null ) {
			echo "<span class='text'>${data['col_4_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Ears, Nose, Mouth and Throat</u></span></td></tr>";
if ( ($data["col_5"] != "N/A" && $data["col_5"] != "" && $data["col_5"] != "--") || ( $data["col_5_textbox"] != "" && $data["col_5_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_5'] != null && $data['col_5'] != 'N/A' ) {
			echo "<span class='text'>${data['col_5']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>External inspection of ears and nose (overall appearance, scars, lesions, masses)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_5_textbox'] != null ) {
			echo "<span class='text'>${data['col_5_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_6"] != "N/A" && $data["col_6"] != "" && $data["col_6"] != "--") || ( $data["col_6_textbox"] != "" && $data["col_6_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_6'] != null && $data['col_6'] != 'N/A' ) {
			echo "<span class='text'>${data['col_6']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Otoscopic examination of external auditory canals and tympanic membranes</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_6_textbox'] != null ) {
			echo "<span class='text'>${data['col_6_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_7"] != "N/A" && $data["col_7"] != "" && $data["col_7"] != "--") || ( $data["col_7_textbox"] != "" && $data["col_7_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_7'] != null && $data['col_7'] != 'N/A' ) {
			echo "<span class='text'>${data['col_7']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Assessment of hearing (eg, whispered voice, finger rub, tuning fork)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_7_textbox'] != null ) {
			echo "<span class='text'>${data['col_7_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_8"] != "N/A" && $data["col_8"] != "" && $data["col_8"] != "--") || ( $data["col_8_textbox"] != "" && $data["col_8_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_8'] != null && $data['col_8'] != 'N/A' ) {
			echo "<span class='text'>${data['col_8']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection of nasal mucosa, septum and turbinates</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_8_textbox'] != null ) {
			echo "<span class='text'>${data['col_8_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_9"] != "N/A" && $data["col_9"] != "" && $data["col_9"] != "--") || ( $data["col_9_textbox"] != "" && $data["col_9_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_9'] != null && $data['col_9'] != 'N/A' ) {
			echo "<span class='text'>${data['col_9']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection of lips, teeth and gums</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_9_textbox'] != null ) {
			echo "<span class='text'>${data['col_9_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_10"] != "N/A" && $data["col_10"] != "" && $data["col_10"] != "--") || ( $data["col_10_textbox"] != "" && $data["col_10_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_10'] != null && $data['col_10'] != 'N/A' ) {
			echo "<span class='text'>${data['col_10']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of oropharynx: oral mucosa, salivary glands, hard and soft palates, tongue, tonsils and posterior pharynx</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_10_textbox'] != null ) {
			echo "<span class='text'>${data['col_10_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Neck</u></span></td></tr>";
if ( ($data["col_11"] != "N/A" && $data["col_11"] != "" && $data["col_11"] != "--") || ( $data["col_11_textbox"] != "" && $data["col_11_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_11'] != null && $data['col_11'] != 'N/A' ) {
			echo "<span class='text'>${data['col_11']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of neck (eg, masses, overall appearance, symmetry, tracheal position, crepitus)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_11_textbox'] != null ) {
			echo "<span class='text'>${data['col_11_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_12"] != "N/A" && $data["col_12"] != "" && $data["col_12"] != "--") || ( $data["col_12_textbox"] != "" && $data["col_12_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_12'] != null && $data['col_12'] != 'N/A' ) {
			echo "<span class='text'>${data['col_12']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of thyroid (eg, enlargement, tenderness, mass)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_12_textbox'] != null ) {
			echo "<span class='text'>${data['col_12_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Respiratory</u></span></td></tr>";
if ( ($data["col_13"] != "N/A" && $data["col_13"] != "" && $data["col_13"] != "--") || ( $data["col_13_textbox"] != "" && $data["col_13_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_13'] != null && $data['col_13'] != 'N/A' ) {
			echo "<span class='text'>${data['col_13']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Assessment of respiratory effort (eg, intercostal retractions, use of accessory muscles, diaphragmatic movement)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_13_textbox'] != null ) {
			echo "<span class='text'>${data['col_13_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_14"] != "N/A" && $data["col_14"] != "" && $data["col_14"] != "--") || ( $data["col_14_textbox"] != "" && $data["col_14_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_14'] != null && $data['col_14'] != 'N/A' ) {
			echo "<span class='text'>${data['col_14']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Percussion of chest (eg, dullness, flatness, hyperresonance)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_14_textbox'] != null ) {
			echo "<span class='text'>${data['col_14_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_15"] != "N/A" && $data["col_15"] != "" && $data["col_15"] != "--") || ( $data["col_15_textbox"] != "" && $data["col_15_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_15'] != null && $data['col_15'] != 'N/A' ) {
			echo "<span class='text'>${data['col_15']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Palpation of chest (eg, tactile fremitus)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_15_textbox'] != null ) {
			echo "<span class='text'>${data['col_15_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_16"] != "N/A" && $data["col_16"] != "" && $data["col_16"] != "--") || ( $data["col_16_textbox"] != "" && $data["col_16_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_16'] != null && $data['col_16'] != 'N/A' ) {
			echo "<span class='text'>${data['col_16']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Auscultation of lungs (eg, breath sounds, adventitious sounds, rubs)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_16_textbox'] != null ) {
			echo "<span class='text'>${data['col_16_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Cardiovascular</u></span></td></tr>";
if ( ($data["col_17"] != "N/A" && $data["col_17"] != "" && $data["col_17"] != "--") || ( $data["col_17_textbox"] != "" && $data["col_17_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_17'] != null && $data['col_17'] != 'N/A' ) {
			echo "<span class='text'>${data['col_17']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Palpation of heart (eg, location, size, thrills)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_17_textbox'] != null ) {
			echo "<span class='text'>${data['col_17_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_18"] != "N/A" && $data["col_18"] != "" && $data["col_18"] != "--") || ( $data["col_18_textbox"] != "" && $data["col_18_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_18'] != null && $data['col_18'] != 'N/A' ) {
			echo "<span class='text'>${data['col_18']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Auscultation of heart with notation of abnormal sounds and murmurs</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_18_textbox'] != null ) {
			echo "<span class='text'>${data['col_18_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_19"] != "N/A" && $data["col_19"] != "" && $data["col_19"] != "--") || ( $data["col_19_textbox"] != "" && $data["col_19_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_19'] != null && $data['col_19'] != 'N/A' ) {
			echo "<span class='text'>${data['col_19']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of: carotid arteries (eg, pulse amplitude, bruits)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_19_textbox'] != null ) {
			echo "<span class='text'>${data['col_19_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_20"] != "N/A" && $data["col_20"] != "" && $data["col_20"] != "--") || ( $data["col_20_textbox"] != "" && $data["col_20_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_20'] != null && $data['col_20'] != 'N/A' ) {
			echo "<span class='text'>${data['col_20']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>abdominal aorta (eg, size, bruits)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_20_textbox'] != null ) {
			echo "<span class='text'>${data['col_20_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_21"] != "N/A" && $data["col_21"] != "" && $data["col_21"] != "--") || ( $data["col_21_textbox"] != "" && $data["col_21_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_21'] != null && $data['col_21'] != 'N/A' ) {
			echo "<span class='text'>${data['col_21']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>femoral arteries (eg, pulse amplitude, bruits)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_21_textbox'] != null ) {
			echo "<span class='text'>${data['col_21_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_22"] != "N/A" && $data["col_22"] != "" && $data["col_22"] != "--") || ( $data["col_22_textbox"] != "" && $data["col_22_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_22'] != null && $data['col_22'] != 'N/A' ) {
			echo "<span class='text'>${data['col_22']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>pedal pulses (eg, pulse amplitude)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_22_textbox'] != null ) {
			echo "<span class='text'>${data['col_22_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_23"] != "N/A" && $data["col_23"] != "" && $data["col_23"] != "--") || ( $data["col_23_textbox"] != "" && $data["col_23_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_23'] != null && $data['col_23'] != 'N/A' ) {
			echo "<span class='text'>${data['col_23']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>extremities for edema and/or varicosities</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_23_textbox'] != null ) {
			echo "<span class='text'>${data['col_23_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Chest (Breasts)</u></span></td></tr>";
if ( ($data["col_24"] != "N/A" && $data["col_24"] != "" && $data["col_24"] != "--") || ( $data["col_24_textbox"] != "" && $data["col_24_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_24'] != null && $data['col_24'] != 'N/A' ) {
			echo "<span class='text'>${data['col_24']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection of breasts (eg, symmetry, nipple discharge)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_24_textbox'] != null ) {
			echo "<span class='text'>${data['col_24_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_25"] != "N/A" && $data["col_25"] != "" && $data["col_25"] != "--") || ( $data["col_25_textbox"] != "" && $data["col_25_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_25'] != null && $data['col_25'] != 'N/A' ) {
			echo "<span class='text'>${data['col_25']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Palpation of breasts and axillae (eg, masses or lumps, tenderness)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_25_textbox'] != null ) {
			echo "<span class='text'>${data['col_25_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Gastrointestinal (Abdomen)</u></span></td></tr>";
if ( ($data["col_26"] != "N/A" && $data["col_26"] != "" && $data["col_26"] != "--") || ( $data["col_26_textbox"] != "" && $data["col_26_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_26'] != null && $data['col_26'] != 'N/A' ) {
			echo "<span class='text'>${data['col_26']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of abdomen with notation of presence of masses or tenderness</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_26_textbox'] != null ) {
			echo "<span class='text'>${data['col_26_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_27"] != "N/A" && $data["col_27"] != "" && $data["col_27"] != "--") || ( $data["col_27_textbox"] != "" && $data["col_27_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_27'] != null && $data['col_27'] != 'N/A' ) {
			echo "<span class='text'>${data['col_27']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of liver and spleen</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_27_textbox'] != null ) {
			echo "<span class='text'>${data['col_27_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_28"] != "N/A" && $data["col_28"] != "" && $data["col_28"] != "--") || ( $data["col_28_textbox"] != "" && $data["col_28_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_28'] != null && $data['col_28'] != 'N/A' ) {
			echo "<span class='text'>${data['col_28']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination for presence or absence of hernia</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_28_textbox'] != null ) {
			echo "<span class='text'>${data['col_28_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_29"] != "N/A" && $data["col_29"] != "" && $data["col_29"] != "--") || ( $data["col_29_textbox"] != "" && $data["col_29_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_29'] != null && $data['col_29'] != 'N/A' ) {
			echo "<span class='text'>${data['col_29']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination (when indicated) of anus, perineum and rectum, including sphincter tone, presence of hemorrhoids, rectal masses</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_29_textbox'] != null ) {
			echo "<span class='text'>${data['col_29_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_30"] != "N/A" && $data["col_30"] != "" && $data["col_30"] != "--") || ( $data["col_30_textbox"] != "" && $data["col_30_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_30'] != null && $data['col_30'] != 'N/A' ) {
			echo "<span class='text'>${data['col_30']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Obtain stool sample for occult blood test when indicated</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_30_textbox'] != null ) {
			echo "<span class='text'>${data['col_30_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Male Genitourinary </u></span></td></tr>";
if ( ($data["col_31"] != "N/A" && $data["col_31"] != "" && $data["col_31"] != "--") || ( $data["col_31_textbox"] != "" && $data["col_31_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_31'] != null && $data['col_31'] != 'N/A' ) {
			echo "<span class='text'>${data['col_31']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of the scrotal contents (eg, hydrocele, spermatocele, tenderness of cord, testicular mass)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_31_textbox'] != null ) {
			echo "<span class='text'>${data['col_31_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_32"] != "N/A" && $data["col_32"] != "" && $data["col_32"] != "--") || ( $data["col_32_textbox"] != "" && $data["col_32_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_32'] != null && $data['col_32'] != 'N/A' ) {
			echo "<span class='text'>${data['col_32']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of the penis</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_32_textbox'] != null ) {
			echo "<span class='text'>${data['col_32_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_33"] != "N/A" && $data["col_33"] != "" && $data["col_33"] != "--") || ( $data["col_33_textbox"] != "" && $data["col_33_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_33'] != null && $data['col_33'] != 'N/A' ) {
			echo "<span class='text'>${data['col_33']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Digital rectal examination of prostate gland (eg, size, symmetry, nodularity, tenderness)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_33_textbox'] != null ) {
			echo "<span class='text'>${data['col_33_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Female Genitourinary </u></span></td></tr>";
if ( ($data["col_34"] != "N/A" && $data["col_34"] != "" && $data["col_34"] != "--") || ( $data["col_34_textbox"] != "" && $data["col_34_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_34'] != null && $data['col_34'] != 'N/A' ) {
			echo "<span class='text'>${data['col_34']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Pelvic examination (with or without specimen collection for smears and cultures), including</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_34_textbox'] != null ) {
			echo "<span class='text'>${data['col_34_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_35"] != "N/A" && $data["col_35"] != "" && $data["col_35"] != "--") || ( $data["col_35_textbox"] != "" && $data["col_35_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_35'] != null && $data['col_35'] != 'N/A' ) {
			echo "<span class='text'>${data['col_35']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of external genitalia (eg, general appearance, hair distribution, lesions) and vagina (eg, general appearance, estrogen effect, discharge, lesions, pelvic support, cystocele, rectocele)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_35_textbox'] != null ) {
			echo "<span class='text'>${data['col_35_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_36"] != "N/A" && $data["col_36"] != "" && $data["col_36"] != "--") || ( $data["col_36_textbox"] != "" && $data["col_36_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_36'] != null && $data['col_36'] != 'N/A' ) {
			echo "<span class='text'>${data['col_36']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of urethra (eg, masses, tenderness, scarring)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_36_textbox'] != null ) {
			echo "<span class='text'>${data['col_36_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_37"] != "N/A" && $data["col_37"] != "" && $data["col_37"] != "--") || ( $data["col_37_textbox"] != "" && $data["col_37_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_37'] != null && $data['col_37'] != 'N/A' ) {
			echo "<span class='text'>${data['col_37']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of bladder (eg, fullness, masses, tenderness)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_37_textbox'] != null ) {
			echo "<span class='text'>${data['col_37_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_38"] != "N/A" && $data["col_38"] != "" && $data["col_38"] != "--") || ( $data["col_38_textbox"] != "" && $data["col_38_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_38'] != null && $data['col_38'] != 'N/A' ) {
			echo "<span class='text'>${data['col_38']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Cervix (eg, general appearance, lesions, discharge)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_38_textbox'] != null ) {
			echo "<span class='text'>${data['col_38_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_39"] != "N/A" && $data["col_39"] != "" && $data["col_39"] != "--") || ( $data["col_39_textbox"] != "" && $data["col_39_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_39'] != null && $data['col_39'] != 'N/A' ) {
			echo "<span class='text'>${data['col_39']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Uterus (eg, size, contour, position, mobility, tenderness, consistency, descent or support)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_39_textbox'] != null ) {
			echo "<span class='text'>${data['col_39_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_40"] != "N/A" && $data["col_40"] != "" && $data["col_40"] != "--") || ( $data["col_40_textbox"] != "" && $data["col_40_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_40'] != null && $data['col_40'] != 'N/A' ) {
			echo "<span class='text'>${data['col_40']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Adnexa/parametria (eg, masses, tenderness, organomegaly, nodularity)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_40_textbox'] != null ) {
			echo "<span class='text'>${data['col_40_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Lymphatic</u></span></td></tr>";
if ( ($data["col_41"] != "N/A" && $data["col_41"] != "" && $data["col_41"] != "--") || ( $data["col_41_textbox"] != "" && $data["col_41_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_41'] != null && $data['col_41'] != 'N/A' ) {
			echo "<span class='text'>${data['col_41']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Palpation of lymph nodes in or more areas:</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_41_textbox'] != null ) {
			echo "<span class='text'>${data['col_41_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_42"] != "N/A" && $data["col_42"] != "" && $data["col_42"] != "--") || ( $data["col_42_textbox"] != "" && $data["col_42_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_42'] != null && $data['col_42'] != 'N/A' ) {
			echo "<span class='text'>${data['col_42']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Neck</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_42_textbox'] != null ) {
			echo "<span class='text'>${data['col_42_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_43"] != "N/A" && $data["col_43"] != "" && $data["col_43"] != "--") || ( $data["col_43_textbox"] != "" && $data["col_43_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_43'] != null && $data['col_43'] != 'N/A' ) {
			echo "<span class='text'>${data['col_43']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Axillae</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_43_textbox'] != null ) {
			echo "<span class='text'>${data['col_43_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_44"] != "N/A" && $data["col_44"] != "" && $data["col_44"] != "--") || ( $data["col_44_textbox"] != "" && $data["col_44_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_44'] != null && $data['col_44'] != 'N/A' ) {
			echo "<span class='text'>${data['col_44']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Groin</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_44_textbox'] != null ) {
			echo "<span class='text'>${data['col_44_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_45"] != "N/A" && $data["col_45"] != "" && $data["col_45"] != "--") || ( $data["col_45_textbox"] != "" && $data["col_45_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_45'] != null && $data['col_45'] != 'N/A' ) {
			echo "<span class='text'>${data['col_45']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Other</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_45_textbox'] != null ) {
			echo "<span class='text'>${data['col_45_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Musculoskeletal</u></span></td></tr>";
if ( ($data["col_46"] != "N/A" && $data["col_46"] != "" && $data["col_46"] != "--") || ( $data["col_46_textbox"] != "" && $data["col_46_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_46'] != null && $data['col_46'] != 'N/A' ) {
			echo "<span class='text'>${data['col_46']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of gait and station</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_46_textbox'] != null ) {
			echo "<span class='text'>${data['col_46_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_47"] != "N/A" && $data["col_47"] != "" && $data["col_47"] != "--") || ( $data["col_47_textbox"] != "" && $data["col_47_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_47'] != null && $data['col_47'] != 'N/A' ) {
			echo "<span class='text'>${data['col_47']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection and/or palpation of digits and nails (eg, clubbing, cyanosis, inflammatory conditions, petechiae, ischemia, infections, nodes)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_47_textbox'] != null ) {
			echo "<span class='text'>${data['col_47_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_48"] != "N/A" && $data["col_48"] != "" && $data["col_48"] != "--") || ( $data["col_48_textbox"] != "" && $data["col_48_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_48'] != null && $data['col_48'] != 'N/A' ) {
			echo "<span class='text'>${data['col_48']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of joints, bones and muscles of or more of the following six areas: 1) head and neck; 2) spine, ribs and pelvis; 3) right upper extremity; 4) left upper extremity; 5) right lower extremity; and 6) left lower extremity. The examination of a given area includes:</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_48_textbox'] != null ) {
			echo "<span class='text'>${data['col_48_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_49"] != "N/A" && $data["col_49"] != "" && $data["col_49"] != "--") || ( $data["col_49_textbox"] != "" && $data["col_49_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_49'] != null && $data['col_49'] != 'N/A' ) {
			echo "<span class='text'>${data['col_49']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection and/or palpation with notation of presence of any misalignment, asymmetry, crepitation, defects, tenderness, masses, effusions</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_49_textbox'] != null ) {
			echo "<span class='text'>${data['col_49_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_50"] != "N/A" && $data["col_50"] != "" && $data["col_50"] != "--") || ( $data["col_50_textbox"] != "" && $data["col_50_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_50'] != null && $data['col_50'] != 'N/A' ) {
			echo "<span class='text'>${data['col_50']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Assessment of range of motion with notation of any pain, crepitation or contracture</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_50_textbox'] != null ) {
			echo "<span class='text'>${data['col_50_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_51"] != "N/A" && $data["col_51"] != "" && $data["col_51"] != "--") || ( $data["col_51_textbox"] != "" && $data["col_51_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_51'] != null && $data['col_51'] != 'N/A' ) {
			echo "<span class='text'>${data['col_51']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Assessment of stability with notation of any dislocation (luxation), subluxation or laxity</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_51_textbox'] != null ) {
			echo "<span class='text'>${data['col_51_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_52"] != "N/A" && $data["col_52"] != "" && $data["col_52"] != "--") || ( $data["col_52_textbox"] != "" && $data["col_52_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_52'] != null && $data['col_52'] != 'N/A' ) {
			echo "<span class='text'>${data['col_52']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Assessment of muscle strength and tone (eg, flaccid, cog wheel, spastic) with notation of any atrophy or abnormal movements</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_52_textbox'] != null ) {
			echo "<span class='text'>${data['col_52_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Skin</u></span></td></tr>";
if ( ($data["col_53"] != "N/A" && $data["col_53"] != "" && $data["col_53"] != "--") || ( $data["col_53_textbox"] != "" && $data["col_53_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_53'] != null && $data['col_53'] != 'N/A' ) {
			echo "<span class='text'>${data['col_53']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Inspection of skin and subcutaneous tissue (eg, rashes, lesions, ulcers)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_53_textbox'] != null ) {
			echo "<span class='text'>${data['col_53_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_54"] != "N/A" && $data["col_54"] != "" && $data["col_54"] != "--") || ( $data["col_54_textbox"] != "" && $data["col_54_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_54'] != null && $data['col_54'] != 'N/A' ) {
			echo "<span class='text'>${data['col_54']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Palpation of skin and subcutaneous tissue (eg, induration, subcutaneous nodules, tightening)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_54_textbox'] != null ) {
			echo "<span class='text'>${data['col_54_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Neurologic</u></span></td></tr>";
if ( ($data["col_55"] != "N/A" && $data["col_55"] != "" && $data["col_55"] != "--") || ( $data["col_55_textbox"] != "" && $data["col_55_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_55'] != null && $data['col_55'] != 'N/A' ) {
			echo "<span class='text'>${data['col_55']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Test cranial nerves with notation of any deficits</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_55_textbox'] != null ) {
			echo "<span class='text'>${data['col_55_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_56"] != "N/A" && $data["col_56"] != "" && $data["col_56"] != "--") || ( $data["col_56_textbox"] != "" && $data["col_56_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_56'] != null && $data['col_56'] != 'N/A' ) {
			echo "<span class='text'>${data['col_56']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of deep tendon reflexes with notation of pathological reflexes (eg, Babinski)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_56_textbox'] != null ) {
			echo "<span class='text'>${data['col_56_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_57"] != "N/A" && $data["col_57"] != "" && $data["col_57"] != "--") || ( $data["col_57_textbox"] != "" && $data["col_57_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_57'] != null && $data['col_57'] != 'N/A' ) {
			echo "<span class='text'>${data['col_57']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Examination of sensation (eg, by touch, pin, vibration, proprioception)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_57_textbox'] != null ) {
			echo "<span class='text'>${data['col_57_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
echo "<tr><td colspan='3'><span class='bold'><u>Psychiatric</u></span></td></tr>";
if ( ($data["col_58"] != "N/A" && $data["col_58"] != "" && $data["col_58"] != "--") || ( $data["col_58_textbox"] != "" && $data["col_58_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_58'] != null && $data['col_58'] != 'N/A' ) {
			echo "<span class='text'>${data['col_58']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Description of patient’s judgment and insight</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_58_textbox'] != null ) {
			echo "<span class='text'>${data['col_58_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_59"] != "N/A" && $data["col_59"] != "" && $data["col_59"] != "--") || ( $data["col_59_textbox"] != "" && $data["col_59_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_59'] != null && $data['col_59'] != 'N/A' ) {
			echo "<span class='text'>${data['col_59']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>Brief assessment of mental status including:</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_59_textbox'] != null ) {
			echo "<span class='text'>${data['col_59_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_60"] != "N/A" && $data["col_60"] != "" && $data["col_60"] != "--") || ( $data["col_60_textbox"] != "" && $data["col_60_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_60'] != null && $data['col_60'] != 'N/A' ) {
			echo "<span class='text'>${data['col_60']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>orientation to time, place and person</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_60_textbox'] != null ) {
			echo "<span class='text'>${data['col_60_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_61"] != "N/A" && $data["col_61"] != "" && $data["col_61"] != "--") || ( $data["col_61_textbox"] != "" && $data["col_61_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_61'] != null && $data['col_61'] != 'N/A' ) {
			echo "<span class='text'>${data['col_61']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>recent and remote memory</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_61_textbox'] != null ) {
			echo "<span class='text'>${data['col_61_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}
if ( ($data["col_62"] != "N/A" && $data["col_62"] != "" && $data["col_62"] != "--") || ( $data["col_62_textbox"] != "" && $data["col_62_textbox"] != null ) ) {
	echo "<tr>";
		echo "<td valign='top'>";
		if ( $data['col_62'] != null && $data['col_62'] != 'N/A' ) {
			echo "<span class='text'>${data['col_62']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
		echo "<td width='300px' valign='top'>";
		echo "<span class='text'><i>mood and affect (eg, depression, anxiety, agitation)</i></span>";
		echo "</td>";
		echo "<td valign='top'>";
		if ( $data['col_62_textbox'] != null ) {
			echo "<span class='text'>${data['col_62_textbox']}</span>";
		} else {
			echo "<br/>";
		}
		echo "</td>";
	echo "</tr>";
}

  
  print "</table>";
  }

}
?>
