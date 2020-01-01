<?php

/*
 * Plugin Name: Bloom extension for newsletter
 * Plugin URI: https://www.simonjanvier.com/
 * Description: Make Bloom work with Newsletter plugin.
 * Version: 0.1
 * Author: Simon JANVIER
 * Author URI: https://www.simonjanvier.com/
 Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 Text Domain: newsletter_for_bloom
 License: GPLv2 or later

 Copyright 2019 Simon JANVIER (email: simon@simonjanvier.com, web: https://www.simonjanvier.com)

 Bloom extension for newsletter is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 2 of the License, or
 any later version.

 Bloom extension for newsletter is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Newsletter. If not, see https://www.gnu.org/licenses/gpl-2.0.html.

*/

if( !function_exists('get_plugin_data') ){
  require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

nlplugin_activate();

if ( !defined( 'ABSPATH' ) ) {
	die();
}

 require_once( 'newsletter_for_bloom-plugin.class.php' );
 require_once( 'newsletter_for_bloom-email-provider-loader.class.php' );

 Nlplugin_Divi::instantiate();

 class Nlplugin_Divi {
 	private static $instance = null;

 	public static function instantiate() {
 		if ( self::$instance === null ) {
 			self::$instance = new self();
 		}
 		return self::$instance;
 	}

 	/**
 	 * Class constructor.
 	 */
 	private function __construct() {
 		Nlplugin_ET_Core_API_Email_Provider_Loader::instantiate();

 		add_action( 'admin_init', array( $this, 'on_admin_init' ) );
 		add_action( 'init', array( $this, 'on_init' ) );
 	}

 	public static function upgrade($current_db_version) {
 		Nlplugin_ET_Core_API_Email_Provider_Loader::upgrade( $current_db_version );
 	}

 	public static function uninstall($wpdb) {
 		Nlplugin_ET_Core_API_Email_Provider_Loader::uninstall( $wpdb );
 	}

 	public function on_admin_init() {
 		add_action( 'wp_ajax_' . Nlplugin_Plugin::get_branding() . '_divi_admin_main_css', array (
 			$this,
 			'do_ajax_admin_main_css'
 		) );
 	}

 	public function on_init() {
 		if ( is_admin() ) {
 			$this->init_admin();
 		}
 	}

 	public function init_admin() {
 		wp_enqueue_style( Nlplugin_Plugin::get_branding() . '_divi_admin_main', add_query_arg( array( 'action' => Nlplugin_Plugin::get_branding() . '_divi_admin_main_css', 'ver' => Nlplugin_Plugin::get_version() ), admin_url( 'admin-ajax.php' ) ) );
 	}

 	public function do_ajax_admin_main_css() {
 		header( 'Content-Type: text/css; charset=utf-8' );

 		require( 'admin/css/main.css.php' );

 		exit();
 	}
 }

 function nlplugin_activate( $network_wide = null ) {
   //Bloom
   $bloom_exists = nlplugin_existing_checker('bloom/bloom.php','1.3.10');

   //The Newsletter Plugin
   $newsletter_exists = nlplugin_existing_checker('newsletter/plugin.php','6.4.0');

   //Check activations
   $bloom_activated = nlplugin_activation_checker('bloom/bloom.php');
   $newsletter_activated = nlplugin_activation_checker('newsletter/plugin.php',true);

   if( $bloom_exists === true && $newsletter_exists === true && $bloom_activated === true && $newsletter_activated === true){
    //let work
   }else{
    add_action( 'admin_init', 'deactivate_plugin_now' );
    add_action( 'admin_notices', 'nlplugin_check_activation_notice' );
   }
 }

 function nlplugin_existing_checker($plugin_path,$version_to_check){
   //replace this with your dependent plugin - $category_ext = 'categories-for-anspress/categories-for-anspress.php';

   // replace this with your version - $version_to_check = '1.3.5';

   $category_error = false;

   if(file_exists(WP_PLUGIN_DIR.'/'.$plugin_path)){
      $plugin = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin_path);
      $category_error = !version_compare ( $plugin['Version'], $version_to_check, '>=') ? true : false;
      if ( $category_error ) {
        return error_return('The version of '.$plugin['Name'].' plugin is too old, please update it before activating Newsletter for Bloom plugin.');
      }
   }else{
     return error_return('The plugin '.$plugin['Name'].' is not installed, please install it before activating Newsletter for Bloom plugin.');
   }

   return true;

 }

function nlplugin_activation_checker($plugin_path,$last = false){
  $status = is_plugin_active($plugin_path);
  $plugin = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin_path);
  if($status === false){
   return error_return('The plugin '.$plugin['Name'].' is not activated, please activate it before activating Newsletter for Bloom plugin.',$last);
  }
  return true;
}

function error_return($message,$last = false){
  if (strpos(get_transient( 'nlplugin-notice-panel' ), $message) === false) {
    set_transient( 'nlplugin-notice-panel', get_transient( 'nlplugin-notice-panel' ).'<p>'.$message.'</p>', 5 );
  }
  return false;

}

function nlplugin_check_activation_notice(){
  echo'<div id="message" class="notice notice-error">
  <h2>Bloom extension for newsletter</h2>
      '.get_transient( 'nlplugin-notice-panel' ).'
  </div>
  <style type="text/css">div.updated.notice.is-dismissible{display:none;}.notice p{display:block;line-height:1.2;}</style>';
  delete_transient( 'nlplugin-notice-panel' );
}

function deactivate_plugin_now(){
  deactivate_plugins( plugin_basename( __FILE__ ) );
}

