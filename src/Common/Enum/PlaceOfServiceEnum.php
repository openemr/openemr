<?php
/*
 * PlaceOfServiceEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright AI Generated content is in the public domain
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Enum;

// This class was AI Generated from the original POSRef class
enum PlaceOfServiceEnum: string
{
    case PHARMACY = '01';
    case TELEHEALTH = '02';
    case SCHOOL = '03';
    case HOMELESS_SHELTER = '04';
    case IHS_FREESTANDING = '05';
    case IHS_PROVIDER_BASED = '06';
    case TRIBAL_638_FREESTANDING = '07';
    case TRIBAL_638_PROVIDER_BASED = '08';
    case PRISON_CORRECTIONAL = '09';
    case UNASSIGNED_10 = '10';
    case OFFICE = '11';
    case HOME = '12';
    case ASSISTED_LIVING = '13';
    case GROUP_HOME = '14';
    case MOBILE_UNIT = '15';
    case TEMPORARY_LODGING = '16';
    case WALKIN_RETAIL_HEALTH = '17';
    case PLACE_OF_EMPLOYMENT = '18';
    case OFF_CAMPUS_OUTPATIENT = '19';
    case URGENT_CARE = '20';
    case INPATIENT_HOSPITAL = '21';
    case OUTPATIENT_HOSPITAL = '22';
    case EMERGENCY_ROOM = '23';
    case AMBULATORY_SURGICAL = '24';
    case BIRTHING_CENTER = '25';
    case MILITARY_TREATMENT = '26';
    case UNASSIGNED_27 = '27';
    case UNASSIGNED_28 = '28';
    case UNASSIGNED_29 = '29';
    case UNASSIGNED_30 = '30';
    case SKILLED_NURSING = '31';
    case NURSING_FACILITY = '32';
    case CUSTODIAL_CARE = '33';
    case HOSPICE = '34';
    case UNASSIGNED_35 = '35';
    case UNASSIGNED_36 = '36';
    case UNASSIGNED_37 = '37';
    case UNASSIGNED_38 = '38';
    case UNASSIGNED_39 = '39';
    case UNASSIGNED_40 = '40';
    case AMBULANCE_LAND = '41';
    case AMBULANCE_AIR_WATER = '42';
    case UNASSIGNED_43 = '43';
    case UNASSIGNED_44 = '44';
    case UNASSIGNED_45 = '45';
    case UNASSIGNED_46 = '46';
    case UNASSIGNED_47 = '47';
    case UNASSIGNED_48 = '48';
    case INDEPENDENT_CLINIC = '49';
    case FEDERALLY_QUALIFIED_HEALTH = '50';
    case INPATIENT_PSYCHIATRIC = '51';
    case PSYCHIATRIC_PARTIAL_HOSPITALIZATION = '52';
    case COMMUNITY_MENTAL_HEALTH = '53';
    case INTERMEDIATE_CARE_MENTALLY_RETARDED = '54';
    case RESIDENTIAL_SUBSTANCE_ABUSE = '55';
    case PSYCHIATRIC_RESIDENTIAL = '56';
    case NONRESIDENTIAL_SUBSTANCE_ABUSE = '57';
    case UNASSIGNED_58 = '58';
    case UNASSIGNED_59 = '59';
    case MASS_IMMUNIZATION = '60';
    case COMPREHENSIVE_INPATIENT_REHAB = '61';
    case COMPREHENSIVE_OUTPATIENT_REHAB = '62';
    case UNASSIGNED_63 = '63';
    case UNASSIGNED_64 = '64';
    case END_STAGE_RENAL_DISEASE = '65';
    case UNASSIGNED_66 = '66';
    case UNASSIGNED_67 = '67';
    case UNASSIGNED_68 = '68';
    case UNASSIGNED_69 = '69';
    case UNASSIGNED_70 = '70';
    case PUBLIC_HEALTH_CLINIC = '71';
    case RURAL_HEALTH_CLINIC = '72';
    case UNASSIGNED_73 = '73';
    case UNASSIGNED_74 = '74';
    case UNASSIGNED_75 = '75';
    case UNASSIGNED_76 = '76';
    case UNASSIGNED_77 = '77';
    case UNASSIGNED_78 = '78';
    case UNASSIGNED_79 = '79';
    case UNASSIGNED_80 = '80';
    case INDEPENDENT_LABORATORY = '81';
    case UNASSIGNED_82 = '82';
    case UNASSIGNED_83 = '83';
    case UNASSIGNED_84 = '84';
    case UNASSIGNED_85 = '85';
    case UNASSIGNED_86 = '86';
    case UNASSIGNED_87 = '87';
    case UNASSIGNED_88 = '88';
    case UNASSIGNED_89 = '89';
    case UNASSIGNED_90 = '90';
    case UNASSIGNED_91 = '91';
    case UNASSIGNED_92 = '92';
    case UNASSIGNED_93 = '93';
    case UNASSIGNED_94 = '94';
    case UNASSIGNED_95 = '95';
    case UNASSIGNED_96 = '96';
    case UNASSIGNED_97 = '97';
    case UNASSIGNED_98 = '98';
    case OTHER = '99';

    /**
     * Get the untranslated name/title for this place of service
     */
    public function getName(): string
    {
        return match ($this) {
            self::PHARMACY => 'Pharmacy',
            self::TELEHEALTH => 'Telehealth',
            self::SCHOOL => 'School',
            self::HOMELESS_SHELTER => 'Homeless Shelter',
            self::IHS_FREESTANDING => 'Indian Health Service Free-standing Facility',
            self::IHS_PROVIDER_BASED => 'Indian Health Service Provider-based Facility',
            self::TRIBAL_638_FREESTANDING => 'Tribal 638 Free-standing Facility',
            self::TRIBAL_638_PROVIDER_BASED => 'Tribal 638 Provider-based Facility',
            self::PRISON_CORRECTIONAL => 'Prison Correctional Facility',
            self::UNASSIGNED_10 => 'Unassigned',
            self::OFFICE => 'Office',
            self::HOME => 'Home',
            self::ASSISTED_LIVING => 'Assisted Living Facility',
            self::GROUP_HOME => 'Group Home *',
            self::MOBILE_UNIT => 'Mobile Unit',
            self::TEMPORARY_LODGING => 'Temporary Lodging',
            self::WALKIN_RETAIL_HEALTH => 'Walk-in Retail Health Clinic',
            self::PLACE_OF_EMPLOYMENT => 'Place of Employment-Worksite',
            self::OFF_CAMPUS_OUTPATIENT => 'Off Campus-Outpatient Hospital',
            self::URGENT_CARE => 'Urgent Care Facility',
            self::INPATIENT_HOSPITAL => 'Inpatient Hospital',
            self::OUTPATIENT_HOSPITAL => 'Outpatient Hospital',
            self::EMERGENCY_ROOM => 'Emergency Room - Hospital',
            self::AMBULATORY_SURGICAL => 'Ambulatory Surgical Center',
            self::BIRTHING_CENTER => 'Birthing Center',
            self::MILITARY_TREATMENT => 'Military Treatment Facility',
            self::UNASSIGNED_27 => 'Unassigned',
            self::UNASSIGNED_28 => 'Unassigned',
            self::UNASSIGNED_29 => 'Unassigned',
            self::UNASSIGNED_30 => 'Unassigned',
            self::SKILLED_NURSING => 'Skilled Nursing Facility',
            self::NURSING_FACILITY => 'Nursing Facility',
            self::CUSTODIAL_CARE => 'Custodial Care Facility',
            self::HOSPICE => 'Hospice',
            self::UNASSIGNED_35 => 'Unassigned',
            self::UNASSIGNED_36 => 'Unassigned',
            self::UNASSIGNED_37 => 'Unassigned',
            self::UNASSIGNED_38 => 'Unassigned',
            self::UNASSIGNED_39 => 'Unassigned',
            self::UNASSIGNED_40 => 'Unassigned',
            self::AMBULANCE_LAND => 'Ambulance - Land',
            self::AMBULANCE_AIR_WATER => 'Ambulance - Air or Water',
            self::UNASSIGNED_43 => 'Unassigned',
            self::UNASSIGNED_44 => 'Unassigned',
            self::UNASSIGNED_45 => 'Unassigned',
            self::UNASSIGNED_46 => 'Unassigned',
            self::UNASSIGNED_47 => 'Unassigned',
            self::UNASSIGNED_48 => 'Unassigned',
            self::INDEPENDENT_CLINIC => 'Independent Clinic',
            self::FEDERALLY_QUALIFIED_HEALTH => 'Federally Qualified Health Center',
            self::INPATIENT_PSYCHIATRIC => 'Inpatient Psychiatric Facility',
            self::PSYCHIATRIC_PARTIAL_HOSPITALIZATION => 'Psychiatric Facility-Partial Hospitalization',
            self::COMMUNITY_MENTAL_HEALTH => 'Community Mental Health Center',
            self::INTERMEDIATE_CARE_MENTALLY_RETARDED => 'Intermediate Care Facility/Mentally Retarded',
            self::RESIDENTIAL_SUBSTANCE_ABUSE => 'Residential Substance Abuse Treatment Facility',
            self::PSYCHIATRIC_RESIDENTIAL => 'Psychiatric Residential Treatment Center',
            self::NONRESIDENTIAL_SUBSTANCE_ABUSE => 'Non-residential Substance Abuse Treatment Facility',
            self::UNASSIGNED_58 => 'Unassigned',
            self::UNASSIGNED_59 => 'Unassigned',
            self::MASS_IMMUNIZATION => 'Mass Immunization Center',
            self::COMPREHENSIVE_INPATIENT_REHAB => 'Comprehensive Inpatient Rehabilitation Facility',
            self::COMPREHENSIVE_OUTPATIENT_REHAB => 'Comprehensive Outpatient Rehabilitation Facility',
            self::UNASSIGNED_63 => 'Unassigned',
            self::UNASSIGNED_64 => 'Unassigned',
            self::END_STAGE_RENAL_DISEASE => 'End-Stage Renal Disease Treatment Facility',
            self::UNASSIGNED_66 => 'Unassigned',
            self::UNASSIGNED_67 => 'Unassigned',
            self::UNASSIGNED_68 => 'Unassigned',
            self::UNASSIGNED_69 => 'Unassigned',
            self::UNASSIGNED_70 => 'Unassigned',
            self::PUBLIC_HEALTH_CLINIC => 'Public Health Clinic',
            self::RURAL_HEALTH_CLINIC => 'Rural Health Clinic',
            self::UNASSIGNED_73 => 'Unassigned',
            self::UNASSIGNED_74 => 'Unassigned',
            self::UNASSIGNED_75 => 'Unassigned',
            self::UNASSIGNED_76 => 'Unassigned',
            self::UNASSIGNED_77 => 'Unassigned',
            self::UNASSIGNED_78 => 'Unassigned',
            self::UNASSIGNED_79 => 'Unassigned',
            self::UNASSIGNED_80 => 'Unassigned',
            self::INDEPENDENT_LABORATORY => 'Independent Laboratory',
            self::UNASSIGNED_82 => 'Unassigned',
            self::UNASSIGNED_83 => 'Unassigned',
            self::UNASSIGNED_84 => 'Unassigned',
            self::UNASSIGNED_85 => 'Unassigned',
            self::UNASSIGNED_86 => 'Unassigned',
            self::UNASSIGNED_87 => 'Unassigned',
            self::UNASSIGNED_88 => 'Unassigned',
            self::UNASSIGNED_89 => 'Unassigned',
            self::UNASSIGNED_90 => 'Unassigned',
            self::UNASSIGNED_91 => 'Unassigned',
            self::UNASSIGNED_92 => 'Unassigned',
            self::UNASSIGNED_93 => 'Unassigned',
            self::UNASSIGNED_94 => 'Unassigned',
            self::UNASSIGNED_95 => 'Unassigned',
            self::UNASSIGNED_96 => 'Unassigned',
            self::UNASSIGNED_97 => 'Unassigned',
            self::UNASSIGNED_98 => 'Unassigned',
            self::OTHER => 'Other Place of Service',
        };
    }

    /**
     * Gets the translated name/title for this place of service.  This duplicates the constants in
     * getName() but wrapped in xl() for translation purposes.  We have the duplicates because we need the translation engine
     * to pick up the strings for translation extraction.  If we can find a better way to handle this to remove the duplicates
     * that would be ideal
     * @return string
     */
    public function getTranslatedTitle(): string
    {
        return match ($this) {
            self::PHARMACY => xl('Pharmacy') . ' **', // TODO: this was in the original, but why the asterisks?
            self::TELEHEALTH => xl('Telehealth'),
            self::SCHOOL => xl('School'),
            self::HOMELESS_SHELTER => xl('Homeless Shelter'),
            self::IHS_FREESTANDING => xl('Indian Health Service Free-standing Facility'),
            self::IHS_PROVIDER_BASED => xl('Indian Health Service Provider-based Facility'),
            self::TRIBAL_638_FREESTANDING => xl('Tribal 638 Free-standing Facility'),
            self::TRIBAL_638_PROVIDER_BASED => xl('Tribal 638 Provider-based Facility'),
            self::PRISON_CORRECTIONAL => xl('Prison Correctional Facility'),
            self::UNASSIGNED_10 => xl('Unassigned'),
            self::OFFICE => xl('Office'),
            self::HOME => xl('Home'),
            self::ASSISTED_LIVING => xl('Assisted Living Facility'),
            self::GROUP_HOME => xl('Group Home *'),
            self::MOBILE_UNIT => xl('Mobile Unit'),
            self::TEMPORARY_LODGING => xl('Temporary Lodging'),
            self::WALKIN_RETAIL_HEALTH => xl('Walk-in Retail Health Clinic'),
            self::PLACE_OF_EMPLOYMENT => xl('Place of Employment-Worksite'),
            self::OFF_CAMPUS_OUTPATIENT => xl('Off Campus-Outpatient Hospital'),
            self::URGENT_CARE => xl('Urgent Care Facility'),
            self::INPATIENT_HOSPITAL => xl('Inpatient Hospital'),
            self::OUTPATIENT_HOSPITAL => xl('Outpatient Hospital'),
            self::EMERGENCY_ROOM => xl('Emergency Room - Hospital'),
            self::AMBULATORY_SURGICAL => xl('Ambulatory Surgical Center'),
            self::BIRTHING_CENTER => xl('Birthing Center'),
            self::MILITARY_TREATMENT => xl('Military Treatment Facility'),
            self::UNASSIGNED_27 => xl('Unassigned'),
            self::UNASSIGNED_28 => xl('Unassigned'),
            self::UNASSIGNED_29 => xl('Unassigned'),
            self::UNASSIGNED_30 => xl('Unassigned'),
            self::SKILLED_NURSING => xl('Skilled Nursing Facility'),
            self::NURSING_FACILITY => xl('Nursing Facility'),
            self::CUSTODIAL_CARE => xl('Custodial Care Facility'),
            self::HOSPICE => xl('Hospice'),
            self::UNASSIGNED_35 => xl('Unassigned'),
            self::UNASSIGNED_36 => xl('Unassigned'),
            self::UNASSIGNED_37 => xl('Unassigned'),
            self::UNASSIGNED_38 => xl('Unassigned'),
            self::UNASSIGNED_39 => xl('Unassigned'),
            self::UNASSIGNED_40 => xl('Unassigned'),
            self::AMBULANCE_LAND => xl('Ambulance - Land'),
            self::AMBULANCE_AIR_WATER => xl('Ambulance - Air or Water'),
            self::UNASSIGNED_43 => xl('Unassigned'),
            self::UNASSIGNED_44 => xl('Unassigned'),
            self::UNASSIGNED_45 => xl('Unassigned'),
            self::UNASSIGNED_46 => xl('Unassigned'),
            self::UNASSIGNED_47 => xl('Unassigned'),
            self::UNASSIGNED_48 => xl('Unassigned'),
            self::INDEPENDENT_CLINIC => xl('Independent Clinic'),
            self::FEDERALLY_QUALIFIED_HEALTH => xl('Federally Qualified Health Center'),
            self::INPATIENT_PSYCHIATRIC => xl('Inpatient Psychiatric Facility'),
            self::PSYCHIATRIC_PARTIAL_HOSPITALIZATION => xl('Psychiatric Facility-Partial Hospitalization'),
            self::COMMUNITY_MENTAL_HEALTH => xl('Community Mental Health Center'),
            self::INTERMEDIATE_CARE_MENTALLY_RETARDED => xl('Intermediate Care Facility/Mentally Retarded'),
            self::RESIDENTIAL_SUBSTANCE_ABUSE => xl('Residential Substance Abuse Treatment Facility'),
            self::PSYCHIATRIC_RESIDENTIAL => xl('Psychiatric Residential Treatment Center'),
            self::NONRESIDENTIAL_SUBSTANCE_ABUSE => xl('Non-residential Substance Abuse Treatment Facility'),
            self::UNASSIGNED_58 => xl('Unassigned'),
            self::UNASSIGNED_59 => xl('Unassigned'),
            self::MASS_IMMUNIZATION => xl('Mass Immunization Center'),
            self::COMPREHENSIVE_INPATIENT_REHAB => xl('Comprehensive Inpatient Rehabilitation Facility'),
            self::COMPREHENSIVE_OUTPATIENT_REHAB => xl('Comprehensive Outpatient Rehabilitation Facility'),
            self::UNASSIGNED_63 => xl('Unassigned'),
            self::UNASSIGNED_64 => xl('Unassigned'),
            self::END_STAGE_RENAL_DISEASE => xl('End-Stage Renal Disease Treatment Facility'),
            self::UNASSIGNED_66 => xl('Unassigned'),
            self::UNASSIGNED_67 => xl('Unassigned'),
            self::UNASSIGNED_68 => xl('Unassigned'),
            self::UNASSIGNED_69 => xl('Unassigned'),
            self::UNASSIGNED_70 => xl('Unassigned'),
            self::PUBLIC_HEALTH_CLINIC => xl('Public Health Clinic'),
            self::RURAL_HEALTH_CLINIC => xl('Rural Health Clinic'),
            self::UNASSIGNED_73 => xl('Unassigned'),
            self::UNASSIGNED_74 => xl('Unassigned'),
            self::UNASSIGNED_75 => xl('Unassigned'),
            self::UNASSIGNED_76 => xl('Unassigned'),
            self::UNASSIGNED_77 => xl('Unassigned'),
            self::UNASSIGNED_78 => xl('Unassigned'),
            self::UNASSIGNED_79 => xl('Unassigned'),
            self::UNASSIGNED_80 => xl('Unassigned'),
            self::INDEPENDENT_LABORATORY => xl('Independent Laboratory'),
            self::UNASSIGNED_82 => xl('Unassigned'),
            self::UNASSIGNED_83 => xl('Unassigned'),
            self::UNASSIGNED_84 => xl('Unassigned'),
            self::UNASSIGNED_85 => xl('Unassigned'),
            self::UNASSIGNED_86 => xl('Unassigned'),
            self::UNASSIGNED_87 => xl('Unassigned'),
            self::UNASSIGNED_88 => xl('Unassigned'),
            self::UNASSIGNED_89 => xl('Unassigned'),
            self::UNASSIGNED_90 => xl('Unassigned'),
            self::UNASSIGNED_91 => xl('Unassigned'),
            self::UNASSIGNED_92 => xl('Unassigned'),
            self::UNASSIGNED_93 => xl('Unassigned'),
            self::UNASSIGNED_94 => xl('Unassigned'),
            self::UNASSIGNED_95 => xl('Unassigned'),
            self::UNASSIGNED_96 => xl('Unassigned'),
            self::UNASSIGNED_97 => xl('Unassigned'),
            self::UNASSIGNED_98 => xl('Unassigned'),
            self::OTHER => xl('Other Place of Service')
        };
    }

    /**
     * Get the untranslated description for this place of service
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::PHARMACY => 'A facility or location where drugs and other medically related items and services are sold, dispensed, or otherwise provided directly to patients.',
            self::TELEHEALTH => 'A facility location where health services and health related services are provided or received, through a telecommunication system',
            self::SCHOOL => 'A facility whose primary purpose is education.',
            self::HOMELESS_SHELTER => 'A facility or location whose primary purpose is to provide temporary housing to homeless individuals (e.g., emergency shelters, individual or family shelters).',
            self::IHS_FREESTANDING => 'A facility or location, owned and operated by the Indian Health Service, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services to American Indians and Alaska Natives who do not require hospitalization.',
            self::IHS_PROVIDER_BASED => 'A facility or location, owned and operated by the Indian Health Service, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services rendered by, or under the supervision of, physicians to American Indians and Alaska Natives admitted as inpatients or outpatients.',
            self::TRIBAL_638_FREESTANDING => 'A facility or location owned and operated by a federally recognized American Indian or Alaska Native tribe or tribal organization under a 638 agreement, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services to tribal members who do not require hospitalization.',
            self::TRIBAL_638_PROVIDER_BASED => 'A facility or location owned and operated by a federally recognized American Indian or Alaska Native tribe or tribal organization under a 638 agreement, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services to tribal members admitted as inpatients or outpatients.',
            self::PRISON_CORRECTIONAL => 'A prison, jail, reformatory, work farm, detention center, or any other similar facility maintained by either Federal, State or local authorities for the purpose of confinement or rehabilitation of adult or juvenile criminal offenders.',
            self::UNASSIGNED_10 => 'N/A',
            self::OFFICE => 'Location, other than a hospital, skilled nursing facility (SNF), military treatment facility, community health center, State or local public health clinic, or intermediate care facility (ICF), where the health professional routinely provides health examinations, diagnosis, and treatment of illness or injury on an ambulatory basis.',
            self::HOME => 'Location, other than a hospital or other facility, where the patient receives care in a private residence.',
            self::ASSISTED_LIVING => 'Congregate residential facility with self-contained living units providing assessment of each resident\'s needs and on-site support 24 hours a day, 7 days a week, with the capacity to deliver or arrange for services including some health care and other services.  (effective 10/1/03)',
            self::GROUP_HOME => 'A residence, with shared living areas, where clients receive supervision and other services such as social and/or behavioral services, custodial service, and minimal services (e.g., medication administration).',
            self::MOBILE_UNIT => 'A facility/unit that moves from place-to-place equipped to provide preventive, screening, diagnostic, and/or treatment services.',
            self::TEMPORARY_LODGING => 'A short term accommodation such as a hotel, camp ground, hostel, cruise ship or resort where the patient receives care, and which is not identified by any other POS code.',
            self::WALKIN_RETAIL_HEALTH => 'A walk-in health clinic, other than an office, urgent care facility, pharmacy or independent clinic and not described by any other Place of Service code, that is located within a retail operation and provides, on an ambulatory basis, preventive and primary care services',
            self::PLACE_OF_EMPLOYMENT => 'A location, not described by any other POS code, owned or operated by a public or private entity where the patient is employed, and where a health professional provides on-going or episodic occupational medical, therapeutic or rehabilitative services to the individual',
            self::OFF_CAMPUS_OUTPATIENT => 'A portion of an off-campus hospital provider based department which provides diagnostic, therapeutic (both surgical and nonsurgical), and rehabilitation services to sick or injured persons who do not require hospitalization or institutionalization',
            self::URGENT_CARE => 'Location, distinct from a hospital emergency room, an office, or a clinic, whose purpose is to diagnose and treat illness or injury for unscheduled, ambulatory patients seeking immediate medical attention.',
            self::INPATIENT_HOSPITAL => 'A facility, other than psychiatric, which primarily provides diagnostic, therapeutic (both surgical and nonsurgical), and rehabilitation services by, or under, the supervision of physicians to patients admitted for a variety of medical conditions.',
            self::OUTPATIENT_HOSPITAL => 'A portion of a hospital which provides diagnostic, therapeutic (both surgical and nonsurgical), and rehabilitation services to sick or injured persons who do not require hospitalization or institutionalization.',
            self::EMERGENCY_ROOM => 'A portion of a hospital where emergency diagnosis and treatment of illness or injury is provided.',
            self::AMBULATORY_SURGICAL => 'A freestanding facility, other than a physician\'s office, where surgical and diagnostic services are provided on an ambulatory basis.',
            self::BIRTHING_CENTER => 'A facility, other than a hospital\'s maternity facilities or a physician\'s office, which provides a setting for labor, delivery, and immediate post-partum care as well as immediate care of new born infants.',
            self::MILITARY_TREATMENT => 'A medical facility operated by one or more of the Uniformed Services. Military Treatment Facility (MTF) also refers to certain former U.S. Public Health Service (USPHS) facilities now designated as Uniformed Service Treatment Facilities (USTF).',
            self::UNASSIGNED_27 => 'N/A',
            self::UNASSIGNED_28 => 'N/A',
            self::UNASSIGNED_29 => 'N/A',
            self::UNASSIGNED_30 => 'N/A',
            self::SKILLED_NURSING => 'A facility which primarily provides inpatient skilled nursing care and related services to patients who require medical, nursing, or rehabilitative services but does not provide the level of care or treatment available in a hospital.',
            self::NURSING_FACILITY => 'A facility which primarily provides to residents skilled nursing care and related services for the rehabilitation of injured, disabled, or sick persons, or, on a regular basis, health-related care services above the level of custodial care to other than mentally retarded individuals.',
            self::CUSTODIAL_CARE => 'A facility which provides room, board and other personal assistance services, generally on a long-term basis, and which does not include a medical component.',
            self::HOSPICE => 'A facility, other than a patient\'s home, in which palliative and supportive care for terminally ill patients and their families are provided.',
            self::UNASSIGNED_35 => 'N/A',
            self::UNASSIGNED_36 => 'N/A',
            self::UNASSIGNED_37 => 'N/A',
            self::UNASSIGNED_38 => 'N/A',
            self::UNASSIGNED_39 => 'N/A',
            self::UNASSIGNED_40 => 'N/A',
            self::AMBULANCE_LAND => 'A land vehicle specifically designed, equipped and staffed for lifesaving and transporting the sick or injured.',
            self::AMBULANCE_AIR_WATER => 'An air or water vehicle specifically designed, equipped and staffed for lifesaving and transporting the sick or injured.',
            self::UNASSIGNED_43 => 'N/A',
            self::UNASSIGNED_44 => 'N/A',
            self::UNASSIGNED_45 => 'N/A',
            self::UNASSIGNED_46 => 'N/A',
            self::UNASSIGNED_47 => 'N/A',
            self::UNASSIGNED_48 => 'N/A',
            self::INDEPENDENT_CLINIC => 'A location, not part of a hospital and not described by any other Place of Service code, that is organized and operated to provide preventive, diagnostic, therapeutic, rehabilitative, or palliative services to outpatients only.  (effective 10/1/03)',
            self::FEDERALLY_QUALIFIED_HEALTH => 'A facility located in a medically underserved area that provides Medicare beneficiaries preventive primary medical care under the general direction of a physician.',
            self::INPATIENT_PSYCHIATRIC => 'A facility that provides inpatient psychiatric services for the diagnosis and treatment of mental illness on a 24-hour basis, by or under the supervision of a physician.',
            self::PSYCHIATRIC_PARTIAL_HOSPITALIZATION => 'A facility for the diagnosis and treatment of mental illness that provides a planned therapeutic program for patients who do not require full time hospitalization, but who need broader programs than are possible from outpatient visits to a hospital-based or hospital-affiliated facility.',
            self::COMMUNITY_MENTAL_HEALTH => 'A facility that provides the following services: outpatient services, including specialized outpatient services for children, the elderly, individuals who are chronically ill, and residents of the CMHC\'s mental health services area who have been discharged from inpatient treatment at a mental health facility; 24 hour a day emergency care services; day treatment, other partial hospitalization services, or psychosocial rehabilitation services; screening for patients being considered for admission to State mental health facilities to determine the appropriateness of such admission; and consultation and education services.',
            self::INTERMEDIATE_CARE_MENTALLY_RETARDED => 'A facility which primarily provides health-related care and services above the level of custodial care to mentally retarded individuals but does not provide the level of care or treatment available in a hospital or SNF.',
            self::RESIDENTIAL_SUBSTANCE_ABUSE => 'A facility which provides treatment for substance (alcohol and drug) abuse to live-in residents who do not require acute medical care. Services include individual and group therapy and counseling, family counseling, laboratory tests, drugs and supplies, psychological testing, and room and board.',
            self::PSYCHIATRIC_RESIDENTIAL => 'A facility or distinct part of a facility for psychiatric care which provides a total 24-hour therapeutically planned and professionally staffed group living and learning environment.',
            self::NONRESIDENTIAL_SUBSTANCE_ABUSE => 'A location which provides treatment for substance (alcohol and drug) abuse on an ambulatory basis.  Services include individual and group therapy and counseling, family counseling, laboratory tests, drugs and supplies, and psychological testing.  (effective 10/1/03)',
            self::UNASSIGNED_58 => 'N/A',
            self::UNASSIGNED_59 => 'N/A',
            self::MASS_IMMUNIZATION => 'A location where providers administer pneumococcal pneumonia and influenza virus vaccinations and submit these services as electronic media claims, paper claims, or using the roster billing method. This generally takes place in a mass immunization setting, such as, a public health center, pharmacy, or mall but may include a physician office setting.',
            self::COMPREHENSIVE_INPATIENT_REHAB => 'A facility that provides comprehensive rehabilitation services under the supervision of a physician to inpatients with physical disabilities. Services include physical therapy, occupational therapy, speech pathology, social or psychological services, and orthotics and prosthetics services.',
            self::COMPREHENSIVE_OUTPATIENT_REHAB => 'A facility that provides comprehensive rehabilitation services under the supervision of a physician to outpatients with physical disabilities. Services include physical therapy, occupational therapy, and speech pathology services.',
            self::UNASSIGNED_63 => 'N/A',
            self::UNASSIGNED_64 => 'N/A',
            self::END_STAGE_RENAL_DISEASE => 'A facility other than a hospital, which provides dialysis treatment, maintenance, and/or training to patients or caregivers on an ambulatory or home-care basis.',
            self::UNASSIGNED_66 => 'N/A',
            self::UNASSIGNED_67 => 'N/A',
            self::UNASSIGNED_68 => 'N/A',
            self::UNASSIGNED_69 => 'N/A',
            self::UNASSIGNED_70 => 'N/A',
            self::PUBLIC_HEALTH_CLINIC => 'A facility maintained by either State or local health departments that provides ambulatory primary medical care under the general direction of a physician.  (effective 10/1/03)',
            self::RURAL_HEALTH_CLINIC => 'A certified facility which is located in a rural medically underserved area that provides ambulatory primary medical care under the general direction of a physician.',
            self::UNASSIGNED_73 => 'N/A',
            self::UNASSIGNED_74 => 'N/A',
            self::UNASSIGNED_75 => 'N/A',
            self::UNASSIGNED_76 => 'N/A',
            self::UNASSIGNED_77 => 'N/A',
            self::UNASSIGNED_78 => 'N/A',
            self::UNASSIGNED_79 => 'N/A',
            self::UNASSIGNED_80 => 'N/A',
            self::INDEPENDENT_LABORATORY => 'A laboratory certified to perform diagnostic and/or clinical tests independent of an institution or a physician\'s office.',
            self::UNASSIGNED_82 => 'N/A',
            self::UNASSIGNED_83 => 'N/A',
            self::UNASSIGNED_84 => 'N/A',
            self::UNASSIGNED_85 => 'N/A',
            self::UNASSIGNED_86 => 'N/A',
            self::UNASSIGNED_87 => 'N/A',
            self::UNASSIGNED_88 => 'N/A',
            self::UNASSIGNED_89 => 'N/A',
            self::UNASSIGNED_90 => 'N/A',
            self::UNASSIGNED_91 => 'N/A',
            self::UNASSIGNED_92 => 'N/A',
            self::UNASSIGNED_93 => 'N/A',
            self::UNASSIGNED_94 => 'N/A',
            self::UNASSIGNED_95 => 'N/A',
            self::UNASSIGNED_96 => 'N/A',
            self::UNASSIGNED_97 => 'N/A',
            self::UNASSIGNED_98 => 'N/A',
            self::OTHER => 'Other place of service not identified above.',
        };
    }

    /**
     * Get the code value (same as ->value)
     */
    public function getCode(): string
    {
        return $this->value;
    }

    /**
     * Get enum case from code string
     */
    public static function fromCode(string $code): ?self
    {
        return self::tryFrom($code);
    }
}
