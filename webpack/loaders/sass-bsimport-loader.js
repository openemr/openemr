"use strict";

/**
 * Pre-loader for OpenEMR BS4 SCSS themes.
 *
 * Reads an optional `?variant=` resource query from the webpack entry to apply
 * the SCSS transformations needed for each theme variant (prepend/append/
 * replace):
 *
 *   (no query)      LTR base — $compact-theme: false, @import bootstrap
 *   ?variant=compact         — compact-theme-defaults, oemr_compact_imports
 *   ?variant=rtl             — RTL flag + rtl partial + if-rtl mixin call
 *   ?variant=rtl_compact     — RTL + compact combined
 *   ?variant=rtl_tabs        — RTL flag + rtl partial (for tab theme files)
 *   ?variant=rtl_misc        — RTL direction flag only (for misc files)
 *
 * Also rewrites ALL relative `public/assets/<pkg>/…` imports to bare
 * node_modules paths so webpack can resolve them directly.
 */
module.exports = function (source) {
  const resource = (this.resourcePath || "").replace(/\\/g, "/");
  const rawQuery = this.resourceQuery || "";
  const params = new URLSearchParams(rawQuery.replace(/^\?/, ""));
  const variant = params.get("variant") || "ltr";

  // Files one level below interface/themes/ (oe-styles/, colors/) need "../"
  // to reach sibling partials; root-level theme files use bare names.
  const isSubdir =
    resource.includes("/oe-styles/") || resource.includes("/colors/");

  const rtlImport = isSubdir ? '@import "../rtl"' : '@import "rtl"';
  const compactImport = isSubdir
    ? '@import "../compact-theme-defaults"'
    : '@import "compact-theme-defaults"';
  const rtlMixin =
    "@include if-rtl { @include rtl_style; " +
    "#bigCal { border-right: 1px solid $black !important; } }";

  let out;

  switch (variant) {
    case "compact":
      // Compact variant — compact-theme-defaults + oemr_compact_imports
      out = `${compactImport};\n` + source;
      out = out.replace(
        /\/\/\s*bs4import\s*/g,
        '@import "oemr_compact_imports";\n'
      );
      break;

    case "rtl":
      // RTL variant — RTL flag + rtl partial + if-rtl mixin
      out = `$compact-theme: false;\n$dir: rtl;\n${rtlImport};\n` + source;
      out += `\n${rtlMixin}`;
      out = out.replace(/\/\/\s*bs4import\s*/g, '@import "oemr-rtl";\n');
      break;

    case "rtl_compact":
      // RTL compact variant — RTL + compact combined
      out = `${compactImport};\n$dir: rtl;\n${rtlImport};\n` + source;
      out += `\n${rtlMixin}`;
      out = out.replace(
        /\/\/\s*bs4import\s*/g,
        '@import "oemr_rtl_compact_imports";\n'
      );
      break;

    case "rtl_tabs":
      // RTL tabs — no bs4import, just direction flag + rtl partial
      out = `$dir: rtl;\n${rtlImport};\n` + source;
      break;

    case "rtl_misc":
      // RTL misc — just the direction flag
      out = `$dir: rtl;\n` + source;
      break;

    default:
      // LTR default — $compact-theme: false, bootstrap import
      out = isSubdir ? `$compact-theme: false;\n` + source : source;
      out = out.replace(
        /\/\/\s*bs4import\s*/g,
        '@import "bootstrap/scss/bootstrap";\n'
      );
      break;
  }

  // Rewrite ALL relative public/assets/<pkg> imports to bare node_modules paths.
  // Skips public/assets/modified/ — those files are committed to the repo.
  out = out.replace(
    /@import\s+"(?:\.\.\/)+public\/assets\/(?!modified\/)([^"]+)";/g,
    '@import "$1";'
  );

  return out;
};
