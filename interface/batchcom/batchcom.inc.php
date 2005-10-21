<?
	//validation functions
	function check_date_format ($date) {
		$pat="^(19[0-9]{2}|20[0-1]{1}[0-9]{1})-(0[1-9]|1[0-2])-(0[1-9]{1}|1[0-9]{1}|2[0-9]{1}|3[0-1]{1})$";
		if (ereg ($pat, $date) OR $date=='' OR $date=='0000-00-00') { return TRUE ; } else { RETURN FALSE; }
	}
	function check_age ($age) {
		$age=trim ($age);
		$pat="^([0-9]+)$";
		if (ereg ($pat, $age) OR $age=='') { return TRUE ; } else { RETURN FALSE; }
	}
	function check_select ($select, $array) {
		if (array_search($select,$array) OR 0===array_search($select,$array) ) { return TRUE ; } else { RETURN FALSE; }
	}
	function check_yes_no ($option) {
		if ($option=='AND' OR $option=='OR') { return TRUE ; } else { RETURN FALSE; }
	}
	function where_or_and ($and) {
		if  ($and=='') { 
			$and='WHERE '; 
		} elseif ($and=='WHERE '){
			$and='AND ';
		} else {
			$and='AND ';
		}
		return $and;
	}

?>