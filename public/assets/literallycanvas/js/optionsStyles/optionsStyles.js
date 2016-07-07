var React, defineOptionsStyle, optionsStyles;

React = require('../reactGUI/React-shim');

optionsStyles = {};

defineOptionsStyle = function(name, style) {
  return optionsStyles[name] = React.createFactory(style);
};

module.exports = {
  optionsStyles: optionsStyles,
  defineOptionsStyle: defineOptionsStyle
};
