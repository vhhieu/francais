<?php
/**
 * Plugin Name: francais
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
		$this->define( 'FC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'FC_VERSION', $this->version );
		$this->define( 'FRANCAIS_VERSION', $this->version );
		$this->define( 'WC_LOG_DIR', $upload_dir['basedir'] . '/fc-logs/' );
	}
	
	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		include_once( 'includes/class-fc-install.php' );
		
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
		
	}
	
	/**
	 * Initialize hooks
	 */
	public function init_hooks() {
		register_activation_hook( __FILE__, array( 'FC_Install', 'install' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
	}
	
	/**
	 * Init Francais when WordPress Initialises.
	 */
	public function init() {
		
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