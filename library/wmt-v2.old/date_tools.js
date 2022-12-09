function oeFormatShortDate(globalFmt) {
  if ($date === 'today') $date = date('Y-m-d');
  if (strlen($date) == 10) {
    // assume input is yyyy-mm-dd
    if ($GLOBALS['date_display_format'] == 1)      // mm/dd/yyyy
      $date = substr($date, 5, 2) . '/' . substr($date, 8, 2) . '/' . substr($date, 0, 4);
    else if ($GLOBALS['date_display_format'] == 2) // dd/mm/yyyy
      $date = substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
  }
  return $date;
}

// 0 - Time format 24 hr
// 1 - Time format 12 hr
function oeFormatTime( $time, $format = "" ) 
{
	$formatted = $time;
	if ( $format == "" ) {
		$format = $GLOBALS['time_display_format'];
	}
	
	if ( $format == 0 ) {
		$formatted = date( "H:i", strtotime( $time ) );	
	} else if ( $format == 1 ) {
		$formatted = date( "g:i a", strtotime( $time ) );		
	}
	
	return $formatted;
}

function testDate(globalFmt, myDate, testDate, testHow)
{
  var allowEmpty = true;
	var dateVal = myDate.value;
	var dateArray;
  if(arguments.length > 4) allowEmpty = arguments[4];
	if(!myDate) {
		if(allowEmpty == 'default') {
			return true;
		}
		if(allowEmpty) return true;
		return false;
	}
	 
	if((globalFmt == 1 || globalFmt == 2) && myDate.indexOf('-') == -1) {
	  dateArray = myDate.split('/');
	  if(globalFmt == 1) dateVal = dateArray[2]+'-'+dateArray[0]+'-'+dateArray[1];
	  if(globalFmt == 2) dateVal = dateArray[2]+'-'+dateArray[1]+'-'+dateArray[0];
	}

	if(testHow == 'lt') {
		if(dateVal < testDate) {
		}
	} else if(testHow == 'gt') {
	} else if(testHow == 'eq') {
	} 
