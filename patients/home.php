<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
 require_once("verify_session.php");
 require_once("$srcdir/patient.inc");
 require_once ("lib/portal_pnotes.inc");

 if(!isset($_SESSION['portal_init'])) $_SESSION['portal_init'] = true;
 $whereto = 'profilepanel';
 if( isset($_SESSION['whereto'])){
 	$whereto = $_SESSION['whereto'];
 }
 $user = isset($_SESSION['sessionUser']) ? $_SESSION['sessionUser'] : 'portal user';
 $result = getPatientData($pid);

 $msgs = getPortalPatientNotes($pid);
 $msgcnt = count($msgs);
 $newcnt = 0;
 foreach ( $msgs as $i ) {
 	if($i['message_status']=='New') $newcnt += 1;
 }

 echo "<script>var cpid='" . $pid . "';var cuser='" . $user . "';var webRoot='" . $GLOBALS['web_root'] . "';var ptName='" . $_SESSION['ptName'] . "';</script>";

 require_once '_header.php';
?>

<script type="text/javascript">

$(document).ready(function(){
	$("#profilereport").load("./get_profile.php", { 'embeddedScreen' : true  }, function() {
		$( "table" ).addClass( "table  table-responsive" );
		$( ".demographics td" ).removeClass( "label" );
		$( ".demographics td" ).addClass( "bold" );
		$( ".insurance table" ).addClass( "table-sm table-striped" );
	 	$("#editDems").click( function() {
	 		showProfileModal()
	 		    });
	});
	$("#appointmentslist").load("./get_appointments.php", { 'embeddedScreen' : true  }, function() {
		$("#reports").load("./report/portal_patient_report.php?pid='<?php echo $pid ?>'", { 'embeddedScreen' : true  }, function() {
	   /*  $("#payment").load("./portal_payment.php", { 'embeddedScreen' : true  }, function() {
			}); */
		});
	});
	$("#medicationlist").load("./get_medications.php", { 'embeddedScreen' : true  }, function() {
		$("#allergylist").load("./get_allergies.php", { 'embeddedScreen' : true  }, function() {
			$("#problemslist").load("./get_problems.php", { 'embeddedScreen' : true  }, function() {
				$("#amendmentslist").load("./get_amendments.php", { 'embeddedScreen' : true  }, function() {
					$("#labresults").load("./get_lab_results.php", { 'embeddedScreen' : true  }, function() {

					});
				});
			});
		});
 	});

 /* */

 	$('#openSignModal').on('show.bs.modal', function(e) {
		$('.sigPad').signaturePad({
        	drawOnly: true
     	});
     });
 	$(".generateDoc_download").click( function() {
        $("#doc_form").submit();
    });
	 function showProfileModal(){
		 var title = 'Demographics Legend Red: Charted Values. Blue: Patient Edits ';
		 var params = {
	                buttons: [
					   { text: 'Help', close: false, style: 'info',id: 'formHelp'},
				   	   { text: 'Cancel', close: true, style: 'default'},
				   	   { text: 'Revert Edits', close: false, style: 'danger',id: 'replaceAllButton'},
	                   { text: 'Send for Review', close: false, style: 'success', id: 'donePatientButton'}],
	               size: eModal.size.xl,
	               subtitle: 'Changes take effect after provider review.',
	               title: title,
	               useBin: false,
	                url: './patient/patientdata?pid='+cpid+'&user='+cuser
	            };
	        return eModal.ajax(params)
	            .then(function () { });
	 }
	 function saveProfile(){
		 page.updateModel();
		 //eModal.alert("Send Complete-Waiting for provider review.","Success");
	 }
	 /* $(document.body).on('hidden.bs.modal', function (){
		 	window.location.href = './home.php';
		}); */
/*
 *  a useful iframe popup for future
 */
 /*$("#editProfile").click( function () {
        var title = 'Demographics';
        var params = {
                buttons: [
                   { text: 'Close', close: true, style: 'danger' },
                   { text: 'Send Edits', style: 'success', close: false, click: saveProfile }
                ],
                size:eModal.size.xl,
                title: title,
                url: './patient/patientdata?pid=30'
            };
        return eModal.iframe(params)
            .then(function () { });
    }); */

 	var gowhere = '#<?php echo $whereto?>';
	$(gowhere).collapse('show');

  	var $doHides = $('#panelgroup');
 	$doHides.on('show.bs.collapse','.collapse', function() {
 		$doHides.find('.collapse.in').collapse('hide');
 	});
    //Enable sidebar toggle
    $("[data-toggle='offcanvas']").click(function(e) {
        e.preventDefault();
        //If window is small enough, enable sidebar push menu
        if ($(window).width() <= 992) {
            $('.row-offcanvas').toggleClass('active');
            $('.left-side').removeClass("collapse-left");
            $(".right-side").removeClass("strech");
            $('.row-offcanvas').toggleClass("relative");
        } else {
            //Else, enable content streching
            $('.left-side').toggleClass("collapse-left");
            $(".right-side").toggleClass("strech");
        }
    });
});

</script>
	<!-- Right side column. Contains content of the page -->
	<aside class="right-side">
		<!-- Main content -->
		<section class="container-fluid content panel-group" id="panelgroup">

			<div class="row collapse" id="lists">
				<div class="col-sm-6">
					<div class="panel panel-primary">
						<header class="panel-heading"><?php echo xlt('Medications'); ?> </header>
						<div id="medicationlist" class="panel-body"></div>

						<div class="panel-footer"></div>
					</div>

					<div class="panel panel-primary">
						<header class="panel-heading"><?php echo xlt('Medications Allergy List'); ?>  </header>
						<div id="allergylist" class="panel-body"></div>

						<div class="panel-footer"></div>
					</div>
				</div><!-- /.col -->
				<div class="col-sm-6">
					<div class="panel panel-primary">
						<header class="panel-heading"><?php echo xlt('Issues List'); ?></header>
						<div id="problemslist" class="panel-body"></div>

						<div class="panel-footer"></div>
					</div>
					<div class="panel panel-primary">
						<header class="panel-heading"><?php echo xlt(' Amendment List'); ?> </header>
						<div id="amendmentslist" class="panel-body"></div>

						<div class="panel-footer"></div>
					</div>
				</div><!-- /.col -->
					<div class="col-sm-12">
						<div class="panel panel-primary">
							<header class="panel-heading"><?php echo xlt('Lab Results'); ?>  </header>
							<div id="labresults" class="panel-body"></div>
							<div class="panel-footer"></div>
						</div><!-- /.panel -->
					</div><!-- /.col -->

			</div><!-- /.lists -->

			<div class="row">
				<div class="col-sm-6">
					<div class="panel panel-primary collapse" id="appointmentpanel">
						<header class="panel-heading"><?php echo xlt('Appointments'); ?>  </header>
						<div id="appointmentslist" class="panel-body"></div>
						<div class="panel-footer"></div>
					</div><!-- /.panel -->
				</div><!-- /.col -->
			</div><!-- /.row -->
			<div class="row">
				<div class="col-sm-12">
					<div class="panel panel-primary collapse" id="paymentpanel">
						<header class="panel-heading"> <?php echo xlt('Payments'); ?> </header>
						<div id="payment" class="panel-body"></div>
						<div class="panel-footer">
						</div>
					</div>
				</div><!-- /.col -->
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="panel panel-primary collapse" style="padding-top:0;padding-bottom:0;" id="messagespanel">
						<!-- <header class="panel-heading"><?php //echo xlt('Secure Chat'); ?>  </header>-->
						<div id="messages" class="panel-body" style="padding:0 0 0 0;" >
						<iframe src="./messaging/secure_chat.php" width="100%" height="600"></iframe><!--  -->
						</div>
						<div class="panel-footer">
						</div>
					</div>
				</div><!-- /.col -->
			</div>

			<div class="row">
				<div class="col-sm-8">
					<div class="panel panel-primary collapse" id="reportpanel">
						<header class="panel-heading"><?php echo xlt('Reports'); ?>  </header>
						<div id="reports" class="panel-body"></div>
						<div class="panel-footer"></div>
					</div>

				</div>
				<!-- /.col -->
				<div class="col-sm-6">
					<div class="panel panel-primary collapse" id="downloadpanel">
						<header class="panel-heading"> <?php echo xlt('Download Documents'); ?> </header>
						<div id="docsdownload" class="panel-body">
						<?php if ( $GLOBALS['portal_onsite_document_download'] ) { ?>
							<div>
								<span class="text"><?php echo xlt('Download all patient documents');?></span>
								<form name='doc_form' id='doc_form' action='./get_patient_documents.php' method='post'>
								<input type="button" class="generateDoc_download" value="<?php echo xla('Download'); ?>" />
								</form>
							</div>
						<?php } ?>
							<!-- <div class="input-group" style='padding-top:10px'>
								<span class="input-group-addon" data-toggle="modal" data-backdrop="true" data-target="#openSignModal">Patient Signature</span>
							</div> -->
						</div><!-- /.panel-body -->
						<div class="panel-footer"></div>
					</div>
				</div><!-- /.col -->
			</div>

			<div class="row">
				<div class="col-sm-12">
					<div class="panel panel-primary collapse" id="ledgerpanel">
						<header class="panel-heading"><?php echo xlt('Ledger');?> </header>
						<div id="patledger" class="panel-body"></div>
						<div class="panel-footer">
						  <iframe src="./report/pat_ledger.php?form=1&patient_id=<?php echo $pid;?>" width="100%" height="475" scrolling="yes"></iframe>
						</div>
					</div>
				</div><!-- /.col -->
			</div>

			<div class="row">
				<div class="col-sm-12">
					<div class="panel panel-primary collapse" id="profilepanel">
						<header class="panel-heading"><?php echo xlt('Profile'); ?>
						<!-- <button type="button" id="editDems" class="btn btn-info btn-sm pull-right">Pop-up Edit</button>
						<button type="button" id="editProfile" class="btn btn-info btn-sm pull-right">Pop-up iFrame</button> -->
						</header>
 						<div id="profilereport" class="panel-body"></div>
					<div class="panel-footer"></div>
					</div>
			  </div>
			</div>

		</section>
		<!-- /.content -->
		<!--<div class="footer-main">Onsite Patient Portal Beta v3.0 Copyright &copy By sjpadgett@gmail.com, 2016 All Rights Reserved and Recorded</div>-->
	</aside><!-- /.right-side -->
	</div><!-- ./wrapper -->
<div id="openSignModal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<div class="input-group">
						<span class="input-group-addon"
							onclick="getSignature(document.getElementById('patientSignaturem'))"><em> <?php echo xlt('Show
							Current Signature On File');?><br><?php echo xlt('As appears on documents');?>.</em></span> <img
							class="signature form-control" type="patient-signature"
							id="patientSignaturem" onclick="getSignature(this)"
							alt="Signature On File" src="">
						<!-- <span class="input-group-addon" onclick="clearSig(this)"><i class="glyphicon glyphicon-trash"></i></span> -->
				</div>
				<!-- <h4 class="modal-title">Sign</h4> -->
			</div>
			<div class="modal-body">
				<form name="signit" id="signit" class="sigPad">
					<input type="hidden" name="name" id="name" class="name">
					<ul class="sigNav">
						<label style='display: none;'><input style='display: none;'
							type="checkbox" class="" id="isAdmin" name'="isAdmin" /><?php echo xlt('Is
							Examiner Signature');?></label>
						<li class="clearButton"><a href="#clear"><button><?php echo xlt('Clear Signature');?></button></a></li>
					</ul>
					<div class="sig sigWrapper">
						<div class="typed"></div>
						<canvas class="spad" id="drawpad" width="765" height="325"
							style="border: 1px solid #000000; left: 0px;"></canvas>
						<img id="loading"
							style="display: none; position: absolute; TOP: 150px; LEFT: 315px; WIDTH: 100px; HEIGHT: 100px"
							src="sign/assets/loading.gif" /> <input type="hidden" id="output"
							name="output" class="output">
					</div>
					<input type="hidden" name="type" id="type"
						value="patient-signature">
					<button type="button" onclick="signDoc(this)"><?php echo xlt('Acknowledge as my Electronic Signature');?>.</button>
				</form>
			</div>
		</div>
		<!-- <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div> -->
	</div>
</div><!-- Modal -->
<img id="waitend"
	style="display: none; position: absolute; top: 100px; left: 260px; width: 100px; height: 100px"
	src="sign/assets/loading.gif" />


</body>
</html>
