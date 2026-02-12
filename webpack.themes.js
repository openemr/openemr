"use strict";

const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

const isProduction = process.env.NODE_ENV === "production";
const themesDir = path.resolve(__dirname, "interface/themes");
const outputThemes = path.resolve(__dirname, "public/themes");
const outputMisc = path.resolve(__dirname, "public/themes/misc");

// Same output paths as Gulp: public/themes and public/themes/misc
const entries = {
  // oe-styles (base themes)
  style_light: path.join(themesDir, "oe-styles/style_light.scss"),
  style_dark: path.join(themesDir, "oe-styles/style_dark.scss"),
  style_solar: path.join(themesDir, "oe-styles/style_solar.scss"),
  style_manila: path.join(themesDir, "oe-styles/style_manila.scss"),
  // colors
  style_cobalt_blue: path.join(themesDir, "colors/style_cobalt_blue.scss"),
  style_forest_green: path.join(themesDir, "colors/style_forest_green.scss"),
  // tabs
  tabs_style_full: path.join(themesDir, "tabs_style_full.scss"),
  tabs_style_compact: path.join(themesDir, "tabs_style_compact.scss"),
  // root theme
  style: path.join(themesDir, "style.scss"),
  directional: path.join(themesDir, "directional.scss"),
  // misc -> public/themes/misc
  "misc/bootstrap_navbar": path.join(themesDir, "misc/bootstrap_navbar.scss"),
  "misc/edi_history_v2": path.join(themesDir, "misc/edi_history_v2.scss"),
  "misc/encounters": path.join(themesDir, "misc/encounters.scss"),
  "misc/labdata": path.join(themesDir, "misc/labdata.scss"),
  "misc/rules": path.join(themesDir, "misc/rules.scss"),
};

module.exports = {
  mode: isProduction ? "production" : "development",
  entry: entries,
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
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: { url: false },
          },
          {
            loader: "postcss-loader",
            options: {
              postcssOptions: {
                plugins: [["autoprefixer"]],
              },
            },
          },
          "strip-bom-loader",
          {
            loader: "sass-loader",
            options: {
              sassOptions: {
                includePaths: [
                  path.resolve(__dirname, "node_modules"),
                  path.resolve(__dirname, "interface/themes"),
                  path.resolve(__dirname, "public/assets"),
                  path.resolve(__dirname, "public/assets/modified"),
                ],
              },
              webpackImporter: false,
            },
          },
          {
            loader: "sass-bsimport-loader",
          },
        ],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "[name].css",
      chunkFilename: "[id].css",
    }),
  ],
  devtool: isProduction ? "source-map" : "eval-source-map",
  stats: { colors: true },
};
