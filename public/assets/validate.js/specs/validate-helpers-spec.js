describe("validate", function() {
  describe('extend', function() {
    it("extends the first argument with the remaining arguments", function() {
      var obj = {};
      validate.extend(obj, {foo: "foo"}, {bar: "bar"});
      expect(obj).toEqual({foo: "foo", bar: "bar"});
    });

    it("returns the first argument", function() {
      var obj = {};
      expect(validate.extend(obj)).toBe(obj);
    });

    it("extends with the seconds argument first", function() {
      var actual = validate.extend({}, {foo: "foo"}, {foo: "bar"});
      expect(actual).toEqual({foo: "bar"});
    });
  });

  describe('result', function() {
    it("returns the first argument if it's not a function", function() {
      var obj = {};
      expect(validate.result(obj)).toBe(obj);
    });

    it("calls the argument if it's a function and returns the result", function() {
      var obj = jasmine.createSpy().and.returnValue("some return value");
      expect(validate.result(obj)).toEqual("some return value");
    });

    it("accepts additional arguments as arguments to the function", function() {
      var obj = jasmine.createSpy();
      validate.result(obj, "foo", "bar", "baz");
      expect(obj).toHaveBeenCalledWith("foo", "bar", "baz");
    });
  });

  describe('isNumber', function() {
    it("returns true for numbers", function() {
      expect(validate.isNumber(0)).toBe(true);
      expect(validate.isNumber(1)).toBe(true);
      expect(validate.isNumber(Math.PI)).toBe(true);
    });

    it("returns false for non numbers", function() {
      expect(validate.isNumber(null)).toBe(false);
      expect(validate.isNumber(true)).toBe(false);
      expect(validate.isNumber("1")).toBe(false);
    });
  });

  describe('isInteger', function() {
    it("returns true for integers", function() {
      expect(validate.isInteger(0)).toBe(true);
      expect(validate.isInteger(1)).toBe(true);
    });

    it("returns false for floats and other types ", function() {
      expect(validate.isInteger(Math.PI)).toBe(false);
      expect(validate.isInteger(null)).toBe(false);
      expect(validate.isInteger("1")).toBe(false);
    });
  });

  describe('isBoolean', function() {
    it("returns true for booleans", function() {
      expect(validate.isBoolean(true)).toBe(true);
      expect(validate.isBoolean(false)).toBe(true);
    });

    it("returns false for non booleans ", function() {
      expect(validate.isBoolean(null)).toBe(false);
      expect(validate.isBoolean({})).toBe(false);
      expect(validate.isBoolean({foo: "bar"})).toBe(false);
      expect(validate.isBoolean([])).toBe(false);
      expect(validate.isBoolean("")).toBe(false);
      expect(validate.isBoolean(function() {})).toBe(false);
    });
  });

  describe('isObject', function() {
    it("returns true for objects", function() {
      expect(validate.isObject({})).toBe(true);
      expect(validate.isObject({foo: "bar"})).toBe(true);
      expect(validate.isObject([])).toBe(true);
      expect(validate.isObject(function() {})).toBe(true);
    });

    it("returns false for non objects", function() {
      expect(validate.isObject(null)).toBe(false);
      expect(validate.isObject(1)).toBe(false);
      expect(validate.isObject("")).toBe(false);
      expect(validate.isObject(false)).toBe(false);
    });
  });

  describe('isDefined', function() {
    it("returns false for null and undefined", function() {
      expect(validate.isDefined(null)).toBe(false);
      expect(validate.isDefined(undefined)).toBe(false);
    });

    it("returns true for other values", function() {
      expect(validate.isDefined(true)).toBe(true);
      expect(validate.isDefined(0)).toBe(true);
      expect(validate.isDefined("")).toBe(true);
    });
  });

  describe("isPromise", function() {
    it("returns false for null and undefined", function() {
      expect(validate.isPromise(null)).toBe(false);
      expect(validate.isPromise(undefined)).toBe(false);
    });

    it("returns false for objects", function() {
      expect(validate.isPromise({})).toBe(false);
    });

    it("returns true for objects with a then function", function() {
      expect(validate.isPromise({then: "that"})).toBe(false);
      expect(validate.isPromise({then: function() {}})).toBe(true);
    });
  });

  describe('format', function() {
    it("replaces %{...} with the correct value", function() {
      var actual = validate.format("Foo is %{foo}, bar is %{bar}", {
        foo: "foo",
        bar: "bar"
      });
      expect(actual).toEqual("Foo is foo, bar is bar");
    });

    it("can replace the same value multiple times", function() {
      var actual = validate.format("%{foo} %{foo}", {foo: "foo"});
      expect(actual).toEqual("foo foo");
    });

    it("supports escaping %", function() {
      var actual = validate.format("Foo is %%{foo}", {foo: "foo"});
      expect(actual).toEqual("Foo is %{foo}");
    });

    it("handles non strings as the message", function() {
      var obj = {foo: "bar"};
      expect(validate.format(obj, {attr: "value"})).toBe(obj);
    });
  });

  describe("stringifyValue", function() {
    it("simply calls validate.prettify", function() {
      spyOn(validate, "prettify").and.returnValue("foobar");
      expect(validate.stringifyValue("barfoo")).toEqual("foobar");
      expect(validate.prettify).toHaveBeenCalledWith("barfoo");
    });

    it("calls custom prettify from options", function() {
      var options = {prettify: function() {}};
      spyOn(options, "prettify").and.returnValue("foobar");
      spyOn(validate, "prettify").and.returnValue("baz");
      expect(validate.stringifyValue("barfoo", options)).toEqual("foobar");
      expect(options.prettify).toHaveBeenCalledWith("barfoo");
      expect(validate.prettify).not.toHaveBeenCalled();
    });
  });

  describe('prettify', function() {
    it("lower cases the entire string", function() {
      expect(validate.prettify("FOO BAR")).toEqual("foo bar");
    });

    it("replaces underscores with spaces", function() {
      expect(validate.prettify("foo_bar_baz")).toEqual("foo bar baz");
    });

    it("replaces dashes with spaces", function() {
      expect(validate.prettify("foo-bar-baz")).toEqual("foo bar baz");
    });

    it("splits camel cased words", function() {
      expect(validate.prettify("fooBar")).toEqual("foo bar");
    });

    it("replaces periods with spaces if no space follows", function() {
      expect(validate.prettify("foo.bar.baz")).toEqual("foo bar baz");
      expect(validate.prettify("foo. bar")).toEqual("foo. bar");
      expect(validate.prettify("foo .bar")).toEqual("foo .bar");
      expect(validate.prettify("foo.bar.")).toEqual("foo bar.");
    });

    it("replaces backslashes with nothing", function() {
      expect(validate.prettify("foo\\.bar\\.baz")).toEqual("foo bar baz");
      expect(validate.prettify("foo\\\\.bar")).toEqual("foo bar");
    });

    it("calls toString on objects", function() {
      var object = {
          toString: function() { return "Custom string"; }
        };

      expect(validate.prettify(object)).toEqual("Custom string");
    });

    it("doesn't allow too many decimals", function() {
      expect(validate.prettify(4711)).toEqual("4711");
      expect(validate.prettify(4711.2)).toEqual("4711.2");
      expect(validate.prettify(4711.255555)).toEqual("4711.26");
    });

    it("handles arrays", function() {
      var array = ["foo", "bar_baz"];
      // It valls it recusively
      spyOn(validate, "prettify").and.callThrough();
      expect(validate.prettify(array)).toEqual("foo, bar baz");
      expect(validate.prettify).toHaveBeenCalledWith(array);
      expect(validate.prettify).toHaveBeenCalledWith("foo");
      expect(validate.prettify).toHaveBeenCalledWith("bar_baz");
    });
  });

  describe('isString', function() {
    it("returns true for strings", function() {
      expect(validate.isString("foobar")).toBe(true);
      expect(validate.isString("")).toBe(true);
    });

    it("returns false for non strings", function() {
      var obj = {toString: function() { return "foobar"; }};
      expect(validate.isString(obj)).toBe(false);
      expect(validate.isString(null)).toBe(false);
      expect(validate.isString(true)).toBe(false);
    });
  });

  describe('isArray', function() {
    var isArray = validate.isArray;

    it("returns true for arrays", function() {
      expect(isArray([])).toBe(true);
      expect(isArray([1])).toBe(true);
      expect(isArray([1, 2])).toBe(true);
    });

    it("returns false for non arrays", function() {
      expect(isArray({})).toBe(false);
      expect(isArray(null)).toBe(false);
      expect(isArray(1)).toBe(false);
      expect(isArray(true)).toBe(false);
    });
  });

  describe('isHash', function() {
    it("returns true for hashes", function() {
      expect(validate.isHash({})).toBe(true);
      expect(validate.isHash({foo: "bar"})).toBe(true);
    });

    it("returns false for non hashes", function() {
      expect(validate.isHash([])).toBe(false);
      expect(validate.isHash(function() {})).toBe(false);
      expect(validate.isHash(null)).toBe(false);
      expect(validate.isHash(1)).toBe(false);
      expect(validate.isHash("")).toBe(false);
      expect(validate.isHash(false)).toBe(false);
    });
  });

  describe('contains', function() {
    var contains = validate.contains;

    it("returns false when not passing in a target object", function() {
      expect(contains(null, "foo")).toBe(false);
      expect(contains(undefined, "foo")).toBe(false);
    });

    describe("arrays", function() {
      it("returns true if the value is in the specified array", function() {
        expect(contains(["foo", "bar", "baz"], "foo")).toBe(true);
        expect(contains(["foo", "bar", "baz"], "bar")).toBe(true);
        expect(contains(["foo", "bar", "baz"], "baz")).toBe(true);
      });

      it("returns false if the value is not in the specified array", function() {
        expect(contains(["foo", "bar", "baz"], "quux")).toBe(false);
        expect(contains(["foo", "bar", "baz"], false)).toBe(false);
        expect(contains(["foo", "bar", "baz"], 0)).toBe(false);
        expect(contains(["foo", "bar", "baz"], null)).toBe(false);
      });

      it("works with empty arrays", function() {
        expect(contains([], "foo")).toBe(false);
      });
    });

    describe("objects", function() {
      it("returns true if the value is a key in the object", function() {
        expect(contains({foo: false, bar: "bar"}, "foo")).toBe(true);
        expect(contains({foo: false, bar: "bar"}, "bar")).toBe(true);
      });

      it("returns false if the value is not a key in the object", function() {
        expect(contains({foo: false, bar: "bar"}, "quux")).toBe(false);
        expect(contains({foo: false, bar: "bar"}, null)).toBe(false);
        expect(contains({foo: false, bar: "bar"}, 1)).toBe(false);
        expect(contains({foo: false, bar: "bar"}, true)).toBe(false);
      });

      it("works with empty objects", function() {
        expect(contains({}, "foo")).toBe(false);
      });
    });
  });

  describe('capitalize', function() {
    var capitalize = validate.capitalize;

    it("captializes the first word", function() {
      expect(capitalize("foo")).toEqual("Foo");
      expect(capitalize("foo bar")).toEqual("Foo bar");
      expect(capitalize("foo bar baz")).toEqual("Foo bar baz");
    });

    it("returns the value for non strings", function() {
      var o = {foo: "bar"};
      expect(capitalize(o)).toEqual(o);
    });
  });

  describe("pruneEmptyErrors", function() {
    it("removes empty errors", function() {
      var input = [{
          attribute: "name",
          value: "test",
          validator: "fail",
          options: {"someOption": "someValue"},
          error: "foobar"
        }, {
          attribute: "name",
          value: "test",
          validator: "fail2",
          options: true,
          error: ["foo", "bar"]
        }, {
          attribute: "name",
          value: "test",
          validator: "pass",
          options: true,
          error: null
        }, {
          attribute: "name",
          value: "test",
          validator: "pass",
          options: true,
          error: []
        }, {
          attribute: "name",
          value: "test",
          validator: "pass",
          options: true,
          error: ""
      }];

      expect(validate.pruneEmptyErrors(input)).toEqual([{
          attribute: "name",
          value: "test",
          validator: "fail",
          options: {"someOption": "someValue"},
          error: "foobar"
        }, {
          attribute: "name",
          value: "test",
          validator: "fail2",
          options: true,
          error: ["foo", "bar"]
      }]);
    });
  });

  describe("expandMultipleErrors", function() {
    it("expands error arrays to multiple entries", function() {
      var input = [{
          attribute: "name",
          value: "test",
          validator: "fail",
          options: {"someOption": "someValue"},
          error: "foobar"
        }, {
          attribute: "name",
          value: "test",
          validator: "fail2",
          options: true,
          error: ["foo", "bar"]
      }];

      expect(validate.expandMultipleErrors(input)).toEqual([{
          attribute: "name",
          value: "test",
          validator: "fail",
          options: {"someOption": "someValue"},
          error: "foobar"
        }, {
          attribute: "name",
          value: "test",
          validator: "fail2",
          options: true,
          error: "foo"
        }, {
          attribute: "name",
          value: "test",
          validator: "fail2",
          options: true,
          error: "bar"
      }]);
    });
  });

  describe('convertErrorMessages', function() {
    var convertErrorMessages = validate.convertErrorMessages;

    it("prettifies and prepends the attribute", function() {
      var errors = [{
        attribute: "foo",
        error: "can't be blank",
        someOtherProperty: "someOtherProperty",
        value: "foobar"
      }, {
        attribute: "foo_bar",
        error: "has some other problem",
        value: "foobar"
      }];

      expect(convertErrorMessages(errors)).toEqual([{
        attribute: "foo",
        error: "Foo can't be blank",
        someOtherProperty: "someOtherProperty",
        value: "foobar"
      }, {
        attribute: "foo_bar",
        error: "Foo bar has some other problem",
        value: "foobar"
      }]);
    });

    it("calls custom prettify from options", function() {
      var errors = [{
          attribute: "foo",
          error: "can't be blank"
        }]
        , options = {prettify: function() {}};
      spyOn(options, "prettify").and.returnValue("foobar");
      spyOn(validate, "prettify").and.returnValue("baz");
      expect(convertErrorMessages(errors, options)[0].error).toEqual("Foobar can't be blank");
      expect(options.prettify).toHaveBeenCalledWith("foo");
      expect(validate.prettify).not.toHaveBeenCalled();
    });

    it("doesn't modify the input", function() {
      var errors = [{
        attribute: "foo",
        error: "can't be blank",
        value: "foobar"
      }];
      convertErrorMessages(errors);
      expect(errors).toEqual([{
        attribute: "foo",
        error: "can't be blank",
        value: "foobar"
      }]);
    });

    it("returns an empty object if there are no errors", function() {
      expect(convertErrorMessages([])).toEqual([]);
    });

    it("doesn't prepend the attribute name if the message starts with a ^", function() {
      var errors = [{
        attribute: "foo",
        error: "^Please don't do that",
        value: "foobar"
      }];
      expect(convertErrorMessages(errors)).toEqual([{
        attribute: "foo",
        error: "Please don't do that",
        value: "foobar"
      }]);
    });

    it("handles an escaped ^", function() {
      var errors = [{
        attribute: "foo",
        error: "\\^ has a weird message^\\^",
        value: "foobar"
      }];
      expect(convertErrorMessages(errors)).toEqual([{
        attribute: "foo",
        error: "Foo ^ has a weird message^^",
        value: "foobar"
      }]);
    });

    it("doesn't prepend the attribute name if fullMessages is false", function() {
      var errors = [{
        attribute: "foo",
        error: "Please don't do that",
        value: "foobar"
      }];
      expect(convertErrorMessages(errors, {fullMessages: false})).toEqual([{
        attribute: "foo",
        error: "Please don't do that",
        value: "foobar"
      }]);
    });

    it("still strips the leading ^ even if fullmessages if false", function() {
      var errors = [{
        attribute: "foo",
        error: "\\^ has a weird message^\\^",
        value: "foobar"
      }];
      expect(convertErrorMessages(errors, {fullMessages: false})).toEqual([{
        attribute: "foo",
        error: "^ has a weird message^^",
        value: "foobar"
      }]);
    });

    it("allow messages to contain %{value}", function() {
      var errors = [{
        attribute: "foo",
        error: "foo %{value}",
        someOtherProperty: "someOtherProperty",
        value: "foobar"
      }];

      spyOn(validate, "format").and.callThrough();
      spyOn(validate, "stringifyValue").and.returnValue("barfoo");

      expect(convertErrorMessages(errors)).toEqual([{
        attribute: "foo",
        error: "Foo foo barfoo",
        someOtherProperty: "someOtherProperty",
        value: "foobar"
      }]);
      expect(validate.format).toHaveBeenCalledWith("Foo foo %{value}", {
        value: "barfoo"
      });
    });
  });

  describe("groupErrorsByAttribute", function() {
    it("groups errors by attribute", function() {
      var input = [{
          attribute: "foo",
          someKey: "someValue"
        }, {
          attribute: "bar",
          someOtherKey: "someOtherValue"
        }, {
          attribute: "foo",
          someThirdKey: "someThirdValue"
      }];

      expect(validate.groupErrorsByAttribute(input)).toEqual({
        foo: [{
          attribute: "foo",
          someKey: "someValue"
        }, {
          attribute: "foo",
          someThirdKey: "someThirdValue"
        }],
        bar: [{
          attribute: "bar",
          someOtherKey: "someOtherValue"
        }]
      });
    });
  });

  describe("processValidationResults", function() {
    var pvr = validate.processValidationResults;

    it("allows the validator to return a string", function() {
      var results = [{attribute: "name", error: "foobar"}];
      expect(pvr(results, {})).toEqual({name: ["Name foobar"]});
    });

    it("allows the validator to return an array", function() {
      var results = [{attribute: "name", error: ["foo", "bar"]}];
      expect(pvr(results, {})).toEqual({name: ["Name foo", "Name bar"]});
    });

    it("supports multiple entries for the same attribute", function() {
      var results = [
        {attribute: "name", error: ["foo", "bar"]},
        {attribute: "name", error: "baz"}
      ];
      expect(pvr(results, {})).toEqual({
        name: ["Name foo", "Name bar", "Name baz"]
      });
    });

    it("the correct functions", function() {
      spyOn(validate, "pruneEmptyErrors").and.returnValue("pruned");
      spyOn(validate, "expandMultipleErrors").and.returnValue("expanded");
      spyOn(validate, "convertErrorMessages").and.returnValue([]);
      var options = {option: "value"};
      expect(pvr("input", options)).toBe(undefined);

      expect(validate.pruneEmptyErrors)
        .toHaveBeenCalledWith("input", options);
      expect(validate.expandMultipleErrors)
        .toHaveBeenCalledWith("pruned", options);
      expect(validate.convertErrorMessages)
        .toHaveBeenCalledWith("expanded", options);
    });

    it("throws an error for unknown formats", function() {
      expect(function() {
        pvr([], {format: "foobar"});
      }).toThrow(new Error("Unknown format foobar"));
    });
  });

  describe("flattenErrorsToArray", function() {
    it("flattens an array of errors objects to just the messages", function() {
      var input = [{
        error: "error 1",
        someKey: "someValue"
      }, {
        error: "error 2"
      }];
      expect(validate.flattenErrorsToArray(input)).toEqual([
        "error 1",
        "error 2"
      ]);
    });
  });

  describe('isFunction', function() {
    var isFunction = validate.isFunction;

    it("returns true for functions", function() {
      expect(isFunction(function() {})).toBe(true);
    });

    it("returns false for non functions", function() {
      expect(isFunction({})).toBe(false);
      expect(isFunction(null)).toBe(false);
      expect(isFunction(1)).toBe(false);
      expect(isFunction(true)).toBe(false);
    });
  });

  describe('exposeModule', function() {
    var exposeModule = validate.exposeModule;

    it("supports simple browser inclusion", function() {
      var root = {};
      exposeModule(validate, root, null, null, null);
      expect(root.validate).toBe(validate);
    });

    it("supports AMD", function() {
      var root = {}
        , define = function(deps, func) {
          expect(deps).toEqual([]);
          expect(func()).toBe(validate);
        };

      var defineSpy = jasmine.createSpy('define').and.callFake(define);

      exposeModule(validate, root, null, null, defineSpy);
      expect(defineSpy).not.toHaveBeenCalled();

      defineSpy.amd = true;

      exposeModule(validate, root, null, null, defineSpy);

      expect(defineSpy).toHaveBeenCalled();

      // It should still expose it through the root
      expect(root.validate).toBe(validate);
    });

    it("supports exports", function() {
      var root = {}
        , exports = {};

      exposeModule(validate, root, exports, null, null);

      expect(root).toEqual({});
      expect(exports.validate).toBe(validate);
    });

    it("supports module.exports", function() {
      var root = {}
        , exports = {}
        , module = {exports: true};

      exposeModule(validate, root, exports, module, null);

      expect(root).toEqual({});
      expect(module.exports).toEqual(validate);
      expect(module.exports.validate).toEqual(validate);
    });
  });

  describe("warn", function() {
    var console = window.console;

    beforeEach(function() {
      window.console = undefined;
    });

    afterEach(function() {
      window.console = console;
    });

    it("does nothing if the console isn't defined", function() {
      expect(function() { validate.warn("Msg"); }).not.toThrow();
    });

    it("calls console.warn if defined", function() {
      window.console = {
        warn: jasmine.createSpy("warn")
      };
      validate.warn("Msg");
      expect(window.console.warn).toHaveBeenCalledWith("[validate.js] Msg");
    });
  });

  describe("error", function() {
    var console = window.console;

    beforeEach(function() { window.console = undefined; });
    afterEach(function() { window.console = console; });

    it("does nothing if the console isn't defined", function() {
      validate.error("Msg");
      expect(function() { validate.error("Msg"); }).not.toThrow();
    });

    it("calls console.error if defined", function() {
      window.console = {
        error: jasmine.createSpy("error")
      };
      validate.error("Msg");
      expect(window.console.error).toHaveBeenCalledWith("[validate.js] Msg");
    });
  });

  describe("getDeepObjectValue", function() {
    it("supports multiple keys separated using a period", function() {
      var attributes = {
        foo: {
          bar: {
            baz: 3
          }
        }
      };

      expect(validate.getDeepObjectValue(attributes, "foo.bar.baz")).toBe(3);
    });

    it("returns undefined if any key is not found", function() {
      var attributes = {
        foo: {
          bar: {
            baz: 3
          }
        }
      };

      expect(validate.getDeepObjectValue(attributes, "bar.foo")).toBe(undefined);
      expect(validate.getDeepObjectValue(attributes, "foo.baz")).toBe(undefined);
    });

    it("handles the object being non objects", function() {
      expect(validate.getDeepObjectValue(null, "foo")).toBe(undefined);
      expect(validate.getDeepObjectValue("foo", "foo")).toBe(undefined);
      expect(validate.getDeepObjectValue(3, "foo")).toBe(undefined);
      expect(validate.getDeepObjectValue([], "foo")).toBe(undefined);
      expect(validate.getDeepObjectValue(true, "foo")).toBe(undefined);
    });

    it("handles the keypath being non strings", function() {
      expect(validate.getDeepObjectValue({}, null)).toBe(undefined);
      expect(validate.getDeepObjectValue({}, 3)).toBe(undefined);
      expect(validate.getDeepObjectValue({}, {})).toBe(undefined);
      expect(validate.getDeepObjectValue({}, [])).toBe(undefined);
      expect(validate.getDeepObjectValue({}, true)).toBe(undefined);
    });

    it("handles escapes properly", function() {
      var attributes = {
        "foo.bar": {
          baz: 3
        },
        "foo\\": {
          bar: {
            baz: 5
          }
        }
      };

      expect(validate.getDeepObjectValue(attributes, "foo.bar.baz"))
        .toBe(undefined);

      expect(validate.getDeepObjectValue(attributes, "foo\\.bar.baz"))
        .toBe(3);

      expect(validate.getDeepObjectValue(attributes, "foo\\\\.bar.baz"))
        .toBe(5);

      expect(validate.getDeepObjectValue(attributes, "\\foo\\\\.bar.baz"))
        .toBe(5);
    });
  });

  describe("isDate", function() {
    it("returns true for dates", function() {
      expect(validate.isDate(new Date())).toBe(true);
    });

    it("returns false for non dates", function() {
      expect(validate.isDate(Date.now())).toBe(false);
      expect(validate.isDate({})).toBe(false);
    });
  });

  describe("isEmpty", function() {
    it("considers null and undefined values empty", function() {
      expect(validate.isEmpty(null)).toBe(true);
      expect(validate.isEmpty(undefined)).toBe(true);
    });

    it("considers functions non empty", function() {
      expect(validate.isEmpty(function(){})).toBe(false);
    });

    it("considers whitespace only strings empty", function() {
      expect(validate.isEmpty("")).toBe(true);
      expect(validate.isEmpty(" ")).toBe(true);
      expect(validate.isEmpty("         ")).toBe(true);
      expect(validate.isEmpty("foo")).toBe(false);
    });

    it("considers empty arrays empty", function() {
      expect(validate.isEmpty([])).toBe(true);
      expect(validate.isEmpty([1])).toBe(false);
    });

    it("considers empty objects empty", function() {
      expect(validate.isEmpty({})).toBe(true);
      expect(validate.isEmpty({foo: "bar"})).toBe(false);
    });

    it("considers false and 0 non empty", function() {
      expect(validate.isEmpty(false)).toBe(false);
      expect(validate.isEmpty(0)).toBe(false);
    });

    it("considers date non empty", function() {
      spyOn(validate, "isDate").and.callThrough();
      expect(validate.isEmpty(new Date())).toBe(false);
      expect(validate.isDate).toHaveBeenCalled();
    });
  });

  describe("collectFormValues", function() {
    it("handles empty input", function() {
      expect(validate.collectFormValues()).toEqual({});
    });

    it("handles simple forms", function() {
      var form = document.createElement("form");
      form.innerHTML = '' +
        '<input type="text" name="text" value="example text">' +
        '<input type="text" name="empty-text">' +
        '<input type="email" name="email" value="example@email.com">' +
        '<input type="password" name="password" value="password!">' +
        '<input type="checkbox" name="selected-checkbox" value="checkbox" checked>' +
        '<input type="checkbox" name="deselected-checkbox" value="checkbox">' +
        '<input type="date" name="date" value="2015-03-08">' +
        '<input type="hidden" name="hidden" value="hidden">' +
        '<input type="number" name="number" value="4711">' +
        '<input type="url" name="url" value="http://validatejs.org">' +
        '<input type="radio" name="single-checked-radio" value="radio" checked>' +
        '<input type="radio" name="single-unchecked-radio" value="radio">' +
        '<radiogroup>' +
        '  <input type="radio" name="checked-radio" value="radio1">' +
        '  <input type="radio" name="checked-radio" value="radio2" checked>' +
        '  <input type="radio" name="checked-radio" value="radio3">' +
        '</radiogroup>' +
        '<radiogroup>' +
        '  <input type="radio" name="unchecked-radio" value="radio1">' +
        '  <input type="radio" name="unchecked-radio" value="radio2">' +
        '  <input type="radio" name="unchecked-radio" value="radio3">' +
        '</radiogroup>' +
        '<select name="selected-dropdown">' +
        '  <option>' +
        '  <option value="option1">' +
        '  <option value="option2" selected>' +
        '</select>' +
        '<select name="unselected-dropdown">' +
        '  <option>' +
        '  <option value="option1">' +
        '  <option value="option2">' +
        '</select>' +
        '<textarea name="textarea-ignored" data-ignored>the textarea</textarea>'+
        '<textarea name="textarea">the textarea</textarea>';

      expect(validate.collectFormValues(form)).toEqual({
        text: "example text",
        "empty-text": null,
        email: "example@email.com",
        password: "password!",
        "selected-checkbox": "checkbox",
        "deselected-checkbox": null,
        date: "2015-03-08",
        hidden: "hidden",
        number: 4711,
        url: "http://validatejs.org",
        "single-checked-radio": "radio",
        "single-unchecked-radio": null,
        "checked-radio": "radio2",
        "unchecked-radio": null,
        "selected-dropdown": "option2",
        "unselected-dropdown": null,
        "textarea": "the textarea"
        });
    });

    it("has an option to nullify empty and trim strings", function() {
      var form = document.createElement("form");
      form.innerHTML = '' +
        '<input type="text" name="normal" value="normal">' +
        '<input type="text" name="empty">' +
        '<input type="text" name="whitespace" value=" ">' +
        '<input type="text" name="trimmed" value=" foo ">';

      var options = {nullify: false};
      expect(validate.collectFormValues(form, options)).toEqual({
        normal: "normal",
        empty: "",
        whitespace: " ",
        trimmed: " foo "
      });

      options = {nullify: true};
      expect(validate.collectFormValues(form, options)).toEqual({
        normal: "normal",
        empty: null,
        whitespace: " ",
        trimmed: " foo "
      });

      options = {trim: true};
      expect(validate.collectFormValues(form, options)).toEqual({
        normal: "normal",
        empty: null,
        whitespace: null,
        trimmed: "foo"
      });
    });

    it("has a way to ignore elements", function() {
      var form = document.createElement("form");
      form.innerHTML = '' +
        '<input type="text" name="ignored" value="ignored" data-ignored>' +
        '<select name="ignored-select" data-ignored>' +
        '  <option value="select" selected>Select</option>' +
        '</select>';
      expect(validate.collectFormValues(form)).toEqual({});
    });

    it("uses true/false for checkboxes without a value", function() {
      var form = document.createElement("form");
      form.innerHTML = '' +
        '<input type="checkbox" name="checked" checked>' +
        '<input type="checkbox" name="unchecked">';

      expect(validate.collectFormValues(form)).toEqual({
        checked: true,
        unchecked: false
      });
    });

    it("accepts jquery elements", function() {
      var $form = $('<form><input value="foobar" name="input" /></form>');
      expect(validate.collectFormValues($form)).toEqual({
        input: "foobar"
      });
    });

    it("empty jquery collections return empty objects", function() {
      expect(validate.collectFormValues($())).toEqual({});
    });

    it("handles empty and invalid numeric inputs", function() {
      var form = document.createElement("form");
      form.innerHTML = '' +
        '<input type="number" name="emptyNumber">' +
        '<input type="number" name="invalidNumber" value="abc">';

      expect(validate.collectFormValues(form)).toEqual({
        emptyNumber: null,
        invalidNumber: null
      });
    });

    it("handles select tags with 'multiple'", function() {
      var form = document.createElement("form");
      form.innerHTML = '' +
        '<select name="selected-dropdown" multiple>' +
        '  <option>' +
        '  <option value="option1">' +
        '  <option value="option2" selected>' +
        '  <option value="option3">' +
        '  <option value="option4" selected>' +
        '</select>' +
        '<select name="unselected-dropdown" multiple>' +
        '  <option>' +
        '  <option value="option1">' +
        '  <option value="option2">' +
        '  <option value="option3">' +
        '  <option value="option4">' +
        '</select>' +
        '<select name="empty-value" multiple>' +
        '  <option selected>' +
        '</select>';

      expect(validate.collectFormValues(form)).toEqual({
        "selected-dropdown": ["option2", "option4"],
        "unselected-dropdown": [],
        "empty-value": [null]
      });
    });

    it("escapes periods", function() {
      var form = document.createElement("form");
      form.innerHTML = '<input type="text" name="foo.bar.baz" value="quux" />';

      expect(validate.collectFormValues(form)).toEqual({
        "foo\\\\.bar\\\\.baz": "quux"
      });
    });
  });

  describe("isDomElement", function() {
    it("returns true of DOM elements", function() {
      var form = document.createElement("form")
        , div = document.createElement("div")
        , a = document.createElement("a");

      expect(validate.isDomElement(form)).toBe(true);
      expect(validate.isDomElement(div)).toBe(true);
      expect(validate.isDomElement(a)).toBe(true);
      expect(validate.isDomElement(document)).toBe(true);
    });

    it("returns false for other objects", function() {
      expect(validate.isDomElement({})).toBe(false);
      expect(validate.isDomElement(0)).toBe(false);
      expect(validate.isDomElement(true)).toBe(false);
      expect(validate.isDomElement("foo")).toBe(false);
      expect(validate.isDomElement("")).toBe(false);
      expect(validate.isDomElement([])).toBe(false);
    });
  });

  describe("cleanAttributes", function() {
    it("handles null for both inputs", function() {
      expect(validate.cleanAttributes(null, {})).toEqual({});
      expect(validate.cleanAttributes({}, null)).toEqual({});
      expect(validate.cleanAttributes(null, null)).toEqual({});
    });

    it("always returns a copy", function() {
      var obj = {};
      expect(validate.cleanAttributes(obj, {})).not.toBe(obj);
    });

    it("returns a copy of the attributes with only the whitelisted attributes", function() {
      var input = {
        foo: "foo",
        bar: "bar",
        baz: "baz"
      };

      expect(validate.cleanAttributes(input, {})).toEqual({});
      expect(validate.cleanAttributes(input, {foo: true})).toEqual({
        foo: "foo"
      });
      expect(validate.cleanAttributes(input, {foo: true, bar: true})).toEqual({
        foo: "foo",
        bar: "bar"
      });
      expect(validate.cleanAttributes(input, {foo: true, bar: true, baz: true})).toEqual({
        foo: "foo",
        bar: "bar",
        baz: "baz"
      });
      expect(validate.cleanAttributes(input, {foo: false})).toEqual({});
    });

    it("handles nested objects", function() {
      var attributes = {
        "foo.bar.baz": "foobarbaz",
        foo: {
          shouldBeRemoved: "yup",
          bar: {
            shouldAlsoBeRemoved: "uhuh",
            baz: "baz",
            quux: "quux"
          }
        },
        one: {
          two: {
            four: "shouldBeRemoved"
          }
        },
        somethingThatIsNull: null
      };

      var whitelist = {
        "foo\\.bar\\.baz": true,
        "foo.bar.baz": true,
        "foo.bar.quux": true,
        "one.two.three": true,
        "somethingThatIsNull.someSubThingie": true
      };
      expect(validate.cleanAttributes(attributes, whitelist)).toEqual({
        "foo.bar.baz": "foobarbaz",
        foo: {
          bar: {
            baz: "baz",
            quux: "quux"
          }
        },
        one: {
          two: {
          }
        },
        somethingThatIsNull: null
      });
    });

    it("works with constraints", function() {
      var attributes = {
        name: "Test",
        description: "Yaay",
        createdAt: 'omgomg',
        address: {
          street: "Some street",
          postal: "47 111"
        }
      };

      var constraints = {
        name: {
          presence: true
        },
        description: {},
        "address.street": {},
        "address.postal": {},
        "address.country": {}
      };

      expect(validate.cleanAttributes(attributes, constraints)).not.toBe(attributes);
      expect(validate.cleanAttributes(attributes, constraints)).toEqual({
        name: "Test",
        description: "Yaay",
        address: {
          street: "Some street",
          postal: "47 111"
        }
      });
    });
  });

  describe("unique", function() {
    it("handles empty and single value arrays", function() {
      expect(validate.unique(null)).toEqual(null);
      expect(validate.unique([])).toEqual([]);
      expect(validate.unique([1])).toEqual([1]);
      expect(validate.unique(["foo"])).toEqual(["foo"]);
      expect(validate.unique("foo")).toEqual("foo");
    });

    it("filters non unique values", function() {
      expect(validate.unique(["foo", "bar", "bar", "foo", "baz"]))
        .toEqual(["foo", "bar", "baz"]);

      expect(validate.unique(["foo", "foo", "foo"]))
        .toEqual(["foo"]);

      expect(validate.unique([1, 2, 3, 3, 2, 1]))
        .toEqual([1, 2, 3]);
    });

    it("returns a copy", function() {
      var a = ["foo"];
      expect(validate.unique(a)).not.toBe(a);
    });
  });
});
