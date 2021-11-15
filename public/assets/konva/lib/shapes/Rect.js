import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { _registerNode } from '../Global.js';
import { getNumberOrArrayOfNumbersValidator } from '../Validators.js';
export class Rect extends Shape {
    _sceneFunc(context) {
        var cornerRadius = this.cornerRadius(), width = this.width(), height = this.height();
        context.beginPath();
        if (!cornerRadius) {
            context.rect(0, 0, width, height);
        }
        else {
            let topLeft = 0;
            let topRight = 0;
            let bottomLeft = 0;
            let bottomRight = 0;
            if (typeof cornerRadius === 'number') {
                topLeft = topRight = bottomLeft = bottomRight = Math.min(cornerRadius, width / 2, height / 2);
            }
            else {
                topLeft = Math.min(cornerRadius[0] || 0, width / 2, height / 2);
                topRight = Math.min(cornerRadius[1] || 0, width / 2, height / 2);
                bottomRight = Math.min(cornerRadius[2] || 0, width / 2, height / 2);
                bottomLeft = Math.min(cornerRadius[3] || 0, width / 2, height / 2);
            }
            context.moveTo(topLeft, 0);
            context.lineTo(width - topRight, 0);
            context.arc(width - topRight, topRight, topRight, (Math.PI * 3) / 2, 0, false);
            context.lineTo(width, height - bottomRight);
            context.arc(width - bottomRight, height - bottomRight, bottomRight, 0, Math.PI / 2, false);
            context.lineTo(bottomLeft, height);
            context.arc(bottomLeft, height - bottomLeft, bottomLeft, Math.PI / 2, Math.PI, false);
            context.lineTo(0, topLeft);
            context.arc(topLeft, topLeft, topLeft, Math.PI, (Math.PI * 3) / 2, false);
        }
        context.closePath();
        context.fillStrokeShape(this);
    }
}
Rect.prototype.className = 'Rect';
_registerNode(Rect);
Factory.addGetterSetter(Rect, 'cornerRadius', 0, getNumberOrArrayOfNumbersValidator(4));
