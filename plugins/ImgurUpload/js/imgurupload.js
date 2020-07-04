/*global gdn, jQuery, Dropzone */
/**
 * ImgurUpload, a drag'n'drop image upload tool for Vanilla Forums
 * Copyright (C) 2015  James Ducker <james.ducker@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

(function ( $ ) {

	"use strict";

	// Stores Dropzone instances
	var dzs = [ ];

	// Inserts the given text at the caret position
	// in the given input or textarea el.
	var insertAtCursor = function ( el, text ) {

		var sel, startPos, endPos;

		text = text + '\n';
        
        // WYSIWYG Editor needs its HTML injected differently than the others.
        // This is because its InputBox is within a IFrame.
        var editor_type = $( "#Form_Format" ).val().toLowerCase();
        if (editor_type == "wysiwyg")
        {
            // We can get the correct sandbox by taking advantage of EL's parent being the editor whose button/dropzone was trigger.
            var editor_sandboxes = el.parentElement.getElementsByClassName("wysihtml5-sandbox");
            if (editor_sandboxes.length > 0)
            {
                // Get the HTML document embeded in the sandbox and then get the InputBox itself.
                var editor_content_document = editor_sandboxes[0].contentDocument || editor_sandboxes[0].contentWindow.document;
                var editor_inputbox = editor_content_document.getElementsByClassName("InputBox TextBox");
                if (editor_inputbox.length > 0)
                {
                    // Unfortunately, I don't quite know how to get the caret position just yet.
                    // For now, just add the images to the end of the text box.
                    editor_inputbox[0].insertAdjacentHTML("beforeend", text);
                }
            }
            return;
        }
        
		//IE support
		if ( document.selection ) {

			el.focus();
			sel = document.selection.createRange();
			sel.text = text;

		} else if ( el.selectionStart || el.selectionStart == '0' ) {

			startPos = el.selectionStart;
			endPos = el.selectionEnd;
			el.value = el.value.substring(0, startPos) + text +
				el.value.substring(endPos, el.value.length);

		} else {

			el.value += text;
		}

	};

	// Returns images in appropriate markup, depending on 
	// forum settings.
	var getLinkCode = function ( data ) {

		var response, thumbnail,
			url = data.link.replace( /^http:/i, "https:" ),
			resize = ( gdn.definition("resizeimages") === "1" && gdn.definition("imgurthumbnailsuffix").length === 1 ),
			type = $( "#Form_Format" ).val();

		if ( resize ) {

			thumbnail = url.split( "." );
			thumbnail[ thumbnail.length - 2 ] += gdn.definition("imgurthumbnailsuffix");
			thumbnail = thumbnail.join(".");

		}

		switch ( type.toLowerCase() ) {
			case "bbcode" :
				response = ( resize ? '[url=' + url + '][img]' + thumbnail + '[/img][/url]' : '[img]' + url + '[/img]' );
				break;

			case "markdown" :
				response = ( resize ? '[![](' + thumbnail + ')](' + url + ')' : '![](' + url + ')' );
				break;

			case "html" :
				// Specify width and height, so your users don't get annoyed with the page moving around as images load!
				if ( resize ) {
					response = '<a href="' + url + '" target="_new"><img src="' + thumbnail + '" alt="" /></a>';
				} else {
					response = '<img src="' + url + '" alt="" width="' + data.width + '" height="' + data.height + '" />';
				}

				break;
                
            case "wysiwyg":
                // Same as HTML, with added <center></center> tags
				if ( resize ) {
					response = '<center><a href="' + url + '" target="_new"><img src="' + thumbnail + '" alt="" /></a></center>';
				} else {
					response = '<center><img src="' + url + '" alt="" width="' + data.width + '" height="' + data.height + '" style="padding: 12px 12px /></center>';
				}
                break;

            case "rich":
				// HELPFUL source: https://stackoverflow.com/questions/46626633/how-do-you-insert-html-into-a-quilljs
				//
				// Works fully, but requires dangerous HTML.
				// to work around this, make sure the url starts with "https://i.imgur.com/"
				// (adapted from https://stackoverflow.com/questions/9714525/javascript-image-url-verify)
				if (url.startsWith("https://i.imgur.com/") == true) {
					// Then make sure we are embedding a url using a regular expression
					if (url.match(/\.(jpeg|jpg|gif|png|tiff|bmp)$/) != null) {
						// If all the checks work, then paste the HTML!
						//var html = '<img src="' + url + '" alt="Imgur image" width="' + data.width + '" height="' + data.height + '" />'
						
						var html = '';
						html += '<div class="js-embed embedResponsive" contenteditable="false">';
						html += '<div class="embedImage">';
						html += '<div class="embedImage-link">';
						html += '<img class="embedImage-img embed-focusableElement" src="' + url + '" alt="Imgur image" tabindex="-1">';
						html += '</div>';
						html += '</div>';
						html += '</div>';
						//console.log(html);
						quill.clipboard.dangerouslyPasteHTML(quill.getSelection(true).index, html, "api");
					} else {
						alert("URL received from Imgur plugin does not point to a valid/supported image format! Please notify forum staff!");
					}
				} else {
					alert("URL received from Imgur plugin does not point to Imgur! Please notify forum staff!");
				}
				
				return '';
                break;
                
			default :
				response = url;
				break;

		}

		return response;

	};

	var getDropzoneConfig = function ( ta, previewCtx, clickable ) {

		var maxFilesizeMB = 10;

		return {
			init: function ( ) {
				this.on( "error", function ( file, message ) {
					if ( message.indexOf("File is too big") > -1 ) {
						// Handle oversize file error a bit more nicely than default messaging
						message = "File <strong>" + file.name + "</strong> is too large at " + Math.round(file.size/1024/1024*100)/100 + "MB. Max filesize is " + maxFilesizeMB + "MB.";
					}

					gdn.informError( message );
					this.removeFile( file );
				});
			},
			sending: function ( ) {
				ta.prop( "disabled", true );
			},
			queuecomplete: function ( ) {
				ta.prop( "disabled", false );
			},
			success: function ( file, response ) {
				if ( response.success ) {
					var link_data = getLinkCode(response.data);
					if (link_data) {
						if (link_data === "") {
							// Do nothing!
						} else {
							insertAtCursor( ta[0], link_data);
						}
					}
				} else {
					gdn.informError( "Something went wrong while uploading your images. Our image host, imgur.com, may be having technical issues. Please try again in a few minutes." )
				}
			},
			autoQueue: true,
			// Accept all image types
			acceptedFiles: "image/*",
			paramName: "image",
			clickable: clickable,
			method: "post",
			// Imgur API max filesize is 10MB
			maxFilesize: maxFilesizeMB,
			maxFiles: 20,
			previewsContainer: previewCtx[0],
			thumbnailWidth: 60,
			thumbnailHeight: 60,
			fallback: function ( ) { },
			url: "https://api.imgur.com/3/upload/",
			headers: {
				Authorization: "Client-ID " + gdn.definition("imgurclientid"),
				//Accept: "application/json"
			}
		};

	};

	var initTextarea = function ( ta ) {

		var fileInput,
			form = ta.parents( "form" ),
			helpTextWrap = form.find( ".bodybox-wrap" ),
			imgurHelpText = helpTextWrap.find( ".imgur-help-text" ),
			dzIdx = -1,
			submitBtn = form.find( "[type=submit]" ).last(),
			previewCtx = $("<div/>", {
				"class": "imguruploader-preview-ctx"
			});

		// Don't bother doing anything if a textarea isn't found
		if ( ta.length ) {

			ta.after( previewCtx );
			
			/*
			if ( helpTextWrap.length && ! imgurHelpText.length ) {
				$("<div/>", {
					"class": "imgur-help-text",
					text: "You can drag and drop images into the comment box."
				}).appendTo( helpTextWrap );
			}
			*/

			// Setup the dropzone
			if ( gdn.definition("enabledragdrop") === "1" ) {

				dzs.push( new Dropzone(ta[0], getDropzoneConfig(ta, previewCtx, false)) );
				dzIdx = dzs.length - 1;
			}

			// If we are dealing with a device that reports to be a touch-screen device,
			// we should show a button also, as most touch-screen devices are mobiles,
			// which don't support file drag'n'drop.
			// (This method isn't perfect but it's pretty effective, I think.)
			if ( "ontouchstart" in window || gdn.definition("showimagesbtn") === "1" ) {

				fileInput = $( "<a href=\"#\" class=\"Button ButtonAddImages\">Add Images</a>" )
					.on( "click", function ( e ) { e.preventDefault(); });
				submitBtn.before( fileInput );
				dzs.push( new Dropzone(fileInput[0], getDropzoneConfig(ta, previewCtx, true)) );

			}

			// Clear the previews frame when the post is submitted
			submitBtn.on( "click", function ( ) {

				previewCtx.empty();

			});

			// Do some additional magic with image links
			// Useful when users drag an image from another browser window
			// This feature can be toggled in the plugin config page.
			if ( gdn.definition("processimageurls") === "1" ) {

				ta.on( "drop", function ( e ) {

					var data = e.originalEvent.dataTransfer.getData('URL');

					if ( data.length ) {

						e.preventDefault();
						e.stopPropagation();

						if ( data.match(/([a-z\-_0-9\/\:\.]*\.(jpeg|jpg|gif|png|tiff|bmp))/i) ) {

							var link_data = getLinkCode({link: data})
							if (link_data) {
								if (link_data === "") {
									// Do nothing!
								} else {
									insertAtCursor( ta[0], link_data);
								}
							}

						} else {

							// Insert plaintext
							insertAtCursor( ta[0], data );

						}

					}

				});

			}

		}

	};

	$(function ( ) {

		var ta = $( "#Form_Body" );

		initTextarea( ta );

		// Capture Vanilla's EditCommentFormLoaded event
		// And add controls to any edit boxes that are generated
		$( document ).on( "EditCommentFormLoaded", function ( e, ctx ) {

			// It's possible to have multiple textareas open at once,
			// So we have to make sure to loop through all of them.
			// initTextarea takes care of not re-initialising things.
			ctx.find( ".EditCommentForm" ).find( "textarea" ).each( function ( ) {

				initTextarea( $(this) );

			});

		});

	});

}( jQuery ));
