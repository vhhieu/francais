<?php
/**
 * Plugin Name: Le Club Francais
 * Plugin URI: https://github.com/vhhieu/francais
 * Description: Francais is private use for Le Club Francais website for course publishing purpose.
 * Version: 1.0.0
 * Author: hieuvh
 * Author URI: https://github.com/vhhieu
 * Requires at least: 4.1
 * Tested up to: 4.3
 *
 * Text Domain: francais
 * Domain Path: /i18n/languages/
 *
 * @package francais
 * @category Core
 * @author hieuvh
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Francais' ) ) :
/**
 * Main Francais Class.
 *
 * @class Francais
 * @version	1.0.0
 */
final class Francais {
	/**
	 * Francais version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';
	
	/**
	 * The single instance of the class.
	 *
	 * @var instance
	 * 
	 * 
	 */
	protected static $_instance = null;
	
	/**
	 * Main Francais Instance.
	 *
	 * Ensures only one instance of Francais is loaded or can be loaded.
	 *
	 * @since 2.1
	 * @static
	 * @see FC()
	 * @return Francais - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Francais default constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}
	
	/**
	 * Define FC Constants.
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir();
	
		$this->define( 'FC_PLUGIN_FILE', __FILE__ );
		$this->define( 'FC_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
		$this->define( 'FC_PLUGIN_URL', plugin_dir_url( __FILE__ ));
		$this->define( 'FC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'FC_VERSION', $this->version );
		$this->define( 'FRANCAIS_VERSION', $this->version );
		$this->define( 'WC_LOG_DIR', $upload_dir['basedir'] . '/fc-logs/' );
		
		// Course Mode
		global $COURSE_MODE;
		$COURSE_MODE = array(
			1 => "Early Bird",
			2 => "Last Call",
		);
		
		global $COURSE_TRIAL;
		$COURSE_TRIAL = array(
				0 => "Non",
				1 => "Oui",
		);
		
		global $COURSE_TYPE;
		$COURSE_TYPE = array(
				"Annuel" => "Annuel",
				"Trimestriel" => "Trimestriel",
				"Stage journée" => "Stage journée",
				"Stage WE" => "Stage WE",
		);
		
		global $MARCO_DISCIPLINE;
		$MARCO_DISCIPLINE = array(
				"danse" => "Danse",
				"theatre" => "Théâtre",
		);
		
		global $MICRO_DISCIPLINE;
		$MICRO_DISCIPLINE = array(
				"danse" => array("Clip dance", "Danse classique", "Danse de couple", "Hip-Hop", "Salsa", "Swing"),
				"theatre" => array("Théâtre"),
		);
		
		global $AGE_GROUP;
		$AGE_GROUP = array(
				"enfants" => "Enfants",
				"ado" => "Ados",
				"adultes" => "Adultes",
				"seniors" => "Seniors",
		);
		
		global $CITY_LIST;
		$CITY_LIST = array(
			"paris" => "Paris",
			"lyon" => "Lyon",
			"marseille" => "Marseille",
			"toulouse" => "Toulouse",
			"strasbourg" => "Strasbourg",
			"bordeaux" => "Bordeaux",
			"nantes" => "Nantes",
			"lille" => "Lille",
			"montpellier" => "Montpellier",
			"rennes" => "Rennes",
		);
	}
	
	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		include_once( 'includes/class-fc-install.php' );
		include_once( 'includes/class-fc-uninstall.php' );
		include_once( 'includes/class-fc-shortcode.php' );
		
		if ( $this->is_request( 'admin' ) ) {
			include_once( 'includes/admin/class-fc-admin.php' );
		}
	
		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}
	}
	
	/**
	 * Frontend include
	 */
	public function frontend_includes() {
		include_once( 'includes/class-fc-frontend.php' );
	}
	
	/**
	 * Initialize hooks
	 */
	public function init_hooks() {
		register_activation_hook( __FILE__, array( 'FC_Install', 'install' ) );
		register_deactivation_hook(__FILE__, array( 'FC_UnInstall', 'uninstall' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_email_classes', array($this, "add_payment_complete_email") );
	}
	
	public function add_payment_complete_email($email_classes) {
		// include our custom email class
		require_once (FC_PLUGIN_PATH . "includes/class-wc-payment-complete-email.php");
	
		$email_classes['WC_Payment_Complete_Email'] = new WC_Payment_Complete_Email();
	
		return $email_classes;
	}
	
	/**
	 * Init Francais when WordPress Initialises.
	 */
	public function init() {
		ob_start();
	}
	
	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) );
		}
	}
	
	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
}
endif;

/**
 * Main instance of Francais.
 *
 * Returns the main instance of FC to prevent the need to use globals.
 *
 * @since  2.1
 * @return Francais
 */
function FC() {
	return Francais::instance();
}

// Global for backwards compatibility.
$GLOBALS['francais'] = FC();