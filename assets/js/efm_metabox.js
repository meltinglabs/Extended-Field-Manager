jQuery.fn.exists = function () {
    return jQuery(this).length > 0;
}
jQuery(document).ready(function($) {
	/* multiple form input */
	if( $(".efm_box").exists() ) {
		$('.efm_box .remove').live('click', function(b,e){
			var me = $(this).parent('li');
			var box = me.parent('ul')
			var sibs = me.siblings().length;
			if( sibs <= 1 ){
				box.addClass('single');
			}
			me.remove();
			return false;
		});
		$('.efm_box .add').live('click', function(b,e){
			var me = $(this).parent('li');
			var box = me.parent('ul');
			me.clone().appendTo(box).children('input').removeAttr('id').val('');
			if( box.hasClass('single') ){
				box.removeClass('single');		
			}			
			return false;
		});
	}
	
	/* Handle upload fields */
	if( $(".efm_upload").exists() ) {
		var uploaders = {};
		$(".efm_upload").each( function(i, me){
			var uploadConfig = $.parseJSON( $(me).children(".efm_upload_config").val() );
			var cfg = $.extend( efm_plupload_config, uploadConfig )
			
			var uploader = uploaders[cfg.multipart_params.image_id];
			uploader = new plupload.Uploader( cfg );
			uploader.bind('Init', function(up){});
            uploader.init();
			
			 // a file was added in the queue 
            uploader.bind('FilesAdded', function(up, files){
                $.each( files, function( i, file ) {
                    $(me).find('.efm_image').append(
                        '<li class="file" id="' + file.id + '">'
							+'<div class="uploading">'
								+'<strong>'+ file.name + '</strong> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') '
								+ '<div class="fileprogress"></div>'
							+'</div>'
						+'</li>'
					);
                }); 
                up.refresh();
                up.start();
            });
			
			uploader.bind('UploadProgress', function(up, file) { 
                $('#' + file.id + " .fileprogress").width(file.percent + "%");
                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });
			
			// a file was uploaded
            uploader.bind('FileUploaded', function(up, file, result) { 
				var item = $('#' + file.id);
				item.children('.uploading').fadeOut();
				var r = $.parseJSON( result.response );				
				if( r.hasOwnProperty('error') ){
					console.log( r.error );					
				} else {
					/* This is not a multiple upload field - remove the old file */
					if( !up.settings.multi_selection ){
						removeImage( item.siblings('li') );
					} else {
						r.fieldname += '[]';
					}
					var fld = $('<input type="hidden" />').attr('name', r.fieldname).attr('value', r.url);
					item.html('').append(
						'<div class="img_pw">'
							+'<img src="'+ r.url +'" alt="Uploaded image" />'
							+'<a href="" class="remove">Remove</a>'
						+'</div>'
					);
					fld.appendTo( item );
				}               
            });
		});	
		
		$('.efm_upload li .remove').live('click', function(b,e){
			removeImage( $(this).parent().parent('li') );
			return false;
		});
	}
});
function removeImage( me ){
	var file = me.children('.field').val();
	var post = me.children('.post').val() || false;
	var meta = me.children('.meta').val() || false;
	removeFile( 'images', file, post, meta );
	me.fadeOut( function(){
		jQuery(this).remove();
	});
}
function removeFile( type, file, post, meta ){	
	jQuery.post( ajaxurl,{
		'action' : 'efmrequest'
		,'task' : 'remove_file'
		,'field_type' : type
		,'to_remove' : file
		,'post' : post
		,'meta' : meta
	}
	,function( response ){});
}