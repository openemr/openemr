<?
# this file is form_report.php,
# is called by all reporting pages, print, view, and report.

	$row = formFetch("form_habits", $id);

	if ($row) {
		// render. will only take positive data.
		echo ("<table span class=\"text\">");

		if ($row['coffee'] || $row['tea'] || $row['soft_drinks'] || $row['other_caffeine'] || $row['caffeine_per_day']) {
			echo ("<tr><td>");
			echo ("Caffeine intake: Drinks ");
			if ($row['coffee']) { echo ("coffee, "); }
			if ($row['tea']) { echo ("tea, "); }
			if ($row['soft_drinks']) { echo ("soft drinks, "); }
			if ($row['other_caffeine']) { echo ("other caffeine, "); }
			if ($row['caffeine_per_day']) { echo ("in an amount of ${row['caffeine_per_day']}  serving(s) per day"); }
			echo ("</td></tr>");
		}
		if ($row['salt_usage']) {
			echo ("<tr><td>");
			echo ("Salt usage is ${row['salt_usage']}");
			echo ("</td></tr>");
		}
		if ($row['sugar_usage']) {
			echo ("<tr><td>");
			echo ("Sugar usage is ${row['sugar_usage']}");
			echo ("</td></tr>");
		}
		if ($row['diet']) {
			echo ("<tr><td>");
			echo ("Diet is ${row['diet']}. ");
			if ($row['diet_comments']) { echo (" Note: ${row['diet_comments']}"); }
			echo ("</td></tr>");
		}
		if ($row['alc_per_day'] || $row['alc_per_week']) {
			echo ("<tr><td>");
			echo ("Drinks alcohol in an amount of: ");
			if ($row['alc_per_day']) { echo ("${row['alc_per_day']} glass(es) per day"); $y=' and/or '; }
			if ($row['alc_per_week']) { echo ("$y ${row['alc_per_week']} glass(es) per week"); }
			echo ("</td></tr>");
		}
		if ($row['recr_drugs'] || $row['recr_drugs_often'] || $row['alc_drug_problem'] || $row['alc_drug_problem_explain'] ) {
			echo ("<tr><td>");
			if ($row['recr_drugs']) { echo ("The patients states that she/he uses ${row['recr_drugs']} as recreational drug(s)"); $y=' and/or '; }
			if ($row['recr_drugs_often']) { echo ("$y she/he uses ${row['recr_drugs_often']} "); } 
			if ($row['recr_drugs'] AND !$row['recr_drugs_often'] ) { echo (" Frecuency is not stated"); }
			echo ("</td></tr>");
			echo ("<tr><td>");
			if ($row['alc_drug_problem']=='YES') { $problem=''; } else { $problem='NOT';}
			echo ("The patient states that she/he DOES $problem have alcohol or drug problems<br>"); 
			if ($row['alc_drug_problem_explain']) { echo ("And explains that: \"${row['alc_drug_problem_explain']} \""); }
			echo ("</td></tr>");
		}
		// smoking part.
		if ($row['current_smoke'] || $row['ever_smoked'] || $row['cig_per_day_now'] || $row['cig_per_day_past'] || $row['how_long_smoke'] || $row['smoke_quit']) {
			echo ("<tr><td>");
			$enc_date=DBToDate ($row['date']);
			if ($row['current_smoke']=='YES' || $row['cig_per_day_now']>=1 ) {
				echo ("As of $enc_date refers that is currently smoking.");
				if ($row['cig_per_day_now']>=1) { 
					echo (" ${row['cig_per_day_now']} cigaretes per day."); 	
				} 
				echo ("<br>");
				$flag_quit=1;
			} else {
				echo ("As of $enc_date refers that is NOT currently smoking.");
			}
			if ($row['ever_smoked']=='YES' || $row['cig_per_day_past']>=1 ) {
				echo ("As of $enc_date refers that smoked in the past.");
				if ($row['cig_per_day_past']>=1) { 
					echo (" ${row['cig_per_day_past']} cigaretes per day."); 	
				} 
				echo ("<br>");
			} else {
				echo ("As of $enc_date refers that DID NOT smoked before.<br>");

			}
			if ($row['like_to_quit']=='YES') {
				echo ("Would like to quit smoking.<br>");
			} elseif ($row['like_to_quit']=='NO') {
				echo ("Does not want to quit smoking.<br>");
			} elseif ($flag_quit==1) {
				echo ("Does not state if wants to quit smoking.<br>");
			} else {
				if ($row['smoke_quit_date']) {
					echo ("Quit smoking on ${row['smoke_quit_date']}.<br>");
				}
			}
			echo ("</tr></td>");
		} //eof smoke
		{ // exercise
			echo ("<tr><td>");
			if ($row['exercise_types'] || $row['exercise_per_week'] || $row['exercise_minutes']) {
				if ($row['exercise_types']) { echo ("Type of exercise: ${row['exercise_types']} ");}
				if ($row['exercise_per_week']) { echo ("${row['exercise_per_week']} times per week");}
				if ($row['exercise_minutes']) { echo (" for aprox. ${row['exercise_minutes']} each time.<br>");}
			} else { $flag='no'; }

			if ($row['exercise_reg'] && $flag=='no') {
				if ($row['exercise_reg']=="YES") {
					echo ("She/he states that exercises regularly, but doesn't especify how.<br>");	
				} elseif ($row['exercise_reg']=="NO") {
						echo ("DOES NOT exercises regularly.<br>");	
				} 
			} else {
						echo ("Does not specifies if exersices regularly.<br>");
			}
			echo ("</td></tr>");
		} //eof exercise
		{ //seat belt.
			if ($row['seat_belt']) {
				echo ("<tr><td>");
				echo ("${row['seat_belt']} uses seat belt.");
				echo ("</td></tr>");
			}
		}
		{ // molestation
			if ($row['ever_been_molested'] || $row['ever_molested_other'] ){
				echo ("<tr><td>");
				if ($row['ever_been_molested']=='NO') {
					echo ("Declares she/he has never been molested.<br>");
				} elseif ($row['ever_been_molested']=='YES') {
					echo ("Declares she/he HAS been molested.<br>");
				} else {
					echo ("Does not specifies if ever been molested.<br>");
				}
				if ($row['ever_molested_other']=='NO') {
					echo ("Declares she/he has never molested other person.<br>");
				} elseif ($row['ever_molested_other']=='YES') {
					echo ("Declares she/he HAS molested other person.<br>");
				} else {
					echo ("Does not specifies if ever molested other person.<br>");
				}
				echo ("</td></tr>");
			}
			
		} //eof molestation


		echo ('</table>');

		// eof render
	}
?>