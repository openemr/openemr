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

// Directories to skip when copying (non-asset content)
const SKIP_DIRS = new Set([
  "test", "tests", "__tests__", "docs", "doc", "example", "examples", ".git",
]);

function copyRecursive(src, dest, root) {
  if (!fs.existsSync(src)) return;

  // Use lstatSync to detect symlinks without following them
  const stat = fs.lstatSync(src);

  // Skip symlinks to prevent traversal attacks
  if (stat.isSymbolicLink()) return;

  // Ensure resolved path stays within package root
  const realPath = fs.realpathSync(src);
  if (!realPath.startsWith(root + path.sep) && realPath !== root) return;

  if (stat.isDirectory()) {
    fs.mkdirSync(dest, { recursive: true });
    for (const entry of fs.readdirSync(src)) {
      // Skip dotfiles and non-asset directories
      if (entry.startsWith(".")) continue;
      if (SKIP_DIRS.has(entry)) continue;
      copyRecursive(path.join(src, entry), path.join(dest, entry), root);
    }
  } else {
    const base = path.basename(src);
    if (base.startsWith(".")) return;
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
  }
}

function copyDir(pkg, subdir) {
  const pkgRoot = path.resolve(__dirname, "../node_modules", pkg);
  const src = path.join(pkgRoot, subdir);
  const dest = path.join(assetsDir, pkg, subdir);
  copyRecursive(src, dest, pkgRoot);
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
  copyRecursive(src, dest, src);
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
