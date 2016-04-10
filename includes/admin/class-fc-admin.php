<?php
/**
 * Francais Admin Class
 *
 * @author   hieuvh
 * @category Admin
 * @package  Francais/Admin
 * @version  1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'FC_Admin' ) ) :
/**
 * FC_Admin.
*/
class FC_Admin {
	public function init() {
		add_action( 'admin_menu', array( $this, 'francais_add_menu' ) );
	}
	
	public function francais_add_menu() {
		add_menu_page( 'Menu de cours', 'Menu de cours', 'manage_options', 'francais-course',
				array(__CLASS__, "init_menu_francais"),
		        plugins_url('../../assets/images/dancing_logo.png', __FILE__),'2.2.9');
		
		// MENU 1 - COURSE
		add_submenu_page( 'francais-course', 'Course List', 'Course List', 'manage_options', "francais-course", 
				array(__CLASS__, "init_menu_francais_course_list"), 1);
		
		$suffix = add_submenu_page( 'francais-course', 'Créer un cours', 'Créer un cours', 'manage_options', "francais-course-add",
				array(__CLASS__, "init_menu_francais_course_add"), 1);
		
		add_submenu_page( 'francais-course', 'Edit cours', 'Edit cours', 'manage_options', "francais-course-edit",
				array(__CLASS__, "init_menu_francais_course_edit"), 2);
		
		// MENU 2 - LIEU
		add_submenu_page( 'francais-course', 'Lieu List', 'Lieu List', 'manage_options', "francais-lieu",
				array(__CLASS__, "init_menu_francais_lieu_list"), 2);
		
		add_submenu_page( "francais-course", 'Add Lieu', 'Add Lieu', 'manage_options', "francais-lieu-add",
				array(__CLASS__, "init_page_francais_lieu_add"));
		
		add_submenu_page( "francais-course", 'Edit Lieu', 'Edit Lieu', 'manage_options', "francais-lieu-edit",
				array(__CLASS__, "init_page_francais_lieu_edit"));
		
		// MENU 3 - PROFS
		add_submenu_page( 'francais-course', 'Profs List', 'Profs List', 'manage_options', "francais-profs",
				array(__CLASS__, "init_menu_francais_profs_list"), 3);
		
		add_submenu_page( 'francais-course', 'Add Profs', 'Add Profs', 'manage_options', "francais-profs-add",
				array(__CLASS__, "init_page_francais_profs_add"));
		
		add_submenu_page( 'francais-course', 'Edit Profs', 'Edit Profs', 'manage_options', "francais-profs-edit",
				array(__CLASS__, "init_page_francais_profs_edit"));
		
		// MENU 4 - DISCIPLINE
		add_submenu_page( 'francais-course', 'Formule de cours', 'Formule de cours', 'manage_options', "francais-discipline",
				array(__CLASS__, "init_menu_francais_discipline_list"), 4);
		
		add_submenu_page( 'francais-course', 'Add Formule de cours', 'Add Formule de cours', 'manage_options', "francais-discipline-add",
				array(__CLASS__, "init_page_francais_discipline_add"));
		
		add_submenu_page( 'francais-course', 'Edit Formule de cours', 'Edit Formule de cours', 'manage_options', "francais-discipline-edit",
				array(__CLASS__, "init_page_francais_discipline_edit"));
		
		add_action( 'admin_head', array( $this, 'remove_submenu' ));
		add_action( 'admin_head', array( $this, 'custom_style_lieu_list' ));
		add_action( 'admin_init', array ($this, 'register_bootstrap'));
		add_action( 'admin_enqueue_scripts', array ($this, 'using_bootstrap'));
	}
	
	function register_bootstrap() {
// 		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		
// 		if('francais-course-add' != $page) {
// 			return;
// 		}
		
		wp_register_style( "custom_wp_admin_css", FC_PLUGIN_URL . "assets/css/style-admin.css");
		wp_register_style('cs_js_time_style' , FC_PLUGIN_URL. 'assets/css/jquery-ui-timepicker-addon.css');
		wp_register_style('jquery_ui_spinner_css' , FC_PLUGIN_URL. 'assets/css/ui.spinner.css');
		wp_register_style('jquery_ui_spinner_js' , FC_PLUGIN_URL. 'assets/js/ui.spinner.js');
		//wp_register_script( "custom_wp_admin_js", FC_PLUGIN_URL . "assets/css/bootstrap.min.js");
	}
	
	function using_bootstrap() {
// 		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		
// 		if('francais-course-add' != $page) {
// 			return;
// 		}
		
		wp_enqueue_style( "custom_wp_admin_css" );
		wp_enqueue_style('fc_js_time_style');
		wp_enqueue_style('jquery_ui_spinner_css');
		wp_enqueue_script('jquery_ui_spinner_js');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
		wp_enqueue_script('jquery-script', 'http://code.jquery.com/ui/1.10.4/jquery-ui.js');
		wp_enqueue_script('jquery-time-picker' ,  FC_PLUGIN_URL. 'assets/js/jquery-ui-timepicker-addon.js',  array('jquery' ));
		
	}
	
	public function remove_submenu() {
		remove_submenu_page( 'francais-course', 'francais-course-add' );
		remove_submenu_page( 'francais-course', 'francais-course-edit' );
		remove_submenu_page( 'francais-course', 'francais-lieu-add' );
		remove_submenu_page( 'francais-course', 'francais-lieu-edit' );
		remove_submenu_page( 'francais-course', 'francais-profs-add' );
		remove_submenu_page( 'francais-course', 'francais-profs-edit' );
		remove_submenu_page( 'francais-course', 'francais-discipline-add' );
		remove_submenu_page( 'francais-course', 'francais-discipline-edit' );
	}
	
	/**
	 * Init Main Menu Content
	 */
	public function init_menu_francais() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-course-list.php");
	}
	
	/**
	 * Init Course List Menu Content
	 */
	public function init_menu_francais_course_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-course-list.php");
	}
	
	public function init_menu_francais_course_add() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-course-add.php");
	}
	
	public function init_menu_francais_course_edit() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-course-edit.php");
	}
	
	/**
	 * Init Lieu List Menu Content
	 */
	public function init_menu_francais_lieu_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-lieu-list.php");
	}
	
	public function custom_style_lieu_list() {
		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
	
		if( 'francais-lieu' != $page && 'francais-course' != $page && 'francais-discipline' != $page) {
			return;
		}
	
		echo '<style type="text/css">';
		echo '.wp-list-table .column-room_index { width: 20%; }';
		echo '.wp-list-table .column-discipline_index { width: 25%; }';
		echo '</style>';
	}
	
	/**
	 * Init Lieu Add Menu Content
	 */
	public function init_page_francais_lieu_add() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-lieu-add.php");
	}
	
	/**
	 * Init Lieu Edit Menu Content
	 */
	public function init_page_francais_lieu_edit() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-lieu-edit.php");
	}
	
	/**
	 * Init Profs Menu Content
	 */
	public function init_menu_francais_profs_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-profs-list.php");
	}
	
	/**
	 * Init Profs Add Menu Content
	 */
	public function init_page_francais_profs_add() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-profs-add.php");
	}
	
	/**
	 * Init Profs Edit Menu Content
	 */
	public function init_page_francais_profs_edit() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-profs-edit.php");
	}
	
	/**
	 * Init discipline Menu Content
	 */
	public function init_menu_francais_discipline_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-discipline-list.php");
	}
	
	/**
	 * Init Discipline Add Menu Content
	 */
	public function init_page_francais_discipline_add() {
// 		if ( !current_user_can( 'manage_options' ) )  {
// 			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
// 		}
		include_once("views/html-admin-page-francais-discipline-add.php");
	}
	
	/**
	 * Init Profs Edit Menu Content
	 */
	public function init_page_francais_discipline_edit() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-discipline-edit.php");
	}
}
endif;

(new FC_Admin())->init();