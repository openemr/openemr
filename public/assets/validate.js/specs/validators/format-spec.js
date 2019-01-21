describe("validators.format", function() {
  var format = validate.validators.format.bind(validate.validators.format)
    , options1 = {pattern: /^foobar$/i}
    , options2 = {pattern: "^foobar$", flags: "i"};

  afterEach(function() {
    delete validate.validators.format.message;
    delete validate.validators.format.options;
  });

  it("allows undefined values", function() {
    expect(format(null, options1)).not.toBeDefined();
    expect(format(null, options2)).not.toBeDefined();
    expect(format(undefined, options1)).not.toBeDefined();
    expect(format(undefined, options2)).not.toBeDefined();
  });

  it("allows values that matches the pattern", function() {
    expect(format("fooBAR", options1)).not.toBeDefined();
    expect(format("fooBAR", options2)).not.toBeDefined();
  });

  it("doesn't allow values that doesn't matches the pattern", function() {
    expect(format("", options1)).toBeDefined("is invalid");
    expect(format("", options2)).toBeDefined("is invalid");
    expect(format(" ", options1)).toBeDefined("is invalid");
    expect(format(" ", options2)).toBeDefined("is invalid");
    expect(format("barfoo", options1)).toEqual("is invalid");
    expect(format("barfoo", options2)).toEqual("is invalid");
  });

  it("non strings are not allowed", function() {
    var obj = {toString: function() { return "foobar"; }};
    expect(format(obj, options1)).toBeDefined();
    expect(format(obj, options2)).toBeDefined();
    expect(format(3, options1)).toBeDefined();
    expect(format(3, options2)).toBeDefined();
  });

  it("non strings are not allowed", function() {
    expect(format(3, options1)).toBeDefined();
    expect(format(3, options2)).toBeDefined();
  });

  it("doesn't allow partial matches", function() {
    var options1 = {pattern: /\.png$/g}
      , options2 = {pattern: "\\.png$", flags: "g"};
    expect(format("foo.png", options1)).toBeDefined();
    expect(format("foo.png", options2)).toBeDefined();
  });

  it("allows a custom message", function() {
    validate.validators.format.message = "is using a default message";

    var options = {pattern: /^[a-z]+$/g};
    expect(format("4711", options)).toEqual("is using a default message");

    options.message = "must only contain a-z";
    expect(format("4711", options)).toEqual("must only contain a-z");
  });

  it("supports the options being the pattern", function() {
    expect(format("barfoo", options1.pattern)).toBeDefined();
    expect(format("barfoo", options2.pattern)).toBeDefined();
  });

  it("supports default options", function() {
    validate.validators.format.options = {
      message: "barfoo",
      pattern: "abc"
    };
    var options = {message: 'foobar'};
    expect(format("cba", options)).toEqual('foobar');
    expect(format("cba", {})).toEqual('barfoo');
    expect(validate.validators.format.options).toEqual({
      message: "barfoo",
      pattern: "abc"
    });
    expect(options).toEqual({message: "foobar"});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message, pattern: /bar/}
      , value = "foo";
    expect(format(value, options)).toBe(message);
  });
});
