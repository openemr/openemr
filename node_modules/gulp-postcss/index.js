var Stream = require('stream')
var postcss = require('postcss')
var applySourceMap = require('vinyl-sourcemaps-apply')
var fancyLog = require('fancy-log')
var PluginError = require('plugin-error')
var path = require('path')


module.exports = withConfigLoader(function (loadConfig) {

  var stream = new Stream.Transform({ objectMode: true })

  stream._transform = function (file, encoding, cb) {

    if (file.isNull()) {
      return cb(null, file)
    }

    if (file.isStream()) {
      return handleError('Streams are not supported!')
    }

    // Protect `from` and `map` if using gulp-sourcemaps
    var isProtected = file.sourceMap
      ? { from: true, map: true }
      : {}

    var options = {
      from: file.path,
      to: file.path,
      // Generate a separate source map for gulp-sourcemaps
      map: file.sourceMap ? { annotation: false } : false
    }

    loadConfig(file)
      .then(function (config) {
        var configOpts = config.options || {}
        // Extend the default options if not protected
        for (var opt in configOpts) {
          if (configOpts.hasOwnProperty(opt) && !isProtected[opt]) {
            options[opt] = configOpts[opt]
          } else {
            fancyLog.info(
              'gulp-postcss:',
              file.relative + '\nCannot override ' + opt +
              ' option, because it is required by gulp-sourcemaps'
            )
          }
        }
        return postcss(config.plugins || [])
          .process(file.contents, options)
      })
      .then(handleResult, handleError)

    function handleResult (result) {
      var map
      var warnings = result.warnings().join('\n')

      file.contents = Buffer.from(result.css)

      // Apply source map to the chain
      if (file.sourceMap) {
        map = result.map.toJSON()
        map.file = file.relative
        map.sources = [].map.call(map.sources, function (source) {
          return path.join(path.dirname(file.relative), source)
        })
        applySourceMap(file, map)
      }

      if (warnings) {
        fancyLog.info('gulp-postcss:', file.relative + '\n' + warnings)
      }

      setImmediate(function () {
        cb(null, file)
      })
    }

    function handleError (error) {
      var errorOptions = { fileName: file.path, showStack: true }
      if (error.name === 'CssSyntaxError') {
        errorOptions.error = error
        errorOptions.fileName = error.file || file.path
        errorOptions.lineNumber = error.line
        errorOptions.showProperties = false
        errorOptions.showStack = false
        error = error.message + '\n\n' + error.showSourceCode() + '\n'
      }
      // Prevent stream’s unhandled exception from
      // being suppressed by Promise
      setImmediate(function () {
        cb(new PluginError('gulp-postcss', error, errorOptions))
      })
    }

  }

  return stream
})


function withConfigLoader(cb) {
  return function (plugins, options) {
    if (Array.isArray(plugins)) {
      return cb(function () {
        return Promise.resolve({
          plugins: plugins,
          options: options
        })
      })
    } else if (typeof plugins === 'function') {
      return cb(function (file) {
        return Promise.resolve(plugins(file))
      })
    } else {
      var postcssLoadConfig = require('postcss-load-config')
      var contextOptions = plugins || {}
      return cb(function(file) {
        var configPath
        if (contextOptions.config) {
          if (path.isAbsolute(contextOptions.config)) {
            configPath = contextOptions.config
          } else {
            configPath = path.join(file.base, contextOptions.config)
          }
        } else {
          configPath = file.dirname
        }
        return postcssLoadConfig(
          {
            file: file,
            options: contextOptions
          },
          configPath
        )
      })
    }
  }
}
