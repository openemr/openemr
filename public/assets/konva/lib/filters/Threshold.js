import { Factory } from '../Factory.js';
import { Node } from '../Node.js';
import { getNumberValidator } from '../Validators.js';
export const Threshold = function (imageData) {
    var level = this.threshold() * 255, data = imageData.data, len = data.length, i;
    for (i = 0; i < len; i += 1) {
        data[i] = data[i] < level ? 0 : 255;
    }
};
Factory.addGetterSetter(Node, 'threshold', 0.5, getNumberValidator(), Factory.afterSetFilter);
