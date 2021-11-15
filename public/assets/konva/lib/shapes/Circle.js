import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Circle extends Shape {
    _sceneFunc(context) {
        context.beginPath();
        context.arc(0, 0, this.attrs.radius || 0, 0, Math.PI * 2, false);
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
        if (this.radius() !== width / 2) {
            this.radius(width / 2);
        }
    }
    setHeight(height) {
        if (this.radius() !== height / 2) {
            this.radius(height / 2);
        }
    }
}
Circle.prototype._centroid = true;
Circle.prototype.className = 'Circle';
Circle.prototype._attrsAffectingSize = ['radius'];
_registerNode(Circle);
Factory.addGetterSetter(Circle, 'radius', 0, getNumberValidator());
