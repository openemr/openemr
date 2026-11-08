"use strict";

/**
 * Strips UTF-8 BOM from loader input (e.g. sass output from Font Awesome).
 * Fixes postcss "Unknown word" when CSS starts with BOM.
 */
module.exports = function (source) {
  if (typeof source !== "string") return source;
  if (source.charCodeAt(0) === 0xfeff) return source.slice(1);
  return source;
};
