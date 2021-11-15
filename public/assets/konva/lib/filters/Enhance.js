import { Factory } from '../Factory.js';
import { Node } from '../Node.js';
import { getNumberValidator } from '../Validators.js';
function remap(fromValue, fromMin, fromMax, toMin, toMax) {
    var fromRange = fromMax - fromMin, toRange = toMax - toMin, toValue;
    if (fromRange === 0) {
        return toMin + toRange / 2;
    }
    if (toRange === 0) {
        return toMin;
    }
    toValue = (fromValue - fromMin) / fromRange;
    toValue = toRange * toValue + toMin;
    return toValue;
}
export const Enhance = function (imageData) {
    var data = imageData.data, nSubPixels = data.length, rMin = data[0], rMax = rMin, r, gMin = data[1], gMax = gMin, g, bMin = data[2], bMax = bMin, b, i;
    var enhanceAmount = this.enhance();
    if (enhanceAmount === 0) {
        return;
    }
    for (i = 0; i < nSubPixels; i += 4) {
        r = data[i + 0];
        if (r < rMin) {
            rMin = r;
        }
        else if (r > rMax) {
            rMax = r;
        }
        g = data[i + 1];
        if (g < gMin) {
            gMin = g;
        }
        else if (g > gMax) {
            gMax = g;
        }
        b = data[i + 2];
        if (b < bMin) {
            bMin = b;
        }
        else if (b > bMax) {
            bMax = b;
        }
    }
    if (rMax === rMin) {
        rMax = 255;
        rMin = 0;
    }
    if (gMax === gMin) {
        gMax = 255;
        gMin = 0;
    }
    if (bMax === bMin) {
        bMax = 255;
        bMin = 0;
    }
    var rMid, rGoalMax, rGoalMin, gMid, gGoalMax, gGoalMin, bMid, bGoalMax, bGoalMin;
    if (enhanceAmount > 0) {
        rGoalMax = rMax + enhanceAmount * (255 - rMax);
        rGoalMin = rMin - enhanceAmount * (rMin - 0);
        gGoalMax = gMax + enhanceAmount * (255 - gMax);
        gGoalMin = gMin - enhanceAmount * (gMin - 0);
        bGoalMax = bMax + enhanceAmount * (255 - bMax);
        bGoalMin = bMin - enhanceAmount * (bMin - 0);
    }
    else {
        rMid = (rMax + rMin) * 0.5;
        rGoalMax = rMax + enhanceAmount * (rMax - rMid);
        rGoalMin = rMin + enhanceAmount * (rMin - rMid);
        gMid = (gMax + gMin) * 0.5;
        gGoalMax = gMax + enhanceAmount * (gMax - gMid);
        gGoalMin = gMin + enhanceAmount * (gMin - gMid);
        bMid = (bMax + bMin) * 0.5;
        bGoalMax = bMax + enhanceAmount * (bMax - bMid);
        bGoalMin = bMin + enhanceAmount * (bMin - bMid);
    }
    for (i = 0; i < nSubPixels; i += 4) {
        data[i + 0] = remap(data[i + 0], rMin, rMax, rGoalMin, rGoalMax);
        data[i + 1] = remap(data[i + 1], gMin, gMax, gGoalMin, gGoalMax);
        data[i + 2] = remap(data[i + 2], bMin, bMax, bGoalMin, bGoalMax);
    }
};
Factory.addGetterSetter(Node, 'enhance', 0, getNumberValidator(), Factory.afterSetFilter);
