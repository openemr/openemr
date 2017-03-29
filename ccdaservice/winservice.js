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
    group: "root"
});

svc.on('install', function() {
	svc.start();
});
env: [ {
	name : "HOME",
	value : process.env["USERPROFILE"]
}, {
// name: "TEMP",
// value: path.join(process.env["USERPROFILE"],"/temp")
} ]
svc.on('alreadyinstalled', function() {
	console.log('This service is already installed.');
});

svc.on('start', function() {
	console.log(svc.name + ' started!\n');
});

svc.install();
