var React, classSet, createToolButton;

React = require('./React-shim');

classSet = require('../core/util').classSet;

createToolButton = function(tool) {
  var displayName, imageName;
  displayName = tool.name;
  imageName = tool.iconName;
  return React.createFactory(React.createClass({
    displayName: displayName,
    getDefaultProps: function() {
      return {
        isSelected: false,
        lc: null
      };
    },
    componentWillMount: function() {
      if (this.props.isSelected) {
        return this.props.lc.setTool(tool);
      }
    },
    render: function() {
      var className, div, imageURLPrefix, img, isSelected, onSelect, ref, ref1, src;
      ref = React.DOM, div = ref.div, img = ref.img;
      ref1 = this.props, imageURLPrefix = ref1.imageURLPrefix, isSelected = ref1.isSelected, onSelect = ref1.onSelect;
      className = classSet({
        'lc-pick-tool': true,
        'toolbar-button': true,
        'thin-button': true,
        'selected': isSelected
      });
      src = imageURLPrefix + "/" + imageName + ".png";
      return div({
        className: className,
        style: {
          'backgroundImage': "url(" + src + ")"
        },
        onClick: (function() {
          return onSelect(tool);
        }),
        title: displayName
      });
    }
  }));
};

module.exports = createToolButton;
