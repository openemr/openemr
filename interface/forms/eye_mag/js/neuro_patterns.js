/**
 * Measurement-pattern guidance, not a diagnosis or billing decision.
 * See NEURO_PATTERN_GUIDANCE.md for mappings, limitations and clinical references.
 * Pure analysis is exported for regression tests; bind() connects the current exam.
 */
(function (root, factory) {
    'use strict';
    var api = factory();
    // A browser page may expose a CommonJS-like module for another library.
    // Eye Form always consumes the window API; do not export into that object.
    if (typeof window !== 'undefined') {
        root.EyeNeuroPatterns = api;
        if (typeof root.dispatchEvent === 'function' && typeof root.Event === 'function') {
            root.dispatchEvent(new root.Event('eye-neuro-patterns-ready'));
        }
    } else if (typeof module === 'object' && module && module.exports) {
        module.exports = api;
    } else {
        root.EyeNeuroPatterns = api;
    }
})(typeof window !== 'undefined' ? window : this, function () {
    'use strict';
    var contexts = ['SCDIST', 'CCDIST', 'SCNEAR', 'CCNEAR'];
    // Patient's directions, not the examiner's: the chart labels R on the left.
    var positions = {1: 'upRight', 2: 'up', 3: 'upLeft', 4: 'rightGaze', 5: 'primary',
        6: 'leftGaze', 7: 'downRight', 8: 'down', 9: 'downLeft', 10: 'rightTilt', 11: 'leftTilt'};
    var motors = {
        OD: {adduction: 'RL', abduction: 'RR', elevation: 'RS', depression: 'RI', up: ['RRSO', 'RLSO'], down: ['RRIO', 'RLIO']},
        OS: {adduction: 'LR', abduction: 'LL', elevation: 'LS', depression: 'LI', up: ['LRSO', 'LLSO'], down: ['LRIO', 'LLIO']}
    };

    function parse(raw) {
        var original = String(raw == null ? '' : raw).trim();
        if (!original) { return null; }
        var value = original.toUpperCase().replace(/Δ|\bPD\b/g, '').trim();
        if (/^(ORTHO|ORTHOPHORIA|0(?:\.0+)?)$/.test(value)) {
            return {valid: true, h: 0, v: 0, hType: 'O', vType: 'O', raw: original};
        }
        var result = {valid: true, h: null, v: null, hType: '', vType: '', raw: original};
        var number = '(?:\\d+(?:\\.\\d+)?|\\.\\d+)';
        var side = '(?:RIGHT|LEFT|OD|OS|R|L)?';
        var deviation = '(?:HYPO\\(T\\)|HYPOT|HYPO|HYPER\\(T\\)|HYPERT|HYPER|H\\(T\\)|HT|HP|H|X\\(T\\)|XT|XP|X|E\\(T\\)|ET|EP|E)';
        // Accept Quick Pick "12 LHT", compact "12LHT", and conventional "LHT 12".
        var token = new RegExp('^(?:(' + number + ')\\s*(' + side + ')\\s*(' + deviation + ')|(' + side + ')\\s*(' + deviation + ')\\s*(' + number + '))');
        while (value) {
            var match = value.match(token);
            if (!match) { return {valid: false, reason: 'unparsed', raw: original}; }
            var magnitude = Number(match[1] || match[6]);
            var laterality = match[2] || match[4] || '';
            var kind = match[3] || match[5];
            var axis = /^[EX]/.test(kind) ? 'h' : 'v';
            if (result[axis] !== null || !Number.isFinite(magnitude)) {
                return {valid: false, reason: 'ambiguous', raw: original};
            }
            // T = constant tropia, I = intermittent "(T)", P = phoria, '' = unspecified.
            result[axis + 'Type'] = /\(T\)$/.test(kind) ? 'I' : /T$/.test(kind) ? 'T' : /P$/.test(kind) ? 'P' : '';
            if (axis === 'h') {
                result.h = (kind.charAt(0) === 'X' ? 1 : -1) * magnitude;
            } else {
                if (!laterality && magnitude !== 0) {
                    return {valid: false, reason: 'laterality', raw: original};
                }
                var right = /^(R|RIGHT|OD)$/.test(laterality);
                result.v = (right ? 1 : -1) * (kind.indexOf('HYPO') === 0 ? -1 : 1) * magnitude;
            }
            value = value.slice(match[0].length);
            if (value && !/^(\s|[,;/+&])/.test(value)) {
                return {valid: false, reason: 'unparsed', raw: original};
            }
            var remainder = value.replace(/^(?:\s|[,;/+&]|\bAND\b)+/, '');
            if (!remainder && value.trim()) { return {valid: false, reason: 'unparsed', raw: original}; }
            value = remainder;
        }
        return result;
    }

    function analyze(values) {
        values = values || {};
        var findings = [], parsed = {}, invalid = [];
        function get(field, axis) {
            if (!Object.prototype.hasOwnProperty.call(parsed, field)) { parsed[field] = parse(values[field]); }
            var item = parsed[field];
            return item && item.valid ? item[axis] : null;
        }
        function add(code, context, eye, fields, data, missing) {
            findings.push({code: code, context: context || '', eye: eye || '', data: data || {},
                evidence: (fields || []).filter(function (field) { return String(values[field] == null ? '' : values[field]).trim() !== ''; })
                    .map(function (field) { return {field: field, value: String(values[field])}; }), missing: missing || []});
        }
        function grade(suffix) {
            var raw = String(values['MOTILITY_' + suffix] == null ? '' : values['MOTILITY_' + suffix]).trim();
            return /^[0-4]$/.test(raw) ? Number(raw) : null;
        }
        function verticalMotor(map, direction) {
            var direct = grade(map[direction]);
            var diagonals = map[direction === 'elevation' ? 'up' : 'down'].map(grade);
            if (direct > 0) { return direct; }
            if (diagonals[0] > 0 && diagonals[1] > 0) { return Math.min(diagonals[0], diagonals[1]); }
            return direct;
        }
        var normalConflict = !!values.MOTILITYNORMAL && Object.keys(motors).some(function (eye) {
            var map = motors[eye];
            return [map.adduction, map.abduction, map.elevation, map.depression].concat(map.up, map.down)
                .some(function (field) { return grade(field) > 0; });
        });
        if (normalConflict) { add('normalConflict'); }

        function item(field) {
            get(field, 'h');
            return parsed[field] && parsed[field].valid ? parsed[field] : null;
        }
        function hasValue(field) { return String(values[field] == null ? '' : values[field]).trim() !== ''; }

        contexts.forEach(function (context) {
            function act(position) { return 'ACT' + position + context; }
            function h(position) { return get(act(position), 'h'); }
            function v(position) { return get(act(position), 'v'); }
            var fields = Object.keys(positions).map(act).concat(['SPCT_' + context]);
            fields.forEach(function (field) {
                get(field, 'h');
                if (parsed[field] && !parsed[field].valid) { invalid.push(field); }
            });

            // Start each context with what primary position shows, so pattern checks read
            // in context instead of leading with whichever check happens to be incomplete.
            var entered = Object.keys(positions).map(act).filter(function (field) { return item(field); });
            var primaryItem = item(act(5));
            if (entered.length >= 3 && entered.every(function (field) { return item(field).hType === 'O'; })) {
                add('orthoAll', context, '', entered);
            } else if (primaryItem) {
                if (primaryItem.hType === 'O') {
                    add('primaryOrtho', context, '', [act(5)]);
                } else {
                    var hDesc = primaryItem.h === null || primaryItem.h === 0 ? ''
                        : (primaryItem.h > 0 ? 'X' : 'E') + (primaryItem.hType || '');
                    var vDesc = primaryItem.v === null || primaryItem.v === 0 ? ''
                        : (primaryItem.v > 0 ? 'R' : 'L') + 'H' + (primaryItem.vType || '');
                    if (hDesc || vDesc) {
                        add('primaryDeviation', context, '', [act(5)], {
                            hSize: hDesc ? Math.abs(primaryItem.h) : '', hKind: hDesc ? 'kind_' + hDesc : '',
                            vSize: vDesc ? Math.abs(primaryItem.v) : '', vKind: vDesc ? 'kind_' + vDesc : ''
                        });
                    }
                }
            }
            ['h', 'v'].forEach(function (axis) {
                var a = get(act(5), axis), s = get('SPCT_' + context, axis);
                if (a === null || s === null) { return; }
                var evidence = ['SPCT_' + context, act(5)];
                if (a * s < 0 || Math.abs(a) < Math.abs(s)) {
                    add('spctConflict', context, '', evidence, {axis: axis});
                } else {
                    add('dissociated', context, '', evidence, {axis: axis, delta: +(Math.abs(a) - Math.abs(s)).toFixed(2)});
                }
            });
            if (h(2) !== null && h(8) !== null) {
                var difference = +(h(2) - h(8)).toFixed(2);
                if (difference >= 15) { add('vPattern', context, '', [act(2), act(8)], {delta: difference}); }
                else if (difference <= -10) { add('aPattern', context, '', [act(2), act(8)], {delta: -difference}); }
                // Where the change happens, relative to primary, distinguishes Y/λ/X features.
                if (h(5) !== null) {
                    var upChange = +(h(2) - h(5)).toFixed(2), downChange = +(h(8) - h(5)).toFixed(2);
                    var avFields = [act(2), act(5), act(8)];
                    if (upChange >= 10 && downChange >= 10) {
                        add('xPattern', context, '', avFields, {up: upChange, down: downChange});
                    } else if (difference >= 15 && Math.abs(downChange) < 5) {
                        add('yFeature', context, '', avFields, {delta: upChange});
                    } else if (difference <= -10 && Math.abs(upChange) < 5) {
                        add('lambdaFeature', context, '', avFields, {delta: downChange});
                    }
                }
            } else if (h(2) !== null || h(8) !== null) {
                add('avIncomplete', context, '', [act(2), act(8)], {}, [act(h(2) === null ? 2 : 8)]);
            }

            if (h(5) !== null && h(5) < 0 && (h(4) === null || h(6) === null)) {
                add('horizontalGazeIncomplete', context, '', [act(5), act(4), act(6)], {},
                    [act(4), act(6)].filter(function (field) { return get(field, 'h') === null; }));
            }

            var primary = v(5);
            var verticalFields = [5, 4, 6, 10, 11].map(act);
            if (primary !== null && primary !== 0) {
                var eye = primary > 0 ? 'OD' : 'OS';
                var sign = primary > 0 ? 1 : -1;
                var missing = verticalFields.filter(function (field) { return get(field, 'v') === null; });
                // No minimum size: a long-standing IV palsy can spread to comitance and leave
                // only a small, similar vertical in every position.
                var fusional = hasValue('VERTFUSAMPS') ? ['VERTFUSAMPS'] : [];
                // Report each step that can be completed, so partial data is still useful.
                var compare = function (a, b, aKey, bKey, sameKey) {
                    if (a === null || b === null) { return 'stepPending'; }
                    if (Math.abs(a - b) <= 2) { return sameKey; }
                    return a > b ? aKey : bKey;
                };
                var gazeStep = compare(v(6) === null ? null : v(6) * sign, v(4) === null ? null : v(4) * sign,
                    'greaterLeftGaze', 'greaterRightGaze', 'similarGaze');
                var tiltStep = compare(v(10) === null ? null : v(10) * sign, v(11) === null ? null : v(11) * sign,
                    'greaterRightTilt', 'greaterLeftTilt', 'similarTilt');
                var comitant = !missing.length && verticalFields.every(function (field) {
                    return get(field, 'v') * sign > 0 && Math.abs(get(field, 'v') - primary) <= 2;
                });
                if (missing.length) {
                    add('ivIncomplete', context, eye, verticalFields.concat(fusional),
                        {step2: gazeStep, step3: tiltStep}, missing);
                } else if (verticalFields.some(function (field) { return get(field, 'v') * sign < 0; })) {
                    // Right hyper in left gaze and right tilt, left hyper in right gaze and left tilt.
                    if (v(6) > 0 && v(10) > 0 && v(4) < 0 && v(11) < 0) {
                        add('bilateralIV', context, '', verticalFields.concat(fusional));
                    } else {
                        add('alternatingVertical', context, '', verticalFields);
                    }
                } else if (comitant) {
                    add('comitantVertical', context, eye, verticalFields.concat(fusional), {size: Math.abs(primary)});
                } else {
                    var contraGaze = v(eye === 'OD' ? 6 : 4) * sign;
                    var ipsiGaze = v(eye === 'OD' ? 4 : 6) * sign;
                    var ipsiTilt = v(eye === 'OD' ? 10 : 11) * sign;
                    var contraTilt = v(eye === 'OD' ? 11 : 10) * sign;
                    if (contraGaze > ipsiGaze && ipsiTilt > contraTilt) {
                        add('ivPattern', context, eye, verticalFields);
                    } else {
                        add('verticalNonlocalizing', context, eye, verticalFields);
                    }
                }
            } else if (primary === null && fields.some(function (field) { return get(field, 'v') !== null && get(field, 'v') !== 0; })) {
                add('verticalPrimaryMissing', context, '', fields.filter(function (field) { return get(field, 'v') !== null; }), {}, [act(5)]);
            }

            ['OD', 'OS'].forEach(function (eye) {
                var map = motors[eye], sign = eye === 'OD' ? 1 : -1;
                var toward = eye === 'OD' ? 4 : 6, away = eye === 'OD' ? 6 : 4;
                var prismFields = [act(5), act(toward), act(away)];
                if (h(5) < 0 && h(5) !== null && h(toward) !== null && h(away) !== null
                    && h(toward) < h(5) && h(toward) < h(away)) {
                    var motorFields = ['MOTILITY_' + map.abduction, 'MOTILITY_' + map.adduction];
                    if (!normalConflict && grade(map.abduction) > 0) {
                        if (grade(map.adduction) > 0 || verticalMotor(map, 'elevation') > 0 || verticalMotor(map, 'depression') > 0) {
                            add('viMixed', context, eye, prismFields.concat(motorFields));
                        } else {
                            add('viPattern', context, eye, prismFields.concat(motorFields));
                        }
                    } else if (grade(map.abduction) === 0 || normalConflict) {
                        add('viNoLimitation', context, eye, prismFields.concat(motorFields));
                    } else {
                        add('viCheckMotility', context, eye, prismFields, {}, ['MOTILITY_' + map.abduction]);
                    }
                }
                // Relative vertical deviation is only a screening clue. It cannot
                // establish monocular overelevation or grade IO overaction.
                var adductedUp = eye === 'OD' ? 3 : 1;
                var abductedUp = eye === 'OD' ? 1 : 3;
                var sideGaze = eye === 'OD' ? 6 : 4;
                if (primary !== null && v(adductedUp) !== null && v(abductedUp) !== null && v(sideGaze) !== null
                    && v(adductedUp) * sign > Math.max(0, primary * sign)
                    && v(adductedUp) * sign > v(abductedUp) * sign
                    && v(adductedUp) * sign > v(sideGaze) * sign) {
                    add('ioScreen', context, eye, [act(5), act(adductedUp), act(abductedUp), act(sideGaze)]);
                }
                // Mirror screen for superior-oblique overaction: relative hypodeviation of
                // this eye in down-adduction.
                var adductedDown = eye === 'OD' ? 9 : 7;
                var abductedDown = eye === 'OD' ? 7 : 9;
                if (primary !== null && v(adductedDown) !== null && v(abductedDown) !== null && v(sideGaze) !== null
                    && -v(adductedDown) * sign > Math.max(0, -primary * sign)
                    && -v(adductedDown) * sign > -v(abductedDown) * sign
                    && -v(adductedDown) * sign > -v(sideGaze) * sign) {
                    add('soScreen', context, eye, [act(5), act(adductedDown), act(abductedDown), act(sideGaze)]);
                }
            });

            var sideFields = [act(4), act(5), act(6)];
            var viFound = findings.some(function (finding) { return finding.context === context && /^vi/.test(finding.code); });
            if (h(4) !== null && h(6) !== null && !viFound) {
                var lateral = +Math.abs(h(4) - h(6)).toFixed(2);
                if (lateral >= 5) {
                    add('lateralIncomitance', context, '', sideFields,
                        {delta: lateral, larger: Math.abs(h(4)) > Math.abs(h(6)) ? 'rightGaze' : 'leftGaze'});
                }
                if (h(5) !== null && h(5) > 0 && h(4) <= h(5) - 5 && h(6) <= h(5) - 5) {
                    add('sideGazeReduction', context, '', sideFields);
                }
            }
            var horizontalFields = [2, 4, 5, 6, 8].map(act).filter(function (field) { return get(field, 'h') !== null; });
            if (horizontalFields.length >= 4) {
                var hValues = horizontalFields.map(function (field) { return get(field, 'h'); });
                var spread = Math.max.apply(null, hValues) - Math.min.apply(null, hValues);
                if (spread < 5 && hValues.some(function (value) { return value !== 0; })) {
                    add('comitantHorizontal', context, '', horizontalFields);
                }
            }
        });

        ['CC', 'SC'].forEach(function (correction) {
            var distanceField = 'ACT5' + correction + 'DIST', nearField = 'ACT5' + correction + 'NEAR';
            var distance = get(distanceField, 'h'), near = get(nearField, 'h');
            if (distance === null || near === null || distance * near <= 0) { return; }
            var delta = +(Math.abs(near) - Math.abs(distance)).toFixed(2);
            var code = distance > 0 ? (delta >= 10 ? 'nearExo' : delta <= -10 ? 'distanceExo' : 'basicExo')
                : (delta >= 10 ? 'nearEso' : delta <= -10 ? 'distanceEso' : 'basicEso');
            var support = code === 'nearExo' ? ['NPC', 'CACCNEAR'] : [];
            add(code, correction, '', [distanceField, nearField].concat(support), {delta: Math.abs(delta)});
        });

        // Remote near point of convergence (CITT criterion: break at 6 cm or more).
        var npc = String(values.NPC == null ? '' : values.NPC).trim().match(/^(\d+(?:\.\d+)?)\s*(?:cm)?$/i);
        if (npc && Number(npc[1]) >= 6) { add('npcRemote', '', '', ['NPC', 'CACCNEAR'], {value: Number(npc[1])}); }

        // Effect of correction, compared at the same distance only.
        var corrected = [];
        ['DIST', 'NEAR'].forEach(function (distance) {
            var scField = 'ACT5SC' + distance, ccField = 'ACT5CC' + distance;
            var sc = get(scField, 'h'), cc = get(ccField, 'h');
            if (sc === null || cc === null) { return; }
            var change = +(cc - sc).toFixed(2);
            corrected.push({distance: distance, sc: sc, cc: cc, change: change, fields: [scField, ccField]});
            if (Math.abs(change) >= 5 || (cc === 0 && sc !== 0)) {
                add('correctionChange', distance, '', [scField, ccField],
                    {sc: values[scField], cc: values[ccField], delta: Math.abs(change)});
            }
            if (sc > 0 && change >= 10) { add('exoLargerWithCorrection', distance, '', [scField, ccField], {delta: change}); }
            if (sc > 0 && change <= -10) { add('exoSmallerWithCorrection', distance, '', [scField, ccField], {delta: -change}); }
        });
        var esoSc = corrected.filter(function (entry) { return entry.sc < 0; });
        var esoFields = [].concat.apply([], esoSc.map(function (entry) { return entry.fields; }));
        if (esoSc.some(function (entry) { return entry.change >= 10; })) {
            if (esoSc.every(function (entry) { return entry.cc > -10; })) {
                add('refractiveAccommodative', '', '', esoFields);
            } else {
                add('partiallyAccommodative', '', '', esoFields);
            }
            if (esoSc.some(function (entry) { return entry.cc >= 10; })) { add('correctionReverses', '', '', esoFields); }
        } else if (esoSc.some(function (entry) { return entry.sc <= -10; })) {
            add('nonAccommodativeEso', '', '', esoFields);
        }
        // Distance controlled with correction, near still eso: high AC/A-type pattern.
        var ccDistance = get('ACT5CCDIST', 'h'), ccNear = get('ACT5CCNEAR', 'h');
        if (ccDistance !== null && ccNear !== null && ccDistance > -10 && ccNear < 0 && ccDistance - ccNear >= 10) {
            add('accommodativeNear', '', '', ['ACT5CCDIST', 'ACT5CCNEAR'], {delta: +(ccDistance - ccNear).toFixed(2)});
        }

        // Small constant tropia with reduced but present stereopsis: monofixation pattern.
        var smallTropia = contexts.map(function (context) { return 'ACT5' + context; }).find(function (field) {
            var entry = item(field);
            return entry && entry.hType === 'T' && Math.abs(entry.h) > 0 && Math.abs(entry.h) <= 8;
        });
        if (smallTropia) {
            var circles = String(values.STEREO_CIRCLES == null ? '' : values.STEREO_CIRCLES).trim().match(/^(\d)(?:\s*\/\s*9)?$/);
            var stereoFields = [smallTropia, 'STEREO_FLY', 'STEREO_ANIMALS', 'STEREO_CIRCLES'];
            if (values.STEREO_FLY === 'Yes' && circles && Number(circles[1]) < 9) {
                add('monofixation', '', '', stereoFields);
            } else if (!hasValue('STEREO_FLY') && !hasValue('STEREO_CIRCLES')) {
                add('smallTropiaStereo', '', '', [smallTropia], {}, ['STEREO_FLY', 'STEREO_CIRCLES']);
            }
        }
        ['OD', 'OS'].forEach(function (eye) {
            var map = motors[eye];
            var fields = [map.adduction, map.abduction, map.elevation, map.depression].concat(map.up, map.down)
                .map(function (field) { return 'MOTILITY_' + field; });
            if (normalConflict) { return; }
            var abd = grade(map.abduction), addu = grade(map.adduction);
            var elev = verticalMotor(map, 'elevation'), dep = verticalMotor(map, 'depression');
            if (addu > 0 && elev > 0 && dep > 0) {
                if (abd === 0) { add('iiiPattern', '', eye, fields); }
                else if (abd === null) { add('iiiIncomplete', '', eye, fields, {}, ['MOTILITY_' + map.abduction]); }
                else { add('globalRestriction', '', eye, fields); }
                return;
            }
            // Chart limbs: OD up-left / down-left and OS up-right / down-right are adducted.
            var upIn = grade(eye === 'OD' ? 'RLSO' : 'LRSO'), upOut = grade(eye === 'OD' ? 'RRSO' : 'LLSO');
            var downIn = grade(eye === 'OD' ? 'RLIO' : 'LRIO'), downOut = grade(eye === 'OD' ? 'RRIO' : 'LLIO');
            var vertical = elev > 0 || dep > 0 || upIn > 0 || downIn > 0;
            if (abd > 0 && addu > 0 && !vertical) {
                add('duane3Screen', '', eye, fields);
            } else if (abd > 0 && !vertical) {
                // Little primary esodeviation for the limitation favours Duane over VI palsy.
                var primaryH = ['ACT5CCDIST', 'ACT5SCDIST'].map(function (field) { return get(field, 'h'); })
                    .find(function (value) { return value !== null; });
                if (primaryH !== undefined && primaryH > -10) {
                    add('duaneScreen', '', eye, fields.concat(['ACT5CCDIST', 'ACT5SCDIST']));
                }
            } else if (addu > 0 && !vertical) {
                add('adductionDeficit', '', eye, fields);
            }
            if (addu > 0) { return; }
            if (upIn > 0 && upOut === 0) {
                add('brownScreen', '', eye, fields);
            } else if (elev > 0 && dep > 0) {
                add('verticalRestriction', '', eye, fields);
            } else if (elev > 0 && !(dep > 0)) {
                add('elevationDeficit', '', eye, fields);
            }
            if (downIn > 0 && downOut === 0) {
                add('depressionInAdduction', '', eye, fields);
            } else if (dep > 0 && !(elev > 0)) {
                add('depressionDeficit', '', eye, fields);
            }
        });
        if (!normalConflict && grade('RR') > 0 && grade('LL') > 0
            && ['ACT5CCDIST', 'ACT5SCDIST'].some(function (field) { return get(field, 'h') < 0; })) {
            add('bilateralAbduction', '', '', ['MOTILITY_RR', 'MOTILITY_LL', 'ACT5CCDIST', 'ACT5SCDIST']);
        }
        if (invalid.length) { add('unparsed', '', '', invalid); }
        return findings;
    }

    function bind($) {
        var strings = window.eyeNeuroPatternStrings || {};
        function t(key) { return strings[key] || key; }
        function fieldLabel(field) {
            var match = field.match(/^ACT(\d+)(SC|CC)(DIST|NEAR)$/);
            if (match) { return t(match[2] + match[3]) + ' ' + t(positions[match[1]]); }
            match = field.match(/^SPCT_(SCDIST|CCDIST|SCNEAR|CCNEAR)$/);
            if (match) { return 'SPCT ' + t(match[1]); }
            var motorLabels = {
                RR: ['OD', 'abduction'], RL: ['OD', 'adduction'], RS: ['OD', 'up'], RI: ['OD', 'down'],
                RRSO: ['OD', 'upRight'], RLSO: ['OD', 'upLeft'], RRIO: ['OD', 'downRight'], RLIO: ['OD', 'downLeft'],
                LR: ['OS', 'adduction'], LL: ['OS', 'abduction'], LS: ['OS', 'up'], LI: ['OS', 'down'],
                LRSO: ['OS', 'upRight'], LLSO: ['OS', 'upLeft'], LRIO: ['OS', 'downRight'], LLIO: ['OS', 'downLeft']
            };
            var label = motorLabels[field.replace(/^MOTILITY_/, '')];
            if (label) { return t(label[0]) + ' ' + t(label[1]) + ' ' + t('limitation'); }
            return strings['field_' + field] || field;
        }
        // Order findings the way the deviation is worked up; completeness notes last.
        var groupOf = {};
        [
            ['group_conflict', ['normalConflict']],
            ['group_primary', ['orthoAll', 'primaryOrtho', 'primaryDeviation', 'comitantHorizontal']],
            ['group_distanceNear', ['nearExo', 'distanceExo', 'basicExo', 'nearEso', 'distanceEso', 'basicEso', 'npcRemote']],
            ['group_correction', ['correctionChange', 'refractiveAccommodative', 'partiallyAccommodative', 'accommodativeNear',
                'nonAccommodativeEso', 'correctionReverses', 'exoLargerWithCorrection', 'exoSmallerWithCorrection']],
            ['group_incomitance', ['vPattern', 'aPattern', 'xPattern', 'yFeature', 'lambdaFeature', 'lateralIncomitance',
                'sideGazeReduction', 'avIncomplete']],
            ['group_vertical', ['ivPattern', 'bilateralIV', 'comitantVertical', 'alternatingVertical', 'verticalNonlocalizing',
                'ivIncomplete', 'verticalPrimaryMissing', 'ioScreen', 'soScreen']],
            ['group_motility', ['viPattern', 'viMixed', 'viCheckMotility', 'viNoLimitation', 'horizontalGazeIncomplete',
                'iiiPattern', 'iiiIncomplete', 'globalRestriction', 'duaneScreen', 'duane3Screen', 'adductionDeficit',
                'bilateralAbduction', 'brownScreen', 'elevationDeficit', 'depressionInAdduction', 'depressionDeficit',
                'verticalRestriction']],
            ['group_sensory', ['dissociated', 'spctConflict', 'monofixation', 'smallTropiaStereo']],
            ['group_data', ['unparsed']]
        ].forEach(function (group, index) {
            group[1].forEach(function (code) { groupOf[code] = {key: group[0], index: index}; });
        });
        var translated = ['axis', 'step2', 'step3', 'larger', 'hKind', 'vKind'];
        function update() {
            var values = {};
            $('#NEURO_1 textarea.ACT[id^="ACT"], #NEURO_1 input[id^="SPCT_"], #NEURO_1 input[id^="MOTILITY_"]').each(function () {
                values[this.id] = $(this).val();
            });
            $('#NPC, #VERTFUSAMPS, #CACCDIST, #CACCNEAR, #DACCDIST, #DACCNEAR, #STEREO_ANIMALS, #STEREO_CIRCLES').each(function () {
                values[this.id] = $(this).val();
            });
            values.STEREO_FLY = $('#STEREO_FLY_YES').prop('checked') ? 'Yes' : ($('#STEREO_FLY_NO').prop('checked') ? 'No' : '');
            values.MOTILITYNORMAL = $('#MOTILITYNORMAL').prop('checked');
            var findings = analyze(values).map(function (finding, order) {
                return {finding: finding, order: order, group: groupOf[finding.code] || {key: 'group_data', index: 99}};
            }).sort(function (a, b) { return a.group.index - b.group.index || a.order - b.order; });
            var target = $('#NEURO_PATTERN_ANALYSIS .neuro-pattern-guidance').empty();
            if (!findings.length) { target.append($('<div class="guidance-note"></div>').text(t('empty'))); }
            var list = $('<ul></ul>'), heading = '';
            findings.forEach(function (entry) {
                var finding = entry.finding;
                if (entry.group.key !== heading) {
                    heading = entry.group.key;
                    list.append($('<li class="guidance-heading"></li>').text(t(heading)));
                }
                var data = Object.assign({}, finding.data, {eye: t(finding.eye)});
                translated.forEach(function (key) { if (data[key]) { data[key] = t(data[key]); } });
                if (finding.code === 'primaryDeviation') {
                    data.desc = [data.hKind ? data.hSize + ' ' + data.hKind : '', data.vKind ? data.vSize + ' ' + data.vKind : '']
                        .filter(Boolean).join('; ');
                }
                var message = t(finding.code).replace(/\{(\w+)\}/g, function (_match, key) { return data[key] == null ? '' : data[key]; });
                var prefix = finding.context ? t(finding.context) + ': ' : '';
                var li = $('<li></li>').text(prefix + message);
                if (finding.evidence.length) {
                    li.append($('<div class="guidance-evidence"></div>').text(finding.evidence.map(function (item) {
                        return fieldLabel(item.field) + ' = ' + item.value;
                    }).join('; ')));
                }
                // An entered box that lacks the needed component is not "missing".
                var notEntered = finding.missing.filter(function (field) { return String(values[field] == null ? '' : values[field]).trim() === ''; });
                var noComponent = finding.missing.filter(function (field) { return notEntered.indexOf(field) === -1; });
                if (notEntered.length) {
                    li.append($('<div class="guidance-note"></div>').text(t('notEntered') + ' ' + notEntered.map(fieldLabel).join(', ')));
                }
                if (noComponent.length) {
                    li.append($('<div class="guidance-note"></div>').text(t('noComponent') + ' ' + noComponent.map(fieldLabel).join(', ')));
                }
                list.append(li);
            });
            if (findings.length) { target.append(list); }
            target.append($('<div class="guidance-note"></div>').text(t('disclaimer')));
        }
        // These controls are not all descendants of Quick Picks. Delegate from
        // the whole form so ACT input and Quick Pick Record both refresh guidance.
        $(document).on('input.eyeNeuroPatterns change.eyeNeuroPatterns',
            '#NEURO_1 textarea.ACT[id^="ACT"], #NEURO_1 input[id^="SPCT_"], #NEURO_1 input[id^="MOTILITY_"], #MOTILITYNORMAL, '
            + '#NPC, #VERTFUSAMPS, #CACCDIST, #CACCNEAR, #DACCDIST, #DACCNEAR, #STEREO_ANIMALS, #STEREO_CIRCLES, '
            + 'input[name="STEREO_FLY"]', update);
        $(document).on('click.eyeNeuroPatterns', '#NEURO_MOTILITY', function () { window.setTimeout(update, 0); });
        Object.keys(positions).forEach(function (position) {
            $('#NEURO_field_' + position + ', #NEURO_1 textarea[id^="ACT' + position + '"]').filter(function () {
                return this.id === 'NEURO_field_' + position || new RegExp('^ACT' + position + '(SC|CC)(DIST|NEAR)$').test(this.id);
            }).attr({'title': t(positions[position]), 'data-original-title': t(positions[position]), 'aria-label': t(positions[position])});
        });
        update();
        return update;
    }
    return {parse: parse, analyze: analyze, bind: bind, positions: positions, contexts: contexts};
});
