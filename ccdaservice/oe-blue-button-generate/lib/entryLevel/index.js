"use strict";

var allergyEntryLevel = require("./allergyEntryLevel");
var resultEntryLevel = require("./resultEntryLevel");
var socialHistoryEntryLevel = require('./socialHistoryEntryLevel');
var payerEntryLevel = require('./payerEntryLevel');
var vitalSignEntryLevel = require('./vitalSignEntryLevel');
var planOfCareEntryLevel = require('./planOfCareEntryLevel');
var procedureEntryLevel = require("./procedureEntryLevel");
var problemEntryLevel = require("./problemEntryLevel");
var encounterEntryLevel = require("./encounterEntryLevel");
var immunizationEntryLevel = require("./immunizationEntryLevel");
var medicationEntryLevel = require("./medicationEntryLevel");

exports.allergyProblemAct = allergyEntryLevel.allergyProblemAct;

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
exports.planOfCareActivityEncounter = planOfCareEntryLevel.planOfCareActivityEncounter;
exports.planOfCareActivitySubstanceAdministration = planOfCareEntryLevel.planOfCareActivitySubstanceAdministration;
exports.planOfCareActivitySupply = planOfCareEntryLevel.planOfCareActivitySupply;
exports.planOfCareActivityInstructions = planOfCareEntryLevel.planOfCareActivityInstructions;

exports.coverageActivity = payerEntryLevel.coverageActivity;

exports.vitalSignsOrganizer = vitalSignEntryLevel.vitalSignsOrganizer;

exports.resultOrganizer = resultEntryLevel.resultOrganizer;

exports.socialHistoryObservation = socialHistoryEntryLevel.socialHistoryObservation;
exports.smokingStatusObservation = socialHistoryEntryLevel.smokingStatusObservation;
