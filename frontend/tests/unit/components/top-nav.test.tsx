// @vitest-environment jsdom
import { describe, it, expect } from "vitest";
import { renderToStaticMarkup } from "react-dom/server";
import { TopNav } from "@/components/TopNav";

describe("TopNav", () => {
  it("renders all six nav items", () => {
    const html = renderToStaticMarkup(<TopNav />);
    for (const label of ["Patients", "Calendar", "Encounters", "Reports", "Modules", "Admin"]) {
      expect(html).toContain(label);
    }
  });

  it("links 'Patients' to /", () => {
    const html = renderToStaticMarkup(<TopNav />);
    expect(html).toMatch(/href="\/"[^>]*>Patients/);
  });

  it("renders disabled items with aria-disabled and Coming-soon hint", () => {
    const html = renderToStaticMarkup(<TopNav />);
    // Calendar is disabled — should appear in a span carrying both
    // aria-disabled="true" and title="Coming soon" (attribute order is
    // not guaranteed by JSX → DOM rendering, so check both individually).
    const calendarMatch = html.match(/<span[^>]*>Calendar<\/span>/);
    expect(calendarMatch).not.toBeNull();
    expect(calendarMatch![0]).toContain('aria-disabled="true"');
    expect(calendarMatch![0]).toContain('title="Coming soon"');
    // Calendar must not be wrapped in an anchor.
    expect(html).not.toMatch(/<a[^>]*>Calendar/);
  });

  it("highlights the active section", () => {
    const html = renderToStaticMarkup(<TopNav active="patients" />);
    // Active item gets the blue-50 / blue-700 styling token.
    expect(html).toMatch(/bg-blue-50[^"]*"[^>]*>Patients/);
  });

  it("does not highlight non-active items", () => {
    const html = renderToStaticMarkup(<TopNav active="patients" />);
    // Calendar still rendered as disabled span; no blue-50 class on it.
    const calendarSegment = html.match(/<[^>]*>Calendar<\//);
    expect(calendarSegment?.[0]).not.toContain("bg-blue-50");
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
    // Brand text lives in a nested <span>; assert the outer <a href="/">
    // exists and that "OpenEMR Dashboard" appears inside its open/close.
    const brandAnchor = html.match(/<a[^>]*href="\/"[^>]*>.*?OpenEMR Dashboard.*?<\/a>/s);
    expect(brandAnchor).not.toBeNull();
  });
});
