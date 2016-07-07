var AddShapeAction, ClearAction;

ClearAction = (function() {
  function ClearAction(lc1, oldShapes, newShapes1) {
    this.lc = lc1;
    this.oldShapes = oldShapes;
    this.newShapes = newShapes1;
  }

  ClearAction.prototype["do"] = function() {
    this.lc.shapes = this.newShapes;
    return this.lc.repaintLayer('main');
  };

  ClearAction.prototype.undo = function() {
    this.lc.shapes = this.oldShapes;
    return this.lc.repaintLayer('main');
  };

  return ClearAction;

})();

AddShapeAction = (function() {
  function AddShapeAction(lc1, shape1, previousShapeId) {
    this.lc = lc1;
    this.shape = shape1;
    this.previousShapeId = previousShapeId != null ? previousShapeId : null;
  }

  AddShapeAction.prototype["do"] = function() {
    var found, i, len, newShapes, ref, shape;
    if (!this.lc.shapes.length || this.lc.shapes[this.lc.shapes.length - 1].id === this.previousShapeId || this.previousShapeId === null) {
      this.lc.shapes.push(this.shape);
    } else {
      newShapes = [];
      found = false;
      ref = this.lc.shapes;
      for (i = 0, len = ref.length; i < len; i++) {
        shape = ref[i];
        newShapes.push(shape);
        if (shape.id === this.previousShapeId) {
          newShapes.push(this.shape);
          found = true;
        }
      }
      if (!found) {
        newShapes.push(this.shape);
      }
      this.lc.shapes = newShapes;
    }
    return this.lc.repaintLayer('main');
  };

  AddShapeAction.prototype.undo = function() {
    var i, len, newShapes, ref, shape;
    if (this.lc.shapes[this.lc.shapes.length - 1].id === this.shape.id) {
      this.lc.shapes.pop();
    } else {
      newShapes = [];
      ref = this.lc.shapes;
      for (i = 0, len = ref.length; i < len; i++) {
        shape = ref[i];
        if (shape.id !== this.shape.id) {
          newShapes.push(shape);
        }
      }
      lc.shapes = newShapes;
    }
    return this.lc.repaintLayer('main');
  };

  return AddShapeAction;

})();

module.exports = {
  ClearAction: ClearAction,
  AddShapeAction: AddShapeAction
};
