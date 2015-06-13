function native_uploader(image_container,counter)
{
	   var custom_uploader;
	   
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.custom_uploader = wp.media({
            title: 'Choose Image',
            button: {text: 'Choose Image'},
			frame:'post',
            state:'insert',
			id: 'logo-frame',
		    multiple: false,
			editing_sidebar: false, // Just added for example
			default_tab: 'library', // Just added for example
			tabs: 'upload, library', // Just added for example
			returned_image_size: 'thumbnail' // Just added for example
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('insert', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
			jQuery('#gimage'+counter).hide();
			jQuery( '#'+image_container).html( '<img src="'+attachment.url+'" width="100px" height="100px" style="vertical-align: middle;margin-right: 15px;"><a onclick="return remove_thumb(\''+image_container+'\')" title="Remove" alt="Remove" href="javascript:void(0);">Remove</a>');
			jQuery( '#upload_image_new'+counter).val(attachment.url);	
			
        });
 
        //Open the uploader dialog
        custom_uploader.open(); 
		return false;
}
function remove_thumb(image_id)
{
	jQuery('#'+image_id).html('');
}