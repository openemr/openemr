describe("validators.exclusion", function() {
  var exclusion = validate.validators.exclusion.bind(validate.validators.exclusion)
    , within = ["foo", "bar", "baz"];

  afterEach(function() {
    delete validate.validators.exclusion.message;
    delete validate.validators.exclusion.options;
  });

  it("allows undefined values", function() {
    expect(exclusion(null, {})).not.toBeDefined();
    expect(exclusion(undefined, {})).not.toBeDefined();
  });

  it("returns nothing if the value is allowed", function() {
    var opts = {within: within};
    expect(exclusion("", {})).not.toBeDefined();
    expect(exclusion(" ", {})).not.toBeDefined();
    expect(exclusion("quux", opts)).not.toBeDefined();
    expect(exclusion(false, opts)).not.toBeDefined();
    expect(exclusion(1, opts)).not.toBeDefined();
  });

  it("returns an error if the value is not allowed", function() {
    var opts = {within: within};
    expect(exclusion("foo", opts)).toEqual("^foo is restricted");
    expect(exclusion("bar", opts)).toEqual("^bar is restricted");
    expect(exclusion("baz", opts)).toEqual("^baz is restricted");
  });

  it("allows you to customize the message", function() {
    validate.validators.exclusion.message = "^%{value} isn't great";
    var opts = {within: within};
    expect(exclusion("foo", opts)).toEqual("^foo isn't great");

    opts.message = "^The value %{value} is not valid";
    expect(exclusion("foo", opts)).toEqual("^The value foo is not valid");
  });

  it("uses the keys if the within value is an object", function() {
    expect(exclusion("foo", {within: {foo: true}})).toBeDefined();
    expect(exclusion("bar", {within: {foo: true}})).not.toBeDefined();
  });

  it("uses the options as the within list if the options is an array", function() {
    expect(exclusion("foo", ["foo", "bar"])).toBeDefined();
    expect(exclusion("baz", ["foo", "bar"])).not.toBeDefined();
  });

  it("supports default options", function() {
    validate.validators.exclusion.options = {
      message: "barfoo",
      within: [1, 2, 3]
    };
    var options = {message: 'foobar'};
    expect(exclusion(2, options)).toEqual('foobar');
    expect(exclusion(2, {})).toEqual('barfoo');
    expect(validate.validators.exclusion.options).toEqual({
      message: "barfoo",
      within: [1, 2, 3]
    });
    expect(options).toEqual({message: "foobar"});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message, within: ["foo"]}
      , value = "foo";
    expect(exclusion(value, options)).toBe(message);
  });
});
