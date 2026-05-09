import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  turbopack: {
    root: import.meta.dirname,
  },
  // Standalone output keeps the Docker image minimal — only .next/standalone +
  // .next/static + public/ + a slim node_modules subset are required.
  output: "standalone",
};

export default nextConfig;
