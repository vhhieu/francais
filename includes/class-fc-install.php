<?php
/**
 * Installation related functions and actions
 *
 * @author   hieuvh
 * @category Admin
 * @package  Francais/Classes
 * @version  2.4.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FC_Install Class.
 */
class FC_Install {

	/** @var array DB updates that need to be run */
	private static $db_updates = array(
		'1.0.0' => 'updates/francais-update-1.0.php',
	);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	/**
	 * Check Francais version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'francais_version' ) !== FC()->version ) {
			self::install();
// 			do_action( 'francais_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_francais'] ) ) {
			self::update();
			add_action( 'admin_notices', array( __CLASS__, 'updated_notice' ) );
		}
	}

	/**
	 * Show notice stating update was successful.
	 */
	public static function updated_notice() {
		?>
		<div id="message" class="updated">
			<p><?php _e( 'Francais data update complete. Thank you for updating to the latest version!', 'francais' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Install FC.
	 */
	public static function install() {
		global $wpdb;

		if ( ! defined( 'FC_INSTALLING' ) ) {
			define( 'FC_INSTALLING', true );
		}

		self::create_options();
		self::create_tables();

		// Also register endpoints - this needs to be done prior to rewrite rule flush
		self::create_files();

		// Queue upgrades/setup wizard
		$current_fc_version    = get_option( 'francais_version', null );
		$current_db_version    = get_option( 'francais_db_version', null );
		
		if ($current_fc_version !== FC()->version) {
			// TODO: do something
		}
		
		if ($current_db_version !== FC()->version) {
			// TODO: do something
		}
		
		FC_Install::update_fc_version();
		FC_Install::update_db_version();
	}

	/**
	 * Update FC version to current.
	 */
	private static function update_fc_version() {
		delete_option( 'francais_version' );
		add_option( 'francais_version', FC()->version );
	}

	/**
	 * Update DB version to current.
	 */
	private static function update_db_version( $version = null ) {
		delete_option( 'francais_db_version' );
		add_option( 'francais_db_version', is_null( $version ) ? FC()->version : $version );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {
		// Include settings so that we can run through defaults
		include_once( 'admin/settings/class-fc-admin-settings.php' );

// 		$options = FC_Admin_Settings::get_options();

// 		foreach ( $options as $option ) {
// 			// do nothing
// 		}
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		francais_course --> Francais course
	 *      francais_room --> Francais room
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();
		$tableprefix = $wpdb->prefix . 'francais_';
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
 		
		// COURSE table
 		$coursetable = $tableprefix . 'course';
 		$sql = "CREATE TABLE " . $coursetable . " (
            course_id bigint(20) NOT NULL AUTO_INCREMENT,
            course_name varchar(256) NOT NULL,
            PRIMARY KEY  (course_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		// ROOM table
 		$roomtable = $tableprefix . 'room';
 		$sql = "CREATE TABLE " . $roomtable . " (
            room_id bigint(20) NOT NULL AUTO_INCREMENT,
            country varchar(64) NOT NULL,
			city varchar(64) NOT NULL,
			zip_code char(5) NOT NULL,
			room_name varchar(128) NOT NULL,
			address varchar(256),
			address_detail varchar(256),
			max_number int(10) NOT NULL DEFAULT 0,
			area_m2 int(10) NOT NULL DEFAULT 0,
			room_description text,
			room_manager_name varchar(128) NOT NULL,
			room_manager_email varchar(64) NOT NULL,
			room_manager_tel varchar(16) NOT NULL,
			UNIQUE KEY room_unique (country, city, zip_code),
            PRIMARY KEY  (room_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
	}

	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Install files and folders for uploading files and prevent hotlinking
		$upload_dir      = wp_upload_dir();
		$download_method = get_option( 'francais_file_download_method', 'force' );

		$files = array(
			array(
				'base' 		=> $upload_dir['basedir'] . '/francais_uploads',
				'file' 		=> 'index.html',
				'content' 	=> ''
			),
			array(
				'base' 		=> FC_LOG_DIR,
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all'
			),
			array(
				'base' 		=> FC_LOG_DIR,
				'file' 		=> 'index.html',
				'content' 	=> ''
			)
		);

		if ( 'redirect' !== $download_method ) {
			$files[] = array(
				'base' 		=> $upload_dir['basedir'] . '/francais_uploads',
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all'
			);
		}

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

}

FC_Install::init();
