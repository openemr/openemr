var ColorGrid, ColorWell, PureRenderMixin, React, cancelAnimationFrame, classSet, getHSLAString, getHSLString, parseHSLAString, ref, requestAnimationFrame;

React = require('./React-shim');

PureRenderMixin = require('react-addons-pure-render-mixin');

ref = require('../core/util'), classSet = ref.classSet, requestAnimationFrame = ref.requestAnimationFrame, cancelAnimationFrame = ref.cancelAnimationFrame;

parseHSLAString = function(s) {
  var components, firstParen, insideParens, lastParen;
  if (s === 'transparent') {
    return {
      hue: 0,
      sat: 0,
      light: 0,
      alpha: 0
    };
  }
  if (s.substring(0, 4) !== 'hsla') {
    return null;
  }
  firstParen = s.indexOf('(');
  lastParen = s.indexOf(')');
  insideParens = s.substring(firstParen + 1, lastParen - firstParen + 4);
  components = (function() {
    var j, len, ref1, results;
    ref1 = insideParens.split(',');
    results = [];
    for (j = 0, len = ref1.length; j < len; j++) {
      s = ref1[j];
      results.push(s.trim());
    }
    return results;
  })();
  return {
    hue: parseInt(components[0], 10),
    sat: parseInt(components[1].substring(0, components[1].length - 1), 10),
    light: parseInt(components[2].substring(0, components[2].length - 1), 10),
    alpha: parseFloat(components[3])
  };
};

getHSLAString = function(arg) {
  var alpha, hue, light, sat;
  hue = arg.hue, sat = arg.sat, light = arg.light, alpha = arg.alpha;
  return "hsla(" + hue + ", " + sat + "%, " + light + "%, " + alpha + ")";
};

getHSLString = function(arg) {
  var hue, light, sat;
  hue = arg.hue, sat = arg.sat, light = arg.light;
  return "hsl(" + hue + ", " + sat + "%, " + light + "%)";
};

ColorGrid = React.createFactory(React.createClass({
  displayName: 'ColorGrid',
  mixins: [PureRenderMixin],
  render: function() {
    var div;
    div = React.DOM.div;
    return div({}, this.props.rows.map((function(_this) {
      return function(row, ix) {
        return div({
          className: 'color-row',
          key: ix,
          style: {
            width: 20 * row.length
          }
        }, row.map(function(cellColor, ix2) {
          var alpha, className, colorString, colorStringNoAlpha, hue, light, sat, update;
          hue = cellColor.hue, sat = cellColor.sat, light = cellColor.light, alpha = cellColor.alpha;
          colorString = getHSLAString(cellColor);
          colorStringNoAlpha = "hsl(" + hue + ", " + sat + "%, " + light + "%)";
          className = classSet({
            'color-cell': true,
            'selected': _this.props.selectedColor === colorString
          });
          update = function(e) {
            _this.props.onChange(cellColor, colorString);
            e.stopPropagation();
            return e.preventDefault();
          };
          return div({
            className: className,
            onTouchStart: update,
            onTouchMove: update,
            onClick: update,
            style: {
              backgroundColor: colorStringNoAlpha
            },
            key: ix2
          });
        }));
      };
    })(this)));
  }
}));

ColorWell = React.createClass({
  displayName: 'ColorWell',
  mixins: [PureRenderMixin],
  getInitialState: function() {
    var colorString, hsla;
    colorString = this.props.lc.colors[this.props.colorName];
    hsla = parseHSLAString(colorString);
    if (hsla == null) {
      hsla = {};
    }
    if (hsla.alpha == null) {
      hsla.alpha = 1;
    }
    if (hsla.sat == null) {
      hsla.sat = 100;
    }
    if (hsla.hue == null) {
      hsla.hue = 0;
    }
    if (hsla.light == null) {
      hsla.light = 50;
    }
    return {
      colorString: colorString,
      alpha: hsla.alpha,
      sat: hsla.sat === 0 ? 100 : hsla.sat,
      isPickerVisible: false,
      hsla: hsla
    };
  },
  componentDidMount: function() {
    return this.unsubscribe = this.props.lc.on(this.props.colorName + "ColorChange", (function(_this) {
      return function() {
        var colorString;
        colorString = _this.props.lc.colors[_this.props.colorName];
        _this.setState({
          colorString: colorString
        });
        return _this.setHSLAFromColorString(colorString);
      };
    })(this));
  },
  componentWillUnmount: function() {
    return this.unsubscribe();
  },
  setHSLAFromColorString: function(c) {
    var hsla;
    hsla = parseHSLAString(c);
    if (hsla) {
      return this.setState({
        hsla: hsla,
        alpha: hsla.alpha,
        sat: hsla.sat
      });
    } else {
      return this.setState({
        hsla: null,
        alpha: 1,
        sat: 100
      });
    }
  },
  closePicker: function() {
    return this.setState({
      isPickerVisible: false
    });
  },
  togglePicker: function() {
    var isPickerVisible, shouldResetSat;
    isPickerVisible = !this.state.isPickerVisible;
    shouldResetSat = isPickerVisible && this.state.sat === 0;
    this.setHSLAFromColorString(this.state.colorString);
    return this.setState({
      isPickerVisible: isPickerVisible,
      sat: shouldResetSat ? 100 : this.state.sat
    });
  },
  setColor: function(c) {
    this.setState({
      colorString: c
    });
    this.setHSLAFromColorString(c);
    return this.props.lc.setColor(this.props.colorName, c);
  },
  setAlpha: function(alpha) {
    var hsla;
    this.setState({
      alpha: alpha
    });
    if (this.state.hsla) {
      hsla = this.state.hsla;
      hsla.alpha = alpha;
      this.setState({
        hsla: hsla
      });
      return this.setColor(getHSLAString(hsla));
    }
  },
  setSat: function(sat) {
    var hsla;
    this.setState({
      sat: sat
    });
    if (isNaN(sat)) {
      throw "SAT";
    }
    if (this.state.hsla) {
      hsla = this.state.hsla;
      hsla.sat = sat;
      this.setState({
        hsla: hsla
      });
      return this.setColor(getHSLAString(hsla));
    }
  },
  render: function() {
    var br, div, label, ref1;
    ref1 = React.DOM, div = ref1.div, label = ref1.label, br = ref1.br;
    return div({
      className: classSet({
        'color-well': true,
        'open': this.state.isPickerVisible
      }),
      onMouseLeave: this.closePicker,
      style: {
        float: 'left',
        textAlign: 'center'
      }
    }, label({
      float: 'left'
    }, this.props.label), br({}), div({
      className: classSet({
        'color-well-color-container': true,
        'selected': this.state.isPickerVisible
      }),
      style: {
        backgroundColor: 'white'
      },
      onClick: this.togglePicker
    }, div({
      className: 'color-well-checker color-well-checker-top-left'
    }), div({
      className: 'color-well-checker color-well-checker-bottom-right',
      style: {
        left: '50%',
        top: '50%'
      }
    }), div({
      className: 'color-well-color',
      style: {
        backgroundColor: this.state.colorString
      }
    }, " ")), this.renderPicker());
  },
  renderPicker: function() {
    var div, hue, i, input, j, label, len, onSelectColor, ref1, ref2, renderColor, renderLabel, rows;
    ref1 = React.DOM, div = ref1.div, label = ref1.label, input = ref1.input;
    if (!this.state.isPickerVisible) {
      return null;
    }
    renderLabel = (function(_this) {
      return function(text) {
        return div({
          className: 'color-row label',
          key: text,
          style: {
            lineHeight: '20px',
            height: 16
          }
        }, text);
      };
    })(this);
    renderColor = (function(_this) {
      return function() {
        var checkerboardURL;
        checkerboardURL = _this.props.lc.opts.imageURLPrefix + "/checkerboard-8x8.png";
        return div({
          className: 'color-row',
          key: "color",
          style: {
            position: 'relative',
            backgroundImage: "url(" + checkerboardURL + ")",
            backgroundRepeat: 'repeat',
            height: 24
          }
        }, div({
          style: {
            position: 'absolute',
            top: 0,
            right: 0,
            bottom: 0,
            left: 0,
            backgroundColor: _this.state.colorString
          }
        }));
      };
    })(this);
    rows = [];
    rows.push((function() {
      var j, results;
      results = [];
      for (i = j = 0; j <= 100; i = j += 10) {
        results.push({
          hue: 0,
          sat: 0,
          light: i,
          alpha: this.state.alpha
        });
      }
      return results;
    }).call(this));
    ref2 = [0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330];
    for (j = 0, len = ref2.length; j < len; j++) {
      hue = ref2[j];
      rows.push((function() {
        var k, results;
        results = [];
        for (i = k = 10; k <= 90; i = k += 8) {
          results.push({
            hue: hue,
            sat: this.state.sat,
            light: i,
            alpha: this.state.alpha
          });
        }
        return results;
      }).call(this));
    }
    onSelectColor = (function(_this) {
      return function(hsla, s) {
        return _this.setColor(s);
      };
    })(this);
    return div({
      className: 'color-picker-popup'
    }, renderColor(), renderLabel("alpha"), input({
      type: 'range',
      min: 0,
      max: 1,
      step: 0.01,
      value: this.state.alpha,
      onChange: (function(_this) {
        return function(e) {
          return _this.setAlpha(parseFloat(e.target.value));
        };
      })(this)
    }), renderLabel("saturation"), input({
      type: 'range',
      min: 0,
      max: 100,
      value: this.state.sat,
      max: 100,
      onChange: (function(_this) {
        return function(e) {
          return _this.setSat(parseInt(e.target.value, 10));
        };
      })(this)
    }), ColorGrid({
      rows: rows,
      selectedColor: this.state.colorString,
      onChange: onSelectColor
    }));
  }
});

module.exports = ColorWell;
