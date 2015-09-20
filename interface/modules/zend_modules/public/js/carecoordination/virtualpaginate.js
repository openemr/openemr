// -------------------------------------------------------------------
// Virtual Pagination Script- By Dynamic Drive, available at: http://www.dynamicdrive.com
// Updated: Nov 21st, 2008 to v2.0
// ** Adds ability to define multiple pagination DIVs (the secondary DIVs mirror primary DIV's contents)
// ** Last viewed page persistence, so last viewed page can be remembered/ recalled within browser session.
// ** Improvements to instance.navigate() to select a page using an arbitrary link or inside another script.
// ** Ability to select a page using a URL parameter (ie: target.htm?virtualpiececlass=index).

// Updated: Oct 19th, 2009 to v2.1
// ** New wraparound:true/false option added, which when false disables moving back/forth beyond first and last content, respectively 
//
// PUBLIC: virtualpaginate()
// Main Virtual Paginate Object function.
// -------------------------------------------------------------------

document.write('<style type="text/css">' //write out CSS for class ".hidepeice" that hides pieces of contents within pages
	+'.hidepiece{display:none}\n'
	+'@media print{.hidepiece{display:block !important;}}\n'
	+'</style>')
	
function virtualpaginate(config){ //config: {piececlass:, piececontainer:, pieces_per_page:, defaultpage:, wraparound:, persist}
	this.piececlass=config.piececlass
	var elementType=(typeof config.piececontainer=="undefined")? "div" : config.piececontainer //The type of element used to divide up content into pieces. Defaults to "div"
	this.pieces=virtualpaginate.collectElementbyClass(config.piececlass, elementType) //get total number of divs matching class name
	//Set this.chunksize: 1 if "chunksize" param is undefined, "chunksize" if it's less than total pieces available, or simply total pieces avail (show all)
	this.chunksize=(typeof config.pieces_per_page=="undefined")? 1 : (config.pieces_per_page>0 && config.pieces_per_page<this.pieces.length)? config.pieces_per_page : this.pieces.length
	this.pagecount=Math.ceil(this.pieces.length/this.chunksize) //calculate number of "pages" needed to show the divs
	this.wraparound=config.wraparound || false
	this.paginatediv=[], this.flatviewlinks=[], this.cpspan=[], this.selectmenu=[], this.prevlinks=[], this.nextlinks=[]
	this.persist=config.persist
	var persistedpage=virtualpaginate.getCookie("dd_"+this.piececlass) || 0
	var urlselectedpage=virtualpaginate.urlparamselect(this.piececlass) //returns null or index from: mypage.htm?piececlass=index
	this.currentpage=(typeof urlselectedpage=="number")? urlselectedpage : ((this.persist)? persistedpage : config.defaultpage)
	this.currentpage=(this.currentpage<this.pagecount)? parseInt(this.currentpage) : 0 //ensure currentpage is within range of available pages
	this.showpage(this.currentpage) //Show selected page
}

// -------------------------------------------------------------------
// PUBLIC: navigate(keyword)- Calls this.showpage() based on parameter passed (0=page1, 1=page2 etc, "next", "first", or "last")
// -------------------------------------------------------------------

virtualpaginate.prototype.navigate=function(keyword){
	if ((!this.wraparound && keyword=="previous" && this.currentpage==0) || (!this.wraparound && keyword=="next" && this.currentpage==this.pagecount-1))
		return //exit immediately if wraparound is disabled and prev link is clicked when on 1st content or last link is clicked when on final content
	var prevlinkindex=this.currentpage //Get index of last clicked on page
	if (keyword=="previous")
		this.currentpage=(this.currentpage>0)? this.currentpage-1 : (this.currentpage==0)? this.pagecount-1 : 0
	else if (keyword=="next")
		this.currentpage=(this.currentpage<this.pagecount-1)? this.currentpage+1 : 0
	else if (keyword=="first")
		this.currentpage=0
	else if (keyword=="last")
		this.currentpage=this.pagecount-1 //last page number
	else
		this.currentpage=parseInt(keyword)
	this.currentpage=(this.currentpage<this.pagecount)? this.currentpage : 0 //ensure pagenumber is within range of available pages
	this.showpage(this.currentpage)
	for (var p=0; p<this.paginatediv.length; p++){ //loop through all pagination DIVs
		if (this.flatviewpresent)
			this.flatviewlinks[p][prevlinkindex].className="" //"Unhighlight" previous page (before this.currentpage increments)
		if (this.selectmenupresent)
			this.selectmenu[p].selectedIndex=this.currentpage
		if (this.flatviewpresent)
			this.flatviewlinks[p][this.currentpage].className="selected" //"Highlight" current page
	}
	if (!this.wraparound){
		for (var i=0; i<this.prevlinks.length; i++) //add or remove "disable" class from prev links depending on current page number
			virtualpaginate.setcssclass(this.prevlinks[i], "disabled", (this.currentpage==0)? "add" : "remove")
		for (var i=0; i<this.nextlinks.length; i++) //add or remove "disable" class from next links depending on current page number
			virtualpaginate.setcssclass(this.nextlinks[i], "disabled", (this.currentpage==(this.pagecount-1))? "add" : "remove")
	}
}


// -------------------------------------------------------------------
// PUBLIC: buildpagination()- Create pagination interface by calling one or more of the paginate_build_() functions
// -------------------------------------------------------------------

virtualpaginate.prototype.buildpagination=function(divids, optnavtext){
	var divids=(typeof divids=="string")? [divids] : divids //force divids to be an array of ids
	var primarypaginatediv=divids.shift() //get first id within divids[]
	var paginaterawHTML=document.getElementById(primarypaginatediv).innerHTML
	this.paginate_build(primarypaginatediv, 0, optnavtext)
	for (var i=0; i<divids.length; i++){
		document.getElementById(divids[i]).innerHTML=paginaterawHTML
		this.paginate_build(divids[i], i+1, optnavtext)
	}
}

// -------------------------------------------------------------------
// PRIVATE utility functions
// -------------------------------------------------------------------

virtualpaginate.collectElementbyClass=function(classname, element){ //Returns an array containing DIVs with specified classname. Requires setcssclass()
	if (document.querySelectorAll){
		var pieces=document.querySelectorAll(element+"."+classname) //return pieces as HTMLCollection
	}
	else{
		var pieces=[]
		var alltags=document.getElementsByTagName(element)
		for (var i=0; i<alltags.length; i++){
			if (virtualpaginate.setcssclass(alltags[i], classname, "check")) //if element carries class name in question
				pieces[pieces.length]=alltags[i] //return pieces as array
		}
	}
	return pieces
}

// -------------------------------------------------------------------
// PRIVATE: setcssclass() method- Checks, Add, or Removes a class from an element
// -------------------------------------------------------------------

virtualpaginate.setcssclass=function(el, targetclass, action){
	var needle=new RegExp("(^|\\s+)"+targetclass+"($|\\s+)", "ig")
	if (action=="check")
		return needle.test(el.className)
	else if (action=="remove")
		el.className=el.className.replace(needle, "")
	else if (action=="add" && !needle.test(el.className))
		el.className+=" "+targetclass
}

virtualpaginate.urlparamselect=function(vpclass){
	var result=window.location.search.match(new RegExp(vpclass+"=(\\d+)", "i")) //check for "?piececlass=2" in URL
	return (result==null)? null : parseInt(RegExp.$1) //returns null or index, where index (int) is the selected virtual page's index
}

virtualpaginate.getCookie=function(Name){ 
	var re=new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
	if (document.cookie.match(re)) //if cookie found
		return document.cookie.match(re)[0].split("=")[1] //return its value
	return null
}

virtualpaginate.setCookie=function(name, value){
	document.cookie = name+"="+value
}

// -------------------------------------------------------------------
// PRIVATE: showpage(pagenumber)- Shows a page based on parameter passed (0=page1, 1=page2 etc)
// -------------------------------------------------------------------

virtualpaginate.prototype.showpage=function(pagenumber){
	var totalitems=this.pieces.length //total number of broken up divs
	var showstartindex=pagenumber*this.chunksize //array index of div to start showing per pagenumber setting
	var showendindex=showstartindex+this.chunksize-1 //array index of div to stop showing after per pagenumber setting
	for (var i=0; i<totalitems; i++){
		if (i>=showstartindex && i<=showendindex) {
			//this.pieces[i].style.display="block";
			var a = this.pieces[i];
			$(a).fadeIn();
			
		}
		else
			this.pieces[i].style.display="none"
	}
	if (this.persist){ //if persistence enabled
		virtualpaginate.setCookie("dd_"+this.piececlass, this.currentpage)
	}
	if (this.cpspan.length>0){ //if <span class="paginateinfo> element is present, update it with the most current info (ie: Page 3/4)
		for (var p=0; p<this.cpspan.length; p++)
			this.cpspan[p].innerHTML='Page '+(this.currentpage+1)+'/'+this.pagecount
	}
}

// -------------------------------------------------------------------
// PRIVATE: build() methods- Various methods to create pagination interfaces
// paginate_paginate_build()- Main build() paginate function
// paginate_output_flatview()- Accepts <span class="flatview"> element and populates it with sequential pagination links
// paginate_paginate_build_flatview()- Parses the modified <span class="flatview"> element and assigns click behavior to the pagination links
// paginate_build_selectmenu(paginatedropdown)- Accepts an empty SELECT element and turns it into pagination menu
// paginate_build_regularlinks(paginatelinks)- Accepts a collection of links and screens out/ creates pagination out of ones with specific "rel" attr
// paginate_build_cpinfo(cpspan)- Accepts <span class="paginateinfo"> element and displays current page info (ie: Page 1/4)
// -------------------------------------------------------------------

virtualpaginate.prototype.paginate_build=function(divid, divpos, optnavtext){
	var instanceOfBox=this
	var paginatediv=document.getElementById(divid)
	if (this.chunksize==this.pieces.length){ //if user has set to display all pieces at once, no point in creating pagination div
		paginatediv.style.display="none"
		return
	}
	var paginationcode=paginatediv.innerHTML //Get user defined, "unprocessed" HTML within paginate div
	if (paginatediv.getElementsByTagName("select").length>0) //if there's a select menu in div
		this.paginate_build_selectmenu(paginatediv.getElementsByTagName("select")[0], divpos, optnavtext)
	if (paginatediv.getElementsByTagName("a").length>0) //if there are links defined in div
		this.paginate_build_regularlinks(paginatediv.getElementsByTagName("a"))
	var allspans=paginatediv.getElementsByTagName("span") //Look for span tags within passed div
	for (var i=0; i<allspans.length; i++){
		if (allspans[i].className=="flatview")
			this.paginate_output_flatview(allspans[i], divpos, optnavtext)
		else if (allspans[i].className=="paginateinfo")
			this.paginate_build_cpinfo(allspans[i], divpos)
	}
	this.paginatediv[divpos]=paginatediv
}

virtualpaginate.prototype.paginate_output_flatview=function(flatviewcontainer, divpos, anchortext){
	var flatviewhtml=""
	var anchortext=anchortext || new Array()
	for (var i=0; i<this.pagecount; i++){
		if (typeof anchortext[i]!="undefined") //if custom anchor text for this link exists
			flatviewhtml+='<a href="#flatview" rel="'+i+'">'+anchortext[i]+'</a> ' //build pagination link using custom anchor text
		else
			flatviewhtml+='<a href="#flatview" rel="'+i+'">'+(i+1)+'</a> ' //build  pagination link using auto incremented sequential number instead
	}
	flatviewcontainer.innerHTML=flatviewhtml
	this.paginate_build_flatview(flatviewcontainer, divpos, anchortext)
}

virtualpaginate.prototype.paginate_build_flatview=function(flatviewcontainer, divpos, anchortext){
	var instanceOfBox=this
	var flatviewhtml=""
	this.flatviewlinks[divpos]=flatviewcontainer.getElementsByTagName("a")
	for (var i=0; i<this.flatviewlinks[divpos].length; i++){
		this.flatviewlinks[divpos][i].onclick=function(){
			var prevlinkindex=instanceOfBox.currentpage //Get index of last clicked on flatview link
			var curlinkindex=parseInt(this.getAttribute("rel"))
			instanceOfBox.navigate(curlinkindex)
			return false
		}
	}
	this.flatviewlinks[divpos][this.currentpage].className="selected" //"Highlight" current flatview link
	this.flatviewpresent=true //indicate flat view links are present
}

virtualpaginate.prototype.paginate_build_selectmenu=function(paginatedropdown, divpos, anchortext){
	var instanceOfBox=this
	var anchortext=anchortext || new Array()
	this.selectmenupresent=1
	for (var i=0; i<this.pagecount; i++){
		if (typeof anchortext[i]!="undefined") //if custom anchor text for this link exists, use anchor text as each OPTION's text
			paginatedropdown.options[i]=new Option(anchortext[i], i)
		else //else, use auto incremented, sequential numbers
			paginatedropdown.options[i]=new Option("Page "+(i+1)+" of "+this.pagecount, i)
	}
	paginatedropdown.selectedIndex=this.currentpage
	setTimeout(function(){paginatedropdown.selectedIndex=instanceOfBox.currentpage}, 500) //refresh currently selected option (for IE's sake)
	paginatedropdown.onchange=function(){
	instanceOfBox.navigate(this.selectedIndex)
	}
	this.selectmenu[divpos]=paginatedropdown
	this.selectmenu[divpos].selectedIndex=this.currentpage //"Select" current page's corresponding option
}

virtualpaginate.prototype.paginate_build_regularlinks=function(paginatelinks){
	var instanceOfBox=this
	for (var i=0; i<paginatelinks.length; i++){
		var currentpagerel=paginatelinks[i].getAttribute("rel")
		if (/^(previous)|(next)|(first)|(last)$/.test(currentpagerel)){ //screen for these "rel" values
			paginatelinks[i].onclick=function(){
				instanceOfBox.navigate(this.getAttribute("rel"))
				return false
			}
		}
		if (currentpagerel=="previous" || paginatelinks[i].href.indexOf("previous")!=-1){ //check if this is a "previous" link
			if (!this.wraparound && this.currentpage==0) //if current page is first page, disable "prev" link
				virtualpaginate.setcssclass(paginatelinks[i], "disabled", "add")
			this.prevlinks.push(paginatelinks[i])
		}
		else if (currentpagerel=="next" || paginatelinks[i].href.indexOf("next")!=-1){ //check if this is a "next" link
			if (!this.wraparound && this.currentpage==this.pagecount-1) //if current page is last page, disable "next" link
				virtualpaginate.setcssclass(paginatelinks[i], "disabled", "add")
			this.nextlinks.push(paginatelinks[i])
		}
		
	}
}

virtualpaginate.prototype.paginate_build_cpinfo=function(cpspan, divpos){
	this.cpspan[divpos]=cpspan
	cpspan.innerHTML='Page '+(this.currentpage+1)+'/'+this.pagecount
}


// JavaScript Document