<?php
	require_once (dirname(__FILE__) ."/../sql.inc");
	require_once("Patient.class.php");
	require_once("Person.class.php");
	require_once("Provider.class.php");
	require_once("Pharmacy.class.php");

/**
 * class ORDataObject
 *
 */

class ORDataObject {
	var $_prefix;
	var $_table;
	var $_db;

	function ORDataObject() {
	  $this->_db = $GLOBALS['adodb']['db']; 	
	}
	
	function persist() {
		$sql = "REPLACE INTO " . $_prefix . $this->_table . " SET ";
		//echo "<br><br>";
		$fields = sqlListFields($this->_table);
		$db = get_db();
		$pkeys = $db->MetaPrimaryKeys($this->_table);

		foreach ($fields as $field) {
			$func = "get_" . $field;
			//echo "f: $field m: $func status: " .  (is_callable(array($this,$func))? "yes" : "no") . "<br>";
			if (is_callable(array($this,$func))) {
				$val = call_user_func(array($this,$func));
				if ((get_magic_quotes_gpc() || get_magic_quotes_runtime()) && !is_array($val)) {
					$val = stripslashes($val);	
				}
				if (in_array($field,$pkeys)  && empty($val)) {
					$last_id = generate_id();
					call_user_func(array(&$this,"set_".$field),$last_id);
					$val = $last_id;
				}

				if (!empty($val)) {
					//echo "s: $field to: $val <br>";
					$sql .= " `" . $field . "` = '" . mysql_real_escape_string(strval($val)) ."',";
				}
			}
		}

		if (strrpos($sql,",") == (strlen($sql) -1)) {
				$sql = substr($sql,0,(strlen($sql) -1));
		}

		//echo "<br>sql is: " . $sql . "<br /><br>";
		sqlQuery($sql);
		return true;
	}

	function populate() {
		$sql = "SELECT * from " . $this->_prefix  . $this->_table . " WHERE id = '" . mysql_real_escape_string(strval($this->id))  . "'";
		$results = sqlQuery($sql);
		  if (is_array($results)) {
			foreach ($results as $field_name => $field) {
				$func = "set_" . $field_name;
				//echo "f: $field m: $func status: " .  (is_callable(array($this,$func))? "yes" : "no") . "<br>";
				if (is_callable(array($this,$func))) {

					if (!empty($field)) {
						//echo "s: $field_name to: $field <br>";
						call_user_func(array(&$this,$func),$field);

					}
				}
			}
		}
	}

	function populate_array($results) {
		  if (is_array($results)) {
			foreach ($results as $field_name => $field) {
				$func = "set_" . $field_name;
				//echo "f: $field m: $func status: " .  (is_callable(array($this,$func))? "yes" : "no") . "<br>";
				if (is_callable(array($this,$func))) {

					if (!empty($field)) {
						//echo "s: $field_name to: $field <br>";
						call_user_func(array(&$this,$func),$field);

					}
				}
			}
		}
	}

	/**
	 * Helper function that loads enumerations from the data as an array, this is also efficient
	 * because it uses psuedo-class variables so that it doesnt have to do database work for each instance
	 *
	 * @param string $field_name name of the enumeration in this objects table
	 * @param boolean $blank optional value to include a empty element at position 0, default is true
	 * @return array array of values as name to index pairs found in the db enumeration of this field  
	 */
	function _load_enum($field_name,$blank = true) {
		if (!empty($GLOBALS['static']['enums'][$this->_table][$field_name]) 
			&& is_array($GLOBALS['static']['enums'][$this->_table][$field_name])
			&& !empty($this->_table)) 												{
				
			return $GLOBALS['static']['enums'][$this->_table][$field_name];		
		}
		else {
			$cols = $this->_db->MetaColumns($this->_table);
			if ($cols && !$cols->EOF) {
				//why is there a foreach here? at some point later there will be a scheme to autoload all enums 
				//for an object rather than 1x1 manually as it is now
				foreach($cols as $col) {
	  		      if ($col->name == $field_name && substr($col->type,0,4) == "enum") {
	  		        preg_match_all("|[\'](.*)[\']|U",$col->type,$enum_types);
	  		        //position 1 is where preg_match puts the matches sans the delimiters
	  		        $enum = $enum_types[1];
	  		        //for future use
	  		        //$enum[$col->name] = $enum_types[1];
	  		      }
			    }
			   array_unshift($enum," ");
			   
			   //keep indexing consistent whether or not a blank is present
			   if (!$blank) {
			     unset($enum[0]);
			   }
			   $enum = array_flip($enum);
			  $GLOBALS['static']['enums'][$this->_table][$field_name] = $enum;
			}	
			return $enum;
		}
	}
	
	function _utility_array($obj_ar,$reverse=false,$blank=true, $name_func="get_name", $value_func="get_id") {
		$ar = array();
		if ($blank) {
			$ar[0] = " ";
		}
		if (!is_array($obj_ar)) return $ar;
		foreach($obj_ar as $obj) {
			$ar[$obj->$value_func()] = $obj->$name_func();	
		}
		if ($reverse) {
			$ar = array_flip($ar);	
		}
		return $ar;
	}

} // end of ORDataObject
?>
