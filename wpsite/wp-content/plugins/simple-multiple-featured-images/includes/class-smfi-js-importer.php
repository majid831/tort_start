<?php
/**
 * SMFI_JS_Importer class
 *
 * This file specifies the SMFI_JS_Importer class which allows to import JS.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_JS_Importer' ) ) {
	
	/**
	  * Allows to import JS.
	 *
	 * @since 1.0.0
	 */
	class SMFI_Js_Importer {
		
		/**
		 * Imports a javascript by given path.
		 *
		 * If use minimized version flag is set then a minimized version of the file will be searched and used. 
		 * If no minimized version was found then the file specified by the path is used. It is possible to deliver
		 * translated strings to client by specifying the name of the translation object and an array of the translated strings. 
		 *
		 * @since 1.0.0 
		 *
		 * @param string $unique_file_identifier The uneque identifier which should be used for the file.
		 * @param string $path Path to the file.
		 * @param bool Optional. $use_minimized_version If true then use minimized version.
		 * @param string Optional. $translation_object_name Name of the translation object.
		 * @param array Optional. $translation_array Translation strings.
		 */
		public static function import_js( $unique_file_identifier = "", $path = "", $use_minimized_version = false, $translation_object_name = '', $translation_array = array() ) {
			
			//TODO Add dependency parameter.
			
			/*
			 * Check if sub path should be change the minimized version.
			 * Will only be minimized if flag is set and the given sub path does not contain already a minimized version.
			 */
			$calculated_path = $path;
			if($use_minimized_version &&  ! preg_match('/.min.js$/', $path)) {
				// We have to change the given sub path to a minimized js version.
				$pattern = '/.js$/';
				$replacement = '.min.js';
				$limit = 1;
				$newPath = preg_replace($pattern, $replacement, $path, $limit);
				if(isset($newPath)) {
					$calculated_path = $newPath;
				}
			}

			// Enqueue script.
			wp_enqueue_script( $unique_file_identifier,  $calculated_path, array('jquery'), null, true );
			
			// Inject translations into the script if available.
			if(is_string( $translation_object_name ) && ! empty( $translation_object_name ) && is_array( $translation_array ) && ! empty( $translation_array ) ) {
				wp_localize_script( $unique_file_identifier, $translation_object_name, $translation_array );
			}
		}
	}
}
?>