describe("validators.url", function() {
  var url = validate.validators.url;
  url = url.bind(url);

  afterEach(function() {
    delete validate.validators.url.message;
    delete validate.validators.url.options;
  });

  it("allows empty values", function() {
    expect(url(null, {})).not.toBeDefined();
    expect(url(undefined, {})).not.toBeDefined();
  });

  it("doesn't allow non strings", function() {
    expect(url(3.14, {})).toBeDefined();
    expect(url(true, {})).toBeDefined();
    expect(url({key: "i'm a string"}, {})).toBeDefined();
  });

  it("doesn't allow 'invalid' urls", function() {
    var expected = "is not a valid url";

    expect(url("", {})).toBeDefined();
    expect(url(" ", {})).toBeDefined();
    expect(url("http://", {})).toBeDefined();
    expect(url("http://.", {})).toBeDefined();
    expect(url("http://..", {})).toBeDefined();
    expect(url("http://../", {})).toBeDefined();
    expect(url("http://?", {})).toBeDefined();
    expect(url("http://??", {})).toBeDefined();
    expect(url("http://??/", {})).toBeDefined();
    expect(url("http://#", {})).toBeDefined();
    expect(url("http://##", {})).toBeDefined();
    expect(url("http://##/", {})).toBeDefined();
    expect(url("http://foo.bar?q=Spaces should be encoded", {})).toBeDefined();
    expect(url("//", {})).toBeDefined();
    expect(url("//a", {})).toBeDefined();
    expect(url("///a", {})).toBeDefined();
    expect(url("///", {})).toBeDefined();
    expect(url("http:///a", {})).toBeDefined();
    expect(url("foo.com", {})).toBeDefined();
    expect(url("rdar://1234", {})).toBeDefined();
    expect(url("h://test", {})).toBeDefined();
    expect(url("http:// shouldfail.com", {})).toBeDefined();
    expect(url(":// should fail", {})).toBeDefined();
    expect(url("http://foo.bar/foo(bar)baz quux", {})).toBeDefined();
    expect(url("ftps://foo.bar/", {})).toBeDefined();
    expect(url("http://-error-.invalid/", {})).toBeDefined();
    expect(url("http://-a.b.co", {})).toBeDefined();
    expect(url("http://a.b-.co", {})).toBeDefined();
    expect(url("http://0.0.0.0", {})).toBeDefined();
    expect(url("http://10.1.1.0", {})).toBeDefined();
    expect(url("http://10.1.1.255", {})).toBeDefined();
    expect(url("http://224.1.1.1", {})).toBeDefined();
    expect(url("http://1.1.1.1.1", {})).toBeDefined();
    expect(url("http://123.123.123", {})).toBeDefined();
    expect(url("http://3628126748", {})).toBeDefined();
    expect(url("http://.www.foo.bar/", {})).toBeDefined();
    expect(url("http://www.foo.bar./", {})).toBeDefined();
    expect(url("http://.www.foo.bar./", {})).toBeDefined();
    expect(url("http://10.1.1.1", {})).toBeDefined();
    expect(url("http://localhost", {})).toBeDefined();
  });

  it("allows valid urls", function() {
    expect(url("http://foo.com", {})).not.toBeDefined();
    expect(url("http://foo.com/", {})).not.toBeDefined();
    expect(url("http://foo.com/blah_blah", {})).not.toBeDefined();
    expect(url("http://foo.com/blah_blah/", {})).not.toBeDefined();
    expect(url("http://foo.com/blah_blah_(wikipedia)", {})).not.toBeDefined();
    expect(url("http://foo.com/blah_blah_(wikipedia)_(again)", {})).not.toBeDefined();
    expect(url("http://foo.com?query=bar", {})).not.toBeDefined();
    expect(url("http://foo.com#fragment=bar", {})).not.toBeDefined();
    expect(url("http://www.example.com/wpstyle/?p=364", {})).not.toBeDefined();
    expect(url("https://www.example.com/foo/?bar=baz&inga=42&quux", {})).not.toBeDefined();
    expect(url("https://www.example.com/foo/#bar=baz&inga=42&quux", {})).not.toBeDefined();
    expect(url("http://✪df.ws/123", {})).not.toBeDefined();
    expect(url("http://userid:password@example.com:8080", {})).not.toBeDefined();
    expect(url("http://userid:password@example.com:8080/", {})).not.toBeDefined();
    expect(url("http://userid@example.com", {})).not.toBeDefined();
    expect(url("http://userid@example.com/", {})).not.toBeDefined();
    expect(url("http://userid@example.com:8080", {})).not.toBeDefined();
    expect(url("http://userid@example.com:8080/", {})).not.toBeDefined();
    expect(url("http://userid:password@example.com", {})).not.toBeDefined();
    expect(url("http://userid:password@example.com/", {})).not.toBeDefined();
    expect(url("http://142.42.1.1/", {})).not.toBeDefined();
    expect(url("http://142.42.1.1:8080/", {})).not.toBeDefined();
    expect(url("http://➡.ws/䨹", {})).not.toBeDefined();
    expect(url("http://⌘.ws", {})).not.toBeDefined();
    expect(url("http://⌘.ws/", {})).not.toBeDefined();
    expect(url("http://foo.com/blah_(wikipedia)#cite-1", {})).not.toBeDefined();
    expect(url("http://foo.com/blah_(wikipedia)_blah#cite-1", {})).not.toBeDefined();
    expect(url("http://foo.com/unicode_(✪)_in_parens", {})).not.toBeDefined();
    expect(url("http://foo.com/(something)?after=parens", {})).not.toBeDefined();
    expect(url("http://☺.damowmow.com/", {})).not.toBeDefined();
    expect(url("http://code.google.com/events/#&product=browser", {})).not.toBeDefined();
    expect(url("http://j.mp", {})).not.toBeDefined();
    expect(url("http://foo.bar/?q=Test%20URL-encoded%20stuff", {})).not.toBeDefined();
    expect(url("http://مثال.إختبار", {})).not.toBeDefined();
    expect(url("http://例子.测试", {})).not.toBeDefined();
    expect(url("http://उदाहरण.परीक्षा", {})).not.toBeDefined();
    expect(url("http://-.~_!$&'()*+,;=:%40:80%2f::::::@example.com", {})).not.toBeDefined();
    expect(url("http://1337.net", {})).not.toBeDefined();
    expect(url("http://a.b-c.de", {})).not.toBeDefined();
    expect(url("http://223.255.255.254", {})).not.toBeDefined();
    expect(url("http://a.b--c.de/", {})).not.toBeDefined();
  });

  it("allows local url and private networks if option is set", function() {
    expect(url("http://10.1.1.1", {allowLocal: true})).not.toBeDefined();
    expect(url("http://172.16.1.123", {allowLocal: true})).not.toBeDefined();
    expect(url("http://192.168.1.123", {allowLocal: true})).not.toBeDefined();
    expect(url("http://localhost/foo", {allowLocal: true})).not.toBeDefined();
    expect(url("http://localhost:4711/foo", {allowLocal: true})).not.toBeDefined();
    // Issue #95
    expect(url("http://servername01:8153/go/cctray.xml", {allowLocal: true})).not.toBeDefined();
    expect(url("http://nicklas:password@localhost:4711/foo", {allowLocal: true})).not.toBeDefined();
  });

  it("allows custom schemes option is set", function() {
    var options = {schemes: ['ftp', 'jdbc']};
    expect(url("ftp://foo.bar.com", options)).not.toBeDefined();
    expect(url("jdbc://foo.bar.com", options)).not.toBeDefined();
    expect(url("http://foo.bar.com", options)).toBeDefined();
  });

  it("allows you to customize the error message", function() {
    validate.validators.url.message = "is totally not an url";
    expect(url("foobar", {})).toEqual("is totally not an url");

    var options = { message: "is not an url dude" };
    expect(url("foobar", options)).toEqual("is not an url dude");
  });

  it("supports default options", function() {
    validate.validators.url.options = {message: "barfoo", allowLocal: true, schemes: ['myscheme']};
    var options = {message: 'foobar', allowLocal: true, schemes: ['myscheme']};
    expect(url("foo", options)).toEqual('foobar');
    expect(url("foo", {})).toEqual('barfoo');
    expect(validate.validators.url.options).toEqual({message: "barfoo", allowLocal: true, schemes: ['myscheme']});
    expect(options).toEqual({message: "foobar", allowLocal: true, schemes: ['myscheme']});
  });

  it("allows functions as messages", function() {
    var message = function() { return "foo"; };
    var options = {message: message}
      , value = "foo";
    expect(url(value, options)).toBe(message);
  });
});
