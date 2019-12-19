<?php

if ( !defined( 'ABSPATH' ) ) {
	die();
}

require_once( 'newsletter_for_bloom-email-providers-wrapper.class.php' );

class Nlplugin_ET_Core_API_Email_Provider_Loader {
	private static $instance;

	public static function instantiate() {
		if ( self::$instance === null ) {
			self::$instance = new Nlplugin_ET_Core_API_Email_Provider_Loader();
		}

		return self::$instance;
	}
	public function __construct() {
		add_filter( 'et_core_get_third_party_components', array (
			$this,
			'third_party_components_filter'
		), 10, 2 );
		add_action( 'after_setup_theme', array (
			$this,
			'wrap_providers'
		), 12 );
	}

	public static function upgrade($current_db_version) {
		// No-op
	}

	public static function uninstall($wpdb) {
		// No-op
	}

	public function third_party_components_filter($components, $groups) {
		if ( ( 'api/email' === $groups || empty( $groups ) || ( is_array( $groups ) && in_array( 'api/email', $groups ) ) ) && class_exists( '\\ET_Core_API_Email_Provider' ) ) {
			require_once( 'newsletter_for_bloom-email-provider.class.php' );
			$components[Nlplugin_Plugin::get_branding()] = new Nlplugin_ET_Core_API_Email_Provider();
		}
		return $components;
	}

	public function wrap_providers() {
		// Bloom plugin support
		if ( isset( $GLOBALS['et_bloom'] ) ) {
			$GLOBALS['et_bloom']->providers = new Nlplugin_ET_Core_API_Email_Providers_Wrapper( $GLOBALS['et_bloom']->providers );
		}

		// Core API support
		if ( class_exists( '\\ET_Core_API_Email_Fields' ) && class_exists( '\\Closure' ) ) {
			$closure = function() {
				self::$_instance = new Nlplugin_ET_Core_API_Email_Providers_Wrapper( self::$_instance );
			};
			$bound = $closure->bindTo( null, '\\ET_Core_API_Email_Providers' );
			$bound();
		}
	}
}
