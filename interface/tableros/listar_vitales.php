<?php

/**
 * List Vitals for Inpatients
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");

use OpenEMR\Common\Database\QueryUtils;

/*Extraer todos los internados actuales, tabla: form_encounter, con tipo Internación pc_catid = 16 (referencia tabla: openemr_postcalendar_categories   )*/
$salas = (string) filter_input(INPUT_GET, 'salas');
$camas = (string) filter_input(INPUT_GET, 'camas');
$query_where = 'where f.pc_catid = 16 and f.out_date is null';

if ($salas !== '') {
    $query_where .= ' and f.cuarto IN (';
    $salas_arr = explode(',', $salas);
    foreach ($salas_arr as $sala) {
        $query_where .= '"' . add_escape_custom($sala) . '",';
    }
    $query_where = rtrim($query_where, ',');
    $query_where .= ')';
}
if ($camas !== '') {
    $query_where .= ' and f.cama IN (';
    $camas_arr = explode(',', $camas);
    foreach ($camas_arr as $cama) {
        $query_where .= '"' . add_escape_custom($cama) . '",';
    }
    $query_where = rtrim($query_where, ',');
    $query_where .= ')';
}
$internados_actuales_consult = "SELECT f.pid, CONCAT(CONCAT(p.fname, ' '),p.lname) as paciente, f.cuarto as sala, f.cama as cama from form_encounter as f join patient_data as p on p.pid = f.pid " . $query_where . "  order by sala, f.cama ASC";
$rows_internados = QueryUtils::fetchRecords($internados_actuales_consult);
$result = [];
foreach ($rows_internados as $row) {
    //encontrar el ultimo form_vitals insertado para este pid y mostrar
    $vital_sql = "SELECT * from form_vitals where  pid = ? order by DATE desc limit 1";
    /** @var array<string,mixed>|null $vitals */
    $vitals = QueryUtils::querySingleRow($vital_sql, [(string)($row['pid'] ?? '')]);
    if ($vitals !== null) {
        $ts_vital = strtotime((string)($vitals["date"] ?? ''));
        $result[] = [
            "paciente"        => (string)($row['paciente'] ?? ''),
            "sala"            => strtoupper((string)($row['sala'] ?? '')),
            "cama"            => (string)($row['cama'] ?? ''),
            "bps"             => $vitals["bps"],             //blood pressure systolic
            "bpd"             => $vitals["bpd"],             //blood pressure diastolic
            "temperatura"     => $vitals["temperature"],
            "respiracion"     => $vitals["respiration"],
            "pulse"           => $vitals["pulse"],
            "BMI"             => $vitals["BMI"],             //Índice de masa corporal
            "oxygen_saturation" => $vitals["oxygen_saturation"],
            "date"            => $ts_vital !== false ? date('d/m/Y H:i:s', $ts_vital) : '',
            "pid"             => $vitals["pid"],
            "hr"              => $vitals["hr"],
            "vpc"             => $vitals["vpc"],
            "lvp_s"           => $vitals["lvp_s"],
            "lvp_d"           => $vitals["lvp_d"],
            "pr_spo2"         => $vitals["pr_spo2"],
            "st1"             => $vitals["st1"],
            "st2"             => $vitals["st2"],
            "st3"             => $vitals["st3"],
            "nibps_sys"       => $vitals["nibps_sys"],
            "nibps_dys"       => $vitals["nibps_dys"],
        ];
    }
}
echo json_encode($result);
