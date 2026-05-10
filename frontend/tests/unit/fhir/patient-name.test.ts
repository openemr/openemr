import { describe, it, expect } from "vitest";
import { renderName } from "@/lib/fhir/patient-name";

describe("renderName", () => {
  it("returns (no name) for undefined / empty", () => {
    expect(renderName(undefined)).toBe("(no name)");
    expect(renderName([])).toBe("(no name)");
  });

  it("uses .text when present", () => {
    expect(renderName([{ text: "Dr. Jane Doe, MD" }])).toBe("Dr. Jane Doe, MD");
  });

  it("composes prefix + given + family + suffix", () => {
    expect(
      renderName([
        { prefix: ["Dr."], given: ["Jane", "Q."], family: "Doe", suffix: ["MD"] },
      ]),
    ).toBe("Dr. Jane Q. Doe MD");
  });

  it("prefers official-use name over usual-use", () => {
    expect(
      renderName([
        { use: "usual", text: "Janie" },
        { use: "official", text: "Jane Doe" },
      ]),
    ).toBe("Jane Doe");
  });

  it("falls back to first name when no official", () => {
    expect(renderName([{ text: "Janie" }, { text: "Jane" }])).toBe("Janie");
  });

  it("trims whitespace and collapses multiple spaces", () => {
    expect(renderName([{ given: ["Jane"], family: "Doe" }])).toBe("Jane Doe");
  });

  it("returns (no name) when chosen entry has no usable parts", () => {
    expect(renderName([{ use: "official" }])).toBe("(no name)");
  });

  it("trims a text-only entry", () => {
    expect(renderName([{ text: "  Jane Doe  " }])).toBe("Jane Doe");
  });
});
