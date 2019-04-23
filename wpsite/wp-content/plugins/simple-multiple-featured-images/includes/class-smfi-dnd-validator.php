<?php
/**
 * SMFI_DND_Validator class
 *
 * This file specifies the SMFI_DND_Addon_Validator class which allows to validate different kind of image data.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_DND_Validator' ) ) {
	
	/**
	 * Allows to validate different kind of image data.
	 *
	 * @since 1.0.0
	 */
	class SMFI_DND_Validator {
		
		/**
		 * Validates given image positions.
		 *
		 * @since 1.0.0 
		 *
		 * @param array $img_positions Image positions as integer.
		 * @return bool True if positions are valid else false.
		 */
		public function are_valid_img_positions( $img_positions = array() ) {
			if( is_array( $img_positions ) && ! empty( $img_positions ) ) {
				foreach( $img_positions as $position ) {
					$validation_result = false;
					if( is_array( $position ) ) { 
						if( sizeof($position) === 1 ) {
							// The value is wrapped inside an array.
							$validation_result = $this -> is_valid_img_position( intval( $position[0], 10 ) );
						} else {
							return false;
						}
					} else {
						// We got the single value, just validate the value.
						$validation_result = $this -> is_valid_img_position( intval( $position, 10 ) );
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
		 * Validates given image position.
		 *
		 * @since 1.0.0 
		 *
		 * @param int $img_position Image position.
		 * @return bool True if position is valid else false.
		 */
		public function is_valid_img_position( $img_position = -1 ) {
			if( is_integer( $img_position ) && $img_position >= 0 ) {
				return true;
			}
			return false;
		}
	}
}
?>