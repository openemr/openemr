var templates = require("./templates.js");
var sections = require("./sections.js");
var statements = require("./clinicalstatements.js");

var templatesconstraints = require("./templates-constraints.js");
var sectionsconstraints = require("./sections-constraints.js");
var codeSystems = require("./code-systems.js");

//General Header Constraints
var CCDA = {
    "document": {
        "name": "CCDA",
        "templateId": "2.16.840.1.113883.10.20.22.1.1"
    },
    "templates": templates,
    "sections": sections.sections,
    "sections_r1": sections.sections_r1,
    "statements": statements.clinicalstatements,
    "statements_r1": statements.clinicalstatements_r1,
    "constraints": {
        "sections": sectionsconstraints,
        "templates": templatesconstraints
    },
    "codeSystems": codeSystems.codeSystems,
    "sections_entries_codes": codeSystems.sections_entries_codes

    /*
		,
    //DOCUMENT-LEVEL TEMPLATES
    "templates":[
		{
			"name":"Consultation Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.4"
		},
		{
			"name":"Continuity Of Care Document",
			"templateId":"2.16.840.1.113883.10.20.22.1.2"
		},
		{
			"name":"Diagnostic Imaging Report",
			"templateId":"2.16.840.1.113883.10.20.22.1.5"
		},
		{
			"name":"Discharge Summary",
			"templateId":"2.16.840.1.113883.10.20.22.1.8"
		},
		{
			"name":"History And Physical Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.3"
		},
		{
			"name":"Operative Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.7"
		},
		{
			"name":"Procedure Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.6"
		},
		{
			"name":"Progress Note",
			"templateId":"2.16.840.1.113883.10.20.22.1.9"
		},
		{
			"name":"Unstructured Document",
			"templateId":"2.16.840.1.113883.10.20.21.1.10"
		},
    ],
    //Sections
    "sections":[
		{"name": "Allergies",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.6', '2.16.840.1.113883.10.20.22.2.6.1']
		},
		{"name": "Encounters",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.22', '2.16.840.1.113883.10.20.22.2.22.1']
		},
		{"name": "Immunizations",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.2", "2.16.840.1.113883.10.20.22.2.2.1"]
		},
		{"name": "Medications",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.1", "2.16.840.1.113883.10.20.22.2.1.1"]
		},
		{"name": "Problems",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.5.1"]
		},
		{"name": "Procedures",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.7', '2.16.840.1.113883.10.20.22.2.7.1']
		},
		{"name": "Results",
			"templateIds": ['2.16.840.1.113883.10.20.22.2.3', '2.16.840.1.113883.10.20.22.2.3.1']
		},
		{"name": "Vital Signs",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.4","2.16.840.1.113883.10.20.22.2.4.1"]
		},
		{"name": "Social History",
			"templateIds": ["2.16.840.1.113883.10.20.22.2.17"]
		}		
    ]
    */
};

//Good source http://cdatools.org/SectionMatrix.html
//and http://cdatools.org/ClinicalStatementMatrix.html

module.exports = exports = CCDA;
