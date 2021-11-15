import { RenderingContext2D } from '../types';
import BoundingBox from '../BoundingBox';
import PathElement from './PathElement';
export default class CircleElement extends PathElement {
    type: string;
    path(ctx: RenderingContext2D): BoundingBox;
    getMarkers(): any;
}
//# sourceMappingURL=CircleElement.d.ts.map