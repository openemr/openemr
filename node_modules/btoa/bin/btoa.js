#!/usr/bin/env node
(function () {
  "use strict";

  var btoa = require('../index');

  console.log(btoa(process.argv[2]));
}());
