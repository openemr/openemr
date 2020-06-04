<?php

/**
 * forms/eye_mag/help.php
 *
 * Help File for Shorthand Entry Technique on the Eye Form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/api.inc");

use OpenEMR\Core\Header;

$form_folder = "eye_mag";
$showit    = $_REQUEST['zone'];
if ($showit == '') {
    $showit = "general";
}

if ($showit == 'ext') {
    $showit = "external";
}
?>
<html>
    <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Eye Exam Help" />
    <meta name="author" content="openEMR: ophthalmology help" />
    <?php Header::setupHeader(); ?>
    </head>
    <body>
        <!-- Navbar Section -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="<?php echo $GLOBALS['webroot']; ?>/sites/default/images/login_logo.gif" width="30" height="30" alt="">
                    OpenEMR: Eye Exam <span class="font-weight-bold">Shorthand Help</span>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbar">
                    <div class="navbar-nav">
                        <a class="nav-item nav-link active" id="general_button" href="#">Introduction<span class="sr-only">(current)</span></a>
                        <a class="nav-item nav-link" id="hpi_button" href="#">HPI</a>
                        <a class="nav-item nav-link" id="pmh_button" href="#">PMH</a>
                        <a class="nav-item nav-link" id="external_button" href="#">External</a>
                        <a class="nav-item nav-link" id="antseg_button" href="#">Anterior Segment</a>
                        <a class="nav-item nav-link" id="retina_button" href="#">Retina</a>
                        <a class="nav-item nav-link" id="neuro_button" href="#">Neuro</a>
                    </div>
                </div>
            </div>
        </nav>
        <!-- Content Section -->
        <div class="container" name="container_group" >
            <!-- Introduction Section -->
            <div id="introductionAccordion">
                <!-- Introduction Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#introduction">
                                Introduction: Paper vs. EHR
                            </button>
                        </h5>
                    </div>
                    <div id="introduction" class="collapse show" data-parent="#introductionAccordion">
                        <div class="card-body">
                            <p class="font-weight-bold">"Documenting an exam on paper is faster because we develop our own shorthand."</p>
                            <p>
                                Starting with this "paper" shorthand, we forged an electronic Shorthand, specifically designed for rapid data entry.<br />
                                Using Shorthand, all your findings are entered in one text box,
                                and OpenEMR automatically knows how to store them.
                            </p>
                            <p>The structure is simple: <span class="font-weight-bold">Field:text;Field:text;Field:text</span></p>
                            <p>
                                Click on any <span class="font-weight-bold">Shorthand</span> icon <i class="fa fa-user-md fa-sm fa-2" name="Shorthand_kb" title="Open the Shorthand Window and display Shorthand Codes" data-toggle="tooltip" data-placement="top"></i> in the Eye Form and two things occur:
                            </p>
                            <ol>
                                <li> The Shorthand <span class="font-weight-bold">textbox</span> opens </li>
                                <li> Shorthand <span class="font-weight-bold text-danger">Field</span> names are visible</li>
                            </ol>
                            <p>In the Shorthand textbox, type the <span class="font-weight-bold">Field</span> name, then a colon, followed by your findings.</p>
                            <p>
                                Look around the form - openEMR: Eye Exam is automatically filled.
                                <br />
                                Done. No extra clicks.
                            </p>
                            <hr />
                            <p>
                                This tutorial shows you how to document each area using Shorthand.
                                <br />
                                We'll show you how to complete the HPI, PMH, POH, Medication list, Surgical History and Allergies.
                                <br />
                                As an example, using a handful of lines of typing in the Shorthand textbox,
                                <br />
                                you will document all your normal findings <span class="font-weight-bold">and more than 40 different clinical issues</span>.
                                <br />
                                That's a lot to document and one mighty complicated patient!
                                <br />
                                Combined it may be many more issues than we would see on a routine day, with routine patients, but it could happen...
                                <br />
                                Documenting this many findings would take a little bit of time on paper, and a lifetime in a typical EHR.
                                <br />
                                The average typist can now do it <span class="font-weight-bold">in less than a minute.</span>  A normal encounter can be accurately documented in seconds.
                            </p>
                            <hr />

                            <h4 class="font-weight-bold mt-5">HPI: </h4>
                            <textarea class="form-control">D;CC:"My eyes are tearing and there is a yellow discharge";hpi:The symptoms began last week and the discharged turned yellow yesterday.  No photophobia.  The redness spread from the right to the left eye two days ago.;</textarea>
                            <button class="btn btn-primary mt-2" id="hpi_button2">Details</button>
                            <br />

                            <h4 class="font-weight-bold mt-5">PMH: </h4>
                            <textarea class="form-control">POH: POAG.Myopia. Dry Eye; POS:Phaco/IOL OD 4/4/1994.Phaco/IOL OS 4/24/1995. Yag/PCO OD 6/5/1999;Meds:Timolol 0.5% GFS QHS OU. Latanoprost 0.01% QHS OU. Trazadone 50mg PO QHS.Famvir 500mg PO TID;Surg:Appendectomy 1998. Choly 2010.Lap Band 2014.;All:sulfa - hives.PCN - SOB;</textarea>
                            <button class="btn btn-primary mt-2" id="pmh_button2">Details</button>
                            <br />

                            <h4 class="font-weight-bold mt-5">External: </h4>
                            <textarea class="form-control">D;bll:+2 meibomitis;rll:frank ect, 7x6mm lid margin bcc lat.a;bul:2mm ptosis;rul.+3 dermato.a</textarea>
                            <button class="btn btn-primary mt-2" id="external_button2">Details</button>
                            <br />

                            <h4 class="font-weight-bold mt-5">Anterior Segment:</h4>
                            <textarea class="form-control">D;bc:+2 inj;bk:med pter;rk:mod endo gut.a;bac:+1 fc, +1 pig cells</textarea>
                            <button class="btn btn-primary mt-2" id="antseg_button2">Details</button>

                            <br />
                            <h4 class="font-weight-bold mt-5">Retina:</h4>
                            <textarea class="form-control">D;bd:+2 bowtie pallor;rcup:0.6Vx0.4H w/ inf notch;lcup:0.5;rmac:+2 BDR, +CSME;lmac:flat, tr BDR;v:+PPDR, ++venous beading;rp:ht 1 o,no vh;</textarea>
                            <button class="btn btn-primary mt-2" id="retina_button2">Details</button>

                            <h4 class="font-weight-bold mt-5">Strabismus:</h4>
                            <textarea class="form-control">scDist;5:8ix 1rht;4:10ix;6:6ix;2:15xt;8:5ix;ccDist;4:5ix;5:ortho;6:ortho</textarea>
                            <button class="btn btn-primary mt-2" id="neuro_button2">Details</button>

                            <hr />
                            <p>
                                Below all these lines are strung together. Copy and paste this into a test patient's chart.
                                <br />
                                Voila! HPI, PMH, POH, Medications Allergies and 40 clinical findings + normals, are documented.
                            </p>
                            <hr />

                            <textarea class="form-control" style="height: 13.8rem;">CC:"My eyes are tearing and there is a yellow discharge";hpi:The symptoms began last week and the discharged turned yellow yesterday.  No photophobia.  The redness spread from the right to the left eye two days ago.;
                                POH:POAG. Myopia. Dry Eye; POS: Phaco/IOL OD 4/4/1994.Phaco/IOL OS 4/24/1995. Yag/PCO OD 6/5/1999;Meds:Timolol 0.5% GFS QHS OU. Latanoprost 0.01% QHS OU.
                                Trazadone 50mg PO QHS.Famvir 500mg PO TID;Surg:Appendectomy 1998. Choly 2010.Lap Band 2014.;All:sulfa - hives.PCN - SOB;
                                D;bll:+2 meibomitis;rll:frank ect, 7x6mm lid margin bcc lat.a;bul:2mm ptosis;rul:+3 dermato.a
                                bc:+2 inj;bk:med pter;rk:mod endo gut.a;bac:+1 fc, +1 pig cells;
                                bd:+2 bowtie pallor;rcup:0.6Vx0.4H w/ inf notch;lcup:0.5;rmac:+2 BDR, +CSME;lmac:flat, tr BDR;v:+PPDR, ++venous beading;rp:ht 1 o,no vh;
                                scDist;5:8ix 1rht;4:10ix;6:6ix;2:15xt;8:5ix;ccDist;4:5ix;5:ortho;6:ortho
                            </textarea>

                            <br />
                            <p>Get back to working at the speed of your brain.</p>
                            <br />
                            <small class="text-muted">Now imagine documenting this without typing, without a scribe?  It is not that far away...</small>
                        </div>
                    </div>
                </div>
                <!-- Shorthand Structure Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#shorthand">
                                Shorthand Structure
                            </button>
                        </h5>
                    </div>
                    <div id="shorthand" class="collapse" data-parent="#introductionAccordion">
                        <div class="card-body">
                            <h4>
                                <span class="font-weight-bold">Usage:</span>  field:text(.a)(;)
                            </h4>
                            <div class="ml-4">
                                <p>
                                    <span class="font-italic">where:</span>
                                    <br>
                                    <span class="font-weight-bold">Field</span> is the shorthand term for the clinical field.
                                    <br/>
                                    <span class="font-weight-bold">text</span> is the complete or shorthand data to enter into this <span class="font-weight-bold">field</span>:
                                    <br />
                                    <span class="font-weight-bold">field</span> and <span class="font-weight-bold">text</span> are separated by a "<span class="font-weight-bold"   >:</span>" colon.
                                    <br />
                                    The trailing "<span class="font-weight-bold">.a</span>"
                                    is optional and will <span class="font-weight-bold">append</span> the <span class="font-weight-bold">text</span> to the data already in the field, instead of replacing it.
                                    <br />
                                    The semi-colon "<span class="font-weight-bold">;</span>" is used to divide entries, allowing multiple field entries simultaneously.
                                    <br />
                                    <span class="font-italic text-muted">The semi-colon separates entries.</span>
                                    <br />
                                    After pressing <span class="font-weight-bold">Enter/Return</span> or <span class="font-weight-bold">Tab</span>, the data are submitted and the form is populated.
                                    <br />
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- HPI Section -->
            <div class="d-none" id="hpiAccordion">
                <!-- HPI Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#hpi">
                                History of Present Illness: Shorthand Walk Through
                            </button>
                        </h5>
                    </div>
                    <div id="hpi" class="collapse show" data-parent="#hpiAccordion">
                        <div class="card-body">
                            <h4><u>Shorthand</u></h4>
                            <textarea class="form-control" style="min-height: 6rem;">CC:"My eyes are tearing and there is a yellow discharge";hpi:The symptoms began last week and the discharged turned yellow yesterday.  No photophobia.  The redness spread from the right to the left eye two days ago.;
                            </textarea>
                            <br />
                            <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_hpi.png" class="img-fluid" alt="Shorthand Example: Anterior Segment" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- PMH Section -->
            <div class="d-none" id="pmhAccordion">
                <!-- PMH Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#pmh">
                                Past Medical History: Shorthand Walk Through
                            </button>
                        </h5>
                    </div>
                    <div id="pmh" class="collapse show" data-parent="#pmhAccordion">
                        <div class="card-body">
                            <h4><u>Shorthand</u></h4>
                            <textarea class="form-control" style="height: 6rem;">POH:POAG. Myopia. Dry Eye; POS:Phaco/IOL OD 4/4/1994.Phaco/IOL OS 4/24/1995.
                                Yag/PCO OD 6/5/1999;Meds:Timolol 0.5% GFS QHS OU. Latanoprost 0.01% QHS OU.
                                Trazadone 50mg PO QHS.Famvir 500mg PO TID;Surg:Appendectomy 1998.
                                Choly 2010.Lap Band 2014.;All:sulfa - hives.PCN - SOB;
                            </textarea>
                            <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_pmh.png" class="img-fluid" alt="Shorthand Example: Anterior Segment" />
                            <br />
                        </div>
                    </div>
                </div>
            </div>
            <!-- External Section -->
            <div class="d-none" id="externalAccordion">
                <!-- Shorthand Walk Through Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#externalShorthandWalkThrough">
                                External: Shorthand Walk Through
                            </button>
                        </h5>
                    </div>
                    <div id="externalShorthandWalkThrough" class="collapse show" data-parent="#externalAccordion">
                      <div class="card-body">
                        <h4><u>Shorthand</u></h4>
                        <textarea class="form-control">D;bll:+2 meibomitis;rll:frank ect, 7x6mm lid margin bcc lat.a;bul:2mm ptosis;rul.+3 dermato.a
                        </textarea>
                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_ext.png" class="img-fluid" alt="Shorthand Example: Anterior Segment" />
                        <br />
                      </div>
                    </div>
                </div>
                <!-- Example Output Structure Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#externalExampleOutput">
                                External: Example Output
                            </button>
                        </h5>
                    </div>
                    <div id="externalExampleOutput" class="collapse" data-parent="#externalAccordion">
                        <div class="card-body">
                            <p>
                                Input:
                                <br />
                                <br />
                                <span class="font-weight-bold">D;bll:+2 meibomitis;rll:frank ect, 7x6mm lid margin bcc lat.a;bul:2mm ptosis;rul.+3 dermato.a</span>
                                <br />
                                <br />
                                Output:
                            </p>
                            <br />

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Eye Exam</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_ext_EMR.png" class="img-fluid" width="95%" alt="Shorthand Example: openEMR">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Reports</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_ext_report.png" class="img-fluid" width="75%" alt="Shorthand Example: Reports">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Field Codes and Shorthand/Abbreviations Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#externalFieldCodesAndShorthand">
                                External: Field Codes and Shorthand/Abbreviations
                            </button>
                        </h5>
                    </div>
                    <div id="externalFieldCodesAndShorthand" class="collapse" data-parent="#externalAccordion">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Clinical Field</th>
                                        <th>Shorthand* Field</th>
                                        <th>Example Shorthand**</th>
                                        <th>EMR: Field text</th>
                                    </tr>
                                    <tr>
                                        <td>Default values</td>
                                        <td>D or d</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">d;</span>
                                            <br />
                                            <span class="font-weight-bold text-danger">D;</span>
                                        </td>
                                        <td>
                                            All fields with defined default values are
                                            <span class="font-weight-bold">erased</span>
                                            and filled with default values.
                                            <br />Fields without defined default values are not affected.
                                        </td>
                                    </tr>
                                    <tr >
                                        <td>Default External values</td>
                                        <td>DEXT or dext</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">dext;</span>
                                            <br />
                                            <span class="font-weight-bold text-danger">DEXT;</span>
                                        </td>
                                        <td>
                                            All External Exam fields with defined default values are
                                            <span class="font-weight-bold">erased</span>
                                            and filled with default values.
                                            <br />
                                            External Fields without defined default values and all other fields on the form are not affected.
                                        </td>
                                    </tr>
                                    <tr >
                                        <td>Right Brow</td>
                                        <td>rb or RB</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">rb</span>:1cm lat ptosis
                                            <br />
                                            <span class="font-weight-bold text-danger">rb</span>:med 2cm SCC
                                        </td>
                                        <td>
                                            1cm lateral ptosis
                                            <br />
                                            medial 2cm SCC
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Left Brow</td>
                                        <td>lb or LB</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">lb</span>:loss of lat brow follicles
                                            <br />
                                            <span class="font-weight-bold text-danger">lb</span>:no rhytids from VIIth nerve palsy
                                        </td>
                                        <td>
                                            loss of lateral brow follicles
                                            <br />
                                            no rhytids from VIIth nerve palsy
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Both Brows/Forehead</td>
                                        <td>
                                            fh or FH
                                            <br />
                                            bb or BB
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-danger">fh</span>:+3 fh rhytids
                                            <br />
                                            <span class="font-weight-bold text-danger">BB</span>:+3 glab rhytids
                                        </td>
                                        <td>
                                            +3 forehead rhytids
                                            <br />
                                            +3 glabellar rhytids
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Right Upper Lid</td>
                                        <td>rul or RUL</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">RUL</span>:1cm lat ptosis
                                            <br />
                                            <span class="font-weight-bold text-danger">rul</span>:med 2cm SCC
                                        </td>
                                        <td>
                                            1cm lateral ptosis
                                            <br />
                                            medial 2cm SCC
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Left Upper Lid</td>
                                        <td>lul or LUL</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">LUL</span>:1cm lat ptosis
                                            <br />
                                            <span class="font-weight-bold text-danger">lul</span>:med 2cm SCC
                                        </td>
                                        <td>
                                            1cm lateral ptosis
                                            <br />
                                            medial 2cm SCC
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Right Lower Lid</td>
                                        <td>rll or RLL</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">rll</span>:1cm lat ptosis
                                            <br />
                                            <span class="font-weight-bold text-danger">rll</span>:med 2cm SCC
                                        </td>
                                        <td>
                                            1cm lateral ptosis
                                            <br />
                                            medial 2cm SCC
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Left Lower Lid</td>
                                        <td>lll or LLL</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">lll</span>:0.5cm lat ptosis
                                            <br />
                                            <span class="font-weight-bold text-danger">LLL</span>:med 2cm SCC
                                        </td>
                                        <td>
                                            1cm lateral ptosis
                                            <br />
                                            medial 2cm SCC
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Both Lower Lids</td>
                                        <td>bll or BLL</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">lll</span>:0.5cm lat ptosis
                                            <br />
                                            <span class="font-weight-bold text-danger">LLL</span>:med 2cm SCC
                                        </td>
                                        <td>
                                            1cm lateral ptosis
                                            <br />
                                            medial 2cm SCC
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>All 4 Lids</td>
                                        <td>4xl or 4XL</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">4xl</span>:+2 laxity
                                            <br />
                                            <span class="font-weight-bold text-danger">4xL</span>:+2 dermato
                                        </td>
                                        <td>
                                            +2 laxity
                                            <br />
                                            +2 dermatochalasis
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Right Medial Canthus</td>
                                        <td>rmc or RMC</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">rmc</span>:1cm bcc
                                            <br />
                                            <span class="font-weight-bold text-danger">RMC</span>:healed dcr scar
                                        </td>
                                        <td>
                                            1cm BCC
                                            <br />
                                            healed DCR scar
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Left Medial Canthus</td>
                                        <td>lmc or LMC</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">lmc</span>:acute dacryo, tender w/ purulent drainage
                                            <br />
                                            <span class="font-weight-bold text-danger">lmc</span>:1.2cm x 8mm mass
                                        </td>
                                        <td>
                                            acute dacryo, tender with purulent drainage
                                            <br />
                                            1.2cm x 8mm mass
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Both Medial Canthi</td>
                                        <td>bmc or BMC</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">bmc</span>:chronic dacryo, non-tender
                                            <br />
                                            <span class="font-weight-bold text-danger">BMC</span>:scaling, ulcerated lesion
                                        </td>
                                        <td>
                                            chronic dacryo, non-tender
                                            <br />
                                            scaling, ulcerated lesion
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Right Adnexa</td>
                                        <td>rad or RAD</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">rad</span>:1.8x2.0cm bcc lat
                                            <br />
                                            <span class="font-weight-bold text-danger">RAD</span>:healed DCR scar
                                        </td>
                                        <td>
                                            1cm BCC
                                            <br />
                                            healed DCR scar
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Left Adnexa</td>
                                        <td>lad or LAD</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">lad</span>:1cm lacr cyst protruding under lid
                                            <br />
                                            <span class="font-weight-bold text-danger">LAD</span>:1.2cm x 8mm mass
                                        </td>
                                        <td>
                                            1cm lacrimal cyst protruding under lid
                                            <br />
                                            1.2cm x 8mm mass
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Both Adnexae</td>
                                        <td>bad or BAD</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">bad</span>:lacr gland prolapse
                                            <br />
                                            <span class="font-weight-bold text-danger">BAD</span>:lat orb wall missing
                                        </td>
                                        <td>
                                            lacrimal gland prolapse
                                            <br />
                                            lateral orbital wall missing
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br />
                            <p class="font-italic">
                                *case insensitive
                                <br />
                                **The default action is to replace the field with the new text.
                                <br />
                                Adding <span class="font-weight-bold">".a"</span> at the end of a <span class="font-weight-bold">text</span> section will append the current text instead of replacing it.
                                <br >
                                For example, <span class="font-weight-bold">entering "4xL:+2 meibomitis.a" will <u>append</u> "+2 meibomitis"</span>
                                to each of the eyelid fields, RUL/RLL/LUL/LLL.
                            </p>
                            <hr />

                            <h2 class="underline">External Shorthand Abbreviations:</h2>
                            <p>The following terms will be expanded from their shorthand to full expression in the EMR fields:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Enter this:</th><th>Get this:</th>
                                    </tr>
                                    <tr><td>inf</td><td>inferior</td></tr>
                                    <tr><td>sup</td><td>superior</td></tr>
                                    <tr><td>nas</td><td>nasal</td></tr>
                                    <tr><td>temp</td><td>temporal</td></tr>
                                    <tr><td>med</td><td>medial</td></tr>
                                    <tr><td>lat</td><td>lateral</td></tr>
                                    <tr><td>dermato</td><td>dematochalasis</td></tr>
                                    <tr><td>w/</td><td>with</td></tr>
                                    <tr><td>lac</td><td>laceration</td></tr>
                                    <tr><td>lacr</td><td>lacrimal</td></tr>
                                    <tr><td>dcr</td><td>DCR</td></tr>
                                    <tr><td>bcc</td><td>BCC</td></tr>
                                    <tr><td>scc</td><td>SCC</td></tr>
                                    <tr><td>sebca</td><td>sebaceous cell</td></tr>
                                    <tr><td>tr</td><td>trace</td></tr>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Anterior Segment Section -->
            <div class="d-none" id="anteriorSegmentAccordion">
                <!-- Shorthand Walk Through Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#anteriorSegmentShorthandWalkThrough">
                                Anterior Segment: Shorthand Walk Through
                            </button>
                        </h5>
                    </div>
                    <div id="anteriorSegmentShorthandWalkThrough" class="collapse show" data-parent="#anteriorSegmentAccordion">
                        <div class="card-body">
                            <h4><u>Shorthand</u></h4>
                            <textarea class="form-control">D;bc:+2 inj;bk:med pter;rk:mod endo gut.a;bac:+1 fc, +1 pig cells
                            </textarea>
                            <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_antseg.png" class="img-fluid" alt="Shorthand Example: Anterior Segment" />
                            <br />
                        </div>
                    </div>
                </div>
                <!-- Example Output Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#anteriorSegmentExampleOutput">
                                Anterior Segment: Example Output
                            </button>
                        </h5>
                    </div>
                    <div id="anteriorSegmentExampleOutput" class="collapse" data-parent="#anteriorSegmentAccordion">
                        <div class="card-body">
                            <p>
                                Input:
                                <br />
                                <br />
                                <span class="font-weight-bold">D;bc:+2 inj;bk:med pter;rk:mod endo gut.a;bac:+1 fc, +1 pig cells</span>
                                <br />
                                <br />
                                Output:
                            </p>
                            <br />

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Eye Exam</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_antseg_EMR.png" class="img-fluid" width="90%" alt="Shorthand Example: openEMR">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Reports</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_antseg_report.png" class="img-fluid" width="95%" alt="Shorthand Example: Reports">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Field Codes and Shorthand/Abbreviations Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#anteriorSegmentFieldCodesAndShorthand">
                                Anterior Segment: Field Codes and Shorthand/Abbreviations
                            </button>
                        </h5>
                    </div>
                    <div id="anteriorSegmentFieldCodesAndShorthand" class="collapse" data-parent="#anteriorSegmentAccordion">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Clinical Field</th>
                                        <th>Shorthand* Field</th>
                                        <th>Example Shorthand**</th>
                                        <th>EMR: Field text</th>
                                    </tr>
                                    <tr>
                                        <td>Default values</td>
                                        <td>D or d</td>
                                        <td>
                                            <span class="font-weight-light text-danger">d</span>;
                                            <br />
                                            <span class="font-weight-light text-danger">D</span>;
                                        </td>
                                        <td>
                                            All fields with defined default values are <span class="font-weight-bold">erased</span> and filled with default values.
                                            <br />
                                            Fields without defined default values are not affected.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Default Anterior Segment values</td>
                                        <td>DANTSEG or das</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">dantseg;</span>
                                            <br />
                                            <span class="font-weight-bold text-danger">DAS;</span>
                                        </td>
                                        <td>
                                            All Anterior Segment fields with defined default values are <span class="font-weight-bold">erased</span> and filled with default values.
                                            <br />
                                            Anterior Segment Fields without defined default values and all other fields on the form are not affected.
                                        </td>
                                    </tr>
                                    <tr >
                                        <td>Conjunctiva</td>
                                        <td>
                                            Right = rc
                                            <br />
                                            Left = lc
                                            <br />
                                            Both = bc or c
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-danger">rc:</span>+1 inj
                                            <br />
                                            <span class="font-weight-bold text-danger">c:</span>med pter
                                        </td>
                                        <td>
                                            "+1 injection" (right conj only)
                                            <br />
                                            "medial pterygium" (both right and left fields are filled)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Cornea</td>
                                        <td>
                                            Right = rc
                                            <br />
                                            Left = lc
                                            <br />
                                            Both = bk or k
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rk:</span>+3 spk
                                            <br />
                                            <span class="font-weight-light text-danger">k:</span>+2 end gut<span class="font-weight-bold text-success">;</span><span class="font-weight-light text-danger">rk:</span>+1 str edema<span class="font-weight-bold text-success">.a</span>
                                        </td>
                                        <td>
                                            "+3 SPK" (right cornea only)
                                            <br />
                                            "+2 endothelial guttatae" (both cornea fields) AND "+1 stromal edema" (appended to Right cornea field)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Anterior Chamber</td>
                                        <td>
                                            Right = rac
                                            <br />
                                            Left = lac
                                            <br />
                                            Both = bac or ac
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rac:</span>+1 fc
                                            <br />
                                            <span class="font-weight-light text-danger">ac:</span>+2 flare
                                        </td>
                                        <td>
                                            "+1 flare/cell" (right A/C field only)
                                            <br />
                                            "+2 flare" (both A/C fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Lens</td>
                                        <td>
                                            Right = rl
                                            <br />
                                            Left = ll
                                            <br />
                                            Both = bl or l
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">RL:</span>+2 NS
                                            <br />
                                            <span class="font-weight-light text-danger">ll:</span>+2 NS<span class="text-weight-bold text-success">;</span><span class="font-weight-light text-danger">l:</span>+3 ant cort spokes.a
                                        </td>
                                        <td>
                                            "+2 NS" (right lens only)
                                            <br />
                                            "+2 NS" (left lens fields) AND "+3 anterior cortical spokes" (appended to both lenses)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Iris</td>
                                        <td>
                                            Right = ri
                                            <br />
                                            Left = li
                                            <br />
                                            Both = bi or i
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-danger">bi.</span>12 0 iridotomy
                                            <br />
                                            <span class="font-weight-light text-danger">ri:</span>+2 TI defects<span class="font-weight-bold text-success">.a</span>;<span class="font-weight-light text-danger">li</span>.round
                                        </td>
                                        <td>
                                            "12 o'clock iriditomy" (both iris fields)
                                            <br />
                                            ", +2 TI defects" (right iris field) AND "round" (left iris field only)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Gonio</td>
                                        <td>
                                            Right = rg
                                            <br />
                                            Left = lg
                                            <br />
                                            Both = bg or g
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rg:</span>ss 360
                                            <br />
                                            <span class="font-weight-light text-danger">lg:</span>3-5 o angle rec
                                        </td>
                                        <td>
                                            SS 360
                                            <br />
                                            3-5 o'clock angle recession
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Pachymetry</td>
                                        <td>
                                            Right = rp
                                            <br />
                                            Left = lp
                                            <br />
                                            Both = bp or p
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">lp:</span>625 um
                                            <br />
                                            <span class="font-weight-light text-danger">p:</span>550 um
                                        </td>
                                        <td>
                                            "625 um" (left pachymetry field)
                                            <br />
                                            "500 um" (both pachymetry fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Schirmer I</td>
                                        <td>
                                            Right = rsch1
                                            <br />
                                            Left = lsch1
                                            <br />
                                            Both = bsch1 or sch1
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rsch1:</span>5mm
                                            <br />
                                            <span class="font-weight-light text-danger">sch1:</span>&lt; 10mm/5 minutes
                                        </td>
                                        <td>
                                            "5mm" (right field only)
                                            <br />
                                            "&lt; 10mm/5 minutes" (both fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Schirmer II</td>
                                        <td>
                                            Right = rsch2
                                            <br />
                                            Left = lsch2
                                            <br />
                                            Both = bsch2 or sch2
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rsch2:</span>9 mm
                                            <br />
                                            <span class="font-weight-light text-danger">sch2:</span>&lt; 10mm/5 minutes
                                        </td>
                                        <td>
                                            "9 mm" (right field only)
                                            <br />
                                            "&lt; 10mm/5 minutes" (both fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tear Break-up Time</td>
                                        <td>
                                            Right = RTBUT
                                            <br />
                                            Left = LTBUT
                                            <br />
                                            Both = BTBUT or tbut
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">tbut:</span>&lt; 10 seconds
                                            <br />
                                            <span class="font-weight-light text-danger">Rtbut:</span>5 secs<span class="font-weight-bold text-success">;</span><span class="font-weight-light text-danger">ltbut:</span>9 seconds<span class="font-weight-bold text-success">;</span>
                                        </td>
                                        <td>
                                            "10 seconds" (both fields)
                                            <br />
                                            "5 seconds" (right) AND "9 seconds" (left)
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br />
                            <p class="font-italic">
                                *case insensitive
                                <br />
                                **The default action is to replace the field with the new text.
                                <br />
                                Adding <span class="font-weight-bold">".a"</span> at the end of a <span class="font-weight-bold">text</span> section will append the current text instead of replacing it.
                                <br >
                                For example, entering <span class="font-weight-bold">"bk:+2 str scarring.a" will <u>append</u>"+2 stromal scarring"</span> to both the right (rk) and left cornea fields (lk).
                            </p>

                            <h2><u>External Shorthand Abbreviations:</u></h2>

                            The following terms will be expanded from their shorthand to full expression in the EMR fields:
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                        <tr class="thead-dark">
                                            <th>Enter this:</th>
                                            <th>Get this:</th>
                                        </tr>
                                        <tr><td>inf</td><td>inferior</td></tr>
                                        <tr><td>sup</td><td>superior</td></tr>
                                        <tr><td>nas</td><td>nasal</td></tr>
                                        <tr><td>temp</td><td>temporal</td></tr>
                                        <tr><td>med</td><td>medial</td></tr>
                                        <tr><td>lat</td><td>lateral</td></tr>
                                        <tr><td>dermato</td><td>dematochalasis</td></tr>
                                        <tr><td>w/</td><td>with</td></tr>
                                        <tr><td>lac</td><td>laceration</td></tr>
                                        <tr><td>lacr</td><td>lacrimal</td></tr>
                                        <tr><td>dcr</td><td>DCR</td></tr>
                                        <tr><td>bcc</td><td>BCC</td></tr>
                                        <tr><td>scc</td><td>SCC</td></tr>
                                        <tr><td>sebca</td><td>sebaceous cell</td></tr>
                                        <tr><td>tr</td><td>trace</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Retina Section -->
            <div class="d-none" id="retinaAccordion">
                <!-- Shorthand Walk Through Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#retinaShorthandWalkThrough">
                                Retina: Shorthand Walk Through
                            </button>
                        </h5>
                    </div>
                    <div id="retinaShorthandWalkThrough" class="collapse show" data-parent="#retinaAccordion">
                        <div class="card-body">
                            <h4><u>Shorthand</u></h4>
                            <textarea class="form-control">D;bd.+2 bowtie pallor;rcup.0.6Vx0.4H w/ inf notch;lcup.0.5;rmac.+2 BDR, +CSME;lmac.flat, tr BDR;v.+PPDR, ++venous beading;rp.ht 1 o,no vh;
                            </textarea>
                            <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_retina.png" class="img-fluid" alt="Shorthand Example: Anterior Segment">
                            <br />
                        </div>
                    </div>
                </div>
                <!-- Example Output Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#retinaExampleOutput">
                                Retina: Example Output
                            </button>
                        </h5>
                    </div>
                    <div id="retinaExampleOutput" class="collapse" data-parent="#retinaAccordion">
                        <div class="card-body">
                            <p>
                                Input:
                                <br />
                                <br />
                                <span class="font-weight-bold">D;bd:+2 bowtie pallor;rcup:0.6Vx0.4H w/ inf notch;lcup:0.5;rmac:+2 BDR, +CSME;lmac:flat, tr BDR;v:+PPDR, ++venous beading;rp:ht 1 o,no vh;</span>
                                <br />
                                <br />
                                Output:
                            </p>
                            <br />

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Eye Exam</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_retina_EMR.png" width="95%" alt="Shorthand Example: openEMR" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Reports</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_retina_report.png" width="95%" alt="Shorthand Example: Reports" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Field Codes and Shorthand/Abbreviations Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#retinaFieldCodesAndShorthand">
                                Retina: Field Codes and Shorthand/Abbreviations
                            </button>
                        </h5>
                    </div>
                    <div id="retinaFieldCodesAndShorthand" class="collapse" data-parent="#retinaAccordion">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Clinical Field</th>
                                        <th>Shorthand* Field</th>
                                        <th>Example Shorthand**</th>
                                        <th>EMR: Field text</th>
                                    </tr>
                                    <tr >
                                        <td>Default values</td>
                                        <td>D or d</td>
                                        <td>
                                            <span class="font-weight-light text-danger">d</span>;
                                            <br />
                                            <span class="font-weight-light text-danger">D</span>;
                                        </td>
                                        <td>
                                            All fields with defined default values are <span class="font-weight-bold">erased</span> and filled with default values.
                                            <br />Fields without defined default values are not affected.
                                        </td>
                                    </tr>
                                    <tr >
                                        <td>Default Retina values</td>
                                        <td>DRET or dret</td>
                                        <td>
                                            <span class="font-weight-bold text-danger">dext;</span>
                                            <br />
                                            <span class="font-weight-bold text-danger">DEXT;</span>
                                        </td>
                                        <td>
                                            All Retina/Posterior Segment Exam fields with defined default values are <span class="font-weight-bold">erased</span> and filled with default values.
                                            <br />Retinal Fields without defined default values and all other fields on the form are not affected.
                                        </td>
                                    </tr>
                                    <tr >
                                        <td>Disc</td>
                                        <td>
                                            Right = rd
                                            <br />
                                            Left = ld
                                            <br />
                                            Both = bd
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rd:</span>temp pallor, PPA
                                            <br />
                                            <span class="font-weight-light text-danger">bd:</span>NVD at 5 o
                                        </td>
                                        <td>
                                            "temporal pallor, PPA" (right disc only)
                                            <br />
                                            "NVD at 5 o'clock" (both right and left disc fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Cup</td>
                                        <td>
                                            Right = rcup
                                            <br />
                                            Left = lcup
                                            <br />
                                            Both = bcup or cup
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rcup:</span>0.5 w/ inf notch
                                            <br />
                                            <span class="font-weight-light text-danger">cup:</span>temp scalloping, 0.5<span class="font-weight-bold text-success">.a</span><span class="font-weight-bold text-success">;</span>
                                        </td>
                                        <td>
                                            "0.5 with inferior notch (right cup only)
                                            <br />"temporal scalloping, 0.5" (appended to both cup fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Macula</td>
                                        <td>
                                            Right = rmac
                                            <br />
                                            Left = lmac
                                            <br />
                                            Both = bmac or mac
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rmac:</span>central scar 500um
                                            <br />
                                            <span class="font-weight-light text-danger">mac:</span>soft drusen, - heme.a
                                        </td>
                                        <td>
                                            "central scar 500um" (right macular field only)
                                            <br />
                                            "soft drusen, - heme" (appended to both macular fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Vessels</td>
                                        <td>
                                            Right = rv
                                            <br />
                                            Left = lv
                                            <br />
                                            Both = bv or v
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">RV:</span>1:2, +2 BDR
                                            <br />
                                            <span class="font-weight-light text-danger">lv:</span>+CSME w/ hard exudate sup to fov (300um)<span class="font-weight-bold text-success">;</span>
                                            <br />
                                            <span class="font-weight-light text-danger">v:</span>narrow arterioles, 1:2<span class="font-weight-bold text-success">.a;</span>
                                        </td>
                                        <td>
                                            "1:2, +2 BDR" (right vessels only)
                                            <br />
                                            "+CSME with hard exudate superior to fovea (300um)" (left vessel field only)
                                            <br />
                                            "narrow arterioles, 1:2" (appended to both vessel fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Periphery</td>
                                        <td>
                                            Right = rp
                                            <br />
                                            Left = lp
                                            <br />
                                            Both = bp or p
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rp:</span>12 0 ht, no heme, amenable to bubble<span class="font-weight-bold text-success">;</span>
                                            <br />
                                            <span class="font-weight-bold text-danger">bp.</span>1 clock hour of lattice 2 o<span class="font-weight-bold text-success">.a</span><span class="font-weight-bold" style="color:navy">;</span>
                                        </td>
                                        <td>
                                            "12 o'clock horseshoe tear, no heme, amenable to bubble" (right periphery field)
                                            <br />
                                            "1 clock hour of lattice 2 o'clock" (appended to both periphery fields)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Central Macular Thickness</td>
                                        <td>
                                            Right = rcmt
                                            <br />
                                            Left = lcmt
                                            <br />
                                            Both = bcmt or cmt
                                        </td>
                                        <td>
                                            <span class="font-weight-light text-danger">rcmt:</span>254
                                            <br />
                                            <span class="font-weight-light text-danger">cmt:</span>flat
                                        </td>
                                        <td>
                                            254 (right CMT only)
                                            <br />
                                            flat (both CMT fields)
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br />
                            <p class="font-italic">
                                *case insensitive
                                <br />
                                **The default action is to replace the field with the new text.
                                <br />
                                Adding <span class="font-weight-bold">".a"</span> at the end of a <span class="font-weight-bold">text</span> section will append the current text instead of replacing it.
                                <br >
                                For example, entering <span class="font-weight-bold">"bcup:0.5 w/ inf notch.a" will <u>append</u> "0.5 with inferior notch"</span> to both the right (rcup) and left cup fields (lcup).
                                <br />
                            </p>
                            <br />

                            <h2><u>Retina Shorthand Abbreviations:</u></h2>
                            <p>The following terms will be expanded from their shorthand to full expression in the EMR fields:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Enter this:</th>
                                        <th>Get this:</th>
                                    </tr>
                                    <tr>
                                        <td>inf</td>
                                        <td>inferior</td>
                                    </tr>
                                    <tr>
                                        <td>sup</td>
                                        <td>superior</td>
                                    </tr>
                                    <tr>
                                        <td>nas</td>
                                        <td>nasal</td>
                                    </tr>
                                    <tr>
                                        <td>temp</td>
                                        <td>temporal</td>
                                    </tr>
                                    <tr>
                                        <td>med</td>
                                        <td>medial</td>
                                    </tr>
                                    <tr>
                                        <td>lat</td>
                                        <td>lateral</td>
                                    </tr>
                                    <tr>
                                        <td>csme</td>
                                        <td>CSME</td>
                                    </tr>
                                    <tr>
                                        <td>w/</td>
                                        <td>with</td>
                                    </tr>
                                    <tr>
                                        <td>bdr</td>
                                        <td>BDR</td>
                                    </tr>
                                    <tr>
                                        <td>ppdr</td>
                                        <td>PPDR</td>
                                    </tr>
                                    <tr>
                                        <td>ht</td>
                                        <td>horsheshoe tear</td>
                                    </tr>
                                    <tr>
                                        <td>ab</td>
                                        <td>air bubble</td>
                                    </tr>
                                    <tr>
                                        <td>c3f8</td>
                                        <td>C3F8</td>
                                    </tr>
                                    <tr>
                                        <td>ma</td>
                                        <td>macroaneurysm</td>
                                    </tr>
                                    <tr>
                                        <td>tr</td>
                                        <td>trace</td>
                                    </tr>
                                    <tr>
                                        <td>mias</td>
                                        <td>microaneurysm</td>
                                    </tr>
                                    <tr>
                                        <td>ped</td>
                                        <td>PED</td>
                                    </tr>
                                    <tr>
                                        <td>1 o</td>
                                        <td> 1 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>2 o</td>
                                        <td>2 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>3 o</td>
                                        <td> 3 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>4 o</td>
                                        <td> 4 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>5 o</td>
                                        <td> 5 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>6 o</td>
                                        <td> 6 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>7 o</td>
                                        <td> 7 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>8 o</td>
                                        <td> 8 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>9 o</td>
                                        <td> 9 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>10 o</td>
                                        <td> 10 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>11 o</td>
                                        <td> 11 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>12 o</td>
                                        <td> 12 o'clock</td>
                                    </tr>
                                    <tr>
                                        <td>mac</td>
                                        <td>macula</td>
                                    </tr>
                                    <tr>
                                        <td>fov</td>
                                        <td>fovea</td>
                                    </tr>
                                    <tr>
                                        <td>vh</td>
                                        <td>vitreous hemorrhage</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Anterior Segment Section -->
            <div class="d-none" id="neuroAccordion">
                <!-- Shorthand Walk Through Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none" data-toggle="collapse" data-target="#neuroShorthandWalkThrough">
                                Neuro: Shorthand Walk Through
                            </button>
                        </h5>
                    </div>
                    <div id="neuroShorthandWalkThrough" class="collapse show" data-parent="#neuroAccordion">
                        <div class="card-body">
                            <h4><u>Shorthand</u></h4>
                            <textarea class="form-control">scDist;5:8ix 1rht;4:10ix;6:6ix;2:15xt;8:5ix;ccDist;4:5ix;5:ortho;6:ortho;
                            </textarea>
                            <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_neuro.png" class="img-fluid" alt="Shorthand Example: Anterior Segment" />
                            <br />
                        </div>
                    </div>
                </div>
                <!-- Example Output Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#neuroExampleOutput">
                                Neuro: Example Output
                            </button>
                        </h5>
                    </div>
                    <div id="neuroExampleOutput" class="collapse" data-parent="#neuroAccordion">
                        <div class="card-body">
                            <p>
                                Input:
                                <br />
                                <br />
                                <span class="font-weight-bold">scDist;5:8ix 1rht;4:10ix;6:6ix;2:15xt;8:5ix;ccDist;4:5ix;5:ortho;6:ortho;</span>
                                <br />
                                <br />
                                Output:
                            </p>
                            <br />

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Eye Exam</h4>
                                        <div class="row">
                                            <div class="col-12 col-md-6 text-sm-center">
                                                <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_neuro_EMR1.png" class="img-fluid" alt="Shorthand Example: openEMR" />
                                            </div>
                                            <div class="col-12 col-md-6 text-sm-center">
                                                <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_neuro_EMR2.png" class="img-fluid" alt="Shorthand Example: openEMR" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="border border-dark p-2">
                                        <h4>Reports</h4>
                                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/sh_neuro_report.png" class="img-fluid" width="75%" alt="Shorthand Example: Reports" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Field Codes and Shorthand/Abbreviations Card Section -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-decoration-none collapsed" data-toggle="collapse" data-target="#neuroFieldCodesAndShorthand">
                                Neuro: Field Codes and Shorthand/Abbreviations
                            </button>
                        </h5>
                    </div>
                    <div id="neuroFieldCodesAndShorthand" class="collapse" data-parent="#neuroAccordion">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Clinical Field</th>
                                        <th>Shorthand* Field</th>
                                        <th>Example Shorthand**</th>
                                        <th>EMR: Field text</th>
                                    </tr>
                                    <tr>
                                        <td>Default values</td>
                                        <td>D or d</td>
                                        <td>
                                            <span class="font-weight-light text-danger">d</span>;
                                            <br />
                                            <span class="font-weight-light text-danger">D</span>;
                                        </td>
                                        <td>
                                            All fields with defined default values are <span class="font-weight-bold">erased</span> and filled with default values.
                                            <br />
                                            Fields without defined default values are not affected.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Without correction at Distance</td>
                                        <td>scDist</td>
                                        <td><span class="font-weight-bold text-danger">scdist</span><span class="font-weight-bold text-success">;</span></td>
                                        <td>scDIST is selected for ensuing values.</td>
                                    </tr>
                                    <tr>
                                        <td>With correction at Distance</td>
                                        <td>scDist</td>
                                        <td><span class="font-weight-bold text-danger">ccdist</span><span class="font-weight-bold text-success">;</span></td>
                                        <td>ccDIST is selected for ensuing values.</td>
                                    </tr>
                                    <tr>
                                        <td>Without correction at Near</td>
                                        <td>scNear</td>
                                        <td><span class="font-weight-bold text-danger">scdist</span><span class="font-weight-bold text-success">;</span></td>
                                        <td>scDIST is selected for ensuing values.</td>
                                    </tr>
                                    <tr>
                                        <td>With correction at Near</td>
                                        <td>scNear</td>
                                        <td><span class="font-weight-bold text-danger">scdist</span><span class="font-weight-bold text-success">;</span></td>
                                        <td>scDIST is selected for ensuing values.</td>
                                    </tr>
                                </table>
                            </div>
                            <br />
                            <p class="font-italic">
                                *case insensitive
                                <br />
                                **The default action is to replace the field with the new text.
                                <br />
                                Adding <span class="font-weight-bold">".a"</span> at the end of a <span class="font-weight-bold">text</span> section will append the current text instead of replacing it.
                                <br >
                                For example, entering <span class="font-weight-bold">"4:5ix.a" will <u>append</u> "5 X(T)"</span>
                                to any measurements previously entered into the right gaze field.
                                <br />
                            </p>
                            <br />

                            <h2><u>Neuro Shorthand Abbreviations:</u></h2>
                            <p>The following terms will be expanded from their shorthand to full expression in the EMR fields:</p>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr class="thead-dark">
                                        <th>Strabismus</th>
                                        <th>Enter this:</th>
                                        <th>Get this:</th>
                                    </tr>
                                    <tr>
                                        <td>Exophoria</td>
                                        <td>x</td>
                                        <td>X</td>
                                    </tr>
                                    <tr>
                                        <td>Intermittent Esotropia</td>
                                        <td>ie or e(t)</td>
                                        <td>E(T)</td>
                                    </tr>
                                    <tr>
                                        <td>Esoptropia</td>
                                        <td>et</td>
                                        <td>ET</td>
                                    </tr>
                                    <tr>
                                        <td>Esophoria</td>
                                        <td>e</td>
                                        <td>E</td>
                                    </tr>
                                    <tr>
                                        <td>Intermittent Exotropia</td>
                                        <td>ix or x(t)</td>
                                        <td>X(T)</td>
                                    </tr>
                                    <tr>
                                        <td>Exoptropia</td>
                                        <td>xt</td>
                                        <td>XT</td>
                                    </tr>
                                    <tr>
                                        <td>Hyperphoria</td>
                                        <td>h</td>
                                        <td>H</td>
                                    </tr>
                                    <tr>
                                        <td>Intermittent Hypertropia</td>
                                        <td>H(T)</td>
                                        <td>H(T)</td>
                                    </tr>
                                    <tr>
                                        <td>Hypertropia</td>
                                        <td>rht<br />lht</td>
                                        <td>RHT<br />LHT</td>
                                    </tr>
                                    <tr>
                                        <td>Hypotropia</td>
                                        <td>hyt</td>
                                        <td>HyT</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const linkItems = Array.from(document.querySelectorAll('.nav-link'));
            const accordionContentsID = ['#introductionAccordion', '#hpiAccordion', '#pmhAccordion', '#externalAccordion',
                                     '#anteriorSegmentAccordion', '#retinaAccordion', '#neuroAccordion'];

            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $('#general_button').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#general_button').addClass('active');
                $('#introductionAccordion').removeClass('d-none');
            });

            $('#hpi_button, #hpi_button2').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#hpi_button').addClass('active');
                $('#hpiAccordion').removeClass('d-none');
            });

            $('#pmh_button, #pmh_button2').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#pmh_button').addClass('active');
                $('#pmhAccordion').removeClass('d-none');
            });

            $('#external_button, #external_button2').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#external_button').addClass('active');
                $('#externalAccordion').removeClass('d-none');
            });

            $('#antseg_button, #antseg_button2').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#antseg_button').addClass('active');
                $('#anteriorSegmentAccordion').removeClass('d-none');
            });

            $('#retina_button, #retina_button2').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#retina_button').addClass('active');
                $('#retinaAccordion').removeClass('d-none');
            });

            $('#neuro_button, #neuro_button2').click(() => {
                removeActiveClass();
                hideAccordionContents();
                $('#neuro_button').addClass('active');
                $('#neuroAccordion').removeClass('d-none');
            });

            /**
            * Remove all nav-link items active css style.
             */
            const removeActiveClass = () => {
                linkItems.forEach((link) => {
                    $(link).removeClass('active');
                });
            };

            /**
            * Hide all accordion contents.
             */
            const hideAccordionContents = () => {
                accordionContentsID.forEach((accordionContentID) => {
                    $(accordionContentID).addClass('d-none');
                });
            };
        </script>
    </body>
</html>
<?php exit; ?>
