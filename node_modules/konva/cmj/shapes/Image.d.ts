import { Shape, ShapeConfig } from '../Shape';
import { GetSet, IRect } from '../types';
import { Context } from '../Context';
export interface ImageConfig extends ShapeConfig {
    image: CanvasImageSource | undefined;
    crop?: IRect;
}
export declare class Image extends Shape<ImageConfig> {
    constructor(attrs: ImageConfig);
    _setImageLoad(): void;
    _useBufferCanvas(): boolean;
    _sceneFunc(context: Context): void;
    _hitFunc(context: any): void;
    getWidth(): any;
    getHeight(): any;
    static fromURL(url: any, callback: any): void;
    image: GetSet<CanvasImageSource | undefined, this>;
    crop: GetSet<IRect, this>;
    cropX: GetSet<number, this>;
    cropY: GetSet<number, this>;
    cropWidth: GetSet<number, this>;
    cropHeight: GetSet<number, this>;
}
