/*
 * jQuery File Upload Plugin JS Example 8.9.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/* global $, window */



$(function () {
    'use strict';

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: '/wedmarket.com.ua/photo/save_photos',
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
    });

});

$('#fileupload').bind('fileuploadstop', function (e, data) {
        $('#btn-submit').attr("disabled", "disabled");
        $('#btn-go-to-album').removeAttr("disabled"); 
    });

$('#fileupload').bind('fileuploadchange', function (e, data) {
        $('#btn-submit').removeAttr("disabled");
    });

$('#fileupload').bind('fileuploaddrop', function (e, data) {
        $('#btn-submit').removeAttr("disabled");
    });

