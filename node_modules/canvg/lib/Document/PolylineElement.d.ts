import { RenderingContext2D } from '../types';
import Point from '../Point';
import BoundingBox from '../BoundingBox';
import Document from './Document';
import PathElement, { Marker } from './PathElement';
export default class PolylineElement extends PathElement {
    type: string;
    protected readonly points: Point[];
    constructor(document: Document, node: HTMLElement, captureTextNodes?: boolean);
    path(ctx: RenderingContext2D): BoundingBox;
    getMarkers(): Marker[];
}
//# sourceMappingURL=PolylineElement.d.ts.map