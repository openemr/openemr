var React, defineOptionsStyle;

React = require('../reactGUI/React-shim');

defineOptionsStyle = require('./optionsStyles').defineOptionsStyle;

defineOptionsStyle('null', React.createClass({
  displayName: 'NoOptions',
  render: function() {
    return React.DOM.div();
  }
}));

module.exports = {};
