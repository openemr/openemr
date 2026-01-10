<?php

/**
 * EntryLevel.php - Entry-level template facade
 *
 * Aggregates all entry-level templates from individual classes into a single facade
 * to match the JavaScript entryLevel.js pattern where all templates are exported
 * from a single module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Carecoordination\Model\PhpCcdaBuilder\Templates\EntryLevel;

class EntryLevel
{
    // ============================================
    // Allergy Entry Level Templates
    // ============================================

    public static function allergyProblemAct(): array
    {
        return AllergyEntryLevel::allergyProblemAct();
    }

    public static function allergyProblemActNKA(): array
    {
        return AllergyEntryLevel::allergyProblemActNKA();
    }

    public static function allergyIntoleranceObservation(): array
    {
        return AllergyEntryLevel::allergyIntoleranceObservation();
    }

    public static function allergyIntoleranceObservationNKA(): array
    {
        return AllergyEntryLevel::allergyIntoleranceObservationNKA();
    }

    // ============================================
    // Medication Entry Level Templates
    // ============================================

    public static function medicationActivity(): array
    {
        return MedicationEntryLevel::medicationActivity();
    }

    public static function medicationInformation(): array
    {
        return MedicationEntryLevel::medicationInformation();
    }

    // ============================================
    // Problem Entry Level Templates
    // ============================================

    public static function problemConcernAct(): array
    {
        return ProblemEntryLevel::problemConcernAct();
    }

    public static function problemObservation(): array
    {
        return ProblemEntryLevel::problemObservation();
    }

    public static function problemStatus(): array
    {
        return ProblemEntryLevel::problemStatus();
    }

    // ============================================
    // Procedure Entry Level Templates
    // ============================================

    public static function procedureActivityAct(): array
    {
        return ProcedureEntryLevel::procedureActivityAct();
    }

    public static function procedureActivityProcedure(): array
    {
        return ProcedureEntryLevel::procedureActivityProcedure();
    }

    public static function procedureActivityObservation(): array
    {
        return ProcedureEntryLevel::procedureActivityObservation();
    }

    // ============================================
    // Result Entry Level Templates
    // ============================================

    public static function resultOrganizer(): array
    {
        return ResultEntryLevel::resultOrganizer();
    }

    public static function resultObservation(): array
    {
        return ResultEntryLevel::resultObservation();
    }

    // ============================================
    // Vital Sign Entry Level Templates
    // ============================================

    public static function vitalSignsOrganizer(): array
    {
        return VitalSignEntryLevel::vitalSignsOrganizer();
    }

    public static function vitalSignObservation(): array
    {
        return VitalSignEntryLevel::vitalSignObservation();
    }

    // ============================================
    // Immunization Entry Level Templates
    // ============================================

    public static function immunizationActivity(): array
    {
        return ImmunizationEntryLevel::immunizationActivity();
    }

    public static function immunizationMedicationInformation(): array
    {
        return ImmunizationEntryLevel::immunizationMedicationInformation();
    }

    // ============================================
    // Encounter Entry Level Templates
    // ============================================

    public static function encounterActivities(): array
    {
        return EncounterEntryLevel::encounterActivities();
    }

    // ============================================
    // Social History Entry Level Templates
    // ============================================

    public static function socialHistoryObservation(): array
    {
        return SocialHistoryEntryLevel::socialHistoryObservation();
    }

    public static function smokingStatusObservation(): array
    {
        return SocialHistoryEntryLevel::smokingStatusObservation();
    }

    public static function genderStatusObservation(): array
    {
        return SocialHistoryEntryLevel::genderStatusObservation();
    }

    public static function tribalAffiliationObservation(): array
    {
        return SocialHistoryEntryLevel::tribalAffiliationObservation();
    }

    public static function pregnancyStatusObservation(): array
    {
        return SocialHistoryEntryLevel::pregnancyStatusObservation();
    }

    public static function sexualOrientationObservation(): array
    {
        return SocialHistoryEntryLevel::sexualOrientationObservation();
    }

    public static function genderIdentityObservation(): array
    {
        return SocialHistoryEntryLevel::genderIdentityObservation();
    }

    public static function sexObservation(): array
    {
        return SocialHistoryEntryLevel::sexObservation();
    }

    // ============================================
    // Plan of Care Entry Level Templates
    // ============================================

    public static function healthConcernObservation(): array
    {
        return PlanOfCareEntryLevel::healthConcernObservation();
    }

    public static function healthConcernActivityAct(): array
    {
        return PlanOfCareEntryLevel::healthConcernActivityAct();
    }

    public static function planOfCareActivityAct(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivityAct();
    }

    public static function planOfCareActivityObservation(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivityObservation();
    }

    public static function plannedProcedure(): array
    {
        return PlanOfCareEntryLevel::plannedProcedure();
    }

    public static function planOfCareActivityProcedure(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivityProcedure();
    }

    public static function planOfCareActivityEncounter(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivityEncounter();
    }

    public static function planOfCareActivitySubstanceAdministration(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivitySubstanceAdministration();
    }

    public static function planOfCareActivitySupply(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivitySupply();
    }

    public static function planOfCareActivityInstructions(): array
    {
        return PlanOfCareEntryLevel::planOfCareActivityInstructions();
    }

    // ============================================
    // Goal Entry Level Templates
    // ============================================

    public static function goalActivityObservation(): array
    {
        return GoalEntryLevel::goalActivityObservation();
    }

    // ============================================
    // Care Team Entry Level Templates
    // ============================================

    public static function careTeamOrganizer(): array
    {
        return CareTeamEntryLevel::careTeamOrganizer();
    }

    public static function careTeamProviderAct(): array
    {
        return CareTeamEntryLevel::careTeamProviderAct();
    }

    // ============================================
    // Functional Status Entry Level Templates
    // ============================================

    public static function mentalStatusObservation(): array
    {
        return FunctionalStatusEntryLevel::mentalStatusObservation();
    }

    public static function functionalStatusOrganizer(): array
    {
        return FunctionalStatusEntryLevel::functionalStatusOrganizer();
    }

    public static function functionalStatusObservation(): array
    {
        return FunctionalStatusEntryLevel::functionalStatusObservation();
    }

    public static function disabilityStatusObservation(): array
    {
        return FunctionalStatusEntryLevel::disabilityStatusObservation();
    }

    // ============================================
    // Advance Directives Entry Level Templates
    // ============================================

    public static function advanceDirectiveObservation(): array
    {
        return AdvanceDirectivesEntryLevel::advanceDirectiveObservation();
    }

    // ============================================
    // Medical Device Entry Level Templates
    // ============================================

    public static function medicalDeviceActivityProcedure(): array
    {
        return MedicalDeviceEntryLevel::medicalDeviceActivityProcedure();
    }

    // ============================================
    // Payer Entry Level Templates
    // ============================================

    public static function coverageActivity(): array
    {
        return PayerEntryLevel::coverageActivity();
    }

    // ============================================
    // Shared Entry Level Templates (delegated)
    // ============================================

    public static function severityObservation(): array
    {
        return SharedEntryLevel::severityObservation();
    }

    public static function reactionObservation(): array
    {
        return SharedEntryLevel::reactionObservation();
    }

    public static function serviceDeliveryLocation(): array
    {
        return SharedEntryLevel::serviceDeliveryLocation();
    }

    public static function ageObservation(): array
    {
        return SharedEntryLevel::ageObservation();
    }

    public static function indication(): array
    {
        return SharedEntryLevel::indication();
    }

    public static function instructions(): array
    {
        return SharedEntryLevel::instructions();
    }

    public static function encDiagnosis(): array
    {
        return SharedEntryLevel::encDiagnosis();
    }

    public static function notesAct(): array
    {
        return SharedEntryLevel::notesAct();
    }

    public static function drugVehicle(): array
    {
        return SharedEntryLevel::drugVehicle();
    }

    public static function preconditionForSubstanceAdministration(): array
    {
        return SharedEntryLevel::preconditionForSubstanceAdministration();
    }
}
