var bindEvents, buttonIsDown, coordsForTouchEvent, position;

coordsForTouchEvent = function(el, e) {
  var p, tx, ty;
  tx = e.changedTouches[0].clientX;
  ty = e.changedTouches[0].clientY;
  p = el.getBoundingClientRect();
  return [tx - p.left, ty - p.top];
};

position = function(el, e) {
  var p;
  p = el.getBoundingClientRect();
  return {
    left: e.clientX - p.left,
    top: e.clientY - p.top
  };
};

buttonIsDown = function(e) {
  if (e.buttons != null) {
    return e.buttons === 1;
  } else {
    return e.which > 0;
  }
};

module.exports = bindEvents = function(lc, canvas, panWithKeyboard) {
  var listener, mouseMoveListener, mouseUpListener, touchEndListener, touchMoveListener, unsubs;
  if (panWithKeyboard == null) {
    panWithKeyboard = false;
  }
  unsubs = [];
  mouseMoveListener = (function(_this) {
    return function(e) {
      var p;
      e.preventDefault();
      p = position(canvas, e);
      return lc.pointerMove(p.left, p.top);
    };
  })(this);
  mouseUpListener = (function(_this) {
    return function(e) {
      var p;
      e.preventDefault();
      canvas.onselectstart = function() {
        return true;
      };
      p = position(canvas, e);
      lc.pointerUp(p.left, p.top);
      document.removeEventListener('mousemove', mouseMoveListener);
      document.removeEventListener('mouseup', mouseUpListener);
      return canvas.addEventListener('mousemove', mouseMoveListener);
    };
  })(this);
  canvas.addEventListener('mousedown', (function(_this) {
    return function(e) {
      var down, p;
      if (e.target.tagName.toLowerCase() !== 'canvas') {
        return;
      }
      down = true;
      e.preventDefault();
      canvas.onselectstart = function() {
        return false;
      };
      p = position(canvas, e);
      lc.pointerDown(p.left, p.top);
      canvas.removeEventListener('mousemove', mouseMoveListener);
      document.addEventListener('mousemove', mouseMoveListener);
      return document.addEventListener('mouseup', mouseUpListener);
    };
  })(this));
  touchMoveListener = function(e) {
    e.preventDefault();
    return lc.pointerMove.apply(lc, coordsForTouchEvent(canvas, e));
  };
  touchEndListener = function(e) {
    e.preventDefault();
    lc.pointerUp.apply(lc, coordsForTouchEvent(canvas, e));
    document.removeEventListener('touchmove', touchMoveListener);
    document.removeEventListener('touchend', touchEndListener);
    return document.removeEventListener('touchcancel', touchEndListener);
  };
  canvas.addEventListener('touchstart', function(e) {
    if (e.target.tagName.toLowerCase() !== 'canvas') {
      return;
    }
    e.preventDefault();
    if (e.touches.length === 1) {
      lc.pointerDown.apply(lc, coordsForTouchEvent(canvas, e));
      document.addEventListener('touchmove', touchMoveListener);
      document.addEventListener('touchend', touchEndListener);
      return document.addEventListener('touchcancel', touchEndListener);
    } else {
      return lc.pointerMove.apply(lc, coordsForTouchEvent(canvas, e));
    }
  });
  if (panWithKeyboard) {
    console.warn("Keyboard panning is deprecated.");
    listener = function(e) {
      switch (e.keyCode) {
        case 37:
          lc.pan(-10, 0);
          break;
        case 38:
          lc.pan(0, -10);
          break;
        case 39:
          lc.pan(10, 0);
          break;
        case 40:
          lc.pan(0, 10);
      }
      return lc.repaintAllLayers();
    };
    document.addEventListener('keydown', listener);
    unsubs.push(function() {
      return document.removeEventListener(listener);
    });
  }
  return function() {
    var f, i, len, results;
    results = [];
    for (i = 0, len = unsubs.length; i < len; i++) {
      f = unsubs[i];
      results.push(f());
    }
    return results;
  };
};
