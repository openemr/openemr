<?php

require_once(dirname(__FILE__, 2) . "/interface/globals.php");

use OpenEMR\Core\Header;

?>

<html>
<head>
	<meta charset="utf-8" />
	<title>Voice Record</title>
  	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'oemr_ad']); ?>

  	<script src="<?php echo $GLOBALS['web_root']; ?>/record/assets/js/script.js"></script>
  	<link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/record/assets/css/styles.css">

    <script type="text/javascript">
        function selnotevalue() {
            if (opener.closed || ! opener.setnotevalue)
                alert("<?php echo xlt('The destination form was closed; I cannot act on your selection.'); ?>");
            else
                var notevalue = document.getElementById('note_active');
                console.log(notevalue.value);
                opener.setnotevalue(notevalue.value);
                dlgclose();
            return false;
        }

        $(document).ready(function() {
            var targetNode = document.querySelector('.recordSection');
            var observer = new MutationObserver(function(){
                if(targetNode.style.display == 'none'){
                   selnotevalue();
                }
            });
            observer.observe(targetNode, { attributes: true, childList: true });
        });
    </script>

    <style type="text/css">
        .recordSection {
            display: block;
            position: relative;
            background-color: transparent;
            height: auto;
            width: auto;
        }

        #note_active {
            /*display: none;*/
        }
    </style>
 </head>
 <body>
 	<div class="recordSection">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <div class="text-center rec-section">
                        <span class=" btn-block-record" id="start-record-btn" >
                            <div class="box">
                              <div class="circle_ripple circle_ripple1"></div>
                               <div class="circle_ripple-2 circle_ripple-22"></div>
                              <div class="circles circles1">
                                <div class="circles-2 circles-22">
                                  <i class="fa fa-microphone" aria-hidden="true"></i>
                                </div>
                              </div>
                            </div>
                        </span>
                       <br>
                       <br>
                             <br>
                       <br>
                            
                        <button class="btn btn-primary btn-sm btn-round" id="stopDictation">Stop Recording</button>
                       
                        <button class="btn btn-primary btn-sm btn-round" id="pause-record-btn">Pause</button>
                       
                        <button class="btn btn-primary btn-sm btn-round" id="save-note-btn" >Save </button>
                       
                        <button class="btn btn-primary btn-sm btn-round " id="clearDectation">Clear</button>
                        <br>
                        <!-- <p contenteditable class="result-box note p-2 no-resize" id="note-textarea"  style="color: #000;" placeholder="Your notes..." ></p> -->
                        <textarea class="result-box note p-2 no-resize form-control" name="note-textarea" id="note-textarea" value=""  placeholder="Your notes..." rows="8"></textarea>
                    
                        <p id="recording-instructions">Press the <strong>Start Recognition</strong> button and allow access.</p>
                
                        <h3>Recent Notes</h3>
                        <ul id="notes">
                            <li>
                                <p class="no-notes">You don't have any notes.</p>
                            </li>
                        </ul>
                    </div> 
                </div>
                <div class="col-lg-2"></div>
            </div>
        </div>
    </div>
    <textarea id="note_active" class="form-control note-active"></textarea>
 </body>
 </html>