// @vitest-environment jsdom
import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { renderToStaticMarkup } from "react-dom/server";

vi.mock("next/headers", () => ({
  cookies: async () => ({
    get: () => undefined,
    has: () => false,
    set: () => {},
  }),
}));

import { StatCards } from "@/components/StatCards";

beforeEach(() => {
  vi.restoreAllMocks();
  vi.stubEnv("DASHBOARD_PUBLIC_URL", "https://dashboard.example.com");
});
afterEach(() => {
  vi.unstubAllEnvs();
  vi.restoreAllMocks();
});

function totalsBundle(total: number) {
  return new Response(JSON.stringify({ resourceType: "Bundle", type: "searchset", total }), {
    status: 200,
  });
}

describe("StatCards", () => {
  it("renders the three labelled tiles", async () => {
    vi.spyOn(globalThis, "fetch").mockImplementation(async () => totalsBundle(0));
    const html = renderToStaticMarkup(await StatCards());
    expect(html).toContain("Patients");
    expect(html).toContain("Encounters");
    expect(html).toContain("Active medications");
  });

  it("formats counts with thousands separators", async () => {
    let i = 0;
    const values = [1234, 56789, 7];
    vi.spyOn(globalThis, "fetch").mockImplementation(async () => totalsBundle(values[i++]!));
    const html = renderToStaticMarkup(await StatCards());
    expect(html).toContain("1,234");
    expect(html).toContain("56,789");
    expect(html).toContain(">7<"); // bare 7 still rendered
  });

  it("renders an em-dash when a single fetch fails (others still render)", async () => {
    let i = 0;
    vi.spyOn(globalThis, "fetch").mockImplementation(async () => {
      i++;
      if (i === 2) return new Response("nope", { status: 500 });
      return totalsBundle(42);
    });
    const html = renderToStaticMarkup(await StatCards());
    expect(html).toContain("42");
    expect(html).toContain("—"); // failed tile shows em-dash, page does not crash
  });

  it("requests _count=1 to keep responses tiny while still getting Bundle.total (OpenEMR doesn't honor _summary=count)", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockImplementation(async () => totalsBundle(0));
    await StatCards();
    const calledUrls = fetchMock.mock.calls.map((c) => String(c[0]));
    for (const url of calledUrls) {
      expect(url).toMatch(/_count=1/);
    }
    // Patient and Encounter use ? prefix; MedicationRequest already has ?status=, should use &
    expect(calledUrls.some((u) => u.endsWith("/Patient?_count=1"))).toBe(true);
    expect(calledUrls.some((u) => u.endsWith("/Encounter?_count=1"))).toBe(true);
    expect(calledUrls.some((u) => u.endsWith("MedicationRequest?status=active&_count=1"))).toBe(true);
  });

  it("shows em-dash when Bundle.total is missing", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify({ resourceType: "Bundle", type: "searchset" }), { status: 200 }),
    );
    const html = renderToStaticMarkup(await StatCards());
    // All three tiles fail to read total, all show em-dash
    const dashes = html.match(/—/g) ?? [];
    expect(dashes.length).toBeGreaterThanOrEqual(3);
  });
});
