<?xml version="1.0" encoding="UTF-8"?>
<!--
EH CMS 2022 QRDA Category I
Version 1.0 

    THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
    THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
    IN NO EVENT SHALL ESAC INC., OR ANY OF THEIR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
    SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
    GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
    THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
    
    IMPLEMENTATION GUIDE CONFORMANCE STATEMENTS and SCHEMATRON ASSERTIONS:
    
           In general, conformance statements are of three main types:
           
           - SHALL statements imply the conformance adherence is required. 
             SHALL Schematron assertions, when triggered, are considered 'errors'.
           
           - SHOULD statements imply the conformance adherence is recommended, but not required. 
             SHOULD Schematron assertions, when triggered, are considered 'warnings'.
                 Note about SHOULD Schematron assertions:
                     When a SHOULD conformance statement has cardinality of "zero or one [0..1]", then the corresponding Schematron assertion will only test for
                     the presence of the item (i.e. "count(item) = 1"). If it tested for 0 as well, then the assertion would never trigger because the item is either present
                     (count=1) or it is not (count=0), both of which would be acceptable. By only checking for the item's presence (count=1), Schematron can issue a
                     warning if the item is absent. Similar logic applies for SHOULD conformance statements with cardinality of "zero or more [0..*]" and the Schematron
                     assertion checks for at least one of the item (i.e. "count(item) > 0").             
           
           - MAY statements imply the conformance adherence is truly optional.
             MAY conformance statements are not enforced in the Schematron.
           
           Each type of conformance statement has three possible flavors:
           
           - 	Simple statements are simply the conformance statement with no further qualifications.
                For example: "SHALL contain exactly one [1..1] id."
           
           - 	Compound statements have additional requirements, represented by one or more "such that" conformance sub-clauses presented beneath the main conformance statement. 
                These are also referred to as "such that" statements.
                For example: "SHALL contain exactly one[1..] id such that 
                                1) SHALL contain exactly one [1..1] root, 
                                2) SHALL contain exactly one [1..1] extension."
           
                Compound statements are implemented in a single Schematron assertion that includes testing for the main conformance and any "such that" sub-clauses.
                In rare instances, a compound conformance statement sub-clause may itself contain "such that" sub-clauses. In that event, the corresponding single 
                Schematron assertion also includes testing for the "sub-sub-clauses".
                In the cases where one or more of a compound conformance sub-clauses have simple conformance statements under them, those are enforced as separate Schematron assertions.
           
           - 	Guidance conformance statements are those that represent conformance requirements that cannot or need not be implemented in Schematron assertions. 
                For example: "If patient name was not provided at time of admission, then a value of UNK SHALL be used."
                Guidance conformance statements of any type (SHALL, SHOULD, MAY) are not enforced in the Schematron.
           
           Examples:
           
           A) SHALL contain exactly one [1..1] id
               1) This id SHALL contain exactly one [1..1] @root
               2) This id SHALL contain exactly one [1..1] @extension
                   i) This id SHALL contain exactly one [1..1] source such that
                        a) SHALL contain exactly one [1..1] @value
           
           For the above example, the Schematron will have 4 assertions: One for A and one each for A.1, A.2 and A.2.i 
           (where A.2.i is a compound conformance that includes the "such that" A.2.i.a sub-clause in its test.)	
           
           
           B) SHALL contain exactly one [1..1] id such that
               1) SHALL contain exactly one [1..1] @root
               2) SHALL contain exactly one [1..1] @extension
               3) SHALL contain exactly one [1..1] source  
                   i) SHALL contain exactly one [1..1] @value
           
           For the above example, the Schematron will have 2 assertions: One for B (where B is a compound conformance that includes "such that" sub-clauses B.1, B.2, and B.3), 
           and one for B.3.i since it is NOT a such-that clause for B.3.
           
           C) MAY contain exactly one [1..1] id such that
               1) SHALL contain exactly one [1..1] @root
               2) SHALL contain exactly one [1..1] @extension
               3) SHALL contain exactly one [1..1] source  
                   i) If present, source SHALL contain exactly one [1..1] @value
           
           For the above example, the Schematron will have 1 assertion for C.3.i.  C is a MAY "such that" compound conformance statement and the Schematron does not implement any MAY conformances.
           However, C.3.i is not a "such that" sub-clause. It merits its own Schematron assertion because if an id/source exists (along with
           id/@root and id/@extension), then it SHALL contain a @value.
           
    
    REPORTING PERIOD: 2022
    Version 1.0
    
    There were no changes implemented in the 2022 Schematron v1.0
    
    The following IG templates are implemented in this schematron:
        Document templates
            QRDA Category I Report CMS V7 
            QDM Based QRDA V7               
            QRDA Category I Framework V4
            US Realm Header V3
        
        Section templates
           Patient Data Section QDM V7 CMS   
           Reporting Parameters Section CMS
           Measure Section
           Measure Section QDM
           Patient Data Section
           Patient Data Section QDM V7     
           Reporting Parameters Section
        
        Entry templates
            Admission Source
            Adverse Event V2 
            Adverse Event Cause Observation Assertion
            Age Observation
            Allergy Intolerance V2  
            Allergy Status Observation
            Assessment Order V2         
            Assessment Performed V3  
            Assessment Recommended V3 
            Care Goal V5 
            Communication Performed V2   
            Component
            Criticality Observation   
            Days Supplied             
            Deceased Observation V3
            Device Applied V6         
            Device Order V5 
            Device Order Act V3 
            Device Recommended V5 
            Device Recommended Act V3 
            Diagnosis V3 
            Diagnosis Concern Act V4 
            Diagnostic Study Order V5 
            Diagnostic Study Performed V5 
            Diagnostic Study Recommended V5 
            Discharge Medication V5    
            Drug Monitoring Act
            Drug Vehicle
            eMeasure Reference QDM
            Encounter Activity V3
            Encounter Diagnosis V3
            Encounter Diagnosis QDM  
            Encounter Order V5 
            Encounter Order Act V3 
            Encounter Performed V5 
            Encounter Performed Act V3 
            Encounter Recommended V5 
            Encounter Recommended Act V3  
            Entity Care Partner 
            Entity Organization 
            Entity Patient      
            Entity Practitioner          
            Entry Reference
            External Document Reference
            Facility Location V2
            Family History Death Observation
            Family History Observation V3
            Family History Observation QDM V4 
            Family History Organizer V3
            Family History Organizer QDM V5 
            Goal Observation
            Immunization Activity V3 
            Immunization Administered V3 
            Immunization Medication Information V2
            Immunization Order V3  
            Immunization Refusal Reason
            Immunization Supply Request 
            Incision Datetime
            Indication V2
            Instruction V2
            Intervention Order V5 
            Intervention Performed V5 
            Intervention Recommended V5 
            Laboratory Test Order V5 
            Laboratory Test Performed V5 
            Laboratory Test Recommended V5 
            Measure Reference
            Medication Active V5 
            Medication Activity V2
            Medication Administered V5 
            Medication Dispense V2
            Medication Dispensed V6     
            Medication Dispensed Act V4  
            Medication Free Text Sig
            Medication Information V2
            Medication Order V6          
            Medication Supply Order V2
            Medication Supply Request V3 
            Patient Care Experience V5 
            Patient Characteristic Clinical Trial Participant V4
            Patient Characteristic Expired V3
            Patient Characteristic Observation Assertion V5 
            Patient Characteristic Payer
            Physical Exam Order V5 
            Physical Exam Performed V5 
            Physical Exam Recommended V5 
            Planned Act V2
            Planned Coverage
            Planned Encounter V2
            Planned Immunization Activity
            Planned Medication Activity V2
            Planned Observation V2
            Planned Procedure V2
            Planned Supply V2
            Precondition for Substance Administration V2
            Present on Admission Indicator 
            Priority Preference
            Problem Concern Act V3
            Problem Observation V3
            Problem Status
            Procedure Activity Act V2
            Procedure Activity Observation V2
            Procedure Activity Procedure V2
            Procedure Order V6       
            Procedure Performed V6    
            Procedure Recommended V6  
            Product Instance
            Prognosis Observation
            Program Participation V2  
            Provider Care Experience V5 
            Rank               
            Reaction Observation V2
            Reason V3
            Related Person QDM
            Related To
            Reporting Parameters Act CMS
            Reporting Parameters Act
            Result V4 
            Result Observation V3
            Service Delivery Location
            Severity Observation V2
            Status V2 
            Substance Administered Act
            Substance or Device Allergy - Intolerance Observation V2
            Substance Recommended V5  
            Symptom V3 
            Symptom Concern Act V4 
            Target Outcome V2
         
        Other Templates
            Author V2
            Author Participation
            US Realm Address
            US Realm Date and Time
            US Realm Patient Name
            US Realm Person Name
             
    
    NOTE: Schematrons may be updated after initial publication to address stakeholder or policy requirements. 
    Be sure to revisit the eCQI Resource Center (https://ecqi.healthit.gov/) for updated resources prior to use. 

Tue Apr 27 16:15:57 MDT 2021
-->
<sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron" xmlns="urn:hl7-org:v3" xmlns:cda="urn:hl7-org:v3" xmlns:sdtc="urn:hl7-org:sdtc" xmlns:svs="urn:ihe:iti:svs:2008" xmlns:voc="http://www.lantanagroup.com/voc" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <sch:ns prefix="voc" uri="http://www.lantanagroup.com/voc" />
  <sch:ns prefix="svs" uri="urn:ihe:iti:svs:2008" />
  <sch:ns prefix="xsi" uri="http://www.w3.org/2001/XMLSchema-instance" />
  <sch:ns prefix="sdtc" uri="urn:hl7-org:sdtc" />
  <sch:ns prefix="cda" uri="urn:hl7-org:v3" />
  <sch:phase id="errors">
    <sch:active pattern="Admission_Source-pattern-errors" />
    <sch:active pattern="Adverse-Event-Cause-Observation-Assertion-pattern-errors" />
    <sch:active pattern="Adverse_Event-pattern-extension-check" />
    <sch:active pattern="Adverse_Event-pattern-errors" />
    <sch:active pattern="Age-observation-pattern-errors" />
    <sch:active pattern="Allergy-Intolerance-pattern-extension-check" />
    <sch:active pattern="Allergy-Intolerance-pattern-errors" />
    <sch:active pattern="Allergy_status_observation-pattern-errors" />
    <sch:active pattern="Assessment_order-pattern-extension-check" />
    <sch:active pattern="Assessment_order-pattern-errors" />
    <sch:active pattern="Assessment_performed-pattern-extension-check" />
    <sch:active pattern="Assessment_performed-pattern-errors" />
    <sch:active pattern="Assessment_recommended-pattern-extension-check" />
    <sch:active pattern="Assessment_recommended-pattern-errors" />
    <sch:active pattern="Author-Participation-pattern-errors" />
    <sch:active pattern="Author-pattern-extension-check" />
    <sch:active pattern="Author-pattern-errors" />
    <sch:active pattern="Care-Goal-pattern-extension-check" />
    <sch:active pattern="Care-Goal-pattern-errors" />
    <sch:active pattern="Communication_Performed-pattern-extension-check" />
    <sch:active pattern="Communication_Performed-pattern-errors" />
    <sch:active pattern="Component-pattern-errors" />
    <sch:active pattern="Criticality-Observation-pattern-errors" />
    <sch:active pattern="Days_Supplied-pattern-errors" />
    <sch:active pattern="Deceased-Observation-pattern-extension-check" />
    <sch:active pattern="Deceased-Observation-pattern-errors" />
    <sch:active pattern="Device-Applied-pattern-extension-check" />
    <sch:active pattern="Device-Applied-pattern-errors" />
    <sch:active pattern="Device-Order-Act-pattern-extension-check" />
    <sch:active pattern="Device-Order-Act-pattern-errors" />
    <sch:active pattern="Device-Order-pattern-extension-check" />
    <sch:active pattern="Device-Order-pattern-errors" />
    <sch:active pattern="Device-Recommended-Act-pattern-extension-check" />
    <sch:active pattern="Device-Recommended-Act-pattern-errors" />
    <sch:active pattern="Device-Recommended-pattern-extension-check" />
    <sch:active pattern="Device-Recommended-pattern-errors" />
    <sch:active pattern="Diagnosis_concern_act-pattern-extension-check" />
    <sch:active pattern="Diagnosis_concern_act-pattern-errors" />
    <sch:active pattern="Diagnosis-pattern-extension-check" />
    <sch:active pattern="Diagnosis-pattern-errors" />
    <sch:active pattern="Diagnostic-Study-Order-pattern-extension-check" />
    <sch:active pattern="Diagnostic-Study-Order-pattern-errors" />
    <sch:active pattern="Diagnostic-Study-Performed-pattern-extension-check" />
    <sch:active pattern="Diagnostic-Study-Performed-pattern-errors" />
    <sch:active pattern="Diagnostic-Study-Recommended-pattern-extension-check" />
    <sch:active pattern="Diagnostic-Study-Recommended-pattern-errors" />
    <sch:active pattern="Discharge-Medication-pattern-extension-check" />
    <sch:active pattern="Discharge-Medication-pattern-errors" />
    <sch:active pattern="Drug-Monitoring-Act-pattern-errors" />
    <sch:active pattern="Drug-Vehicle-pattern-errors" />
    <sch:active pattern="eMeasure-Reference-QDM-pattern-errors" />
    <sch:active pattern="Encounter-Activity-pattern-extension-check" />
    <sch:active pattern="Encounter-Activity-pattern-errors" />
    <sch:active pattern="Encounter-Diagnosis-QDM-pattern-errors" />
    <sch:active pattern="Encounter-Diagnosis-pattern-extension-check" />
    <sch:active pattern="Encounter-Diagnosis-pattern-errors" />
    <sch:active pattern="Encounter-Order-Act-pattern-extension-check" />
    <sch:active pattern="Encounter-Order-Act-pattern-errors" />
    <sch:active pattern="Encounter-Order-pattern-extension-check" />
    <sch:active pattern="Encounter-Order-pattern-errors" />
    <sch:active pattern="Encounter-Performed-Act-pattern-extension-check" />
    <sch:active pattern="Encounter-Performed-Act-pattern-errors" />
    <sch:active pattern="Encounter-Performed-pattern-extension-check" />
    <sch:active pattern="Encounter-Performed-pattern-errors" />
    <sch:active pattern="Encounter-Recommended-Act-pattern-extension-check" />
    <sch:active pattern="Encounter-Recommended-Act-pattern-errors" />
    <sch:active pattern="Encounter-Recommended-pattern-extension-check" />
    <sch:active pattern="Encounter-Recommended-pattern-errors" />
    <sch:active pattern="Entity_Care_Partner-pattern-errors" />
    <sch:active pattern="Entity_Organization-pattern-errors" />
    <sch:active pattern="Entity_Patient-pattern-errors" />
    <sch:active pattern="Entity_Practitioner-pattern-errors" />
    <sch:active pattern="Entry-Reference-pattern-errors" />
    <sch:active pattern="External-Document-Reference-pattern-errors" />
    <sch:active pattern="Facility-Location-pattern-extension-check" />
    <sch:active pattern="Facility-Location-pattern-errors" />
    <sch:active pattern="Family_History_Death_Observation-pattern-errors" />
    <sch:active pattern="Family_History_Observation_QDM-pattern-extension-check" />
    <sch:active pattern="Family_History_Observation_QDM-pattern-errors" />
    <sch:active pattern="Family_History_Observation-pattern-extension-check" />
    <sch:active pattern="Family_History_Observation-pattern-errors" />
    <sch:active pattern="Family_History_Organizer_QDM-pattern-extension-check" />
    <sch:active pattern="Family_History_Organizer_QDM-pattern-errors" />
    <sch:active pattern="Family_History_Organizer-pattern-extension-check" />
    <sch:active pattern="Family_History_Organizer-pattern-errors" />
    <sch:active pattern="Goal_Observation-pattern-errors" />
    <sch:active pattern="Immunization_activity-pattern-extension-check" />
    <sch:active pattern="Immunization_activity-pattern-errors" />
    <sch:active pattern="Immunization_administered-pattern-extension-check" />
    <sch:active pattern="Immunization_administered-pattern-errors" />
    <sch:active pattern="Immunization_medication_information-pattern-extension-check" />
    <sch:active pattern="Immunization_medication_information-pattern-errors" />
    <sch:active pattern="Immunization_order-pattern-extension-check" />
    <sch:active pattern="Immunization_order-pattern-errors" />
    <sch:active pattern="Immunization_refusal_reason-pattern-errors" />
    <sch:active pattern="Immunization_Supply_Request-pattern-errors" />
    <sch:active pattern="Incision_datetime-pattern-errors" />
    <sch:active pattern="Indication-pattern-extension-check" />
    <sch:active pattern="Indication-pattern-errors" />
    <sch:active pattern="Instruction-pattern-extension-check" />
    <sch:active pattern="Instruction-pattern-errors" />
    <sch:active pattern="Intervention_Order-pattern-extension-check" />
    <sch:active pattern="Intervention_Order-pattern-errors" />
    <sch:active pattern="Intervention_Performed-pattern-extension-check" />
    <sch:active pattern="Intervention_Performed-pattern-errors" />
    <sch:active pattern="Intervention_Recommended-pattern-extension-check" />
    <sch:active pattern="Intervention_Recommended-pattern-errors" />
    <sch:active pattern="Laboratory_Test_Order-pattern-extension-check" />
    <sch:active pattern="Laboratory_Test_Order-pattern-errors" />
    <sch:active pattern="Laboratory_Test_Performed-pattern-extension-check" />
    <sch:active pattern="Laboratory_Test_Performed-pattern-errors" />
    <sch:active pattern="Laboratory_Test_Recommended-pattern-extension-check" />
    <sch:active pattern="Laboratory_Test_Recommended-pattern-errors" />
    <sch:active pattern="Measure_Reference-pattern-errors" />
    <sch:active pattern="Measure-section-pattern-errors" />
    <sch:active pattern="Measure-section-qdm-pattern-errors" />
    <sch:active pattern="Medication_Active-pattern-extension-check" />
    <sch:active pattern="Medication_Active-pattern-errors" />
    <sch:active pattern="Medication_Activity-pattern-extension-check" />
    <sch:active pattern="Medication_Activity-pattern-errors" />
    <sch:active pattern="Medication_Administered-pattern-extension-check" />
    <sch:active pattern="Medication_Administered-pattern-errors" />
    <sch:active pattern="Medication_Dispense-pattern-extension-check" />
    <sch:active pattern="Medication_Dispense-pattern-errors" />
    <sch:active pattern="Medication_Dispensed_Act-pattern-extension-check" />
    <sch:active pattern="Medication_Dispensed_Act-pattern-errors" />
    <sch:active pattern="Medication_Dispensed-pattern-extension-check" />
    <sch:active pattern="Medication_Dispensed-pattern-errors" />
    <sch:active pattern="Medication_Free_Text_Sig-pattern-errors" />
    <sch:active pattern="Medication_Information-pattern-extension-check" />
    <sch:active pattern="Medication_Information-pattern-errors" />
    <sch:active pattern="Medication_Order-pattern-extension-check" />
    <sch:active pattern="Medication_Order-pattern-errors" />
    <sch:active pattern="Medication_Supply_Order-pattern-extension-check" />
    <sch:active pattern="Medication_Supply_Order-pattern-errors" />
    <sch:active pattern="Medication_Supply_Request-pattern-extension-check" />
    <sch:active pattern="Medication_Supply_Request-pattern-errors" />
    <sch:active pattern="Patient_care_experience-pattern-extension-check" />
    <sch:active pattern="Patient_care_experience-pattern-errors" />
    <sch:active pattern="Patient_Characteristic_Clinical_Trial_Participant-pattern-extension-check" />
    <sch:active pattern="Patient_Characteristic_Clinical_Trial_Participant-pattern-errors" />
    <sch:active pattern="Patient_Characteristic_Expired-pattern-extension-check" />
    <sch:active pattern="Patient_Characteristic_Expired-pattern-errors" />
    <sch:active pattern="Patient_Characteristic_Observation_Assertion-pattern-extension-check" />
    <sch:active pattern="Patient_Characteristic_Observation_Assertion-pattern-errors" />
    <sch:active pattern="Patient_Characteristic_Payer-pattern-errors" />
    <sch:active pattern="Patient-data-section-pattern-errors" />
    <sch:active pattern="Patient_data_section_QDM-pattern-extension-check" />
    <sch:active pattern="Patient_data_section_QDM-pattern-errors" />
    <sch:active pattern="Physical_Exam_Order-pattern-extension-check" />
    <sch:active pattern="Physical_Exam_Order-pattern-errors" />
    <sch:active pattern="Physical_Exam_Performed-pattern-extension-check" />
    <sch:active pattern="Physical_Exam_Performed-pattern-errors" />
    <sch:active pattern="Physical_Exam_Recommended-pattern-extension-check" />
    <sch:active pattern="Physical_Exam_Recommended-pattern-errors" />
    <sch:active pattern="Planned_Act-pattern-extension-check" />
    <sch:active pattern="Planned_Act-pattern-errors" />
    <sch:active pattern="Planned_Coverage-pattern-errors" />
    <sch:active pattern="Planned_Encounter-pattern-extension-check" />
    <sch:active pattern="Planned_Encounter-pattern-errors" />
    <sch:active pattern="Planned_Immunization_Activity-pattern-errors" />
    <sch:active pattern="Planned-Medication-Activity-pattern-extension-check" />
    <sch:active pattern="Planned-Medication-Activity-pattern-errors" />
    <sch:active pattern="Planned-Observation-pattern-extension-check" />
    <sch:active pattern="Planned-Observation-pattern-errors" />
    <sch:active pattern="Planned-Procedure-pattern-extension-check" />
    <sch:active pattern="Planned-Procedure-pattern-errors" />
    <sch:active pattern="Planned-Supply-pattern-extension-check" />
    <sch:active pattern="Planned-Supply-pattern-errors" />
    <sch:active pattern="Precondition-For-Substance-Administration-pattern-extension-check" />
    <sch:active pattern="Precondition-For-Substance-Administration-pattern-errors" />
    <sch:active pattern="Present-on-Admission-Indicator-pattern-errors" />
    <sch:active pattern="Priority-Preference-pattern-errors" />
    <sch:active pattern="Problem-Concern-Act-pattern-extension-check" />
    <sch:active pattern="Problem-Concern-Act-pattern-errors" />
    <sch:active pattern="Problem-Observation-pattern-extension-check" />
    <sch:active pattern="Problem-Observation-pattern-errors" />
    <sch:active pattern="Problem-Status-pattern-errors" />
    <sch:active pattern="Procedure-Activity-Act-pattern-extension-check" />
    <sch:active pattern="Procedure-Activity-Act-pattern-errors" />
    <sch:active pattern="Procedure-Activity-Observation-pattern-extension-check" />
    <sch:active pattern="Procedure-Activity-Observation-pattern-errors" />
    <sch:active pattern="Procedure-Activity-Procedure-pattern-extension-check" />
    <sch:active pattern="Procedure-Activity-Procedure-pattern-errors" />
    <sch:active pattern="Procedure-Order-pattern-extension-check" />
    <sch:active pattern="Procedure-Order-pattern-errors" />
    <sch:active pattern="Procedure-Performed-pattern-extension-check" />
    <sch:active pattern="Procedure-Performed-pattern-errors" />
    <sch:active pattern="Procedure-Recommended-pattern-extension-check" />
    <sch:active pattern="Procedure-Recommended-pattern-errors" />
    <sch:active pattern="Product-Instance-pattern-errors" />
    <sch:active pattern="Prognosis-Observation-pattern-errors" />
    <sch:active pattern="Program_Participation-pattern-extension-check" />
    <sch:active pattern="Program_Participation-pattern-errors" />
    <sch:active pattern="Provider-Care-Experience-pattern-extension-check" />
    <sch:active pattern="Provider-Care-Experience-pattern-errors" />
    <sch:active pattern="QDM_based_QRDA-pattern-extension-check" />
    <sch:active pattern="QDM_based_QRDA-pattern-errors" />
    <sch:active pattern="QRDA_Category_I-pattern-extension-check" />
    <sch:active pattern="QRDA_Category_I-pattern-errors" />
    <sch:active pattern="Rank-pattern-errors" />
    <sch:active pattern="Reaction-Observation-pattern-extension-check" />
    <sch:active pattern="Reaction-Observation-pattern-errors" />
    <sch:active pattern="Reason-pattern-extension-check" />
    <sch:active pattern="Reason-pattern-errors" />
    <sch:active pattern="Related-Person-QDM-pattern-errors" />
    <sch:active pattern="Related-To-pattern-errors" />
    <sch:active pattern="Reporting-Parameters-Act-pattern-errors" />
    <sch:active pattern="Reporting-parameters-section-pattern-errors" />
    <sch:active pattern="Result-Observation-pattern-extension-check" />
    <sch:active pattern="Result-Observation-pattern-errors" />
    <sch:active pattern="Result-pattern-extension-check" />
    <sch:active pattern="Result-pattern-errors" />
    <sch:active pattern="Service-Delivery-Location-pattern-errors" />
    <sch:active pattern="Severity-Observation-pattern-extension-check" />
    <sch:active pattern="Severity-Observation-pattern-errors" />
    <sch:active pattern="Status-pattern-extension-check" />
    <sch:active pattern="Status-pattern-errors" />
    <sch:active pattern="Substance-Administered-Act-pattern-errors" />
    <sch:active pattern="Substance-Device-Allergy-Intolerance-Observation-pattern-extension-check" />
    <sch:active pattern="Substance-Device-Allergy-Intolerance-Observation-pattern-errors" />
    <sch:active pattern="Substance-Recommended-pattern-extension-check" />
    <sch:active pattern="Substance-Recommended-pattern-errors" />
    <sch:active pattern="Symptom-Concern-Act-pattern-extension-check" />
    <sch:active pattern="Symptom-Concern-Act-pattern-errors" />
    <sch:active pattern="Symptom-pattern-extension-check" />
    <sch:active pattern="Symptom-pattern-errors" />
    <sch:active pattern="Target-Outcome-pattern-extension-check" />
    <sch:active pattern="Target-Outcome-pattern-errors" />
    <sch:active pattern="US-Realm-Address-pattern-errors" />
    <sch:active pattern="US-Realm-Date-and-Time-pattern-errors" />
    <sch:active pattern="US_Realm-pattern-extension-check" />
    <sch:active pattern="US_Realm-pattern-errors" />
    <sch:active pattern="US-Realm-Patient-Name-pattern-errors" />
    <sch:active pattern="US-Realm-Person-Name-pattern-errors" />
    <sch:active pattern="p-validate_CD_CE-errors" />
    <sch:active pattern="p-validate_BL-errors" />
    <sch:active pattern="p-validate_CS-errors" />
    <sch:active pattern="p-validate_II-errors" />
    <sch:active pattern="p-validate_PQ-errors" />
    <sch:active pattern="p-validate_ST-errors" />
    <sch:active pattern="p-validate_REAL-errors" />
    <sch:active pattern="p-validate_INT-errors" />
    <sch:active pattern="p-validate_NPI_format-errors" />
    <sch:active pattern="p-validate_TIN_format-errors" />
    <sch:active pattern="p-validate_TS-errors" />
    <sch:active pattern="p-validate_TZ-errors" />
    <sch:active pattern="p-CMS-QRDA-I-templateId-errors" />
    <sch:active pattern="CMS_QRDA_Category_I_Patient_Data_Section_QDM_template-pattern-errors" />
    <sch:active pattern="CMS_QRDA_Category_I_Patient_Data_Section_QDM_CMS_pattern-errors" />
    <sch:active pattern="QRDA_Category_I_Report_CMS-pattern-extension-check" />
    <sch:active pattern="QRDA_Category_I_Report_CMS-pattern-errors" />
    <sch:active pattern="Reporting-Parameters-Act-template-pattern-errors" />
    <sch:active pattern="Reporting-Parameters-Act-CMS-pattern-errors" />
    <sch:active pattern="QRDA_Category_I_Reporting_Parameters_Section-template-pattern-errors" />
    <sch:active pattern="QRDA_Category_I_Reporting_Parameters_Section_CMS-pattern-errors" />
  </sch:phase>
  <sch:phase id="warnings">
    <sch:active pattern="Admission_Source-pattern-warnings" />
    <sch:active pattern="Author-Participation-pattern-warnings" />
    <sch:active pattern="Deceased-Observation-pattern-warnings" />
    <sch:active pattern="Device-Applied-pattern-warnings" />
    <sch:active pattern="Diagnostic-Study-Performed-pattern-warnings" />
    <sch:active pattern="eMeasure-Reference-QDM-pattern-warnings" />
    <sch:active pattern="Encounter-Activity-pattern-warnings" />
    <sch:active pattern="External-Document-Reference-pattern-warnings" />
    <sch:active pattern="Facility-Location-pattern-warnings" />
    <sch:active pattern="Family_History_Observation-pattern-warnings" />
    <sch:active pattern="Family_History_Organizer-pattern-warnings" />
    <sch:active pattern="Goal_Observation-pattern-warnings" />
    <sch:active pattern="Immunization_activity-pattern-warnings" />
    <sch:active pattern="Immunization_medication_information-pattern-warnings" />
    <sch:active pattern="Indication-pattern-warnings" />
    <sch:active pattern="Intervention_Performed-pattern-warnings" />
    <sch:active pattern="Measure_Reference-pattern-warnings" />
    <sch:active pattern="Medication_Activity-pattern-warnings" />
    <sch:active pattern="Medication_Dispense-pattern-warnings" />
    <sch:active pattern="Medication_Free_Text_Sig-pattern-warnings" />
    <sch:active pattern="Medication_Supply_Order-pattern-warnings" />
    <sch:active pattern="Patient_Characteristic_Payer-pattern-warnings" />
    <sch:active pattern="Physical_Exam_Performed-pattern-warnings" />
    <sch:active pattern="Planned_Act-pattern-warnings" />
    <sch:active pattern="Planned_Encounter-pattern-warnings" />
    <sch:active pattern="Planned_Immunization_Activity-pattern-warnings" />
    <sch:active pattern="Planned-Medication-Activity-pattern-warnings" />
    <sch:active pattern="Planned-Observation-pattern-warnings" />
    <sch:active pattern="Planned-Procedure-pattern-warnings" />
    <sch:active pattern="Planned-Supply-pattern-warnings" />
    <sch:active pattern="Priority-Preference-pattern-warnings" />
    <sch:active pattern="Problem-Concern-Act-pattern-warnings" />
    <sch:active pattern="Problem-Observation-pattern-warnings" />
    <sch:active pattern="Procedure-Activity-Act-pattern-warnings" />
    <sch:active pattern="Procedure-Activity-Observation-pattern-warnings" />
    <sch:active pattern="Procedure-Activity-Procedure-pattern-warnings" />
    <sch:active pattern="Procedure-Performed-pattern-warnings" />
    <sch:active pattern="Product-Instance-pattern-warnings" />
    <sch:active pattern="QDM_based_QRDA-pattern-warnings" />
    <sch:active pattern="Reaction-Observation-pattern-warnings" />
    <sch:active pattern="Result-Observation-pattern-warnings" />
    <sch:active pattern="Service-Delivery-Location-pattern-warnings" />
    <sch:active pattern="Substance-Device-Allergy-Intolerance-Observation-pattern-warnings" />
    <sch:active pattern="US-Realm-Address-pattern-warnings" />
    <sch:active pattern="US-Realm-Date-and-Time-pattern-warnings" />
    <sch:active pattern="US_Realm-pattern-warnings" />
    <sch:active pattern="QRDA_Category_I_Report_CMS-pattern-warnings" />
  </sch:phase>
  <!--
      ERROR Patterns and Assertions
  -->
  <sch:pattern id="Admission_Source-pattern-errors">
    <sch:rule id="Admission_Source-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.151'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-29094-error" test="@classCode='SDLOC'">SHALL contain exactly one [1..1] @classCode="SDLOC" (CodeSystem: HL7RoleCode urn:oid:2.16.840.1.113883.5.111 STATIC) (CONF:3343-29094).</sch:assert>
      <sch:assert id="a-3343-29091-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.151'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-29091) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.151" (CONF:3343-29093). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-29100).</sch:assert>
      <sch:assert id="a-3343-29099-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:3343-29099).</sch:assert>
    </sch:rule>
    <sch:rule id="Admission_Source-playingEntity-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.151'][@extension='2017-08-01']]/cda:playingEntity">
      <sch:assert id="a-3343-29097-error" test="@classCode='PLC'">The playingEntity, if present, SHALL contain exactly one [1..1] @classCode="PLC" (CodeSystem: HL7EntityClass urn:oid:2.16.840.1.113883.5.41 STATIC) (CONF:3343-29097).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Adverse-Event-Cause-Observation-Assertion-pattern-errors">
    <sch:rule id="Adverse-Event-Cause-Observation-Assertion-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.148'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-28741-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CONF:3343-28741).</sch:assert>
      <sch:assert id="a-3343-28742-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:3343-28742).</sch:assert>
      <sch:assert id="a-3343-28745-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:3343-28745).</sch:assert>
      <sch:assert id="a-3343-28731-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.148'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-28731) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.148" (CONF:3343-28736). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-28737).</sch:assert>
      <sch:assert id="a-3343-28730-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:3343-28730).</sch:assert>
      <sch:assert id="a-3343-28733-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:3343-28733).</sch:assert>
    </sch:rule>
    <sch:rule id="Adverse-Event-Cause-Observation-Assertion-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.148'][@extension='2017-08-01']]/cda:code">
      <sch:assert id="a-3343-28734-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:3343-28734).</sch:assert>
      <sch:assert id="a-3343-28735-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:3343-28735).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Adverse_Event-pattern-extension-check">
    <sch:rule id="Adverse_Event-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146']">
      <sch:assert id="a-4444-28751-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28751) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.146" (CONF:4444-28761). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28762).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Adverse_Event-pattern-errors">
    <sch:rule id="Adverse_Event-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28773-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28773).</sch:assert>
      <sch:assert id="a-4444-28774-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28774).</sch:assert>
      <sch:assert id="a-4444-28776-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28776).</sch:assert>
      <sch:assert id="a-4444-28751-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28751) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.146" (CONF:4444-28761). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28762).</sch:assert>
      <sch:assert id="a-4444-28775-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-28775).</sch:assert>
      <sch:assert id="a-4444-28752-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-28752).</sch:assert>
      <sch:assert id="a-4444-28753-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-28753).</sch:assert>
      <sch:assert id="a-4444-28754-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-28754).</sch:assert>
      <sch:assert id="a-4444-28756-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-28756).</sch:assert>
      <sch:assert id="a-4444-28755-error" test="count(cda:entryRelationship[@typeCode='CAUS'][@inversionInd='true'][count(cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.148'][@extension='2017-08-01'])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28755) such that it SHALL contain exactly one [1..1] @typeCode="CAUS" (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28768). SHALL contain exactly one [1..1] @inversionInd="true" (CONF:4444-28769). SHALL contain exactly one [1..1] Adverse Event Cause Observation Assertion (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.148:2017-08-01) (CONF:4444-28770).</sch:assert>
    </sch:rule>
    <sch:rule id="Adverse_Event-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28763-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:4444-28763).</sch:assert>
      <sch:assert id="a-4444-28764-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: HL7ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:4444-28764).</sch:assert>
    </sch:rule>
    <sch:rule id="Adverse_Event-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-28765-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: HL7ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-28765).</sch:assert>
    </sch:rule>
    <sch:rule id="Adverse_Event-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-30015-error" test="count(@value)=1">This effectiveTime SHALL contain exactly one [1..1] @value (CONF:4444-30015).</sch:assert>
    </sch:rule>
    <sch:rule id="Adverse_Event-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.146'][@extension='2019-12-01']]/cda:value">
      <sch:assert id="a-4444-28771-error" test="@code='281647001'">This value SHALL contain exactly one [1..1] @code="281647001" Adverse reaction (CONF:4444-28771).</sch:assert>
      <sch:assert id="a-4444-28772-error" test="@codeSystem='2.16.840.1.113883.6.96'">This value SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" SNOMED CT (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:4444-28772).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Age-observation-pattern-errors">
    <sch:rule id="Age-observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.31']]">
      <sch:assert id="a-81-7613-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:81-7613).</sch:assert>
      <sch:assert id="a-81-7614-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:81-7614).</sch:assert>
      <sch:assert id="a-81-7899-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.31'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-7899) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.31" (CONF:81-10487).</sch:assert>
      <sch:assert id="a-81-7615-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:81-7615).</sch:assert>
      <sch:assert id="a-81-15965-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:81-15965).</sch:assert>
      <sch:assert id="a-81-7617-error" test="count(cda:value[@xsi:type='PQ'])=1">SHALL contain exactly one [1..1] value with @xsi:type="PQ" (CONF:81-7617).</sch:assert>
    </sch:rule>
    <sch:rule id="Age-observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.31']]/cda:code">
      <sch:assert id="a-81-16776-error" test="@code='445518008'">This code SHALL contain exactly one [1..1] @code="445518008" Age At Onset (CONF:81-16776).</sch:assert>
      <sch:assert id="a-81-26499-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:81-26499).</sch:assert>
    </sch:rule>
    <sch:rule id="Age-observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.31']]/cda:statusCode">
      <sch:assert id="a-81-15966-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:81-15966).</sch:assert>
    </sch:rule>
    <sch:rule id="Age-observation-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.31']]/cda:value[@xsi:type='PQ']">
      <sch:assert id="a-81-7618-error" test="@unit">This value SHALL contain exactly one [1..1] @unit, which SHALL be selected from ValueSet AgePQ_UCUM urn:oid:2.16.840.1.113883.11.20.9.21 DYNAMIC (CONF:81-7618).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Allergy-Intolerance-pattern-extension-check">
    <sch:rule id="Allergy-Intolerance-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147']">
      <sch:assert id="a-4444-29592-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-29592) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.147" (CONF:4444-28828). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28829).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Allergy-Intolerance-pattern-errors">
    <sch:rule id="Allergy-Intolerance-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28826-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:4444-28826).</sch:assert>
      <sch:assert id="a-4444-28827-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:4444-28827).</sch:assert>
      <sch:assert id="a-4444-28848-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28848).</sch:assert>
      <sch:assert id="a-4444-29592-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-29592) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.147" (CONF:4444-28828). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28829).</sch:assert>
      <sch:assert id="a-4444-29593-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29593).</sch:assert>
      <sch:assert id="a-4444-29594-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-29594).</sch:assert>
      <sch:assert id="a-4444-28836-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-28836).</sch:assert>
      <!-- 4444-30034 added for STU 5.2 -->
      <sch:assert id="a-4444-30034-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30034).</sch:assert>
      <sch:assert id="a-4444-29595-error" test="count(cda:participant[@typeCode='CSM'][count(cda:participantRole)=1])=1">SHALL contain exactly one [1..1] participant (CONF:4444-29595) such that it SHALL contain exactly one [1..1] @typeCode="CSM" Consumable (CodeSystem: HL7ParticipationType urn:oid:2.16.840.1.113883.5.90 STATIC) (CONF:4444-28837). SHALL contain exactly one [1..1] participantRole (CONF:4444-28821).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy-Intolerance-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28831-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:4444-28831).</sch:assert>
      <sch:assert id="a-4444-28832-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: HL7ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:4444-28832).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy-Intolerance-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-28834-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:4444-28834).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy-Intolerance-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']]/cda:value[@xsi:type='CD']">
      <sch:assert id="a-4444-28849-error" test="@code='419199007'">This value SHALL contain exactly one [1..1] @code="419199007" Allergy to substance (CONF:4444-28849).</sch:assert>
      <sch:assert id="a-4444-28850-error" test="@codeSystem='2.16.840.1.113883.6.96'">This value SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:4444-28850).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy-Intolerance-participant-participantRole-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']]/cda:participant[@typeCode='CSM']/cda:participantRole">
      <sch:assert id="a-4444-28838-error" test="@classCode='MANU'">This participantRole SHALL contain exactly one [1..1] @classCode="MANU" Manufactured Product (CodeSystem: HL7RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:4444-28838).</sch:assert>
      <sch:assert id="a-4444-28822-error" test="count(cda:playingEntity)=1">This participantRole SHALL contain exactly one [1..1] playingEntity (CONF:4444-28822).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy-Intolerance-participant-participantRole-playingEntity-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.147'][@extension='2019-12-01']]/cda:participant[@typeCode='CSM']/cda:participantRole[@classCode='MANU']/cda:playingEntity">
      <sch:assert id="a-4444-28839-error" test="@classCode='MMAT'">This playingEntity SHALL contain exactly one [1..1] @classCode="MMAT" Manufactured Material (CodeSystem: HL7EntityClass urn:oid:2.16.840.1.113883.5.41 STATIC) (CONF:4444-28839).</sch:assert>
      <sch:assert id="a-4444-28840-error" test="count(cda:code)=1">This playingEntity SHALL contain exactly one [1..1] code (CONF:4444-28840).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Allergy_status_observation-pattern-errors">
    <sch:rule id="Allergy_status_observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.28']]">
      <sch:assert id="a-1198-7318-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-7318).</sch:assert>
      <sch:assert id="a-1198-7319-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-7319).</sch:assert>
      <sch:assert id="a-1198-7317-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.28'][@extension='2019-06-20'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-7317) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.28" (CONF:1198-10490).	SHALL contain exactly one [1..1] @extension="2019-06-20" (CONF:1198-32962).</sch:assert>
      <sch:assert id="a-1198-7320-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-7320).</sch:assert>
      <sch:assert id="a-1198-7321-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-7321).</sch:assert>
      <sch:assert id="a-1198-7322-error" test="count(cda:value[@xsi:type='CE'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CE", where the code SHALL be selected from ValueSet Problem Status urn:oid:2.16.840.1.113883.3.88.12.80.68 DYNAMIC (CONF:1198-7322).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy_status_observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.28']]/cda:code">
      <sch:assert id="a-1198-19131-error" test="@code='33999-4'">This code SHALL contain exactly one [1..1] @code="33999-4" Status (CONF:1198-19131).</sch:assert>
      <sch:assert id="a-1198-32155-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:1198-32155).</sch:assert>
    </sch:rule>
    <sch:rule id="Allergy_status_observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.28']]/cda:statusCode">
      <sch:assert id="a-1198-19087-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1198-19087).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Assessment_order-pattern-extension-check">
    <sch:rule id="Assessment_order-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.158']">
      <sch:assert id="a-4444-29241-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-29241) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.158" (CONF:4444-29244) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29245).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Assessment_order-pattern-errors">
    <sch:rule id="Assessment_order-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.158'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29249-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29249).</sch:assert>
      <sch:assert id="a-4444-29248-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" request (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-29248).</sch:assert>
      <sch:assert id="a-4444-29241-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.158'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29241) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.158" (CONF:4444-29244) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29245).</sch:assert>
      <sch:assert id="a-4444-29246-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29246).</sch:assert>
      <sch:assert id="a-4444-29242-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-29242).</sch:assert>
      <!-- 4444-29565 added for STU 5.2 -->
      <sch:assert id="a-4444-29565-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Assessment Not Order (CONF:4444-29565).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Assessment_performed-pattern-extension-check">
    <sch:rule id="Assessment_performed-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.144']">
      <sch:assert id="a-4444-28652-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28652) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.144" (CONF:4444-28660) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28701).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Assessment_performed-pattern-errors">
    <sch:rule id="Assessment_performed-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.144'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28670-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28670).</sch:assert>
      <sch:assert id="a-4444-28669-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28669).</sch:assert>
      <sch:assert id="a-4444-28652-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.144'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28652) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.144" (CONF:4444-28660) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28701).</sch:assert>
      <!-- 4444-9613 added for STU 5.2 -->
      <sch:assert id="a-4444-29613-error" test="count(cda:id) &gt;=1">SHALL contain at least one [1..*] id (CONF:4444-29613).</sch:assert>
      <sch:assert id="a-4444-28656-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-28656).</sch:assert>
      <sch:assert id="a-4444-28653-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-28653).</sch:assert>
      <!-- 05-26-2020 Conformance 4444-28818 change from a SHALL to a MAY per STU comment 1976  -->
      <!-- <sch:assert id="a-4444-28818-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-29242). (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-28818).</sch:assert> -->
      <!--Removed 33443-28783 as it was included in V2 (per http://www.hl7.org/dstucomments/showdetail_comment.cfm?commentid=1787), but not in V3    -->
      <!--  <sch:assert id="a-4444-28783-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2017-08-01']]) = 1">SHALL contain exactly one [1..1] Author (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2017-08-01) (CONF:4444-28783). </sch:assert> -->
      <!-- 05-26-2020 4444-29122 added per STU comment 1976 -->
      <sch:assert id="a-4444-29122-error" test="count(cda:effectiveTime[count(@value | @nullFlavor | cda:low) =1 ]) =1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-29122) such that it  SHOULD contain zero or one [0..1] @value (CONF:4444-30016).  SHOULD contain zero or one [0..1] low (CONF:4444-29123). MAY contain zero or one [0..1] high (CONF:4444-29124).  This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low (CONF:4444-29125).</sch:assert>
      <!-- 4444-29563 error added for STU 5.2 -->
      <sch:assert id="a-4444-29563-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Assessment Not Performed (CONF:4444-29563).</sch:assert>
    </sch:rule>
    <sch:rule id="Assessment_performed-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.144'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-28662-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-28662).</sch:assert>
    </sch:rule>
    <!-- 05-06-2020 Added  @nullFlavor to the test -->
    <!-- 05-26-2020 STU 1976 dictates that 4444-29125 is subsumed within the "such that it" clauses of 4444-29122 -->
    <!--
        <sch:rule id="Assessment_performed-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.144'][@extension='2019-12-01']]/cda:effectiveTime">
            <sch:assert id="a-4444-29125-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low (CONF:4444-29125).</sch:assert>
        </sch:rule>
        -->
  </sch:pattern>
  <sch:pattern id="Assessment_recommended-pattern-extension-check">
    <sch:rule id="Assessment_recommended-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.145']">
      <sch:assert id="a-4444-28673-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28673) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.145" (CONF:4444-28676). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28702).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Assessment_recommended-pattern-errors">
    <sch:rule id="Assessment_recommended-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.145'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28682-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28682).</sch:assert>
      <sch:assert id="a-4444-28681-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28681).</sch:assert>
      <sch:assert id="a-4444-28673-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.145'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28673) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.145" (CONF:4444-28676). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28702).</sch:assert>
      <sch:assert id="a-4444-28674-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-28674).</sch:assert>
      <sch:assert id="a-4444-28680-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-28680).</sch:assert>
      <!-- 4444-29566 added for STU 5.2 -->
      <sch:assert id="a-4444-29566-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Assessment Not Recommended (CONF:4444-29566).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Author-Participation-pattern-errors">
    <sch:rule id="Author-Participation-errors" context="cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]">
      <sch:assert id="a-1098-32017-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-32017) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.119" (CONF:1098-32018).</sch:assert>
      <sch:assert id="a-1098-31471-error" test="count(cda:time)=1">SHALL contain exactly one [1..1] time (CONF:1098-31471).</sch:assert>
      <sch:assert id="a-1098-31472-error" test="count(cda:assignedAuthor)=1">SHALL contain exactly one [1..1] assignedAuthor (CONF:1098-31472).</sch:assert>
    </sch:rule>
    <sch:rule id="Author-Participation-assignedAuthor-errors" context="cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]/cda:assignedAuthor">
      <sch:assert id="a-1098-31473-error" test="count(cda:id)&gt;=1">This assignedAuthor SHALL contain at least one [1..*] id (CONF:1098-31473).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Author-pattern-extension-check">
    <sch:rule id="Author-extension-check" context="cda:author/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155']">
      <sch:assert id="a-4444-30005-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-30005) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.155" (CONF:4444-30007). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-30008).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Author-pattern-errors">
    <sch:rule id="Author-errors" context="cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-30005-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-30005) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.155" (CONF:4444-30007). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-30008).</sch:assert>
      <sch:assert id="a-4444-30009-error" test="count(cda:time)=1">SHALL contain exactly one [1..1] time (CONF:4444-30009).</sch:assert>
      <sch:assert id="a-4444-29146-error" test="count(cda:assignedAuthor)=1">SHALL contain exactly one [1..1] assignedAuthor (CONF:4444-29146).</sch:assert>
    </sch:rule>
    <sch:rule id="Author-time-errors" context="cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]/cda:time">
      <sch:assert id="a-4444-30010-error" test="count(@value)=1">This time SHALL contain exactly one [1..1] @value (CONF:4444-30010).</sch:assert>
    </sch:rule>
    <sch:rule id="Author-assignedAuthor-errors" context="cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]/cda:assignedAuthor">
      <sch:assert id="a-4444-30006-error" test="count(cda:id)&gt;=1">This assignedAuthor SHALL contain at least one [1..*] id (CONF:4444-30006).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Care-Goal-pattern-extension-check">
    <sch:rule id="Care-Goal-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.1']">
      <sch:assert id="a-4444-11247-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11247) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.1" (CONF:4444-11248). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27067).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Care-Goal-pattern-errors">
    <sch:rule id="Care-Goal-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.1'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-11245-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-11245).</sch:assert>
      <sch:assert id="a-4444-11246-error" test="@moodCode='GOL'">SHALL contain exactly one [1..1] @moodCode="GOL" goal (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11246).</sch:assert>
      <sch:assert id="a-4444-28040-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28040).</sch:assert>
      <sch:assert id="a-4444-11247-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.1'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11247) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.1" (CONF:4444-11248). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27067).</sch:assert>
      <sch:assert id="a-4444-27576-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27576).</sch:assert>
      <sch:assert id="a-4444-11255-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-11255).</sch:assert>
    </sch:rule>
    <sch:rule id="Care-Goal-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.1'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-27557-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:4444-27557).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Communication_Performed-pattern-extension-check">
    <sch:rule id="Communication_Performed-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.156']">
      <sch:assert id="a-4444-29143-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-29143) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.156" (CONF:4444-29151) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29152).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Communication_Performed-pattern-errors">
    <sch:rule id="Communication_Performed-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.156'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29160-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29160).</sch:assert>
      <sch:assert id="a-4444-29161-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-29161).</sch:assert>
      <sch:assert id="a-4444-29143-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.156'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29143) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.156" (CONF:4444-29151) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29152).</sch:assert>
      <sch:assert id="a-4444-29162-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:4444-29162).</sch:assert>
      <sch:assert id="a-4444-29159-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29159).</sch:assert>
      <sch:assert id="a-4444-29163-error" test="count(cda:statusCode[@code='completed'])=1">SHALL contain exactly one [1..1] statusCode="completed", which SHALL be selected from CodeSystem HL7ActStatus (urn:oid:2.16.840.1.113883.5.14) (CONF:4444-29163).</sch:assert>
      <sch:assert id="a-4444-29149-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-29149).</sch:assert>
      <sch:assert id="a-4444-29168-error" test="count(cda:entryRelationship[@typeCode='REFR'][count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']])=1]) = 1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-29168) such that it SHALL contain exactly one [1..1] @typeCode="REFR" Has reference (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-29175)  SHALL contain exactly one [1..1] Reason (V3) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.88:2017-08-01) (CONF:4444-29169).</sch:assert>
      <!-- 4444-29591 added for STU 5.2 -->
      <!-- 4444-29591 added specificity of typeCode='RSON' for entryRelationship when negationInd=true. 03-26-2020 -->
      <sch:assert id="a-4444-29591-error" test="(@negationInd='true' and count(cda:entryRelationship[@typeCode='RSON'][cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Communication Not Performed (CONF:4444-29591).</sch:assert>
    </sch:rule>
    <sch:rule id="Communication_Performed-participant-VIA-participantRole-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.156'][@extension='2019-12-01']]/cda:participant[@typeCode='VIA']/cda:participantRole">
      <sch:assert id="a-4444-29174-error" test="count(cda:code)=1">This participantRole SHALL contain exactly one [1..1] code (CONF:4444-29174).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Component-pattern-errors">
    <sch:rule id="Component-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.149'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-28788-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:3343-28788).</sch:assert>
      <sch:assert id="a-3343-28789-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:3343-28789).</sch:assert>
      <sch:assert id="a-3343-28786-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.149'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-28786) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.149" (CONF:3343-28793).SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-28796).</sch:assert>
      <sch:assert id="a-3343-28784-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:3343-28784).</sch:assert>
      <sch:assert id="a-3343-28797-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:3343-28797).</sch:assert>
      <sch:assert id="a-3343-28785-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:3343-28785).</sch:assert>
    </sch:rule>
    <sch:rule id="Component-referenceRange-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.149'][@extension='2017-08-01']]/cda:referenceRange">
      <sch:assert id="a-3343-28795-error" test="count(cda:observationRange)=1">The referenceRange, if present, SHALL contain exactly one [1..1] observationRange (CONF:3343-28795).</sch:assert>
    </sch:rule>
    <sch:rule id="Component-referenceRange-observationRange-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.149'][@extension='2017-08-01']]/cda:referenceRange/cda:observationRange">
      <sch:assert id="a-3343-28798-error" test="count(cda:code)=0">This observationRange SHALL NOT contain [0..0] code (CONF:3343-28798).</sch:assert>
      <sch:assert id="a-3343-28799-error" test="count(cda:value)=1">This observationRange SHALL contain exactly one [1..1] value (CONF:3343-28799).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Criticality-Observation-pattern-errors">
    <sch:rule id="Criticality-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.145']]">
      <sch:assert id="a-81-32921-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:81-32921).</sch:assert>
      <sch:assert id="a-81-32922-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:81-32922).</sch:assert>
      <sch:assert id="a-81-32918-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.145'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-32918) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.145" (CONF:81-32923).</sch:assert>
      <sch:assert id="a-81-32919-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:81-32919).</sch:assert>
      <sch:assert id="a-81-32920-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:81-32920).</sch:assert>
      <!-- 08-14-2019 Changed from STATIC to DYNAMIC. removed value set inclusion test since it is now dynamic -->
      <sch:assert id="a-81-32928-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Criticality Observation urn:oid:2.16.840.1.113883.1.11.20549 DYNAMIC (CONF:81-32928).</sch:assert>
    </sch:rule>
    <sch:rule id="Criticality-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.145']]/cda:code">
      <sch:assert id="a-81-32925-error" test="@code='82606-5'">This code SHALL contain exactly one [1..1] @code="82606-5" Criticality (CONF:81-32925).</sch:assert>
      <sch:assert id="a-81-32926-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:81-32926).</sch:assert>
    </sch:rule>
    <sch:rule id="Criticality-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.145']]/cda:statusCode">
      <sch:assert id="a-81-32927-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:81-32927).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Days_Supplied-pattern-errors">
    <sch:rule id="Days_Supplied-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.157'][@extension='2018-10-01']]">
      <sch:assert id="a-4388-29196-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:4388-29196).</sch:assert>
      <sch:assert id="a-4388-29197-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:4388-29197).</sch:assert>
      <sch:assert id="a-4388-29179-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.157'][@extension='2018-10-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4388-29179) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.157" (CONF:4388-29198) SHALL contain exactly one [1..1] @extension="2018-10-01" (CONF:4388-29199).</sch:assert>
      <sch:assert id="a-4388-29211-error" test="count(cda:quantity)=1">SHALL contain exactly one [1..1] quantity (CONF:4388-29211).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Deceased-Observation-pattern-extension-check">
    <sch:rule id="Deceased-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79']">
      <sch:assert id="a-1198-14871-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-14871) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.79" (CONF:1198-14872) SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32541).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Deceased-Observation-pattern-errors">
    <sch:rule id="Deceased-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-14851-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-14851).</sch:assert>
      <sch:assert id="a-1198-14852-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-14852).</sch:assert>
      <sch:assert id="a-1198-14871-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-14871) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.79" (CONF:1198-14872) SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32541).</sch:assert>
      <sch:assert id="a-1198-14873-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:1198-14873).</sch:assert>
      <sch:assert id="a-1198-14854-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-14854).</sch:assert>
      <sch:assert id="a-1198-14853-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-14853).</sch:assert>
      <sch:assert id="a-1198-14855-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1198-14855).</sch:assert>
      <sch:assert id="a-1198-14857-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:1198-14857).</sch:assert>
    </sch:rule>
    <sch:rule id="Deceased-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79'][@extension='2015-08-01']]/cda:code">
      <sch:assert id="a-1198-19135-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:1198-19135).</sch:assert>
      <sch:assert id="a-1198-32158-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1198-32158).</sch:assert>
    </sch:rule>
    <sch:rule id="Deceased-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-19095-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1198-19095).</sch:assert>
    </sch:rule>
    <sch:rule id="Deceased-Observation-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79'][@extension='2015-08-01']]/cda:value">
      <sch:assert id="a-1198-15142-error" test="@code='419099009'">This value SHALL contain exactly one [1..1] @code="419099009" Dead (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96 STATIC) (CONF:1198-15142).</sch:assert>
    </sch:rule>
    <sch:rule id="Deceased-Observation-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79'][@extension='2015-08-01']]/cda:effectiveTime">
      <sch:assert id="a-1198-14874-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:1198-14874).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Applied-pattern-extension-check">
    <sch:rule id="Device-Applied-extension-check" context="cda:procedure/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7']">
      <sch:assert id="a-4444-12391-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12391) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.7" (CONF:4444-12392).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27132).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Applied-pattern-errors">
    <sch:rule id="Device-Applied-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28050-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" Procedure (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28050).</sch:assert>
      <sch:assert id="a-4444-28051-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28051).</sch:assert>
      <sch:assert id="a-4444-12391-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12391) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.7" (CONF:4444-12392).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27132).</sch:assert>
      <sch:assert id="a-4444-12414-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-12414).</sch:assert>
      <sch:assert id="a-4444-12394-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-12394).</sch:assert>
      <sch:assert id="a-4444-12395-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-12395).</sch:assert>
      <sch:assert id="a-4444-30026-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30026).</sch:assert>
      <sch:assert id="a-4444-12396-error" test="count(cda:participant[@typeCode='DEV'][count(cda:participantRole)=1])=1">SHALL contain exactly one [1..1] participant (CONF:4444-12396) such that it SHALL contain exactly one [1..1] @typeCode="DEV" device, which SHALL be selected from CodeSystem HL7ParticipationType (urn:oid:2.16.840.1.113883.5.90) (CONF:4444-12397).  SHALL contain exactly one [1..1] participantRole (CONF:4444-12398).</sch:assert>
      <!-- 4444-29616 added for STU 5.2 -->
      <sch:assert id="a-4444-29616-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Device Not Applied (CONF:4444-29616).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Applied-code-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-12415-error" test="@code='360030002'">This code SHALL contain exactly one [1..1] @code="360030002" application of device (CONF:4444-12415).</sch:assert>
      <sch:assert id="a-4444-27356-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:4444-27536).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Applied-statusCode-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-29261-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: HL7ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-29261).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Applied-effectiveTime-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 05-06-2020 Added @nullFlavor to the test -->
      <sch:assert id="a-4444-29618-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low (CONF:4444-29618).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Applied-participant-participantRole-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]/cda:participant[@typeCode='DEV']/cda:participantRole">
      <sch:assert id="a-4444-12399-error" test="@classCode='MANU'">This participantRole SHALL contain exactly one [1..1] @classCode="MANU" manufactured product, which SHALL be selected from CodeSystem HL7RoleClass (urn:oid:2.16.840.1.113883.5.110) (CONF:4444-12399).</sch:assert>
      <sch:assert id="a-4444-12400-error" test="count(cda:playingDevice)=1">This participantRole SHALL contain exactly one [1..1] playingDevice (CONF:4444-12400).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Applied-participant-participantRole-playingDevice-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]/cda:participant[@typeCode='DEV']/cda:participantRole[@classCode='MANU']/cda:playingDevice">
      <sch:assert id="a-4444-12401-error" test="@classCode='DEV'">This playingDevice SHALL contain exactly one [1..1] @classCode="DEV" device, which SHALL be selected from CodeSystem HL7ParticipationType (urn:oid:2.16.840.1.113883.5.90) (CONF:4444-12401).</sch:assert>
      <sch:assert id="a-4444-12402-error" test="count(cda:code)=1">This playingDevice SHALL contain exactly one [1..1] code (CONF:4444-12402).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Order-Act-pattern-extension-check">
    <sch:rule id="Device-Order-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.130']">
      <sch:assert id="a-4444-28441-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28441) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.130" (CONF:4444-28447).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28918).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Order-Act-pattern-errors">
    <sch:rule id="Device-Order-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.130'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28444-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28444).</sch:assert>
      <sch:assert id="a-4444-28445-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28445).</sch:assert>
      <sch:assert id="a-4444-28441-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.130'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28441) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.130" (CONF:4444-28447).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28918).</sch:assert>
      <sch:assert id="a-4444-28442-error" test="count(cda:code) =1">SHALL contain exactly one [1..1] code (CONF:4444-28442).</sch:assert>
      <sch:assert id="a-4444-28443-error" test="count(cda:entryRelationship[@typeCode='SUBJ'] [count(cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.9' and @extension = '2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28443) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" Has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28450). SHALL contain exactly one [1..1] Device Order (V5) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.9:2019-12-01) (CONF:4444-28451).</sch:assert>
      <!-- 4444-29621 added for STU 5.2 -->
      <sch:assert id="a-4444-29621-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Device Not Order (CONF:4444-29621).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Order-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.130'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28448-error" test="@code='SPLY'">This code SHALL contain exactly one [1..1] @code="SPLY" Supply (CONF:4444-28448).</sch:assert>
      <sch:assert id="a-4444-28449-error" test="@codeSystem">This code SHALL contain exactly one [1..1] @codeSystem (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28449).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Order-pattern-extension-check">
    <sch:rule id="Device-Order-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.9']">
      <sch:assert id="a-4444-12344-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12344) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.9" (CONF:4444-12345). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27091).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Order-pattern-errors">
    <sch:rule id="Device-Order-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.9'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27723-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" Supply (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27723).</sch:assert>
      <sch:assert id="a-4444-12343-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request, which SHALL be selected from CodeSystem ActMood (urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12343).</sch:assert>
      <sch:assert id="a-4444-12344-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.9'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12344) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.9" (CONF:4444-12345). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27091).</sch:assert>
      <sch:assert id="a-4444-27721-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27721).</sch:assert>
      <sch:assert id="a-4444-12349-error" test="count(cda:participant[@typeCode='DEV'][count(cda:participantRole)=1])=1">SHALL contain exactly one [1..1] participant (CONF:4444-12349) such that it  SHALL contain exactly one [1..1] @typeCode="DEV" device, which SHALL be selected from CodeSystem HL7ParticipationType (urn:oid:2.16.840.1.113883.5.90) (CONF:4444-12350). SHALL contain exactly one [1..1] participantRole (CONF:4444-12351).</sch:assert>
      <sch:assert id="a-4444-28665-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.130'][@extension='2019-12-01'])=1">This template SHALL be contained by a Device Order Act (V2) (CONF:4444-28665).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Order-participant-participantRole-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.9'][@extension='2019-12-01']]/cda:participant[@typeCode='DEV']/cda:participantRole">
      <sch:assert id="a-4444-12352-error" test="@classCode='MANU'">This participantRole SHALL contain exactly one [1..1] @classCode="MANU" manufactured product, which SHALL be selected from CodeSystem HL7RoleClass (urn:oid:2.16.840.1.113883.5.110) (CONF:4444-12352).</sch:assert>
      <sch:assert id="a-4444-12353-error" test="count(cda:playingDevice)=1">This participantRole SHALL contain exactly one [1..1] playingDevice (CONF:4444-12353).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Order-participant-participantRole-playingDevice-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.9'][@extension='2019-12-01']]/cda:participant[@typeCode='DEV']/cda:participantRole/cda:playingDevice">
      <sch:assert id="a-4444-12354-error" test="@classCode='DEV'">This playingDevice SHALL contain exactly one [1..1] @classCode="DEV" device, which SHALL be selected from CodeSystem HL7ParticipationType (urn:oid:2.16.840.1.113883.5.90) (CONF:4444-12354).</sch:assert>
      <sch:assert id="a-4444-12355-error" test="count(cda:code)=1">his playingDevice SHALL contain exactly one [1..1] code (CONF:4444-12355).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Recommended-Act-pattern-extension-check">
    <sch:rule id="Device-Recommended-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.131']">
      <sch:assert id="a-4444-28452-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28452) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.131" (CONF:4444-28456). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28922).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Recommended-Act-pattern-errors">
    <sch:rule id="Device-Recommended-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.131'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28454-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28454).</sch:assert>
      <sch:assert id="a-4444-28455-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28455).</sch:assert>
      <sch:assert id="a-4444-28452-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.131'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28452) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.131" (CONF:4444-28456). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28922).</sch:assert>
      <sch:assert id="a-4444-28453-error" test="count(cda:entryRelationship[@typeCode='SUBJ'] [count(cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.10'][@extension = '2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28453) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28458). SHALL contain exactly one [1..1] Device Recommended (V5) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.10:2019-12-01) (CONF:4444-28459).</sch:assert>
      <!-- 4444-29630 added for STU 5.2 -->
      <sch:assert id="a-4444-29630-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Device Not Recommended (CONF:4444-29630).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Recommended-pattern-extension-check">
    <sch:rule id="Device-Recommended-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.10']">
      <sch:assert id="a-4444-12369-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12369) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.10" (CONF:4444-12370). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27094).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Recommended-pattern-errors">
    <sch:rule id="Device-Recommended-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.10'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27722-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" Supply (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27722).</sch:assert>
      <sch:assert id="a-4444-12368-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent, which SHALL be selected from CodeSystem ActMood (urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12368).</sch:assert>
      <sch:assert id="a-4444-12369-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.10'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12369) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.10" (CONF:4444-12370). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27094).</sch:assert>
      <sch:assert id="a-4444-27719-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27719).</sch:assert>
      <sch:assert id="a-4444-12374-error" test="count(cda:participant[@typeCode='DEV'][count(cda:participantRole)=1])=1">SHALL contain exactly one [1..1] participant (CONF:4444-12374) such that it SHALL contain exactly one [1..1] @typeCode="DEV" device, which SHALL be selected from CodeSystem HL7ParticipationType (urn:oid:2.16.840.1.113883.5.90) (CONF:4444-12375).SHALL contain exactly one [1..1] participantRole (CONF:4444-12376).</sch:assert>
      <sch:assert id="a-4444-28666-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.131'][@extension='2019-12-01'])=1">This template SHALL be contained by a Device Recommended Act (V3) (CONF:4444-28666).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Recommended-participant-participantRole-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.10'][@extension='2019-12-01']]/cda:participant[@typeCode='DEV']/cda:participantRole">
      <sch:assert id="a-4444-12377-error" test="@classCode='MANU'">This participantRole SHALL contain exactly one [1..1] @classCode="MANU" manufactured product, which SHALL be selected from CodeSystem HL7RoleClass (urn:oid:2.16.840.1.113883.5.110) (CONF:4444-12377).</sch:assert>
      <sch:assert id="a-4444-12378-error" test="count(cda:playingDevice)=1">This participantRole SHALL contain exactly one [1..1] playingDevice (CONF:4444-12378).</sch:assert>
    </sch:rule>
    <sch:rule id="Device-Recommended-participant-participantRole-playingDevice-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.10'][@extension='2019-12-01']]/cda:participant[@typeCode='DEV']/cda:participantRole[@classCode='MANU']/cda:playingDevice">
      <sch:assert id="a-4444-12379-error" test="@classCode='DEV'">This playingDevice SHALL contain exactly one [1..1] @classCode="DEV" device, which SHALL be selected from CodeSystem HL7ParticipationType (urn:oid:2.16.840.1.113883.5.90) (CONF:4444-12379).</sch:assert>
      <sch:assert id="a-4444-12380-error" test="count(cda:code)=1">This playingDevice SHALL contain exactly one [1..1] code (CONF:4444-12380).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnosis_concern_act-pattern-extension-check">
    <sch:rule id="Diagnosis_concern_act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.137']">
      <sch:assert id="a-4444-28143-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28143) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.137" (CONF:4444-28146). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28692).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnosis_concern_act-pattern-errors">
    <sch:rule id="Diagnosis_concern_act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.137'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28148-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28148).</sch:assert>
      <sch:assert id="a-4444-28149-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28149).</sch:assert>
      <sch:assert id="a-4444-29632-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-29632).</sch:assert>
      <sch:assert id="a-4444-28143-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.137'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28143) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.137" (CONF:4444-28146). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28692).</sch:assert>
      <sch:assert id="a-4444-28144-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-28144).</sch:assert>
      <sch:assert id="a-4444-28142-error" test="count(cda:entryRelationship[@typeCode='SUBJ'] [count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.135' ][@extension='2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28142) such that it  SHALL contain exactly one [1..1] @typeCode="SUBJ" Has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28151).  SHALL contain exactly one [1..1] Diagnosis (V3) (identifier: urn:oid:2.16.840.1.113883.10.20.24.3.135:2019-12-01) (CONF:4444-28145).</sch:assert>
    </sch:rule>
    <sch:rule id="Diagnosis_concern_act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.137'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-28150-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet QDM Diagnosis Status urn:oid:2.16.840.1.113762.1.4.1021.35 DYNAMIC (CONF:4444-28150).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnosis-pattern-extension-check">
    <sch:rule id="Diagnosis-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.135']">
      <sch:assert id="a-4444-28498-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28498) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.135" (CONF:4444-28503). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28887).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnosis-pattern-errors">
    <sch:rule id="Diagnosis-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.135'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28510-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28510).</sch:assert>
      <sch:assert id="a-4444-28511-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28511).</sch:assert>
      <sch:assert id="a-4444-28512-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28512).</sch:assert>
      <sch:assert id="a-4444-28498-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.135'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28498) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.135" (CONF:4444-28503). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28887).</sch:assert>
      <sch:assert id="a-4444-28499-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-28499).</sch:assert>
      <!-- 4444-30033 added for STU 5.2 -->
      <sch:assert id="a-4444-30033-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30033).</sch:assert>
      <sch:assert id="a-4444-28885-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.137'][@extension='2019-12-01'])=1">This template SHALL be contained by a Diagnosis Concern Act (V4) (CONF:4444-28885).</sch:assert>
    </sch:rule>
    <sch:rule id="Diagnosis-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.135'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28505-error" test="@code='29308-4'">This code SHALL contain exactly one [1..1] @code="29308-4" diagnosis (CONF:4444-28505).</sch:assert>
      <sch:assert id="a-4444-28506-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-28506).</sch:assert>
      <sch:assert id="a-4444-28886-error" test="count(cda:translation)=1">This code SHALL contain exactly one [1..1] translation (CONF:4444-28886).</sch:assert>
    </sch:rule>
    <sch:rule id="Diagnosis-code-translation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.135'][@extension='2019-12-01']]/cda:code/cda:translation">
      <sch:assert id="a-4444-28888-error" test="@code='282291009'">This translation, if present, SHALL contain exactly one [1..1] @code="282291009" 2.16.840.1.113883.6.96 (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:4444-28888).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Order-pattern-extension-check">
    <sch:rule id="Diagnostic-Study-Order-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.17']">
      <sch:assert id="a-4444-13412-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13412) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.17" (CONF:4444-13413). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27069).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Order-pattern-errors">
    <sch:rule id="Diagnostic-Study-Order-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.17'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27408-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27408).</sch:assert>
      <sch:assert id="a-4444-13411-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13411).</sch:assert>
      <sch:assert id="a-4444-13412-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.17'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-13412) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.17" (CONF:4444-13413). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27069).</sch:assert>
      <sch:assert id="a-4444-27615-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27615).</sch:assert>
      <sch:assert id="a-4444-27340-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27340).</sch:assert>
      <!-- 4444-29637 added for STU 5.2 -->
      <sch:assert id="a-4444-29637-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Diagnostic Study Not Order (CONF:4444-29637).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Performed-pattern-extension-check">
    <sch:rule id="Diagnostic-Study-Performed-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18']">
      <sch:assert id="a-4444-12951-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12951) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.18" (CONF:4444-12952). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27141).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Performed-pattern-errors">
    <sch:rule id="Diagnostic-Study-Performed-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27369-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27369).</sch:assert>
      <sch:assert id="a-4444-12950-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12950).</sch:assert>
      <sch:assert id="a-4444-12951-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12951) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.18" (CONF:4444-12952). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27141).</sch:assert>
      <!-- Removed 4444-29633 for STU 5.2 -->
      <!-- <sch:assert id="a-4444-29633-error" test="count(cda:id)=1">SHALL contain exactly one [1..1] id 4444-29633. </sch:assert> -->
      <sch:assert id="a-4444-27617-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27617).</sch:assert>
      <sch:assert id="a-4444-12956-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-12956).</sch:assert>
      <sch:assert id="a-4444-12958-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-12958).</sch:assert>
      <sch:assert id="a-4444-29332-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:4444-29332).</sch:assert>
      <sch:assert id="a-4444-30022-error" test="count(cda:author[cda:templateId[@root=':2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30022).</sch:assert>
      <!-- 4444-29634 added for STU 5.2 -->
      <sch:assert id="a-4444-29634-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Diagnostic Study Not Performed (CONF:4444-29634).</sch:assert>
    </sch:rule>
    <sch:rule id="Diagnostic-Study-Performed-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-12957-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-12957).</sch:assert>
    </sch:rule>
    <sch:rule id="Diagnostic-Study-Performed-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 05-06-2020 Added @nullFlavor to test -->
      <sch:assert id="a-4444-30025-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low  (CONF:4444-30025).</sch:assert>
    </sch:rule>
    <sch:rule id="Diagnostic-Study-Performed-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18'][@extension='2019-12-01']]/cda:value">
      <sch:assert id="a-4444-29333-error" test="@nullFlavor='NA'">This value SHALL contain exactly one [1..1] @nullFlavor="NA" (CONF:4444-29333).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Recommended-pattern-extension-check">
    <sch:rule id="Diagnostic-Study-Recommended-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.19']">
      <sch:assert id="a-4444-13393-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13393) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.19" (CONF:4444-13394). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27070).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Recommended-pattern-errors">
    <sch:rule id="Diagnostic-Study-Recommended-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.19'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27406-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27406).</sch:assert>
      <sch:assert id="a-4444-13392-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13392).</sch:assert>
      <sch:assert id="a-4444-13393-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.19'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-13393) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.19" (CONF:4444-13394). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27070).</sch:assert>
      <sch:assert id="a-4444-27619-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27619).</sch:assert>
      <sch:assert id="a-4444-13400-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-13400)..</sch:assert>
      <!-- 4444-29639 added for STU 5.2 -->
      <sch:assert id="a-4444-29639-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Diagnostic Study Not Recommended (CONF:4444-29639).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Discharge-Medication-pattern-extension-check">
    <sch:rule id="Discharge-Medication-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.105']">
      <sch:assert id="a-4444-26956-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-26956) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.105" (CONF:4444-26957). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27037).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Discharge-Medication-pattern-errors">
    <sch:rule id="Discharge-Medication-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.105'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-16550-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-16550).</sch:assert>
      <sch:assert id="a-4444-16551-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-16551).</sch:assert>
      <sch:assert id="a-4444-26956-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.105'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-26956) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.105" (CONF:4444-26957). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27037).</sch:assert>
      <!-- 05-18-2020 Removed 4444-26955 per STU comment 1973 -->
      <!-- <sch:assert id="a-4444-26955-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:4444-26955).  </sch:assert> -->
      <sch:assert id="a-4444-16552-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-16552).</sch:assert>
      <sch:assert id="a-4444-16553-error" test="count(cda:entryRelationship[@typeCode='SUBJ'] [count(cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16' ][@extension='2014-06-09']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-16553) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-16554) SHALL contain exactly one [1..1] Medication Activity (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.16:2014-06-09) (CONF:4444-16555).</sch:assert>
      <!-- 4444-29845 added for STU 5.2 -->
      <sch:assert id="a-4444-29845-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Medication Not Discharge (CONF:4444-29845).</sch:assert>
    </sch:rule>
    <sch:rule id="Discharge-Medication-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.105'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28140-error" test="@code='75311-1'">This code SHALL contain exactly one [1..1] @code="75311-1" Discharge medications (CONF:4444-28140).</sch:assert>
      <sch:assert id="a-4444-28141-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-28141).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Drug-Monitoring-Act-pattern-errors">
    <sch:rule id="Drug-Monitoring-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.123']]">
      <sch:assert id="a-1098-30823-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-30823).</sch:assert>
      <sch:assert id="a-1098-28656-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" (CONF:1098-28656).</sch:assert>
      <sch:assert id="a-1098-28657-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.123'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-28657) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.123" (CONF:1098-28658).</sch:assert>
      <sch:assert id="a-1098-31920-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:1098-31920).</sch:assert>
      <sch:assert id="a-1098-28660-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-28660).</sch:assert>
      <sch:assert id="a-1098-31921-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-31921).</sch:assert>
      <sch:assert id="a-1098-31922-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-31922).</sch:assert>
      <sch:assert id="a-1098-28661-error" test="count(cda:participant[@typeCode='RESP'][count(cda:participantRole[@classCode='ASSIGNED'][count(cda:id)&gt;=1] [count(cda:playingEntity[@classCode='PSN'][count(cda:name)=1])=1])=1])=1">SHALL contain at least one [1..*] participant (CONF:1098-28661) such that it SHALL contain exactly one [1..1] @typeCode="RESP" (CONF:1098-28663). SHALL contain exactly one [1..1] participantRole (CONF:1098-28662). This participantRole SHALL contain exactly one [1..1] @classCode="ASSIGNED" (CONF:1098-28664). This participantRole SHALL contain at least one [1..*] id (CONF:1098-28665). This participantRole SHALL contain exactly one [1..1] playingEntity (CONF:1098-28667). This playingEntity SHALL contain exactly one [1..1] @classCode="PSN" (CONF:1098-28668).  This playingEntity SHALL contain exactly one [1..1] US Realm Patient Name (PTN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1) (CONF:1098-28669).</sch:assert>
    </sch:rule>
    <sch:rule id="Drug-Monitoring-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.123']]/cda:code">
      <sch:assert id="a-1098-30818-error" test="@code='395170001'">This code SHALL contain exactly one [1..1] @code="395170001" medication monitoring (regime/therapy) (CONF:1098-30818).</sch:assert>
      <sch:assert id="a-1098-30819-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:1098-30819).</sch:assert>
    </sch:rule>
    <sch:rule id="Drug-Monitoring-Act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.123']]/cda:statusCode">
      <sch:assert id="a-1098-32358-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ActStatus urn:oid:2.16.840.1.113883.1.11.159331 DYNAMIC (CONF:1098-32358).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Drug-Vehicle-pattern-errors">
    <sch:rule id="Drug-Vehicle-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.24']]">
      <sch:assert id="a-81-7490-error" test="@classCode='MANU'">SHALL contain exactly one [1..1] @classCode="MANU" (CodeSystem: RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:81-7490).</sch:assert>
      <sch:assert id="a-81-7495-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.24'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-7495) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.24" (CONF:81-10493).</sch:assert>
      <sch:assert id="a-81-19137-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:81-19137).</sch:assert>
      <sch:assert id="a-81-7492-error" test="count(cda:playingEntity)=1">SHALL contain exactly one [1..1] playingEntity (CONF:81-7492).</sch:assert>
    </sch:rule>
    <sch:rule id="Drug-Vehicle-code-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.24']]/cda:code">
      <sch:assert id="a-81-30818-error" test="@code='412307009'">This code SHALL contain exactly one [1..1] @code="412307009" Drug Vehicle (CONF:81-19138).</sch:assert>
      <sch:assert id="a-81-26502-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:81-26502).</sch:assert>
    </sch:rule>
    <sch:rule id="Drug-Vehicle-playingEntity-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.24']]/cda:playingEntity">
      <sch:assert id="a-81-7493-error" test="count(cda:code)=1">This playingEntity SHALL contain exactly one [1..1] code (CONF:81-7493).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="eMeasure-Reference-QDM-pattern-errors">
    <sch:rule id="eMeasure-Reference-QDM-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]">
      <sch:assert id="a-67-12805-error" test="@classCode='CLUSTER'">SHALL contain exactly one [1..1] @classCode="CLUSTER" cluster (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:67-12805).</sch:assert>
      <sch:assert id="a-67-12806-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:67-12806).</sch:assert>
      <!-- 07-15-2019 Added assert for 67-27018 https://tracker.esacinc.com/browse/QRDA-617  -->
      <sch:assert id="a-67-27018-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97'])=1">SHALL contain exactly one [1..1] templateId (CONF:67-27018) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.97" (CONF:67-27019).</sch:assert>
      <sch:assert id="a-67-12807-error" test="count(cda:statusCode[@code='completed'])=1">SHALL contain exactly one [1..1] statusCode="completed" completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:67-12807).</sch:assert>
      <sch:assert id="a-67-12808-error" test="count(cda:reference[@typeCode='REFR'] [count(cda:externalDocument)=1])=1">SHALL contain exactly one [1..1] reference (CONF:67-12808) such that it SHALL contain exactly one [1..1] @typeCode="REFR" refers to (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:67-12809). SHALL contain exactly one [1..1] externalDocument (CONF:67-12810).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-externalDocument-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:reference/cda:externalDocument">
      <sch:assert id="a-67-27017-error" test="@classCode='DOC'">This externalDocument SHALL contain exactly one [1..1] @classCode="DOC" Document (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:67-27017).</sch:assert>
      <sch:assert id="a-67-12811-error" test="count(cda:id[@root='2.16.840.1.113883.4.738'][@extension])=1">This externalDocument SHALL contain exactly one [1..1] id (CONF:67-12811) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.738" (CONF:67-12812). SHALL contain exactly one [1..1] @extension (CONF:67-12813).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component">
      <sch:assert id="a-67-16679-error" test="count(cda:observation)=1">The component, if present, SHALL contain exactly one [1..1] observation (CONF:67-16679).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-observation-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component/cda:observation">
      <sch:assert id="a-67-16680-error" test="@negationInd">This observation SHALL contain exactly one [1..1] @negationInd (CONF:67-16680).</sch:assert>
      <sch:assert id="a-67-16681-error" test="count(cda:code)=1">This observation SHALL contain exactly one [1..1] code (CONF:67-16681).</sch:assert>
      <sch:assert id="a-67-16683-error" test="count(cda:value[@xsi:type='CD'])=1">This observation SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:67-16683).</sch:assert>
      <sch:assert id="a-67-16684-error" test="count(cda:reference)=1">This observation SHALL contain exactly one [1..1] reference (CONF:67-16684).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-observation-code-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component/cda:observation/cda:code">
      <sch:assert id="a-67-16682-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:67-16682).</sch:assert>
      <sch:assert id="a-67-27010-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:67-27010).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-observation-reference-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component/cda:observation/cda:reference">
      <sch:assert id="a-67-16685-error" test="@typeCode='REFR'">This reference SHALL contain exactly one [1..1] @typeCode="REFR" Refers to (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:67-16685).</sch:assert>
      <sch:assert id="a-67-16686-error" test="count(cda:externalObservation)=1">This reference SHALL contain exactly one [1..1] externalObservation (CONF:67-16686).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-observation-reference-externalObservation-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component/cda:observation/cda:reference/cda:externalObservation">
      <sch:assert id="a-67-16693-error" test="@classCode='OBS'">This externalObservation SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:67-16693).</sch:assert>
      <sch:assert id="a-67-16694-error" test="@moodCode='EVN'">This externalObservation SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:67-16694).</sch:assert>
      <sch:assert id="a-67-16687-error" test="count(cda:id)=1">This externalObservation SHALL contain exactly one [1..1] id (CONF:67-16687).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-observation-referenceRange-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component/cda:observation/cda:referenceRange">
      <sch:assert id="a-67-16690-error" test="count(cda:observationRange)=1">The referenceRange, if present, SHALL contain exactly one [1..1] observationRange (CONF:67-16690).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-component-observation-referenceRange-observationRange-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:component/cda:observation/cda:referenceRange/observationRange">
      <sch:assert id="a-67-16691-error" test="count(cda:value[@xsi:type='REAL'])=1">This observationRange SHALL contain exactly one [1..1] value with @xsi:type="REAL" (CONF:67-16691).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Activity-pattern-extension-check">
    <sch:rule id="Encounter-Activity-extension-check" context="cda:encounter/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49']">
      <sch:assert id="a-1198-8712-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-8712) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.49" (CONF:1198-26353). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32546).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Activity-pattern-errors">
    <sch:rule id="Encounter-Activity-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8710-error" test="@classCode='ENC'">SHALL contain exactly one [1..1] @classCode="ENC" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-8710).</sch:assert>
      <sch:assert id="a-1198-8711-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-8711).</sch:assert>
      <sch:assert id="a-1198-8712-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-8712) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.49" (CONF:1198-26353). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32546).</sch:assert>
      <sch:assert id="a-1198-8713-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:1198-8713).</sch:assert>
      <sch:assert id="a-1198-8714-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-8714).</sch:assert>
      <sch:assert id="a-1198-8715-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1198-8715).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Activity-code-originalText-reference-value-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]/cda:code/cda:originalText/cda:reference">
      <sch:assert id="a-1198-15972-error" test="starts-with(@value,'#')">This reference/@value SHALL begin with a '#' and SHALL point to its corresponding narrative (using the approach defined in CDA Release 2, section 4.3.5.1) (CONF:1198-15972).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Activity-performer-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]/cda:performer">
      <sch:assert id="a-1198-8726-error" test="count(cda:assignedEntity)=1">The performer, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1198-8726).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Diagnosis-QDM-pattern-errors">
    <sch:rule id="Encounter-Diagnosis-QDM-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.168'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29937-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CONF:4444-29937)</sch:assert>
      <sch:assert id="a-4444-29938-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29938).</sch:assert>
      <sch:assert id="a-4444-29939-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-29939).</sch:assert>
      <sch:assert id="a-4444-29931-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.168'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29931) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.168" (CONF:4444-29934). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29935).</sch:assert>
      <sch:assert id="a-4444-29930-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29930).</sch:assert>
      <sch:assert id="a-4444-29936-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-29936).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Diagnosis-QDM-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.168'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29932-error" test="@code='29308-4'">This code SHALL contain exactly one [1..1] @code="29308-4" Diagnosis (CONF:4444-29932).</sch:assert>
      <sch:assert id="a-4444-29933-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-29933).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Diagnosis-pattern-extension-check">
    <sch:rule id="Encounter-Diagnosis-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.80']">
      <sch:assert id="a-1198-14895-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-14895) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.80" (CONF:1198-14896). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32542).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Diagnosis-pattern-errors">
    <sch:rule id="Encounter-Diagnosis-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.80'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-14889-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-14889).</sch:assert>
      <sch:assert id="a-1198-14890-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-14890).</sch:assert>
      <sch:assert id="a-1198-14895-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.80'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-14895) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.80" (CONF:1198-14896). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32542).</sch:assert>
      <sch:assert id="a-1198-19182-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-19182).</sch:assert>
      <sch:assert id="a-1198-14892-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']])=1])&gt;0">SHALL contain at least one [1..*] entryRelationship (CONF:1198-14892) such that it  SHALL contain exactly one [1..1] @typeCode="SUBJ" (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1198-14893). SHALL contain exactly one [1..1] Problem Observation (V3) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.4:2015-08-01) (CONF:1198-14898).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Diagnosis-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.80'][@extension='2015-08-01']]/cda:code">
      <sch:assert id="a-1198-19183-error" test="@code='29308-4'">This code SHALL contain exactly one [1..1] @code="29308-4" Diagnosis (CONF:1198-19183).</sch:assert>
      <sch:assert id="a-1198-32160-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:1198-32160).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Order-Act-pattern-extension-check">
    <sch:rule id="Encounter-Order-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.132']">
      <sch:assert id="a-4444-28467-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28467) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.132" (CONF:4444-28471). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29410).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Order-Act-pattern-errors">
    <sch:rule id="Encounter-Order-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.132'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28469-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28469).</sch:assert>
      <sch:assert id="a-4444-28470-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28470).</sch:assert>
      <sch:assert id="a-4444-28467-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.132'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28467) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.132" (CONF:4444-28471). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29410).</sch:assert>
      <sch:assert id="a-4444-29409-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29409).</sch:assert>
      <sch:assert id="a-4444-28468-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.22'][@extension='2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28468) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28473).  SHALL contain exactly one [1..1] Encounter Order (V5) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.22:2019-12-01) (CONF:4444-28474).</sch:assert>
      <!-- 4444-29640 added for STU 5.2 -->
      <sch:assert id="a-4444-29640-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Encounter Not Order (CONF:4444-29640).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Order-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.132'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29411-error" test="@code='ENC'">This code SHALL contain exactly one [1..1] @code="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29411).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Order-pattern-extension-check">
    <sch:rule id="Encounter-Order-extension-check" context="cda:encounter/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.22']">
      <sch:assert id="a-4444-11933-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11933) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.22" (CONF:4444-11934).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27064).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Order-pattern-errors">
    <sch:rule id="Encounter-Order-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.22'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27534-error" test="@classCode='ENC'">SHALL contain exactly one [1..1] @classCode="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27534).</sch:assert>
      <sch:assert id="a-4444-11932-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11932).</sch:assert>
      <sch:assert id="a-4444-11933-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.22'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11933) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.22" (CONF:4444-11934).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27064).</sch:assert>
      <sch:assert id="a-4444-11936-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-11936).</sch:assert>
      <sch:assert id="a-4444-27341-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27341).</sch:assert>
      <sch:assert id="a-4444-28668-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.132'][@extension='2019-12-01'])=1">This template SHALL be contained by an Encounter Order Act (V3) (CONF:4444-28668).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Performed-Act-pattern-extension-check">
    <sch:rule id="Encounter-Performed-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.133']">
      <sch:assert id="a-4444-28475-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28475) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.133" (CONF:4444-28479). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29422).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Performed-Act-pattern-errors">
    <sch:rule id="Encounter-Performed-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.133'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28477-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28477).</sch:assert>
      <sch:assert id="a-4444-28478-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28478).</sch:assert>
      <sch:assert id="a-4444-28475-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.133'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28475) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.133" (CONF:4444-28479). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29422).</sch:assert>
      <sch:assert id="a-4444-29421-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29421).</sch:assert>
      <sch:assert id="a-4444-28476-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23'][@extension='2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28476) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28481). SHALL contain exactly one [1..1] Encounter Performed (V5) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.23:2019-12-01) (CONF:4444-28482).</sch:assert>
      <!-- 4444-29929 added for STU 5.2 -->
      <sch:assert id="a-4444-29929-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Encounter Not Performed (CONF:4444-29929).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Performed-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.133'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29423-error" test="@code='ENC'">This code SHALL contain exactly one [1..1] @code="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29423).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Performed-pattern-extension-check">
    <sch:rule id="Encounter-Performed-extension-check" context="cda:encounter/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23']">
      <sch:assert id="a-4444-11861-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11861) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.23" (CONF:4444-11862).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26552).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Performed-pattern-errors">
    <sch:rule id="Encounter-Performed-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27532-error" test="@classCode='ENC'">SHALL contain exactly one [1..1] @classCode="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27532).</sch:assert>
      <sch:assert id="a-4444-27533-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-27533).</sch:assert>
      <sch:assert id="a-4444-11861-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11861) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.23" (CONF:4444-11862).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26552).</sch:assert>
      <sch:assert id="a-4444-29416-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-29416).</sch:assert>
      <sch:assert id="a-4444-27624-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27624).</sch:assert>
      <sch:assert id="a-4444-11874-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-11874).</sch:assert>
      <sch:assert id="a-4444-11876-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-11876).</sch:assert>
      <sch:assert id="a-4444-29420-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.133'][@extension='2019-12-01'])=1">This template SHALL be contained by an Encounter Performed Act (V3) (CONF:4444-29420).</sch:assert>
      <!-- 05-06-2020 Added 4444-30035 -->
      <sch:assert id="a-4444-30035-error" test="count(cda:entryRelationship[count(cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.80'][@extension='2015-08-01']])&gt;=1])=0">SHALL NOT contain [0..0] entryRelationship (CONF:4444-30035) such that it  SHALL contain one or more [1..*] Encounter Diagnosis (V3) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.80:2015-08-01) (CONF:4444-30036).</sch:assert>
      <!-- 11-04-2020 Added test for having at most one diagnosis of rank = 1 https://tracker.esacinc.com/browse/QRDA-938 -->
      <sch:assert id="a-Diagnosis-Count-error" test="count(cda:entryRelationship[cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.168'][@extension='2019-12-01']][cda:entryRelationship[cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.166'][@extension='2019-12-01']]/cda:value[@xsi:type='INT'][@value=1]]]]) &lt;= 1">SHALL contain at most one Encounter Diagnosis QDM of rank 1, as principal diagnosis.</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Performed-id-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23'][@extension='2019-12-01']]/cda:id">
      <sch:assert id="a-4444-29418-error" test="@root">Such ids SHALL contain exactly one [1..1] @root (CONF:4444-29418).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Performed-statusCode-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-11875-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-11875).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Performed-effectiveTime-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.23'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-11877-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:4444-11877).</sch:assert>
      <sch:assert id="a-4444-11878-error" test="count(cda:high)=1">This effectiveTime SHALL contain exactly one [1..1] high (CONF:4444-11878).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Recommended-Act-pattern-extension-check">
    <sch:rule id="Encounter-Recommended-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.134']">
      <sch:assert id="a-4444-28485-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28485) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.134" (CONF:4444-28490). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29654).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Recommended-Act-pattern-errors">
    <sch:rule id="Encounter-Recommended-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.134'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28487-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28487).</sch:assert>
      <sch:assert id="a-4444-28488-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28488).</sch:assert>
      <sch:assert id="a-4444-28485-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.134'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28485) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.134" (CONF:4444-28490). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29654).</sch:assert>
      <sch:assert id="a-4444-28677-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-28677).</sch:assert>
      <sch:assert id="a-4444-28486-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.24'][@extension='2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28486) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28492).  SHALL contain exactly one [1..1] Encounter Recommended (V4) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.24:2019-12-01) (CONF:4444-28493).</sch:assert>
      <!-- 4444-29658 added for STU 5.2 -->
      <sch:assert id="a-4444-29658-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Encounter Not Recommended (CONF:4444-29658).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Recommended-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.134'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29655-error" test="@code='ENC'">This code SHALL contain exactly one [1..1] @code="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29655).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Recommended-pattern-extension-check">
    <sch:rule id="Encounter-Recommended-extension-check" context="cda:encounter/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.24']">
      <sch:assert id="a-4444-11912-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11912) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.24" (CONF:4444-11913).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27066).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Recommended-pattern-errors">
    <sch:rule id="Encounter-Recommended-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.24'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27549-error" test="@classCode='ENC'">SHALL contain exactly one [1..1] @classCode="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27549).</sch:assert>
      <sch:assert id="a-4444-11911-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11911).</sch:assert>
      <sch:assert id="a-4444-11912-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.24'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11912) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.24" (CONF:4444-11913).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27066).</sch:assert>
      <sch:assert id="a-4444-11915-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-11915).</sch:assert>
      <sch:assert id="a-4444-27347-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01)  (CONF:4444-27347).</sch:assert>
      <sch:assert id="a-4444-29496-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.134'][@extension='2019-12-01'])=1">This template SHALL be contained by an Encounter Recommended Act (V3) (CONF:4444-29496).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Entity_Care_Partner-pattern-errors">
    <sch:rule id="Entity_Care_Partner-playingEntity-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.160'][@extension='2019-12-01']]/cda:playingEntity">
      <sch:assert id="a-4444-28798-error" test="count(cda:code)=1">The playingEntity, if present, SHALL contain exactly one [1..1] code (CONF:4444-28798).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Entity_Organization-pattern-errors">
    <sch:rule id="Entity_Organization-playingEntity-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.163'][@extension='2019-12-01']]/cda:playingEntity">
      <sch:assert id="a-4444-28806-error" test="count(cda:code)=1">The playingEntity, if present, SHALL contain exactly one [1..1] code (CONF:4444-28806).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Entity_Patient-pattern-errors">
    <sch:rule id="Entity_Patient-id-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.161'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28785-error" test="count(cda:id[@root][@extension])=1">SHALL contain exactly one [1..1] id (CONF:4444-28785) such that it  SHALL contain exactly one [1..1] @root (CONF:4444-28789) SHALL contain exactly one [1..1] @extension (CONF:4444-28790).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Entity_Practitioner-pattern-errors">
    <sch:rule id="Entity_Practitioner-playingEntity-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.162'][@extension='2019-12-01']]/cda:playingEntity">
      <sch:assert id="a-4444-28814-error" test="count(cda:code)=1">The playingEntity, if present, SHALL contain exactly one [1..1] code (CONF:4444-28814).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Entry-Reference-pattern-errors">
    <sch:rule id="Entry-Reference-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.122']]">
      <sch:assert id="a-1098-31485-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-31485).</sch:assert>
      <sch:assert id="a-1098-31486-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-31486).</sch:assert>
      <sch:assert id="a-1098-31487-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.122'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-31487) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.122" (CONF:1098-31488).</sch:assert>
      <sch:assert id="a-1098-31489-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:1098-31489).</sch:assert>
      <sch:assert id="a-1098-31490-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-31490).</sch:assert>
      <sch:assert id="a-1098-31498-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] code (CONF:1098-31498).</sch:assert>
    </sch:rule>
    <sch:rule id="Entry-Reference-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.122']]/cda:code">
      <sch:assert id="a-1098-31491-error" test="@nullFlavor='NP'">This code SHALL contain exactly one [1..1] @nullFlavor="NP" Not Present (CodeSystem: HL7NullFlavor urn:oid:2.16.840.1.113883.5.1008) (CONF:1098-31491).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="External-Document-Reference-pattern-errors">
    <sch:rule id="External-Document-Reference-errors" context="cda:externalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.115'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-31931-error" test="@classCode='DOCCLIN'">SHALL contain exactly one [1..1] @classCode="DOCCLIN" Clinical Document (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-31931).</sch:assert>
      <sch:assert id="a-1098-31932-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-31932).</sch:assert>
      <sch:assert id="a-1098-32748-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.115'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-32748) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.115" (CONF:1098-32750).SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32749).</sch:assert>
      <sch:assert id="a-1098-32751-error" test="count(cda:id)=1">SHALL contain exactly one [1..1] id (CONF:1098-32751).</sch:assert>
      <sch:assert id="a-1098-31933-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-31933).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Facility-Location-pattern-extension-check">
    <sch:rule id="Facility-Location-extension-errors" context="cda:participant/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100']">
      <sch:assert id="a-3343-13375-extension-error" test="@extension='2017-08-01'">Extension missing SHALL contain exactly one [1..1] templateId (CONF:3343-13375) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.100" (CONF:3343-13376) SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-28729).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Facility-Location-pattern-errors">
    <sch:rule id="Facility-Location-errors" context="cda:participant[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-13375-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-13375) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.100" (CONF:3343-13376) SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-28729).</sch:assert>
      <sch:assert id="a-3343-13374-error" test="@typeCode='LOC'">SHALL contain exactly one [1..1] @typeCode="LOC" location (CodeSystem: HL7ParticipationType urn:oid:2.16.840.1.113883.5.90 STATIC) (CONF:3343-13374).</sch:assert>
      <sch:assert id="a-3343-13371-error" test="count(cda:time)=1">SHALL contain exactly one [1..1] time (CONF:3343-13371).</sch:assert>
      <sch:assert id="a-3343-13372-error" test="count(cda:participantRole)=1">SHALL contain exactly one [1..1] participantRole (CONF:3343-13372).</sch:assert>
    </sch:rule>
    <sch:rule id="Facility-Location-participantRole-errors" context="cda:participant[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100'][@extension='2017-08-01']]/cda:participantRole">
      <sch:assert id="a-3343-13373-error" test="@classCode='SDLOC'">This participantRole SHALL contain exactly one [1..1] @classCode="SDLOC" service delivery location (CodeSystem: RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:3343-13373).</sch:assert>
      <sch:assert id="a-3343-13378-error" test="count(cda:code)=1">This participantRole SHALL contain exactly one [1..1] code (CONF:3343-13378).</sch:assert>
    </sch:rule>
    <sch:rule id="Facility-Location-participantRole-playingEntity-errors" context="cda:participant[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100'][@extension='2017-08-01']]/cda:participantRole/cda:playingEntity">
      <sch:assert id="a-3343-13382-error" test="@classCode='PLC'">The playingEntity, if present, SHALL contain exactly one [1..1] @classCode="PLC" place (CodeSystem: EntityClass urn:oid:2.16.840.1.113883.5.41 STATIC) (CONF:3343-13382).</sch:assert>
    </sch:rule>
    <sch:rule id="Facility-Location-time-errors" context="cda:participant[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100'][@extension='2017-08-01']]/cda:time">
      <sch:assert id="a-3343-13384-error" test="count(cda:low)=1">This time SHALL contain exactly one [1..1] low (CONF:3343-13384).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Death_Observation-pattern-errors">
    <sch:rule id="Family_History_Death_Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.47']]">
      <sch:assert id="a-81-8621-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:81-8621).</sch:assert>
      <sch:assert id="a-81-8622-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:81-8622).</sch:assert>
      <sch:assert id="a-81-8623-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.47'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-8623) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.47" (CONF:81-10495).</sch:assert>
      <sch:assert id="a-81-19141-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:81-19141).</sch:assert>
      <sch:assert id="a-81-8625-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:81-8625).</sch:assert>
      <sch:assert id="a-81-8626-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:81-8626).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Death_Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.47']]/cda:code">
      <sch:assert id="a-81-19142-error" test="@code ='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:81-19142).</sch:assert>
      <sch:assert id="a-81-26504-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:81-26504).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Death_Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.47']]/cda:statusCode">
      <sch:assert id="a-81-19097-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:81-19097).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Death_Observation-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.47']]/cda:value">
      <sch:assert id="a-81-26470-error" test="@code='419099009'">This value SHALL contain exactly one [1..1] @code="419099009" Dead (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96 STATIC) (CONF:81-26470).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Observation_QDM-pattern-extension-check">
    <sch:rule id="Family_History_Observation_QDM-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.112']">
      <sch:assert id="a-4444-27675-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-27675) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.112" (CONF:4444-27681). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27682).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Observation_QDM-pattern-errors">
    <sch:rule id="Family_History_Observation_QDM-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.112'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27685-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27685).</sch:assert>
      <sch:assert id="a-4444-27686-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-27686).</sch:assert>
      <sch:assert id="a-4444-28057-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28057).</sch:assert>
      <sch:assert id="a-4444-27675-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.112'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-27675) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.112" (CONF:4444-27681). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27682).</sch:assert>
      <sch:assert id="a-4444-27688-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-27688).</sch:assert>
      <sch:assert id="a-4444-28571-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-28571).</sch:assert>
      <sch:assert id="a-4444-28661-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.12'][@extension='2019-12-01'])=1">This template SHALL be contained by a Family History Organizer QDM (V5) (CONF:4444-28661).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Observation-pattern-extension-check">
    <sch:rule id="Family_History_Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46']">
      <sch:assert id="a-1198-8599-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-8599) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.46" (CONF:1198-10496). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32605).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Observation-pattern-errors">
    <sch:rule id="Family_History_Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8586-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-8586).</sch:assert>
      <sch:assert id="a-1198-8587-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-8587).</sch:assert>
      <sch:assert id="a-1198-8599-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-8599) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.46" (CONF:1198-10496). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32605).</sch:assert>
      <sch:assert id="a-1198-8592-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1198-8592).</sch:assert>
      <!-- 08-14-2019 Changed conformance text for 1198-32427 from STATIC to DYNAMIC -->
      <sch:assert id="a-1198-32427-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHOULD be selected from ValueSet Problem Type (SNOMEDCT) 2.16.840.1.113883.3.88.12.3221.7.2 DYNAMIC (CONF:1198-32427).</sch:assert>
      <sch:assert id="a-1198-8590-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-8590).</sch:assert>
      <sch:assert id="a-1198-8591-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Problem urn:oid:2.16.840.1.113883.3.88.12.3221.7.4 DYNAMIC (CONF:1198-8591).</sch:assert>
    </sch:rule>
    <!-- 08-16-2019 Conformance 1198-32847 should be ignored due to the new conformance text...we do not test for this condition. -->
    <!-- 08-14-2019 Changed conformance text for 1198-32847 from STATIC to DYNAMIC -->
    <!--
		<sch:rule id="Family_History_Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]/cda:code">
			<sch:assert id="a-1198-32847-error" test="count(cda:translation) &gt; 0"> If code is selected from ValueSet Problem Type (SNOMEDCT) 2.16.840.1.113883.3.88.12.3221.7.2 DYNAMIC, then it SHALL have at least one [1..*] translation, which SHOULD be selected from ValueSet Problem Type (LOINC) 2.16.840.1.113762.1.4.1099.28 DYNAMIC (CONF:1198-32847).</sch:assert>
		</sch:rule>
		-->
    <sch:rule id="Family_History_Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-19098-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1198-19098).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Organizer_QDM-pattern-extension-check">
    <sch:rule id="Family_History_Organizer_QDM-extension-check" context="cda:organizer/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.12']">
      <sch:assert id="a-4444-14175-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-14175) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.12" (CONF:4444-14176). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26553).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Organizer_QDM-pattern-errors">
    <sch:rule id="Family_History_Organizer_QDM-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.12'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-14175-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.12'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-14175) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.12" (CONF:4444-14176). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26553).</sch:assert>
      <sch:assert id="a-4444-27715-error" test="(count(cda:component[count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.112'][@extension='2019-12-01']]) = 1]) &gt; 0)">SHALL contain at least one [1..*] component (CONF:4444-27715) such that it SHALL contain exactly one [1..1] Family History Observation QDM (V4) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.112:2019-12-01) (CONF:4444-27716).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Organizer-pattern-extension-check">
    <sch:rule id="Family_History_Organizer-extension-check" context="cda:organizer/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45']">
      <sch:assert id="a-1198-8604-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-8604) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.45" (CONF:1198-10497). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32606).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Organizer-pattern-errors">
    <sch:rule id="Family_History_Organizer-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8600-error" test="@classCode='CLUSTER'">SHALL contain exactly one [1..1] @classCode="CLUSTER" Cluster (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-8600).</sch:assert>
      <sch:assert id="a-1198-8601-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-8601).</sch:assert>
      <sch:assert id="a-1198-8604-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-8604) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.45" (CONF:1198-10497). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32606).</sch:assert>
      <sch:assert id="a-1198-32485-error" test="count(cda:id)  &gt; 0">SHALL contain at least one [1..*] id (CONF:1198-32485).</sch:assert>
      <sch:assert id="a-1198-8602-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1198-8602).</sch:assert>
      <sch:assert id="a-1198-8609-error" test="count(cda:subject)  = 1">SHALL contain exactly one [1..1] subject (CONF:1198-8609).</sch:assert>
      <sch:assert id="a-1198-32428-error" test="count(cda:component) &gt; 0">SHALL contain at least one [1..*] component (CONF:1198-32428).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Organizer-statusCode-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-19099-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1198-19099).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Organizer-subject-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:subject">
      <sch:assert id="a-1198-15244-error" test="count(cda:relatedSubject) = 1">This subject SHALL contain exactly one [1..1] relatedSubject (CONF:1198-15244).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Organizer-relatedSubject-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:subject/cda:relatedSubject">
      <sch:assert id="a-1198-15245-error" test="@classCode='PRS'">This relatedSubject SHALL contain exactly one [1..1] @classCode="PRS" Person (CodeSystem: EntityClass urn:oid:2.16.840.1.113883.5.41 STATIC) (CONF:1198-15245).</sch:assert>
      <sch:assert id="a-1198-15246-error" test="count(cda:code) = 1">This relatedSubject SHALL contain exactly one [1..1] code (CONF:1198-15246).</sch:assert>
    </sch:rule>
    <!-- 07-15-2019 Removed assertion test for 1198-15247: code SHALL contain one @code. (no longer exists in IG)  https://tracker.esacinc.com/browse/QRDA-617 -->
    <sch:rule id="Family_History_Organizer-relatedSubject-subject-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:subject/cda:relatedSubject/cda:subject">
      <sch:assert id="a-1198-15974-error" test="count(cda:administrativeGenderCode) = 1">The subject, if present, SHALL contain exactly one [1..1] administrativeGenderCode (CONF:1198-15974).</sch:assert>
    </sch:rule>
    <!-- 07-15-2019  Removed assertion test for 1198-15975: administrativeGenderCode SHALL contain one @code. (No longer exists in IG) https://tracker.esacinc.com/browse/QRDA-617  -->
    <sch:rule id="Family_History_Organizer-component-subject-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:component">
      <sch:assert id="a-1198-32429-error" test="count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]) = 1">Such components SHALL contain exactly one [1..1] Family History Observation (V3) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.46:2015-08-01) (CONF:1198-32429).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Goal_Observation-pattern-errors">
    <sch:rule id="Goal_Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.121']]">
      <sch:assert id="a-1098-30418-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-30418).</sch:assert>
      <sch:assert id="a-1098-30419-error" test="@moodCode='GOL'">SHALL contain exactly one [1..1] @moodCode="GOL" Goal (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-30419).</sch:assert>
      <sch:assert id="a-1098-8583-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.121'][not(@extension)]) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-8583) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.121" (CONF:1098-10512).</sch:assert>
      <sch:assert id="a-1098-32332-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-32332).</sch:assert>
      <sch:assert id="a-1098-30784-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code, which SHOULD be selected from CodeSystem LOINC (urn:oid:2.16.840.1.113883.6.1) (CONF:1098-30784).</sch:assert>
      <sch:assert id="a-1098-32333-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-32333).</sch:assert>
    </sch:rule>
    <sch:rule id="Goal_Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.121']]/cda:statusCode">
      <sch:assert id="a-1098-32334-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-32334).</sch:assert>
    </sch:rule>
    <sch:rule id="Goal_Observation-may-reference-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.121']]/cda:reference">
      <sch:assert id="a-1098-32755-error" test="@typeCode='REFR'">The reference, if present, SHALL contain exactly one [1..1] @typeCode="REFR" Refers to (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:1098-32755).</sch:assert>
      <sch:assert id="a-1098-32756-error" test="count(cda:externalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.115'][@extension='2014-06-09']])=1">The reference, if present, SHALL contain exactly one [1..1] External Document Reference (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.115:2014-06-09) (CONF:1098-32756).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_activity-pattern-extension-check">
    <sch:rule id="Immunization_activity-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52']">
      <sch:assert id="a-1198-8828-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-8828) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.52" (CONF:1198-10498). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32528).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_activity-pattern-errors">
    <sch:rule id="Immunization_activity-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8826-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-8826).</sch:assert>
      <sch:assert id="a-1198-8827-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.18']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet MoodCodeEvnInt urn:oid:2.16.840.1.113883.11.20.9.18 STATIC 2014-09-01 (CONF:1198-8827).</sch:assert>
      <sch:assert id="a-1198-8985-error" test="@negationInd">SHALL contain exactly one [1..1] @negationInd (CONF:1198-8985).</sch:assert>
      <sch:assert id="a-1198-8828-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-8828) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.52" (CONF:1198-10498). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32528).</sch:assert>
      <sch:assert id="a-1198-8829-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1198-8829).</sch:assert>
      <sch:assert id="a-1198-8833-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-8833).</sch:assert>
      <sch:assert id="a-1198-8834-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1198-8834).</sch:assert>
      <sch:assert id="a-1198-8847-error" test="count(cda:consumable)=1">SHALL contain exactly one [1..1] consumable (CONF:1198-8847).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_activity-statusCode-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-32359-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ActStatus urn:oid:2.16.840.1.113883.1.11.159331 DYNAMIC (CONF:1198-32359).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_activity-consumable-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01']]/cda:consumable">
      <sch:assert id="a-1198-15546-error" test="count(cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']])=1">This consumable SHALL contain exactly one [1..1] Immunization Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.54:2014-06-09) (CONF:1198-15546).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_administered-pattern-extension-check">
    <sch:rule id="Immunization_administered-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.140']">
      <sch:assert id="a-4444-28574-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28574) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.140" (CONF:4444-28581). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28958).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_administered-pattern-errors">
    <sch:rule id="Immunization_administered-errors" context="cda:substanceAdministration [cda:templateId[@root='2.16.840.1.113883.10.20.24.3.140'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28588-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" Substance Administration (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28588).</sch:assert>
      <sch:assert id="a-4444-28589-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28589).</sch:assert>
      <!-- 05-18-2020 Added 4444-29684 per STU comment http://www.hl7.org/dstucomments/showdetail_comment.cfm?commentid=1972  -->
      <sch:assert id="a-4444-29684-error" test="@negationInd">SHALL contain exactly one [1..1] @negationInd (CONF:4444-29684).</sch:assert>
      <sch:assert id="a-4444-28574-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.140'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28574) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.140" (CONF:4444-28581). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28958).</sch:assert>
      <sch:assert id="a-4444-28576-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-28576).</sch:assert>
      <sch:assert id="a-4444-28578-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-28578).</sch:assert>
      <sch:assert id="a-4444-28957-error" test="count(cda:consumable)=1">SHALL contain exactly one [1..1] consumable (CONF:4444-28957).</sch:assert>
      <!-- 4444-29699 added for STU 5.2 -->
      <sch:assert id="a-4444-29699-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) = 0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-29699).</sch:assert>
      <!-- 4444-29694 added for STU 5.2 -->
      <!-- 05-27-2020 QRDA-870 Updated test to remove "or (not(negationInd))" because it is now required via 4444-29684 -->
      <sch:assert id="a-4444-29694-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Immunization Not Administered (CONF:4444-29694).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_administered-statusCode-errors" context="cda:substanceAdministration [cda:templateId[@root='2.16.840.1.113883.10.20.24.3.140'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-28585-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-28585).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_administered-effectiveTime-errors" context="cda:substanceAdministration [cda:templateId[@root='2.16.840.1.113883.10.20.24.3.140'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-28959-error" test="@value">This effectiveTime SHALL contain exactly one [1..1] @value (CONF:4444-28959).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_administered-consumable-errors" context="cda:substanceAdministration [cda:templateId[@root='2.16.840.1.113883.10.20.24.3.140'][@extension='2019-12-01']]/cda:consumable">
      <sch:assert id="a-4444-28960-error" test="count(cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']])=1">This consumable SHALL contain exactly one [1..1] Immunization Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.54:2014-06-09) (CONF:4444-28960).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_medication_information-pattern-extension-check">
    <sch:rule id="Immunization_medication_information-extension-check" context="cda:manufacturedProduct/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54']">
      <sch:assert id="a-1098-9004-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-9004) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.54" (CONF:1098-10499). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32602).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_medication_information-pattern-errors">
    <sch:rule id="Immunization_medication_information-errors" context="cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-9002-error" test="@classCode='MANU'">SHALL contain exactly one [1..1] @classCode="MANU" (CodeSystem: RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:1098-9002).</sch:assert>
      <sch:assert id="a-1098-9004-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-9004) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.54" (CONF:1098-10499). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32602).</sch:assert>
      <sch:assert id="a-1098-9006-error" test="count(cda:manufacturedMaterial)=1">SHALL contain exactly one [1..1] manufacturedMaterial (CONF:1098-9006).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_medication_information-manufacturedMaterial-errors" context="cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']]/cda:manufacturedMaterial">
      <sch:assert id="a-1098-9007-error" test="count(cda:code)=1">This manufacturedMaterial SHALL contain exactly one [1..1] code, which SHALL be selected from ValueSet CVX Vaccines Administered - Vaccine Set urn:oid:2.16.840.1.113762.1.4.1010.6 DYNAMIC (CONF:1098-9007).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_order-pattern-extension-check">
    <sch:rule id="Immunization_order-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.143']">
      <sch:assert id="a-4444-28627-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28627) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.143" (CONF:4444-28634). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28923).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_order-pattern-errors">
    <sch:rule id="Immunization_order-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.143'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28645-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28645).</sch:assert>
      <sch:assert id="a-4444-28644-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28644).</sch:assert>
      <sch:assert id="a-4444-28627-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.143'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28627) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.143" (CONF:4444-28634). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28923).</sch:assert>
      <sch:assert id="a-4444-28924-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-28924).</sch:assert>
      <sch:assert id="a-4444-28646-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01)  (CONF:4444-28646).</sch:assert>
      <!-- 4444-29701 added for STU 5.2 -->
      <sch:assert id="a-4444-29701-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Immunization Not Order (CONF:4444-29701).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_order-effectiveTime-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.143'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-29702-error" test="count(@value)=1">This effectiveTime SHALL contain exactly one [1..1] @value (CONF:4444-29702).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_refusal_reason-pattern-errors">
    <sch:rule id="Immunization_refusal_reason-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.53']]">
      <sch:assert id="a-81-8991-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:81-8991).</sch:assert>
      <sch:assert id="a-81-8992-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:81-8992).</sch:assert>
      <sch:assert id="a-81-8993-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.53'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-8993) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.53" (CONF:81-10500).</sch:assert>
      <sch:assert id="a-81-8994-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:81-8994).</sch:assert>
      <sch:assert id="a-81-8995-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHALL be selected from ValueSet No Immunization Reason Value Set urn:oid:2.16.840.1.113883.1.11.19717 DYNAMIC (CONF:81-8995).</sch:assert>
      <sch:assert id="a-81-8996-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:81-8996).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_refusal_reason-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.53']]/cda:statusCode">
      <sch:assert id="a-81-19104-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:81-19104).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_Supply_Request-pattern-errors">
    <sch:rule id="Immunization_Supply_Request-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.167'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29713-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" Supply (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29713).</sch:assert>
      <sch:assert id="a-4444-29712-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-29712).</sch:assert>
      <sch:assert id="a-4444-29707-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.167'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29707) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.167" (CONF:4444-29710).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29711).</sch:assert>
      <sch:assert id="a-4444-29708-error" test="count(cda:product[count(cda:manufacturedProduct[@classCode='MANU'][cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']])=1])=1">SHALL contain exactly one [1..1] product (CONF:4444-29708) such that it  SHALL contain exactly one [1..1] Immunization Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.54:2014-06-09) (CONF:4444-29709).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Incision_datetime-pattern-errors">
    <sch:rule id="Incision_datetime-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.89']]">
      <sch:assert id="a-67-14559-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:67-14559).</sch:assert>
      <sch:assert id="a-67-11401-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:67-11401).</sch:assert>
      <sch:assert id="a-67-11402-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.89'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:67-11402) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.89" (CONF:67-11403).</sch:assert>
      <!-- Removed conformance 67-26984 on 04/04/2019. Will affect all schematrons using this template from this date forward. https://tracker.esacinc.com/browse/QRDA-568 -->
      <!-- <sch:assert id="a-67-26984-error" test="count(cda:id) &gt; 0"> SHALL contain at least one [1..*] id (CONF:67-26984).</sch:assert> -->
      <sch:assert id="a-67-14562-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:67-14562).</sch:assert>
      <sch:assert id="a-67-14561-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:67-14561).</sch:assert>
    </sch:rule>
    <sch:rule id="Incision_datetime-code-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.89']]/cda:code">
      <sch:assert id="a-67-27014-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:67-27014).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Indication-pattern-extension-check">
    <sch:rule id="Indication-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.19']">
      <sch:assert id="a-1098-7482-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7482) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.19" (CONF:1098-10502). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32570).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Indication-pattern-errors">
    <sch:rule id="Indication-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.19'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7480-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7480).</sch:assert>
      <sch:assert id="a-1098-7481-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7481).</sch:assert>
      <sch:assert id="a-1098-7482-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.19'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-7482) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.19" (CONF:1098-10502). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32570).</sch:assert>
      <sch:assert id="a-1098-7483-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-7483).</sch:assert>
      <sch:assert id="a-1098-31229-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which MAY be selected from ValueSet Problem Type urn:oid:2.16.840.1.113883.3.88.12.3221.7.2 STATIC 2014-09-02 (CONF:1098-31229).</sch:assert>
      <sch:assert id="a-1098-7487-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7487).</sch:assert>
    </sch:rule>
    <sch:rule id="Indication-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.19'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-19105-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1098-19105).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Instruction-pattern-extension-check">
    <sch:rule id="Instruction-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.20']">
      <sch:assert id="a-1098-7393-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7393) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.20" (CONF:1098-10503). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32598).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Instruction-pattern-errors">
    <sch:rule id="Instruction-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.20'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7391-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7391).</sch:assert>
      <sch:assert id="a-1098-7392-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7392).</sch:assert>
      <sch:assert id="a-1098-7393-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.20'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-7393) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.20" (CONF:1098-10503). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32598).</sch:assert>
      <sch:assert id="a-1098-16884-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHOULD be selected from ValueSet Patient Education urn:oid:2.16.840.1.113883.11.20.9.34 DYNAMIC (CONF:1098-16884).</sch:assert>
      <sch:assert id="a-1098-7396-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7396).</sch:assert>
    </sch:rule>
    <sch:rule id="Instruction-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.20'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-19106-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1098-19106).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Order-pattern-extension-check">
    <sch:rule id="Intervention_Order-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.31']">
      <sch:assert id="a-4444-13743-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13743) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.31" (CONF:4444-13744). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26556).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Order-pattern-errors">
    <sch:rule id="Intervention_Order-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.31'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27353-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27353).</sch:assert>
      <sch:assert id="a-4444-13742-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13742).</sch:assert>
      <sch:assert id="a-4444-13743-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.31'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-13743) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.31" (CONF:4444-13744). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26556).</sch:assert>
      <sch:assert id="a-4444-13746-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-13746).</sch:assert>
      <sch:assert id="a-4444-27343-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01)  (CONF:4444-27343).</sch:assert>
      <!-- 4444-29735 added for STU 5.2 -->
      <sch:assert id="a-4444-29735-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Intervention Not Order (CONF:4444-29735).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Performed-pattern-extension-check">
    <sch:rule id="Intervention_Performed-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.32']">
      <sch:assert id="a-4444-13591-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13591) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.32" (CONF:4444-13592). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27144).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Performed-pattern-errors">
    <sch:rule id="Intervention_Performed-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.32'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27354-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27354).</sch:assert>
      <sch:assert id="a-4444-13590-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13590).</sch:assert>
      <sch:assert id="a-4444-13591-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.32'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-13591) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.32" (CONF:4444-13592). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27144).</sch:assert>
      <sch:assert id="a-4444-27633-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-27633).</sch:assert>
      <sch:assert id="a-4444-27362-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-27362).</sch:assert>
      <sch:assert id="a-4444-13611-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-13611).</sch:assert>
      <sch:assert id="a-4444-30020-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) = 0">SHALL NOT contain [0..0]  Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30020).</sch:assert>
      <!-- 4444-29741 added for STU 5.2 -->
      <sch:assert id="a-4444-29741-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Intervention Not Performed (CONF:4444-29741).</sch:assert>
    </sch:rule>
    <sch:rule id="Intervention_Performed-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.32'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-27363-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-27363).</sch:assert>
    </sch:rule>
    <sch:rule id="Intervention_Performed-effectiveTime-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.32'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 05-06-2020 Added @nullFlavor to test -->
      <sch:assert id="a-4444-29743-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low  (CONF:4444-29743).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Recommended-pattern-extension-check">
    <sch:rule id="Intervention_Recommended-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.33']">
      <sch:assert id="a-4444-13764-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13764) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.33" (CONF:4444-13765). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26557).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Recommended-pattern-errors">
    <sch:rule id="Intervention_Recommended-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.33'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27355-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27355).</sch:assert>
      <sch:assert id="a-4444-13763-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13763).</sch:assert>
      <sch:assert id="a-4444-13764-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.33'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-13764) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.33" (CONF:4444-13765). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26557).</sch:assert>
      <!-- Removed 4444-29767 for STU 5.2 -->
      <!-- <sch:assert id="a-4444-29767-error" test="count(cda:id) &gt;= 1"> SHALL contain at least one [1..*] id 4444-29767.</sch:assert> -->
      <sch:assert id="a-4444-13767-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-13767).</sch:assert>
      <sch:assert id="a-4444-27349-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27349).</sch:assert>
      <!-- 4444-29762 added for STU 5.2 -->
      <sch:assert id="a-4444-29762-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Intervention Not Recommended (CONF:4444-29762).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Laboratory_Test_Order-pattern-extension-check">
    <sch:rule id="Laboratory_Test_Order-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.37']">
      <sch:assert id="a-4444-11954-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11954) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.37" (CONF:4444-11955). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27075).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Laboratory_Test_Order-pattern-errors">
    <sch:rule id="Laboratory_Test_Order-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.37'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27417-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27417).</sch:assert>
      <sch:assert id="a-4444-11953-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11953).</sch:assert>
      <sch:assert id="a-4444-11954-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.37'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-11954) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.37" (CONF:4444-11955). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27075).</sch:assert>
      <sch:assert id="a-4444-11957-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-11957).</sch:assert>
      <sch:assert id="a-4444-27344-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27344).</sch:assert>
      <!-- 4444-29558 added for STU 5.2 -->
      <sch:assert id="a-4444-29558-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Laboratory Test Not Order (CONF:4444-29558).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Laboratory_Test_Performed-pattern-extension-check">
    <sch:rule id="Laboratory_Test_Performed-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38']">
      <sch:assert id="a-4444-11721-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11721) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.38" (CONF:4444-11722). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27021).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Laboratory_Test_Performed-pattern-errors">
    <sch:rule id="Laboratory_Test_Performed-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-11705-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-11705).</sch:assert>
      <sch:assert id="a-4444-11706-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11706).</sch:assert>
      <sch:assert id="a-4444-11721-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-11721) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.38" (CONF:4444-11722). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27021).</sch:assert>
      <sch:assert id="a-4444-11707-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-11707).</sch:assert>
      <sch:assert id="a-4444-27637-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-27637).</sch:assert>
      <sch:assert id="a-4444-11709-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-11709).</sch:assert>
      <sch:assert id="a-4444-11711-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-11711) such that it  SHOULD contain zero or one [0..1] @value (CONF:4444-30017).  SHOULD contain zero or one [0..1] low (CONF:4444-11712).  MAY contain zero or one [0..1] high (CONF:4444-11713).</sch:assert>
      <sch:assert id="a-4444-29534-error" test="count(cda:value)=0">SHALL NOT contain [0..0] value (CONF:4444-29534).</sch:assert>
      <!-- 4444-29564 added for STU 5.2 -->
      <sch:assert id="a-4444-29564-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Laboratory Test Not Performed (CONF:4444-29564).</sch:assert>
    </sch:rule>
    <sch:rule id="Laboratory_Test_Performed-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 05-06-2020 Added @nullFlavor to the test -->
      <sch:assert id="a-4444-29548-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low  (CONF:4444-29548).</sch:assert>
    </sch:rule>
    <sch:rule id="Laboratory_Test_Performed-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-11710-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-11710).</sch:assert>
    </sch:rule>
    <sch:rule id="Laboratory_Test_Performed-referenceRange-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38'][@extension='2019-12-01']]/cda:referenceRange">
      <sch:assert id="a-4444-29527-error" test="count(cda:observationRange)=1">The referenceRange, if present, SHALL contain exactly one [1..1] observationRange (CONF:4444-29527).</sch:assert>
    </sch:rule>
    <sch:rule id="Laboratory_Test_Performed-referenceRange-observationRange-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.38'][@extension='2019-12-01']]/cda:referenceRange/cda:observationRange">
      <sch:assert id="a-4444-29531-error" test="count(cda:code)=0">This observationRange SHALL NOT contain [0..0] code (CONF:4444-29531).</sch:assert>
      <sch:assert id="a-4444-29532-error" test="count(cda:value[@xsi:type='IVL_PQ'])=1">This observationRange SHALL contain exactly one [1..1] value with @xsi:type="IVL_PQ" (CONF:4444-29532).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Laboratory_Test_Recommended-pattern-extension-check">
    <sch:rule id="Laboratory_Test_Recommended-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.39']">
      <sch:assert id="a-4444-11794-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11794) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.39" (CONF:4444-11795).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27077).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Laboratory_Test_Recommended-pattern-errors">
    <sch:rule id="Laboratory_Test_Recommended-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.39'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27416-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27416).</sch:assert>
      <sch:assert id="a-4444-11793-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11793).</sch:assert>
      <sch:assert id="a-4444-11794-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.39'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-11794) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.39" (CONF:4444-11795).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27077).</sch:assert>
      <sch:assert id="a-4444-27639-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-27639).</sch:assert>
      <sch:assert id="a-4444-27350-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27350).</sch:assert>
      <!-- 4444-29776 added for STU 5.2 -->
      <sch:assert id="a-4444-29776-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Laboratory Test Not Recommended (CONF:4444-29776).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Measure_Reference-pattern-errors">
    <sch:rule id="Measure_Reference-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.98']]">
      <sch:assert id="a-67-12979-error" test="@classCode='CLUSTER'">SHALL contain exactly one [1..1] @classCode="CLUSTER" cluster (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:67-12979).</sch:assert>
      <sch:assert id="a-67-12980-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:67-12980).</sch:assert>
      <sch:assert id="a-67-19532-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.98'][not(@extension)]) = 1">SHALL contain exactly one [1..1] templateId (CONF:67-19532) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.98" (CONF:67-19533).</sch:assert>
      <sch:assert id="a-67-26992-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:67-26992).</sch:assert>
      <sch:assert id="a-67-12981-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CodeSystem: HL7ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:67-12981).</sch:assert>
      <sch:assert id="a-67-12982-error" test="count(cda:reference[@typeCode='REFR'][count(cda:externalDocument)=1])=1">SHALL contain exactly one [1..1] reference (CONF:67-12982) such that it SHALL contain exactly one [1..1] @typeCode="REFR" refers to (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:67-12983). SHALL contain exactly one [1..1] externalDocument (CONF:67-12984).</sch:assert>
    </sch:rule>
    <sch:rule id="Measure_Reference-statusCode-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.98']]/cda:statusCode">
      <sch:assert id="a-67-27020-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: HL7ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:67-27020).</sch:assert>
    </sch:rule>
    <sch:rule id="Measure_Reference-externalDocument-errors" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.98']]/cda:reference/cda:externalDocument">
      <sch:assert id="a-67-19534-error" test="@classCode='DOC'">This externalDocument SHALL contain exactly one [1..1] @classCode="DOC" Document (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:67-19534).</sch:assert>
      <sch:assert id="a-67-12985-error" test="count(cda:id[@root]) &gt; 0">This externalDocument SHALL contain at least one [1..*] id (CONF:67-12985) such that it SHALL contain exactly one [1..1] @root (CONF:67-12986). This ID references an ID of the Quality Measure (CONF:67-27008).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Measure-section-pattern-errors">
    <sch:rule id="Measure-section-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.2']]">
      <sch:assert id="a-67-12801-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.2.2'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:67-12801) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.2.2" (CONF:67-12802).</sch:assert>
      <sch:assert id="a-67-12798-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:67-12798).</sch:assert>
      <sch:assert id="a-67-12799-error" test="count(cda:title[translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='measure section'])=1">SHALL contain exactly one [1..1] title="Measure Section" (CONF:67-12799).</sch:assert>
      <sch:assert id="a-67-12800-error" test="count(cda:text)=1">SHALL contain exactly one [1..1] text (CONF:67-12800).</sch:assert>
      <sch:assert id="a-67-13003-error" test="count(cda:entry[cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.98']]]) &gt; 0">SHALL contain at least one [1..*] entry (CONF:67-13003) such that it SHALL contain exactly one [1..1] Measure Reference (identifier: urn:oid:2.16.840.1.113883.10.20.24.3.98) (CONF:67-16677).</sch:assert>
    </sch:rule>
    <sch:rule id="Measure-section-code-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.2']]/cda:code">
      <sch:assert id="a-67-19230-error" test="@code='55186-1'">This code SHALL contain exactly one [1..1] @code="55186-1" Measure Section (CONF:67-19230).</sch:assert>
      <sch:assert id="a-67-27012-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:67-27012).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Measure-section-qdm-pattern-errors">
    <sch:rule id="Measure-section-qdm-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.3']]">
      <sch:assert id="a-67-12803-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.2.3'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:67-12803) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.2.3" (CONF:67-12804).</sch:assert>
      <sch:assert id="a-67-12978-error" test="count(cda:entry) &gt; 0">SHALL contain at least one [1..*] entry (CONF:67-12978).</sch:assert>
    </sch:rule>
    <sch:rule id="Measure-section-qdm-entry-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.3']]/cda:entry">
      <sch:assert id="a-67-13193-error" test="count(cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']])=1">Such entries SHALL contain exactly one [1..1] eMeasure Reference QDM (identifier: urn:oid:2.16.840.1.113883.10.20.24.3.97) (CONF:67-13193).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Active-pattern-extension-check">
    <sch:rule id="Medication_Active-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.41']">
      <sch:assert id="a-4444-28858-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28858) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.41" (CONF:4444-28860). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28654).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Active-pattern-errors">
    <sch:rule id="Medication_Active-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.41'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28861-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28861).</sch:assert>
      <sch:assert id="a-4444-28079-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28079).</sch:assert>
      <sch:assert id="a-4444-28858-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.41'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-28858) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.41" (CONF:4444-28860). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28654).</sch:assert>
      <sch:assert id="a-4444-28859-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:4444-28859).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Active-statusCode-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.41'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-28655-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-28655).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Activity-pattern-extension-check">
    <sch:rule id="Medication_Activity-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16']">
      <sch:assert id="a-1098-7499-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7499) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.16" (CONF:1098-10504). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32498).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Activity-pattern-errors">
    <sch:rule id="Medication_Activity-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7496-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7496).</sch:assert>
      <sch:assert id="a-1098-7497-v-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.18']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet MoodCodeEvnInt urn:oid:2.16.840.1.113883.11.20.9.18 STATIC 2011-04-03 (CONF:1098-7497).</sch:assert>
      <sch:assert id="a-1098-7499-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-7499) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.16" (CONF:1098-10504). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32498).</sch:assert>
      <sch:assert id="a-1098-7500-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-7500).</sch:assert>
      <sch:assert id="a-1098-7507-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7507).</sch:assert>
      <sch:assert id="a-1098-7508-error" test="count(cda:effectiveTime[not(@operator='A')]) = 1 and (cda:effectiveTime[@value] or count(cda:effectiveTime[cda:low])=1) and not(cda:effectiveTime[@value] and cda:effectiveTime[cda:low])">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-7508) such that it SHALL contain either a low or a @value but not both (CONF:1098-32890).</sch:assert>
      <sch:assert id="a-1098-7516-error" test="count(cda:doseQuantity) = 1">SHALL contain exactly one [1..1] doseQuantity (CONF:1098-7516).</sch:assert>
      <sch:assert id="a-1098-7520-error" test="count(cda:consumable) = 1">SHALL contain exactly one [1..1] consumable (CONF:1098-7520).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Activity-code-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32360-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ActStatus urn:oid:2.16.840.1.113883.1.11.159331 DYNAMIC (CONF:1098-32360).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Activity-may-rateQuantity-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:rateQuantity">
      <sch:assert id="a-1098-7525-error" test="@unit">The rateQuantity, if present, SHALL contain exactly one [1..1] @unit, which SHALL be selected from ValueSet UnitsOfMeasureCaseSensitive urn:oid:2.16.840.1.113883.1.11.12839 DYNAMIC (CONF:1098-7525).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Activity-consumable-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:consumable">
      <sch:assert id="a-1098-16085-error" test="count(cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']])=1">This consumable SHALL contain exactly one [1..1] Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.23:2014-06-09) (CONF:1098-16085).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Activity-may-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:precondition">
      <sch:assert id="a-1098-31882-error" test="@typeCode='PRCN'">The precondition, if present, SHALL contain exactly one [1..1] @typeCode="PRCN" (CONF:1098-31882).</sch:assert>
      <sch:assert id="a-1098-31883-error" test="count(cda:criterion[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.25'][@extension='2014-06-09']]) = 1">The precondition, if present, SHALL contain exactly one [1..1] Precondition for Substance Administration (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.25:2014-06-09) (CONF:1098-31883).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Administered-pattern-extension-check">
    <sch:rule id="Medication_Administered-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.42']">
      <sch:assert id="a-4444-12446-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12446) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.42" (CONF:4444-12447). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27023).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Administered-pattern-errors">
    <sch:rule id="Medication_Administered-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.42'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-12444-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" Substance Administration (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-12444).</sch:assert>
      <sch:assert id="a-4444-12445-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12445).</sch:assert>
      <sch:assert id="a-4444-12446-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.42'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-12446) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.42" (CONF:4444-12447). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27023).</sch:assert>
      <sch:assert id="a-4444-12452-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:4444-12452).</sch:assert>
      <!-- 4444-30032 added for STU 5.2 -->
      <sch:assert id="a-4444-30032-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30032).</sch:assert>
      <!-- 4444-29854 added for STU 5.2 -->
      <sch:assert id="a-4444-29854-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Medication Not Administered (CONF:4444-29854).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Administered-statuscode-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.42'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-13241-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: HL7ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-13241).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispense-pattern-extension-check">
    <sch:rule id="Medication_Dispense-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18']">
      <sch:assert id="a-1098-7453-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7453) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.18" (CONF:1098-10505). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32580).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispense-pattern-errors">
    <sch:rule id="Medication_Dispense-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7451-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7451).</sch:assert>
      <sch:assert id="a-1098-7452-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7452).</sch:assert>
      <sch:assert id="a-1098-7453-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-7453) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.18" (CONF:1098-10505). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32580).</sch:assert>
      <sch:assert id="a-1098-7454-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-7454).</sch:assert>
      <sch:assert id="a-1098-7455-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7455).</sch:assert>
      <sch:assert id="a-1098-9333-error" test="(cda:product[count(cda:manufacturedProduct)=1]) and (count(cda:product[cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']] or cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']]]) = 1)">A supply act SHALL contain one product/Medication Information OR one product/Immunization Medication Information template (CONF:1098-9333).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispense-statuscode-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32361-error" test="@code=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.3.88.12.80.64']/voc:code/@value">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet Medication Fill Status urn:oid:2.16.840.1.113883.3.88.12.80.64 STATIC 2014-04-23 (CONF:1098-32361).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispense-may-performer-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18'][@extension='2014-06-09']]/cda:performer">
      <sch:assert id="a-1098-7461-error" test="count(cda:assignedEntity) = 1">The performer, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1098-7467).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispensed_Act-pattern-extension-check">
    <sch:rule id="Medication_Dispensed_Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.139']">
      <sch:assert id="a-4444-28558-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28558) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.139" (CONF:4444-28560). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28907).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispensed_Act-pattern-errors">
    <sch:rule id="Medication_Dispensed_Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.139'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28562-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28562).</sch:assert>
      <sch:assert id="a-4444-28563-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28563).</sch:assert>
      <sch:assert id="a-4444-28558-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.139'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-28558) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.139" (CONF:4444-28560). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28907).</sch:assert>
      <!-- 05-18-2020 Removed 4444-28564 per STU comment 1973 -->
      <!-- <sch:assert id="a-4444-28564-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-28564).</sch:assert> -->
      <sch:assert id="a-4444-28567-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-28567).</sch:assert>
      <sch:assert id="a-4444-28557-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']])=1]) = 1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28557) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28561). SHALL contain exactly one [1..1] Medication Dispensed (V5) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.45:2019-12-01) (CONF:4444-28566).</sch:assert>
      <!-- 4444-29895 added for STU 5.2 -->
      <sch:assert id="a-4444-29895-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Medication Not Dispensed (CONF:4444-29895).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispensed_Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.139'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28568-error" test="@code='SPLY'">This code SHALL contain exactly one [1..1] @code="SPLY" supply (CONF:4444-28568).</sch:assert>
      <sch:assert id="a-4444-28569-error" test="@codeSystem='2.16.840.1.113883.5.6'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.6" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28569).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispensed-pattern-extension-check">
    <sch:rule id="Medication_Dispensed-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45']">
      <sch:assert id="a-4444-13851-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13851) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.45" (CONF:4444-13852). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26555).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispensed-pattern-errors">
    <sch:rule id="Medication_Dispensed-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27528-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27528).</sch:assert>
      <sch:assert id="a-4444-27529-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-27529).</sch:assert>
      <sch:assert id="a-4444-13851-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-13851) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.45" (CONF:4444-13852). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-26555).</sch:assert>
      <sch:assert id="a-4444-19440-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:4444-19440).</sch:assert>
      <!-- 05-26-2020 Conformkance 4444-28910 change from a SHALL to a MAY per STU comment 1976  -->
      <!-- <sch:assert id="a-4444-28910-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-28910). </sch:assert> -->
      <!-- 05-26-2020 4444-29859 added per STU comment 1976 -->
      <sch:assert id="a-4444-29859-error" test="count(cda:effectiveTime[count(@value | @nullFlavor | cda:low) =1 ]) =1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-29859) such that it  SHOULD contain zero or one [0..1] @value (CONF:4444-29899).  SHOULD contain zero or one [0..1] low (CONF:4444-29864). MAY contain zero or one [0..1] high (CONF:4444-29865).  This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low (CONF:4444-30019).</sch:assert>
      <sch:assert id="a-4444-28908-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.139'][@extension='2019-12-01']) = 1">This template SHALL be contained by a Medication Dispensed Act (V4) (CONF:4444-28908).</sch:assert>
      <!-- 05-25-2020 4444-30039 added per STU comment 1977 -->
      <sch:assert id="a-4444-30039-error" test="count(cda:product[count(cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']])=1])=1">SHALL contain exactly one [1..1] product (CONF:4444-30039) such that it SHALL contain exactly one [1..1] Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.23:2014-06-09) (CONF:4444-30040).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispensed-statuscode-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-19441-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-19441).</sch:assert>
    </sch:rule>
    <!-- 05-06-20202 Added @nullFlavor to test -->
    <!-- 05-26-2020 STU 1976 dictates that 4444-30019 is subsumed within the "such that it" clauses of 4444-29859 -->
    <!--
		<sch:rule id="Medication_Dispensed-effectiveTime-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:effectiveTime">
			<sch:assert id="a-4444-30019-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low (CONF:4444-30019).</sch:assert>
		</sch:rule>
		-->
    <sch:rule id="Medication_Dispensed-participant-participantRole-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:participant/cda:participantRole">
      <sch:assert id="a-4444-29223-error" test="count(cda:id) &gt;= 1">This participantRole SHALL contain at least one [1..*] id (CONF:4444-29223).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispensed-entryRelationship-substanceAdministration-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:entryRelationship[@typeCode='REFR']/cda:substanceAdministration">
      <sch:assert id="a-4444-28226-error" test="@classCode='SBADM'">This substanceAdministration SHALL contain exactly one [1..1] @classCode="SBADM" Substance Administration (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28226).</sch:assert>
      <sch:assert id="a-4444-28227-error" test="@moodCode='EVN'">This substanceAdministration SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28227).</sch:assert>
      <sch:assert id="a-4444-28229-error" test="count(cda:consumable)=1">This substanceAdministration SHALL contain exactly one [1..1] consumable (CONF:4444-28229).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispensed-entryRelationship-substanceAdministration-consumable-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:entryRelationship[@typeCode='REFR']/cda:substanceAdministration/cda:consumable">
      <sch:assert id="a-4444-28230-error" test="count(cda:manufacturedProduct)=1">This consumable SHALL contain exactly one [1..1] manufacturedProduct (CONF:4444-28230).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispensed-entryRelationship-substanceAdministration-consumable-manufacturedProduct-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:entryRelationship[@typeCode='REFR']/cda:substanceAdministration/cda:consumable/cda:manufacturedProduct">
      <sch:assert id="a-4444-28231-error" test="count(cda:manufacturedMaterial)=1">This manufacturedProduct SHALL contain exactly one [1..1] manufacturedMaterial (CONF:4444-28231).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispensed-entryRelationship-substanceAdministration-consumable-manufacturedProduct-manufacturedMaterial-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.45'][@extension='2019-12-01']]/cda:entryRelationship[@typeCode='REFR']/cda:substanceAdministration/cda:consumable/cda:manufacturedProduct/cda:manufacturedMaterial">
      <sch:assert id="a-4444-28232-error" test="@nullFlavor='NA'">This manufacturedMaterial SHALL contain exactly one [1..1] @nullFlavor="NA" (CodeSystem: HL7NullFlavor urn:oid:2.16.840.1.113883.5.1008) (CONF:4444-28232).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Free_Text_Sig-pattern-errors">
    <sch:rule id="Medication_Free_Text_Sig-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]">
      <sch:assert id="a-81-32770-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:81-32770).</sch:assert>
      <sch:assert id="a-81-32771-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.18']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet MoodCodeEvnInt urn:oid:2.16.840.1.113883.11.20.9.18 STATIC 2011-04-03 (CONF:81-32771).</sch:assert>
      <sch:assert id="a-81-32753-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147'][not(@extension)]) = 1">SHALL contain exactly one [1..1] templateId (CONF:81-32753) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.147" (CONF:81-32772).</sch:assert>
      <sch:assert id="a-81-32775-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:81-32775).</sch:assert>
      <sch:assert id="a-81-32754-error" test="count(cda:text) = 1">SHALL contain exactly one [1..1] text (CONF:81-32754).</sch:assert>
      <sch:assert id="a-81-32776-error" test="count(cda:consumable) = 1">SHALL contain exactly one [1..1] consumable (CONF:81-32776).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Free_Text_Sig-code-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:code">
      <sch:assert id="a-81-32780-error" test="@code='76662-6'">This code SHALL contain exactly one [1..1] @code="76662-6" Instructions Medication (CONF:81-32780).</sch:assert>
      <sch:assert id="a-81-32781-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1 STATIC) (CONF:81-32781).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Free_Text_Sig-text-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:text">
      <sch:assert id="a-81-32755-error" test="count(cda:reference) = 1">This text SHALL contain exactly one [1..1] reference (CONF:81-32755).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Free_Text_Sig-reference-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:text/cda:reference">
      <sch:assert id="a-81-32774-error" test="starts-with(@value, '#')">This reference/@value SHALL begin with a '#' and SHALL point to its corresponding narrative (using the approach defined in CDA Release 2, section 4.3.5.1) (CONF:81-32774).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Free_Text_Sig-consumable-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:consumable">
      <sch:assert id="a-81-32777-error" test="count(cda:manufacturedProduct) = 1">This consumable SHALL contain exactly one [1..1] manufacturedProduct (CONF:81-32777).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Free_Text_Sig-manufacturedProduct-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:consumable/cda:manufacturedProduct">
      <sch:assert id="a-81-32778-error" test="count(cda:manufacturedLabeledDrug) = 1">This manufacturedProduct SHALL contain exactly one [1..1]  (CONF:81-32778).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Free_Text_Sig-manufacturedLabeledDrug-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:consumable/cda:manufacturedProduct/cda:manufacturedLabeledDrug">
      <sch:assert id="a-81-32779-error" test="@nullFlavor='NA'">This manufacturedLabeledDrug SHALL contain exactly one [1..1] @nullFlavor="NA" Not Applicable (CONF:81-32779).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Information-pattern-extension-check">
    <sch:rule id="Medication_Information-extension-check" context="cda:manufacturedProduct/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23']">
      <sch:assert id="a-1098-7409-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7409) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.23" (CONF:1098-10506). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32579).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Information-pattern-errors">
    <sch:rule id="Medication_Information-errors" context="cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7408-error" test="@classCode='MANU'">SHALL contain exactly one [1..1] @classCode="MANU" (CodeSystem: RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:1098-7408).</sch:assert>
      <sch:assert id="a-1098-7409-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-7409) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.23" (CONF:1098-10506). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32579).</sch:assert>
      <sch:assert id="a-1098-7411-error" test="count(cda:manufacturedMaterial) = 1">SHALL contain exactly one [1..1] manufacturedMaterial (CONF:1098-7411).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Information-manufacturedMaterial-errors" context="cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']]/cda:manufacturedMaterial">
      <sch:assert id="a-1098-7412-error" test="count(cda:code) = 1">This manufacturedMaterial SHALL contain exactly one [1..1] code, which SHALL be selected from ValueSet Medication Clinical Drug urn:oid:2.16.840.1.113762.1.4.1010.4 DYNAMIC (CONF:1098-7412).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Order-pattern-extension-check">
    <sch:rule id="Medication_Order-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.47']">
      <sch:assert id="a-4444-12585-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12585) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.47" (CONF:4444-12586). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27089).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Order-pattern-errors">
    <sch:rule id="Medication_Order-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.47'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27377-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27377).</sch:assert>
      <sch:assert id="a-4444-12639-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12639).</sch:assert>
      <sch:assert id="a-4444-12585-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.47'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-12585) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.47" (CONF:4444-12586). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27089).</sch:assert>
      <sch:assert id="a-4444-27740-error" test="count(cda:effectiveTime[count(cda:low)=1][count(cda:high)=1]) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-27740) such that it SHALL contain exactly one [1..1] low (CONF:4444-27742) SHALL contain exactly one [1..1] high (CONF:4444-29901).</sch:assert>
      <sch:assert id="a-4444-27745-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27745).</sch:assert>
      <!-- 4444-29910 added for STU 5.2 -->
      <sch:assert id="a-4444-29910-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Medication Not Order (CONF:4444-29910).</sch:assert>
    </sch:rule>
    <!-- Updated 03-26-2020 The assertion requiring code is only valid when participant typeCode = LOC. -->
    <sch:rule id="Medication_Order-participant-participantRole-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.47'][@extension='2019-12-01']]/cda:participant[@typeCode='LOC']/cda:participantRole">
      <sch:assert id="a-4444-29233-error" test="count(cda:code)=1">This participantRole SHALL contain exactly one [1..1] code (CONF:4444-29233).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Supply_Order-pattern-extension-check">
    <sch:rule id="Medication_Supply_Order-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.17']">
      <sch:assert id="a-1098-7429-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7429) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.17" (CONF:1098-10507). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32578).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Supply_Order-pattern-errors">
    <sch:rule id="Medication_Supply_Order-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.17'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7427-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7427).</sch:assert>
      <sch:assert id="a-1098-7428-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7428).</sch:assert>
      <sch:assert id="a-1098-7429-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.17'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-7429) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.17" (CONF:1098-10507). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32578).</sch:assert>
      <sch:assert id="a-1098-7430-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-7430).</sch:assert>
      <sch:assert id="a-1098-7432-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7432).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Supply_Order-statusCode-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.17'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32362-error" test="count(@code)=1">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ActStatus urn:oid:2.16.840.1.113883.1.11.159331 DYNAMIC (CONF:1098-32362).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Supply_Order-may-entryRelationship-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.17'][@extension='2014-06-09']]/cda:entryRelationship">
      <sch:assert id="a-1098-7444-error" test="@typeCode='SUBJ'">The entryRelationship, if present, SHALL contain exactly one [1..1] @typeCode="SUBJ" (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1098-7444).</sch:assert>
      <sch:assert id="a-1098-7445-error" test="@inversionInd='true'">The entryRelationship, if present, SHALL contain exactly one [1..1] @inversionInd="true" True (CONF:1098-7445).</sch:assert>
      <sch:assert id="a-1098-31391-error" test="count(cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.20'][@extension='2014-06-09']])=1">The entryRelationship, if present, SHALL contain exactly one [1..1] Instruction (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.20:2014-06-09) (CONF:1098-31391).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Supply_Request-pattern-extension-check">
    <sch:rule id="Medication_Supply_Request-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.99']">
      <sch:assert id="a-4444-13821-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13821) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.99" (CONF:4444-13822). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28374).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Supply_Request-pattern-errors">
    <sch:rule id="Medication_Supply_Request-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.99'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28373-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" Supply (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28373).</sch:assert>
      <sch:assert id="a-4444-13820-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13820).</sch:assert>
      <sch:assert id="a-4444-13821-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.99'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-13821) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.99" (CONF:4444-13822). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28374).</sch:assert>
      <sch:assert id="a-4444-29705-error" test="count(cda:product[count(cda:manufacturedProduct[@classCode='MANU'][cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'][@extension='2014-06-09']])=1]) = 1">SHALL contain exactly one [1..1] product (CONF:4444-29705) such that it SHALL contain exactly one [1..1] Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.23:2014-06-09) (CONF:4444-29706).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_care_experience-pattern-extension-check">
    <sch:rule id="Patient_care_experience-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.48']">
      <sch:assert id="a-4444-12466-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12466) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.48" (CONF:4444-12467). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27290).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_care_experience-pattern-errors">
    <sch:rule id="Patient_care_experience-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.48'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-12464-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" observation, which SHALL be selected from CodeSystem HL7ActClass (urn:oid:2.16.840.1.113883.5.6) (CONF:4444-12464).</sch:assert>
      <sch:assert id="a-4444-12465-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" event, which SHALL be selected from CodeSystem ActMood (urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12465).</sch:assert>
      <sch:assert id="a-4444-28085-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28085)</sch:assert>
      <sch:assert id="a-4444-12466-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.48'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-12466) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.48" (CONF:4444-12467). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27290).</sch:assert>
      <sch:assert id="a-4444-12469-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-12469).</sch:assert>
      <sch:assert id="a-4444-12470-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-12470).</sch:assert>
      <sch:assert id="a-4444-12471-error" test="count(cda:statusCode[@code='completed']) = 1">SHALL contain exactly one [1..1] statusCode="completed", which SHALL be selected from CodeSystem ActStatus (urn:oid:2.16.840.1.113883.5.14) (CONF:4444-12471).</sch:assert>
      <sch:assert id="a-4444-13038-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-13038).</sch:assert>
      <sch:assert id="a-4444-28932-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01)(CONF:4444-28932).</sch:assert>
    </sch:rule>
    <sch:rule id="Patient_care_experience-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.48'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-13037-error" test="@code='77218-6'">This code SHALL contain exactly one [1..1] @code="77218-6" Patient satisfaction with healthcare delivery (CONF:4444-13037).</sch:assert>
      <sch:assert id="a-4444-27555-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-27555).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Clinical_Trial_Participant-pattern-extension-check">
    <sch:rule id="Patient_Characteristic_Clinical_Trial_Participant-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.51']">
      <sch:assert id="a-3343-12537-extension-error" test="@extension='2017-08-01'">SHALL contain exactly one [1..1] templateId (CONF:3343-12537) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.51" (CONF:3343-12538).SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-27026).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Clinical_Trial_Participant-pattern-errors">
    <sch:rule id="Patient_Characteristic_Clinical_Trial_Participant-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.51'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-16711-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:3343-16711).</sch:assert>
      <sch:assert id="a-3343-12526-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" event, which SHALL be selected from CodeSystem ActMood (urn:oid:2.16.840.1.113883.5.1001) (CONF:3343-12526).</sch:assert>
      <sch:assert id="a-3343-28086-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:3343-28086).</sch:assert>
      <sch:assert id="a-3343-12537-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.51'][@extension='2017-08-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:3343-12537) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.51" (CONF:3343-12538).SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-27026).</sch:assert>
      <sch:assert id="a-3343-26996-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:3343-26996).</sch:assert>
      <sch:assert id="a-3343-13041-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:3343-13041).</sch:assert>
      <sch:assert id="a-3343-13042-error" test="count(cda:statusCode[@code='active']) = 1">SHALL contain exactly one [1..1] statusCode="active" (CodeSystem: HL7ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:3343-13042).</sch:assert>
      <sch:assert id="a-3343-12536-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:3343-12536).</sch:assert>
      <sch:assert id="a-3343-16712-error" test="count(cda:value[@xsi:type='CD']) = 1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:3343-16712).</sch:assert>
    </sch:rule>
    <sch:rule id="Patient_Characteristic_Clinical_Trial_Participant-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.51'][@extension='2017-08-01']]/cda:code">
      <sch:assert id="a-3343-28130-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:3343-28130).</sch:assert>
      <sch:assert id="a-3343-28131-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:3343-28131).</sch:assert>
    </sch:rule>
    <sch:rule id="Patient_Characteristic_Clinical_Trial_Participant-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.51'][@extension='2017-08-01']]/cda:effectiveTime">
      <sch:assert id="a-3343-27668-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:3343-27668).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Expired-pattern-extension-check">
    <sch:rule id="Patient_Characteristic_Expired-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.54']">
      <sch:assert id="a-2228-12540-extension-error" test="@extension='2016-02-01'">SHALL contain exactly one [1..1] templateId (CONF:2228-12540) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.54" (CONF:2228-12541). SHALL contain exactly one [1..1] @extension="2016-02-01" (CONF:2228-27014).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Expired-pattern-errors">
    <sch:rule id="Patient_Characteristic_Expired-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.54'][@extension='2016-02-01']]">
      <sch:assert id="a-2228-28087-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:2228-28087).</sch:assert>
      <sch:assert id="a-2228-28088-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:2228-28088).</sch:assert>
      <sch:assert id="a-2228-28089-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:2228-28089).</sch:assert>
      <sch:assert id="a-2228-12540-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.54'][@extension='2016-02-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:2228-12540) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.54" (CONF:2228-12541). SHALL contain exactly one [1..1] @extension="2016-02-01" (CONF:2228-27014).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Observation_Assertion-pattern-extension-check">
    <sch:rule id="Patient_Characteristic_Observation_Assertion-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.103']">
      <sch:assert id="a-4444-26962-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-26962) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.103" (CONF:4444-26963) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27781).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Observation_Assertion-pattern-errors">
    <sch:rule id="Patient_Characteristic_Observation_Assertion-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.103'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-16536-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CONF:4444-16536).</sch:assert>
      <sch:assert id="a-4444-16537-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-16537).</sch:assert>
      <sch:assert id="a-4444-28623-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28623).</sch:assert>
      <sch:assert id="a-4444-26962-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.103'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-26962) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.103" (CONF:4444-26963) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27781).</sch:assert>
      <sch:assert id="a-4444-16538-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-16538).</sch:assert>
      <sch:assert id="a-4444-16544-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-16544).</sch:assert>
      <sch:assert id="a-4444-16541-error" test="count(cda:value[@xsi:type='CD']) = 1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-16541)</sch:assert>
    </sch:rule>
    <sch:rule id="Patient_Characteristic_Observation_Assertion-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.103'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-16545-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" (CONF:4444-16545).</sch:assert>
      <sch:assert id="a-4444-28135-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:4444-28135).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Payer-pattern-errors">
    <sch:rule id="Patient_Characteristic_Payer-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.55']]">
      <sch:assert id="a-67-14213-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:67-14213).</sch:assert>
      <sch:assert id="a-67-14214-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:67-14214).</sch:assert>
      <sch:assert id="a-67-12561-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.55'][not(@extension)]) = 1">SHALL contain exactly one [1..1] templateId (CONF:67-12561) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.55" (CONF:67-12562).</sch:assert>
      <sch:assert id="a-67-12564-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:67-12564).</sch:assert>
      <sch:assert id="a-67-12565-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:67-12565).</sch:assert>
      <sch:assert id="a-67-26933-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:67-26933).</sch:assert>
      <sch:assert id="a-67-16710-error" test="count(cda:value[@xsi:type='CD']) = 1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Payer urn:oid:2.16.840.1.114222.4.11.3591 DYNAMIC (CONF:67-16710).</sch:assert>
    </sch:rule>
    <sch:rule id="Patient_Characteristic_Payer-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.55']]/cda:code">
      <sch:assert id="a-67-14029-error" test="@code='48768-6'">This code SHALL contain exactly one [1..1] @code="48768-6" Payment source (CONF:67-14029).</sch:assert>
      <sch:assert id="a-67-27009-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:67-27009).</sch:assert>
    </sch:rule>
    <sch:rule id="Patient_Characteristic_Payer-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.55']]/cda:effectiveTime">
      <sch:assert id="a-67-26934-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:67-26934).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient-data-section-pattern-errors">
    <sch:rule id="Patient-data-section-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.4']]">
      <sch:assert id="a-67-12794-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.17.2.4'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:67-12794) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.17.2.4" (CONF:67-12795).</sch:assert>
      <sch:assert id="a-67-3865-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:67-3865).</sch:assert>
      <sch:assert id="a-67-3866-error" test="count(cda:title[translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='patient data'])=1">SHALL contain exactly one [1..1] title="Patient Data" (CONF:67-3866).</sch:assert>
      <sch:assert id="a-67-3867-error" test="count(cda:text)=1">SHALL contain exactly one [1..1] text (CONF:67-3867).</sch:assert>
      <sch:assert id="a-67-14567-error" test="count(cda:entry) &gt; 0">SHALL contain at least one [1..*] entry (CONF:67-14567).</sch:assert>
    </sch:rule>
    <sch:rule id="Patient-data-section-code-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.4']]/cda:code">
      <sch:assert id="a-67-26548-error" test="@code='55188-7'">This code SHALL contain exactly one [1..1] @code="55188-7" (CONF:67-26548).</sch:assert>
      <sch:assert id="a-67-27013-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:67-27013).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_data_section_QDM-pattern-extension-check">
    <sch:rule id="Patient_data_section_QDM-extension-check-errors" context="cda:section/cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1']">
      <sch:assert id="a-4444-12796-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12796) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.2.1" (CONF:4444-12797). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28405).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_data_section_QDM-pattern-errors">
    <sch:rule id="Patient_data_section_QDM-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-12796-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12796) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.2.1" (CONF:4444-12797). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28405).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Order-pattern-extension-check">
    <sch:rule id="Physical_Exam_Order-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.58']">
      <sch:assert id="a-4444-12686-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12686) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.58" (CONF:4444-12687). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27078).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Order-pattern-errors">
    <sch:rule id="Physical_Exam_Order-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.58'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27550-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27550).</sch:assert>
      <sch:assert id="a-4444-12685-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12685).</sch:assert>
      <sch:assert id="a-4444-12686-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.58'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-12686) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.58" (CONF:4444-12687). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27078).</sch:assert>
      <sch:assert id="a-4444-12689-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-12689).</sch:assert>
      <sch:assert id="a-4444-13254-error" test="count(cda:value[@xsi:type='CD']) = 1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-13254).</sch:assert>
      <sch:assert id="a-4444-27345-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27345).</sch:assert>
      <!-- 4444-29789 added for STU 5.2 -->
      <sch:assert id="a-4444-29789-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Physical Exam Not Order (CONF:4444-29789).</sch:assert>
    </sch:rule>
    <sch:rule id="Physical_Exam_Order-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.58'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-13242-error" test="@code='29545-1'">This code SHALL contain exactly one [1..1] @code="29545-1" physical examination (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-13242).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Performed-pattern-extension-check">
    <sch:rule id="Physical_Exam_Performed-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.59']">
      <sch:assert id="a-4444-12644-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12644) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.59" (CONF:4444-12645). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27135).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Performed-pattern-errors">
    <sch:rule id="Physical_Exam_Performed-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.59'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27559-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27559).</sch:assert>
      <sch:assert id="a-4444-12643-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12643).</sch:assert>
      <sch:assert id="a-4444-12644-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.59'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-12644) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.59" (CONF:4444-12645). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27135).</sch:assert>
      <sch:assert id="a-4444-29817-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:4444-29817).</sch:assert>
      <sch:assert id="a-4444-27651-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-27651).</sch:assert>
      <sch:assert id="a-4444-12649-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:4444-12649).</sch:assert>
      <sch:assert id="a-4444-12651-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-12651).</sch:assert>
      <sch:assert id="a-4444-29824-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) = 0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-29824).</sch:assert>
      <!-- 4444-29816 added for STU 5.2 -->
      <sch:assert id="a-4444-29816-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Physical Exam Not Performed (CONF:4444-29816).</sch:assert>
    </sch:rule>
    <sch:rule id="Physical_Exam_Performed-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.59'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 05-06-2020 Added @nullFlavor to test -->
      <sch:assert id="a-4444-29819-error" test="count(cda:low | @value | @nullFlavor)=1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low  (CONF:4444-29819).</sch:assert>
    </sch:rule>
    <sch:rule id="Physical_Exam_Performed-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.59'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-12650-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-12650).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Recommended-pattern-extension-check">
    <sch:rule id="Physical_Exam_Recommended-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.60']">
      <sch:assert id="a-4444-12666-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12666) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.60" (CONF:4444-12667). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27082).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Recommended-pattern-errors">
    <sch:rule id="Physical_Exam_Recommended-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.60'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27556-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27556).</sch:assert>
      <sch:assert id="a-4444-12665-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12665).</sch:assert>
      <sch:assert id="a-4444-12666-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.60'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-12666) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.60" (CONF:4444-12667). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27082).</sch:assert>
      <sch:assert id="a-4444-12669-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-12669).</sch:assert>
      <sch:assert id="a-4444-13275-error" test="count(cda:value[@xsi:type='CD']) = 1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-13275).</sch:assert>
      <sch:assert id="a-4444-27351-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27351).</sch:assert>
      <!-- 4444-29802 added for STU 5.2 -->
      <sch:assert id="a-4444-29802-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Physical Exam Not Recommended (CONF:4444-29802).</sch:assert>
    </sch:rule>
    <sch:rule id="Physical_Exam_Recommended-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.60'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-13274-error" test="@code='29545-1'">This code SHALL contain exactly one [1..1] @code="29545-1" physical examination (CONF:4444-13274).</sch:assert>
      <sch:assert id="a-4444-28132-error" test="@codeSystem='2.16.840.1.113883.6.1' ">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-28132).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Act-pattern-extension-check">
    <sch:rule id="Planned_Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.39']">
      <sch:assert id="a-1098-30430-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-30430) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.39" (CONF:1098-30431). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32552).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Act-pattern-errors">
    <sch:rule id="Planned_Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.39'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8538-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8538).</sch:assert>
      <sch:assert id="a-1098-8539-error" test="@moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.23']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (Act/Encounter/Procedure) urn:oid:2.16.840.1.113883.11.20.9.23 STATIC 2011-09-30 (CONF:1098-8539).</sch:assert>
      <sch:assert id="a-1098-30430-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.39'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-30430) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.39" (CONF:1098-30431). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32552).</sch:assert>
      <sch:assert id="a-1098-8546-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-8546).</sch:assert>
      <sch:assert id="a-1098-31687-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:1098-31687).</sch:assert>
      <sch:assert id="a-1098-30432-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-30432).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.39'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32019-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-32019).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Coverage-pattern-errors">
    <sch:rule id="Planned_Coverage-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.129']]">
      <sch:assert id="a-1098-31945-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" act (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1098-31945).</sch:assert>
      <sch:assert id="a-1098-31946-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-31946).</sch:assert>
      <sch:assert id="a-1098-31947-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.129'][not(@extension)]) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-31947) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.129" (CONF:1098-31948).</sch:assert>
      <sch:assert id="a-1098-31950-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-31950).</sch:assert>
      <sch:assert id="a-1098-31951-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:1098-31951).</sch:assert>
      <sch:assert id="a-1098-31954-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-31954).</sch:assert>
      <sch:assert id="a-1098-31967-error" test="count(cda:entryRelationship[@typeCode='COMP'][count(cda:act)=1]) = 1">SHALL contain exactly one [1..1] entryRelationship (CONF:1098-31967) such that it SHALL contain exactly one [1..1] @typeCode="COMP" has component (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:1098-31968). SHALL contain exactly one [1..1] act (CONF:1098-31969).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Coverage-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.129']]/cda:code">
      <sch:assert id="a-1098-31952-error" test="@code='48768-6'">This code SHALL contain exactly one [1..1] @code="48768-6" Payment Sources (CONF:1098-31952).</sch:assert>
      <sch:assert id="a-1098-31953-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:1098-31953).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Coverage-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.129']]/cda:statusCode">
      <sch:assert id="a-1098-31955-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1098-31955).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Coverage-act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.129']]/cda:entryRelationship/cda:act">
      <sch:assert id="a-1098-31970-error" test="@classCode='ACT'">This act SHALL contain exactly one [1..1] @classCode="ACT" ACT (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-31970).</sch:assert>
      <sch:assert id="a-1098-31971-error" test="@moodCode='INT'">This act SHALL contain exactly one [1..1] @moodCode="INT" intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-31971).</sch:assert>
      <sch:assert id="a-1098-31972-error" test="count(cda:id) &gt; 0">This act SHALL contain at least one [1..*] id (CONF:1098-31972).</sch:assert>
      <sch:assert id="a-1098-31973-error" test="count(cda:code) = 1">This act SHALL contain exactly one [1..1] code, which SHALL be selected from ValueSet Payer urn:oid:2.16.840.1.114222.4.11.3591 DYNAMIC (CONF:1098-31973).</sch:assert>
      <sch:assert id="a-1098-31974-error" test="count(cda:statusCode) = 1">This act SHALL contain exactly one [1..1] statusCode (CONF:1098-31974).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Coverage-act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.129']]/cda:entryRelationship/cda:act/cda:statusCode">
      <sch:assert id="a-1098-31975-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-31975).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Encounter-pattern-extension-check">
    <sch:rule id="Planned_Encounter-extension-check" context="cda:encounter/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.40']">
      <sch:assert id="a-1098-30437-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-30437) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.40" (CONF:1098-30438). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32553).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Encounter-pattern-errors">
    <sch:rule id="Planned_Encounter-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.40'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8564-error" test="@classCode='ENC'">SHALL contain exactly one [1..1] @classCode="ENC" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8564).</sch:assert>
      <sch:assert id="a-1098-8565-error" test="@moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.23']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (Act/Encounter/Procedure) urn:oid:2.16.840.1.113883.11.20.9.23 STATIC 2011-09-30 (CONF:1098-8565).</sch:assert>
      <sch:assert id="a-1098-30437-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.40'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-30437) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.40" (CONF:1098-30438). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32553).</sch:assert>
      <sch:assert id="a-1098-8567-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-8567).</sch:assert>
      <sch:assert id="a-1098-30439-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-30439).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Encounter-statusCode-errors" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.40'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-31880-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-31880).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Immunization_Activity-pattern-errors">
    <sch:rule id="Planned_Immunization_Activity-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.120']]">
      <sch:assert id="a-1098-32091-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CONF:1098-32091).</sch:assert>
      <sch:assert id="a-1098-32097-error" test="@moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.24']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (SubstanceAdministration/Supply) urn:oid:2.16.840.1.113883.11.20.9.24 STATIC 2014-09-01 (CONF:1098-32097).</sch:assert>
      <sch:assert id="a-1098-32098-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.120'][not(@extension)]) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-32098) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.120" (CONF:1098-32099).</sch:assert>
      <sch:assert id="a-1098-32100-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:1098-32100).</sch:assert>
      <sch:assert id="a-1098-32101-error" test="count(cda:statusCode) = 1">SHALL contain exactly one [1..1] statusCode (CONF:1098-32101).</sch:assert>
      <sch:assert id="a-1098-32103-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-32103).</sch:assert>
      <sch:assert id="a-1098-32131-error" test="count(cda:consumable) = 1">SHALL contain exactly one [1..1] consumable (CONF:1098-32131).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Immunization_Activity-consumable-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.120']]/cda:consumable">
      <sch:assert id="a-1098-32132-error" test="count(cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']]) = 1">This consumable SHALL contain exactly one [1..1] Immunization Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.54:2014-06-09) (CONF:1098-32132).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Immunization_Activity-statusCode-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.120']]/cda:statusCode">
      <sch:assert id="a-1098-32102-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-32102).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Medication-Activity-pattern-extension-check">
    <sch:rule id="Planned-Medication-Activity-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42']">
      <sch:assert id="a-1098-30465-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-30465) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.42" (CONF:1098-30466). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32557).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Medication-Activity-pattern-errors">
    <sch:rule id="Planned-Medication-Activity-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8572-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8572).</sch:assert>
      <sch:assert id="a-1098-8573-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.24']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (SubstanceAdministration/Supply) urn:oid:2.16.840.1.113883.11.20.9.24 STATIC 2011-09-30 (CONF:1098-8573).</sch:assert>
      <sch:assert id="a-1098-30465-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-30465) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.42" (CONF:1098-30466). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32557).</sch:assert>
      <sch:assert id="a-1098-8575-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-8575).</sch:assert>
      <sch:assert id="a-1098-32087-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-32087).</sch:assert>
      <!--  07-15-2019 Added required 'such that' clause to effective time test  https://tracker.esacinc.com/browse/QRDA-617 -->
      <sch:assert id="a-1098-30468-error" test="count(cda:effectiveTime[ (count(cda:low)=1 and not(@value)) or (count(cda:low)=0 and @value)])=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-30468) such thatThis effectiveTime SHALL contain either a low or a @value but not both (CONF:1098-32947).</sch:assert>
      <sch:assert id="a-1098-32082-error" test="count(cda:consumable)=1">SHALL contain exactly one [1..1] consumable (CONF:1098-32082).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Medication-Activity-statusCode-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32088-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-32088).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Medication-Activity-consumable-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:consumable">
      <sch:assert id="a-1098-32083-error" test="count(cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.23'] [@extension = '2014-06-09']])=1">This consumable SHALL contain exactly one [1..1] Medication Information (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.23:2014-06-09) (CONF:1098-32083).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Medication-Activity-precondition-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:precondition">
      <sch:assert id="a-1098-32085-error" test="@typeCode='PRCN'">The precondition, if present, SHALL contain exactly one [1..1] @typeCode="PRCN" Precondition (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:1098-32085).</sch:assert>
      <sch:assert id="a-1098-32086-error" test="count(cda:criterion[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.25' and @extension = '2014-06-09']])=1">The precondition, if present, SHALL contain exactly one [1..1] Precondition for Substance Administration (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.25:2014-06-09) (CONF:1098-32086).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Observation-pattern-extension-check">
    <sch:rule id="Planned-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.44']">
      <sch:assert id="a-1098-30451-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-30451) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.44" (CONF:1098-30452). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32555).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Observation-pattern-errors">
    <sch:rule id="Planned-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.44'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8581-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8581).</sch:assert>
      <sch:assert id="a-1098-8582-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.25']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (Observation) urn:oid:2.16.840.1.113883.11.20.9.25 STATIC 2011-09-30 (CONF:1098-8582).</sch:assert>
      <sch:assert id="a-1098-30451-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.44'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-30451) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.44" (CONF:1098-30452). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32555).</sch:assert>
      <sch:assert id="a-1098-8584-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-8584).</sch:assert>
      <sch:assert id="a-1098-30453-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-30453).</sch:assert>
      <sch:assert id="a-1098-31030-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHOULD be selected from CodeSystem LOINC (urn:oid:2.16.840.1.113883.6.1) (CONF:1098-31030).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.44'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32032-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-32032).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Procedure-pattern-extension-check">
    <sch:rule id="Planned-Procedure-extension-check" context="cda:procedure/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.41']">
      <sch:assert id="a-1098-30444-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-30444) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.41" (CONF:1098-30445). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32554).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Procedure-pattern-errors">
    <sch:rule id="Planned-Procedure-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.41'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8568-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8568).</sch:assert>
      <sch:assert id="a-1098-8569-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.23']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (Act/Encounter/Procedure) urn:oid:2.16.840.1.113883.11.20.9.23 STATIC 2011-09-30 (CONF:1098-8569).</sch:assert>
      <sch:assert id="a-1098-30444-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.41'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-30444) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.41" (CONF:1098-30445). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32554).</sch:assert>
      <sch:assert id="a-1098-8571-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-8571).</sch:assert>
      <sch:assert id="a-1098-30446-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-30446).</sch:assert>
      <sch:assert id="a-1098-31976-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-31976).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Procedure-statusCode-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.41'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-31978-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-31978).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Supply-pattern-extension-check">
    <sch:rule id="Planned-Supply-extension-check" context="cda:supply/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.43']">
      <sch:assert id="a-1098-30463-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-30463) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.43" (CONF:1098-30464). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32556).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Supply-pattern-errors">
    <sch:rule id="Planned-Supply-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.43'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8577-error" test="@classCode='SPLY'">SHALL contain exactly one [1..1] @classCode="SPLY" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8577).</sch:assert>
      <sch:assert id="a-1098-8578-error" test="@moodCode and @moodCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.24']/voc:code/@value">SHALL contain exactly one [1..1] @moodCode, which SHALL be selected from ValueSet Planned moodCode (SubstanceAdministration/Supply) urn:oid:2.16.840.1.113883.11.20.9.24 STATIC 2011-09-30 (CONF:1098-8578).</sch:assert>
      <sch:assert id="a-1098-30463-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.43'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-30463) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.43" (CONF:1098-30464). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32556).</sch:assert>
      <sch:assert id="a-1098-8580-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-8580).</sch:assert>
      <sch:assert id="a-1098-30458-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-30458).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Supply-statusCode-errors" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.43'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32047-error" test="@code='active'">This statusCode SHALL contain exactly one [1..1] @code="active" Active (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-32047).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Precondition-For-Substance-Administration-pattern-extension-check">
    <sch:rule id="Precondition-For-Substance-Administration-extension-check" context="cda:criterion/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.25']">
      <sch:assert id="a-1098-7372-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7372) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.25" (CONF:1098-10517). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32603).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Precondition-For-Substance-Administration-pattern-errors">
    <sch:rule id="Precondition-For-Substance-Administration-errors" context="cda:criterion[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.25'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7372-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.25'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-7372) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.25" (CONF:1098-10517). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32603).</sch:assert>
      <sch:assert id="a-1098-32396-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code with @xsi:type="CD" (CONF:1098-32396).</sch:assert>
      <sch:assert id="a-098-7369-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Problem urn:oid:2.16.840.1.113883.3.88.12.3221.7.4 DYNAMIC (CONF:1098-7369).</sch:assert>
    </sch:rule>
    <sch:rule id="Precondition-For-Substance-Administration-code-errors" context="cda:criterion[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.25'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-32397-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:1098-32397).</sch:assert>
      <sch:assert id="a-1098-32398-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1098-32398).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Present-on-Admission-Indicator-pattern-errors">
    <sch:rule id="Present-on-Admission-Indicator-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.169'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29956-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29956).</sch:assert>
      <sch:assert id="a-4444-29957-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-29957).</sch:assert>
      <sch:assert id="a-4444-29945-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.169'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29945) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.169" (CONF:4444-29949). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29950).</sch:assert>
      <sch:assert id="a-4444-29947-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29947).</sch:assert>
      <sch:assert id="a-4444-29948-error" test="count(cda:value[@xsi:type='CD']) =1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHOULD be selected from CodeSystem NUBC UB-04 Patient Discharge Status code set (urn:oid:2.16.840.1.113883.6.301.5) DYNAMIC (CONF:4444-29948).</sch:assert>
    </sch:rule>
    <sch:rule id="Present-on-Admission-Indicator-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.169'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29952-error" test="@code='78026-2'">This code SHALL contain exactly one [1..1] @code="78026-2" Present on admission (CONF:4444-29952).</sch:assert>
      <sch:assert id="a-4444-29953-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CONF:4444-29953).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Priority-Preference-pattern-errors">
    <sch:rule id="Priority-Preference-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.143']]">
      <sch:assert id="a-1098-30949-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-30949).</sch:assert>
      <sch:assert id="a-1098-30950-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-30950).</sch:assert>
      <sch:assert id="a-1098-30951-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.143'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-30951) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.143" (CONF:1098-30952).</sch:assert>
      <sch:assert id="a-1098-30953-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-30953).</sch:assert>
      <sch:assert id="a-1098-30954-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-30954).</sch:assert>
      <sch:assert id="a-1098-30957-error" test="count(cda:value[@xsi:type='CD' and @code=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.60']/voc:code/@value or @nullFlavor])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Priority Level urn:oid:2.16.840.1.113883.11.20.9.60 STATIC 2014-06-11 (CONF:1098-30957).</sch:assert>
    </sch:rule>
    <sch:rule id="Priority-Preference-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.143']]/cda:code">
      <sch:assert id="a-1098-30955-error" test="@code='225773000'">This code SHALL contain exactly one [1..1] @code="225773000" Preference (CONF:1098-30955).</sch:assert>
      <sch:assert id="a-1098-30956-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:1098-30956).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Concern-Act-pattern-extension-check">
    <sch:rule id="Problem-Concern-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3']">
      <sch:assert id="a-1198-16772-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-16772) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.3" (CONF:1198-16773). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32509).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Concern-Act-pattern-errors">
    <sch:rule id="Problem-Concern-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-9024-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-9024).</sch:assert>
      <sch:assert id="a-1198-9025-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-9025).</sch:assert>
      <sch:assert id="a-1198-16772-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-16772) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.3" (CONF:1198-16773). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32509).</sch:assert>
      <sch:assert id="a-1198-9026-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1198-9026).</sch:assert>
      <sch:assert id="a-1198-9027-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-9027).</sch:assert>
      <sch:assert id="a-1198-9029-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-9029).</sch:assert>
      <sch:assert id="a-1198-9030-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1198-9030).</sch:assert>
      <sch:assert id="a-1198-9034-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']])=1]) &gt;= 1">SHALL contain at least one [1..*] entryRelationship (CONF:1198-9034) such that it SHALL contain exactly one [1..1] Problem Observation (V3) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.4:2015-08-01) (CONF:1198-15980). SHALL contain exactly one [1..1] @typeCode="SUBJ" Has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1198-9035).</sch:assert>
    </sch:rule>
    <sch:rule id="Problem-Concern-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3'][@extension='2015-08-01']]/cda:code">
      <sch:assert id="a-1198-19184-error" test="@code='CONC'">This code SHALL contain exactly one [1..1] @code="CONC" Concern (CONF:1198-19184).</sch:assert>
      <sch:assert id="a-1198-32168-error" test="@codeSystem='2.16.840.1.113883.5.6'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.6" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1198-32168).</sch:assert>
    </sch:rule>
    <sch:rule id="Problem-Concern-Act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-31525-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ProblemAct statusCode urn:oid:2.16.840.1.113883.11.20.9.19 STATIC (CONF:1198-31525).</sch:assert>
    </sch:rule>
    <sch:rule id="Problem-Concern-Act-effectiveTime-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3'][@extension='2015-08-01']]/cda:effectiveTime">
      <sch:assert id="a-1198-9032-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:1198-9032).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Observation-pattern-extension-check">
    <sch:rule id="Problem-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4']">
      <sch:assert id="a-1198-14926-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-14926) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.4" (CONF:1198-14927). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32508).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Observation-pattern-errors">
    <sch:rule id="Problem-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-9041-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-9041).</sch:assert>
      <sch:assert id="a-1198-9042-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-9042).</sch:assert>
      <sch:assert id="a-1198-14926-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-14926) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.4" (CONF:1198-14927). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32508).</sch:assert>
      <sch:assert id="a-1198-9043-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1198-9043).</sch:assert>
      <!-- 08-14-2019 Updated conformance text, changed from STATIC to DYNAMIC -->
      <sch:assert id="a-1198-9045-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHOULD be selected from ValueSet Problem Type urn:oid:2.16.840.1.113883.3.88.12.3221.7.2 DYNAMIC (CONF:1198-9045).</sch:assert>
      <sch:assert id="a-1198-9049-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-9049).</sch:assert>
      <sch:assert id="a-1198-9050-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1198-9050).</sch:assert>
      <sch:assert id="a-1198-9058-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHOULD be selected from ValueSet Problem urn:oid:2.16.840.1.113883.3.88.12.3221.7.4 DYNAMIC (CONF:1198-9058).</sch:assert>
    </sch:rule>
    <!--  Removed, see JIRA https://tracker.esacinc.com/browse/QRDA-196 -->
    <!-- 
        <sch:rule id="Problem-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']]/cda:code">
          <sch:assert id="a-1198-32848-error" test="count(cda:translation) &gt;= 1">This code SHALL contain at least one [1..*] translation, which SHOULD be selected from ValueSet Problem Type urn:oid:2.16.840.1.113883.3.88.12.3221.7.2 2014-09-02 (CONF:1198-32848).</sch:assert> 
        </sch:rule>  
        -->
    <sch:rule id="Problem-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-19112-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1198-19112).</sch:assert>
    </sch:rule>
    <sch:rule id="Problem-Observation-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']]/cda:effectiveTime">
      <sch:assert id="a-1198-15603-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:1198-15603).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Status-pattern-errors">
    <sch:rule id="Problem-Status-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.6'][@extension='2019-06-20']]">
      <sch:assert id="a-1198-7357-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-7357).</sch:assert>
      <sch:assert id="a-1198-7358-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-7358).</sch:assert>
      <sch:assert id="a-1198-7359-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.6'][@extension='2019-06-20'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-7359) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.6" (CONF:1198-10518) SHALL contain exactly one [1..1] @extension="2019-06-20" (CONF:1198-32961).</sch:assert>
      <sch:assert id="a-1198-19162-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-19162).</sch:assert>
      <sch:assert id="a-1198-7364-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-7364).</sch:assert>
      <!-- 07-15-2019 Added 1198-7365 assertion test  https://tracker.esacinc.com/browse/QRDA-617 -->
      <sch:assert id="a-1198-7365-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Problem Status urn:oid:2.16.840.1.113883.3.88.12.80.68 DYNAMIC (CONF:1198-7365).</sch:assert>
    </sch:rule>
    <sch:rule id="Problem-Status-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.6'][@extension='2019-06-20']]/cda:code">
      <sch:assert id="a-1198-19163-error" test="@code='33999-4'">This code SHALL contain exactly one [1..1] @code="33999-4" Status (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1 STATIC) (CONF:1198-19163).</sch:assert>
    </sch:rule>
    <sch:rule id="Problem-Status-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.6'][@extension='2019-06-20']]/cda:statusCode">
      <sch:assert id="a-1198-19113-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1198-19113).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Act-pattern-extension-check">
    <sch:rule id="Procedure-Activity-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12']">
      <sch:assert id="a-1098-8291-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-8291) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.12" (CONF:1098-10519). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32505).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Act-pattern-errors">
    <sch:rule id="Procedure-Activity-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8289-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8289).</sch:assert>
      <sch:assert id="a-1098-8290-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-8290).</sch:assert>
      <sch:assert id="a-1098-8291-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-8291) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.12" (CONF:1098-10519). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32505).</sch:assert>
      <sch:assert id="a-1098-8292-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-8292).</sch:assert>
      <sch:assert id="a-1098-8293-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-8293).</sch:assert>
      <sch:assert id="a-1098-8298-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-8298).</sch:assert>
      <sch:assert id="a-1098-8299-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-8299).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-code-originalText-reference-value-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:code/cda:originalText/cda:reference[@value]">
      <sch:assert id="a-1098-19189-error" test="starts-with(@value, '#')">This reference/@value SHALL begin with a '#' and SHALL point to its corresponding narrative (using the approach defined in CDA Release 2, section 4.3.5.1) (CONF:1098-19189).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32364-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ProcedureAct statusCode urn:oid:2.16.840.1.113883.11.20.9.22 STATIC 2014-04-23 (CONF:1098-32364).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-performer-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:performer">
      <sch:assert id="a-1098-8302-error" test="count(cda:assignedEntity)=1">The performer, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1098-8302).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-performer-assignedEntity-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-8303-error" test="count(cda:id)&gt;=1">This assignedEntity SHALL contain at least one [1..*] id (CONF:1098-8303).</sch:assert>
      <sch:assert id="a-1098-8304-error" test="count(cda:addr)&gt;=1">This assignedEntity SHALL contain at least one [1..*] addr (CONF:1098-8304).</sch:assert>
      <sch:assert id="a-1098-8305-error" test="count(cda:telecom)&gt;=1">This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1098-8305).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-performer-assignedEntity-representedOrganization-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-1098-8309-error" test="count(cda:addr)&gt;=1">The representedOrganization, if present, SHALL contain at least one [1..*] addr (CONF:1098-8309).</sch:assert>
      <sch:assert id="a-1098-8310-error" test="count(cda:telecom)&gt;=1">The representedOrganization, if present, SHALL contain at least one [1..*] telecom (CONF:1098-8310).</sch:assert>
    </sch:rule>
    <!-- 07-15-2019 Added assertions for conformance statements 1098-8318, 1098-8319, 1098-8320  https://tracker.esacinc.com/browse/QRDA-617  -->
    <sch:rule id="Procedure-Activity-Act-entryRelationship-encounter-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:entryRelationship[@typeCode='COMP'][@inversionInd='true']/cda:encounter">
      <sch:assert id="a-1098-8318-error" test="@classCode='ENC'">This encounter SHALL contain exactly one [1..1] @classCode="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8318).</sch:assert>
      <sch:assert id="a-1098-8319-error" test="@moodCode='EVN'">This encounter SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-8319).</sch:assert>
      <sch:assert id="a-1098-8320-error" test="count(cda:id)=1">This encounter SHALL contain exactly one [1..1] id (CONF:1098-8320).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Observation-pattern-extension-check">
    <sch:rule id="Procedure-Activity-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13']">
      <sch:assert id="a-1098-8238-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-8238) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.13" (CONF:1098-10520). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32507).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Observation-pattern-errors">
    <sch:rule id="Procedure-Activity-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8282-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8282).</sch:assert>
      <sch:assert id="a-1098-8237-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-8237).</sch:assert>
      <sch:assert id="a-1098-8238-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-8238) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.13" (CONF:1098-10520). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32507).</sch:assert>
      <sch:assert id="a-1098-8239-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-8239).</sch:assert>
      <sch:assert id="a-1098-19197-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-19197).</sch:assert>
      <sch:assert id="a-1098-8245-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-8245).</sch:assert>
      <sch:assert id="a-1098-16846-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:1098-16846).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:code/cda:originalText/cda:reference[@value]">
      <sch:assert id="a-1098-19201-error" test="starts-with(@value, '#')">This reference/@value SHALL begin with a '#' and SHALL point to its corresponding narrative (using the approach defined in CDA Release 2, section 4.3.5.1) (CONF:1098-19201).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32365-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ProcedureAct statusCode urn:oid:2.16.840.1.113883.11.20.9.22 STATIC 2014-04-23 (CONF:1098-32365).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-performer-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:performer">
      <sch:assert id="a-1098-8252-error" test="count(cda:assignedEntity)=1">The performer, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1098-8252).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-performer-assignedEntity-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-8253-error" test="count(cda:id)&gt;=1">This assignedEntity SHALL contain at least one [1..*] id (CONF:1098-8253).</sch:assert>
      <sch:assert id="a-1098-8254-error" test="count(cda:addr)&gt;=1">This assignedEntity SHALL contain at least one [1..*] addr (CONF:1098-8254).</sch:assert>
      <sch:assert id="a-1098-8255-error" test="count(cda:telecom)&gt;=1">This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1098-8255).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-performer-assignedEntity-representedOrganization-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-1098-8259-error" test="count(cda:addr)&gt;=1">The representedOrganization, if present, SHALL contain exactly one [1..1] addr (CONF:1098-8259).</sch:assert>
      <sch:assert id="a-1098-8260-error" test="count(cda:telecom)&gt;=1">The representedOrganization, if present, SHALL contain exactly one [1..1] telecom (CONF:1098-8260).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-entryRelationship-encounter-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:entryRelationship[@typeCode='COMP'][@inversionInd='true']/cda:encounter">
      <sch:assert id="a-1098-8268-error" test="@classCode='ENC'">This encounter SHALL contain exactly one [1..1] @classCode="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-8268).</sch:assert>
      <sch:assert id="a-1098-8269-error" test="@moodCode='EVN'">This encounter SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-8269).</sch:assert>
      <sch:assert id="a-1098-8270-error" test="@moodCode='EVN'">This encounter SHALL contain exactly one [1..1] id (CONF:1098-8270).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Procedure-pattern-extension-check">
    <sch:rule id="Procedure-Activity-Procedure-extension-check" context="cda:procedure/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14']">
      <sch:assert id="a-1098-7654-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7654) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.14" (CONF:1098-10521). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32506).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Procedure-pattern-errors">
    <sch:rule id="Procedure-Activity-Procedure-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7652-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" Procedure (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7652).</sch:assert>
      <sch:assert id="a-1098-7653-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7653).</sch:assert>
      <sch:assert id="a-1098-7654-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-7654) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.14" (CONF:1098-10521). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32506).</sch:assert>
      <sch:assert id="a-1098-7655-error" test="count(cda:id) &gt;=1">SHALL contain at least one [1..*] id (CONF:1098-7655).</sch:assert>
      <sch:assert id="a-1098-7656-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-7656).</sch:assert>
      <sch:assert id="a-1098-7661-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7661).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-code-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:code/cda:originalText/cda:reference[@value]">
      <sch:assert id="a-1098-19206-error" test="starts-with(@value, '#')">This reference/@value SHALL begin with a '#' and SHALL point to its corresponding narrative (using the approach defined in CDA Release 2, section 4.3.5.1) (CONF:1098-19206).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-statusCode-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-32366-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet ProcedureAct statusCode urn:oid:2.16.840.1.113883.11.20.9.22 STATIC 2014-04-23 (CONF:1098-32366).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-performer-specimen-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:specimen">
      <sch:assert id="a-1098-7704-error" test="count(cda:specimenRole)=1">The specimen, if present, SHALL contain exactly one [1..1] specimenRole (CONF:1098-7704).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-performer-assignedEntity-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-7722-error" test="count(cda:id)&gt;=1">This assignedEntity SHALL contain at least one [1..*] id (CONF:1098-7722).</sch:assert>
      <sch:assert id="a-1098-7732-error" test="count(cda:telecom)&gt;=1">This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1098-7732).</sch:assert>
      <sch:assert id="a-1098-7731-error" test="count(cda:addr)&gt;=1">This assignedEntity SHALL contain at least one [1..*] addr (CONF:1098-7731).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-performer-assignedEntity-representedOrganization-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-1098-7771-error" test="@classCode='ENC'">This encounter SHALL contain exactly one [1..1] @classCode="ENC" Encounter (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7771).</sch:assert>
      <sch:assert id="a-1098-7772-error" test="@moodCode='EVN'">This encounter SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7772).</sch:assert>
      <sch:assert id="a-1098-7773-error" test="count(cda:id)=1">This encounter SHALL contain exactly one [1..1] id (CONF:1098-7773).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-entryRelationship-encounter-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:entryRelationship[@typeCode='COMP'][@inversionInd='true']/cda:encounter">
      <sch:assert id="a-1098-7737-error" test="count(cda:telecom)=1">The representedOrganization, if present, SHALL contain exactly one [1..1] telecom (CONF:1098-7737).</sch:assert>
      <sch:assert id="a-1098-7736-error" test="count(cda:addr)=1">The representedOrganization, if present, SHALL contain exactly one [1..1] addr (CONF:1098-7736).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Order-pattern-extension-check">
    <sch:rule id="Procedure-Order-extension-check" context="cda:procedure/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.63']">
      <sch:assert id="a-4444-11098-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11098) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.63" (CONF:4444-11099). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27083).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Order-pattern-errors">
    <sch:rule id="Procedure-Order-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.63'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27323-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" Procedure (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27323).</sch:assert>
      <sch:assert id="a-4444-11097-error" test="@moodCode='RQO'">SHALL contain exactly one [1..1] @moodCode="RQO" Request (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11097).</sch:assert>
      <sch:assert id="a-4444-11098-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.63'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11098) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.63" (CONF:4444-11099). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27083).</sch:assert>
      <sch:assert id="a-4444-27324-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27324).</sch:assert>
      <sch:assert id="a-4444-27346-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01)(CONF:4444-27346).</sch:assert>
      <!-- 4444-29827 added for STU 5.2 -->
      <sch:assert id="a-4444-29827-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Procedure Not Order (CONF:4444-29827).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Performed-pattern-extension-check">
    <sch:rule id="Procedure-Performed-extension-check" context="cda:procedure/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64']">
      <sch:assert id="a-4444-11262-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11262) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.64" (CONF:4444-11263). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27129).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Performed-pattern-errors">
    <sch:rule id="Procedure-Performed-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27308-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" Procedure (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27308).</sch:assert>
      <sch:assert id="a-4444-11261-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11261).</sch:assert>
      <sch:assert id="a-4444-11262-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11262) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.64" (CONF:4444-11263). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27129).</sch:assert>
      <sch:assert id="a-4444-27309-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-27309).</sch:assert>
      <sch:assert id="a-4444-27305-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-27305).</sch:assert>
      <sch:assert id="a-4444-11669-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-11669).</sch:assert>
      <sch:assert id="a-4444-30027-error" test="count(cda:author[cda:template[@root='2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30027).</sch:assert>
      <!-- 4444-29928 added for STU 5.2 -->
      <sch:assert id="a-4444-29928-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Procedure Not Performed (CONF:4444-29928).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Performed-statusCode-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-27367-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-27367).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Performed-effectiveTime-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 05-06-2020 Added @nullFlavor to test -->
      <sch:assert id="a-4444-29438-error" test="count(cda:low | @value | @nullFlavor) =1">This effectiveTime SHALL contain exactly one of @value, @nullFlavor, or low  (CONF:4444-29438).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Performed-entryRelationship-REFR-status-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64'][@extension='2019-12-01']]/cda:entryRelationship[@typeCode='REFR']/cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.93'][@extension='2019-12-01']">
      <sch:assert id="a-4444-28608-error" test="count(../cda:value[@xsi:type='CD'])=1">This observation SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-28608).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Recommended-pattern-extension-check">
    <sch:rule id="Procedure-Recommended-extension-check" context="cda:procedure/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.65']">
      <sch:assert id="a-4444-11104-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11104) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.65" (CONF:4444-11105). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27086).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Recommended-pattern-errors">
    <sch:rule id="Procedure-Recommended-errors" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.65'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27337-error" test="@classCode='PROC'">SHALL contain exactly one [1..1] @classCode="PROC" Procedure (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27337).</sch:assert>
      <sch:assert id="a-4444-11103-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" Intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-11103).</sch:assert>
      <sch:assert id="a-4444-11104-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.65'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11104) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.65" (CONF:4444-11105). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27086).</sch:assert>
      <sch:assert id="a-4444-11107-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-11107).</sch:assert>
      <sch:assert id="a-4444-27352-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-27352).</sch:assert>
      <!-- 4444-29830 added for STU 5.2 -->
      <sch:assert id="a-4444-29830-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Procedure Not Recommended (CONF:4444-29830).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Product-Instance-pattern-errors">
    <sch:rule id="Product-Instance-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.37']]">
      <sch:assert id="a-81-7900-error" test="@classCode='MANU'">SHALL contain exactly one [1..1] @classCode="MANU" Manufactured Product (CodeSystem: RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:81-7900).</sch:assert>
      <sch:assert id="a-81-7901-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.37'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-7901) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.37" (CONF:81-10522).</sch:assert>
      <sch:assert id="a-81-7902-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:81-7902).</sch:assert>
      <sch:assert id="a-81-7903-error" test="count(cda:playingDevice)=1">SHALL contain exactly one [1..1] playingDevice (CONF:81-7903).</sch:assert>
      <sch:assert id="a-81-7905-error" test="count(cda:scopingEntity)=1">SHALL contain exactly one [1..1] scopingEntity (CONF:81-7905).</sch:assert>
    </sch:rule>
    <sch:rule id="Product-Instance-scopingEntity-errors" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.37']]/cda:scopingEntity">
      <sch:assert id="a-81-7908-error" test="count(cda:id)&gt;=1">This scopingEntity SHALL contain at least one [1..*] id (CONF:81-7908).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Prognosis-Observation-pattern-errors">
    <sch:rule id="Prognosis-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.113']]">
      <sch:assert id="a-1098-29035-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-29035).</sch:assert>
      <sch:assert id="a-1098-29036-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-29036).</sch:assert>
      <sch:assert id="a-1098-29037-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.113'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-29037) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.113" (CONF:1098-29038).</sch:assert>
      <sch:assert id="a-1098-29039-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] value (CONF:1098-29039).</sch:assert>
      <sch:assert id="a-1098-31350-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-31350).</sch:assert>
      <sch:assert id="a-1098-31123-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-31123).</sch:assert>
      <sch:assert id="a-1098-29469-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:1098-29469).</sch:assert>
    </sch:rule>
    <sch:rule id="Prognosis-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.113']]/cda:code">
      <sch:assert id="a-1098-29468-error" test="@code='75328-5'">This code SHALL contain exactly one [1..1] @code="75328-5" Prognosis (CONF:1098-29468).</sch:assert>
      <sch:assert id="a-1098-31349-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:1098-31349).</sch:assert>
    </sch:rule>
    <sch:rule id="Prognosis-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.113']]/cda:statusCode">
      <sch:assert id="a-1098-31351-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-31351).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Program_Participation-pattern-extension-check">
    <sch:rule id="Program_Participation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.154']">
      <sch:assert id="a-4444-28965-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28965) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.154" (CONF:4444-28969) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28970).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Program_Participation-pattern-errors">
    <sch:rule id="Program_Participation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.154'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28974-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CONF:4444-28974).</sch:assert>
      <sch:assert id="a-4444-28975-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28975).</sch:assert>
      <sch:assert id="a-4444-28978-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28978).</sch:assert>
      <sch:assert id="a-4444-28965-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.154'][@extension='2019-12-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:4444-28965) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.154" (CONF:4444-28969) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28970).</sch:assert>
      <sch:assert id="a-4444-28976-error" test="count(cda:id) &gt; 0">SHALL contain at least one [1..*] id (CONF:4444-28976).</sch:assert>
      <sch:assert id="a-4444-28964-error" test="count(cda:code) = 1">SHALL contain exactly one [1..1] code (CONF:4444-28964).</sch:assert>
      <sch:assert id="a-4444-28977-error" test="count(cda:statusCode[@code='completed']) = 1">SHALL contain exactly one [1..1] statusCode="completed" Completed (CONF:4444-28977).</sch:assert>
      <sch:assert id="a-4444-28966-error" test="count(cda:effectiveTime) = 1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-28966).</sch:assert>
      <!-- 05-11-2020 Added valueset text to conformance statement for 4444-28973-->
      <sch:assert id="a-4444-28973-error" test="count(cda:value[@xsi:type='CD']) = 1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHOULD be selected from ValueSet HL7ActCoverageType urn:oid:2.16.840.1.113883.1.11.19832 DYNAMIC (CONF:4444-28973). (CONF:4444-28973)</sch:assert>
    </sch:rule>
    <sch:rule id="Program_Participation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.154'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28967-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" (CONF:4444-28967).</sch:assert>
      <sch:assert id="a-4444-28968-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:4444-28968).</sch:assert>
    </sch:rule>
    <sch:rule id="Program_Participation-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.154'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-28971-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:4444-28971).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Provider-Care-Experience-pattern-extension-check">
    <sch:rule id="Provider-Care-Experience-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.67']">
      <sch:assert id="a-4444-12481-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12481) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.67" (CONF:4444-12482). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27293).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Provider-Care-Experience-pattern-errors">
    <sch:rule id="Provider-Care-Experience-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.67'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-12479-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" observation, which SHALL be selected from CodeSystem HL7ActClass (urn:oid:2.16.840.1.113883.5.6) (CONF:4444-12479).</sch:assert>
      <sch:assert id="a-4444-12480-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" event, which SHALL be selected from CodeSystem ActMood (urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-12480).</sch:assert>
      <sch:assert id="a-4444-28100-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28100).</sch:assert>
      <sch:assert id="a-4444-12481-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.67'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12481) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.67" (CONF:4444-12482). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27293).</sch:assert>
      <sch:assert id="a-4444-12484-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:4444-12484).</sch:assert>
      <sch:assert id="a-4444-12485-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-12485).</sch:assert>
      <sch:assert id="a-4444-12486-error" test="count(cda:statusCode[@code='completed'])=1">SHALL contain exactly one [1..1] statusCode="completed", which SHALL be selected from CodeSystem ActStatus (urn:oid:2.16.840.1.113883.5.14) (CONF:4444-12486).</sch:assert>
      <sch:assert id="a-4444-12572-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-12572).</sch:assert>
      <sch:assert id="a-4444-28941-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']]) = 1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01) (CONF:4444-28941).</sch:assert>
    </sch:rule>
    <sch:rule id="Provider-Care-Experience-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.67'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-27562-error" test="@code='77219-4'">This code SHALL contain exactly one [1..1] @code="77219-4" Provider satisfaction with healthcare delivery (CONF:4444-27562).</sch:assert>
      <sch:assert id="a-4444-27563-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-27563).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QDM_based_QRDA-pattern-extension-check">
    <sch:rule id="QDM_based_QRDA-extension-errors" context="cda:ClinicalDocument/cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2']">
      <sch:assert id="a-4444-12972-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-12972) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.1.2" (CONF:4444-26943). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28696).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QDM_based_QRDA-pattern-errors">
    <sch:rule id="QDM_based_QRDA-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]">
      <!-- Update 04-24-2020 Corrected extension to 2019-12-01 in conformance text for 4444-12972 -->
      <sch:assert id="a-4444-12972-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-12972) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.1.2" (CONF:4444-26943). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28696).</sch:assert>
      <sch:assert id="a-4444-16598-error" test="count(cda:recordTarget)=1">SHALL contain exactly one [1..1] recordTarget (CONF:4444-16598).</sch:assert>
      <sch:assert id="a-4444-16600-error" test="count(cda:custodian)=1">SHALL contain exactly one [1..1] custodian (CONF:4444-16600).</sch:assert>
      <sch:assert id="a-4444-12973-error" test="count(cda:component[count(cda:structuredBody)=1])=1">SHALL contain exactly one [1..1] component (CONF:4444-12973) such that it SHALL contain exactly one [1..1] structuredBody (CONF:4444-17081).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-recordTarget-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:recordTarget">
      <sch:assert id="a-4444-16856-error" test="count(cda:patientRole)=1">This recordTarget SHALL contain exactly one [1..1] patientRole (CONF:4444-16856).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-recordTarget-patientRole-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:recordTarget/cda:patientRole">
      <sch:assert id="a-4444-27570-error" test="count(cda:patient)=1">This patientRole SHALL contain exactly one [1..1] patient (CONF:4444-27570).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-recordTarget-patientRole-patient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:recordTarget/cda:patientRole/cda:patient">
      <sch:assert id="a-4444-27571-error" test="count(cda:birthTime)=1">This patient SHALL contain exactly one [1..1] birthTime (CONF:4444-27571).</sch:assert>
      <sch:assert id="a-4444-27572-error" test="count(cda:administrativeGenderCode)=1">This patient SHALL contain exactly one [1..1] administrativeGenderCode (CONF:4444-27572).</sch:assert>
      <sch:assert id="a-4444-27573-error" test="count(cda:raceCode)=1">This patient SHALL contain exactly one [1..1] raceCode (CONF:4444-27573).</sch:assert>
      <sch:assert id="a-4444-27574-error" test="count(cda:ethnicGroupCode)=1">This patient SHALL contain exactly one [1..1] ethnicGroupCode (CONF:4444-27574).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-custodian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:custodian">
      <sch:assert id="a-4444-28239-error" test="count(cda:assignedCustodian)=1">This custodian SHALL contain exactly one [1..1] assignedCustodian (CONF:4444-28239).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-custodian-assignedCustodian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:custodian/cda:assignedCustodian">
      <sch:assert id="a-4444-28240-error" test="count(cda:representedCustodianOrganization)=1">This assignedCustodian SHALL contain exactly one [1..1] representedCustodianOrganization (CONF:4444-28240).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-informationRecipient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:informationRecipient">
      <sch:assert id="a-4444-16704-error" test="count(cda:intendedRecipient)=1">The informationRecipient, if present, SHALL contain exactly one [1..1] intendedRecipient (CONF:4444-16704).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-informationRecipient-intendedRecipient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:informationRecipient/cda:intendedRecipient">
      <sch:assert id="a-4444-16705-error" test="count(cda:id) &gt; 0">This intendedRecipient SHALL contain at least one [1..*] id (CONF:4444-16705).</sch:assert>
    </sch:rule>
    <!-- Removed 4444-17081 as it became a "such that" clause under 4444-12973 -->
    <sch:rule id="QDM_based_QRDA-component-structuredBody-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:component/cda:structuredBody">
      <sch:assert id="a-4444-17082-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.3']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:4444-17082) such that This component SHALL contain exactly one [1..1] Measure Section QDM (identifier: urn:oid:2.16.840.1.113883.10.20.24.2.3) (CONF:4444-17083).</sch:assert>
      <sch:assert id="a-4444-17090-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:4444-17090) such that This component SHALL contain exactly one [1..1] Reporting Parameters Section (identifier: urn:oid:2.16.840.1.113883.10.20.17.2.1) (CONF:4444-17092).</sch:assert>
      <sch:assert id="a-4444-17091-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1'][@extension='2019-12-01']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:4444-17091) such that This component SHALL contain exactly one [1..1] Patient Data Section QDM (V7) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.2.1:2019-12-01) (CONF:4444-17093).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-documentationOf-serviceEvent-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:documentationOf/cda:serviceEvent">
      <sch:assert id="a-4444-16581-error" test="@classCode='PCPR'">This serviceEvent SHALL contain exactly one [1..1] @classCode="PCPR" Care Provision (CONF:4444-16581).</sch:assert>
      <sch:assert id="a-4444-16583-error" test="count(cda:performer) &gt; 0">This serviceEvent SHALL contain at least one [1..*] performer (CONF:4444-16583).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-documentationOf-serviceEvent-performer-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:documentationOf/cda:serviceEvent/cda:performer">
      <sch:assert id="a-4444-16584-error" test="@typeCode='PRF'">Such performers SHALL contain exactly one [1..1] @typeCode="PRF" Performer (CONF:4444-16584).</sch:assert>
      <sch:assert id="a-4444-16586-error" test="count(cda:assignedEntity)=1">Such performers SHALL contain exactly one [1..1] assignedEntity (CONF:4444-16586).</sch:assert>
    </sch:rule>
    <sch:rule id="QDM_based_QRDA-documentationOf-serviceEvent-performer-assignedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:assignedEntity">
      <sch:assert id="a-4444-16591-error" test="count(cda:representedOrganization)=1">This assignedEntity SHALL contain exactly one [1..1] representedOrganization (CONF:4444-16591).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I-pattern-extension-check">
    <sch:rule id="QRDA_Category_I-extensionerrors" context="cda:ClinicalDocument/cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1']">
      <sch:assert id="a-3343-12910-extension-error" test="@extension='2017-08-01'">SHALL contain exactly one [1..1] templateId (CONF:3343-12910) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.1.1" (CONF:3343-14613). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-27005)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I-pattern-errors">
    <sch:rule id="QRDA_Category_I-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-12910-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-12910) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.1.1" (CONF:3343-14613). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-27005)</sch:assert>
      <sch:assert id="a-3343-12911-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:3343-12911).</sch:assert>
      <sch:assert id="a-3343-12912-error" test="count(cda:title)=1">SHALL contain exactly one [1..1] title (CONF:3343-12912).</sch:assert>
      <sch:assert id="a-3343-12913-error" test="count(cda:recordTarget)=1">SHALL contain exactly one [1..1] recordTarget (CONF:3343-12913).</sch:assert>
      <sch:assert id="a-3343-12914-error" test="count(cda:custodian)=1">SHALL contain exactly one [1..1] custodian (CONF:3343-12914).</sch:assert>
      <sch:assert id="a-3343-12918-error" test="count(cda:component)=1">SHALL contain exactly one [1..1] component (CONF:3343-12918).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I-code-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]/cda:code">
      <sch:assert id="a-3343-28137-error" test="@code='55182-0'">This code SHALL contain exactly one [1..1] @code="55182-0" Quality Measure Report (CONF:3343-28137).</sch:assert>
      <sch:assert id="a-3343-28138-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:3343-28138).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I-recordTarget-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]/cda:recordTarget">
      <sch:assert id="a-3343-28387-error" test="count(cda:patientRole)=1">This recordTarget SHALL contain exactly one [1..1] patientRole (CONF:3343-28387).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I-custodian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]/cda:custodian">
      <sch:assert id="a-3343-12915-error" test="count(cda:assignedCustodian)=1">This custodian SHALL contain exactly one [1..1] assignedCustodian (CONF:3343-12915).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I-custodian-assignedCustodian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]/cda:custodian/cda:assignedCustodian">
      <sch:assert id="a-3343-12916-error" test="count(cda:representedCustodianOrganization)=1">This assignedCustodian SHALL contain exactly one [1..1] representedCustodianOrganization (CONF:3343-12916).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I-component-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]/cda:component">
      <sch:assert id="a-3343-12919-error" test="count(cda:structuredBody)=1">This component SHALL contain exactly one [1..1] structuredBody (CONF:3343-12919).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I-component-structuredBody-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'][@extension='2017-08-01']]/cda:component/cda:structuredBody">
      <sch:assert id="a-3343-12920-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.2']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:3343-12920) such that it SHALL contain exactly one [1..1] Measure Section (identifier: urn:oid:2.16.840.1.113883.10.20.24.2.2) (CONF:3343-17078).</sch:assert>
      <sch:assert id="a-3343-12923-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:3343-12923) such that it SHALL contain exactly one [1..1] Reporting Parameters Section (identifier: urn:oid:2.16.840.1.113883.10.20.17.2.1) (CONF:3343-17079).</sch:assert>
      <sch:assert id="a-3343-12924-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.4']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:3343-12924) such that it SHALL contain exactly one [1..1] Patient Data Section (identifier: urn:oid:2.16.840.1.113883.10.20.17.2.4) (CONF:3343-17080).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Rank-pattern-errors">
    <sch:rule id="Rank-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.166'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29455-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CONF:4444-29455).</sch:assert>
      <sch:assert id="a-4444-29456-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29456).</sch:assert>
      <sch:assert id="a-4444-29459-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-29459).</sch:assert>
      <sch:assert id="a-4444-29446-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.166'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29446) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.166" (CONF:4444-29450). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29451).</sch:assert>
      <sch:assert id="a-4444-29445-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29445).</sch:assert>
      <sch:assert id="a-4444-29460-error" test="count(cda:value[@xsi:type='INT'])=1">SHALL contain exactly one [1..1] value with @xsi:type="INT" (CONF:4444-29460).</sch:assert>
    </sch:rule>
    <sch:rule id="Rank-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.166'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29448-error" test="@code='263486008'">This code SHALL contain exactly one [1..1] @code="263486008" Rank (CONF:4444-29448).</sch:assert>
      <sch:assert id="a-4444-29449-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:4444-29449).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reaction-Observation-pattern-extension-check">
    <sch:rule id="Reaction-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9']">
      <sch:assert id="a-1098-7323-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7323) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.9" (CONF:1098-10523). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32504).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reaction-Observation-pattern-errors">
    <sch:rule id="Reaction-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7325-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7325).</sch:assert>
      <sch:assert id="a-1098-7326-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7326).</sch:assert>
      <sch:assert id="a-1098-7323-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-7323) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.9" (CONF:1098-10523). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32504).</sch:assert>
      <sch:assert id="a-1098-7329-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-7329).</sch:assert>
      <sch:assert id="a-1098-16851-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-16851).</sch:assert>
      <sch:assert id="a-1098-7328-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7328).</sch:assert>
      <sch:assert id="a-1098-7335-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Problem urn:oid:2.16.840.1.113883.3.88.12.3221.7.4 DYNAMIC (CONF:1098-7335).</sch:assert>
    </sch:rule>
    <sch:rule id="Reaction-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-31124-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" (CONF:1098-31124).</sch:assert>
      <sch:assert id="a-1098-32169-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1098-32169).</sch:assert>
    </sch:rule>
    <sch:rule id="Reaction-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-19114-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1098-19114).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reason-pattern-extension-check">
    <sch:rule id="Reason-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88']">
      <sch:assert id="a-3343-11359-extension-error" test="@extension='2017-08-01'">SHALL contain exactly one [1..1] templateId (CONF:3343-11359) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.88" (CONF:3343-11360). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-27027).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reason-pattern-errors">
    <sch:rule id="Reason-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-11357-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:3343-11357).</sch:assert>
      <sch:assert id="a-3343-11358-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:3343-11358).</sch:assert>
      <sch:assert id="a-3343-11359-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-11359) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.88" (CONF:3343-11360). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-27027).</sch:assert>
      <sch:assert id="a-3343-11361-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:3343-11361).</sch:assert>
      <sch:assert id="a-3343-11367-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:3343-11367).</sch:assert>
    </sch:rule>
    <sch:rule id="Reason-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]/cda:code">
      <sch:assert id="a-3343-11362-error" test="@code='77301-0'">This code SHALL contain exactly one [1..1] @code="77301-0" Reason care action performed or not (CONF:3343-11362).</sch:assert>
      <sch:assert id="a-3343-27028-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:3343-27028).</sch:assert>
    </sch:rule>
    <sch:rule id="Reason-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]/cda:effectiveTime">
      <sch:assert id="a-3343-27551-error" test="count(cda:low)=1">The effectiveTime, if present, SHALL contain exactly one [1..1] low (CONF:3343-27551).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Related-Person-QDM-pattern-errors">
    <sch:rule id="Related-Person-QDM-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.170'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-29994-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-29994).</sch:assert>
      <sch:assert id="a-4444-29995-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: HL7ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-29995).</sch:assert>
      <sch:assert id="a-4444-29986-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.170'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-29986) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.170" (CONF:4444-29989).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29990).</sch:assert>
      <sch:assert id="a-4444-29987-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-29987).</sch:assert>
      <sch:assert id="a-4444-29996-error" test="count(cda:participant[@typeCode='PRF'][count(cda:participantRole)=1])=1">SHALL contain exactly one [1..1] participant (CONF:4444-29996) such that it SHALL contain exactly one [1..1] @typeCode="PRF" (CodeSystem: HL7ParticipationType urn:oid:2.16.840.1.113883.5.90) (CONF:4444-29999). SHALL contain exactly one [1..1] participantRole (CONF:4444-29997).</sch:assert>
    </sch:rule>
    <sch:rule id="Related-Person-QDM-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.170'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-29991-error" test="@code='444018008'">This code SHALL contain exactly one [1..1] @code="444018008" Person with characteristic related to subject of record (CONF:4444-29991).</sch:assert>
      <sch:assert id="a-4444-29992-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:4444-29992).</sch:assert>
    </sch:rule>
    <sch:rule id="Related-Person-QDM-participant-participantRole-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.170'][@extension='2019-12-01']]/cda:participant[@typeCode='PRF']/cda:participantRole">
      <sch:assert id="a-4444-30000-error" test="@classCode='PAT'">This participantRole SHALL contain exactly one [1..1] @classCode="PAT" Patient (CONF:4444-30000).</sch:assert>
      <sch:assert id="a-4444-30001-error" test="count(cda:id) &gt;=1">This participantRole SHALL contain at least one [1..*] id (CONF:4444-30001).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Related-To-pattern-errors">
    <sch:rule id="Related-To-errors" context="sdtc:inFulfillmentOf1[sdtc:templateId[@root='2.16.840.1.113883.10.20.24.3.150'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-29113-error" test="@typeCode='FLFS'">SHALL contain exactly one [1..1] @typeCode="FLFS" Fulfills (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:3343-29113).</sch:assert>
      <sch:assert id="a-3343-29104-error" test="count(sdtc:templateId[@root='2.16.840.1.113883.10.20.24.3.150'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] sdtc:templateId (CONF:3343-29104) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.150" (CONF:3343-29107). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-29108).</sch:assert>
      <sch:assert id="a-3343-29105-error" test="count(sdtc:actReference)=1">SHALL contain exactly one [1..1] sdtc:actReference (CONF:3343-29105).</sch:assert>
    </sch:rule>
    <sch:rule id="Related-To-actReference-errors" context="sdtc:inFulfillmentOf1[sdtc:templateId[@root='2.16.840.1.113883.10.20.24.3.150'][@extension='2017-08-01']]/sdtc:actReference">
      <sch:assert id="a-3343-29114-error" test="@classCode">This sdtc:actReference SHALL contain exactly one [1..1] @classCode (CONF:3343-29114).</sch:assert>
      <sch:assert id="a-3343-29110-error" test="@moodCode">This sdtc:actReference SHALL contain exactly one [1..1] @moodCode (CONF:3343-29110).</sch:assert>
      <sch:assert id="a-3343-29106-error" test="count(sdtc:id)&gt;=1">This sdtc:actReference SHALL contain at least one [1..*] sdtc:id (CONF:3343-29106)</sch:assert>
    </sch:rule>
    <sch:rule id="Related-To-actReference-id-errors" context="sdtc:inFulfillmentOf1[sdtc:templateId[@root='2.16.840.1.113883.10.20.24.3.150'][@extension='2017-08-01']]/sdtc:actReference/sdtc:id">
      <sch:assert id="a-3343-29111-error" test="@root">Such sdtc:ids SHALL contain exactly one [1..1] @root (CONF:3343-29111).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reporting-Parameters-Act-pattern-errors">
    <sch:rule id="Reporting-Parameters-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8']]">
      <sch:assert id="a-23-3269-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:23-3269).</sch:assert>
      <sch:assert id="a-23-3270-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:23-3270).</sch:assert>
      <sch:assert id="a-23-18098-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:23-18098) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.17.3.8" (CONF:23-18099).</sch:assert>
      <sch:assert id="a-23-26549-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:23-26549).</sch:assert>
      <sch:assert id="a-23-3272-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:23-3272).</sch:assert>
      <sch:assert id="a-23-3273-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:23-3273).</sch:assert>
    </sch:rule>
    <sch:rule id="Reporting-Parameters-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8']]/cda:code">
      <sch:assert id="a-23-26550-error" test="@code='252116004'">This code SHALL contain exactly one [1..1] @code="252116004" Observation Parameters (CONF:23-26550).</sch:assert>
      <sch:assert id="a-23-26551-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:23-26551).</sch:assert>
    </sch:rule>
    <sch:rule id="Reporting-Parameters-Act-effectiveTime-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8']]/cda:effectiveTime">
      <sch:assert id="a-23-3274-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:23-3274).</sch:assert>
      <sch:assert id="a-23-3275-error" test="count(cda:high)=1">This effectiveTime SHALL contain exactly one [1..1] high (CONF:23-3275).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reporting-parameters-section-pattern-errors">
    <sch:rule id="Reporting-parameters-section-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1']]">
      <sch:assert id="a-23-14611-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:23-14611) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.17.2.1" (CONF:23-14612).</sch:assert>
      <sch:assert id="a-23-18191-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:23-18191).</sch:assert>
      <sch:assert id="a-23-4142-error" test="count(cda:title[translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')='reporting parameters'])=1">SHALL contain exactly one [1..1] title="Reporting Parameters" (CONF:23-4142).</sch:assert>
      <sch:assert id="a-23-4143-error" test="count(cda:text)=1">SHALL contain exactly one [1..1] text (CONF:23-4143).</sch:assert>
      <sch:assert id="a-23-3277-error" test="count(cda:entry[@typeCode='DRIV'][count(cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8']])=1])=1">SHALL contain exactly one [1..1] entry (CONF:23-3277) such that it SHALL contain exactly one [1..1] @typeCode="DRIV" Is derived from (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:23-3278). SHALL contain exactly one [1..1] Reporting Parameters Act (identifier: urn:oid:2.16.840.1.113883.10.20.17.3.8) (CONF:23-17496).</sch:assert>
    </sch:rule>
    <sch:rule id="Reporting-parameters-section-code-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1']]/cda:code">
      <sch:assert id="a-23-19229-error" test="@code='55187-9'">This code SHALL contain exactly one [1..1] @code="55187-9" Reporting Parameters (CONF:23-19229).</sch:assert>
      <sch:assert id="a-23-26552-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:23-26552).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Result-Observation-pattern-extension-check">
    <sch:rule id="Result-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2']">
      <sch:assert id="a-1198-7136-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-7136) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.2" (CONF:1198-9138). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32575).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Result-Observation-pattern-errors">
    <sch:rule id="Result-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-7130-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1198-7130).</sch:assert>
      <sch:assert id="a-1198-7131-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1198-7131).</sch:assert>
      <sch:assert id="a-1198-7136-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-7136) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.2" (CONF:1198-9138). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32575).</sch:assert>
      <sch:assert id="a-1198-7137-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:1198-7137).</sch:assert>
      <sch:assert id="a-1198-7133-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHOULD be selected from CodeSystem LOINC (urn:oid:2.16.840.1.113883.6.1) (CONF:1198-7133).</sch:assert>
      <sch:assert id="a-1198-7134-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1198-7134).</sch:assert>
      <sch:assert id="a-1198-7140-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1198-7140).</sch:assert>
      <sch:assert id="a-1198-7143-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:1198-7143).</sch:assert>
    </sch:rule>
    <sch:rule id="Result-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01']]/cda:statusCode">
      <sch:assert id="a-1198-14849-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet Result Status urn:oid:2.16.840.1.113883.11.20.9.39 STATIC (CONF:1198-14849).</sch:assert>
    </sch:rule>
    <sch:rule id="Result-Observation-interpretationCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01']]/cda:interpretationCode">
      <sch:assert id="a-1198-32476-error" test="@code">The interpretationCode, if present, SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet Observation Interpretation (HL7) urn:oid:2.16.840.1.113883.1.11.78 STATIC (CONF:1198-32476).</sch:assert>
    </sch:rule>
    <sch:rule id="Result-Observation-referenceRange-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01']]/cda:referenceRange">
      <sch:assert id="a-1198-7151-error" test="count(cda:observationRange)=1">The referenceRange, if present, SHALL contain exactly one [1..1] observationRange (CONF:1198-7151).</sch:assert>
    </sch:rule>
    <sch:rule id="Result-Observation-referenceRange-observationRange-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01']]/cda:referenceRange/cda:observationRange">
      <sch:assert id="a-1198-7152-error" test="count(cda:code)=0">This observationRange SHALL NOT contain [0..0] code (CONF:1198-7152).</sch:assert>
      <sch:assert id="a-1198-32175-error" test="count(cda:value)=1">This observationRange SHALL contain exactly one [1..1] value (CONF:1198-32175).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Result-pattern-extension-check">
    <sch:rule id="Result-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.87']">
      <sch:assert id="a-4444-11672-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11672) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.87" (CONF:4444-11673). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27035).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Result-pattern-errors">
    <sch:rule id="Result-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.87'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-11672-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.87'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11672) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.87" (CONF:4444-11673). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27035).</sch:assert>
      <sch:assert id="a-4444-30011-error" test="count(cda:effectiveTime)=1">SHALL contain  exactly one [1..1] effectiveTime (CONF:4444-30011).</sch:assert>
      <sch:assert id="a-4444-30013-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])=0">SHALL NOT contain [0..0] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:4444-30013).</sch:assert>
    </sch:rule>
    <sch:rule id="Result-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.87'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-30014-error" test="@value">The effectiveTime, if present, SHALL contain exactly one [1..1] @value (CONF:4444-30014).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Service-Delivery-Location-pattern-errors">
    <sch:rule id="Service-Delivery-Location-errors" context="cda:participationRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.32']]">
      <sch:assert id="a-81-28426-error" test="@classCode='SDLOC'">SHALL contain exactly one [1..1] @classCode="SDLOC" (CodeSystem: RoleCode urn:oid:2.16.840.1.113883.5.111 STATIC) (CONF:81-7758).</sch:assert>
      <sch:assert id="a-81-10524-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.32'][not(@extension)])=1">SHALL contain exactly one [1..1] templateId (CONF:81-7635) such that it  SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.32" (CONF:81-10524).</sch:assert>
      <!-- 08-14-2019 Changed conformance text from STATIC to DYNAMIC -->
      <sch:assert id="a-81-16850-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHALL be selected from ValueSet HealthcareServiceLocation urn:oid:2.16.840.1.113883.1.11.20275 DYNAMIC (CONF:81-16850).</sch:assert>
    </sch:rule>
    <sch:rule id="Service-Delivery-Location-playingEntity-errors" context="cda:participationRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.32']]/cda:playingEntity">
      <sch:assert id="a-81-7763-error" test="@classCode='PLC'">The playingEntity, if present, SHALL contain exactly one [1..1] @classCode="PLC" (CodeSystem: EntityClass urn:oid:2.16.840.1.113883.5.41 STATIC) (CONF:81-7763).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Severity-Observation-pattern-extension-check">
    <sch:rule id="Severity-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.22.4.8']">
      <sch:assert id="a-1098-7347-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-7347) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.8" (CONF:1098-10525). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32577).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Severity-Observation-pattern-errors">
    <sch:rule id="Severity-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.8'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7345-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-7345).</sch:assert>
      <sch:assert id="a-1098-7346-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-7346).</sch:assert>
      <sch:assert id="a-1098-7347-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.8'][@extension='2014-06-09'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-7347) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.8" (CONF:1098-10525). SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32577).</sch:assert>
      <sch:assert id="a-1098-19168-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-19168).</sch:assert>
      <sch:assert id="a-1098-7352-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-7352).</sch:assert>
      <sch:assert id="a-1098-7356-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD", where the code SHALL be selected from ValueSet Problem Severity urn:oid:2.16.840.1.113883.3.88.12.3221.6.8 DYNAMIC (CONF:1098-7356).</sch:assert>
    </sch:rule>
    <sch:rule id="Severity-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.8'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-19169-error" test="@code='SEV'">This code SHALL contain exactly one [1..1] @code="SEV" Severity (CONF:1098-19169).</sch:assert>
      <sch:assert id="a-1098-32170-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1098-32170).</sch:assert>
    </sch:rule>
    <sch:rule id="Severity-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.8'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-19115-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14 STATIC) (CONF:1098-19115).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Status-pattern-extension-check">
    <sch:rule id="Status-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.93']">
      <sch:assert id="a-4444-11881-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-11881) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.93" (CONF:4444-11882) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29586)..</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Status-pattern-errors">
    <sch:rule id="Status-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.93'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-11879-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:4444-11879).</sch:assert>
      <sch:assert id="a-4444-11880-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:4444-11880).</sch:assert>
      <sch:assert id="a-4444-11881-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.93'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-11881) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.93" (CONF:4444-11882) SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-29586)..</sch:assert>
      <sch:assert id="a-4444-11885-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-11885).</sch:assert>
      <sch:assert id="a-4444-11887-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:4444-11887).</sch:assert>
    </sch:rule>
    <sch:rule id="Status-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.93'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-11886-error" test="@code='33999-4'">This code SHALL contain exactly one [1..1] @code="33999-4" status (CONF:4444-11886).</sch:assert>
      <sch:assert id="a-4444-27011-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-27011).</sch:assert>
    </sch:rule>
    <sch:rule id="Status-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.93'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-29587-error" test="count(@value)=1">The effectiveTime, if present, SHALL contain exactly one [1..1] @value (CONF:4444-29587).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Substance-Administered-Act-pattern-errors">
    <sch:rule id="Substance-Administered-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.118']]">
      <sch:assert id="a-1098-31500-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1098-31500).</sch:assert>
      <sch:assert id="a-1098-31501-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:1098-31501).</sch:assert>
      <sch:assert id="a-1098-31502-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.118'])=1">SHALL contain exactly one [1..1] templateId (CONF:1098-31502) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.4.118" (CONF:1098-31503).</sch:assert>
      <sch:assert id="a-1098-31504-error" test="count(cda:id)&gt;=1">SHALL contain at least one [1..*] id (CONF:1098-31504).</sch:assert>
      <sch:assert id="a-1098-31506-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-31506).</sch:assert>
      <sch:assert id="a-1098-31505-error" test="count(cda:statusCode[@code='completed'])=1">SHALL contain exactly one [1..1] statusCode="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-31505).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Administered-Act-code-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.118']]/cda:code">
      <sch:assert id="a-1098-31507-error" test="@code='416118004'">This code SHALL contain exactly one [1..1] @code="416118004" Administration (CONF:1098-31507).</sch:assert>
      <sch:assert id="a-1098-31508-error" test="@codeSystem='2.16.840.1.113883.6.96'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.96" (CodeSystem: SNOMED CT urn:oid:2.16.840.1.113883.6.96) (CONF:1098-31508).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Substance-Device-Allergy-Intolerance-Observation-pattern-extension-check">
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90']">
      <sch:assert id="a-1098-16305-extension-error" test="@extension='2014-06-09'">SHALL contain exactly one [1..1] templateId (CONF:1098-16305) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.90" (CONF:1098-16306).  SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32527).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Substance-Device-Allergy-Intolerance-Observation-pattern-errors">
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-16303-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6 STATIC) (CONF:1098-16303).</sch:assert>
      <sch:assert id="a-1098-16304-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001 STATIC) (CONF:1098-16304).</sch:assert>
      <sch:assert id="a-1098-16305-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']) = 1">SHALL contain exactly one [1..1] templateId (CONF:1098-16305) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.90" (CONF:1098-16306).  SHALL contain exactly one [1..1] @extension="2014-06-09" (CONF:1098-32527).</sch:assert>
      <sch:assert id="a-1098-16307-error" test="count(cda:id) &gt;= 1">SHALL contain at least one [1..*] id (CONF:1098-16307).</sch:assert>
      <sch:assert id="a-1098-16345-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1098-16345).</sch:assert>
      <sch:assert id="a-1098-16308-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:1098-16308).</sch:assert>
      <sch:assert id="a-1098-16309-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:1098-16309).</sch:assert>
      <sch:assert id="a-1098-16312-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:1098-16312).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-16346-error" test="@code='ASSERTION'">This code SHALL contain exactly one [1..1] @code="ASSERTION" Assertion (CONF:1098-16346).</sch:assert>
      <sch:assert id="a-1098-32171-error" test="@codeSystem='2.16.840.1.113883.5.4'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.5.4" (CodeSystem: ActCode urn:oid:2.16.840.1.113883.5.4) (CONF:1098-32171).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-statusCode-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']]/cda:statusCode">
      <sch:assert id="a-1098-26354-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:1098-26354).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']]/cda:effectiveTime">
      <sch:assert id="a-1098-31536-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:1098-31536).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-value-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']]/cda:value">
      <sch:assert id="a-1098-16317-error" test="@code">This value SHALL contain exactly one [1..1] @code, which SHALL be selected from ValueSet Allergy and Intolerance Type urn:oid:2.16.840.1.113883.3.88.12.3221.6.2 DYNAMIC (CONF:1098-16317).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Substance-Recommended-pattern-extension-check">
    <sch:rule id="Substance-Recommended-extension-check" context="cda:substanceAdministration/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.75']">
      <sch:assert id="a-4444-13785-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-13785) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.75" (CONF:4444-13786).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27152).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Substance-Recommended-pattern-errors">
    <sch:rule id="Substance-Recommended-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.75'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-27495-error" test="@classCode='SBADM'">SHALL contain exactly one [1..1] @classCode="SBADM" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-27495).</sch:assert>
      <sch:assert id="a-4444-13784-error" test="@moodCode='INT'">SHALL contain exactly one [1..1] @moodCode="INT" intent (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-13784).</sch:assert>
      <sch:assert id="a-4444-13785-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.75'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-13785) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.75" (CONF:4444-13786).  SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-27152).</sch:assert>
      <sch:assert id="a-4444-27988-error" test="count(cda:consumable)=1">SHALL contain exactly one [1..1] consumable (CONF:4444-27988).</sch:assert>
      <sch:assert id="a-4444-27720-error" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.155'][@extension='2019-12-01']])=1">SHALL contain exactly one [1..1] Author (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.3.155:2019-12-01)(CONF:4444-27720).</sch:assert>
      <!-- 4444-29832 added for STU 5.2 -->
      <sch:assert id="a-4444-29832-error" test="(@negationInd='true' and count(cda:entryRelationship[cda:observation[@classCode='OBS'][@moodCode='EVN'][cda:templateId[@root='2.16.840.1.113883.10.20.24.3.88'][@extension='2017-08-01']]])=1) or (not(@negationInd)) or (@negationInd != 'true')">If @negationInd="true" is present, SHALL contain one [1..1] entryRelationship such that it contains exactly one [1..1] Reason (V3) to state the reason for Substance Not Recommended (CONF:4444-29832).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Recommended-consumable-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.75'][@extension='2019-12-01']]/cda:consumable">
      <sch:assert id="a-4444-27989-error" test="count(cda:manufacturedProduct)=1">This consumable SHALL contain exactly one [1..1] manufacturedProduct (CONF:4444-27989).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Recommended-consumable-manufacturedProduct-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.75'][@extension='2019-12-01']]/cda:consumable/cda:manufacturedProduct">
      <sch:assert id="a-4444-27990-error" test="count(cda:manufacturedMaterial)=1">This manufacturedProduct SHALL contain exactly one [1..1] manufacturedMaterial (CONF:4444-27990).</sch:assert>
    </sch:rule>
    <sch:rule id="Substance-Recommended-consumable-manufacturedProduct-manufacturedMaterial-errors" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.75'][@extension='2019-12-01']]/cda:consumable/cda:manufacturedProduct/cda:manufacturedMaterial">
      <sch:assert id="a-4444-27991-error" test="count(cda:code)=1">This manufacturedMaterial SHALL contain exactly one [1..1] code (CONF:4444-27991).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Symptom-Concern-Act-pattern-extension-check">
    <sch:rule id="Symptom-Concern-Act-extension-check" context="cda:act/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.138']">
      <sch:assert id="a-4444-28539-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28539) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.138" (CONF:4444-28544). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28694).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Symptom-Concern-Act-pattern-errors">
    <sch:rule id="Symptom-Concern-Act-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.138'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28547-error" test="@classCode='ACT'">SHALL contain exactly one [1..1] @classCode="ACT" Act (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28547).</sch:assert>
      <sch:assert id="a-4444-28548-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28548).</sch:assert>
      <sch:assert id="a-4444-28539-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.138'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28539) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.138" (CONF:4444-28544). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28694).</sch:assert>
      <sch:assert id="a-4444-28540-error" test="count(cda:statusCode)=1">SHALL contain exactly one [1..1] statusCode (CONF:4444-28540).</sch:assert>
      <sch:assert id="a-4444-28538-error" test="count(cda:entryRelationship[@typeCode='SUBJ'][count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.136'][@extension='2019-12-01']])=1])=1">SHALL contain exactly one [1..1] entryRelationship (CONF:4444-28538) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" Has subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:4444-28543). SHALL contain exactly one [1..1] Symptom V3 (identifier: urn:oid:2.16.840.1.113883.10.20.24.3.136:2019-12-01) (CONF:4444-28542).</sch:assert>
    </sch:rule>
    <sch:rule id="Symptom-Concern-Act-statusCode-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.138'][@extension='2019-12-01']]/cda:statusCode">
      <sch:assert id="a-4444-28545-error" test="@code">This statusCode SHALL contain exactly one [1..1] @code (CodeSystem: ActStatus urn:oid:2.16.840.1.113883.5.14) (CONF:4444-28545).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Symptom-pattern-extension-check">
    <sch:rule id="Symptom-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.136']">
      <sch:assert id="a-4444-28514-extension-error" test="@extension='2019-12-01'">SHALL contain exactly one [1..1] templateId (CONF:4444-28514) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.136" (CONF:4444-28518). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28855).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Symptom-pattern-errors">
    <sch:rule id="Symptom-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.136'][@extension='2019-12-01']]">
      <sch:assert id="a-4444-28524-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:4444-28524).</sch:assert>
      <sch:assert id="a-4444-28525-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:4444-28525).</sch:assert>
      <sch:assert id="a-4444-28526-error" test="not(@negationInd)">SHALL NOT contain [0..0] @negationInd (CONF:4444-28526).</sch:assert>
      <sch:assert id="a-4444-28514-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.136'][@extension='2019-12-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:4444-28514) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.136" (CONF:4444-28518). SHALL contain exactly one [1..1] @extension="2019-12-01" (CONF:4444-28855).</sch:assert>
      <sch:assert id="a-4444-28515-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:4444-28515).</sch:assert>
      <sch:assert id="a-4444-28854-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] effectiveTime (CONF:4444-28854).</sch:assert>
      <sch:assert id="a-4444-28516-error" test="count(cda:value[@xsi:type='CD'])=1">SHALL contain exactly one [1..1] value with @xsi:type="CD" (CONF:4444-28516).</sch:assert>
      <sch:assert id="a-4444-28667-error" test="count(../../cda:templateId[@root='2.16.840.1.113883.10.20.24.3.138'][@extension='2019-12-01'])=1">This template SHALL be contained by a Symptom Concern Act (V4) (CONF:4444-28667).</sch:assert>
    </sch:rule>
    <sch:rule id="Symptom-code-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.136'][@extension='2019-12-01']]/cda:code">
      <sch:assert id="a-4444-28520-error" test="@code='75325-1'">This code SHALL contain exactly one [1..1] @code="75325-1" Symptom (CONF:4444-28520).</sch:assert>
      <sch:assert id="a-4444-28521-error" test="@codeSystem='2.16.840.1.113883.6.1'">This code SHALL contain exactly one [1..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:4444-28521).</sch:assert>
    </sch:rule>
    <sch:rule id="Symptom-effectiveTime-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.136'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-28856-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:4444-28856).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Target-Outcome-pattern-extension-check">
    <sch:rule id="Target-Outcome-extension-check" context="cda:observation/cda:templateId[@root='2.16.840.1.113883.10.20.24.3.119']">
      <sch:assert id="a-3343-28025-extension-error" test="@extension='2017-08-01'">SHALL contain exactly one [1..1] templateId (CONF:3343-28025) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.119" (CONF:3343-28028). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-28029).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Target-Outcome-pattern-errors">
    <sch:rule id="Target-Outcome-errors" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.119'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-28033-error" test="@classCode='OBS'">SHALL contain exactly one [1..1] @classCode="OBS" Observation (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:3343-28033).</sch:assert>
      <sch:assert id="a-3343-28034-error" test="@moodCode='EVN'">SHALL contain exactly one [1..1] @moodCode="EVN" Event (CodeSystem: ActMood urn:oid:2.16.840.1.113883.5.1001) (CONF:3343-28034).</sch:assert>
      <sch:assert id="a-3343-28025-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.3.119'][@extension='2017-08-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:3343-28025) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.3.119" (CONF:3343-28028). SHALL contain exactly one [1..1] @extension="2017-08-01" (CONF:3343-28029).</sch:assert>
      <sch:assert id="a-3343-28026-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code, which SHOULD be selected from CodeSystem LOINC (urn:oid:2.16.840.1.113883.6.1) (CONF:3343-28026).</sch:assert>
      <sch:assert id="a-3343-28027-error" test="count(cda:value)=1">SHALL contain exactly one [1..1] value (CONF:3343-28027).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US-Realm-Address-pattern-errors">
    <sch:rule id="US-Realm-Address-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3']]/cda:recordTarget/cda:patientRole/cda:addr             | cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18']]/cda:performer/cda:assignedEntity/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:author/cda:assignedAuthor/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:dataEnterer/cda:assignedEntity/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:legalAuthenticator/cda:assignedEntity/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian/cda:addr">
      <sch:assert id="a-81-7292-error" test="count(cda:city)=1">SHALL contain exactly one [1..1] city (CONF:81-7292).</sch:assert>
      <sch:assert id="a-81-7291-error" test="count(cda:streetAddressLine) &gt; 0 and count(cda:streetAddressLine) &lt; 5">SHALL contain at least one and not more than 4 [1..4] streetAddressLine (CONF:81-7291).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US-Realm-Date-and-Time-pattern-errors">
    <sch:rule id="US-Realm-Date-and-Time-effectiveTime-errors" context="cda:effectiveTime[parent::cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]]              | cda:effectiveTime[parent::cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3']]]             | cda:effectiveTime[parent::cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.27.1.1'][@extension='2016-09-01']]]">
      <sch:assert id="a-81-10127-e-error" test="string-length(@value)&gt;=8">SHALL be precise to the day (CONF:81-10127).</sch:assert>
    </sch:rule>
    <sch:rule id="US-Realm-Date-and-Time-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:author/cda:time                                                             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:legalAuthenticator/cda:time">
      <sch:assert id="a-81-10127-t-error" test="string-length(@value)&gt;=8">SHALL be precise to the day (CONF:81-10127).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US_Realm-pattern-extension-check">
    <sch:rule id="US_Realm-extension-errors" context="cda:ClinicalDocument/cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']">
      <sch:assert id="a-1198-5252-extension-error" test="@extension='2015-08-01'">SHALL contain exactly one [1..1] templateId (CONF:1198-5252) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.1.1" (CONF:1198-10036). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32503).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US_Realm-pattern-errors">
    <sch:rule id="US_Realm-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-5252-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'])=1">SHALL contain exactly one [1..1] templateId (CONF:1198-5252) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.22.1.1" (CONF:1198-10036). SHALL contain exactly one [1..1] @extension="2015-08-01" (CONF:1198-32503).</sch:assert>
      <sch:assert id="a-1198-16791-error" test="count(cda:realmCode[@code='US'])=1">SHALL contain exactly one [1..1] realmCode="US" (CONF:1198-16791)</sch:assert>
      <sch:assert id="a-1198-5361-error" test="count(cda:typeId)=1">SHALL contain exactly one [1..1] typeId (CONF:1198-5361).</sch:assert>
      <sch:assert id="a-1198-5363-error" test="count(cda:id)=1">SHALL contain exactly one [1..1] id (CONF:1198-5363).</sch:assert>
      <sch:assert id="a-1198-5253-error" test="count(cda:code)=1">SHALL contain exactly one [1..1] code (CONF:1198-5253).</sch:assert>
      <sch:assert id="a-1198-5254-error" test="count(cda:title)=1">SHALL contain exactly one [1..1] title (CONF:1198-5254).</sch:assert>
      <sch:assert id="a-1198-5256-error" test="count(cda:effectiveTime)=1">SHALL contain exactly one [1..1] US Realm Date and Time (DTM.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.4) (CONF:1198-5256).</sch:assert>
      <sch:assert id="a-1198-5259-error" test="count(cda:confidentialityCode)=1">SHALL contain exactly one [1..1] confidentialityCode (CONF:1198-5259).</sch:assert>
      <sch:assert id="a-1198-5372-error" test="count(cda:languageCode)=1">SHALL contain exactly one [1..1] languageCode, which SHALL be selected from ValueSet Language urn:oid:2.16.840.1.113883.1.11.11526 DYNAMIC (CONF:1198-5372).</sch:assert>
      <sch:assert id="a-1198-5266-error" test="count(cda:recordTarget) &gt; 0">SHALL contain at least one [1..*] recordTarget (CONF:1198-5266).</sch:assert>
      <sch:assert id="a-1198-5444-error" test="count(cda:author) &gt; 0">SHALL contain at least one [1..*] author (CONF:1198-5444).</sch:assert>
      <sch:assert id="a-1198-5519-error" test="count(cda:custodian)=1">SHALL contain exactly one [1..1] custodian (CONF:1198-5519).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-typeId-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:typeId">
      <sch:assert id="a-1198-5250-error" test="@root='2.16.840.1.113883.1.3'">This typeId SHALL contain exactly one [1..1] @root="2.16.840.1.113883.1.3" (CONF:1198-5250).</sch:assert>
      <sch:assert id="a-1198-5251-error" test="@extension='POCD_HD000040'">This typeId SHALL contain exactly one [1..1] @extension="POCD_HD000040" (CONF:1198-5251).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-setId-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:setId">
      <sch:assert id="a-1198-6380-error" test="count(../cda:versionNumber)=1">If setId is present versionNumber SHALL be present (CONF:1198-6380).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-versionNumber-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:versionNumber">
      <sch:assert id="a-1198-6387-error" test="count(../cda:setId)=1">If versionNumber is present setId SHALL be present (CONF:1198-6387).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget">
      <sch:assert id="a-1198-5267-error" test="count(cda:patientRole)=1">Such recordTargets SHALL contain exactly one [1..1] patientRole (CONF:1198-5267).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole">
      <sch:assert id="a-1198-5268-error" test="count(cda:id) &gt; 0">This patientRole SHALL contain at least one [1..*] id (CONF:1198-5268).</sch:assert>
      <sch:assert id="a-1198-5271-error" test="count(cda:addr) &gt; 0">This patientRole SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5271).</sch:assert>
      <sch:assert id="a-1198-5280-error" test="count(cda:telecom) &gt; 0">This patientRole SHALL contain at least one [1..*] telecom (CONF:1198-5280).</sch:assert>
      <sch:assert id="a-1198-5283-error" test="count(cda:patient)=1">This patientRole SHALL contain exactly one [1..1] patient (CONF:1198-5283).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient">
      <sch:assert id="a-1198-5284-error" test="count(cda:name)&gt; 0">This patient SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5284).</sch:assert>
      <sch:assert id="a-1198-6394-error" test="count(cda:administrativeGenderCode)=1">This patient SHALL contain exactly one [1..1] administrativeGenderCode, which SHALL be selected from ValueSet Administrative Gender (HL7 V3) urn:oid:2.16.840.1.113883.1.11.1 DYNAMIC (CONF:1198-6394).</sch:assert>
      <sch:assert id="a-1198-5298-error" test="count(cda:birthTime)=1">This patient SHALL contain exactly one [1..1] birthTime (CONF:1198-5298).</sch:assert>
      <sch:assert id="a-1198-5322-error" test="count(cda:raceCode)=1">This patient SHALL contain exactly one [1..1] raceCode, which SHALL be selected from ValueSet Race Category Excluding Nulls urn:oid:2.16.840.1.113883.3.2074.1.1.3 DYNAMIC (CONF:1198-5322).</sch:assert>
      <sch:assert id="a-1198-5323-error" test="count(cda:ethnicGroupCode)=1">This patient SHALL contain exactly one [1..1] ethnicGroupCode, which SHALL be selected from ValueSet Ethnicity urn:oid:2.16.840.1.114222.4.11.837 DYNAMIC (CONF:1198-5323).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-birthTime-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:birthTime">
      <sch:assert id="a-1198-5299-error" test="string-length(@value) &gt;= 4">SHALL be precise to year (CONF:1198-5299).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-raceCode-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/sdtc:raceCode">
      <sch:assert id="a-1198-31347-error" test="count(../cda:raceCode)=1">If sdtc:raceCode is present, then the patient SHALL contain [1..1] raceCode (CONF:1198-31347).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-guardian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian">
      <sch:assert id="a-1198-5385-error" test="count(cda:guardianPerson)=1">The guardian, if present, SHALL contain exactly one [1..1] guardianPerson (CONF:1198-5385).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-guardian-guardianPerson-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian/cda:guardianPerson">
      <sch:assert id="a-1198-5386-error" test="count(cda:name) &gt; 0">This guardianPerson SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5386).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-birthplace-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:birthplace">
      <sch:assert id="a-1198-5396-error" test="count(cda:place)=1">The birthplace, if present, SHALL contain exactly one [1..1] place (CONF:1198-5396).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-birthplace-place-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:birthplace/cda:place">
      <sch:assert id="a-1198-5397-error" test="count(cda:addr)=1">This place SHALL contain exactly one [1..1] addr (CONF:1198-5397).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-languageCommunication-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:languageCommunication">
      <sch:assert id="a-1198-5407-error" test="count(cda:languageCode)=1">The languageCommunication, if present, SHALL contain exactly one [1..1] languageCode, which SHALL be selected from ValueSet Language urn:oid:2.16.840.1.113883.1.11.11526 DYNAMIC (CONF:1198-5407).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-providerOrganization-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:providerOrganization">
      <sch:assert id="a-1198-5417-error" test="count(cda:id) &gt; 0">The providerOrganization, if present, SHALL contain at least one [1..*] id (CONF:1198-5417).</sch:assert>
      <sch:assert id="a-1198-5419-error" test="count(cda:name) &gt; 0">The providerOrganization, if present, SHALL contain at least one [1..*] name (CONF:1198-5419).</sch:assert>
      <sch:assert id="a-1198-5420-error" test="count(cda:telecom) &gt; 0">The providerOrganization, if present, SHALL contain at least one [1..*] telecom (CONF:1198-5420).</sch:assert>
      <sch:assert id="a-1198-5422-error" test="count(cda:addr) &gt; 0">The providerOrganization, if present, SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5422).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author">
      <sch:assert id="a-1198-5445-error" test="count(cda:time)=1">Such authors SHALL contain exactly one [1..1] US Realm Date and Time (DTM.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.4) (CONF:1198-5445).</sch:assert>
      <sch:assert id="a-1198-5448-error" test="count(cda:assignedAuthor)=1">Such authors SHALL contain exactly one [1..1] assignedAuthor (CONF:1198-5448).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor">
      <sch:assert id="a-1198-5449-error" test="count(cda:id) &gt; 0">This assignedAuthor SHALL contain at least one [1..*] id (CONF:1198-5449).</sch:assert>
      <sch:assert id="a-1198-5452-error" test="count(cda:addr) &gt; 0">This assignedAuthor SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5452).</sch:assert>
      <sch:assert id="a-1198-5428-error" test="count(cda:telecom) &gt; 0">This assignedAuthor SHALL contain at least one [1..*] telecom (CONF:1198-5428).</sch:assert>
      <sch:assert id="a-1198-16790-error" test="count(cda:assignedPerson)=1 or count(cda:assignedAuthoringDevice)=1">There SHALL be exactly one assignedAuthor/assignedPerson or exactly one assignedAuthor/assignedAuthoringDevice (CONF:1198-16790).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-code-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor/cda:code">
      <sch:assert id="a-1198-16788-error" test="@code">The code, if present, SHALL contain exactly one [1..1] @code, which SHOULD be selected from ValueSet Healthcare Provider Taxonomy (HIPAA) urn:oid:2.16.840.1.114222.4.11.1066 DYNAMIC (CONF:1198-16788).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-assignedPerson-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor/cda:assignedPerson">
      <sch:assert id="a-1198-16789-error" test="count(cda:name) &gt; 0">The assignedPerson, if present, SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-16789).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-assignedAuthoringDevice-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor/cda:assignedAuthoringDevice">
      <sch:assert id="a-1198-16784-error" test="count(cda:manufacturerModelName)=1">The assignedAuthoringDevice, if present, SHALL contain exactly one [1..1] manufacturerModelName (CONF:1198-16784).</sch:assert>
      <sch:assert id="a-1198-16785-error" test="count(cda:softwareName)=1">The assignedAuthoringDevice, if present, SHALL contain exactly one [1..1] softwareName (CONF:1198-16785).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-dataEnterer-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:dataEnterer">
      <sch:assert id="a-1198-5442-error" test="count(cda:assignedEntity)=1">The dataEnterer, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1198-5442).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-dataEnterer-assignedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:dataEnterer/cda:assignedEntity">
      <sch:assert id="a-1198-5443-error" test="count(cda:id) &gt; 0">This assignedEntity SHALL contain at least one [1..*] id (CONF:1198-5443).</sch:assert>
      <sch:assert id="a-1198-5460-error" test="count(cda:addr) &gt; 0">This assignedEntity SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5460).</sch:assert>
      <sch:assert id="a-1198-5466-error" test="count(cda:telecom) &gt; 0">This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1198-5466).</sch:assert>
      <sch:assert id="a-1198-5469-error" test="count(cda:assignedPerson)=1">This assignedEntity SHALL contain exactly one [1..1] assignedPerson (CONF:1198-5469).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-dataEnterer-assignedEntity-assignedPerson-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:dataEnterer/cda:assignedEntity/cda:assignedPerson">
      <sch:assert id="a-1198-5470-error" test="count(cda:name) &gt; 0">This assignedPerson SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5470).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-custodian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:custodian">
      <sch:assert id="a-1198-5520-error" test="count(cda:assignedCustodian)=1">This custodian SHALL contain exactly one [1..1] assignedCustodian (CONF:1198-5520).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-custodian-assignedCustodian-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:custodian/cda:assignedCustodian">
      <sch:assert id="a-1198-5521-error" test="count(cda:representedCustodianOrganization)=1">This assignedCustodian SHALL contain exactly one [1..1] representedCustodianOrganization (CONF:1198-5521).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-custodian-assignedCustodian-representedCustodianOrganization-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization">
      <sch:assert id="a-1198-5522-error" test="count(cda:id) &gt; 0">This representedCustodianOrganization SHALL contain at least one [1..*] id (CONF:1198-5522).</sch:assert>
      <sch:assert id="a-1198-5524-error" test="count(cda:name)=1">This representedCustodianOrganization SHALL contain exactly one [1..1] name (CONF:1198-5524).</sch:assert>
      <sch:assert id="a-1198-5525-error" test="count(cda:telecom)=1">This representedCustodianOrganization SHALL contain exactly one [1..1] telecom (CONF:1198-5525).</sch:assert>
      <sch:assert id="a-1198-5559-error" test="count(cda:addr)=1">This representedCustodianOrganization SHALL contain exactly one [1..1] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5559).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-informant-assignedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:informant/cda:assignedEntity">
      <sch:assert id="a-1198-9945-error" test="count(cda:id) &gt; 0">This assignedEntity SHALL contain at least one [1..*] id (CONF:1198-9945).</sch:assert>
      <sch:assert id="a-1198-8220-error" test="count(cda:addr) &gt; 0">This assignedEntity SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-8220).</sch:assert>
      <sch:assert id="a-1198-8221-error" test="count(cda:assignedPerson) =1">This assignedEntity SHALL contain exactly one [1..1] assignedPerson (CONF:1198-8221).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-informant-assignedEntity-assignedPerson-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:informant/cda:assignedEntity/cda:assignedPerson">
      <sch:assert id="a-1198-8222-error" test="count(cda:name) &gt; 0">This assignedPerson SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-8222).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-informationRecipient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:informationRecipient">
      <sch:assert id="a-1198-5566-error" test="count(cda:intendedRecipient)=1">The informationRecipient, if present, SHALL contain exactly one [1..1] intendedRecipient (CONF:1198-5566).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-informationRecipient-intendedRecipient-informationRecipient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:informationRecipient/cda:intendedRecipient/cda:informationRecipient">
      <sch:assert id="a-1198-5568-error" test="count(cda:name) &gt; 0">The informationRecipient, if present, SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5568).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-informationRecipient-intendedRecipient-receivedOrganization-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:informationRecipient/cda:intendedRecipient/cda:receivedOrganization">
      <sch:assert id="a-1198-5578-error" test="count(cda:name)=1">The receivedOrganization, if present, SHALL contain exactly one [1..1] name (CONF:1198-5578).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-legalAuthenticator-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:legalAuthenticator">
      <sch:assert id="a-1198-5580-error" test="count(cda:time)=1">The legalAuthenticator, if present, SHALL contain exactly one [1..1] US Realm Date and Time (DTM.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.4) (CONF:1198-5580).</sch:assert>
      <sch:assert id="a-1198-5583-error" test="count(cda:signatureCode)=1">The legalAuthenticator, if present, SHALL contain exactly one [1..1] signatureCode (CONF:1198-5583).</sch:assert>
      <sch:assert id="a-1198-5585-error" test="count(cda:assignedEntity)=1">The legalAuthenticator, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1198-5585).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-legalAuthenticator-signatureCode-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:legalAuthenticator/cda:signatureCode">
      <sch:assert id="a-1198-5584-error" test="@code='S'">This signatureCode SHALL contain exactly one [1..1] @code="S" (CodeSystem: Participationsignature urn:oid:2.16.840.1.113883.5.89 STATIC) (CONF:1198-5584).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-legalAuthenticator-assignedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:legalAuthenticator/cda:assignedEntity">
      <sch:assert id="a-1198-5586-error" test="count(cda:id) &gt; 0">This assignedEntity SHALL contain at least one [1..*] id (CONF:1198-5586).</sch:assert>
      <sch:assert id="a-1198-5589-error" test="count(cda:addr) &gt; 0">This assignedEntity SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5589).</sch:assert>
      <sch:assert id="a-1198-5595-error" test="count(cda:telecom) &gt; 0">This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1198-5595).</sch:assert>
      <sch:assert id="a-1198-5597-error" test="count(cda:assignedPerson)=1">This assignedEntity SHALL contain exactly one [1..1] assignedPerson (CONF:1198-5597).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-legalAuthenticator-assignedEntity-assignedPerson-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:legalAuthenticator/cda:assignedEntity/cda:assignedPerson">
      <sch:assert id="a-1198-5598-error" test="count(cda:name) &gt; 0">This assignedPerson SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5598).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authenticator-signatureCode-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authenticator/cda:signatureCode">
      <sch:assert id="a-1198-5611-error" test="@code='S'">This signatureCode SHALL contain exactly one [1..1] @code="S" (CodeSystem: HL7ParticipationSignature urn:oid:2.16.840.1.113883.5.89 STATIC) (CONF:1198-5611).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authenticator-assignedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authenticator/cda:assignedEntity">
      <sch:assert id="a-1198-5613-error" test="count(cda:id) &gt; 0">This assignedEntity SHALL contain at least one [1..*] id (CONF:1198-5613).</sch:assert>
      <sch:assert id="a-1198-5616-error" test="count(cda:addr) &gt; 0">This assignedEntity SHALL contain at least one [1..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5616).</sch:assert>
      <sch:assert id="a-1198-5622-error" test="count(cda:telecom) &gt; 0">This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1198-5622).</sch:assert>
      <sch:assert id="a-1198-5624-error" test="count(cda:assignedPerson) = 1">This assignedEntity SHALL contain exactly one [1..1] assignedPerson (CONF:1198-5624).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authenticator-assignedEntity-assignedPerson-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authenticator/cda:assignedEntity/cda:assignedPerson">
      <sch:assert id="a-1198-5625-error" test="count(cda:name) &gt; 0">This assignedPerson SHALL contain at least one [1..*] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5625).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-inFulfillmentOf-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:inFulfillmentOf">
      <sch:assert id="a-1198-9953-error" test="count(cda:order)=1">The inFulfillmentOf, if present, SHALL contain exactly one [1..1] order (CONF:1198-9953).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-inFulfillmentOf-order-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:inFulfillmentOf/cda:order">
      <sch:assert id="a-1198-9954-error" test="count(cda:id) &gt; 0">This order SHALL contain at least one [1..*] id (CONF:1198-9954).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf">
      <sch:assert id="a-1198-14836-error" test="count(cda:serviceEvent)=1">The documentationOf, if present, SHALL contain exactly one [1..1] serviceEvent (CONF:1198-14836).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent">
      <sch:assert id="a-1198-14837-error" test="count(cda:effectiveTime)=1">This serviceEvent SHALL contain exactly one [1..1] effectiveTime (CONF:1198-14837).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-effectiveTime-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent/cda:effectiveTime">
      <sch:assert id="a-1198-14838-error" test="count(cda:low)=1">This effectiveTime SHALL contain exactly one [1..1] low (CONF:1198-14838).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-performer-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent/cda:performer">
      <sch:assert id="a-1198-14840-error" test="@typeCode=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.1.11.19601']/voc:code/@value">The performer, if present, SHALL contain exactly one [1..1] @typeCode, which SHALL be selected from ValueSet x_ServiceEventPerformer urn:oid:2.16.840.1.113883.1.11.19601 STATIC (CONF:1198-14840).</sch:assert>
      <sch:assert id="a-1198-14841-error" test="count(cda:assignedEntity)=1">The performer, if present, SHALL contain exactly one [1..1] assignedEntity (CONF:1198-14841).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-performer-assignedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1198-14846-error" test="count(cda:id) &gt; 0">This assignedEntity SHALL contain at least one [1..*] id (CONF:1198-14846).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-componentOf-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:componentOf">
      <sch:assert id="a-1198-9956-error" test="count(cda:encompassingEncounter)=1">The componentOf, if present, SHALL contain exactly one [1..1] encompassingEncounter (CONF:1198-9956).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-componentOf-encompassingEncounter-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:componentOf/cda:encompassingEncounter">
      <sch:assert id="a-1198-9959-error" test="count(cda:id) &gt; 0">This encompassingEncounter SHALL contain at least one [1..*] id (CONF:1198-9959).</sch:assert>
      <sch:assert id="a-1198-9958-error" test="count(cda:effectiveTime)=1">This encompassingEncounter SHALL contain exactly one [1..1] effectiveTime (CONF:1198-9958).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authorization-consent-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authorization/cda:consent">
      <sch:assert id="a-1198-16797-error" test="count(cda:statusCode)=1">This consent SHALL contain exactly one [1..1] statusCode (CONF:1198-16797).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authorization-consent-statusCode-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authorization/cda:consent/cda:statusCode">
      <sch:assert id="a-1198-16798-error" test="@code='completed'">This statusCode SHALL contain exactly one [1..1] @code="completed" Completed (CodeSystem: HL7ActClass urn:oid:2.16.840.1.113883.5.6) (CONF:1198-16798).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US-Realm-Patient-Name-pattern-errors">
    <sch:rule id="US-Realm-Patient-Name-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.123']]/cda:participant/cda:participantRole/cda:playingEntity/cda:name">
      <sch:assert id="a-81-7159-error" test="count(cda:family[@xsi:type='ST'])=1">SHALL contain exactly one [1..1] family (CONF:81-7159).</sch:assert>
      <sch:assert id="a-81-7157-error" test="count(cda:given[@xsi:type='ST']) &gt;=1">SHALL contain at least one [1..*] given (CONF:81-7157).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US-Realm-Person-Name-pattern-errors">
    <!-- Updated 07-15-2019 to add 81-9368 assertion  https://tracker.esacinc.com/browse/QRDA-617 -->
    <sch:rule id="US-Realm-Person-Name-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:patient             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3']]/cda:recordTarget/cda:patientRole/cda:patient             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian/cda:guardianPerson             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:author/cda:assignedAuthor/cda:assignedPerson             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:dataEnterer/cda:assignedEntity/cda:assignedPerson             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:informationRecipient/cda:intendedRecipient/cda:informationRecipient             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:legalAuthenticator/cda:assignedEntity/cda:assignedPerson">
      <sch:assert id="a-81-9368-error" test="count(cda:name) = 1">SHALL contain exactly one [1..1] name (CONF:81-9368).</sch:assert>
    </sch:rule>
    <sch:rule id="US-Realm-Person-Name-name-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:patient/cda:name             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3']]/cda:recordTarget/cda:patientRole/cda:patient/cda:name             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian/cda:guardianPerson/cda:name             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:author/cda:assignedAuthor/cda:assignedPerson/cda:name             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:dataEnterer/cda:assignedEntity/cda:assignedPerson/cda:name             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:informationRecipient/cda:intendedRecipient/cda:informationRecipient/cda:name             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:legalAuthenticator/cda:assignedEntity/cda:assignedPerson/cda:name">
      <sch:assert id="a-81-9371-error" test="(cda:given and cda:family) or (count(../cda:name/*)=0 and string-length(../cda:name/text()[normalize-space()])!=0)">The content of name SHALL be either a conformant Patient Name (PTN.US.FIELDED), or a string (CONF:81-9371).</sch:assert>
      <sch:assert id="a-81-9372-error" test="(cda:given and cda:family) or (count(../cda:name/*)=0)">The string SHALL NOT contain name parts (CONF:81-9372).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_CD_CE-errors">
    <sch:rule id="r-validate_CD_CE-errors" context="//cda:code|cda:value[@xsi:type='CD']|cda:value[@xsi:type='CE']|cda:administrationUnitCode|cda:administrativeGenderCode|cda:awarenessCode|cda:confidentialityCode|cda:dischargeDispositionCode|cda:ethnicGroupCode|cda:functionCode|cda:interpretationCode|cda:maritalStatusCode|cda:methodCode|cda:modeCode|cda:priorityCode|cda:proficiencyLevelCode|cda:RaceCode|cda:religiousAffiliationCode|cda:routeCode|cda:standardIndustryClassCode">
      <sch:assert id="a-CMS_0107-error" test="(parent::cda:regionOfInterest) or ((@code or @nullFlavor) and not(@code and @nullFlavor))">Data types of CD or CE SHALL have either @code or @nullFlavor but SHALL NOT have both @code and @nullFlavor (CONF:CMS_0107).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_BL-errors">
    <sch:rule id="r-validate_BL-errors" context="//cda:value[@xsi:type='BL']|cda:contextConductionInd|inversionInd|negationInd|independentInd|seperatableInd|preferenceInd">
      <sch:assert id="a-CMS_0105-error" test="(@value or @nullFlavor) and not(@value and @nullFlavor)">Data types of BL SHALL have either @value or @nullFlavor but SHALL NOT have both @value and @nullFlavor (CONF: CMS_0105)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_CS-errors">
    <sch:rule id="r-validate_CS-errors" context="//cda:value[@xsi:type='CS']|cda:regionOfInterest/cda:code|cda:languageCode|cda:realmCode">
      <sch:assert id="a-CMS_0106-error" test="(@code or @nullFlavor) and not (@code and @nullFlavor)">Data types of CS SHALL have either @code or @nullFlavor but SHALL NOT have both @code and @nullFlavor (CONF: CMS_0106)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_II-errors">
    <sch:rule id="r-validate_II-errors" context="//cda:value[@xsi:type='II']|cda:id|cda:setId|cda:templateId">
      <sch:assert id="a-CMS_0108-error" test="(@root or @nullFlavor or (@root and @nullFlavor) or (@root and @extension)) and not (@root and @extension and @nullFlavor)">Data types of II SHALL have either @root or @nullFlavor or (@root and @nullFlavor) or (@root and @extension) but SHALL NOT have all three of (@root and @extension and @nullFlavor) (CONF: CMS_0108)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_PQ-errors">
    <!-- Add doseQuantity to elements to which to apply this rule. First appearance in CMS QRDA I 2021 schematron -->
    <!-- 02-28-2020 Removed doseQuantity since it makes little sense to require unit for dose quantities. -->
    <sch:rule id="r-validate_PQ-errors" context="//cda:value[@xsi:type='PQ']|cda:quantity">
      <sch:assert id="a-CMS_0110-error" test="((@value and @unit) or @nullFlavor) and not (@value and @nullFlavor) and not(@unit and @nullFlavor) and not(not(@value) and @unit)">Data types of PQ SHALL have either @value or @nullFlavor but SHALL NOT have both @value and @nullFlavor. If @value is present then @unit SHALL be present but @unit SHALL NOT be present if @value is not present. (CONF: CMS_0110)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_ST-errors">
    <sch:rule id="r-validate_ST-errors" context="//cda:value[@xsi:type='ST']|cda:title|cda:lotNumberText|cda:derivationExpr">
      <sch:assert id="a-CMS_0112-error" test="string-length()&gt;=1 or @nullFlavor">Data types of ST SHALL either not be empty or have @nullFlavor. (CONF: CMS_0112)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_REAL-errors">
    <sch:rule id="r-validate_REAL-errors" context="//cda:value[@xsi:type='REAL']">
      <sch:assert id="a-CMS_0111-error" test="(@value or @nullFlavor) and not (@value and @nullFlavor)">Data types of REAL SHALL NOT have both @value and @nullFlavor. (CONF: CMS_0111)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_INT-errors">
    <sch:rule id="r-validate_INT-errors" context="//cda:value[@xsi:type='INT']|cda:sequenceNumber|cda:versionNumber">
      <sch:assert id="a-CMS_0109-error" test="(@value or @nullFlavor) and not (@value and @nullFlavor)">Data types of INT SHALL NOT have both @value and @nullFlavor. (CONF: CMS_0109)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_NPI_format-errors">
    <sch:rule id="r-validate_NPI_format-errors" context="//cda:id[@root='2.16.840.1.113883.4.6']">
      <sch:let name="s" value="normalize-space(@extension)" />
      <sch:let name="n" value="string-length($s)" />
      <sch:let name="sum" value="24 + (number(substring($s, $n - 1, 1))*2) mod 10 + floor(number(substring($s, $n - 1,1))*2 div 10) + number(substring($s, $n - 2, 1)) +(number(substring($s, $n - 3, 1))*2) mod 10 + floor(number(substring($s, $n - 3,1))*2 div 10) + number(substring($s, $n - 4, 1)) + (number(substring($s, $n - 5, 1))*2) mod 10 + floor(number(substring($s, $n - 5,1))*2 div 10) + number(substring($s, $n - 6, 1)) + (number(substring($s, $n - 7, 1))*2) mod 10 + floor(number(substring($s, $n - 7,1))*2 div 10) + number(substring($s, $n - 8, 1)) + (number(substring($s, $n - 9, 1))*2) mod 10 + floor(number(substring($s, $n - 9,1))*2 div 10)" />
      <sch:assert id="a-CMS_0115-error" test="not(@extension) or $n = 10">The NPI should have 10 digits. (CONF: CMS_0115)</sch:assert>
      <sch:assert id="a-CMS_0116-error" test="not(@extension) or number($s)=$s">The NPI should be composed of all digits. (CONF: CMS_0116)</sch:assert>
      <sch:assert id="a-CMS_0117-error" test="not(@extension) or number(substring($s, $n, 1)) = (10 - ($sum mod 10)) mod 10">The NPI should have a correct checksum, using the Luhn algorithm. (CONF: CMS_0117)</sch:assert>
      <sch:assert id="a-CMS_0118-error" test="count(@extension|@nullFlavor)=1">The NPI should have @extension or @nullFlavor, but not both. (CONF: CMS_0118)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_TIN_format-errors">
    <sch:rule id="r-validate_TIN_format-errors-abstract" context="//cda:id[@root='2.16.840.1.113883.4.2']">
      <sch:assert id="a-CMS_0119-error" test="not(@extension) or ((number(@extension)=@extension) and string-length(@extension)=9)">When a Tax Identification Number is used, the provided TIN must be in valid format (9 decimal digits).  (CONF: CMS_0119)</sch:assert>
      <sch:assert id="a-CMS_0120-error" test="count(@extension|@nullFlavor)=1">The TIN SHALL have either @extension or @nullFlavor, but not both. (CONF: CMS_0120)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_TS-errors">
    <sch:rule id="r-validate_TS-errors-abstract" context="//cda:birthTime | //cda:time | //cda:effectiveTime | //cda:time/cda:low | //cda:time/cda:high | //cda:effectiveTime/cda:low | //cda:effectiveTime/cda:high">
      <!-- Update 03-02-2020 Corrected test to correctly enforce the CMS_0113 conformance rule.  (Changed test to ignore parent element when parent element contains a low or high, 
                 since the test needs to be done only the low and high.  Also changed test  "< 2" to "= 1" to correctly test the "one or the other but not both" predicate.).   https://tracker.esacinc.com/browse/QRDA-781 -->
      <!-- Update 04-29-2020 Corrected test to exclude elements that have PIVL_TS or EIVL_TS interval timestamps. These two datatypes do not have the constraints as normal TS elements do.  https://tracker.esacinc.com/browse/QRDA-832-->
      <sch:assert id="a-CMS_0113-error" test="(@xsi:type='PIVL_TS' or @xsi:type='EIVL_TS') or ( count(cda:low | cda:high) &gt; 0 or count(@value | @nullFlavor)=1 )">Data types of TS SHALL have either @value or @nullFlavor but SHALL NOT have @value and @nullFlavor. (CONF: CMS_0113)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-validate_TZ-errors">
    <sch:let name="timeZoneExists" value="string-length(normalize-space(/cda:ClinicalDocument/cda:effectiveTime/@value)) &gt; 8 and (contains(normalize-space(/cda:ClinicalDocument/cda:effectiveTime/@value), '-') or contains(normalize-space(/cda:ClinicalDocument/cda:effectiveTime/@value), '+'))" />
    <sch:rule id="r-validate_TZ-errors" context="//cda:time[@value] | //cda:effectiveTime[@value] | //cda:time/cda:low[@value] | //cda:time/cda:high[@value] | //cda:effectiveTime/cda:low[@value] | //cda:effectiveTime/cda:high[@value]">
      <sch:assert id="a-CMS_0121-error" test="string-length(normalize-space(@value)) &lt;= 8 or (parent::node()[parent::node()[parent::node()[cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8.1'][@extension='2016-03-01']]]]]) or ($timeZoneExists=(contains(normalize-space(@value), '-') or contains(normalize-space(@value), '+'))) or @nullFlavor">A Coordinated Universal Time (UTC time) offset should not be used anywhere in a QRDA Category I file or, if a UTC time offset is needed anywhere, then it must be specified everywhere a time field is provided (CONF: CMS_0121).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="p-CMS-QRDA-I-templateId-errors">
    <sch:rule id="r-CMS-QRDA-I-templateId-errors" context="cda:ClinicalDocument">
      <!-- Fixed typo in assertion test, incorrect template root.  https://oncprojectracking.healthit.gov/support/browse/QRDA-795 -->
      <sch:assert id="a-CMS_US-Header-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'])=1">This document SHALL contain exactly one US Header templateId (@root='2.16.840.1.113883.10.20.22.1.1') with appropriate @extension (version) of the form 'yyyy-mm-dd'.</sch:assert>
      <sch:assert id="a-CMS_QRDA-Category-I-Framework-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.1.1'])=1">This document SHALL contain exactly one QRDA Category I framework templateId (@root='2.16.840.1.113883.10.20.24.1.1') with appropriate @extension (version) of the form 'yyyy-mm-dd'.</sch:assert>
      <sch:assert id="a-CMS_QDM-based-QRDA-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'])=1">This document SHALL contain exactly one QDM-based QRDA templateId (@root='2.16.840.1.113883.10.20.24.1.2') with appropriate @extension (version) of the form 'yyyy-mm-dd'.</sch:assert>
      <sch:assert id="a-CMS_QRDA-Category-I-Report-CMS-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'])=1">This document SHALL contain exactly one QRDA Category I Report - CMS templateId (@root='2.16.840.1.113883.10.20.24.1.3') with appropriate @extension (version) of the form 'yyyy-mm-dd'.</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="CMS_QRDA_Category_I_Patient_Data_Section_QDM_template-pattern-errors">
    <sch:rule id="Patient_data_section_QDM-template-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1'][@extension='2019-12-01']]">
      <sch:assert id="a-CMS_0036-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1.1'][@extension='2020-02-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:CMS_0036) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.2.1.1" (CONF:CMS_0037). SHALL contain exactly one [1..1] @extension="2020-02-01" (CONF:CMS_0038).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="CMS_QRDA_Category_I_Patient_Data_Section_QDM_CMS_pattern-errors">
    <sch:rule id="CMS_QRDA_Category_I_Patient_Data_Section_QDM_CMS-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1.1'][@extension='2020-02-01']]">
      <sch:assert id="a-CMS_0051-error" test="count(cda:entry[*[cda:templateId[@root != '2.16.840.1.113883.10.20.24.3.55']]]) &gt;= 1">SHALL contain at least one [1..*] entry (CONF:CMS_0051) such that it SHALL contain exactly one [1..1] entry template that is other than the Patient Characteristic Payer (identifier: urn:oid:2.16.840.1.113883.10.20.24.3.55) (CONF:CMS_0039).</sch:assert>
      <sch:assert id="a-4444-14430_C01-error" test="count(cda:entry[count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.55']])=1]) &gt; 0">SHALL contain at least one [1..*] entry (CONF:4444-14430_C01) such that it SHALL contain exactly one [1..1] Patient Characteristic Payer (identifier: urn:oid:2.16.840.1.113883.10.20.24.3.55) (CONF:4444-14431).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I_Report_CMS-pattern-extension-check">
    <sch:rule id="QRDA_Category_I_Report_CMS-extension-check" context="cda:ClinicalDocument/cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3']">
      <sch:assert id="a-CMS_0001-extension-error" test="@extension='2020-02-01'">SHALL contain exactly one [1..1] templateId (CONF:CMS_0001) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.1.3" (CONF:CMS_0002). SHALL contain exactly one [1..1] @extension="2020-02-01" (CONF:CMS_0003).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I_Report_CMS-pattern-errors">
    <sch:rule id="QRDA_Category_I_Report_CMS-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]">
      <sch:assert id="a-CMS_0001-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:CMS_0001) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.24.1.3" (CONF:CMS_0002). SHALL contain exactly one [1..1] @extension="2020-02-01" (CONF:CMS_0003).</sch:assert>
      <sch:assert id="a-4444-16703_C01-error" test="count(cda:informationRecipient)=1">SHALL contain exactly one [1..1] informationRecipient (CONF:4444-16703_C01).</sch:assert>
      <sch:assert id="a-1198-10003_C01-error" test="count(cda:participant)=1">SHALL contain exactly one [1..1] participant (CONF:1198-10003_C01).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-languageCode-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:languageCode">
      <sch:assert id="a-CMS_0010-error" test="@code='en'">This languageCode SHALL contain exactly one [1..1] @code="en" (CONF:CMS_0010).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-recordTarget-patientRole-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:recordTarget/cda:patientRole">
      <sch:assert id="a-CMS_0009-error" test="count(cda:id[@root!='2.16.840.1.113883.4.572'][@root!='2.16.840.1.113883.4.927'][@extension] )=1">This patientRole SHALL contain exactly one [1..1] id (CONF:CMS_0009) such that it SHALL contain exactly one [1..1] @root (CONF:CMS_0053). SHALL contain exactly one [1..1] @extension (CONF:CMS_0103).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-recordTarget-patientRole-patient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:recordTarget/cda:patientRole/cda:patient">
      <sch:assert id="a-1198-5284_C01-error" test="count(cda:name)=1">This patient SHALL contain exactly one [1..1] US Realm Person Name (PN.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.1.1) (CONF:1198-5284_C01).</sch:assert>
      <sch:assert id="a-CMS_0011-error" test="count(cda:administrativeGenderCode)=1">This patient SHALL contain exactly one [1..1] administrativeGenderCode, which SHALL be selected from ValueSet ONC Administrative Sex urn:oid:2.16.840.1.113762.1.4.1 DYNAMIC (CONF:CMS_0011).</sch:assert>
      <sch:assert id="a-CMS_0013-error" test="count(cda:raceCode)=1">This patient SHALL contain exactly one [1..1] raceCode, which SHALL be selected from ValueSet Race urn:oid:2.16.840.1.114222.4.11.836 DYNAMIC (CONF:CMS_0013).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-recordTarget-patientRole-patient-birthTime-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:birthTime">
      <sch:assert id="a-1198-5300_C01-error" test="string-length(@value)&gt;=8">SHALL be precise to day (CONF:1198-5300_C01).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-custodian-assignedCustodian-representedCustodianOrganization-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization">
      <sch:assert id="a-4444-28241_C01-error" test="count(cda:id[@root='2.16.840.1.113883.4.336'][@extension])=1">This representedCustodianOrganization SHALL contain exactly one [1..1] id (CONF:4444-28241_C01) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.336" CMS Certification Number (CONF:4444-28244). SHALL contain exactly one [1..1] @extension (CONF:4444-28245).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-custodian-representedCustodianOrganization-id-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization/cda:id[@root='2.16.840.1.113883.4.336'][@extension]">
      <sch:assert id="a-CMS_0035-error" test="string-length(normalize-space(@extension)) &gt;= 6 and string-length(normalize-space(@extension)) &lt;= 10">CCN SHALL be six to ten characters in length (CONF:CMS_0035).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-informationRecipient-intendedRecipient-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:informationRecipient/cda:intendedRecipient">
      <sch:assert id="a-4444-16705_C01-error" test="count(cda:id)=1">This intendedRecipient SHALL contain exactly one [1..1] id (CONF:4444-16705_C01).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-informationRecipient-intendedRecipient-id-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:informationRecipient/cda:intendedRecipient/cda:id">
      <sch:assert id="a-CMS_0025-error" test="@root='2.16.840.1.113883.3.249.7'">This id SHALL contain exactly one [1..1] @root="2.16.840.1.113883.3.249.7" (CONF:CMS_0025).</sch:assert>
      <!-- STATIC version in CMS_0026 changed to 2020-02-01  for 2021 -->
      <sch:assert id="a-CMS_0026-error" test="@extension=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.3.249.14.103']/voc:code/@value">This id SHALL contain exactly one [1..1] @extension, which SHALL be selected from ValueSet QRDA I CMS Program Name urn:oid:2.16.840.1.113883.3.249.14.103 STATIC 2020-02-01 (CONF:CMS_0026).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-participant-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:participant">
      <sch:assert id="a-CMS_0004-error" test="count(cda:associatedEntity)=1">This participant SHALL contain exactly one [1..1] associatedEntity (CONF:CMS_0004).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-participant-associatedEntity-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:participant/cda:associatedEntity">
      <sch:assert id="a-CMS_0005-error" test="count(cda:id)=1">This associatedEntity SHALL contain exactly one [1..1] id (CONF:CMS_0005).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-participant-associatedEntity-id-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:participant/cda:associatedEntity/cda:id">
      <sch:assert id="a-CMS_0006-error" test="@root='2.16.840.1.113883.3.2074.1'">This id SHALL contain exactly one [1..1] @root="2.16.840.1.113883.3.2074.1" CMS EHR Certification ID (CONF:CMS_0006).</sch:assert>
      <sch:assert id="a-CMS_0008-error" test="@extension">This id SHALL contain exactly one [1..1] @extension (CONF:CMS_0008).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-component-structuredBody-errors" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:component/cda:structuredBody">
      <sch:assert id="a-CMS_0056-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1.1'][@extension='2016-03-01']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:CMS_0056) such that it SHALL contain exactly one [1..1] Reporting Parameters Section - CMS (identifier: urn:hl7ii:2.16.840.1.113883.10.20.17.2.1.1:2016-03-01) (CONF:CMS_0054).</sch:assert>
      <!-- 04-24-2020 Corrected version of Patient Data Section QDM referenced in the conformance text to V7 -->
      <sch:assert id="a-CMS_0057-error" test="count(cda:component[count(cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.24.2.1.1'][@extension='2020-02-01']])=1])=1">This structuredBody SHALL contain exactly one [1..1] component (CONF:CMS_0057) such that it SHALL contain exactly one [1..1] Patient Data Section QDM (V7) - CMS (identifier: urn:hl7ii:2.16.840.1.113883.10.20.24.2.1.1:2020-02-01) (CONF:CMS_0055).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reporting-Parameters-Act-template-pattern-errors">
    <sch:rule id="Reporting-Parameters-Act-template-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8']]">
      <sch:assert id="a-CMS_0044-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8.1'][@extension='2016-03-01'])=1">SHALL contain exactly one [1..1] templateId (CONF:CMS_0044) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.17.3.8.1" (CONF:CMS_0045) SHALL contain exactly one [1..1] @extension="2016-03-01" (CONF:CMS_0046)</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reporting-Parameters-Act-CMS-pattern-errors">
    <!-- Empty rule removed 04-29-2019 -->
    <!--
         <sch:rule id="Reporting-Parameters-Act-CMS-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8.1'][@extension='2016-03-01']]">
         </sch:rule>
        -->
    <sch:rule id="Reporting-Parameters-Act-CMS-effectiveTime-low-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8.1'][@extension='2016-03-01']]/cda:effectiveTime/cda:low">
      <sch:assert id="a-CMS_0048-error" test="@value">This low SHALL contain exactly one [1..1] @value (CONF:CMS_0048).</sch:assert>
      <sch:assert id="a-CMS_0027-error" test="string-length(@value)&gt;=8">SHALL be precise to day (CONF:CMS_0027).</sch:assert>
    </sch:rule>
    <sch:rule id="Reporting-Parameters-Act-CMS-effectiveTime-high-errors" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8.1'][@extension='2016-03-01']]/cda:effectiveTime/cda:high">
      <sch:assert id="a-CMS_0050-error" test="@value">This high SHALL contain exactly one [1..1] @value (CONF:CMS_0050).</sch:assert>
      <sch:assert id="a-CMS_0028-error" test="string-length(@value)&gt;=8">SHALL be precise to day (CONF:CMS_0028).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I_Reporting_Parameters_Section-template-pattern-errors">
    <sch:rule id="Reporting-parameters-section-template-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1']]">
      <sch:assert id="a-CMS_0040-error" test="count(cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1.1'][@extension='2016-03-01']) = 1">SHALL contain exactly one [1..1] templateId (CONF:CMS_0040) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.10.20.17.2.1.1" (CONF:CMS_0041). SHALL contain exactly one [1..1] @extension="2016-03-01" (CONF:CMS_0042).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I_Reporting_Parameters_Section_CMS-pattern-errors">
    <sch:rule id="QRDA_Category_I_Reporting_Parameters_Section_CMS-errors" context="cda:section[cda:templateId[@root='2.16.840.1.113883.10.20.17.2.1.1'][@extension='2016-03-01']]">
      <sch:assert id="a-CMS_0023-error" test="count(cda:entry[count(cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.17.3.8.1'][@extension='2016-03-01']])=1]) = 1">SHALL contain exactly one [1..1] entry (CONF:CMS_0023) such that it SHALL contain exactly one [1..1] Reporting Parameters Act - CMS (identifier: urn:hl7ii:2.16.840.1.113883.10.20.17.3.8.1:2016-03-01) (CONF:CMS_0024).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <!--
      WARNING Patterns and Assertions
  -->
  <sch:pattern id="Admission_Source-pattern-warnings">
    <sch:rule id="Admission_Source-warnings" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.151'][@extension='2017-08-01']]">
      <sch:assert id="a-3343-29095-warning" test="count(cda:addr) &gt; 0">SHOULD contain zero or more [0..*] addr (CONF:3343-29095).</sch:assert>
      <sch:assert id="a-3343-29096-warning" test="count(cda:telecom) &gt; 0">SHOULD contain zero or more [0..*] telecom (CONF:3343-29096).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Author-Participation-pattern-warnings">
    <sch:rule id="Author-Participation-assignedAuthor-warnings" context="cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]/cda:assignedAuthor">
      <sch:assert id="a-1098-31671-warning" test="count(cda:code)=1">This assignedAuthor SHOULD contain zero or one [0..1] code, which SHOULD be selected from ValueSet Healthcare Provider Taxonomy (HIPAA) urn:oid:2.16.840.1.114222.4.11.1066 DYNAMIC (CONF:1098-31671). .</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Deceased-Observation-pattern-warnings">
    <sch:rule id="Deceased-Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.79']]">
      <sch:assert id="a-1198-14868-warning" test="count(cda:entryRelationship[@typeCode='CAUS'][@inversionInd='true'] [count(cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']])=1])&lt;=1">SHOULD contain zero or one [0..1] entryRelationship (CONF:1198-14868) such that it SHALL contain exactly one [1..1] @typeCode="CAUS" Is etiology for (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1198-14875). SHALL contain exactly one [1..1] @inversionInd="true" True (CONF:1198-32900). SHALL contain exactly one [1..1] Problem Observation (V3) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.4:2015-08-01) (CONF:1198-14870).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Device-Applied-pattern-warnings">
    <sch:rule id="Device-Applied-effectiveTime-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.7'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-27537-warning" test="count(cda:low)=1">This effectiveTime SHOULD contain zero or one [0..1] low (CONF:4444-27537).</sch:assert>
      <sch:assert id="a-4444-29617-warning" test="count(@value)=1">This effectiveTime SHOULD contain zero or one [0..1] @value (CONF:4444-29617).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Diagnostic-Study-Performed-pattern-warnings">
    <sch:rule id="Diagnostic-Study-Performed-effectiveTime-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.18'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-12959-warning" test="count(cda:low)=1">This effectiveTime SHOULD contain zero or one [0..1] low (CONF:4444-12959).</sch:assert>
      <sch:assert id="a-4444-30024-warning" test="count(@value)=1">This effectiveTime SHOULD contain zero or one [0..1] @value (CONF:4444-30024).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="eMeasure-Reference-QDM-pattern-warnings">
    <sch:rule id="eMeasure-Reference-QDM-reference-externalDocument-warnings" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:reference/cda:externalDocument">
      <sch:assert id="a-67-12864-warning" test="count(cda:code)=1">This externalDocument SHOULD contain zero or one [0..1] code (CONF:67-12864).</sch:assert>
      <sch:assert id="a-67-12865-warning" test="count(cda:text)=1">This externalDocument SHOULD contain zero or one [0..1] text (CONF:67-12865).</sch:assert>
      <sch:assert id="a-67-12867-warning" test="count(cda:setId)=1">This externalDocument SHOULD contain zero or one [0..1] setId (CONF:67-12867).</sch:assert>
      <sch:assert id="a-67-12869-warning" test="count(cda:versionNumber)=1">This externalDocument SHOULD contain zero or one [0..1] versionNumber (CONF:67-12869).</sch:assert>
    </sch:rule>
    <sch:rule id="eMeasure-Reference-QDM-reference-externalDocument-code-warnings" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.97']]/cda:reference/cda:externalDocument/cda:code">
      <sch:assert id="a-67-27015-warning" test="@code='57024-2'">The code, if present, SHOULD contain zero or one [0..1] @code="57024-2" Health Quality Measure Document (CONF:67-27015).</sch:assert>
      <sch:assert id="a-67-27016-warning" test="@codeSystem='2.16.840.1.113883.6.1'">The code, if present, SHOULD contain zero or one [0..1] @codeSystem="2.16.840.1.113883.6.1" (CodeSystem: LOINC urn:oid:2.16.840.1.113883.6.1) (CONF:67-27016).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Encounter-Activity-pattern-warnings">
    <sch:rule id="Encounter-Activity-warnings" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8738-warning" test="count(cda:participant[@typeCode='LOC'][count(cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.32']])=1])&gt;=1">SHOULD contain zero or more [0..*] participant (CONF:1198-8738) such that it SHALL contain exactly one [1..1] @typeCode="LOC" Location (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1198-8740).  SHALL contain exactly one [1..1] Service Delivery Location (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.32) (CONF:1198-14903).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Activity-code-warnings" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]/cda:code">
      <sch:assert id="a-1198-8719-warning" test="count(cda:originalText)=1">This code SHOULD contain zero or one [0..1] originalText (CONF:1198-8719).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Activity-code-originalText-warnings" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]/cda:code/cda:originalText">
      <sch:assert id="a-1198-15970-warning" test="count(cda:reference)=1">The originalText, if present, SHOULD contain zero or one [0..1] reference (CONF:1198-15970).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Activity-code-originalText-reference-warnings" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]/cda:code/cda:originalText/cda:reference">
      <sch:assert id="a-1198-15971-warning" test="@value">The reference, if present, SHOULD contain zero or one [0..1] @value (CONF:1198-15971).</sch:assert>
    </sch:rule>
    <sch:rule id="Encounter-Activity-dischargeDispositionCode-warnings" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.49'][@extension='2015-08-01']]/sdtc:dischargeDispositionCode">
      <sch:assert id="a-1198-32177-warning" test="count(@code)=1">This sdtc:dischargeDispositionCode SHOULD contain exactly [1..1] @code(CONF:1198-32177).</sch:assert>
      <sch:assert id="a-1198-32377-warning" test="count(@codeSystem)=1">This sdtc:dischargeDispositionCode SHOULD contain exactly [1..1] @codeSystem (CONF:1198-32377).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="External-Document-Reference-pattern-warnings">
    <sch:rule id="External-Document-Reference-warnings" context="cda:externalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.115'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-32752-warning" test="count(cda:setId)=1">SHOULD contain zero or one [0..1] setId (CONF:1098-32752).</sch:assert>
      <sch:assert id="a-1098-32753-warning" test="count(cda:versionNumber)=1">SHOULD contain zero or one [0..1] versionNumber (CONF:1098-32753).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Facility-Location-pattern-warnings">
    <sch:rule id="Facility-Location-participantRole-warnings" context="cda:participant[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.100'][@extension='2017-08-01']]/cda:participantRole">
      <sch:assert id="a-3343-13379-warning" test="count(cda:addr) &gt; 0">This participantRole SHOULD contain zero or more [0..*] addr (CONF:3343-13379).</sch:assert>
      <sch:assert id="a-3343-13380-warning" test="count(cda:telecom) &gt; 0">This participantRole SHOULD contain zero or more [0..*] telecom (CONF:3343-13380).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Observation-pattern-warnings">
    <sch:rule id="Family_History_Observation-code-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]/cda:code">
      <!-- 08-14-2019 Changed conformance text for 1198-32427 from STATIC to DYNAMIC -->
      <sch:assert id="a-1198-32427-warning" test="@code">SHALL contain exactly one [1..1] code, which SHOULD be selected from ValueSet Problem Type urn:oid:2.16.840.1.113883.3.88.12.3221.7.2 DYNAMIC (CONF:1198-32427).</sch:assert>
    </sch:rule>
    <!-- 04-25-2019 Make translation valueSet warning separate from requirement 1198-32847,  https://tracker.esacinc.com/browse/QRDA-573 -->
    <!-- 08-14-2019 Changed conformance text for 1198-32427 from STATIC to DYNAMIC -->
    <!-- 08-16-2019 Conformance 1198-32847 should be ignored due to the new conformance text...we do not test for this condition. -->
    <!--
		<sch:rule id="Family_History_Observation-code-translation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]/cda:code/cda:translation">
			<sch:assert id="a-1198-32847-warning" test="@sdtc:valueSet='2.16.840.1.113883.3.88.12.3221.7.2'">This translation, if present, SHOULD be selected from ValueSet Problem Type urn:oid:2.16.840.1.113883.3.88.12.3221.7.2 DYNAMIC (CONF:1198-32847).</sch:assert>
		</sch:rule>
		-->
    <sch:rule id="Family_History_Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.46'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8593-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1198-8593).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Family_History_Organizer-pattern-warnings">
    <sch:rule id="Family_History_Organizer_QDM-subject-warnings" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:subject/cda:relatedSubject">
      <sch:assert id="a-1198-15248-warning" test="count(cda:subject) = 1">This relatedSubject SHOULD contain zero or one [0..1] subject (CONF:1198-15248).</sch:assert>
    </sch:rule>
    <sch:rule id="Family_History_Organizer_QDM-subject-subject-warnings" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.45'][@extension='2015-08-01']]/cda:subject/cda:relatedSubject/cda:subject">
      <sch:assert id="a-1198-15976-warning" test="count(cda:birthTime) = 1">The subject, if present, SHOULD contain zero or one [0..1] birthTime (CONF:1198-15976).</sch:assert>
      <sch:assert id="a-1198-15249-warning" test="count(sdtc:id) &gt; 0">The subject SHOULD contain zero or more [0..*] sdtc:id. The prefix sdtc: SHALL be bound to the namespace “urn:hl7-org:sdtc”. The use of the namespace provides a necessary extension to CDA R2 for the use of the id element (CONF:1198-15249).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Goal_Observation-pattern-warnings">
    <sch:rule id="Goal_Observation-codesystem-code-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.121']]/cda:code">
      <sch:assert id="a-1098-30784-c-warning" test="@codeSystem='2.16.840.1.113883.6.1'">SHALL contain exactly one [1..1] code, which SHOULD be selected from CodeSystem LOINC (urn:oid:2.16.840.1.113883.6.1) (CONF:1098-30784).</sch:assert>
    </sch:rule>
    <sch:rule id="Goal_Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.121']]">
      <sch:assert id="a-1098-32335-warning" test="count(cda:effectiveTime) = 1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-32335).</sch:assert>
      <sch:assert id="a-1098-30995-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) &gt;= 1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-30995).</sch:assert>
      <sch:assert id="a-1098-30785-warning" test="count(cda:entryRelationship[@typeCode='REFR'][cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.143']]]) = 1">SHOULD contain zero or one [0..1] entryRelationship (CONF:1098-30785) such that it SHALL contain exactly one [1..1] @typeCode="REFR" Refers to (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:1098-30786). SHALL contain exactly one [1..1] Priority Preference (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.143) (CONF:1098-30787).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_activity-pattern-warnings">
    <sch:rule id="Immunization_activity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-8841-warning" test="count(cda:doseQuantity)=1">SHOULD contain zero or one [0..1] doseQuantity (CONF:1198-8841).</sch:assert>
      <sch:assert id="a-1198-8849-warning" test="count(cda:performer)=1">SHOULD contain zero or one [0..1] performer (CONF:1198-8849).</sch:assert>
      <sch:assert id="a-1198-31151-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) &gt; 0">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1198-31151).</sch:assert>
      <sch:assert id="a-1198-31510-warning" test="count(cda:entryRelationship[@typeCode='COMP'][@inversionInd='true'][count(cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.118']])=1]) &gt; 0">SHOULD contain zero or more [0..*] entryRelationship (CONF:1198-31510) such that it SHALL contain exactly one [1..1] @typeCode="COMP" Component (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:1198-31511). SHALL contain exactly one [1..1] @inversionInd="true" (CONF:1198-31512). SHALL contain exactly one [1..1] Substance Administered Act (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.118) (CONF:1198-31514).</sch:assert>
    </sch:rule>
    <sch:rule id="Immunization_activity-doseQuantity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01']]/cda:doseQuantity">
      <sch:assert id="a-1198-8842-warning" test="@unit">The doseQuantity, if present, SHOULD contain zero or one [0..1] @unit, which SHALL be selected from ValueSet UnitsOfMeasureCaseSensitive urn:oid:2.16.840.1.113883.1.11.12839 DYNAMIC (CONF:1198-8842).</sch:assert>
    </sch:rule>
    <!-- 08-15-2019 Added 1198-32960 warning -->
    <sch:rule id="Immunization_activity-routeCode-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.52'][@extension='2015-08-01']]/cda:routeCode">
      <sch:assert id="a-1198-32960-warning" test="count(cda:translation) &gt; 0">The routeCode, if present, SHOULD contain zero or more [0..*] translation, which SHALL be selected from ValueSet Medication Route urn:oid:2.16.840.1.113762.1.4.1099.12 DYNAMIC (CONF:1198-32960).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Immunization_medication_information-pattern-warnings">
    <sch:rule id="Immunization_medication_information-warnings" context="cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-9012-warning" test="count(cda:manufacturerOrganization)=1">SHOULD contain zero or one [0..1] manufacturerOrganization (CONF:1098-9012).</sch:assert>
    </sch:rule>
    <!-- Updated 04-14-2020 Changed 1098-9014 (lot number) from SHALL to SHOULD, per STU 5.1 IG and https://oncprojectracking.healthit.gov/support/browse/QRDA-887  -->
    <sch:rule id="Immunization_medication_information-manufacturedMaterial-warnings" context="cda:manufacturedProduct[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.54'][@extension='2014-06-09']]/cda:manufacturedMaterial">
      <sch:assert id="a-1098-9014-warning" test="count(cda:lotNumberText)=1">This manufacturedMaterial SHOULD contain zero or one [0..1] lotNumberText (CONF:1098-9014).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Indication-pattern-warnings">
    <sch:rule id="Indication-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.19'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7488-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-7488).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Intervention_Performed-pattern-warnings">
    <sch:rule id="Intervention_Performed-effectiveTime-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.32'][@extension='2019-12-01']]/cda:effectiveTime">
      <!-- 04-24-2020 Fixed the assertion ids for 4444-29742 and 4444-13612 to be "warning" instead of "error" -->
      <sch:assert id="a-4444-29742-warning" test="count(@value)=1">This effectiveTime SHOULD contain zero or one [0..1] @value (CONF:4444-29742).</sch:assert>
      <sch:assert id="a-4444-13612-warning" test="count(cda:low)=1">This effectiveTime SHOULD contain zero or one [0..1] low (CONF:4444-13612).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Measure_Reference-pattern-warnings">
    <sch:rule id="Measure_Reference-externalDocument-warnings" context="cda:organizer[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.98']]/cda:reference/cda:externalDocument">
      <sch:assert id="a-67-12997-warning" test="count(cda:text) &gt; 0">This externalDocument SHOULD contain zero or one [0..1] text (CONF:67-12997).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Activity-pattern-warnings">
    <sch:rule id="Medication_Activity-effectiveTime-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:effectiveTime">
      <sch:assert id="a-1098-7513-warning" test="parent::node()[count(cda:effectiveTime[@operator='A'][@xsi:type='PIVL_TS' or 'EIVL_TS'])=1]">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-7513) such that it SHALL contain exactly one [1..1] @operator="A" (CONF:1098-9106). SHALL contain exactly one [1..1] @xsi:type="PIVL_TS" or "EIVL_TS" (CONF:1098-28499).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Activity-doseQuantity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:doseQuantity">
      <sch:assert id="a-1098-7526-warning" test="@unit">This doseQuantity SHOULD contain zero or one [0..1] @unit, which SHALL be selected from ValueSet UnitsOfMeasureCaseSensitive urn:oid:2.16.840.1.113883.1.11.12839 DYNAMIC (CONF:1098-7526).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Activity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]">
      <!-- <sch:assert id="a-1098-7514-warning" test="count(cda:routeCode) = 1">SHOULD contain zero or one [0..1] routeCode (CONF:1098-7514).</sch:assert> -->
      <!-- New conformance test for a-1098-7514-warning per TJC: https://tracker.esacinc.com/browse/QRDA-429 -->
      <sch:assert id="a-1098-7514-warning" test="((not(parent::node()[parent::node()[parent::node()[cda:act[@negationInd]]]]) or parent::node()[parent::node()[parent::node()[cda:act[@negationInd='false']]]]) and count(cda:routeCode) = 1) or parent::node()[parent::node()[parent::node()[cda:act[@negationInd='true']]]]">SHOULD contain zero or one [0..1] routeCode (CONF:1098-7514).</sch:assert>
      <sch:assert id="a-1098-31150-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) &gt; 0">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-31150).</sch:assert>
      <sch:assert id="a-1098-30800-warning" test="(count(cda:doseQuantity) &gt; 0) or (count(cda:rateQuantity) &gt; 0)">Medication Activity SHOULD include doseQuantity OR rateQuantity (CONF:1098-30800).</sch:assert>
    </sch:rule>
    <!-- Warning rule forn CONF: 1098-32950 added 04/25/2019  https://tracker.esacinc.com/browse/QRDA-435 -->
    <sch:rule id="Medication_Activity-routeCode-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.16'][@extension='2014-06-09']]/cda:routeCode">
      <sch:assert id="a-1098-32950-warning" test="count(cda:translation) &gt;= 1">The routeCode, if present, SHOULD contain zero or more [0..*] translation, which SHALL be selected from ValueSet Medication Route urn:oid:2.16.840.1.113762.1.4.1099.12 DYNAMIC (CONF:1098-32950).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Dispense-pattern-warnings">
    <sch:rule id="Medication_Dispense-warnings" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7456-warning" test="count(cda:effectiveTime) = 1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-7456).</sch:assert>
      <sch:assert id="a-1098-7457-warning" test="count(cda:repeatNumber) = 1">SHOULD contain zero or one [0..1] repeatNumber (CONF:1098-7457).</sch:assert>
      <sch:assert id="a-1098-7458-warning" test="count(cda:quantity) = 1">SHOULD contain zero or one [0..1] quantity (CONF:1098-7458).</sch:assert>
    </sch:rule>
    <sch:rule id="Medication_Dispense-performer-assignedEntity-warnings" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-7468-warning" test="count(cda:addr) = 1">This assignedEntity SHOULD contain zero or one [0..1] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1098-7468).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Free_Text_Sig-pattern-warnings">
    <sch:rule id="Medication_Free_Text_Sig-reference-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.147']]/cda:text/cda:reference">
      <sch:assert id="a-81-32756-warning" test="count(@value) = 1">This reference SHOULD contain zero or one [0..1] @value (CONF:81-32756).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Medication_Supply_Order-pattern-warnings">
    <sch:rule id="Medication_Supply_Order-effectiveTime-warnings" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.17'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-15143-warning" test="count(cda:effectiveTime[count(cda:high)=1]) = 1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-15143) such that it SHALL contain exactly one [1..1] high (CONF:1098-15144).</sch:assert>
      <sch:assert id="a-1098-7434-warning" test="count(cda:repeatNumber) = 1">SHOULD contain zero or one [0..1] repeatNumber (CONF:1098-7434).</sch:assert>
      <sch:assert id="a-1098-7436-warning" test="count(cda:quantity) = 1">SHOULD contain zero or one [0..1] quantity (CONF:1098-7436).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Patient_Characteristic_Payer-pattern-warnings">
    <sch:rule id="Patient_Characteristic_Payer-effectiveTime-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.55']]/cda:effectiveTime">
      <sch:assert id="a-67-26935-warning" test="count(cda:high)=1">This effectiveTime SHOULD contain zero or one [0..1] high (CONF:67-26935).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Physical_Exam_Performed-pattern-warnings">
    <sch:rule id="Physical_Exam_Performed-effectiveTime-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.59'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-12652-warning" test="count(cda:low)=1">This effectiveTime SHOULD contain zero or one [0..1] low (CONF:4444-12652).</sch:assert>
      <sch:assert id="a-4444-29818-warning" test="count(@value)=1">This effectiveTime SHOULD contain zero or one [0..1] @value (CONF:4444-29818).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Act-pattern-warnings">
    <sch:rule id="Planned_Act-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.39'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-32020-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) = 1">SHOULD contain zero or one [0..1] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32020).</sch:assert>
      <sch:assert id="a-1098-30433-warning" test="count(cda:effectiveTime) = 1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-30433).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned_Act-code-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.39'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-32030-warning" test="@codeSystem = '2.16.840.1.113883.6.96' or @codeSystem = '2.16.840.1.113883.6.1'">This code in a Planned Act SHOULD be selected from LOINC (CodeSystem: 2.16.840.1.113883.6.1) OR SNOMED CT (CodeSystem: 2.16.840.1.113883.6.96) (CONF:1098-32030).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Encounter-pattern-warnings">
    <sch:rule id="Planned_Encounter-warnings" context="cda:encounter[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.40'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-30440-warning" test="count(cda:effectiveTime) = 1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-30440).</sch:assert>
      <sch:assert id="a-1098-32045-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) &gt; 0">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32045).</sch:assert>
      <sch:assert id="a-1098-31032-warning" test="count(cda:code) = 1">SHOULD contain zero or one [0..1] code, which SHOULD be selected from ValueSet Encounter Planned urn:oid:2.16.840.1.113883.11.20.9.52 DYNAMIC (CONF:1098-31032).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned_Immunization_Activity-pattern-warnings">
    <sch:rule id="Planned_Immunization_Activity-doseQuantity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.120']]/cda:doseQuantity">
      <sch:assert id="a-1098-32130-warning" test="@unit">The doseQuantity, if present, SHOULD contain zero or one [0..1] @unit, which SHALL be selected from ValueSet UnitsOfMeasureCaseSensitive urn:oid:2.16.840.1.113883.1.11.12839 DYNAMIC (CONF:1098-32130).</sch:assert>
    </sch:rule>
    <!-- 08-15-2019 Added 1098-32951 warning -->
    <sch:rule id="Planned_Immunization_Activity-routeCode-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.120']]/cda:routeCode">
      <sch:assert id="a-1098-32951-warning" test="count(cda:translation) &gt; 0">The routeCode, if present, SHOULD contain zero or more [0..*] translation, which SHALL be selected from ValueSet Medication Route urn:oid:2.16.840.1.113762.1.4.1099.12 DYNAMIC (CONF:1098-32951).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Medication-Activity-pattern-warnings">
    <!--  07-15-2019 Added warning for effective time (operator='A', etc.) warning https://tracker.esacinc.com/browse/QRDA-617  -->
    <sch:rule id="Planned-Medication-Activity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-32046-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) &gt;= 1">SHOULD contain zero or one [0..1] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32046).</sch:assert>
      <sch:assert id="a-1098-32943-warning" test="count(cda:effectiveTime[@operator='A'][@xsi:type='PIVL_TS' or @xsl:type='EIVL_TS'])=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-32943) such that it SHALL contain exactly one [1..1] @operator="A" (CONF:1098-32945) SHALL contain exactly one [1..1] @xsi:type="PIVL_TS" or "EIVL_TS" (CONF:1098-32946).</sch:assert>
    </sch:rule>
    <!--  07-15-2019 Added warnings for effective time such that assertion (1098-30468) https://tracker.esacinc.com/browse/QRDA-617  -->
    <sch:rule id="Planned-Medication-Activity-effectiveTime-low-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:effectiveTime[ (count(cda:low)=1)]">
      <sch:assert id="a-1098-32944-warning" test="count(@value)=0">effectiveTime SHOULD contain zero  @value (CONF:1098-32944).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Medication-Activity-effectiveTime-value-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:effectiveTime[@value]">
      <sch:assert id="a-1098-32948-warning" test="count(cda:low)=0">effectiveTime SHOULD contain zero low (CONF:1098-32948).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Medication-Activity-doseQuantity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:doseQuantity">
      <sch:assert id="a-1098-32133-warning" test="@unit">The doseQuantity, if present, SHOULD contain zero or one [0..1] @unit, which SHALL be selected from ValueSet UnitsOfMeasureCaseSensitive urn:oid:2.16.840.1.113883.1.11.12839 DYNAMIC (CONF:1098-32133).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Medication-Activity-rateQuantity-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:rateQuantity">
      <sch:assert id="a-1098-32134-warning" test="@unit">The rateQuantity, if present, SHOULD contain zero or one [0..1] @unit, which SHALL be selected from ValueSet UnitsOfMeasureCaseSensitive urn:oid:2.16.840.1.113883.1.11.12839 DYNAMIC (CONF:1098-32134).</sch:assert>
    </sch:rule>
    <!-- 08-15-2019 Added warning for 1098-32952 -->
    <sch:rule id="Planned-Medication-Activity-routeCode-warnings" context="cda:substanceAdministration[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.42'][@extension='2014-06-09']]/cda:routeCode">
      <sch:assert id="a-1098-32952-warning" test="count(cda:translation) &gt; 0">The routeCode, if present, SHOULD contain zero or more [0..*] translation, which SHALL be selected from ValueSet Medication Route urn:oid:2.16.840.1.113762.1.4.1099.12 DYNAMIC (CONF:1098-32952).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Observation-pattern-warnings">
    <sch:rule id="Planned-Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.44'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-30454-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-30454).</sch:assert>
      <sch:assert id="a-1098-32033-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']]) &gt;= 1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32033).</sch:assert>
      <sch:assert id="a-1098-32044-warning" test="count(cda:targetSiteCode) &gt;= 1">SHOULD contain zero or more [0..*] targetSiteCode, which SHALL be selected from ValueSet Body Site urn:oid:2.16.840.1.113883.3.88.12.3221.8.9 DYNAMIC (CONF:1098-32044).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Procedure-pattern-warnings">
    <sch:rule id="Planned-Procedure-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.41'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-30447-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-30447).</sch:assert>
      <sch:assert id="a-1098-31979-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or one [0..1] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-31979).</sch:assert>
    </sch:rule>
    <sch:rule id="Planned-Procedure-code-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.41'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-31977-warning" test="@codeSystem='2.16.840.1.113883.6.1' or @codeSystem='2.16.840.1.113883.6.96' or @codeSystem='2.16.840.1.113883.6.12' or @codeSystem='2.16.840.1.113883.6.4'">The procedure/code in a planned procedure SHOULD be selected from LOINC (codeSystem 2.16.840.1.113883.6.1) *OR* SNOMED CT (CodeSystem: 2.16.840.1.113883.6.96), and *MAY* be selected from CPT-4 (CodeSystem: 2.16.840.1.113883.6.12) *OR* ICD10 PCS (CodeSystem: 2.16.840.1.113883.6.4) (CONF:1098-31977).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Planned-Supply-pattern-warnings">
    <sch:rule id="Planned-Supply-warnings" context="cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.43'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-30459-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-30459).</sch:assert>
      <sch:assert id="a-1098-32325-warning" test="count(cda:product)=1">SHOULD contain zero or one [0..1] product (CONF:1098-32325).</sch:assert>
      <sch:assert id="a-1098-31129-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])=1">SHOULD contain zero or one [0..1] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-31129).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Priority-Preference-pattern-warnings">
    <sch:rule id="Priority-Preference-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.143']]">
      <sch:assert id="a-1098-32327-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-32327).</sch:assert>
      <sch:assert id="a-1098-30958-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-30958).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Concern-Act-pattern-warnings">
    <sch:rule id="Problem-Concern-Act-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.3'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-31146-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;= 1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1198-31146).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Problem-Observation-pattern-warnings">
    <!-- Corrected cda:observation, was incorrectly prefixed with cda:act -->
    <sch:rule id="Problem-Observation-participant-participantRole-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.4'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-31147-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1198-31147).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Act-pattern-warnings">
    <sch:rule id="Procedure-Activity-Act-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8301-warning" test="count(cda:performer)&gt;=1">SHOULD contain zero or more [0..*] performer (CONF:1098-8301).</sch:assert>
      <!-- 08-14-2019 Updated conformance text of 1098-32477 -->
      <!-- 04-22-2020 Corrected typo in conformance text of 1098-32477 -->
      <sch:assert id="a-1098-32477-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32477)</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-code-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-19186-warning" test="count(cda:originalText)=1">This code SHOULD contain zero or one [0..1] originalText (CONF:1098-19186).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-performer-assignedEntity-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-8306-warning" test="count(cda:representedOrganization)=1">This assignedEntity SHOULD contain zero or one [0..1] representedOrganization (CONF:1098-8306).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Act-performer-assignedEntity-representedOrganization-warnings" context="cda:act[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.12'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-1098-8307-warning" test="count(cda:id)&gt;=1">The representedOrganization, if present, SHOULD contain zero or more [0..*] id (CONF:1098-8307).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Observation-pattern-warnings">
    <sch:rule id="Procedure-Activity-Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-8246-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-8246).</sch:assert>
      <sch:assert id="a-1098-8250-warning" test="count(cda:targetSiteCode)&gt;=1">SHOULD contain zero or more [0..*] targetSiteCode, which SHALL be selected from ValueSet Body Site urn:oid:2.16.840.1.113883.3.88.12.3221.8.9 DYNAMIC (CONF:1098-8250).</sch:assert>
      <sch:assert id="a-1098-8251-warning" test="count(cda:performer)&gt;=1">SHOULD contain zero or more [0..*] performer (CONF:1098-8251).</sch:assert>
      <!-- 08-14-2019 Updated conformance text of 1098-32478  -->
      <sch:assert id="a-1098-32478-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32478)</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-code-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-19198-warning" test="count(cda:originalText)=1">This code SHOULD contain zero or one [0..1] originalText (CONF:1098-19198).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-code-originalText-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:code/cda:originalText">
      <sch:assert id="a-1098-19199-warning" test="count(cda:reference)=1">The originalText, if present, SHOULD contain zero or one [0..1] reference (CONF:1098-19199).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-code-originalText-reference-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:code/cda:originalText/cda:reference">
      <sch:assert id="a-1098-19200-warning" test="@value">The reference, if present, SHOULD contain zero or one [0..1] @value (CONF:1098-19200).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-performer-assignedEntity-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-8256-warning" test="count(cda:representedOrganization)=1">This assignedEntity SHOULD contain zero or one [0..1] representedOrganization (CONF:1098-8256).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Observation-performer-assignedEntity-representedOrganization-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.13'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-1098-8257-warning" test="count(cda:id)&gt;=1">The representedOrganization, if present, SHOULD contain zero or more [0..*] id (CONF:1098-8257).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Activity-Procedure-pattern-warnings">
    <sch:rule id="Procedure-Activity-Procedure-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7662-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-7662).</sch:assert>
      <sch:assert id="a-1098-7683-warning" test="count(cda:targetSiteCode)&gt;=1">SHOULD contain zero or more [0..*] targetSiteCode, which SHALL be selected from ValueSet Body Site urn:oid:2.16.840.1.113883.3.88.12.3221.8.9 DYNAMIC (CONF:1098-7683).</sch:assert>
      <sch:assert id="a-1098-7718-warning" test="count(cda:performer[count(cda:assignedEntity[count(cda:id) &gt; 0][count(cda:addr) &gt; 0][count(cda:telecom) &gt; 0])=1]) &gt; 0">SHOULD contain zero or more [0..*] performer (CONF:1098-7718) such that it SHALL contain exactly one [1..1] assignedEntity (CONF:1098-7720). This assignedEntity SHALL contain at least one [1..*] id (CONF:1098-7722). This assignedEntity SHALL contain at least one [1..*] addr (CONF:1098-7731). This assignedEntity SHALL contain at least one [1..*] telecom (CONF:1098-7732).</sch:assert>
      <!-- 04-22-2020 Change the conformance text of 1098-32479 to match that in the IG -->
      <sch:assert id="a-1098-32479-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-32479).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-code-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:code">
      <sch:assert id="a-1098-19203-warning" test="count(cda:originalText)=1">This code SHOULD contain zero or one [0..1] originalText (CONF:1098-19203).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-code-originalText-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:code/cda:originalText">
      <sch:assert id="a-1098-19204-warning" test="count(cda:reference)=1">The originalText, if present, SHOULD contain zero or one [0..1] reference (CONF:1098-19204).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-code-originalText-reference-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:code/cda:originalText/cda:reference">
      <sch:assert id="a-1098-19205-warning" test="@value">The reference, if present, SHOULD contain zero or one [0..1] @value (CONF:1098-19205).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-specimen-specimenRole-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:specimen/cda:specimenRole">
      <sch:assert id="a-1098-7716-warning" test="count(cda:id)&gt;=1">This specimenRole SHOULD contain zero or more [0..*] id (CONF:1098-7716).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-performer-assignedEntity-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1098-7733-warning" test="count(cda:representedOrganization)=1">This assignedEntity SHOULD contain zero or one [0..1] representedOrganization (CONF:1098-7733).</sch:assert>
    </sch:rule>
    <sch:rule id="Procedure-Activity-Procedure-performer-assignedEntity-representedOrganization-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.14'][@extension='2014-06-09']]/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-1098-7734-warning" test="count(cda:id)&gt;=1">The representedOrganization, if present, SHOULD contain zero or more [0..*] id (CONF:1098-7734).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Procedure-Performed-pattern-warnings">
    <sch:rule id="Procedure-Performed-effectiveTime-warnings" context="cda:procedure[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.64'][@extension='2019-12-01']]/cda:effectiveTime">
      <sch:assert id="a-4444-11670-warning" test="count(cda:low)=1">This effectiveTime SHOULD contain zero or one [0..1] low (CONF:4444-11670).</sch:assert>
      <sch:assert id="a-4444-29831-warning" test="count(@value)=1">This effectiveTime SHOULD contain zero or one [0..1] @value (CONF:4444-29831).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Product-Instance-pattern-warnings">
    <sch:rule id="Product-Instance-playingDevice-warnings" context="cda:participantRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.37']]/cda:playingDevice">
      <sch:assert id="a-81-16837-warning" test="count(cda:code)=1">This playingDevice SHOULD contain zero or one [0..1] code (CONF:81-16837).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QDM_based_QRDA-pattern-warnings">
    <sch:rule id="QDM_based_QRDA-documentationOf-serviceEvent-performer-assignedEntity-representedOrganization-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.2'][@extension='2019-12-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:assignedEntity/cda:representedOrganization">
      <sch:assert id="a-4444-16592-warning" test="count(cda:id[@root='2.16.840.1.113883.4.2'])=1">This representedOrganization SHOULD contain zero or one [0..1] id (CONF:4444-16592) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.2" Tax ID Number (CONF:4444-16593).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Reaction-Observation-pattern-warnings">
    <sch:rule id="Reaction-Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-7332-warning" test="count(cda:effectiveTime)=1">SHOULD contain zero or one [0..1] effectiveTime (CONF:1098-7332).</sch:assert>
    </sch:rule>
    <sch:rule id="Reaction-Observation-effectiveTime-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09']]/cda:effectiveTime">
      <sch:assert id="a-1098-7333-warning" test="count(cda:low)=1">The effectiveTime, if present, SHOULD contain zero or one [0..1] low (CONF:1098-7333).</sch:assert>
      <sch:assert id="a-1098-7334-warning" test="count(cda:high)=1">The effectiveTime, if present, SHOULD contain zero or one [0..1] high (CONF:1098-7334).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Result-Observation-pattern-warnings">
    <sch:rule id="Result-Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.2'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-7147-warning" test="count(cda:interpretationCode)&gt;=1">SHOULD contain zero or more [0..*] interpretationCode (CONF:1198-7147).</sch:assert>
      <sch:assert id="a-1198-7149-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1198-7149).</sch:assert>
      <sch:assert id="a-1198-7150-warning" test="count(cda:referenceRange)&gt;=1">SHOULD contain zero or more [0..*] referenceRange (CONF:1198-7150).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Service-Delivery-Location-pattern-warnings">
    <sch:rule id="Service-Delivery-Location-warnings" context="cda:participationRole[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.32']]">
      <sch:assert id="a-81-7760-warning" test="count(cda:addr)&gt;=1">SHOULD contain zero or more [0..*] addr (CONF:81-7760).</sch:assert>
      <sch:assert id="a-81-7761-warning" test="count(cda:telecom)=1">SHOULD contain zero or more [0..*] telecom (CONF:81-7761).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="Substance-Device-Allergy-Intolerance-Observation-pattern-warnings">
    <sch:rule id="Substance-Device-Allergy-Intolerance-Observation-warnings" context="cda:observation[cda:templateId[@root='2.16.840.1.113883.10.20.24.3.90'][@extension='2014-06-09']]">
      <sch:assert id="a-1098-31144-warning" test="count(cda:author[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.119']])&gt;=1">SHOULD contain zero or more [0..*] Author Participation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.119) (CONF:1098-31144).</sch:assert>
      <sch:assert id="a-1098-16318-warning" test="count(cda:participant[@typeCode='CSM']  [count(cda:participantRole[@classCode='MANU']  [count(cda:playingEntity[@classCode='MMAT']  [count(cda:code)=1])=1])=1])&gt;=1">SHOULD contain zero or more [0..*] participant (CONF:1098-16318) such that it SHALL contain exactly one [1..1] @typeCode="CSM" Consumable (CodeSystem: HL7ParticipationType urn:oid:2.16.840.1.113883.5.90 STATIC) (CONF:1098-16319). SHALL contain exactly one [1..1] participantRole (CONF:1098-16320). This participantRole SHALL contain exactly one [1..1] @classCode="MANU" Manufactured Product (CodeSystem: RoleClass urn:oid:2.16.840.1.113883.5.110 STATIC) (CONF:1098-16321). This participantRole SHALL contain exactly one [1..1] playingEntity (CONF:1098-16322). This playingEntity SHALL contain exactly one [1..1] @classCode="MMAT" Manufactured Material (CodeSystem: EntityClass urn:oid:2.16.840.1.113883.5.41 STATIC) (CONF:1098-16323).  This playingEntity SHALL contain exactly one [1..1] code, which MAY be selected from ValueSet Substance-Reactant for Intolerance urn:oid:2.16.840.1.113762.1.4.1010.1 DYNAMIC (CONF:1098-16324).</sch:assert>
      <!-- 07-15-2019 Added SHOULD assertion tests for 1098-16337, 1098-16341, 1098-32935  https://tracker.esacinc.com/browse/QRDA-617 -->
      <sch:assert id="a-1098-16337-warning" test="count(cda:entryRelationship[@typeCode='MFST'][@inversionInd='true'][count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.9'][@extension='2014-06-09'])=1])&gt;=1">SHOULD contain zero or more [0..*] entryRelationship (CONF:1098-16337) such that it SHALL contain exactly one [1..1] @typeCode="MFST" Is Manifestation of (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1098-16339).  SHALL contain exactly one [1..1] @inversionInd="true" True (CONF:1098-16338). SHALL contain exactly one [1..1] Reaction Observation (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.9:2014-06-09) (CONF:1098-16340).</sch:assert>
      <sch:assert id="a-1098-16341-warning" test="count(cda:entryRelationship[@typeCode='SUBJ'][@inversionInd='true'][count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.8'][@extension='2014-06-09'])=1])=0">SHOULD NOT contain zero or one [0..1] entryRelationship (CONF:1098-16341) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" Has Subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002 STATIC) (CONF:1098-16342).  SHALL contain exactly one [1..1] @inversionInd="true" True (CONF:1098-16343).  SHALL contain exactly one [1..1] Severity Observation (V2) (identifier: urn:hl7ii:2.16.840.1.113883.10.20.22.4.8:2014-06-09) (CONF:1098-16344).</sch:assert>
      <sch:assert id="a-1098-32935-warning" test="count(cda:entryRelationship[@typeCode='SUBJ'][@inversionInd='true'][count(cda:templateId[@root='2.16.840.1.113883.10.20.22.4.145'])=1])=1">SHOULD contain zero or one [0..1] entryRelationship (CONF:1098-32935) such that it SHALL contain exactly one [1..1] @typeCode="SUBJ" Has Subject (CodeSystem: HL7ActRelationshipType urn:oid:2.16.840.1.113883.5.1002) (CONF:1098-32936).  SHALL contain exactly one [1..1] @inversionInd="true" True (CONF:1098-32937). SHALL contain exactly one [1..1] Criticality Observation (identifier: urn:oid:2.16.840.1.113883.10.20.22.4.145) (CONF:1098-32938).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US-Realm-Address-pattern-warnings">
    <sch:rule id="US-Realm-Address-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:addr             | cda:supply[cda:templateId[@root='2.16.840.1.113883.10.20.22.4.18']]/cda:performer/cda:assignedEntity/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:author/cda:assignedAuthor/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:dataEnterer/cda:assignedEntity/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:legalAuthenticator/cda:assignedEntity/cda:addr             | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian/cda:addr">
      <sch:assert id="a-81-7290-warning" test="@use">SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet PostalAddressUse urn:oid:2.16.840.1.113883.1.11.10637 STATIC 2005-05-01 (CONF:81-7290).</sch:assert>
      <sch:assert id="a-81-7295-warning" test="count(cda:country)=1">SHOULD contain zero or one [0..1] country, which SHALL be selected from ValueSet Country urn:oid:2.16.840.1.113883.3.88.12.80.63 DYNAMIC (CONF:81-7295).</sch:assert>
      <sch:assert id="a-81-7293-warning" test="count(cda:state)=1">SHOULD contain zero or one [0..1] state (ValueSet: StateValueSet urn:oid:2.16.840.1.113883.3.88.12.80.1 DYNAMIC) (CONF:81-7293).</sch:assert>
      <sch:assert id="a-81-7294-warning" test="count(cda:postalCode)=1">SHOULD contain zero or one [0..1] postalCode, which SHOULD be selected from ValueSet PostalCode urn:oid:2.16.840.1.113883.3.88.12.80.2 DYNAMIC (CONF:81-7294).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US-Realm-Date-and-Time-pattern-warnings">
    <sch:rule id="US-Realm-Date-and-Time-effectiveTime-warnings" context="cda:effectiveTime[parent::cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]]              | cda:effectiveTime[parent::cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3']]]             | cda:effectiveTime[parent::cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.27.1.1'][@extension='2016-09-01']]]">
      <sch:assert id="a-81-10128-e-warning" test="string-length(@value)&gt;=12">SHOULD be precise to the minute (CONF:81-10128).</sch:assert>
      <sch:assert id="a-81-10130-e-warning" test="string-length(@value)&lt;10 or ( string-length(@value)&gt;=10 and (contains(@value,'+') or contains(@value,'-')))">If more precise than day, SHOULD include time-zone offset (CONF:81-10130).</sch:assert>
    </sch:rule>
    <sch:rule id="US-Realm-Date-and-Time-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:author/cda:time                                                               | cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1']]/cda:legalAuthenticator/cda:time">
      <sch:assert id="a-81-10128-t-warning" test="string-length(@value)&gt;=12">SHOULD be precise to the minute (CONF:81-10128).</sch:assert>
      <sch:assert id="a-81-10130-t-warning" test="string-length(@value)&lt;10 or ( string-length(@value)&gt;=10 and (contains(@value,'+') or contains(@value,'-')))">If more precise than day, SHOULD include time-zone offset (CONF:81-10130).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="US_Realm-pattern-warnings">
    <sch:rule id="US_Realm-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]">
      <sch:assert id="a-1198-5579-warning" test="count(cda:legalAuthenticator)=1">SHOULD contain zero or one [0..1] legalAuthenticator (CONF:1198-5579).</sch:assert>
    </sch:rule>
    <!-- 08-16-2019 Removed a-1198-5259-v-warning test for inclusion in value set because conformance text was changed from STATIC to DYNAMIC JIRA https://tracker.esacinc.com/browse/QRDA-635 -->
    <!--
        <sch:rule id="US_Realm-confidentialityCode-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:confidentialityCode">
             <sch:assert id="a-1198-5259-v-warning" test="@code=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.1.11.16926']/voc:code/@value">SHALL contain exactly one [1..1] confidentialityCode, which SHOULD be selected from ValueSet HL7 BasicConfidentialityKind urn:oid:2.16.840.1.113883.1.11.16926 DYNAMIC (CONF:1198-5259).</sch:assert>
        </sch:rule>
        -->
    <sch:rule id="US_Realm-recordTarget-patientRole-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:telecom">
      <sch:assert id="a-1198-5375-warning" test="@use">Such telecoms SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-5375).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient">
      <sch:assert id="a-1198-5303-warning" test="count(cda:maritalStatusCode)=1">This patient SHOULD contain zero or one [0..1] maritalStatusCode, which SHALL be selected from ValueSet Marital Status urn:oid:2.16.840.1.113883.1.11.12212 DYNAMIC (CONF:1198-5303).</sch:assert>
      <sch:assert id="a-1198-5406-warning" test="count(cda:languageCommunication) &gt; 0">This patient SHOULD contain zero or more [0..*] languageCommunication (CONF:1198-5406).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-birthTime-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:birthTime">
      <sch:assert id="a-1198-5300-warning" test="string-length(@value) &gt;= 8">SHOULD be precise to day (CONF:1198-5300).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-guardian-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian">
      <sch:assert id="a-1198-5326-warning" test="count(cda:code)=1">The guardian, if present, SHOULD contain zero or one [0..1] code, which SHALL be selected from ValueSet Personal And Legal Relationship Role Type urn:oid:2.16.840.1.113883.11.20.12.1 DYNAMIC (CONF:1198-5326).</sch:assert>
      <sch:assert id="a-1198-5359-warning" test="count(cda:addr) &gt; 0">The guardian, if present, SHOULD contain zero or more [0..*] US Realm Address (AD.US.FIELDED) (identifier: urn:oid:2.16.840.1.113883.10.20.22.5.2) (CONF:1198-5359).</sch:assert>
      <sch:assert id="a-1198-5382-warning" test="count(cda:telecom) &gt; 0">The guardian, if present, SHOULD contain zero or more [0..*] telecom (CONF:1198-5382).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-guardian-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:guardian/cda:telecom">
      <sch:assert id="a-1198-7993-warning" test="@use">The telecom, if present, SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-7993).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-birthplace-place-addr-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:birthplace/cda:place/cda:addr">
      <sch:assert id="a-1198-5404-warning" test="count(cda:country)=1">This addr SHOULD contain zero or one [0..1] country, which SHALL be selected from ValueSet Country urn:oid:2.16.840.1.113883.3.88.12.80.63 DYNAMIC (CONF:1198-5404).</sch:assert>
      <sch:assert id="a-1198-5402-warning" test="count(cda:state)=1 and (cda:country='US' or cda:country='USA')">If country is US, this addr SHALL contain exactly one [1..1] state, which SHALL be selected from ValueSet StateValueSet 2.16.840.1.113883.3.88.12.80.1 DYNAMIC (CONF:1198-5402).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-patient-languageCommunication-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:patient/cda:languageCommunication">
      <sch:assert id="a-1198-9965-warning" test="count(cda:proficiencyLevelCode)=1">The languageCommunication, if present, SHOULD contain zero or one [0..1] proficiencyLevelCode, which SHALL be selected from ValueSet LanguageAbilityProficiency urn:oid:2.16.840.1.113883.1.11.12199 DYNAMIC (CONF:1198-9965).</sch:assert>
      <sch:assert id="a-1198-5414-warning" test="count(cda:preferenceInd)=1">The languageCommunication, if present, SHOULD contain zero or one [0..1] preferenceInd (CONF:1198-5414).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-providerOrganization-id-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:providerOrganization/cda:id">
      <sch:assert id="a-1198-16820-warning" test="@root='2.16.840.1.113883.4.6'">Such ids SHOULD contain zero or one [0..1] @root="2.16.840.1.113883.4.6" National Provider Identifier (CONF:1198-16820).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-recordTarget-patientRole-providerOrganization-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:recordTarget/cda:patientRole/cda:providerOrganization/cda:telecom">
      <sch:assert id="a-1198-7994-warning" test="@use">Such telecoms SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-7994).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor">
      <sch:assert id="a-1198-16783-warning" test="count(cda:assignedAuthoringDevice)=1">This assignedAuthor SHOULD contain zero or one [0..1] assignedAuthoringDevice (CONF:1198-16783).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-assignedPerson-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor/cda:assignedPerson">
      <sch:assert id="a-1198-32882-warning" test="count(../cda:id[@root='2.16.840.1.113883.4.6'][@extension])=1">If this assignedAuthor is an assignedPerson, this assignedAuthor SHOULD contain zero or one [0..1] id (CONF:1198-32882) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.6" National Provider Identifier (CONF:1198-32884). SHOULD contain zero or one [0..1] @extension (CONF:1198-32885).</sch:assert>
      <sch:assert id="a-1198-16787-warning" test="count(../cda:code)=1">&gt;If this assignedAuthor is an assignedPerson, this assignedAuthor SHOULD contain zero or one [0..1] code (CONF:1198-16787).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-author-assignedAuthor-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:author/cda:assignedAuthor/cda:telecom">
      <sch:assert id="a-1198-7995-warning" test="@use">Such telecoms SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-7995).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-dataEnterer-assignedEntity-id-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:dataEnterer/cda:assignedEntity/cda:id">
      <sch:assert id="a-1198-16821-warning" test="@root='2.16.840.1.113883.4.6'">Such ids SHOULD contain zero or one [0..1] @root="2.16.840.1.113883.4.6" National Provider Identifier (CONF:1198-16821).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-dataEnterer-assignedEntity-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:dataEnterer/cda:assignedEntity/cda:telecom">
      <sch:assert id="a-1198-7996-warning" test="@use">Such telecoms SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-7996).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-custodian-assignedCustodian-representedCustodianOrganization-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization">
      <sch:assert id="a-1198-16822-warning" test="count(cda:id[@root='2.16.840.1.113883.4.6'])=1">Such ids SHOULD contain zero or one [0..1] @root="2.16.840.1.113883.4.6" National Provider Identifier (CONF:1198-16822).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-custodian-assignedCustodian-representedCustodianOrganization-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:custodian/cda:assignedCustodian/cda:representedCustodianOrganization/cda:telecom">
      <sch:assert id="a-1198-7998-warning" test="@use">This telecom SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-7998).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-legalAuthenticator-assignedEntity-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:legalAuthenticator/cda:assignedEntity/cda:telecom">
      <sch:assert id="a-1198-7999-warning" test="@use">Such telecoms SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-7999).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authenticator-assignedEntity-id-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authenticator/cda:assignedEntity/cda:id">
      <sch:assert id="a-1198-16824-warning" test="@root='2.16.840.1.113883.4.6'">Such ids SHOULD contain zero or one [0..1] @root="2.16.840.1.113883.4.6" National Provider Identifier (CONF:1198-16824).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-authenticator-assignedEntity-telecom-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:authenticator/cda:assignedEntity/cda:telecom">
      <sch:assert id="a-1198-8000-warning" test="@use">Such telecoms SHOULD contain zero or one [0..1] @use, which SHALL be selected from ValueSet Telecom Use (US Realm Header) urn:oid:2.16.840.1.113883.11.20.9.20 DYNAMIC (CONF:1198-8000).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent">
      <sch:assert id="a-1198-14839-warning" test="count(cda:performer) &gt; 0">This serviceEvent SHOULD contain zero or more [0..*] performer (CONF:1198-14839).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-performer-functionCode-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:functionCode">
      <!-- 08-14-2019 Changed conformance text of 1198-32889 from STATIC to DYNAMIC JIRA https://tracker.esacinc.com/browse/QRDA-635 -->
      <sch:assert id="a-1198-32889-warning" test="@code">The functionCode, if present, SHOULD contain zero or one [0..1] @code, which SHOULD be selected from ValueSet ParticipationFunction urn:oid:2.16.840.1.113883.1.11.10267 DYNAMIC (CONF:1198-32889).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-performer-assignedEntity-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:assignedEntity">
      <sch:assert id="a-1198-14842-warning" test="count(cda:code)=1">This assignedEntity SHOULD contain zero or one [0..1] code, which SHOULD be selected from ValueSet Healthcare Provider Taxonomy (HIPAA) urn:oid:2.16.840.1.114222.4.11.1066 DYNAMIC (CONF:1198-14842).</sch:assert>
    </sch:rule>
    <sch:rule id="US_Realm-documentationOf-serviceEvent-performer-assignedEntity-id-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.22.1.1'][@extension='2015-08-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:assignedEntity/cda:id">
      <sch:assert id="a-1198-14847-warning" test="@root='2.16.840.1.113883.4.6'">Such ids SHOULD contain zero or one [0..1] @root="2.16.840.1.113883.4.6" National Provider Identifier (CONF:1198-14847).</sch:assert>
    </sch:rule>
  </sch:pattern>
  <sch:pattern id="QRDA_Category_I_Report_CMS-pattern-warnings">
    <sch:rule id="QRDA_Category_I_Report_CMS-recordTarget-patientRole-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:recordTarget/cda:patientRole">
      <sch:assert id="a-4444-16857_C01-warning" test="count(cda:id[@root='2.16.840.1.113883.4.572']) = 1">This patientRole SHOULD contain zero or one [0..1] id (CONF:4444-16857_C01) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.572" Medicare HIC number (CONF:4444-16858).</sch:assert>
      <sch:assert id="a-4444-28697_C01-warning" test="count(cda:id[@root='2.16.840.1.113883.4.927']) = 1">This patientRole SHOULD contain zero or one [0..1] id (CONF:4444-28697_C01) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.927" Medicare Beneficiary Identifier (MBI) (CONF:4444-28698).</sch:assert>
    </sch:rule>
    <sch:rule id="QRDA_Category_I_Report_CMS-documentationOf-serviceEvent-performer-assignedEntity-warnings" context="cda:ClinicalDocument[cda:templateId[@root='2.16.840.1.113883.10.20.24.1.3'][@extension='2020-02-01']]/cda:documentationOf/cda:serviceEvent/cda:performer/cda:assignedEntity">
      <!-- Added _C01 suffix to 4444-16587. Updated conformance id for 4444-16588  for 2021, QRDA-631-->
      <!-- 12-07-2020 Corrected such that subclause text to reflect the conformance ID used in the IG (4444-28497). -->
      <sch:assert id="a-4444-16587_C01-warning" test="count(cda:id[@root='2.16.840.1.113883.4.6']) = 1">This assignedEntity SHOULD contain zero or one [0..1] id (CONF:4444-16587_C01) such that it SHALL contain exactly one [1..1] @root="2.16.840.1.113883.4.6" National Provider ID (CONF:4444-28497).</sch:assert>
    </sch:rule>
  </sch:pattern>
</sch:schema>
