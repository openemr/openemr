var isWin = /^win/.test(process.platform);
if( isWin ){
	var Service = require('node-windows').Service;
}
else{
	var Service = require('node-linux').Service;
}
var svc = new Service({
  name:'CCDA Service',
  script: require('path').join(__dirname,'serveccda.njs')
});

svc.on('uninstall',function(){
  console.log('Uninstall complete.');
  console.log('The service exists: ',svc.exists);
});

svc.uninstall();