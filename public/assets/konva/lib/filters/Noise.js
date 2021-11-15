import { Factory } from '../Factory.js';
import { Node } from '../Node.js';
import { getNumberValidator } from '../Validators.js';
export const Noise = function (imageData) {
    var amount = this.noise() * 255, data = imageData.data, nPixels = data.length, half = amount / 2, i;
    for (i = 0; i < nPixels; i += 4) {
        data[i + 0] += half - 2 * half * Math.random();
        data[i + 1] += half - 2 * half * Math.random();
        data[i + 2] += half - 2 * half * Math.random();
    }
};
Factory.addGetterSetter(Node, 'noise', 0.2, getNumberValidator(), Factory.afterSetFilter);
