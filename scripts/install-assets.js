#!/usr/bin/env node
"use strict";

/**
 * Copies dependency assets from node_modules/ to public/assets/.
 *
 * Replaces the Gulp `install` task (previously invoked via `gulp -i`).
 * Runs as part of the npm postinstall hook: `napa && node scripts/install-assets.js`.
 *
 * Special-case packages get specific subdirectories copied; everything else
 * gets either its dist/ folder (if it exists) or the entire package.
 */

const fs = require("fs");
const path = require("path");

const packages = require("../package.json");
const assetsDir = path.resolve(__dirname, "../public/assets");

function copyRecursive(src, dest) {
  if (!fs.existsSync(src)) return;
  const stat = fs.statSync(src);
  if (stat.isDirectory()) {
    fs.mkdirSync(dest, { recursive: true });
    for (const entry of fs.readdirSync(src)) {
      copyRecursive(path.join(src, entry), path.join(dest, entry));
    }
  } else {
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
  }
}

function copyDir(pkg, subdir) {
  const src = path.resolve(__dirname, "../node_modules", pkg, subdir);
  const dest = path.join(assetsDir, pkg, subdir);
  copyRecursive(src, dest);
}

function copyFile(pkg, file) {
  const src = path.resolve(__dirname, "../node_modules", pkg, file);
  const dest = path.join(assetsDir, pkg, file);
  if (fs.existsSync(src)) {
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
  }
}

function copyAll(pkg) {
  const src = path.resolve(__dirname, "../node_modules", pkg);
  const dest = path.join(assetsDir, pkg);
  copyRecursive(src, dest);
}

// Merge dependencies and napa sources
const dependencies = { ...packages.dependencies };
if (packages.napa) {
  for (const key of Object.keys(packages.napa)) {
    dependencies[key] = packages.napa[key];
  }
}

console.log("[install-assets] Copying dependency assets to public/assets/ ...");

for (const key of Object.keys(dependencies)) {
  if (key === "dwv") {
    copyDir(key, "dist");
    copyDir(key, "decoders");
    copyDir(key, "locales");
  } else if (key === "bootstrap" || key === "bootstrap-rtl") {
    copyDir(key, "dist");
    copyDir(key, "scss");
  } else if (key === "@fortawesome/fontawesome-free") {
    copyDir(key, "css");
    copyDir(key, "scss");
    copyDir(key, "webfonts");
  } else if (key === "moment") {
    copyDir(key, "min");
    copyFile(key, "moment.js");
  } else if (
    fs.existsSync(
      path.resolve(__dirname, "../node_modules", key, "dist")
    )
  ) {
    copyDir(key, "dist");
  } else {
    copyAll(key);
  }
}

console.log("[install-assets] Done.");
