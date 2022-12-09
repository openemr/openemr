<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="generator" content="HTML Tidy for Linux (vers 1 September 2005), see www.w3.org" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PostalMethods HTML Sample - http://www.postalmethods.com/</title>

<style type="text/css">
body {
    text-align: left;
    white-space: normal;
    font-family: "Times New Roman", Times, serif;
    margin:0;
    padding:0;
    height: 11in; /*Letter Size Paper*/
    width: 8.5in; /*Letter Size Paper*/
    /* margin-left: 0.5in;
    margin-right: 0.5in;
    margin-top: 0.5in;
    margin-bottom: 0.5in; */
    /*margin: 56px 48px 12px 48px;*/
}
#Addresses {
    display: none;
    position:relative;
    height:2.875in; 
    width:7.4in;
    margin-bottom:10px;
}
#ReturnAddressContainer {
    width:3.5in; 
    /*Your company logo (optional). You can change the size of the font and your company logo image to make it fit to the envelope window */
    /*height:0.792in;*/
    height:300px;
    top: 10px;
    background-color: #0ff;
    position: relative;
}
#ReturnAddress {
    position:absolute;
    font-size: 13px;
    line-height: 13px;
    z-index:100;
    bottom: 0;
    height:0.900in;
    font-family:Helvetica, sans-serif;
}
#CompanyLogo {
    position:absolute;
    left:2in;
    width:1.45in;
    height:0.792in;
    z-index:10;
    text-align:right;
}
#RecipientAddress {
    position:absolute;
    /*top:1.542in;*/
    top:1.642in;
    width:3.5in;
    height:0.875in;
    text-transform: uppercase;
    font-family:Helvetica, sans-serif;
    font-size: 13px;
    line-height: 13px;
}
#RightSideContent {
    display:none;
    position:relative;
    border:thin #000000;
    left: 4.5in;
    height: 2.8in;
    width: 2.9in;
}
#BodyContent {
    position:relative;
    left:0;
    width:7.4in;
    font-size: 13px;
    line-height: 1.5;
    white-space: normal;
    margin:0;
    padding:0;
}
.PageBreak {
    page-break-after:always; /*Using this tag as a DIV class, forces a page break when printing the letter*/
    height:1px;
    margin:0;
    padding:0;
}

.Address {
    margin-bottom: 50px;
}
.ReturnAddress {
    width:3.5in; 
    /*Your company logo (optional). You can change the size of the font and your company logo image to make it fit to the envelope window */
    height:0.750in;
    /*background-color: #00f;*/
    font-size: 13px;
    line-height: 14px;
    font-family:Helvetica, sans-serif;
}
.blankCol {
    /*background-color: #0ff;*/
    height:0.755in;
}
.RecipientAddress {
    width:3.5in;
    height:0.875in;
    font-size: 13px;
    line-height: 14px;
    /*text-transform: uppercase;*/
    font-family:Helvetica, sans-serif;
    /*background-color: #f00;*/
}
.BodyContent {
    position:relative;
    left:0;
    width:7.4in;
    font-size: 14px;
    line-height: 1.5;
    white-space: normal;
    margin:0;
    padding:0;
}
</style>
</head>
<body>
<table class="Address">
    <tr>
        <td class="ReturnAddress" valign="bottom">
            <span><?php echo isset($returnAddress) ? $returnAddress : ''; ?></span>
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" class="blankCol"></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" class="RecipientAddress" valign="bottom">
            <span><?php echo isset($recipientAddress) ? $recipientAddress : ''; ?></span>
        </td>
        <td></td>
    </tr>
</table>
<!-- Content of the letter -->
<div class="BodyContent"><?php echo isset($bodyContent) ? $bodyContent : ''; ?></div>
</body>
</html>