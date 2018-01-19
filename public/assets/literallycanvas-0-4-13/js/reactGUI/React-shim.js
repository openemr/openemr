var React, error;

try {
  React = require('react');
} catch (error) {
  React = window.React;
}

if (React == null) {
  throw "Can't find React";
}

module.exports = React;
