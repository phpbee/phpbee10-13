	if ( typeof left_heightWid == 'undefined' ) {
		function left_heightWid() {};
	}

	var params = {  
		BGColor: "#FFFFFF",
		wmode: "opaque"
	};
	
	var attributes = {  
		id: "MultiPowUpload",  
		name: "MultiPowUpload"
	};


	var flashvars = {
	  "language.autoDetect": "true",
	  "serialNumber": widget_MultiPowUpload_license ,
	  "uploadUrl": "/libs/widgets/MultiPowUpload/FileProcessingScripts/PHP/uploadfiles.php?stayHere=true",
	  "customPostFields":params_str,
	  "removeUploadedFilesFromList": "true",
	  "fileFilter.types":"Images|*.jpg:*.jpeg:*.gif:*.png:*.bmp",
	  "sendThumbnails": "true",
	  "sendOriginalImages": "false",
	  "useExternalInterface": "true",
	  "fileView.defaultView":"thumbnails",
	  "thumbnail.width": "1600",
	  "thumbnail.height": "1600",
	  "thumbnail.resizeMode": "fit",
	  "thumbnail.format": "AUTO",
	  "thumbnail.jpgQuality": "85",
	  "thumbnail.resizeSmallImages":"false",
	  "thumbnail.backgroundColor": "#000000",
	  "thumbnail.transparentBackground": "true",
	  "thumbnail.autoRotate": "true",
	  "readImageMetadata": "true",
	  "thumbnailView.allowCrop": "true",
	  "thumbnailView.allowRotate": "true",
	  "thumbnailView.cropRectKeepAspectRatio": "NO",
	  "thumbnailView.showCropRectDimensions": "true",
	  "thumbnailView.thumbnailWidth": "180",
	  "thumbnailView.thumbnailHeight": "180",
	  "thumbnail.watermark.enabled": "true",
	  "thumbnail.watermark.position": "bottom.center",
	  "thumbnail.watermark.text" : "",
	  "thumbnail.watermark.imageUrl": widget_MultiPowUpload_watermark


	};

	function MultiPowUpload_Start() {
		var so=swfobject.embedSWF("/libs/widgets/MultiPowUpload/ElementITMultiPowUpload.swf", "MultiPowUpload_holder", "900", "450", "10.0.0", "/widgets/MultiPowUpload/Extra/expressInstall.swf", flashvars, params, attributes);
	}



	var path_to_file = "";
	
	function MultiPowUpload_onThumbnailUploadComplete(li, response)
	{
			addThumbnail(response);
	}
	
	function addThumbnail(response)
	{
		$("#gallery_"+hash).append(response);
	}



	$(document).ready(function() {
		$("#gallery_"+hash+" div.MultiPowUploadGalleryItem").live("click",function(){
			var li=$(this);
			var inp=$("input",li);
			inp.attr('checked',!(inp.attr('checked')));
			li.removeClass('MultiPowUploadCheckedElement');
			if(inp.attr('checked')) li.addClass('MultiPowUploadCheckedElement');
		});


		$(".MultiPowUploadGalleryGroup").dblclick(function(){
			$("div.MultiPowUploadGalleryItem",this).click();
			return false;
		});
		$(".MultiPowUploadGallery").dblclick(function(){
			$("div.MultiPowUploadGalleryItem",this).click();
			return false;
		});

		$("#checked_items_submit").click(function(){
			var form=$(this).closest('form');
			//var action=$(':radio[name=checked_items_action]',form).filter(":checked").val();
			var gspgid="widgets/MultiPowUpload/action";
			$('input[name=gspgid_form]',form).val(gspgid);
			form.get(0).submit();
		});
	});

this.imagePreview = function(){	
	/* CONFIG */
		
		xOffset = 10;
		yOffset = 30;
		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result
		
	/* END CONFIG */
	$("a.preview").hover(function(e){
		this.t = this.title;
		this.title = "";	
		var c = (this.t != "") ? "<br/>" + this.t : "";
		$("body").append("<p id='preview'><img src='"+ this.href +"' alt='Image preview' />"+ c +"</p>");								 
		$("#preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");						
    },
	function(){
		this.title = this.t;	
		$("#preview").remove();
    });	
	$("a.preview").mousemove(function(e){
		$("#preview")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});			
};


// starting the script on page load
$(document).ready(function(){
	imagePreview();
});



