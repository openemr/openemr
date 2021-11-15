import { Shape, ShapeConfig } from '../Shape';
import { GetSet, Vector2d } from '../types';
export interface TextPathConfig extends ShapeConfig {
    text?: string;
    data?: string;
    fontFamily?: string;
    fontSize?: number;
    fontStyle?: string;
    letterSpacing?: number;
}
export declare class TextPath extends Shape<TextPathConfig> {
    dummyCanvas: HTMLCanvasElement;
    dataArray: any[];
    glyphInfo: Array<{
        transposeX: number;
        transposeY: number;
        text: string;
        rotation: number;
        p0: Vector2d;
        p1: Vector2d;
    }>;
    partialText: string;
    textWidth: number;
    textHeight: number;
    constructor(config?: TextPathConfig);
    _sceneFunc(context: any): void;
    _hitFunc(context: any): void;
    getTextWidth(): number;
    getTextHeight(): number;
    setText(text: any): any;
    _getContextFont(): any;
    _getTextSize(text: any): {
        width: number;
        height: number;
    };
    _setTextData(): void;
    getSelfRect(): {
        x: number;
        y: number;
        width: number;
        height: number;
    };
    fontFamily: GetSet<string, this>;
    fontSize: GetSet<number, this>;
    fontStyle: GetSet<string, this>;
    fontVariant: GetSet<string, this>;
    align: GetSet<string, this>;
    letterSpacing: GetSet<number, this>;
    text: GetSet<string, this>;
    data: GetSet<string, this>;
    kerningFunc: GetSet<(leftChar: string, rightChar: string) => number, this>;
    textBaseline: GetSet<string, this>;
    textDecoration: GetSet<string, this>;
}
