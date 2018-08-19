<?php

require_once('../../globals.php');
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once '/var/www/openemr/vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
include_once("m_functions.php");
require_once("$srcdir/MedEx/API.php");

use OpenEMR\Core\Header;

$MedEx = new MedExApi\MedEx('medexbank.com');

$detect = new Mobile_Detect;
$device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version = $detect->getScriptVersion();

$desktop ="";
$categories = array();
$display="cam";
$doc =array();

if (!empty($_GET['desktop'])) {
    $desktop = $_GET['desktop'];
}

// If “Go to full website” link is clicked, redirect mobile user to main website
if (!empty($_SESSION['desktop']) || ($device_type == 'Xphone')) {
    $desktop_url = $GLOBALS['webroot']."/interface/main/tabs/main.php";
    header("Location:" . $desktop_url);
}

if ($GLOBALS['medex_enable'] == '1') {
    $logged_in = $MedEx->login();
    $MedEx->display->SMS_bot($logged_in);
    exit;
} else {

}

if ( ($setting_mFind == 'byRoom') && (!empty($setting_mRoom)) ) {
    //we have a preference to take the photo of the person in a specific room
    //who is in that room?  Is there more than one 'cause patient_tracker is not up-to-date?
    //presume one for now and select them without user input.
    //we need the fname,lname and pid for person in Room X
    $query = "select fname,lname,pid from patient_data
              where pid in (
                SELECT pc_pid FROM `openemr_postcalendar_events`
                where pc_room=? and pc_apptstatus in (
                  SELECT option_id FROM list_options
                  WHERE list_id = 'apptstat' and toggle_setting_1 ='1' and activity='1'
                ) and
                pc_eventDate = CURDATE() )";
    $results_byRoom = sqlStatement($query,array($setting_mRoom));
}

?><!doctype html>
<html style="cursor: pointer;" lang="en">
<?php
    common_head();
?>
<style>
    #PhotoBooth {
        margin: 2vH;
        max-height: 15VH;
    }
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
        Xheight: 190px;
        background-color: #2d98cf66;
        box-shadow: 1px 3px 9px #c0c0c0;
    }
    .fa {
        padding-right:2px;
    }
    #preview {
        text-align: center;
    }
    #preview  img {
        margin:2%;
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

    .closeButton {
        display:inline-block;
        float:right;
        position:absolute;
        top:8px;
        right:8px;
        padding: 0px 5px;
        font-size:12px;
    }
    .closeButton:hover {
        cursor:pointer;
        border: 0 solid grey;
        text-decoration: none;
        background-color: yellow;
    }
    .closeButton:hover a {
        text-decoration: none;
        background-color: yellow;
    }
    .closeButton a:link {
        outline:none;
        color:black;
    }
    label input {
        padding:left:30px;

    }
    .radio_cats {
        text-align: center;
        vertical-align: text-top;
        max-width: 30%;
        display: inline-block;
        padding: 0px 2px;
    }

    .byCatDisplay {
        display:none;
    }
    .btn {
        font-size: 1.5rem;
    }
    
    #SMS_phone {
        width:100%;
        height:700px;
    }

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
    
    var mRoom;
    var reply;
    <?php
    if (!empty($setting_mRoom)) {
        echo "var mRoom = ".text($setting_mRoom).";";
    }
    ?>
</script>

<body style="background-color: #F8EDDF;" >
<?php common_header("photo"); ?>
<div id="sma-main" class="">
    <form id="save_media" name="save_media" action="" method="post" enctype="multipart/form-data">
        <div class=" col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
        <iframe id="SMS_phone" src="https://oculoplasticsllc.com/openemr/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&dir=back&show=new"></iframe>
        </div>
        <div class="hidden-xs col-sm-8 col-md-8 col-lg-8 jumbotron" style="min-height:700px;background-color: #2d98cf66;box-shadow: 1px 3px 9px #c0c0c0;">
            <div id="preview" class="">
                <br />
            </div>
        </div>
               
    
                <hr />
    
    
            <input id="file-upload-c" name="file" type="file" onchange="handleFiles(this.files)" />
            <input type="hidden" id="pid" name="pid" value="<?php echo attr($pid); ?>" />
        
    </form>
</div>

<?php common_footer($display); ?>

</div>

<script>
    
    function send_form() {
        //alert('Testing values first needed.');
        var category = $("#category").val();
        var pid = $("#pid").val();
        if ( (pid =='')||(category =='')) {
            alert("Please select a patient and a category");
            return;
        }
        var form = document.forms.namedItem("save_media");
        var formData = new FormData(form);
        formData.append("go","save_media");
        var url = "<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_save.php";
        top.restoreSession();
        $.ajax({
                   type: 'POST',
                   url: url,
                   data: formData,
                   cache: false,
                   contentType: false,
                   processData: false
               }).done(function (result) {
                    reply = JSON.parse(result);
                    //alert(reply.message+ " = "+reply.DOC_ID);
                    $("#div_response").html('<span style="color:red;">' + reply.message + '.</span>');
                    setTimeout(function () {
                        $("#div_response").html('<br />&nbsp;');
                    }, 4000);
        });
    }
    
    function handleFiles(files) {
        send_form();
        //alert("sending"+files.length);
        console.log(FileList);
        console.log(files);

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            //alert(file.type);
            
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
                wrapper.classList.add('col-xs-3','col-sm-3','col-md-3','col-md-offset-1','col-lg-4','text-center','custom-file-upload');
                wrapper.appendChild(card);
                
                preview.appendChild(wrapper); // Assuming that "preview" is the div output where the content will be displayed.
                
                var reader = new FileReader();
                reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
                reader.readAsDataURL(file);
            }
            
            if (file.type.startsWith('video/')) {
                var obj = document.createElement('video');
                //$(obj).attr('id', 'example_video_'+Math.random() );
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
                wrapper.classList.add('col-xs-3','col-sm-3','col-md-3','col-md-offset-1','col-lg-4','text-center','custom-file-upload');
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
                
                //preview.append(obj);
            } else  {
                
                var obj = document.createElement('div');
                //$(obj).attr('id', 'example_video_'+Math.random() );
                //$(obj).attr('class', 'video-js vjs-default-skin');
                //$(obj).attr('controls','');
    
                var card = document.createElement('div');
                card.classList.add('card');
                card.appendChild(obj);
    
                var named = document.createElement('a');
                named.classList.add('card-title');
                named.href = "<?php echo $$GLOBALS['webroot']; ?>/controller.php?document&view&patient_id="+pid+"&doc_id="+reply['DOC_ID'];
                named.insertAdjacentHTML('beforeend', file.name);
                
                var card_body = document.createElement('div');
                card_body.classList.add('card-body');
                card_body.appendChild(named);
                card.appendChild(card_body);
    
                var wrapper = document.createElement('div');
                wrapper.classList.add('col-xs-3','col-sm-3','col-md-3','col-md-offset-1','col-lg-4','text-center','custom-file-upload');
                wrapper.appendChild(card);
    
                preview.appendChild(wrapper); // Assuming that "preview" is the div output where the content will be displayed.
    
                var source = document.createElement('source');
                
                var blob = new Blob([file], { type: file.type });
                var objectUrl = URL.createObjectURL(blob);
                
                var reader = new FileReader();
                /*reader.onload = (function (source) {
                    return function (e) {
                        source.src = e.target.result;
                    };
                })(obj);*/
                reader.onload = receivedText;
                arrayBuffer =  reader.readAsBinaryString(file);
                reader.readAsBinaryString(file);
                $(source).attr('type', file.type );
                $(obj).append(source);
            }
        }
        $(".fbar").height(0);
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
        } else if ($size > 1) {
        ?>
        $( "#patient_matches" ).show();
        pid = $( "#patient_matches" ).val();
        $( "#pid" ).val(pid);
        <?php
        } else { ?>
        $( "#patient_matches" ).hide();
        <?php
        }
        }
        
        if ($setting_mFind=="byName") { ?>
        $( "#findPatient" ).show();
        $( "#findRoom" ).hide();
        <?php } ?>
        
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
                                           }
                                       }).off('mouseenter');
        
        $("#upload").on('click', function (e) {
            //alert("Uploading");
            //send_form();
        });
        
        $("#byName").on('click', function() {
            top.restoreSession();
            $("#findPatient").show();
            $("#findRoom").hide();
            $( "#patient_matches" ).hide();
            $.post("<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/camera.php", {
                'setting_mFind': 'byName',
                success: function (data) {
                    $("#findRoom").hide();
                    $("#findPatient").show();
                }
            });
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
        });
        /**
         * When byRoom is pressed, if a room preference exists, perform the search on the server.
         * If not don't until #findRoom changes.
         * Save byRoom as the prefererence
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
            var url = "<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_save.php";
            $.ajax({
                       type: 'POST',
                       url: url,
                       // Form data
                dataType : 'json',
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
                    $("#patient_matches").append("<option value='" + elem.pid + "'>" + elem.fname + " " + elem.lname + "</option>");
                });
                $( "#patient_matches" ).show();
                $( "#findPatient" ).hide();
            });
        });
        
        $( "#patient_matches" ).on('change', function(e) {
            pid = $( "#patient_matches" ).val();
            $( "#pid" ).val(pid);
        });
    });
</script>
</body>
</html>