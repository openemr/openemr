"use strict";

var moment = require('moment');

var dateToModel = exports.dateToModel = (function () {
    var precisions = ['year', 'month', 'day'];

    return function (d) {
        d = d.replace(/-/g, '');
        var m = moment.utc(d, 'YYYYMMDD');
        var dl = d.length;
        dl = (dl > 8) ? 8 : dl;
        var precisionIndex = dl / 2 - 2;
        var precision = precisions[precisionIndex];
        if (m.isValid() && precision) {
            return {
                date: m.toISOString(),
                precision: precision
            };
        } else {
            return null;
        }
    };
})();

exports.dateTimeToModel = function (d) {
    if (d.indexOf('T') > 0) {
        return {
            date: d,
            precision: 'second'
        };
    } else {
        return dateToModel(d);
    }
};

var modelToDateTime = exports.modelToDateTime = (function () {
    var precisionBasedFormatter = {
        year: function (t) {
            return moment(t, 'YYYY').format('YYYY');
        },
        month: function (t) {
            return moment(t, 'YYYYMM').format('YYYY-MM');
        },
        day: function (t) {
            return moment(t, 'YYYYMMDD').format('YYYY-MM-DD');
        }
    };

    return function (dt) {
        var f = precisionBasedFormatter[dt.precision];
        if (f) {
            return f(dt.date);
        } else {
            return dt.date;
        }
    };
})();

exports.modelToDate = function (dt) {
    var validPrecisions = {
        'day': true,
        'month': true,
        'year': true
    };
    if (!validPrecisions[dt.precision]) {
        dt.precision = 'day';
    }
    return modelToDateTime(dt);
};
