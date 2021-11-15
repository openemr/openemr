describe('Test options', function () {
    describe('dayOfWeekStart', function () {
        it('Should change default start of week', function (done) {
            $.datetimepicker.setLocale('en');
            var input = $(getInput());
            var picker = input.datetimepicker({inline: true}).trigger('mousedown').data('xdsoft_datetimepicker');
            setTimeout(function () {
                var day = picker.find('th').eq(0).text();
                var date = picker.find('td').eq(0).text();
                input.datetimepicker('destroy');

                var picker2 = $(getInput()).datetimepicker({
                    inline: true,
                    dayOfWeekStart: 2
                }).trigger('mousedown').data(PICKER);

                setTimeout(function () {
                    expect(picker2.find('th').eq(0).text()).to.be.not.equal(day);
                    expect(picker2.find('td').eq(0).text()).to.be.not.equal(date);
                    done();
                }, 100);
            }, 100);
        });
    });
    describe('disabledDates and startDate', function () {
        it('Should disable some dates in picker and picker should be open on startDate', function (done) {
            var input= $(getInput());
            var picker = input.datetimepicker({
                disabledDates:['1986/01/08','1986/01/09','1986/01/10'],
                startDate:	'1986/01/05'
            }).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var day = picker.find('td[data-date="8"][data-month="0"][data-year="1986"]');
                expect(day.hasClass('xdsoft_disabled')).to.be.true;
                var start = picker.find('td[data-date="5"][data-month="0"][data-year="1986"]');
                expect(start.length).to.be.equal(1);
                expect(start.hasClass('xdsoft_disabled')).to.be.false;
                done();
            }, 100);
        });
    });
    describe('defaultDate', function () {
        it('Should open picker on some date', function (done) {
            var input= $(getInput());
            var picker = input.datetimepicker({formatDate:'d.m.Y', defaultDate: '+03.01.1970'}).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var now = new Date();
                now.setDate(now.getDate() + 2)
                var start = picker.find('td[data-date="' + now.getDate() + '"][data-month="' + now.getMonth() + '"][data-year="' + now.getFullYear() + '"]');
                expect(start.length).to.be.equal(1);
                expect(start.hasClass('xdsoft_disabled')).to.be.false;
                expect(start.hasClass('xdsoft_current xdsoft_today')).to.be.true;
                done();
            }, 100);
        });
    });
    describe('Value', function () {
        it('Should set value to plugin', function (done) {
            var input= $(getInput());
            var picker = input.datetimepicker({value: '2011/04/15 05:03'}).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var start = picker.find('td[data-date="15"][data-month="3"][data-year="2011"]');
                expect(start.length).to.be.equal(1);
                expect(start.hasClass('xdsoft_disabled')).to.be.false;
                done();
            }, 100);
        });
    });
    describe('timepicker = false', function () {
        it('Should create only datepicker', function (done) {
            var input= $(getInput());
            var picker = input.datetimepicker({
                timepicker: false
            }).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var timepicker = picker.find('.xdsoft_timepicker');
                expect(timepicker.length).to.be.equal(1);
                expect(timepicker.is(':hidden')).to.be.true;
                done();
            }, 100);
        });
    });
    describe('datepicker = false', function () {
        it('Should create only timepicker', function (done) {
            var input= $(getInput());
            var picker = input.datetimepicker({
                datepicker: false
            }).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var datepicker = picker.find('.xdsoft_datepicker');
                expect(datepicker.length).to.be.equal(1);
                expect(datepicker.is(':hidden')).to.be.true;
                done();
            }, 100);
        });
    });
});