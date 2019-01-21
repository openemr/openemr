describe("validators.inclusion", function() {
  var inclusion = validate.validators.inclusion.bind(validate.validators.inclusion)
    , within = ["foo", "bar", "baz"];

  afterEach(function() {
    delete validate.validators.inclusion.message;
    delete validate.validators.inclusion.message;
  });

  it("allows empty values", function() {
    expect(inclusion(null, {})).not.toBeDefined();
    expect(inclusion(undefined, {})).not.toBeDefined();
  });

  it("returns nothing if the value is allowed", function() {
    var opts = {within: within};
    expect(inclusion("foo", opts)).not.toBeDefined();
    expect(inclusion("bar", opts)).not.toBeDefined();
    expect(inclusion("baz", opts)).not.toBeDefined();
  });

  it("returns an error if the value is not included", function() {
    var opts = {within: within};
    expect(inclusion("", {})).toBeDefined();
    expect(inclusion(" ", {})).toBeDefined();
    expect(inclusion("quux", opts)).toEqual("^quux is not included in the list");
    expect(inclusion(false, opts)).toEqual("^false is not included in the list");
    expect(inclusion(1, opts)).toEqual("^1 is not included in the list");
  });

  it("allows you to customize the message", function() {
    validate.validators.inclusion.message = "^Default message: %{value}";
    var opts = {within: within};
    expect(inclusion("quux", opts)).toEqual("^Default message: quux");

    opts.message = "^%{value} is not a valid choice";
    expect(inclusion("quux", opts)).toEqual("^quux is not a valid choice");
  });

  it("uses the keys if the within value is an object", function() {
    expect(inclusion("foo", {within: {foo: true}})).not.toBeDefined();
    expect(inclusion("bar", {within: {foo: true}})).toBeDefined();
  });

  it("uses the options as the within list if the options is an array", function() {
    expect(inclusion("foo", ["foo", "bar"])).not.toBeDefined();
    expect(inclusion("baz", ["foo", "bar"])).toBeDefined();
  });

  it("supports default options", function() {
    validate.validators.inclusion.options = {
      message: "barfoo",
      within: [1, 2, 3]
    };
    var options = {message: 'foobar'};
    expect(inclusion(4, options)).toEqual('foobar');
    expect(inclusion(4, {})).toEqual('barfoo');
    expect(validate.validators.inclusion.options).toEqual({
      message: "barfoo",
      within: [1, 2, 3]
    });
    expect(options).toEqual({message: "foobar"});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message, within: ["bar"]}
      , value = "foo";
    expect(inclusion(value, options)).toBe(message);
  });
});
