<?php 


include_once("../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");
include_once($GLOBALS['srcdir']."/classes/InsuranceCompany.class.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['pagetype'])) $_REQUEST['pagetype'] = '';

$pid = strip_tags($_REQUEST['pid']);
$pagetype = $_REQUEST['pagetype'];
$pageTypeStr = !empty($pagetype) ? '&pagetype='.$pagetype : '';

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
	            if ($aColumns[$iSortCol] == 'address') {
	        		$orderby .= "line1 $sSortDir, line2 $sSortDir";
		        } else if ($aColumns[$iSortCol] == 'city_state') {
		            $orderby .= "city $sSortDir, state $sSortDir";
		        } else if ($aColumns[$iSortCol] == 'phone') {
	                $orderby .= "ph.area_code $sSortDir, ph.prefix $sSortDir, ph.number $sSortDir";
	            } else if ($aColumns[$iSortCol] == 'fax') {
	                $orderby .= "fx.area_code $sSortDir, fx.prefix $sSortDir, fx.number $sSortDir";
	            } else {
	                $orderby .= "`" . escape_sql_column_name($aColumns[$iSortCol], array('insurance_companies')) . "` $sSortDir";
	            }
	        }
	    }
	}

	if($pagetype == "fax") {
		$typeWhere = "NOT (fx.area_code = '' OR fx.prefix = '' OR fx.number = '') ";
	} else if($pagetype == "postal_letter") {
		$typeWhere = "NOT (a.zip = '') ";
	}

	if(isset($typeWhere)) {
		$typeWhereTmp = "AND " . $typeWhere . " ";
	}

	// Global filtering.
	//
	$tmp_where = "WHERE p.inactive = 0 "  . $typeWhereTmp;
	$where = "";
	if (isset($_GET['sSearch']) && $_GET['sSearch'] !== "") {
	    $sSearch = add_escape_custom(trim($_GET['sSearch']));
	    foreach ($aColumns as $colname) {
	        $where .= $where ? "OR " : " ( ";
	        if ($colname == 'address') {
	        	$where .= "" .
	            "line1 LIKE '$sSearch%' OR " .
	            "line2 LIKE '$sSearch%' ";
	        } else if ($colname == 'city_state') {
	            $where .= "" .
	            "city LIKE '$sSearch%' OR " .
	            "state LIKE '$sSearch%' ";
	        } else if ($colname == 'zip') {
	            $where .= "a.zip LIKE '$sSearch%' ";
	        } else if ($colname == 'phone') {
	            $where .= "CONCAT( ph.area_code,  '-', ph.prefix, '-',  ph.number) LIKE '$sSearch%' ";
	        } else if ($colname == 'fax') {
	            $where .= "CONCAT( fx.area_code,  '-', fx.prefix, '-',  fx.number) LIKE '$sSearch%' ";
	        } else {
	            $where .= "`" . escape_sql_column_name($colname, array('insurance_companies')) . "` LIKE '$sSearch%' ";
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
	        if ($colname == 'address') {
	        	$where .= " ( " .
	            "line1 LIKE '$sSearch%' OR " .
	            "line2 LIKE '$sSearch%' ) ";
	        } else if ($colname == 'city_state') {
	            $where .= " ( " .
	            "city LIKE '$sSearch%' OR " .
	            "state LIKE '$sSearch%' ) ";
	        } else if ($colname == 'zip') {
	            $where .= "a.zip LIKE '$sSearch%' ";
	        } else if ($colname == 'phone') {
	            $where .= " ( CONCAT( ph.area_code,  '-', ph.prefix, '-',  ph.number) LIKE '$sSearch%' ) ";
	        } else if ($colname == 'fax') {
	            $where .= " ( CONCAT( fx.area_code,  '-', fx.prefix, '-',  fx.number) LIKE '$sSearch%' ) ";
	        } else {
	            $where .= " `" . escape_sql_column_name($colname, array('insurance_companies')) . "` LIKE '$sSearch%'";
	        }
	    }
	}

	$where = !empty($where) ? "AND " . $where : "";
	$where = $tmp_where . $where;

	// Get total number of rows in the table.
	//
	$iTotalsqlQtr = "SELECT COUNT(p.id) AS count FROM insurance_companies as p ". 
		 "INNER JOIN addresses as a on p.id = a.foreign_id ".
		 "LEFT JOIN phone_numbers as ph on p.id = ph.foreign_id AND ph.type = 2 ".
		 "LEFT JOIN phone_numbers as fx on p.id = fx.foreign_id AND fx.type = 5 WHERE p.inactive = 0 ";

	$row = sqlQuery($iTotalsqlQtr);
	$iTotal = $row['count'];

	// Get total number of rows in the table after filtering.
	//
	$iFilteredTotalsqlQtr = "SELECT COUNT(p.id) AS count FROM insurance_companies as p ". 
		 "INNER JOIN addresses as a on p.id = a.foreign_id ".
		 "LEFT JOIN phone_numbers as ph on p.id = ph.foreign_id AND ph.type = 2 ".
		 "LEFT JOIN phone_numbers as fx on p.id = fx.foreign_id AND fx.type = 5 ";
	$row = sqlQuery($iFilteredTotalsqlQtr . $where);
	$iFilteredTotal = $row['count'];

	$out = array(
	  "sEcho"                => intval($_GET['sEcho']),
	  "iTotalRecords"        => $iTotal,
	  "iTotalDisplayRecords" => $iFilteredTotal,
	  "aaData"               => array()
	);


	$sellist = "p.id, p.name, a.line1, a.line2, a.city, a.state, a.zip, ph.area_code AS phone_area_code, ph.prefix AS phone_prefix, ph.number AS phone_number, fx.area_code AS fax_area_code, fx.prefix AS fax_prefix, fx.number AS fax_number";
	
	$query = "SELECT $sellist FROM insurance_companies AS p ". 
		 "INNER JOIN addresses as a on p.id = a.foreign_id ".
		 "LEFT JOIN phone_numbers as ph on p.id = ph.foreign_id AND ph.type = 2 ".
		 "LEFT JOIN phone_numbers as fx on p.id = fx.foreign_id AND fx.type = 5 $where $orderby $limit";
	
	$res = sqlStatement($query);
	while ($row = sqlFetchArray($res)) {
		$lastStr = '';
		$nameStr = '';
		if($pagetype == "postal_letter") {
			$pl = PostalLetter::generatePostalAddress(array(
				'street' => $row['line1'],
				'street1' => $row['line2'],
				'city' => $row['city'],
				'state' => $row['state'],
				'postal_code' => $row['zip']
			), "\n");
			$lastStr = base64_encode(json_encode($pl));
			$nameStr = $row['name'];
		} else {
			$lastStr =  attr(trim($row['fax_area_code'] . "-" . $row['fax_prefix'] . "-" . $row['fax_number'],"-"));
			$nameStr = $row['name'];
		}
		$arow = array('DT_RowId' => $row['id'] .'~'. $nameStr .'~'. $lastStr);

		foreach ($aColumns as $colname) {
        	if($colname == 'address') {
        		$arow[] = attr(trim($row['line1'] . ", " . $row['line2'], ", "));
        	} else if($colname == 'city_state') { 
        		$arow[] = attr(trim($row['city'] . " " . $row['state'], ", "));
        	} else if ($colname == 'phone') {
        		$arow[] = attr(trim($row['phone_area_code'] . "-" . $row['phone_prefix'] . "-" . $row['phone_number'],"-"));
	        } else if ($colname == 'fax') {
        		$arow[] = attr(trim($row['fax_area_code'] . "-" . $row['fax_prefix'] . "-" . $row['fax_number'],"-"));
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
	<title><?php echo htmlspecialchars( xl('Insurance Companies Finder'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'fontawesome', 'oemr_ad']); ?>

	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>

    <style type="text/css">
		/*table#insDataTable thead th, table#insDataTable thead td {
			border-bottom: 0px solid black;
		}
		table#insDataTable thead th, table#insDataTable thead td,
		table#insDataTable tr th, table#insDataTable tr td {
			padding: 4px!important;
		}

		table#insDataTable tr td {
			border-top: 1px solid black;
			vertical-align: text-top;
		}
		table#insDataTable tr:hover td {
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
		#insDataTable_wrapper {
			margin-top: 20px;
		}
		#insDataTable_wrapper {
			
		}
		#insDataTable_wrapper input{
			padding: 5px 12px;
		    font-size: 14px;
		    line-height: 1.42857143;
		    color: #555;
		    background-color: #fff;
		    background-image: none;
		    border: 1px solid #444444;
		    border-radius: 4px;
		}
		#insDataTable_filter {
			margin-right: 10px;
			margin-bottom: 20px;
		}*/
		.disclaimersContainer {
			font-size: 14px;
			padding: 15px;
		}
	</style>
    <style type="text/css">
    	.insDataTable {
    		width: 100%!important;
    	}
    </style>
    <script language="JavaScript">

	 function selfax(id, name, data) {
		if (opener.closed || ! opener.setAddressBook)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setInsurancecompanies(id, name, data);
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
		<table id='insDataTable' class='table table-sm addressDataTable'>
		  <thead class="thead-dark">
		    <tr>
		      <th>Name</th>
		      <th width="120">Fax</th>
		      <th>Address</th>
		      <th>City, State</th>
			  <th>Zip</th>
		      <th>Phone</th>
		    </tr>
		  </thead>
		</table>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
		   $('#insDataTable').DataTable({
		      'processing': true,
		      'serverSide': true,
		      'pageLength': 8,
		      'bLengthChange': false,
		      'sAjaxSource': '<?php echo $GLOBALS['webroot']."/interface/main/attachment/find_insurancecompanies_popup.php?pid=". $pid; ?>&ajax=1<?php echo $pageTypeStr; ?>',
		      'columns': [
		         { sName: 'name' },
		         { sName: 'fax' },
		         { sName: 'address' },
		         { sName: 'city_state' },
				 { sName: 'zip' },
		         { sName: 'phone' },
		      ],
		      <?php // Bring in the translations ?>
    			<?php $translationsDatatablesOverride = array('search'=>(xla('Search all columns') . ':')) ; ?>
    		 <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
		   });

		    $("#insDataTable").on('click', 'tbody > tr', function() { SelectIns(this); });

		    var SelectIns = function (eObj) {
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