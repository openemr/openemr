describe('Test events', function () {
    describe('onSelectDate', function () {
        it('Should fired after user selected day', function (done) {
            var input= $(getInput()).val('2011/04/15');

            var picker = input.datetimepicker({
                onSelectDate: function (time, inp, evt) {
                    expect(picker).to.be.equal(this);
                    expect(time.getDate()).to.be.equal(17);
                    expect(time.getMonth()).to.be.equal(3);
                    expect(time.getFullYear()).to.be.equal(2011);
                    expect(inp[0]).to.be.equal(input[0]);
                    expect(evt.type).to.be.equal('click');
                    done();
                },
                format:	'Y/m/d'
            }).trigger('mousedown').data(PICKER);

            setTimeout(function () {
                var select = picker.find('td[data-date="17"][data-month="3"][data-year="2011"]');
                expect(select.length).to.be.equal(1);
                select.trigger('click');
            }, 100);
        });
    });
});