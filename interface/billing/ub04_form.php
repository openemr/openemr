<?php
/**
 * UB04 Claims Form
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */
/* $isAuthorized tells us if the form is for user UI or claim processing and provides another security check */
if ($isAuthorized !== true) {
    require_once("./ub04_dispose.php");
    $isAuthorized = 0;
    $pid = $_REQUEST['pid'] ? $_REQUEST['pid'] : '0';
    $encounter = $_REQUEST['enc'] ? $_REQUEST['enc'] : '0';
    $action = $_REQUEST['action'] ? $_REQUEST['action'] : false;
    $payerid = $_REQUEST['id'] ? $_REQUEST['id'] : '0';
    $imgurl = $GLOBALS['images_static_relative'];
    if ($action == 'payer_defaults') {
        $ub04id = get_payer_defaults($payerid);
    } elseif ($pid && $encounter) {
        $ub04id = json_encode(get_ub04_array($pid, $encounter));
    } else {
        exit(xlt("Sorry! Not Authorized."));
    }
} else {
    $imgurl = "../../../../public/images";
}

?>
<!DOCTYPE html >
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta charset="utf-8" />
<?php if ($isAuthorized !== true) {?>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
 <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
 <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-12-1/themes/base/jquery-ui.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">
<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/emodal-1-2-65/dist/eModal.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-12-1/jquery-ui.min.js"></script>

<script type="text/javascript">
$( document ).ready(function() {
    $( "[title*='DATE']" ).datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php echo(",validateOnBlur: false, formatDate: 'mdy', format: 'mdy'") ?>
    });

    $(".user").on( 'dblclick', function( event ) {
        event.preventDefault();
        selectUser(this.id,event);
    });
    $("#dismissHelp").click(function(e){
        e.preventDefault();
        $('#formHelp').toggle();
    });

    $( function() {
        var cache = {};
        $( "[title*='REVENUE CODE']" ).autocomplete({
          minLength: 1,
          source: function( request, response ) {
            var term = request.term;
            request.code_group = "revenue_code";
            if ( term in cache ) {
              response( cache[ term ] );
              return;
            }
            $.getJSON( "./ub04_helpers.php", request, function( data, status, xhr ) {
              cache[ term ] = data;
              response( data );
            });
          }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
      });
    $( function() {
        var cache = {};
        $( "[title*='OCCURRENCE CODE']" ).autocomplete({
            minLength: 1,
            source: function( request, response ) {
              var term = request.term;
              request.code_group = "occurrence_code";
              if ( term in cache ) {
                response( cache[ term ] );
                return;
              }
              $.getJSON( "./ub04_helpers.php", request, function( data, status, xhr ) {
                cache[ term ] = data;
                response( data );
              });
            }
          }).dblclick(function(event) {
              $(this).autocomplete('search'," ");
          });
    });
    $( function() {
        var cache = {};
        $( "[title*='OCCURRENCE SPAN CODE']" ).autocomplete({
            minLength: 1,
            source: function( request, response ) {
              var term = request.term;
              request.code_group = "occurrence_span_code";
              if ( term in cache ) {
                response( cache[ term ] );
                return;
              }
              $.getJSON( "./ub04_helpers.php", request, function( data, status, xhr ) {
                cache[ term ] = data;
                response( data );
              });
            }
          }).dblclick(function(event) {
              $(this).autocomplete('search'," ");
          });
    });
    $( function() {
        var cache = {};
        $( "[title*='VALUE CODE']" ).autocomplete({
          minLength: 1,
          source: function( request, response ) {
            var term = request.term;
            request.code_group = "value_codes";
            if ( term in cache ) {
              response( cache[ term ] );
              return;
            }
            $.getJSON( "./ub04_helpers.php", request, function( data, status, xhr ) {
              cache[ term ] = data;
              response( data );
            });
          }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
      });
    $( function() {
        var cache = {};
        $( "[title*='CONDITION CODE']" ).autocomplete({
          minLength: 1,
          source: function( request, response ) {
            var term = request.term;
            request.code_group = "condition_code";
            if ( term in cache ) {
              response( cache[ term ] );
              return;
            }
            $.getJSON( "./ub04_helpers.php", request, function( data, status, xhr ) {
              cache[ term ] = data;
              response( data );
            });
          }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
      });
    $( function() {
        var cache = {};
        $( "[title*='STATUS']" ).autocomplete({
          minLength: 1,
          source: function( request, response ) {
            var term = request.term;
            request.code_group = "patient_status_code";
            if ( term in cache ) {
              response( cache[ term ] );
              return;
            }
            $.getJSON( "./ub04_helpers.php", request, function( data, status, xhr ) {
              cache[ term ] = data;
              response( data );
            });
          }
        }).dblclick(function(event) {
            $(this).autocomplete('search'," ");
        });
      });

    $('form :input').change(function(e){
           formChanged = true;
           $(this).css("color","red");
           return false;
    });
});
<?php } else {?>
<script type="text/javascript">
<?php } ?>
var pid;
var encounter;
var align = true;
var isTemplate;
var ub04id = new Array();
payerid = '<?php echo attr($payerid);?>';
pid = <?php echo attr($pid);?>;
encounter = <?php echo attr($encounter);?>;
isTemplate = <?php echo attr(($isAuthorized === true ? $isAuthorized : 0)); ?>;
ub04id = <?php echo $ub04id;?>;

function adjustForm()
{
    // cycle trough the input fields
    // rem form index i is not same as formid index ii
    var inputs = Array.prototype.slice.call(document.forms[0])

    Array.prototype.forEach.call(inputs, function(el, i) {
        var type = el.getAttribute('type');
        var ta = window.getComputedStyle(el,null).getPropertyValue("text-align");
        var title = el.getAttribute('title')
        var max = el.getAttribute('maxlength');
        var w = el.clientWidth
        var size = w;
            if( ta == 'right' ){
                if(title.indexOf("CHARGES") !== -1){
                     w = size - 14;
                     if(!isTemplate){ w = size - 8; }
                }
                else{
                    w = size - 8;
                    if(!isTemplate){ w = size - 4; }
                }
                el.style.width = w + 'px'
            }
        el.style.top = (el.offsetTop+3)+'px'
        if( ta == 'left' )
            el.style.left = (el.offsetLeft+2)+'px'
        var ii = i+1;
        if(typeof(ub04id[ii]) != 'undefined' && ub04id[ii] != ""){
            var val = ub04id[ii]
            if( val.length > max && max > 0 ){
                val = ub04id[ii].substring(0,max);
            }
            document.getElementById("ub04id"+ ii.toString()).value = val
            return;
        }
 });
 return false;
}
<?php if ($isAuthorized !== true) {?>
var formChanged = false;

function rewrite(ub04id){
    var inputs = Array.prototype.slice.call(document.forms[0])
    Array.prototype.forEach.call(inputs, function(el, i) {
        var max = el.getAttribute('maxlength');
        var ii = i+1;
        if(typeof(ub04id[ii]) != 'undefined'){
            var val = ub04id[ii]
            if( val.length > max && max > 0 ){
                val = ub04id[ii].substring(0,max);
            }
            document.getElementById("ub04id"+ ii.toString()).value = val
            return;
        }
    });
}

function cleanUp()
{
    if(payerid == '0'){
       window.opener.SubmitTheScreen();
    }
    window.close()
}

function selectUser(formid,event)
{
    var title = 'Providers';
     var params = {
               buttons: [
                   { text: 'Cancel', close: true, style: 'default btn-sm'} ],
               //size: eModal.size.sm,
               subtitle: 'Provider Info.',
               title: title,
               useBin: false,
               url: './ub04_helpers.php?action=user_select&formid=' + encodeURIComponent(formid)
           };
       return eModal.ajax(params)
           .then(function () { });
}

function updateProvider(formid, selected)
{
    document.getElementById(formid).value = selected.npi;
    if(formid == 'ub04id379'){
        document.getElementById('ub04id388').value = selected.lname;
        document.getElementById('ub04id389').value = selected.fname;
        document.getElementById('ub04id289').value = selected.taxonomy;
    }
    else if(formid == 'ub04id390'){
        document.getElementById('ub04id400').value = selected.lname;
        document.getElementById('ub04id401').value = selected.fname;
        document.getElementById('ub04id296').value = selected.taxonomy;
    }
    else if(formid == 'ub04id406'){
        document.getElementById('ub04id413').value = selected.lname;
        document.getElementById('ub04id414').value = selected.fname;
        document.getElementById('ub04id303').value = selected.taxonomy;
    }
    else if(formid == 'ub04id420'){
        document.getElementById('ub04id427').value = selected.lname;
        document.getElementById('ub04id428').value = selected.fname;
    }
    return false;
}
function disposeSave(action)
{
    var htmlout = "";
    var inputs;
    var slice = Array.prototype.slice;
    inputs = slice.call(document.forms[0])
    Array.prototype.forEach.call(inputs, function(el, i) {
        var ii = i+1;
        var tmp = document.getElementById("ub04id"+ ii.toString()).value;
        if(tmp){
            ub04id[ii] = document.getElementById("ub04id"+ ii.toString()).value.toUpperCase();
        }
    });
    ub04idSave = JSON.stringify(ub04id);
    var qstr = param({ handler: 'edit_save',pid:pid,encounter:encounter,action:action,ub04id:ub04idSave });
    location.href='ub04_dispose.php?'+qstr;
}

function postClaim(action)
{
    var inputs;
    var c = [];
    var slice = Array.prototype.slice;
    inputs = slice.call(document.forms[0])
    Array.prototype.forEach.call(inputs, function(el, i) {
        var ii = i+1;
        var tmp = document.getElementById("ub04id"+ ii.toString()).value;
        if(tmp){
            var row = el.offsetTop.toString();
            var col = el.offsetLeft.toString();
            var max = el.getAttribute('maxlength');
            var ta = window.getComputedStyle(el,null).getPropertyValue("text-align");
            var w = window.getComputedStyle(el,null).getPropertyValue("width");
            var ob = {fld:ii,top:row,left:col,max:max,align:ta,width:w}
            c.push(ob)
            ub04id[ii] = document.getElementById("ub04id"+ ii.toString()).value.toUpperCase();
        }
    });
    var cj = JSON.stringify(c)
    ub04idSave = JSON.stringify(ub04id);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ub04_dispose.php');
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log(this.responseText ? this.responseText : 'no response');
            if(this.responseText == 'done')
                alert("<?php echo xls("Save Completed")?>");
        }
    };
    if(action == 'payer_save'){
        var qs = param({handler:action,payerid:payerid,ub04id: ub04idSave})
    }
    else if(action == 'batch_save'){
        var qs = param({handler:action,pid:pid,encounter:encounter,ub04id:ub04idSave,loc:cj})
    }
    xhr.send(qs);
}

function param(object)
{
    var encodedString = '';
    for (var prop in object) {
        if (object.hasOwnProperty(prop)) {
            if (encodedString.length > 0) {
                encodedString += '&';
            }
            encodedString += encodeURI(prop + '=' + object[prop]);
        }
    }
    return encodedString;
}

var scale = 1.25;
function myZoom()
{
    var el = document.getElementById('p1');
    scale += 0.05;
    if(scale > 1.25) scale = 1.0;
    el.style.transform = 'scale(' + scale + ')';
}

function resetClaim(){
    var msg = '<?php echo xlt('This action will reset your claim!') . '\n' . xlt('Click OK if you are sure.')?>';
    var yn = confirm(msg);
    if (yn != true) {
        return false;
    }
    $.ajax({
        type: 'GET',
        url: 'ub04_dispose.php',
        data: {handler:'reset_claim',pid:pid,encounter:encounter},
        dataType: 'json',
        success: function( rtn ) {
          ub04id = rtn;
          document.getElementById('ub04Data').reset();
          rewrite(ub04id);
        }
    });
}

<?php } ?>
</script>

<style>
.container {
text-align:center;
}

.ui-autocomplete {max-height: 250px; max-width: 25%; width: 50%; overflow-y: auto; overflow-x: hidden;}

input{
    z-index:2;
    text-transform: uppercase;
    position: absolute; border-width:0px;
    height:14px; color: rgb(0,0,0);
    background: transparent; border-color:transparent;
    font:normal 10pt 'Times New Roman', Times, serif;
}

textarea{
    z-index:2;
    text-transform: uppercase;
    position: absolute; border-width:0px;
    color: rgb(0,0,0);
    background: transparent; border-color:transparent;
    font:normal 10pt 'Times New Roman', Times, serif;
}

input:focus{color: blue !important;}

body {
    padding: 0px;
    margin-top: 35px;
    margin-bottom: 35px;
    background-color: lightgrey;
}

#p1 {
    transform: scale(1.2);
    transform-origin: top center;
    margin-top: 0px;
    margin-left: auto;
    margin-right: auto;
}

#menu {
    text-align: center;
    z-index: 9999;
    background-color: ghostwhite;
    position: fixed;
    padding:5px 5px 5px 5px;
    margin-left: auto;
    margin-right: auto;
    /* opacity: 0.8; */
    top: 0px;
}

@media print {
.datepicker{
    border-color:transparent !important;
    background-color:transparent !important;
}
.user{
    background-color:transparent !important;
    border-color:transparent !important;
}
body {
    padding: 0px;
    margin: 0px;
    background-color: #fff;
}
.formhide {
    display: none;
}
#p1 {
    margin-top: 0px;
    margin-left: 0px;
    margin-right: 0px;
}
input{
    z-index:2;
    text-transform: uppercase;
    position: absolute; border-width:0px;
    height:14px; color: rgb(0,0,0);
    background: transparent; border-color:transparent;
    font:normal 10pt 'Times New Roman', Times, serif;
}
textarea{
    z-index:2;
    text-transform: uppercase;
    position: absolute; border-width:0px;
    color: rgb(0,0,0);
    background: transparent; border-color:transparent;
    font:normal 10pt 'Times New Roman', Times, serif;
}
}

#ub04id1{ left:301px; top:19px; width:264px;  text-align:left;}
#ub04id2{ left:26px;  top:19px; width:264px;  text-align:left;}
#ub04id3{ left:599px; top:19px; width:263px;  text-align:left;}
#ub04id4{ left:26px;  top:37px; width:264px;  text-align:left;}
#ub04id5{ left:301px; top:37px; width:264px;  text-align:left;}
#ub04id6{ left:599px; top:37px; width:263px;  text-align:left;}
#ub04id7{ left:862px; top:37px; width:57px;  text-align:center;}
#ub04id8{ left:26px;  top:57px; width:264px;  text-align:left;}
#ub04id9{ left:301px; top:57px; width:264px;  text-align:left;}
#ub04id10{left:26px;  top:74px; width:264px;  text-align:left;}
#ub04id11{left:301px; top:74px; width:264px;  text-align:left;}
#ub04id12{left:567px; top:74px; width:110px;  text-align:left;}
#ub04id13{left:675px; top:74px; width:77px;  text-align:center;}
#ub04id14{left:753px; top:74px; width:77px;  text-align:center;}
#ub04id15{resize:none; left:842px; top:55px; width:77px; height:32px; color: rgb(0,0,0); text-align:left;}
#ub04id16{left:138px; top:92px; width:208px;  text-align:left;}
#ub04id17{left:468px; top:92px; width:451px;  text-align:left;}
#ub04id18{left:28px;  top:110px; width:318px;  text-align:left;}
#ub04id19{left:358px; top:110px; width:352px;  text-align:left;}
#ub04id20{left:720px; top:110px; width:34px;  text-align:left;}
#ub04id21{left:764px; top:110px; width:110px;  text-align:left;}
#ub04id22{left:885px; top:110px; width:34px;  text-align:left;}
#ub04id23{left:15px;  top:147px; width:99px;  text-align:right;}
#ub04id24{left:115px; top:147px; width:34px;  text-align:center;}
#ub04id25{left:148px; top:147px; width:66px;  text-align:right;}
#ub04id26{left:214px; top:147px; width:32px;  text-align:center;}
#ub04id27{left:246px; top:147px; width:34px;  text-align:center;}
#ub04id28{left:280px; top:147px; width:32px;  text-align:center;}
#ub04id29{left:312px; top:147px; width:34px;  text-align:center;}
#ub04id30{left:345px; top:147px; width:34px;  text-align:right;}
#ub04id31{left:378px; top:147px; width:34px;  text-align:center;}
#ub04id32{left:411px; top:147px; width:34px;  text-align:center;}
#ub04id33{left:445px; top:147px; width:34px;  text-align:center;}
#ub04id34{left:477px; top:147px; width:34px;  text-align:center;}
#ub04id35{left:510px; top:147px; width:34px;  text-align:center;}
#ub04id36{left:543px; top:147px; width:34px;  text-align:center;}
#ub04id37{left:576px; top:147px; width:34px;  text-align:center;}
#ub04id38{left:610px; top:147px; width:34px;  text-align:center;}
#ub04id39{left:643px; top:147px; width:34px;  text-align:center;}
#ub04id40{left:675px; top:147px; width:34px;  text-align:center;}
#ub04id41{left:709px; top:147px; width:32px;  text-align:center;}
#ub04id42{left:741px; top:147px; width:37px;  text-align:center;}
#ub04id43{left:778px; top:147px; width:141px;  text-align:left;}
#ub04id44{left:15px;  top:184px; width:32px;  text-align:center;}
#ub04id45{left:49px;  top:184px; width:77px;  text-align:right;}
#ub04id46{left:125px; top:184px; width:32px;  text-align:center;}
#ub04id47{left:159px; top:184px; width:77px;  text-align:right;}
#ub04id48{left:235px; top:184px; width:32px;  text-align:center;}
#ub04id49{left:269px; top:184px; width:77px;  text-align:right;}
#ub04id50{left:345px; top:184px; width:34px;  text-align:center;}
#ub04id51{left:379px; top:184px; width:77px;  text-align:right;}
#ub04id52{left:455px; top:184px; width:34px;  text-align:center;}
#ub04id53{left:489px; top:184px; width:77px;  text-align:right;}
#ub04id54{left:567px; top:184px; width:77px;  text-align:right;}
#ub04id55{left:643px; top:184px; width:32px;  text-align:center;}
#ub04id56{left:677px; top:184px; width:77px;  text-align:right;}
#ub04id57{left:755px; top:184px; width:77px;  text-align:right;}
#ub04id58{left:831px; top:184px; width:87px;  text-align:center;}
#ub04id59{left:15px;  top:202px; width:32px;  text-align:center;}
#ub04id60{left:49px;  top:202px; width:77px;  text-align:right;}
#ub04id61{left:125px; top:202px; width:32px;  text-align:center;}
#ub04id62{left:159px; top:202px; width:77px;  text-align:right;}
#ub04id63{left:235px; top:202px; width:32px;  text-align:center;}
#ub04id64{left:269px; top:202px; width:77px;  text-align:right;}
#ub04id65{left:345px; top:202px; width:34px;  text-align:center;}
#ub04id66{left:379px; top:202px; width:77px;  text-align:right;}
#ub04id67{left:455px; top:202px; width:34px;  text-align:center;}
#ub04id68{left:489px; top:202px; width:77px;  text-align:right;}
#ub04id69{left:567px; top:202px; width:77px;  text-align:right;}
#ub04id70{left:643px; top:202px; width:32px;  text-align:center;}
#ub04id71{left:677px; top:202px; width:77px;  text-align:right;}
#ub04id72{left:755px; top:202px; width:77px;  text-align:right;}
#ub04id73{left:831px; top:202px; width:87px;  text-align:center;}
#ub04id74{left:489px; top:240px; width:32px;  text-align:center;}
#ub04id75{left:523px; top:240px; width:110px;  text-align:right;}
#ub04id76{left:631px; top:240px; width:34px;  text-align:center;}
#ub04id77{left:666px; top:240px; width:110px;  text-align:right;}
#ub04id78{left:775px; top:240px; width:34px;  text-align:center;}
#ub04id79{left:808px; top:240px; width:110px;  text-align:right;}
#ub04id80{left:489px; top:258px; width:32px;  text-align:center;}
#ub04id81{left:523px; top:258px; width:110px;  text-align:right;}
#ub04id82{left:631px; top:258px; width:34px;  text-align:center;}
#ub04id83{left:666px; top:258px; width:110px;  text-align:right;}
#ub04id84{left:775px; top:258px; width:34px;  text-align:center;}
#ub04id85{left:808px; top:258px; width:110px;  text-align:right;}
#ub04id86{left:489px; top:275px; width:32px;  text-align:center;}
#ub04id87{left:523px; top:275px; width:110px;  text-align:right;}
#ub04id88{left:631px; top:275px; width:34px;  text-align:center;}
#ub04id89{left:666px; top:275px; width:110px;  text-align:right;}
#ub04id90{left:775px; top:275px; width:34px;  text-align:center;}
#ub04id91{left:808px; top:275px; width:110px;  text-align:right;}
#ub04id92{left:489px; top:294px; width:32px;  text-align:center;}
#ub04id93{left:523px; top:294px; width:110px;  text-align:right;}
#ub04id94{left:631px; top:294px; width:34px;  text-align:center;}
#ub04id95{left:666px; top:294px; width:110px;  text-align:right;}
#ub04id96{left:775px; top:294px; width:34px;  text-align:center;}
#ub04id97{left:808px; top:294px; width:110px;  text-align:right;}
#ub04id98{z-index:2; resize:none; position: absolute; left:31px; top:220px; width:448px; height:87px; color: rgb(0,0,0); text-align:left;}
#ub04id99{left:15px;  top:332px; width:52px;  text-align:center;}
#ub04id100{left:67px;  top:332px; width:274px;  text-align:left;}
#ub04id101{left:342px; top:332px; width:164px;  text-align:center;}
#ub04id102{left:507px; top:332px; width:77px;  text-align:right;}
#ub04id103{left:585px; top:332px; width:86px;  text-align:right;}
#ub04id104{left:672px; top:332px; width:110px;  text-align:right;}
#ub04id105{left:782px; top:332px; width:109px;  text-align:right;}
#ub04id106{left:891px; top:332px; width:28px;  text-align:center;}
#ub04id107{left:15px;  top:349px; width:52px;  text-align:center;}
#ub04id108{left:67px;  top:349px; width:274px;  text-align:left;}
#ub04id109{left:342px; top:349px; width:164px;  text-align:center;}
#ub04id110{left:507px; top:349px; width:77px;  text-align:right;}
#ub04id111{left:585px; top:349px; width:86px;  text-align:right;}
#ub04id112{left:672px; top:349px; width:110px;  text-align:right;}
#ub04id113{left:782px; top:349px; width:109px;  text-align:right;}
#ub04id114{left:891px; top:349px; width:28px;  text-align:center;}
#ub04id115{left:15px;  top:368px; width:52px;  text-align:center;}
#ub04id116{left:67px;  top:368px; width:274px;  text-align:left;}
#ub04id117{left:342px; top:368px; width:164px;  text-align:center;}
#ub04id118{left:507px; top:368px; width:77px;  text-align:right;}
#ub04id119{left:585px; top:368px; width:86px;  text-align:right;}
#ub04id120{left:672px; top:368px; width:110px;  text-align:right;}
#ub04id121{left:782px; top:368px; width:109px;  text-align:right;}
#ub04id122{left:891px; top:368px; width:28px;  text-align:center;}
#ub04id123{left:15px;  top:385px; width:52px;  text-align:center;}
#ub04id124{left:67px;  top:385px; width:274px;  text-align:left;}
#ub04id125{left:342px; top:385px; width:164px;  text-align:center;}
#ub04id126{left:507px; top:385px; width:77px;  text-align:right;}
#ub04id127{left:585px; top:385px; width:86px;  text-align:right;}
#ub04id128{left:672px; top:385px; width:110px;  text-align:right;}
#ub04id129{left:782px; top:385px; width:109px;  text-align:right;}
#ub04id130{left:891px; top:385px; width:28px;  text-align:center;}
#ub04id131{left:15px;  top:404px; width:52px;  text-align:center;}
#ub04id132{left:67px;  top:404px; width:274px;  text-align:left;}
#ub04id133{left:342px; top:404px; width:164px;  text-align:center;}
#ub04id134{left:507px; top:404px; width:77px;  text-align:right;}
#ub04id135{left:585px; top:404px; width:86px;  text-align:right;}
#ub04id136{left:672px; top:404px; width:110px;  text-align:right;}
#ub04id137{left:782px; top:404px; width:109px;  text-align:right;}
#ub04id138{left:891px; top:404px; width:28px;  text-align:center;}
#ub04id139{left:15px;  top:422px; width:52px;  text-align:center;}
#ub04id140{left:67px;  top:422px; width:274px;  text-align:left;}
#ub04id141{left:342px; top:422px; width:164px;  text-align:center;}
#ub04id142{left:507px; top:422px; width:77px;  text-align:right;}
#ub04id143{left:585px; top:422px; width:86px;  text-align:right;}
#ub04id144{left:672px; top:422px; width:110px;  text-align:right;}
#ub04id145{left:782px; top:422px; width:109px;  text-align:right;}
#ub04id146{left:891px; top:422px; width:28px;  text-align:center;}
#ub04id147{left:15px;  top:440px; width:52px;  text-align:center;}
#ub04id148{left:67px;  top:440px; width:274px;  text-align:left;}
#ub04id149{left:342px; top:440px; width:164px;  text-align:center;}
#ub04id150{left:507px; top:440px; width:77px;  text-align:right;}
#ub04id151{left:585px; top:440px; width:86px;  text-align:right;}
#ub04id152{left:672px; top:440px; width:110px;  text-align:right;}
#ub04id153{left:782px; top:440px; width:109px;  text-align:right;}
#ub04id154{left:891px; top:440px; width:28px;  text-align:center;}
#ub04id155{left:15px;  top:459px; width:52px;  text-align:center;}
#ub04id156{left:67px;  top:459px; width:274px;  text-align:left;}
#ub04id157{left:342px; top:459px; width:164px;  text-align:center;}
#ub04id158{left:507px; top:459px; width:77px;  text-align:right;}
#ub04id159{left:585px; top:459px; width:86px;  text-align:right;}
#ub04id160{left:672px; top:459px; width:110px;  text-align:right;}
#ub04id161{left:782px; top:459px; width:109px;  text-align:right;}
#ub04id162{left:891px; top:459px; width:28px;  text-align:center;}
#ub04id163{left:15px;  top:477px; width:52px;  text-align:center;}
#ub04id164{left:67px;  top:477px; width:274px;  text-align:left;}
#ub04id165{left:342px; top:477px; width:164px;  text-align:center;}
#ub04id166{left:507px; top:477px; width:77px;  text-align:right;}
#ub04id167{left:585px; top:477px; width:86px;  text-align:right;}
#ub04id168{left:672px; top:477px; width:110px;  text-align:right;}
#ub04id169{left:782px; top:477px; width:109px;  text-align:right;}
#ub04id170{left:891px; top:477px; width:28px;  text-align:center;}
#ub04id171{left:15px;  top:495px; width:52px;  text-align:center;}
#ub04id172{left:67px;  top:495px; width:274px;  text-align:left;}
#ub04id173{left:342px; top:495px; width:164px;  text-align:center;}
#ub04id174{left:507px; top:495px; width:77px;  text-align:right;}
#ub04id175{left:585px; top:495px; width:86px;  text-align:right;}
#ub04id176{left:672px; top:495px; width:110px;  text-align:right;}
#ub04id177{left:782px; top:495px; width:109px;  text-align:right;}
#ub04id178{left:891px; top:495px; width:28px;  text-align:center;}
#ub04id179{left:15px;  top:514px; width:52px;  text-align:center;}
#ub04id180{left:67px;  top:514px; width:274px;  text-align:left;}
#ub04id181{left:342px; top:514px; width:164px;  text-align:center;}
#ub04id182{left:507px; top:514px; width:77px;  text-align:right;}
#ub04id183{left:585px; top:514px; width:86px;  text-align:right;}
#ub04id184{left:672px; top:514px; width:110px;  text-align:right;}
#ub04id185{left:782px; top:514px; width:109px;  text-align:right;}
#ub04id186{left:891px; top:514px; width:28px;  text-align:center;}
#ub04id187{left:15px;  top:532px; width:52px;  text-align:center;}
#ub04id188{left:67px;  top:532px; width:274px;  text-align:left;}
#ub04id189{left:342px; top:532px; width:164px;  text-align:center;}
#ub04id190{left:507px; top:532px; width:77px;  text-align:right;}
#ub04id191{left:585px; top:532px; width:86px;  text-align:right;}
#ub04id192{left:672px; top:532px; width:110px;  text-align:right;}
#ub04id193{left:782px; top:532px; width:109px;  text-align:right;}
#ub04id194{left:891px; top:532px; width:28px;  text-align:center;}
#ub04id195{left:15px;  top:550px; width:52px;  text-align:center;}
#ub04id196{left:67px;  top:550px; width:274px;  text-align:left;}
#ub04id197{left:342px; top:550px; width:164px;  text-align:center;}
#ub04id198{left:507px; top:550px; width:77px;  text-align:right;}
#ub04id199{left:585px; top:550px; width:86px;  text-align:right;}
#ub04id200{left:672px; top:550px; width:110px;  text-align:right;}
#ub04id201{left:782px; top:550px; width:109px;  text-align:right;}
#ub04id202{left:891px; top:550px; width:28px;  text-align:center;}
#ub04id203{left:15px;  top:569px; width:52px;  text-align:center;}
#ub04id204{left:67px;  top:569px; width:274px;  text-align:left;}
#ub04id205{left:342px; top:569px; width:164px;  text-align:center;}
#ub04id206{left:507px; top:569px; width:77px;  text-align:right;}
#ub04id207{left:585px; top:569px; width:86px;  text-align:right;}
#ub04id208{left:672px; top:569px; width:110px;  text-align:right;}
#ub04id209{left:782px; top:569px; width:109px;  text-align:right;}
#ub04id210{left:891px; top:569px; width:28px;  text-align:center;}
#ub04id211{left:15px;  top:587px; width:52px;  text-align:center;}
#ub04id212{left:67px;  top:587px; width:274px;  text-align:left;}
#ub04id213{left:342px; top:587px; width:164px;  text-align:center;}
#ub04id214{left:507px; top:587px; width:77px;  text-align:right;}
#ub04id215{left:585px; top:587px; width:86px;  text-align:right;}
#ub04id216{left:672px; top:587px; width:110px;  text-align:right;}
#ub04id217{left:782px; top:587px; width:109px;  text-align:right;}
#ub04id218{left:891px; top:587px; width:28px;  text-align:center;}
#ub04id219{left:15px;  top:605px; width:52px;  text-align:center;}
#ub04id220{left:67px;  top:605px; width:274px;  text-align:left;}
#ub04id221{left:342px; top:605px; width:164px;  text-align:center;}
#ub04id222{left:507px; top:605px; width:77px;  text-align:right;}
#ub04id223{left:585px; top:605px; width:86px;  text-align:right;}
#ub04id224{left:672px; top:605px; width:110px;  text-align:right;}
#ub04id225{left:782px; top:605px; width:109px;  text-align:right;}
#ub04id226{left:891px; top:605px; width:28px;  text-align:center;}
#ub04id227{left:15px;  top:624px; width:52px;  text-align:center;}
#ub04id228{left:67px;  top:624px; width:274px;  text-align:left;}
#ub04id229{left:342px; top:624px; width:164px;  text-align:center;}
#ub04id230{left:507px; top:624px; width:77px;  text-align:right;}
#ub04id231{left:585px; top:624px; width:86px;  text-align:right;}
#ub04id232{left:672px; top:624px; width:110px;  text-align:right;}
#ub04id233{left:782px; top:624px; width:109px;  text-align:right;}
#ub04id234{left:891px; top:624px; width:28px;  text-align:center;}
#ub04id235{left:15px;  top:642px; width:52px;  text-align:center;}
#ub04id236{left:67px;  top:642px; width:274px;  text-align:left;}
#ub04id237{left:342px; top:642px; width:164px;  text-align:center;}
#ub04id238{left:507px; top:642px; width:77px;  text-align:right;}
#ub04id239{left:585px; top:642px; width:86px;  text-align:right;}
#ub04id240{left:672px; top:642px; width:110px;  text-align:right;}
#ub04id241{left:782px; top:642px; width:109px;  text-align:right;}
#ub04id242{left:891px; top:642px; width:28px;  text-align:center;}
#ub04id243{left:15px;  top:660px; width:52px;  text-align:center;}
#ub04id244{left:67px;  top:660px; width:274px;  text-align:left;}
#ub04id245{left:342px; top:660px; width:164px;  text-align:center;}
#ub04id246{left:507px; top:660px; width:77px;  text-align:right;}
#ub04id247{left:585px; top:660px; width:86px;  text-align:right;}
#ub04id248{left:672px; top:660px; width:110px;  text-align:right;}
#ub04id249{left:782px; top:660px; width:109px;  text-align:right;}
#ub04id250{left:891px; top:660px; width:28px;  text-align:center;}
#ub04id251{left:15px;  top:679px; width:52px;  text-align:center;}
#ub04id252{left:67px;  top:679px; width:274px;  text-align:left;}
#ub04id253{left:342px; top:679px; width:164px;  text-align:center;}
#ub04id254{left:507px; top:679px; width:77px;  text-align:right;}
#ub04id255{left:585px; top:679px; width:86px;  text-align:right;}
#ub04id256{left:672px; top:679px; width:110px;  text-align:right;}
#ub04id257{left:782px; top:679px; width:109px;  text-align:right;}
#ub04id258{left:891px; top:679px; width:28px;  text-align:center;}
#ub04id259{left:15px;  top:697px; width:52px;  text-align:center;}
#ub04id260{left:67px;  top:697px; width:274px;  text-align:left;}
#ub04id261{left:342px; top:697px; width:164px;  text-align:center;}
#ub04id262{left:507px; top:697px; width:77px;  text-align:right;}
#ub04id263{left:585px; top:697px; width:86px;  text-align:right;}
#ub04id264{left:672px; top:697px; width:110px;  text-align:right;}
#ub04id265{left:782px; top:697px; width:109px;  text-align:right;}
#ub04id266{left:891px; top:697px; width:28px;  text-align:center;}
#ub04id267{left:15px;  top:715px; width:52px;  text-align:center;}
#ub04id268{left:67px;  top:715px; width:274px;  text-align:left;}
#ub04id269{left:342px; top:715px; width:164px;  text-align:center;}
#ub04id270{left:507px; top:715px; width:77px;  text-align:right;}
#ub04id271{left:585px; top:715px; width:86px;  text-align:right;}
#ub04id272{left:672px; top:715px; width:110px;  text-align:right;}
#ub04id273{left:782px; top:715px; width:109px;  text-align:right;}
#ub04id274{left:891px; top:715px; width:28px;  text-align:center;}
#ub04id275{left:15px;  top:734px; width:52px;  text-align:center;}
#ub04id276{left:118px; top:734px; width:32px;  text-align:center;}
#ub04id277{left:182px; top:734px; width:32px;  text-align:center;}
#ub04id278{left:507px; top:734px; width:77px;  text-align:center;}
#ub04id279{left:672px; top:734px; width:110px;  text-align:right;}
#ub04id280{left:782px; top:734px; width:109px;  text-align:right;}
#ub04id281{left:891px; top:734px; width:28px;  text-align:center;}
#ub04id282{left:753px; top:752px; width:165px;  text-align:left;}
#ub04id283{left:15px;  top:770px; width:252px;  text-align:left;}
#ub04id284{left:269px; top:770px; width:164px;  text-align:left;}
#ub04id285{left:434px; top:770px; width:22px;  text-align:left;}
#ub04id286{left:468px; top:770px; width:20px;  text-align:left;}
#ub04id287{left:489px; top:770px; width:110px;  text-align:right;}
#ub04id288{left:599px; top:770px; width:121px;  text-align:right;}
#ub04id289{left:753px; top:770px; width:165px;  text-align:left;}
#ub04id290{left:15px;  top:789px; width:252px;  text-align:left;}
#ub04id291{left:269px; top:789px; width:164px;  text-align:left;}
#ub04id292{left:434px; top:789px; width:22px;  text-align:left;}
#ub04id293{left:468px; top:789px; width:20px;  text-align:left;}
#ub04id294{left:489px; top:789px; width:110px;  text-align:right;}
#ub04id295{left:599px; top:789px; width:121px;  text-align:right;}
#ub04id296{left:753px; top:789px; width:165px;  text-align:left;}
#ub04id297{left:15px;  top:807px; width:252px;  text-align:left;}
#ub04id298{left:269px; top:807px; width:164px;  text-align:left;}
#ub04id299{left:434px; top:807px; width:22px;  text-align:left;}
#ub04id300{left:468px; top:807px; width:20px;  text-align:left;}
#ub04id301{left:489px; top:807px; width:110px;  text-align:right;}
#ub04id302{left:599px; top:807px; width:121px;  text-align:right;}
#ub04id303{left:753px; top:807px; width:165px;  text-align:left;}
#ub04id304{left:15px;  top:845px; width:286px;  text-align:left;}
#ub04id305{left:301px; top:845px; width:32px;  text-align:left;}
#ub04id306{left:333px; top:845px; width:220px;  text-align:left;}
#ub04id307{left:555px; top:845px; width:165px;  text-align:left;}
#ub04id308{left:720px; top:845px; width:199px;  text-align:left;}
#ub04id309{left:15px;  top:862px; width:286px;  text-align:left;}
#ub04id310{left:301px; top:862px; width:32px;  text-align:left;}
#ub04id311{left:333px; top:862px; width:220px;  text-align:left;}
#ub04id312{left:555px; top:862px; width:165px;  text-align:left;}
#ub04id313{left:720px; top:862px; width:199px;  text-align:left;}
#ub04id314{left:15px;  top:880px; width:286px;  text-align:left;}
#ub04id315{left:301px; top:880px; width:32px;  text-align:left;}
#ub04id316{left:333px; top:880px; width:220px;  text-align:left;}
#ub04id317{left:555px; top:880px; width:165px;  text-align:left;}
#ub04id318{left:720px; top:880px; width:199px;  text-align:left;}
#ub04id319{left:15px;  top:917px; width:341px;  text-align:left;}
#ub04id320{left:358px; top:917px; width:286px;  text-align:left;}
#ub04id321{left:643px; top:917px; width:275px;  text-align:left;}
#ub04id322{left:15px;  top:935px; width:341px;  text-align:left;}
#ub04id323{left:358px; top:935px; width:286px;  text-align:left;}
#ub04id324{left:643px; top:935px; width:275px;  text-align:left;}
#ub04id325{left:15px;  top:954px; width:341px;  text-align:left;}
#ub04id326{left:358px; top:954px; width:286px;  text-align:left;}
#ub04id327{left:643px; top:954px; width:275px;  text-align:left;}
#ub04id328{left:28px;  top:972px; width:77px;  text-align:left;}
#ub04id329{left:106px; top:972px; width:11px;  text-align:center;}
#ub04id330{left:115px; top:972px; width:78px;  text-align:left;}
#ub04id331{left:193px; top:972px; width:12px;  text-align:center;}
#ub04id332{left:203px; top:972px; width:78px;  text-align:left;}
#ub04id333{left:280px; top:972px; width:12px;  text-align:center;}
#ub04id334{left:290px; top:972px; width:78px;  text-align:left;}
#ub04id335{left:368px; top:972px; width:12px;  text-align:center;}
#ub04id336{left:381px; top:972px; width:77px;  text-align:left;}
#ub04id337{left:457px; top:972px; width:11px;  text-align:center;}
#ub04id338{left:468px; top:972px; width:77px;  text-align:left;}
#ub04id339{left:544px; top:972px; width:12px;  text-align:center;}
#ub04id340{left:556px; top:972px; width:77px;  text-align:left;}
#ub04id341{left:633px; top:972px; width:12px;  text-align:center;}
#ub04id342{left:643px; top:972px; width:78px;  text-align:left;}
#ub04id343{left:721px; top:972px; width:11px;  text-align:center;}
#ub04id344{left:732px; top:972px; width:78px;  text-align:left;}
#ub04id345{left:810px; top:972px; width:11px;  text-align:center;}
#ub04id346{left:833px; top:972px; width:86px;  text-align:left;}
#ub04id347{left:15px;  top:990px; width:11px;  text-align:center;}
#ub04id348{left:28px;  top:990px; width:77px;  text-align:left;}
#ub04id349{left:106px; top:990px; width:11px;  text-align:center;}
#ub04id350{left:115px; top:990px; width:78px;  text-align:left;}
#ub04id351{left:193px; top:990px; width:12px;  text-align:center;}
#ub04id352{left:203px; top:990px; width:78px;  text-align:left;}
#ub04id353{left:280px; top:990px; width:12px;  text-align:center;}
#ub04id354{left:290px; top:990px; width:78px;  text-align:left;}
#ub04id355{left:368px; top:990px; width:12px;  text-align:center;}
#ub04id356{left:381px; top:990px; width:77px;  text-align:left;}
#ub04id357{left:457px; top:990px; width:11px;  text-align:center;}
#ub04id358{left:468px; top:990px; width:78px;  text-align:left;}
#ub04id359{left:544px; top:990px; width:12px;  text-align:center;}
#ub04id360{left:556px; top:990px; width:77px;  text-align:left;}
#ub04id361{left:633px; top:990px; width:12px;  text-align:center;}
#ub04id362{left:643px; top:990px; width:78px;  text-align:left;}
#ub04id363{left:732px; top:990px; width:78px;  text-align:left;}
#ub04id364{left:810px; top:990px; width:11px;  text-align:center;}
#ub04id365{left:821px; top:990px; width:98px;  text-align:left;}
#ub04id366{left:721px; top:990px; width:11px;  text-align:center;}
#ub04id367{left:58px;  top:1010px; width:80px;  text-align:left;}
#ub04id368{left:193px; top:1010px; width:77px;  text-align:center;}
#ub04id369{left:269px; top:1010px; width:77px;  text-align:center;}
#ub04id370{left:347px; top:1010px; width:77px;  text-align:center;}
#ub04id371{left:468px; top:1010px; width:55px;  text-align:right;}
#ub04id372{left:544px; top:1010px; width:78px;  text-align:center;}
#ub04id373{left:622px; top:1010px; width:11px;  text-align:center;}
#ub04id374{left:633px; top:1010px; width:77px;  text-align:center;}
#ub04id375{left:711px; top:1010px; width:11px;  text-align:center;}
#ub04id376{left:721px; top:1010px; width:77px;  text-align:center;}
#ub04id377{left:798px; top:1010px; width:12px;  text-align:center;}
#ub04id378{left:824px; top:1010px; width:95px;  text-align:left;}
#ub04id379{left:662px; top:1028px; width:113px;  text-align:left;}
#ub04id380{left:798px; top:1028px; width:23px;  text-align:left;}
#ub04id381{left:821px; top:1028px; width:98px;  text-align:left;}
#ub04id382{left:15px;  top:1045px; width:89px;  text-align:left;}
#ub04id383{left:104px; top:1045px; width:77px;  text-align:right;}
#ub04id384{left:182px; top:1045px; width:87px;  text-align:left;}
#ub04id385{left:269px; top:1045px; width:77px;  text-align:right;}
#ub04id386{left:347px; top:1045px; width:87px;  text-align:left;}
#ub04id387{left:434px; top:1045px; width:77px;  text-align:right;}
#ub04id388{left:594px; top:1045px; width:170px;  text-align:left;}
#ub04id389{left:790px; top:1045px; width:129px;  text-align:left;}
#ub04id390{left:662px; top:1065px; width:113px;  text-align:left;}
#ub04id391{left:798px; top:1065px; width:23px;  text-align:left;}
#ub04id392{left:821px; top:1065px; width:98px;  text-align:left;}
#ub04id393{left:15px;  top:1082px; width:89px;  text-align:left;}
#ub04id394{left:104px; top:1082px; width:77px;  text-align:right;}
#ub04id395{left:182px; top:1082px; width:87px;  text-align:left;}
#ub04id396{left:269px; top:1082px; width:77px;  text-align:right;}
#ub04id397{left:347px; top:1082px; width:87px;  text-align:left;}
#ub04id398{left:434px; top:1082px; width:77px;  text-align:right;}
#ub04id399{resize:none; left:512px; top:1036px; width:55px; height:60px; color: rgb(0,0,0); text-align:left;}
#ub04id400{left:594px; top:1082px; width:170px;  text-align:left;}
#ub04id401{left:790px; top:1082px; width:129px;  text-align:left;}
#ub04id402{left:303px; top:1102px; width:20px;  text-align:center;}
#ub04id403{left:324px; top:1102px; width:110px;  text-align:left;}
#ub04id404{left:436px; top:1102px; width:132px;  text-align:right;}
#ub04id405{left:622px; top:1102px; width:22px;  text-align:left;}
#ub04id406{left:662px; top:1102px; width:113px;  text-align:left;}
#ub04id407{left:798px; top:1102px; width:23px;  text-align:left;}
#ub04id408{left:821px; top:1102px; width:98px;  text-align:left;}
#ub04id409{left:72px;  top:1102px; width:209px;  text-align:left;}
#ub04id410{left:303px; top:1120px; width:20px;  text-align:center;}
#ub04id411{left:324px; top:1120px; width:110px;  text-align:left;}
#ub04id412{left:436px; top:1120px; width:132px;  text-align:right;}
#ub04id413{left:594px; top:1120px; width:170px;  text-align:left;}
#ub04id414{left:790px; top:1120px; width:129px;  text-align:left;}
#ub04id415{left:19px;  top:1120px; width:263px;  text-align:left;}
#ub04id416{left:303px; top:1137px; width:20px;  text-align:center;}
#ub04id417{left:324px; top:1137px; width:110px;  text-align:left;}
#ub04id418{left:436px; top:1137px; width:132px;  text-align:right;}
#ub04id419{left:622px; top:1137px; width:22px;  text-align:left;}
#ub04id420{left:662px; top:1137px; width:113px;  text-align:left;}
#ub04id421{left:821px; top:1137px; width:98px;  text-align:left;}
#ub04id422{left:798px; top:1137px; width:23px;  text-align:left;}
#ub04id423{left:19px;  top:1137px; width:263px;  text-align:left;}
#ub04id424{left:303px; top:1155px; width:20px;  text-align:center;}
#ub04id425{left:324px; top:1155px; width:110px;  text-align:left;}
#ub04id426{left:436px; top:1155px; width:132px;  text-align:right;}
#ub04id427{left:594px; top:1155px; width:170px;  text-align:left;}
#ub04id428{left:790px; top:1155px; width:129px;  text-align:left;}
#ub04id429{left:19px;  top:1155px; width:263px;  text-align:left;}
</style>
</head>
<body onload="adjustForm();">
<div class="container" id="formContainer">
<?php if ($isAuthorized !== true) {?>
<h3 class='formhide'><em><?php echo xlt('Claim Edit') ?> </em><button class="btn btn-xs btn-warning" onclick="myZoom()" ><?php echo xlt('Zoom'); ?></button></h3>
<div class="navbar-fixed-top formhide" id='menu'>
<?php if ($pid && $encounter) {?>
    <button class="btn btn-xs btn-success" onclick="disposeSave('form')" title=<?php echo xlt("Save for printing with form") ?>><?php echo xlt('Pdf With Form'); ?></button>
    <button class="btn btn-xs btn-success" onclick="disposeSave('noform')" title=<?php echo xlt("Save for printing to a pre printed sheet"); ?>><?php echo xlt('Pdf Without Form'); ?></button>
    <button class="btn btn-xs btn-success" onclick="postClaim('batch_save')" title=<?php echo xlt("Save claim for batch processing"); ?>><?php echo xlt('Save Claim'); ?></button>
    <?php } else {?>
    <button class="btn btn-xs btn-success" onclick="postClaim('payer_save')"><?php echo xlt('Save Payer'); ?></button>
    <?php } ?>
    <button class="btn btn-xs btn-danger" onclick="resetClaim()" title=<?php echo xlt("Reset claim form to Fee Sheet Version"); ?>><?php echo xlt('Reset Version'); ?></button>
    <button class="btn btn-info btn-xs" type="button" onclick="window.scrollTo(0, 0);$('#formhelp').toggle()"><?php echo xlt('Help'); ?></button>
    <button class="btn btn-xs btn-danger" onclick="cleanUp()"><?php echo xlt('Return'); ?></button>
</div>
<div id='formhelp' class='well' style='display:none; text-align:center; width: auto; margin: 5px auto;'>
    <h4>Help</h4>
     <div style='text-align:left;'>
        * <?php echo xlt('Many code items have a lookup/hint.'); ?>
        <?php echo xlt('Either type a search term in appropriate box or double click box to see available codes.'); ?><br>
        * <?php echo xlt('Double Clicking a NPI box will bring up a current Users dialog for providers.'); ?><br>
        * <?php echo xlt('PDF buttons will save, mark claim reviewed and download claim'); ?><br>
        * <?php echo xlt('Reset button resets the edited claim to the fee sheet version. If subsequently saved, it will replace last claim version and be considered reviewed. Otherwise, claim is reset to fee sheet version.'); ?><br>
        * <?php echo xlt('Save button saves claim and marks reviewed'); ?><br>
        * <?php echo xlt('Return button simply returns, then refreshes billing manager'); ?><br><br>
    </div>
   <button class="btn btn-primary btn-xs" type="button" onclick="$('#formhelp').toggle()"><?php echo xlt('Dismiss Help'); ?></button>
</div>
<?php } ?>
<div id="p1" class="pageArea" style="overflow: hidden; position: relative; width: 934px; height: 1210px;">
<!-- Template background -->
<div id="pg1" style="-webkit-user-select: none;"><img src="<?php echo $imgurl ?>/ub04.svg" id="frmbg" style="width:934px; height:1210px; background-color:white; -moz-transform:scale(1); z-index: 0;" /></div>
<!-- Begin Form Data -->
<form id="ub04Data">
<input type="text"   maxlength="25"  id="ub04id1" value=""  title="2. PAY-TO NAME"  name="ub04Block_4"/>
<input type="text"   maxlength="25"  id="ub04id2" value=""  title="1. BILLING PROVIDER NAME"  name="ub04Block_0"/>
<input type="text"   maxlength="20"  id="ub04id3" value=""  title="3a. PATIENT CONTROL NUMBER"  name="ub04Block_8"/>
<input type="text"   maxlength="25"  id="ub04id4" value=""  title="1. BILLING PROVIDER STREET ADDRESS"  name="ub04Block_1"/>
<input type="text"   maxlength="25"  id="ub04id5" value=""  title="2. PAY-TO STREET ADDRESS"  name="ub04Block_5"/>
<input type="text"   maxlength="24"  id="ub04id6" value=""  title="3b. MEDICAL/HEALTH RECORD NUMBER"  name="ub04Block_9"/>
<input type="text"   maxlength="4"  id="ub04id7" value=""   title="4. TYPE OF BILL"  name="ub04Block_10"/>
<input type="text"   maxlength="25"  id="ub04id8" value=""  title="1. BILLING PROVIDER CITY, STATE, ZIP"  name="ub04Block_2"/>
<input type="text"   maxlength="25"  id="ub04id9" value=""  title="2. PAY-TO CITY, STATE, ZIP"  name="ub04Block_6"/>
<input type="text"   maxlength="25"  id="ub04id10" value="" title="1. BILLING PROVIDER COUNTRY CODE, PHONE NUMBER"  name="ub04Block_3"/>
<input type="text"   maxlength="25"  id="ub04id11" value="" title="2. PAY-TO COUNTRY CODE, PHONE NUMBER"  name="ub04Block_7"/>
<input type="text"   maxlength="10"  id="ub04id12" value="" title="5. FEDERAL TAX NUMBER"  name="ub04Block_11"/>
<input type="text" data-dp  maxlength="10"  id="ub04id13" value=""  title="6. STATEMENT COVERS PERIOD FROM DATE"  name="ub04Block_12"/>
<input type="text" data-dp   maxlength="10"  id="ub04id14" value=""  title="6. STATEMENT COVERS PERIOD TO DATE"  name="ub04Block_13"/>
<textarea  disabled maxlength="15"  id="ub04id15" title="7. RESERVED"  name="ub04Block_14"></textarea>
<input type="text"   maxlength="19"  id="ub04id16" value="" title="8a. PATIENT IDENTIFIER"  name="ub04Block_15"/>
<input type="text"   maxlength="40"  id="ub04id17" value="" title="9a. PATIENT STREET ADDRESS"  name="ub04Block_17"/>
<input type="text"   maxlength="29"  id="ub04id18" value="" title="8b. PATIENT NAME"  name="ub04Block_16"/>
<input type="text"   maxlength="30"  id="ub04id19" value="" title="9b. PATIENT CITY"  name="ub04Block_18"/>
<input type="text"   maxlength="2"  id="ub04id20" value=""  title="9c. PATIENT STATE"  name="ub04Block_19"/>
<input type="text"   maxlength="9"  id="ub04id21" value=""  title="9d. PATIENT ZIP CODE"  name="ub04Block_20"/>
<input type="text"   maxlength="2"  id="ub04id22" value=""  title="9e. PATIENT COUNTRY CODE"  name="ub04Block_21"/>
<input type="text"   maxlength="8"  id="ub04id23" value=""  title="10. PATIENT BIRTH DATE (MMDDYYYY)"  name="ub04Block_22"/>
<input type="text"   maxlength="1"  id="ub04id24" value=""  title="11. PATIENT SEX"  name="ub04Block_23"/>
<input type="text" data-dp   maxlength="10"  id="ub04id25" value=""  title="12. PATIENT ADMISSION/START OF CARE DATE (MMDDYY)"  name="ub04Block_24"/>
<input type="text"   maxlength="2"  id="ub04id26" value=""  title="13. PATIENT ADMISSION HOUR"  name="ub04Block_25"/>
<input type="text"   maxlength="1"  id="ub04id27" value=""  title="14. PATIENT PRIORITY (TYPE) OF VISIT"  name="ub04Block_26"/>
<input type="text"   maxlength="1"  id="ub04id28" value=""  title="15. PATIENT POINT OF ORIGIN FOR ADMISSION OR VISIT"  name="ub04Block_27"/>
<input type="text"   maxlength="2"  id="ub04id29" value=""  title="16. PATIENT DISCHARGE HOUR"  name="ub04Block_28"/>
<input type="text"   maxlength="2"  id="ub04id30" value=""  title="17. PATIENT DISCHARGE STATUS"  name="ub04Block_29"/>
<input type="text"   maxlength="2"  id="ub04id31" value=""  title="18. CONDITION CODE"  name="ub04Block_30"/>
<input type="text"   maxlength="2"  id="ub04id32" value=""  title="19. CONDITION CODE"  name="ub04Block_31"/>
<input type="text"   maxlength="2"  id="ub04id33" value=""  title="20. CONDITION CODE"  name="ub04Block_32"/>
<input type="text"   maxlength="2"  id="ub04id34" value=""  title="21. CONDITION CODE"  name="ub04Block_33"/>
<input type="text"   maxlength="2"  id="ub04id35" value=""  title="22. CONDITION CODE"  name="ub04Block_34"/>
<input type="text"   maxlength="2"  id="ub04id36" value=""  title="23. CONDITION CODE"  name="ub04Block_35"/>
<input type="text"   maxlength="2"  id="ub04id37" value=""  title="24. CONDITION CODE"  name="ub04Block_36"/>
<input type="text"   maxlength="2"  id="ub04id38" value=""  title="25. CONDITION CODE"  name="ub04Block_37"/>
<input type="text"   maxlength="2"  id="ub04id39" value=""  title="26. CONDITION CODE"  name="ub04Block_38"/>
<input type="text"   maxlength="2"  id="ub04id40" value=""  title="27. CONDITION CODE"  name="ub04Block_39"/>
<input type="text"   maxlength="2"  id="ub04id41" value=""  title="28. CONDITION CODE"  name="ub04Block_40"/>
<input type="text"   maxlength="2"  id="ub04id42" value=""  title="29. ACCIDENT STATE"  name="ub04Block_41"/>
<input type="text" disabled   id="ub04id43" value="" data-objref="520" title="30. RESERVED"  name="ub04Block_42"/>
<input type="text"   maxlength="2"  id="ub04id44" value=""  title="31a. OCCURRENCE CODE"  name="ub04Block_43"/>
<input type="text" data-dp   maxlength="10"  id="ub04id45" value=""  title="31a. OCCURRENCE DATE"  name="ub04Block_44"/>
<input type="text"   maxlength="2"  id="ub04id46" value=""  title="32a. OCCURRENCE CODE"  name="ub04Block_45"/>
<input type="text" data-dp   maxlength="10"  id="ub04id47" value=""  title="32a. OCCURRENCE DATE"  name="ub04Block_46"/>
<input type="text"   maxlength="2"  id="ub04id48" value=""  title="33a. OCCURRENCE CODE"  name="ub04Block_47"/>
<input type="text" data-dp   maxlength="10"  id="ub04id49" value=""  title="33a. OCCURRENCE DATE"  name="ub04Block_48"/>
<input type="text"   maxlength="2"  id="ub04id50" value=""  title="34a. OCCURRENCE CODE"  name="ub04Block_49"/>
<input type="text" data-dp  maxlength="10"  id="ub04id51" value=""  title="34a. OCCURRENCE DATE"  name="ub04Block_50"/>
<input type="text"   maxlength="2"  id="ub04id52" value=""  title="35a. OCCURRENCE SPAN CODE"  name="ub04Block_59"/>
<input type="text" data-dp   maxlength="10"  id="ub04id53" value=""  title="35a. OCCURRENCE SPAN DATE FROM"  name="ub04Block_60"/>
<input type="text" data-dp  maxlength="10"  id="ub04id54" value=""  title="35a. OCCURRENCE SPAN DATE THROUGH"  name="ub04Block_61"/>
<input type="text"   maxlength="2"  id="ub04id55" value=""  title="36a. OCCURRENCE SPAN CODE"  name="ub04Block_62"/>
<input type="text" data-dp   maxlength="10"  id="ub04id56" value=""  title="36a. OCCURRENCE SPAN DATE FROM"  name="ub04Block_63"/>
<input type="text" data-dp   maxlength="10"  id="ub04id57" value=""  title="36a. OCCURRENCE SPAN DATE THROUGH"  name="ub04Block_64"/>
<input type="text" disabled  maxlength="8"  id="ub04id58" value=""  title="37a. RESERVED"  name="ub04Block_71"/>
<input type="text"   maxlength="2"  id="ub04id59" value=""  title="31b. OCCURRENCE CODE"  name="ub04Block_51"/>
<input type="text" data-dp  maxlength="10"  id="ub04id60" value=""  title="31b. OCCURRENCE DATE"  name="ub04Block_52"/>
<input type="text"   maxlength="2"  id="ub04id61" value=""  title="32b. OCCURRENCE CODE"  name="ub04Block_53"/>
<input type="text" data-dp  maxlength="10"  id="ub04id62" value=""  title="32b. OCCURRENCE DATE"  name="ub04Block_54"/>
<input type="text"   maxlength="2"  id="ub04id63" value=""  title="33b. OCCURRENCE CODE"  name="ub04Block_55"/>
<input type="text" data-dp  maxlength="10"  id="ub04id64" value=""  title="33b. OCCURRENCE DATE"  name="ub04Block_56"/>
<input type="text"   maxlength="2"  id="ub04id65" value=""  title="34b. OCCURRENCE CODE"  name="ub04Block_57"/>
<input type="text" data-dp   maxlength="10"  id="ub04id66" value=""  title="34b. OCCURRENCE DATE"  name="ub04Block_58"/>
<input type="text"   maxlength="2"  id="ub04id67" value=""  title="35b. OCCURRENCE SPAN CODE"  name="ub04Block_65"/>
<input type="text" data-dp   maxlength="10"  id="ub04id68" value=""  title="35b. OCCURRENCE SPAN DATE FROM"  name="ub04Block_66"/>
<input type="text" data-dp   maxlength="10"  id="ub04id69" value=""  title="35b. OCCURRENCE SPAN DATE THROUGH"  name="ub04Block_67"/>
<input type="text"   maxlength="2"  id="ub04id70" value=""  title="36b. OCCURRENCE SPAN CODE"  name="ub04Block_68"/>
<input type="text" data-dp   maxlength="10"  id="ub04id71" value=""  title="36b. OCCURRENCE SPAN DATE FROM"  name="ub04Block_69"/>
<input type="text" data-dp   maxlength="10"  id="ub04id72" value=""  title="36b. OCCURRENCE SPAN DATE THROUGH"  name="ub04Block_70"/>
<input type="text" disabled  maxlength="8"  id="ub04id73" value=""  title="37b. RESERVED"  name="ub04Block_72"/>
<input type="text"   maxlength="2"  id="ub04id74" value=""  title="39a. VALUE CODE"  name="ub04Block_74"/>
<input type="text"   maxlength="9"  id="ub04id75" value=""  title="39a. VALUE AMOUNT"  name="ub04Block_75"/>
<input type="text"   maxlength="2"  id="ub04id76" value=""  title="40a. VALUE CODE"  name="ub04Block_76"/>
<input type="text"   maxlength="9"  id="ub04id77" value=""  title="40a. VALUE AMOUNT"  name="ub04Block_77"/>
<input type="text"   maxlength="2"  id="ub04id78" value=""  title="41a. VALUE CODE"  name="ub04Block_78"/>
<input type="text"   maxlength="9"  id="ub04id79" value=""  title="41a. VALUE AMOUNT"  name="ub04Block_79"/>
<input type="text"   maxlength="2"  id="ub04id80" value=""  title="39b. VALUE CODE"  name="ub04Block_80"/>
<input type="text"   maxlength="9"  id="ub04id81" value=""  title="39b. VALUE AMOUNT"  name="ub04Block_81"/>
<input type="text"   maxlength="2"  id="ub04id82" value=""  title="40b. VALUE CODE"  name="ub04Block_82"/>
<input type="text"   maxlength="9"  id="ub04id83" value=""  title="40b. VALUE AMOUNT"  name="ub04Block_83"/>
<input type="text"   maxlength="2"  id="ub04id84" value=""  title="41b. VALUE CODE"  name="ub04Block_84"/>
<input type="text"   maxlength="9"  id="ub04id85" value=""  title="41b. VALUE AMOUNT"  name="ub04Block_85"/>
<input type="text"   maxlength="2"  id="ub04id86" value=""  title="39c. VALUE CODE"  name="ub04Block_86"/>
<input type="text"   maxlength="9"  id="ub04id87" value=""  title="39c. VALUE AMOUNT"  name="ub04Block_87"/>
<input type="text"   maxlength="2"  id="ub04id88" value=""  title="40c. VALUE CODE"  name="ub04Block_88"/>
<input type="text"   maxlength="9"  id="ub04id89" value=""  title="40c. VALUE AMOUNT"  name="ub04Block_89"/>
<input type="text"   maxlength="2"  id="ub04id90" value=""  title="41c. VALUE CODE"  name="ub04Block_90"/>
<input type="text"   maxlength="9"  id="ub04id91" value=""  title="41c. VALUE AMOUNT"  name="ub04Block_91"/>
<input type="text"   maxlength="2"  id="ub04id92" value=""  title="39d. VALUE CODE"  name="ub04Block_92"/>
<input type="text"   maxlength="9"  id="ub04id93" value=""  title="39d. VALUE AMOUNT"  name="ub04Block_93"/>
<input type="text"   maxlength="2"  id="ub04id94" value=""  title="40d. VALUE CODE"  name="ub04Block_94"/>
<input type="text"   maxlength="9"  id="ub04id95" value=""  title="40d. VALUE AMOUNT"  name="ub04Block_95"/>
<input type="text"   maxlength="2"  id="ub04id96" value=""  title="41d. VALUE CODE"  name="ub04Block_96"/>
<input type="text"   maxlength="9"  id="ub04id97" value=""  title="41d. VALUE AMOUNT"  name="ub04Block_97"/>
<textarea   id="ub04id98"  title="38. RESPONSIBLE PARTY NAME AND ADDRESS"  name="ub04Block_73"></textarea>
<input type="text" class="revcode" maxlength="4"  id="ub04id99" value=""   title="42. REVENUE CODE, Line 1"  name="ub04Block_98"/>
<input type="text"   maxlength="25"  id="ub04id100" value="" title="43. REVENUE DESCRIPTION, Line 1"  name="ub04Block_99"/>
<input type="text"   maxlength="24"  id="ub04id101" value="" title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 1"  name="ub04Block_100"/>
<input type="text"   maxlength="10"  id="ub04id102" value=""  title="45. SERVICE DATE, Line 1"  name="ub04Block_101"/>
<input type="text"   maxlength="7"  id="ub04id103" value=""  title="46. SERVICE UNITS, Line 1"  name="ub04Block_102"/>
<input type="text"   maxlength="9"  id="ub04id104" value=""  title="47. TOTAL CHARGES, Line 1"  name="ub04Block_103"/>
<input type="text"   maxlength="9"  id="ub04id105" value=""  title="48. NON-COVERED CHARGES, Line 1"  name="ub04Block_104"/>
<input type="text" disabled  maxlength="2"  id="ub04id106" value=""  title="49. RESERVED, Line 1"  name="ub04Block_105"/>
<input type="text" class="revcode" maxlength="4"  id="ub04id107" value=""  title="42. REVENUE CODE, Line 2"  name="ub04Block_106"/>
<input type="text"   maxlength="25"  id="ub04id108" value=""  title="43. REVENUE DESCRIPTION, Line 2"  name="ub04Block_107"/>
<input type="text"   maxlength="24"  id="ub04id109" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 2"  name="ub04Block_108"/>
<input type="text"   maxlength="10"  id="ub04id110" value=""  title="45. SERVICE DATE, Line 2"  name="ub04Block_109"/>
<input type="text"   maxlength="7"  id="ub04id111" value=""  title="46. SERVICE UNITS, Line 2"  name="ub04Block_110"/>
<input type="text"   maxlength="9"  id="ub04id112" value=""  title="47. TOTAL CHARGES, Line 2"  name="ub04Block_111"/>
<input type="text"   maxlength="9"  id="ub04id113" value=""  title="48. NON-COVERED CHARGES, Line 2"  name="ub04Block_112"/>
<input type="text" disabled  maxlength="2"  id="ub04id114" value=""  title="49. RESERVED, Line 2"  name="ub04Block_113"/>
<input type="text"   maxlength="4"  id="ub04id115" value=""  title="42. REVENUE CODE, Line 3"  name="ub04Block_114"/>
<input type="text"   maxlength="25"  id="ub04id116" value=""  title="43. REVENUE DESCRIPTION, Line 3"  name="ub04Block_115"/>
<input type="text"   maxlength="24"  id="ub04id117" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 3"  name="ub04Block_116"/>
<input type="text" class="revcode" maxlength="10"  id="ub04id118" value=""  title="45. SERVICE DATE, Line 3"  name="ub04Block_117"/>
<input type="text"   maxlength="7"  id="ub04id119" value=""  title="46. SERVICE UNITS, Line 3"  name="ub04Block_118"/>
<input type="text"   maxlength="9"  id="ub04id120" value=""  title="47. TOTAL CHARGES, Line 3"  name="ub04Block_119"/>
<input type="text"   maxlength="9"  id="ub04id121" value=""  title="48. NON-COVERED CHARGES, Line 3"  name="ub04Block_120"/>
<input type="text"  disabled maxlength="2"  id="ub04id122" value=""  title="49. RESERVED, Line 3"  name="ub04Block_121"/>
<input type="text"   maxlength="4"  id="ub04id123" value=""  title="42. REVENUE CODE, Line 4"  name="ub04Block_122"/>
<input type="text"   maxlength="25"  id="ub04id124" value=""  title="43. REVENUE DESCRIPTION, Line 4"  name="ub04Block_123"/>
<input type="text"   maxlength="24"  id="ub04id125" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 4"  name="ub04Block_124"/>
<input type="text"   maxlength="10"  id="ub04id126" value=""  title="45. SERVICE DATE, Line 4"  name="ub04Block_125"/>
<input type="text"   maxlength="7"  id="ub04id127" value=""  title="46. SERVICE UNITS, Line 4"  name="ub04Block_126"/>
<input type="text"   maxlength="9"  id="ub04id128" value=""  title="47. TOTAL CHARGES, Line 4"  name="ub04Block_127"/>
<input type="text"   maxlength="9"  id="ub04id129" value=""  title="48. NON-COVERED CHARGES, Line 4"  name="ub04Block_128"/>
<input type="text" disabled  maxlength="2"  id="ub04id130" value=""  title="49. RESERVED, Line 4"  name="ub04Block_129"/>
<input type="text"   maxlength="4"  id="ub04id131" value=""  title="42. REVENUE CODE, Line 5"  name="ub04Block_130"/>
<input type="text"   maxlength="25"  id="ub04id132" value=""  title="43. REVENUE DESCRIPTION, Line 5"  name="ub04Block_131"/>
<input type="text"   maxlength="24"  id="ub04id133" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 5"  name="ub04Block_132"/>
<input type="text"   maxlength="10"  id="ub04id134" value=""  title="45. SERVICE DATE, Line 5"  name="ub04Block_133"/>
<input type="text"   maxlength="7"  id="ub04id135" value=""  title="46. SERVICE UNITS, Line 5"  name="ub04Block_134"/>
<input type="text"   maxlength="9"  id="ub04id136" value=""  title="47. TOTAL CHARGES, Line 5"  name="ub04Block_135"/>
<input type="text"   maxlength="9"  id="ub04id137" value=""  title="48. NON-COVERED CHARGES, Line 5"  name="ub04Block_136"/>
<input type="text" disabled  maxlength="2"  id="ub04id138" value=""  title="49. RESERVED, Line 5"  name="ub04Block_137"/>
<input type="text"   maxlength="4"  id="ub04id139" value=""  title="42. REVENUE CODE, Line 6"  name="ub04Block_138"/>
<input type="text"   maxlength="25"  id="ub04id140" value=""  title="43. REVENUE DESCRIPTION, Line 6"  name="ub04Block_139"/>
<input type="text"   maxlength="24"  id="ub04id141" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 6"  name="ub04Block_140"/>
<input type="text"   maxlength="10"  id="ub04id142" value=""  title="45. SERVICE DATE, Line 6"  name="ub04Block_141"/>
<input type="text"   maxlength="7"  id="ub04id143" value=""  title="46. SERVICE UNITS, Line 6"  name="ub04Block_142"/>
<input type="text"   maxlength="9"  id="ub04id144" value=""  title="47. TOTAL CHARGES, Line 6"  name="ub04Block_143"/>
<input type="text"   maxlength="9"  id="ub04id145" value=""  title="48. NON-COVERED CHARGES, Line 6"  name="ub04Block_144"/>
<input type="text" disabled  maxlength="2"  id="ub04id146" value=""  title="49. RESERVED, Line 6"  name="ub04Block_145"/>
<input type="text"   maxlength="4"  id="ub04id147" value=""  title="42. REVENUE CODE, Line 7"  name="ub04Block_146"/>
<input type="text"   maxlength="25"  id="ub04id148" value=""  title="43. REVENUE DESCRIPTION, Line 7"  name="ub04Block_147"/>
<input type="text"   maxlength="24"  id="ub04id149" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 7"  name="ub04Block_148"/>
<input type="text"   maxlength="10"  id="ub04id150" value=""  title="45. SERVICE DATE, Line 7"  name="ub04Block_149"/>
<input type="text"   maxlength="7"  id="ub04id151" value=""  title="46. SERVICE UNITS, Line 7"  name="ub04Block_150"/>
<input type="text"   maxlength="9"  id="ub04id152" value=""  title="47. TOTAL CHARGES, Line 7"  name="ub04Block_151"/>
<input type="text"   maxlength="9"  id="ub04id153" value=""  title="48. NON-COVERED CHARGES, Line 7"  name="ub04Block_152"/>
<input type="text" disabled  maxlength="2"  id="ub04id154" value=""  title="49. RESERVED, Line 7"  name="ub04Block_153"/>
<input type="text"   maxlength="4"  id="ub04id155" value=""  title="42. REVENUE CODE, Line 8"  name="ub04Block_154"/>
<input type="text"   maxlength="25"  id="ub04id156" value=""  title="43. REVENUE DESCRIPTION, Line 8"  name="ub04Block_155"/>
<input type="text"   maxlength="24"  id="ub04id157" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 8"  name="ub04Block_156"/>
<input type="text"   maxlength="10"  id="ub04id158" value=""  title="45. SERVICE DATE, Line 8"  name="ub04Block_157"/>
<input type="text"   maxlength="7"  id="ub04id159" value=""  title="46. SERVICE UNITS, Line 8"  name="ub04Block_158"/>
<input type="text"   maxlength="9"  id="ub04id160" value=""  title="47. TOTAL CHARGES, Line 8"  name="ub04Block_159"/>
<input type="text"   maxlength="9"  id="ub04id161" value=""  title="48. NON-COVERED CHARGES, Line 8"  name="ub04Block_160"/>
<input type="text"   maxlength="2"  id="ub04id162" value=""  title="49. RESERVED, Line 8"  name="ub04Block_161"/>
<input type="text"   maxlength="4"  id="ub04id163" value=""  title="42. REVENUE CODE, Line 9"  name="ub04Block_162"/>
<input type="text"   maxlength="25"  id="ub04id164" value=""  title="43. REVENUE DESCRIPTION, Line 9"  name="ub04Block_163"/>
<input type="text"   maxlength="24"  id="ub04id165" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 9"  name="ub04Block_164"/>
<input type="text"   maxlength="10"  id="ub04id166" value=""  title="45. SERVICE DATE, Line 9"  name="ub04Block_165"/>
<input type="text"   maxlength="7"  id="ub04id167" value=""  title="46. SERVICE UNITS, Line 9"  name="ub04Block_166"/>
<input type="text"   maxlength="9"  id="ub04id168" value=""  title="47. TOTAL CHARGES, Line 9"  name="ub04Block_167"/>
<input type="text"   maxlength="9"  id="ub04id169" value=""  title="48. NON-COVERED CHARGES, Line 9"  name="ub04Block_168"/>
<input type="text" disabled  maxlength="2"  id="ub04id170" value=""  title="49. RESERVED, Line 9"  name="ub04Block_169"/>
<input type="text"   maxlength="4"  id="ub04id171" value=""  title="42. REVENUE CODE, Line 10"  name="ub04Block_170"/>
<input type="text"   maxlength="25"  id="ub04id172" value=""  title="43. REVENUE DESCRIPTION, Line 10"  name="ub04Block_171"/>
<input type="text"   maxlength="24"  id="ub04id173" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 10"  name="ub04Block_172"/>
<input type="text"   maxlength="10"  id="ub04id174" value=""  title="45. SERVICE DATE, Line 10"  name="ub04Block_173"/>
<input type="text"   maxlength="7"  id="ub04id175" value=""  title="46. SERVICE UNITS, Line 10"  name="ub04Block_174"/>
<input type="text"   maxlength="9"  id="ub04id176" value=""  title="47. TOTAL CHARGES, Line 10"  name="ub04Block_175"/>
<input type="text"   maxlength="9"  id="ub04id177" value=""  title="48. NON-COVERED CHARGES, Line 10"  name="ub04Block_176"/>
<input type="text"  disabled maxlength="2"  id="ub04id178" value=""  title="49. RESERVED, Line 10"  name="ub04Block_177"/>
<input type="text"   maxlength="4"  id="ub04id179" value=""  title="42. REVENUE CODE, Line 11"  name="ub04Block_178"/>
<input type="text"   maxlength="25"  id="ub04id180" value=""  title="43. REVENUE DESCRIPTION, Line 11"  name="ub04Block_179"/>
<input type="text"   maxlength="24"  id="ub04id181" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 11"  name="ub04Block_180"/>
<input type="text"   maxlength="10"  id="ub04id182" value=""  title="45. SERVICE DATE, Line 11"  name="ub04Block_181"/>
<input type="text"   maxlength="7"  id="ub04id183" value=""  title="46. SERVICE UNITS, Line 11"  name="ub04Block_182"/>
<input type="text"   maxlength="9"  id="ub04id184" value=""  title="47. TOTAL CHARGES, Line 11"  name="ub04Block_183"/>
<input type="text"   maxlength="9"  id="ub04id185" value=""  title="48. NON-COVERED CHARGES, Line 11"  name="ub04Block_184"/>
<input type="text" disabled  maxlength="2"  id="ub04id186" value=""  title="49. RESERVED, Line 11"  name="ub04Block_185"/>
<input type="text"   maxlength="4"  id="ub04id187" value=""  title="42. REVENUE CODE, Line 12"  name="ub04Block_186"/>
<input type="text"   maxlength="25"  id="ub04id188" value=""  title="43. REVENUE DESCRIPTION, Line 12"  name="ub04Block_187"/>
<input type="text"   maxlength="24"  id="ub04id189" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 12"  name="ub04Block_188"/>
<input type="text"   maxlength="10"  id="ub04id190" value=""  title="45. SERVICE DATE, Line 12"  name="ub04Block_189"/>
<input type="text"   maxlength="7"  id="ub04id191" value=""  title="46. SERVICE UNITS, Line 12"  name="ub04Block_190"/>
<input type="text"   maxlength="9"  id="ub04id192" value=""  title="47. TOTAL CHARGES, Line 12"  name="ub04Block_191"/>
<input type="text"   maxlength="9"  id="ub04id193" value=""  title="48. NON-COVERED CHARGES, Line 12"  name="ub04Block_192"/>
<input type="text" disabled  maxlength="2"  id="ub04id194" value=""  title="49. RESERVED, Line 12"  name="ub04Block_193"/>
<input type="text"   maxlength="4"  id="ub04id195" value=""  title="42. REVENUE CODE, Line 13"  name="ub04Block_194"/>
<input type="text"   maxlength="25"  id="ub04id196" value=""  title="43. REVENUE DESCRIPTION, Line 13"  name="ub04Block_195"/>
<input type="text"   maxlength="24"  id="ub04id197" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 13"  name="ub04Block_196"/>
<input type="text"   maxlength="10"  id="ub04id198" value=""  title="45. SERVICE DATE, Line 13"  name="ub04Block_197"/>
<input type="text"   maxlength="7"  id="ub04id199" value=""  title="46. SERVICE UNITS, Line 13"  name="ub04Block_198"/>
<input type="text"   maxlength="9"  id="ub04id200" value=""  title="47. TOTAL CHARGES, Line 13"  name="ub04Block_199"/>
<input type="text"   maxlength="9"  id="ub04id201" value=""  title="48. NON-COVERED CHARGES, Line 13"  name="ub04Block_200"/>
<input type="text" disabled  maxlength="2"  id="ub04id202" value=""  title="49. RESERVED, Line 13"  name="ub04Block_201"/>
<input type="text"   maxlength="4"  id="ub04id203" value=""  title="42. REVENUE CODE, Line 14"  name="ub04Block_202"/>
<input type="text"   maxlength="25"  id="ub04id204" value=""  title="43. REVENUE DESCRIPTION, Line 14"  name="ub04Block_203"/>
<input type="text"   maxlength="24"  id="ub04id205" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 14"  name="ub04Block_204"/>
<input type="text"   maxlength="10"  id="ub04id206" value=""  title="45. SERVICE DATE, Line 14"  name="ub04Block_205"/>
<input type="text"   maxlength="7"  id="ub04id207" value=""  title="46. SERVICE UNITS, Line 14"  name="ub04Block_206"/>
<input type="text"   maxlength="9"  id="ub04id208" value=""  title="47. TOTAL CHARGES, Line 14"  name="ub04Block_207"/>
<input type="text"   maxlength="9"  id="ub04id209" value=""  title="48. NON-COVERED CHARGES, Line 14"  name="ub04Block_208"/>
<input type="text"  disabled maxlength="2"  id="ub04id210" value=""  title="49. RESERVED, Line 14"  name="ub04Block_209"/>
<input type="text"   maxlength="4"  id="ub04id211" value=""  title="42. REVENUE CODE, Line 15"  name="ub04Block_210"/>
<input type="text"   maxlength="25"  id="ub04id212" value=""  title="43. REVENUE DESCRIPTION, Line 15"  name="ub04Block_211"/>
<input type="text"   maxlength="24"  id="ub04id213" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 15"  name="ub04Block_212"/>
<input type="text"   maxlength="10"  id="ub04id214" value=""  title="45. SERVICE DATE, Line 15"  name="ub04Block_213"/>
<input type="text"   maxlength="7"  id="ub04id215" value=""  title="46. SERVICE UNITS, Line 15"  name="ub04Block_214"/>
<input type="text"   maxlength="9"  id="ub04id216" value=""  title="47. TOTAL CHARGES, Line 15"  name="ub04Block_215"/>
<input type="text"   maxlength="9"  id="ub04id217" value=""  title="48. NON-COVERED CHARGES, Line 15"  name="ub04Block_216"/>
<input type="text" disabled  maxlength="2"  id="ub04id218" value=""  title="49. RESERVED, Line 15"  name="ub04Block_217"/>
<input type="text"   maxlength="4"  id="ub04id219" value=""  title="42. REVENUE CODE, Line 16"  name="ub04Block_218"/>
<input type="text"   maxlength="25"  id="ub04id220" value=""  title="43. REVENUE DESCRIPTION, Line 16"  name="ub04Block_219"/>
<input type="text"   maxlength="24"  id="ub04id221" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 16"  name="ub04Block_220"/>
<input type="text"   maxlength="10"  id="ub04id222" value=""  title="45. SERVICE DATE, Line 16"  name="ub04Block_221"/>
<input type="text"   maxlength="7"  id="ub04id223" value=""  title="46. SERVICE UNITS, Line 16"  name="ub04Block_222"/>
<input type="text"   maxlength="9"  id="ub04id224" value=""  title="47. TOTAL CHARGES, Line 16"  name="ub04Block_223"/>
<input type="text"   maxlength="9"  id="ub04id225" value=""  title="48. NON-COVERED CHARGES, Line 16"  name="ub04Block_224"/>
<input type="text" disabled  maxlength="2"  id="ub04id226" value=""  title="49. RESERVED, Line 16"  name="ub04Block_225"/>
<input type="text"   maxlength="4"  id="ub04id227" value=""  title="42. REVENUE CODE, Line 17"  name="ub04Block_226"/>
<input type="text"   maxlength="25"  id="ub04id228" value=""  title="43. REVENUE DESCRIPTION, Line 17"  name="ub04Block_227"/>
<input type="text"   maxlength="24"  id="ub04id229" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 17"  name="ub04Block_228"/>
<input type="text"   maxlength="10"  id="ub04id230" value=""  title="45. SERVICE DATE, Line 17"  name="ub04Block_229"/>
<input type="text"   maxlength="7"  id="ub04id231" value=""  title="46. SERVICE UNITS, Line 17"  name="ub04Block_230"/>
<input type="text"   maxlength="9"  id="ub04id232" value=""  title="47. TOTAL CHARGES, Line 17"  name="ub04Block_231"/>
<input type="text"   maxlength="9"  id="ub04id233" value=""  title="48. NON-COVERED CHARGES, Line 17"  name="ub04Block_232"/>
<input type="text" disabled  maxlength="2"  id="ub04id234" value=""  title="49. RESERVED, Line 17"  name="ub04Block_233"/>
<input type="text"   maxlength="4"  id="ub04id235" value=""  title="42. REVENUE CODE, Line 18"  name="ub04Block_234"/>
<input type="text"   maxlength="25"  id="ub04id236" value=""  title="43. REVENUE DESCRIPTION, Line 18"  name="ub04Block_235"/>
<input type="text"   maxlength="24"  id="ub04id237" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 18"  name="ub04Block_236"/>
<input type="text"   maxlength="10"  id="ub04id238" value=""  title="45. SERVICE DATE, Line 18"  name="ub04Block_237"/>
<input type="text"   maxlength="7"  id="ub04id239" value=""  title="46. SERVICE UNITS, Line 18"  name="ub04Block_238"/>
<input type="text"   maxlength="9"  id="ub04id240" value=""  title="47. TOTAL CHARGES, Line 18"  name="ub04Block_239"/>
<input type="text"   maxlength="9"  id="ub04id241" value=""  title="48. NON-COVERED CHARGES, Line 18"  name="ub04Block_240"/>
<input type="text" disabled  maxlength="2"  id="ub04id242" value=""  title="49. RESERVED, Line 18"  name="ub04Block_241"/>
<input type="text"   maxlength="4"  id="ub04id243" value=""  title="42. REVENUE CODE, Line 19"  name="ub04Block_242"/>
<input type="text"   maxlength="25"  id="ub04id244" value=""  title="43. REVENUE DESCRIPTION, Line 19"  name="ub04Block_243"/>
<input type="text"   maxlength="24"  id="ub04id245" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 19"  name="ub04Block_244"/>
<input type="text"   maxlength="10"  id="ub04id246" value=""  title="45. SERVICE DATE, Line 19"  name="ub04Block_245"/>
<input type="text"   maxlength="7"  id="ub04id247" value=""  title="46. SERVICE UNITS, Line 19"  name="ub04Block_246"/>
<input type="text"   maxlength="9"  id="ub04id248" value=""  title="47. TOTAL CHARGES, Line 19"  name="ub04Block_247"/>
<input type="text"   maxlength="9"  id="ub04id249" value=""  title="48. NON-COVERED CHARGES, Line 19"  name="ub04Block_248"/>
<input type="text" disabled  maxlength="2"  id="ub04id250" value=""  title="49. RESERVED, Line 19"  name="ub04Block_249"/>
<input type="text"   maxlength="4"  id="ub04id251" value=""  title="42. REVENUE CODE, Line 20"  name="ub04Block_250"/>
<input type="text"   maxlength="25"  id="ub04id252" value=""  title="43. REVENUE DESCRIPTION, Line 20"  name="ub04Block_251"/>
<input type="text"   maxlength="24"  id="ub04id253" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 20"  name="ub04Block_252"/>
<input type="text"   maxlength="10"  id="ub04id254" value=""  title="45. SERVICE DATE, Line 20"  name="ub04Block_253"/>
<input type="text"   maxlength="7"  id="ub04id255" value=""  title="46. SERVICE UNITS, Line 20"  name="ub04Block_254"/>
<input type="text"   maxlength="9"  id="ub04id256" value=""  title="47. TOTAL CHARGES, Line 20"  name="ub04Block_255"/>
<input type="text"   maxlength="9"  id="ub04id257" value=""  title="48. NON-COVERED CHARGES, Line 20"  name="ub04Block_256"/>
<input type="text" disabled  maxlength="2"  id="ub04id258" value=""  title="49. RESERVED, Line 20"  name="ub04Block_257"/>
<input type="text"   maxlength="4"  id="ub04id259" value=""  title="42. REVENUE CODE, Line 21"  name="ub04Block_258"/>
<input type="text"   maxlength="25"  id="ub04id260" value=""  title="43. REVENUE DESCRIPTION, Line 21"  name="ub04Block_259"/>
<input type="text"   maxlength="24"  id="ub04id261" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 21"  name="ub04Block_260"/>
<input type="text"   maxlength="10"  id="ub04id262" value=""  title="45. SERVICE DATE, Line 21"  name="ub04Block_261"/>
<input type="text"   maxlength="7"  id="ub04id263" value=""  title="46. SERVICE UNITS, Line 21"  name="ub04Block_262"/>
<input type="text"   maxlength="9"  id="ub04id264" value=""  title="47. TOTAL CHARGES, Line 21"  name="ub04Block_263"/>
<input type="text"   maxlength="9"  id="ub04id265" value=""  title="48. NON-COVERED CHARGES, Line 21"  name="ub04Block_264"/>
<input type="text" disabled  maxlength="2"  id="ub04id266" value=""  title="49. RESERVED, Line 21"  name="ub04Block_265"/>
<input type="text"   maxlength="4"  id="ub04id267" value=""  title="42. REVENUE CODE, Line 22"  name="ub04Block_266"/>
<input type="text"   maxlength="25"  id="ub04id268" value=""  title="43. REVENUE DESCRIPTION, Line 22"  name="ub04Block_267"/>
<input type="text"   maxlength="24"  id="ub04id269" value=""  title="44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 22"  name="ub04Block_268"/>
<input type="text"   maxlength="10"  id="ub04id270" value=""  title="45. SERVICE DATE, Line 22"  name="ub04Block_269"/>
<input type="text"   maxlength="7"  id="ub04id271" value=""  title="46. SERVICE UNITS, Line 22"  name="ub04Block_270"/>
<input type="text"   maxlength="9"  id="ub04id272" value=""  title="47. TOTAL CHARGES, Line 22"  name="ub04Block_271"/>
<input type="text"   maxlength="9"  id="ub04id273" value=""  title="48. NON-COVERED CHARGES, Line 22"  name="ub04Block_272"/>
<input type="text" disabled  maxlength="2"  id="ub04id274" value=""  title="49. RESERVED, Line 22"  name="ub04Block_273"/>
<input type="text"    id="ub04id275" value=""  title="42. REVENUE CODE, Line 23"  name="ub04Block_274"/>
<input type="text"   maxlength="3"  id="ub04id276" value=""  title="43. CLAIM PAGE NUMBER"  name="ub04Block_275"/>
<input type="text"   maxlength="3"  id="ub04id277" value=""  title="43. TOTAL NUMBER OF CLAIM PAGES"  name="ub04Block_276"/>
<input type="text"    id="ub04id278" value=""  title="45. CREATION DATE, Line 23"  name="ub04Block_277"/>
<input type="text"    id="ub04id279" value=""  title="47. TOTAL OF TOTAL CHARGES, Line 23"  name="ub04Block_278"/>
<input type="text"    id="ub04id280" value=""  title="48. TOTAL OF NON-COVERED CHARGES, Line 23"  name="ub04Block_279"/>
<input type="text" disabled id="ub04id281" value=""  title="49. RESERVED, Line 23"  name="ub04Block_280"/>
<input type="text"   maxlength="10"  id="ub04id282" value="" title="56. NATIONAL PROVIDER IDENTIFIER - BILLING PROVIDER"  name="ub04Block_299"/>
<input type="text"   maxlength="23"  id="ub04id283" value="" title="50a. PRIMARY PAYER NAME"  name="ub04Block_281"/>
<input type="text"   maxlength="15"  id="ub04id284" value="" title="51a. PRIMARY PAYER HEALTH PLAN ID"  name="ub04Block_282"/>
<input type="text"   maxlength="1"  id="ub04id285" value="" title="52a. RELEASE OF INFORMATION CERTIFICATION INDICATOR, PRIMARY PAYER"  name="ub04Block_283"/>
<input type="text"   maxlength="1"  id="ub04id286" value="" title="53a. ASSIGNMENT OF BENEFITS CERTIFICATION INDICATOR, PRIMARY PAYER"  name="ub04Block_284"/>
<input type="text"   maxlength="10"  id="ub04id287" value="" title="54a. PRIMARY PAYER PRIOR PAYMENTS"  name="ub04Block_285"/>
<input type="text"   maxlength="10"  id="ub04id288" value="" title="55a. PRIMARY PAYER ESTIMATED AMOUNT DUE"  name="ub04Block_286"/>
<input type="text"    id="ub04id289" value=""  title="57a. OTHER (BILLING) PROVIDER IDENTIFIER"  name="ub04Block_300"/>
<input type="text"   maxlength="23"  id="ub04id290" value="" title="50b. SECONDARY PAYER NAME"  name="ub04Block_287"/>
<input type="text"   maxlength="15"  id="ub04id291" value="" title="51b. SECONDARY PAYER HEALTH PLAN ID"  name="ub04Block_288"/>
<input type="text"   maxlength="1"  id="ub04id292" value="" title="52b. RELEASE OF INFORMATION CERTIFICATION INDICATOR, SECONDARY PAYER"  name="ub04Block_289"/>
<input type="text"   maxlength="1"  id="ub04id293" value="" title="53b. ASSIGNMENT OF BENEFITS CERTIFICATION INDICATOR, SECONDARY PAYER"  name="ub04Block_290"/>
<input type="text"   maxlength="10"  id="ub04id294" value="" title="54b. SECONDARY PAYER PRIOR PAYMENTS"  name="ub04Block_291"/>
<input type="text"   maxlength="10"  id="ub04id295" value="" title="55b. SECONDARY PAYER ESTIMATED AMOUNT DUE"  name="ub04Block_292"/>
<input type="text"    id="ub04id296" value="" title="57b. OTHER (BILLING) PROVIDER IDENTIFIER"  name="ub04Block_301"/>
<input type="text"   maxlength="23"  id="ub04id297" value="" title="50c. TERTIARY PAYER NAME"  name="ub04Block_293"/>
<input type="text"   maxlength="15"  id="ub04id298" value="" title="51c. TERTIARY PAYER HEALTH PLAN ID"  name="ub04Block_294"/>
<input type="text"   maxlength="1"  id="ub04id299" value="" title="52c.RELEASE OF INFORMATION CERTIFICATION INDICATOR, TERTIARY PAYER"  name="ub04Block_295"/>
<input type="text"   maxlength="1"  id="ub04id300" value="" title="53c. ASSIGNMENT OF BENEFITS CERTIFICATION INDICATOR, TERTIARY PAYER"  name="ub04Block_296"/>
<input type="text"   maxlength="10"  id="ub04id301" value="" title="54c. TERTIARY PAYER PRIOR PAYMENTS"  name="ub04Block_297"/>
<input type="text"   maxlength="10"  id="ub04id302" value="" title="55c. TERTIARY PAYER ESTIMATED AMOUNT DUE"  name="ub04Block_298"/>
<input type="text"    id="ub04id303" value="" title="57c. OTHER (BILLING) PROVIDER IDENTIFIER"  name="ub04Block_302"/>
<input type="text"   maxlength="25"  id="ub04id304" value="" title="58a. INSURED'S NAME - PRIMARY PLAN"  name="ub04Block_303"/>
<input type="text"   maxlength="2"  id="ub04id305" value="" title="59a. PATIENT'S RELATIONSHIP TO INSURED - PRIMARY PLAN"  name="ub04Block_304"/>
<input type="text"   maxlength="20"  id="ub04id306" value="" title="60a. INSURED'S UNIQUE IDENTIFIER - PRIMARY PLAN"  name="ub04Block_305"/>
<input type="text"   maxlength="14"  id="ub04id307" value="" title="61a. INSURED'S GROUP NAME - PRIMARY PLAN"  name="ub04Block_306"/>
<input type="text"   maxlength="17"  id="ub04id308" value="" title="62a. INSURANCE GROUP NUMBER - PRIMARY PLAN"  name="ub04Block_307"/>
<input type="text"   maxlength="25"  id="ub04id309" value="" title="58b. INSURED'S NAME - SECONDARY PLAN"  name="ub04Block_308"/>
<input type="text"   maxlength="2"  id="ub04id310" value="" title="59b. PATIENT'S RELATIONSHIP TO INSURED - SECONDARY PLAN"  name="ub04Block_309"/>
<input type="text"   maxlength="20"  id="ub04id311" value="" title="60b. INSURED'S UNIQUE IDENTIFIER - SECONDARY PLAN"  name="ub04Block_310"/>
<input type="text"   maxlength="14"  id="ub04id312" value="" title="61b. INSURED'S GROUP NAME - SECONDARY PLAN"  name="ub04Block_311"/>
<input type="text"   maxlength="17"  id="ub04id313" value="" title="62b. INSURANCE GROUP NUMBER - SECONDARY PLAN"  name="ub04Block_312"/>
<input type="text"   maxlength="25"  id="ub04id314" value="" title="58c. INSURED'S NAME - TERTIARY PLAN"  name="ub04Block_313"/>
<input type="text"   maxlength="2"  id="ub04id315" value="" title="59c. PATIENT'S RELATIONSHIP TO INSURED - TERTIARY PLAN"  name="ub04Block_314"/>
<input type="text"   maxlength="20"  id="ub04id316" value="" title="60c. INSURED'S UNIQUE IDENTIFIER - TERTIARY PLAN"  name="ub04Block_315"/>
<input type="text"   maxlength="14"  id="ub04id317" value="" title="61c. INSURED'S GROUP NAME - TERTIARY PLAN"  name="ub04Block_316"/>
<input type="text"   maxlength="17"  id="ub04id318" value="" title="62c. INSURANCE GROUP NUMBER - TERTIARY PLAN"  name="ub04Block_317"/>
<input type="text"   maxlength="30"  id="ub04id319" value="" title="63a. TREATMENT AUTHORIZATION CODES - PRIMARY PLAN"  name="ub04Block_318"/>
<input type="text"   maxlength="26"  id="ub04id320" value="" title="64a. DOCUMENT CONTROL NUMBER (DCN) - PRIMARY PLAN"  name="ub04Block_319"/>
<input type="text"   maxlength="25"  id="ub04id321" value="" title="65a. INSURED'S EMPLOYER NAME - PRIMARY PLAN"  name="ub04Block_320"/>
<input type="text"   maxlength="30"  id="ub04id322" value="" title="63b. TREATMENT AUTHORIZATION CODES - SECONDARY PLAN"  name="ub04Block_321"/>
<input type="text"   maxlength="26"  id="ub04id323" value="" title="64b. DOCUMENT CONTROL NUMBER (DCN) - SECONDARY PLAN"  name="ub04Block_322"/>
<input type="text"   maxlength="25"  id="ub04id324" value="" title="65b. INSURED'S EMPLOYER NAME - SECONDARY PLAN"  name="ub04Block_323"/>
<input type="text"   maxlength="30"  id="ub04id325" value="" title="63c. TREATMENT AUTHORIZATION CODES - TERTIARY PLAN"  name="ub04Block_324"/>
<input type="text"   maxlength="26"  id="ub04id326" value="" title="64c. DOCUMENT CONTROL NUMBER (DCN) - TERTIARY PLAN"  name="ub04Block_325"/>
<input type="text"   maxlength="25"  id="ub04id327" value="" title="65c. INSURED'S EMPLOYER NAME - TERTIARY PLAN"  name="ub04Block_326"/>
<input type="text"   maxlength="7"  id="ub04id328" value="" title="67. PRINCIPAL DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_328"/>
<input type="text"   maxlength="1"  id="ub04id329" value="" title="67. PRINCIPAL DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_329"/>
<input type="text"   maxlength="7"  id="ub04id330" value="" title="67A. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_330"/>
<input type="text"   maxlength="1"  id="ub04id331" value="" title="67A. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_331"/>
<input type="text"   maxlength="7"  id="ub04id332" value="" title="67B. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_332"/>
<input type="text"   maxlength="1"  id="ub04id333" value="" title="67B. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_333"/>
<input type="text"   maxlength="7"  id="ub04id334" value="" title="67C. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_334"/>
<input type="text"   maxlength="1"  id="ub04id335" value="" title="67C. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_335"/>
<input type="text"   maxlength="7"  id="ub04id336" value="" title="67D. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_336"/>
<input type="text"   maxlength="1"  id="ub04id337" value="" title="67D. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_337"/>
<input type="text"   maxlength="7"  id="ub04id338" value="" title="67E. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_338"/>
<input type="text"   maxlength="1"  id="ub04id339" value="" title="67E. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_339"/>
<input type="text"   maxlength="7"  id="ub04id340" value="" title="67F. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_340"/>
<input type="text"   maxlength="1"  id="ub04id341" value="" title="67F. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_341"/>
<input type="text"   maxlength="7"  id="ub04id342" value="" title="67G. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_342"/>
<input type="text"   maxlength="1"  id="ub04id343" value="" title="67G. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_343"/>
<input type="text"   maxlength="7"  id="ub04id344" value="" title="67H. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_344"/>
<input type="text"   maxlength="1"  id="ub04id345" value="" title="67H. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_345"/>
<input type="text" disabled  maxlength="8"  id="ub04id346" value="" title="68. RESERVED"  name="ub04Block_364"/>
<input type="text"   maxlength="1"  id="ub04id347" value="" title="66. DIAGNOSIS AND PROCEDURE CODE QUALIFIER (ICD VERSION INDICATOR)"  name="ub04Block_327"/>
<input type="text"   maxlength="7"  id="ub04id348" value="" title="67I. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_346"/>
<input type="text"   maxlength="1"  id="ub04id349" value="" title="67I. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_347"/>
<input type="text"   maxlength="7"  id="ub04id350" value="" title="67J. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_348"/>
<input type="text"   maxlength="1"  id="ub04id351" value="" title="67J. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_349"/>
<input type="text"   maxlength="7"  id="ub04id352" value="" title="67K. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_350"/>
<input type="text"   maxlength="1"  id="ub04id353" value="" title="67K. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_351"/>
<input type="text"   maxlength="7"  id="ub04id354" value="" title="67L. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_352"/>
<input type="text"   maxlength="1"  id="ub04id355" value="" title="67L. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_353"/>
<input type="text"   maxlength="7"  id="ub04id356" value="" title="67M. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_354"/>
<input type="text"   maxlength="1"  id="ub04id357" value="" title="67M. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_355"/>
<input type="text"   maxlength="7"  id="ub04id358" value="" title="67N. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_356"/>
<input type="text"   maxlength="1"  id="ub04id359" value="" title="67N. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_357"/>
<input type="text"   maxlength="7"  id="ub04id360" value="" title="67O. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_358"/>
<input type="text"   maxlength="1"  id="ub04id361" value="" title="67O. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_359"/>
<input type="text"   maxlength="7"  id="ub04id362" value="" title="67P. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_360"/>
<input type="text"   maxlength="7"  id="ub04id363" value="" title="67Q. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_362"/>
<input type="text"   maxlength="1"  id="ub04id364" value="" title="67Q. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_363"/>
<input type="text" disabled  maxlength="9"  id="ub04id365" value="" title="68. RESERVED"  name="ub04Block_365"/>
<input type="text"   maxlength="1"  id="ub04id366" value="" title="67P. OTHER DIAGNOSIS CODE AND POA INDICATOR"  name="ub04Block_361"/>
<input type="text"   maxlength="7"  id="ub04id367" value="" title="69. ADMITTING DIAGNOSIS CODE"  name="ub04Block_366"/>
<input type="text"   maxlength="7"  id="ub04id368" value="" title="70a. PATIENT'S REASON FOR VISIT"  name="ub04Block_367"/>
<input type="text"   maxlength="7"  id="ub04id369" value="" title="70b. PATIENT'S REASON FOR VISIT"  name="ub04Block_368"/>
<input type="text"   maxlength="7"  id="ub04id370" value="" title="70c. PATIENT'S REASON FOR VISIT"  name="ub04Block_369"/>
<input type="text"   maxlength="4"  id="ub04id371" value="" title="71. PROSPECTIVE PAYMENT SYSTEM (PPS) CODE"  name="ub04Block_370"/>
<input type="text"   maxlength="7"  id="ub04id372" value="" title="72a. EXTERNAL CAUSE OF INJURY (ECI) CODE AND POA INDICATOR"  name="ub04Block_371"/>
<input type="text"   maxlength="1"  id="ub04id373" value="" title="72a. EXTERNAL CAUSE OF INJURY (ECI) PRESENT ON ADMISSION INDICATOR "  name="ub04Block_372"/>
<input type="text"   maxlength="7"  id="ub04id374" value="" title="72b. EXTERNAL CAUSE OF INJURY (ECI) CODE AND POA INDICATOR"  name="ub04Block_373"/>
<input type="text"   maxlength="1"  id="ub04id375" value="" title="72b. EXTERNAL CAUSE OF INJURY (ECI) PRESENT ON ADMISSION INDICATOR "  name="ub04Block_374"/>
<input type="text"   maxlength="7"  id="ub04id376" value="" title="72c. EXTERNAL CAUSE OF INJURY (ECI) CODE AND POA INDICATOR"  name="ub04Block_375"/>
<input type="text"   maxlength="1"  id="ub04id377" value="" title="72c. EXTERNAL CAUSE OF INJURY (ECI) PRESENT ON ADMISSION INDICATOR "  name="ub04Block_376"/>
<input type="text" disabled  maxlength="9"  id="ub04id378" value="" title="73. RESERVED"  name="ub04Block_377"/>
<input type="text" class="user"  maxlength="11"  id="ub04id379" value="" title="76. ATTENDING PROVIDER NATIONAL PROVIDER IDENTIFIER"  name="ub04Block_391"/>
<input type="text"   maxlength="2"  id="ub04id380" value="" title="76. ATTENDING PROVIDER SECONDARY IDENTIFIER QUALIFIER"  name="ub04Block_392"/>
<input type="text"   maxlength="9"  id="ub04id381" value="" title="76. ATTENDING PROVIDER SECONDARY IDENTIFIER"  name="ub04Block_393"/>
<input type="text"   maxlength="7"  id="ub04id382" value="" title="74. PRINCIPAL PROCEDURE CODE"  name="ub04Block_378"/>
<input type="text"   maxlength="10"  id="ub04id383" value="" title="74. PRINCIPAL PROCEDURE DATE"  name="ub04Block_379"/>
<input type="text"   maxlength="7"  id="ub04id384" value="" title="74a. OTHER PROCEDURE CODE"  name="ub04Block_380"/>
<input type="text"   maxlength="10"  id="ub04id385" value="" title="74a. OTHER PROCEDURE DATE"  name="ub04Block_381"/>
<input type="text"   maxlength="7"  id="ub04id386" value="" title="74b. OTHER PROCEDURE CODE"  name="ub04Block_382"/>
<input type="text"   maxlength="10"  id="ub04id387" value="" title="74b. OTHER PROCEDURE DATE"  name="ub04Block_383"/>
<input type="text"   maxlength="16"  id="ub04id388" value="" title="76. ATTENDING PROVIDER LAST NAME"  name="ub04Block_394"/>
<input type="text"   maxlength="12"  id="ub04id389" value="" title="76. ATTENDING PROVIDER FIRST NAME"  name="ub04Block_395"/>
<input type="text" class="user" maxlength="11"  id="ub04id390" value="" title="77. OPERATING PHYSICIAN NATIONAL PROVIDER IDENTIFIER"  name="ub04Block_396"/>
<input type="text"   maxlength="2"  id="ub04id391" value="" title="77. OPERATING PHYSICIAN SECONDARY IDENTIFIER QUALIFIER"  name="ub04Block_397"/>
<input type="text"   maxlength="9"  id="ub04id392" value="" title="77. OPERATING PHYSICIAN SECONDARY IDENTIFIER"  name="ub04Block_398"/>
<input type="text"   maxlength="7"  id="ub04id393" value="" title="74c. OTHER PROCEDURE CODE"  name="ub04Block_384"/>
<input type="text"   maxlength="10"  id="ub04id394" value="" title="74c. OTHER PROCEDURE DATE"  name="ub04Block_385"/>
<input type="text"   maxlength="7"  id="ub04id395" value="" title="74d. OTHER PROCEDURE CODE"  name="ub04Block_386"/>
<input type="text"   maxlength="10"  id="ub04id396" value="" title="74d. OTHER PROCEDURE DATE"  name="ub04Block_387"/>
<input type="text"   maxlength="7"  id="ub04id397" value="" title="74e. OTHER PROCEDURE CODE"  name="ub04Block_388"/>
<input type="text"   maxlength="10"  id="ub04id398" value="" title="74d. OTHER PROCEDURE DATE"  name="ub04Block_389"/>
<textarea disabled maxlength="16"  id="ub04id399" title="75. RESERVED"  name="ub04Block_390"></textarea>
<input type="text"   maxlength="16"  id="ub04id400" value="" title="77. OPERATING PHYSICIAN LAST NAME"  name="ub04Block_399"/>
<input type="text"   maxlength="12"  id="ub04id401" value="" title="77. OPERATING PHYSICIAN FIRST NAME"  name="ub04Block_400"/>
<input type="text"   maxlength="2"  id="ub04id402" value="" title="81a. CODE QUALIFIER"  name="ub04Block_417"/>
<input type="text"   maxlength="10"  id="ub04id403" value="" title="81a. CODE"  name="ub04Block_421"/>
<input type="text"   maxlength="12"  id="ub04id404" value="" title="81a. VALUE"  name="ub04Block_425"/>
<input type="text"   maxlength="2"  id="ub04id405" value="" title="79. OTHER PROVIDER TYPE QUALIFIER"  name="ub04Block_401"/>
<input type="text" class="user" maxlength="11"  id="ub04id406" value="" title="78. OTHER PROVIDER NATIONAL PROVIDER IDENTIFIER"  name="ub04Block_402"/>
<input type="text"   maxlength="2"  id="ub04id407" value="" title="78. OTHER PROVIDER SECONDARY IDENTIFIER QUALIFIER"  name="ub04Block_403"/>
<input type="text"   maxlength="9"  id="ub04id408" value="" title="78. OTHER PROVIDER SECONDARY IDENTIFIER"  name="ub04Block_404"/>
<input type="text"   maxlength="19"  id="ub04id409" value="" title="80. REMARKS"  name="ub04Block_413"/>
<input type="text"   maxlength="2"  id="ub04id410" value="" title="81b. CODE QUALIFIER"  name="ub04Block_418"/>
<input type="text"   maxlength="10"  id="ub04id411" value="" title="81b. CODE"  name="ub04Block_422"/>
<input type="text"   maxlength="12"  id="ub04id412" value="" title="81b. VALUE"  name="ub04Block_426"/>
<input type="text"   maxlength="16"  id="ub04id413" value="" title="78. OTHER PROVIDER LAST NAME"  name="ub04Block_405"/>
<input type="text"   maxlength="12"  id="ub04id414" value="" title="78. OTHER PROVIDER FIRST NAME"  name="ub04Block_406"/>
<input type="text"   maxlength="24"  id="ub04id415" value="" title="80. REMARKS"  name="ub04Block_414"/>
<input type="text"   maxlength="2"  id="ub04id416" value="" title="81c. CODE QUALIFIER"  name="ub04Block_419"/>
<input type="text"   maxlength="10"  id="ub04id417" value="" title="81c. CODE"  name="ub04Block_423"/>
<input type="text"   maxlength="12"  id="ub04id418" value="" title="81c. VALUE"  name="ub04Block_427"/>
<input type="text"   maxlength="2"  id="ub04id419" value="" title="79. OTHER PROVIDER TYPE QUALIFIER"  name="ub04Block_407"/>
<input type="text" class="user"  maxlength="11"  id="ub04id420" value="" title="79. OTHER PROVIDER NATIONAL PROVIDER IDENTIFIER"  name="ub04Block_408"/>
<input type="text"   maxlength="9"  id="ub04id421" value="" title="79. OTHER PROVIDER SECONDARY IDENTIFIER"  name="ub04Block_410"/>
<input type="text"   maxlength="2"  id="ub04id422" value="" title="79. OTHER PROVIDER SECONDARY IDENTIFIER QUALIFIER"  name="ub04Block_409"/>
<input type="text"   maxlength="24"  id="ub04id423" value="" title="80. REMARKS"  name="ub04Block_415"/>
<input type="text"   maxlength="2"  id="ub04id424" value="" title="81d. CODE QUALIFIER"  name="ub04Block_420"/>
<input type="text"   maxlength="10"  id="ub04id425" value="" title="81d. CODE"  name="ub04Block_424"/>
<input type="text"   maxlength="12"  id="ub04id426" value="" title="81d. VALUE"  name="ub04Block_428"/>
<input type="text"   maxlength="16"  id="ub04id427" value="" title="79. OTHER PROVIDER LAST NAME"  name="ub04Block_411"/>
<input type="text"   maxlength="12"  id="ub04id428" value="" title="79. OTHER PROVIDER FIRST NAME"  name="ub04Block_412"/>
<input type="text"   maxlength="24"  id="ub04id429" value="" title="80. REMARKS"  name="ub04Block_416"/>
</form>
<!-- End Form Data -->
</div>
</div>
</body>
</html>
