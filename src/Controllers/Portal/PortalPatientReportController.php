<?php

/**
 * PortalPatientReportController class file - holds helper methods for retrieving data with the custom patient report
 * in the portal_patient_report.php file.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2024 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

use OpenEMR\Common\Database\QueryUtils;

class PortalPatientReportController
{
    public function getDocuments($pid)
    {

        // show available documents
        $sql = "SELECT d.id, d.url, d.name as document_name, c.name FROM documents AS d " .
            "LEFT JOIN categories_to_documents AS ctd ON d.id=ctd.document_id " .
            "LEFT JOIN categories AS c ON c.id = ctd.category_id WHERE " .
            "d.foreign_id = ? AND d.deleted = 0";
        $records = QueryUtils::sqlStatementThrowException($sql, [$pid]);
        $documents = [];
        foreach ($records as $record) {
            $fname = basename($record['url']);
            $extension = strtolower(substr($fname, strrpos($fname, ".")));
            if ($extension !== '.zip' && $extension !== '.dcm') {
                $documents[] = [
                    'id' => $record['id'],
                    'url' => basename($record['url']),
                    'name' => $record['document_name'],
                    'category' => xl_document_category($record['name'])
                ];
            }
        }
        return $documents;
    }
    public function getProcedureOrders($pid)
    {
        $res = sqlStatement(
            "SELECT po.procedure_order_id, po.date_ordered, fe.date " .
            "FROM procedure_order AS po " .
            "LEFT JOIN forms AS f ON f.pid = po.patient_id AND f.formdir = 'procedure_order' AND " .
            "f.form_id = po.procedure_order_id AND f.deleted = 0 " .
            "LEFT JOIN form_encounter AS fe ON fe.pid = f.pid AND fe.encounter = f.encounter " .
            "WHERE po.patient_id = ? " .
            "ORDER BY po.date_ordered DESC, po.procedure_order_id DESC",
            array($pid)
        );
        $procedures = [];
        $proceduresById = [];
        $index = 0;
        while ($row = sqlFetchArray($res)) {
            $poid = $row['procedure_order_id'];
            $proceduresById[$poid] = $index++;
            $procedures[] = [
                'id' => $poid,
                'date_ordered' => $row['date_ordered'],
                'date' => $row['date'],
                'procedures' => []
            ];
        }
        // nothing to do here
        if (empty($proceduresById)) {
            return [];
        }
        $sql = "SELECT procedure_order_id, procedure_code, procedure_name FROM procedure_order_code " .
            "WHERE procedure_order_id IN (" . implode(",", array_keys($proceduresById)) . ") ORDER BY procedure_order_id, procedure_order_seq";
        $res = sqlStatement($sql);
        while ($row = sqlFetchArray($res)) {
            $poid = $row['procedure_order_id'];
            $index = $proceduresById[$poid];
            $procedures[$index]['procedures'][] = [
                'code' => $row['procedure_code'],
                'name' => $row['procedure_name']
            ];
        }
        return $procedures;
    }

    public function getIssues(array $ISSUE_TYPES, int $pid)
    {
        $issuesByType = [];
        // get issues
        $pres = sqlStatement("SELECT lists.* FROM lists "
        . " WHERE lists.pid = ? " .
            "ORDER BY lists.type, lists.begdate", [$pid]);
        $lasttype = "";
        $lastEncounter = null;
        $issuesIndex = 0;
        $issuesByTypeMap = [];
        while ($prow = sqlFetchArray($pres)) {
            if ($lasttype != $prow['type']) {
                $lasttype = $prow['type'];

                if (empty($issuesByType[$lasttype])) {
                    $issuesByType[$lasttype] = [
                        'type' => $lasttype,
                        'display' => $ISSUE_TYPES[$lasttype][0],
                        'issues' => []
                    ];
                }
                $issuesIndex = 0;
            }
            $rowid = $prow['id'];
            $disptitle = trim($prow['title']) ? $prow['title'] : "[Missing Title]";


            $issuesByTypeMap[$lasttype][$rowid] = $issuesIndex;
            $issuesByType[$lasttype]['issues'][$issuesIndex++] = [
                'id' => $rowid,
                'title' => $disptitle,
                'begdate' => $prow['begdate'],
                'enddate' => $prow['enddate'],
                'status' => !empty($prow['enddate']) ? 'inactive' : 'active',
                'encounters' => []
            ];
        }
        // now populate encounters
        $ieres = sqlStatement("SELECT encounter,list_id,lists.type FROM issue_encounter JOIN lists ON lists.id = list_id "
            . " AND issue_encounter.pid = lists.pid WHERE " .
            "lists.pid = ?", [$pid]);
        while ($ierow = sqlFetchArray($ieres)) {
            $listId = $ierow['list_id'];
            $encounter = $ierow['encounter'];
            $issuesIndex = $issuesByTypeMap[$ierow['type']][$listId];
            $issuesByType[$ierow['type']]['issues'][$issuesIndex]['encounters'][] = $encounter;
        }
        return $issuesByType;
    }
    public function getEncounters($pid)
    {

        $isfirst = 1;
        $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
            "forms.formdir, forms.date AS fdate, form_encounter.date " .
            ",form_encounter.reason " .
            "FROM forms, form_encounter WHERE " .
            "forms.pid = ? AND form_encounter.pid = ? AND " .
            "form_encounter.encounter = forms.encounter " .
            " AND forms.deleted=0 " . // --JRM--
            "ORDER BY form_encounter.date DESC, fdate ASC", [$pid, $pid]);
        $res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
        $encountersByDate = [];
        $encountersByEncounter = [];
        $html_strings = array();
        $registry_form_name = array();
        while ($result2 = sqlFetchArray($res2)) {
            array_push($registry_form_name, trim($result2['name']));
        }

        $encounter = null;
        while ($result = sqlFetchArray($res)) {
            $encounterId = $result['encounter'];
            if ($result["form_name"] == "New Patient Encounter") {
                $encounter = [
                    'formdir' => $result["formdir"]
                    ,'form_id' => $result["form_id"]
                    ,'encounter' => $result["encounter"]
                    ,'display' => ''
                    ,'forms' => []
                ];
                // show encounter reason, not just 'New Encounter'
                // trim to a reasonable length for display purposes --cfapress
                $maxReasonLength = 20;
                if (strlen($result["reason"]) > $maxReasonLength) {
                    $encounter['display'] = substr($result['reason'], 0, $maxReasonLength) . " ... ";
                } else {
                    $encounter['display'] = $result['reason'] ?? '';
                }
                $encounter['date'] = date("Y-m-d", strtotime($result["date"]));
                $encountersByDate[] = $encounterId;
                $encountersByEncounter[$encounterId] = $encounter;
            } else {
                $form_name = trim($result["form_name"]);
                // TODO: @adunsulag we need to investigate why procedure order form saves
                // its name as this way.. so odd.
                if ($form_name === '-procedure') {
                    $form_name = 'Procedure Order';
                }
                //if form name is not in registry, look for the closest match by
                // finding a registry name which is  at the start of the form name.
                //this is to allow for forms to put additional helpful information
                //in the database in the same string as their form name after the name
                $form_name_found_flag = 0;
                foreach ($registry_form_name as $var) {
                    if ($var == $form_name) {
                        $form_name_found_flag = 1;
                    }
                }

                // if the form does not match precisely with any names in the registry, now see if any front partial matches
                // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
                if (!$form_name_found_flag) {
                    foreach ($registry_form_name as $var) {
                        if (strpos($form_name, $var) === 0) {
                            $form_name = $var;
                        }
                    }
                }
                if (empty($encountersByEncounter[$encounterId]['forms'][$form_name])) {
                    $encountersByEncounter[$encounterId]['forms'][$form_name] = [];
                }
                $encountersByEncounter[$encounterId]['forms'][$form_name][] = [
                    'formdir' => $result['formdir']
                    , 'form_id' => $result['form_id']
                    , 'encounter' => $result['encounter']
                    , 'display' => xl_form_title($form_name)
                ];
            }
        }
        $encounters = array_map(function ($encounterId) use ($encountersByEncounter) {
            return $encountersByEncounter[$encounterId];
        }, $encountersByDate);
        return $encounters;
    }
}
