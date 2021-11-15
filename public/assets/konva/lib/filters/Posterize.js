import { Factory } from '../Factory.js';
import { Node } from '../Node.js';
import { getNumberValidator } from '../Validators.js';
export const Posterize = function (imageData) {
    var levels = Math.round(this.levels() * 254) + 1, data = imageData.data, len = data.length, scale = 255 / levels, i;
    for (i = 0; i < len; i += 1) {
        data[i] = Math.floor(data[i] / scale) * scale;
    }
};
Factory.addGetterSetter(Node, 'levels', 0.5, getNumberValidator(), Factory.afterSetFilter);
