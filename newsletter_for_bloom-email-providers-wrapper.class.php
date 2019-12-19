<?php

if ( !defined( 'ABSPATH' ) ) {
	die();
}

class Nlplugin_ET_Core_API_Email_Providers_Wrapper {
	private $providers = null;

	public function __construct($providers) {
		$this->providers = $providers;
	}

	public function __call($name, $args) {
		if ( $this->providers !== null ) {
			$result = call_user_func_array( array (
				$this->providers,
				$name
			), $args );
			if ( $name === 'get' && count( $args ) > 1 && $args[1] !== '' ) {
				// The new ET_Core_API_Email_Providers class does not handle this, so we will until they fix that bug.
				$result->set_account_name( $args[1] );
			}
			return $result;
		}
	}
}
