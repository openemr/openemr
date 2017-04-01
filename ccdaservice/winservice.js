/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
var isWin = /^win/.test(process.platform);
var f = __dirname +''//serveccda.njs';

if( isWin ){
	var Service = require('node-windows').Service;
	//f =  '\\xampp\\htdocs\\openemr\\services\\ccdaservice\\serveccda.njs';
}
else{
	var Service = require('node-linux').Service;
}
var svc = new Service({
	name : 'CCDA Service',
	description : 'The ccda document server.',
	script : require('path').join(__dirname,'serveccda.njs'),
	user: "root",
    group: "root",
    wait: 2,
    grow: .5
});

svc.on('install', function() {
	svc.start();
});
env: [ {
	name : "HOME",
	value : process.env["USERPROFILE"]
}, {
	//name: "TEMP",
    //value: require('path').join(process.env["USERPROFILE"],"/temp")
} ]
svc.on('alreadyinstalled', function() {
	console.log('This service is already installed.');
});

svc.on('start', function() {
	console.log(svc.name + ' started!\n');
});

svc.install();
