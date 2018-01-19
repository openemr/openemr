<h1>OpenEMR Certification Test Mappings</h1>

<h4>Overview:</h4>
<p>In order to verify that code changes pass certification tests, contributors must execute the relevant test cases
before pushing changes. This document maps OpenEMR features with Meaningful Use certification tests.</p>

<h4>Notes:</h4>
<ul>
  <li>Feature mappings are linked to <a href="http://www.open-emr.org/wiki/index.php/OpenEMR_Features">this master list.</a></li>
  <li>Use <a href="http://bestonlinehtmleditor.com/">WYSIWYG HTML editor</a> for editing this document.</li>
  <li>This document is reasonably large so be sure to make use of `ctrl+f` to search for the feature you need to test.</li>
</ul>

<h4>Table:</h4>
<table>
  <thead>
    <tr>
      <th align="center"><span>Certification Test Name</span></th>
      <th align="center"><span>OpenEMR Feature Mappings</span></th>
      <th align="center"><span>Testing Procedures</span></th>
      <th align="center"><span>Testing Tools</span></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Computerized provider order entry</td>
      <td>
        <ul>
          <li>Electronic Medical Records > Medications</li>
          <li>Electronic Medical Records > Labs</li>
          <li>Electronic Medical Records > Procedures</li>
          <li>Prescriptions > All</li>
          <li>Reports > Prescriptions and Drug Dispensing</li>
          <li>Reports > Pending Procedure Orders</li>
          <li>Reports > Ordered Procedure Statistics</li>
          <li>Patient Portal > Labs</li>
          <li>Patient Portal > Medications</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170-314a1cpoe_2014_tp_approvedv1.3docx.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a1cpoe_2014_td_approvedv1.5.pdf">MU Test Data</a></li>
          <li><a href="http://www.open-emr.org/wiki/images/1/10/CPOE.pdf">OpenEMR Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Drug-drug, drug-allergy interaction checks</td>
      <td>
        <ul>
          <li>(Should there be a section on the master feature list for this?)</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a2drug_interaction_checks_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Demographics</td>
      <td>
        <ul>
          <li>Patient Demographics > Track patient demographics</li>
          <li>Patient Demographics > customizations</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a3demographics_2014_td_approvedv1.4_onc.pdf">MU Test Data</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314a3demographics_2014_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Vital signs, body mass index, and growth charts</td>
      <td>
        <ul>
          <li>Electronic Medical Records > Forms and clinical notes</li>
          <li>Electronic Medical Records > Encounters</li>
          <li>Patient Portal > Appointments</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a4vitalsignsbmigrowthcharts_2014_tp_approvedv1.4.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a4vitalsignsbmigrowthcharts_2014_td_approvedv1.3.pdf">MU Test Data</a></li>
          <li><a href="http://www.open-emr.org/wiki/images/7/74/Vitals_v2.pdf">OpenEMR Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Problem list</td>
      <td>
        <ul>
          <li>Electronic Medical Records > Medical Issues</li>
          <li>Electronic Medical Records > Encounters</li>
          <li>Patient Portal > Medical Problems</li>
          <li>Patient Portal > Appointments</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a5problemlist_2014_tp_approvedv1.4.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a5problemlist_2014_td_approvedv1.3.pdf">MU Test Data</a></li>
          <li><a href="http://www.open-emr.org/wiki/images/7/7f/Problem_list_MU2.pdf">OpenEMR Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Medication list</td>
      <td>
        <ul>
          <li>Electronic Medical Records > Medications</li>
          <li>Prescriptions > track patient prescriptions and medications</li>
          <li>Patient Portal > Medications</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a6medicationlist_2014_tp_approvedv1.3.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a6medicationlist_2014_td_approvedv1.4_onc.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Medication allergy list</td>
      <td>
        <ul>
          <li>Electronic Medical Records > Allergies</li>
          <li>Patient Portal > Allergies</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a7medicationallergylist_2014_tp_approvedv1.3.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a7medicationallergylist_2014_td_approvedv1.3.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Clinical decision support</td>
      <td>
        <ul>
          <li>(HL7?)</li>
          <li>Electronic Medical Records > Medications</li>
          <li>Electronic Medical Records > Medical Issues</li>
          <li>Electronic Medical Records > Encounters</li>
          <li>Patient Demographics > Track patient demographics</li>
          <li>Electronic Medical Records > Labs</li>
          <li>Electronic Medical Records > Procedures</li>
          <li>Clinical Decision Rules > Physician Reminders</li>
          <li>Clinical Decision Rules > Patient Reminders</li>
          <li>Clinical Decision Rules > Measurement Calculations</li>
          <li>Clinical Decision Rules > customizations</li>
          <li>Patient Portal > Labs</li>
          <li>Patient Portal > Medical Problems</li>
          <li>Patient Portal > Appointments</li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314a8cds_2014_tp_approvedv1.3.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Electronic notes</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a9electronic_notes_2014_tp_approved_v1.3.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a9electronicnotes_2014_td_approvedv1.3.pdf">MU Test Data</a></li>
          <li><a href="http://www.open-emr.org/wiki/images/f/fe/Electronic_Notes.pdf">OpenEMR Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Drug-formulary checks</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a10drugformularychecks_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a10drugformularychecks_2014_td_approvedv1.4_onc.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Smoking status</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a11smokingstatus_2014_tp_approvedv1.3.pdf">MU Test Procedure</a></li>
          <li><a href="http://www.open-emr.org/wiki/images/9/9a/Smoking_status.pdf">OpenEMR Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Image results</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a12_imageresults_2014_tp_approved_v1.3_onc.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Family health history</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a13familyhealthhistory_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Patient list creation</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a14patientlist_2014_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Patient-specific education resources</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314a15educationresources_2014_tp_approvedv1.5.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Transitions of care &ndash; receive, display, and incorporate transition of care/referral summaries</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b1toc_rdi_2014_tp_v1.7.pdf">MU Test Data</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b1toc_rdi_2014_td_v1.4.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Transitions of care &ndash; create and transmit transition of care/referral summaries</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b2toc_createandtransmit_2014_tp_updated_v1.4.pdf">MU Test Data</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b2toc_create_transmit_2014_td_v1.6.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Electronic prescribing</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b3eprescribing_2014_tp_approved_v1.4.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td>
        <a href="http://erx-testing.nist.gov">ePrescribing Validation Tool</a>
      </td>
    </tr>
    <tr>
      <td>Clinical information reconciliation</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b4cir_2014_tp_approved_v1.3.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b4cir_2014_tp_approved_v1.3.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Incorporate lab tests and values/results</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b5aincorporatelabtests_2014_tp_approved_v1.4_onc.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/guidelines-for-configuring-and-priming-the-ehr-for-lri-incorporation-tes.pdf">MU Test Data</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b5bincorporatelabtests_2014_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b5bincorplabtests_2014_td_approved_v1.2.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td>
        <a href="http://hl7v2-lab-testing.nist.gov">HL7v2 Laboratory Results Interface (LRI) Validation Tool</a>
      </td>
    </tr>
    <tr>
      <td>Data portability</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/policy/170_314b7_data_portability_v15_07092015.pdf">MU Test Data</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314b7dataportability_2014_td_approved_v1.7.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Clinical quality measures - capture and export</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/policy/cypress_test_procedure_07232015.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Clinical quality measures - import and calculate</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/policy/cypress_test_procedure_07232015.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Clinical quality measures - electronic submission</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/policy/cypress_test_procedure_07232015.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Authentication, access control, and authorization</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d1authentication_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Auditable events and tamper-resistance</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314d2auditableevents_2014_tp_v1_6.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Audit report(s)</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d3auditreports_2014_tp_approved_v1.3.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Amendments</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d4amendments_2014_tp_v1.3.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Automatic log-off</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d5automaticlogoff_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
          <li><a href="http://www.open-emr.org/wiki/images/7/7a/Automatic_log_off_MU2.zip">OpenEMR Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Emergency access</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d6emergencyaccess_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>End-user device encryption</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d7_enduserdeviceencryption_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Integrity</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d8integrity_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Accounting of disclosures</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314d9accountingdisclosures_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>View, download, and transmit to 3rd party</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314e1_for_final_posting.pdf">MU Test Data</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314e1vdt_2014_td_v1.5.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Clinical summaries</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314e2clinicalsummary_2014_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td>
        <a href="http://transport-testing.nist.gov">Transport Testing Tool</a>
      </td>
    </tr>
    <tr>
      <td>Secure messaging</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314e3securemessaging_2014_tp_approvedv1.3.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Immunization information</td>
      <td></td>
      <td>
        <ul>
          <li><a href="http://www.open-emr.org/wiki/images/9/97/Immunization_Information_MU2.pdf">OpenEMR Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314f1immunizationinformation_2014_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314f1immunizationinformation_2014_td_approved_v1.2.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Transmission to immunization registries</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314f2transmissiontoimmunizationregistries_2014_tp_approved_v1.3.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/guidelines-for-pre-loading-test-data-for-immunization-registries_032013.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Transmission to public health agencies &ndash; syndromic surveillance</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314f3transmissiontopubhealthsyndsurv_2014_tp_approved_v1.3.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/guidelines-for-pre-loading-test-data-for-syndromic-surveillance_032013.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td>
        <a href="http://hl7v2-ss-testing.nist.gov">HL7v2 Syndromic Surveillance Validation Tool</a>
      </td>
    </tr>
    <tr>
      <td>Cancer case information</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314f5cancercaseinformation_2014_tp_approved_v1.2.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314f5cancercaseinformation_2014_td_approved_v1.2.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Transmission to cancer registries</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314f6transmissiontocancerregistries_tp_approved_v1_4.pdf">MU Test Procedure</a></li>
          <li><a href="https://www.healthit.gov/sites/default/files/guidelines-for-pre-loading-test-data-for-cancer-registries_032013.pdf">MU Test Data</a></li>
        </ul>
      </td>
      <td>
        <ul>
          <li><a href="http://hl7v2-iz-testing.nist.gov ">HL7v2 Immunization Information System Reporting Validation Tool</a></li>
          <li><a href="http://cda-cancer-testing.nist.gov">HL7 CDA Cancer Registry Report Validation tool</a></li>
        </ul>
      </td>
    </tr>
    <tr>
      <td>Automated numerator recording</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314g1g2_20151118_v2_2.pdf">MU Test Data</a></li>
          <li><a href="http://healthit.gov/sites/default/files/170.314g12_2014_td_approved_v2.0.xlsx">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Automated measure calculation</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170_314g1g2_20151118_v2_2.pdf">MU Test Data</a></li>
          <li><a href="http://healthit.gov/sites/default/files/170.314g12_2014_td_approved_v2.0.xlsx">MU Test Data</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Safety-enhanced design</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170-314g3safetyenhanceddesign_2014_tp_approvedv1.4.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>Quality management system</td>
      <td></td>
      <td>
        <ul>
          <li><a href="https://www.healthit.gov/sites/default/files/170.314g4qms_2014_tp_approvedv1.2.pdf">MU Test Procedure</a></li>
        </ul>
      </td>
      <td></td>
    </tr>
  </tbody>
</table>
