// @vitest-environment jsdom
import { describe, it, expect } from "vitest";
import { renderToStaticMarkup } from "react-dom/server";
import { TopNav } from "@/components/TopNav";

describe("TopNav", () => {
  it("renders the Patients nav item", () => {
    const html = renderToStaticMarkup(<TopNav />);
    expect(html).toContain("Patients");
  });

  it("links 'Patients' to /", () => {
    const html = renderToStaticMarkup(<TopNav />);
    expect(html).toMatch(/href="\/"[^>]*>Patients/);
  });

  it("does NOT render the legacy disabled placeholder items (Calendar, Encounters, Reports, Modules, Admin)", () => {
    const html = renderToStaticMarkup(<TopNav />);
    for (const label of ["Calendar", "Encounters", "Reports", "Modules", "Admin"]) {
      expect(html).not.toContain(label);
    }
  });

  it("highlights the active section", () => {
    const html = renderToStaticMarkup(<TopNav active="patients" />);
    expect(html).toMatch(/bg-blue-50[^"]*"[^>]*>Patients/);
  });

  it("shows the username badge when provided", () => {
    const html = renderToStaticMarkup(<TopNav username="dr.smith" />);
    expect(html).toContain("dr.smith");
    expect(html).toContain("Signed in as");
  });

  it("hides the username badge when null/undefined", () => {
    const noUser = renderToStaticMarkup(<TopNav username={null} />);
    expect(noUser).not.toContain("Signed in as");
    const noUser2 = renderToStaticMarkup(<TopNav />);
    expect(noUser2).not.toContain("Signed in as");
  });

  it("renders a sign-out form posting to /api/auth/logout", () => {
    const html = renderToStaticMarkup(<TopNav />);
    expect(html).toMatch(/<form[^>]*action="\/api\/auth\/logout"[^>]*method="POST"/);
    expect(html).toContain("Sign out");
  });

  it("brand link points to /", () => {
    const html = renderToStaticMarkup(<TopNav />);
    // [^]*? is the ES2017-compatible alternative to .*? + /s flag (dotall).
    const brandAnchor = html.match(/<a[^>]*href="\/"[^>]*>[^]*?OpenEMR Dashboard[^]*?<\/a>/);
    expect(brandAnchor).not.toBeNull();
  });
});
