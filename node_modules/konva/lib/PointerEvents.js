import { Konva } from './Global.js';
const Captures = new Map();
const SUPPORT_POINTER_EVENTS = Konva._global['PointerEvent'] !== undefined;
export function getCapturedShape(pointerId) {
    return Captures.get(pointerId);
}
export function createEvent(evt) {
    return {
        evt,
        pointerId: evt.pointerId,
    };
}
export function hasPointerCapture(pointerId, shape) {
    return Captures.get(pointerId) === shape;
}
export function setPointerCapture(pointerId, shape) {
    releaseCapture(pointerId);
    const stage = shape.getStage();
    if (!stage)
        return;
    Captures.set(pointerId, shape);
    if (SUPPORT_POINTER_EVENTS) {
        shape._fire('gotpointercapture', createEvent(new PointerEvent('gotpointercapture')));
    }
}
export function releaseCapture(pointerId, target) {
    const shape = Captures.get(pointerId);
    if (!shape)
        return;
    const stage = shape.getStage();
    if (stage && stage.content) {
    }
    Captures.delete(pointerId);
    if (SUPPORT_POINTER_EVENTS) {
        shape._fire('lostpointercapture', createEvent(new PointerEvent('lostpointercapture')));
    }
}
