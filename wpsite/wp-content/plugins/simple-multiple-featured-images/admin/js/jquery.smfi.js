/**
 * Allows to create multiple featured images.
 *
 * @author Roman Bauer.
 * @since  1.0.0
 */
jQuery('document').ready( function( jQuery ){
	
	// Search smfi metabox,
	var metaBox = jQuery( '#smfi-metabox.postbox' );
	
	if( metaBox.length ) {
		
		// Get error container which will be used for error reporting.
		var errorContainer = metaBox.find( '#smfi-error-container' );
		
		// Get add image button.
		var addNewImgBtn = metaBox.find( '#smfi-add-new-img-btn' );
		
		// Get image container.
		var imgContainer = metaBox.find( '.smfi-img-container' );
		
		/*
		 * Open media frame as soon as the add new image button was clicked.
		 * This allows the user to add a new image from the media library.
		 */
		de.smfi.MediaFrameManager.openMediaFrameOnAddBtnClick( addNewImgBtn, imgContainer, errorContainer );

		// Enable removal and modification of existing images.
		imgContainer.find( '.smfi-img' ).each( function( index, element ) {
			
			/*
			 * Open media frame as soon as an existing image was clicked.
			 * This allows the user to exchange an existing image by a new one from the media library.
			 */
			de.smfi.MediaFrameManager.openMediaFrameOnImgClick( jQuery( element ), errorContainer );
			
			// Remove image as soon as its remove button was clicked.
			de.smfi.MediaFrameManager.removeImgOnBtnClick( jQuery( element ).siblings( '.smfi-remove-img-btn' ) );
		} );
	}
} );

var de = de || {
	smfi : {
		
		ImageAddedListener : [],
		
		ImageRemovedListener : [],
		
		/**
		 * Contains translated textes for the UI. If no translation available a default one is used.
		 *
		 * @since  1.0.0
		 * @access private
		 *
		 * @type     {Object}
		 */
		UIText : {
			
			/**
			 * The title for the media frame.
			 *
			 * @since  1.0.0
			 * @access private
			 *
			 * @type     {string}
			 */
			mediaFrameTitle: typeof smfi_translation_object !== 'undefined' ? smfi_translation_object.media_frame_title : 'Select image',
			
			/**
			 * The text for the media frame add button.
			 *
			 * @since  1.0.0
			 * @access private
			 *
			 * @type     {string}
			 */
			mediaFrameAddBtnTxt: typeof smfi_translation_object !== 'undefined' ? smfi_translation_object.media_frame_add_btn_txt : 'Add image',
		},
		
		/**
		 * Helper in context of working with images.
		 *
		 * @since  1.0.0
		 * @access private
		 *
		 * @type     {Object}
		 */
		ImageHelper : {
			
			/**
			 * Compared the ID of the given image with the given id.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @param {jQuery} img  The image.
			 * @param {number} idToCompare  The id which should be compared with the id of the given image.
			 *
			 * @return {bool} True if ids are the same else false.
			 */
			hasImageId : function ( img, idToCompare ) {
				var imgWrapper = img.parent();
				var hiddenInputWithImgID = imgWrapper.find( '[name="smfi-img-ids[]"]' );
				
				var parsedImgId = parseInt( hiddenInputWithImgID.attr( 'value'), 10 );
				var parsedIdToCompare = parseInt( idToCompare, 10 );
				
				if( Number.isNaN( parsedImgId ) || Number.isNaN( parsedIdToCompare ) ) {
					return false;
				}
				return parsedImgId === parsedIdToCompare;
			}
		},
		
		/**
		 * Handles the error handling.
		 *
		 * @since  1.0.0
		 * @access private
		 *
		 * @type     {Object}
		 */
		ErrorHandler : {

			/**
			 * Returns the default error message.
			 *
			 * It uses the received translated default error message from the server. If no error message was transmitted then a default one is used
			 * which is specified inside this function.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @return {string} Error message.
			 */
			getDefaultErrorMessageTxt : function() {
				if(typeof smfi_translation_object.default_error_message !== 'undefined') {
					return smfi_translation_object.default_error_message;
				} else {
					return 'An error occured. Please contact the responsible person for your website or the plugin developer.'
				}
			},
			
			/**
			 * Returns the error message for failed adding an image.
			 *
			 * It uses the received translated error message from the server. If no error message was transmitted then a default one is used
			 * which is specified inside this function.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @return {string} Error message.
			 */
			getAddImageErrorMessage : function() {
				if(typeof smfi_translation_object.add_image_error_message !== 'undefined') {
					return smfi_translation_object.add_image_error_message;
				} else {
					return getDefaultErrorMessageTxt();
				}
			},
			
			/**
			 * Returns the error message for failed changing an image.
			 *
			 * It uses the received translated error message from the server. If no error message was transmitted then a default one is used
			 * which is specified inside this function.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @return {string} Error message.
			 */
			getChangeImageErrorMessage : function() {
				if(typeof smfi_translation_object.change_image_error_message !== 'undefined') {
					return smfi_translation_object.change_image_error_message;
				} else {
					return getDefaultErrorMessageTxt();
				}
			},
			
			/**
			 * Shows the given error message inside the given error container.
			 *
			 * It uses the received translated error message from the server. If no error message was transmitted then a default one is used
			 * which is specified inside this function. The error message can be removed by a click on a close button.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @listens click
			 *
			 * @param {jQuery} errorContainer  The error container which should contain possible the error messages.
			 * @param {number} errorMessageTxt  The error message.
			 */
			showError : function( errorContainer, errorMessageTxt ) {
				
				// Create new error.
				var newError = jQuery( document.createElement( 'div' ) );
				newError.attr( 'class', 'notice notice-error is-dismissible' );
				errorContainer.append( newError );
				
				// Create error message and append it to the error.
				var errorMessage = jQuery( document.createElement( 'p' ) );
				if(errorMessageTxt !== undefined) {
					errorMessage.text( errorMessageTxt );
				} else {
					errorMessage.text( this.getDefaultErrorMessageTxt() );
				}
				
				newError.append( errorMessage );
				
				// Create close button which allows to remove the error message.
				var closeBtn = jQuery( document.createElement( 'button' ) );
				closeBtn.attr( 'class', 'notice-dismiss' );
				newError.append( closeBtn );
				
				// Remove error message if close button was clicked.
				closeBtn.click( function( event ) {
					event.preventDefault();
					newError.fadeTo( 100, 0, function() {
						newError.slideUp( 100, function() {
							newError.remove();
						} );
					} );
				} );
				
				// Show error in the error container.
				errorContainer.append( newError );
			}
		},
		
		/**
		 * Handles interaction with the media frame.
		 *
		 * @since  1.0.0
		 * @access private
		 *
		 * @type     {Object}
		 */
		MediaFrameManager : {
							
			/**
			 * Creates and returns a new media frame which allows to select images.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @return {wp.media.view.MediaFrame.Select} The media frame.
			 */
			getNewMediaFrame : function() {
			
				// Accepts an optional object hash to override default values.
				var mediaFrame = new wp.media.view.MediaFrame.Select( {
					
					// Modal title.
					title: de.smfi.UIText.mediaFrameTitle,

					// Enable/disable multiple select.
					multiple: false,

					// Library WordPress query arguments.
					library: {
						order: 'ASC',

						/*
						 * [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo',
						 * 'id', 'post__in', 'menuOrder' ]
						 */
						orderby: 'title',

						// mime type. e.g. 'image', 'image/jpeg'.
						type: 'image',

						// Searches the attachment title.
						search: null,

						// Attached to a specific post (ID).
						uploadedTo: null
					},

					button: {
						text: de.smfi.UIText.mediaFrameAddBtnTxt
					}
				} );
				return mediaFrame;
			},
			
			/**
			 * Adds a new image by the specified image ID.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
		     * @param {number} newImgID  The image ID of the new image.
		     * @param {jQuery} imgContainer  The image container which contains all images.
		     * @param {jQuery} errorContainer  The error container which can be used for reporting ocurred errors to the user.
			 */
			addNewImg : function( newImgID, imgContainer, errorContainer) {
				
				// Setup data for ajax request.
				var requestData = {
					
					// Specify which ajax callback should be triggered on server side.
					'action': 'get_img_wrapper_html_by_ajax_as_json',
					
					// Transmit the received nonce back to server.
					'smfi_add_img_security': ajax_smfi_object.ajax_add_image_nonce,
					
					// ID of new image.
					'smfi_img_id': newImgID
				};
				
				// Make an ajax request and get the html of the new image.
				mediaFrameManager = this;
				jQuery.ajax({
					url: ajax_smfi_object.ajax_url,
					dataType: 'json',
					type:'POST',
					data: requestData,
					
					success: function( response ){
						
						if( response.hasOwnProperty( 'newImgHtml' ) ) {

							// Add new image wrapper into the DOM
							var newImgHtml = jQuery( response.newImgHtml ).prependTo( imgContainer );
							
							// Invoke all registered listener and let them do their stuff with the new image.
							for( var i = 0; i < de.smfi.ImageAddedListener.length; i++ ) {
								if( typeof( de.smfi.ImageAddedListener[i].doAfterImageAddedAtBegin) == 'function' ) {
									de.smfi.ImageAddedListener[i].doAfterImageAddedAtBegin( newImgHtml );
								};
							}
							
							/*
							 * Open media frame as soon as the new image was clicked.
							 * This allows the user to exchange the image by a new one from the media library.
							*/
							var newImg = newImgHtml.find( '.smfi-img' );
							mediaFrameManager.openMediaFrameOnImgClick( newImg, errorContainer );
							
							// Remove the new image as soon as its remove button was clicked.
							mediaFrameManager.removeImgOnBtnClick( newImg.siblings( '.smfi-remove-img-btn' ) );
						} else {
							
							var hasErrorMessage = response.hasOwnProperty( 'data' ) && response.data.hasOwnProperty( 'smfiErrorMessage' );
							if( hasErrorMessage ) {
								// Show received error message.
								de.smfi.ErrorHandler.showError(errorContainer, response.data.smfiErrorMessage);
							} else {
								// Show default error message.
								de.smfi.ErrorHandler.showError(errorContainer, de.smfi.ErrorHandler.getAddImageErrorMessage());
							}
						}
					},
					error: function(response){
						// Show default error message.
						de.smfi.ErrorHandler.showError(errorContainer);
					}
				});
			},
			
			/**
			 * Updates an existing image with the new selected image by the user.
			 *
			 * The user select the image via media frame. For this an ajax request is done. 
			 * If the user selects the same image then no update will be executed.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @param {number} newImgID  The image ID of the new image.
			 * @param {jQuery} oldImg  The old image.
			 * @param {jQuery} errorContainer  The error container which should contain possible the error messages.
			 */
			updateImg : function( newImgID, oldImg, errorContainer) {
				
				// Setup data for ajax request.
				var requestData = {
					
					// Specify which ajax callback should be triggered on server side.
					'action': 'get_img_html_by_ajax_as_json',
					
					// Transmit the received nonce back to server.
					'smfi_change_img_security': ajax_smfi_object.ajax_change_image_nonce,
					
					// ID of new image.
					'smfi_img_id': newImgID
				};
				
				// Make an ajax request and get the html of the new image.
				mediaFrameManager = this;
				jQuery.ajax({
					url: ajax_smfi_object.ajax_url,
					dataType: 'json',
					type:'POST',
					data: requestData,
					
					success: function(response){
						
						if( response.hasOwnProperty( 'newImgHtml' ) ) {
							
							// Update the image ID in the hidden input.
							var imgWrapper = oldImg.parent();
							var hiddenInputWithImgID = imgWrapper.find( '[name="smfi-img-ids[]"]' );
							hiddenInputWithImgID.attr( 'value', newImgID );
						
							// Update image html.
							oldImg.replaceWith(response.newImgHtml);

							/*
							 * Open media frame as soon as the new image was clicked.
							 * This allows the user to exchange the image by a new one from the media library.
							*/
							var img = imgWrapper.find( '.smfi-img' );
							mediaFrameManager.openMediaFrameOnImgClick( img, errorContainer );
							
						} else {
							
							var hasErrorMessage = response.hasOwnProperty( 'data' ) && response.data.hasOwnProperty( 'smfiErrorMessage' );
							if( hasErrorMessage ) {
								// Show received error message.
								de.smfi.ErrorHandler.showError(errorContainer, response.data.smfiErrorMessage);
							} else {
								// Show default error message.
								de.smfi.ErrorHandler.showError(errorContainer, de.smfi.ErrorHandler.getChangeImageErrorMessage());
							}
						}
					},
					error: function(response){
						// Show default error message.
						de.smfi.ErrorHandler.showError(errorContainer);
					}
				});
			},
							
			/**
			 * Adds a listener which opens the media frame on clicking the add new image button.
			 *
			 * The user can selects an image via the opened media frame. This image will be added as new feauterd image to the metabox.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @listens click
			 * @listens select
			 *
			 * @param {jQuery} btn  The button.
			 * @param {jQuery} imgContainer  The image container.
			 * @param {jQuery} errorContainer  The error container which should contain possible the error messages.
			 */
			openMediaFrameOnAddBtnClick : function( btn, imgContainer, errorContainer ) {
				
				// Create new media frame
				var mediaFrame = this.getNewMediaFrame();
				
				// Open the media frame if button was clicked.
				btn.click( function( event ) {
					event.preventDefault();
					mediaFrame.open();
				} );
				
				// Add new image if something was selected by the user.
				mediaFrameManager = this;
				mediaFrame.on( 'select', function() {

					// Get selected image
					var selectedAttachment = mediaFrame.state().get( 'selection' ).first().toJSON();
					
					if( typeof selectedAttachment.id !== 'undefined' ) {
						// Create new image wrapper which shows the new image.
						mediaFrameManager.addNewImg( selectedAttachment.id, imgContainer, errorContainer );
					} else {
						// Show error with default error message.
						de.smfi.ErrorHandler.showError( errorContainer, de.smfi.ErrorHandler.getAddImageErrorMessage() );
					}
				} );
				
			},
			
			/**
			 * Adds a listener which opens the media frame on clicking the given image.
			 *
			 * The user can selects an image via the opened media frame. The old image will be exchanged by the new one.
			 * If the media frame is opened the current image will be preselected. If the preselection failed then an error
			 * will be logged.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @listens click
			 * @listens select
			 * @listens open
			 *
			 * @param {jQuery} img  The image.
			 * @param {jQuery} errorContainer  The error container which should contain possible the error messages.
			 */
			openMediaFrameOnImgClick : function( img, errorContainer ) {
				
				// Create new media frame
				var mediaFrame = this.getNewMediaFrame();
				
				// Open the media frame if image was clicked.
				img.click( function( event ) {
					event.preventDefault();
					mediaFrame.open();
				} );
				
				// Update the image if user select a new image via media frame.
				mediaFrameManager = this;
				mediaFrame.on( 'select', function() {
					
					// Get id of the new selected image
					var selectedAttachment = mediaFrame.state().get( 'selection' ).first().toJSON();
					var newImgID = selectedAttachment.id;
					
					if(typeof newImgID !== 'undefined') {
						
						// If the selected image is the same as the current image then no update is necessary.
						if(de.smfi.ImageHelper.hasImageId(img, newImgID)) {
							return;
						}
						
						// Update existing image by the new selected image.
						mediaFrameManager.updateImg( newImgID, img, errorContainer );
					} else {
						// Show error with default error message.
						de.smfi.ErrorHandler.showError(errorContainer);
					}
				} );
				
				// Preselect the clicked image inside the opened media frame.
				mediaFrame.on( 'open',function() {
					
					// Get image ID from hidden input
					var imgWrapper = img.parent();
					var hiddenInputWithImgID = imgWrapper.find( '[name="smfi-img-ids[]"]' );
					var imgID = parseInt( hiddenInputWithImgID.attr( 'value' ) , 10);
					
					if( Number.isInteger( imgID ) && imgID > 0 ) {
						
						/* 
						 *	Get/Create media frame attachment by using the image ID.
						 *	The attachment can be used to set the selection inside the opened media frame properly.
						 */
						attachment = wp.media.attachment(imgID);
						attachment.fetch();
						
						// Get current media frame selection and add the image attachment to it.
						if( attachment !== 'undefined') {
							var selection = mediaFrame.state().get( 'selection' );
							selection.add( attachment );
							return;
						}
					}
					
					/*
					 * If preselection failed then just log the error.
					 * I think it does not necessary to show this error to the user because it is not critical enough.
					 * The user can continue working without a preselection.
					 */						
					console.log('SMFI Plugin: Could not preselect image in media frame.');
				} );
			},
			
			/**
			 * Adds a click listener to the given button which removes the image it belongs to.
			 *
			 * @since      1.0.0
			 * @access     private
			 *
			 * @listens click
			 *
			 * @param {jQuery} removeBtn  The remove button.
			 */
			removeImgOnBtnClick : function( removeBtn ) {
				removeBtn.click( function( event ) {
					
					event.preventDefault();
					
					// Remove the entire image container which is the parent of the remove button.
					var removedImageWrapper = jQuery( this ).parent().remove();
					
					// Invoke all registered listener and let them do their stuff in context of the removed image.
					for( var i = 0; i < de.smfi.ImageRemovedListener.length; i++ ) {
						if( typeof( de.smfi.ImageRemovedListener[i].doAfterImageRemoved) == 'function' ) {
							de.smfi.ImageRemovedListener[i].doAfterImageRemoved( removedImageWrapper );
						};
					}
				} );
			},
		}
	}
};