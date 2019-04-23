<?php
/**
 * SMFI_Public_API class
 *
 * This file specifies the SMFI_Public_API class which allows external programs to interact with the plugin.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_Public_API' ) ) {
	
	/**
	 * Allows external programs to interact with the plugin.
	 *
	 * @since 1.0.0
	 */
	class SMFI_Public_API {
		
		/**
		 * Main class of plugin simple multiple featured images plugin.
		 *
		 * @since 1.0.0
		 * @var Simple_Multiple_Featured_Images 
		 */
		private $simple_multiple_featured_images;
		
		/**
		 * Constructor.
		 *
		 * @since 1.0.0 
		 *
		 * @param SMFI_Validator  $validator Used to validate img data in different places.
		 * @param SMFI_Nonce_Manager  $nonce_manager Used in context of nonces.
		 */
		public function __construct( $simple_multiple_featured_images ) {
			$this -> simple_multiple_featured_images = $simple_multiple_featured_images;
		}
		
		/**
		 * Returns image tags of all featured images for the given post ID.
		 *
		 * @since 1.0.0
		 *
		 * @param int $post_id The ID of the post.
		 * @param string|array $img_size Optional The wanted image size. Accepts any valid wordpress image size, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
		 * @param string|array $attributes Optional Attributes for the image markup.
		 * @return array All image tags or empty array.
		 */
		public function get_all_featured_images_tags( $post_id, $img_size = 'thumbnail', $attributes = '' ) {

			$tags = array();
		
			if ( ! is_numeric( $post_id ) ) {
				return $tags;
			}
			
			$post_id = abs( intval( $post_id ) );
			if ( ! $post_id ) {
				return $tags;
			}
			
			$saved_img_data = get_post_meta( $post_id, $this -> simple_multiple_featured_images -> get_db_meta_key() , false );
			$has_img_data = is_array( $saved_img_data ) && ! empty( $saved_img_data );
			
			if($has_img_data) {
				foreach( $saved_img_data as $img_data ) {
					
					// Get the image id.
					$img_id = $img_data['img_id'];
						
					// Get image html.
					$img_html = wp_get_attachment_image( $img_id, $img_size, false /* threat as icon */, $attributes );
					
					if( ! empty( $img_html ) ) {
						array_push( $tags, $img_html );
					}
				}
			}
			return $tags;
		}
			
		/**
		 * Returns image tag of the given image attachment.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The ID of the image attachment.
		 * @param string|array $img_size Optional The wanted image size. Accepts any valid wordpress image size, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
		 * @param string|array $attributes Optional Attributes for the image markup.
		 * @return string Image tag or empty string.
		 */
		public function get_featured_image_tag( $img_id, $img_size = 'thumbnail', $attributes = '' ) {

			$tag = '';
		
			if ( ! is_numeric( $img_id ) ) {
				return $tag;
			}
			
			$img_id = abs( intval( $img_id ) );
			if ( ! $img_id ) {
				return $tag;
			}
					
			// Get image html.
			$img_html = wp_get_attachment_image( $img_id, $img_size, false /* threat as icon */, $attributes );
			
			if( ! empty( $img_html ) ) {
				$tag = $img_html;
			}
			return $tag;
		}
		
		/**
		 * Returns all featured image attachment IDs for the given post.
		 *
		 * @since 1.0.0
		 *
		 * @param int $post_id The ID of post.
		 * @return array The image attachment IDs as strings
		 */
		public function get_all_featured_image_ids( $post_id ) {
			
			if ( ! is_numeric( $post_id ) ) {
				return array();
			}
			
			$img_id = abs( intval( $post_id ) );
			if ( ! $img_id ) {
				return array();
			}
			
			$saved_img_data = get_post_meta( $post_id, $this -> simple_multiple_featured_images -> get_db_meta_key() , false );
			$has_img_data = is_array( $saved_img_data ) && ! empty( $saved_img_data );
			if( $has_img_data ) {
				return array_column( $saved_img_data, 'img_id' );
			} else {
				return array();
			}
		}
		
		/**
		 * Returns the image url.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The ID of the image attachment.
		 * @param string|array $img_size Optional The wanted image size. Accepts any valid wordpress image size, or an array of width and height values in pixels (in that order). Default 'thumbnail'.
		 * @return string The image url or empty string on failure.
		 */
		public function get_image_url_by_id( $img_id, $img_size  = 'thumbnail' ) {
			
			if ( ! is_numeric( $img_id ) ) {
				return '';
			}
			
			$img_id = abs( intval( $img_id ) );
			if ( ! $img_id ) {
				return '';
			}
			
			$url = wp_get_attachment_image_url( $img_id, $img_size, false /* threat as icon */ );
			if( $url !== false) {
				return $url;
			} else {
				return '';
			}
		}
		
		/**
		 * Returns the image title.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The ID of the image attachment.
		 * @return string The image title or empty string on failure.
		 */
		public function get_image_title_by_id( $img_id ) {
			
			if ( ! is_numeric( $img_id ) ) {
				return '';
			}
			
			$img_id = abs( intval( $img_id ) );
			if ( ! $img_id ) {
				return '';
			}
			
			$img_attachment = get_post( $img_id, OBJECT, 'raw' );
			
			if( ! is_null( $img_attachment ) ) {
				return $img_attachment -> post_title;
			} else {
				return '';
			}
		}

		/**
		 * Returns the image alternate text.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The ID of the image attachment.
		 * @return string The image alternate text or empty string on failure.
		 */
		public function get_image_alt_by_id( $img_id ) {
			
			if ( ! is_numeric( $img_id ) ) {
				return '';
			}
			
			$img_id = abs( intval( $img_id ) );
			if ( ! $img_id ) {
				return '';
			}
			
			return get_post_meta( $img_id, '_wp_attachment_image_alt', true );
		}
		
		/**
		 * Returns the image caption.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The ID of the image attachment.
		 * @return string The image caption or empty string on failure.
		 */
		public function get_image_caption_by_id( $img_id ) {
			
			if ( ! is_numeric( $img_id ) ) {
				return '';
			}
			
			$img_id = abs( intval( $img_id ) );
			if ( ! $img_id ) {
				return '';
			}
			
			$img_attachment = get_post( $img_id, OBJECT, 'raw' );
			
			if( ! is_null( $img_attachment ) ) {
				return $img_attachment -> post_excerpt;
			} else {
				return '';
			}
		}
		
		/**
		 * Returns the image description.
		 *
		 * @since 1.0.0
		 *
		 * @param int $img_id The ID of the image attachment.
		 * @return string The image description or empty string on failure.
		 */
		public function get_image_description_by_id( $img_id ) {
			
			if ( ! is_numeric( $img_id ) ) {
				return '';
			}
			
			$img_id = abs( intval( $img_id ) );
			if ( ! $img_id ) {
				return '';
			}
			
			$img_attachment = get_post( $img_id, OBJECT, 'raw' );
			
			if( ! is_null( $img_attachment ) ) {
				return $img_attachment -> post_content;
			} else {
				return '';
			}
		}
		
		/**
		 * Checks if the current post uses the SMFI featured images.
		 *
		 * @since 1.0.0
		 *
		 * @return bool True if post uses the SMFI featured images else false.
		*/		
		public function is_smfi_showed() {
			return $this -> simple_multiple_featured_images -> is_supported_post();
		}
	}
}
?>