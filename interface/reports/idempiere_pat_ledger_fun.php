<?php
require_once('../globals.php');
require_once($GLOBALS['OE_SITE_DIR'] . "/odbcconf.php");

//ColumnList of Charge View
$chargeColsList = array(
    array(
        'name' => 'date_from',
        'title' => 'Date From',
        'width' => '100'
    ),
    array(
        'name' => 'provider',
        'title' => 'Provider',
        'width' => '150'
    ),
    array(
        'name' => 'procedure',
        'title' => 'Procedure',
    ),
    array(
        'name' => 'units',
        'title' => 'Units'
    ),
    array(
        'name' => 'amount',
        'title' => 'Amount'
    ),
    array(
        'name' => 'remainder',
        'title' => 'Remainder'
    ),
    array(
        'name' => 'adjustment_amount',
        'title' => 'Adjustment Amount'
    ),
    array(
        'name' => 'insurance_1_paid',
        'title' => 'Insurance 1 paid'
    ),
    array(
        'name' => 'insurance_2_paid',
        'title' => 'Insurance 2 paid'
    ),
    array(
        'name' => 'insurance_3_paid',
        'title' => 'Insurance 3 paid'
    ),
    array(
        'name' => 'guarantor_amount_paid',
        'title' => 'Guarantor amount paid'
    ),
);

//ColumnList of Charge print View
$printChargeColsList = array(
    array(
        'name' => 'date_from',
        'title' => 'Date From',
        'width' => '120'
    ),
    array(
        'name' => 'provider',
        'title' => 'Provider',
        'width' => '150'
    ),
    array(
        'name' => 'procedure',
        'title' => 'Procedure',
    ),
    array(
        'name' => 'units',
        'title' => 'Units'
    ),
    array(
        'name' => 'amount',
        'title' => 'Amt'
    ),
    array(
        'name' => 'remainder',
        'title' => 'Remainder'
    ),
    array(
        'name' => 'adjustment_amount',
        'title' => 'Adjustment Amt'
    ),
    array(
        'name' => 'insurance_1_paid',
        'title' => 'Ins 1 pd'
    ),
    array(
        'name' => 'insurance_2_paid',
        'title' => 'Ins 2 pd'
    ),
    array(
        'name' => 'insurance_3_paid',
        'title' => 'Ins 3 pd'
    ),
    array(
        'name' => 'guarantor_amount_paid',
        'title' => 'Guarantor Amt paid'
    ),
);

//ColumnList of Payment View
$paymentColsList = array(
    array(
        'name' => 'date_from',
        'title' => 'Date From',
        'width' => '100'
    ),
    // array(
    //     'name' => 'description',
    //     'title' => 'Description',
    //     'width' => '200'
    // ),
    array(
        'name' => 'provider',
        'title' => 'Provider'
    ),
    array(
        'name' => 'payment_adjustment_code',
        'title' => 'Payment/Adjustment Code'
    ),
    array(
        'name' => 'payment_amount',
        'title' => 'Payment Amount'
    ),
    array(
        'name' => 'deposit_description',
        'title' => 'Deposit Description'
    ),
    array(
        'name' => 'full_deposit_amount',
        'title' => 'Full Deposit Amount'
    ),
    array(
        'name' => 'payor_name',
        'title' => 'Payor Name'
    )
);

$chargeColsList1 = array(
    array(
        'name' => 'date_from',
        'title' => 'Date From',
        'width' => '100'
    ),
    array(
        'name' => 'provider',
        'title' => 'Provider',
        'width' => '150'
    ),
    array(
        'name' => 'procedure',
        'title' => 'Procedure',
    ),
    array(
        'name' => 'units',
        'title' => 'Units'
    ),
    array(
        'name' => 'amount',
        'title' => 'Amount'
    ),
    array(
        'name' => 'remainder',
        'title' => 'Rmdr'
    ),
    array(
        'name' => 'adjustment_amount',
        'title' => 'Adj Amt'
    ),
    array(
        'name' => 'insurance_1_paid',
        'title' => 'Ins 1 pd'
    ),
    array(
        'name' => 'insurance_2_paid',
        'title' => 'Ins 2 pd'
    ),
    array(
        'name' => 'insurance_3_paid',
        'title' => 'Ins 3 pd'
    ),
    array(
        'name' => 'takeback',
        'title' => 'TakeBack'
    ),
    array(
        'name' => 'guarantor_amount_paid',
        'title' => 'Grnt Pd'
    ),
    array(
        'name' => 'withhold',
        'title' => 'wh Amt'
    ),
    array(
        'name' => 'deductamt',
        'title' => 'ded Amt'
    ),
    array(
        'name' => 'coins',
        'title' => 'Coins'
    ),
    array(
        'name' => 'copay',
        'title' => 'Copay'
    )
);

//ColumnList of Payment View
$paymentColsList1 = array(
    array(
        'name' => 'date_from',
        'title' => 'Date From',
        'width' => '100'
    ),
    // array(
    //     'name' => 'description',
    //     'title' => 'Description',
    //     'width' => '200'
    // ),
    array(
        'name' => 'provider',
        'title' => 'Provider'
    ),
    array(
        'name' => 'payment_adjustment_code',
        'title' => 'Payment/Adjustment Code'
    ),
    array(
        'name' => 'payment_amount',
        'title' => 'Payment Amount'
    ),
    array(
        'name' => 'full_deposit_amount',
        'title' => 'Full Deposit Amount'
    ),
    array(
        'name' => 'payor_name',
        'title' => 'Payor Name'
    ),
    array(
        'name' => 'adjustment_amount',
        'title' => 'Adj Amt'
    ),
    array(
        'name' => 'takeback',
        'title' => 'TakeBack'
    ),
    array(
        'name' => 'withhold',
        'title' => 'wh Amt'
    ),
    array(
        'name' => 'deductamt',
        'title' => 'ded Amt'
    ),
    array(
        'name' => 'coins',
        'title' => 'Coins'
    ),
    array(
        'name' => 'copay',
        'title' => 'Copay'
    )
);

//Check access right
function checkUserAccessControle() {
    $userList = array("BBoecking", "GSontakke");
    $user = $_SESSION['authUser'];

    if (in_array($user, $userList)) {
        return true;
    } else {
        die("You don't have permission to access this page.");
    }
}

//Find Highest Case Number from the list
function getHighestCaseNumber($caseList, $form_extra_case_filter) {
    $tempList = array();
    
    if(isset($form_extra_case_filter)) {
        return $form_extra_case_filter;
    } else {
        if(is_array($caseList)) {
            foreach ($caseList as $key => $list) {
                $tempList[] = $list['value'];
            }
            return max($tempList);
        }
    }
    return null;
}

//Get Chart Number by using pid
function getChartNumber($pid) {
    $patient = sqlQuery("SELECT * from patient_data WHERE pid=?", array($pid));
    
    if($patient && $patient['pubpid']) {
        if(strlen($patient['pubpid']) == 5) {
            return $patient['pubpid'];
        } else {
            return $patient['pubpid'];
        }
    }
}

//Get Case number list by chartNumber
function getCaseDropdown($connection, $chartNumber) {
    $caseNumbers = array();
    if($chartNumber) {
        //$sql = "SELECT [Chart Number], [Case Number], [Level of Subluxation], [Description] FROM MWCAS WHERE [Chart Number] = '".$chartNumber."'";
        $sql = "SELECT bp.value as chart_number, xm.value as case_number, xm.pc_oemrcaseno, xm.vh_isprintpatientstatement, xm.description,xm.x_mwcase_id ,bp.c_bpartner_id  from c_bpartner bp, x_mwcase xm where bp.c_bpartner_id = xm.c_bpartner_id and bp.value = '".$chartNumber."' and xm.ad_client_id = 1000007 order by xm.created desc";
        $result = pg_query($connection, $sql);
        while ($rows = pg_fetch_object($result)) {
            if($rows->{'case_number'} && !empty($rows->{'case_number'}) && $rows->{'case_number'} != '') {
                $caseNumbers[] = array(
                    'name' => $rows->{'description'} && $rows->{'description'} != null ? $rows->{'pc_oemrcaseno'}.' - '.$rows->{'description'} : $rows->{'pc_oemrcaseno'},
                    'value' => $rows->{'case_number'}
                );
            }
        }
    }

    return $caseNumbers;
}

//Get Row Details of Charge Data For Print
function getChargeDataForPrint($connection, $entryNumbers) {
    $chargeData = array();
    // $sql = "SELECT mwpaxtb.[Payment Reference], mwpaxtb.[Charge Reference], mwtrntb.[Chart Number], mwtrntb.[Case Number], mwtrntb.[Entry Number], mwtrntb.[Description], mwtrntb.[Documentation], mwtrntb.[Date From], mwtrntb.[Attending Provider], mwtrntb.[Procedure Code], mwtrntb.[Units], mwtrntb.[Amount], mwtrntb.[Adjustment Amount], mwtrntb.[Insurance 1 Amount Paid], mwtrntb.[Insurance 2 Amount Paid], mwtrntb.[Insurance 3 Amount Paid], mwtrntb.[Guarantor Amount Paid], mwtrntb.[Insurance 1 Paid], mwtrntb.[Insurance 2 Paid], mwtrntb.[Insurance 3 Paid], mwphytb.[First Name], mwphytb.[Last Name], mwphytb.[Credentials], mwtrntb.[Entry Number], mwpro.[Description] as ProcDescription  
    // FROM MWPAX mwpaxtb 
    // LEFT JOIN MWTRN mwtrntb ON mwtrntb.[Entry Number] = mwpaxtb.[Charge Reference] 
    // LEFT JOIN MWPHY mwphytb ON mwphytb.[Code] = mwtrntb.[Attending Provider] 
    // LEFT JOIN MWPRO mwpro ON mwtrntb.[Procedure Code] = mwpro.[Code 1] 
    // WHERE mwpaxtb.[Payment Reference] IN(".implode(",",$entryNumbers).") ORDER BY mwtrntb.[Date From] DESC";
    $sql = "SELECT ca.c_payment_id as payment_reference, pat.value as chart_number, cc.value as case_number, ci.documentno as entry_number, vh_invoicelineopentodate(ci.c_invoiceline_id) as remainder, ci.description as documentation, ca.description  as description, ci.dateinvoiced as date_from, CONCAT(cb.name, ' - ', cb.pc_credentials) as attending_provider, prod.value as procedure_code, ci.qtyinvoiced as units, ci.linenetamt as amount, ca.writeoffamt *-1 as adjustment_amount, vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id, ci.c_currency_id,1)*-1 as insurance_1_amount_paid,vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id ,ci.c_currency_id,2)*-1 as insurance_2_amount_paid,vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id,ci.c_currency_id ,3)*-1 as insurance_3_amount_paid,vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id ,ci.c_currency_id,4)*-1 as guarantor_amount_paid,case when coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id = ci.c_invoiceline_id and ppc.line = 1 limit 1), '') in ('Y', '') then 'true' else 'false' end as insurance_1_paid, case when coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id = ci.c_invoiceline_id and ppc.line = 2 limit 1), '') in ('Y', '') then 'true' else 'false' end as insurance_2_paid, case when coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id = ci.c_invoiceline_id and ppc.line = 3 limit 1), '') in ('Y', '') then 'true' else 'false' end as insurance_3_paid, cb.pc_firstname as firstname, cb.pc_lastname as lastname,cb.pc_credentials as credentials,prod.description as proc_description,ci.c_invoiceline_id,ca.pc_takeback*-1 as takebackamount,ca.pc_withhold*-1 as withhold,ca.pc_deductamt*-1 as deductamt,ca.pc_coinsamt*-1 as coins,ca.pc_copayamt *-1 as copay from c_allocationline ca left outer join vh_invoiceline_v ci on ca.c_invoiceline_id = ci.c_invoiceline_id left join c_bpartner cb on ci.procare_provider_id = cb.c_bpartner_id and cb.isprocareprovider = 'Y' left join M_Product prod on ci.m_product_id = prod.m_product_id left outer join c_bpartner pat on ci.c_bpartner_id = pat.c_bpartner_id left outer join x_mwcase cc on ci.x_mwcase_id = cc.x_mwcase_id where ci.ad_client_id = 1000007 and ca.c_payment_id IN(".implode(",",$entryNumbers).") order by ci.dateinvoiced desc";

    $result = pg_query($connection, $sql);
    while ($row = pg_fetch_object($result)) {
        $chargeData[$row->{'payment_reference'}][] =  prepareChargeData($row, 'print');
    }

    return $chargeData;
}

//Get Row Details of Payment Data For Print
function getPaymentDataForPrint($connection, $entryNumbers) {
    $paymentData = array();
    // $sql = "SELECT mwpaxtb.[Payment Reference], mwpaxtb.[Charge Reference], mwtrntb.[Chart Number], mwtrntb.[Case Number], mwtrntb.[Description], mwtrntb.[Date From], mwtrntb.[Attending Provider], mwtrntb.[Procedure Code], mwtrntb.[Amount], mwdeptb.[Description] AS dep_Description, mwdeptb.[Payment Amount], mwdeptb.[Payor Name], mwphytb.[First Name], mwphytb.[Last Name], mwphytb.[Credentials], mwtrntb.[Entry Number] 
    // FROM MWPAX mwpaxtb 
    // LEFT JOIN MWTRN mwtrntb ON mwtrntb.[Entry Number] = mwpaxtb.[Payment Reference]
    // LEFT JOIN MWPHY mwphytb ON mwphytb.[Code] = mwtrntb.[Attending Provider] 
    // LEFT JOIN MWDEP mwdeptb ON mwdeptb.[Entry Number] = mwtrntb.[Deposit ID]
    // WHERE mwpaxtb.[Charge Reference] IN(".implode(",",$entryNumbers).") ORDER BY mwtrntb.[Date From] DESC";
    $sql = "SELECT ca.c_invoiceline_id as charge_reference, '' as chart_number, '' as case_number, ca.description as description, ca.datetrx as date_from, '' as attending_provider, '' as procedure_code, ca.amount as amount, ca.description as dep_description, cp.payamt as payment_amount, bp.name as payer_name, prov.pc_firstname as first_name, prov.pc_lastname as last_name, prov.pc_credentials as credentials, case when ca.amount != 0 then 'Payment Amount' when ca.writeoffamt != 0 then 'Adjustment' when ca.pc_takeback != 0 then 'Take Back' else '' end as PaymentADjustmentCode, ca.writeoffamt as adj_amt, ca.pc_takeback*-1 as takebackamount, ca.pc_withhold*-1 as withhold, ca.pc_deductamt*-1 as deductamt, ca.pc_coinsamt*-1 as coins, ca.pc_copayamt *-1 as copay from c_allocationline ca left outer join c_payment cp on ca.c_payment_id = cp.c_payment_id left outer join c_doctype cd on cp.c_doctype_id = cd.c_doctype_id left outer join c_bpartner bp on cp.c_bpartner_id = bp.c_bpartner_id , vh_invoiceline_v viv, c_bpartner prov where ca.c_invoiceline_id = viv.c_invoiceline_id and viv.procare_provider_id = prov.c_bpartner_id and ca.c_invoiceline_id IN(".implode(",",$entryNumbers).") order by ca.datetrx desc";

    $result = pg_query($connection, $sql);
    while ($row = pg_fetch_object($result)) {
        $paymentData[$row->{'charge_reference'}][] =  preparePaymentData($row);
    }

    return $paymentData;
}

//Get Payment Reference by using entryNumber of Charge data.
// function getPaymentReference($connection, $entryNumber) {
//     $paymentReference = array();
//     $sql = "SELECT mwpaxtb.[Payment Reference] FROM MWPAX mwpaxtb WHERE mwpaxtb.[Charge Reference] = ".$entryNumber."";
//     $result = pg_query($connection, $sql);

//     while ($rows = pg_fetch_object($result)) {
//         $paymentReference[] = $rows->{'Payment Reference'};
//     }

//     return $paymentReference;
// }

//Get Charge Reference by using entryNumber of Payment data.
// function getChargesReference($connection, $entryNumber) {
//     $chargeReference = array();
//     $sql = "SELECT mwpaxtb.[Charge Reference] FROM MWPAX mwpaxtb WHERE mwpaxtb.[Payment Reference] = ".$entryNumber."";
//     $result = pg_query($connection, $sql);

//     while ($rows = pg_fetch_object($result)) {
//         $chargeReference[] = $rows->{'Charge Reference'};
//     }

//     return $chargeReference;
// }

//Get Charge Data
function getChargesData($connection, $entryNumber = false, $chartNumber = '', $caseNumber = '', $dateFrom = '', $dateTo = '', $type = '') {
    $returnData = array();
    $where = "";
    
    if($entryNumber != false && !empty($entryNumber)) {
        if(is_array($entryNumber)) {
            //$where = "mwtrntb.[Entry Number] IN(".implode(",",$entryNumber).")";
            $where = "ci.documentno IN('".implode("','",$entryNumber)."')";
        } else {
            //$where = "mwtrntb.[Entry Number] = ".trim($entryNumber)."";
            $where = "ci.documentno = '".trim($entryNumber)."'";
        }
    } else {
        //$where = "mwtrntb.[Transaction Type] IN('A','B')";
        $where = "";

        if($chartNumber) {
            //$where .= " AND mwtrntb.[Chart Number] = '".trim($chartNumber)."'";
            $where .= " AND pat.value = '".trim($chartNumber)."'";
        }

        if($caseNumber && $caseNumber != '') {
            //$where .= " AND mwtrntb.[Case Number] = ".trim($caseNumber)."";
            $where .= " AND cc.value = '".trim($caseNumber)."'";
        }

        if($dateFrom) {
            //$tmpDateFrom = date("m/d/Y", strtotime($dateFrom));
            //$where .= " AND mwtrntb.[Date From] >= '". $tmpDateFrom."'";
            $tmpDateFrom = date("Y-m-d", strtotime($dateFrom));
            $tmpDateTo = date("Y-m-d", strtotime($dateTo));
            $where .= " AND DATE(ci.dateinvoiced) between '". $tmpDateFrom."' and '". $tmpDateTo."' ";
        }

        if($dateTo) {
            // $tmpDateTo = date("m/d/Y", strtotime($dateTo));
            // $where .= " AND mwtrntb.[Date To] <= '". $tmpDateTo."'";
        }
    }

    // $sql = "SELECT mwtrntb.[Chart Number], mwtrntb.[Case Number], mwtrntb.[Entry Number], mwtrntb.[Description], mwtrntb.[Documentation], mwtrntb.[Date From], mwtrntb.[Attending Provider], mwtrntb.[Procedure Code], mwtrntb.[Units], mwtrntb.[Amount], mwtrntb.[Adjustment Amount], mwtrntb.[Insurance 1 Amount Paid], mwtrntb.[Insurance 2 Amount Paid], mwtrntb.[Insurance 3 Amount Paid], mwtrntb.[Guarantor Amount Paid], mwtrntb.[Insurance 1 Paid], mwtrntb.[Insurance 2 Paid], mwtrntb.[Insurance 3 Paid], mwphytb.[First Name], mwphytb.[Last Name], mwphytb.[Credentials], mwtrntb.[Entry Number], mwpro.[Description] as ProcDescription
    //         FROM MWTRN mwtrntb
    //         LEFT JOIN MWPHY mwphytb ON mwphytb.[Code] = mwtrntb.[Attending Provider] 
    //         LEFT JOIN MWPRO mwpro ON mwtrntb.[Procedure Code] = mwpro.[Code 1] 
    //         WHERE ".$where." 
    //         ORDER BY mwtrntb.[Date From] DESC";

    $sql = "SELECT pat.value as chart_number, cc.value as case_number, ci.documentno as entry_number1, ci.description as documentation, ci.description as description, ci.dateinvoiced as date_from, CONCAT(cb.name, ' - ',cb.pc_credentials) as attending_provider, prod.value as procedure_code, ci.qtyinvoiced as units, ci.linenetamt as amount, vh_chargeadjustmentamt(ci.c_invoiceline_id)*-1 as adjustment_amount, vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id,ci.c_currency_id ,1)*-1 as insurance_1_amount_paid, vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id ,ci.c_currency_id,2)*-1 as insurance_2_amount_paid, vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id,ci.c_currency_id ,3)*-1 as insurance_3_amount_paid, vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id ,ci.c_currency_id,4)*-1 as guarantor_amount_paid, CASE WHEN coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id =ci.c_invoiceline_id and ppc.line=1 limit 1), '') IN ('Y', '') THEN 'true' ELSE 'false' END AS insurance_1_paid, CASE WHEN coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id =ci.c_invoiceline_id and ppc.line=2 limit 1), '') IN ('Y', '') THEN 'true' ELSE 'false' END AS insurance_2_paid, CASE WHEN coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id =ci.c_invoiceline_id and ppc.line=3 limit 1), '') IN ('Y', '') THEN 'true' ELSE 'false' END AS insurance_3_paid, cb.pc_firstname as firstname, cb.pc_lastname as lastname, cb.pc_credentials as credentials, prod.description as proc_description,ci.c_invoiceline_id as entry_number, vh_invoicelineopentodate(ci.c_invoiceline_id) as remainder from vh_invoiceline_v ci  left join c_bpartner cb on ci.procare_provider_id  = cb.c_bpartner_id and cb.isprocareprovider ='Y' left join M_Product prod on ci.m_product_id  = prod.m_product_id,c_bpartner pat,x_mwcase cc where ci.c_bpartner_id = pat.c_bpartner_id and ci.x_mwcase_id = cc.x_mwcase_id and ci.ad_client_id =1000007 and ci.DocStatus='CO' ".$where." order by ci.dateinvoiced desc";

    $result = pg_query($connection, $sql);

    while ($rows = pg_fetch_object($result)) {
        $returnData[] = prepareChargeData($rows, $type);
    }

    return $returnData;
}

//Get Payment Data
function getPaymentData($connection, $entryNumber = false, $chartNumber = '', $caseNumber = '', $dateFrom = '', $dateTo = '') {
    $returnData = array();
    
    if($entryNumber != false && !empty($entryNumber)) {
        if(is_array($entryNumber)) {
            //$where = "mwtrntb.[Entry Number] IN(".implode(",",$entryNumber).")";
            $where = "cp.documentno IN('".implode("','",$entryNumber)."')";
        } else {
            //$where = "mwtrntb.[Entry Number] = ".trim($entryNumber)."";
            $where = "cp.documentno = '".trim($entryNumber)."'";
        }
    } else {
            //$where = "mwtrntb.[Transaction Type] IN('I','M','N','O')";
            $where = "";

            if($chartNumber) {
                $where .= " AND pat.value = '".trim($chartNumber)."'";
            }

            if($caseNumber && $caseNumber != '') {
                $where .= " AND cc.value = '".trim($caseNumber)."'";
            }

            if($dateFrom) {
                //$tmpDateFrom = date("m/d/Y", strtotime($dateFrom));
                //$where .= " AND mwtrntb.[Date From] >= '". $tmpDateFrom."'";
                $tmpDateFrom = date("Y-m-d", strtotime($dateFrom));
                $tmpDateTo = date("Y-m-d", strtotime($dateTo));
                $where .= " AND DATE(cp.dateacct) between '". $tmpDateFrom."' and '". $tmpDateTo."' ";
            }

            // if($dateTo) {
            //     $tmpDateTo = date("m/d/Y", strtotime($dateTo));
            //     $where .= " AND mwtrntb.[Date To] <= '". $tmpDateTo."'";
            // }
        }

    // $sql = "SELECT mwtrntb.[Chart Number], mwtrntb.[Case Number], mwtrntb.[Description], mwtrntb.[Date From], mwtrntb.[Attending Provider], mwtrntb.[Procedure Code], mwtrntb.[Amount], mwdeptb.[Description] AS dep_Description, mwdeptb.[Payment Amount], mwdeptb.[Payor Name], mwphytb.[First Name], mwphytb.[Last Name], mwphytb.[Credentials], mwtrntb.[Entry Number] FROM MWTRN mwtrntb LEFT JOIN MWPHY mwphytb ON mwphytb.[Code] = mwtrntb.[Attending Provider] LEFT JOIN MWDEP mwdeptb ON mwdeptb.[Entry Number] = mwtrntb.[Deposit ID] WHERE ".$where." ORDER BY mwtrntb.[Date From] DESC";
    $sql = "SELECT pat.value as chart_number, cc.value as case_number, cp.description as description, cp.dateacct as date_from, pat.name as attending_provider,(select arl.name  from ad_ref_list arl where arl.AD_Reference_ID=214 and arl.value like cp.tendertype) as procedure_code, cp.payamt  as amount, cp.description as dep_description, cp.payamt as payment_amount, pat.name as payer_name, prov.pc_firstname  as first_name, prov.pc_lastname as last_name, prov.pc_credentials as credentials, cp.documentno as entry_number1, cp.c_payment_id as entry_number ,cd.name as PaymentADjustmentCode from c_payment cp left outer join c_bpartner pat on cp.c_bpartner_id = pat.c_bpartner_id left outer join x_mwcase cc on cp.x_mwcase_id = cc.x_mwcase_id left outer join c_bpartner prov on cc.procare_provider_id = prov.c_bpartner_id , c_doctype cd where cp.c_doctype_id = cd.c_doctype_id and cp.ad_client_id = 1000007 and cp.DocStatus='CO' ".$where." order by cp.dateacct desc";

    $result = pg_query($connection, $sql);

    while ($rows = pg_fetch_object($result)) {
        $returnData[] = preparePaymentData($rows);
    }

    return $returnData;
}

function getChargeRowDetails($connection, $entryNumber = false) {
    $returnData = array();

    if($entryNumber != false && !empty($entryNumber)) {
        $sql = "SELECT pat.value as chart_number, cc.value as case_number, ci.documentno as entry_number, ci.description as documentation, ca.description  as description, ci.dateinvoiced as date_from, CONCAT(cb.name, ' - ', cb.pc_credentials) as attending_provider, prod.value as procedure_code, ci.qtyinvoiced as units, ci.linenetamt as amount, vh_chargeadjustmentamt(ci.c_invoiceline_id)-ca.pc_takeback-ca.pc_withhold) as adjustment_amount, vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id, ci.c_currency_id,1)*-1 as insurance_1_amount_paid,vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id ,ci.c_currency_id,2)*-1 as insurance_2_amount_paid,vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id,ci.c_currency_id ,3)*-1 as insurance_3_amount_paid,vh_invoicelinepaidbyinsurance(ci.c_invoiceline_id ,ci.c_currency_id,4)*-1 as guarantor_amount_paid,case when coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id = ci.c_invoiceline_id and ppc.line = 1), '') in ('Y', '') then 'true' else 'false' end as insurance_1_paid, case when coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id = ci.c_invoiceline_id and ppc.line = 2), '') in ('Y', '') then 'true' else 'false' end as insurance_2_paid, case when coalesce((select iscomplete from pc_payerdet_charge ppc where ppc.c_invoiceline_id = ci.c_invoiceline_id and ppc.line = 3), '') in ('Y', '') then 'true' else 'false' end as insurance_3_paid, cb.pc_firstname as firstname, cb.pc_lastname as lastname,cb.pc_credentials as credentials,prod.description as proc_description,ci.c_invoiceline_id,ca.pc_takeback as takebackamount,ca.pc_withhold*-1 as withhold,ca.pc_deductamt*-1 as deductamt,ca.pc_coinsamt*-1 as coins,ca.pc_copayamt *-1 as copay from c_allocationline ca left outer join vh_invoiceline_v ci on ca.c_invoiceline_id = ci.c_invoiceline_id left join c_bpartner cb on ci.procare_provider_id = cb.c_bpartner_id and cb.isprocareprovider = 'Y' left join M_Product prod on ci.m_product_id = prod.m_product_id left outer join c_bpartner pat on ci.c_bpartner_id = pat.c_bpartner_id left outer join x_mwcase cc on ci.x_mwcase_id = cc.x_mwcase_id where ci.ad_client_id = 1000007 and ca.c_payment_id = ".$entryNumber." order by ci.dateinvoiced desc ";

        $result = pg_query($connection, $sql);

        while ($rows = pg_fetch_object($result)) {
            $returnData[] = prepareChargeData($rows);
        }
    }

    return $returnData;
}

function getPaymentRowDetails($connection, $entryNumber = false) {
    $returnData = array();

    if($entryNumber != false && !empty($entryNumber)) {
        $sql = "SELECT '' as chart_number, '' as case_number, ca.description as description, ca.datetrx as date_from, '' as attending_provider, '' as procedure_code, ca.amount as amount, ca.description as dep_description, cp.payamt as payment_amount, bp.name as payer_name, prov.pc_firstname as first_name, prov.pc_lastname as last_name, prov.pc_credentials as credentials, case when ca.amount != 0 then 'Payment Amount' when ca.writeoffamt != 0 then 'Adjustment' when ca.pc_takeback != 0 then 'Take Back' else '' end as paymentADjustmentCode, ca.writeoffamt as adj_amt, ca.pc_takeback as takebackamount, ca.pc_withhold*-1 as withhold, ca.pc_deductamt*-1 as deductamt, ca.pc_coinsamt*-1 as coins, ca.pc_copayamt *-1 as copay from c_allocationline ca left outer join c_payment cp on ca.c_payment_id = cp.c_payment_id left outer join c_doctype cd on cp.c_doctype_id = cd.c_doctype_id left outer join c_bpartner bp on cp.c_bpartner_id = bp.c_bpartner_id , vh_invoiceline_v viv, c_bpartner prov where ca.c_invoiceline_id = viv.c_invoiceline_id and viv.procare_provider_id = prov.c_bpartner_id and ca.c_invoiceline_id = " . $entryNumber;
    
        $result = pg_query($connection, $sql);

        while ($rows = pg_fetch_object($result)) {
            $returnData[] = preparePaymentData($rows);
        }
    }

    return $returnData;
}

//Prepare Charge Data
function prepareChargeData($rows, $type = '') {
    $provider = $rows->{'firstname'}.' '.$rows->{'lastname'}.' - '.$rows->{'credentials'};
    $checkStatus = (($rows->{'insurance_1_paid'} == "true" && $rows->{'insurance_2_paid'} == "true" && $rows->{'insurance_3_paid'} == "true") || trim($rows->{'procedure_code'}) == "PAY") ? 'green' : 'red';
    $checkProcedureStatus = (trim($rows->{'procedure_code'}) == "PAY") ? 'green' : '';
    $insurance1Paid = $rows->{'insurance_1_paid'} == "true" ? 'green' : '';
    $insurance2Paid = $rows->{'insurance_2_paid'} == "true" ? 'green' : '';
    $insurance3Paid = $rows->{'insurance_3_paid'} == "true" ? 'green' : '';
    //$remainderText = number_format((array_sum(array($rows->{'amount'},$rows->{'adjustment_amount'},$rows->{'insurance_1_amount_paid'},$rows->{'insurance_2_amount_paid'},$rows->{'insurance_3_amount_paid'},$rows->{'guarantor_amount_paid'}))), 2, '.', ',');
    $remainderText = number_format($rows->{'remainder'}, 2, '.', ',');
    $checkIsNoneZero = $remainderText != 0 ? $checkStatus : "";

    $procdescription = "";
    if($type == "print" && !empty($rows->{'proc_description'})) {
        $procdescription = " - " . $rows->{'proc_description'};
    }

    return array(
        'description' => $rows->{'description'},
        'date_from' => !empty($rows->{'date_from'}) ? date("m-d-Y", strtotime($rows->{'date_from'})) : "",
        'provider' => $provider,
        'procedure' => '<span data-toggle="tooltip" title="'.$rows->{'proc_description'}.'">'.trim($rows->{'procedure_code'}).$procdescription.'</span>',
        'procdescription' => $rows->{'proc_description'},
        'units' => $rows->{'Units'},
        'documentation' =>  '<div data-toggle="tooltip" class="documentationText" title="'.$rows->{'documentation'}.'">'.$rows->{'documentation'}.'</div>',
        'amount' => number_format(($rows->{'amount'}), 2, '.', ','),
        'remainder' => '<span class="remainderText '.$checkIsNoneZero.'" data-toggle="tooltip" title="'.$rows->{'documentation'}.'">'.$remainderText.'</span>',
        'adjustment_amount' => number_format(($rows->{'adjustment_amount'}), 2, '.', ','),
        'insurance_1_paid' => '<span class="'.$insurance1Paid.'">'.number_format(($rows->{'insurance_1_amount_paid'}), 2, '.', ',').'</span>',
        'insurance_2_paid' => '<span class="'.$insurance2Paid.'">'.number_format(($rows->{'insurance_2_amount_paid'}), 2, '.', ',').'</span>',
        'insurance_3_paid' => '<span class="'.$insurance3Paid.'">'.number_format(($rows->{'insurance_3_amount_paid'}), 2, '.', ',').'</span>',
        'guarantor_amount_paid' => number_format(($rows->{'guarantor_amount_paid'}), 2, '.', ','),
        'data' => $rows->{'entry_number'},
        'takeback' => isset($rows->{'takebackamount'}) ? number_format(($rows->{'takebackamount'}), 2, '.', ',') : '',
        'withhold' => isset($rows->{'withhold'}) ? number_format(($rows->{'withhold'}), 2, '.', ',') : '',
        'deductamt' => isset($rows->{'deductamt'}) ? number_format(($rows->{'deductamt'}), 2, '.', ',') : '',
        'coins' => isset($rows->{'coins'}) ? number_format(($rows->{'coins'}), 2, '.', ',') : '',
        'copay' => isset($rows->{'copay'}) ? number_format(($rows->{'copay'}), 2, '.', ',') : ''
    );
}

//Prepare Payment Data
function preparePaymentData($rows) {
    $provider = $rows->{'first_name'}.' '.$rows->{'last_name'}.' - '.$rows->{'credentials'};
    return array(
        'description' => $rows->{'description'},
        'date_from' => !empty($rows->{'date_from'}) ? date("m-d-Y", strtotime($rows->{'date_from'})) : "",
        'provider' => $provider,
        'payment_adjustment_code' => $rows->{'procedure_code'},
        'payment_description' => $rows->{'description'},
        'payment_amount' => number_format(($rows->{'amount'}), 2, '.', ','),
        'deposit_description' => $rows->{'dep_description'},
        'full_deposit_amount' => number_format(($rows->{'payment_amount'}), 2, '.', ','),
        'payor_name' => $rows->{'payer_name'},
        'data' => $rows->{'entry_number'},
        'adjustment_amount' => isset($rows->{'adj_amt'}) ? number_format(($rows->{'adj_amt'}), 2, '.', ',') : '',
        'takeback' => isset($rows->{'takebackamount'}) ? number_format(($rows->{'takebackamount'}), 2, '.', ',') : '',
        'withhold' => isset($rows->{'withhold'}) ? number_format(($rows->{'withhold'}), 2, '.', ',') : '',
        'deductamt' => isset($rows->{'deductamt'}) ? number_format(($rows->{'deductamt'}), 2, '.', ',') : '',
        'coins' => isset($rows->{'coins'}) ? number_format(($rows->{'coins'}), 2, '.', ',') : '',
        'copay' => isset($rows->{'copay'}) ? number_format(($rows->{'copay'}), 2, '.', ',') : ''
    );
}

// Genererate or Prepare HTML string for rowdetails view
function prepareChildHTML($form_extra_payment_filter, $rowData, $paymentColsList, $chargeColsList) {
    $childrowHTML = '';
    $childColsList = "";

    if($form_extra_payment_filter == "charge") {
        $childColsList = $chargeColsList;
        $childTitle = "Charge";
    } else if($form_extra_payment_filter == "payment") {
        $childColsList = $paymentColsList;
        $childTitle = "Payment";
    }

    if($childColsList && $childColsList != "") {
        $childrowHTML .= '<div class="subViewTitle"><h4>'.$childTitle.'</h3></div>';
        $childrowHTML .= '<table class="text table table-sm childTable" cellspacing="0" width="100%">';
        $childrowHTML .= '<thead class="thead-light"><tr>';
            foreach ($childColsList as $key => $col) {
                $childrowHTML .= '<th>'.$col['title'].'</th>';
            }
        $childrowHTML .= '</tr></thead>';
        $childrowHTML .= '<tbody>';
        if(!empty($rowData)) {
            foreach ($rowData as $key => $row) {
                $childrowHTML .= '<tr>';
                    foreach ($childColsList as $key => $col) {
                        $childrowHTML .= '<td>'.$row[$col['name']].'</td>';
                    }
                $childrowHTML .= '</tr>';
            }
        } else {
            $childrowHTML .= '<tr><td align="center" colspan="'.count($childColsList).'"><div class="emptyRow">No records found</div></td></tr>';
        }
        $childrowHTML .= '</tbody>';
        $childrowHTML .= '</table>';
    }

    return $childrowHTML;
}

//Get & Calculate Balances (Overall Balance, Case Balance, Patient Responsibility)
function calculateBalance($connection, $chartNumber, $caseNumber) {
    // $sql = "SELECT mwtrntb.[Case Number], mwtrntb.[Amount], mwtrntb.[Adjustment Amount], mwtrntb.[Procedure Code], mwtrntb.[Insurance 1 Amount Paid], mwtrntb.[Insurance 2 Amount Paid], mwtrntb.[Insurance 3 Amount Paid], mwtrntb.[Guarantor Amount Paid], mwtrntb.[Insurance 1 Paid], mwtrntb.[Insurance 2 Paid], mwtrntb.[Insurance 3 Paid] FROM MWTRN mwtrntb WHERE mwtrntb.[Chart Number] = '".trim($chartNumber)."' AND mwtrntb.[Transaction Type] IN('A','B') ";
    
    $sql = "SELECT bp.c_bpartner_id param1, xm.x_mwcase_id param2, xm.value as case_id from c_bpartner bp, x_mwcase xm where bp.c_bpartner_id = xm.c_bpartner_id and bp.value = '".trim($chartNumber)."' and xm.ad_client_id = 1000007";

    $mainData = array();
    $mainResult = pg_query($connection, $sql);

    while($rows = pg_fetch_object($mainResult)) {
        $mainData[] = $rows;
    }

    $overallBalance = 0;
    $caseBalance = 0;
    $patientResponsibility = 0;
    $caseBilled = 0;
    $casePaidAmtTotal = 0;
    $caseAdjAmt = 0;
    $caseUnAllocatedAmt = 0;
    $overAllUnAllocatedAmt = 0;

    foreach ($mainData as $mk => $mItem) {
        $param1 = isset($mItem->{'param1'}) ? $mItem->{'param1'} : '';
        $param2 = isset($mItem->{'param2'}) ? $mItem->{'param2'} : '';
        $case_id = isset($mItem->{'case_id'}) ? $mItem->{'case_id'} : '';

        $sqlResult1 = pg_query($connection, "select coalesce(vh_openbalanceofpatient(".$param1.",".$param2."),0) as amt");
        $sqlResultData1 = pg_fetch_object($sqlResult1);
        $caseOpenBalance = (!empty($sqlResultData1) && isset($sqlResultData1->{'amt'})) ? $sqlResultData1->{'amt'} : 0;

        $cNo = trim($case_id);
        $sqlResult2 = pg_query($connection, "select coalesce(vh_opendepositamtofpatient(".$param1.",".$param2."),0) as amt");
        $sqlResultData2 = pg_fetch_object($sqlResult2);
        $unAllocatedDepositAmtPerCase = (!empty($sqlResultData2) && isset($sqlResultData2->{'amt'})) ? $sqlResultData2->{'amt'} : 0;

        $sqlResult3 = pg_query($connection, "select coalesce(sum(vh_paymentavailable(c_payment_id)),0) as amt from c_payment cp, C_DocType cd where cp.C_DocType_ID=cd.C_DocType_ID and cd.pc_paymenttypeinsurancepatient in ('A','T') and x_mwcase_id = ".$param2."");
        $sqlResultData3 = pg_fetch_object($sqlResult3);
        $adjAmtOpenDepositCase = (!empty($sqlResultData3) && isset($sqlResultData3->{'amt'})) ? $sqlResultData3->{'amt'} : 0;

        //Replace data
        $sqlResult4 = pg_query($connection, "select sum(viv.linenetamt) as casebilled, sum(vh_chargeadjustmentamt(viv.c_invoiceline_id)) as caseadj from vh_invoiceline_v viv where viv.x_mwcase_id = ".$param2."");
        $sqlResultData4 = pg_fetch_object($sqlResult4);

        $caseAdjAmt1 = ($unAllocatedDepositAmtPerCase + $adjAmtOpenDepositCase);
        $caseOpenBalance1 = ($caseOpenBalance - $caseAdjAmt1);

        $sqlResult5 = pg_query($connection, "select vh_invoicelineopentodate(ci.c_invoiceline_id) as openAmt from c_invoiceline ci ,c_invoice ci2 where ci.c_invoice_id = ci2.c_invoice_id and (select count(pc_payerdet_charge_id) from pc_payerdet_charge cii where ci.c_invoiceline_id = cii.c_invoiceline_id and cii.line<4 and cii.iscomplete='N') = 0 and ci2.c_bpartner_id = ".$param1." and ci2.X_MwCase_ID = ".$param2." and ci2.processed ='Y' and ci2.DocStatus='CO' and ci.LineNetAmt!=0");
        $tOpenAmt = 0;
        while($rows5 = pg_fetch_object($sqlResult5)) {
            if(isset($rows5->{'openamt'}) && !empty($rows5->{'openamt'})) {
                $tOpenAmt += $rows5->{'openamt'};
            }
        }

        if($caseNumber && !empty($caseNumber) && $caseNumber != '') {
            if($cNo == $caseNumber) {
                $caseBalance = $caseOpenBalance1;
                $caseBilled = (!empty($sqlResultData4) && isset($sqlResultData4->{'casebilled'})) ? $sqlResultData4->{'casebilled'} : 0;
                $caseAdjAmt = (!empty($sqlResultData4) && isset($sqlResultData4->{'caseadj'})) ? $sqlResultData4->{'caseadj'} : 0;
                $casePaidAmtTotal = ($caseBilled - $caseBalance - $caseAdjAmt);
                $caseUnAllocatedAmt = $caseAdjAmt1;

                $patientResponsibility = ($tOpenAmt - $caseUnAllocatedAmt);
            }
        }

        $overallBalance += $caseOpenBalance1;
        $overAllUnAllocatedAmt += $caseAdjAmt1;
    }

    // while ($rows = pg_fetch_object($result)) {
    //     $remianderTotal = array_sum(array($rows->{'Amount'},$rows->{'Adjustment Amount'},$rows->{'Insurance 1 Amount Paid'},$rows->{'Insurance 2 Amount Paid'},$rows->{'Insurance 3 Amount Paid'},$rows->{'Guarantor Amount Paid'}));

    //     $overallBalance += $remianderTotal;

    //     if($caseNumber && !empty($caseNumber) && $caseNumber != '') {
    //         $cNo = trim($rows->{'Case Number'}); 
    //         if($cNo == $caseNumber) {
    //             $caseBalance += $remianderTotal;

    //             if(($rows->{'Insurance 1 Paid'} == true && $rows->{'Insurance 2 Paid'} == true && $rows->{'Insurance 3 Paid'} == true) || (trim($rows->{'Procedure Code'}) == "PAY")) {
    //                 $patientResponsibility += $remianderTotal;
    //             }

    //             $caseBilled += $rows->{'Amount'};
    //             $casePaidAmtTotal += array_sum(array($rows->{'Insurance 1 Amount Paid'},$rows->{'Insurance 2 Amount Paid'},$rows->{'Insurance 3 Amount Paid'},$rows->{'Guarantor Amount Paid'}));
    //             $caseAdjAmt += $rows->{'Adjustment Amount'};
    //         }
    //     }
    // }

    return array(
        'overallBalance' => $overallBalance,
        'caseBalance' => $caseBalance,
        'patientResponsibility' => $patientResponsibility,
        'caseBilled' => $caseBilled,
        'casePaidAmt' => $casePaidAmtTotal,
        'caseAdjAmt' => $caseAdjAmt,
        'caseUnAllocatedAmt' => $caseUnAllocatedAmt,
        'overAllUnAllocatedAmt' => $overAllUnAllocatedAmt
    );
}
