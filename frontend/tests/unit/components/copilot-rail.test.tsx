// @vitest-environment jsdom
import { describe, it, expect } from "vitest";
import { renderToStaticMarkup } from "react-dom/server";
import { CopilotRail } from "@/components/CopilotRail";

describe("CopilotRail", () => {
  it("builds the iframe src from copilotUrl + patient_id", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="abc-123" copilotUrl="https://copilot.example.com" />,
    );
    expect(html).toContain('src="https://copilot.example.com/iframe?patient_id=abc-123"');
  });

  it("strips trailing slashes from copilotUrl", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://copilot.example.com/" />,
    );
    expect(html).toContain('src="https://copilot.example.com/iframe?patient_id=x"');
  });

  it("URL-encodes the patient_id (URLSearchParams space → '+')", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="patient with space" copilotUrl="https://x.example" />,
    );
    // URLSearchParams uses application/x-www-form-urlencoded which encodes
    // space as '+', not '%20'. Both forms decode back to space at the
    // receiver per the spec.
    expect(html).toContain('patient_id=patient+with+space');
  });

  it("appends physician_user_id when provided", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://x.example" physicianUserId="dr-smith" />,
    );
    expect(html).toContain("patient_id=x");
    expect(html).toContain("physician_user_id=dr-smith");
  });

  it("URL-encodes physician_user_id with special chars", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://x.example" physicianUserId="dr smith+1" />,
    );
    expect(html).toContain("physician_user_id=dr+smith%2B1");
  });

  it("omits physician_user_id when null/undefined", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://x.example" physicianUserId={null} />,
    );
    expect(html).not.toContain("physician_user_id");
    const html2 = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://x.example" />,
    );
    expect(html2).not.toContain("physician_user_id");
  });

  it("emits a defensive sandbox attribute on the iframe", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://x.example" />,
    );
    expect(html).toContain('sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"');
  });
});
