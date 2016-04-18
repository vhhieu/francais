<?php
/**
 * Shortcode using for frontend.
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
 * FC_Shortcode Class.
 */
class FC_Shortcode {
	
	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_shortcode( 'foobar', array('FC_Shortcode', 'shortcode') );
	}
	
	public static function shortcode( $atts, $content = "" ) {
		
		$html = "<select class='selectsearch'>";
		$html .= "<option>TEST 1</option>";
		$html .= "<option>TEST 2</option>";
		$html .= "</select>";
		return $html;
	}
}

FC_Shortcode::init();