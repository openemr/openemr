import { describe, it, expect } from "vitest";
import { GET } from "@/app/api/health/route";

describe("GET /api/health", () => {
  it("returns the static-placeholder shape", async () => {
    const res = await GET();

    expect(res.status).toBe(200);

    const body = (await res.json()) as {
      ok: unknown;
      version: unknown;
      openemr_reachable: unknown;
      note: unknown;
    };

    expect(body.ok).toBe(true);
    expect(typeof body.version).toBe("string");
    expect(body.openemr_reachable).toBe(null);
    expect(typeof body.note).toBe("string");
    expect(body.note).toContain("not enabled yet");
  });
});
