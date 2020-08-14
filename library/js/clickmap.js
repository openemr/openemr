const clickmap = function (args) {
    let f = true;
    let counter = 0;

    const fnBuildMarker = function (x, y, pos, annotation) {
        const legendItem = $(`<li class='legend-item'><b>${pos}</b>${decodeURIComponent(annotation)}</li>`);
        $('.legend .body ul').append(legendItem);

        const marker = $('.marker-template').clone();
        marker.attr('data-x', x).attr('data-y', y).attr('data-pos', pos).attr('id', new Date().getTime())
            .attr('class', 'marker')
            .attr('style', `left:${x}px; top:${y}px;`)
            .find('span.count')
            .text(pos);

        marker.mouseenter(() => {})
            .mouseleave(() => {
                f = false;
            })
            .attr('title', annotation ? decodeURIComponent(annotation) : '')
            .show()
            .click(() => {
                $(this).remove();
                legendItem.remove();
                f = false;
            });
        return marker;
    };

    const fnIsnumber = function (num) {
        return !Number.isNaN(parseInt(num, 10));
    };

    const fnClear = function () {
        $('.marker').remove();
        $('.legend-item').remove();
        counter = 0;
    };

    const fnLoad = function (container, val) {
        fnClear();
        if (!val) {
            return;
        }
        const coordinates = val.split('}');
        for (let i = 0; i < coordinates.length; i += 1) {
            const coordinate = coordinates[i];
            if (coordinate) {
                const info = coordinate.split('^');
                const x = info[0];
                const y = info[1];
                const label = info[2];
                const detail = info[3];
                const marker = fnBuildMarker(x, y, label, detail);
                container.append(marker);
                if (fnIsnumber(label)) {
                    counter = parseInt(label, 10);
                }
            }
        }
    };

    const fnSave = function () {
        let val = '';
        $('.marker').each(function () {
            const marker = $(this);
            val += `${marker.attr('data-x')}^${marker.attr('data-y')}^${marker.attr('data-pos')}^${encodeURIComponent(marker.attr('data-pos'))}}`;
        });
        $('#data').attr('value', val);
        $('#submitForm').submit();
    };

    // main
    // let container = args.container;
    // const data = args.data;
    // const hideNav = args.hideNav;
    const {
        container, data, hideNav, dropdownOptions,
    } = args;

    const {
        options, optionsLabel,
    } = dropdownOptions;

    let optionsTitle = '';
    let optionsSelect = '';

    container.mousemove(() => {
        f = true;
    });

    if (!hideNav) {
        container.click((e) => {
            if (!f) {
                return;
            }
            const x = e.pageX - this.offsetLeft - 5;
            const y = e.pageY - this.offsetTop - 5;
            const hasOptions = typeof (options) !== 'undefined';

            if (hasOptions) {
                optionsTitle = typeof (optionsLabel) !== 'undefined' ? optionsLabel : 'Select one';
                for (const attr in options) {
                    if (Object.prototype.hasOwnProperty.call(options, attr)) {
                        optionsSelect += `<option value='${attr}'>${options[attr]}</option>`;
                    }
                }
            }

            const doMarker = function () {
                const newcounter = $('#counterInput').val();
                const notes = encodeURIComponent($('#detailTextArea').val());
                const selectedOption = encodeURIComponent($('#painScaleSelect').val());

                let combinedNotes = '';
                if (selectedOption) {
                    combinedNotes = options[selectedOption];
                }
                if (selectedOption && notes) {
                    combinedNotes += '%3A%20';
                }
                if (notes) {
                    combinedNotes += notes;
                }

                const marker = fnBuildMarker(x, y, newcounter, combinedNotes);
                container.append(marker);
                if (fnIsnumber(newcounter)) {
                    counter += 1;
                }
            };

            dlgopen('', '', hasOptions ? 345 : 300, 350, false, xl('Information'), {
                buttons: [{
                    text: xl('Save'),
                    close: true,
                    style: 'btn-sm btn-primary',
                    click: doMarker,
                }, {
                    text: xl('Cancel'),
                    close: true,
                    style: 'btn-sm btn-secondary',
                }],
                type: 'Alert',
                html: `
                    <div class="form-group">
                        <label for="counterInput">${xl('Label')}</label>
                        <input type="text" class="form-control" id="counterInput" value="${counter + 1}">
                    </div>
                    <div class="form-group">
                        <label for="painScaleSelect">${optionsTitle}</label>
                        <select class="form-control" id="painScaleSelect">
                            ${optionsSelect}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="detailTextArea">${xl('Detail')}</label>
                        <textarea class="form-control" id="detailTextArea" rows="3"></textarea>
                    </div>`,
            });
        });
    }

    $('#btn_clear').click(fnClear);
    $('#btn_save').click(fnSave);

    fnLoad(container, data);

    if (hideNav) {
        $('.nav').hide();
    }
};
