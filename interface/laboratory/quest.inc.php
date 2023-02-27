<?php
// -----------------------------------------------------------------------------------------------------------------
// Vendor = Quest Diagnostics
// -----------------------------------------------------------------------------------------------------------------
if ($form_action == 1) { // load compendium
                         // Get the compendium server parameters
                         // 0: server address
                         // 1: lab identifier (STL, SEA, etc)
                         // 2: user name
                         // 3: password
	$params = array ();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv );
		$params [] = trim ( $acsv [0] );
	}
	
	// verify directory
	$server = $params [0];
	$group = $params [1];
	$login = $params [2];
	$password = $params [3];
	
	echo "<br/>LOADING FROM: " . $server . $group . "<br/><br/>";
	
	$cdcdir = $GLOBALS ['temporary_files_dir'] . "/quest";
	if (! file_exists ( $cdcdir )) {
		if (! mkdir ( $cdcdir, 0700 )) {
			die ( '<br/><br/>Unable to create directory for CDC files (' . $cdcdir . ')' );
		}
	}
	
	$CDC = array ();
	$CDC [] = "/ORDCODE_" . $group . ".TXT";
	$CDC [] = "/METHODOLOGY_" . $group . ".TXT";
	$CDC [] = "/SPECIMENREQ_" . $group . ".TXT";
	$CDC [] = "/SPECIMENSTAB_" . $group . ".TXT";
	$CDC [] = "/SPECIMENVOL_" . $group . ".TXT";
	$CDC [] = "/TRANSPORT_" . $group . ".TXT";
	$CDC [] = "/ANALYTE_" . $group . ".TXT";
	$CDC [] = "/WORKLIST_" . $group . ".TXT";
	
	foreach ( $CDC as $file ) {
		unlink ( $cdcdir . $file ); // remove old file if there is one
		if (($fp = fopen ( $cdcdir . $file, "w+" )) == false) {
			die ( '<br/><br/>Could not create local CDC file (' . $cdcdir . $file . ')' );
		}
		
		$ch = curl_init ();
		$credit = ($login . ':' . $password);
		curl_setopt ( $ch, CURLOPT_URL, $server . $group . $file );
		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt ( $ch, CURLOPT_USERPWD, $credit );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 15 );
		curl_setopt ( $ch, CURLOPT_FILE, $fp );
		
		// testing only!!
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		
		if ((curl_exec ( $ch )) === false) {
			curl_close ( $ch );
			fclose ( $fp );
			unlink ( $cdcdir . $file );
			die ( "<br/><br/>READ ERROR: " . curl_error ( $ch ) . " QUITING..." );
		}
		
		curl_close ( $ch );
		fclose ( $fp );
	}
	
	// verify required files
	if (! file_exists ( $cdcdir . "/ORDCODE_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [ORDCODE_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/METHODOLOGY_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [METHODOLOGY_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/SPECIMENREQ_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [SPECIMENREQ_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/SPECIMENSTAB_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [SPECIMENSTAB_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/SPECIMENVOL_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [SPECIMENVOL_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/TRANSPORT_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [TRANSPORT_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/ANALYTE_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [ANALYTE_" . $group . ".TXT] not accessable!!" );
	if (! file_exists ( $cdcdir . "/WORKLIST_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium order file [WORKLIST_" . $group . ".TXT] not accessable!!" );
		
		// Delete the detail records for this lab.
	sqlStatement ( "DELETE FROM procedure_type WHERE lab_id = ? AND (procedure_type = 'det' OR procedure_type = 'res') ", array (
			$lab_id 
	) );
	
	// Mark everything else for the indicated lab as inactive.
	sqlStatement ( "UPDATE procedure_type SET activity = 0, related_code = '' WHERE lab_id = ? AND procedure_type != 'grp' ", array (
			$lab_id 
	) );
	
	// Load category group ids (procedure and profile)
	$result = sqlStatement ( "SELECT procedure_type_id, name FROM procedure_type WHERE parent = ? AND procedure_type = 'grp'", array (
			$form_group 
	) );
	while ( $record = sqlFetchArray ( $result ) )
		$groups [$record ['name']] = $record [procedure_type_id];
	if (! $groups ['Profiles'] || ! $groups ['Procedures'])
		die ( "<br/><br/>Missing required compendium groups [Profiles, Procedures]" );
		
		// open the order code file for processing
	$fhcsv = fopen ( $cdcdir . "/ORDCODE_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium order file [ORDCODE_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the Order Compendium (ORDCODE) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Order Code : mapped as procedure_code
	// 2: State : mapped as route_admin
	// 3: Unit Code : mapped as standard code
	// 4: Active Flag : mapped as activity
	// 5: Insert Date
	// 6: Order Name : mapped as procedure name
	// 7: Specimen : mapped as specimen
	// 8: NBS Service
	// 9: Performed
	// 10: Updated Date
	// 11: Update User
	// 12: Sufix
	// 13: Profile Flag : mapped as procedure_type
	// 14: Selectable
	// 15: NBS Site
	// 16: Test Flag
	// 17: No Bill
	// 18: Bill Only
	// 19: Reflex Count
	// 20: Conform Flag
	// 21: Alt Temp
	// 22: Pap Flag : mapped with route_admin
	
	$lastcode = '';
	$pseq = 1;
	
	echo "<pre style='font-size:10px'>";
	
	$codes = array ();
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the order
		$ordercode = trim ( $ahl7 [1] );
		if ($lastcode != $ordercode) { // new code (store only once)
			$stdcode = '';
			if (trim ( $ahl7 [3] ) != '')
				$stdcode .= "UNIT:" . trim ( $ahl7 [3] );
			
			$state = (strtoupper ( trim ( $ahl7 [22] ) ) == 'P') ? 'PAP' : strtoupper ( trim ( $ahl7 [2] ) );
			$type = (strtoupper ( $profile ) == 'Y') ? 'pro' : 'ord';
			
			$profile = trim ( $ahl7 [13] );
			$groupid = $groups ['Procedures'];
			if ($type == 'pro')
				$groupid = $groups ['Profiles'];
			
			$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND procedure_type = ? ORDER BY procedure_type_id DESC LIMIT 1", array (
					$groupid,
					$ordercode,
					$type 
			) );
			
			$name = preg_replace ( "/\r|\n/", " ", trim ( $ahl7 [6] ) );
			$specimen = trim ( $ahl7 [7] );
			$activity = trim ( $ahl7 [4] );
			$activity = ($activity == 'A') ? 1 : 0;
			
			$speclist [$specimen] = $specimen; // store unique names
			
			if (empty ( $trow ['procedure_type_id'] )) {
				$orderid = sqlInsert ( "INSERT INTO procedure_type SET parent = ?, name = ?, specimen = ?, transport = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = ?", array (
						$groupid,
						$name,
						$specimen,
						$state,
						$lab_id,
						$ordercode,
						$stdcode,
						$type,
						$pseq ++,
						$activity 
				) );
			} else {
				$orderid = $trow ['procedure_type_id'];
				sqlStatement ( "UPDATE procedure_type SET parent = ?, name = ?, specimen = ?, transport = ?, lab_id = ?, procedure_code = ?, standard_code = ?, procedure_type = ?, seq = ?, activity = ? WHERE procedure_type_id = ?", array (
						$groupid,
						$name,
						$specimen,
						$state,
						$lab_id,
						$ordercode,
						$stdcode,
						$type,
						$pseq ++,
						$activity,
						$orderid 
				) );
			}
			
			// store test code/order id cross reference
			$codes [$ordercode] = $orderid;
			
			if ($type == 'pro')
//				echo "PROFILE: $row";
				echo "PROFILE: $ahl7[0], $ordercode, $type, $name\n";
			else
//				echo "TEST: $row";
				echo "TEST: $ahl7[0], $ordercode, $type, $name\n";
			flush ();
			
			// reset counters for new procedure
			$lastcode = $ordercode;
		}
	}
	
	// done with the order file
	fclose ( $fhcsv );
	echo "</pre>";
	
	// update list_option table
	if (is_array ( $speclist )) {
		foreach ( $speclist as $specimen ) {
			sqlStatement ( "REPLACE INTO list_options SET list_id = 'proc_specimen', option_id = ?, title = ?", array (
					$specimen,
					$specimen 
			) );
		}
	}
	
	// open the specimen requirements file for processing
	$fhcsv = fopen ( $cdcdir . "/SPECIMENREQ_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium requirements file [SPECIMENREQ_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the requirements (SPECIMENREQ) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Order Code : mapped as procedure_code
	// 2: Sequence
	// 3: Description : mapped as description
	
	$lastcode = '';
	$text = '';
	$dseq = 100;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the description
		$ordercode = trim ( $ahl7 [1] );
		if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
			if ($codes [$lastcode]) { // only save if there is a parent order
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$codes [$lastcode],
						'PREFERRED SPECIMEN',
						'Preferred specimen collection method',
						$lab_id,
						$lastcode,
						$text,
						'det',
						$dseq ++ 
				) );
			}
			$text = '';
			$dseq = 100;
		} else { // still working with the last code
			$text .= trim ( $ahl7 [3] ) . "\n";
		}
		
		echo "PROCESS: $row";
		flush ();
		
		// reset counters for new procedure
		$lastcode = $ordercode;
	}
	
	if ($lastcode) { // new code (store and restart)
		if ($orders [$lastcode]) { // only save if there is a parent order
			sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
					$orders [$lastcode],
					'PREFERRED SPECIMEN',
					'Preferred specimen collection method',
					$lab_id,
					$lastcode,
					$text,
					'det',
					$dseq ++ 
			) );
		}
	}
	
	// done with the requirements file
	fclose ( $fhcsv );
	echo "</pre>";
	
	// open the specimen stability file for processing
	$fhcsv = fopen ( $cdcdir . "/SPECIMENSTAB_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium stability file [SPECIMENSTAB_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the stability (SPECIMENSTAB) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Order Code : mapped as procedure_code
	// 2: Sequence
	// 3: Description : mapped as description
	
	$lastcode = '';
	$text = '';
	$dseq = 200;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the description
		$ordercode = trim ( $ahl7 [1] );
		if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
			if ($codes [$lastcode]) { // only save if there is a parent order
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$codes [$lastcode],
						'SPECIMEN STABILITY',
						'Specimen storage stability',
						$lab_id,
						$lastcode,
						$text,
						'det',
						$dseq ++ 
				) );
			}
			$text = '';
			$dseq = 200;
		} else { // still working with the last code
			$text .= trim ( $ahl7 [3] ) . "\n";
		}
		
		echo "STABILITY: $row";
		flush ();
		
		// reset counters for new procedure
		$lastcode = $ordercode;
	}
	
	if ($lastcode) { // new code (store and restart)
		if ($orders [$lastcode]) { // only save if there is a parent order
			sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
					$orders [$lastcode],
					'SPECIMEN STABILITY',
					'Specimen storage stability',
					$lab_id,
					$lastcode,
					$text,
					'det',
					$dseq ++ 
			) );
		}
	}
	
	// done with the stability file
	fclose ( $fhcsv );
	echo "</pre>";
	
	// open the specimen volume file for processing
	$fhcsv = fopen ( $cdcdir . "/SPECIMENVOL_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium volume file [SPECIMENVOL_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the stability (SPECIMENSTAB) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Order Code : mapped as procedure_code
	// 2: Sequence
	// 3: Description : mapped as description
	
	$lastcode = '';
	$text = '';
	$dseq = 300;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the description
		$ordercode = trim ( $ahl7 [1] );
		if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
			if ($codes [$lastcode]) { // only save if there is a parent order
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$codes [$lastcode],
						'SPECIMEN VOLUME',
						'Specimen volume requirements',
						$lab_id,
						$lastcode,
						$text,
						'det',
						$dseq ++ 
				) );
			}
			$text = '';
			$dseq = 300;
		} else { // still working with the last code
			$text .= trim ( $ahl7 [3] ) . "\n";
		}
		
		echo "VOLUME: $row";
		flush ();
		
		// reset counters for new procedure
		$lastcode = $ordercode;
	}
	
	if ($lastcode) { // new code (store and restart)
		if ($orders [$lastcode]) { // only save if there is a parent order
			sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
					$orders [$lastcode],
					'SPECIMEN VOLUME',
					'Specimen volume requirements',
					$lab_id,
					$lastcode,
					$text,
					'det',
					$dseq ++ 
			) );
		}
	}
	
	// done with the volume file
	fclose ( $fhcsv );
	echo "</pre>";
	
	// open the specimen transport file for processing
	$fhcsv = fopen ( $cdcdir . "/TRANSPORT_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium transport file [TRANSPORT_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the transport (TRANSPORT) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Order Code : mapped as procedure_code
	// 2: Sequence
	// 3: Description : mapped as description
	
	$lastcode = '';
	$text = '';
	$dseq = 400;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the description
		$ordercode = trim ( $ahl7 [1] );
		if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
			if ($codes [$lastcode]) { // only save if there is a parent order
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$codes [$lastcode],
						'SPECIMEN TRANSPORT',
						'Specimen transport requirements',
						$lab_id,
						$lastcode,
						$text,
						'det',
						$dseq ++ 
				) );
			}
			$text = '';
			$dseq = 400;
		} else { // still working with the last code
			$text .= trim ( $ahl7 [3] ) . "\n";
		}
		
		echo "TRANSPORT: $row";
		flush ();
		
		// reset counters for new procedure
		$lastcode = $ordercode;
	}
	
	if ($lastcode) { // new code (store and restart)
		if ($orders [$lastcode]) { // only save if there is a parent order
			sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
					$orders [$lastcode],
					'SPECIMEN TRANSPORT',
					'Specimen transport requirements',
					$lab_id,
					$lastcode,
					$text,
					'det',
					$dseq ++ 
			) );
		}
	}
	
	// done with the transport file
	fclose ( $fhcsv );
	echo "</pre>";
	
	// open the methodology file for processing
	$fhcsv = fopen ( $cdcdir . "/METHODOLOGY_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium methodology file [METHODOLOGY_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the methodology (METHODOLOGY) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Order Code : mapped as procedure_code
	// 2: Sequence
	// 3: Description : mapped as description
	
	$lastcode = '';
	$text = '';
	$dseq = 600;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the description
		$ordercode = trim ( $ahl7 [1] );
		if ($lastcode && $lastcode != $ordercode) { // new code (store and restart)
			if ($codes [$lastcode]) { // only save if there is a parent order
				sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
						$codes [$lastcode],
						'TESTING METHODOLOGY',
						'Method of performing test',
						$lab_id,
						$lastcode,
						$text,
						'det',
						$dseq ++ 
				) );
			}
			$text = '';
			$dseq = 600;
		} else { // still working with the last code
			$text .= trim ( $ahl7 [3] ) . "\n";
		}
		
		echo "METHOD: $row";
		flush ();
		
		// reset counters for new procedure
		$lastcode = $ordercode;
	}
	
	if ($lastcode) { // new code (store and restart)
		if ($orders [$lastcode]) { // only save if there is a parent order
			sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, description = ?, lab_id = ?, procedure_code = ?, notes = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
					$orders [$lastcode],
					'TESTING METHODOLOGY',
					'Method of performing test',
					$lab_id,
					$lastcode,
					$text,
					'det',
					$dseq ++ 
			) );
		}
	}
	
	// done with the requirements file
	fclose ( $fhcsv );
	echo "</pre>";
	
	// open the results file for processing
	$fhcsv = fopen ( $cdcdir . "/ANALYTE_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium results file [ANALYTE_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the results (ANALYTE) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Top Lab
	// 2: Analyte Code : mapped as procedure_code
	// 3: Mnemonic
	// 4: Description 1
	// 5: Description 2
	// 6: blank
	// 7: blank
	// 8: LOINC
	// 9: UOM
	
	$lastcode = '';
	$text = '';
	$dseq = 700;
	$results = array ();
	
	echo "<pre style='font-size:10px'>";
	
	// retrieve all of the result records
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
		// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store all of the result records
		$resultcode = trim ( $ahl7 [2] );
		$results [$resultcode] = $ahl7;
		
		echo "RESULTS: $row";
		flush ();
	}
	
	// done with the results file
	fclose ( $fhcsv );
	
	// open the results cross reference file for processing
	$fhcsv = fopen ( $cdcdir . "/WORKLIST_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium results cross-reference file [WORKLIST_" . $group . ".TXT] could not be openned!!" );
	}
	
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Top Lab
	// 2: Test Code
	// 3: Suffix
	// 4: Result Code
	// 5: Unit Code
	// 6: Active Flag
	// 7: Update Date
	
	// retrieve each cross-reference record
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
		
		// explode all content row fields
		$ahl7 = explode ( '^', $row );
	
		$ordercode = trim ( $ahl7 [2] ); // checking for test record
		if (!$ordercode || !$codes[$ordercode]) continue; // no match
	
		$stdcode = trim ( $ahl7 [4] ); // checking for result record
		if (!$stdcode || !$results[$stdcode]) continue; // no match
	 
		// store the result data
		$title = preg_replace ( "/\r|\n/", " ", trim ( $results [$stdcode] [4] ) );
		$title2 = trim ( $results [$stdcode] [5] );
		if ($title2)
			$title .= " " . $title2;
	
		if ( !$title ) continue; // must have a title
		
		$descretecode = trim ( $results [$stdcode] [8] );
		if ( !$descretecode ) $descretecode = $stdcode;
		$stdcode = 'Quest: '.$stdcode;
	
		$units = trim ( $results [$stdcode] [9] );
	
		sqlStatement ( "REPLACE INTO procedure_type SET parent = ?, name = ?, lab_id = ?, procedure_code = ?, standard_code = ?, units = ?, procedure_type = ?, seq = ?, activity = 1 ", array (
				$codes [$ordercode],
				$title,
				$lab_id,
				$descretecode,
				$stdcode,
				$units,
				'res',
				$dseq
			) );
	
		echo "DESCRETE: $row";
				
	}
	
	echo "</pre>";
} 

else if ($form_action == 2) { // load questions
                              // Get the compendium server parameters
                              // 0: server address
                              // 1: lab identifier (STL, SEA, etc)
                              // 2: user name
                              // 3: password
	$params = array ();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv );
		$params [] = trim ( $acsv [0] );
	}
	
	// verify directory
	$server = $params [0];
	$group = $params [1];
	$login = $params [2];
	$password = $params [3];
	
	echo "<br/>LOADING FROM: " . $server . $group . "<br/><br/>";
	
	$cdcdir = $GLOBALS ['temporary_files_dir'] . "/quest";
	if (! file_exists ( $cdcdir )) {
		if (! mkdir ( $cdcdir, 0700 )) {
			die ( '<br/><br/>Unable to create directory for CDC files (' . $cdcdir . ')' );
		}
	}
	
	$CDC = array ();
	$CDC [] = "/AOE_" . $group . ".TXT";
	
	foreach ( $CDC as $file ) {
		unlink ( $cdcdir . $file ); // remove old file if there is one
		if (($fp = fopen ( $cdcdir . $file, "w+" )) == false) {
			die ( '<br/><br/>Could not create local CDC file (' . $cdcdir . $file . ')' );
		}
		
		$ch = curl_init ();
		$credit = ($login . ':' . $password);
		curl_setopt ( $ch, CURLOPT_URL, $server . $group . $file );
		//curl_setopt ( $ch, CURLOPT_URL, 'https://cert.hub.care360.com/webdav/cdc/' . $group . $file );
		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt ( $ch, CURLOPT_USERPWD, $credit );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 15 );
		curl_setopt ( $ch, CURLOPT_FILE, $fp );
		
		// testing only!!
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		
		if (($xml = curl_exec ( $ch )) === false) {
			curl_close ( $ch );
			fclose ( $fp );
			unlink ( $path . $file );
			die ( "<br/><br/>READ ERROR: " . curl_error ( $ch ) . " QUITING..." );
		}
		
		curl_close ( $ch );
		fclose ( $fp );
	}
	
	// verify required file
	if (! file_exists ( $cdcdir . "/AOE_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium AOE file [AOE_" . $group . ".TXT] not accessable!!" );
		
		// Mark the vendor's current questions inactive.
	sqlStatement ( "UPDATE procedure_questions SET activity = 0 WHERE lab_id = ?", array (
			$lab_id 
	) );
	
	// open the specimen requirements file for processing
	$fhcsv = fopen ( $cdcdir . "/AOE_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium AOE questions file [AOE_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the AOE questions (AOE) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Performing Lab
	// 2: Unit Code
	// 3: Order Code : mapped as procedure_code
	// 4: Analyte Code : mapped as question_code
	// 5: Question Code
	// 6: Active Flag : mapped as activity (0/1)
	// 7: Profile Key
	// 8: Insert Date
	// 9: Question : mapped as question_text
	// 10: Suffix
	// 11: Result Filter : mapped as tips
	// 12: Mnemonic
	// 13: Test Flag
	// 14: Update Date
	// 15: Update User
	// 16: Component
	//
	
	$lastcode = '';
	$text = '';
	$seq = 1;
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the data
		$pcode = trim ( $ahl7 [3] );
		$qcode = trim ( $ahl7 [4] );
		$fldtype = 'T'; // always text
		$required = 1; // always required
		$activity = trim ( $ahl7 [6] );
		$activity = ($activity == 'A') ? 1 : 0;
		$question = trim ( $ahl7 [9] );
		$question = str_replace ( ':', '', $question ); // no colon (I add them)
		$question = str_replace ( '?', '', $question ); // no question mark (inconsistant)
		
		if (empty ( $pcode ) || empty ( $qcode ))
			continue;
			
			// check for existing record
		$qrow = sqlQuery ( "SELECT * FROM procedure_questions WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
				$lab_id,
				$pcode,
				$qcode 
		) );
		
		// new record
		if (empty ( $qrow ['procedure_code'] )) {
			sqlStatement ( "INSERT INTO procedure_questions SET seq = ?, lab_id = ?, procedure_code = ?, question_code = ?, question_text = ?, fldtype = ?, required = ?, tips = ?, activity = ?", array (
					$seq ++,
					$lab_id,
					$pcode,
					$qcode,
					$question,
					$fldtype,
					$required,
					trim ( $ahl7 [11] ),
					$activity 
			) );
		} else { // update record
			sqlStatement ( "UPDATE procedure_questions SET seq = ?, question_text = ?, fldtype = ?, required = ?, tips = ?, activity = ? WHERE lab_id = ? AND procedure_code = ? AND question_code = ?", array (
					$seq ++,
					$question,
					$fldtype,
					$required,
					trim ( $ahl7 [11] ),
					$activity,
					$lab_id,
					$pcode,
					$qcode 
			) );
		}
		
		echo "QUESTION: $row";
		flush ();
	} // end while
	
	echo "</pre>";
} // end load questions

if ($form_action == 4) { // load profiles
                         // Get the compendium server parameters
                         // 0: server address
                         // 1: lab identifier (STL, SEA, etc)
                         // 2: user name
                         // 3: password
	$params = array ();
	while ( ! feof ( $fhcsv ) ) {
		$acsv = fgetcsv ( $fhcsv );
		$params [] = trim ( $acsv [0] );
	}
	
	// verify directory
	$server = $params [0];
	$group = $params [1];
	$login = $params [2];
	$password = $params [3];
	
	echo "<br/>LOADING FROM: " . $server . $group . "<br/><br/>";
	
	$cdcdir = $GLOBALS ['temporary_files_dir'] . "/quest";
	if (! file_exists ( $cdcdir )) {
		if (! mkdir ( $cdcdir, 0700 )) {
			die ( '<br/><br/>Unable to create directory for CDC files (' . $cdcdir . ')' );
		}
	}
	
	$CDC = array ();
	$CDC [] = "/PROFILE_" . $group . ".TXT";
	
	foreach ( $CDC as $file ) {
		unlink ( $cdcdir . $file ); // remove old file if there is one
		if (($fp = fopen ( $cdcdir . $file, "w+" )) == false) {
			die ( '<br/><br/>Could not create local CDC file (' . $cdcdir . $file . ')' );
		}
		
		$ch = curl_init ();
		$credit = ($login . ':' . $password);
		curl_setopt ( $ch, CURLOPT_URL, $server . $group . $file );
		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
		curl_setopt ( $ch, CURLOPT_USERPWD, $credit );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 90 );
		curl_setopt ( $ch, CURLOPT_FILE, $fp );
		
		// testing only!!
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		
		if (($xml = curl_exec ( $ch )) === false) {
			curl_close ( $ch );
			fclose ( $fp );
			unlink ( $path . $file );
			die ( "<br/><br/>READ ERROR: " . curl_error ( $ch ) . " QUITING..." );
		}
		
		curl_close ( $ch );
		fclose ( $fp );
	}
	
	// verify required file
	if (! file_exists ( $cdcdir . "/PROFILE_" . $group . ".TXT" ))
		die ( "<br/><br/>Compendium profile file [PROFILE_" . $group . ".TXT] not accessable!!" );
		
	// open the profile file for processing
	$fhcsv = fopen ( $cdcdir . "/PROFILE_" . $group . ".TXT", 'r' );
	if (! $fhcsv) {
		die ( "<br/><br/>Compendium profile file [PROFILE_" . $group . ".TXT] could not be openned!!" );
	}
	
	// What should be uploaded is the profile (PROFILE) file provided
	// by Quest, saved in "Text HL7" format. The contents are '^' delimited.
	// Values for each row are:
	// 0: Quest Group : group identifier (STL, ORD, QTE, etc)
	// 1: Performing Lab
	// 2: Order Code : mapped as procedure_code
	// 3: Test Code
	// 4: Unit Code : array stored as related_code
	// 5: Description
	// 6: Specimen Type
	// 7: State
	//
	
	$pcode = '';
	$ucode = '';
	$lastcode = '';
	$components = array ();
	$codes = array();
	
	echo "<pre style='font-size:10px'>";
	
	while ( ! feof ( $fhcsv ) ) {
		$row = fgets ( $fhcsv );
		if (substr ( $row, 0, 3 ) != $group)
			continue;
			
			// explode all content row fields
		$ahl7 = explode ( '^', $row );
		
		// store the data
		$ordercode = trim ( $ahl7 [2] );
		if (empty ( $ordercode ))
			continue;
		
		if ($lastcode && $lastcode != $ordercode) { // new code (store only once)
		                                            // store componets for previous record
			$trow = sqlQuery ( "SELECT procedure_type_id FROM procedure_type WHERE parent = ? AND procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' ORDER BY procedure_type_id DESC LIMIT 1", array (
					$groupid,
					$lastcode,
					$lab_id 
			) );
			
			if (! empty ( $trow ['procedure_type_id'] )) {
				$comp_list = implode ( "^", $components );
				sqlInsert ( "UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?", array (
						$comp_list,
						$trow ['procedure_type_id'] 
				) );
				$components = array ();
			}

			echo "PROFILE: $lastcode\n";
			flush ();
		
		}
		
		echo "COMPONENT: $row";
		flush ();
		
		// collect the comopnents
		$comp = trim ( $ahl7 [4] );
		$components [$comp] = $comp;
		$lastcode = $ordercode;
		$testcode = trim ( $ahl7 [3] );
		$codes [$testcode] = $trow ['procedure_type_id'];
	}
	
	// process last profile code
	$trow = sqlQuery ( "SELECT * FROM procedure_type WHERE parent = ? AND procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' ORDER BY procedure_type_id DESC LIMIT 1", array (
			$groupid,
			$lastcode,
			$lab_id 
	) );
	
	if (! empty ( $trow ['procedure_type_id'] )) {
		$comp_list = implode ( "^", $components );
		$comp_list = $comp_list;
		sqlInsert ( "UPDATE procedure_type SET related_code = ? WHERE procedure_type_id = ?", array (
				$comp_list,
				$trow ['procedure_type_id'] 
		) );
		$components = array ();
	}
	
	echo "</pre>";

}


