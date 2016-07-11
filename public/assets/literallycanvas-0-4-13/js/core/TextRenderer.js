var TextRenderer, getLinesToRender, getNextLine, parseFontString;

require('./fontmetrics.js');

parseFontString = function(font) {
  var fontFamily, fontItems, fontSize, item, j, len, maybeSize, remainingFontString;
  fontItems = font.split(' ');
  fontSize = 0;
  for (j = 0, len = fontItems.length; j < len; j++) {
    item = fontItems[j];
    maybeSize = parseInt(item.replace("px", ""), 10);
    if (!isNaN(maybeSize)) {
      fontSize = maybeSize;
    }
  }
  if (!fontSize) {
    throw "Font size not found";
  }
  remainingFontString = font.substring(fontItems[0].length + 1).replace('bold ', '').replace('italic ', '').replace('underline ', '');
  fontFamily = remainingFontString;
  return {
    fontSize: fontSize,
    fontFamily: fontFamily
  };
};

getNextLine = function(ctx, text, forcedWidth) {
  var doesSubstringFit, endIndex, isEndOfString, isNonWord, isWhitespace, lastGoodIndex, lastOkayIndex, nextWordStartIndex, textToHere, wasInWord;
  if (!text.length) {
    return ['', ''];
  }
  endIndex = 0;
  lastGoodIndex = 0;
  lastOkayIndex = 0;
  wasInWord = false;
  while (true) {
    endIndex += 1;
    isEndOfString = endIndex >= text.length;
    isWhitespace = (!isEndOfString) && text[endIndex].match(/\s/);
    isNonWord = isWhitespace || isEndOfString;
    textToHere = text.substring(0, endIndex);
    doesSubstringFit = forcedWidth ? ctx.measureTextWidth(textToHere).width <= forcedWidth : true;
    if (doesSubstringFit) {
      lastOkayIndex = endIndex;
    }
    if (isNonWord && wasInWord) {
      wasInWord = false;
      if (doesSubstringFit) {
        lastGoodIndex = endIndex;
      }
    }
    wasInWord = !isWhitespace;
    if (isEndOfString || !doesSubstringFit) {
      if (doesSubstringFit) {
        return [text, ''];
      } else if (lastGoodIndex > 0) {
        nextWordStartIndex = lastGoodIndex + 1;
        while (nextWordStartIndex < text.length && text[nextWordStartIndex].match('/\s/')) {
          nextWordStartIndex += 1;
        }
        return [text.substring(0, lastGoodIndex), text.substring(nextWordStartIndex)];
      } else {
        return [text.substring(0, lastOkayIndex), text.substring(lastOkayIndex)];
      }
    }
  }
};

getLinesToRender = function(ctx, text, forcedWidth) {
  var j, len, lines, nextLine, ref, ref1, remainingText, textLine, textSplitOnLines;
  textSplitOnLines = text.split(/\r\n|\r|\n/g);
  lines = [];
  for (j = 0, len = textSplitOnLines.length; j < len; j++) {
    textLine = textSplitOnLines[j];
    ref = getNextLine(ctx, textLine, forcedWidth), nextLine = ref[0], remainingText = ref[1];
    if (nextLine) {
      while (nextLine) {
        lines.push(nextLine);
        ref1 = getNextLine(ctx, remainingText, forcedWidth), nextLine = ref1[0], remainingText = ref1[1];
      }
    } else {
      lines.push(textLine);
    }
  }
  return lines;
};

TextRenderer = (function() {
  function TextRenderer(ctx, text1, font1, forcedWidth1, forcedHeight) {
    var fontFamily, fontSize, ref;
    this.text = text1;
    this.font = font1;
    this.forcedWidth = forcedWidth1;
    this.forcedHeight = forcedHeight;
    ref = parseFontString(this.font), fontFamily = ref.fontFamily, fontSize = ref.fontSize;
    ctx.font = this.font;
    ctx.textBaseline = 'baseline';
    this.emDashWidth = ctx.measureTextWidth('—', fontSize, fontFamily).width;
    this.caratWidth = ctx.measureTextWidth('|', fontSize, fontFamily).width;
    this.lines = getLinesToRender(ctx, this.text, this.forcedWidth);
    this.metricses = this.lines.map((function(_this) {
      return function(line) {
        return ctx.measureText2(line || 'X', fontSize, _this.font);
      };
    })(this));
    this.metrics = {
      ascent: Math.max.apply(Math, this.metricses.map(function(arg) {
        var ascent;
        ascent = arg.ascent;
        return ascent;
      })),
      descent: Math.max.apply(Math, this.metricses.map(function(arg) {
        var descent;
        descent = arg.descent;
        return descent;
      })),
      fontsize: Math.max.apply(Math, this.metricses.map(function(arg) {
        var fontsize;
        fontsize = arg.fontsize;
        return fontsize;
      })),
      leading: Math.max.apply(Math, this.metricses.map(function(arg) {
        var leading;
        leading = arg.leading;
        return leading;
      })),
      width: Math.max.apply(Math, this.metricses.map(function(arg) {
        var width;
        width = arg.width;
        return width;
      })),
      height: Math.max.apply(Math, this.metricses.map(function(arg) {
        var height;
        height = arg.height;
        return height;
      })),
      bounds: {
        minx: Math.min.apply(Math, this.metricses.map(function(arg) {
          var bounds;
          bounds = arg.bounds;
          return bounds.minx;
        })),
        miny: Math.min.apply(Math, this.metricses.map(function(arg) {
          var bounds;
          bounds = arg.bounds;
          return bounds.miny;
        })),
        maxx: Math.max.apply(Math, this.metricses.map(function(arg) {
          var bounds;
          bounds = arg.bounds;
          return bounds.maxx;
        })),
        maxy: Math.max.apply(Math, this.metricses.map(function(arg) {
          var bounds;
          bounds = arg.bounds;
          return bounds.maxy;
        }))
      }
    };
    this.boundingBoxWidth = Math.ceil(this.metrics.width);
  }

  TextRenderer.prototype.draw = function(ctx, x, y) {
    var i, j, len, line, ref, results;
    ctx.textBaseline = 'top';
    ctx.font = this.font;
    i = 0;
    ref = this.lines;
    results = [];
    for (j = 0, len = ref.length; j < len; j++) {
      line = ref[j];
      ctx.fillText(line, x, y + i * this.metrics.leading);
      results.push(i += 1);
    }
    return results;
  };

  TextRenderer.prototype.getWidth = function(isEditing) {
    if (isEditing == null) {
      isEditing = false;
    }
    if (this.forcedWidth) {
      return this.forcedWidth;
    } else {
      if (isEditing) {
        return this.metrics.bounds.maxx + this.caratWidth;
      } else {
        return this.metrics.bounds.maxx;
      }
    }
  };

  TextRenderer.prototype.getHeight = function() {
    return this.forcedHeight || (this.metrics.leading * this.lines.length);
  };

  return TextRenderer;

})();

module.exports = TextRenderer;
