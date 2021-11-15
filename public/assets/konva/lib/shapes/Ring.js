import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
var PIx2 = Math.PI * 2;
export class Ring extends Shape {
    _sceneFunc(context) {
        context.beginPath();
        context.arc(0, 0, this.innerRadius(), 0, PIx2, false);
        context.moveTo(this.outerRadius(), 0);
        context.arc(0, 0, this.outerRadius(), PIx2, 0, true);
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
Ring.prototype.className = 'Ring';
Ring.prototype._centroid = true;
Ring.prototype._attrsAffectingSize = ['innerRadius', 'outerRadius'];
_registerNode(Ring);
Factory.addGetterSetter(Ring, 'innerRadius', 0, getNumberValidator());
Factory.addGetterSetter(Ring, 'outerRadius', 0, getNumberValidator());
