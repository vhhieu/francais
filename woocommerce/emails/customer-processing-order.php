<?php
/**
 * Customer processing order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-processing-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function find_product( $product_id ) {
	global $wpdb;
	$table_prefix = $wpdb->prefix . "francais_";
	$sql = "SELECT
	c.course_id, c.start_date, c.start_time, c.end_date, c.number_available, c.course_mode, c.trial_mode,
	CONCAT(p.first_name, ' ', p.family_name) prof_name,
	CONCAT(r.room_name, ', ', r.address, ', ', r.zip_code) room_info, r.city room_city,
	d.course_type, d.macro_discipline, d.age_group, d.micro_discipline, d.short_description, d.lesson_duration
	FROM {$table_prefix}course c
	LEFT JOIN {$table_prefix}discipline d USING (discipline_id)
	LEFT JOIN {$table_prefix}room r USING (room_id)
	LEFT JOIN {$table_prefix}profs p USING (profs_id)
	WHERE c.product_id = %d";

	$sql = $wpdb->prepare($sql, $product_id);
	$result = $wpdb->get_row($sql);
	return $result;
}

function get_cities_list() {
	global $wpdb;
	$result = array();
	$arr = get_terms('city', array('taxonomy' => 'city', 'hide_empty' => false));
	foreach ($arr as $city) {
		$result[$city->slug] = $city->name;
	}
	return $result;
}

function get_micro_discipline_list() {
	global $wpdb;
	$result = array();
	$arr = get_terms('discipline', array('taxonomy' => 'discipline', 'hide_empty' => false));
	foreach ($arr as $micro_discipline) {
		$result[$micro_discipline->slug] = $micro_discipline->name;
	}
	return $result;
}
/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php 
// START CUSTOM CODE
$first_name = $order->billing_first_name;
$cities = get_cities_list();
$micro_list = get_micro_discipline_list();
$AGE_GROUP = array(
	"enfants" => "Enfants",
	"ado" => "Ados",
	"adultes" => "Adultes",
	"seniors" => "Seniors",
);
$items = $order->get_items();
foreach ($items as $item) {
	$product_id = $item['product_id'];
		
	$course = find_product($product_id);// find course
		
	if ($course !== NULL) {
		setlocale(LC_TIME, get_locale());
		$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
		$to_time = $from_time + $course->lesson_duration * 60;
		$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();

		$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
		$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
		$start_date_str = strftime("%d %b. %Y", $start_date);
		$day_of_week = strftime("%A", $start_date);

		$content = "Bonjour {FIRST_NAME},<br/>
					<br/>
					Ravi de vous accueillir chez nous au Club Français !<br/>
					<br/>
					Nous vous confirmons quelques détails pratiques pour vos cours de {MICRO_DISCIPLINE} pour {AGE_GROUP} :<br/>
					- Jour de la semaine : tous les {DAY_OF_WEEK}<br/>
					- Lieu : {ROOM_INFO}<br/>
					- Horaires : de {START_TIME} à {END_TIME}<br/>
					<br/>
					On espère vraiment que vous allez vous amuser et faire rapidement de jolis progrès avec votre professeur {PROF_NAME} :-)<br/>
					<br/>
					A bientôt,<br/>
					Célestine, du service client";
		$content = str_replace("{FIRST_NAME}", $first_name, $content);
		$content = str_replace("{ROOM_INFO}", $course->room_info . ", " . $cities[$course->room_city], $content);
		$content = str_replace("{PROF_NAME}", $course->prof_name, $content);
		$content = str_replace("{MICRO_DISCIPLINE}", $micro_list[$course->micro_discipline], $content);
		$content = str_replace("{AGE_GROUP}", $AGE_GROUP[$course->age_group], $content);
		$content = str_replace("{DAY_OF_WEEK}", $day_of_week, $content);
		$content = str_replace("{START_TIME}", $from_time_str, $content);
		$content = str_replace("{END_TIME}", $to_time_str, $content);
	}
}
// END CUSTOM CODE
?>
<p><?= $content ?></p>
<p><?php _e( "Your order has been received and is now being processed. Your order details are shown below for your reference:", 'woocommerce' ); ?></p>

<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );