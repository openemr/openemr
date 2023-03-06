<?php

use OpenEMR\Core\Header;

require_once("../../globals.php");

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['id'])) $_REQUEST['id'] = '';
if(!isset($_REQUEST['data_set'])) $_REQUEST['data_set'] = '';

$pid = strip_tags($_REQUEST['pid']);
$eleid = $_REQUEST['id'];
$data_set = $_REQUEST['data_set'];

$data_set = isset($data_set) && !empty($data_set) ? json_decode($data_set, true) : array();
$time_type = isset($data_set['time_type']) && !empty($data_set['time_type']) ? $data_set['time_type'] : "one_time";

$time_type_list = array(
	'one_time' => 'One Time',
	'every_minute' => 'Every Minute',
	'daily' => 'Daily',
	'weekly' => 'Weekly',
	'monthly' => 'Monthly'
);

$week_day_list = array(
    'sunday' => 'Sunday',
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
);

$month_list = array(
    'january' => 'January',
    'february' => 'February',
    'march' => 'March',
    'april' => 'April',
    'may' => 'May',
    'june' => 'June',
    'july' => 'July',
    'august' => 'August',
    'september' => 'September',
    'october' => 'October',
    'november' => 'November',
    'december' => 'December',
);

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Facility Finder'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'datetime-picker']); ?>

    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']."/interface/batchcom/php/assets/js/input-values.jquery.js" ?>"></script>

    <script language="JavaScript">

	 function selTime(id, data) {
		if (opener.closed || ! opener.setTime)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setTime(id, data);
		window.close();
		return false;
	 }

	</script>
    </script>

    <style type="text/css">
    	.form_container {
    		grid-gap: 20px;
            display: grid;
            grid-template-columns: minmax(auto, 150px) 1fr;
            min-height: 300px;
    	}
    	.btn_container {
    		position: absolute;
    		bottom: 15px;
    		right: 15px;
    	}
    	.left_section {
    		border-right: 1px solid #e5e5e5;
    	}
    	#time_form {
    		margin-top: 30px;
    		margin-left: 20px;
    	}
    	.hide {
    		display: none;
    	}
        .multiple_selection_container {
            margin-top: 30px;
            display: grid;
            grid-template-columns: auto auto auto 1fr;
            grid-column-gap: 20px;
        }
    </style>
</head>
<body>
	<form id="time_form">
		<div class="form_container">
			<div class="left_section">
				<?php 
					foreach ($time_type_list as $key => $title) {
				?>
						<input type="radio" id="<?php echo $key; ?>" data-name="time_type" name="time_type" value="<?php echo $key; ?>" class="time_type_select">
						<label for="<?php echo $title; ?>"><?php echo $title; ?></label><br>
				<?php
					}
				?>
			</div>
			<div>
				<div id="one_time_container" class="time_container hide">
					<label for="Date & Time">Date & Time: </label>
					<input type="text" data-name="one_time.date_time" name="one_time[date_time]" value="" class="optin datepicker form-control form-control-sm">
				</div>
                <div id="every_minute_container" class="time_container hide">
                    <label for="Minutes">Minutes: </label>
                    <input type="text" data-name="every_minute.minutes" name="every_minute[minutes]" value="0" class="optin form-control form-control-sm" onkeyup="this.value=this.value.replace(/[^\d]/,'')">
                </div>
                <div id="daily_container" class="time_container hide">
                    <label for="Time">Time: </label>
                    <input type="text" data-name="daily.time" name="daily[time]" value="" class="optin timepicker form-control form-control-sm">
                </div>
                <div id="weekly_container" class="time_container hide">
                    <label for="Time">Time: </label>
                    <input type="text" data-name="weekly.time" name="weekly[time]" value="" class="optin timepicker form-control form-control-sm">

                    <div class="multiple_selection_container">
                        <?php foreach ($week_day_list as $key => $title) { ?>
                            <div class="inner_container">
                                <input type="checkbox" id="<?php echo "day_".$key; ?>" data-name="weekly.day.<?php echo $key; ?>" name="weekly[day][<?php echo $key; ?>]" value="<?php echo $title; ?>" >
                                <label for="<?php echo $title; ?>"><?php echo $title; ?></label><br>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div id="monthly_container" class="time_container hide">
                    <label for="Day">Day: </label>
                    <select data-name="monthly.day" name="monthly[day]" class="optin selectInput form-control form-control-sm">
                        <option value="">Select Day</option>
                        <?php
                            for ($i=1; $i <= 31 ; $i++) { 
                                ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php
                            }
                        ?>
                    </select>

                    <label for="Time">Time: </label>
                    <input type="text" data-name="monthly.time" name="monthly[time]" value="" class="optin timepicker form-control form-control-sm">

                    <div class="multiple_selection_container">
                        <?php foreach ($month_list as $key => $title) { ?>
                            <div class="inner_container">
                                <input type="checkbox" id="<?php echo "month_".$key; ?>" data-name="monthly.month.<?php echo $key; ?>" name="monthly[month][<?php echo $key; ?>]" value="<?php echo $title; ?>" >
                                <label for="<?php echo $title; ?>"><?php echo $title; ?></label><br>
                            </div>
                        <?php } ?>
                    </div>
                </div>
			</div>
		</div>
		<div class="btn_container">
			<button type="button" class="btn btn-primary btn-sm" onClick="timeFormSubmit()">Submit</button>
		</div>
	</form>
</body>
<script type="text/javascript">
	$time_type_list = <?php echo json_encode($time_type_list); ?>;
    $data_set = <?php echo json_encode($data_set); ?>;

	$(document).ready(function(){
		initSection('<?php echo $time_type; ?>');
		$('.time_type_select').change(function(){
			var vl = $(this).val();
			setVal(vl);
		});
	});

	function initSection(val = '') {
		$('#'+val).attr('checked', true);
        var dotNotation = dotNotate($data_set);
        var timeForm = $('#time_form');
        timeForm.inputValues.config({
            attr:'data-name'
        });
        timeForm.inputValues(dotNotation);

		setVal('<?php echo $time_type; ?>');
	}

    function dotNotate(obj,target,prefix) {
      target = target || {},
      prefix = prefix || "";

      Object.keys(obj).forEach(function(key) {
        if ( typeof(obj[key]) === "object" ) {
          dotNotate(obj[key],target,prefix + key + ".");
        } else {
          return target[prefix + key] = obj[key];
        }
      });

      return target;
    }

	function setVal(id = ''){
		$('.time_container').addClass('hide');
		$('#'+id+'_container').removeClass('hide');
	}

	function timeFormSubmit() {
		var timeform = $('#time_form').serializeObject()
        var returnData = generateFinalData(timeform);

        console.log(returnData);
        var isEmpty = checkIsEmpty(returnData);

        if(isEmpty === false) {
		  selectTime('<?php echo $eleid; ?>',returnData);
        } else {
            alert('Please fill required fields value');
        }
	}

    function checkIsEmpty(data) {
        $isEmpty = false;
        if(data && data['time_type'] && data[data['time_type']]) {
            $.each(data[data['time_type']], function( key, value ) {
                if(!$.trim(value).length) {
                    $isEmpty = true;
                } 
            });
        } else {
            $isEmpty = true;
        }

        if(data && data['time_type'] == "weekly") {
            if(data['weekly']['day']) {
                if(data['weekly']['day'].length === 0) {
                    $isEmpty = true; 
                }
            } else {
                $isEmpty = true;   
            } 
        } else if(data && data['time_type'] == "monthly") {
            if(data['monthly']['month']) {
                if(data['monthly']['month'].length === 0) {
                    $isEmpty = true; 
                }
            } else {
                $isEmpty = true;   
            } 
        }
        return $isEmpty;
    }

	function generateFinalData(data) {
        $returnData = {};
        if(data) {
            $.each(data, function( key, value ) {
                if(key == "time_type") {
                    $returnData[key] = value;
                    $returnData[value] = data[value];
                }

                if(key == "one_time" && data[key] && $returnData[key]) {
                    $returnData["title"] = "One Time - "+data[key]['date_time'];
                } else if(key == "every_minute" && data[key] && $returnData[key]) {
                    $returnData["title"] = "At every "+data[key]['minutes']+" Minutes";
                } else if(key == "daily" && data[key] && $returnData[key]) {
                    $returnData["title"] = "Daily at "+data[key]['time'];
                } else if(key == "weekly" && data[key] && $returnData[key]) {
                    var finalDayList = getSelectedItemList(data[key]['day']);
                    $returnData["title"] = "At "+data[key]['time']+" every "+finalDayList.join(", ");
                } else if(key == "monthly" && data[key] && $returnData[key]) {
                    var finalMonthList = getSelectedItemList(data[key]['month']);
                    $returnData["title"] = "At "+data[key]['time']+" on day "+data[key]['day']+" of "+finalMonthList.join(", ");
                }
            });
        }

        return $returnData;
	}

    function getSelectedItemList(data) {
        var finalList = [];
        $.each(data, function( key, value ) {
            finalList.push(value);
        });

        return finalList;
    }

	function selectTime(id, data) {
	    return selTime(id, data);
	}

	$('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    });

    $('.timepicker').datetimepicker({
        datepicker:false,
        timeOnly: true,
        step: 15,
        format:'H:i'
    });
</script>

<script type="text/javascript">
(function($){
    $.fn.serializeObject = function(){

        var self = this,
            json = {},
            push_counters = {},
            patterns = {
                "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
                "push":     /^$/,
                "fixed":    /^\d+$/,
                "named":    /^[a-zA-Z0-9_]+$/
            };


        this.build = function(base, key, value){
            base[key] = value;
            return base;
        };

        this.push_counter = function(key){
            if(push_counters[key] === undefined){
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };

        $.each($(this).serializeArray(), function(){

            // Skip invalid keys
            if(!patterns.validate.test(this.name)){
                return;
            }

            var k,
                keys = this.name.match(patterns.key),
                merge = this.value,
                reverse_key = this.name;

            while((k = keys.pop()) !== undefined){

                // Adjust reverse_key
                reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

                // Push
                if(k.match(patterns.push)){
                    merge = self.build([], self.push_counter(reverse_key), merge);
                }

                // Fixed
                else if(k.match(patterns.fixed)){
                    merge = self.build([], k, merge);
                }

                // Named
                else if(k.match(patterns.named)){
                    merge = self.build({}, k, merge);
                }
            }

            json = $.extend(true, json, merge);
        });

        return json;
    };
})(jQuery);
</script>
</html>