var ReactDOM, error, error1;

try {
  ReactDOM = require('react-dom');
} catch (error) {
  ReactDOM = window.ReactDOM;
}

if (ReactDOM == null) {
  try {
    ReactDOM = require('react');
  } catch (error1) {
    ReactDOM = window.React;
  }
}

if (ReactDOM == null) {
  throw "Can't find ReactDOM";
}

module.exports = ReactDOM;
