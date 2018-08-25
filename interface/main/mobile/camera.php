<?php

require_once "../../globals.php";
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once $GLOBALS['srcdir']."/../vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php";
require_once "m_functions.php";

$detect             = new Mobile_Detect;
$device_type        = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version     = $detect->getScriptVersion();

$uspfx              = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_mFind      = prevSetting('', 'setting_mFind', 'setting_mFind', 'byRoom');
$setting_mRoom      = prevSetting('', 'setting_mRoom', 'setting_mRoom', '');
$setting_mCategory  = prevSetting('', 'setting_mCategories', 'setting_mCategories', '');

if (($_POST['setting_new_window']) ||
    ($_POST['setting_mFind'])) {
    exit();
}

if (!empty($_GET['desktop'])) {
    $desktop = $_GET['desktop'];
} else {
    $desktop        = "";
}
$categories         = array();
$doc                = array();
$display            = "photo";
$pid                = "";

// If “Go to full website” link is clicked, redirect mobile user to main website
if (!empty($_SESSION['desktop']) || ($device_type == 'computer')) {
    $desktop_url = $GLOBALS['webroot']."/interface/main/tabs/main.php";
    header("Location:" . $desktop_url);
}

/**
 * We have a preference to take the photo of the person in a specific room
 * Who is in that room?  Is there more than one 'cause patient_tracker is not up-to-date?
 * We need the fname,lname and pid(s) for person(s) in Room X.
 * Let's get those data points now.
 */
if (($setting_mFind == 'byRoom') && (!empty($setting_mRoom))) {
    $query = "select fname,lname,pid from patient_data
              where pid in (
                SELECT pc_pid FROM `openemr_postcalendar_events`
                where pc_room=? and pc_apptstatus in (
                  SELECT option_id FROM list_options
                  WHERE list_id = 'apptstat' and toggle_setting_1 ='1' and activity='1'
                ) and
                pc_eventDate = CURDATE() )";
    $results_byRoom = sqlStatement($query, array($setting_mRoom));
}

?><!doctype html>
<html style="cursor: pointer;">
<?php
    common_head();
?>
<style>
    #autocomplete {
        background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
        border: 1px solid rgba(0, 0, 0, 0.25);
        border-radius: 4px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.4) inset, 0 1px 0 rgba(255, 255, 255, 0.1);
        color: #fff;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.796), 0 0 10px rgba(255, 255, 255, 0.298);
        padding: 10px;
        margin: 10px 0;
    }
    input[type="file"] {
        display: none;
    }
    .custom-file-upload {
        border: 1px solid #ccc;
        display: inline-block;
        padding: 12px 12px;
        cursor: pointer;
        border-radius: 5px;
        margin: 8px auto;
        text-align: center;
       background-color: #2d98cf66;
        box-shadow: 1px 1px 3px #c0c0c0;
    }
    .fa {
        padding-right:2px;
    }
    #preview {
        text-align: center;
    }
    #preview  img {
        vertical-align: top;
        width: 85%;
        margin: 0px auto;
    }
    obj, audio, canvas, progress, video {
        margin:2%;
        max-width: 8em;
        vertical-align: top;
        text-align: center;
    }
    label {
        margin:5px;
        padding:5px 20px;
        box-shadow: 1px 1px 2px #938282;
    }
    label input {
        padding:left:30px;
    }
    .byCatDisplay {
        display:none;
    }
    .btn {
        font-size: 1.5rem;
    }
    .card-title {
        overflow:hidden;
    }
    .jumbotronA {
        min-height:400px;
        margin:8px;
    }
    @media (min-width:1200px){
        .auto-clear .col-lg-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-lg-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-lg-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-lg-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-lg-6:nth-child(odd){clear:left;}
    }
    @media (min-width:992px) and (max-width:1199px){
        .auto-clear .col-md-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-md-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-md-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-md-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-md-6:nth-child(odd){clear:left;}
    }
    @media (min-width:768px) and (max-width:991px){
        .auto-clear .col-sm-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-sm-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-sm-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-sm-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-sm-6:nth-child(odd){clear:left;}
    }
    @media (max-width:767px) {
        .auto-clear .col-xs-1:nth-child(12n+1) {clear: left;}
        .auto-clear .col-xs-2:nth-child(6n+1) {clear: left;}
        .auto-clear .col-xs-3:nth-child(4n+1) {clear: left;}
        .auto-clear .col-xs-4:nth-child(3n+1) {clear: left;}
        .auto-clear .col-xs-6:nth-child(odd) {clear: left;}
        .jumbotronA {display:none;margin: 8px auto;}
        #head_img {margin: 2vH 0 0 0;max-height: 15vH;}
    }
    
    @media (max-width:400px){
            .auto-clear .col-xs-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-xs-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-xs-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-xs-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-xs-6:nth-child(odd){clear:left;}
            .jumbotronA {display:none;margin: 8px auto;}
            #head_img {margin: 2vH 0 0 0;max-height: 10vH;}
    }
    .section_title {font-size:1.2em;text-decoration:underline;font-weight:600;margin-bottom:8px;}
</style>
<script>
    var projects = [
        {
            label: "<?php echo xlt('Select Document Category'); ?>"
        }<?php
        $categories =  sqlStatement("Select * from categories");
        
        while ($row1 = sqlFetchArray($categories)) {
            echo ',
                {
                    label: "'.text($row1['name']).'",
                    catID: "'.text($row1['id']).'"
                }';
        }
        ?>
    ];
    
    
    var reply = [];
    <?php
    if (!empty($setting_mRoom)) {
        echo "var mRoom = ".text($setting_mRoom).";";
    } else {
        echo "var mRoom;";
    }
    ?>
</script>

<body style="background-color: #fff;" >
<?php common_header($display); ?>

<div id="gb-main" class="container-fluid">
    <form id="save_media" name="save_media" action="#" method="post" enctype="multipart/form-data">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                <img src="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/photobooth.png" id="head_img" alt="<?php echo xla('File Uploader'); ?>">
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
                <div class="row text-center">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 custom-file-upload">
                        <span class="section_title"><?php echo xlt('Select Patient'); ?></span>
                        <br />
                        <div class="btn-group" data-toggle="buttons">
                            <button class="btn btn-primary" id="byRoom"><?php echo xlt('By Room'); ?></button>
                            <button class="btn btn-primary" id="byName"><?php echo xlt('By Name'); ?></button>
                        </div>
                        <br /><br />
                        <select id="findRoom" name="findRoom" type="text"
                                class="form-control byNameDisplay">
                            <?php
                                $rows = sqlStatement("SELECT * FROM list_options WHERE " .
                                    "list_id = ? AND activity = 1 ", array('patient_flow_board_rooms'));
                                while ($row = sqlFetchArray($rows)) {
                                    $selected = ( ($row['option_id'] == $setting_mRoom) ? ' selected' : '');
                                    echo "<option value='" . attr($row['option_id']) ."' ".$selected.">". text($row['title']) . "</option>\n";
                                }
                            ?></select>
                        <?php
                            /**
                             * Logic to select patient who owns this data we are going to upload
                             */
                            
                        if (!empty($results_byRoom)) {
                            //we have a preference to select patient by Room, and we have a room, and we know who is in it!
                            // $results_byRoom is the array holding these answers...
                            $size = sqlNumRows($results_byRoom);
                            if ($size == '1') {
                                $row = sqlFetchArray($results_byRoom);
                                $occupant = text($row['fname'] . " " . $row['lname']);
                                $pid = $row['pid'];
                                $patList_visible = "style='display:none;'";
                                $patList = "<option value='".attr($row['pid'])."'>".$occupant."</option>\n";
                            } elseif ($size > '1') {
                                //build a select list
                                while ($row = sqlFetchArray($results_byRoom)) {
                                    $patList .= "<option value='".attr($row['pid'])."'>".text($row['fname']." ".$row['lname'])."</option>\n";
                                }
                            }
                        } else {
                            $patList_visible = "style='display:none;'";
                        }
                        ?>
                        
                        <input id="findPatient" name="findPatient" type="text" class="form-control ui-autocomplete-input byNameDisplay"
                               placeholder="<?php echo xla("Patient Name"); ?>" value="<?php echo $occupant; ?>" />


                        <select id="patient_matches" name="patient_matches" type="text" <?php echo $patList_visible; ?>
                                class="form-control byNameDisplay">
                            <?php
                                echo $patList;
                            ?>
                        </select>

                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 custom-file-upload">
                        <span class="section_title"><?php echo xlt('Document Category'); ?></span><br /><br />
                        <select id="category" name="category" class="form-control ui-autocomplete-input">
                            <?php
                                $categories =  sqlStatement("Select * from categories");
                            while ($cat = sqlFetchArray($categories)) {
                                if ($cat['name'] == 'Categories') {
                                    continue;
                                }
                                $selected = ( ($cat['id'] == $setting_mCategory) ? ' selected' : '');
                                echo '<option value="'.attr($cat['id']).'" '.$selected.'>'. text($cat['name']) .'</option>\n';
                            }
                            ?>
                        </select>
                               <label for="file-upload-a" class="btn btn-primary">
                                    <i class="fa fa-camera"></i> <?php echo xlt('Photo'); ?>
                                </label>
                                <label for="file-upload-b" class="btn btn-primary"><i class="fa fa-film"></i> <?php echo xlt('Video'); ?>
                                </label>
        
                                <label for="file-upload-c" class="btn btn-primary">
                                    <i class="fa fa-cloud-upload"></i> <?php echo xlt('Other'); ?>
                                </label>
                                <input type="hidden" id="pid" name="pid" value="<?php echo attr($pid); ?>" />
                                <input type="hidden" id="go" name="go" value="save_media" />
                            
                    </div>
                    <div id="div_response" class="text-center"></div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7 col-sm-offset-1 col-md-offset-1 col-lg-offset-1 jumbotronA custom-file-upload">
                <div id="preview" class=""></div>
            </div>
        </div>
        <input type="file" id="file-upload-a" name="file2" accept="image/*" capture="camera" onchange="handleFiles(this.files);"  />
        <input type="file" id="file-upload-b" name="file3" accept="video/*" capture="camera" onchange="handleFiles(this.files);" />
        <input type="file" id="file-upload-c" name="file4" onchange="handleFiles(this.files);" multiple />
    </form>
    
    
    <?php common_footer($display); ?>

</div>
<br />
&nbsp;
<br />
<script>
    
    function send_form(files) {
        console.log(files);
        var category = $("#category").val();
        var pid = $("#pid").val();
        
        if ( (pid <='0')||(category <='0')) {
            alert("<?php echo xla('Please select a patient and a category'); ?>");
            return;
        }
        var formData = new FormData();
        formData.append('go', 'save_media');
        formData.append('pid', $("#pid").val());
        formData.append('category', $("#category").val());
        jQuery.each(files, function(i, file) {
            formData.append('file-'+i, file);
        });
        
        var url = "<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_save.php";
        top.restoreSession();
        $("#div_response").html('<span style="color:red;"><?php echo xla('loading'); ?>...</span>');
        $.ajax({
                   type: 'POST',
                   url: url,
                   headers: { "cache-control": "no-cache" },
                   data: formData,
                   cache: false,
                   contentType: false,
                   processData: false
                   
               }).done(function (result) {
                    reply = JSON.parse(result);
                   $("#div_response").html('<span style="color:red;">' + reply.message + '.</span>');
                   
        });
        
    }
    
    function handleFiles(files) {
        send_form(files);
        $(".jumbotronA").show();
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            if (file.type.startsWith('image/')) {
                var img = document.createElement("img");
                img.classList.add("obj");
                img.file = file;
                
                var card = document.createElement('div');
                card.classList.add('card');
                card.appendChild(img);
                
                var named = document.createElement('b');
                named.classList.add('card-title');
                named.insertAdjacentHTML('beforeend', file.name+"<br />");
                
                var card_body = document.createElement('div');
                card_body.classList.add('card-body');
                card_body.appendChild(named);
                card.appendChild(card_body);
                
                var wrapper = document.createElement('div');
                wrapper.classList.add('col-xs-6','col-sm-4','col-md-4','col-md-offset-1','col-lg-4','text-center','custom-file-upload');
                wrapper.appendChild(card);
                
                preview.appendChild(wrapper); // Assuming that "preview" is the div output where the content will be displayed.
                
                var reader = new FileReader();
                reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
                reader.readAsDataURL(file);
                
            } else if (file.type.startsWith('video/')) {
                var obj = document.createElement('video');
                $(obj).attr('class', 'video-js vjs-default-skin');
                $(obj).attr('controls','');
    
                var card = document.createElement('div');
                card.classList.add('card');
                card.appendChild(obj);
    
                var named = document.createElement('b');
                named.classList.add('card-title');
                named.insertAdjacentHTML('beforeend', file.name+"<br />");
    
                var card_body = document.createElement('div');
                card_body.classList.add('card-body');
                card_body.appendChild(named);
                card.appendChild(card_body);
    
                var wrapper = document.createElement('div');
                wrapper.classList.add('col-xs-6','col-sm-4','col-md-4','col-md-offset-1','col-lg-4','text-center','custom-file-upload');
                wrapper.appendChild(card);
    
                preview.appendChild(wrapper); // Assuming that "preview" is the div output where the content will be displayed.
                
                var source = document.createElement('source');
                
                var reader = new FileReader();
                reader.onload = (function (source) {
                    return function (e) {
                        source.src = e.target.result;
                    };
                })(obj);
                reader.readAsDataURL(file);
                $(source).attr('type', file.type );
                $(obj).append(source);
                
            } else  {
                
                var obj = document.createElement('div');
           
                var card = document.createElement('div');
                card.classList.add('card');
                card.appendChild(obj);
    
                var named = document.createElement('a');
                named.classList.add('card-title');
                named.href = "<?php echo $GLOBALS['webroot']; ?>/controller.php?document&view&patient_id="+pid+"&doc_id="+reply.DOC_ID;
                named.insertAdjacentHTML('beforeend', file.name.substring(0,5)+'...');
                
                var card_body = document.createElement('div');
                card_body.classList.add('card-body');
                card_body.appendChild(named);
                card.appendChild(card_body);
    
                var wrapper = document.createElement('div');
                wrapper.classList.add('col-xs-6','col-sm-4','col-md-4','col-md-offset-1','col-lg-4','text-center','custom-file-upload');
                wrapper.appendChild(card);
    
                preview.appendChild(wrapper); // Assuming that "preview" is the div output where the content will be displayed.
            }
        }
        //files.reset();
        $(".fbar").height(0);
    }
    function label_enable() {
        if ( ($("#pid").val() <='0')||( $("#category").val() <='0') ) {
            $("label").addClass('disabled');
            $("[name^=upload]").prop('disabled', true);
        } else {
            $("label").removeClass('disabled');
            $("[name^=upload]").prop('disabled', false);
        }
    }
    
    $(document).ready(function () {
        $( "#findPatient" ).hide();
        <?php
        if ($setting_mFind=="byRoom") { ?>
            $("#findRoom").show();
            <?php
            if ($size == 1) { ?>
                $( "#findPatient" ) . show();
                $( "#patient_matches" ).hide();
                pid = $( "#patient_matches" ).val();
                 $( "#pid" ).val(pid);
            <?php
            } elseif ($size > 1) {
            //More than one person is in that room...
                ?>
                $( "#patient_matches" ).show();
                pid = $( "#patient_matches" ).val();
                $( "#pid" ).val(pid);
                $( "#patient_matches" ).focus();
                <?php
            } else { ?>
                $( "#patient_matches" ).hide();
                <?php
            }
        }
        
        if ($setting_mFind=="byName") { ?>
            $( "#findPatient" ).show();
            $( "#findRoom" ).hide();
            $( "#findPatient" ).focus();
            <?php
        } ?>
        label_enable();
        
        $("#findPatient").autocomplete({
                                           source: "<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_save.php?go=pat_search&here=2",
                                           minLength: 2,
                                           open: function( event, ui ) {
                                               if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
                                                   $('.ui-autocomplete').off('menufocus hover mouseover mouseenter');
                                               }
                                           },
                                           select: function (event, ui) {
                                               event.preventDefault();
                                               $("#findPatient").val(ui.item.label);
                                               $("#pid").val(ui.item.pid);
                                               label_enable();
                                           }
                                       }).off('mouseenter');
    
        $("#byName").on('click', function() {
            top.restoreSession();
            $("#findPatient").show();
            $("#findRoom").hide();
            $( "#patient_matches" ).hide();
            $( "#findPatient" ).focus();
            $.post("<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/camera.php", {
                'setting_mFind': 'byName',
                success: function (data) {
                    $("#findRoom").hide();
                    $("#findPatient").show();
                }
            });
            label_enable();
        });
        
        $("#category").on('change', function() {
            top.restoreSession();
            var catValue = $("#category").val();
            $.post("<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/camera.php", {
                setting_mCategories: catValue,
                success: function (data) {
                   // alert('cat change ok');
                }
            });
            label_enable();
        });
        
        /**
         * When byRoom is pressed, perform the search on the server.
         *  using the current or default room.
         * Save byRoom as a prefererence
         */
        $("#byRoom").on('click', function() {
            top.restoreSession();
            $.post("<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/camera.php", {
                'setting_mFind': 'byRoom',
                success: function (data) {
                    $("#findPatient").hide();
                    $("#findRoom").show();
                    $( "#patient_matches" ).hide();
                }
            });
            if (mRoom >'') { $( "#findRoom" ).trigger('change'); }
        });
        
        $("#findRoom").on('change', function() {
            $("#findPatient").show();
            top.restoreSession();
            $("#findPatient").val('');
            $("#findPatient").hide();
            $( "#patient_matches" ).hide();
            $("#pid").val('');
            label_enable();
            var url = "<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_save.php";
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {
                           go: 'byRoom',
                           room: $("#findRoom").val(),
                           setting_mRoom: $("#findRoom").val()
                       }
                   }).done(function (result) {
                       $("#div_response").html('');
                       if (result === 'null') { return null;}
                       obj = JSON.parse(result);
                       $("#patient_matches").html('<br />&nbsp;');
                       count = "0";
                       $.map(obj, function(elem) {
                            count++;
                            //need to escape these?
                            $("#patient_matches").append("<option value='" + elem.pid + "'>" + elem.fname + " " + elem.lname + "</option>");
                       });
                       $( "#patient_matches" ).show();
                       $( "#findPatient" ).hide();
                       $("#pid").val($("#patient_matches").val());
                       label_enable();
                   });
        });
        
        $( "#patient_matches" ).on('change', function(e) {
            pid = $( "#patient_matches" ).val();
            $( "#pid" ).val(pid);
            label_enable();
        });
    });
</script>
</body>
</html>