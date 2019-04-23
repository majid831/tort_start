(function() {
    tinymce.create('tinymce.plugins.smfiShortcodesButtons', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
			
			// Add shortcode button for inserting the default gallery.
			ed.addButton('smfiDefaultGalleryBtn', {
				title : de.smfi.shortcodes.UIText.insertDefaultGalleryBtnTooltip,
				cmd : 'smfiDefaultGalleryBtn',
				text : 'SMFI Gallery'
			});
			
			// Add default gallery shortcode on button click by using the command API.
			ed.addCommand('smfiDefaultGalleryBtn', function() {
				shortcode = '[smfi-insert-default-gallery]';
				ed.execCommand('mceInsertContent', 0, shortcode);
            });
			
			// Add shortcode button for inserting the default slider.
			ed.addButton('smfiDefaultSliderBtn', {
				title : de.smfi.shortcodes.UIText.insertDefaultSliderBtnTooltip,
				cmd : 'smfiDefaultSliderBtn',
				text : 'SMFI Slider'
			});
			
			// Add slider shortcode on button click by using the command API.
			ed.addCommand('smfiDefaultSliderBtn', function() {
				shortcode = '[smfi-insert-default-slider]';
				ed.execCommand('mceInsertContent', 0, shortcode);
            });
        },
 
        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },
 
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'SMFI Shortcodes Buttons',
                author : 'Roman Bauer',
                authorurl : 'https://www.roman-bauer-web.de',
                infourl : 'https://www.roman-bauer-web.de/wordpress-plugin-smfi',
                version : '1.0.0'
            };
        }
    });
 
    // Register plugin
    tinymce.PluginManager.add( 'smfiShortcodesButtonsPlugin', tinymce.plugins.smfiShortcodesButtons );
})();