"use strict";

var allergyEntryLevel = require("./allergyEntryLevel");
var resultEntryLevel = require("./resultEntryLevel");
var socialHistoryEntryLevel = require('./socialHistoryEntryLevel');
var payerEntryLevel = require('./payerEntryLevel');
var vitalSignEntryLevel = require('./vitalSignEntryLevel');
var functionalStatusEntryLevel = require('./functionalStatusEntryLevel');
var planOfCareEntryLevel = require('./planOfCareEntryLevel');
var goalEntryLevel = require('./goalEntryLevel');
var procedureEntryLevel = require("./procedureEntryLevel");
var problemEntryLevel = require("./problemEntryLevel");
var encounterEntryLevel = require("./encounterEntryLevel");
var immunizationEntryLevel = require("./immunizationEntryLevel");
var medicationEntryLevel = require("./medicationEntryLevel");
var medicalDeviceEntryLevel = require("./medicalDeviceEntryLevel");
var careTeamEntryLevel = require('./careTeamEntryLevel');

const advanceDirectivesEntryLevel = require("./advanceDirectivesEntryLevel");

exports.allergyProblemAct = allergyEntryLevel.allergyProblemAct;
exports.allergyProblemActNKA = allergyEntryLevel.allergyProblemActNKA;

exports.medicationActivity = medicationEntryLevel.medicationActivity;

exports.immunizationActivity = immunizationEntryLevel.immunizationActivity;

exports.problemConcernAct = problemEntryLevel.problemConcernAct;

exports.encounterActivities = encounterEntryLevel.encounterActivities;

exports.procedureActivityAct = procedureEntryLevel.procedureActivityAct;
exports.procedureActivityProcedure = procedureEntryLevel.procedureActivityProcedure;
exports.procedureActivityObservation = procedureEntryLevel.procedureActivityObservation;

exports.planOfCareActivityAct = planOfCareEntryLevel.planOfCareActivityAct;
exports.planOfCareActivityObservation = planOfCareEntryLevel.planOfCareActivityObservation;
exports.planOfCareActivityProcedure = planOfCareEntryLevel.planOfCareActivityProcedure;
exports.planOfCarePlannedProcedure = planOfCareEntryLevel.planOfCarePlannedProcedure;
exports.planOfCareActivityEncounter = planOfCareEntryLevel.planOfCareActivityEncounter;
exports.planOfCareActivitySubstanceAdministration = planOfCareEntryLevel.planOfCareActivitySubstanceAdministration;
exports.planOfCareActivitySupply = planOfCareEntryLevel.planOfCareActivitySupply;
exports.planOfCareActivityInstructions = planOfCareEntryLevel.planOfCareActivityInstructions;
exports.healthConcernActWithProblemObservation = planOfCareEntryLevel.healthConcernActWithProblemObservation;
exports.healthConcernActivityAct = planOfCareEntryLevel.healthConcernActivityAct;
exports.healthConcernObservation = planOfCareEntryLevel.healthConcernObservation;

exports.goalActivityObservation = goalEntryLevel.goalActivityObservation;

exports.coverageActivity = payerEntryLevel.coverageActivity;

exports.vitalSignsOrganizer = vitalSignEntryLevel.vitalSignsOrganizer;
exports.functionalStatusOrganizer = functionalStatusEntryLevel.functionalStatusOrganizer;
exports.mentalStatusObservation = functionalStatusEntryLevel.mentalStatusObservation;
exports.disabilityStatusObservation = functionalStatusEntryLevel.disabilityStatusObservation;
exports.resultOrganizer = resultEntryLevel.resultOrganizer;
exports.careTeamOrganizer = careTeamEntryLevel.careTeamOrganizer;

exports.advanceDirectiveOrganizer = advanceDirectivesEntryLevel.advanceDirectiveOrganizer;

exports.socialHistoryObservation = socialHistoryEntryLevel.socialHistoryObservation;
exports.smokingStatusObservation = socialHistoryEntryLevel.smokingStatusObservation;
exports.genderStatusObservation = socialHistoryEntryLevel.genderStatusObservation;
exports.occupationObservation = socialHistoryEntryLevel.occupationObservation;
exports.tribalAffiliationObservation = socialHistoryEntryLevel.tribalAffiliationObservation;
exports.pregnancyStatusObservation = socialHistoryEntryLevel.pregnancyStatusObservation;
exports.hungerVitalSignsObservation = socialHistoryEntryLevel.hungerVitalSignsObservation;
exports.disabilityAssessmentObservation = socialHistoryEntryLevel.disabilityAssessmentObservation;

exports.medicalDeviceActivityProcedure = medicalDeviceEntryLevel.medicalDeviceActivityProcedure;
