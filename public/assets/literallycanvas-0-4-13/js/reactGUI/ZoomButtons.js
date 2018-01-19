var React, ZoomButtons, ZoomInButton, ZoomOutButton, classSet, createSetStateOnEventMixin, createZoomButtonComponent;

React = require('./React-shim');

createSetStateOnEventMixin = require('./createSetStateOnEventMixin');

classSet = require('../core/util').classSet;

createZoomButtonComponent = function(inOrOut) {
  return React.createClass({
    displayName: inOrOut === 'in' ? 'ZoomInButton' : 'ZoomOutButton',
    getState: function() {
      return {
        isEnabled: (function() {
          switch (false) {
            case inOrOut !== 'in':
              return this.props.lc.scale < this.props.lc.config.zoomMax;
            case inOrOut !== 'out':
              return this.props.lc.scale > this.props.lc.config.zoomMin;
          }
        }).call(this)
      };
    },
    getInitialState: function() {
      return this.getState();
    },
    mixins: [createSetStateOnEventMixin('zoom')],
    render: function() {
      var className, div, imageURLPrefix, img, lc, onClick, ref, ref1, src, style, title;
      ref = React.DOM, div = ref.div, img = ref.img;
      ref1 = this.props, lc = ref1.lc, imageURLPrefix = ref1.imageURLPrefix;
      title = inOrOut === 'in' ? 'Zoom in' : 'Zoom out';
      className = ("lc-zoom-" + inOrOut + " ") + classSet({
        'toolbar-button': true,
        'thin-button': true,
        'disabled': !this.state.isEnabled
      });
      onClick = (function() {
        switch (false) {
          case !!this.state.isEnabled:
            return function() {};
          case inOrOut !== 'in':
            return function() {
              return lc.zoom(lc.config.zoomStep);
            };
          case inOrOut !== 'out':
            return function() {
              return lc.zoom(-lc.config.zoomStep);
            };
        }
      }).call(this);
      src = imageURLPrefix + "/zoom-" + inOrOut + ".png";
      style = {
        backgroundImage: "url(" + src + ")"
      };
      return div({
        className: className,
        onClick: onClick,
        title: title,
        style: style
      });
    }
  });
};

ZoomOutButton = React.createFactory(createZoomButtonComponent('out'));

ZoomInButton = React.createFactory(createZoomButtonComponent('in'));

ZoomButtons = React.createClass({
  displayName: 'ZoomButtons',
  render: function() {
    var div;
    div = React.DOM.div;
    return div({
      className: 'lc-zoom'
    }, ZoomOutButton(this.props), ZoomInButton(this.props));
  }
});

module.exports = ZoomButtons;
