"use strict";

/**
 * Replaces // bs5import and public/assets paths in OpenEMR theme SCSS so Webpack
 * can resolve from node_modules without requiring gulp install (public/assets).
 * Prepends $compact-theme: false for base themes (same as Gulp).
 * Uses absolute path for Bootstrap so Sass loads it in the same compilation.
 */
module.exports = function (source) {
  const resource = (this.resourcePath || "").replace(/\\/g, "/");
  if (resource.includes("oe-styles/") || resource.includes("colors/style_")) {
    source = "$compact-theme: false;\n" + source;
  }
  let out = source.replace(
    /\/\/\s*bs5import\s*/g,
    '@import "bootstrap/scss/bootstrap";\n'
  );
  out = out.replace(
    /@import\s+"\.\.\/\.\.\/\.\.\/public\/assets\/@fortawesome\/fontawesome-free\/([^"]+)";/g,
    '@import "@fortawesome/fontawesome-free/$1";'
  );
  return out;
};
