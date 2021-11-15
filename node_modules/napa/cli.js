'use strict'

var path = require('path')
var fs = require('fs')
var cwd = process.cwd()
var Pkg = require('./lib/pkg')
var extend = require('extend')
var minimist = require('minimist')

var napa = module.exports = {}

napa.cli = function (args, done) {
  var parsedArgs = minimist(args)
  args = parsedArgs['_']
  var total = 0
  var pkg = napa.readpkg()
  var opts = napa._loadFromPkg('napa-config', {})

  // Add flags to opts
  var flags = parsedArgs
  delete flags['_']
  opts = extend(opts, flags)

  if (pkg) {
    args = args.map(napa.args).concat(pkg)
  } else {
    args = args.map(napa.args)
  }

  args.forEach(function (cmd) {
    total++
    opts.ref = cmd[2]

    var pkg = new Pkg(cmd[0], cmd[1], opts)
    pkg.install(close)
  })

  function close () {
    total--
    if (total < 1 && typeof done === 'function') {
      return done()
    }
  }
}

napa.args = function (str) {
  var url, name
  var split = str.split(':')

  if (split.length === 3) {
    name = split[2]
    url = split.slice(0, 2).join(':')
  } else if (split.length === 2) {
    if (split[1].slice(0, 2) === '//') {
      url = split.join(':')
    } else {
      url = split[0]
      name = split[1]
    }
  } else {
    url = split.join(':')
  }

  if (!name) {
    name = url.slice(url.lastIndexOf('/') + 1)
  }

  return [napa.url(url), name, napa.getref(str)]
}

napa.url = function (url) {
  if (typeof url !== 'string') {
    if (url.url) url = url.url
    else return false
  }

  if (url.indexOf('#') !== -1) {
    if (url.indexOf('://') === -1) {
      var s = url.split('#')
      url = 'https://github.com/' + s[0] + '/archive/' + s[1]
      if (process.platform === 'win32') url += '.zip'
      else url += '.tar.gz'
    } else {
      url = url.replace(/#.*?$/, '')
    }
  }

  if (url.slice(0, 1) === '/') {
    url = url.slice(1)
  }

  if (url.indexOf('://') === -1) {
    url = 'git://github.com/' + url
  }

  return url
}

napa.readpkg = function () {
  var repos = napa._loadFromPkg('napa') || {}

  return Object.keys(repos).map(function (repo) {
    var repoLocation = repos[repo]
    return [napa.url(repoLocation), repo, napa.getref(repoLocation)]
  })
}

napa._loadFromPkg = function (property, defaults) {
  if (typeof defaults === 'undefined') {
    defaults = false
  }

  var pkgPath = path.join(cwd, 'package.json')

  if (!fs.existsSync(pkgPath)) {
    return defaults
  }

  var pkg = require(pkgPath)

  return pkg.hasOwnProperty(property) ? pkg[property] : defaults
}

napa.getref = function (url) {
  return url.replace(/^[^#]*#?/, '')
}
