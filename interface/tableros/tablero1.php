<?php



require_once(__DIR__ . "/../globals.php");
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
    <script src="../../public/assets/knob/jquery.knob.min.js"></script>
    <script src="../../public/assets/canvas/canvasjs.min.js"></script>
</HEAD>

<body class="body_top">

    <div class="row" style="margin: 10px">
        <div class="col-md-12">
            <div class="page-header clearfix">
                <div class="row" style="margin-bottom: 5%;">
                    <div class="col-md-12">
                        <h4 class="text-center">Filtros</h4>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-sm-2 oe-text-to-right" for="sala">Sala</label>
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
                    </div>
                </div>
                <div class="row" id="body-vitals-tab">
                </div>
            </div>
        </div>

</body>

<!-- (CHEMED) -->
<script type='text/javascript' language='JavaScript'>
    let camas = [];
    let salas = [];

    function showVitals() {
        var xmlhttp = new XMLHttpRequest();
        let vitals_show = {};
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                let resp = JSON.parse(this.responseText);
                if (resp.length > 0) {
                    $('#body-vitals-tab').html('');

                } else {
                    $('#body-vitals-tab').html('');
                    $('#body-vitals-tab').append('<div class="alert alert-warning text-center" role="alert">' +
                        '<p>- No se encontraron datos -</p>' +
                        '</div>')
                }
                for (element in resp) {
                    let paciente = resp[element]["paciente"];
                    let sala = resp[element]["sala"];
                    let cama = resp[element]["cama"];
                    var camaExists = ($('#cama option[value="' + cama + '"]').length > 0);

                    if (!camaExists) {
                        $('#cama').append("<option value='" + cama + "'>" + cama + "</option>");
                    }
                    var salaExists = ($('#sala option[value="' + sala + '"]').length > 0);

                    if (!salaExists) {
                        $('#sala').append("<option value='" + sala + "'>" + sala + "</option>");
                    }
                    let bps = resp[element]["bps"];
                    let bpd = resp[element]["bpd"];
                    let temperatura = resp[element]["temperatura"];
                    let respiracion = resp[element]["respiracion"];
                    let hora = resp[element]["date"];
                    let pid = resp[element]["pid"];
                    let pulse = resp[element]["pulse"];
                    let oxygen_saturation = resp[element]["oxygen_saturation"];
                    let bmi = resp[element]["BMI"];
                    let hr = resp[element]["hr"];
                    let vpc = resp[element]["vpc"];
                    let lvp_s = resp[element]["lvp_s"];
                    let lvp_d = resp[element]["lvp_d"];
                    let pr_spo2 = resp[element]["pr_spo2"];
                    let st1 = resp[element]["st1"];
                    let st2 = resp[element]["st2"];
                    let st3 = resp[element]["st3"];
                    let nibps_sys = resp[element]["nibps_sys"];
                    let nibps_dys = resp[element]["nibps_dys"];
                    let display_bbps = localStorage.getItem('bps-check' + pid) !== null ? localStorage.getItem('bps-check' + pid) : true;
                    let display_bbpd = localStorage.getItem('bpd-check' + pid) !== null ? localStorage.getItem('bpd-check' + pid) : true;
                    let display_temperature = localStorage.getItem('temperature-check' + pid) !== null ? localStorage.getItem('temperature-check' + pid) : true;
                    let display_respiracion = localStorage.getItem('respiracion-check' + pid) !== null ? localStorage.getItem('respiracion-check' + pid) : true;
                    let display_pulse = localStorage.getItem('pulse-check' + pid) !== null ? localStorage.getItem('pulse-check' + pid) : true;
                    let display_oxygen_saturation = localStorage.getItem('oxygen_saturation-check' + pid) !== null ? localStorage.getItem('oxygen_saturation-check' + pid) : true;

                    let display_hr = localStorage.getItem('hr-check' + pid) !== null ? localStorage.getItem('hr-check' + pid) : true;
                    let display_vpc = localStorage.getItem('vpc-check' + pid) !== null ? localStorage.getItem('vpc-check' + pid) : true;
                    let display_lvp_s = localStorage.getItem('lvp_s-check' + pid) !== null ? localStorage.getItem('lvp_s-check' + pid) : true;
                    let display_lvp_d = localStorage.getItem('lvp_d-check' + pid) !== null ? localStorage.getItem('lvp_d-check' + pid) : true;
                    let display_pr_spo2 = localStorage.getItem('pr_spo2-check' + pid) !== null ? localStorage.getItem('pr_spo2-check' + pid) : true;
                    let display_st1 = localStorage.getItem('st1-check' + pid) !== null ? localStorage.getItem('st1-check' + pid) : true;
                    let display_st2 = localStorage.getItem('st2-check' + pid) !== null ? localStorage.getItem('st2-check' + pid) : true;
                    let display_st3 = localStorage.getItem('st3-check' + pid) !== null ? localStorage.getItem('st3-check' + pid) : true;
                    let display_nibps_sys = localStorage.getItem('nibps_sys-check' + pid) !== null ? localStorage.getItem('nibps_sys-check' + pid) : true;
                    let display_nibps_dys = localStorage.getItem('nibps_dys-check' + pid) !== null ? localStorage.getItem('nibps_dys-check' + pid) : true;
                    $('#body-vitals-tab').append('<div class="col-md-12 sala-' + sala + ' ' + cama + ' listado_sv">' +
                        '<div class="row"><div class="col-md-10 text-left><h4 class="text-left"> Sala ' + sala + ' - Cama ' + cama + ' - ' + paciente + " - Último signo recibido a las: " + hora + '</h4>' +
                        '<a class="btn btn-primary vitalsbtn" href="javascript:void(0)" title="Ir al Paciente" data-pid="' + pid + '"><i class="fa fa-eye"></i></a>' +
                        '<button title="Seleccionar signos vitales a visualizar" data-id="list_sv' + pid + '" class="btn show-list btn-success"><i class="fa fa-caret-down" aria-hidden="true"></i></button></div>\n' +
                        '             </div>   <div class="row">\n' +

                        '<div class="col-xs-6 col-md-3" style="display: none" id="list_sv' + pid + '">\n' +
                        '<ul style="list-style-type:none">' +
                        '<li><input type="checkbox" class="sv-item" id="bps-check' + pid + '"  name="bps-check' + pid + '" data-value="bps' + pid + '"  ' + ((display_bbps === true || display_bbps === 'true') ? "checked" : "") + '  >\n' +
                        '<label for="bps-check' + pid + '"> Presión arterial sistólica</label><br></li>' +
                        '<li><input type="checkbox" class="sv-item" ' + ((display_bbpd === true || display_bbpd === 'true') ? "checked" : "") + ' id="bpd-check' + pid + '"  name="bpd-check' + pid + '" data-value="bpd' + pid + '">\n' +
                        '<label for="bpd-check' + pid + '"> Presión arterial diastólica</label><br></li>' +
                        '<li><input type="checkbox" class="sv-item" ' + ((display_temperature === true || display_temperature === 'true') ? "checked" : "") + ' id="temperature-check' + pid + '"  name="temperature-check' + pid + '" data-value="temperatura' + pid + '">\n' +
                        '<label for="temperature-check' + pid + '"> Temperatura</label><br></li>' +
                        '<li><input type="checkbox" class="sv-item" ' + ((display_respiracion === true || display_respiracion === 'true') ? "checked" : "") + ' id="respiracion-check' + pid + '"  name="respiracion-check' + pid + '" data-value="respiracion' + pid + '">\n' +
                        '<label for="respiracion-check' + pid + '"> Respiración</label><br></li>' +
                        '<li><input type="checkbox" class="sv-item" ' + ((display_pulse === true || display_pulse === 'true') ? "checked" : "") + ' id="pulse-check' + pid + '"  name="pulse-check' + pid + '" data-value="pulse' + pid + '">\n' +
                        '<label for="pulse-check' + pid + '"> Pulso</label><br></li>' +
                        '<li><input type="checkbox" class="sv-item"  ' + ((display_oxygen_saturation === true || display_oxygen_saturation === 'true') ? "checked" : "") + ' id="oxygen_saturation-check' + pid + '"  name="oxygen_saturation-check' + pid + '" data-value="oxygen_saturation' + pid + '">\n' +
                        '<label for="oxygen_saturation-check' + pid + '"> Sat. de Oxígeno</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_hr === true || display_hr === 'true') ? "checked" : "") + ' id="hr-check' + pid + '"  name="hr-check' + pid + '" data-value="hr' + pid + '">\n' +
                        '<label for="hr-check' + pid + '"> Ritmo cardiaco</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_vpc === true || display_vpc === 'true') ? "checked" : "") + ' id="vpc-check' + pid + '"  name="vpc-check' + pid + '" data-value="vpc' + pid + '">\n' +
                        '<label for="vpc-check' + pid + '"> Contracciones ventriculares prematuras</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_lvp_s === true || display_lvp_s === 'true') ? "checked" : "") + ' id="lvp_s-check' + pid + '"  name="lvp_s-check' + pid + '" data-value="lvp_s' + pid + '">\n' +
                        '<label for="lvp_s-check' + pid + '"> Pr. ventricular izq sis</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_lvp_d === true || display_lvp_d === 'true') ? "checked" : "") + ' id="lvp_d-check' + pid + '"  name="lvp_d-check' + pid + '" data-value="lvp_d' + pid + '">\n' +
                        '<label for="lvp_d-check' + pid + '"> Pr. ventricular izq diast</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_pr_spo2 === true || display_pr_spo2 === 'true') ? "checked" : "") + ' id="pr_spo2-check' + pid + '"  name="pr_spo2-check' + pid + '" data-value="pr_spo2' + pid + '">\n' +
                        '<label for="pr_spo2-check' + pid + '"> Frec. del pulso por sat. de oxígeno</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_st1 === true || display_st1 === 'true') ? "checked" : "") + ' id="st1-check' + pid + '"  name="st1-check' + pid + '" data-value="st1' + pid + '">\n' +
                        '<label for="st1-check' + pid + '"> ST1</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_st2 === true || display_st2 === 'true') ? "checked" : "") + ' id="st2-check' + pid + '"  name="st2-check' + pid + '" data-value="st2' + pid + '">\n' +
                        '<label for="st2-check' + pid + '"> ST2</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_st3 === true || display_st3 === 'true') ? "checked" : "") + ' id="st3-check' + pid + '"  name="st3-check' + pid + '" data-value="st3' + pid + '">\n' +
                        '<label for="st3-check' + pid + '"> ST3</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_nibps_sys === true || display_nibps_sys === 'true') ? "checked" : "") + ' id="nibps_sys-check' + pid + '"  name="nibps_sys-check' + pid + '" data-value="nibps_sys' + pid + '">\n' +
                        '<label for="nibps_sys-check' + pid + '"> Presión No Invasiva sis</label><br></li>' +

                        '<li><input type="checkbox" class="sv-item"  ' + ((display_nibps_dys === true || display_nibps_dys === 'true') ? "checked" : "") + ' id="nibps_dys-check' + pid + '"  name="nibps_dys-check' + pid + '" data-value="nibps_dys' + pid + '">\n' +
                        '<label for="nibps_dys-check' + pid + '"> Presión No Invasiva Dis</label><br></li>' +
                        '</ul>' +
                        '                    </div>\n' +


                        '                    <div class="col-xs-6 col-md-2  ' + ((display_bbps === true || display_bbps === 'true') ? "" : "hidden") + '"  id="bps' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + bps + '"  data-width="100" data-height="100"  data-max="300" data-fgColor="#3c8dbc" readonly>\n' +
                        '\n' +
                        '                        <div class="knob-label">Presión arterial sistólica</div>\n' +
                        '                    </div>\n' +
                        '                    <!-- ./col -->\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_bbpd === true || display_bbpd === 'true') ? "" : "hidden") + '" id="bpd' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + bpd + '" data-width="100" data-height="100"  data-max="300" data-fgColor="#f56954" readonly>\n' +
                        '\n' +
                        '                        <div class="knob-label">Presión arterial diastólica</div>\n' +
                        '                    </div>\n' +
                        '                    <!-- ./col -->\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_temperature === true || display_temperature === 'true') ? "" : "hidden") + '" id="temperatura' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + temperatura + '"  data-width="100" data-height="100" data-max="300" readonly data-fgColor="#00a65a">\n' +
                        '\n' +
                        '                        <div class="knob-label">Temperatura</div>\n' +
                        '                    </div>\n' +
                        '                    <!-- ./col -->\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_respiracion === true || display_respiracion === 'true') ? "" : "hidden") + ' " id="respiracion' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + respiracion + '" data-width="100" data-height="100" data-max="300" data-fgColor="#00c0ef" readonly>\n' +
                        '                        <div class="knob-label">Respiración</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center  ' + ((display_pulse === true || display_pulse === 'true') ? "" : "hidden") + '" id="pulse' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + pulse + '" data-width="100" data-height="100" data-max="300" data-fgColor="#1CEB05" readonly>\n' +
                        '                        <div class="knob-label">Pulso</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_oxygen_saturation === true || display_oxygen_saturation === 'true') ? "" : "hidden") + ' " id="oxygen_saturation' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + oxygen_saturation + '" data-width="100" data-height="100" data-max="300" data-fgColor="#DEC112" readonly>\n' +
                        '                        <div class="knob-label">Saturación de oxígeno</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_hr === true || display_hr === 'true') ? "" : "hidden") + ' " id="hr' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + hr + '" data-width="100" data-height="100" data-max="300" data-fgColor="#ED4F37" readonly>\n' +
                        '                        <div class="knob-label">Ritmo cardiaco</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_vpc === true || display_vpc === 'true') ? "" : "hidden") + ' " id="vpc' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + vpc + '" data-width="100" data-height="100" data-max="300" data-fgColor="#73EF93" readonly>\n' +
                        '                        <div class="knob-label">Contracciones ventriculares prematuras</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_lvp_s === true || display_lvp_s === 'true') ? "" : "hidden") + ' " id="lvp_s' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + lvp_s + '" data-width="100" data-height="100" data-max="300" data-fgColor="#F2B0D5" readonly>\n' +
                        '                        <div class="knob-label">Pr. ventricular izq sis</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_lvp_d === true || display_lvp_d === 'true') ? "" : "hidden") + ' " id="lvp_d' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + lvp_d + '" data-width="100" data-height="100" data-max="300" data-fgColor="#B948BD" readonly>\n' +
                        '                        <div class="knob-label">Pr. ventricular izq diast</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_pr_spo2 === true || display_pr_spo2 === 'true') ? "" : "hidden") + ' " id="pr_spo2' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + pr_spo2 + '" data-width="100" data-height="100" data-max="300" data-fgColor="#DEC112" readonly>\n' +
                        '                        <div class="knob-label">Frec. del pulso por sat. de oxígeno</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2  text-center ' + ((display_st1 === true || display_st1 === 'true') ? "" : "hidden") + ' " id="st1' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + st1 + '" data-width="100" data-height="100" data-max="300" data-fgColor="#48BDA7" readonly>\n' +
                        '                        <div class="knob-label">ST1</div>\n' +
                        '                    </div>\n' +
                        '                </div><div class="row">' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_st2 === true || display_st2 === 'true') ? "" : "hidden") + ' " id="st2' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + st2 + '" data-width="100" data-height="100" data-max="300" data-fgColor="#F0845B" readonly>\n' +
                        '                        <div class="knob-label">ST2</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_st3 === true || display_st3 === 'true') ? "" : "hidden") + ' " id="st3' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + st3 + '" data-width="100" data-height="100" data-max="300" data-fgColor="#6B62BD" readonly>\n' +
                        '                        <div class="knob-label">ST3</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_nibps_sys === true || display_nibps_sys === 'true') ? "" : "hidden") + ' " id="nibps_sys' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + nibps_sys + '" data-width="100" data-height="100" data-max="300" data-fgColor="#5985BD" readonly>\n' +
                        '                        <div class="knob-label">Presión No Invasiva sis</div>\n' +
                        '                    </div>\n' +
                        '                    <div class="col-xs-6 col-md-2 text-center ' + ((display_nibps_dys === true || display_nibps_dys === 'true') ? "" : "hidden") + ' " id="nibps_dys' + pid + '">\n' +
                        '                        <input type="text" class="knob" value="' + nibps_dys + '" data-width="100" data-height="100" data-max="300" data-fgColor="#F0E189" readonly>\n' +
                        '                        <div class="knob-label">Presión No Invasiva Dis</div>\n' +
                        '                    </div>\n' +
                        '</div></div><hr style="border-top: 1px solid black;margin-bottom: 10px;">');
                }
                $('.knob').knob();
            }
        }
        xmlhttp.open("GET", "listar_vitales.php?salas=" + salas + '&camas=' + camas, true);
        xmlhttp.send();
    }

    showVitals()
    setInterval(showVitals, 60000);
    $(document).on('click', '.vitalsbtn', function() {
        top.RTop.location = "<?php echo $GLOBALS['webroot'] ?>" + "/interface/patient_file/summary/demographics.php?set_pid=" + $(this).data('pid') + '&goto-vitals=vitals';
    });
    $(document).on('click', '.show-list', function() {
        showVitalsList('#' + $(this).data('id'))
    });

    function showVitalsList(id) {
        if ($(id).is(":visible")) {
            $(id).hide();
        } else {
            $(id).css('display', 'block');
        }
    }
    $(document).on('click', '.sv-item', function() {
        let check = $(this).prop('checked')
        showVitalItem('#' + $(this).data('value'), check, $(this).attr('id'))
    });

    function showVitalItem(id, check, input) {
        if (check) {
            if (localStorage.getItem(input) != null) {
                localStorage.removeItem(input);
            }
            localStorage.setItem(input, check);
            $(id).removeClass('hidden');
            $(id).css('display', 'block');
        } else {
            if (localStorage.getItem(input) != null) {
                localStorage.removeItem(input);
            }
            localStorage.setItem(input, false);
            $(id).hide();
        }
    }
    $(document).ready(function() {
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
<script>

</script>
<!-- END (CHEMED) -->


<noframes>

    <body bgcolor="#FFFFFF">
        <?php echo xlt('Frame support required'); ?>
    </body>
</noframes>

</HTML>