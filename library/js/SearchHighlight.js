/**
 * SearchHighlight plugin for jQuery
 * 
 * Thanks to Scott Yang <http://scott.yang.id.au/>
 * for the original idea and some code
 *    
 * @author Renato Formato <renatoformato@virgilio.it> 
 *  
 * @version 0.33
 *
 *  Options
 *  - exact (string, default:"exact") 
 *    "exact" : find and highlight the exact words.
 *    "whole" : find partial matches but highlight whole words
 *    "partial": find and highlight partial matches
 *     
 *  - style_name (string, default:'hilite')
 *    The class given to the span wrapping the matched words.
 *     
 *  - style_name_suffix (boolean, default:true)
 *    If true a different number is added to style_name for every different matched word.
 *     
 *  - debug_referrer (string, default:null)
 *    Set a referrer for debugging purpose.
 *     
 *  - engines (array of regex, default:null)
 *    Add a new search engine regex to highlight searches coming from new search engines.
 *    The first element is the regex to match the domain.
 *    The second element is the regex to match the query string. 
 *    Ex: [/^http:\/\/my\.site\.net/i,/search=([^&]+)/i]        
 *            
 *  - highlight (string, default:null)
 *    A jQuery selector or object to set the elements enabled for highlight.
 *    If null or no elements are found, all the document is enabled for highlight.
 *        
 *  - nohighlight (string, default:null)  
 *    A jQuery selector or object to set the elements not enabled for highlight.
 *    This option has priority on highlight. 
 *    
 *  - keys (string, default:null)
 *    Disable the analisys of the referrer and search for the words given as argument    
 *    
 */

/**
 * Included in OpenEMR package on 2/28/2013:
 * No license/copyright/restrictions/conditions included in script or from site it was
 * obtained from. Additionally, the author email address no longer exists. For these
 * reasons, OpenEMR considers this script as public domain.
 */

(function($){
  jQuery.fn.SearchHighlight = function(options) {
    var ref = options.debug_referrer || document.referrer;
    if(!ref && options.keys==undefined) return this;
    
    SearchHighlight.options = $.extend({exact:"exact",style_name:'hilite',style_name_suffix:true},options);
    
    if(options.engines) SearchHighlight.engines.unshift(options.engines);  
    var q = options.keys!=undefined?options.keys.split(/[\s,\+\.]+/):SearchHighlight.decodeURL(ref,SearchHighlight.engines);
    if(q && q.join("")) {
      SearchHighlight.buildReplaceTools(q);
      return this.each(function(){
        var el = this;
        if(el==document) el = $("body")[0];
        SearchHighlight.hiliteElement(el, q); 
      })
    } else return this;
  }    

  var SearchHighlight = {
    options: {},
    regex: [],
    engines: [
    [/^http:\/\/(www\.)?google\./i, /q=([^&]+)/i],                            // Google
    [/^http:\/\/(www\.)?search\.yahoo\./i, /p=([^&]+)/i],                     // Yahoo
    [/^http:\/\/(www\.)?search\.msn\./i, /q=([^&]+)/i],                       // MSN
    [/^http:\/\/(www\.)?search\.live\./i, /query=([^&]+)/i],                  // MSN Live
    [/^http:\/\/(www\.)?search\.aol\./i, /userQuery=([^&]+)/i],               // AOL
    [/^http:\/\/(www\.)?ask\.com/i, /q=([^&]+)/i],                            // Ask.com
    [/^http:\/\/(www\.)?altavista\./i, /q=([^&]+)/i],                         // AltaVista
    [/^http:\/\/(www\.)?feedster\./i, /q=([^&]+)/i],                          // Feedster
    [/^http:\/\/(www\.)?search\.lycos\./i, /q=([^&]+)/i],                     // Lycos
    [/^http:\/\/(www\.)?alltheweb\./i, /q=([^&]+)/i],                         // AllTheWeb
    [/^http:\/\/(www\.)?technorati\.com/i, /([^\?\/]+)(?:\?.*)$/i],           // Technorati
    ],
    subs: {},
    decodeURL: function(URL,reg) {
      URL = decodeURIComponent(URL);
      var query = null;
      $.each(reg,function(i,n){
        if(n[0].test(URL)) {
          var match = URL.match(n[1]);
          if(match) {
            query = match[1];
            return false;
          }
        }
      })
      
      if (query) {
      query = query.replace(/(\'|")/, '\$1');
      query = query.split(/[\s,\+\.]+/);
      }
      
      return query;
    },
		regexAccent : [
      [/[\xC0-\xC5\u0100-\u0105]/ig,'a'],
      [/[\xC7\u0106-\u010D]/ig,'c'],
      [/[\xC8-\xCB]/ig,'e'],
      [/[\xCC-\xCF]/ig,'i'],
      [/\xD1/ig,'n'],
      [/[\xD2-\xD6\xD8]/ig,'o'],
      [/[\u015A-\u0161]/ig,'s'],
      [/[\u0162-\u0167]/ig,'t'],
      [/[\xD9-\xDC]/ig,'u'],
      [/\xFF/ig,'y'],
      [/[\x91\x92\u2018\u2019]/ig,'\'']
    ],
    matchAccent : /[\x91\x92\xC0-\xC5\xC7-\xCF\xD1-\xD6\xD8-\xDC\xFF\u0100-\u010D\u015A-\u0167\u2018\u2019]/ig,  
		replaceAccent: function(q) {
		  SearchHighlight.matchAccent.lastIndex = 0;
      if(SearchHighlight.matchAccent.test(q)) {
        for(var i=0,l=SearchHighlight.regexAccent.length;i<l;i++)
          q = q.replace(SearchHighlight.regexAccent[i][0],SearchHighlight.regexAccent[i][1]);
      }
      return q;
    },
    escapeRegEx : /((?:\\{2})*)([[\]{}*?|])/g, //the special chars . and + are already gone at this point because they are considered split chars
    buildReplaceTools : function(query) {
        var re = [], regex;
        $.each(query,function(i,n){
            if(n = SearchHighlight.replaceAccent(n).replace(SearchHighlight.escapeRegEx,"$1\\$2"))
              re.push(n);        
        });
        
        regex = re.join("|");
        switch(SearchHighlight.options.exact) {
          case "exact":
            regex = '\\b(?:'+regex+')\\b';
            break;
          case "whole":
            regex = '\\b\\w*('+regex+')\\w*\\b';
            break;
        }
		switch(SearchHighlight.options.set_case_sensitive) {
				  case "true":
					SearchHighlight.regex = new RegExp(regex, "g");
					break;
				  case "false":
					SearchHighlight.regex = new RegExp(regex, "gi");
					break;
		}   		
        
        
        $.each(re,function(i,n){
            SearchHighlight.subs[n] = SearchHighlight.options.style_name+
              (SearchHighlight.options.style_name_suffix?i+1:''); 
        });       
    },
    nosearch: /s(?:cript|tyle)|textarea/i,
    hiliteElement: function(el, query) {
        var opt = SearchHighlight.options, elHighlight, noHighlight;
        elHighlight = opt.highlight?$(opt.highlight):$("body"); 
        if(!elHighlight.length) elHighlight = $("body"); 
        noHighlight = opt.nohighlight?$(opt.nohighlight):$([]);
                
        elHighlight.each(function(){
          SearchHighlight.hiliteTree(this,query,noHighlight);
        });
    },
    hiliteTree : function(el,query,noHighlight) {
        if(noHighlight.index(el)!=-1) return;
        var matchIndex = SearchHighlight.options.exact=="whole"?1:0;
        for(var startIndex=0,endIndex=el.childNodes.length;startIndex<endIndex;startIndex++) {
          var item = el.childNodes[startIndex];
          if ( item.nodeType != 8 ) {//comment node
  				  //text node
            if(item.nodeType==3) {
              var text = item.data, textNoAcc = SearchHighlight.replaceAccent(text);
              var newtext="",match,index=0;
              SearchHighlight.regex.lastIndex = 0;
              while(match = SearchHighlight.regex.exec(textNoAcc)) {
               /* newtext += text.substr(index,match.index-index)+'<span class="'+
                SearchHighlight.subs[match[matchIndex]]+'">'+text.substr(match.index,match[0].length)+"</span>";*/
				newtext += text.substr(index,match.index-index)+'<span class="hilite">'+text.substr(match.index,match[0].length)+"</span>";
                index = match.index+match[0].length;
              }
              if(newtext) {
                //add the last part of the text
                newtext += text.substring(index);
                var repl = $.merge([],$("<span>"+newtext+"</span>")[0].childNodes);
                endIndex += repl.length-1;
                startIndex += repl.length-1;
                $(item).before(repl).remove();
              }                
            } else {
              if(item.nodeType==1 && item.nodeName.search(SearchHighlight.nosearch)==-1)
                  SearchHighlight.hiliteTree(item,query,noHighlight);
            }	
          }
        }    
    }
  };
})(jQuery)
