<?php

include_once("../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;


if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['pagetype'])) $_REQUEST['pagetype'] = '';

$pid = strip_tags($_REQUEST['pid']);
$pagetype = $_REQUEST['pagetype'];
$pageTypeStr = !empty($pagetype) ? '&pagetype='.$pagetype : '';

function checkName($row) {
	$name = '';

	if(!empty($row['lname']) && !empty($row['fname'])) {
		$name = $row['lname'].', '.$row['fname'].' '.$row['mname'];
	} else if(!empty($row['organization'])) {
		$name = $row['organization'];
	}

	return $name;
}

function addressName($row) {
	$name = '';

	if(!empty($row['lname']) && !empty($row['fname'])) {
		$name = $row['fname'].' '.$row['lname'];
	} else if(!empty($row['organization'])) {
		$name = $row['organization'];
	}

	return $name;
}

if(isset($_REQUEST['ajax'])) {
	$aColumns = explode(',', $_REQUEST['sColumns']);

	// Paging parameters.  -1 means not applicable.
	//
	$iDisplayStart  = isset($_REQUEST['iDisplayStart' ]) ? 0 + $_REQUEST['iDisplayStart' ] : -1;
	$iDisplayLength = isset($_REQUEST['iDisplayLength']) ? 0 + $_REQUEST['iDisplayLength'] : -1;
	$limit = '';
	if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
	    $limit = "LIMIT " . escape_limit($iDisplayStart) . ", " . escape_limit($iDisplayLength);
	}

	// Column sorting parameters.
	//
	$orderby = '';
	if (isset($_REQUEST['iSortCol_0'])) {
	    for ($i = 0; $i < intval($_REQUEST['iSortingCols']); ++$i) {
	        $iSortCol = intval($_REQUEST["iSortCol_$i"]);
	        if ($_REQUEST["bSortable_$iSortCol"] == "true") {
	            $sSortDir = escape_sort_order($_REQUEST["sSortDir_$i"]); // ASC or DESC
	      		// We are to sort on column # $iSortCol in direction $sSortDir.
	            $orderby .= $orderby ? ', ' : 'ORDER BY ';
	      		//
	            if ($aColumns[$iSortCol] == 'name') {
	                    $orderby .= "lname $sSortDir, fname $sSortDir, mname $sSortDir";
	            } else if ($aColumns[$iSortCol] == 'street') {
	        		$orderby .= "street $sSortDir, streetb2 $sSortDir";
		        } else if ($aColumns[$iSortCol] == 'city_state') {
		            $orderby .= "city $sSortDir, state $sSortDir";
		        } else {
	                $orderby .= "`" . escape_sql_column_name($aColumns[$iSortCol], array('users')) . "` $sSortDir";
	            }
	        }
	    }
	}

	if($pagetype == "fax") {
		$typeWhere = "NOT (u.fax = '') ";
	} else if($pagetype == "postal_letter") {
		$typeWhere = "NOT (u.zip = '') ";
	}

	if(isset($typeWhere)) {
		$typeWhereTmp = "AND " . $typeWhere . " ";
	}

	// Global filtering.
	//
	$tmp_where = "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) " . $typeWhereTmp;
	$where = "";
	if (isset($_GET['sSearch']) && $_GET['sSearch'] !== "") {
	    $sSearch = add_escape_custom(trim($_GET['sSearch']));
	    foreach ($aColumns as $colname) {
	        $where .= $where ? "OR " : " ( ";
	        if ($colname == 'name') {
	            $where .=
	            "lname LIKE '$sSearch%' OR " .
	            "fname LIKE '$sSearch%' OR " .
	            "mname LIKE '$sSearch%' OR ";
	            $where .= "CONCAT( lname,  ', ', fname, ' ',  mname) LIKE '$sSearch%' ";
	        } else if ($colname == 'street') {
	        	$where .= "" .
	            "street LIKE '$sSearch%' OR " .
	            "streetb2 LIKE '$sSearch%' ";
	        } else if ($colname == 'city_state') {
	            $where .= "" .
	            "city LIKE '$sSearch%' OR " .
	            "state LIKE '$sSearch%' ";
	        } else {
	            $where .= "`" . escape_sql_column_name($colname, array('users')) . "` LIKE '$sSearch%' ";
	        }
	    }

	    if ($where) {
	        $where .= ")";
	    }
	}

	// Column-specific filtering.
	//
	for ($i = 0; $i < count($aColumns); ++$i) {
	    $colname = $aColumns[$i];
	    if (isset($_GET["bSearchable_$i"]) && $_GET["bSearchable_$i"] == "true" && $_GET["sSearch_$i"] != '') {
	        $where .= $where ? ' AND' : "";
	        $sSearch = add_escape_custom($_GET["sSearch_$i"]);
	        if ($colname == 'name') {
	            $where .= " ( " .
	            "lname LIKE '$sSearch%' OR " .
	            "fname LIKE '$sSearch%' OR " .
	            "mname LIKE '$sSearch%' ) OR";
	            $where .= " ( CONCAT( lname,  ', ', fname, ' ',  mname) LIKE '$sSearch%' ) ";
	        } else if ($colname == 'street') {
	        	$where .= " ( " .
	            "street LIKE '$sSearch%' OR " .
	            "streetb2 LIKE '$sSearch%' ) ";
	        } else if ($colname == 'city_state') {
	            $where .= " ( " .
	            "city LIKE '$sSearch%' OR " .
	            "state LIKE '$sSearch%' ) ";
	        } else {
	            $where .= " `" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE '$sSearch%'";
	        }
	    }
	}

	$where = !empty($where) ? "AND " . $where : "";
	$where = $tmp_where . $where;

	// Get total number of rows in the table.
	//
	$iTotalsqlQtr = "SELECT COUNT(u.id) AS count FROM users AS u " .
	  "LEFT JOIN list_options AS lo ON " .
	  "list_id = 'abook_type' AND option_id = u.abook_type AND activity = 1 " .
	  "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) " . $typeWhereTmp;
	$row = sqlQuery($iTotalsqlQtr);
	$iTotal = $row['count'];

	// Get total number of rows in the table after filtering.
	//
	$iFilteredTotalsqlQtr = "SELECT COUNT(u.id) AS count FROM users AS u " .
	  "LEFT JOIN list_options AS lo ON " .
	  "list_id = 'abook_type' AND option_id = u.abook_type AND activity = 1 ";
	$row = sqlQuery($iFilteredTotalsqlQtr . $where);
	$iFilteredTotal = $row['count'];

	$out = array(
	  "sEcho"                => intval($_GET['sEcho']),
	  "iTotalRecords"        => $iTotal,
	  "iTotalDisplayRecords" => $iFilteredTotal,
	  "aaData"               => array()
	);

	$sellist = "u.id, u.organization, u.fname, u.mname, u.lname, u.abook_type, u.specialty, u.phonew1, u.fax, u.street, u.streetb2, u.city, u.state, u.zip";
	$query = "SELECT $sellist FROM users AS u " .
	  "LEFT JOIN list_options AS lo ON " .
	  "list_id = 'abook_type' AND option_id = u.abook_type AND activity = 1 $where $orderby $limit";
	$res = sqlStatement($query);
	while ($row = sqlFetchArray($res)) {

		$lastStr = '';
		$nameStr = '';
		if($pagetype == "postal_letter") {
			$pl = PostalLetter::generatePostalAddress(array(
				'street' => $row['street'],
				'street1' => $row['streetb2'],
				'city' => $row['city'],
				'state' => $row['state'],
				'postal_code' => $row['zip']
			), "\n");
			$lastStr = base64_encode(json_encode($pl));
			$nameStr = addressName($row);
		} else {
			$lastStr = $row['fax'];
			$nameStr = checkName($row);
		}

		$arow = array('DT_RowId' => $row['id'].'~'.$nameStr.'~'.$lastStr);

		foreach ($aColumns as $colname) {
        	if ($colname == 'name') {
	            $name = $row['lname'];
	            if ($name && $row['fname']) {
	                $name .= ', ';
	            }

	            if ($row['fname']) {
	                $name .= $row['fname'];
	            }

	            if ($row['mname']) {
	                $name .= ' ' . $row['mname'];
	            }

	            $arow[] = attr($name);
	        } else if($colname == 'street') {
        		$arow[] = attr(trim($row['street'] . ", " . $row['streetb2'], ", "));
        	} else if($colname == 'city_state') { 
        		$arow[] = attr(trim($row['city'] . " " . $row['state'], ", "));
        	} else {
	            $arow[] = isset($row[$colname]) ? $row[$colname] : '';
	        }
	    }

	    $out['aaData'][] = $arow;
	}

	echo json_encode($out, 15);

} else {

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('AddressBook Finder'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'fontawesome', 'oemr_ad']); ?>

	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>

    <style type="text/css">
		/*table#addressDataTable thead th, table#addressDataTable thead td {
			border-bottom: 0px solid black;
		}
		table#addressDataTable thead th, table#addressDataTable thead td,
		table#addressDataTable tr th, table#addressDataTable tr td {
			padding: 4px!important;
		}

		table#addressDataTable tr td {
			border-top: 1px solid black;
			vertical-align: text-top;
		}
		table#addressDataTable tr:hover td {
			background-color: #bbb!important;
			cursor: pointer;
		}

		.sectionTitle {
			padding: 0px 10px;
			margin-top: 20px;
			margin-bottom: 10px;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button {
			padding: 5px 10px!important;
		    font-size: 12px!important;
		    line-height: 1.5!important;
		    border-radius: 3px!important;
		    box-shadow: none!important;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button.current{
			background: #2672ec!important;
			color: #FFF!important;
		}
		#addressDataTable_wrapper {
			margin-top: 20px;
		}
		#addressDataTable_wrapper {
			
		}
		#addressDataTable_wrapper input{
			padding: 5px 12px;
		    font-size: 14px;
		    line-height: 1.42857143;
		    color: #555;
		    background-color: #fff;
		    background-image: none;
		    border: 1px solid #444444;
		    border-radius: 4px;
		}
		#addressDataTable_filter {
			margin-right: 10px;
			margin-bottom: 20px;
		}*/
		.disclaimersContainer {
			font-size: 14px;
			padding: 15px;
		}
	</style>
    <style type="text/css">
    	.addressDataTable {
    		width: 100%!important;
    	}
    </style>
    <script language="JavaScript">

	 function selfax(id, name, data) {
		if (opener.closed || ! opener.setAddressBook)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setAddressBook(id, name, data);
		window.close();
		return false;
	 }

	</script>
</head>
<body>
	<div class="disclaimersContainer">
		<b>Disclaimer:</b> <span>Result will contain only active entries</span>
	</div>
	<div class="table-responsive table-container">
		<table id='addressDataTable' class='table table-sm addressDataTable'>
		  <thead class="thead-dark">
		    <tr>
		      <th>Organization</th>
		      <th>Name</th>
		      <th width="120">Fax</th>
		      <th>Specialty</th>
			  <th>Street</th>
			  <th>City, State</th>
			  <th>Zip</th>
		    </tr>
		  </thead>
		</table>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
		   $('#addressDataTable').dataTable({
		      'processing': true,
		      'serverSide': true,
		      'pageLength': 8,
		      'bLengthChange': false,
		      'sAjaxSource': '<?php echo $GLOBALS['webroot']."/interface/main/attachment/find_addressbook_popup.php?pid=". $pid; ?>&ajax=1<?php echo $pageTypeStr; ?>',
		      'columns': [
		      	 { sName: 'organization' },
		         { sName: 'name' },
		         { sName: 'fax' },
		         { sName: 'specialty' },
				 { sName: 'street' },
				 { sName: 'city_state' },
				 { sName: 'zip' }
		      ],
		      <?php // Bring in the translations ?>
    			<?php $translationsDatatablesOverride = array('search'=>(xla('Search all columns') . ':')) ; ?>
    		 <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
		   });

		    $("#addressDataTable").on('click', 'tbody > tr', function() { SelectAddressBook(this); });

		    var SelectAddressBook = function (eObj) {
			    objID = eObj.id;
			    var parts = objID.split("~");
			    return selfax(parts[0], parts[1], parts[2]);
			}

		});
	</script>
</body>
</html>
<?php	
}