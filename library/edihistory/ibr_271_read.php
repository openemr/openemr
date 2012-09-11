<?php
/**
 * ibr_271_read.php
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 or later.  You should have 
 * received a copy of the GNU General Public License along with this program; 
 * if not, write to the Free Software Foundation, Inc., 
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *  <http://opensource.org/licenses/gpl-license.php>
 * 
 * Strictly pre-alpha script to read x12 271 eligibility inquiry response files
 * 
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
/* ********************
from http://www.faima.com/edi_4010/ts271.htm

These are version 4010, but still informative

270 Eligibility, Coverage or Benefit Inquiry
FUNCTIONAL GROUP = HS

This Draft Standard for Trial Use contains the format and establishes the data contents 
of the Eligibility, Coverage or Benefit Inquiry Transaction Set (270) for use within the 
context of an Electronic Data Interchange (EDI) environment. This transaction set can be 
used to inquire about the eligibility, coverages or benefits associated with a benefit plan, 
employer, plan sponsor, subscriber or a dependent under the subscriber's policy. 
The transaction set is intended to be used by all lines of insurance such as Health, 
Life, and Property and Casualty.


Table 1

    0010 ST  Transaction Set Header                  M         1            
    0020 BHT Beginning of Hierarchical Transaction   M         1            

Table 2

    ---- LOOP ID - 2000 -----------------------------         >1-----------+
    0010 HL  Hierarchical Level                      M         1           |
N   0020 TRN Trace                                   O         9           |
    ---- LOOP ID - 2100 -----------------------------         >1----------+|
    0030 NM1 Individual or Organizational Name       M         1          ||
    0040 REF Reference Identification                O         9          ||
    0050 N2  Additional Name Information             O         1          ||
    0060 N3  Address Information                     O         1          ||
    0070 N4  Geographic Location                     O         1          ||
    0080 PER Administrative Communications Contact   O         3          ||
    0090 PRV Provider Information                    O         1          ||
    0100 DMG Demographic Information                 O         1          ||
    0110 INS Insured Benefit                         O         1          ||
    0120 DTP Date or Time or Period                  O         9          ||
    ---- LOOP ID - 2110 -----------------------------         99---------+||
    0130 EQ  Eligibility or Benefit Inquiry          O         1         |||
    0135 AMT Monetary Amount                         O         2         |||
    0140 VEH Vehicle Information                     O         1         |||
    0150 PDR Property Description - Real             O         1         |||
    0160 PDP Property Description - Personal         O         1         |||
    0170 III Information                             O        10         |||
    0190 REF Reference Identification                O         1         |||
    0200 DTP Date or Time or Period                  O         9---------+++
    0210 SE  Transaction Set Trailer                 M         1            


Notes:
2/020   If the Eligibility, Coverage or Benefit Inquiry Transaction Set (270) includes 
        a TRN segment, then the Eligibility, Coverage or Benefit Information 
        Transaction Set (271) must return the trace number identified in the TRN 
        segment.




271 Eligibility, Coverage or Benefit Information
FUNCTIONAL GROUP = HB

This Draft Standard for Trial Use contains the format and establishes the data contents 
of the Eligibility, Coverage or Benefit Information Transaction Set (271) for use within 
the context of an Electronic Data Interchange (EDI) environment. This transaction set can 
be used to communicate information about or changes to eligibility, coverage or benefits 
from information sources (such as - insurers, sponsors, payors) to information receivers 
(such as - physicians, hospitals, repair facilities, third party administrators, 
governmental agencies). This information includes but is not limited to: benefit status, 
explanation of benefits, coverages, dependent coverage level, effective dates, amounts 
for co-insurance, co-pays, deductibles, exclusions and limitations.


Table 1

    0010 ST  Transaction Set Header                  M         1            
    0020 BHT Beginning of Hierarchical Transaction   M         1            

Table 2

    ---- LOOP ID - 2000 -----------------------------         >1-----------+
    0010 HL  Hierarchical Level                      M         1           |
N   0020 TRN Trace                                   O         9           |
    0025 AAA Request Validation                      O         9           |
    ---- LOOP ID - 2100 -----------------------------         >1----------+|
    0030 NM1 Individual or Organizational Name       O         1          ||
    0040 REF Reference Identification                O         9          ||
    0050 N2  Additional Name Information             O         1          ||
    0060 N3  Address Information                     O         1          ||
    0070 N4  Geographic Location                     O         1          ||
    0080 PER Administrative Communications Contact   O         3          ||
    0085 AAA Request Validation                      O         9          ||
    0090 PRV Provider Information                    O         1          ||
    0100 DMG Demographic Information                 O         1          ||
    0110 INS Insured Benefit                         O         1          ||
    0120 DTP Date or Time or Period                  O         9          ||
    ---- LOOP ID - 2110 -----------------------------         >1---------+||
    0130 EB  Eligibility or Benefit Information      O         1         |||
    0135 HSD Health Care Services Delivery           O         9         |||
    0140 REF Reference Identification                O         9         |||
    0150 DTP Date or Time or Period                  O        20         |||
    0160 AAA Request Validation                      O         9         |||
    0170 VEH Vehicle Information                     O         1         |||
    0180 PID Product/Item Description                O         1         |||
    0190 PDR Property Description - Real             O         1         |||
    0200 PDP Property Description - Personal         O         1         |||
    0210 LIN Item Identification                     O         1         |||
    0220 EM  Equipment Characteristics               O         1         |||
    0230 SD1 Safety Data                             O         1         |||
    0240 PKD Packaging Description                   O         1         |||
    0250 MSG Message Text                            O        10         |||
    ---- LOOP ID - 2115 -----------------------------         >1--------+|||
    0260 III Information                             O         1        ||||
    0270 DTP Date or Time or Period                  O         5        ||||
    0280 AMT Monetary Amount                         O         5        ||||
    0290 PCT Percent Amounts                         O         5        ||||
    ---- LOOP ID - 2117 -----------------------------         >1-------+||||
    0300 LQ  Industry Code                           O         1       |||||
    0310 AMT Monetary Amount                         O         5       |||||
    0320 PCT Percent Amounts                         O         5-------++|||
    0330 LS  Loop Header                             O         1         |||
    ---- LOOP ID - 2120 -----------------------------          1--------+|||
    0340 NM1 Individual or Organizational Name       O         1        ||||
    0350 N2  Additional Name Information             O         1        ||||
    0360 N3  Address Information                     O         1        ||||
    0370 N4  Geographic Location                     O         1        ||||
    0380 PER Administrative Communications Contact   O         3        ||||
    0390 PRV Provider Information                    O         1--------+|||
    0400 LE  Loop Trailer                            O         1---------+++
    0410 SE  Transaction Set Trailer                 M         1            


Notes:
2/020   If the Eligibility, Coverage or Benefit Inquiry Transaction Set (270) includes 
        a TRN segment, then the Eligibility, Coverage or Benefit Information 
        Transaction Set (271) must return the trace number identified in the TRN 
        segment.


Rendered layout
* loop 2000A
*    | Payer Name | Payer ID | Payer Contact |
* loop 2000B  (omit or append to 2000A)
*    | Provider Name | Provider ID | Provider Contact |
* loop 2000C/2000D
*    | Subscriber Name | Date of Birth | Sex | Street, City, Postal
*    | Policy ID | Group Number | Effective Date | Coverage | Primary? | Waiting Period Pre-existing?
*   foreach(EB as b) -- then b['EB01'] b['EB02'], etc.
*    | EB01=A : Co-Ins | EB02=IND : individual only | EB03=[repetitions - service type] : Professional (Physician) Visit - Office, etc.
* 	 | EB04=MB : Medicare Part B | EB05='product or plan name' | EB06=27 : Visit | EB07='' : co-pay not specified | EB08=20 : 20% Co-Ins
* 


***************** */
// need entity identifier code

function ibr_271_codes($segment_id, $code) {
	//
	$code271 = array();
	// AAA rejection reasons 
	$code271['AAA'] = array(
			'15'=>'Required application data missing',
			'35'=>'Out of Network',
			'41'=>'Authorization/Access restrictions',
			'43'=>'Invalid/Missing provider information',
			'44'=>'Invalid/Missing provider name',
			'45'=>'Invalid/Missing provider speciality',
			'46'=>'Invalid/Missing provider phone number',
			'47'=>'Invalid/Missing provider state',
			'48'=>'Invalid/Missing referring provider identification number',
			'49'=>'Provider is not primary care physician',
			'50'=>'Provider ineligible for inquiries',
			'51'=>'Provider not on file',
			'52'=>'Service dates not within provider plan enrollment',
			'56'=>'Inappropriate date',
			'57'=>'Invalid/Missing dates of service',
			'58'=>'Invalid/Missing date of birth',
			'60'=>'Date of birth follows date of service',
			'61'=>'Date of death preceeds dates of service',
			'62'=>'Date of service not within allowable inquiry period',
			'63'=>'Date of service in future',
			'71'=>'Patient birth date does not match that for the parient in the database',
			'72'=>'Invalid/Missing subscriber/insured ID',
			'73'=>'Invalid/Missing subscriber/insured name',
			'74'=>'Invalid/Missing subscriber/insured gender code',
			'75'=>'Subscriber/Insured not found',
			'76'=>'Duplicate Subscriber/Insured ID number',
			'78'=>'Subscriber/Insured not in Group/Plan identified',
			'79'=>'Invalid participant identification',
			'97'=>'invalid or missing provider address',
			'T4'=>'Payer name or identifier missing',
			'80'=>'No response received by clearinghouse',
			'C'=>'Please correct and resubmit',
			'N'=>'Resubmission not allowed',
			'P'=>'Please resubmit original transaction',
			'R'=>'Resubmission allowed',
			'S'=>'Do not resubmit; Inquiry initiated to a third party',
			'W'=>'Please wait 30 days and resubmit',
			'X'=>'Please wait 10 days and resubmit',
			'Y'=>'Do not resubmit; We will hold your request and respond again shortly'
			);
			
			
	// provider PRV codes
	$code271['PRV'] = array(			
			'AD'=>'Admitting',
			'AT'=>'Attending',
			'BI'=>'Billing',
			'CO'=>'Consulting',
			'CV'=>'Covering',
			'H'=>'Hospital',
			'HH'=>'Home Health Care',
			'LA'=>'Laboratory',
			'OT'=>'Other Physician',
			'P1'=>'Pharmacy',
			'PC'=>'Primary Care Physician',
			'PE'=>'Performing',
			'R'=>'Rural Health Clinic',
			'RF'=>'Referring',
			'SB'=>'Sunmitting',
			'SK'=>'Skilled Nursing Facility',
			'SU'=>'Supervising'
			);
			
	//REF codes
	$code271['REF'] = array(
			'18'=>'Plan Number',
			'1L'=>'Group or Policy number',
			'1W'=>'Member identification number',
			'3H'=>'Case number',
			'49'=>'Family unit number',
			'6P'=>'Group number',
			'CE'=>'Class of contract code',
			'CT'=>'Provider contract number',
			'EA'=>'Medical record identification number',
			'EJ'=>'Patient account number',
			'F6'=>'Health insurance claim (HIC) number',
			'GH'=>'Identification card serial number',
			'HJ'=>'Identity card number',
			'IF'=>'Issue number',
			'IG'=>'Insurance policy number',
			'N6'=>'Plan network identification number',
			'NQ'=>'Medicaid recipient identification number',
			'Q4'=>'Prior identifier number',
			'SY'=>'Social security number',
			'Y4'=>'Agency claim number'
			);
			
	// DTP date qualifiers
	$code271['DTP'] = array(
			'096'=>'Discharge',
			'102'=>'Issue',
			'152'=>'Effective date of change',
			'291'=>'Plan',
			'307'=>'Eligibility',
			'318'=>'Added',
			'340'=>'COBRA begin',
			'341'=>'COBRA end',
			'342'=>'Premium paid to date begin',
			'343'=>'Premium paid to date end',
			'346'=>'Plan begin',
			'347'=>'Plan end',
			'356'=>'Eligibility begin',
			'357'=>'Eligibility end',
			'382'=>'Enrollment',
			'435'=>'Admission',
			'442'=>'Date of death',
			'458'=>'Certification',
			'472'=>'Service',
			'539'=>'Policy effective',
			'540'=>'Policy expiration',
			'636'=>'Date of last update',
			'771'=>'Status'
			);


	//entity identifier code  --code source 237
	$code271['NM101'] = array(	
			'03'=>'Dependent',
			'13'=>'Contracted Service Provider',
			'1I'=>'Preferred Provider Organization (PPO)',
			'1P'=>'Provider',
			'2B'=>'Third-Party Administrator',
			'36'=>'Employer',
			'70'=>'Prior Incorrect Insured',
			'71'=>'Attending Physician',
			'72'=>'Operating Physician',
			'73'=>'Other Physician',
			'74'=>'Corrected Insured',
			'75'=>'Participant',
			'EXS'=>'Ex-spouse',
			'FA'=>'Facility',
			'GM'=>'Spouse Insured',
			'GP'=>'Gateway Provider',
			'GW'=>'Group',
			'HF'=>'HPSA Facility',
			'HH'=>'Home Health Agency',
			'I3'=>'Independent Physicians Association (IPA)',
			'IL'=>'Insured or Subscriber',
			'IR'=>'Self Insured',
			'LR'=>'Legal Representative',
			'NZ'=>'Primary Physician',
			'OC'=>'Origin Carrier',
			'P2'=>'Primary Insured or Subscriber',
			'P3'=>'Primary Care Provider',
			'P4'=>'Prior Insurance Carrier',
			'P5'=>'Plan Sponsor',
			'PR'=>'Payer',
			'SEP'=>'Secondary Payer',
			'TTP'=>'Tertiary Payer',
			'VER'=>'Party Performing Verification',
			'VN'=>'Vendor',
			'VY'=>'Organization Completing Configuration Change',
			'X3'=>'Utilization Management Organization',
			'X4'=>'Spouse',
			'Y2'=>'Managed Care Organization',
			'AAG'=>'Ground Ambulance Services',
			'AAK'=>'Primary Surgeon',
			'AAL'=>'Medical Nurse',
			'AAM'=>'Cardiac Rehabilitation Services',
			'AAN'=>'Skilled Nursing Services',
			'AAO'=>'Observation Room Services',
			'AAQ'=>'Anesthesiology Services',
			'NCT'=>'Name Changed To',
			'ORI'=>'Original Name',
			'5T'=>'X-Ray Radiation Therapy Unit',
			'5U'=>'CT Scanner Unit',
			'5V'=>'Diagnostic Radioisotope Facility',
			'5W'=>'Magnetic Resonance Imaging (MRI) Facility',
			'5X'=>'Ultrasound Unit',
			'5Y'=>'Rehabilitation Inpatient Unit',
			'5Z'=>'Rehabilitation Outpatient Services'			
			);

	// Name -- entity relationship code
	$code271['NM110'] = array(
			'01'=>'Parent',
			'02'=>'Child',
			'27'=>'Domestic Partner',
			'41'=>'Spouse',
			'48'=>'Employee',
			'65'=>'Other',
			'72'=>'Unknown'
			);
			
	// MPI  qualifiers
	// the MPI03 Service affiliation qualifiers have 'SB' prepended
	$code271['MPI'] = array(
			'A'=>'Partial',
			'C'=>'Current',
			'L'=>'Latest',
			'O'=>'Oldest',
			'P'=>'Prior',
			'S'=>'Second most current',
			'T'=>'Third most current',
			'AE'=>'Active reserve',
			'AO'=>'Active military - overseas',
			'AS'=>'Academy student',
			'AT'=>'Presidential appointee',
			'CC'=>'Contractor',
			'DD'=>'Dishonorably discharges',
			'HD'=>'Honorably discharges',
			'IR'=>'Inactive reserve',
			'LX'=>'Leave of absence: military',
			'PE'=>'Plan to enlist',
			'RE'=>'REcommissioned',
			'RM'=>'Retired military - overseas',
			'RR'=>'Retired without recall',
			'RU'=>'REtired military - USA',
			'SBA'=>'Air Force',
			'SBB'=>'Air Force Reserves',
			'SBC'=>'Army',
			'SBD'=>'Army Reserves',
			'SBE'=>'Coast Guard',
			'SBF'=>'Marine Corps',
			'SBG'=>'Marine Corps Reserves',
			'SBH'=>'National Guard',
			'SBI'=>'Navy',
			'SBJ'=>'Navy Reserves',
			'SBK'=>'Other',
			'SBL'=>'Peace Corp',
			'SBM'=>'Regular Armed Forces',
			'SBN'=>'Reserves',
			'SBO'=>'U.S. Public Health Service',
			'SBQ'=>'Foreign Military',
			'SBR'=>'American Red Cross',
			'SBS'=>'Department of Defvense',
			'SBU'=>'Unites Services Organization',
			'SBW'=>'Military Sealift Command',
			'A1'=>'Admiral',
			'A2'=>'Airman',
			'A3'=>'Airman First Class',
			'B1'=>'Basic Airman',
			'B2'=>'Brigadier General',
			'C1'=>'Captain',
			'C2'=>'Chief Master Sergeant',
			'C3'=>'Chief Petty Officer',
			'C4'=>'Chief Warrant',
			'C5'=>'Colonel',
			'C6'=>'Commander',
			'C7'=>'Commodore',
			'C8'=>'Corporal',
			'C9'=>'Corporal Specialist 4',
			'E1'=>'Ensign',
			'F1'=>'First Lieutenant',
			'F2'=>'First Sergeant',
			'F3'=>'First Sergeant-Master Sergeant',
			'F4'=>'Fleet Admiral',
			'G1'=>'General',
			'G4'=>'Gunnery Sergeant',
			'L1'=>'Lance Corporal',
			'L2'=>'Lieutenant',
			'L3'=>'Lieutenant Colonel',
			'L4'=>'Lieutenant Commander',
			'L5'=>'Lieutenant General',
			'L6'=>'Lieutenant Junior Grade',
			'M1'=>'Major',
			'M2'=>'Major General',
			'M3'=>'Master Chief Petty Officer',
			'M4'=>'Master Gunnery Sergeant Major',
			'M5'=>'Master Sergeant',
			'M6'=>'Master Sergeant Specialist 8',
			'P1'=>'Petty Officer First Class',
			'P2'=>'Petty Officer Second Class',
			'P3'=>'Petty Officer Third Class',
			'P4'=>'Private',
			'P5'=>'Private First Class',
			'R1'=>'Rear Admiral',
			'R2'=>'Recruit',
			'S1'=>'Seaman',
			'S2'=>'Seaman Apprentice',
			'S3'=>'Seaman Recruit',
			'S4'=>'Second Lieutenant',
			'S5'=>'Senior Chief Petty Officer',
			'S6'=>'Senior Master Sergeant',
			'S7'=>'Sergeant',
			'S8'=>'Sergeant First Class Specialist 7',
			'S9'=>'Sergeant Major Specialist 9',
			'SA'=>'Sergeant Specialist 5',
			'SB'=>'Staff Sergeant',
			'SC'=>'Staff Sergeant Specialist 6',
			'T1'=>'Technical Sergeant',
			'V1'=>'Vice Admiral',
			'W1'=>'Warrant Officer'			
			);

	// eligibility or benifit information code
	$code271['EB01'] = array(			
			'1'=>'Active Coverage',
			'2'=>'Active - Full Risk Capitation',
			'3'=>'Active - Services Capitated',
			'4'=>'Active - Services Capitated to Primary Care Physician',
			'5'=>'Active - Pending Investigation',
			'6'=>'Inactive',
			'7'=>'Inactive - Pending Eligibility Update',
			'8'=>'Inactive - Pending Investigation',
			'A'=>'Co-Insurance',
			'B'=>'Co-Payment',
			'C'=>'Deductible',
			'D'=>'Benefit Description',
			'E'=>'Exclusions',
			'F'=>'Limitations',
			'G'=>'Out of Pocket (Stop Loss)',
			'H'=>'Unlimited',
			'I'=>'Non-Covered',
			'J'=>'Cost Containment',
			'K'=>'Reserve',
			'L'=>'Primary Care Provider',
			'M'=>'Pre-existing Condition',
			'N'=>'Services Restricted to Following Provider',
			'O'=>'Not Deemed a Medical Necessity',
			'P'=>'Benefit Disclaimer',
			'Q'=>'Second Surgical Opinion Required',
			'R'=>'Other or Additional Payor',
			'S'=>'Prior Year(s) History',
			'T'=>'Card(s) Reported Lost/Stolen',
			'U'=>'Contact Following Entity for Eligibility or Benefit Information',
			'V'=>'Cannot Process',
			'W'=>'Other Source of Data',
			'X'=>'Health Care Facility',
			'Y'=>'Spend Down',
			'CB'=>'Coverage Basis',
			'MC'=>'Managed Care Coordinator'
			);
	
	// coverage level code		
	$code271['EB02'] = array(		
			'CHD'=>'Children Only',
			'DEP'=>'Dependents Only',
			'E1D'=>'Employee and One Dependent',
			'E2D'=>'Employee and Two Dependents',
			'E3D'=>'Employee and Three Dependents',
			'E5D'=>'Employee and One or More Dependents',
			'E6D'=>'Employee and Two or More Dependents',
			'E7D'=>'Employee and Three or More Dependents',
			'E8D'=>'Employee and Four or More Dependents',
			'E9D'=>'Employee and Five or More Dependents',
			'ECH'=>'Employee and Children',
			'EMP'=>'Employee Only',
			'ESP'=>'Employee and Spouse',
			'FAM'=>'Family',
			'IND'=>'Individual',
			'SPC'=>'Spouse and Children',
			'SPO'=>'Spouse Only',
			'TWO'=>'Two Party'
			);
	
	// service type code 
	$code271['EB03'] = array(
			'1'=>'Medical Care',
			'2'=>'Surgical',
			'3'=>'Consultation',
			'4'=>'Diagnostic X-Ray',
			'5'=>'Diagnostic Lab',
			'6'=>'Radiation Therapy',
			'7'=>'Anesthesia',
			'8'=>'Surgical Assistance',
			'10'=>'Blood',
			'11'=>'Durable Medical Equipment Used',
			'12'=>'Durable Medical Equipment Purchased',
			'14'=>'Renal Supplies',
			'17'=>'Pre-Admission Testing',
			'18'=>'Durable Medical Equipment Rental',
			'19'=>'Pneumonia Vaccine',
			'20'=>'Second Surgical Opinion',
			'21'=>'Third Surgical Opinion',
			'22'=>'Social Work',
			'23'=>'Diagnostic Dental',
			'24'=>'Periodontics',
			'25'=>'Restorative',
			'26'=>'Endodontics',
			'27'=>'Maxillofacial Prosthetics',
			'28'=>'Adjunctive Dental Services',
			'30'=>'Health Benefit Plan Coverage',
			'32'=>'Plan Waiting Period',
			'33'=>'Chiropractic',
			'34'=>'Chiropractic Modality',
			'35'=>'Dental Care',
			'36'=>'Dental Crowns',
			'37'=>'Dental Accident',
			'38'=>'Orthodontics',
			'39'=>'Prosthodontics',
			'40'=>'Oral Surgery',
			'41'=>'Preventive Dental',
			'42'=>'Home Health Care',
			'43'=>'Home Health Prescriptions',
			'45'=>'Hospice',
			'46'=>'Respite Care',
			'47'=>'Hospitalization',
			'49'=>'Hospital - Room and Board',
			'54'=>'Long Term Care',
			'55'=>'Major Medical',
			'56'=>'Medically Related Transportation',
			'60'=>'General Benefits',
			'61'=>'In-vitro Fertilization',
			'62'=>'MRI Scan',
			'63'=>'Donor Procedures',
			'64'=>'Acupuncture',
			'65'=>'Newborn Care',
			'66'=>'Pathology',
			'67'=>'Smoking Cessation',
			'68'=>'Well Baby Care',
			'69'=>'Maternity',
			'70'=>'Transplants',
			'71'=>'Audiology',
			'72'=>'Inhalation Therapy',
			'73'=>'Diagnostic Medical',
			'74'=>'Private Duty Nursing',
			'75'=>'Prosthetic Device',
			'76'=>'Dialysis',
			'77'=>'Otology',
			'78'=>'Chemotherapy',
			'79'=>'Allergy Testing',
			'80'=>'Immunizations',
			'81'=>'Routine Physical',
			'82'=>'Family Planning',
			'83'=>'Infertility',
			'84'=>'Abortion',
			'85'=>'HIV - AIDS Treatment',
			'86'=>'Emergency Services',
			'87'=>'Cancer Treatment',
			'88'=>'Pharmacy',
			'89'=>'Free Standing Prescription Drug',
			'90'=>'Mail Order Prescription Drug',
			'91'=>'Brand Name Prescription Drug',
			'92'=>'Generic Prescription Drug',
			'93'=>'Podiatry',
			'94'=>'Podiatry - Office Visits',
			'95'=>'Podiatry - Nursing Home Visits',
			'96'=>'Professional (Physician)',
			'97'=>'Anesthesiologist',
			'98'=>'Professional (Physician) Visit - Office',
			'99'=>'Professional (Physician) Visit - Inpatient',
			'A0'=>'Professional (Physician) Visit - Outpatient',
			'A1'=>'Professional (Physician) Visit - Nursing Home',
			'A2'=>'Professional (Physician) Visit - Skilled Nursing Facility',
			'A3'=>'Professional (Physician) Visit - Home',
			'A4'=>'Psychiatric',
			'A5'=>'Psychiatric - Room and Board',
			'A6'=>'Psychotherapy',
			'A7'=>'Psychiatric - Inpatient',
			'A8'=>'Psychiatric - Outpatient',
			'A9'=>'Rehabilitation',
			'AB'=>'Rehabilitation - Inpatient',
			'AC'=>'Rehabilitation - Outpatient',
			'AD'=>'Occupational Therapy',
			'AE'=>'Physical Medicine',
			'AF'=>'Speech Therapy',
			'AG'=>'Skilled Nursing Care',
			'AI'=>'Substance Abuse',
			'AJ'=>'Alcoholism Treatment',
			'AK'=>'Drug Addiction',
			'AL'=>'Optometry',
			'AM'=>'Frames',
			'AO'=>'Lenses',
			'AP'=>'Routine Eye Exam',
			'AQ'=>'Nonmedically Necessary Physical (e.g. insurance app, pilot license, employment, or school)',
			'AR'=>'Experimental Drug Therapy',
			'B1'=>'Burn Care',
			'B2'=>'Brand Name Prescription Drug - Formulary',
			'B3'=>'Brand Name Prescription Drug - Non-Formulary',
			'BA'=>'Independent Medical Evaluation',
			'BB'=>'Psychiatric Treatment Partial Hospitalization',
			'BC'=>'Day Care (Psychiatric)',
			'BD'=>'Cognitive Therapy',
			'BE'=>'Massage Therapy',
			'BF'=>'Pulmonary Rehabilitation',
			'BG'=>'Cardiac Rehabilitation',
			'BH'=>'Pediatric',
			'BI'=>'Nursery Room and Board',
			'BK'=>'Orthopedic',
			'BL'=>'Cardiac',
			'BM'=>'Lymphatic',
			'BN'=>'Gastrointestinal',
			'BP'=>'Endocrine',
			'BQ'=>'Neurology',
			'BR'=>'Eye',
			'BS'=>'Invasive Procedures',
			'BT'=>'Gynecological',
			'BU'=>'Obstetrical',
			'BV'=>'Obstetrical/Gynecological',
			'BW'=>'Mail Order Prescription Drug: Brand Name',
			'BX'=>'Mail Order Prescription Drug: Generic',
			'BY'=>'Physician Visit - Sick',
			'BZ'=>'Physician Visit - Well',
			'C1'=>'Coronary Care',
			'CA'=>'Private Duty Nursing - Inpatient',
			'CB'=>'Private Duty Nursing - Home',
			'CC'=>'Surgical Benefits - Professional (Physician)',
			'CD'=>'Surgical Benefits - Facility',
			'CE'=>'Mental Health Provider - Inpatient',
			'CF'=>'Mental Health Provider - Home',
			'CG'=>'Mental Health Facility - Inpatient',
			'CH'=>'Mental Health Facility - Outpatient',
			'CI'=>'Substance Abuse Facility - Inpatient',
			'CJ'=>'Substance Abuse Facility - Outpatient',
			'CK'=>'Screening X-ray',
			'CL'=>'Screening laboratory',
			'CM'=>'Mammogram, High Risk Patient',
			'CN'=>'Mammogram, Low Risk Patient',
			'CO'=>'Flu Vaccination',
			'CP'=>'Eyewear and Eyewear Accessories',
			'CQ'=>'Case Management',
			'DG'=>'Dermatology',
			'DM'=>'Durable Medical Equipment',
			'DS'=>'Diabetic Supplies',
			'E0'=>'Allied Behavioral Analysis Therapy',
			'E1'=>'Non-Medical Equipment (non DME)',
			'E2'=>'Psychiatric Emergency',
			'E3'=>'Step Down Unit',
			'E4'=>'Skilled Nursing Facility Head Level of Care',
			'E5'=>'Skilled Nursing Facility Ventilator Level of Care',
			'E6'=>'Level of Care 1',
			'E7'=>'Level of Care 2',
			'E8'=>'Level of Care 3',
			'E9'=>'Level of Care 4',
			'E10'=>'Radiographs',
			'E11'=>'Diagnostic Imaging',
			'E12'=>'Basic Restorative - Dental',
			'E13'=>'Major Restorative - Dental',
			'E14'=>'Fixed Prosthodontics',
			'E15'=>'Removable Prosthodontics',
			'E16'=>'Intraoral Images - Complete Series',
			'E17'=>'Oral Evaluation',
			'E18'=>'Dental Prophylaxis',
			'E19'=>'Panoramic Images',
			'E20'=>'Sealants',
			'E21'=>'Flouride Treatments',
			'E22'=>'Dental Implants',
			'E23'=>'Temporomandibular Joint Dysfunction',
			'E24'=>'Retail Pharmacy Prescription Drug',
			'E25'=>'Long Term Care Pharmacy',
			'E26'=>'Comprehensive Medication Therapy Management Review',
			'E27'=>'Targeted Medication Therapy Management Review',
			'E28'=>'Dietary/Nutritional Services',
			'EA'=>'Preventive Services',
			'EB'=>'Specialty Pharmacy',
			'EC'=>'Durable Medical Equipment New',
			'ED'=>'CAT Scan',
			'EE'=>'Ophthalmology',
			'EF'=>'Contact Lenses',
			'GF'=>'Generic Prescription Drug - Formulary',
			'GN'=>'Generic Prescription Drug - Non-Formulary',
			'GY'=>'Allergy',
			'IC'=>'Intensive Care',
			'MH'=>'Mental Health',
			'NI'=>'Neonatal Intensive Care',
			'ON'=>'Oncology',
			'PE'=>'Positron Emission Tomography (PET) Scan',
			'PT'=>'Physical Therapy',
			'PU'=>'Pulmonary',
			'RN'=>'Renal',
			'RT'=>'Residential Psychiatric Treatment',
			'SMH'=>'Serious Mental Health',
			'TC'=>'Transitional Care',
			'TN'=>'Transitional Nursery Care',
			'UC'=>'Urgent Care'
			);
			
	// insurance type codes
	$code271['EB04'] = array(			
			'D'=>'Disability',
			'12'=>'Medicare Secondary Working Aged Beneficiary or Spouse with Employer Group Health Plan',
			'13'=>'Medicare Secondary End-Stage Renal Disease Beneficiary in the 12 month coordination period with an employer\'s group health plan',
			'14'=>'Medicare Secondary, No-fault Insurance including Auto is Primary',
			'15'=>'Medicare Secondary Worker\'s Compensation',
			'16'=>'Medicare Secondary Public Health Service (PHS)or Other Federal Agency',
			'41'=>'Medicare Secondary Black Lung',
			'42'=>'Medicare Secondary Veteran\'s Administration',
			'43'=>'Medicare Secondary Disabled Beneficiary Under Age 65 with Large Group Health Plan (LGHP)',
			'47'=>'Medicare Secondary, Other Liability Insurance is Primary',
			'AP'=>'Auto Insurance Policy',
			'C1'=>'Commercial',
			'CO'=>'Consolidated Omnibus Budget Reconciliation Act (COBRA)',
			'CP'=>'Medicare Conditionally Primary',
			'DB'=>'Disability Benefits',
			'EP'=>'Exclusive Provider Organization',
			'FF'=>'Family or Friends',
			'GP'=>'Group Policy',
			'HM'=>'Health Maintenance Organization (HMO)',
			'HN'=>'Health Maintenance Organization (HMO) - Medicare Risk',
			'HS'=>'Special Low Income Medicare Beneficiary',
			'IN'=>'Indemnity',
			'IP'=>'Individual Policy',
			'LC'=>'Long Term Care',
			'LD'=>'Long Term Policy',
			'LI'=>'Life Insurance',
			'LT'=>'Litigation',
			'MA'=>'Medicare Part A',
			'MB'=>'Medicare Part B',
			'MC'=>'Medicaid',
			'MH'=>'Medigap Part A',
			'MI'=>'Medigap Part B',
			'MP'=>'Medicare Primary',
			'OT'=>'Other',
			'PE'=>'Property Insurance - Personal',
			'PL'=>'Personal',
			'PP'=>'Personal Payment (Cash - No Insurance)',
			'PR'=>'Preferred Provider Organization (PPO)',
			'PS'=>'Point of Service (POS)',
			'QM'=>'Qualified Medicare Beneficiary',
			'RP'=>'Property Insurance - Real',
			'SP'=>'Supplemental Policy',
			'TF'=>'Tax Equity Fiscal Responsibility Act (TEFRA)',
			'WC'=>'Workers Compensation',
			'WU'=>'Wrap Up Policy'
			);
			
	// time period qualifier
	$code271['EB06'] = array(			
			'1'=>'Month',
			'2'=>'Year',
			'6'=>'Hour',
			'7'=>'Day',
			'21'=>'Years',
			'22'=>'Service Year',
			'23'=>'Calendar Year',
			'24'=>'Year to Date',
			'25'=>'Contract',
			'26'=>'Episode',
			'27'=>'Visit',
			'28'=>'Outlier',
			'29'=>'Remaining',
			'30'=>'Exceeded',
			'31'=>'Not Exceeded',
			'33'=>'Lifetime Remaining',
			'34'=>'Month',
			'35'=>'Week',
			'36'=>'Admission',
			'A'=>'Hourly Appurtenance Units (Hours of Enhancement/Addition to Equipment)',
			'D'=>'Daily Time Units',
			'H'=>'Hourly Time Units',
			'O'=>'Other Time Units'
			);
			
	// quantity type qualifier
	$code271['EB09'] = array(
			'8H'=>'Minimum',
			'99'=>'Quantity Used',
			'CA'=>'Covered - Actual',
			'CE'=>'Covered - Estimated',
			'D3'=>'Number of Co-insurance Days',
			'DB'=>'Deductible Blood Units',
			'DY'=>'Days',
			'HS'=>'Hours',
			'LA'=>'Life-time Reserve - Actual',
			'LE'=>'Life-time Reserve - Estimated',
			'M2'=>'Maximum',
			'MN'=>'Month',
			'P6'=>'Number of Services or Procedures',
			'QA'=>'Quantity Approved',
			'S7'=>'Age, High Value',
			'S8'=>'Age, Low Value',
			'VS'=>'Visits',
			'YY'=>'Years'
			);
		
	// product/service id qualifier
	$code271['EB13'] = array(
			'AD'=>'American Dental Association Codes',
			'CJ'=>'CPT Codes',
			'HC'=>'HCPCS Codes',
			'ID'=>'ICD-9-CM - Procedure',
			'IV'=>'Home Infusion EDI Coalition (HIEC) Product/Service Code',
			'N4'=>'National Drug Code in 5-4-2 Format',
			'ZZ'=>'Mutually Defined'
			);	
			
	// quantity qualifier - unit or basis copde
	$code271['HSD'] = array(	
			'DY'=>'Days',
			'FL'=>'Units',
			'HS'=>'Hours',
			'MN'=>'Month',
			'VS'=>'Visit',
			'DA'=>'Days',
			'MO'=>'Months',
			'WK'=>'Week',
			'YR'=>'Years'			
			);

	// delivery frequency code
	$code271['HSD07'] = array(
			'1'=>'1st Week of the Month',
			'2'=>'2nd Week of the Month',
			'3'=>'3rd Week of the Month',
			'4'=>'4th Week of the Month',
			'5'=>'5th Week of the Month',
			'6'=>'1st & 3rd Weeks of the Month',
			'7'=>'2nd & 4th Weeks of the Month',
			'8'=>'1st Working Day of Period',
			'9'=>'Last Working Day of Period',
			'A'=>'Monday through Friday',
			'B'=>'Monday through Saturday',
			'C'=>'Monday through Sunday',
			'D'=>'Monday',
			'E'=>'Tuesday',
			'F'=>'Wednesday',
			'G'=>'Thursday',
			'H'=>'Friday',
			'J'=>'Saturday',
			'K'=>'Sunday',
			'L'=>'Monday through Thursday',
			'M'=>'Immediately',
			'N'=>'As Directed',
			'O'=>'Daily Mon. through Fri.',
			'P'=>'1/2 Mon. & 1/2 Thurs.',
			'Q'=>'1/2 Tues. & 1/2 Thurs.',
			'R'=>'1/2 Wed. & 1/2 Fri.',
			'S'=>'Once Anytime Mon. through Fri.',
			'T'=>'1/2 Tue. & 1/2 Fri.',
			'U'=>'1/2 Mon. & 1/2 Wed.',
			'V'=>'1/3 Mon., 1/3 Wed., 1/3 Fri.',
			'W'=>'Whenever Necessary',
			'X'=>'1/2 By Wed., Bal. By Fri.',
			'Y'=>'None (Also Used to Cancel or Override a Previous Pattern)',
			'Z'=>'Mutually Defined',
			'SA'=>'Sunday, Monday, Thursday, Friday, Saturday',
			'SB'=>'Tuesday through Saturday',
			'SC'=>'Sunday, Wednesday, Thursday, Friday, Saturday',
			'SD'=>'Monday, Wednesday, Thursday, Friday, Saturday',
			'SG'=>'Tuesday through Friday',
			'SL'=>'Monday, Tuesday and Thursday',
			'SP'=>'Monday, Tuesday and Friday',
			'SX'=>'Wednesday and Thursday',
			'SY'=>'Monday, Wednesday and Thursday',
			'SZ'=>'Tuesday, Thursday and Friday',
			);

	// Ship/Delivery Pattern Time Code
	$code271['HSD08'] = array(			
			'A'=>'1st Shift (Normal Working Hours)',
			'B'=>'2nd Shift',
			'C'=>'3rd Shift',
			'D'=>'A.M.',
			'E'=>'P.M.',
			'F'=>'As Directed',
			'G'=>'Any Shift',
			'Y'=>'None (Also Cancel or Override Previous)',
			'Z'=>'Mutually Defined'
			);
			
	//place of service  --code source 237
	$code271['POS'] = array(				
			'01'=>'Pharmacy',
			'02'=>'Unassigned',
			'03'=>'School',
			'04'=>'Homeless Shelter',
			'05'=>'Indian Health Service Free-standing Facility',
			'06'=>'Indian Health Service Provider-based Facility',
			'07'=>'Tribal 638 Free-standing Facility',
			'08'=>'Tribal 638 Provider-based Facility',
			'09'=>'Prison/Correctional Facility',
			'11'=>'Office',
			'12'=>'Home ',
			'13'=>'Assisted Living Facility',
			'14'=>'Group Home',
			'15'=>'Mobile Unit',
			'16'=>'Temporary Lodging',
			'17'=>'Walk-in Retail Health Clinic',
			'20'=>'Urgent Care Facility',
			'21'=>'Inpatient Hospital',
			'22'=>'Outpatient Hospital',
			'23'=>'Emergency Room - Hospital',
			'24'=>'Ambulatory Surgical Center',
			'25'=>'Birthing Center',
			'26'=>'Military Treatment Facility',
			'31'=>'Skilled Nursing Facility',
			'32'=>'Nursing Facility',
			'33'=>'Custodial Care Facility',
			'34'=>'Hospice',
			'41'=>'Ambulance - Land',
			'42'=>'Ambulance - Air or Water',
			'49'=>'Independent Clinic',
			'50'=>'Federally Qualified Health Center',
			'51'=>'Inpatient Psychiatric Facility',
			'52'=>'Psychiatric Facility-Partial Hospitalization',
			'53'=>'Community Mental Health Center',
			'54'=>'Intermediate Care Facility/Mentally Retarded',
			'55'=>'Residential Substance Abuse Treatment Facility',
			'56'=>'Psychiatric Residential Treatment Center',
			'57'=>'Non-residential Substance Abuse Treatment Facility',
			'60'=>'Mass Immunization Center',
			'61'=>'Comprehensive Inpatient Rehabilitation Facility',
			'62'=>'Comprehensive Outpatient Rehabilitation Facility',
			'71'=>'Public Health Clinic',
			'72'=>'Rural Health Clinic',
			'81'=>'Independent Laboratory',
			'99'=>'Other Place of Service'
			);


			
	// insurance relationship code  
	$code271['INS02'] = array(	
			'18'=>'self',
			'01'=>'spouse',
			'19'=>'child', 
			'20'=>'employee',
			'21'=>'unknown'
			);
	//
	if ( array_key_exists($segment_id, $code271) ) {
		$val = (isset($code271[$segment_id][$code]) ) ? $code271[$segment_id][$code] : "code $code not found";
	} else {
		$val = "segment $segment_id codes ($code) not available";
	}
	//
	return $val;
}
			



function ibr_271_toarray($ar_segments) {
	// @param array $ar_segments -- array produced by csv_x12segments()
	//
	if (is_array($ar_segments) && count($ar_segments['segments']) ) {
		$fdir = dirname($ar_segments['path']);
		$fname = basename($ar_segments['path']);
		$fmtime = date('Ymd', filemtime($ar_segments['path']) );
		//
		$ar_271segs = $ar_segments['segments'];
		//
		$elem_d = $ar_segments['delimiters']['e'];
		$rep_d = $ar_segments['delimiters']['r'];
		$sub_d = $ar_segments['delimiters']['s'];
	} else {
		csv_edihist_log("ibr_271toarray: error invalid segments array");
		return FALSE;
	}
	// loop 0 		ST BHT
	// loop 2000A	HL AAA 
	// loop 2100A 	NM1 PER AAA
	// loop 2000B 	HL
	// loop 2100B 	NM1 REF N3 N4 AAA PRV
	// loop 2000C 	HL TRN
	// loop 2100C 	NM1 REF N3 N4 AAA PRV DMG INS HI DTP MPI
	// loop 2110C 	EB HSD REF DTP AAA MSG
	// loop 2115C 	III LS 
	// loop 2120C 	NM1 N3 N4 PER PRV LE
	// loop 2000D 	HL TRN
	// loop 2100D 	NM1 REF N3 N4 AAA PRV DMG INS HI DTP MPI
	// loop 2110D 	EB HSD REF DTP AAA MSG
	// loop 2115D 	III LS
	// loop 2120D 	NM1 N3 N4 PER PRV LE 
	// loop trailer SE
	//
	$ar_elig = array();
	$B = -1;
	//
	foreach($ar_271segs as $seg_str) {
		$seg = explode($elem_d, $seg_str);
		$st_ct++;
		//
		// expectation is multiple ST-SE with a one or more BHT in each, so index on BHT instead of ST
		// if multiple ISA--IEA blocks, then an $I for ISA index may need to be inserted, with a transaction number
		//
		// identify loops
		if ($seg[0]=="ST") { $loopid = "0"; $st_ct=1; }
		if ($seg[0]=="BHT") { $loopid = "0"; $B++; $hl_ct = 0; }
		if ($seg[0]=="HL") {
			//if ($seg[1] == '0022' && ($seg[2]=='06' || $seg[2]=='11')) { $loopid = "2000A"; }
			if ($seg[3] == '20')  { $loopid = "2000A"; }
			if ($seg[3] == '21')  { $loopid = "2000B"; }
			if ($seg[3] == '22')  { $loopid = "2000C"; }
			if ($seg[1] == '23')  { $loopid = "2000D"; }
			//
			$haschild = ($seg[4]=="1") ? TRUE : FALSE;
			$hl_ct++;
		}
		
		if ($seg[0]=="NM1") {
			// 1P Provider 2B Third-Party Administrator 36 Employer 80 Hospital FA Facility GP Gateway Provider P5 Pan Sponsor PR Payer
			//if (strpos("1P|2B|36|80|FA|GP|P5|PR", $seg[1]) { $loopid = "2100B"; }  // information receiver name
			if ($loopid == "2000A") { $loopid = "2100A"; } 
			if ($loopid == "2000B") { $loopid = "2100B"; }
			// allow for repeat loops
			if (($loopid == "2000C" || $loopid = "2100C") && $seg[1] == "IL") { $loopid = "2100C"; $eb_2110C_ct = 0; }
			if (($loopid == "2000D" || $loopid = "2100D") && $seg[1] == "03") { $loopid = "2100D"; $eb_2110D_ct = 0; }
			// EB III or LS should occur before this, so this should never apply
			if ($loopid == "2110D" && 
			    strpos("|13|1I|1P|2B|36|73|FA|GP|GW|I3|IL|LR|OC|P3|P4|P5|PR|PRP|SEP|TTP|VER|VN|VY|X3|Y2", $seg[1]) ) { $loopid = "2120D"; }
		}
		
		if ($seg[0]=="EB") { $loopid = "2110" . substr($loopid, -1); }  // loopid should now be 2110C or 2110D
		if ($seg[0]=="III") { $loopid = "2115" . substr($loopid, -1); }
		if ($seg[0]=="LS") { $loopid = $seg[1] . substr($loopid, -1); } // LS begins inner loop 2120C or 2120D
		if ($seg[0]=="LE") { $loopid = "2110" . substr($loopid, -1); }
		//
		// loops are identified, now arrange the data into an array -- concept is [BHT][idx][loop]
		//
		$ar_elig['BHT'][$B] = array();
		$L = $loopid;
		if ( !array_key_exists($L, $ar_elig['BHT'][$B]) ) { $ar_elig['BHT'][$B][$L] = array(); }
		//
		//
		if ($seg[0]=="ST") {
			$ar_elig['BHT'][$B] = array();
			$ar_elig['BHT'][$B]['ST01'] = $seg[1];
			$ar_elig['BHT'][$B]['FILE'] = $fname;
			continue;
		}
		
		if ($seg[0]=="BHT") {
			// begin heirarchical transaction -- basics: purpose, referenceID, date, time ['ST01'] ['BHT01']['BHT02']['BHT03']['BHT04']['BHT05']
			$ar_elig['BHT'][$B]['BHT01'] = $seg[1];  // expect '0022' for normal response
			$ar_elig['BHT'][$B]['BHT02'] = $seg[2];	 // purpose code '06' confirmation '11' response
			$ar_elig['BHT'][$B]['BHT03'] = $seg[3];	 // reference ID
			$ar_elig['BHT'][$B]['BHT04'] = $seg[4];	 // date CCYYMMDD
			$ar_elig['BHT'][$B]['BHT05'] = $seg[5];	 // time HHMMSSDD
			//$ar_elig['BHT'][$B]['BHT06'] = $seg[6];	 // transaction type -- not used
			//
			continue;
		}
		
		if ($seg[0]=="GS") {
			$ar_elig['BHT'][$B]['GS04'] = $seg[4]; 	// 271 response date  CCYYMMDD
			continue;
		}
		
		if ($seg[0]=="AAA") {
			// AAA segment is returned in case of errors
			$rej_str = $seg[1] . ': ' . ibr_271_codes('AAA', $seg[3]) . ' -- ' . ibr_271_codes('AAA', $seg[4]);
			if ($loopid == "2000A" || $loopid == "2100A" ) {
				$ar_elig['BHT'][$B]['AAA'] = $rej_str;
			} else {
				$ar_elig['BHT'][$B][$L]['AAA'] = $rej_str;
			}
			//
			continue;
		}
		
		if ($seg[0]=="NM1") {
			if ($loopid == "2100A") {
				// information source name 
				$ar_elig['BHT'][$B][$L]['NM101'] = ibr_271_codes('NM101', $seg[1]);
				$ar_elig['BHT'][$B][$L]['NM103'] = ($seg[2] == "2") ? $seg[3] : $seg[3] .', ' . $seg[4];
				$ar_elig['BHT'][$B][$L]['NM109'] = isset($seg[9]) ? $seg[9] : '';
			} elseif ($loopid == "2100B") {
				$ar_elig['BHT'][$B][$L]['NM103'] = ($seg[2] == "2") ? $seg[3] : $seg[3] .', ' . $seg[4];
				$ar_elig['BHT'][$B][$L]['NM109'] = isset($seg[9]) ? $seg[9] : '';
			} elseif ($loopid == "2100C" || $loopid == "2100D") {
				$ar_elig['BHT'][$B][$L]['NM103'] = ($seg[2] == "2") ? $seg[3] : $seg[3] .', ' . $seg[4];
				if ( isset($seg[9]) ) { $ar_elig['BHT'][$B][$L]['NM109'] = $seg[8] .': ' .$seg[9]; } 			// policy or patient id
				if ( isset($seg[10]) ) { $ar_elig['BHT'][$B][$L]['NM110'] = ibr_271_codes('NM110', $seg[10]); } // relationship
			} elseif ($loopid == "2120C" || $loopid == "2120D" ) {
				// person (provider) involved in the eligibility
				$ar_elig['BHT'][$B][$L]['NM101'] = ibr_271_codes("NM101", $seg[1]);
				$ar_elig['BHT'][$B][$L]['NM103'] = ($seg[2] == "2") ? $seg[3] : $seg[3] .', ' . $seg[4];
				if ( isset($seg[9]) ) { $ar_elig['BHT'][$B][$L]['NM109'] = $seg[8] .': ' .$seg[9]; } 			// policy or patient id
				if ( isset($seg[10]) ) { $ar_elig['BHT'][$B][$L]['NM110'] = ibr_271_codes('NM110', $seg[10]); } // relationship				
			}
			//
			continue;
		}
		
		if ($seg[0]=="PER"  && $loopid == "2100A") {
			$ar_elig['BHT'][$B][$L]['PER02'] = isset($seg[2]) ? $seg[2] : '';
			$ar_elig['BHT'][$B][$L]['PER03'] = isset($seg[3]) ? $seg[3] : '';
			$ar_elig['BHT'][$B][$L]['PER04'] = isset($seg[4]) ? $seg[4] : '';
			//
			continue;			
		}
		
		if ($seg[0] == "REF") {
			// since this segment can repeat, create a subarray under the 'REF' key
			$ref_str = ibr_271_codes("REF", $seg[1]);
			$ar_elig['BHT'][$B][$L]['REF'][] = $seg[2] . ' ' . $ref_str;
			continue;
		}
		
		if ($seg[0] == "TRN") {
			// TRN in loop 2000C or 2000D, not both
			// trace numbers can be tricky.  If TRN01 == 2, the original 270 trace number is returned
			// do we have an originating company id? if so,it distinguishes TRN segments
			// if ($seg[3] == "originating company id") -- expect our trace in TRN02
			// likewise, if ($seg[3] == "clearinghouse id") -- clearinghouse trace in TRN02
			$ar_elig['BHT'][$B][$L]['TRN01'] = $seg[1];
			if ($seg[1] == "2") {
				$ar_elig['BHT'][$B][$L]['TRN02'] = $seg[2];   // TRN02  TRACE holds the id number originally sent
				// department or subdivision
				if ( isset($seg[4]) ) { $ar_elig['BHT'][$B][$L]['TRN04'] = $seg[4]; } 
			} else {
				$ar_elig['BHT'][$B][$L]['TRN02'] = $seg[2];   // TRN02 holds a number by the sender/clearinghouse
			}
			$ar_elig['BHT'][$B][$L]['TRN03'] = isset($seg[3]) ? $seg[3] : '';
			continue;
		}
		
		if ($seg[0] == "N3") {
			// loop 2100C subscriber is patient, ins co uses address, or there is a rejection due to incorrect address information
			$ar_elig['BHT'][$B][$L]['N301'] = $seg[1];
			if ( isset($seg[2]) ) { $ar_elig['BHT'][$B][$L]['N302'] = $seg[2]; }
			continue;
		}
		
		if ($seg[0] == "N4") {
			//loop 2100C subscriber is patient, ins co uses address, or there is a rejection due to incorrect address information
			//
			$ar_elig['BHT'][$B][$L]['N401'] = $seg[1];
			$ar_elig['BHT'][$B][$L]['N402'] = $seg[2];
			$ar_elig['BHT'][$B][$L]['N403'] = $seg[3];
			continue;
		}
			
		if ($seg[0] == "PRV") {
			$ar_elig['BHT'][$B][$L]['PRV01'] = ibr_271_codes("PRV", $seg[1]);
			if ( isset($seg[2]) ) { 
				// if PRV02 then PRV03
				$ar_elig['BHT'][$B][$L]['PRV02'] = $seg[2];
				$ar_elig['BHT'][$B][$L]['PRV03'] = $seg[3]; 
			}
			continue;
		}
		
		if ($seg[0] == "DMG") {
			$ar_elig['BHT'][$B][$L]['DMG01'] = $seg[1];
			$ar_elig['BHT'][$B][$L]['DMG02'] = $seg[2];
			$ar_elig['BHT'][$B][$L]['DMG03'] = $seg[3];
			continue;
		}
		
		if ($seg[0] == "INS") {
			$ar_elig['BHT'][$B][$L]['INS01'] = $seg[1];  	// Y/N Yes/No whether subscriber
			//  18 self 01 spouse 19 child 20 employee 21 unknown
			$ar_elig['BHT'][$B][$L]['INS02'] = ibr_271_codes("INS02", $seg[2]);  	// individual relationship code
			if ( isset($seg[3]) ) { 
				// possibly create some type of alert to show that returned information is different from what was submitted
				$ar_elig['BHT'][$B][$L]['INS03'] = $seg[3]; 	// maintenance type code  001
				$ar_elig['BHT'][$B][$L]['INS04'] = $seg[4]; 	// maintenance reason code  25 change in identifying details
			}
			// birth order if twins, triplets, etc.
			if ( isset($seg[17]) ) { $ar_elig['BHT'][$B][$L]['INS17'] = $seg[17]; }
			continue;
		}
		
		if ($seg[0] == "HI") {
			$hi_str = "";
			for ($i=0; $i<count($seg); $i++) {
				// could go  on to HI08  ex: BK:40101*BF:2724
				$dt = substr($seg[$i], 0, strpos($seg[$i]), $sub_d);
				$dc = substr($seg[$i], strpos($seg[$i], $sub_d)+1);
				$hi_str .= $dt . $sub_d . sprintf("%1$ 3d.%2$02d", substr($dc,0,3), substr($dc,3)) .' ';
			}
			$ar_elig['BHT'][$B][$L]['HI'] = $hi_str; 
			//	
			continue;
		}
		
		if ($seg[0] == "DTP") {
			// segment can repeat, set up an indexed key
			$dtp_str = ibr_271_codes("DTP", $seg[1]);
			$dtp_str .= ' ' . $seg[3];
			//
			$ar_elig['BHT'][$B][$L]['DTP'][] = $dtp_str;
			continue;
		}
		
		if ($seg[0] == "MPI") {
			if (isset($seg[1])) { $ar_elig['BHT'][$B][$L]['MPI01'] = ibr_271_codes("MPI", $seg[1]); }
			if (isset($seg[2])) { $ar_elig['BHT'][$B][$L]['MPI02'] = ibr_271_codes("MPI", $seg[2]); }
			// prepended 'SB' to service affiliation codes for uniqueness
			if (isset($seg[3])) { $ar_elig['BHT'][$B][$L]['MPI03'] = ibr_271_codes("MPI", 'SB'.$seg[3]); }
			if (isset($seg[4])) { $ar_elig['BHT'][$B][$L]['MPI04'] = $seg[4]; }
			if (isset($seg[5])) { $ar_elig['BHT'][$B][$L]['MPI05'] = ibr_271_codes("MPI", $seg[5]); }
			//
			if (isset($seg[7])) { $ar_elig['BHT'][$B][$L]['MPI07'] = $seg[7]; }
			continue;
		}

		if ($seg[0] == "EB") {	
			// this one is complicated -- repetitions, code references, and subelements, references to subordinate loops
			// create a concatenated string of the descriptions and a subarray under the 'EB' key 
			//   this may not really be adequate, but after testing a better layout should be apparent
			// if loop 2100C subscriber is patient
			$strcov = "";
			$strplan = "";
			$strfin = "";
			$strauth = "";
			$strmed = "";
			//
			$ebarr = array();
			if (isset($seg[1])) { $strcov .= ibr_271_codes("EB01", $seg[1]); }            // eligibility or benefit information code
			if (isset($seg[2])) { $strcov .= ' | ' . ibr_271_codes("EB02", $seg[2]); }   // coverage level i.e. individual, family, etc
			$ebarr['COV'] = $strcov;
			//
			if (isset($seg[3])) {
				// here we have a possibly long list of repetitions of different service type codes
				// we are probably interested in code 1 "medical care" and codes 98-A3, BY, BZ, "physician visit" 4, 5 are x-ray and lab
				/* ********* important *****************************************************************
				 * This filter needs to be checked in practice so that necessary information is not lost
				 * perhaps the filter should not be used at all, but it can shorten output to relevant applicable benefit listing
				 * this filter does not affect other elements in the segment
				 * ********* */
				if ( strpos($seg[3], $rep_d) ) {
					$relevant = array('1', '96', '4', '5', '98', '99', 'A0', 'A1', 'A2', 'A3', 'BY','BZ');
					$eb03 = explode($rep_d, $seg[3]);
					if ( is_array($eb03) ) {
						foreach($eb03 as $st) {
							if (in_array($st, $relevant)) { 
								$ebarr['EB03'][] = ibr_271_codes("EB03", $st); 
							}
						}
					}
				} else {
					$ebarr['EB03'][] = ibr_271_codes("EB03", $seg[3]);
				}
				
			}
			if (isset($seg[5])) { $strplan .= $seg[5] . ' '; } 							// plan description e.g. "Super-Duper NadaDinero"
			if (isset($seg[4])) { $strplan .= ibr_271_codes("EB04", $seg[4]) . ' '; }   // insurance type
			$ebarr['PLAN'] = $strplan;
			                           
			if (isset($seg[6])) { $strfin .= ibr_271_codes("EB06", $seg[6]) . ' '; }    // time period e.r. Visit, "Calendar Year", "Lifetime"
			if (isset($seg[7])) { $strfin .= sprintf("%01.2f", $seg[7]) . ' '; } 	    // dollar amount
			if (isset($seg[8])) { $strfin .= sprintf("% 1.0f%%", $seg[8]) . ' '; } 	    // percentage
			if (isset($seg[9])) { $stfinr .= ibr_271_codes("EB09", $seg[9]) . ' '; }    // quantity qualifier e.g. days, visits, lifetime reserve, 
			if (isset($seg[10])) { $strfin .= $seg[10] . ' '; } 						// quantity for eb09
			$ebarr['FIN'] = $strfin;
			
			// authorization required ?
			if (isset($seg[11])) { 
				if ($seg[11] == "Y") { 
					$strauth .= 'auth/cert required'; 
				} elseif ($seg[11] == "N") {
					$strauth .= 'auth/cert not required'; 
				} else {
					$strauth .= 'auth/cert requirement unknown';
				}
			}
			// difference for in or out of network
			if (isset($seg[12])) { 
				if ($seg[12] == "Y") {
					$strauth .= '  In-Plan-Network'; 
				} elseif ($seg[12] == "N") {
					$strauth .= '  Out-Of-Plan-Network'; 
				} elseif ($seg[12] == "W") {
					$strauth .= '  Same for In or Out of Plan-Network';
				} else {
					$strauth .= '  Unknown for Plan-Network';
				} 
			}			
			$ebarr['AUTH'] = $strauth;
			
			// EB13 is used only when EB03 is empty -- composit medical procedure identifier 
			//  expect a list of eligible procedures (repetitions of composite elements, with up to 8 parts in the composition), 
			//  but treat it as a string, extracting the code type for labeling (CPT, ICD-9), and concatenate			
			if (isset($seg[13])) { 
				if ( strpos($seg[13], $rep_d) ) {						
					$eb13 = explode($rep_d, $seg[13]);
					if ( is_array($eb13) ) {
						foreach($eb13 as $sv) {
							if (strpos($sv, $sub_d)) {
								$strmed .= ibr_271_codes("EB13", substr($sv, 0, strpos($sv, $sub_d)-1));
								$strmed .= ' ' . substr($sv, strpos($sv, $sub_d)+1) . ' ' ;
							} else {
								$strmed .= $sv .' ';
							} 
						}
						$strmed .= trim($strmed);
					}
				} else {
					// no repetitions of the element
					$strmed .= ibr_271_codes("EB13", substr($sv, 0, strpos($sv, $sub_d))) . ' ' . substr($sv, strpos($sv, $sub_d)+1);
				}
			}
			// diagnosis code pointers -- the index position of the HI segment in loop 2100C or 2100D
			if (isset($seg[14])) { 
				$strmed = "Diagnosis Code Pointers: " . $seg[14];
			}			
			$ebarr['MED'] = $strmed;
			
			/* ********* using concatenated strings per information topics instead of segment id *************			
			if (isset($seg[4]) { $ebarr['EB04'] = ibr_271_codes("EB04", $seg[4]); }  // insurance type
			if (isset($seg[5]) { $ebarr['EB05'] = $seg[5]; }   						 // plan description e.g. "Super-Duper NadaDinero"
			if (isset($seg[6]) { $ebarr['EB06'] = ibr_271_codes("EB06", $seg[6]); }  // time period e.r. Visit, "Calendar Year", "Lifetime"
			if (isset($seg[7]) { $ebarr['EB07'] = sprintf("%01.2f", $seg[7]); } 	 // dollar amount
			if (isset($seg[8]) { $ebarr['EB08'] = sprintf("% 1.0f%%", $seg[8]); } 	 // percentage
			if (isset($seg[9]) { $ebarr['EB09'] = ibr_271_codes("EB09", $seg[9]); }  // quantity qualifier e.g. days, visits, lifetime reserve, 			
			if (isset($seg[10]) { $ebarr['EB10'] = $seg[10]; } 						 // quantity for eb09
			// authorization required ?
			if (isset($seg[11]) { 
				if ($seg[11] == "Y") { 
					$ebarr['EB11'] = 'auth/cert required'; 
				} elseif ($seg[11] == "N") {
					$ebarr['EB11'] = 'auth/cert not required'; 
				} else {
					$ebarr['EB11'] = 'auth/cert requirement unknown';
				}
			}
			// difference for in or out of network
			if (isset($seg[12]) { 
				if ($seg[12] == "Y") {
					$ebarr['EB12'] = 'In-Plan-Network'; 
				} elseif ($seg[12] == "N") {
					$ebarr['EB12'] = 'Out-Of-Plan-Network'; 
				} elseif ($seg[12] == "W") {
					$ebarr['EB12'] = 'Same for In/Out of Plan-Network';
				} else {
					$ebarr['EB12'] = 'Unknown for In/Out of Plan-Network';
				} 
			}
			// EB13 is used only when EB03 is empty -- composit medical procedure identifier
			//  expect a list of eligible procedures (repetitions of composite elements), 
			//  but treat it as a string, extracting the code type for labeling (CPT, ICD-9), and concatenate			
			if (isset($seg[13]) { 
				if ( strpos($seg[13], $rep_d) ) {						
					$eb13 = explode($rep_d, $seg[13]);
					if ( is_array($eb13) ) {
						foreach($eb13 as $sv) {
							if (strpos($sv, $sub_d) {
								$str_eb .= ibr_271_codes("EB13", substr($sv, 0, strpos($sv, $sub_d)-1));
								$str_eb .= ' ' . substr($sv, strpos($sv, $sub_d)+1) . ' ' ;
							} else {
								$str_eb .= $sv .' ';
							} 
						}
						$ebarr['EB13'] = trim($str_eb);
					}
				} else {
					$ebarr['EB13'] = ibr_271_codes("EB13", substr($sv, 0, strpos($sv, $sub_d)-1)) . ' ' . substr($sv, strpos($sv, $sub_d)+1);
				}
			}
			// diagnosis code pointers -- the index position of the HI segment in loop 2100C or 2100D
			if (isset($seg[14]) { 
				$ebarr['EB14'] = "Diagnisis Code Pointers: " . $seg[14];
			}
			* **************************** */			
			//
			// OK, we have the EB segment safely lodged in $ebarr, so add it as a subarray of 'EB'
			$ar_elig['BHT'][$B][$L]['EB'][] = $ebarr;
			//
			continue;
			//
		} // end if ($seg[0] == 'EB')		
						
		
		if ($seg[0] == "HSD") {
			// health care services delivery
			// qualifier followed by count
			// create a concatenated string
			//
			$str_hsd = "";
			if (isset($seg[2])) { $str_hsd .= $seg[2] . ' ' . ibr_271_codes("HSD", $seg[1]); }
			if (isset($seg[4])) { $str_hsd .= ' ' . $seg[4] . ' per ' . ibr_271_codes("EB06", $seg[3]); } 	
			if (isset($seg[6])) { $str_hsd .= ' for ' . $seg[6] . ' ' . ibr_271_codes("EB06", $seg[5]); }
			if (isset($seg[7])) { $str_hsd .= ' ' . ibr_271_codes("HSD07", $seg[7]); }
			if (isset($seg[8])) { $str_hsd .= ' ' . ibr_271_codes("HSD08", $seg[8]); }
			//
			$ar_elig['BHT'][$B][$L]['HSD'] = $str_hsd;
			//
			continue;
		}
		
		if ($seg[0] == "MSG") {
			if (isset($seg[1])) { $ar_elig['BHT'][$B][$L]['MSG'] = $seg[2]; }
			continue;
		}
		
		if ($seg[0] == "III") {
			// the "nature of injury" codes are reported here if used in the request, codes not available
			// service location information can also be reported
			//  again, create a concatenated string
			$iii_str = "";
			if (isset($seg[1]) && $seg[1]=="ZZ") { 
				$iii_str .= ibr_271_codes("POS", $seg[2]);
			} elseif (isset($seg[1])) {
				$iii_str .= $seg[1] . ' ' . $seg[2];
			}
			if (isset($seg[3])) { $iii_str .= ' ' . $seg[3]; }
			if (isset($seg[4])) { $iii_str .= ' ' . $seg[4]; }
			//
			$ar_elig['BHT'][$B][$L]['III'] = $iii_str;
			continue;
		}
		//
	}
	//
	return $ar_elig;
}
	

function ibr_271_csv_data($ar_277_vals) {
	// @param array $ar_277_vals -- the array produced by ibr_277_toarray()
	//
	// we will take the bare minimum for a 'claim' type record
	// patient name, trace number (pid -- or something), ST number, payer_name, file trace, file name, error message
	//  concept is to point to a rendition of the file rather than try to distill 271 response
	//
	// files csv will also be minimal record
	
	
	/* *** code goes here **** */
	$csv271['file']['filename'] = $ar_elig['BHT']['FILE'];
	$ar_elig['BHT']['controlnum'] = 
	
	
	
}


function ibr_271html($ar_271) {
	// @param array $ar_271vals -- the multi-dimensional array from ibr_271toarray()
	//
	// render the array into an html table
	//
	if ( !isarray($ar_271) ) { return false; }
	//
	// expect array like $ar_271vals['BHT'][$i][$loop][keys]
	//
	// table
	// row 1: if (isset($ar_271vals['BHT']['2000A']['AAA']) -- rejected
	// loop 0 		ST BHT
	// loop 2000A	HL AAA 
	// loop 2100A 	NM1 PER AAA
	// loop 2000B 	HL
	// loop 2100B 	NM1 REF N3 N4 AAA PRV
	// loop 2000C 	HL TRN
	// loop 2100C 	NM1 REF N3 N4 AAA PRV DMG INS HI DTP MPI
	// loop 2110C 	EB HSD REF DTP AAA MSG
	// loop 2115C 	III LS 
	// loop 2120C 	NM1 N3 N4 PER PRV LE
	// loop 2000D 	HL TRN
	// loop 2100D 	NM1 REF N3 N4 AAA PRV DMG INS HI DTP MPI
	// loop 2110D 	EB HSD REF DTP AAA MSG
	// loop 2115D 	III LS
	// loop 2120D 	NM1 N3 N4 PER PRV LE 
	// loop trailer SE
	//	
	$html_str = "";
	//
	// header lables here  (6 columns)
	$hd_str = "";
	//
	//
	$idx = 0;
	foreach ($ar_271['BHT'] as $b) {
		
		if (isset($b['ST01'])) {
			// basic information  basics: purpose, referenceID, date, time ['ST01'] ['BHT01']['BHT02']['BHT03']['BHT04']['BHT05']
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['ST01']}</td>";
			$html_str .= "<td>{$b['BHT01']}</td>";	
			$html_str .= "<td>{$b['BHT02']}</td>";
			$html_str .= "<td>{$b['BHT03']}</td>";
			$html_str .= "<td>{$b['BHT04']}</td>";
			$html_str .= "<td>{$b['BHT05']}</td>";	
			$html_str .= "
			<tr>";	
		}							

		if (isset($b['2000A']['AAA'])){	
			// error in 270 request ISA06, ISA08, GS02, or GS03
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2000A']['AAA']}</td>";
			$html_str .= "
			<tr>";	
		}
				
		if (isset($b['2100A'])) {
			// information sender -- clearinghouse or payer?
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100A']['NM101']}</td>";
			$html_str .= "<td>{$b['2100A']['NM103']}</td>";
			$html_str .= "<td>{$b['2100A']['NM109']}</td>";
			$html_str .= "<td>{$b['2100A']['PER02']}</td>";
			$html_str .= "<td colspan=2>{$b['2100A']['PER03']}" . " {$b['2100A']['PER04']}</td>";
			$html_str .= "
			</tr>";
			// error in 270 request loop 2100A or in 271 sender's system
			$html_str .= isset($b['2100A']['AAA']) ? "<tr>
			 <td colspan=6>{$b['2100A']['AAA']}</td>
			</tr>" : "";
			
		}

		if (isset($b['2100B'])) {
			// inforamtion receiver -- practice
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100B']['NM103']}</td>";
			$html_str .= "<td>{$b['2100B']['NM109']}</td>";
			$html_str .= isset($b['2100B']['PRV']) ? "<td>{$b['2100B']['REF']}</td>" : "<td>&nbsp</td>";
			if ( isset($b['2100B']['REF']) ) {
				$ref_str = "";
				foreach($b['2100B']['REF'] as $r) { $ref_str .= $r . " "; }
				$html_str .= "<td colspan =2>$ref_str</td>";
				$ref_str = "";
			} else {
				$html_str .= "<td colspan=2>&nbsp</td>";
			}
			$html_str .= "
			</tr>";
			if ( isset($b['2100B']['AAA']) ) {
				//  error in 270 request loop 2100B 
				$html_str .= "<tr>
				<td colspan=6>{$b['2100B']['AAA']}</td>
				</tr>";
			}
		}
		
		if ( isset($b['2000C']['TRN01']) ){
			// expect sunscriber identification here
			$html_str .= "<tr>
			";
			$html_str .= ($b['2000C']['TRN01'] == "1") ? "<td>current</td>" : "<td>referenced</td>";
			$html_str .= "<td>{$b['2000C']['TRN02']}</td>";
			$html_str .= "<td>{$b['2000C']['TRN03']}</td>";
			$html_str .= "<td>{$b['2000C']['TRN04']}</td>";
			$html_str .= "<td colspan=2>&nbsp</td>";
			$html_str .= "
			</tr>";
		}
		
		/* ******************************************************** 
		 * the 2100C and 2100D loops are practically identical and one or the other is expected
		 * but it may be that there is a small amount of subscriber information, followed by
		 * detailed patient /dependent information
		 *  thus, at this point, we have redundant code
		 * ******************************************************** */
		if ( isset($b['2100C']) ) {
			// subscriber information
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100C']['NM103']}</td>";
			$html_str .= "<td>{$b['2100C']['NM109']}</td>";	
			$html_str .= "<td>{$b['2100C']['NM110']}</td>";
			$html_str .= isset($b['2100C']['DMG03']) ? "<td>{$b['2100C']['DMG03']}</td>" : "<td>&nbsp</td>";
			//
			// put the relationship in this row, if available 
			if (isset($b['2100C']['INS01']) ) {
				$ins_str = $b['2100C']['INS01'] . " " . $b['2100C']['INS02'];
				$ins_str .= isset($b['2100C']['INS03']) ? $b['2100C']['INS04'] . " " . $b['2100C']['INS04'] : "";
				$ins_str .= isset($b['2100C']['INS17']) ? $b['2100C']['INS17'] : "";
				$html_str .= "<td colspan=2>$ins_str</td>";
			}
			$html_str .= "
			</tr>";
		}
		
		if ( isset($b['2100C']['N3']) ) {
			// subscriber address							
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100C']['N301']}</td>";
			$html_str .= "<td>{$b['2100C']['N302']}</td>";
			$html_str .= isset($b['2100C']['N401']) ? "<td>{$b['2100C']['N401']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2100C']['N402']) ? "<td>{$b['2100C']['N402']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2100C']['N403']) ? "<td>{$b['2100C']['N403']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2100C']['N404']) ? "<td>{$b['2100C']['N404']}</td>" : "<td>&nbsp</td>";
			$html_str .= "
			</tr>";
		}
		
		if (isset($b['2100C']['AAA'])) {	
			// error in 270 request subscriber information
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2100C']['AAA']}</td>";
			$html_str .= "
			</tr>";	
		}
		
		if (isset($b['2100C']['MPI01'])) {			
			// military personnell information
			$html_str .= "<tr>
			";			
			$html_str .= "<td>{$b['2100C']['MPI01']}</td>";
			$html_str .= "<td>{$b['2100C']['MPI02']}</td>";
			$html_str .= "<td>{$b['2100C']['MPI03']}</td>";
			$html_str .= "<td>{$b['2100C']['MPI04']}</td>";
			$html_str .= "<td>{$b['2100C']['MPI05']}</td>";
			$html_str .= "<td>{$b['2100C']['MPI07']}</td>";
			$html_str .= "
			</tr>";	
		}			
			
		
		if ( isset($b['2100C']['DTP']) ) {	
			// dates such as eligibility, termnation, authorization, etc
			// made into a subarray of strings e.g. "eligibility 20110501"
			$dct = 0;
			$html_str .= "<tr>
			";				
			foreach($b['2100C']['DTP'] as $d) {
				$dct++;
				$html_str .= "<td colspan=2>$d</td>";
				if ($dct % 3 == 0) {
					$html_str .= "</tr>
					<tr>"; 
				}
			}
			if ($dct % 3 > 0) { 
				$cs = 2*(3 - ($dct % 3));
				"<td colspan=$cs>&nbsp</td>"; }
			$html_str .= "
			</tr>";	
		}
		
		if ( isset($b['2100C']['PRV01']) ) {							
			// provider information, which will be combined with diagnosis codes if present
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100C']['PRV01']}</td>";
			$html_str .= "<td>{$b['2100C']['PRV02']}</td>";
			$html_str .= "<td>{$b['2100C']['PRV03']}</td>";
			//
			$html_str .= isset($b['2100C']['HI']) ? "<td colspan=2>{$b['2100C']['HI']}</td>" : "<td colspan=2>&nbsp</td>";
			$html_str .= "
			</tr>tr>";	
		}
		
		// EB segment is most difficult one
		if ( isset($b['2110C']['EB']) && count($b['2110C']['EB']) > 0 ) {
			// eligibility / benefits detail
			// this is an array which is indexed under 'EB'  ['EB03'] and ['COV']['PLAN']['FIN'] ['AUTH']['MED']
			foreach($b['2110C']['EB'] as $eb) {
				// first issue is 'EB03' -- an array of service types
				if (isset($eb['EB03']) && count($eb['EB03']) > 0 ) {
					$ebct = 0;
					$html_str .= "<tr>
					";	
					foreach($eb['EB03'] as $st) {
						$html_str .= "<td>$st</td>";
						if ($dct % 6 == 0) {
							$html_str .= "</tr>
							<tr>"; 
						}
					}
					$html_str .= "
					</tr>";	
				}
				$html_str .= "<tr>
				";	
				if (isset($eb['PLAN']) ) { 
					$html_str .= "<tr>
				     <td colspan=6>{$eb['PLAN']}</td>
				    </tr>";
				}
				if (isset($eb['COV']) ) { 
					$html_str .= "<tr>
				     <td colspan=6>{$eb['COV']}</td>
				    </tr>";
				}
				if ( isset($eb['FIN']) ) { 
					$html_str .= "<tr>
					 <td colspan=6>{$eb['FIN']}</td>
					</tr>";
				}
				if ( isset($eb['AUTH']) ) { 
					$html_str .= "<tr>
					 <td colspan=6>{$eb['AUTH']}</td>
					</tr>";
				}
				if ( isset($eb['MED']) ) { 
					$html_str .= "<tr>
					 <td colspan=6>{$eb['MED']}</td>
					</tr>";
				}
												
			}
		} // end if ( isset($b['2110C']['EB']) 
		
		// 
		if ( isset($b['2110C']['DTP']) ) {	
			// dates such as eligibility, termnation, authorization, etc
			// DTP in loop 2110C when the specific benefit has a different date than in loop 2100C
			$html_str .= "<tr>
			";			
			// made into a subarray of strings e.g. "eligibility 20110501"
			$dct = 0;
			foreach($b['2110C']['DTP'] as $d) {
				$dct++;
				$html_str .= "<td colspan=2>$d</td>";
				if ($dct % 3 == 0) {
					$html_str .= "</tr>
					<tr>"; 
				}
			}
			if ($dct % 3 > 0) { "<td colspan={2*(3 - $dct % 3)}>&nbsp</td>"; }
			$html_str .= "
			</tr>";	
		}
					
		if (isset($b['2110C']['AAA'])) {	
			// error in 270 request or in regards to specific benefit
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2110C']['AAA']}</td>";
			$html_str .= "
			</tr>";	
		}
		
		if (isset($b['2110C']['MSG'])) {	
			// free form text messaget
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2110C']['MSG']}</td>";
			$html_str .= "
			</tr>";	
		}
				
		if (isset($b['2115C']['III'])) {	
			// added information regarding eligibility or benefits in the 2110C loop
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2115C']['III']}</td>";
			$html_str .= "
			</tr>";	
		}
					
		if (isset($b['2120C']['NM101'])) {
			// subscriber benefit related entity
			$html_str .= "<tr>
			";
			$html_str .= "<td colspan=2>{$b['2100C']['NM101']}</td>";
			$html_str .= "<td>{$b['2100C']['NM103']}</td>";
			$html_str .= "<td>{$b['2100C']['NM109']}</td>";	
			$html_str .= "<td colspan=2>{$b['2100C']['NM110']}</td>";	
			$html_str .= "
			</tr>";
		}			
						
		if (isset($b['2120C']['N301'])) {
			$html_str .= "<tr>
			";							
			$html_str .= "<td>{$b['2120C']['N301']}</td>";
			$html_str .= "<td>{$b['2120C']['N302']}</td>";
			$html_str .= isset($b['2120C']['N401']) ? "<td>{$b['2100C']['N401']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2120C']['N402']) ? "<td>{$b['2100C']['N402']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2120C']['N403']) ? "<td>{$b['2100C']['N403']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2120C']['N404']) ? "<td>{$b['2100C']['N404']}</td>" : "<td>&nbsp</td>";
			$html_str .= "
			</tr>";
		}
		
		if (isset($b['2120C']['PRV01'])) {
			$html_str .= "<tr>
			";
			$html_str .= "<td colspan=2>{$b['2120C']['PRV01']} </td>";
			$html_str .= "<td>{$b['2120C']['PRV02']} </td>";
			$html_str .= "<td>{$b['2120C']['PRV03']} </td>";
			$html_str .= "<td colspan=2>&nbsp </td>";
			$html_str .= "
			</tr>";
		}
		
		if (isset($b['2120C']['PER02'])) {		
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2120C']['PER02']} </td>";
			$html_str .= "<td colspan=2>{$b['2120C']['PER03']}" . "   {$b['2120C']['PER04']}</td>";
			$html_str .= "<td colspan=3>&nbsp </td>";
			//
			$html_str .= "
			</tr>";
		}
		//
		/* *************
		 * now repeat the above code, but for the 2100D loop
		 * ************* */
		if ( isset($b['2100D']) ) {
			// subscriber information
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100D']['NM103']}</td>";
			$html_str .= "<td>{$b['2100D']['NM109']}</td>";	
			$html_str .= "<td>{$b['2100D']['NM110']}</td>";
			$html_str .= isset($b['2100D']['DMG03']) ? "<td>{$b['2100D']['DMG03']}</td>" : "<td>&nbsp</td>";
			//
			// put the relationship in this row, if available 
			if (isset($b['2100D']['INS01']) ) {
				$ins_str = $b['2100D']['INS01'] . " " . $b['2100D']['INS02'];
				$ins_str .= isset($b['2100D']['INS03']) ? $b['2100D']['INS04'] . " " . $b['2100D']['INS04'] : "";
				$ins_str .= isset($b['2100D']['INS17']) ? $b['2100D']['INS17'] : "";
				$html_str .= "<td colspan=2>$ins_str</td>";
			}
			$html_str .= "
			</tr>";
		}
		
		if ( isset($b['2100D']['N3']) ) {
			// subscriber address							
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100D']['N301']}</td>";
			$html_str .= "<td>{$b['2100D']['N302']}</td>";
			$html_str .= isset($b['2100D']['N401']) ? "<td>{$b['2100D']['N401']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2100D']['N402']) ? "<td>{$b['2100D']['N402']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2100D']['N403']) ? "<td>{$b['2100D']['N403']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2100D']['N404']) ? "<td>{$b['2100D']['N404']}</td>" : "<td>&nbsp</td>";
			$html_str .= "
			</tr>";
		}
		
		if (isset($b['2100D']['AAA'])) {	
			// error in 270 request subscriber information
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2100D']['AAA']}</td>";
			$html_str .= "
			</tr>";	
		}
		
		if (isset($b['2100D']['MPI01'])) {			
			// military personnell information
			$html_str .= "<tr>
			";			
			$html_str .= "<td>{$b['2100D']['MPI01']}</td>";
			$html_str .= "<td>{$b['2100D']['MPI02']}</td>";
			$html_str .= "<td>{$b['2100D']['MPI03']}</td>";
			$html_str .= "<td>{$b['2100D']['MPI04']}</td>";
			$html_str .= "<td>{$b['2100D']['MPI05']}</td>";
			$html_str .= "<td>{$b['2100D']['MPI07']}</td>";
			$html_str .= "
			</tr>";	
		}			
			
		
		if ( isset($b['2100D']['DTP']) ) {	
			// dates such as eligibility, termnation, authorization, etc
			$html_str .= "<tr>
			";			
			// made into a subarray of strings e.g. "eligibility 20110501"
			$dct = 0;
			foreach($b['2100D']['DTP'] as $d) {
				$dct++;
				$html_str .= "<td colspan=2>$d</td>";
				if ($dct % 3 == 0) {
					$html_str .= "</tr>
					<tr>"; 
				}
			}
			if ($dct % 3 > 0) { "<td colspan={2*(3 - $dct % 3)}>&nbsp</td>"; }
			$html_str .= "
			</tr>";	
		}
		
		if ( isset($b['2100D']['PRV01']) ) {							
			// provider information, which will be combined with diagnosis codes if present
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2100D']['PRV01']}</td>";
			$html_str .= "<td>{$b['2100D']['PRV02']}</td>";
			$html_str .= "<td>{$b['2100D']['PRV03']}</td>";
			//
			$html_str .= isset($b['2100D']['HI']) ? "<td colspan=2>{$b['2100D']['HI']}</td>" : "<td colspan=2>&nbsp</td>";
			$html_str .= "
			</tr>tr>";	
		}
		
		// EB segment is most difficult one
		if ( isset($b['2110D']['EB']) && count($b['2110D']['EB']) > 0 ) {
			// eligibility / benefits detail
			// this is an array which is indexed under 'EB'  ['EB03'] and ['COV']['PLAN']['FIN'] ['AUTH']['MED']
			foreach($b['2110D']['EB'] as $eb) {
				// first issue is 'EB03' -- an array of service types
				if (isset($eb['EB03']) && count($eb['EB03']) > 0 ) {
					$ebct = 0;
					$html_str .= "<tr>
					";	
					foreach($eb['EB03'] as $st) {
						$html_str .= "<td>$st</td>";
						if ($dct % 6 == 0) {
							$html_str .= "</tr>
							<tr>"; 
						}
					}
					$html_str .= "
					</tr>";	
				}
				$html_str .= "<tr>
				";	
				if (isset($eb['PLAN']) ) { 
					$html_str .= "<tr>
				     <td colspan=6>{$eb['PLAN']}</td>
				    </tr>";
				}
				if (isset($eb['COV']) ) { 
					$html_str .= "<tr>
				     <td colspan=6>{$eb['COV']}</td>
				    </tr>";
				}
				if ( isset($eb['FIN']) ) { 
					$html_str .= "<tr>
					 <td colspan=6>{$eb['FIN']}</td>
					</tr>";
				}
				if ( isset($eb['AUTH']) ) { 
					$html_str .= "<tr>
					 <td colspan=6>{$eb['AUTH']}</td>
					</tr>";
				}
				if ( isset($eb['MED']) ) { 
					$html_str .= "<tr>
					 <td colspan=6>{$eb['MED']}</td>
					</tr>";
				}
												
			}
		} // end if ( isset($b['2110D']['EB']) 
		
		// 
		if ( isset($b['2110D']['DTP']) ) {	
			// dates such as eligibility, termnation, authorization, etc
			// DTP in loop 2110C when the specific benefit has a different date than in loop 2100C
			$html_str .= "<tr>
			";			
			// made into a subarray of strings e.g. "eligibility 20110501"
			$dct = 0;
			foreach($b['2110D']['DTP'] as $d) {
				$dct++;
				$html_str .= "<td colspan=2>$d</td>";
				if ($dct % 3 == 0) {
					$html_str .= "</tr>
					<tr>"; 
				}
			}
			if ($dct % 3 > 0) { 
				$cs = 2*(3 - ($dct % 3));
				$html_str .= "<td colspan=$cs>&nbsp</td>"; 
			}
			$html_str .= "
			</tr>";	
		}
					
		if (isset($b['2110D']['AAA'])) {	
			// error in 270 request or in regards to specific benefit
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2110D']['AAA']}</td>";
			$html_str .= "
			</tr>";	
		}
		
		if (isset($b['2110D']['MSG'])) {	
			// free form text messaget
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2110D']['MSG']}</td>";
			$html_str .= "
			</tr>";	
		}
				
		if (isset($b['2115D']['III'])) {	
			// added information regarding eligibility or benefits in the 2110C loop
			$html_str .= "<tr>
			";			
			$html_str .= "<td colspan=6>{$b['2115D']['III']}</td>";
			$html_str .= "
			</tr>";	
		}
					
		if (isset($b['2120D']['NM101'])) {
			// subscriber benefit related entity
			$html_str .= "<tr>
			";
			$html_str .= "<td colspan=2>{$b['2100D']['NM101']}</td>";
			$html_str .= "<td>{$b['2100D']['NM103']}</td>";
			$html_str .= "<td>{$b['2100D']['NM109']}</td>";	
			$html_str .= "<td colspan=2>{$b['2100D']['NM110']}</td>";	
			$html_str .= "
			</tr>";
		}			
						
		if (isset($b['2120D']['N301'])) {
			$html_str .= "<tr>
			";							
			$html_str .= "<td>{$b['2120D']['N301']}</td>";
			$html_str .= "<td>{$b['2120D']['N302']}</td>";
			$html_str .= isset($b['2120D']['N401']) ? "<td>{$b['2100D']['N401']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2120D']['N402']) ? "<td>{$b['2100D']['N402']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2120D']['N403']) ? "<td>{$b['2100D']['N403']}</td>" : "<td>&nbsp</td>";
			$html_str .= isset($b['2120D']['N404']) ? "<td>{$b['2100D']['N404']}</td>" : "<td>&nbsp</td>";
			$html_str .= "
			</tr>";
		}
		
		if (isset($b['2120D']['PRV01'])) {
			$html_str .= "<tr>
			";
			$html_str .= "<td colspan=2>{$b['2120D']['PRV01']} </td>";
			$html_str .= "<td>{$b['2120D']['PRV02']} </td>";
			$html_str .= "<td>{$b['2120D']['PRV03']} </td>";
			$html_str .= "<td colspan=2>&nbsp </td>";
			$html_str .= "
			</tr>";
		}
		
		if (isset($b['2120D']['PER02'])) {		
			$html_str .= "<tr>
			";
			$html_str .= "<td>{$b['2120D']['PER02']} </td>";
			$html_str .= "<td colspan=2>{$b['2120D']['PER03']}" . "   {$b['2120D']['PER04']}</td>";
			$html_str .= "<td colspan=3>&nbsp </td>";
			//
			$html_str .= "
			</tr>";
		}
		//  
	}					
}




function ibr_271_new_files($files_ar = NULL, $html_out = FALSE, $err_only = TRUE) {
	// @param bool $html_out -- whether to return html output
	// @param bool $err_only -- only list claims with errors in output
	// @param array $files_ar -- list of new files from upload script
	//   otherwise the 271/ directory is scanned and files in directory that
	//   are not in the 271_files.csv record are treated as "new" files
	//   					    
	// get the new files in an array
	if ( is_array($files_ar) && count($files_ar) ) {
		$ar_files = $files_ar;
		$need_dir = FALSE;
	} else {
		$ar_files = csv_newfile_list("f271");
		$need_dir = TRUE;
		$p = csv_parameters("f271");
		$dir = dirname(__FILE__).$p['directory'];
	}
	//
	if (!is_array($ar_files) || count($ar_files) == 0 ) {
		$html_str = "ibr_271_new_files: no new f271 (eligibility) files found <br />" . PHP_EOL;
		return $html_str;
	}		
	// OK, we have some new files
	$html_str = "";
	$idx = 0;
	$chr_c = 0;
	$chr_f = 0;
	//
	foreach ($ar_files as $file_271) {
		// debug
		//echo "ibr_277_process_new:  $file_277 <br />";
		$path_271 = ($need_dir) ? $dir.DIRECTORY_SEPARATOR.$file_271 : $file_271;
		//
		$ar_seg = csv_x12_segments($path_277, "277", FALSE);
		//
		if (is_array($ar_seg) && count($ar_seg['segments']) ) {
			$ar_271_vals = ibr_271_toarray($ar_seg);
			if (!$ar_271_vals) {
				$html_str .= "failed to get segments for $file_271 <br />" .PHP_EOL;
				continue;
			}		
		} else {
			$html_str .= "failed to get segments for $file_271 <br />" .PHP_EOL;
			continue;
		}
		//$ar_277_vals = ($need_dir) ? ibr_277_parse($dir . $file_277) : $file_277;
		//
		// debug
		//echo "ar_277_vals count " . count($ar_277_vals) . "<br />";
		//var_dump(array_keys($ar_277_vals));
		//var_dump($ar_277_vals['file']);
		//
		$ar_csvc = ibr_271_csv_data($ar_277_vals);
		//
		//$chr_f = ibr_277_write_csv ($ar_277_vals['file'], "file");
		//$chr_c = ibr_277_write_csv ($ar_csvc, "claim"); 
		//
		$chr_f += csv_write_record($ar_277_vals['file'], "f277", "file");
		$chr_c += csv_write_record($ar_csvc, "f277", "claim");
		//
		if ($html_out) {
			// store the data arrays so they are html'ized at once
			$ar_h[$idx]['file'] = $ar_277_vals['file'];
			$ar_h[$idx]['claim'] = $ar_csvc;
			$idx++;

			//
		}
	} // end foreach ($ar_files as $file_277)
	//
	if ($html_out) {
		$html_str .= ibr_277_html($ar_h, $err_only);
	} else {
		$html_str .= "ibr_277_process_new: wrote $chr_c characters claims and $chr_f characters files <br />". PHP_EOL;
	} 
	csv_edihist_log("ibr_277_process_new: $chr_f characters written to files_277.csv");
	csv_edihist_log("ibr_277_process_new: $chr_c characters written to claims_277.csv");		
	// var_dump ($ar_277_vals);
	//
	return $html_str;
}
	

?>
