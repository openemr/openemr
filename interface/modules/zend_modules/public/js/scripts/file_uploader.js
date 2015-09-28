/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Basil PT <basil@zhservices.com>
* +------------------------------------------------------------------------------+
*/

$(document).ready(function(){
	$(".file-uploader-progress-close").click(function(){
		$(this).parents().eq(1).hide();
	});
	$(".file-uploader-progress-minimize").click(function(){
		$(this).hide();
		$(this).siblings(".file-uploader-progress-expand").show();
		$(this).parents().eq(1).css("height","28px");
		$(this).parents().eq(1).css("overflow","hidden");
		
	});
	$(".file-uploader-progress-expand").click(function(){
		window_height = window.innerHeight;
		progress_div_height = Number(window_height*40/100);
		$(this).hide();
		$(this).siblings(".file-uploader-progress-minimize").show();
		$(this).parents().eq(1).css("height",progress_div_height+"px");
		$(this).parents().eq(1).css("overflow","auto");
		
	});
});

function AjaxFileUploader(settings) {
	uplader_id = settings.uploader_id;
	
	drag_enter_class = settings.custom_classes.drag_enter;
	drag_over_class	 = settings.custom_classes.drag_over;
	
	// Window Height & Width
	window_height = window.innerHeight;
	window_width	= window.innerWidth;
	
	progress_div_height = Number(window_height*40/100);
	progress_div_width	= Number(window_width*40/100);
	
	if($(".file-uploader-progress").length <1 && settings.progress_bar) {
		var progress_div = "<div class='file-uploader-progress' "+
										 " style='width:"+progress_div_width+"px;height:"+progress_div_height+"px;'>"+
										 "<div class='file-uploader-progress-div_head'>"+
										 "<div class='file-uploader-progress-close'></div>"+
										 "<div class='file-uploader-progress-minimize'></div>"+
										 "<div class='file-uploader-progress-expand'></div>"+
										 "</div>"+
										 "<div class='file-uploader-progress-content'></div>"+
										 "</div>";
		$("body").append(progress_div);
	}
	if(settings.batch_upload){
		if($("#"+uplader_id+"_browse"))
			$("#"+uplader_id+"_browse").attr("multiple",true);
	} else {
		if($("#"+uplader_id+"_browse"))
			$("#"+uplader_id+"_browse").attr("multiple",false);
	}
	$("#"+uplader_id+"_upload").click(function(){
			progress_div_height = Number(window_height*40/100);
			progress_div_width	= Number(window_width*80/100);
			$(".file-uploader-progress").css("height",progress_div_height+"px");
			$(".file-uploader-progress").css("width",progress_div_width+"px");
			$(".file-uploader-progress").show();
			var files = $("#"+uplader_id+"_browse").prop("files");
			handleFileUpload(files,$(".file-uploader-progress-content"),settings);
	});
	
	if(settings.uploader_type == "single") {
		$("body").prepend("<div id='"+uplader_id+"_drop' class='disable-select file-uploader-drop-div' style='width:"+window_width+"px;height:"+window_height+"px;'></div>");
		
		// To Show/Hide Drop handler on file drag
		$(document).on('dragenter', function (e)
		{
			$('#'+uplader_id+'_drop').show();
			$('#'+uplader_id+'_drop').addClass(drag_enter_class);
			e.stopPropagation();
			e.preventDefault();
		});

		$(document).on('dragover', function (e)
		{
			$('#'+uplader_id+'_drop').show();
			$('#'+uplader_id+'_drop').addClass(drag_over_class);
			e.stopPropagation();
			e.preventDefault();
		});

		$(document).on('drop', function (e)
		{
			e.stopPropagation();
			e.preventDefault();
			
			$('#'+uplader_id+'_drop').removeClass(drag_over_class);
			$('#'+uplader_id+'_drop').removeClass(drag_enter_class);
			
			$('#'+uplader_id+'_drop').hide();
			
			progress_div_height = Number(window_height*40/100);
			progress_div_width	= Number(window_width*80/100);
			$(".file-uploader-progress").css("height",progress_div_height+"px");
			$(".file-uploader-progress").css("width",progress_div_width+"px");
			$(".file-uploader-progress").show();
			
			var files = e.originalEvent.dataTransfer.files;
			handleFileUpload(files,$(".file-uploader-progress-content"),settings);
			
		});

		$("body").on('dragleave', function (e)
		{
			$('#'+uplader_id+'_drop').removeClass(drag_over_class);
			$('#'+uplader_id+'_drop').removeClass(drag_enter_class);
			
			$('#'+uplader_id+'_drop').hide();
			
			e.stopPropagation();
			e.preventDefault();
		});
	} else if(settings.uploader_type == "multiple") {
		// Prevent Default
		$(document).on('dragenter', function (e)
		{
			e.stopPropagation();
			e.preventDefault();
		});

		$(document).on('dragover', function (e)
		{
			e.stopPropagation();
			e.preventDefault();
		});

		$(document).on('drop', function (e)
		{
			e.stopPropagation();
			e.preventDefault();	
		});
		
		var obj = $("#"+uplader_id);
	
		obj.on('dragenter', function (e){
			obj.addClass(drag_enter_class);
			e.stopPropagation();
			e.preventDefault();
		});
	
		obj.on('dragover', function (e){
			obj.addClass(drag_over_class);
			e.stopPropagation();
			e.preventDefault();
		});
	
		obj.on('drop', function (e){
			obj.removeClass(drag_over_class);
			obj.removeClass(drag_enter_class);
			e.preventDefault();
			progress_div_height = Number(window_height*40/100);
			progress_div_width	= Number(window_width*80/100);
			$(".file-uploader-progress").css("height",progress_div_height+"px");
			$(".file-uploader-progress").css("width",progress_div_width+"px");
			$(".file-uploader-progress").show();
			
			var files = e.originalEvent.dataTransfer.files;
			handleFileUpload(files,$(".file-uploader-progress-content"),settings);
		});
		
		$("body").on('dragleave', function (e)
		{
			obj.removeClass(drag_over_class);
			obj.removeClass(drag_enter_class);
			e.stopPropagation();
			e.preventDefault();
		});
	}
}

function handleFileUpload(files,obj,settings)
{
	if(settings.batch_upload) {
		file_length = files.length;
	} else {
		file_length = 1;
	}
	for (var i = 0; i < file_length; i++)
	{
		var fd = new FormData();
		fd.append('file', files[i]);
		if(settings.progress_bar) {
			var status = new createStatusbar(obj);
			status.setFileNameSize(files[i].name,files[i].size);
		}
		fd.append('file_location', settings.file_location);
		fd.append('patient_specific', settings.patient_specific.toString());
		fd.append('encounter_specific', settings.encounter_specific.toString());
		fd.append('user_specific', settings.user_specific.toString());
		fd.append('batch_upload', (files.length >1 && settings.batch_upload) ? 1 : 0);
		
		sendFileToServer(fd,status,settings.progress_bar,settings.success_function);
	}
}

function sendFileToServer(formData,status,progress_bar,success_function)
{
	var uploadURL = basePath+"/documents/documents/upload"; //Upload URL
	var extraData ={}; //Extra Data.
	var jqXHR=$.ajax({
		xhr: function() {
			var xhrobj = $.ajaxSettings.xhr();
			if (xhrobj.upload) {
				xhrobj.upload.addEventListener('progress', function(event) {
					var percent = 0;
					var position = event.loaded || event.position;
					var total = event.total;
					if (event.lengthComputable) {
						percent = Math.ceil(position / total * 100);
					}
					if(progress_bar) {
						status.setProgress(percent);
					}
					
				}, false);
			}
			return xhrobj;
		},
		url: uploadURL,
		type: "POST",
		contentType:false,
		processData: false,
		cache: false,
		data: formData,
		success: function(data){
			if(progress_bar) {
				status.setProgress(100);
			}
			eval(success_function+"("+data+")");
		}
	});
	if(progress_bar) {
		status.setAbort(jqXHR);
	}
}

var rowCount=0;
function createStatusbar(obj)
{
     rowCount++;
     var row="odd";
     if(rowCount %2 ==0) row ="even";
     this.statusbar = $("<div class='statusbar "+row+"'></div>");
     this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
     this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
     this.progressBar = $("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
	 var resultTranslated = js_xl('Abort');
     this.abort = $("<div class='abort'>"+resultTranslated.msg+"</div>").appendTo(this.statusbar);
     obj.after(this.statusbar);
 
    this.setFileNameSize = function(name,size)
    {
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024)
        {
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }
        else
        {
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }
    this.setProgress = function(progress)
    {       
        var progressBarWidth =progress*this.progressBar.width()/ 100;  
        this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
            this.abort.hide();
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
        });
    }
}