var React, classSet, createSetStateOnEventMixin;

React = require('./React-shim');

createSetStateOnEventMixin = require('../reactGUI/createSetStateOnEventMixin');

classSet = require('../core/util').classSet;

module.exports = React.createClass({
  displayName: 'StrokeWidthPicker',
  getState: function(tool) {
    if (tool == null) {
      tool = this.props.tool;
    }
    return {
      strokeWidth: tool.strokeWidth
    };
  },
  getInitialState: function() {
    return this.getState();
  },
  mixins: [createSetStateOnEventMixin('toolDidUpdateOptions')],
  componentWillReceiveProps: function(props) {
    return this.setState(this.getState(props.tool));
  },
  render: function() {
    var circle, div, li, ref, strokeWidths, svg, ul;
    ref = React.DOM, ul = ref.ul, li = ref.li, svg = ref.svg, circle = ref.circle, div = ref.div;
    strokeWidths = this.props.lc.opts.strokeWidths;
    return div({}, strokeWidths.map((function(_this) {
      return function(strokeWidth, ix) {
        var buttonClassName, buttonSize;
        buttonClassName = classSet({
          'square-toolbar-button': true,
          'selected': strokeWidth === _this.state.strokeWidth
        });
        buttonSize = 28;
        return div({
          key: strokeWidth
        }, div({
          className: buttonClassName,
          onClick: function() {
            return _this.props.lc.trigger('setStrokeWidth', strokeWidth);
          }
        }, svg({
          width: buttonSize - 2,
          height: buttonSize - 2,
          viewPort: "0 0 " + strokeWidth + " " + strokeWidth,
          version: "1.1",
          xmlns: "http://www.w3.org/2000/svg"
        }, circle({
          cx: Math.ceil(buttonSize / 2 - 1),
          cy: Math.ceil(buttonSize / 2 - 1),
          r: strokeWidth / 2
        }))));
      };
    })(this)));
  }
});
