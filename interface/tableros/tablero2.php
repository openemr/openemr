<?php

/**
 * Main info frame.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");
include("../fusioncharts.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<HTML>

<HEAD>
    <TITLE><?php echo xlt('Tablero'); ?></TITLE>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.4.11/d3.min.js"></script>
    <link rel=stylesheet href="../../public/themes/style_light.css">
    <link rel="stylesheet" href="../../public/assets/bootstrap/dist/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="../../public/assets/jquery-ui/jquery-ui.css" type="text/css">
    <script type="text/javascript" src="../../public/assets/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="../../public/assets/bootstrap/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../../public/assets/font-awesome/css/font-awesome.min.css" type="text/css">
    <link rel="shortcut icon" href="../../public/images/favicon.ico" />
    <script type="text/javascript" src="../../public/assets/jquery-ui/jquery-ui.js"></script>
    <script type="text/javascript" src="../../public/assets/select2/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="../../public/assets/select2/dist/css/select2.min.css" type="text/css">
    <script src="../../public/assets/canvas/canvasjs.min.js"></script>
    <script src="../../public/assets/moment/moment.js"></script>
    <script src="../../public/assets/moment/locale/es.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script  src="../../public/assets/fusionChart/fusioncharts.js"></script>
    <style type="text/css>">
        .canvasjs-chart-credit {
            display: none !important;
        }
    </style>
</HEAD>

<body class="body_top">
    <div class="row" style="margin: 10px">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header">
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom: 5%;">
                        <div class="col-md-12">
                            <h4 class="text-center">Filtros</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label col-sm-2 oe-text-to-right" for="sala">Sala</label>
                                        <div class="col-sm-8">
                                            <select class="form-control col-sm-9" name="sala" id="sala" multiple="">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label col-sm-2 oe-text-to-right" for="cama">Camas</label>
                                        <div class="col-sm-8">
                                            <select class="form-control col-sm-9" name="cama" id="cama" multiple="">

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-top: 2%;">
                                    <div class="form-group">
                                        <label class="control-label col-sm-2 oe-text-to-right">Rango de fechas</label>
                                        <div class="col-sm-8">
                                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                                <i class="fa fa-calendar"></i>&nbsp;
                                                <span></span> <i class="fa fa-caret-down"></i>
                                                <input type="hidden" name="inicio" id="inicio">
                                                <input type="hidden" name="fin" id="fin">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 20px" >

                        <?php
                        $inicio = '2021-06-01 00:00';
                        $fin = '2021-06-20 00:00';
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
                                $i = 0;
                                while ($row = sqlFetchArray($results)) {
                                    $row["date"] = date('d-M-y H:i:s', strtotime($row["date"]));
                                    //echo $row["date"];
                                    array_push($paciente, [$row["date"], 'bps', $row["bps"]??0]);
                                    array_push($paciente, [$row["date"], 'bpd', $row["bpd"]??0]);
                                    array_push($paciente, [$row["date"], 'bmi', $row["bmi"]??0]);
                                    array_push($paciente, [$row["date"], 'pulse', $row["pulse"]??0]);
                                    array_push($paciente, [$row["date"], 'respiration', $row["respiration"]??0]);
                                    array_push($paciente, [$row["date"], 'temperature', $row["temperature"]??0]);
                                    array_push($paciente, [$row["date"], 'oxygen_saturation', $row["oxygen_saturation"]??0]);
                                    array_push($paciente, [$row["date"], 'hr', $row["hr"]??0]);
                                    array_push($paciente, [$row["date"], 'vpc', $row["vpc"]??0]);
                                    array_push($paciente, [$row["date"], 'lvp_d', $row["lvp_d"]??0]);
                                    array_push($paciente, [$row["date"], 'lvp_s', $row["lvp_s"]??0]);
                                    array_push($paciente, [$row["date"], 'pr_spo2', $row["pr_spo2"]??0]);
                                    array_push($paciente, [$row["date"], 'st1', $row["st1"]??0]);
                                    array_push($paciente, [$row["date"], 'st2', $row["st2"]??0]);
                                    array_push($paciente, [$row["date"], 'st3', $row["st3"]??0]);
                                    array_push($paciente, [$row["date"], 'nibps_sys', $row["nibps_sys"]??0]);
                                    array_push($paciente, [$row["date"], 'nibps_dys', $row["nibps_dys"]]);
                                    $i++;
                                }
                                if ($i > 0) {
                                    echo '<div id="chart-container-'. $encounter['pid'].'"class="col-md-6"></div>';
                                    $data = json_encode($paciente);
                                    //print_r(json_encode($paciente));
                                    $schema = '[{"name": "Time","type": "date","format": "%d-%b-%y %H:%M:%S"}, {"name": "Type","type": "string"}, {"name": "valor_vital","type": "number"}]';

                                    $fusionTable = new FusionTable($schema, $data);
                                    $timeSeries = new TimeSeries($fusionTable);

                                    $timeSeries->AddAttribute('chart', '{}');
                                    $timeSeries->AddAttribute('caption', '{"text":"' . $encounter['paciente'] . '"}');
                                    $timeSeries->AddAttribute('subcaption', '{"text":" Sala: ' . $encounter['sala'] . ' - Cama: ' . $encounter['cama'] . '"}');
                                    $timeSeries->AddAttribute('series', '"Type"');
                                    $timeSeries->AddAttribute('yaxis', '[{"plot":"valor_vital","title":"Signos Vitales"}]');


                                    // chart object
                                    $Chart = new FusionCharts(
                                        "timeseries",
                                        "pid-chart-" . $encounter['pid'],
                                        "700",
                                        "450",
                                        "chart-container-". $encounter['pid'],
                                        "json",
                                        $timeSeries
                                    );

                                    // Render the chart
                                    $Chart->render();
                                }
                            }
                        }

                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

<!-- (CHEMED) -->


<script>
    let salas = [];
    let camas = [];

    function showVitals() {

        var xmlhttp = new XMLHttpRequest();
        let result = [];
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                result = $.parseJSON(this.responseText)
                $('#body-vitals-tab').html('');
                let existen_datos = false;
                $.each(result, function(index, I) {
                    let mostrar = false;
                    if (camas.length === 0 && salas.length === 0) {
                        mostrar = true;
                    }
                    if (salas.length > 0) {
                        if (salas.includes(result[index]['sala'].toUpperCase())) {
                            if (camas.length > 0) {
                                if (camas.includes(result[index]['cama'])) {
                                    mostrar = true;
                                } else {
                                    mostrar = false;
                                }
                            } else {
                                mostrar = true;
                            }
                        }
                    } else {
                        if (camas.length > 0) {
                            if (camas.includes(result[index]['cama'])) {
                                mostrar = true;
                            } else {
                                mostrar = false;
                            }
                        }
                    }
                    let sala = result[index]["sala"];
                    let cama = result[index]["cama"];
                    let pid = result[index]["pid"];
                    var camaExists = ($('#cama option[value="' + cama + '"]').length > 0);

                    if (!camaExists) {
                        $('#cama').append("<option value='" + cama + "'>" + cama + "</option>");
                    }
                    var salaExists = ($('#sala option[value="' + sala + '"]').length > 0);

                    if (!salaExists) {
                        $('#sala').append("<option value='" + sala + "'>" + sala + "</option>");
                    }
                    if (mostrar == true) {
                        existen_datos = true;
                        $('#body-vitals-tab').append('<div class="col-md-6"><div id="chartContainer' + pid + '" style="height: 350px; width: 100%;"></div></div>');
                        new CanvasJS.Chart("chartContainer" + pid, {
                            animationEnabled: true,
                            exportEnabled: false,
                            title: {
                                fontFamily: "tahoma",
                                text: 'Sala: ' + result[index]['sala'] + ' Cama: ' + result[index]['cama'] + ' Nro Registro: ' + pid + '   ' + result[index]['paciente']
                            },
                            axisY: {
                                title: "Signos vitales"
                            },
                            legend: {
                                cursor: "pointer",
                                dockInsidePlotArea: false,
                                itemclick: toggleDataSeries
                            },
                            data: [{
                                    type: "spline",
                                    name: "BPS",
                                    showInLegend: true,
                                    dataPoints: JSON.parse(result[index]['bps'])
                                },
                                {
                                    type: "spline",
                                    name: "BPD",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['bpd'])
                                },
                                {
                                    type: "spline",
                                    name: "Sat. Ox",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['oxy'])
                                },
                                {
                                    type: "spline",
                                    name: "Pulso",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['pulso'])
                                },
                                {
                                    type: "spline",
                                    name: "Temperatura",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['temp'])
                                },

                                {
                                    type: "spline",
                                    name: "HR",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['hr'])
                                },
                                {
                                    type: "spline",
                                    name: "VPC",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['vpc'])
                                },
                                {
                                    type: "spline",
                                    name: "HR",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['hr'])
                                },
                                {
                                    type: "spline",
                                    name: "lvp(s)",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['lvp_s'])
                                },
                                {
                                    type: "spline",
                                    name: "lvp(d)",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['lvp_s'])
                                },
                                {
                                    type: "spline",
                                    name: "PR(Sp02)",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['pr_spo2'])
                                },
                                {
                                    type: "spline",
                                    name: "ST1",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['st1'])
                                },
                                {
                                    type: "spline",
                                    name: "ST2",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['st2'])
                                },
                                {
                                    type: "spline",
                                    name: "ST3",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['st3'])
                                },
                                {
                                    type: "spline",
                                    name: "Nibp(S)",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['nibps_sys'])
                                },
                                {
                                    type: "spline",
                                    name: "Nibps(D)",
                                    showInLegend: true,
                                    xValueType: "dateTime",
                                    dataPoints: JSON.parse(result[index]['nibps_dys'])
                                }
                            ]
                        }).render();
                    }
                });
                if (!existen_datos) {
                    $('#body-vitals-tab').html('');
                    $('#body-vitals-tab').html('<div class="alert alert-warning text-center" role="alert">\n' +
                        '                <p>- No se encontraron datos -</p>\n' +
                        '            </div>');

                }


                function toggleDataSeries(e) {
                    if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                        e.dataSeries.visible = false;
                    } else {
                        e.dataSeries.visible = true;
                    }
                    e.chart.render();
                }
            }
        };
        let inicio = $('#inicio').val();
        let fin = $('#fin').val()
        xmlhttp.open("GET", "list_tablero2.php?inicio=" + inicio + "&fin=" + fin, true);
        xmlhttp.send();


    }

    $(document).on('click', '.vitalsbtn', function() {
        top.RTop.location = "../../patient_file/summary/demographics.php?set_pid=" + $(this).data('pid') + '&goto-vitals=vitals';
    });
    $(document).ready(function() {
        moment.locale('es');
        var start = moment().locale('es');
        var end = moment().locale('es');
        cb(start, end)

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('#inicio').val(start.format('YYYY-MM-DD'));
            $('#fin').val(end.add(1, 'days').format('YYYY-MM-DD'));
            showVitals()
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            locale: {
                "daysOfWeek": [
                    "Dom",
                    "Lun",
                    "Ma",
                    "Mie",
                    "Jue",
                    "Vie"
                ],
                "monthNames": [
                    "Enero",
                    "Febrero",
                    "Marzo",
                    "Abril",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre"
                ],
                applyLabel: "Aceptar",
                cancelLabel: "Cancelar",
                weekLabel: "w",
                customRangeLabel: "Seleccione un Rango"
            },
            ranges: {
                //Boton 'para probar' es para uso de desarrollo exclusivo, omitir en produccion
                'Para probar': [moment().subtract(15, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'El mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        //showVitals()
        //setInterval(showVitals, 60000);
        $('#sala').select2({
            placeholder: 'Seleccione una o más opciones'
        }).on("select2:select select2:unselect", function(e) {
            //this returns all the selected item
            var items = $(this).val();
            salas = items;
            showVitals()

        });
        $('#cama').select2({
            placeholder: 'Seleccione una o más opciones'
        }).on("select2:select select2:unselect", function(e) {

            var items = $(this).val();
            camas = items;
            showVitals()

        });
    })
</script>


<!-- END (CHEMED) -->


</HTML>