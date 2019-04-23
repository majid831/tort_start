<?php
/*
Plugin Name: Simple Multiple Featured Images
Plugin URI:   https://roman-bauer-web.de/wordpress-plugin-smfi
Description:  Allows to add multiple featured images.
Version:      1.0.6
Author:       Roman Bauer
Author URI:   https://roman-bauer-web.de/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  simple-multiple-featured-images
Domain Path:  /languages

Simple Multiple Featured Images is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Simple Multiple Featured Images is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Simple Multiple Featured Images. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
if ( ! class_exists( 'Simple_Multiple_Featured_Images' ) ) {

	/*
	 * Register the activation, deactivation und uninstall hook for this plugin.
	 */
	register_activation_hook( __FILE__, array( 'Simple_Multiple_Featured_Images', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Simple_Multiple_Featured_Images', 'deactivate' ) );
	register_uninstall_hook( __FILE__, array( 'Simple_Multiple_Featured_Images', 'uninstall' ) );

	/**
	 * Import css importer which allows to import css files.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-css-importer.php';

	/**
	 * Import js importer which allows to import js files.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-js-importer.php';

	/**
	 * Import smfi validator which allows to validate image data.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-validator.php';

	/**
	 * Import smfi nonce manager which is used in context of nonces.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-nonce-manager.php';

	/**
	 * Import the public API. This API is intended to be used by external programs.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-public-api.php';

	/**
	 * Import the shortcodes.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-shortcodes.php';

	/**
	 * Import the drag and drop support.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smfi-dnd.php';

	/**
	 * Adds multiple featured images feature to wordpress.
	 *
	 * Allows to create and add multiple featured images. The user can easily add and remove as many featured images as he like.
	 *
	 * @since 1.0.0
	 */
	class Simple_Multiple_Featured_Images {

		/**
		 * Name of the hidden input which will be used to transmit the selected image id from client to server.
		 * This name will be used as value of the name attribute of the input tag.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $hidden_input_name_img_ids = 'smfi-img-ids';

		/**
		 * Meta key which will be used to save necessary img data inside the wordpress database.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $db_meta_key = '_smfi';

		/**
		 * Contains the supported post types of this plugin by default.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $supported_post_types = array( 'post', 'page' );

		/**
		 * Contains IDs of all posts which should not support the featured images.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $excluded_posts_by_id = array();

		/**
		 * Contains IDs of all posts which should be support the featured images.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $included_posts_by_id = array();

		/**
		 * Helper which is used to validate img data in different places.
		 *
		 * @since 1.0.0
		 * @var SMFI_Validator
		 */
		private $validator;

		/**
		 * Helper which is used in context of nonces.
		 *
		 * @since 1.0.0
		 * @var SMFI_Nonce_Manager
		 */
		private $nonce_manager;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param SMFI_Validator  $validator Used to validate img data in different places.
		 * @param SMFI_Nonce_Manager  $nonce_manager Used in context of nonces.
		 */
		public function __construct( $validator, $nonce_manager ) {
			$this -> validator = $validator;
			$this -> nonce_manager = $nonce_manager;
		}

		/**
		 * Activate callback for this plugin.
		 *
		 * Callback which is used if the plugin is activated.
		 *
		 * @since 1.0.0
		 */
		public static function activate() {
		}

		/**
		 * Deactivate callback for this plugin.
		 *
		 * Callback which is used if the plugin is deactivated.
		 *
		 * @since 1.0.0
		 */
		public static function deactivate() {
		}

		/**
		 * Uninstall callback for this plugin.
		 *
		 * Callback which is used if the plugin is uninstalled.
		 *
		 * @since 1.0.0
		 */
		public static function uninstall() {

			// If uninstall not called from WordPress, then exit
			if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
				exit;
			}

			// Remove all post meta data which was added by this plugin.
			delete_post_meta_by_key( $this -> db_meta_key );
		}

		/**
		 * Initialize the plugin.
		 *
		 * This have to be called to start the plugin. It adds all necessary scripts, actions and hooks.
		 *
		 * @since 1.0.0
		 */
		public function init() {

			// Enqueue all necessary CSS and JS into the admin screen.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			/*
			 * Register ajax handler which will be used to handle image change requests via ajax.
			 * This happens if an existing image is exchanged by another image
			 */
			add_action( 'wp_ajax_get_img_html_by_ajax_as_json', array( $this, 'get_img_html_by_ajax_as_json' ) );

			/*
			 * Register ajax handler which will be used to handle adding new images.
			 * This happens if the user adds a complete new image.
			 */
			add_action( 'wp_ajax_get_img_wrapper_html_by_ajax_as_json', array( $this, 'get_img_wrapper_html_by_ajax_as_json' ) );

			// Add own meta box which will display the featured images
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

			// Add save callback which will be used to save the featured images
			add_action( 'save_post', array( $this, 'save_selected_images' ) );

			// Activate translation support
			add_action( 'plugins_loaded', array( $this, 'activate_plugin_textdomain' ) );

			// Add own admin notices for error handling
			add_action( 'admin_notices', array( $this, 'add_admin_notices' ) );

			/*
			 *	Setup hook which will remove all error variables which were transmitted via GET to the client.
			 *  This will be used in context of showing admin notices after saving a post.
			 */
			add_filter( 'removable_query_args', array( $this, 'collect_removeable_parameter_from_url' ) );

			/*
			*	Initialize shortcodes and drag and drop after theme setup.
			* For this we use the after_setup_theme hook.
			* This ensures that the hooks are not called too early.
			* In this way, the filters can be added, for example, in the functions.php.
			*/
			add_action('after_setup_theme', array( $this, 'init_shortcodes' ));
			add_action('after_setup_theme', array( $this, 'init_drag_and_drop' ));
		}

		/**
		 * Initializes the shortcodes feature.
		 *
		 * @since 1.0.6
		 */
		public function init_shortcodes() {
			/**
			 * Filters the shortcodes feature deactivation.
			 *
			 * @since 1.0.6
			 *
			 * @param bool The current deactivation state. Default is false.
			 */
			$deactivate_shortcodes = apply_filters( 'smfi_deactivate_shortcodes', false );
			$deactivate_shortcodes = is_bool ( $deactivate_shortcodes ) && $deactivate_shortcodes ? true : false;
			if( ! $deactivate_shortcodes ) {

				$smfi_shortcodes = new SMFI_Shortcodes( $this );
				$smfi_shortcodes -> init();
			}
		}

		/**
		 * Initializes the drag and drop feature.
		 *
		 * @since 1.0.6
		 */
		public function init_drag_and_drop() {
			/**
			 * Filters the drag and drop feature deactivation.
			 *
			 * @since 1.0.6
			 *
			 * @param bool The current deactivation state. Default is false.
			 */
			$deactivate_dnd = apply_filters( 'smfi_deactivate_dnd', false );
			$deactivate_dnd = is_bool ( $deactivate_dnd ) && $deactivate_dnd ? true : false;
			if( ! $deactivate_dnd ) {
				$smfi_dnd = new SMFI_DND( $this );
				$smfi_dnd -> init();
			}
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * Enqueue all necessary CSS and javascript files. If the current post type is not supported by this plugin then no files will be enqueued.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_admin_scripts() {

			// Enqueue all necessary styles and scripts if current post type is supported
			if( $this -> is_supported_post() ) {

				// Add wordpress media uploader scripts because we use it to give the user the possibility to select images.
				wp_enqueue_media();

				// Add plugin specific css.
				SMFI_CSS_Importer::import_css( 'smfi_css', plugin_dir_url( __FILE__ ) . 'admin/css/smfi-style.css' );

				// Add plugin specific js with translations.
				$translation_object_name = 'smfi_translation_object';
				$translation_array = array(
					'media_frame_title' => esc_html__( 'Select image', 'simple-multiple-featured-images'),
					'media_frame_add_btn_txt' => esc_html__( 'Add image', 'simple-multiple-featured-images'),

					'default_error_message' => $this -> get_default_error_message() .  ' ' . $this -> get_default_error_instruction(),
					'add_image_error_message' => $this -> get_add_image_failed_error_message(),
					'change_image_error_message' => $this -> get_change_image_failed_error_message(),
				);

				SMFI_JS_Importer::import_js(
					'smfi_js',
					plugin_dir_url( __FILE__ ) . 'admin/js/jquery.smfi.js',
					false, /*use minified version*/
					$translation_object_name,
					$translation_array
				);

				// Add some necessary polyfills
				SMFI_JS_Importer::import_js(
					'smfi_polyfills_js',
					plugin_dir_url( __FILE__ ) . 'admin/js/jquery.smfi-polyfills.js',
					false /*use minified version*/
				);

				// Initialize ajax as last step, after all scripts were loaded!
				$this -> init_ajax( 'smfi_js' );
			}
		}

		/**
		 * Ajax callback which returns the html of an image tag.
		 *
		 * Gets the image id by the client via $_POST and generates the HTML for the corresponding image.
		 *
		 * @since 1.0.0
		 *
		 * @return json JSON with 'newImgHtml' property which contains the html or 'smfiErrorMessage' property which contains the error message
		 * if something go wrong.
		 */
		public function get_img_html_by_ajax_as_json() {

			try {

				// Check nonce at first step.
				$die_if_invalid = false;
				if( ! check_ajax_referer(	$this -> nonce_manager::NONCE_ACTION_IMG_CHANGE, $this -> nonce_manager::NONCE_NAME_IMG_CHANGE, $die_if_invalid ) ) {
					// Get an invalid nonce.
					throw new Exception( "Received invalid nonce during image change request via ajax" );
				}

				// Get requested image by received id and response the img html to the client.
				$img_id = isset( $_POST['smfi_img_id'] ) && is_numeric( $_POST['smfi_img_id'] ) ? intval( $_POST['smfi_img_id'], 10 ) : -1;
				if( $this -> validator -> is_valid_img_id( intval( $img_id, 10 ) ) ) {

					// Get image html.
					$img_html = $this -> get_image_html( $img_id );

					// Send image html to client.
					wp_send_json(array(
						'newImgHtml' => $img_html
					));

				} else {
					// Get an invalid image id.
					throw new Exception( "Received invalid image id during image change request via ajax" );
				}

			} catch( Exception $exception ) {
					// Response error message to user.
					$this -> send_ajax_error_as_json();
			}
		}

		/**
		 * Ajax callback which returns the HTML of an image wrapper including the image tag, the remove button tag
		 * and the hidden input tag with the image ID.
		 *
		 * Gets an image id by the client via $_POST and generates the HTML for the corresponding image wrapper.
		 *
		 * @since 1.0.0
		 *
		 * @return json JSON with 'newImgHtml' property which contains the html or 'smfiErrorMessage' property which contains the error message
		 * if something go wrong.
		 */
		public function get_img_wrapper_html_by_ajax_as_json() {

			try {

				// Check nonce at first step.
				$die_if_invalid = false;
				if( ! check_ajax_referer(	$this -> nonce_manager::NONCE_ACTION_IMG_ADD, $this -> nonce_manager::NONCE_NAME_IMG_ADD, $die_if_invalid ) ) {
					// Get an invalid nonce.
					throw new Exception( "Received invalid nonce during add new image request via ajax" );
				}

				// Get requested image by received id and response the img html to the client.
				$img_id = isset( $_POST['smfi_img_id'] ) && is_numeric( $_POST['smfi_img_id'] ) ? intval( $_POST['smfi_img_id'], 10 ) : -1;
				if( $this -> validator -> is_valid_img_id( intval( $img_id, 10 ) ) ) {

					// Create image wrapper HTML.
					$output = $this -> get_image_wrapper_html( $img_id );

					// Send all generated HTML to the client.
					wp_send_json(array(
						'newImgHtml' => $output
					));

				} else {
					// Get an invalid image id.
					throw new Exception( "Received invalid image id during add new image request via ajax" );
				}

			} catch( Exception $exception ) {
					// Response error message to user.
					$this -> send_ajax_error_as_json();
			}
		}

		/**
		 * Adds the metabox.
		 *
		 * The metabox shows the created featured images and allows to add new featured images or remove old featured images.
		 *
		 * @since 1.0.0
		 */
		public function add_meta_box() {

			// Add meta box only if the current post is not excluded from using the featured images.
			if( $this -> is_supported_post() ) {

				// Create metabox title.
				$meta_box_title = esc_html__( 'More Featured Images', 'simple-multiple-featured-images' );

				/**
				 * Filters the title of the metabox.
				 *
				 * @since 1.0.0
				 *
				 * @param string The current title.
				 */
				$custom_meta_box_title = apply_filters( 'smfi_metabox_title', $meta_box_title );
				if( is_string( $custom_meta_box_title ) ) {
					$meta_box_title = esc_html( $custom_meta_box_title );
				}

				/** This filter is documented in simple-multiple-featured-images.php */
				$supported_post_types = apply_filters( 'smfi_supported_post_types', $this -> supported_post_types );
				if( ! is_array( $supported_post_types ) ) {
					$supported_post_types = $this -> supported_post_types;
				}

				if( ! $this -> is_supported_post_type() && $this -> is_included_post() ) {
					/*
					* Found a post which should be use the features images but not its post type.
					* We have to add its post type as supported post type outselves because wordpress
					* allows only to add a metaxbox by post type.
					*/
					array_push( $supported_post_types, get_post_type() );
				}

				// Add meta box.
			add_meta_box( 'smfi-metabox',
										$meta_box_title ,
										array( $this, 'echo_meta_box_html' ),
										$supported_post_types,
										'normal' /*context*/,
										'low' /*priority*/,
										array(
        							'__block_editor_compatible_meta_box' => true,
    									) /*callback_args*/
									);
			}
		}

		/**
		 * Echo the metabox html for the given post.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post $post Current post object.
		 */
		public function echo_meta_box_html( $post ) {

			// Set nonce for this metabox.
			wp_nonce_field( $this -> nonce_manager::NONCE_ACTION_METABOX  . $post->ID, $this -> nonce_manager::NONCE_NAME_METABOX );

			// Create and echo the html.
			echo '<div id="smfi-content-container">';
				echo $this -> get_error_container_html();
				echo $this -> get_add_new_images_btn_html();
				echo '<hr>';
				echo $this -> get_all_existing_images_html();
			echo '</div>';
		}

		/**
		 * Save callback which saves the featured images.
		 *
		 * Saves the created/selected featured images. If something go wrong the redirect_post_location hook is used to attach an error code to the url.
		 * This error code will be used by the admin notice hook to show the error message on page reload.
		 * This way is necessary because after the save_hook was fired, it redirects back to the origin url.
		 * Because of the redirect we have not any information about the error anymore and could not show it to the user.
		 *
		 * @since 1.0.0
		 *
		 * @param int $post_id Current post id.
		 */
		public function save_selected_images( $post_id ) {

			try {
				if( $this -> is_supported_post() ) {

					// If this is just a revision, don't save our image data.
					if( wp_is_post_revision( $post_id ) ) {
						return;
					}

					// Verify if this is an auto save routine because custom fields does not sent on wordpress autosave.
					// Therefore the save functions for the custom fields would assume the current data should be deleted because no data was sent.
					if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
						return;
					}

					// Check permission.
					if( ! current_user_can( 'edit_post', $post_id ) ) {
						throw new Exception( 'Save failed because of missing permissions.' );
					}

					// Check nonce.
					if( ! wp_verify_nonce( $_POST[ $this -> nonce_manager::NONCE_NAME_METABOX ], $this -> nonce_manager::NONCE_ACTION_METABOX . $post_id ) ) {
						throw new Exception( 'Save failed because of invalid nonce.' );
					}
					// Get the ids of the images which were selected by the user.
					$received_img_ids = $_POST[ $this -> hidden_input_name_img_ids ];

					/*
					* If no image ids were transmitted just set an empty array. An empty array means
					* that no images were selected by the user and no images should be saved with this post.
					*/
					if( is_null( $received_img_ids ) ) {
						$received_img_ids = array();
					}

					// Validate image ids.
					if( ! $this -> validator -> are_valid_img_ids( $received_img_ids ) ) {
						throw new Exception( "Save failed because of an invalid image ids." );
					}

					/**
					 * Filters the image ids before saving them into the database.
					 * This filter is triggered when a post is saved.
					 *
					 * @since 1.0.0
					 *
					 * @param array The image attachment IDs.
					 */
					$received_img_ids = apply_filters( 'smfi_img_ids_before_save', $received_img_ids );

					// Validate image ids again.
					if( ! $this -> validator -> are_valid_img_ids( $received_img_ids ) ) {
						throw new Exception( "Save failed because a hook made the image ids invalid." );
					}

					// Now save the image ids.
					$this -> save_image_ids_into_db($post_id, $received_img_ids);
				}
			} catch ( Exception $exception ) {
				// Use the redirect_post_location hook to attach an error code to the url.
				add_filter( 'redirect_post_location', array( $this, 'attach_user_save_error_to_url' ) );
			}
		}

		/**
		 * Saves the given image ids into wordpress database.
		 *
		 * @since 1.0.0
		 *
		 * @param int $post_id Current post id.
		 * @param array $received_img_ids Array with image ids.
		 */
		private function save_image_ids_into_db( $post_id, $received_img_ids ) {
			// Clean up db from old data before saving new data.
			if( metadata_exists( 'post', $post_id, $this -> db_meta_key ) ) {
				$is_deleted = delete_post_meta(
					$post_id,
					$this -> db_meta_key,
					'' /* value*/
				);

				if( ! $is_deleted ) {
					throw new Exception( "Save failed because deletion of old meta data from database failed." );
				}
			}

			// Save the new data inside the database.
			for( $i = 0; $i < count( $received_img_ids ); $i++ ) {
				$id = $received_img_ids[$i];

				$is_saved = add_post_meta(
					$post_id,
					$this -> db_meta_key,
					array('img_id' => $id),
					false /* unique*/
				);

				if( ! $is_saved ) {

					throw new Exception( "Save failed because adding meta data to the database failed." );

					// TODO: At this point we need a rollback, because the old data was already deleted!
				}
			}
		}

		/**
		 * Attachs an error code to the given url.
		 *
		 * This function is used as callback of the 'redirect_post_location' hook and should executed only once.
		 * Therefore it removes itself from the 'redirect_post_location' hook as first step.
		 *
		 * @since 1.0.0
		 *
		 * @see save_selected_images
		 *
		 * @param string $location URL.
		 * @return string URL
		 */
		public function attach_user_save_error_to_url( $location ) {

			// We need this filter only once (during save_post hook). So remove this filter immediately.
			remove_filter( 'redirect_post_location', array( $this, 'attach_user_save_error_to_url' ), 99 );

			// Attach save error as parameter to the url.
			$key = 'smfi-error';
			$value = 'save-error';
			return add_query_arg( $key , $value , $location );
		}

		/**
		 * Collects all temporarily set parameters by this plugin which should be removed from the current URL.
		 *
		 * The parameter names are collected inside the given array.
		 *
		 * @since 1.0.0
		 *
		 * @see save_selected_images
		 *
		 * @param array $args An array of all parameter names which should be removed from the URL.
		 * @return array An array of all parameter names which should be removed from the URL.
		 */
		public function collect_removeable_parameter_from_url( $args ) {
			// Remove any possible error code.
			array_push( $args, 'smfi-error' );
			array_push( $args, 'smfi-dnd-error' );
			return $args;
		}

		/**
		 * Activate translation support of this plugin.
		 *
		 * @since 1.0.0
		 *
		 */
		public function activate_plugin_textdomain() {
			load_plugin_textdomain( 'simple-multiple-featured-images', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add custom admin notices that report errors to the user in the main notice area.
		 *
		 * @since 1.0.0
		 *
		 */
		public function add_admin_notices() {

			if( $this -> is_supported_post() ) {

				// Check url for error parameter and show an error message to the user if found.
				if ( array_key_exists( 'smfi-error', $_GET ) ) {
					echo '<div class="notice notice-error is-dismissible">';
						echo '<p>';

							switch($_GET['smfi-error']) {
								case 'save-error':
									$saveErrorMsg = esc_html__( 'Saving the featured images failed.', 'simple-multiple-featured-images' );
									echo '	Simple Multiple Featured Images Plugin: ' .
											$saveErrorMsg . ' ' . $this -> get_default_error_instruction();
									break;
								default:
									echo $this -> get_default_error_message() . ' ' . $this -> get_default_error_instruction();
									break;
							}

						echo '</p>';
					echo '</div>';
				}
			}
		}

		/**
		 * Returns the public API which can be used by external programs to work with the featured images plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return SMFI_Public_API The API.
		*/
		public function get_public_api() {
				return new SMFI_Public_API( $this );
		}

		/**
		 * Returns the public API which can be used by external programs to work with the featured images plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return SMFI_Public_API The API.
		*/
		public function get_db_meta_key() {
				return $this -> db_meta_key;
		}

		/**
		 * Checks if the current post is supported by this plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if post is supported else false.
		*/
		public function is_supported_post() {
			$is_supported_post_type = $this -> is_supported_post_type();
			$is_excluded_post = $this -> is_excluded_post();
			$is_included_post = $this -> is_included_post();
			return ( $is_supported_post_type && ! $is_excluded_post ) || $is_included_post;
		}

		/**
		 * Checks if the current post type is supported by this plugin.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if post type is supported else false.
		*/
		private function is_supported_post_type() {
			/**
			 * Filters the supported post types of this plugin.
			 * Only the supported post types will display the featured images.
			 * If something is returned that is invalid, the post types 'post' and 'page' are used by default.
			 *
			 * @since 1.0.0
			 *
			 * @param array The supported post types.
			 */
			$supported_post_types = apply_filters( 'smfi_supported_post_types', $this -> supported_post_types );
			if( ! is_array( $supported_post_types ) ) {
				$supported_post_types = $this -> supported_post_types;
			}
			return in_array( get_post_type() , $supported_post_types, true );
		}

		/**
		 * Checks if the current post is included in using the features images.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if post is supported else false.
		*/
		private function is_included_post() {
			/**
			 * Filters the ids of posts which should be explicit included in using the featured images.
			 * If you do not want to unclude an entire post type. Just use a post ID.
			 *
			 * @since 1.0.0
			 *
			 * @param array The IDs of the posts which should be included in using the featured images.
			 */
			$included_posts_by_id = apply_filters( 'smfi_included_posts_by_id', $this -> included_posts_by_id );
			if( ! is_array( $included_posts_by_id ) ) {
				$included_posts_by_id = $this -> included_posts_by_id;
			}
			return  in_array( get_the_ID() , $included_posts_by_id, true );
		}

		/**
		 * Checks if the current post is excluded from using the features images.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if post is supported else false.
		*/
		private function is_excluded_post() {
			/**
			 * Filters the IDs of posts which should be explicit excluded from using the featured images.
			 *
			 * @since 1.0.0
			 *
			 * @param array The (int) ids of the posts which should be excluded from using the featured images.
			 */
			$excluded_posts_by_id = apply_filters( 'smfi_excluded_posts_by_id', $this -> excluded_posts_by_id );
			if( ! is_array( $excluded_posts_by_id ) ) {
				$excluded_posts_by_id = $this -> excluded_posts_by_id;
			}
			return  in_array( get_the_ID() , $excluded_posts_by_id, true );
		}

		/**
		 * Sends ajax response as an json error to the client.
		 *
		 * If no error message or an invalid error message was found then a default error message will be sent.
		 *
		 * @since 1.0.0
		 *
		 * @param string $errorMessage The error message.
		*/
		private function send_ajax_error_as_json( $errorMessage = '' ) {

			if( ! is_string( $errorMessage ) || empty( $errorMessage ) ) {
				// Send default error message.
				wp_send_json_error( array(
						'smfiErrorMessage' => $this -> get_default_error_message() .  ' ' . $this -> get_default_error_instruction()
				) );
			} else {
				// Send given error message.
				wp_send_json_error( array(
						'smfiErrorMessage' => $errorMessage
				) );
			}

		}

		/**
		 * Returns the default error message.
		 *
		 * @since 1.0.0
		 *
		 * @return string The escaped and translated default error message.
		*/
		private function get_default_error_message() {
			return 'Simple Multiple Featured Images Plugin: '  .
					esc_html__(
					'An error occured.' ,
					'simple-multiple-featured-images' );
		}

		/**
		 * Returns the error message for the failed adding an image.
		 *
		 * @since 1.0.0
		 *
		 * @return string The escaped and translated error message.
		*/
		private function get_add_image_failed_error_message() {

			$errorDesc = esc_html__(
							'Adding the image failed.' ,
							'simple-multiple-featured-images' );

			return 'Simple Multiple Featured Images Plugin: '  . $errorDesc . ' ' .  $this -> get_default_error_instruction();
		}


		/**
		 * Returns the error message for the failed changing of an image.
		 *
		 * @since 1.0.0
		 *
		 * @return string The escaped and translated error message.
		*/
		private function get_change_image_failed_error_message() {

			$errorDesc = esc_html__(
							'Changing the image failed.' ,
							'simple-multiple-featured-images' );

			return 'Simple Multiple Featured Images Plugin: '  . $errorDesc . ' ' .  $this -> get_default_error_instruction();
		}

		/**
		 * Returns the default error instruction.
		 *
		 * The instruction explains the user what to do in case of an error.
		 *
		 * @since 1.0.0
		 *
		 * @return string The escaped and translated default error instruction.
		*/
		public function get_default_error_instruction() {
			return esc_html__(
						'Please contact the responsible person for your website or the plugin developer.',
						'simple-multiple-featured-images');
		}

		/**
		 * Initialize ajax.
		 *
		 * Injects an ajax object as javascript object. This object holds some informations (like the AJAX URL) and can be used for ajax requests.
		 *
		 * @since 1.0.0
		 *
		 * @param string $scriptName Unique script name.
		*/
		private function init_ajax( $scriptName ) {
			// Inject an ajax object into the javascript which holds the necessary ajax url for the ajax requests.
			wp_localize_script(
				$scriptName,
				'ajax_smfi_object',
				array( 	'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_change_image_nonce' => wp_create_nonce( $this -> nonce_manager::NONCE_ACTION_IMG_CHANGE ),
				'ajax_add_image_nonce' => wp_create_nonce( $this -> nonce_manager::NONCE_ACTION_IMG_ADD ),
			) );
		}

		/**
		 * Returns the HTML for the error container.
		 *
		 * @since 1.0.0
		 *
		 * @return string The HTML.
		*/
		private function get_error_container_html() {
			$output = '<div id="smfi-error-container">';
			$output .= '</div>';
			return $output;
		}

		/**
		 * Returns the HTML for button which will be used to add new images.
		 *
		 * @since 1.0.0
		 *
		 * @return string The HTML.
		*/
		private function get_add_new_images_btn_html() {
			$output = '<div class="smfi-btn-container">';
				$title = esc_attr__( 'Add image', 'simple-multiple-featured-images' ) . '">' . esc_html__( 'Add image', 'simple-multiple-featured-images' );
				$output .= '<button id= "smfi-add-new-img-btn" class="button" title="' . $title . '</button>';
			$output .= '</div>';
			return $output;
		}

		/**
		 * Returns the HTML for all assign featured images.
		 *
		 * Reads the image data from the database and builds the HTML from it. If no images were found then an empty container will be returned.
		 *
		 * @since 1.0.0
		 *
		 * @return string The HTML.
		*/
		private function get_all_existing_images_html() {
			$output = '<div class="smfi-img-container clearfix">';

				// Get saved image data.
				$isSingle = false;
				$saved_img_data = get_post_meta( get_the_ID(), $this -> db_meta_key, $isSingle );
				$has_img_data = is_array( $saved_img_data ) && ! empty( $saved_img_data );

				// If image data was found then try to show it.
				if( $has_img_data ) {

					// Validate image received image data
					if( $this -> validator -> is_valid_img_data( $saved_img_data ) ) {

						// Show images by using the retrieved imaga data.
						$number_of_images = count( $saved_img_data );
						for( $i = 0; $i < $number_of_images; $i++ ) {

							// Get the image id.
							$img_id = $saved_img_data[$i]['img_id'];

							// Get image wrapper html.
							$img_wrapper_html = $this -> get_image_wrapper_html( $img_id );

							// Show image.
							if( ! empty( $img_wrapper_html ) ) {
								$output .=  $img_wrapper_html;
							} else {
								// TODO: A hint to the user that no proper image html generated. Maybe it was deleted via media library.
							}
						}
					} else {
						// TODO: A hint to the user that invalid image data was found inside the database.
					}
				}

			$output .=  '</div>';
			return $output;
		}

		/**
		 * Returns the image wrapper tag including the image tag, the remove button tag and the hidden input tag with the image ID.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The image id.
		 * @return string The HTML or empty string on failure.
		*/
		private function get_image_wrapper_html( $img_id ) {

			$output = '';

			// Get image html.
			$img_html = $this -> get_image_html( $img_id );

			// Get image wrapper html.
			if( ! empty( $img_html ) ) {
				$output .=  '<div class="smfi-img-wrapper">';
					$output .=  $img_html;
					$output .= $this -> get_remove_btn_html();
					$output .= $this -> get_hidden_input_for_img_id_html( $img_id );
				$output .=  '</div>';
			}
			return $output;
		}

		/**
		 * Returns the image tag for a single image.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The image id.
		 * @return string The HTML.
		*/
		private function get_image_html( $img_id ) {
			// Get alternate text of image.
			$img_alternate_txt = get_post_meta(
				$img_id,
				'_wp_attachment_image_alt',
				true /* is single*/
			);

			// Create image html attributes.
			$img_html_attributes = array('class' => 'smfi-img');
			if( ! empty( $img_alternate_txt ) ) {
				$img_html_attributes['alt'] = esc_attr( $img_alternate_txt );
			}

			// Get image html.
			return wp_get_attachment_image(
				$img_id,
				'thumbnail',
				false /* threat as icon */,
				$img_html_attributes
			);
		}

		/**
		 * Returns the HTML for the button which will be used to remove an existing featured image.
		 *
		 * It will be a link because the default featured image metabox of wordpress use a link for removing instead of <button>.
		 * To keep the appereance consistent, we also use a link instead of a button (which would make more sense).
		 * The HTML will be just returned and not outputed!
		 *
		 * @since 1.0.0
		 *
		 * @return string The HTML.
		*/
		private function get_remove_btn_html() {
			$title = esc_attr__( 'Remove image', 'simple-multiple-featured-images' ) . '">' . esc_html__( 'Remove image', 'simple-multiple-featured-images' );
			return '<a href="#" class="smfi-remove-img-btn" title="' . $title . '</a>';
		}

		/**
		 * Returns the HTML for the hidden input which will be used to transmit the image id to the client.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The image id.
		 * @return string The HTML.
		*/
		private function get_hidden_input_for_img_id_html( $img_id ) {
			return '<input name="' . $this -> hidden_input_name_img_ids . '[]" type="hidden" value="' . esc_attr( $img_id ) . '">';
		}
	}

	// Initialize plugin.
	$simple_multiple_featured_images = new Simple_Multiple_Featured_Images( new SMFI_Validator(), new SMFI_Nonce_Manager() );
	$simple_multiple_featured_images -> init();
}
?>
