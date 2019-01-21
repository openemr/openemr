describe('validators.email', function() {
  var email = validate.validators.email;
  email = email.bind(email);

  afterEach(function() {
    delete validate.validators.email.message;
    delete validate.validators.email.options;
  });

  it("allows undefined values", function() {
    expect(email(null, {})).not.toBeDefined();
    expect(email(undefined, {})).not.toBeDefined();
  });

  it("doesn't allow non strings", function() {
    expect(email(3.14, {})).toBeDefined();
    expect(email(true, {})).toBeDefined();
  });

  it("allows valid emails", function() {
    expect(email('nicklas@ansman.se', {})).not.toBeDefined();
    expect(email('NiCkLaS@AnSmAn.Se', {})).not.toBeDefined();
    // Source: https://en.wikipedia.org/wiki/Email_address#Valid_email_addresses
    expect(email('niceandsimple@example.com', {})).not.toBeDefined();
    expect(email('very.common@example.com', {})).not.toBeDefined();
    expect(email('a.little.lengthy.but.fine@dept.example.com', {})).not.toBeDefined();
    expect(email('disposable.style.email.with+symbol@example.com', {})).not.toBeDefined();
    expect(email('other.email-with-dash@example.com', {})).not.toBeDefined();
    expect(email('üñîçøðé@example.com', {})).not.toBeDefined();
    expect(email("foo@some.customtld", {})).not.toBeDefined();
  });

  it("doesn't allow 'invalid' emails", function() {
    var expected = "is not a valid email";
    expect(email("", {})).toEqual(expected);
    expect(email(" ", {})).toEqual(expected);
    expect(email("foobar", {})).toEqual(expected);
    expect(email("foo@bar", {})).toEqual(expected);

    // Source: https://en.wikipedia.org/wiki/Email_address#Invalid_email_addresses
    expect(email('abc.example.com', {})).toEqual(expected);
    expect(email('a@b@c@example.com', {})).toEqual(expected);
    expect(email('a"b(c)d,e:f;g<h>i[j\\k]l@example.com', {})).toEqual(expected);
    expect(email('just"not"right@example.com', {})).toEqual(expected);
    expect(email('this is"not\\allowed@example.com', {})).toEqual(expected);
    expect(email('this\\ still\\"not\\\\allowed@example.com', {})).toEqual(expected);
  });

  it("allows you to customize the error message", function() {
    validate.validators.email.message = "is totally not an email";
    expect(email("foobar", {})).toEqual("is totally not an email");
    var options = {message: "some other message"};
    expect(email("foobar", options)).toEqual("some other message");
  });

  it("supports default options", function() {
    validate.validators.email.options = {message: "barfoo"};
    var options = {message: 'foobar'};
    expect(email("foo", options)).toEqual('foobar');
    expect(email("foo", {})).toEqual('barfoo');
    expect(validate.validators.email.options).toEqual({message: "barfoo"});
    expect(options).toEqual({message: "foobar"});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message}
      , value = "foo";
    expect(email(value, options)).toBe(message);
  });
});
