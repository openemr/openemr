import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { Konva } from '../Global.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Wedge extends Shape {
    _sceneFunc(context) {
        context.beginPath();
        context.arc(0, 0, this.radius(), 0, Konva.getAngle(this.angle()), this.clockwise());
        context.lineTo(0, 0);
        context.closePath();
        context.fillStrokeShape(this);
    }
    getWidth() {
        return this.radius() * 2;
    }
    getHeight() {
        return this.radius() * 2;
    }
    setWidth(width) {
        this.radius(width / 2);
    }
    setHeight(height) {
        this.radius(height / 2);
    }
}
Wedge.prototype.className = 'Wedge';
Wedge.prototype._centroid = true;
Wedge.prototype._attrsAffectingSize = ['radius'];
_registerNode(Wedge);
Factory.addGetterSetter(Wedge, 'radius', 0, getNumberValidator());
Factory.addGetterSetter(Wedge, 'angle', 0, getNumberValidator());
Factory.addGetterSetter(Wedge, 'clockwise', false);
Factory.backCompat(Wedge, {
    angleDeg: 'angle',
    getAngleDeg: 'getAngle',
    setAngleDeg: 'setAngle',
});
