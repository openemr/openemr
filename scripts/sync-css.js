#!/usr/bin/env node
"use strict";

/**
 * Replaces Gulp's `sync` task.
 *
 * Copies static CSS files from interface/themes/ to public/themes/ with
 * autoprefixer applied, mirroring what `gulp sync` did:
 *
 *   interface/themes/*.css  →  public/themes/  (autoprefixed)
 *
 * Invoked by `npm run build:sync` and included in `npm run build`.
 */

const fs = require("fs");
const path = require("path");
const postcss = require("postcss");
const autoprefixer = require("autoprefixer");

const srcDir = path.resolve(__dirname, "../interface/themes");
const destDir = path.resolve(__dirname, "../public/themes");

async function syncCss() {
  fs.mkdirSync(destDir, { recursive: true });

  const files = fs.readdirSync(srcDir).filter((f) => f.endsWith(".css"));

  if (files.length === 0) {
    console.log("[sync-css] No CSS files to copy.");
    return;
  }

  const processor = postcss([autoprefixer]);

  await Promise.all(
    files.map(async (file) => {
      const src = path.join(srcDir, file);
      const dest = path.join(destDir, file);
      const css = fs.readFileSync(src, "utf8");
      const result = await processor.process(css, { from: src, to: dest });
      fs.writeFileSync(dest, result.css);
      if (result.map) {
        fs.writeFileSync(dest + ".map", result.map.toString());
      }
      console.log("[sync-css] Copied: " + file);
    })
  );

  console.log("[sync-css] Done.");
}

syncCss().catch((err) => {
  console.error("[sync-css] Error:", err);
  process.exit(1);
});
