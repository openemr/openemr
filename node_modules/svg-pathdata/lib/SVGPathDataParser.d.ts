import { TransformableSVG } from "./TransformableSVG";
import { SVGCommand, TransformFunction } from "./types";
export declare class SVGPathDataParser extends TransformableSVG {
    private curNumber;
    private curCommandType;
    private curCommandRelative;
    private canParseCommandOrComma;
    private curNumberHasExp;
    private curNumberHasExpDigits;
    private curNumberHasDecimal;
    private curArgs;
    constructor();
    finish(commands?: SVGCommand[]): SVGCommand[];
    parse(str: string, commands?: SVGCommand[]): SVGCommand[];
    /**
     * Return a wrapper around this parser which applies the transformation on parsed commands.
     */
    transform(transform: TransformFunction): this;
}
