/**
 * Patient Finder filter panel
 *
 * The Filter button in the finder's heading opens a small panel where the user adds filters,
 * each a field, an operator and a value (Sex is Female, Age between 65 and 80, ...). The
 * fields, operators and choices come from the server (window.finderConfig.criteria, built from
 * PatientFinderCriteriaRegistry), so a new criterion needs no change here. The filters in use
 * show as removable chips under the heading; with none, the chip row is hidden and the finder
 * looks as it always has.
 *
 * dynamic_finder.js reads params() when it asks the server for rows and redraws on the
 * "finder:criteria" event this file triggers when the filters in use change.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function () {
    'use strict';

    var definitions = (window.finderConfig && window.finderConfig.criteria) || [];
    var byKey = {};
    definitions.forEach(function (definition) {
        byKey[definition.key] = definition;
    });

    // Filters in use: [{key, operator, value}]. Rows being edited in the open panel: draft.
    var applied = [];
    var draft = [];
    var $panel;
    var $chips;
    var $button;
    var $label;

    function operatorOf(definition, key) {
        return definition.operators.filter(function (operator) {
            return operator.key === key;
        })[0] || null;
    }

    function defaultValue(definition, operator) {
        if (operator.input === 'select') {
            return definition.options.length ? definition.options[0].id : '';
        }
        return operator.input === 'range' ? ['', ''] : '';
    }

    function newRow(key, used) {
        var definition = byKey[key] || definitions.filter(function (candidate) {
            return used.indexOf(candidate.key) === -1;
        })[0];
        if (!definition) {
            return null;
        }
        var operator = definition.operators[0];
        return {key: definition.key, operator: operator.key, value: defaultValue(definition, operator)};
    }

    // Whether a row has everything its operator needs.
    function isComplete(row) {
        var definition = byKey[row.key];
        var operator = definition ? operatorOf(definition, row.operator) : null;
        if (!operator) {
            return false;
        }
        if (operator.input === 'none') {
            return true;
        }
        if (operator.input === 'range') {
            return row.value[0] !== '' && row.value[1] !== '';
        }
        return row.value !== '' && row.value !== null;
    }

    // Whether a whole number written as digits is inside the operator's range.
    function inRange(operator, text) {
        if (!/^[0-9]{1,6}$/.test(String(text).trim())) {
            return false;
        }
        var number = parseInt(text, 10);
        return number >= operator.min && number <= operator.max;
    }

    // Whether a number or range row holds numbers the server will accept; other rows always do.
    function isValid(row) {
        var operator = operatorOf(byKey[row.key], row.operator);
        if (operator.input === 'range') {
            return inRange(operator, row.value[0]) && inRange(operator, row.value[1]);
        }
        return operator.input !== 'number' || inRange(operator, row.value);
    }

    function clone(rows) {
        return JSON.parse(JSON.stringify(rows));
    }

    function usedKeys(rows) {
        return rows.map(function (row) {
            return row.key;
        });
    }

    // The text of a chip: "Age between 65–80", "Last visit more than 365 days ago".
    function describe(row) {
        var definition = byKey[row.key];
        var operator = operatorOf(definition, row.operator);
        var value = '';
        if (operator.input === 'select') {
            value = definition.options.filter(function (option) {
                return option.id === row.value;
            }).map(function (option) {
                return option.title;
            })[0] || row.value;
        } else if (operator.input === 'range') {
            value = row.value[0] + '–' + row.value[1];
        } else if (operator.input === 'number') {
            value = row.value + (operator.unit ? ' ' + operator.unit : '');
        }
        return definition.label + ' ' + operator.label + (value ? ' ' + value : '');
    }

    function numberInput(operator, value, label, onInput) {
        return $('<input type="number" class="form-control form-control-sm finder-number">')
            .attr({min: operator.min, max: operator.max, 'aria-label': label}).val(value)
            .on('input', function () {
                onInput(this.value);
            });
    }

    function renderRow(row, index) {
        var definition = byKey[row.key];
        var operator = operatorOf(definition, row.operator);
        var others = usedKeys(draft.filter(function (other, at) {
            return at !== index;
        }));
        var $row = $('<div class="form-row align-items-center mb-2"></div>');
        var $field = $('<select class="form-control form-control-sm"></select>').attr('aria-label', definition.label);
        definitions.forEach(function (candidate) {
            if (others.indexOf(candidate.key) === -1) {
                $field.append($('<option></option>').val(candidate.key).text(candidate.label));
            }
        });
        $field.val(row.key).on('change', function () {
            draft[index] = newRow(this.value, []);
            renderPanel();
        });
        var $operator = $('<select class="form-control form-control-sm"></select>').attr('aria-label', definition.label);
        definition.operators.forEach(function (candidate) {
            $operator.append($('<option></option>').val(candidate.key).text(candidate.label));
        });
        $operator.val(row.operator).on('change', function () {
            row.operator = this.value;
            row.value = defaultValue(definition, operatorOf(definition, this.value));
            renderPanel();
        });
        $row.append($('<div class="col-auto"></div>').append($field));
        $row.append($('<div class="col-auto"></div>').append($operator));

        var $value = null;
        if (operator.input === 'select') {
            $value = $('<select class="form-control form-control-sm"></select>').attr('aria-label', definition.label);
            definition.options.forEach(function (option) {
                $value.append($('<option></option>').val(option.id).text(option.title));
            });
            $value.val(row.value).on('change', function () {
                row.value = this.value;
            });
        } else if (operator.input === 'number') {
            $value = $('<span class="d-flex align-items-center"></span>')
                .append(numberInput(operator, row.value, definition.label, function (value) {
                    row.value = value;
                }))
                .append($('<span class="ml-1"></span>').text(operator.unit));
        } else if (operator.input === 'range') {
            $value = $('<span class="d-flex align-items-center"></span>')
                .append(numberInput(operator, row.value[0], definition.label, function (value) {
                    row.value[0] = value;
                }))
                .append($('<span class="mx-1">–</span>'))
                .append(numberInput(operator, row.value[1], definition.label, function (value) {
                    row.value[1] = value;
                }));
        }
        if ($value) {
            $row.append($('<div class="col-auto"></div>').append($value));
        }
        $row.append($('<div class="col-auto"></div>').append(
            $('<button type="button" class="btn btn-link btn-sm text-danger px-1"></button>')
                .attr('aria-label', $panel.data('remove')).html('<i class="fa fa-times" aria-hidden="true"></i>')
                .on('click', function () {
                    draft.splice(index, 1);
                    renderPanel();
                })
        ));
        return $row;
    }

    function renderPanel() {
        $panel.empty();
        draft.forEach(function (row, index) {
            $panel.append(renderRow(row, index));
        });
        var $footer = $('<div class="d-flex align-items-center"></div>');
        $footer.append($('<button type="button" class="btn btn-link btn-sm px-0 mr-3"></button>')
            .text('+ ' + $panel.data('add')).prop('disabled', draft.length >= definitions.length)
            .on('click', function () {
                var row = newRow('', usedKeys(draft));
                if (row) {
                    draft.push(row);
                    renderPanel();
                }
            }));
        $footer.append($('<button type="button" class="btn btn-link btn-sm px-0 mr-3"></button>')
            .text($panel.data('clear')).on('click', function () {
                draft = [];
                renderPanel();
            }));
        $footer.append($('<button type="button" class="btn btn-primary btn-sm ml-auto"></button>')
            .text($panel.data('apply')).on('click', apply));
        $panel.append($footer);
    }

    function renderChips() {
        $chips.empty();
        applied.forEach(function (row, index) {
            $chips.append($('<span class="badge badge-pill badge-primary mr-1 p-2"></span>')
                .append($('<span></span>').text(describe(row)))
                .append($('<button type="button" class="btn btn-link btn-sm p-0 ml-2 text-white"></button>')
                    .attr('aria-label', $panel.data('remove') + ': ' + describe(row))
                    .html('<i class="fa fa-times" aria-hidden="true"></i>')
                    .on('click', function () {
                        applied.splice(index, 1);
                        changed();
                    })));
        });
        if (applied.length) {
            $chips.append($('<button type="button" class="btn btn-link btn-sm"></button>')
                .text($panel.data('clear')).on('click', function () {
                    applied = [];
                    changed();
                }));
        }
        $chips.prop('hidden', applied.length === 0);
        $label.text($label.data('label') + (applied.length ? ' (' + applied.length + ')' : ''));
    }

    function changed() {
        renderChips();
        $(document).trigger('finder:criteria');
    }

    function apply() {
        var complete = draft.filter(isComplete);
        if (!complete.every(isValid)) {
            // Keep the panel open: a value the server would drop must not show as an active filter.
            renderPanel();
            $panel.prepend($('<div class="alert alert-danger py-1 px-2 mb-2" role="alert"></div>').text($panel.data('invalid')));
            return;
        }
        applied = clone(complete);
        $button.dropdown('hide');
        changed();
    }

    // The request parameter the server reads: the filters in use as JSON, or nothing.
    function params() {
        if (!applied.length) {
            return [];
        }
        return [{
            name: 'criteria',
            value: JSON.stringify(applied.map(function (row) {
                return {key: row.key, operator: row.operator, value: row.value};
            }))
        }];
    }

    $(function () {
        $panel = $('#finder-filter-panel');
        $chips = $('#finder-chips');
        $button = $('#finder-filter-button');
        $label = $('#finder-filter-label');
        if (!$panel.length || !definitions.length) {
            $('#finder-filter').remove();
            return;
        }
        // Clicks inside the panel must not close it (Bootstrap closes a dropdown on any click).
        $panel.on('click', function (event) {
            event.stopPropagation();
        });
        // Bootstrap's dropdown keydown handler cancels the arrow keys, which a focused select needs.
        $panel.on('keydown', function (event) {
            if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
                event.stopPropagation();
            }
        });
        $('#finder-filter').on('show.bs.dropdown', function () {
            draft = clone(applied);
            if (!draft.length) {
                var row = newRow('', []);
                draft = row ? [row] : [];
            }
            renderPanel();
        });
        renderChips();
    });

    window.finderCriteria = {params: params};
})();
