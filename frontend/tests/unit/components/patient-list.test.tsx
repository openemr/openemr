// @vitest-environment jsdom
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { renderToStaticMarkup } from "react-dom/server";

// Mock next/headers cookies() so fhirGet doesn't call into Next's request
// context (unavailable in unit tests).
vi.mock("next/headers", () => ({
  cookies: async () => ({
    get: () => undefined,
    has: () => false,
    set: () => {},
  }),
}));

import { PatientList } from "@/components/PatientList";

const PATIENT_BUNDLE = {
  resourceType: "Bundle",
  type: "searchset",
  total: 2,
  entry: [
    {
      resource: {
        resourceType: "Patient",
        id: "uuid-aaaa",
        name: [{ use: "official", family: "Doe", given: ["John"] }],
        birthDate: "1980-01-15",
        gender: "male",
        identifier: [{ type: { coding: [{ code: "MR" }] }, value: "MRN-101" }],
      },
    },
    {
      resource: {
        resourceType: "Patient",
        id: "uuid-bbbb",
        name: [{ family: "Smith", given: ["Jane"] }],
        birthDate: "1992-08-03",
        gender: "female",
        identifier: [{ type: { coding: [{ code: "MR" }] }, value: "MRN-202" }],
      },
    },
  ],
};

beforeEach(() => {
  vi.restoreAllMocks();
  vi.stubEnv("DASHBOARD_PUBLIC_URL", "https://dashboard.example.com");
});
afterEach(() => {
  vi.unstubAllEnvs();
  vi.restoreAllMocks();
});

describe("PatientList", () => {
  it("renders a clickable row per patient", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify(PATIENT_BUNDLE), { status: 200 }),
    );
    const node = await PatientList();
    const html = renderToStaticMarkup(node);
    expect(html).toContain("John Doe");
    expect(html).toContain("Jane Smith");
    // Row links use /patient/<id>
    expect(html).toContain('href="/patient/uuid-aaaa"');
    expect(html).toContain('href="/patient/uuid-bbbb"');
    // MRN renders
    expect(html).toContain("MRN-101");
    expect(html).toContain("MRN-202");
  });

  it("calls the FHIR proxy with sort + count parameters", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify(PATIENT_BUNDLE), { status: 200 }),
    );
    await PatientList({ count: 10 });
    const url = String(fetchMock.mock.calls[0][0]);
    expect(url).toContain("/api/fhir/Patient?_count=10&_sort=family");
  });

  it("renders empty state when bundle has no entries", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify({ resourceType: "Bundle", type: "searchset", total: 0 }), {
        status: 200,
      }),
    );
    const html = renderToStaticMarkup(await PatientList());
    expect(html).toContain("No patients found");
  });

  it("renders error state on FHIR failure", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response("nope", { status: 500 }),
    );
    const html = renderToStaticMarkup(await PatientList());
    expect(html).toContain("Could not load patient list");
    expect(html).toContain("(500)");
  });

  it("hints to re-sign-in on 401", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("nope", { status: 401 }));
    const html = renderToStaticMarkup(await PatientList());
    expect(html).toContain("Try signing out and back in");
  });

  it("URL-encodes the patient id in the row link", async () => {
    const bundle = {
      ...PATIENT_BUNDLE,
      entry: [
        {
          resource: {
            resourceType: "Patient",
            id: "weird id with space",
            name: [{ family: "Nguyen" }],
          },
        },
      ],
      total: 1,
    };
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify(bundle), { status: 200 }),
    );
    const html = renderToStaticMarkup(await PatientList());
    expect(html).toContain('href="/patient/weird%20id%20with%20space"');
  });

  it("shows total count in the header when total > displayed", async () => {
    const bundle = { ...PATIENT_BUNDLE, total: 50 };
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify(bundle), { status: 200 }),
    );
    const html = renderToStaticMarkup(await PatientList());
    expect(html).toMatch(/Showing 2[^<]*of 50/);
  });
});
