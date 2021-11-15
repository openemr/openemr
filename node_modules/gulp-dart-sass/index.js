const chalk = require('chalk');
const PluginError = require('plugin-error');
const replaceExtension = require('replace-ext');
const stripAnsi = require('strip-ansi');
const through = require('through2');
const clonedeep = require('lodash.clonedeep');
const path = require('path');
const applySourceMap = require('vinyl-sourcemaps-apply');

const PLUGIN_NAME = 'gulp-dart-sass';

//////////////////////////////
// Main Gulp Sass function
//////////////////////////////
const gulpSass = (options, sync) => through.obj((file, enc, cb) => { // eslint-disable-line consistent-return
  if (file.isNull()) {
    return cb(null, file);
  }

  if (file.isStream()) {
    return cb(new PluginError(PLUGIN_NAME, 'Streaming not supported'));
  }

  if (path.basename(file.path).indexOf('_') === 0) {
    return cb();
  }

  if (!file.contents.length) {
    file.path = replaceExtension(file.path, '.css'); // eslint-disable-line no-param-reassign
    return cb(null, file);
  }

  const opts = clonedeep(options || {});
  opts.data = file.contents.toString();

  // we set the file path here so that libsass can correctly resolve import paths
  opts.file = file.path;

  // Ensure `indentedSyntax` is true if a `.sass` file
  if (path.extname(file.path) === '.sass') {
    opts.indentedSyntax = true;
  }

  // Ensure file's parent directory in the include path
  if (opts.includePaths) {
    if (typeof opts.includePaths === 'string') {
      opts.includePaths = [opts.includePaths];
    }
  } else {
    opts.includePaths = [];
  }

  opts.includePaths.unshift(path.dirname(file.path));

  // Generate Source Maps if plugin source-map present
  if (file.sourceMap) {
    opts.sourceMap = file.path;
    opts.omitSourceMapUrl = true;
    opts.sourceMapContents = true;
  }

  //////////////////////////////
  // Handles returning the file to the stream
  //////////////////////////////
  const filePush = (sassObj) => {
    let sassMap;
    let sassMapFile;
    let sassFileSrc;
    let sassFileSrcPath;
    let sourceFileIndex;

    // Build Source Maps!
    if (sassObj.map) {
      // Transform map into JSON
      sassMap = JSON.parse(sassObj.map.toString());
      // Grab the stdout and transform it into stdin
      sassMapFile = sassMap.file.replace(/^stdout$/, 'stdin');
      // Grab the base file name that's being worked on
      sassFileSrc = file.relative;
      // Grab the path portion of the file that's being worked on
      sassFileSrcPath = path.dirname(sassFileSrc);
      if (sassFileSrcPath) {
        // Prepend the path to all files in the sources array except the file that's being worked on
        sourceFileIndex = sassMap.sources.indexOf(sassMapFile);
        sassMap.sources = sassMap.sources.map((source, index) => { // eslint-disable-line arrow-body-style
          return index === sourceFileIndex ? source : path.join(sassFileSrcPath, source);
        });
      }

      // Remove 'stdin' from souces and replace with filenames!
      sassMap.sources = sassMap.sources.filter(src => src !== 'stdin' && src);

      // Replace the map file with the original file name (but new extension)
      sassMap.file = replaceExtension(sassFileSrc, '.css');
      // Apply the map
      applySourceMap(file, sassMap);
    }

    file.contents = sassObj.css; // eslint-disable-line no-param-reassign
    file.path = replaceExtension(file.path, '.css'); // eslint-disable-line no-param-reassign

    if (file.stat) {
      file.stat.atime = file.stat.mtime = file.stat.ctime = new Date(); // eslint-disable-line
    }

    cb(null, file);
  };

  //////////////////////////////
  // Handles error message
  //////////////////////////////
  const errorM = (error) => {
    const filePath = (error.file === 'stdin' ? file.path : error.file) || file.path;
    const relativePath = path.relative(process.cwd(), filePath);
    const message = [chalk.underline(relativePath), error.formatted].join('\n');

    error.messageFormatted = message; // eslint-disable-line no-param-reassign
    error.messageOriginal = error.message; // eslint-disable-line no-param-reassign
    error.message = stripAnsi(message); // eslint-disable-line no-param-reassign
    error.relativePath = relativePath; // eslint-disable-line no-param-reassign

    return cb(new PluginError(PLUGIN_NAME, error));
  };

  if (sync !== true) {
    //////////////////////////////
    // Async Sass render
    //////////////////////////////
    const callback = (error, obj) => { // eslint-disable-line consistent-return
      if (error) {
        return errorM(error);
      }
      filePush(obj);
    };

    gulpSass.compiler.render(opts, callback);
  } else {
    //////////////////////////////
    // Sync Sass render
    //////////////////////////////
    try {
      filePush(gulpSass.compiler.renderSync(opts));
    } catch (error) {
      return errorM(error);
    }
  }
});

//////////////////////////////
// Sync Sass render
//////////////////////////////
gulpSass.sync = options => gulpSass(options, true);

//////////////////////////////
// Log errors nicely
//////////////////////////////
gulpSass.logError = function logError(error) {
  const message = new PluginError('sass', error.messageFormatted).toString();
  process.stderr.write(`${message}\n`);
  this.emit('end');
};

//////////////////////////////
// Store compiler in a prop
//////////////////////////////
gulpSass.compiler = require('sass');

module.exports = gulpSass;
