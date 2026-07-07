<?php

require_once(__DIR__ . "/../globals.php");

use OpenEMR\Common\Database\QueryUtils;

$_inicio_raw = filter_input(INPUT_GET, 'inicio');
$inicio = is_string($_inicio_raw) ? $_inicio_raw : '';
$_fin_raw = filter_input(INPUT_GET, 'fin');
$fin    = is_string($_fin_raw) ? $_fin_raw : '';
//modificacion de consulta query para visualizar las salas y camas de forma ordenada ascendente
$internados_actuales_consult = "SELECT f.pid, CONCAT(CONCAT(p.fname, ' '),p.lname) as paciente, f.cuarto COLLATE utf8_general_ci sala, f.cama as cama from form_encounter as f join patient_data as p on p.pid = f.pid where f.pc_catid = 16 and f.out_date is null order by sala, f.cama ASC";
/** @var list<array<string, string|int|null>> $encounters */
$encounters = QueryUtils::fetchRecords($internados_actuales_consult);
$inpatient = [];
$result = [];
$arrayPID = []; //Array gigante nambrena luego
$iter = 0;
foreach ($encounters as $encounter) {
    /** @var string $escaped_inicio */
    $escaped_inicio = add_escape_custom($inicio);
    /** @var string $escaped_fin */
    $escaped_fin = add_escape_custom($fin);
    $sql_vitals = "SELECT p.fname, v.* FROM form_vitals as v JOIN patient_data as p on p.pid = v.pid  WHERE v.pid =? and v.date between '" . $escaped_inicio . "' and '" . $escaped_fin . "'  ORDER by v.date ASC";
    /** @var list<array<string, string|int|null>> $vitalsRows */
    $vitalsRows = QueryUtils::fetchRecords($sql_vitals, [(string)($encounter['pid'] ?? '')]);
    $paciente = [];
    if ($vitalsRows !== []) {
        /*
        $i = 0;
        $paciente = "";
        $datos_bps = [];
        $datos_bpd = [];
        $datos_temp = [];
        $datos_resp = [];
        $datos_pulso = [];
        $datos_bmi = [];
        $datos_oxy = [];

        $datos_hr = [];
        $datos_vpc = [];
        $datos_lvp_d = [];
        $datos_lvp_s = [];
        $datos_pr_spo2 = [];
        $datos_st1 = [];
        $datos_st2 = [];
        $datos_st3 = [];
        $datos_nibps_sys = [];
        $datos_nibps_dys = [];
        */
        foreach ($vitalsRows as $row) {
            $paciente[] = [$row["date"], 'bps', $row["bps"]];
            $paciente[] = [$row["date"], 'bpd', $row["bpd"]];
            $paciente[] = [$row["date"], 'bmi', $row["bmi"]];
            $paciente[] = [$row["date"], 'pulse', $row["pulse"]];
            $paciente[] = [$row["date"], 'respiration', $row["respiration"]];
            $paciente[] = [$row["date"], 'temperature', $row["temperature"]];
            $paciente[] = [$row["date"], 'oxygen_saturation', $row["oxygen_saturation"]];
            $paciente[] = [$row["date"], 'hr', $row["hr"]];
            $paciente[] = [$row["date"], 'vpc', $row["vpc"]];
            $paciente[] = [$row["date"], 'lvp_d', $row["lvp_d"]];
            $paciente[] = [$row["date"], 'lvp_s', $row["lvp_s"]];
            $paciente[] = [$row["date"], 'pr_spo2', $row["pr_spo2"]];
            $paciente[] = [$row["date"], 'st1', $row["st1"]];
            $paciente[] = [$row["date"], 'st2', $row["st2"]];
            $paciente[] = [$row["date"], 'st3', $row["st3"]];
            $paciente[] = [$row["date"], 'nibps_sys', $row["nibps_sys"]];
            $paciente[] = [$row["date"], 'nibps_dys', $row["nibps_dys"]];
        }
        $result[$iter] = [
            "pid" => $encounter['pid'],
            "paciente" => $encounter['paciente'],
            "cama" => $encounter['cama'],
            "sala" => $encounter['sala'],
            "dato" => json_encode($paciente, JSON_NUMERIC_CHECK),
        ];
    }
    $iter++;
}
echo json_encode($result)
?>
