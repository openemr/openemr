import * as stream from "stream";

declare function insert(
  texts: string | string[],
  separator: string,
  type: "append" | "prepend"
): stream.Transform;

export function appendFile(
  filepath: string,
  separator?: string
): stream.Transform;
export function prependFile(
  filepath: string,
  separator?: string
): stream.Transform;
export function appendText(text: string): stream.Transform;
export function prependText(text: string): stream.Transform;
