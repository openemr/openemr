import { describe, it, expect } from "vitest";
import { bundleEntries, findIdentifier } from "@/lib/fhir/bundle";
import type { Bundle, Identifier, Patient } from "@/lib/fhir/types";

describe("bundleEntries", () => {
  it("returns [] for null / undefined / empty", () => {
    expect(bundleEntries(null)).toEqual([]);
    expect(bundleEntries(undefined)).toEqual([]);
    expect(bundleEntries({ resourceType: "Bundle" } as Bundle<Patient>)).toEqual([]);
  });

  it("flattens entries to resources", () => {
    const b: Bundle<Patient> = {
      resourceType: "Bundle",
      entry: [
        { resource: { resourceType: "Patient", id: "1" } },
        { resource: { resourceType: "Patient", id: "2" } },
      ],
    };
    const out = bundleEntries(b);
    expect(out.length).toBe(2);
    expect(out[0].id).toBe("1");
  });

  it("skips entries without a resource", () => {
    const b: Bundle<Patient> = {
      resourceType: "Bundle",
      entry: [{ fullUrl: "x" }, { resource: { resourceType: "Patient", id: "1" } }],
    };
    expect(bundleEntries(b).length).toBe(1);
  });
});

describe("findIdentifier", () => {
  const ids: Identifier[] = [
    { system: "http://hl7.org/fhir/sid/us-mrn", value: "MRN-123" },
    { type: { coding: [{ code: "MR" }] }, value: "MRN-456" },
    { system: "http://other.example.com/id", value: "OTH-789" },
  ];

  it("finds by system match (case-insensitive)", () => {
    expect(findIdentifier(ids, ["http://HL7.org/fhir/sid/us-MRN"])).toBe("MRN-123");
  });

  it("falls back to type.coding.code match", () => {
    expect(findIdentifier(ids, ["http://nope.example.com"], ["mr"])).toBe("MRN-456");
  });

  it("returns null when nothing matches", () => {
    expect(findIdentifier(ids, ["http://nope"], ["XX"])).toBeNull();
  });

  it("returns null on undefined / empty", () => {
    expect(findIdentifier(undefined, ["x"])).toBeNull();
    expect(findIdentifier([], ["x"])).toBeNull();
  });
});
