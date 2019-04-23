<?php
/**
 * SMFI_Validator class
 *
 * This file specifies the SMFI_Validator class which allows to validate different kind of data.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_Validator' ) ) {
	
	/**
	 * Allows to validate different kind of data.
	 *
	 * @since 1.0.0
	 */
	class SMFI_Validator {
		
		/**
		 * Validates given image data.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $img_data {
		 *     @type int $img_id  image ID
		 * }
		 * @return bool True if data is valid else false.
		 */
		public function is_valid_img_data( $img_data = array() ) {
			if( is_array( $img_data ) ) {
				foreach( $img_data as $data ) {
					if( ! $this -> is_valid_img_id( intval( $data['img_id'], 10 ) ) ) {
						return false;
					}
				}
				return true;
			}
			return false;
		}
		
		/**
		 * Validates given image ids.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $img_ids Image ids as integer.
		 * @return bool True if ids are valid else false.
		 */
		public function are_valid_img_ids( $img_ids = array() ) {
			if( is_array( $img_ids ) ) {
				foreach( $img_ids as $id ) {
					$validation_result = false;
					if( is_array( $id ) ) { 
						if( sizeof($id) === 1 ) {
							// The value is wrapped inside an array.
							$validation_result = $this -> is_valid_img_id( intval( $id[0], 10 ) );
						} else {
							return false;
						}
					} else {
						// We got the single value, just validate the value.
						$validation_result = $this -> is_valid_img_id( intval( $id, 10 ) );
					}
					
					if( ! $validation_result ) {
						return false;
					}
				}
				return true;
			}
			return false;
		}
		
		/**
		 * Validates given image id.
		 *
		 * @since 1.0.0 
		 *
		 * @param int $img_id Image id.
		 * @return bool True if id is valid else false.
		 */
		public function is_valid_img_id( $img_id = -1 ) {
			if( is_integer( $img_id ) && $img_id > 0 ) {
				return true;
			}
			return false;
		}
	}
}
?>