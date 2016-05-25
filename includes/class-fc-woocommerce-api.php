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
	
	public $wc_client;
	
	/**
	 * Francais default constructor.
	 */
	public function __construct() {
		require_once( FC_PLUGIN_PATH  . 'lib/woocommerce-api.php' );
		$options = array(
			'debug'           => true,
			'return_as_array' => false,
			'validate_url'    => false,
			'timeout'         => 30,
			'ssl_verify'      => false,
		);
		
		// local nha
		$consumer_key = 'ck_970edc23e76c2a83cd0c359ecea0f203ba8fe8f9';
		$consumer_secret = 'cs_62eed9eacb484623edecf17bfcf754d9c523b9e1';
		
		// local cty
		$consumer_key = 'ck_74c91fc15f3800c3e759ea7585da63bdb7e27ee8';
		$consumer_secret = 'cs_e624b0bf4fa28fbc6643ac47f6289b0a585ff331';
		
		// product
 		$consumer_key = 'ck_c094b06f63b257484e55371affe86e89931cd3f0';
 		$consumer_secret = 'cs_5947968a4c901cc66fe18c4638c04118c4c132b2';
		
 		$url = home_url();
 		//$url = "http://leclubfrancais.fr/";
		try {
			$this->wc_client = new WC_API_Client( $url, $consumer_key, $consumer_secret, $options );
		} catch ( WC_API_Client_Exception $e ) {
		
			echo $e->getMessage() . PHP_EOL;
			echo $e->getCode() . PHP_EOL;
		
			if ( $e instanceof WC_API_Client_HTTP_Exception ) {
		
				print_r( $e->get_request() );
				print_r( $e->get_response() );
			}
		}
	}
	
	public function add_or_update_product($course_id) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT c.course_id, c.number_available, c.product_id,
					d.short_description, d.discipline_description, d.price, d.application_fee,
					po.post_title AS title
				FROM {$prefix}francais_course c
				LEFT JOIN {$prefix}francais_discipline d USING(discipline_id)
				LEFT JOIN {$wpdb->prefix}posts po ON c.post_id = po.ID
		WHERE c.course_id = %d\n";
		$sql = $wpdb->prepare($sql, $course_id);
		
		$course = $wpdb->get_row( $sql );
		//wp_die(print_r($course));
		if (!$course) {
			return;
		}
		
		$price = $course->price + $course->application_fee;
		$params = array(
			'title' => $course->title,
			'type' => 'simple',
			'regular_price' => $course->price,
			'short_description' => $course->short_description,
			'description' => $course->discipline_description,
			'regular_price' => $price,
			'managing_stock' => true,
			'stock_quantity' => $course->number_available
		);
		$product_id = $course->product_id;
		
		$result = '';
		if ($product_id) {
			$result = $this->wc_client->products->update( $product_id, $params );
		}
		
		if (empty($result)) {
			$result = $this->wc_client->products->create($params);
			$product_id = $result->product->id;
		}
		return $product_id;
	}
}
endif;