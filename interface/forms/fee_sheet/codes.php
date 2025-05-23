<?php

// Change this to 1 if your drop-downs are too wide for 2 columns:
$FEE_SHEET_COLUMNS = 2;

// The $bcodes table is obsolete.  You can customize the dropdowns
// now via the Admin/Lists page.

/*********************************************************************
$bcodes = array();

$bcodes['CPT4']  = array();

// These are the commonly used CPT codes for YOUR practice.
// Change them to suit your needs.
//
$bcodes['CPT4']['Established Patient'] = array(
    '99211' => 'Brief',
    '99212' => 'Limited',
    '99213' => 'Detailed',
    '99214' => 'Extended',
    '99215' => 'Comprehensive',
    '99391' => 'Well Exam Infant (V20.2)',
    '99392' => 'Well Exam 1-4 Yrs (V20.2)',
    '99393' => 'Well Exam 5-11 Yrs (V20.2)',
    '99394' => 'Well Exam 12-17 Yrs (V72.31/V20.2)',
    '99395' => 'Well Exam 18-39 Yrs (V72.31/V70.0)',
    '99396' => 'Well Exam 40-64 Yrs (V72.31/V70.0)',
    '99397' => 'Well Exam 65+ Yrs (V72.31/V70.0)',
);
$bcodes['CPT4']['New Patient'] = array(
    '99201' => 'Brief',
    '99202' => 'Limited',
    '99203' => 'Detailed',
    '99204' => 'Extended',
    '99205' => 'Comprehensive',
    '99381' => 'Well Exam Infant (V20.2)',
    '99382' => 'Well Exam 1-4 Yrs (V20.2)',
    '99383' => 'Well Exam 5-11 Yrs (V20.2)',
    '99384' => 'Well Exam 12-17 Yrs (V72.31/V20.2)',
    '99385' => 'Well Exam 18-39 Yrs (V72.31/V70.0)',
    '99386' => 'Well Exam 40-64 Yrs (V72.31/V70.0)',
    '99387' => 'Well Exam 65+ Yrs (V72.31/V70.0)',
);
$bcodes['CPT4']['Procedures'] = array(
    '10040' => 'Cyst Removal',
    '10060' => 'I & D simple/single',
    '10061' => 'I & D abscess multiple/complex',
    '10160' => 'Aspiration of abscess/hematoma/ap',
    '11055' => 'Paring/cutting hypokeratosis-single',
    '11056' => 'Paring/cutting hypokeratosis (2-4)',
    '11057' => 'Paring/cutting hypokeratosis > 4',
    '11100' => 'Biopsy of skin',
    '11200' => 'Removal of skin tags up to 15',
    '11305' => 'Shave epidermal lesion <= 0.5 cm',
    '11730' => 'Avulsion of nail plain (single)',
    '12001' => 'Wound repair < 2.5 cm',
    '12032' => 'Wound repair > 2.5 cm < 7.5 cm',
    '17000' => 'Destruction - one benign lesion',
    '17003' => 'Destruction other lesions / 2-14',
    '17004' => 'Destruction other lesions >= 15',
    '17110' => 'Destr warts <= 14 milla/molloscum',
    '17111' => 'Destruction warts >= 15',
    '20600' => 'Arthrocentesis - small joint',
    '20605' => 'Arthrocentesis - intermediate',
    '20610' => 'Arthrocentesis - major joint',
    '26075' => 'Incision/splinter removal - finger',
    '28190' => 'Foreign body removal - foot',
    '29130' => 'Finger splint',
    '29530' => 'Strapping knee',
    '29540' => 'Strapping ankle',
    '29550' => 'Strapping toes',
    '30300' => 'Foreign body removal - nasal',
    '36550' => 'Flushing portacath',
    '69200' => 'Foreign body removal - ear canal',
    '69210' => 'Impacted cerumen removal',
    '76075-26' => 'Bone density review',
    '87210' => 'Wet Prep/Reading',
    '87220' => 'KOH Prep/Reading',
    '93233' => 'Holter Monitor Review',
    '93272' => 'Event monitor interpretation',
);
$bcodes['CPT4']['Lab'] = array(
    '82962' => 'Accucheck',
    '90788' => 'Admin of Antibiotic',
    '90782' => 'Admin of Injection',
    '95115' => 'Allergy injection',
    '94640' => 'Breating Treatment (aerosol)',
    '94664' => 'MDI Instruction',
    '93000' => 'EKG',
    '99173' => 'Eye acuity screening',
    '93270' => 'Event monitor hookup/disc',
    '85018-QW' => 'Hemocue',
    '82270' => 'Hemoccult',
    '93231' => 'Holter connect/disconnect',
    '90780' => 'IV infusion up to one hour',
    '86308' => 'Monospot',
    '94760' => 'O2 saturation',
    '94761' => 'O2 saturation > 1 hour',
    '85610-QW' => 'Prothrombin time',
    '87430' => 'Quick strep test',
    '94375-26' => 'Respiratory Flow Volume Loop',
    '99000' => 'Specimen handling',
    '94010' => 'Spirometry Screen',
    '99070' => 'Supplies Medical splint/aco',
    '86580' => 'TB skin test',
    '81002' => 'Urinalysis w/o micro',
    '81025' => 'Urine pregnancy losl/BHCG',
    '36415' => 'Venipuncture',
    'G0107' => 'Screening Hemoccult - Medicare',
);
$bcodes['CPT4']['Immunizations'] = array(
    '90471' => 'Single Immunization Admin',
    '90472' => 'Additional Immunization Admin',
    'G0008' => 'Flu shot / medicare (V06.6)',
    'G0009' => 'Pneumovax / medicare (V06.6)',
    '90658' => 'Influenza (V04.8)',
    '90732' => 'Pneumonia (V03.82)',
    '90718' => 'Td (V06.5)',
    '90703' => 'Tot Tox (V03.7)',
);
$bcodes['CPT4']['Consultation and Review'] = array(
    '99361' => 'Medical Conference 30 min',
    '99371' => 'Telephone Consultation 1-10 min',
    '99372' => 'Telephone Consultation 10-20 min',
    '99374' => 'Physician Supervision H.H. Pt',
    'G0180' => 'HH Certification Initial',
    'G0179' => 'HH Recertification',
    'G0181' => 'Care Plan Oversight',
);

$bcodes['HCPCS'] = array();

$bcodes['HCPCS']['Therapeutic Injections'] = array(
    'J7619' => 'Albuterol unit dose 0.083%',
    'J1200' => 'Benadryl up to 50 mg',
    'J3420' => 'B-12 1000 mg',
    'J0704' => 'Colostone Soluspan',
    'J0698' => 'Claforan up to 1000 mg',
    'J0735' => 'Clonidine up to 1 mg',
    'J1094' => 'Decadron 8 mg/ml',
    'J1100' => 'Decadron 4 mg/ml',
    'J1390' => 'Estrogen 20 mg',
    'J0970' => 'Estrogen 40 mg',
    'J1820' => 'Insulin up to 100 units',
    'J1940' => 'Lasix up to 20 mg',
    'J2000' => 'Lidocaine 1% cc',
    'J2010' => 'Lincocin up to 300 mg',
    'J2550' => 'Phenergan up to 50 mg',
    'J2680' => 'Prolixin 25 mg',
    'J3410' => 'Vistaril up to 25 mg',
);
*********************************************************************/
