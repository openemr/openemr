import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { Konva } from '../Global.js';
import { getNumberValidator, getBooleanValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Arc extends Shape {
    _sceneFunc(context) {
        var angle = Konva.getAngle(this.angle()), clockwise = this.clockwise();
        context.beginPath();
        context.arc(0, 0, this.outerRadius(), 0, angle, clockwise);
        context.arc(0, 0, this.innerRadius(), angle, 0, !clockwise);
        context.closePath();
        context.fillStrokeShape(this);
    }
    getWidth() {
        return this.outerRadius() * 2;
    }
    getHeight() {
        return this.outerRadius() * 2;
    }
    setWidth(width) {
        this.outerRadius(width / 2);
    }
    setHeight(height) {
        this.outerRadius(height / 2);
    }
}
Arc.prototype._centroid = true;
Arc.prototype.className = 'Arc';
Arc.prototype._attrsAffectingSize = ['innerRadius', 'outerRadius'];
_registerNode(Arc);
Factory.addGetterSetter(Arc, 'innerRadius', 0, getNumberValidator());
Factory.addGetterSetter(Arc, 'outerRadius', 0, getNumberValidator());
Factory.addGetterSetter(Arc, 'angle', 0, getNumberValidator());
Factory.addGetterSetter(Arc, 'clockwise', false, getBooleanValidator());
