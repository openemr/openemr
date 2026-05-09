/**
 * Subset of FHIR R4 / US Core 3.1.0 types we render in the dashboard.
 * Only the fields actually referenced by the UI are typed; extra fields
 * on the wire are tolerated and ignored.
 */

export interface Coding {
  system?: string;
  code?: string;
  display?: string;
}

export interface CodeableConcept {
  text?: string;
  coding?: Coding[];
}

export interface Reference {
  reference?: string;
  display?: string;
}

export interface Identifier {
  system?: string;
  value?: string;
  type?: CodeableConcept;
}

export interface HumanName {
  use?: string;
  text?: string;
  family?: string;
  given?: string[];
  prefix?: string[];
  suffix?: string[];
}

export interface Period {
  start?: string;
  end?: string;
}

export interface BundleEntry<T> {
  resource?: T;
  fullUrl?: string;
}

export interface Bundle<T> {
  resourceType: "Bundle";
  type?: string;
  total?: number;
  entry?: BundleEntry<T>[];
}

export interface Patient {
  resourceType: "Patient";
  id?: string;
  active?: boolean;
  name?: HumanName[];
  birthDate?: string;
  gender?: "male" | "female" | "other" | "unknown";
  identifier?: Identifier[];
  generalPractitioner?: Reference[];
}

export interface AllergyIntolerance {
  resourceType: "AllergyIntolerance";
  id?: string;
  clinicalStatus?: CodeableConcept;
  verificationStatus?: CodeableConcept;
  category?: string[];
  criticality?: "low" | "high" | "unable-to-assess";
  code?: CodeableConcept;
  recordedDate?: string;
  reaction?: Array<{
    manifestation?: CodeableConcept[];
    severity?: "mild" | "moderate" | "severe";
  }>;
}

export interface Condition {
  resourceType: "Condition";
  id?: string;
  clinicalStatus?: CodeableConcept;
  category?: CodeableConcept[];
  code?: CodeableConcept;
  onsetDateTime?: string;
}

export interface MedicationRequest {
  resourceType: "MedicationRequest";
  id?: string;
  status?: string;
  intent?: string;
  medicationCodeableConcept?: CodeableConcept;
  medicationReference?: Reference;
  authoredOn?: string;
  dosageInstruction?: Array<{
    text?: string;
  }>;
}

export interface CareTeam {
  resourceType: "CareTeam";
  id?: string;
  status?: string;
  name?: string;
  participant?: Array<{
    member?: Reference;
    role?: CodeableConcept[];
  }>;
}

export interface Encounter {
  resourceType: "Encounter";
  id?: string;
  status?: string;
  class?: Coding;
  type?: CodeableConcept[];
  period?: Period;
  reasonCode?: CodeableConcept[];
  serviceProvider?: Reference;
}
