'use strict';

var React = require('../reactGUI/React-shim');

var _require = require('./optionsStyles');

var defineOptionsStyle = _require.defineOptionsStyle;

var createSetStateOnEventMixin = require('../reactGUI/createSetStateOnEventMixin');

defineOptionsStyle('stroke-or-fill', React.createClass({
  displayName: 'StrokeOrFillPicker',
  getState: function getState() {
    return { strokeOrFill: 'stroke' };
  },
  getInitialState: function getInitialState() {
    return this.getState();
  },
  mixins: [createSetStateOnEventMixin('toolChange')],

  onChange: function onChange(e) {
    if (e.target.id == 'stroke-or-fill-stroke') {
      this.props.lc.tool.strokeOrFill = 'stroke';
    } else {
      this.props.lc.tool.strokeOrFill = 'fill';
    }
    this.setState(this.getState());
  },

  render: function render() {
    var lc = this.props.lc;

    return React.createElement(
      'form',
      null,
      React.createElement(
        'span',
        null,
        'Color to change: '
      ),
      React.createElement(
        'span',
        null,
        React.createElement('input', { type: 'radio', name: 'stroke-or-fill', value: 'stroke',
          id: 'stroke-or-fill-stroke', onChange: this.onChange,
          checked: lc.tool.strokeOrFill == 'stroke' }),
        React.createElement(
          'label',
          { htmlFor: 'stroke-or-fill-stroke', className: 'label' },
          ' stroke'
        )
      ),
      React.createElement(
        'span',
        null,
        React.createElement('input', { type: 'radio', name: 'stroke-or-fill', value: 'fill',
          id: 'stroke-or-fill-fill', onChange: this.onChange,
          checked: lc.tool.strokeOrFill == 'fill' }),
        React.createElement(
          'label',
          { htmlFor: 'stroke-or-fill-fill', className: 'label' },
          ' fill'
        )
      )
    );
  }
}));

module.exports = {};