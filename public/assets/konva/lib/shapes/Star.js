import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Star extends Shape {
    _sceneFunc(context) {
        var innerRadius = this.innerRadius(), outerRadius = this.outerRadius(), numPoints = this.numPoints();
        context.beginPath();
        context.moveTo(0, 0 - outerRadius);
        for (var n = 1; n < numPoints * 2; n++) {
            var radius = n % 2 === 0 ? outerRadius : innerRadius;
            var x = radius * Math.sin((n * Math.PI) / numPoints);
            var y = -1 * radius * Math.cos((n * Math.PI) / numPoints);
            context.lineTo(x, y);
        }
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
Star.prototype.className = 'Star';
Star.prototype._centroid = true;
Star.prototype._attrsAffectingSize = ['innerRadius', 'outerRadius'];
_registerNode(Star);
Factory.addGetterSetter(Star, 'numPoints', 5, getNumberValidator());
Factory.addGetterSetter(Star, 'innerRadius', 0, getNumberValidator());
Factory.addGetterSetter(Star, 'outerRadius', 0, getNumberValidator());
