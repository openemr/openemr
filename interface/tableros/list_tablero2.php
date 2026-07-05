<?php

require_once(__DIR__ . "/../globals.php");
$inicio = $_REQUEST["inicio"];
$fin = $_REQUEST["fin"];
//modificacion de consulta query para visualizar las salas y camas de forma ordenada ascendente
$internados_actuales_consult = "SELECT f.pid, CONCAT(CONCAT(p.fname, ' '),p.lname) as paciente, f.cuarto COLLATE utf8_general_ci sala, f.cama as cama from form_encounter as f join patient_data as p on p.pid = f.pid where f.pc_catid = 16 and f.out_date is null order by sala, f.cama ASC";
$res = sqlStatement($internados_actuales_consult);
$inpatient = [];
$result = [];
$arrayPID = []; //Array gigante nambrena luego
for ($iter = 0; $encounter = sqlFetchArray($res); $iter++) {
    $sql_vitals = "SELECT p.fname, v.* FROM form_vitals as v JOIN patient_data as p on p.pid = v.pid  WHERE v.pid =? and v.date between '" . $inicio . "' and '" . $fin . "'  ORDER by v.date ASC";
    $results = sqlStatement($sql_vitals, array($encounter['pid']));
    $paciente = [];
    if ($results) {
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
        while ($row = sqlFetchArray($results)) {
            array_push($paciente, [$row["date"], 'bps', $row["bps"]]);
            array_push($paciente, [$row["date"], 'bpd', $row["bpd"]]);
            array_push($paciente, [$row["date"], 'bmi', $row["bmi"]]);
            array_push($paciente, [$row["date"], 'pulse', $row["pulse"]]);
            array_push($paciente, [$row["date"], 'respiration', $row["respiration"]]);
            array_push($paciente, [$row["date"], 'temperature', $row["temperature"]]);
            array_push($paciente, [$row["date"], 'oxygen_saturation', $row["oxygen_saturation"]]);
            array_push($paciente, [$row["date"], 'hr', $row["hr"]]);
            array_push($paciente, [$row["date"], 'vpc', $row["vpc"]]);
            array_push($paciente, [$row["date"], 'lvp_d', $row["lvp_d"]]);
            array_push($paciente, [$row["date"], 'lvp_s', $row["lvp_s"]]);
            array_push($paciente, [$row["date"], 'pr_spo2', $row["pr_spo2"]]);
            array_push($paciente, [$row["date"], 'st1', $row["st1"]]);
            array_push($paciente, [$row["date"], 'st2', $row["st2"]]);
            array_push($paciente, [$row["date"], 'st3', $row["st3"]]);
            array_push($paciente, [$row["date"], 'nibps_sys', $row["nibps_sys"]]);
            array_push($paciente, [$row["date"], 'nibps_dys', $row["nibps_dys"]]);
            $i++;
        }
        if ($i > 0) {
            $result[$iter] = [
                "pid" => $encounter['pid'],
                "paciente" => $encounter['paciente'],
                "cama" => $encounter['cama'],
                "sala" => $encounter['sala'],
                "dato" => json_encode($paciente, JSON_NUMERIC_CHECK),
            ];
        }
    }
}
echo json_encode($result)
?>
