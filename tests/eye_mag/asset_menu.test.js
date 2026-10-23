'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const links = JSON.parse(execFileSync('php', [path.join(__dirname, 'asset_menu_markup.php')], {encoding:'utf8'}));

for (const link of links) {
    for (const mode of ['framed','fullscreen','no-session-helper']) {
        test(link.menu + ' opens once: ' + mode + ', quoted title=' + link.quoted, () => {
            const calls = [], restores = [];
            const context = {top:{},window:{},dlgopen:(...args) => calls.push(args)};
            if (mode === 'framed') context.top.restoreSession = () => restores.push('top');
            if (mode === 'fullscreen') context.window.restoreSession = () => restores.push('window');
            vm.createContext(context);
            const result = vm.runInContext('(function(){' + link.onclick + '})()', context);
            assert.equal(result, false);
            assert.equal(calls.length, 1);
            const signature = link.menu === 'My_Signature';
            const url = new URL(calls[0][0], 'https://fixture.invalid');
            assert.equal(url.pathname, '/openemr/interface/forms/eye_mag/asset_manager.php');
            assert.equal(url.searchParams.get('asset'), signature ? 'signature' : 'logo');
            assert.equal(url.searchParams.get('provider_id'), signature ? '17' : null);
            assert.equal(calls[0][1], signature ? 'eye_signature' : 'eye_practice_logo');
            assert.equal(calls[0][5], link.quoted ? 'Practice "Logo" & Provider\'s Signature' : signature ? 'My Signature' : 'Practice Logo');
            assert.deepEqual(restores, mode === 'framed' ? ['top'] : mode === 'fullscreen' ? ['window'] : []);
        });
    }
}
