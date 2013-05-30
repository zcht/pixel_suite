jQuery('document').ready(function($) {
	
	$("#pixel_suite_form").ajaxForm({
        // target identifies the element(s) to update with the server response 
        target: '#upload_result', 
		beforeSubmit:function( arr, form, options )
		{
			if( !imageMaxUploadSize ) return true;
			var allOk = true;
			imageMaxUploadSize = parseInt( imageMaxUploadSize );
			$( '#upload_result' ).html( '' );
			$( '#pixel_suite_form input[type="file"]' ).each( function(){
				if( !$( this )[0].files[0] ) return;
				if( $( this )[0].files[0].size/1024 > imageMaxUploadSize )
				{
					allOk = false;
					$( '#upload_result' ).append( '<div class="message red" style="">Filesize of '+$( '#file_0' )[0].files[0].name+' exceeds limit of '+imageMaxUploadSize+'KB.</div>' );
					
				}
			} );
			return allOk;
		},
        // success identifies the function to invoke when the server response 
        // has been received; here we apply a fade-in effect to the new content 
        success: function() { 
            $('#upload_result').fadeIn('slow');
			$( '#pixel_suite_form input[type="file"]' ).each( function(){
				if( $( this ).val() )
				{
					$( this ).next().show().unbind( 'click' ).click( function(){ 
						$( this ).hide().prev().val( '' ); 
					} );
				}
			} );

        }
	});
	
});