<?php
/**
 * Frontend config using for frontend.
 *
 * @author   hieuvh
 * @category Frontend
 * @package  Francais/Classes
 * @version  2.4.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * FC_Frontend Class.
 */
class FC_Frontend {
	
	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array ("FC_Frontend", 'register_style'));
		add_action( 'wp_enqueue_scripts', array ("FC_Frontend", 'using_style'));
	}
	
	public static function register_style() {
		wp_register_style( "custom_wp_css", FC_PLUGIN_URL . "assets/css/style.css");
	}
	
	public static function using_style() {
		wp_enqueue_style( 'custom_wp_css' );
	}
}

FC_Frontend::init();