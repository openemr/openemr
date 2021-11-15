import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { getNumberValidator, getNumberArrayValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
function getControlPoints(x0, y0, x1, y1, x2, y2, t) {
    var d01 = Math.sqrt(Math.pow(x1 - x0, 2) + Math.pow(y1 - y0, 2)), d12 = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2)), fa = (t * d01) / (d01 + d12), fb = (t * d12) / (d01 + d12), p1x = x1 - fa * (x2 - x0), p1y = y1 - fa * (y2 - y0), p2x = x1 + fb * (x2 - x0), p2y = y1 + fb * (y2 - y0);
    return [p1x, p1y, p2x, p2y];
}
function expandPoints(p, tension) {
    var len = p.length, allPoints = [], n, cp;
    for (n = 2; n < len - 2; n += 2) {
        cp = getControlPoints(p[n - 2], p[n - 1], p[n], p[n + 1], p[n + 2], p[n + 3], tension);
        if (isNaN(cp[0])) {
            continue;
        }
        allPoints.push(cp[0]);
        allPoints.push(cp[1]);
        allPoints.push(p[n]);
        allPoints.push(p[n + 1]);
        allPoints.push(cp[2]);
        allPoints.push(cp[3]);
    }
    return allPoints;
}
export class Line extends Shape {
    constructor(config) {
        super(config);
        this.on('pointsChange.konva tensionChange.konva closedChange.konva bezierChange.konva', function () {
            this._clearCache('tensionPoints');
        });
    }
    _sceneFunc(context) {
        var points = this.points(), length = points.length, tension = this.tension(), closed = this.closed(), bezier = this.bezier(), tp, len, n;
        if (!length) {
            return;
        }
        context.beginPath();
        context.moveTo(points[0], points[1]);
        if (tension !== 0 && length > 4) {
            tp = this.getTensionPoints();
            len = tp.length;
            n = closed ? 0 : 4;
            if (!closed) {
                context.quadraticCurveTo(tp[0], tp[1], tp[2], tp[3]);
            }
            while (n < len - 2) {
                context.bezierCurveTo(tp[n++], tp[n++], tp[n++], tp[n++], tp[n++], tp[n++]);
            }
            if (!closed) {
                context.quadraticCurveTo(tp[len - 2], tp[len - 1], points[length - 2], points[length - 1]);
            }
        }
        else if (bezier) {
            n = 2;
            while (n < length) {
                context.bezierCurveTo(points[n++], points[n++], points[n++], points[n++], points[n++], points[n++]);
            }
        }
        else {
            for (n = 2; n < length; n += 2) {
                context.lineTo(points[n], points[n + 1]);
            }
        }
        if (closed) {
            context.closePath();
            context.fillStrokeShape(this);
        }
        else {
            context.strokeShape(this);
        }
    }
    getTensionPoints() {
        return this._getCache('tensionPoints', this._getTensionPoints);
    }
    _getTensionPoints() {
        if (this.closed()) {
            return this._getTensionPointsClosed();
        }
        else {
            return expandPoints(this.points(), this.tension());
        }
    }
    _getTensionPointsClosed() {
        var p = this.points(), len = p.length, tension = this.tension(), firstControlPoints = getControlPoints(p[len - 2], p[len - 1], p[0], p[1], p[2], p[3], tension), lastControlPoints = getControlPoints(p[len - 4], p[len - 3], p[len - 2], p[len - 1], p[0], p[1], tension), middle = expandPoints(p, tension), tp = [firstControlPoints[2], firstControlPoints[3]]
            .concat(middle)
            .concat([
            lastControlPoints[0],
            lastControlPoints[1],
            p[len - 2],
            p[len - 1],
            lastControlPoints[2],
            lastControlPoints[3],
            firstControlPoints[0],
            firstControlPoints[1],
            p[0],
            p[1],
        ]);
        return tp;
    }
    getWidth() {
        return this.getSelfRect().width;
    }
    getHeight() {
        return this.getSelfRect().height;
    }
    getSelfRect() {
        var points = this.points();
        if (points.length < 4) {
            return {
                x: points[0] || 0,
                y: points[1] || 0,
                width: 0,
                height: 0,
            };
        }
        if (this.tension() !== 0) {
            points = [
                points[0],
                points[1],
                ...this._getTensionPoints(),
                points[points.length - 2],
                points[points.length - 1],
            ];
        }
        else {
            points = this.points();
        }
        var minX = points[0];
        var maxX = points[0];
        var minY = points[1];
        var maxY = points[1];
        var x, y;
        for (var i = 0; i < points.length / 2; i++) {
            x = points[i * 2];
            y = points[i * 2 + 1];
            minX = Math.min(minX, x);
            maxX = Math.max(maxX, x);
            minY = Math.min(minY, y);
            maxY = Math.max(maxY, y);
        }
        return {
            x: minX,
            y: minY,
            width: maxX - minX,
            height: maxY - minY,
        };
    }
}
Line.prototype.className = 'Line';
Line.prototype._attrsAffectingSize = ['points', 'bezier', 'tension'];
_registerNode(Line);
Factory.addGetterSetter(Line, 'closed', false);
Factory.addGetterSetter(Line, 'bezier', false);
Factory.addGetterSetter(Line, 'tension', 0, getNumberValidator());
Factory.addGetterSetter(Line, 'points', [], getNumberArrayValidator());
