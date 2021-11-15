import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Ellipse extends Shape {
    _sceneFunc(context) {
        var rx = this.radiusX(), ry = this.radiusY();
        context.beginPath();
        context.save();
        if (rx !== ry) {
            context.scale(1, ry / rx);
        }
        context.arc(0, 0, rx, 0, Math.PI * 2, false);
        context.restore();
        context.closePath();
        context.fillStrokeShape(this);
    }
    getWidth() {
        return this.radiusX() * 2;
    }
    getHeight() {
        return this.radiusY() * 2;
    }
    setWidth(width) {
        this.radiusX(width / 2);
    }
    setHeight(height) {
        this.radiusY(height / 2);
    }
}
Ellipse.prototype.className = 'Ellipse';
Ellipse.prototype._centroid = true;
Ellipse.prototype._attrsAffectingSize = ['radiusX', 'radiusY'];
_registerNode(Ellipse);
Factory.addComponentsGetterSetter(Ellipse, 'radius', ['x', 'y']);
Factory.addGetterSetter(Ellipse, 'radiusX', 0, getNumberValidator());
Factory.addGetterSetter(Ellipse, 'radiusY', 0, getNumberValidator());
