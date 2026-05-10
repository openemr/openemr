import type { NextConfig } from "next";
import { buildSecurityHeaders } from "./lib/security/csp";

const nextConfig: NextConfig = {
  turbopack: {
    root: import.meta.dirname,
  },
  // Standalone output keeps the Docker image minimal — only .next/standalone +
  // .next/static + public/ + a slim node_modules subset are required.
  output: "standalone",

  // Dashboard is co-hosted inside OpenEMR's Apache container. Apache
  // mod_proxy forwards /modern/* to a local Node process listening on
  // 127.0.0.1:3000; basePath + assetPrefix make Next.js generate every
  // route, asset, and API path under /modern/ so URLs round-trip cleanly.
  basePath: "/modern",
  assetPrefix: "/modern",

  async headers() {
    const security = buildSecurityHeaders(process.env);
    return [
      {
        // Apply security headers to ALL routes. The /api/fhir proxy code
        // separately sets Cache-Control: no-store, private; it will
        // overwrite these for that route family, which is correct.
        source: "/:path*",
        headers: security.map((h) => ({ key: h.key, value: h.value })),
      },
    ];
  },
};

export default nextConfig;
