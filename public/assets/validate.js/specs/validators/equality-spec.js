describe('validators.equality', function() {
  var equality = validate.validators.equality;
  equality = equality.bind(equality);

  afterEach(function() {
    delete validate.validators.equality.message;
    delete validate.validators.equality.options;
  });

  it("allows empty values", function() {
    expect(equality(null, "bar", "foo", {})).not.toBeDefined();
    expect(equality(undefined, "bar", "foo", {})).not.toBeDefined();
  });

  it("supports equality with another attribute", function() {
    expect(equality("", "bar", "foo", {foo: "foo"})).toBeDefined();
    expect(equality("  ", "bar", "foo", {foo: "foo"})).toBeDefined();
    expect(equality("foo", "bar", "foo", {foo: "foo"})).toBeDefined();
    expect(equality("foo", "bar", "foo", {foo: "foo", bar: "bar"})).toBeDefined();
    expect(equality("foo", "bar", "foo", {foo: "foo", bar: "foo"})).not.toBeDefined();

    expect(equality(1, "bar", "foo", {foo: 1})).toBeDefined();
    expect(equality(1, "bar", "foo", {foo: 1, bar: 2})).toBeDefined();
    expect(equality(1, "bar", "foo", {foo: 1, bar: 1})).not.toBeDefined();
  });

  it("has a nice default message", function() {
    var constraints = {
      foo: {
        equality: "fooBar"
      }
    };
    expect(equality("foo", "fooBar", "foo", {foo: "foo"})).toEqual("is not equal to foo bar");
  });

  it("allows you to customize the error message", function() {
    validate.validators.equality.message = "is totally not equal to %{attribute}";
    expect(equality("foo", "bar", "foo", {foo: "foo"})).toEqual("is totally not equal to bar");
    var options = {attribute: "bar", message: "some other message %{attribute}"};
    expect(equality("foo", options, "foo", {foo: "foo"})).toEqual("some other message bar");
  });

  it("supports default options", function() {
    validate.validators.equality.options = {message: "barfoo", attribute: "bar"};
    var options = {message: 'foobar', attribute: "baz"};
    expect(equality("foo", options, "foo", {foo: "foo"})).toEqual('foobar');
    expect(equality("foo", {}, "foo", {foo: "foo"})).toEqual('barfoo');
    expect(equality("foo", {message: "foobar"}, "foo", {foo: "foo"})).toEqual('foobar');
    expect(validate.validators.equality.options).toEqual({message: "barfoo", attribute: "bar"});
    expect(options).toEqual({message: "foobar", attribute: "baz"});
  });

  it("supports nested objects", function() {
    expect(equality("foo", "bar.baz", "foo", {foo: "foo"})).toBeDefined();
    expect(equality("foo", "bar.baz", "foo", {foo: "foo", bar: {baz: "baz"}})).toBeDefined();
    expect(equality("foo", "bar.baz", "foo", {foo: "foo", bar: {baz: "foo"}})).not.toBeDefined();
  });

  it("throws an error if the attribute option isn't provided", function() {
    function run(options) {
      equality("foo", options, "foo", {foo: "foo"});
    }

    expect(run.bind({})).toThrow();
    expect(run.bind({attribute: null})).toThrow();
    expect(run.bind({attribute: 4711})).toThrow();
    expect(run.bind({attribute: false})).toThrow();
    expect(run.bind({attribute: undefined})).toThrow();
    expect(run.bind({attribute: ""})).toThrow();
  });

  it("accepts an optional comparator", function() {
    var complex = {foo: "bar"}
      , other = {bar: "foo"}
      , comparator = jasmine.createSpy("comparator").and.returnValue(true)
      , attributes = {complex: complex, other: other}
      , constraints = {
          complex: {
            equality: {
              attribute: "other",
              comparator: comparator
            }
          }
        };

    expect(validate(attributes, constraints)).not.toBeDefined();
    expect(comparator)
      .toHaveBeenCalledWith(complex,
                            other,
                            constraints.complex.equality,
                            "complex",
                            attributes);

    comparator.and.returnValue(false);
    expect(validate(attributes, constraints)).toBeDefined();
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message, attribute: "bar"}
      , value = "foo";
    expect(equality(value, options)).toBe(message);
  });

  it("calls custom prettify from options", function() {
    var options = {attribute: "fooBar", prettify: function() {}};
    spyOn(options, "prettify").and.returnValue("qux");
    spyOn(validate, "prettify").and.returnValue("baz");
    expect(equality("foo", options, "foo", {foo: "foo"})).toEqual("is not equal to qux");
    expect(options.prettify).toHaveBeenCalledWith("fooBar");
    expect(validate.prettify).not.toHaveBeenCalled();
  });
});
