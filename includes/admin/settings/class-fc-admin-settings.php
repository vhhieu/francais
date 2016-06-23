<?php
/**
 * Francais Admin Settings Class
 *
 * @author   hieuvh
 * @category Admin
 * @package  Francais/Admin
 * @version  1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'FC_Admin_Settings' ) ) :
/**
 * FC_Admin_Settings.
 */
class FC_Admin_Settings {
	/**
	 * Options.
	 *
	 * @var array
	 */
	private static $options = array();
	
	/**
	 * Include the options page classes.
	 */
	public static function get_options() {
		if ( empty( self::$options ) ) {
			$options = array();
		}
	
		return self::$options;
	}
}
endif;