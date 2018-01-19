var ALL_FONTS, FONT_NAME_TO_VALUE, MONOSPACE_FONTS, OTHER_FONTS, React, SANS_SERIF_FONTS, SERIF_FONTS, _, defineOptionsStyle, i, j, l, len, len1, len2, len3, m, name, ref, ref1, ref2, ref3, value;

React = require('../reactGUI/React-shim');

defineOptionsStyle = require('./optionsStyles').defineOptionsStyle;

_ = require('../core/localization')._;

SANS_SERIF_FONTS = [['Arial', 'Arial,"Helvetica Neue",Helvetica,sans-serif'], ['Arial Black', '"Arial Black","Arial Bold",Gadget,sans-serif'], ['Arial Narrow', '"Arial Narrow",Arial,sans-serif'], ['Gill Sans', '"Gill Sans","Gill Sans MT",Calibri,sans-serif'], ['Helvetica', '"Helvetica Neue",Helvetica,Arial,sans-serif'], ['Impact', 'Impact,Haettenschweiler,"Franklin Gothic Bold",Charcoal,"Helvetica Inserat","Bitstream Vera Sans Bold","Arial Black",sans-serif'], ['Tahoma', 'Tahoma,Verdana,Segoe,sans-serif'], ['Trebuchet MS', '"Trebuchet MS","Lucida Grande","Lucida Sans Unicode","Lucida Sans",Tahoma,sans-serif'], ['Verdana', 'Verdana,Geneva,sans-serif']].map(function(arg) {
  var name, value;
  name = arg[0], value = arg[1];
  return {
    name: _(name),
    value: value
  };
});

SERIF_FONTS = [['Baskerville', 'Baskerville,"Baskerville Old Face","Hoefler Text",Garamond,"Times New Roman",serif'], ['Garamond', 'Garamond,Baskerville,"Baskerville Old Face","Hoefler Text","Times New Roman",serif'], ['Georgia', 'Georgia,Times,"Times New Roman",serif'], ['Hoefler Text', '"Hoefler Text","Baskerville Old Face",Garamond,"Times New Roman",serif'], ['Lucida Bright', '"Lucida Bright",Georgia,serif'], ['Palatino', 'Palatino,"Palatino Linotype","Palatino LT STD","Book Antiqua",Georgia,serif'], ['Times New Roman', 'TimesNewRoman,"Times New Roman",Times,Baskerville,Georgia,serif']].map(function(arg) {
  var name, value;
  name = arg[0], value = arg[1];
  return {
    name: _(name),
    value: value
  };
});

MONOSPACE_FONTS = [['Consolas/Monaco', 'Consolas,monaco,"Lucida Console",monospace'], ['Courier New', '"Courier New",Courier,"Lucida Sans Typewriter","Lucida Typewriter",monospace'], ['Lucida Sans Typewriter', '"Lucida Sans Typewriter","Lucida Console",monaco,"Bitstream Vera Sans Mono",monospace']].map(function(arg) {
  var name, value;
  name = arg[0], value = arg[1];
  return {
    name: _(name),
    value: value
  };
});

OTHER_FONTS = [['Copperplate', 'Copperplate,"Copperplate Gothic Light",fantasy'], ['Papyrus', 'Papyrus,fantasy'], ['Script', '"Brush Script MT",cursive']].map(function(arg) {
  var name, value;
  name = arg[0], value = arg[1];
  return {
    name: _(name),
    value: value
  };
});

ALL_FONTS = [[_('Sans Serif'), SANS_SERIF_FONTS], [_('Serif'), SERIF_FONTS], [_('Monospace'), MONOSPACE_FONTS], [_('Other'), OTHER_FONTS]];

FONT_NAME_TO_VALUE = {};

for (i = 0, len = SANS_SERIF_FONTS.length; i < len; i++) {
  ref = SANS_SERIF_FONTS[i], name = ref.name, value = ref.value;
  FONT_NAME_TO_VALUE[name] = value;
}

for (j = 0, len1 = SERIF_FONTS.length; j < len1; j++) {
  ref1 = SERIF_FONTS[j], name = ref1.name, value = ref1.value;
  FONT_NAME_TO_VALUE[name] = value;
}

for (l = 0, len2 = MONOSPACE_FONTS.length; l < len2; l++) {
  ref2 = MONOSPACE_FONTS[l], name = ref2.name, value = ref2.value;
  FONT_NAME_TO_VALUE[name] = value;
}

for (m = 0, len3 = OTHER_FONTS.length; m < len3; m++) {
  ref3 = OTHER_FONTS[m], name = ref3.name, value = ref3.value;
  FONT_NAME_TO_VALUE[name] = value;
}

defineOptionsStyle('font', React.createClass({
  displayName: 'FontOptions',
  getInitialState: function() {
    return {
      isItalic: false,
      isBold: false,
      fontName: 'Helvetica',
      fontSizeIndex: 4
    };
  },
  getFontSizes: function() {
    return [9, 10, 12, 14, 18, 24, 36, 48, 64, 72, 96, 144, 288];
  },
  updateTool: function(newState) {
    var fontSize, items, k;
    if (newState == null) {
      newState = {};
    }
    for (k in this.state) {
      if (!(k in newState)) {
        newState[k] = this.state[k];
      }
    }
    fontSize = this.getFontSizes()[newState.fontSizeIndex];
    items = [];
    if (newState.isItalic) {
      items.push('italic');
    }
    if (newState.isBold) {
      items.push('bold');
    }
    items.push(fontSize + "px");
    items.push(FONT_NAME_TO_VALUE[newState.fontName]);
    this.props.lc.tool.font = items.join(' ');
    return this.props.lc.trigger('setFont', items.join(' '));
  },
  handleFontSize: function(event) {
    var newState;
    newState = {
      fontSizeIndex: event.target.value
    };
    this.setState(newState);
    return this.updateTool(newState);
  },
  handleFontFamily: function(event) {
    var newState;
    newState = {
      fontName: event.target.selectedOptions[0].innerHTML
    };
    this.setState(newState);
    return this.updateTool(newState);
  },
  handleItalic: function(event) {
    var newState;
    newState = {
      isItalic: !this.state.isItalic
    };
    this.setState(newState);
    return this.updateTool(newState);
  },
  handleBold: function(event) {
    var newState;
    newState = {
      isBold: !this.state.isBold
    };
    this.setState(newState);
    return this.updateTool(newState);
  },
  componentDidMount: function() {
    return this.updateTool();
  },
  render: function() {
    var br, div, input, label, lc, optgroup, option, ref4, select, span;
    lc = this.props.lc;
    ref4 = React.DOM, div = ref4.div, input = ref4.input, select = ref4.select, option = ref4.option, br = ref4.br, label = ref4.label, span = ref4.span, optgroup = ref4.optgroup;
    return div({
      className: 'lc-font-settings'
    }, select({
      value: this.state.fontSizeIndex,
      onChange: this.handleFontSize
    }, this.getFontSizes().map((function(_this) {
      return function(size, ix) {
        return option({
          value: ix,
          key: ix
        }, size + "px");
      };
    })(this))), select({
      value: this.state.fontName,
      onChange: this.handleFontFamily
    }, ALL_FONTS.map((function(_this) {
      return function(arg) {
        var fonts, label;
        label = arg[0], fonts = arg[1];
        return optgroup({
          key: label,
          label: label
        }, fonts.map(function(family, ix) {
          return option({
            value: family.name,
            key: ix
          }, family.name);
        }));
      };
    })(this))), span({}, label({
      htmlFor: 'italic'
    }, _("italic")), input({
      type: 'checkbox',
      id: 'italic',
      checked: this.state.isItalic,
      onChange: this.handleItalic
    })), span({}, label({
      htmlFor: 'bold'
    }, _("bold")), input({
      type: 'checkbox',
      id: 'bold',
      checked: this.state.isBold,
      onChange: this.handleBold
    })));
  }
}));

module.exports = {};
