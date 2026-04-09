"use strict";

const fs = require("fs");

/**
 * Webpack build for OpenEMR SCSS themes.
 *
 * Invoked by `npm run build` and `npm run build:webpack`.
 *
 * Usage:
 *   npm run build               # Themes webpack (prod) + static CSS sync
 *   npm run build:webpack:prod  # Webpack production build only
 *   npm run build:webpack:dev   # Webpack development build only
 *   npm run watch               # Webpack dev build with file watching
 *
 * Outputs:
 *   public/themes/            ← BS4 SCSS base theme CSS (same paths as Gulp)
 *   public/themes/misc/       ← misc SCSS
 *
 * Docker cache:
 *   The filesystem cache writes to .webpack-cache/ (mapped by the Dockerfile's
 *   --mount=type=cache,id=webpack-openemr,target=…/.webpack-cache mount).
 *   Add openemr-internal/.webpack-cache to .dockerignore.
 */

const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

// webpack --mode production sets NODE_ENV=production via DefinePlugin for
// bundled code; also check argv so the config itself sees the right mode.
const isProduction =
  process.env.NODE_ENV === "production" ||
  (process.argv.includes("--mode") &&
    process.argv[process.argv.indexOf("--mode") + 1] === "production");

// ---------------------------------------------------------------------------
// Custom Sass importer: resolves `public/assets/<pkg>/…` → node_modules/<pkg>/…
//
// Runs for ALL @import calls including in transitively imported SCSS files
// (e.g. color_base.scss). This lets webpack compile without the postinstall
// asset copy that populates public/assets. In Docker, the postinstall script
// runs as part of npm ci, so public/assets IS populated — but this importer
// is still invoked first and is consistent either way.
//
// Skips public/assets/modified/ — those files are committed to the repo and
// resolve via Sass's includePaths entry for public/assets/modified.
// ---------------------------------------------------------------------------
function publicAssetsImporter(url) {
  const match = url.match(/^(?:\.\.\/)*public\/assets\/(?!modified\/)(.+)$/);
  if (!match) return null;

  const subPath = match[1];
  const resolved = path.resolve(__dirname, "node_modules", subPath);

  const candidates = [
    resolved,
    resolved + ".scss",
    path.join(path.dirname(resolved), "_" + path.basename(resolved) + ".scss"),
    path.join(path.dirname(resolved), "_" + path.basename(resolved)),
  ];

  for (const candidate of candidates) {
    if (fs.existsSync(candidate)) {
      return { file: candidate };
    }
  }
  return null;
}

// ---------------------------------------------------------------------------
// Shared Sass loader chain (BS4 themes)
// ---------------------------------------------------------------------------
const themesDir = path.resolve(__dirname, "interface/themes");
const outputThemes = path.resolve(__dirname, "public/themes");

function sassRule() {
  return {
    test: /\.scss$/,
    use: [
      MiniCssExtractPlugin.loader,
      { loader: "css-loader", options: { url: false } },
      {
        loader: "postcss-loader",
        options: { postcssOptions: { plugins: [["autoprefixer"]] } },
      },
      "strip-bom-loader",
      {
        loader: "sass-loader",
        options: {
          sassOptions: {
            // In Docker public/assets is pre-populated by the npm ci postinstall
            // (install-assets.js). The custom importer handles paths that
            // reference public/assets directly in both Docker and local dev.
            includePaths: [
              path.resolve(__dirname, "node_modules"),
              path.resolve(__dirname, "interface/themes"),
              path.resolve(__dirname, "public/assets"),
              path.resolve(__dirname, "public/assets/modified"),
            ],
            importer: publicAssetsImporter,
          },
          // webpackImporter: true uses webpack's module resolution for @import,
          // complementing publicAssetsImporter for public/assets/... paths.
          webpackImporter: true,
        },
      },
      // Runs first (webpack loaders execute right-to-left):
      // replaces // bs4import, rewrites public/assets paths, prepends $compact-theme.
      { loader: "sass-bsimport-loader" },
    ],
  };
}

function sharedCacheConfig() {
  return {
    type: "filesystem",
    // Explicit directory so the Dockerfile can mount a BuildKit cache here.
    cacheDirectory: path.resolve(__dirname, ".webpack-cache"),
    buildDependencies: {
      config: [
        __filename,
        path.resolve(__dirname, "webpack/loaders/sass-bsimport-loader.js"),
        path.resolve(__dirname, "webpack/loaders/strip-bom-loader.js"),
      ],
    },
  };
}

// ---------------------------------------------------------------------------
// Config 1 — BS4 SCSS themes
//
// Compiles all BS4 SCSS theme variants: base, compact, RTL, RTL compact,
// color variants, tabs, misc, and directional.
//
// Variants use the ?variant= resource query so sass-bsimport-loader can apply
// the same prepend/append/replace transformations Gulp used.
//
// JS shim files (e.g. style_light.js) are emitted alongside the CSS — they
// are valid empty modules (~600 B each) and are harmless when served by nginx.
// ---------------------------------------------------------------------------

// Helper to build an absolute entry path with an optional resource query.
function entry(relPath, variant) {
  const abs = path.resolve(themesDir, relPath);
  return variant ? abs + "?variant=" + variant : abs;
}

const themesConfig = {
  name: "themes",
  mode: isProduction ? "production" : "development",
  entry: {
    // ── oe-styles base themes (LTR) ──────────────────────────────────────────
    style_light:         entry("oe-styles/style_light.scss"),
    style_dark:          entry("oe-styles/style_dark.scss"),
    style_solar:         entry("oe-styles/style_solar.scss"),
    style_manila:        entry("oe-styles/style_manila.scss"),

    // ── oe-styles compact themes ─────────────────────────────────────────────
    compact_style_light:  entry("oe-styles/style_light.scss",  "compact"),
    compact_style_dark:   entry("oe-styles/style_dark.scss",   "compact"),
    compact_style_solar:  entry("oe-styles/style_solar.scss",  "compact"),
    compact_style_manila: entry("oe-styles/style_manila.scss", "compact"),

    // ── oe-styles RTL themes ─────────────────────────────────────────────────
    rtl_style_light:  entry("oe-styles/style_light.scss",  "rtl"),
    rtl_style_dark:   entry("oe-styles/style_dark.scss",   "rtl"),
    rtl_style_solar:  entry("oe-styles/style_solar.scss",  "rtl"),
    rtl_style_manila: entry("oe-styles/style_manila.scss", "rtl"),

    // ── oe-styles RTL compact themes ─────────────────────────────────────────
    rtl_compact_style_light:  entry("oe-styles/style_light.scss",  "rtl_compact"),
    rtl_compact_style_dark:   entry("oe-styles/style_dark.scss",   "rtl_compact"),
    rtl_compact_style_solar:  entry("oe-styles/style_solar.scss",  "rtl_compact"),
    rtl_compact_style_manila: entry("oe-styles/style_manila.scss", "rtl_compact"),

    // ── color variant themes (LTR) ───────────────────────────────────────────
    style_cobalt_blue:  entry("colors/style_cobalt_blue.scss"),
    style_forest_green: entry("colors/style_forest_green.scss"),

    // ── color variant themes (compact) ───────────────────────────────────────
    compact_style_cobalt_blue:  entry("colors/style_cobalt_blue.scss",  "compact"),
    compact_style_forest_green: entry("colors/style_forest_green.scss", "compact"),

    // ── color variant themes (RTL) ───────────────────────────────────────────
    rtl_style_cobalt_blue:  entry("colors/style_cobalt_blue.scss",  "rtl"),
    rtl_style_forest_green: entry("colors/style_forest_green.scss", "rtl"),

    // ── color variant themes (RTL compact) ───────────────────────────────────
    rtl_compact_style_cobalt_blue:  entry("colors/style_cobalt_blue.scss",  "rtl_compact"),
    rtl_compact_style_forest_green: entry("colors/style_forest_green.scss", "rtl_compact"),

    // ── tabs themes (LTR) ────────────────────────────────────────────────────
    tabs_style_full:    entry("tabs_style_full.scss"),
    tabs_style_compact: entry("tabs_style_compact.scss"),

    // ── tabs themes (RTL) ────────────────────────────────────────────────────
    rtl_tabs_style_full:    entry("tabs_style_full.scss",    "rtl_tabs"),
    rtl_tabs_style_compact: entry("tabs_style_compact.scss", "rtl_tabs"),

    // ── root-level themes (no variant transforms) ────────────────────────────
    style:       entry("style.scss"),
    style_pdf:   entry("style_pdf.scss"),
    directional: entry("directional.scss"),

    // ── misc → public/themes/misc/ (LTR) ────────────────────────────────────
    "misc/bootstrap_navbar": entry("misc/bootstrap_navbar.scss"),
    "misc/edi_history_v2":   entry("misc/edi_history_v2.scss"),
    "misc/encounters":       entry("misc/encounters.scss"),
    "misc/labdata":          entry("misc/labdata.scss"),
    "misc/rules":            entry("misc/rules.scss"),

    // ── misc → public/themes/misc/ (RTL) ────────────────────────────────────
    "misc/rtl_bootstrap_navbar": entry("misc/bootstrap_navbar.scss", "rtl_misc"),
    "misc/rtl_edi_history_v2":   entry("misc/edi_history_v2.scss",   "rtl_misc"),
    "misc/rtl_encounters":       entry("misc/encounters.scss",        "rtl_misc"),
    "misc/rtl_labdata":          entry("misc/labdata.scss",           "rtl_misc"),
    "misc/rtl_rules":            entry("misc/rules.scss",             "rtl_misc"),
  },
  output: {
    path: outputThemes,
    filename: "[name].js",
    chunkFilename: "[name].chunk.js",
    clean: false,
  },
  resolve: {
    alias: {
      "~bootstrap": path.resolve(__dirname, "node_modules/bootstrap"),
      "~@fortawesome/fontawesome-free": path.resolve(
        __dirname,
        "node_modules/@fortawesome/fontawesome-free"
      ),
    },
    extensions: [".scss", ".css", ".js"],
  },
  resolveLoader: {
    alias: {
      "sass-bsimport-loader": path.resolve(
        __dirname,
        "webpack/loaders/sass-bsimport-loader.js"
      ),
      "strip-bom-loader": path.resolve(
        __dirname,
        "webpack/loaders/strip-bom-loader.js"
      ),
    },
  },
  module: { rules: [sassRule()] },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "[name].css",
      chunkFilename: "[id].css",
    }),
  ],
  devtool: isProduction ? "source-map" : "eval-source-map",
  stats: { colors: true },
  cache: sharedCacheConfig(),
};

module.exports = [themesConfig];
