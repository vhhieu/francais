<?php
/**
 * UnInstallation related functions and actions
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
class FC_UnInstall {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		//add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
	}

	public static function uninstall_actions() {
		
	}

	/**
	 * Show notice stating update was successful.
	 */
	public static function uninstall_notice() {
		?>
		<div id="message" class="updated">
			<p><?php _e( 'Francais UnInstall complete.', 'francais' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Un_Install FC.
	 */
	public static function uninstall() {
// 		global $wpdb;
// 		$table_prefix = $wpdb->prefix . 'francais_';
// 		$table_name = $table_prefix . "course";
// 		$sql = "DROP TABLE " . $table_name;
// 		$result = $wpdb->query($sql);
// 		if ($result) {
// 			add_action( 'admin_notices', array( __CLASS__, 'uninstall_notice' ) );
// 		}
	}
}

FC_UnInstall::init();
