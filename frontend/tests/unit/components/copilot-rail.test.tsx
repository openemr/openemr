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

  it("URL-encodes the patient_id", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="patient with space" copilotUrl="https://x.example" />,
    );
    expect(html).toContain('patient_id=patient%20with%20space');
  });

  it("emits a defensive sandbox attribute on the iframe", () => {
    const html = renderToStaticMarkup(
      <CopilotRail patientId="x" copilotUrl="https://x.example" />,
    );
    expect(html).toContain('sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"');
  });
});
