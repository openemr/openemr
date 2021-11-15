import { RenderingContext2D } from '../types';
import BoundingBox from '../BoundingBox';
import PathElement from './PathElement';
export default class EllipseElement extends PathElement {
    type: string;
    path(ctx: RenderingContext2D): BoundingBox;
    getMarkers(): any;
}
//# sourceMappingURL=EllipseElement.d.ts.map