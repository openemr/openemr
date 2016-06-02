/**
 * Created by amiel on 02/06/16.
 */


// Extend for date roll from  https://validatejs.org/#validators-datetime

// Before using it we must add the parse and format functions
// Here is a sample implementation using moment.js
validate.extend(validate.validators.datetime, {
    // The value is guaranteed not to be null or undefined but otherwise it
    // could be anything.
    parse: function(value, options) {
        return +moment.utc(value);
    },
    // Input is a unix timestamp
    format: function(value, options) {
        var format = options.dateOnly ? "YYYY-MM-DD" : "YYYY-MM-DD hh:mm:ss";
        return moment.utc(value).format(format);
    }
});


/*
*  Custom validator documentation - https://validatejs.org/#custom-validator
*/

/**
* validate that date is past date, recommended to put it after {date: {dateOnly: true}}
* you can specify the message option {onlyPast:{message:'text example'}}
*
*/
validate.validators.onlyPast = function(value, options, key, attributes) {

    // exit if options = false
    if(!options) return;

    var date =  new Date(value);
    var mls_date = date.getTime();
    if(isNaN(mls_date)) {
        return 'must be valid date';
    }

    var now = new Date().getTime();
    if(now < mls_date) {
       if(validate.isObject(options) && options.message != undefined) {
           return options.message;
       } else {
           return 'must be past date';
       }
    }
};







