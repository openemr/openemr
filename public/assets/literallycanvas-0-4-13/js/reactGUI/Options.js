var Options, React, createSetStateOnEventMixin, optionsStyles;

React = require('./React-shim');

createSetStateOnEventMixin = require('./createSetStateOnEventMixin');

optionsStyles = require('../optionsStyles/optionsStyles').optionsStyles;

Options = React.createClass({
  displayName: 'Options',
  getState: function() {
    var ref;
    return {
      style: (ref = this.props.lc.tool) != null ? ref.optionsStyle : void 0,
      tool: this.props.lc.tool
    };
  },
  getInitialState: function() {
    return this.getState();
  },
  mixins: [createSetStateOnEventMixin('toolChange')],
  renderBody: function() {
    var style;
    style = "" + this.state.style;
    return optionsStyles[style] && optionsStyles[style]({
      lc: this.props.lc,
      tool: this.state.tool,
      imageURLPrefix: this.props.imageURLPrefix
    });
  },
  render: function() {
    var div;
    div = React.DOM.div;
    return div({
      className: 'lc-options horz-toolbar'
    }, this.renderBody());
  }
});

module.exports = Options;
