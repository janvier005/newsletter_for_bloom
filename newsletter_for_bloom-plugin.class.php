<?php

if ( !defined( 'ABSPATH' ) ) {
	die();
}

class Nlplugin_Plugin {
	private $wpdb;

	private $api;

	private $publicAppProperties = array();


	private $ajax_url;

	private static $version = '0.1';

	private static $textdomain = 'newsletter_for_bloom-com';

	private static $branding = 'Newsletter';
	private static $menu_branding = 'Newsletter';
	private static $branding_domain = 'newsletter_for_bloom.com';

	private static $instance = null;

	public static function instantiate($wpdb = null) {
		if ( self::$instance === null ) {
			self::$instance = new self( $wpdb );

			// Initialize Integrations
			Nlplugin_Divi::instantiate();
		}
		return self::$instance;
	}

	public static function get_version() {
		return self::$version;
	}

	public static function get_textdomain() {
		return self::$textdomain;
	}

	public static function get_branding() {
		return self::$branding;
	}

	public static function get_menu_branding() {
		return self::$menu_branding;
	}

	public static function get_branding_domain() {
		return self::$branding_domain;
	}

	public function get_wpdb() {
		return $this->wpdb;
	}

	public function get_ajax_url() {
		return $this->ajax_url;
	}

	public function get_api() {
		return $this->api;
	}

}
