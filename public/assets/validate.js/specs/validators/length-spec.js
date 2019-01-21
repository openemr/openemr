describe('validator.length', function() {
  var length = validate.validators.length.bind(validate.validators.length);

  afterEach(function() {
    delete validate.validators.length.notValid;
    delete validate.validators.length.tooLong;
    delete validate.validators.length.tooShort;
    delete validate.validators.length.wrongLength;
    delete validate.validators.length.options;
  });

  describe("is", function() {
    it("allows you to specify a fixed length the object has to be", function() {
      var value = {length: 10}
        , options = {is: 10};
      expect(length(value, options)).not.toBeDefined();

      options.is = 11;
      var expected = ["is the wrong length (should be 11 characters)"];
      expect(length(value, options)).toEqual(expected);
    });

    it("allows a custom message", function() {
      var value = {length: 10}
        , options = {is: 11};

      validate.validators.length.wrongLength = "default %{count}";
      expect(length(value, options)).toEqual(["default 11"]);

      options.wrongLength = "wrongLength %{count}";
      expect(length(value, options)).toEqual(["wrongLength 11"]);
    });
  });

  describe("minimum", function() {
    it("allows you to specify a minimum value", function() {
      var value = {length: 10}
        , options = {minimum: 10};
      expect(length(value, options)).not.toBeDefined();

      options.minimum = 11;
      var expected = ["is too short (minimum is 11 characters)"];
      expect(length(value, options)).toEqual(expected);
    });

    it("allows a custom message", function() {
      var value = {length: 10}
        , options = {minimum: 11};

      validate.validators.length.tooShort = "default %{count}";

      expect(length(value, options)).toEqual(["default 11"]);

      options.tooShort = "tooShort %{count}";
      expect(length(value, options)).toEqual(["tooShort 11"]);
    });
  });

  describe("maximum", function() {
    it("allows you to specify a maximum value", function() {
      var value = {length: 11}
        , options = {maximum: 11};
      expect(length(value, options)).not.toBeDefined();

      options.maximum = 10;
      var expected = ["is too long (maximum is 10 characters)"];
      expect(length(value, options)).toEqual(expected);
    });

    it("allows a custom message", function() {
      var value = {length: 11}
        , options = {maximum: 10};

      validate.validators.length.tooLong = "default %{count}";
      expect(length(value, options)).toEqual(["default 10"]);

      options.tooLong = "tooLong %{count}";
      expect(length(value, options)).toEqual(["tooLong 10"]);
    });
  });

  it("allows empty values", function() {
    var options = {is: 10, minimum: 20, maximum: 5};
    expect(length(null, options)).not.toBeDefined();
    expect(length(undefined, options)).not.toBeDefined();
  });

  it("refuses values without a numeric length property", function() {
    spyOn(validate, "error");
    var options = {is: 10, minimum: 10, maximum: 20};
    expect(length(3.1415, options)).toBeDefined();
    expect(length(-3.1415, options)).toBeDefined();
    expect(length(0, options)).toBeDefined();
    expect(length({foo: "bar"}, options)).toBeDefined();
    expect(length({lengthi: 10}, options)).toBeDefined();
    expect(length({length: "foo"}, options)).toBeDefined();
    expect(length(3, {})).toBeDefined();
    expect(validate.error).toHaveBeenCalled();
  });

  // This test is not a real life example, specifying is with anything else
  // is just weird but hey.
  it("allows you to specify is, minimum and maximum", function() {
    var options = {
          is: 10,
          minimum: 20,
          maximum: 5
      };
      expect(length({length: 9}, options)).toHaveLength(3);
      expect(length("foobar", options)).toHaveLength(3);
      expect(length("", options)).toHaveLength(2);
      expect(length(" ", options)).toHaveLength(2);
  });

  it("return the message only once if specified", function() {
    var value = {length: 9}
      , options = {
          message: "my message",
          is: 10,
          minimum: 20,
          maximum: 5
        };
      expect(length(value, options)).toBe("my message");
  });

  it("doesn't override specific messages with the default one", function() {
    var value = {length: 3}
      , options = {
        is: 2,
        minimum: 4,
        maximum: 2,
        wrongLength: "wrongLength",
        tooLong: "tooLong",
        tooShort: "tooShort"
      };

      validate.validators.length.notValid = "default message";
      expect(length(value, options)).toHaveItems([
        "wrongLength",
        "tooLong",
        "tooShort"
      ]);
  });

  describe("tokenizer", function() {
    it("allows you to count words for example", function() {
      var options = {
        maximum: 2,
        tokenizer: function(value) { return value.split(/\s+/g); }
      };

      expect(length("foo bar", options)).not.toBeDefined();
      expect(length("foo bar baz", options)).toBeDefined();
    });
  });

  it("supports default options", function() {
    var tokenizer = jasmine.createSpy("tokenizer").and.returnValue({length: 3});
    validate.validators.length.options = {
      minimum: 10,
      tokenizer: tokenizer,
      message: "barfoo"
    };
    var options = {message: 'foobar'};
    expect(length(4, options)).toEqual('foobar');
    expect(length(4, {})).toEqual('barfoo');
    expect(tokenizer).toHaveBeenCalled();
    expect(validate.validators.length.options).toEqual({
      minimum: 10,
      tokenizer: tokenizer,
      message: "barfoo"
    });
    expect(options).toEqual({message: "foobar"});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message, minimum: 10}
      , value = "foo";
    expect(length(value, options)).toBe(message);
  });
});
