import { RenderingContext2D } from '../types';
import Point from '../Point';
import Element from './Element';
export default class MarkerElement extends Element {
    type: string;
    render(ctx: RenderingContext2D, point?: Point, angle?: number): void;
}
//# sourceMappingURL=MarkerElement.d.ts.map