<?php
/**
 * Template for Image Upload
 *
 * PHP version 5
 *
 * LICENSE: Hotaru CMS is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of 
 * the License, or (at your option) any later version. 
 *
 * Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE. 
 *
 * You should have received a copy of the GNU General Public License along 
 * with Hotaru CMS. If not, see http://www.gnu.org/licenses/.
 * 
 * @category  Content Management System
 * @package   HotaruCMS
 * @author    Nick Ramsay <admin@hotarucms.org>
 * @copyright Copyright (c) 2009, Hotaru CMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://www.hotarucms.org/
 */

$iu_settings = $h->getSerializedSettings();	
global $image_exists;

 ?>
<script type="text/javascript">
	<?php
	$var = 'max_images_'.str_replace( '-','_', $h->currentUser->role);
	?>
	var imageMaxUploadSize = <?=@intval($iu_settings['max_file_size']*1024)?>;
	var maxImages = <?=@intval($iu_settings[$var])?>;
	var targetPath = '<?php echo BASEURL; ?>index.php?page=<?php echo $h->pageName; if ($h->pageName == 'edit_post') { echo "&post_id=" . $h->post->id; } ?>';
</script>
<link rel="stylesheet" href="/content/plugins/pixel_suite/css/jquery.fileupload-ui.css">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="css/jquery.fileupload-ui-noscript.css"></noscript>

<link rel="stylesheet" href="/content/plugins/pixel_suite/css/bootstrap.min.css">
<!-- Bootstrap styles for responsive website layout, supporting different screen sizes -->
<link rel="stylesheet" href="/content/plugins/pixel_suite/css/bootstrap-responsive.min.css">
<!-- Bootstrap CSS fixes for IE6 -->
<!--[if lt IE 7]><link rel="stylesheet" href="http://blueimp.github.com/cdn/css/bootstrap-ie6.min.css"><![endif]-->
<!-- Bootstrap Image Gallery styles -->
<link rel="stylesheet" href="/content/plugins/pixel_suite/css/bootstrap-image-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->

<table>
<tr>
	<td style='vertical-align: top;'><?php echo $h->lang['pixel_suite']; ?></td>
	<td>
	
    <form id="fileupload" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data">
        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
			<input type="hidden" name="image" value="true" />
        <div class="row fileupload-buttonbar">
            <div class="span7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="icon-plus icon-white"></i>
                    <span>Add files...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="submit" class="btn btn-primary start">
                    <i class="icon-upload icon-white"></i>
                    <span>Start upload</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="icon-ban-circle icon-white"></i>
                    <span>Cancel upload</span>
                </button>
                <button type="button" class="btn btn-danger delete">
                    <i class="icon-trash icon-white"></i>
                    <span>Delete</span>
                </button>
                <input type="checkbox" class="toggle">
            </div>
            <!-- The global progress information -->
            <div class="span5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="bar" style="width:0%;"></div>
                </div>
                <!-- The extended global progress information -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The loading indicator is shown during file processing -->
        <div class="fileupload-loading"></div>
        <br>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
    </form>
    <br>
</div>
<!-- modal-gallery is the modal dialog used for the image gallery -->
<div id="modal-gallery" class="modal modal-gallery hide fade" data-filter=":odd" tabindex="-1">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3 class="modal-title"></h3>
    </div>
    <div class="modal-body"><div class="modal-image"></div></div>
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><div class='message red'>{%=file.error%}</div></td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>Start</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>Cancel</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><div class='message red'>{%=file.error%}</div></td>
        {% } else if(file.success) { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <img style="max-width:100px;max-height:100px;" src="{%=file.thumbnail_url%}">
            {% } %}</td>
            <td class="name">
                {%=file.name%}
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><div class='message green'>{%=file.success%}</div></td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <img style="max-width:100px;max-height:100px;" src="{%=file.thumbnail_url%}">
            {% } %}</td>
            <td class="name">
                {%=file.name%}
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <td class="delete">
            <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                <i class="icon-trash icon-white"></i>
                <span>Delete</span>
            </button>
            <input type="checkbox" name="delete" value="1">
        </td>
    </tr>
{% } %}
</script>

	</td>
</tr>
</table>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="/content/plugins/pixel_suite/js/vendor/jquery.ui.widget.js"></script>
<!-- The Templates plugin is included to render the upload/download listings -->
<script src="/content/plugins/pixel_suite/js/tmpl.min.js"></script>
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<script src="/content/plugins/pixel_suite/js/load-image.min.js"></script>
<!-- The Canvas to Blob plugin is included for image resizing functionality 
<script src="http://blueimp.github.com/JavaScript-Canvas-to-Blob/canvas-to-blob.min.js"></script>
<!-- Bootstrap JS and Bootstrap Image Gallery are not required, but included for the demo
<script src="http://blueimp.github.com/cdn/js/bootstrap.min.js"></script>
<script src="http://blueimp.github.com/Bootstrap-Image-Gallery/js/bootstrap-image-gallery.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="/content/plugins/pixel_suite/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="/content/plugins/pixel_suite/js/jquery.fileupload.js"></script>
<!-- The File Upload file processing plugin -->
<script src="/content/plugins/pixel_suite/js/jquery.fileupload-fp.js"></script>
<!-- The File Upload user interface plugin -->
<script src="/content/plugins/pixel_suite/js/jquery.fileupload-ui.js"></script>
<!-- The main application script -->
<script src="/content/plugins/pixel_suite/js/main.js"></script>
<script type="text/javascript">
function customCheckFileUpload(){
	if( $( 'tbody.files tr' ).length >= maxImages )
		customDisableFileUpload();
	else
		customenableFileUpload();
}
function customDisableFileUpload(){
	$( '.btn.btn-success.fileinput-button' ).addClass( 'disabled' );
	$( 'input[type="file"]' ).hide();
}
function customenableFileUpload(){
	$( '.btn.btn-success.fileinput-button' ).removeClass( 'disabled' );
	$( 'input[type="file"]' ).show();
}
$( document ).ready( function(){

	var hiddenPostField = $( 'input[name="submit_post_id"]' ).val();
	if( hiddenPostField )
	{
		targetPath = '<?php echo BASEURL; ?>index.php?page=<?php echo $h->pageName?>&post_id='+hiddenPostField;
		$('#fileupload').fileupload( 'option', {url: targetPath} );
	}

	$($('#fileupload')[0]).fileupload('option', 'done').call($('#fileupload')[0], null, {result: {files:<?=json_encode( $image_exists )?>}});
	
	$( 'button.btn-danger' ).live( 'click', customCheckFileUpload );
	$( '.btn.btn-success.fileinput-button' ).live( 'mouseenter', customCheckFileUpload );
	customCheckFileUpload();
} );
<?php if( count( $image_exists ) ){ ?>

<?php } ?>
</script>
<table>
<tr>
    <td style='text-align:right;'><input type='submit' onclick="safeExit=true;$( '#submit_2_form' ).submit(); return false;" class='submit' name='submit' value='<?php echo $h->lang['main_form_next']; ?>' /></td>
</tr>
</table>