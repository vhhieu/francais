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
		//$this->object = $order; 
		
// 		// woohoo, send the email!
//  		$to_email = $this->object["billing_email"];
//  		$first_name = $this->object["billing_first_name"];
		$this->send( 'vhhieu@gmail.com' , $this->get_subject(), "TEST CONTENT " . $order->status . " XX " . $order->billing_email, $this->get_headers(), $this->get_attachments() );
	}
	

	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		woocommerce_get_template( $this->template_html, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading()
		) );
		$text = ob_get_clean();
		
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
		woocommerce_get_template( $this->template_plain, array(
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