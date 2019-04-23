<?php
/**
 * SMFI_Shortcodes class
 *
 * This file specifies the SMFI_Shortcodes class which adds some shortcodes.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_Shortcodes' ) ) {
	
	/**
	 * Adds shortcodes support to the simple multiple featured images plugin. 
	 *
	 * Allows to visualize the featured images via shortcodes.
	 *
	 * @since 1.0.0
	 */
	class SMFI_Shortcodes {
		
		/**
		 * The default gallery shortcode.
		 *
		 * @since 1.0.0
		 * @var string 
		 */
		private $shortcode_insert_default_gallery = 'smfi-insert-default-gallery';
		
		/**
		 * The default slider shortcode.
		 *
		 * @since 1.0.0
		 * @var string 
		 */
		private $shortcode_insert_default_slider = 'smfi-insert-default-slider';
		
		/**
		 * The smfi plugin.
		 *
		 * @since 1.0.0
		 * @var string 
		 */
		private $smfi_plugin;
		
		/**
		 * Constructor.
		 *
		 * @since 1.0.0 
		 *
		 */
		public function __construct( $smfi_plugin ) {
			$this -> smfi_plugin = $smfi_plugin;
		}
		
		/**
		 * Initialize the shortcodes.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			
			// Enqueue all necessary CSS and JS into the admin screen.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			
			// Enqueue all necessary CSS and JS into the front end.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
			
			// Register shortcodes.
			add_action('init', array( $this, 'register_shortcodes' ) );
			
			/*
			* Attach new buttons to the TinyMCE editor.
			* Pressing these buttons will add the corresponding shortcodes automatically into the current selected area inside the editor.
			*/
			add_action( 'init', array( $this, 'init_shortcode_buttons') );
		}
		
		/**
		 * Enqueue admin scripts.
		 *
		 * Enqueue all necessary CSS and javascript files. If the current post type is not supported by this plugin then no files will be enqueued.
		 *
		 * @since 1.0.0 
		 */
		public function enqueue_admin_scripts() {
			
			if( ! is_null( $this -> smfi_plugin ) ) {
				if( $this -> smfi_plugin -> get_public_api() -> is_smfi_showed() ) {
					
					// Add plugin specific js with translations.
					$translation_object_name = 'smfi_shortcode_translation_object';
					$translation_array = array(
						'insert_default_gallery_btn_tooltip' => esc_html__( 'Insert SMFI gallery', 'simple-multiple-featured-images'),
						'insert_default_slider_btn_tooltip' => esc_html__( 'Insert SMFI slider', 'simple-multiple-featured-images'),
					);
					
					// Add shortcodes JS.
					SMFI_JS_Importer::import_js(
						'smfi_shortcodes_js', 
						plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/jquery.smfi-shortcodes.js', 
						false /*use minified version*/,
						$translation_object_name, 
						$translation_array
					);
				}
			}
		}
		
		/**
		 * Enqueue frontend scripts.
		 *
		 * Enqueue all necessary CSS and javascript files into the frontend. If the current post type is not supported by this plugin then no files will be enqueued.
		 *
		 * @since 1.0.0 
		 */
		public function enqueue_frontend_scripts() {
			
			if( ! is_null( $this -> smfi_plugin ) ) {
				if( $this -> smfi_plugin -> get_public_api() -> is_smfi_showed() ) {
					
					// Add shortcode and CSS
					SMFI_CSS_Importer::import_css(
						'smfi_shortcodes_frontend_css', 
						plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/smfi-shortcodes-frontend-style.css', 
						false /*use minified version*/ 
					);
					
					// Add shortcode JS.
					SMFI_JS_Importer::import_js(
						'smfi_shortcodes_frontend_js', 
						plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/jquery.smfi-shortcodes-frontend.js', 
						false /*use minified version*/
					);
				}
			}
		}
		
		/**
		 * Registers the shortcodes in wordpress.
		 *
		 * Adds the shortcode for inserting a default gallery.
		 * Adds the shortcode for inserting a default slider.
		 *
		 * @since 1.0.0
		 */
		public function register_shortcodes() {
			add_shortcode( $this -> shortcode_insert_default_gallery,  array( $this, 'get_frontend_html_for_shortcode' ) );
			add_shortcode( $this -> shortcode_insert_default_slider,  array( $this, 'get_frontend_html_for_shortcode' ) );
		}
		
		/**
		 * Attachs new shortcode buttons into the TinyMCE editor. For each shortcode a seperate button will be provided.
		 *
		 * Pressing the button will add the corresponding shortcode automatically into the current selected area inside the editor.
		 *
		 * @since 1.0.0 
		 */
		function init_shortcode_buttons() {
			// Register the shortcode buttons.
			add_filter( 'mce_buttons', array( $this, 'register_shortcode_buttons') );
			
			// Register the necessary JS plugin for the shortcode buttons.
			add_filter( 'mce_external_plugins', array( $this, 'register_shortcode_buttons_js') );
		}
		
		/**
		 * Register the shortcode buttons.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $buttonIDs All IDs (as string) of all already registered buttons.
		 * @return array The button IDs.
		 */
		function register_shortcode_buttons( $buttonIDs ) {
			$buttonIDs[] = 'smfiDefaultGalleryBtn';
			$buttonIDs[] = 'smfiDefaultSliderBtn';
			return $buttonIDs;
		}
		
		/**
		 * Register the necessary JS for the registered shortcode buttons.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $paths All absolute paths of all JS scripts of all already registered buttons.
		 * @return array The absolute paths.
		 */
		function register_shortcode_buttons_js( $paths ) {
			$paths[ 'smfiShortcodesButtonsPlugin' ] = plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/jquery.smfi-shortcodes-buttons-plugin.js';
			return $paths;
		}

		/**
		 * Returns the HTML for the provided shortcode.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $content The enclosed shortcode content.
		 * @param string $shortcode_name The shortcode name.
		 * @return string The shortcode frontend HTML.
		 */
		public function get_frontend_html_for_shortcode( $shortcode_attributes = [], $content = null, $shortcode_name = '' ) {
			
			$output = '';
			
			// Search the SMFI plugin.
			if( ! is_null( $this -> smfi_plugin ) ) {
				
				// Check if the current post uses the featured images of the SMFI plugin.
				if( $this -> smfi_plugin -> get_public_api() -> is_smfi_showed() ) {
					
					// Sanitizes the raw shortcode attributes for further using.
					$sanitize_shortcode_attributes = $this -> sanitize_shortcode_attributes( $shortcode_attributes, $shortcode_name );
					
					// Get the appropriate image size for the current shortcode.
					$image_size = $this -> get_image_size_by_shortcode( $sanitize_shortcode_attributes, $shortcode_name );
					
					// Create and collect custom image tag attributes.
					$imgAttributes = array();
					$imgAttributes[ 'class' ] = $this -> get_image_tag_css_classes( $shortcode_name );
					
					// Get the HTML for each featured image individually.
					$smfi_api = $this -> smfi_plugin -> get_public_api();
					$img_tags = $smfi_api -> get_all_featured_images_tags( get_the_id(), $image_size, $imgAttributes );
					
					// Generate the frontend HTML by using the generated image tags.
					$output .= '<div class="' . $this -> get_image_container_css_class( $shortcode_name ) . '"';
					if( $this -> is_slideshow_shortcode( $shortcode_name ) ) {
						$output .= $this -> get_slideshow_speed_attribute( $sanitize_shortcode_attributes, $shortcode_name );
						$output .= $this -> get_dot_color_attribute( $sanitize_shortcode_attributes, $shortcode_name );
						$output .= $this -> get_active_dot_color_attr( $sanitize_shortcode_attributes, $shortcode_name );
						$output .= $this -> get_arrow_color_attr( $sanitize_shortcode_attributes, $shortcode_name );
						$output .= $this -> get_active_arrow_color_attr( $sanitize_shortcode_attributes, $shortcode_name );
					}
					$output .= '>';
					
						// Adjust the image tags as necessary for the current shortcode.
						foreach( $img_tags as $index => $img_tag ) {
							$output .= $this -> adjust_image_tag_by_shortcode( $img_tag, $shortcode_name );
						}
						
						// Check if the current shortcode has some HTML which should be inserted after the image tags.
						$number_of_image_tags = count( $img_tags );
						$output .= $this -> get_additional_html_after_image_tags( $number_of_image_tags, $shortcode_name );
					$output .= '</div>';
				}
			}
			
			return $output;
		}
		
		/**
		 * Sanitizes the raw shortcode attributes for further using.
		 *
		 * Removes unused attributes and creates and initialize missing attributes with default values.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return array Attributes with attribute names as keys.
		 */
		private function sanitize_shortcode_attributes( $shortcode_attributes, $shortcode_name ) {
			// Change all received shortcode attributes to lowercase.
			$shortcode_attributes = array_change_key_case( ( array ) $shortcode_attributes , CASE_LOWER );
					
			// Set default attribute values and override them with attribute values defined by the user.
			return shortcode_atts( [
											'image-size' => $this -> get_default_image_size_by_shortcode( $shortcode_name ),
											'slideshow-speed' => $this -> get_default_slideshow_speed( $shortcode_name ),
											'slideshow-dot-color' => $this -> get_default_dot_color( $shortcode_name ),
											'slideshow-active-dot-color' => $this -> get_default_active_dot_color( $shortcode_name ),
											'slideshow-arrow-color' => $this -> get_default_arrow_color( $shortcode_name ),
											'slideshow-active-arrow-color' => $this -> get_default_active_arrow_color( $shortcode_name ),
											], $shortcode_attributes, $shortcode_name );
		}
		
		/**
		 * Returns the user defined image size via shortcode.
		 *
		 * If no image size can be found then a default image size will be returned.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string A registered wordpress image size. Returns the wordpress 'medium' image size on invalid shortcode.
		 */
		private function get_image_size_by_shortcode( $shortcode_attributes, $shortcode_name ) {
			
			// Get all available intermediate image sizes.
			$all_available_image_sizes = get_intermediate_image_sizes();
			
			// Attach additionally the full image size.
			$all_available_image_sizes[] = 'full';
			
			if( in_array( $shortcode_attributes[ 'image-size' ], $all_available_image_sizes ) ) {
				// User defined a valid image size via shortcode -> return this image size.
				return $shortcode_attributes[ 'image-size' ];
			} else {
				// User does not defined a valid image size via shortcode -> return the default image size for the current shortcode.
				return $this -> get_default_image_size_by_shortcode( $shortcode_name );
			}
		}
		
		/**
		 * Returns the default image size for the given shortcode.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string A registered wordpress image size. Returns the wordpress 'medium' image size on invalid shortcode.
		 */
		private function get_default_image_size_by_shortcode( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_gallery ) {
				// Return 'medium' as default image size for the default gallery.
				return 'medium';
			} else if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				// Return 'large' as default image size for the default slider.
				return 'large';
			} else {
				// Return 'medium' as default image size.
				return 'medium';
			}
		}
		
		/**
		 * Returns the value for the class attribute of the image tags of the given shortcode.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string The value for the class attribute of the image tags of the given shortcode. Returns emtpy string on invalid shortcode.
		 */
		private function get_image_tag_css_classes( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_gallery ) {
				return 'smfi-default-gallery-img';
			} else if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				return 'smfi-default-slider-img';
			} else {
				// Return empty string as default.
				return '';
			}
		}
		
		/**
		 * Returns the class of the container tag which will contains all image tags.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string The container class attribute value. Returns emtpy string on invalid shortcode.
		 */
		private function get_image_container_css_class( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_gallery ) {
				return 'smfi-default-gallery-container';
			} else if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				return 'smfi-default-slider-container';
			} else {
				// Return empty string as default.
				return '';
			}
		}
		
		
		/**
		 * Checks if the given shortcode should create a slideshow.
		 * 
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return bool True if the shortcode have to generate a slideshow else false.
		 */
		private function is_slideshow_shortcode( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				return true;
			}
			return false;
		}
		
		/**
		 * Returns the slideshow speed as a HTML5 data attribute.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string A data attribute with the slideshow speed as value or empty string if user does not enter the slideshow speed attribute.
		 */
		private function get_slideshow_speed_attribute( $shortcode_attributes, $shortcode_name ) {
			// Get the slideshow speed for the current shortcode if available.
			$slideshow_speed = $this -> get_slideshow_speed( $shortcode_attributes, $shortcode_name );
			if( $slideshow_speed > 0 ) {
				return 'data-slideshow-speed="' . $slideshow_speed . '"';
			}
			return '';
		}
		
		/**
		 * Returns the slideshow dot color as a HTML5 data attribute.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string A data attribute with the dot color as value or empty string if user does not enter the dot color attribute.
		 */
		private function get_dot_color_attribute( $shortcode_attributes, $shortcode_name ) {
			// Get the slideshow dot color.
			$slideshow_dot_color = $this -> get_dot_color( $shortcode_attributes, $shortcode_name );
			if( ! empty( $slideshow_dot_color ) ) {
				return 'data-slideshow-dot-color="' . $slideshow_dot_color . '"';
			}
			return '';
		}
		
		/**
		 * Returns the slideshow active dot color as a HTML5 data attribute.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string A data attribute with the active dot color as value or empty string if user does not enter the active dot color attribute.
		 */
		private function get_active_dot_color_attr( $shortcode_attributes, $shortcode_name ) {
			// Get the slideshow dot color.
			$slideshow_active_dot_color = $this -> get_active_dot_color( $shortcode_attributes, $shortcode_name );
			if( ! empty( $slideshow_active_dot_color ) ) {
				return 'data-slideshow-active-dot-color="' . $slideshow_active_dot_color . '"';
			}
			return '';
		}
		
		/**
		 * Returns the slideshow arrow color as a HTML5 data attribute.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string A data attribute with the arrow color as value or empty string if user does not enter the arrow color attribute.
		 */
		private function get_arrow_color_attr( $shortcode_attributes, $shortcode_name ) {
			// Get the slideshow arrow color.
			$slideshow_arrow_color = $this -> get_arrow_color( $shortcode_attributes, $shortcode_name );
			if( ! empty( $slideshow_arrow_color ) ) {
				return 'data-slideshow-arrow-color="' . $slideshow_arrow_color . '"';
			}
			return '';
		}
		
		/**
		 * Returns the slideshow active arrow color as a HTML5 data attribute.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string A data attribute with the active arrow color as value or empty string if user does not enter the active arrow color attribute.
		 */
		private function get_active_arrow_color_attr( $shortcode_attributes, $shortcode_name ) {
			// Get the slideshow arrow color.
			$slideshow_active_arrow_color = $this -> get_active_arrow_color( $shortcode_attributes, $shortcode_name );
			if( ! empty( $slideshow_active_arrow_color ) ) {
				return 'data-slideshow-active-arrow-color="' . $slideshow_active_arrow_color . '"';
			}
			return '';
		}
		
		/**
		 * Returns the user defined time after which the slider automatically changes images.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return int Duration in millisonds. Returns -1 if the given shortcode is invalid, 
		 *									   shortcode does not support slideshow speed or
		 *									   user entered an invalid shortcode attribute value (less than 1).
		 */
		private function get_slideshow_speed( $shortcode_attributes, $shortcode_name ) {
			// Search time entered by user and convert it into int.
			$time = intval( $shortcode_attributes[ 'slideshow-speed' ] );
			
			if( $time > 0 ) {
				// User defined a valid time via shortcode -> return this time.
				return $time;
			} else {
				// User does not defined a valid time via shortcode -> return the default time for the current shortcode.
				return $this -> get_default_slideshow_speed( $shortcode_name );
			}
		}
		
		/**
		 * Returns the default time after which the slider automatically changes images.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return int Duration in millisonds. Returns -1 if the given shortcode is invalid or shortcode does not support slideshow speed.
		 */
		private function get_default_slideshow_speed( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				// Return 8000 milliseconds as default for the default slider.
				return 8000;
			}
			return -1;
		}
		
		/**
		 * Returns the user defined color for the dots of a slideshow.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid, 
		 *							   	   shortcode does not support dots or
		 *								   user entered an invalid color as shortcode attribute value.
		 */
		private function get_dot_color( $shortcode_attributes, $shortcode_name ) {
			
			// Search color entered by user.
			$color = sanitize_hex_color( $shortcode_attributes[ 'slideshow-dot-color' ] );
			
			if( isset( $color ) || ! empty( $color ) ) {
				// User defined a valid color via shortcode -> return this color.
				return $color;
			} else {
				// User does not defined a valid color via shortcode -> return the default color for the current shortcode.
				return $this -> get_default_dot_color( $shortcode_name );
			}
		}
		
		/**
		 * Returns the default color for the dots of a slideshow.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid or shortcode does not support a dot color.
		 */
		private function get_default_dot_color( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				// Return #696969 as default for the default slider.
				return '#696969';
			}
			return '';
		}
		
		/**
		 * Returns the user defined color for active dots of a slideshow.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid, 
		 *							   	   shortcode does not support dots or
		 *								   user entered an invalid color as shortcode attribute value.
		 */
		private function get_active_dot_color( $shortcode_attributes, $shortcode_name ) {
			
			// Search color entered by user.
			$color = sanitize_hex_color( $shortcode_attributes[ 'slideshow-active-dot-color' ] );
			
			if( isset( $color ) || ! empty( $color ) ) {
				// User defined a valid color via shortcode -> return this color.
				return $color;
			} else {
				// User does not defined a valid color via shortcode -> return the default color for the current shortcode.
				return $this -> get_default_active_dot_color( $shortcode_name );
			}
		}
		
		/**
		 * Returns the default color for active dots of a slideshow.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid or shortcode does not support an active dot color.
		 */
		private function get_default_active_dot_color( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				// Return #696969 as default for the default slider.
				return '#DCDCDC';
			}
			return '';
		}
		
		/**
		 * Returns the user defined color for the arrows of a slideshow.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid, 
		 *							   	   shortcode does not support arrows or
		 *								   user entered an invalid color as shortcode attribute value.
		 */
		private function get_arrow_color( $shortcode_attributes, $shortcode_name ) {
			
			// Search color entered by user.
			$color = sanitize_hex_color( $shortcode_attributes[ 'slideshow-arrow-color' ] );
			
			if( isset( $color ) || ! empty( $color ) ) {
				// User defined a valid color via shortcode -> return this color.
				return $color;
			} else {
				// User does not defined a valid color via shortcode -> return the default color for the current shortcode.
				return $this -> get_default_arrow_color( $shortcode_name );
			}
		}
		
		/**
		 * Returns the default color for the arrows of a slideshow.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid or shortcode does not support a arrow color.
		 */
		private function get_default_arrow_color( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				// Return #696969 as default for the default slider.
				return '#696969';
			}
			return '';
		}
			
		/**
		 * Returns the user defined color for active arrows of a slideshow.
		 * 
		 * @since 1.0.0 
		 *
		 * @param array $shortcode_attributes The shortcode attributes entered by the user.
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color as HEX. Returns empty string if the given shortcode is invalid, 
		 *							   	   shortcode does not support arrows or
		 *								   user entered an invalid color as shortcode attribute value.
		 */
		private function get_active_arrow_color( $shortcode_attributes, $shortcode_name ) {
			
			// Search color entered by user.
			$color = sanitize_hex_color( $shortcode_attributes[ 'slideshow-active-arrow-color' ] );
			
			if( isset( $color ) || ! empty( $color ) ) {
				// User defined a valid color via shortcode -> return this color.
				return $color;
			} else {
				// User does not defined a valid color via shortcode -> return the default color for the current shortcode.
				return $this -> get_default_active_arrow_color( $shortcode_name );
			}
		}
		
		/**
		 * Returns the default color for active arrows of a slideshow.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $shortcode_name The shortcode name.
		 * @return string Color. Returns empty string if the given shortcode is invalid or shortcode does not support an active arrow color.
		 */
		private function get_default_active_arrow_color( $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				// Return #696969 as default for the default slider.
				return '#DCDCDC';
			}
			return '';
		}
		
		/**
		 * Adjusts the given image tag for the provided shortcode.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $img_tag The image tag.
		 * @param string $shortcode_name The shortcode name.
		 * @return string The adjusted image tag. Returns the received img tag if given shortcode is invalid or shortcode does not need to adjust anything.
		 */
		private function adjust_image_tag_by_shortcode( $img_tag, $shortcode_name ) {
			
			if( $shortcode_name === $this -> shortcode_insert_default_gallery ) {
				// Currently the default gallery do not need any adjustments -> just return the image tag.
				return $img_tag;
			} else if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				/*
				* The default slider have to make some adjustments.
				* It wraps the image tag by a div which represents a slide.
				* Additionally it shows the position of the image explicit.
				*/
				$output = '<div class="smfi-default-slider-slide smfi-default-slider-fade-in-slide">';
					$output .= $img_tag;
				$output .= '</div>';
				return $output;
			} else {
				// Return the received img tag as default.
				return $img_tag;
			}
		}
		
		/**
		 * Returns the HTML which should be appended after all image tags.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $number_of_image_tags The number of all image tags.
		 * @param string $shortcode_name The shortcode name.
		 * @return string HTML. Returns empty string if given shortcode is invalid or shortcode have nothing to add.
		 */
		private function get_additional_html_after_image_tags( $number_of_image_tags, $shortcode_name ) {
			if( $shortcode_name === $this -> shortcode_insert_default_gallery ) {
				// The default gallery currently do not need any additional HTML.
				return '';
			} else if( $shortcode_name === $this -> shortcode_insert_default_slider ) {
				/*
				* The default slider have to create some additional HTML.
				* It adds to buttons which can be used to switch between the slides.
				* Additionally it adds a dot for each image tag. The dots can be used
				* to jump to the corresponding slide.
				*/
				$output = '<button class="smfi-default-slider-prev">&#10094;</button>';
				$output .= '<button class="smfi-default-slider-next">&#10095;</button>';
				$output .= '<div class="smfi-default-slider-dot-container">';
					for ( $i = 0 ; $i < $number_of_image_tags; $i++ ){ 
						$output .= '<div class="smfi-default-slider-dot" tabindex=0></div>'; 
					}
				$output .= '</div>';
				return $output;
			} else {
				// Return the received img tag as default.
				return '';
			}
		}
	}
}
?>