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
if ( ! class_exists( 'FC_Frontend' ) ) :
class FC_Frontend {
	
	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array ("FC_Frontend", "register_style"));
		add_action( 'wp_enqueue_scripts', array ("FC_Frontend", "using_style"));
		add_filter( 'wp_nav_menu_items', array("FC_Frontend", "your_custom_menu_item"), 10, 2 );
		add_action('template_redirect', array ("FC_Frontend", "check_for_event_submissions"));
// 		add_action('woocommerce_payment_complete', array ("FC_Frontend", 'custom_process_order'), 10, 1);
	}
	
// 	public static function custom_process_order($order_id) {
// 		$order = wc_get_order( $order_id );
// 		$myuser_id = (int) $order->user_id;
// 		$user_info = get_userdata($myuser_id);
// 		$items = $order->get_items();
// 		foreach ($items as $item) {
// 			$product_id = $item['product_id'];
// 			FC_Frontend::send_complete_email($product_id);
// 		}
// 	}
	
// 	public static function send_complete_email($product_id) {
// 		// do nothing
// 	}
	
	public static function check_for_event_submissions () {
		include_once ( FC_PLUGIN_PATH . 'lib/EmailAddressValidator.php');
		
		if (isset($_POST['event']) && $_POST['event']==='course_category') {
			include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
			//$cities = FC_Util::get_cities_list();
			$city = $_POST['city'];
			$discipline = $_POST['dis'];
			$macro_discipline = "danse";
			if (!empty($discipline)) {
				$macro_discipline = FC_Util::get_macro_discipline($discipline);
			}
			$age_group = $_POST['age'];
			
			$url = FC_Frontend::build_category_url($macro_discipline, $age_group, $discipline, $city);
			
			wp_redirect($url);
			die();
		}
		if (isset($_POST['promo_event']) && $_POST['promo_event']==='promo_email') {
			if ((new EmailAddressValidator())->check_email_address($_POST['client_email'])) {
				$mailer = WC()->mailer();
				$subject = "20€ de réduction offerts";
				$code = md5($_POST['client_email'] . microtime());
				$code = substr($code, strlen($code) - 6, 6);
				$coupon_data = array(
						'code' => $code,
						'discount_type' => 'fixed_cart',
						'amount' => 20,
						'individual_use' => true,
						'exclude_sale_items' => true,
						'usage_limit' => 1,
						'email_restrictions' => array($_POST['client_email'])
				);
				include_once ( FC_PLUGIN_PATH  . 'includes/class-fc-woocommerce-api.php' );
				$api = new FC_Product_Api();
				$api->wc_client->coupons->create($coupon_data);
				$content = "Bonjour {$_POST['client_name']},<br/>
				<br/>
				Ravi de vous accueillir chez nous au Club Français !<br/>
				<br/>
				Pour profiter des 20€ de réduction offerts sur vos cours de danse ou de théâtre, veuillez entre le code <b>{$code}</b> lors de la confirmation de votre commande. La réduction se fera automatiquement :-)<br/>
				<br/>
				On espère vraiment que vous allez vous amuser et faire rapidement de jolis progrès au Club Français !<br/>
				<br/>
				A bientôt,<br/>
				Célestine, du service client";
				$mailer->send($_POST['client_email'], $subject, $content, '', '');
			}

			wp_redirect(home_url());
			die();
		}
		if (isset($_POST['subscriber_event']) && $_POST['subscriber_event']==='subscriber_email') {
			if ((new EmailAddressValidator())->check_email_address($_POST['subscriber_email'])) {
				global $wpdb;
				$_POST      = array_map('stripslashes_deep', $_POST);
				$result = $wpdb->insert(
				$wpdb->prefix . 'francais_subscriber', //table
				array(
						'subscriber_email' => $_POST['subscriber_email']
				), //data
				array('%s') //data format
				);
			}
			
			wp_redirect(home_url());
			die();
		}
	}
	
	public static function your_custom_menu_item ( $items, $args ) {
		if ($args->theme_location != 'onepage') {
			return $items;
		}
		
		$url = FC_Frontend::build_category_url("danse", "", "", "");
		$sub_citys = FC_Frontend::create_sub_menu($url, "danse");
		
		$items .= "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
				      <a href='{$url}'><span>COURS DE DANSE</span></a>
				      {$sub_citys}
				   </li>";
				      
		$url = FC_Frontend::build_category_url("theatre", "", "", "");
		$sub_citys = FC_Frontend::create_sub_menu($url, "theatre");
		$items .= "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
					<a href='{$url}'><span>COURS DE THÉÂTRE</span></a>
					{$sub_citys}
					</li>";
		
		return $items;
	}
	
	public static function create_sub_menu($url, $macro_discipline) {
		global $wpdb;
		$prefix = $wpdb->prefix . "francais";
		$sql = "SELECT DISTINCT (city) FROM {$prefix}_room WHERE room_id IN (
				  SELECT room_id FROM {$prefix}_course WHERE discipline_id IN (
				    SELECT discipline_id FROM {$prefix}_discipline WHERE macro_discipline = '{$macro_discipline}'
				  )
				);";
		$data = $wpdb->get_col ( $sql );
		if (empty($data)) {
			return "";
		}
		
		$subcity = "";
		foreach ($data as $city) {
			$url_sub = $url . "-" . strtolower($city);
			$sub_micro = FC_Frontend::create_sub_menu_micro($macro_discipline, $city);
			$value = strtoupper($city);
			$macro_discipline = strtoupper($macro_discipline);
			$subcity .= 
			   "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
					<a href='{$url_sub}'><span>COURS DE {$macro_discipline} {$value}</span></a>
					{$sub_micro}
			    </li>";
		}
		$result = "<ul class='sub-menu'>{$subcity}</ul>";
		return $result;
	}
	
	public static function create_sub_menu_micro($macro_discipline, $city) {
		global $wpdb;
		$macro_discipline = strtolower($macro_discipline);
		
		$prefix = $wpdb->prefix . "francais";
		$sql = "SELECT d.age_group, d.micro_discipline FROM {$prefix}_discipline d 
					INNER JOIN {$prefix}_course c USING(discipline_id)
					INNER JOIN {$prefix}_room r USING (room_id)
				WHERE d.macro_discipline = '{$macro_discipline}' AND r.city = '{$city}'
				GROUP BY d.age_group, d.micro_discipline
				ORDER BY d.age_group;";
	
		$data = $wpdb->get_results ( $sql );
		if (empty($data)) {
			return "";
		}
		
		include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
		$micro_list = FC_Util::get_micro_discipline_list();
		
		$city_val = strtoupper($city);
		$macro_discipline_val = strtoupper($macro_discipline);
		
		$sub_micro = "<li><b>COURS DE {$macro_discipline_val} {$city_val}</b></li><li></li>";
		$current_age_group = "";
		foreach ($data as $entity) {
			$value = strtoupper($entity->micro_discipline);
			global $AGE_GROUP;
			$age_group = strtoupper($entity->age_group);
			if ($age_group !== $current_age_group) {
				$url = FC_Frontend::build_category_url($macro_discipline, $entity->age_group, "", $city);
				$current_age_group = $age_group;
				$temp_age = strtoupper($AGE_GROUP[$entity->age_group]);
				$sub_micro .= "<li><a href='{$url}'><b>COURS {$temp_age}:</b></a></li>";
			}
			
			$url = FC_Frontend::build_category_url($macro_discipline, $entity->age_group, $entity->micro_discipline, $city);
			$sub_micro .=
				"<li class='menu-item menu-item-type-custom menu-item-object-custom'>
			    	<a href='{$url}'><span style='padding-left: 10px;'>COURS DE {$value}</span></a>
				</li>";
		}
		$result = "<ul class='sub-menu'>{$sub_micro}</ul>";
		return $result;
	}
	
	public static function build_category_url($macro_discipline, $age_group, $micro_discipline, $city) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT c.slug
				FROM {$prefix}francais_category c 
				WHERE c.macro_discipline = %s\n";
		$sql = $wpdb->prepare($sql, $macro_discipline);
		if (!empty($micro_discipline)) {
			$sql .= "AND c.micro_discipline = %s\n";
			$sql = $wpdb->prepare($sql, $micro_discipline);
		} else {
			$sql .= "AND (c.micro_discipline = '' OR c.micro_discipline IS NULL)\n";
		}
		
		if (!empty($age_group)) {
			$sql .= "AND c.age_group = %s";
			$sql = $wpdb->prepare($sql, $age_group);
		} else {
			$sql .= "AND c.age_group = ''\n";
		}
		
		$result = $wpdb->get_col( $sql );
		if (!empty($result)) {
			$result = $result[0];
			if (!empty($city)) {
				$result .= "-" . $city;
			}
		} else {
// 			wp_die($sql);
			return "#";
		}
		
		$result = home_url() . "/cours-de-" . $result;
		return $result;
	}
	
	public static function register_style() {
		wp_register_style( "custom_wp_css", FC_PLUGIN_URL . "assets/css/style.css");
		wp_register_style( "custom1_wp_css", plugins_url() . "/woocommerce/assets/css/woocommerce.css?ver=2.5.5");
		wp_register_style( "custom2_wp_css", plugins_url() . "/woocommerce/assets/css/woocommerce-layout.css?ver=2.5.5");
	}
	
	public static function using_style() {
		wp_enqueue_style( 'custom_wp_css' );
		wp_enqueue_style( 'custom1_wp_css' );
		wp_enqueue_style( 'custom2_wp_css' );
	}
}
endif;
FC_Frontend::init();