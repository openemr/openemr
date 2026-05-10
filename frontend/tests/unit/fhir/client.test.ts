import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { fhirGet, FhirError } from "@/lib/fhir/client";

beforeEach(() => {
  vi.restoreAllMocks();
  vi.stubEnv("DASHBOARD_PUBLIC_URL", "https://dashboard.example.com");
});
afterEach(() => {
  vi.unstubAllEnvs();
  vi.restoreAllMocks();
});

describe("fhirGet", () => {
  it("happy path: fetches absolute URL through /api/fhir, returns parsed JSON", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      new Response(JSON.stringify({ resourceType: "Patient", id: "abc" }), {
        status: 200,
        headers: { "content-type": "application/fhir+json" },
      }),
    );
    const out = await fhirGet<{ resourceType: string; id: string }>(
      "Patient/abc",
      { cookieHeader: "dashboard_session=signed.thing" },
    );
    expect(out.id).toBe("abc");
    expect(fetchMock).toHaveBeenCalledOnce();
    const [url, init] = fetchMock.mock.calls[0];
    expect(url).toBe("https://dashboard.example.com/api/fhir/Patient/abc");
    const initObj = init as RequestInit & { cache?: string };
    expect(initObj.cache).toBe("no-store");
    const headers = new Headers(initObj.headers as HeadersInit);
    expect(headers.get("Accept")).toBe("application/fhir+json");
    expect(headers.get("Cookie")).toBe("dashboard_session=signed.thing");
  });

  it("prepends a leading slash to the path", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("{}", { status: 200 }));
    await fhirGet("AllergyIntolerance?patient=1", { cookieHeader: "" });
    const [url] = fetchMock.mock.calls[0];
    expect(url).toBe("https://dashboard.example.com/api/fhir/AllergyIntolerance?patient=1");
  });

  it("supports leading-slash path", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("{}", { status: 200 }));
    await fhirGet("/Patient/1", { cookieHeader: "" });
    const [url] = fetchMock.mock.calls[0];
    expect(url).toBe("https://dashboard.example.com/api/fhir/Patient/1");
  });

  it("throws FhirError on non-200 with the upstream status", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("nope", { status: 401 }));
    await expect(fhirGet("Patient/1", { cookieHeader: "" })).rejects.toMatchObject({
      name: "FhirError",
      status: 401,
    });
  });

  it("throws FhirError when DASHBOARD_PUBLIC_URL missing", async () => {
    vi.stubEnv("DASHBOARD_PUBLIC_URL", "");
    await expect(fhirGet("Patient/1", { cookieHeader: "" })).rejects.toBeInstanceOf(FhirError);
  });

  it("custom Accept header is forwarded", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("{}", { status: 200 }));
    await fhirGet("Patient/1", { cookieHeader: "", accept: "application/json" });
    const init = fetchMock.mock.calls[0][1] as RequestInit;
    expect(new Headers(init.headers as HeadersInit).get("Accept")).toBe("application/json");
  });

  it("does not set a Cookie header when cookieHeader is empty string", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response("{}", { status: 200 }));
    await fhirGet("Patient/1", { cookieHeader: "" });
    const init = fetchMock.mock.calls[0][1] as RequestInit;
    expect(new Headers(init.headers as HeadersInit).get("Cookie")).toBeNull();
  });
});
