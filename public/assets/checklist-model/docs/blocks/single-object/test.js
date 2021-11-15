describe('single-object', function() {

  beforeEach(function() {
    browser().navigateTo(mainUrl);
  });

  var s = '[ng-controller="Ctrl7"] ';
  var a = s+' input[type="checkbox"]';

  it('should initialize with correct values', function() {
    check(a, [1]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
  });

  it('should check/uncheck items', function() {
    using(s+'label:eq(0)').input('checked').check(false);
    check(a, [0]);
    expect(element(s+'pre').text()).toBe('[]');
    using(s+'label:eq(0)').input('checked').check(true);
    check(a, [1]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
  });

  it('should check all', function() {
    element(s+'button[ng-click="checkAll()"]').click();
    check(a, [1]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
  });

  it('should uncheck all', function() {
    element(s+'button[ng-click="uncheckAll()"]').click();
    check(a, [0]);
    expect(element(s+'pre').text()).toBe('[]');
  });

  it('should check first', function() {
    element(s+'button[ng-click="checkFirst()"]').click();
    check(a, [1]);
    expect(element(s+'pre').text()).toMatch('id');
    expect(element(s+'pre').text()).toMatch('guest');
  });

});