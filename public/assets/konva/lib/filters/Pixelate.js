import { Factory } from '../Factory.js';
import { Util } from '../Util.js';
import { Node } from '../Node.js';
import { getNumberValidator } from '../Validators.js';
export const Pixelate = function (imageData) {
    var pixelSize = Math.ceil(this.pixelSize()), width = imageData.width, height = imageData.height, x, y, i, red, green, blue, alpha, nBinsX = Math.ceil(width / pixelSize), nBinsY = Math.ceil(height / pixelSize), xBinStart, xBinEnd, yBinStart, yBinEnd, xBin, yBin, pixelsInBin, data = imageData.data;
    if (pixelSize <= 0) {
        Util.error('pixelSize value can not be <= 0');
        return;
    }
    for (xBin = 0; xBin < nBinsX; xBin += 1) {
        for (yBin = 0; yBin < nBinsY; yBin += 1) {
            red = 0;
            green = 0;
            blue = 0;
            alpha = 0;
            xBinStart = xBin * pixelSize;
            xBinEnd = xBinStart + pixelSize;
            yBinStart = yBin * pixelSize;
            yBinEnd = yBinStart + pixelSize;
            pixelsInBin = 0;
            for (x = xBinStart; x < xBinEnd; x += 1) {
                if (x >= width) {
                    continue;
                }
                for (y = yBinStart; y < yBinEnd; y += 1) {
                    if (y >= height) {
                        continue;
                    }
                    i = (width * y + x) * 4;
                    red += data[i + 0];
                    green += data[i + 1];
                    blue += data[i + 2];
                    alpha += data[i + 3];
                    pixelsInBin += 1;
                }
            }
            red = red / pixelsInBin;
            green = green / pixelsInBin;
            blue = blue / pixelsInBin;
            alpha = alpha / pixelsInBin;
            for (x = xBinStart; x < xBinEnd; x += 1) {
                if (x >= width) {
                    continue;
                }
                for (y = yBinStart; y < yBinEnd; y += 1) {
                    if (y >= height) {
                        continue;
                    }
                    i = (width * y + x) * 4;
                    data[i + 0] = red;
                    data[i + 1] = green;
                    data[i + 2] = blue;
                    data[i + 3] = alpha;
                }
            }
        }
    }
};
Factory.addGetterSetter(Node, 'pixelSize', 8, getNumberValidator(), Factory.afterSetFilter);
