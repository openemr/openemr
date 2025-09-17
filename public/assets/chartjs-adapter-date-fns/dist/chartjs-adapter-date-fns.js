/*!
 * chartjs-adapter-date-fns v3.0.0
 * https://www.chartjs.org
 * (c) 2022 chartjs-adapter-date-fns Contributors
 * Released under the MIT license
 */
(function (global, factory) {
typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('chart.js'), require('date-fns')) :
typeof define === 'function' && define.amd ? define(['chart.js', 'date-fns'], factory) :
(global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.Chart, global.dateFns));
})(this, (function (chart_js, dateFns) { 'use strict';

const FORMATS = {
  datetime: 'MMM d, yyyy, h:mm:ss aaaa',
  millisecond: 'h:mm:ss.SSS aaaa',
  second: 'h:mm:ss aaaa',
  minute: 'h:mm aaaa',
  hour: 'ha',
  day: 'MMM d',
  week: 'PP',
  month: 'MMM yyyy',
  quarter: 'qqq - yyyy',
  year: 'yyyy'
};

chart_js._adapters._date.override({
  _id: 'date-fns', // DEBUG

  formats: function() {
    return FORMATS;
  },

  parse: function(value, fmt) {
    if (value === null || typeof value === 'undefined') {
      return null;
    }
    const type = typeof value;
    if (type === 'number' || value instanceof Date) {
      value = dateFns.toDate(value);
    } else if (type === 'string') {
      if (typeof fmt === 'string') {
        value = dateFns.parse(value, fmt, new Date(), this.options);
      } else {
        value = dateFns.parseISO(value, this.options);
      }
    }
    return dateFns.isValid(value) ? value.getTime() : null;
  },

  format: function(time, fmt) {
    return dateFns.format(time, fmt, this.options);
  },

  add: function(time, amount, unit) {
    switch (unit) {
    case 'millisecond': return dateFns.addMilliseconds(time, amount);
    case 'second': return dateFns.addSeconds(time, amount);
    case 'minute': return dateFns.addMinutes(time, amount);
    case 'hour': return dateFns.addHours(time, amount);
    case 'day': return dateFns.addDays(time, amount);
    case 'week': return dateFns.addWeeks(time, amount);
    case 'month': return dateFns.addMonths(time, amount);
    case 'quarter': return dateFns.addQuarters(time, amount);
    case 'year': return dateFns.addYears(time, amount);
    default: return time;
    }
  },

  diff: function(max, min, unit) {
    switch (unit) {
    case 'millisecond': return dateFns.differenceInMilliseconds(max, min);
    case 'second': return dateFns.differenceInSeconds(max, min);
    case 'minute': return dateFns.differenceInMinutes(max, min);
    case 'hour': return dateFns.differenceInHours(max, min);
    case 'day': return dateFns.differenceInDays(max, min);
    case 'week': return dateFns.differenceInWeeks(max, min);
    case 'month': return dateFns.differenceInMonths(max, min);
    case 'quarter': return dateFns.differenceInQuarters(max, min);
    case 'year': return dateFns.differenceInYears(max, min);
    default: return 0;
    }
  },

  startOf: function(time, unit, weekday) {
    switch (unit) {
    case 'second': return dateFns.startOfSecond(time);
    case 'minute': return dateFns.startOfMinute(time);
    case 'hour': return dateFns.startOfHour(time);
    case 'day': return dateFns.startOfDay(time);
    case 'week': return dateFns.startOfWeek(time);
    case 'isoWeek': return dateFns.startOfWeek(time, {weekStartsOn: +weekday});
    case 'month': return dateFns.startOfMonth(time);
    case 'quarter': return dateFns.startOfQuarter(time);
    case 'year': return dateFns.startOfYear(time);
    default: return time;
    }
  },

  endOf: function(time, unit) {
    switch (unit) {
    case 'second': return dateFns.endOfSecond(time);
    case 'minute': return dateFns.endOfMinute(time);
    case 'hour': return dateFns.endOfHour(time);
    case 'day': return dateFns.endOfDay(time);
    case 'week': return dateFns.endOfWeek(time);
    case 'month': return dateFns.endOfMonth(time);
    case 'quarter': return dateFns.endOfQuarter(time);
    case 'year': return dateFns.endOfYear(time);
    default: return time;
    }
  }
});

}));
