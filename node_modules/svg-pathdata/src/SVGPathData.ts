import { encodeSVGPath } from "./SVGPathDataEncoder";
import { SVGPathDataParser } from "./SVGPathDataParser";
import { SVGPathDataTransformer } from "./SVGPathDataTransformer";
import { TransformableSVG } from "./TransformableSVG";
import { SVGCommand } from "./types";

export class SVGPathData extends TransformableSVG {
  commands: SVGCommand[];
  constructor(content: string | SVGCommand[]) {
    super();
    if ("string" === typeof content) {
      this.commands = SVGPathData.parse(content);
    } else {
      this.commands = content;
    }
  }

  encode() {
    return SVGPathData.encode(this.commands);
  }

  getBounds() {
    const boundsTransform = SVGPathDataTransformer.CALCULATE_BOUNDS();

    this.transform(boundsTransform);
    return boundsTransform;
  }

  transform(
    transformFunction: (input: SVGCommand) => SVGCommand | SVGCommand[],
  ) {
    const newCommands = [];

    for (const command of this.commands) {
      const transformedCommand = transformFunction(command);

      if (Array.isArray(transformedCommand)) {
        newCommands.push(...transformedCommand);
      } else {
        newCommands.push(transformedCommand);
      }
    }
    this.commands = newCommands;
    return this;
  }

  static encode(commands: SVGCommand[]) {
    return encodeSVGPath(commands);
      }

  static parse(path: string) {
    const parser = new SVGPathDataParser();
    const commands: SVGCommand[] = [];
    parser.parse(path, commands);
    parser.finish(commands);
    return commands;
  }

  static readonly CLOSE_PATH: 1 = 1;
  static readonly MOVE_TO: 2 = 2;
  static readonly HORIZ_LINE_TO: 4 = 4;
  static readonly VERT_LINE_TO: 8 = 8;
  static readonly LINE_TO: 16 = 16;
  static readonly CURVE_TO: 32 = 32;
  static readonly SMOOTH_CURVE_TO: 64 = 64;
  static readonly QUAD_TO: 128 = 128;
  static readonly SMOOTH_QUAD_TO: 256 = 256;
  static readonly ARC: 512 = 512;
  static readonly LINE_COMMANDS = SVGPathData.LINE_TO | SVGPathData.HORIZ_LINE_TO | SVGPathData.VERT_LINE_TO;
  static readonly DRAWING_COMMANDS = SVGPathData.HORIZ_LINE_TO | SVGPathData.VERT_LINE_TO | SVGPathData.LINE_TO |
  SVGPathData.CURVE_TO | SVGPathData.SMOOTH_CURVE_TO | SVGPathData.QUAD_TO |
  SVGPathData.SMOOTH_QUAD_TO | SVGPathData.ARC;
}

export const COMMAND_ARG_COUNTS = {
    [SVGPathData.MOVE_TO]: 2,
    [SVGPathData.LINE_TO]: 2,
    [SVGPathData.HORIZ_LINE_TO]: 1,
    [SVGPathData.VERT_LINE_TO]: 1,
    [SVGPathData.CLOSE_PATH]: 0,
    [SVGPathData.QUAD_TO]: 4,
    [SVGPathData.SMOOTH_QUAD_TO]: 2,
    [SVGPathData.CURVE_TO]: 6,
    [SVGPathData.SMOOTH_CURVE_TO]: 4,
    [SVGPathData.ARC]: 7,
};

export {encodeSVGPath} from "./SVGPathDataEncoder";
export {SVGPathDataParser} from "./SVGPathDataParser";
export {SVGPathDataTransformer} from "./SVGPathDataTransformer";
