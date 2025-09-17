var React, StrokeWidthPicker, createSetStateOnEventMixin, defineOptionsStyle;

React = require('../reactGUI/React-shim');

defineOptionsStyle = require('./optionsStyles').defineOptionsStyle;

StrokeWidthPicker = React.createFactory(require('../reactGUI/StrokeWidthPicker'));

createSetStateOnEventMixin = require('../reactGUI/createSetStateOnEventMixin');

defineOptionsStyle('polygon-and-stroke-width', React.createClass({
  displayName: 'PolygonAndStrokeWidth',
  getState: function() {
    return {
      strokeWidth: this.props.tool.strokeWidth,
      inProgress: false
    };
  },
  getInitialState: function() {
    return this.getState();
  },
  mixins: [createSetStateOnEventMixin('toolChange')],
  componentDidMount: function() {
    var hidePolygonTools, showPolygonTools, unsubscribeFuncs;
    unsubscribeFuncs = [];
    this.unsubscribe = (function(_this) {
      return function() {
        var func, i, len, results;
        results = [];
        for (i = 0, len = unsubscribeFuncs.length; i < len; i++) {
          func = unsubscribeFuncs[i];
          results.push(func());
        }
        return results;
      };
    })(this);
    showPolygonTools = (function(_this) {
      return function() {
        if (!_this.state.inProgress) {
          return _this.setState({
            inProgress: true
          });
        }
      };
    })(this);
    hidePolygonTools = (function(_this) {
      return function() {
        return _this.setState({
          inProgress: false
        });
      };
    })(this);
    unsubscribeFuncs.push(this.props.lc.on('lc-polygon-started', showPolygonTools));
    return unsubscribeFuncs.push(this.props.lc.on('lc-polygon-stopped', hidePolygonTools));
  },
  componentWillUnmount: function() {
    return this.unsubscribe();
  },
  render: function() {
    var div, img, lc, polygonCancel, polygonFinishClosed, polygonFinishOpen, polygonToolStyle, ref;
    lc = this.props.lc;
    ref = React.DOM, div = ref.div, img = ref.img;
    polygonFinishOpen = (function(_this) {
      return function() {
        return lc.trigger('lc-polygon-finishopen');
      };
    })(this);
    polygonFinishClosed = (function(_this) {
      return function() {
        return lc.trigger('lc-polygon-finishclosed');
      };
    })(this);
    polygonCancel = (function(_this) {
      return function() {
        return lc.trigger('lc-polygon-cancel');
      };
    })(this);
    polygonToolStyle = {};
    if (!this.state.inProgress) {
      polygonToolStyle = {
        display: 'none'
      };
    }
    return div({}, div({
      className: 'polygon-toolbar horz-toolbar',
      style: polygonToolStyle
    }, div({
      className: 'square-toolbar-button',
      onClick: polygonFinishOpen
    }, img({
      src: this.props.imageURLPrefix + "/polygon-open.png"
    })), div({
      className: 'square-toolbar-button',
      onClick: polygonFinishClosed
    }, img({
      src: this.props.imageURLPrefix + "/polygon-closed.png"
    })), div({
      className: 'square-toolbar-button',
      onClick: polygonCancel
    }, img({
      src: this.props.imageURLPrefix + "/polygon-cancel.png"
    }))), div({}, StrokeWidthPicker({
      tool: this.props.tool,
      lc: this.props.lc
    })));
  }
}));

module.exports = {};
