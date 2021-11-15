import { RenderingContext2D } from '../types';
import Point from '../Point';
import BoundingBox from '../BoundingBox';
import PathElement, { Marker } from './PathElement';
export default class LineElement extends PathElement {
    type: string;
    getPoints(): Point[];
    path(ctx: RenderingContext2D): BoundingBox;
    getMarkers(): Marker[];
}
//# sourceMappingURL=LineElement.d.ts.map