import { Shape, ShapeConfig } from '../Shape';
import { GetSet } from '../types';
export declare function stringToArray(string: string): string[];
export interface TextConfig extends ShapeConfig {
    text?: string;
    fontFamily?: string;
    fontSize?: number;
    fontStyle?: string;
    fontVariant?: string;
    textDecoration?: string;
    align?: string;
    verticalAlign?: string;
    padding?: number;
    lineHeight?: number;
    letterSpacing?: number;
    wrap?: string;
    ellipsis?: boolean;
}
export declare class Text extends Shape<TextConfig> {
    textArr: Array<{
        text: string;
        width: number;
    }>;
    _partialText: string;
    _partialTextX: number;
    _partialTextY: number;
    textWidth: number;
    textHeight: number;
    constructor(config?: TextConfig);
    _sceneFunc(context: any): void;
    _hitFunc(context: any): void;
    setText(text: any): this;
    getWidth(): any;
    getHeight(): any;
    getTextWidth(): number;
    getTextHeight(): number;
    measureSize(text: any): {
        width: any;
        height: number;
    };
    _getContextFont(): string;
    _addTextLine(line: any): number;
    _getTextWidth(text: any): any;
    _setTextData(): void;
    getStrokeScaleEnabled(): boolean;
    fontFamily: GetSet<string, this>;
    fontSize: GetSet<number, this>;
    fontStyle: GetSet<string, this>;
    fontVariant: GetSet<string, this>;
    align: GetSet<string, this>;
    letterSpacing: GetSet<number, this>;
    verticalAlign: GetSet<string, this>;
    padding: GetSet<number, this>;
    lineHeight: GetSet<number, this>;
    textDecoration: GetSet<string, this>;
    text: GetSet<string, this>;
    wrap: GetSet<string, this>;
    ellipsis: GetSet<boolean, this>;
}
