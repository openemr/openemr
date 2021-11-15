"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var assert_1 = require("assert");
var parser_1 = require("../../syntax/parser");
var paint_order_1 = require("../paint-order");
var paintOrderParse = function (value) { return paint_order_1.paintOrder.parse({}, parser_1.Parser.parseValues(value)); };
describe('property-descriptors', function () {
    describe('paint-order', function () {
        it('none', function () {
            return assert_1.deepStrictEqual(paintOrderParse('none'), [
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('EMPTY', function () {
            return assert_1.deepStrictEqual(paintOrderParse(''), [
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('other values', function () {
            return assert_1.deepStrictEqual(paintOrderParse('other values'), [
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('normal', function () {
            return assert_1.deepStrictEqual(paintOrderParse('normal'), [
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('stroke', function () {
            return assert_1.deepStrictEqual(paintOrderParse('stroke'), [
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('fill', function () {
            return assert_1.deepStrictEqual(paintOrderParse('fill'), [
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('markers', function () {
            return assert_1.deepStrictEqual(paintOrderParse('markers'), [
                paint_order_1.PAINT_ORDER_LAYER.MARKERS,
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.STROKE
            ]);
        });
        it('stroke fill', function () {
            return assert_1.deepStrictEqual(paintOrderParse('stroke fill'), [
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
        it('markers stroke', function () {
            return assert_1.deepStrictEqual(paintOrderParse('markers stroke'), [
                paint_order_1.PAINT_ORDER_LAYER.MARKERS,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.FILL
            ]);
        });
        it('markers stroke fill', function () {
            return assert_1.deepStrictEqual(paintOrderParse('markers stroke fill'), [
                paint_order_1.PAINT_ORDER_LAYER.MARKERS,
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.FILL
            ]);
        });
        it('stroke fill markers', function () {
            return assert_1.deepStrictEqual(paintOrderParse('stroke fill markers'), [
                paint_order_1.PAINT_ORDER_LAYER.STROKE,
                paint_order_1.PAINT_ORDER_LAYER.FILL,
                paint_order_1.PAINT_ORDER_LAYER.MARKERS
            ]);
        });
    });
});
//# sourceMappingURL=paint-order.js.map