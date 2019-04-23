<?php
/**
 * SMFI_CSS_Importer class
 *
 * This file specifies the SMFI_CSS_Importer class which allows to import CSS.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_CSS_Importer' ) ) {
	
	/**
	 * Allows to import CSS.
	 *
	 * @since 1.0.0
	 */
	class SMFI_CSS_Importer {
		
		/**
		 * Imports a CSS by given path.
		 *
		 * If use minimized version flag is set then a minimized version of the file will be searched and used. 
		 * If no minimized version was found then the file specified by the path is used.
		 *
		 * @since 1.0.0 
		 *
		 * @param string $unique_file_identifier The uneque identifier which should be used for the file.
		 * @param string $path Path to the file.
		 * @param bool Optional. $use_minimized_version If true then use minimized version.
		 */
		public static function import_css( $unique_file_identifier = "", $path = "", $use_minimized_version = false ) {
			/*
			 * Check if sub path should be change the minimized version.
			 * Will only be minimized if flag is set and the given sub path does not contain already a minimized version.
			 */
			$calculated_path = $path;
			if($use_minimized_version &&  ! preg_match('/.min.css$/', $path)) {
				// We have change the given sub path to a minimized js version.
				$pattern = '/.css$/';
				$replacement = '.min.css';
				$limit = 1;
				$newPath = preg_replace( $pattern, $replacement, $path, $limit );
				if(isset($newPath)) {
					$calculated_path = $newPath;
				}
			}
			
			// Enqueue style.
			wp_enqueue_style( $unique_file_identifier, $calculated_path );
		}
	}
}
?>