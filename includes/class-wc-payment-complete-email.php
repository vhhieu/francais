<?php
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

/**
 * A custom Expedited Order WooCommerce Email class
 *
 * @since 0.1
 *        @extends \WC_Email
 */
if (! class_exists ( 'WC_Payment_Complete_Email' )) :
class WC_Payment_Complete_Email extends WC_Email {
	public function __construct() {
		
		// set ID, this simply needs to be a unique name
		$this->id = 'wc_payment_complete_order';
		$this->customer_email = true;
		
		// this is the title in WooCommerce Email settings
		$this->title = 'Payment Complete Order';
		
		// this is the description in WooCommerce email settings
		$this->description = 'automatic send e-mailing to confirm when a course has been booked/bought';
		
		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Payment Complete Order';
		$this->subject = 'Payment Complete Order';
		
		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html = FC_PLUGIN_PATH . '/includes/emails/payment-complete-order.php';
		$this->template_plain = FC_PLUGIN_PATH . '/includes/emails/plain/payment-complete-order.php';
		
		// Trigger on new paid orders
		add_action ( 'woocommerce_order_status_pending_to_processing_notification', array (
				$this,
				'trigger' 
		) );
		add_action ( 'woocommerce_order_status_failed_to_processing_notification', array (
				$this,
				'trigger' 
		) );
		
		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct ();
		
		// this sets the recipient to the settings defined below in init_form_fields()
		$this->recipient = $this->get_option ( 'recipient' );
		
		// if none was entered, just use the WP admin email as a fallback
		if (! $this->recipient)
			$this->recipient = get_option ( 'admin_email' );
	}
	
	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 0.1
	 * @param int $order_id
	 */
	public function trigger( $order_id ) {
	
		// bail if no order ID is present
		if ( ! $order_id ) {
			return;
		}
	
		// setup order object
		$order = new WC_Order( intval($order_id) );
		 
// 		// woohoo, send the email!
  		$to_email = $order->billing_email;
  		
  		$first_name = $order->billing_first_name;
  		
  		$items = $order->get_items();
  		include_once(FC_PLUGIN_PATH . "/includes/admin/class-fc-util.php");
  		$cities = FC_Util::get_cities_list();
  		$micro_list = FC_Util::get_micro_discipline_list();
  		global $AGE_GROUP;
  		
  		foreach ($items as $item) {
  			 $product_id = $item['product_id'];
  			
  			$course = $this->find_product($product_id);// find course
			
  			if ($course !== NULL) {
  				setlocale(LC_TIME, get_locale());
  				$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
  				$to_time = $from_time + $course->lesson_duration * 60;
  				$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();
  				
  				$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
  				$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
  				$start_date_str = strftime("%d %b. %Y", $start_date);
  				$day_of_week = strftime("%A", $start_date);
  				
  				$content = $this->get_content_html();
  				$content = str_replace("{FIRST_NAME}", $first_name, $content);
  				$content = str_replace("{ROOM_INFO}", $course->room_info . ", " . $cities[$course->room_city], $content);
  				$content = str_replace("{PROF_NAME}", $course->prof_name, $content);
  				$content = str_replace("{MICRO_DISCIPLINE}", $micro_list[$course->micro_discipline], $content);
  				$content = str_replace("{AGE_GROUP}", $AGE_GROUP[$course->age_group], $content);
  				$content = str_replace("{DAY_OF_WEEK}", $day_of_week, $content);
  				$content = str_replace("{START_TIME}", $from_time_str, $content);
  				$content = str_replace("{END_TIME}", $to_time_str, $content);
  				
  				$this->send( $to_email , $this->get_subject(), $content , $this->get_headers(), $this->get_attachments() );
  			}
  		}
	}
	
	public function find_product( $product_id ) {
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
		//return $sql;
	}
	
	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		$text = file_get_contents($this->template_html);
		return $text;
	}
	
	
	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading()
		) );
		return ob_get_clean();
	}
	
	
	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 2.0
	 */
	public function init_form_fields() {
	
		$this->form_fields = array(
				'enabled'    => array(
						'title'   => 'Enable/Disable',
						'type'    => 'checkbox',
						'label'   => 'Enable this email notification',
						'default' => 'yes'
				),
				'recipient'  => array(
						'title'       => 'Recipient(s)',
						'type'        => 'text',
						'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
						'placeholder' => '',
						'default'     => ''
				),
				'subject'    => array(
						'title'       => 'Subject',
						'type'        => 'text',
						'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
						'placeholder' => '',
						'default'     => ''
				),
				'heading'    => array(
						'title'       => 'Email Heading',
						'type'        => 'text',
						'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
						'placeholder' => '',
						'default'     => ''
				),
				'email_type' => array(
						'title'       => 'Email type',
						'type'        => 'select',
						'description' => 'Choose which format of email to send.',
						'default'     => 'html',
						'class'       => 'email_type',
						'options'     => array(
								'plain'	    => __( 'Plain text', 'woocommerce' ),
								'html' 	    => __( 'HTML', 'woocommerce' ),
						)
				)
		);
	}
}

endif;