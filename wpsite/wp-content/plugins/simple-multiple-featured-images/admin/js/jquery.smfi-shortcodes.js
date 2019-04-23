de.smfi.shortcodes = {
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
		 * The tooltip text for the default gallery shortcode button.
		 *
		 * @since  1.0.0
		 * @access private
		 *
		 * @type     {string}
		 */
		insertDefaultGalleryBtnTooltip: typeof smfi_shortcode_translation_object !== 'undefined' ? smfi_shortcode_translation_object.insert_default_gallery_btn_tooltip : 'Insert SMFI gallery',
		
		/**
		 * The tooltip text for the default slider shortcode button.
		 *
		 * @since  1.0.0
		 * @access private
		 *
		 * @type     {string}
		 */
		insertDefaultSliderBtnTooltip: typeof smfi_shortcode_translation_object !== 'undefined' ? smfi_shortcode_translation_object.insert_default_slider_btn_tooltip : 'Insert SMFI slider',
	}
}
