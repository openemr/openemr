import { RenderingContext2D } from '../types';
import PolylineElement from './PolylineElement';
export default class PolygonElement extends PolylineElement {
    type: string;
    path(ctx: RenderingContext2D): import("..").BoundingBox;
}
//# sourceMappingURL=PolygonElement.d.ts.map