describe("validators.numericality", function() {
  var numericality = validate.validators.numericality
      .bind(validate.validators.numericality);

  afterEach(function() {
    var n = validate.validators.numericality;
    delete n.message;
    delete n.notValid;
    delete n.notInteger;
    delete n.notOdd;
    delete n.notEven;
    delete n.notGreaterThan;
    delete n.notGreaterThanOrEqualTo;
    delete n.notEqualTo;
    delete n.notLessThan;
    delete n.notLessThanOrEqualTo;
    delete n.notDivisibleBy;
    delete n.options;
  });

  it("allows empty values", function() {
    expect(numericality(null, {})).not.toBeDefined();
    expect(numericality(undefined, {})).not.toBeDefined();
  });

  it("allows numbers", function() {
    expect(numericality(3.14, {})).not.toBeDefined();
    expect(numericality("3.14", {})).not.toBeDefined();
  });

  it("doesn't allow non numbers", function() {
    var e = "is not a number";
    expect(numericality("", {})).toEqual(e);
    expect(numericality("  ", {})).toEqual(e);
    expect(numericality("foo", {})).toEqual(e);
    expect(numericality(NaN, {})).toEqual(e);
    expect(numericality(false, {})).toEqual(e);
    expect(numericality([1], {})).toEqual(e);
    expect(numericality({foo: "bar"}, {})).toEqual(e);
  });

  it("doesn't allow strings if noStrings is true", function() {
    expect(numericality("3.14", {noStrings: true})).toBeDefined();
  });

  it("uses the message if specified", function() {
    validate.validators.numericality.notValid = "default message";
    expect(numericality("foo", {})).toEqual("default message");
    expect(numericality("foo", {message: "my message"})).toEqual("my message");
  });

  it("uses the custom message if specified", function() {
    validate.validators.numericality.message = "default generic message";
    expect(numericality("foo", {})).toEqual("default generic message");
    var expected = "default message";
    expect(numericality("foo", {notValid: expected})).toEqual("default message");
    expect(numericality("foo", {message: "my message"})).toEqual("my message");
  });

  describe("onlyInteger", function() {
    it("allows integers", function() {
      expect(numericality(1, {onlyInteger: true})).not.toBeDefined();
    });

    it("doesn't allow real numbers", function() {
      var expected = "must be an integer";
      expect(numericality(3.14, {onlyInteger: true})).toEqual(expected);
    });

    it("uses the message if specified", function() {
      var opts = {onlyInteger: true};

      validate.validators.numericality.message = "default generic message";
      expect(numericality(3.14, opts)).toEqual("default generic message");

      validate.validators.numericality.notInteger = "default message";
      expect(numericality(3.14, opts)).toEqual("default message");

      opts.message = "my message";
      expect(numericality(3.14, opts)).toEqual("my message");
    });

    it("uses the custom message if specified", function() {
      var opts = {onlyInteger: true, notInteger: "default message" };
      expect(numericality(3.14, opts)).toEqual("default message");
    });
  });

  describe("greaterThan", function() {
    it("allows numbers that are greater than", function() {
      expect(numericality(3.14, {greaterThan: 2.72})).not.toBeDefined();
    });

    it("doesn't allow numbers that are smaller than or equal to", function() {
      var expected = ["must be greater than 3.14"];
      expect(numericality(3.14, {greaterThan: 3.14})).toEqual(expected);
      expect(numericality(2.72, {greaterThan: 3.14})).toEqual(expected);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(3.14, {greaterThan: 3.14, notGreaterThan: expected})).toEqual([expected]);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(3.14, {greaterThan: 3.14})).toEqual(["default generic message"]);

      validate.validators.numericality.notGreaterThan = "default message";
      expect(numericality(3.14, {greaterThan: 3.14})).toEqual(["default message"]);
    });
  });

  describe("greaterThanOrEqualTo", function() {
    it("allows numbers that are greater than or equal to", function() {
      expect(numericality(3.14, {greaterThanOrEqualTo: 2.72})).not.toBeDefined();
      expect(numericality(2.72, {greaterThanOrEqualTo: 2.72})).not.toBeDefined();
    });

    it("doesn't allow numbers that are smaller than", function() {
      var expected = ["must be greater than or equal to 3.14"];
      expect(numericality(2.72, {greaterThanOrEqualTo: 3.14})).toEqual(expected);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(3.13, {greaterThanOrEqualTo: 3.14, notGreaterThanOrEqualTo: expected})).toEqual([expected]);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(3.13, {greaterThanOrEqualTo: 3.14})).toEqual(["default generic message"]);

      validate.validators.numericality.notGreaterThanOrEqualTo = "default message";
      expect(numericality(3.13, {greaterThanOrEqualTo: 3.14})).toEqual(["default message"]);
    });
  });

  describe("equalTo", function() {
    it("allows numbers that are equal to", function() {
      expect(numericality(2.72, {equalTo: 2.72})).not.toBeDefined();
    });

    it("doesn't allow numbers that are not equal", function() {
      var expected = ["must be equal to 2.72"];
      expect(numericality(3.14, {equalTo: 2.72})).toEqual(expected);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(3.13, {equalTo: 3.14})).toEqual(["default generic message"]);

      validate.validators.numericality.notEqualTo = "default message";
      expect(numericality(3.13, {equalTo: 3.14})).toEqual(["default message"]);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(3.13, {equalTo: 3.14, notEqualTo:expected})).toEqual([expected]);
    });
  });

  describe("lessThan", function() {
    it("allows numbers that are less than", function() {
      expect(numericality(2.72, {lessThan: 3.14})).not.toBeDefined();
    });

    it("doesn't allow numbers that are greater than or equal to", function() {
      var expected = ["must be less than 2.72"];
      expect(numericality(2.72, {lessThan: 2.72})).toEqual(expected);
      expect(numericality(3.14, {lessThan: 2.72})).toEqual(expected);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(3.14, {lessThan: 3.14})).toEqual(["default generic message"]);

      validate.validators.numericality.notLessThan = "default message";
      expect(numericality(3.14, {lessThan: 3.14})).toEqual(["default message"]);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(3.14, {lessThan: 3.14, notLessThan: expected})).toEqual([expected]);
    });
  });

  describe("lessThanOrEqualTo", function() {
    it("allows numbers that are less than or equal to", function() {
      expect(numericality(2.72, {lessThanOrEqualTo: 3.14})).not.toBeDefined();
      expect(numericality(3.14, {lessThanOrEqualTo: 3.14})).not.toBeDefined();
    });

    it("doesn't allow numbers that are greater than", function() {
      var expected = ["must be less than or equal to 2.72"];
      expect(numericality(3.14, {lessThanOrEqualTo: 2.72})).toEqual(expected);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(3.15, {lessThanOrEqualTo: 3.14})).toEqual(["default generic message"]);

      validate.validators.numericality.notLessThanOrEqualTo = "default message";
      expect(numericality(3.15, {lessThanOrEqualTo: 3.14})).toEqual(["default message"]);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(3.15, {lessThanOrEqualTo: 3.14, notLessThanOrEqualTo: expected})).toEqual([expected]);
    });
  });

  describe("divisibleBy", function() {
    it("allows numbers divisible by the value", function() {
      expect(numericality(0, {divisibleBy: 2})).not.toBeDefined();
      expect(numericality(5, {divisibleBy: 5})).not.toBeDefined();
      expect(numericality(16, {divisibleBy: 8})).not.toBeDefined();
    });

    it("doesn't allow numbers that are not divisible by the given number", function() {
      var expected = ["must be divisible by 100"];
      expect(numericality(121, {divisibleBy: 100})).toEqual(expected);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(161, {divisibleBy: 200})).toEqual(["default generic message"]);

      validate.validators.numericality.notDivisibleBy = "default message";
      expect(numericality(161, {divisibleBy: 200})).toEqual(["default message"]);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(133, {divisibleBy: 4, notDivisibleBy: expected})).toEqual([expected]);
    });
  });

  describe("odd", function() {
    it("allows odd numbers", function() {
      expect(numericality(1, {odd: true})).not.toBeDefined();
      expect(numericality(3, {odd: true})).not.toBeDefined();
      expect(numericality(5, {odd: true})).not.toBeDefined();
    });

    it("disallows even numbers", function() {
      var expected = ["must be odd"];
      expect(numericality(0, {odd: true})).toEqual(expected);
      expect(numericality(2, {odd: true})).toEqual(expected);
      expect(numericality(4, {odd: true})).toEqual(expected);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(2, {odd: true})).toEqual(["default generic message"]);

      validate.validators.numericality.notOdd = "default message";
      expect(numericality(2, {odd: true})).toEqual(["default message"]);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(2, {odd: true, notOdd: expected})).toEqual([expected]);
    });
  });

  describe("even", function() {
    it("allows even numbers", function() {
      expect(numericality(0, {even: true})).not.toBeDefined();
      expect(numericality(2, {even: true})).not.toBeDefined();
      expect(numericality(4, {even: true})).not.toBeDefined();
    });

    it("disallows odd numbers", function() {
      var expected = ["must be even"];
      expect(numericality(1, {even: true})).toEqual(expected);
      expect(numericality(3, {even: true})).toEqual(expected);
      expect(numericality(5, {even: true})).toEqual(expected);
    });

    it("allows for a default message", function() {
      validate.validators.numericality.message = "default generic message";
      expect(numericality(3, {even: true})).toEqual(["default generic message"]);

      validate.validators.numericality.notEven = "default message";
      expect(numericality(3, {even: true})).toEqual(["default message"]);
    });

    it("allows for a custom message", function() {
      var expected = "custom message";
      expect(numericality(3, {even: true, notEven: expected})).toEqual([expected]);
    });
  });

  it("can return multiple errors", function() {
    var options = {
      greaterThan: 10,
      greaterThanOrEqualTo: 10,
      lessThan: 5,
      lessThanOrEqualTo: 5,
      divisibleBy: 10,
      equalTo: 20,
      odd: true,
      even: true
    };
    expect(numericality(7.2, options)).toHaveLength(8);
  });

  it("returns options.message only once", function() {
    var options = {
      greaterThan: 10,
      greaterThanOrEqualTo: 10,
      lessThan: 5,
      lessThanOrEqualTo: 5,
      divisibleBy: 10,
      equalTo: 20,
      odd: true,
      even: true,
      message: 'my message'
    };
    expect(numericality(7.2, options)).toEqual("my message");
  });

  it("supports default options", function() {
    validate.validators.numericality.options = {
      greaterThan: 10,
      message: "barfoo"
    };
    var options = {message: 'foobar'};
    expect(numericality(4, options)).toEqual('foobar');
    expect(numericality(4, {})).toEqual('barfoo');
    expect(validate.validators.numericality.options).toEqual({
      greaterThan: 10,
      message: "barfoo"
    });
    expect(options).toEqual({message: "foobar"});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message}
      , value = "foo";
    expect(numericality(value, options)).toBe(message);
  });

  describe("strict", function() {
    it("disallows prefixed zeros", function() {
      expect(numericality("01.0", {strict: true}))
        .toEqual("must be a valid number");
      expect(numericality("0001.0000000", {strict: true}))
        .toEqual("must be a valid number");
      expect(numericality("020", {strict: true}))
        .toEqual("must be a valid number");
      expect(numericality("1.00", {strict: true, onlyInteger: true}))
        .toEqual("must be a valid number");
      expect(numericality("1.", {strict: true}))
        .toEqual("must be a valid number");
      expect(numericality("1.", {strict: true, onlyInteger: true}))
        .toEqual("must be a valid number");
      expect(numericality(".0", {strict: true}))
        .toEqual("must be a valid number");
      expect(numericality(".1", {strict: true}))
        .toEqual("must be a valid number");

      expect(numericality("1.00", {strict: true})).not.toBeDefined();
      expect(numericality("1.0", {strict: true})).not.toBeDefined();
      expect(numericality(10, {strict: true})).not.toBeDefined();
      expect(numericality("10", {strict: true})).not.toBeDefined();
      expect(numericality("0.1", {strict: true})).not.toBeDefined();
      expect(numericality("0", {strict: true})).not.toBeDefined();
      expect(numericality("-3", {strict: true})).not.toBeDefined();
    });
  });

  it("allows overriding the generic message", function() {
    validate.validators.numericality.message = "default generic message";
    expect(numericality("foo", {})).toEqual("default generic message");

    validate.validators.numericality.notValid = "default not valid message";
    expect(numericality("foo", {})).toEqual("default not valid message");

    expect(numericality("foo", {notValid: "not valid"})).toEqual("not valid");

    expect(numericality("foo", {message: "some error"})).toEqual("some error");
  });

  it("calls custom prettify from options", function() {
    var options = {greaterThan: 0, prettify: function() {}};
    spyOn(options, "prettify").and.returnValue("grooter than");
    spyOn(validate, "prettify").and.returnValue("greeter than");
    expect(numericality(0, options)).toEqual(["must be grooter than 0"]);
    expect(options.prettify).toHaveBeenCalledWith("greaterThan");
    expect(validate.prettify).not.toHaveBeenCalled();
  });
});
