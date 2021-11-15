import { RenderingContext2D } from '../types';
/**
 * Wrap rendering context to log every action.
 * @param ctx - Rendering context.
 * @returns Proxy logger.
 */
export declare function ctxLogger(ctx: RenderingContext2D): RenderingContext2D;
/**
 * Draw point.
 * @param ctx - Rendering context.
 * @param x - Point x.
 * @param y - Point y
 * @param radius - Point radius.
 */
export declare function point(ctx: RenderingContext2D, x?: number, y?: number, radius?: number): void;
/**
 * Draw triangle to vizualize angle.
 * @param ctx - Rendering context.
 * @param x - Angle x.
 * @param y - Angle y.
 * @param size - Triangle size.
 */
export declare function angle(ctx: RenderingContext2D, x?: number, y?: number, size?: number): void;
/**
 * Draw triangle to vizualize angle.
 * @param ctx - Rendering context.
 * @param x - Angle x.
 * @param y - Angle y.
 * @param width
 * @param height
 */
export declare function box(ctx: RenderingContext2D, x: number, y: number, width: number, height: number): void;
//# sourceMappingURL=debug.d.ts.map