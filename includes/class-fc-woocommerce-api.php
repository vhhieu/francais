<?php
/**
 * Woo Commerce API Integration client
 *
 * @author   hieuvh
 * @category Admin
 * @package  Francais/Classes
 * @version  2.4.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FC_Product_Api' ) ) :
/**
 * Main Francais Class.
 *
 * @class Francais
 * @version	1.0.0
 */
class FC_Product_Api {
	
	private $wc_client;
	
	/**
	 * Francais default constructor.
	 */
	public function __construct() {
		require_once( FC_PLUGIN_PATH  . 'lib/woocommerce-api.php' );
		$options = array(
				'ssl_verify'      => false,
		);
		
		try {
			$this->wc_client = new WC_API_Client( home_url(), $consumer_key, $consumer_secret, $options );
		} catch ( WC_API_Client_Exception $e ) {
		
			echo $e->getMessage() . PHP_EOL;
			echo $e->getCode() . PHP_EOL;
		
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
		
				print_r( $e->get_request() );
				print_r( $e->get_response() );
			}
		}
	}
	
	public function add_product($course_id) {
		$params = array(
			'title' => $course->title,
			'type' => 'simple',
			'regular_price' => $course->price,
			'description' => $course->description
		);
		$wc_client->products->create($params);
	}
}
endif;