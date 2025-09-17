<h3>DateTimepicker</h3>
<p><input id="datetimepicker" type="text" value="2014/03/15 05:06" /></p>
<h3>Use mask DateTimepicker</h3>
<p><input id="datetimepicker_mask" type="text" value="" /></p>
<h3>TimePicker</h3>
<p><input id="datetimepicker1" type="text" /></p>
<h3>DatePicker</h3>
<p><input id="datetimepicker2" type="text" /></p>
<h3>Inline DateTimePicker</h3>
<p><input id="datetimepicker3" type="text" /></p>
<h3>Dark theme</h3>
<p><input id="datetimepicker_dark" type="text" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#datetimepicker_mask').datetimepicker({
  mask:'9999/19/39 29:59',
});
jQuery('#datetimepicker').datetimepicker();
jQuery('#datetimepicker').datetimepicker({value:'2015/04/15 05:06'});
jQuery('#datetimepicker1').datetimepicker({
  datepicker:false,
  format:'H:i',
  value:'12:00'
});
jQuery('#datetimepicker2').datetimepicker({
  timepicker:false,
  format:'d/m/Y'
});
jQuery('#datetimepicker3').datetimepicker({
  inline:true,
  weeks:true
});
jQuery('#datetimepicker_dark').datetimepicker({
  	inline:true,
  	theme:'dark'
});
});
// ]]></script>
[include scripts/pp/reklama1.php]
<h2>How do I use it?</h2>
<p>First include to page css and js files</p>
<pre><code class="language-html">&lt;!-- this should go after your &lt;/body&gt; --&gt;
&lt;link rel="stylesheet" type="text/css" href="jquery.datetimepicker.css"/ &gt;
&lt;script src="jquery.js"&gt;&lt;/script&gt;
&lt;script src="build/jquery.datetimepicker.full.min.js"&gt;&lt;/script&gt;</code></pre>
<h2>Examples</h2>
<hr id="Simple" />
<h4>Simple init DateTimePicker Example <a href="#Simple">#</a></h4>
<p>HTML</p>
<pre><code class="language-html">&lt;input id="datetimepicker" type="text" &gt;</code></pre>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker').datetimepicker();</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker" type="text" value="2014/03/15 05:06" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){jQuery('#_datetimepicker').datetimepicker();});
// ]]></script>
<hr id="i18n" />
<h4>i18n DatePicker Example <a href="#i18n">#</a></h4>
<p>All supported languages <a href="#lang">here</a></p>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery.datetimepicker.setLocale('de');

jQuery('#datetimepicker1').datetimepicker({
 i18n:{
  de:{
   months:[
    'Januar','Februar','März','April',
    'Mai','Juni','Juli','August',
    'September','Oktober','November','Dezember',
   ],
   dayOfWeek:[
    "So.", "Mo", "Di", "Mi", 
    "Do", "Fr", "Sa.",
   ]
  }
 },
 timepicker:false,
 format:'d.m.Y'
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker1" type="text" value="15.08.2013" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker1').datetimepicker({
 lang:'de',
 i18n:{de:{
  months:[
   'Januar','Februar','Marz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'
  ],
  dayOfWeek:["So.", "Mo", "Di", "Mi", "Do", "Fr", "Sa."]
 }},
timepicker:false,
format:'d.m.Y'
});
});
// ]]></script>
<hr id="TimePicker" />
<h4>Only TimePicker Example <a href="#TimePicker">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker2').datetimepicker({
  datepicker:false,
  format:'H:i'
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker2" type="text" value="23:16" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker2').datetimepicker({
  datepicker:false,
  format:'H:i'
});
});
// ]]></script>
<h3 id="startdateex">Date Time Picker start date <a href="#startdateex">#</a></h3>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker_start_time').datetimepicker({
  startDate:'+1971/05/01'//or 1986/12/08
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="datetimepicker_start_time" type="text" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
  jQuery('#datetimepicker_start_time').datetimepicker({
    startDate:'+1971/05/01'
  });
});
// ]]></script>
<h3 id="unixtime">Date Time Picker from unixtime <a href="#unixtime">#</a></h3>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker_unixtime').datetimepicker({
  format:'unixtime'
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="datetimepicker_unixtime" type="text" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
  jQuery('#datetimepicker_unixtime').datetimepicker({
    format:'unixtime'
  });
});
// ]]></script>
<hr id="Inline" />
<h4>Inline DateTimePicker Example <a href="#Inline">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker3').datetimepicker({
  format:'d.m.Y H:i',
  inline:true,
  lang:'ru'
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker3" type="text" value="10.12.2013 23:45" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker3').datetimepicker({
 format:'d.m.Y H:i',
 inline:true,
 lang:'en'
});
});
// ]]></script>
<hr id="trigger" />
<h4>Icon trigger <a href="#trigger">#</a></h4>
<p>Click the icon next to the input field to show the datetimepicker</p>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker4').datetimepicker({
  format:'d.m.Y H:i',
  lang:'ru'
});</code></pre>
<p>and handler onclick event</p>
<pre><code class="language-javascript">jQuery('#image_button').click(function(){
  jQuery('#datetimepicker4').datetimepicker('show'); //support hide,show and destroy command
});</code></pre>
<p><strong>Result</strong></p>
<div class="row">
<div class="col-lg-6">
<div class="input-group"><input id="_datetimepicker4" class="form-control" type="text" value="10.12.2013 23:45" /></div>
<!-- /input-group --></div>
<!-- /.col-lg-6 --></div>
<!-- /.row -->
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker4').datetimepicker({
  format:'d.m.Y H:i',
  lang:'en'
});
jQuery('#image_button').click(function(){
  jQuery('#_datetimepicker4').datetimepicker('show'); 
});
});
// ]]></script>
<hr id="allowTimes" />
<h4>allowTimes options TimePicker Example <a href="#allowTimes">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker5').datetimepicker({
 datepicker:false,
 allowTimes:[
  '12:00', '13:00', '15:00', 
  '17:00', '17:05', '17:20', '19:00', '20:00'
 ]
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker5" type="text" value="23:45" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker5').datetimepicker({
  datepicker:false,
  allowTimes:['12:00','13:00','15:00','17:00','17:05','17:20','19:00','20:00']
});
});
// ]]></script>
<hr id="onChangeDateTime" />
<h4>handler onChangeDateTime Example <a href="#onChangeDateTime">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker6').datetimepicker({
  timepicker:false,
  onChangeDateTime:function(dp,$input){
    alert($input.val())
  }
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker6" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker6').datetimepicker({
 timepicker:false,
 onChangeDateTime:function(current_time,$input){
  alert($input.val())
 }
});
});
// ]]></script>
<hr id="mindate" />
<h4>minDate and maxDate Example <a href="#mindate">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker7').datetimepicker({
 timepicker:false,
 formatDate:'Y/m/d',
 minDate:'-1970/01/02',//yesterday is minimum date(for today use 0 or -1970/01/01)
 maxDate:'+1970/01/02'//tomorrow is maximum date calendar
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker7" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker7').datetimepicker({
  timepicker:false,
  formatDate:'Y/m/d',
  minDate:'-1970/01/02', // yesterday is minimum date
  maxDate:'+1970/01/02' // and tomorrow is maximum date calendar
});
});
// ]]></script>
<hr id="mask" />
<h4>Use mask input Example <a href="#mask">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker_mask').datetimepicker({
 timepicker:false,
 mask:true, // '9999/19/39 29:59' - digit is the maximum possible for a cell
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker_mask" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker_mask').datetimepicker({
  timepicker:false,
  mask:'9999/19/39',
  format:'Y/m/d'
});
});
// ]]></script>
<hr id="runtime_options" />
<h4>Set options runtime DateTimePicker <a href="#runtime_options">#</a></h4>
<p>If select day is Saturday, the minimum set 11:00, otherwise 8:00</p>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">var logic = function( currentDateTime ){
  // 'this' is jquery object datetimepicker
  if( currentDateTime.getDay()==6 ){
    this.setOptions({
      minTime:'11:00'
    });
  }else
    this.setOptions({
      minTime:'8:00'
    });
};
jQuery('#datetimepicker_rantime').datetimepicker({
  onChangeDateTime:logic,
  onShow:logic
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker_runtime" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
var logic = function( currentDateTime ){
  if( currentDateTime.getDay()==6 ){
    this.setOptions({
      minTime:'11:00'
    });
  }else
    this.setOptions({
      minTime:'8:00'
    });
};
jQuery('#_datetimepicker_runtime').datetimepicker({
  onChangeDateTime:logic,
  onShow:logic
});
});
// ]]></script>
<hr id="ongenerate" />
<h4>After generating a calendar called the event onGenerate <a href="#ongenerate">#</a></h4>
<p>Invert settings minDate and maxDate</p>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker8').datetimepicker({
  onGenerate:function( ct ){
    jQuery(this).find('.xdsoft_date')
      .toggleClass('xdsoft_disabled');
  },
  minDate:'-1970/01/2',
  maxDate:'+1970/01/2',
  timepicker:false
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker_ongenerate" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker_ongenerate').datetimepicker({
  onGenerate:function( ct ){
    jQuery(this).find('.xdsoft_date')
      .toggleClass('xdsoft_disabled');
  },
  minDate:'-1970/01/2',
  maxDate:'+1970/01/2',
  timepicker:false
});
});
// ]]></script>
<hr id="weekends_disable" />
<h4>disable all weekend <a href="#weekends_disable">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery('#datetimepicker9').datetimepicker({
  onGenerate:function( ct ){
    jQuery(this).find('.xdsoft_date.xdsoft_weekend')
      .addClass('xdsoft_disabled');
  },
  weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
  timepicker:false
});</code></pre>
<p><strong>Result</strong></p>
<p><input id="_datetimepicker_weekends_disable" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
jQuery('#_datetimepicker_weekends_disable').datetimepicker({
  onGenerate:function( ct ){
    jQuery(this).find('.xdsoft_date.xdsoft_weekend')
      .addClass('xdsoft_disabled');
  },
  weekends:['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'],
  timepicker:false
});
});
// ]]></script>
<hr id="use_other_date_parser" />
<h4>Use another date parser/formatter<a href="#use_other_date_parser">#</a></h4>
<p>By default, datetimepicker uses <a href="https://github.com/kartik-v/php-date-formatter">php-date-formatter</a> for parsing and formatting the date and time displayed. You can replace the library by setting a custom DateFormatter. Simply supply an object that implements the <tt>parseDate</tt> and <tt>formatDate</tt> methods. This example uses the popular <a href="http://momentjs.com/">MomentJS</a> library:</p>
<pre><code class="language-javascript">$.datetimepicker.setDateFormatter({
    parseDate: function (date, format) {
        var d = moment(date, format);
        return d.isValid() ? d.toDate() : false;
    },
    
    formatDate: function (date, format) {
        return moment(date).format(format);
    },

    //Optional if using mask input
    formatMask: function(format){
        return format
            .replace(/Y{4}/g, '9999')
            .replace(/Y{2}/g, '99')
            .replace(/M{2}/g, '19')
            .replace(/D{2}/g, '39')
            .replace(/H{2}/g, '29')
            .replace(/m{2}/g, '59')
            .replace(/s{2}/g, '59');
    }
});
</code></pre>
<p>After this, you can init datetimepicker with moment.js <a href="http://momentjs.com/docs/#/parsing/string-format/" target="_blanc" rel="nofollow">format</a></p>
<pre><code class="language-javascript">jQuery('#datetimepicker').datetimepicker({
  format:'DD.MM.YYYY h:mm a',
  formatTime:'h:mm a',
  formatDate:'DD.MM.YYYY'
});</code></pre>
<p>Because of its popularity, moment.js has a pre-defined configuration that can be enabled with:</p>
<pre><code class="language-javascript">$.datetimepicker.setDateFormatter('moment');</code></pre>
<hr id="range" />
<h4>Range between date<a href="#range">#</a></h4>
<p><strong>javaScript</strong></p>
<pre><code class="language-javascript">jQuery(function(){
 jQuery('#date_timepicker_start').datetimepicker({
  format:'Y/m/d',
  onShow:function( ct ){
   this.setOptions({
    maxDate:jQuery('#date_timepicker_end').val()?jQuery('#date_timepicker_end').val():false
   })
  },
  timepicker:false
 });
 jQuery('#date_timepicker_end').datetimepicker({
  format:'Y/m/d',
  onShow:function( ct ){
   this.setOptions({
    minDate:jQuery('#date_timepicker_start').val()?jQuery('#date_timepicker_start').val():false
   })
  },
  timepicker:false
 });
});</code></pre>
<p><strong>Result</strong></p>
<p>Start <input id="date_timepicker_start" type="text" value="" />  End <input id="date_timepicker_end" type="text" value="" /></p>
<script type="text/javascript">// <![CDATA[
jQuery(function(){
  jQuery('#date_timepicker_start').datetimepicker({
    format:'Y/m/d',
    onShow:function( ct ){
      this.setOptions({
        maxDate:jQuery('#date_timepicker_end').val()?jQuery('#date_timepicker_end').val():false
      })
    },
    timepicker:false
  });
  jQuery('#date_timepicker_end').datetimepicker({
    format:'Y/m/d',
    onShow:function( ct ){
      this.setOptions({
        minDate:jQuery('#date_timepicker_start').val()?jQuery('#date_timepicker_start').val():false
      })
    },
    timepicker:false
  });
});
// ]]></script>
[include scripts/pp/reklama2.php]
{module 147}
<h2>Full options list</h2>
<table class="table table-condensed table-bordered table-striped">
<thead>
<tr><th style="text-align: center;"><strong>Name</strong></th><th style="text-align: center;"><strong> default</strong></th><th style="text-align: center;"><strong>Descr</strong></th><th style="width: 200px; text-align: center;"><strong>Ex.</strong></th></tr>
</thead>
<tbody>
<tr id="lazyInit">
<td><a href="#lazyInit">lazyInit</a></td>
<td>false</td>
<td>Initializing plugin occurs only when the user interacts. Greatly accelerates plugin work with a large number of fields</td>
<td> </td>
</tr>
<tr id="parentID">
<td><a href="#parentID">parentID</a></td>
<td>'body'</td>
<td>Attach datetimepicker to this element, which can be either a selector or a DOM/JQuery element</td>
<td>
<pre><code class="language-javascript">{parentID:'#parent'}</code></pre>
</td>
</tr>
<tr id="value">
<td><a href="#value">value</a></td>
<td>null</td>
<td>Current value datetimepicker. If it is set, ignored input.value</td>
<td>
<pre><code class="language-javascript">{value:'12.03.2013',
 format:'d.m.Y'}</code></pre>
</td>
</tr>
<tr id="lang">
<td><a href="#lang">lang</a></td>
<td>en</td>
<td>Language i18n<br /> 

<strong>ar</strong> -  Arabic
<br /><strong>az</strong> -  Azerbaijanian (Azeri)
<br /><strong>bg</strong> -  Bulgarian
<br /><strong>bs</strong> -  Bosanski
<br /><strong>ca</strong> -  Català
<br /><strong>ch</strong> -  Simplified Chinese
<br /><strong>cs</strong> -  Čeština
<br /><strong>da</strong> -  Dansk
<br /><strong>de</strong> -  German
<br /><strong>el</strong> -  Ελληνικά
<br /><strong>en</strong> -  English
<br /><strong>en-GB</strong> -  English (British)
<br /><strong>es</strong> -  Spanish
<br /><strong>et</strong> -  "Eesti"
<br /><strong>eu</strong> -  Euskara
<br /><strong>fa</strong> -  Persian
<br /><strong>fi</strong> -  Finnish (Suomi)
<br /><strong>fr</strong> -  French
<br /><strong>gl</strong> -  Galego
<br /><strong>he</strong> -  Hebrew (עברית)
<br /><strong>hr</strong> -  Hrvatski
<br /><strong>hu</strong> -  Hungarian
<br /><strong>id</strong> -  Indonesian
<br /><strong>it</strong> -  Italian
<br /><strong>ja</strong> -  Japanese
<br /><strong>ko</strong> -  Korean (한국어)
<br /><strong>kr</strong> -  Korean
<br /><strong>lt</strong> -  Lithuanian (lietuvių)
<br /><strong>lv</strong> -  Latvian (Latviešu)
<br /><strong>mk</strong> -  Macedonian (Македонски)
<br /><strong>mn</strong> -  Mongolian (Монгол)
<br /><strong>nl</strong> -  Dutch
<br /><strong>no</strong> -  Norwegian
<br /><strong>pl</strong> -  Polish
<br /><strong>pt</strong> -  Portuguese
<br /><strong>pt-BR</strong> -  Português(Brasil)
<br /><strong>ro</strong> -  Romanian
<br /><strong>ru</strong> -  Russian
<br /><strong>se</strong> -  Swedish
<br /><strong>sk</strong> -  Slovenčina
<br /><strong>sl</strong> -  Slovenščina
<br /><strong>sq</strong> -  Albanian (Shqip)
<br /><strong>sr</strong> -  Serbian Cyrillic (Српски)
<br /><strong>sr-YU</strong> -  Serbian (Srpski)
<br /><strong>sv</strong> -  Svenska
<br /><strong>th</strong> -  Thai
<br /><strong>tr</strong> -  Turkish
<br /><strong>uk</strong> -  Ukrainian
<br /><strong>vi</strong> -  Vietnamese
<br /><strong>zh</strong> -  Simplified Chinese (简体中文)
<br /><strong>zh-TW</strong> -  Traditional Chinese (繁體中文)
<br />

  
</td>
<td>
<pre><code class="language-javascript">$.datetimepicker.setLocale('ru');</code></pre>
</td>
</tr>
<tr id="format">
<td><a href="#format">format</a></td>
<td>Y/m/d H:i</td>
<td>Format datetime. <a href="http://php.net/manual/ru/function.date.php" target="_blank">More</a>  Also there is a special type of <a href="#unixtime"><em>«unixtime»</em></a></td>
<td>
<pre><code class="language-javascript">{format:'H'}
{format:'Y'}{format:'unixtime'}</code></pre>
</td>
</tr>
<tr id="formatDate">
<td><a href="#formatDate">formatDate</a></td>
<td>Y/m/d</td>
<td>Format date for minDate and maxDate</td>
<td>
<pre><code class="language-javascript">{formatDate:'d.m.Y'}</code></pre>
</td>
</tr>
<tr id="formatTime">
<td><a href="#formatTime">formatTime</a></td>
<td>H:i</td>
<td> Similarly, formatDate . But for minTime and maxTime</td>
<td>
<pre><code class="language-javascript">{formatTime:'H'}</code></pre>
</td>
</tr>
<tr id="step">
<td><a href="#step">step</a></td>
<td>60</td>
<td>Step time</td>
<td>
<pre><code class="language-javascript">{step:5}</code></pre>
</td>
</tr>
<tr id="closeOnDateSelect">
<td><a href="#closeOnDateSelect">closeOnDateSelect</a></td>
<td>0</td>
<td> </td>
<td>
<pre><code class="language-javascript">{closeOnDateSelect:true}</code></pre>
</td>
</tr>
<tr id="closeOnWithoutClick">
<td><a href="#closeOnWithoutClick">closeOnWithoutClick</a></td>
<td>true</td>
<td> </td>
<td>
<pre><code class="language-javascript">{ closeOnWithoutClick :false}</code></pre>
</td>
</tr>
<tr id="validateOnBlur">
<td><a href="#validateOnBlur">validateOnBlur</a></td>
<td>true</td>
<td>Verify datetime value from input, when losing focus. If value is not valid datetime, then to value inserts the current datetime</td>
<td>
<pre><code class="language-javascript">{ validateOnBlur:false}</code></pre>
</td>
</tr>
<tr id="timepicker">
<td><a href="#timepicker">timepicker</a></td>
<td>true</td>
<td> </td>
<td>
<pre><code class="language-javascript">{timepicker:false}</code></pre>
</td>
</tr>
<tr id="datepicker">
<td><a href="#datepicker">datepicker</a></td>
<td>true</td>
<td> </td>
<td>
<pre><code class="language-javascript">{datepicker:false}</code></pre>
</td>
</tr>
<tr id="weeks">
<td><a href="#weeks">weeks</a></td>
<td>false</td>
<td>Show week number</td>
<td>
<pre><code class="language-javascript">{weeks:true}</code></pre>
</td>
</tr>
<tr id="theme">
<td><a href="#theme">theme</a></td>
<td>'default'</td>
<td>Setting a color scheme. Now only supported default and dark theme</td>
<td>
<pre><code class="language-javascript">{theme:'dark'}</code></pre>
</td>
</tr>
<tr id="minDate">
<td><a href="#minDate">minDate</a></td>
<td>false</td>
<td> </td>
<td>
<pre><code class="language-javascript">{minDate:0} // today
{minDate:'2013/12/03'}
{minDate:'-1970/01/02'} // yesterday
{minDate:'05.12.2013',formatDate:'d.m.Y'}</code></pre>
</td>
</tr>
<tr id="maxDate">
<td><a href="#maxDate">maxDate</a></td>
<td>false</td>
<td> </td>
<td>
<pre><code class="language-javascript">{maxDate:0}
{maxDate:'2013/12/03'}
{maxDate:'+1970/01/02'} // tomorrow
{maxDate:'05.12.2013',formatDate:'d.m.Y'}</code></pre>
</td>
</tr>
<tr id="startDate">
<td><a href="#starDate">startDate</a></td>
<td>false</td>
<td>calendar set date use starDate</td>
<td>
<pre><code class="language-javascript">{startDate:'1987/12/03'}
{startDate:new Date()}
{startDate:'+1970/01/02'} // tomorrow
{startDate:'08.12.1986',formatDate:'d.m.Y'}</code></pre>
</td>
</tr>

<tr id="defaultDate">
<td><a href="#defaultDate">defaultDate</a></td>
<td>false</td>
<td>if input value is empty, calendar set date use defaultDate</td>
<td>
<pre><code class="language-javascript">{defaultDate:'1987/12/03'}
{defaultDate:new Date()}
{defaultDate:'+1970/01/02'} // tomorrow
{defaultDate:'08.12.1986',formatDate:'d.m.Y'}</code></pre>
</td>
</tr>  
  
<tr id="defaultTime">
<td><a href="#defaultTime">defaultTime</a></td>
<td>false</td>
<td>if input value is empty, timepicker set time use defaultTime</td>
<td>
<pre><code class="language-javascript">{defaultTime:'05:00'}
{defaultTime:'33-12',formatTime:'i-H'}</code></pre>
</td>
</tr>  
  
<tr id="minTime">
<td><a href="#minTime">minTime</a></td>
<td>false</td>
<td> </td>
<td>
<pre><code class="language-javascript">{minTime:0,}// now
{minTime:new Date()}
{minTime:'12:00'}
{minTime:'13:45:34',formatTime:'H:i:s'}</code></pre>
</td>
</tr>
<tr id="maxTime">
<td><a href="#maxTime">maxTime</a></td>
<td>false</td>
<td> </td>
<td>
<pre><code class="language-javascript">{maxTime:0,}
{maxTime:'12:00'}
{maxTime:'13:45:34',formatTime:'H:i:s'}</code></pre>
</td>
</tr>
<tr id="allowTimes">
<td><a href="#allowTimes">allowTimes</a></td>
<td>[]</td>
<td> </td>
<td>
<pre><code class="language-javascript">{allowTimes:[
  '09:00',
  '11:00',
  '12:00',
  '21:00'
]}</code></pre>
</td>
</tr>
<tr id="mask">
<td><a href="#mask">mask</a></td>
<td>false</td>
<td>Use mask for input. true - automatically generates a mask on the field 'format', Digit from 0 to 9, set the highest possible digit for the value. For example: the first digit of hours can not be greater than 2, and the first digit of the minutes can not be greater than 5</td>
<td>
<pre>{mask:'9999/19/39',format:'Y/m/d'}
{mask:true,format:'Y/m/d'} // automatically generate a mask 9999/99/99
{mask:'29:59',format:'H:i'} //
{mask:true,format:'H:i'} //automatically generate a mask 99:99</pre>
</td>
</tr>
<tr id="opened">
<td><a href="#opened">opened</a></td>
<td>false</td>
<td> </td>
<td> </td>
</tr>
<tr id="yearoffset">
<td><a href="#yearoffset">yearOffset</a></td>
<td>0</td>
<td>Year offset for Buddhist era</td>
<td> </td>
</tr>
<tr id="inline">
<td><a href="#inline">inline</a></td>
<td>false</td>
<td> </td>
<td> </td>
</tr>
<tr id="todayButton">
<td><a href="#todayButton">todayButton</a></td>
<td>true</td>
<td>Show button "Go To Today"</td>
<td> </td>
</tr>
<tr id="defaultSelect">
<td><a href="#defaultSelect">defaultSelect</a></td>
<td>true</td>
<td>Highlight the current date even if the input is empty</td>
<td> </td>
</tr>
<tr id="allowBlank">
<td><a href="#allowBlank">allowBlank</a></td>
<td>false</td>
<td>Allow field to be empty even with the option <a href="#validateOnBlur">validateOnBlur</a> in true</td>
<td> </td>
</tr>
<tr id="timepickerScrollbar">
<td><a href="#timepickerScrollbar">timepickerScrollbar</a></td>
<td>true</td>
<td> </td>
<td> </td>
</tr>
<tr id="onSelectDate">
<td><a href="#onSelectDate">onSelectDate</a></td>
<td>function(){}</td>
<td> </td>
<td>
<pre><code class="language-javascript">onSelectDate:function(ct,$i){
  alert(ct.dateFormat('d/m/Y'))
}</code></pre>
</td>
</tr>
<tr id="onSelectTime">
<td><a href="#onSelectTime">onSelectTime</a></td>
<td>function(current_time,$input){}</td>
<td> </td>
<td> </td>
</tr>
<tr id="onChangeMonth">
<td><a href="#onChangeMonth">onChangeMonth</a></td>
<td>function(current_time,$input){}</td>
<td> </td>
<td> </td>
</tr>
 <tr id="onChangeYear">
<td><a href="#onChangeYear">onChangeYear</a></td>
<td>function(current_time,$input){}</td>
<td> </td>
<td> </td>
</tr>
<tr id="onChangeDateTime">
<td><a href="#onChangeDateTime">onChangeDateTime</a></td>
<td>function(current_time,$input){}</td>
<td> </td>
<td> </td>
</tr>
<tr id="onShow">
<td><a href="#onShow">onShow</a></td>
<td>function(current_time,$input){}</td>
<td> </td>
<td> </td>
</tr>
<tr id="onClose">
<td><a href="#onClose">onClose</a></td>
<td>function(current_time,$input){}</td>
<td> </td>
<td><pre><code class="language-javascript">onSelectDate:function(ct,$i){
  $i.datetimepicker('destroy');
}</code></pre></td>
</tr>
<tr id="onGenerate">
<td><a href="#onGenerate">onGenerate</a></td>
<td>function(current_time,$input){}</td>
<td>trigger after construct calendar and timepicker</td>
<td> </td>
</tr>
<tr>
<td>withoutCopyright</td>
<td>true</td>
<td> </td>
<td> </td>
</tr>
<tr id="inverseButton">
<td><a href="#inverseButton">inverseButton</a></td>
<td>false</td>
<td> </td>
<td> </td>
</tr>
<tr id="scrollMonth">
<td><a href="#scrollMonth">scrollMonth</a></td>
<td>true</td>
<td> </td>
<td> </td>
</tr>
<tr id="scrollTime">
<td><a href="#scrollTime">scrollTime</a></td>
<td>true</td>
<td> </td>
<td> </td>
</tr>
<tr id="scrollInput">
<td><a href="#scrollInput">scrollInput</a></td>
<td>true</td>
<td> </td>
<td> </td>
</tr>
<tr id="hours12">
<td><a href="#hours12">hours12</a></td>
<td>false</td>
<td> </td>
<td> </td>
</tr>
<tr id="yearStart">
<td><a href="#yearStart">yearStart</a></td>
<td>1950</td>
<td>Start value for fast Year selector</td>
<td> </td>
</tr>
<tr id="yearEnd">
<td><a href="#yearEnd">yearEnd</a></td>
<td>2050</td>
<td>End value for fast Year selector</td>
<td> </td>
</tr>
<tr id="roundTime">
<td><a href="#roundTime">roundTime</a></td>
<td>round</td>
<td>Round time in timepicker, possible values: round, ceil, floor</td>
<td>
<pre><code class="language-javascript">{roundTime:'floor'}</code></pre>
</td>
</tr>
<tr id="dayOfWeekStart">
<td><a href="#dayOfWeekStart">dayOfWeekStart</a></td>
<td>0</td>
<td>
<p>Star week DatePicker. Default Sunday - 0.</p>
<p>Monday - 1 ...</p>
</td>
<td> </td>
</tr>
<tr id="className">
<td>className</td>
<td> </td>
<td> </td>
<td> </td>
</tr>
<tr id="weekends">
<td><a href="#weekends">weekends</a></td>
<td>[]</td>
<td> </td>
<td>
<pre><code class="language-javascript">['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014']</code></pre>
</td>
</tr>
<tr id="disabledDates">
<td><a href="#disabledDates">disabledDates</a></td>
<td>[]</td>
<td><p>Disbale all dates in list</p></td>
<td>
<pre><code class="language-javascript">{disabledDates: ['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'], formatDate:'d.m.Y'}</code></pre>
</td>
</tr>
<tr id="allowDates">
<td><a href="#allowDates">allowDates</a></td>
<td>[]</td>
<td><p>Allow all dates in list</p></td>
<td>
<pre><code class="language-javascript">{allowDates: ['01.01.2014','02.01.2014','03.01.2014','04.01.2014','05.01.2014','06.01.2014'], formatDate:'d.m.Y'}</code></pre>
</td>
</tr>
<tr id="allowDateRe">
<td><a href="#allowDateRe">allowDateRe</a></td>
<td>[]</td>
<td><p>Use Regex to check dates</p></td>
<td>
<pre><code class="language-javascript">{format:'Y-m-d',allowDateRe:'\d{4}-(03-31|06-30|09-30|12-31)' }</code></pre>
</td>
</tr>
<tr id="disabledWeekDays">
<td><a href="#disabledWeekDays">disabledWeekDays</a></td>
<td>[]</td>
<td><p>Disable days listed by index</p></td>
<td>
<pre><code class="language-javascript">[0, 3, 4]</code></pre>
</td>
</tr>
<tr id="id">
<td>id</td>
<td> </td>
<td> </td>
<td> </td>
</tr>
<tr id="style">
<td>style</td>
<td> </td>
<td> </td>
<td> </td>
</tr>
<tr id="ownerDocument">
<td>ownerDocument</td>
<td>document</td>
<td>The ownerDocument object for the datetimepicker to properly attach events and calc position (iframe support).</td>
<td> </td>
</tr>
<tr id="contentWindow">
<td>contentWindow</td>
<td>window</td>
<td>The contentWindow object that contains the datetimepicker to properly attach events (iframe support).</td>
<td> </td>
</tr>
</tbody>
</table>
<hr>
<h2 id="methods">Methods</h2>
<h3>show</h3>
<p>Show Datetimepicker</p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$('button.somebutton').on('click', function () {
    $('#input').datetimepicker('show');
});</code></pre>
<h3>hide</h3>
<p>Hide Datetimepicker</p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$(window).on('resize', function () {
    $('#input').datetimepicker('hide');
});</code></pre>
<h3>toggle</h3>
<p>Sgow/Hide Datetimepicker</p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$('button.trigger').on('click', function () {
    $('#input').datetimepicker('toggle');
});</code></pre>
<h3>destroy</h3>
<p>Destroy datetimepicker</p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$('#input').datetimepicker('destroy');
</code></pre>
<h3>reset</h3>
<p>Reset datetimepicker's value</p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$('#input').val('12/01/2006');
$('#input')
    .datetimepicker('show')
    .datetimepicker('reset')
</code></pre>
<h3>validate</h3>
<p>Validate datetimepicker's value </p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$('#input').datetimepicker(validate)
</code></pre>
<h3>setOptions</h3>
<p>Set datetimepicker's options </p>
<pre><code class="language-javascript">$('#input').datetimepicker({format: 'd.m.Y'});
$('#input').datetimepicker('setOptions', {format: 'd/m/Y'});
//or
$('#input').datetimepicker({format: 'd/m/Y'});
</code></pre>
<h3>getValue</h3>
<p>Get current datetimepicker's value (Date object) </p>
<pre><code class="language-javascript">$('#input').datetimepicker();
$('button.somebutton').on('click', function () {
    var d = $('#input').datetimepicker('getValue');
    console.log(d.getFullYear());
});
</code></pre>
