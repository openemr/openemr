describe("validate.async", function() {
  var error = null
    , success = null
    , Promise = validate.Promise;

  beforeEach(function() {
    success = jasmine.createSpy("success");
    error = jasmine.createSpy("error");

    validate.validators.asyncFailReject = function() {
      return new validate.Promise(function(resolve, reject) {
        setTimeout(function() {
          reject("failz");
        }, 1);
      });
    };

    validate.validators.asyncFail = function() {
      return new validate.Promise(function(resolve, reject) {
        setTimeout(function() {
          resolve("failz");
        }, 1);
      });
    };

    validate.validators.asyncSuccess = function() {
      return new validate.Promise(function(resolve, reject) {
        setTimeout(function() {
          resolve();
        }, 1);
      });
    };
  });

  afterEach(function() {
    delete validate.validators.asyncFailReject;
    delete validate.validators.asyncFail;
    delete validate.validators.asyncSuccess;
    delete validate.validators.asyncError;
    delete validate.async.options;
    validate.Promise = Promise;
  });

  it("makes validate return a promise", function() {
    var promise = validate.async({}, {});
    expect(promise).toBeAPromise();
  });

  it("throws an error if no promise is found", function() {
    delete validate.Promise;
    expect(function() {
      validate.async({}, {});
    }).toThrow();
  });

  it("allows you to specify a custom Promise implementation", function() {
    spyOn(validate, "Promise").and.callFake(function(func) {
      return new Promise(func);
    });
    var promise = validate.async({}, {});
    expect(validate.Promise).toHaveBeenCalled();
  });

  it.promise("resolves the promise if all constraints pass", function() {
    var attrs = {foo: "bar"}
      , constraints = {foo: {presence: true}};
    return validate.async(attrs, constraints).then(success, error).then(function() {
      expect(error).not.toHaveBeenCalled();
      expect(success).toHaveBeenCalledWith(attrs);
    });
  });

  it.promise("rejects the promise if any constraint fails", function() {
    var c = {name: {presence: true}};
    return validate.async({}, c).then(success, error).then(function() {
      expect(success).not.toHaveBeenCalled();
      expect(error).toHaveBeenCalled();
    });
  });

  it.promise("handles validators returning a promise", function() {
    var c = {
      name: {
        asyncFail: true,
        asyncSuccess: true
      }
    };
    return validate.async({}, c).then(success, error).then(function() {
      expect(success).not.toHaveBeenCalled();
      expect(error).toHaveBeenCalledWith({
        name: ["Name failz"]
      });
    });
  });

  it.promise('handles validators resolving a promise with error', function() {
    var c = {
      name: {
        asyncFail: true
      }
    };
    return validate.async({}, c).then(success, error).then(function() {
      expect(error).toHaveBeenCalledWith({
        name: ["Name failz"]
      });
    });
  });

  it.promise("supports fullMessages: false", function() {
    var c = {name: {presence: true}};
    return validate.async({}, c, {fullMessages: false}).then(success, error).then(function() {
      expect(success).not.toHaveBeenCalled();
      expect(error).toHaveBeenCalledWith({
        name: ["can't be blank"]
      });
    });
  });

  describe("waitForResults", function() {
    var error, success;

    beforeEach(function() {
      error = jasmine.createSpy("error handler");
      success = jasmine.createSpy("success handler");
    });

    it.promise("handles no results", function() {
      return validate.waitForResults([]);
    });

    it.promise("handles results with no promises", function() {
      var results = [{attribute: "foo", error: "bar"}];
      return validate.waitForResults(results).then(function() {
        expect(results).toEqual([{attribute: "foo", error: "bar"}]);
      });
    });

    it.promise("handles results with promises", function() {
      var results = [{
        attribute: "foo",
        error: new validate.Promise(function(resolve, reject) {
          setTimeout(resolve, 1);
        })
      }, {
        attribute: "bar",
        error: new validate.Promise(function(resolve, reject) {
          setTimeout(resolve.bind(this, "My error"), 1);
        })
      }, {
        attribute: "baz",
        error: 4711
      }];

      return validate.waitForResults(results).then(function() {
        expect(results).toEqual([{
          attribute: "foo",
          error: null
        }, {
          attribute: "bar",
          error: "My error"
        }, {
          attribute: "baz",
          error: 4711
        }]);
      });
    });

    it.promise("rejects the promise if any promise throw an exception", function() {
      var results = [{
        attribute: "foo",
        error: new validate.Promise(function(res, rej) { res(); })
      }, {
        attribute: "bar",
        error: new validate.Promise(function(resolve, reject) {
          throw new Error("Error");
        })
      }, {
        attribute: "baz",
        error: new validate.Promise(function(res, rej) { res(); })
      }];

      return validate.waitForResults(results).then(success, error).then(function() {
        expect(success).not.toHaveBeenCalled();
        expect(error).toHaveBeenCalledWith(new Error("Error"));
      });
    });
  });

  it.promise("allows default options", function() {
    validate.async.options = {format: "flat"};
    var c = {name: {presence: true}}
      , options = {foo: "bar"};
    return validate.async({}, c, options).then(success, error).then(function() {
      expect(success).not.toHaveBeenCalled();
      expect(error).toHaveBeenCalledWith(["Name can't be blank"]);
      expect(options).toEqual({foo: "bar"});
      expect(validate.async.options).toEqual({format: "flat"});
    });
  });

  it.promise("rejects the promise with an error if an exception is thrown", function() {
    var c = {
      attribute: {
        asyncError: true
      }
    };
    validate.validators.asyncError = function() {
      return new Promise(function(resolve, reject) {
        reject(new Error("Some error"));
      });
    };

    return validate.async({}, c).then(success, error).then(function() {
      expect(success).not.toHaveBeenCalled();
      expect(error).toHaveBeenCalledWith(new Error("Some error"));
    });
  });

  it.promise("cleans the attributes per default", function() {
    var attrs = {foo: "bar"}
      , constraints = {bar: {presence: true}}
      , cleaned = {bar: "foo"};

    spyOn(validate, "cleanAttributes").and.returnValue(cleaned);

    return validate.async(attrs, constraints).then(success, error).then(function() {
      expect(error).not.toHaveBeenCalled();
      expect(success).toHaveBeenCalledWith(cleaned);
      expect(validate.cleanAttributes).toHaveBeenCalledWith(attrs, constraints);
    });
  });

  it.promise("doesn't cleans the attributes is cleanAttributes: false", function() {
    var attrs = {foo: "bar"}
      , constraints = {foo: {presence: true}}
      , cleaned = {bar: "foo"};

    spyOn(validate, "cleanAttributes").and.returnValue(cleaned);

    return validate.async(attrs, constraints, {cleanAttributes: false}).then(success, error).then(function() {
      expect(error).not.toHaveBeenCalled();
      expect(success).toHaveBeenCalledWith(attrs);
      expect(validate.cleanAttributes).not.toHaveBeenCalled();
    });
  });

  describe("wrapping errors", function() {
    it.promise("it allows you to wrap errors using a custom function", function() {
      validate.async.options = {someOption: "someValue"};

      var attrs = {foo: "bar", bar: "foo"}
        , originalConstraints = {foo: {numericality: true}}
        , wrapped = {attr: ["errors"]}
        , wrapper = jasmine.createSpy("wrapper").and.callFake(function(errors, options, attributes, constraints) {
            expect(errors).toEqual({foo: ["Foo is not a number"]});
            // The options should have been merged with the default options
            expect(options).toEqual({
              wrapErrors: wrapper,
              someOption: "someValue"
            });
            expect(attributes).toEqual({foo: "bar"});
            expect(constraints).toBe(originalConstraints);
            return wrapped;
          })
        , originalOptions = {wrapErrors: wrapper};

      return validate.async(attrs, originalConstraints, originalOptions).then(success, error).then(function() {
        expect(wrapper).toHaveBeenCalled();
        expect(error).toHaveBeenCalledWith(wrapped);
        expect(success).not.toHaveBeenCalled();
      });
    });

    it.promise("calls the wrapper function with the new keyword", function() {
      var wrapper = jasmine.createSpy("wrapper").and.callFake(function(errors) {
        expect(this.constructor).toBe(wrapper);
        return errors;
      });

      return validate.async({}, {foo: {presence: true}}, {wrapErrors: wrapper}).then(success, error).then(function() {
        expect(error).toHaveBeenCalled();
        expect(success).not.toHaveBeenCalled();
        expect(wrapper).toHaveBeenCalled();
      });
    });
  });
});
