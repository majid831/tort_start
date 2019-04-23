<?php
/**
 * SMFI_Nonce_Manager class
 *
 * This file specifies the SMFI_Nonce_Manager class which implements some functionality in context of nonces.
 * Some nonce related code is outsourced into this file.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'SMFI_Nonce_Manager' ) ) {
	
	/**
	 * Provides some functionality in context of nonces.
	 *
	 * @since 1.0.0
	 */
	class SMFI_Nonce_Manager {
		
		/**
		 * Name of the nonce action which is used for exchanging an image via ajax.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		const NONCE_ACTION_IMG_CHANGE = 'smfi_change_img_action';
		
		/**
		 * Name of the nonce which is used for exchanging an image via ajax.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		const NONCE_NAME_IMG_CHANGE = 'smfi_change_img_security';
		
		/**
		 * Name of the nonce action which is used for adding a new image via ajax.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		const NONCE_ACTION_IMG_ADD = 'smfi_add_img_action';
		
		/**
		 * Name of the nonce which is used for adding a new image via ajax.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		const NONCE_NAME_IMG_ADD = 'smfi_add_img_security';
		
		/**
		 * Name of the nonce action which is used for the metabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		const NONCE_ACTION_METABOX = 'smfi_metabox_action';
		
		/**
		 * Name of the nonce which is used for the metabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		const NONCE_NAME_METABOX = 'smfi_metabox_security';
		
	}
}
?>