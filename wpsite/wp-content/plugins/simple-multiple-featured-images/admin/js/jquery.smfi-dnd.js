/**
 * Allows to drag and drop featured images of the SMFI plugin.
 *
 * @author Roman Bauer.
 * @since  1.0.2
 */
jQuery( 'document' ).ready( function( jQuery ){

	// Search smfi metabox,
	var metaBox = jQuery( '#smfi-metabox.postbox' );
	if( metaBox.length ) {
		// Setup dnd manager with the found metabox.
		de.smfi.dnd.SmfiDndManager._metaBox = metaBox;

		/*
		* Wait until the metabox is rendered and then initialize drag and drop.
		* This is necessary because we need to know the metabox width for drag and drop initialisation.
		* In Wordpress 5.0 the gutenberg editor was introduced.
		* Gutenberg changes many things, especially how metaboxes are rendered.
		* Because of gutenberg we have explicit to wait until the metabox is rendered.
		*/
		var waitUntilMetaboxRendered = setInterval( function() {
		  if ( de.smfi.dnd.SmfiDndManager.isMetaboxRendered() ) {
				de.smfi.dnd.SmfiDndManager.initializeDND( metaBox );
				clearInterval( waitUntilMetaboxRendered );
		  }
		}, 100 );
	}
} );

de.smfi.dnd = {
	/**
	 * Handles the drag and drop of the featured images.
	 *
	 * @since  1.0.2
	 * @access private
	 *
	 * @type     {Object}
	 */
	SmfiDndManager : {

		/**
		 * The metabox element.
		 *
		 * @since  1.0.5
		 * @access private
		 *
		 * @type     {jQuery}
		 */
		_metaBox : null,

		/**
		 * The container element which holds all images.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {jQuery}
		 */
		_imageContainer : null,

		/**
		 * The storage which holds dimension and position data for each image slot.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {Array}
		 */
		_imageSlotsStorage : [],

		/**
		 * The storage which contains the images in the current order.
		 * The order changes if the user drag and drop the images.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {Array}
		 */
		_imagesOrderStorage : [],

		/**
		 * The width of a showed image.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {number}
		 */
		_imageAspectWidth : 150,

		/**
		 * The height of a showed image.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {number}
		 */
		_imageAspectHeight : 150,

		/**
		 * The current selected/dragged image.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {jQuery}
		 */
		_selectedImage : null,

		/**
		 * The original image slot of the current dragged image.
		 *
		 * @since  1.0.2
		 * @access private
		 *
		 * @type     {jQuery}
		 */
		_originalImgSlot: null,

		/**
		 * Initializes the entire drag and drop support for the SMFI plugin.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} metaBox The metabox which contains all images.
		 */
		initializeDND : function( metaBox ) {

			// Store image container globaly.
			this._imageContainer = metaBox.find( '.smfi-img-container' );

			// Get the image wrapper and the corresponding slots.
			var imgWrapper = this._imageContainer.find( '.smfi-img-wrapper' ),
					imgSlots = this.createInitialImageSlots( this._imageContainer, imgWrapper.length );

			// Initialize image slots.
			this.initImgSlots( this._imageContainer.find( '.smfi-img-slot' ) );

			// Initialize image wrapper.
			this.initImgWrapper( imgWrapper );

			// Initialize the dnd support for the new image wrapper.
			this.initImgDNDListener( imgWrapper );
			/*
			* Because the firefox does not save the mouse coordinates on the drag event
			* we have to use the dragover event on the entire document as workaround for
			* handling a drag of an image wrapper.
			*/
			jQuery( document ).on( 'dragover', { dndManager : this }, this.dragImg );

			/*
			* Add itself as a new image listener to the SMFI plugin and listen
			* to new added images. If a new image was added the listener will
			*  update the image wrapper and their slots as necessary.
			*/
			de.smfi.ImageAddedListener.push( this );

			/*
			* Add itself as image removed listener to the SMFI plugin and listen
			* to removed images. If an image was removed the listener will update
			* the image wrapper and their slots as necessary.
			*/
			de.smfi.ImageRemovedListener.push( this );

			// Add listener which updates the image dimensions and positions on window resize.
			jQuery( window ).on( 'resize', { dndManager : this }, this.updateImgBoundsAndPositionsOnEvent );

			// Add listener which updates the image dimensions and positions if the user changes the wordpress layout.
			jQuery( '.columns-prefs' ).on( 'click', { dndManager : this }, this.updateImgBoundsAndPositionsOnEvent );

			// Add listener which updates the image dimensions and positions if the user opens the metabox.
			metaBox.children( '.hndle' ).on( 'click', { dndManager : this }, this.updateImgBoundsAndPositionsOnEvent  );
		},

		/**
		 * Creates the initial image slots.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} imageContainer The image container which holds all images.
		 * @param {jQuery} numberOfSlots The number of slots to create.
		 */
		createInitialImageSlots : function( imageContainer, numberOfSlots ) {

			// Build the HTML with all image slots in this variable.
			var imageSlotsHtml = '';
			for( var i = 0; i < numberOfSlots; i++ ) {
				imageSlotsHtml += this.getImageSlotHTML();
			}

			// Append all image slots into the image container.
			imageContainer.append( imageSlotsHtml );
		},

		/**
		 * Gets HTML for an image slot.
		 *
		 * @since      1.0.2
		 * @access     private
		 * @return {string} The HTML.
		 */
		getImageSlotHTML : function() {
			return '<div class="smfi-img-slot"><div class="smfi-img-slot-num"></div></div>';
		},

		/**
		 * Initializes image slots.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} imgSlots The image slots.
		 */
		initImgSlots : function( imgSlots ) {
			if( imgSlots.length ) {

				// Empty image slot bounds storage.
				this._imageSlotsStorage = [];

				// Calculate number of images per row.
				var imagesPerRow = this.calculateImagesPerRow();

				// Calculate margin for each image slot.
				var imgSlotMarginBottom = this.calculateImgSlotMargin();

				// Set width and height for each slot.
				var _imageAspectWidth = this._imageAspectWidth;
					_imageAspectHeight = this._imageAspectHeight;
				imgSlots.each( function( index ) {
					jQuery( this ).css( 'width', ( 100 / imagesPerRow ) + '%' );
					jQuery( this ).css( 'padding-bottom', ( ( 100 / imagesPerRow ) * ( _imageAspectWidth / _imageAspectHeight ) ) + '%' );
					jQuery( this ).css( 'margin-bottom', imgSlotMarginBottom );
				});

				/*
				* Now get the width and height. Just use the first slot because all slots have the same dimensions.
				*/
				var firstSlot = imgSlots.first(),
				bounds = firstSlot.get( 0 ).getBoundingClientRect(),
				imgSlotWidth = bounds.width,
				imgSlotHeight = bounds.height;

				// Store dimensions and positions data for each slot globaly.
				for( var i = 0; i < imgSlots.length; i++ ) {

					// Calculate the x and y position.
					xPosition = ( i % imagesPerRow ) * imgSlotHeight;
					yPosition = Math.floor( i / imagesPerRow ) * ( imgSlotHeight + imgSlotMarginBottom );

					// Save dimension and position data for this image slot.
					this._imageSlotsStorage[i] = { width: imgSlotWidth, height: imgSlotHeight, x: xPosition, y: yPosition }

					// Set the number inside the slot.
					imgSlots.eq( i ).find( '.smfi-img-slot-num' ).text( i + 1 );
				}
			}
		},

		/**
		 * Initializes image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} allImgWrapper The image wrapper.
		 */
		initImgWrapper : function( allImgWrapper ) {

			if( allImgWrapper.length ) {
				// Iterate over the image wrapper and position them correctly according to their slots.
				for( var i = 0; i < allImgWrapper.length; i++ ) {

					// Get the bounds of the corresponding slot of this image wrapper.
					var imgSlotBounds = this._imageSlotsStorage[i],
						imgSlotWidth = imgSlotBounds.width,
						imgSlotHeight = imgSlotBounds.height,
						imgSlotX = imgSlotBounds.x,
						imgSlotY = imgSlotBounds.y;

					var currentImgWrapper = allImgWrapper.eq( i );

					// Setup initial bounds of image wrapper.
					this.initImgWrapperBounds( currentImgWrapper,  imgSlotWidth,  imgSlotHeight );

					// Set end position of the image wrapper according to its slot.
					currentImgWrapper.css( 'transform', 'translate3d(' + imgSlotX + 'px,' + imgSlotY + 'px, 0' + ')' );

					// Setup the move animation which will executed if the image change its position.
					this.initMoveAnimation( currentImgWrapper );

					/*
					* Store initial order of the images.
					* For this, the IDs of the images are stored in the order of the initial occurrence of the images.
					*/
					this._imagesOrderStorage.push( currentImgWrapper );

					/*
					* Create a hidden input which will store the current position of the image.
					* This input is necessary because it will transmit the image position to
					* the server if the post is saved.
					*/
					this.attachHiddenInput( currentImgWrapper, i );

					/*
					* The HTML5 DND do not support touch devices. Additionally there is a conflict between the
					* native scroll gesture and the drag and drop operation that we need for moving the images.
					* For this we attach an extra icon which is primary intended for touch devices.
					* On touch devices the images can be dragged by using this icon.
					*/
					this.attachMoveIcon( currentImgWrapper );
				}
			}
		},

		/**
		 * Initializes bounds for the given image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} imgWrapper The image wrapper.
		 * @param {number} imgWrapper The width.
		 * @param {number} imgWrapper The height.
		 */
		initImgWrapperBounds : function( imgWrapper, width, height ) {

			// Set initial position.
			imgWrapper.css( 'position', 'absolute' );
			imgWrapper.css( 'top', 0 );
			imgWrapper.css( 'left', 0 );

			// Set width and height of the image wrapper according to its slot.
			imgWrapper.css( 'width', width + 'px');
			imgWrapper.css( 'height', height + 'px' );
		},

		/**
		 * Initializes move animation for the given image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} imgWrapper The image wrapper.
		 */
		initMoveAnimation : function( imgWrapper ) {
			// Add transition which will animate the movement of the images.
			imgWrapper.css( 'transition', 'all 0.3s ease' );
		},

		/**
		 * Attachs a hidden input to the image wrapper.
		 * The input have to store the current position of the image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} imgWrapper The image wrapper.
		 * @param {number} position The image wrapper position.
		 */
		attachHiddenInput : function( imgWrapper, position ) {

			var hiddenPositionInput = jQuery( document.createElement( 'input' ) );
			hiddenPositionInput.attr( 'name', 'smfi-img-pos[]' );
			hiddenPositionInput.attr( 'type', 'hidden' );
			hiddenPositionInput.attr( 'value', position );

			// Attach it to the wrapper.
			hiddenPositionInput.appendTo( imgWrapper );
		},

		/**
		 * Attachs a move icon to the image wrapper..
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} imgWrapper The image wrapper.
		 */
		attachMoveIcon : function( imgWrapper ) {

			// Create move icon.
			var moveIcon = jQuery( document.createElement( 'div' ) );
			moveIcon.attr( 'class', 'smfi-move-icon' );

			// Attach move icon to the wrapper.
			moveIcon.appendTo( imgWrapper );
		},

		/**
		 * Initializes drag and drop listener for the given image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} allImgWrapper The image wrapper.
		 */
		initImgDNDListener : function( allImgWrapper ) {

			// Setup drag and drop operations for mouse devices.
			allImgWrapper.attr( 'draggable', 'true' );
			allImgWrapper.on( 'dragstart', { dndManager : this }, this.startImgDrag );
			allImgWrapper.on( 'drop', { dndManager : this }, this.dropImg );
			allImgWrapper.on( 'dragend', { dndManager : this }, this.endImgDrag );

			// Setup drag and drop operations for touch devices.
			allImgWrapper.find( '.smfi-move-icon' ).on( 'touchstart', { dndManager : this }, this.startImgDragByTouch );
			allImgWrapper.find( '.smfi-move-icon' ).on( 'touchmove', { dndManager : this }, this.dragImgByTouch );
			allImgWrapper.find( '.smfi-move-icon' ).on( 'touchend', { dndManager : this }, this.endImgDrag );
		},

		/**
		 * Prepares the image wrapper for dragging.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		startImgDrag: function( event ) {
			dndManager = event.data.dndManager;
			if ( ! dndManager._selectedImage ) {

				var currentTarget = jQuery( event.currentTarget );

				dndManager._selectedImage = currentTarget;
				dndManager._originalImgSlot = dndManager.getPositionOfImage( dndManager._selectedImage );
				dndManager._selectedImage.addClass( 'smfi-img-dragged' );

				/*
				* Set drag image by ourself because not all browser handle it properly.
				* IE does not support setDragImage but the default behaviour of the IE is ok.
				* So for IE we have not any special handling.
				*/
				if( event.originalEvent.dataTransfer !== undefined && event.originalEvent.dataTransfer.setDragImage !== undefined ) {

					// Set ghost image explicit because the firefox can not position the ghost image properly by default.
					event.originalEvent.dataTransfer.setDragImage( currentTarget.get( 0 ), event.offsetX, event.offsetY );

					/*
					* In HTML, apart from the default behavior for images, links, and selections, no other elements are draggable by default.
					* All XUL elements are also draggable. In order to make another HTML element draggable, two things must be done:
					* Set the draggable attribute to true on the element that you wish to make draggable.
					* Add a listener for the dragstart event and set the drag data within this listener.
					*
					* This seems to be necessary for the firefox.
					*/
					event.originalEvent.dataTransfer.setData('text/plain', 'dummy');

					// Use the move cursor during the a drag operation.
					event.originalEvent.dataTransfer.effectAllowed = 'move';
				}
			}
		},

		/**
		 * Performs the drag of an image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		dragImg: function( event ) {
			dndManager = event.data.dndManager;
			if ( dndManager._selectedImage ) {

				/*
				* Stop changing the cursor into no-allowed cursor. This is happening because the browser assumes we have an invalid drop target.
				* Calling the preventDefault() method will indicate that a drop is allowed at that location.
				*/
				event.preventDefault();

				/*
				* Get top and left position of the image container relative to the entire document.
				*/
				var imgContainerBounds = dndManager._imageContainer.get( 0 ).getBoundingClientRect(),
					imgContainerLeft = imgContainerBounds.left + window.pageXOffset,
					imgContainerTop = imgContainerBounds.top + window.pageYOffset;

				/*
				* Get current cursor position relative to the entire document.
				*/
				var cursorX = event.pageX,
					cursorY = event.pageY;

				/*
				* Calculate current cursor position relative to the image container.
				*/
				var localCursorX = cursorX - imgContainerLeft,
					localCursorY = cursorY - imgContainerTop;

				// Get the currently hovered image slot and update the image positions if necessary.
				var hoveredSlot = dndManager.getImgSlotIdByCoords( { x : localCursorX, y : localCursorY } );
				if( hoveredSlot != undefined) {
					dndManager.updateImgPositionsByHoveredSlot( hoveredSlot );
				}
			}
		},

		/**
		 * Prepares the image wrapper for dragging on mobile devices.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		startImgDragByTouch: function( event ) {
			dndManager = event.data.dndManager;
			if ( ! dndManager._selectedImage ) {
				dndManager._selectedImage = jQuery( event.currentTarget ).parent();
				dndManager._originalImgSlot = dndManager.getPositionOfImage( dndManager._selectedImage );
				dndManager._selectedImage.addClass( 'smfi-img-dragged' );

				// Setting up ghost image.
				var ghostImg = jQuery( '#smfi-dnd-touch-ghost-img' );
				if ( ghostImg == null ) {
					ghostImg = document.createElement('div');
					document.getElementById('#smfi-content-container').appendChild( ghostImg );
					ghostImg.setAttribute('id','smfi-dnd-touch-ghost-img');
					ghostImg.css( 'position', 'absolute' );
					ghostImg.css( 'z-index', '999' );
				}

				var xPos = event.originalEvent.touches[0].pageX;
				var yPos = event.originalEvent.touches[0].pageY;

				// for the purpose of this article ghost image will be a square of
				// the same color of touched object
				jQuery('#smfi-dnd-touch-ghost-img').css('visibility','visible');
				var ghostImg = jQuery( this ).css('background-image');
				jQuery('#smfi-dnd-touch-ghost-img').css('background-image',ghostImg);
				jQuery('#smfi-dnd-touch-ghost-img').css('background-size','100% 100%');
				jQuery('#smfi-dnd-touch-ghost-img').css('left',xPos+'px');
				jQuery('#smfi-dnd-touch-ghost-img').css('top',yPos+'px');
				jQuery('#smfi-dnd-touch-ghost-img').css('width','20px');
				jQuery('#smfi-dnd-touch-ghost-img').css('height','20px');
			}
		},

		/**
		 * Performs the drag of an image wrapper on mobile devices.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		dragImgByTouch: function( event ) {
			dndManager = event.data.dndManager;
			if ( dndManager._selectedImage ) {

				// Prevent default scrolling.
				event.preventDefault();

				/*
				* Get top and left position of the image container relative to the entire document.
				*/
				var imgContainerBounds = dndManager._imageContainer.get( 0 ).getBoundingClientRect(),
					imgContainerLeft = imgContainerBounds.left + window.pageXOffset,
					imgContainerTop = imgContainerBounds.top + window.pageYOffset;

				/*
				* Get current touch position relative to the entire document.
				*/
				var touchedElem = event.originalEvent.changedTouches[0];
				var touchPosX = touchedElem.pageX,
					touchPosY = touchedElem.pageY;

				/*
				* Calculate current cursor position relative to the image container.
				*/
				var localTouchPosX = touchPosX - imgContainerLeft,
					localTouchPosY = touchPosY - imgContainerTop;


				// Get the currently hovered image slot and update the image positions if necessary.
				var hoveredSlot = dndManager.getImgSlotIdByCoords( { x : localTouchPosX, y : localTouchPosY } );
				if( hoveredSlot != undefined) {
					dndManager.updateImgPositionsByHoveredSlot( hoveredSlot );
				}

				 // Move the ghost image.
				var sw = parseFloat($('#smfi-dnd-touch-ghost-img').width());
				var sh = parseFloat($('#smfi-dnd-touch-ghost-img').height());
				jQuery('#smfi-dnd-touch-ghost-img').css('left',(touchPosX-sw/2)+'px');
				jQuery('#smfi-dnd-touch-ghost-img').css('top',(touchPosY-sh/2)+'px');

				// We have to scroll ourself if necessary because we deactivated the native scrolling.
				var viewPortHeight = Math.max( document.documentElement.clientHeight, window.innerHeight || 0 );
				if( viewPortHeight - touchedElem.clientY < 200 ) {
					// Scroll down if the bottom is reached.
					window.scrollBy({
						top: 10,
						left: 0
					});
				} else if( touchedElem.clientY < 200 ) {
					// Scroll up if the top is reached.
					window.scrollBy({
						top: -10,
						left: 0
					});
				}
			}
		},

		/**
		 * Updates the image positions depending on the currently hovered image slot.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		updateImgPositionsByHoveredSlot: function( hoveredSlot ) {
			/*
			* If the currently hovered image slot belongs to the current dragged image
			* then we have nothing to do.
			*/
			var currentImgPosition = this.getPositionOfImage( this._selectedImage );
			if( currentImgPosition !== hoveredSlot ) {

				/*
				* Update image position in the order storage. Use the new hovered slot as the position for the dragged image.
				*/
				this._imagesOrderStorage.splice( hoveredSlot, 0, this._imagesOrderStorage.splice( currentImgPosition, 1 )[0] );

				// Update all images positions.
				this.updateImagePositions();
			}
		},

		/**
		 * Completes the drag of an image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		endImgDrag: function( event ) {
			dndManager = event.data.dndManager;
			event.preventDefault();
			if ( dndManager ) {

				// Remove dragged class fromm the dragged image.
				if( dndManager._selectedImage !== null ) {
					dndManager._selectedImage.removeClass( 'smfi-img-dragged' );
				};

				// Clean up all used globals.
				dndManager._selectedImage = null;
				dndManager._originalImgSlot = null;

				// Update image positions last time.
				dndManager.updateImagePositions();
			}
		},

		/**
		 * Completes the drop of an image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		dropImg: function( event ) {
			/*
			* In Firefox, the default behavior for a drop event is to navigate to the URL that was dropped on the drop target.
			* That means dropping an image onto the drop target will result in the page navigating to the image file.
			* We do not want this behaviour, so we prevent it explicit.
			*/
			event.preventDefault();
		},

		/**
		 * Updates the image wrapper positions.
		 *
		 * @since      1.0.2
		 * @access     private
		 */
		updateImagePositions : function () {
			// Iterate over the image slots and update their image positions properly.
			for( var i = 0; i < this._imagesOrderStorage.length; i++ ) {

				// Get the image.
				var image = this._imagesOrderStorage[i];

				// Get its corresponding image slot.
				var imgSlot = this._imageSlotsStorage[i];

				// Update the image position by using the position data of its image slot.
				image.css( 'transform', 'translate3d(' + imgSlot.x + 'px, ' + imgSlot.y + 'px, 0)' );

				/*
				* Update the image position inside the hidden input which is used to transmit
				* the image positions to the server if the post is saved.
				*/
				image.find( 'input[name^=smfi-img-pos]' ).val( i );
			}
		},

		/**
		 * Updates the image dimensions and positions depending on given event.
		 *
		 * @since      1.0.5
		 * @access     private
		 *
		 * @param {Object} event The event.
		 */
		updateImgBoundsAndPositionsOnEvent: function( event ) {
			dndManager = event.data.dndManager;
			if ( dndManager ) {
				var isMetaboxTitle = jQuery( event.currentTarget ).hasClass( 'hndle' );
						isMetaboxClosed = dndManager._metaBox.hasClass( 'closed' );
				if( event.type === 'click' && isMetaboxTitle && isMetaboxClosed ) {
					/*
					* Metabox is in the closed state and was clicked on the title.
					* Because of the click the metabox will be expanded by wordpress.
					* Wait until the metabox is expanded and the image container is rendered.
					* Then update the image bounds and positions.
					*/
					var waitUntilImgContainerRendered = setInterval( function() {
						if ( dndManager.isImgContainerRendered() ) {
							dndManager.updateImgBoundsAndPositions();
							clearInterval( waitUntilImgContainerRendered );
						}
					}, 100 );
				} else {
					// On all another events we currently just update the image positions and bounds.
					dndManager.updateImgBoundsAndPositions();
				}
			}
		},

		/**
		 * Updates the image dimensions and positions.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 */
		updateImgBoundsAndPositions: function() {
				// Reinitialize all image slots because the available width changed.
				var imgSlots = dndManager._imageContainer.find( '.smfi-img-slot' );
				if( imgSlots.length === 0 ) {
					// No image slots found --> we have nothing to update.
					return;
				}
				this.initImgSlots( imgSlots );

				// Get bounds for an image by using the first image slot.
				var imgSlotBounds = dndManager._imageSlotsStorage[0],
						imgSlotWidth = imgSlotBounds.width,
						imgSlotHeight = imgSlotBounds.height;

				// Update the bounds for each image.
				for( var i=0; i < dndManager._imagesOrderStorage.length; i++ ) {
					this.initImgWrapperBounds( dndManager._imagesOrderStorage[i],  imgSlotWidth,  imgSlotHeight );
				}

				// Update all images positions.
				this.updateImagePositions();
		},
		/**
		 * Calculates the number of images per row.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @return {number} The number of images per row.
		 */
		calculateImagesPerRow : function() {
			if( window.matchMedia( '(min-width: 992px)' ).matches ) {
				return 4;
			} else if( window.matchMedia( '(min-width: 768px)' ).matches ) {
				return 2;
			} else {
				return 1;
			}
		},

		/**
		 * Calculates bottom margin of an image slot.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @return {number} The bottom margin of an image slot.
		 */
		calculateImgSlotMargin : function() {

			// We want at least 30px;
			var margin = 30;

			// Now take into account the height of the remove button of an image wrapper.
			var removeImgBtn = this._imageContainer.find( '.smfi-img-wrapper .smfi-remove-img-btn' ).first();
			if( removeImgBtn.length ) {
				margin += removeImgBtn.outerHeight( true );
			}

			return margin;
		},

		/**
		 * Calculates the position of the given image wrapper.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} image The wrapper.
		 *
		 * @return {jQuery} The image wrapper.
		 */
		getPositionOfImage : function( image ) {
			for( var i = 0; i < this._imagesOrderStorage.length; i++ ) {
				if( this._imagesOrderStorage[i].get( 0 ) === image.get( 0 ) ){
					return i;
				}
			}
			return -1;
		},

		/**
		 * Calculates the currently hovered image slot by coords.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {Object} coords The coordinates.
		 *
		 * @return {jQuery} The hovered image slot.
		 */
		getImgSlotIdByCoords : function( coords ) {
			// Get the current slot being hovered over
			for( var i = 0; i < this._imageSlotsStorage.length; i++ ) {
				var slot = this._imageSlotsStorage[i];
				if ( slot.x <= coords.x && coords.x <= slot.x + slot.width && slot.y <= coords.y && coords.y <= slot.y + slot.height ) {
					return i;
				}
			}
		},

		/**
		 * Rearranges all images after a new image wrapper was added.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} newImgWrapper The new image wrapper.
		 */
		doAfterImageAddedAtBegin : function( newImgWrapper ) {

			if( newImgWrapper.length ) {
				// Add new image slot to the image container.
				this._imageContainer.append( this.getImageSlotHTML() );

				// Reinitialize all image slots because we have a new slot.
				this.initImgSlots( this._imageContainer.find( '.smfi-img-slot' ) );

				// Add the new image in the order storage at begin and setup it properly.
				this._imagesOrderStorage.unshift( newImgWrapper );

				// Setup initial bounds of image wrapper.
				this.initImgWrapperBounds( newImgWrapper, this._imageSlotsStorage[ 0 ].width, this._imageSlotsStorage[ 0 ].height );

				// Setup the move animation which will executed if the image change its position.
				this.initMoveAnimation( newImgWrapper );

				/*
				* Create a hidden input which will store the current position of the image.
				* This input is necessary because it will transmit the image position to
				* the server if the post is saved.
				*/
				this.attachHiddenInput( newImgWrapper, 0 );

				// Attach the move icon.
				this.attachMoveIcon( newImgWrapper );

				// Initialize the dnd support for the new image wrapper.
				this.initImgDNDListener( newImgWrapper );

				// As last step update all images positions.
				this.updateImagePositions();
			}
		},

		/**
		 * Rearranges all images after an image wrapper was removed.
		 *
		 * @since      1.0.2
		 * @access     private
		 *
		 * @param {jQuery} removedImgWrapper The removed image wrapper.
		 */
		doAfterImageRemoved : function( removedImgWrapper ) {

			if( removedImgWrapper.length ) {

				// Get the source DOM element.
				var sourceElem = removedImgWrapper.get( 0 );

				/*
				* Remove the corresponding image wrapper from the order storage.
				*/
				_imageSlotsStorage = this._imageSlotsStorage;
				this._imagesOrderStorage = jQuery.grep( this._imagesOrderStorage, function( imageWrapper, index ) {
					return imageWrapper.get( 0 ) !== sourceElem;
				});

				// Remove one image slot from the slot storage because one slot is unnecessary now.
				var imgSlots = this._imageContainer.find( '.smfi-img-slot' );
				imgSlots.first().remove();
				imgSlots = this._imageContainer.find( '.smfi-img-slot' );

				// Reinitialize all image slots because we deleted one slot.
				this.initImgSlots( imgSlots );

				// As last step update position of all images.
				this.updateImagePositions();
			}
		},

		/**
		 * Checks if the metabox was rendered by the browser.
		 *
		 * @since      1.0.5
		 * @access     private
		 *
		 */
		isMetaboxRendered : function() {
			if( this._metaBox ) {
				return this.isRenderedByWidthAndHeight( this._metaBox );
			}
			return false;
		},

		/**
		 * Checks if the image container inside the metabox was rendered by the browser.
		 *
		 * @since      1.0.5
		 * @access     private
		 *
		 */
		isImgContainerRendered : function() {
			if( this._imageContainer ) {
				return this.isRenderedByWidthAndHeight( this._imageContainer );
			}
			return false;
		},

		/**
		 * Checks if the given element was rendered by the browser.
		 * For this we just check if the element has some width and height.
		 *
		 * @since      1.0.5
		 * @access     private
		 *
		 */
		isRenderedByWidthAndHeight : function( element ) {
			if( element ) {
				var hasWidth = element.get( 0 ).getBoundingClientRect().width > 0;
						hasHeight = element.get( 0 ).getBoundingClientRect().height > 0;
				return hasWidth && hasHeight;
			}
		}
	}
}
