import { RenderingContext2D } from '../types';
import BoundingBox from '../BoundingBox';
import RenderedElement from './RenderedElement';
export default class GElement extends RenderedElement {
    type: string;
    getBoundingBox(ctx: RenderingContext2D): BoundingBox;
}
//# sourceMappingURL=GElement.d.ts.map