import { Factory } from '../Factory.js';
import { Shape } from '../Shape.js';
import { Animation } from '../Animation.js';
import { getNumberValidator } from '../Validators.js';
import { _registerNode } from '../Global.js';
export class Sprite extends Shape {
    constructor(config) {
        super(config);
        this._updated = true;
        this.anim = new Animation(() => {
            var updated = this._updated;
            this._updated = false;
            return updated;
        });
        this.on('animationChange.konva', function () {
            this.frameIndex(0);
        });
        this.on('frameIndexChange.konva', function () {
            this._updated = true;
        });
        this.on('frameRateChange.konva', function () {
            if (!this.anim.isRunning()) {
                return;
            }
            clearInterval(this.interval);
            this._setInterval();
        });
    }
    _sceneFunc(context) {
        var anim = this.animation(), index = this.frameIndex(), ix4 = index * 4, set = this.animations()[anim], offsets = this.frameOffsets(), x = set[ix4 + 0], y = set[ix4 + 1], width = set[ix4 + 2], height = set[ix4 + 3], image = this.image();
        if (this.hasFill() || this.hasStroke()) {
            context.beginPath();
            context.rect(0, 0, width, height);
            context.closePath();
            context.fillStrokeShape(this);
        }
        if (image) {
            if (offsets) {
                var offset = offsets[anim], ix2 = index * 2;
                context.drawImage(image, x, y, width, height, offset[ix2 + 0], offset[ix2 + 1], width, height);
            }
            else {
                context.drawImage(image, x, y, width, height, 0, 0, width, height);
            }
        }
    }
    _hitFunc(context) {
        var anim = this.animation(), index = this.frameIndex(), ix4 = index * 4, set = this.animations()[anim], offsets = this.frameOffsets(), width = set[ix4 + 2], height = set[ix4 + 3];
        context.beginPath();
        if (offsets) {
            var offset = offsets[anim];
            var ix2 = index * 2;
            context.rect(offset[ix2 + 0], offset[ix2 + 1], width, height);
        }
        else {
            context.rect(0, 0, width, height);
        }
        context.closePath();
        context.fillShape(this);
    }
    _useBufferCanvas() {
        return super._useBufferCanvas(true);
    }
    _setInterval() {
        var that = this;
        this.interval = setInterval(function () {
            that._updateIndex();
        }, 1000 / this.frameRate());
    }
    start() {
        if (this.isRunning()) {
            return;
        }
        var layer = this.getLayer();
        this.anim.setLayers(layer);
        this._setInterval();
        this.anim.start();
    }
    stop() {
        this.anim.stop();
        clearInterval(this.interval);
    }
    isRunning() {
        return this.anim.isRunning();
    }
    _updateIndex() {
        var index = this.frameIndex(), animation = this.animation(), animations = this.animations(), anim = animations[animation], len = anim.length / 4;
        if (index < len - 1) {
            this.frameIndex(index + 1);
        }
        else {
            this.frameIndex(0);
        }
    }
}
Sprite.prototype.className = 'Sprite';
_registerNode(Sprite);
Factory.addGetterSetter(Sprite, 'animation');
Factory.addGetterSetter(Sprite, 'animations');
Factory.addGetterSetter(Sprite, 'frameOffsets');
Factory.addGetterSetter(Sprite, 'image');
Factory.addGetterSetter(Sprite, 'frameIndex', 0, getNumberValidator());
Factory.addGetterSetter(Sprite, 'frameRate', 17, getNumberValidator());
Factory.backCompat(Sprite, {
    index: 'frameIndex',
    getIndex: 'getFrameIndex',
    setIndex: 'setFrameIndex',
});
