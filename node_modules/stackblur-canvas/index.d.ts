export class BlurStack {
  r: number;
  g: number;
  b: number;
  a: number;
  next: BlurStack;
}

export function image(
  img: HTMLImageElement | string,
  canvas: HTMLCanvasElement | string,
  radius: number,
  blurAlphaChannel?: boolean,
  useOffset?: boolean,
  skipStyles?: boolean,
): void;

export function canvasRGBA(
  canvas: HTMLCanvasElement,
  topX: number,
  topY: number,
  width: number,
  height: number,
  radius: number
): void;

export function canvasRGB(
  canvas: HTMLCanvasElement,
  topX: number,
  topY: number,
  width: number,
  height: number,
  radius: number
): void;

export function imageDataRGBA(
  data: ImageData,
  topX: number,
  topY: number,
  width: number,
  height: number,
  radius: number
): ImageData;

export function imageDataRGB(
  data: ImageData,
  topX: number,
  topY: number,
  width: number,
  height: number,
  radius: number
): ImageData;
