export async function GET() {
  return Response.json({
    ok: true,
    version: process.env.npm_package_version ?? "0.0.0",
    openemr_reachable: null,
    note: "Authentication and FHIR reachability are not enabled yet.",
  });
}
