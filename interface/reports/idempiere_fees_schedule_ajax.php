<?php 

require_once("../globals.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/odbcconf.php");

$params = $columns = $totalRecords = $data = array();
$params = $_REQUEST;

$code1 = (isset($params["code1"]) && !empty($params["code1"])) ? array_map('trim', explode(",", $params["code1"])) : array();

$columns = array(
	0 => 'code1',
	1 => 'description', 
	2 => 'amount_a'
);

//Get Charge Data
function getMWPROData($connection) {
	global $params, $code1;
    $returnData = array();

    //$sql_query = " mwprotb.[Code 1], mwprotb.[Description], mwprotb.[Amount A], mwprotb.[Amount B], mwprotb.[Amount C], mwprotb.[Amount D], mwprotb.[Amount E], mwprotb.[Amount F], mwprotb.[Amount G], mwprotb.[Amount H], mwprotb.[Inactive], mwprotb.[Patient Only Responsible], mwprotb.[Date Created], mwprotb.[User Code], mwprotb.[Date Modified], mwprotb.[Type] FROM MWPRO mwprotb";
    $sql_query = "SELECT mp.value as code_1, mp.description, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='A') as amount_a, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='B') as amount_b, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='C') as amount_c, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='D') as amount_d, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='E') as amount_e, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='F') as amount_f, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='G') as amount_g, (select mm.pricestd from m_pricelist_version ver,m_productprice mm where ver.m_pricelist_version_id = mm.m_pricelist_version_id and mm.m_product_id = mp.m_product_id and ver.description ='H') as amount_h, mp.isactive as inactive, mp.pc_ispatientresponsible as patient_only_responsible, mp.created as date_created, (select adu.name from ad_user adu where mp.updatedby = adu.ad_user_id limit 1) as user_code, mp.updated as date_modified, mp.classification as type from m_product mp ";

    $whereQtr = "WHERE mp.ad_client_id=1000007 ";
    if(isset($code1) && !empty($code1) && count($code1) == 1) {
    	//$whereQtr = " WHERE LOWER(mwprotb.[Code 1]) LIKE '%".strtolower($code1[0])."%' OR  LOWER(mwprotb.[Description]) LIKE '%".strtolower($code1[0])."%' ";
        $whereQtr .= " AND lower(mp.value) like '%".strtolower($code1[0])."%' or lower(mp.description) like '%".strtolower($code1[0])."%'";
    } else if(isset($code1) && !empty($code1) && count($code1) > 1) {
    	//$whereQtr = " WHERE LOWER(mwprotb.[Code 1]) IN("."'".implode("','",strtolower($code1)). "'". ") OR LOWER(mwprotb.[Description]) IN("."'".implode("','",strtolower($code1)). "'". ")";
        $whereQtr .= " AND lower(mp.value) IN("."'".implode("','",strtolower($code1)). "'". ") or lower(mp.description) IN("."'".implode("','",strtolower($code1)). "'". ") ";
    }

    $sqlTot = "SELECT count(*) AS rowcount FROM m_product mp " . $whereQtr;
	$sqlRec = $sql_query . $whereQtr . " LIMIT ".$params['length']." OFFSET ".$params['start'];

	$resultTot = pg_query($connection, $sqlTot);
	$totRows = pg_fetch_object($resultTot);

	$totalRecords  = isset($totRows->rowcount) ? $totRows->rowcount : 0;

    $result = pg_query($connection, $sqlRec);
    while ($rows = pg_fetch_object($result)) {
        $returnData[] = prepareChargeData($rows);
    }

    $json_data = array(
		"draw"            => intval( $params['draw'] ),   
		"recordsTotal"    => intval( $totalRecords ),  
		"recordsFiltered" => intval($totalRecords),
		"data"            => $returnData
	);

    return $json_data;
}

//Prepare Charge Data
function prepareChargeDataOld($rows) {
	return array(
        'code1' => $rows->{'code_1'},
        'description' => $rows->{'description'},
        'amount_a' => $rows->{'amount_a'},
        'amount_b' => $rows->{'amount_b'},
        'amount_c' => $rows->{'amount_c'},
        'amount_d' => $rows->{'amount_d'},
        'amount_e' => $rows->{'amount_e'},
        'amount_f' => $rows->{'amount_f'},
        'amount_g' => $rows->{'amount_g'},
        'amount_h' => $rows->{'amount_h'},
        'inactive' => $rows->{'inactive'},
        'patient_only_responsible' => $rows->{'patient_only_responsible'},
        'date_created' => $rows->{'date_created'},
        'user_code' => $rows->{'user_code'},
        'date_modified' => $rows->{'date_modified'},
        'type' => $rows->{'type'}
    );
}

//Prepare Charge Data
function prepareChargeData($rows) {
	$inActive = $rows->{'inactive'};
	if($rows->{'inactive'} == "Y") {
		$inActive = "False";
	} else if($rows->{'inactive'} == "N") {
		$inActive = "True";
	}

	$patientRes = $rows->{'patient_only_responsible'};
	if($rows->{'patient_only_responsible'} == "N") {
		$patientRes = "False";
	} else if($rows->{'patient_only_responsible'} == "Y") {
		$patientRes = "True";
	}

	return array(
        $rows->{'code_1'},
        $rows->{'description'},
        $rows->{'amount_a'},
        $rows->{'amount_b'},
        $rows->{'amount_c'},
        $rows->{'amount_d'},
        $rows->{'amount_e'},
        $rows->{'amount_f'},
        $rows->{'amount_g'},
        $rows->{'amount_h'},
        $inActive,
        $patientRes,
        $rows->{'date_created'},
        $rows->{'user_code'},
        $rows->{'date_modified'},
    );
    //'type' => $rows->{'type'}
}

if(isset($_REQUEST['page']) && $_REQUEST['page'] == "datatable") {
	$rawData = getMWPROData($idempiere_connection);
	echo json_encode($rawData);
}
?>