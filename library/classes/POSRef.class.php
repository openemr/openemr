<?php

class POSRef {
	
	var $pos_ref;
	
	function POSRef($state = "") {
		$this->pos_ref = array();
		$this->pos_ref = POSRef::init_pos();
		$this->pos_ref = array_merge ($this->pos_ref,$this->state_overides($state));	
	}
	
	function init_pos() {
		$pos = array();
		$pos[] = array ("code" => "01","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "02","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "03","title" => "School", "description" => "A facility whose primary purpose is education.");
		$pos[] = array ("code" => "04","title" => "Homeless Shelter", "description" => "A facility or location whose primary purpose is to provide temporary housing to homeless individuals (e.g., emergency shelters, individual or family shelters).");
		$pos[] = array ("code" => "05","title" => "Indian Health Service Free-standing Facility", "description" => "A facility or location, owned and operated by the Indian Health Service, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services to American Indians and Alaska Natives who do not require hospitalization.");
		$pos[] = array ("code" => "06","title" => "Indian Health Service Provider-based Facility", "description" => "A facility or location, owned and operated by the Indian Health Service, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services rendered by, or under the supervision of, physicians to American Indians and Alaska Natives admitted as inpatients or outpatients."); 
		$pos[] = array ("code" => "07","title" => "Tribal 638 Free-standing Facility", "description" => "A facility or location owned and operated by a federally recognized American Indian or Alaska Native tribe or tribal organization under a 638 agreement, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services to tribal members who do not require hospitalization.");
		$pos[] = array ("code" => "08","title" => "Tribal 638 Provider-based Facility", "description" => "A facility or location owned and operated by a federally recognized American Indian or Alaska Native tribe or tribal organization under a 638 agreement, which provides diagnostic, therapeutic (surgical and non-surgical), and rehabilitation services to tribal members admitted as inpatients or outpatients.");
		$pos[] = array ("code" => "09","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "10","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "11","title" => "Office ", "description" => "Location, other than a hospital, skilled nursing facility (SNF), military treatment facility, community health center, State or local public health clinic, or intermediate care facility (ICF), where the health professional routinely provides health examinations, diagnosis, and treatment of illness or injury on an ambulatory basis.");
		$pos[] = array ("code" => "12","title" => "Home", "description" => "Location, other than a hospital or other facility, where the patient receives care in a private residence.");
		$pos[] = array ("code" => "13","title" => "Assisted Living Facility", "description" => "Congregate residential facility with self-contained living units providing assessment of each resident’s needs and on-site support 24 hours a day, 7 days a week, with the capacity to deliver or arrange for services including some health care and other services.  (effective 10/1/03)");
		$pos[] = array ("code" => "14","title" => "Group Home *", "description" => "A residence, with shared living areas, where clients receive supervision and other services such as social and/or behavioral services, custodial service, and minimal services (e.g., medication administration).");
		$pos[] = array ("code" => "15","title" => "Mobile Unit", "description" => "A facility/unit that moves from place-to-place equipped to provide preventive, screening, diagnostic, and/or treatment services.");
		$pos[] = array ("code" => "16","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "17","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "18","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "19","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "20","title" => "Urgent Care Facility", "description" => "Location, distinct from a hospital emergency room, an office, or a clinic, whose purpose is to diagnose and treat illness or injury for unscheduled, ambulatory patients seeking immediate medical attention.");
		$pos[] = array ("code" => "21","title" => "Inpatient Hospital", "description" => "A facility, other than psychiatric, which primarily provides diagnostic, therapeutic (both surgical and nonsurgical), and rehabilitation services by, or under, the supervision of physicians to patients admitted for a variety of medical conditions.");
		$pos[] = array ("code" => "22","title" => "Outpatient Hospital", "description" => "A portion of a hospital which provides diagnostic, therapeutic (both surgical and nonsurgical), and rehabilitation services to sick or injured persons who do not require hospitalization or institutionalization.");
		$pos[] = array ("code" => "23","title" => "Emergency Room - Hospital", "description" => "A portion of a hospital where emergency diagnosis and treatment of illness or injury is provided.");
		$pos[] = array ("code" => "24","title" => "Ambulatory Surgical Center", "description" => "A freestanding facility, other than a physician's office, where surgical and diagnostic services are provided on an ambulatory basis.");
		$pos[] = array ("code" => "25","title" => "Birthing Center ", "description" => "A facility, other than a hospital's maternity facilities or a physician's office, which provides a setting for labor, delivery, and immediate post-partum care as well as immediate care of new born infants.");
		$pos[] = array ("code" => "26","title" => "Military Treatment Facility", "description" => "A medical facility operated by one or more of the Uniformed Services. Military Treatment Facility (MTF) also refers to certain former U.S. Public Health Service (USPHS) facilities now designated as Uniformed Service Treatment Facilities (USTF).");
		$pos[] = array ("code" => "27","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "28","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "29","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "30","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "31","title" => "Skilled Nursing Facility", "description" => "A facility which primarily provides inpatient skilled nursing care and related services to patients who require medical, nursing, or rehabilitative services but does not provide the level of care or treatment available in a hospital.");
		$pos[] = array ("code" => "32","title" => "Nursing Facility", "description" => "A facility which primarily provides to residents skilled nursing care and related services for the rehabilitation of injured, disabled, or sick persons, or, on a regular basis, health-related care services above the level of custodial care to other than mentally retarded individuals.");
		$pos[] = array ("code" => "33","title" => "Custodial Care Facility", "description" => "A facility which provides room, board and other personal assistance services, generally on a long-term basis, and which does not include a medical component.");
		$pos[] = array ("code" => "34","title" => "Hospice", "description" => "A facility, other than a patient's home, in which palliative and supportive care for terminally ill patients and their families are provided.");
		$pos[] = array ("code" => "35","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "36","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "37","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "38","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "39","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "40","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "41","title" => "Ambulance - Land", "description" => "A land vehicle specifically designed, equipped and staffed for lifesaving and transporting the sick or injured.");
		$pos[] = array ("code" => "42","title" => "Ambulance - Air or Water", "description" => "An air or water vehicle specifically designed, equipped and staffed for lifesaving and transporting the sick or injured.");
		$pos[] = array ("code" => "43","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "44","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "45","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "46","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "47","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "48","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "49","title" => "Independent Clinic", "description" => "A location, not part of a hospital and not described by any other Place of Service code, that is organized and operated to provide preventive, diagnostic, therapeutic, rehabilitative, or palliative services to outpatients only.  (effective 10/1/03)");
		$pos[] = array ("code" => "50","title" => "Federally Qualified Health Center", "description" => "A facility located in a medically underserved area that provides Medicare beneficiaries preventive primary medical care under the general direction of a physician.");
		$pos[] = array ("code" => "51","title" => "Inpatient Psychiatric Facility", "description" => "A facility that provides inpatient psychiatric services for the diagnosis and treatment of mental illness on a 24-hour basis, by or under the supervision of a physician.");
		$pos[] = array ("code" => "52","title" => "Psychiatric Facility-Partial Hospitalization", "description" => "A facility for the diagnosis and treatment of mental illness that provides a planned therapeutic program for patients who do not require full time hospitalization, but who need broader programs than are possible from outpatient visits to a hospital-based or hospital-affiliated facility.");
		$pos[] = array ("code" => "53","title" => "Community Mental Health Center", "description" => "A facility that provides the following services: outpatient services, including specialized outpatient services for children, the elderly, individuals who are chronically ill, and residents of the CMHC's mental health services area who have been discharged from inpatient treatment at a mental health facility; 24 hour a day emergency care services; day treatment, other partial hospitalization services, or psychosocial rehabilitation services; screening for patients being considered for admission to State mental health facilities to determine the appropriateness of such admission; and consultation and education services.");
		$pos[] = array ("code" => "54","title" => "Intermediate Care Facility/Mentally Retarded", "description" => "A facility which primarily provides health-related care and services above the level of custodial care to mentally retarded individuals but does not provide the level of care or treatment available in a hospital or SNF.");
		$pos[] = array ("code" => "55","title" => "Residential Substance Abuse Treatment Facility", "description" => "A facility which provides treatment for substance (alcohol and drug) abuse to live-in residents who do not require acute medical care. Services include individual and group therapy and counseling, family counseling, laboratory tests, drugs and supplies, psychological testing, and room and board.");
		$pos[] = array ("code" => "56","title" => "Psychiatric Residential Treatment Center", "description" => "A facility or distinct part of a facility for psychiatric care which provides a total 24-hour therapeutically planned and professionally staffed group living and learning environment.");
		$pos[] = array ("code" => "57","title" => "Non-residential Substance Abuse Treatment Facility", "description" => "A location which provides treatment for substance (alcohol and drug) abuse on an ambulatory basis.  Services include individual and group therapy and counseling, family counseling, laboratory tests, drugs and supplies, and psychological testing.  (effective 10/1/03)");
		$pos[] = array ("code" => "58","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "59","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "60","title" => "Mass Immunization Center", "description" => "A location where providers administer pneumococcal pneumonia and influenza virus vaccinations and submit these services as electronic media claims, paper claims, or using the roster billing method. This generally takes place in a mass immunization setting, such as, a public health center, pharmacy, or mall but may include a physician office setting.");
		$pos[] = array ("code" => "61","title" => "Comprehensive Inpatient Rehabilitation Facility", "description" => "A facility that provides comprehensive rehabilitation services under the supervision of a physician to inpatients with physical disabilities. Services include physical therapy, occupational therapy, speech pathology, social or psychological services, and orthotics and prosthetics services.");
		$pos[] = array ("code" => "62","title" => "Comprehensive Outpatient Rehabilitation Facility", "description" => "A facility that provides comprehensive rehabilitation services under the supervision of a physician to outpatients with physical disabilities. Services include physical therapy, occupational therapy, and speech pathology services.");
		$pos[] = array ("code" => "63","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "64","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "65","title" => "End-Stage Renal Disease Treatment Facility", "description" => "A facility other than a hospital, which provides dialysis treatment, maintenance, and/or training to patients or caregivers on an ambulatory or home-care basis.");
		$pos[] = array ("code" => "66","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "67","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "68","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "69","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "70","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "71","title" => "Public Health Clinic", "description" => "A facility maintained by either State or local health departments that provides ambulatory primary medical care under the general direction of a physician.  (effective 10/1/03)");
		$pos[] = array ("code" => "72","title" => "Rural Health Clinic", "description" => "A certified facility which is located in a rural medically underserved area that provides ambulatory primary medical care under the general direction of a physician.");
		$pos[] = array ("code" => "73","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "74","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "75","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "76","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "77","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "78","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "79","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "80","title" => "Unassigned", "description" => "N/A");	
		$pos[] = array ("code" => "81","title" => "Independent Laboratory", "description" => "A laboratory certified to perform diagnostic and/or clinical tests independent of an institution or a physician's office.");
		$pos[] = array ("code" => "82","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "83","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "84","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "85","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "86","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "87","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "88","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "89","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "90","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "91","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "92","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "93","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "94","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "95","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "96","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "97","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "98","title" => "Unassigned", "description" => "N/A");
		$pos[] = array ("code" => "99","title" => "Other Place of Service", "description" => "Other place of service not identified above. ");
		return $pos;
	} 
	function state_overides($state) {
		$pos = array();
		switch (strtoupper($state)) {
			case "CA":
				break;
			default:
				break;	
		}
		return $pos;
	}
	
	function get_pos_ref() {
		return $this->pos_ref;	
	}
}
?>
