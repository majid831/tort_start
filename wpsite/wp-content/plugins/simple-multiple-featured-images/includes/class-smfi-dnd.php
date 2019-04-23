<?php
/**
 * SMFI_DND class
 *
 * This file specifies the SMFI_DND class which adds drag and drop support for the featured images.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_DND' ) ) {
	
	/**
	 * Import validator which allows to validate image data.
	 */
	require_once plugin_dir_path( __FILE__ ) . 'class-smfi-dnd-validator.php';
	
	/**
	 * Adds drag and drop support to the simple multiple featured images plugin. 
	 *
	 * Allows to change the position of the images via drag and drop. 
	 *
	 * @since 1.0.0
	 */
	class SMFI_DND {
		
		/**
		 * Helper which is used to validate img data in different places.
		 *
		 * @since 1.0.0
		 * @var SMFI_DND_Validator 
		 */
		private $validator;
		
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
		 * @param SMFI_DND_Validator  $validator Used to validate img data in different places.
		 */
		public function __construct( $smfi_plugin ) {
			$this -> validator = new SMFI_DND_Validator();
			$this -> smfi_plugin = $smfi_plugin;
		}
		
		/**
		 * Initialize the drag and drop support.
		 *
		 * This have to be called to start the plugin. It adds all necessary scripts, actions and hooks.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			
			// Enqueue all necessary CSS and JS into the admin screen.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			
			/*
			* Hook into the saving of the images and change the order of the image ids before they are saved
			* into the database. The image ids are ordered according to the positions which were resulted
			* from the drag and drop operations.
			*/
			add_filter( 'smfi_img_ids_before_save', array( $this, 'sort_image_ids_by_its_position' ) );
			
			// Add own admin notices for error handling.
			add_action( 'admin_notices', array( $this, 'add_admin_notices' ) );
		}
		
		/**
		 * Enqueue admin scripts.
		 *
		 * Enqueue all necessary CSS and javascript files. If the current post type is not supported by this plugin then no files will be enqueued.
		 *
		 * @since 1.0.0 
		 */
		public function enqueue_admin_scripts() {
			
			// Add the drag and drop CSS and JS only if the images are showed on the current screen.
			if( ! is_null( $this -> smfi_plugin ) ) {
				if( $this -> smfi_plugin -> get_public_api() -> is_smfi_showed() ) {
					// Add plugin specific css.
					SMFI_CSS_Importer::import_css( 'smfi_dnd_css', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/smfi-dnd-style.css' );

					// Add plugin specific js.
					SMFI_JS_Importer::import_js(
						'smfi_dnd_js', 
						plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/jquery.smfi-dnd.js', 
						false /*use minified version*/ 
					);
				}
			}
		}
		
		/**
		 * Sorts the given image IDs according to its positions which were changed via drag and drop.
		 *
		 * @since 1.0.0
		 *
		 * @param array $img_ids All image IDs.
		 * @return array Sorted image IDs.
		 */
		public function sort_image_ids_by_its_position( $img_ids ) {

			try {
				
				if( empty( $img_ids ) ) {
					return $img_ids;
				}
				
				// Get the transmitted image positions.
				$received_img_positions = $_POST[ 'smfi-img-pos' ];
				if( ! $this -> validator -> are_valid_img_positions( $received_img_positions ) ) {
					throw new Exception( "Sorting failed because of an invalid image positions." );
				}

				if( count( $received_img_positions ) !== count( $img_ids ) ) {
					throw new Exception( "Sorting failed because of different number of image ids and image positions." );
				}
	
				/*
				* Combine image ids and their positions into one array. The positions are used
				* as the keys and the image ids as values.
				*/
				$combinedArray = array_combine ( $received_img_positions, $img_ids );
				
				// Sort the img ids by its positions.
				ksort( $combinedArray );
				
				// Return the sorted image ids.
				$result = array_values( $combinedArray );
				if( count( $result ) !== count( $img_ids ) ) {
					//throw new Exception( "Sorting failed for some reason." );
				}
				return $result;
				
			} catch ( Exception $exception ) {
				
				// Use the redirect_post_location hook to attach an error code to the url.
				add_filter( 'redirect_post_location', array( $this, 'attach_save_error_to_url' ) );
				
				/*
				* Just return the origin image ids. We do not want to crash the SMFI plugin.
				*/ 
				return $img_ids;
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
		 * @param string $location URL.
		 * @return string URL
		 */
		public function attach_save_error_to_url( $location ) {
			
			// We need this filter only once (during save_post hook). So remove this filter immediately.
			remove_filter( 'redirect_post_location', array( $this, 'attach_user_save_error_to_url' ), 99 );
			
			// Attach save error as parameter to the url.
			$key = 'smfi-dnd-error';
			$value = 'sort-failed';
			return add_query_arg( $key , $value , $location );
		}
		
		/**
		 * Add custom admin notices that report errors to the user in the main notice area.
		 *
		 * @since 1.0.0
		 *
		 */
		public function add_admin_notices() {
			
			// Check url for error parameter and show an error message to the user if found.
			if ( array_key_exists( 'smfi-dnd-error', $_GET ) ) {
				echo '<div class="notice notice-error is-dismissible">';
					echo '<p>';
						switch($_GET['smfi-dnd-error']) {
							case 'sort-failed':
								$saveErrorMsg = esc_html__( 'The featured images could not be saved in the correct order.', 'simple-multiple-featured-images' );
								echo '	Simple Multiple Featured Images Plugin: ' . 
										$saveErrorMsg . ' ' . $this -> smfi_plugin -> get_default_error_instruction();
								break;
							default:
								echo $this -> smfi_plugin -> get_default_error_message() . ' ' . $this -> smfi_plugin -> get_default_error_instruction();
								break;
						}
					echo '</p>';
				echo '</div>';
			}
		}
	}
}
?>