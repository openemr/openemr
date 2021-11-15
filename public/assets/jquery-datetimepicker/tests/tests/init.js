describe('Init', function () {
    describe('jQuery.fn', function () {
        it('Should have datetimepicker method', function () {
            expect(typeof jQuery.fn.datetimepicker).to.be.equal('function');
            expect(typeof $.fn.datetimepicker).to.be.equal('function');
        });
    });
    describe('jQuery.fn.datetimepicker', function () {
        it('Should have `defaults` property', function () {
            expect(typeof jQuery.fn.datetimepicker.defaults).to.be.equal('object');
            expect(jQuery.fn.datetimepicker.defaults.format).to.be.equal('Y/m/d H:i');
        });
    });
    describe('Create datetimepicker', function () {
        describe('Without parameters', function () {
            it('Should create plugin with default options', function (done) {
                var input = getInput();
                $(input).datetimepicker();
                var dtp = $(input).data('xdsoft_datetimepicker');
                expect(dtp).to.be.not.equal(null);
                expect(dtp[0].tagName).to.be.equal('DIV');
                expect(dtp[0].classList.contains('xdsoft_datetimepicker')).to.be.true;
                expect(dtp.is(':hidden')).to.be.true;
                $(input).trigger('mousedown')
                setTimeout(function () {
                    expect(dtp.is(':hidden')).to.be.false;
                    done();
                }, 150)
            });
        });
        describe('In inline mode', function () {
            it('Should create picker and replace original input', function () {
                var input = getInput();
                $(input).datetimepicker({
                    inline: true
                });
                var dtp = $(input).data('xdsoft_datetimepicker');
                expect(dtp.is(':hidden')).to.be.false;
                expect($(input).is(':hidden')).to.be.true;
            });
        });
    });
    describe('Set locale', function () {
        describe('Change locale', function () {
            it('Should create different pickers fro all locales', function (done) {
                $.datetimepicker.setLocale('en');
                var $input = $(getInput());
                $input.datetimepicker({inline: true});
                setTimeout(function () {
                    var text = $input.data('xdsoft_datetimepicker').text();
                    $input.datetimepicker('destroy');
                    $.datetimepicker.setLocale('ru');
                    $input.datetimepicker({inline: true});
                    setTimeout(function () {
                        expect($input.data('xdsoft_datetimepicker').text()).to.be.not.equal(text);
                        done();
                    }, 100)
                }, 100)
            });
        });
    });
    describe('Select day', function () {
        it('Should set selected date to input by format', function (done) {
            var input= $(getInput()).val('2011/04/15');

            var picker = input.datetimepicker({
                format:	'Y/m/d'
            }).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var start = picker.find('td[data-date="15"][data-month="3"][data-year="2011"]');
                expect(start.length).to.be.equal(1);
                expect(start.hasClass('xdsoft_disabled')).to.be.false;
                expect(start.hasClass('xdsoft_current')).to.be.true;
                var select = picker.find('td[data-date="17"][data-month="3"][data-year="2011"]');
                expect(start.length).to.be.equal(1);
                select.trigger('click');
                expect(input.val()).to.be.equal('2011/04/17')
                done();
            }, 100);
        });
    });
});