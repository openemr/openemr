var Point, _slope, math, normals, unit, util;

Point = require('./shapes').Point;

util = require('./util');

math = {};

math.toPoly = function(line) {
  var i, index, len, n, point, polyLeft, polyRight;
  polyLeft = [];
  polyRight = [];
  index = 0;
  for (i = 0, len = line.length; i < len; i++) {
    point = line[i];
    n = normals(point, _slope(line, index));
    polyLeft = polyLeft.concat([n[0]]);
    polyRight = [n[1]].concat(polyRight);
    index += 1;
  }
  return polyLeft.concat(polyRight);
};

_slope = function(line, index) {
  var point;
  if (line.length < 3) {
    point = {
      x: 0,
      y: 0
    };
  }
  if (index === 0) {
    point = _slope(line, index + 1);
  } else if (index === line.length - 1) {
    point = _slope(line, index - 1);
  } else {
    point = math.diff(line[index - 1], line[index + 1]);
  }
  return point;
};

math.diff = function(a, b) {
  return {
    x: b.x - a.x,
    y: b.y - a.y
  };
};

unit = function(vector) {
  var length;
  length = math.len(vector);
  return {
    x: vector.x / length,
    y: vector.y / length
  };
};

normals = function(p, slope) {
  slope = unit(slope);
  slope.x = slope.x * p.size / 2;
  slope.y = slope.y * p.size / 2;
  return [
    {
      x: p.x - slope.y,
      y: p.y + slope.x,
      color: p.color
    }, {
      x: p.x + slope.y,
      y: p.y - slope.x,
      color: p.color
    }
  ];
};

math.len = function(vector) {
  return Math.sqrt(Math.pow(vector.x, 2) + Math.pow(vector.y, 2));
};

math.scalePositionScalar = function(val, viewportSize, oldScale, newScale) {
  var newSize, oldSize;
  oldSize = viewportSize * oldScale;
  newSize = viewportSize * newScale;
  return val + (oldSize - newSize) / 2;
};

module.exports = math;
