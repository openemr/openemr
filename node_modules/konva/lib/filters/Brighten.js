import { Factory } from '../Factory.js';
import { Node } from '../Node.js';
import { getNumberValidator } from '../Validators.js';
export const Brighten = function (imageData) {
    var brightness = this.brightness() * 255, data = imageData.data, len = data.length, i;
    for (i = 0; i < len; i += 4) {
        data[i] += brightness;
        data[i + 1] += brightness;
        data[i + 2] += brightness;
    }
};
Factory.addGetterSetter(Node, 'brightness', 0, getNumberValidator(), Factory.afterSetFilter);
