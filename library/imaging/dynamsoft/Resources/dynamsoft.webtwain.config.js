//
// Dynamsoft JavaScript Library for Basic Initiation of Dynamic Web TWAIN
// More info on DWT: http://www.dynamsoft.com/Products/WebTWAIN_Overview.aspx
//
// Copyright 2016, Dynamsoft Corporation 
// Author: Dynamsoft Team
// Version: 11.3
//
/// <reference path="dynamsoft.webtwain.initiate.js" />
var Dynamsoft = Dynamsoft || { WebTwainEnv: {} };

Dynamsoft.WebTwainEnv.AutoLoad = true;
///
Dynamsoft.WebTwainEnv.Containers = [ {ContainerId:'dwtcontrolContainerLargeViewer', Width: 500, Height: 700},{ContainerId:'dwtcontrolContainer', Width: 180, Height: 700}];
///
Dynamsoft.WebTwainEnv.ProductKey = 'A99E96BD66C4D3433379545D3604C125CC2CF0C3AE6AA09EA5222710DC6B2FDE9FBB0C3C5D210FB8C75F327BCA7EB04F0FF522BF4361390A16996370F87269B67A58FCA87DA68B889A4EC32528D8225DF17C4A136E7C66A199285510BFA5DF006F1A189296D8BAABE42370980BC9EC968ECB21D0D23AE97C95D72C703DA7B82A2723666E5109E35BAA6CEE5BDF4E7E46E37C2604D2944541F44AA162E893FE154117588050EA8426566B1D5A571CEAE20541F34AA71F93B3356CC9926EB314AE6B31D42FFD6144606987FDAD8B02F5C9872E1ECBC18FC15249DF335C5CEAF8C8F90E1ECF3A09A412B6E243D2684DE4DA3F9173A20BB6BF6DD39613DE65DA259D6DB147F24739CAD03C0ACB04FA672B5B20465B938C571221B056A3DEEE86CEB8114AC2EA764A562A88B43619D43442947EF605256807108ABE57B9D153E5B64C76E0FD9197E40C270566B16C05487DB0A4A5B8C0B7669672CE7EAC0C7BF2654CCCFD88C1D87B2BDF71D60D39629ABEAD8E309A69B83EF62060519A4FAA74B304F07644D3AD243514124C7F52';
///
Dynamsoft.WebTwainEnv.Trial = true;
///
Dynamsoft.WebTwainEnv.ActiveXInstallWithCAB = false;
///
Dynamsoft.WebTwainEnv.Debug = false; // only for debugger output
///
// Dynamsoft.WebTwainEnv.ResourcesPath = 'Resources';

/// All callbacks are defined in the dynamsoft.webtwain.install.js file, you can customize them.

// Dynamsoft.WebTwainEnv.RegisterEvent('OnWebTwainReady', function(){
// 		// webtwain has been inited
// });

