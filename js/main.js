/*
 * jQuery File Upload Plugin JS Example 7.0
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, unparam: true, regexp: true */
/*global $, window, document */

$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: targetPath
    });

    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );

        // Demo settings:
        $('#fileupload').fileupload('option', {
            url: targetPath,
            maxFileSize: imageMaxUploadSize,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
            process: [
                {
                    action: 'load',
                    fileTypes: /^image\/(gif|jpeg|png)$/,
                    maxFileSize: imageMaxUploadSize // 20MB
                },
                {
                    action: 'resize',
                    maxWidth: 1440,
                    maxHeight: 900
                },
                {
                    action: 'save'
                }
            ]
        }).bind('fileuploadadd', customCheckFileUpload)
    .bind('fileuploadsubmit', customCheckFileUpload)
    .bind('fileuploadsend', customCheckFileUpload)
    .bind('fileuploaddone', customCheckFileUpload)
    .bind('fileuploadfail', customCheckFileUpload)
    .bind('fileuploadalways', customCheckFileUpload)
    .bind('fileuploadprogress', customCheckFileUpload)
    .bind('fileuploadprogressall', customCheckFileUpload)
    .bind('fileuploadstart', customCheckFileUpload)
    .bind('fileuploadstop', customCheckFileUpload)
    .bind('fileuploadchange', customCheckFileUpload)
    .bind('fileuploadpaste', customCheckFileUpload)
    .bind('fileuploaddrop', customCheckFileUpload)
    .bind('fileuploaddragover', customCheckFileUpload)
    .bind('fileuploadchunksend', customCheckFileUpload)
    .bind('fileuploadchunkdone', customCheckFileUpload)
    .bind('fileuploadchunkfail', customCheckFileUpload)
    .bind('fileuploadchunkalways', customCheckFileUpload);
        // Upload server status check for browsers with CORS support:

});
