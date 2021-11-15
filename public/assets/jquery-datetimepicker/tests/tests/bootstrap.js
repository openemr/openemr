var inputs = [];

var box = document.createElement('div');
document.body.appendChild(box);


var getInput = function () {
    var input = document.createElement('input');
    input.setAttribute('type', 'text');
    inputs.push(input);
    box.appendChild(input);
    return input;
};

var clear = function() {
    inputs.forEach(function (inp) {
        $(inp).datetimepicker('destroy');
        inp.parentNode && inp.parentNode.removeChild(inp)
    });
};

var PICKER = 'xdsoft_datetimepicker';

var simulateEvent = function (type, element, keyCodeArg, options) {
    if (!keyCodeArg) {
        keyCodeArg = 0;
    }

    if (element instanceof jQuery) {
        element = element[0];
    }

    var evt = (element.ownerDocument || document).createEvent('HTMLEvents')
    evt.initEvent(type, true, true);
    evt.keyCode = keyCodeArg;
    evt.which = keyCodeArg;

    if (options) {
        options(evt);
    }

    if (type.match(/^mouse/)) {
        ['pageX', 'pageY', 'clientX', 'clientY'].forEach(function (key) {
            if (evt[key] === undefined) {
                evt[key] = 0;
            }
        })
    }

    element.dispatchEvent(evt);
};

afterEach(clear);
var expect = chai.expect;
chai.config.includeStack = true