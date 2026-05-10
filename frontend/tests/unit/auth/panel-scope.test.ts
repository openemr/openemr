import { describe, it, expect } from "vitest";
import { inPanel, parseAdminAllowlist } from "@/lib/auth/panel-scope";
import type { Patient } from "@/lib/fhir/types";

const ADMIN = ["admin-user", "another-admin"];

function patientWithGPs(refs: string[]): Patient {
  return {
    resourceType: "Patient",
    id: "p1",
    generalPractitioner: refs.map((r) => ({ reference: r })),
  };
}

describe("inPanel", () => {
  it("admin in allowlist → allow regardless of GP", () => {
    expect(inPanel(patientWithGPs(["Practitioner/someone-else"]), "admin-user", ADMIN)).toBe("allow");
    expect(inPanel(null, "admin-user", ADMIN)).toBe("allow");
  });

  it("non-admin with matching Practitioner reference → allow", () => {
    const patient = patientWithGPs(["Practitioner/dr-jones", "Practitioner/dr-smith"]);
    expect(inPanel(patient, "dr-smith", ADMIN)).toBe("allow");
  });

  it("non-admin with no match → deny", () => {
    const patient = patientWithGPs(["Practitioner/dr-jones"]);
    expect(inPanel(patient, "dr-smith", ADMIN)).toBe("deny");
  });

  it("non-admin with empty generalPractitioner → unknown-gp-fallthrough", () => {
    const patient: Patient = { resourceType: "Patient", id: "p1" };
    expect(inPanel(patient, "dr-smith", ADMIN)).toBe("unknown-gp-fallthrough");
    const patient2 = patientWithGPs([]);
    expect(inPanel(patient2, "dr-smith", ADMIN)).toBe("unknown-gp-fallthrough");
  });

  it("non-admin with non-Practitioner references in GP list → unknown-gp-fallthrough (none match)", () => {
    const patient = patientWithGPs(["Organization/some-org"]);
    expect(inPanel(patient, "dr-smith", ADMIN)).toBe("unknown-gp-fallthrough");
  });

  it("missing username → unknown-gp-fallthrough (no basis to match)", () => {
    expect(inPanel(patientWithGPs(["Practitioner/dr-jones"]), undefined, ADMIN)).toBe("unknown-gp-fallthrough");
    expect(inPanel(null, undefined, ADMIN)).toBe("unknown-gp-fallthrough");
  });

  it("null patient with valid username → deny (couldn't fetch the patient)", () => {
    expect(inPanel(null, "dr-smith", ADMIN)).toBe("deny");
  });

  it("case-sensitive username comparison", () => {
    const patient = patientWithGPs(["Practitioner/Dr-Smith"]);
    expect(inPanel(patient, "dr-smith", ADMIN)).toBe("deny");
    expect(inPanel(patient, "Dr-Smith", ADMIN)).toBe("allow");
  });
});

describe("parseAdminAllowlist", () => {
  it("returns [] for undefined / empty", () => {
    expect(parseAdminAllowlist(undefined)).toEqual([]);
    expect(parseAdminAllowlist("")).toEqual([]);
  });

  it("parses comma-separated, trims whitespace, drops empties", () => {
    expect(parseAdminAllowlist("admin")).toEqual(["admin"]);
    expect(parseAdminAllowlist("admin, second-admin")).toEqual(["admin", "second-admin"]);
    expect(parseAdminAllowlist("a,, b ,c")).toEqual(["a", "b", "c"]);
  });
});
