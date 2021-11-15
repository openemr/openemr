#!/usr/bin/env node

/*!
 * Script to create the built distribution package zip archive;
 * requires the `Windows PowerShell` to be present!
 * Copyright 2018-2021 Arash Laylazi (Inspired by The Bootstrap Authors)
 * Licensed under MIT (https://github.com/PerseusTheGreat/bootstrap-4-rtl/blob/v4-dev/LICENSE)
 */

'use strict'

const shell = require('shelljs')
const packageJson = require('../package.json')
const fileName = `bootstrap-${packageJson.version}-dist.zip`
const deleteCommand = `PowerShell Remove-Item -Path ${fileName}`
const buildCommand = `PowerShell Compress-Archive -Path dist/* -CompressionLevel Optimal -DestinationPath ${fileName}`

shell.exec(deleteCommand, (code, stdout, stderr) => {
  code = Number(code)
  if (code === 0 || code === 1) {
    shell.exec(buildCommand, (code, stdout, stderr) => {
      code = Number(code)
      if (code === 0) {
        shell.echo('Package build succeeded!')
      } else {
        shell.echo(`Error: ${stderr}`)
        shell.exit(1)
      }
    })
  } else {
    shell.echo(`Error: ${stderr}`)
    shell.exit(1)
  }
})
