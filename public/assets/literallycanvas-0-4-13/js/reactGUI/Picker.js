var ClearButton, ColorPickers, ColorWell, Picker, React, UndoRedoButtons, ZoomButtons, _;

React = require('./React-shim');

ClearButton = React.createFactory(require('./ClearButton'));

UndoRedoButtons = React.createFactory(require('./UndoRedoButtons'));

ZoomButtons = React.createFactory(require('./ZoomButtons'));

_ = require('../core/localization')._;

ColorWell = React.createFactory(require('./ColorWell'));

ColorPickers = React.createFactory(React.createClass({
  displayName: 'ColorPickers',
  render: function() {
    var div, lc;
    lc = this.props.lc;
    div = React.DOM.div;
    return div({
      className: 'lc-color-pickers'
    }, ColorWell({
      lc: lc,
      colorName: 'primary',
      label: _('stroke')
    }), ColorWell({
      lc: lc,
      colorName: 'secondary',
      label: _('fill')
    }), ColorWell({
      lc: lc,
      colorName: 'background',
      label: _('bg')
    }));
  }
}));

Picker = React.createClass({
  displayName: 'Picker',
  getInitialState: function() {
    return {
      selectedToolIndex: 0
    };
  },
  renderBody: function() {
    var div, imageURLPrefix, lc, ref, toolButtonComponents;
    div = React.DOM.div;
    ref = this.props, toolButtonComponents = ref.toolButtonComponents, lc = ref.lc, imageURLPrefix = ref.imageURLPrefix;
    return div({
      className: 'lc-picker-contents'
    }, toolButtonComponents.map((function(_this) {
      return function(component, ix) {
        return component({
          lc: lc,
          imageURLPrefix: imageURLPrefix,
          key: ix,
          isSelected: ix === _this.state.selectedToolIndex,
          onSelect: function(tool) {
            lc.setTool(tool);
            return _this.setState({
              selectedToolIndex: ix
            });
          }
        });
      };
    })(this)), toolButtonComponents.length % 2 !== 0 ? div({
      className: 'toolbar-button thin-button disabled'
    }) : void 0, div({
      style: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0
      }
    }, ColorPickers({
      lc: this.props.lc
    }), UndoRedoButtons({
      lc: lc,
      imageURLPrefix: imageURLPrefix
    }), ZoomButtons({
      lc: lc,
      imageURLPrefix: imageURLPrefix
    }), ClearButton({
      lc: lc
    })));
  },
  render: function() {
    var div;
    div = React.DOM.div;
    return div({
      className: 'lc-picker'
    }, this.renderBody());
  }
});

module.exports = Picker;
