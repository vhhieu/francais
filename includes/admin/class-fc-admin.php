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
		add_action( 'parent_file', array( $this, 'correct_menu_position' ) );
	}
	
	public function correct_menu_position() {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		global $parent_file, $submenu_file;
		if ($taxonomy == 'city' || $taxonomy == 'discipline') {
			$parent_file = 'francais-course';
		}
		
		if (is_admin()) {
			$page = $_GET['page'];
			if ($page === 'francais-course-add' || $page === 'francais-course-edit') {
				$parent_file = "francais-course";
				$submenu_file = "francais-course";
			} else if ($page === 'francais-lieu-add' || $page === 'francais-lieu-edit') {
				$parent_file = "francais-course";
				$submenu_file = "francais-lieu";
			} else if ($page === 'francais-profs-add' || $page === 'francais-profs-edit') {
				$parent_file = "francais-course";
				$submenu_file = "francais-profs";
			} else if ($page === 'francais-discipline-add' || $page === 'francais-discipline-edit') {
				$parent_file = "francais-course";
				$submenu_file = "francais-discipline";
			} else if ($page === 'francais-category-add' || $page === 'francais-category-edit') {
				$parent_file = "francais-course";
				$submenu_file = "francais-category";
			} else if ($page === 'francais-client-review-add' || $page === 'francais-client-review-edit') {
				$parent_file = "francais-course";
				$submenu_file = "francais-client-review";
			}
		}
		
		return $parent_file;
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
		
		// MENU 4 - Formule de cours
		add_submenu_page( 'francais-course', 'Formule de cours', 'Formule de cours', 'manage_options', "francais-discipline",
				array(__CLASS__, "init_menu_francais_discipline_list"), 4);
		
		add_submenu_page( 'francais-course', 'Add Formule de cours', 'Add Formule de cours', 'manage_options', "francais-discipline-add",
				array(__CLASS__, "init_page_francais_discipline_add"));
		
		add_submenu_page( 'francais-course', 'Edit Formule de cours', 'Edit Formule de cours', 'manage_options', "francais-discipline-edit",
				array(__CLASS__, "init_page_francais_discipline_edit"));
		
		// MENU 5 - Cities
		add_submenu_page( 'francais-course', 'Villes', 'Villes', 'manage_options', "edit-tags.php?taxonomy=city", null, 5);
		
		// MENU 6 - Micro Discipline
		add_submenu_page( 'francais-course', 'Micro Discipline', 'Micro Discipline', 'manage_options', "edit-tags.php?taxonomy=discipline", null, 6);
		
		// MENU 7 - Categories
		add_submenu_page( 'francais-course', 'Categories', 'Course Categories', 'manage_options', "francais-category",
				array(__CLASS__, "init_menu_francais_category_list"), 7);
		add_submenu_page( 'francais-course', 'Add Category', 'Add Category', 'manage_options', "francais-category-add",
				array(__CLASS__, "init_menu_francais_category_add"));
		add_submenu_page( 'francais-course', 'Edit Category', 'Edit Category', 'manage_options', "francais-category-edit",
				array(__CLASS__, "init_menu_francais_category_edit"));
		
		// MENU 8 - Trial
		add_submenu_page( 'francais-course', "Séance d'essai", "Séance d'essai", 'manage_options', "seance-essai",
				array(__CLASS__, "init_menu_francais_seance_essai"), 8);
		
		// MENU 9 - Client Review
		add_submenu_page( 'francais-course', "Client Review", "Client Review", 'manage_options', "francais-client-review",
				array(__CLASS__, "init_menu_francais_client_review_list"), 8);
		add_submenu_page( 'francais-course', "Add Client Review", "Add Client Review", 'manage_options', "francais-client-review-add",
				array(__CLASS__, "init_menu_francais_client_review_add"), 8);
		
		add_submenu_page( 'francais-course', "Edit Client Review", "Edit Client Review", 'manage_options', "francais-client-review-edit",
				array(__CLASS__, "init_menu_francais_client_review_edit"), 8);
	
		// MENU 10 - Subscriber List
		add_submenu_page( 'francais-course', "Subscriber List", "Subscriber List", 'manage_options', "francais-subscriber",
				array(__CLASS__, "init_menu_francais_subscriber_list"), 8);
		
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
		
		wp_enqueue_style( 'custom_wp_admin_css' );
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
		remove_submenu_page( 'francais-course', 'francais-category-add' );
		remove_submenu_page( 'francais-course', 'francais-category-edit' );
		remove_submenu_page( 'francais-course', 'francais-client-review-add' );
		remove_submenu_page( 'francais-course', 'francais-client-review-edit' );
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
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
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
	
	/**
	 * Init Categories List Menu Content
	 */
	public function init_menu_francais_category_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-category-list.php");
	}
	
	/**
	 * Init Categories Add Menu Content
	 */
	public function init_menu_francais_category_add() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-category-add.php");
	}
	
	/**
	 * Init Categories Edit Menu Content
	 */
	public function init_menu_francais_category_edit() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-category-edit.php");
	}
	
	public function init_menu_francais_seance_essai() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-seance-essai-list.php");
	}
	
	/**
	 * Init Client Review List Menu Content
	 */
	public function init_menu_francais_client_review_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-client-review-list.php");
	}
	
	/**
	 * Init Client Review Add Menu Content
	 */
	public function init_menu_francais_client_review_add() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-client-review-add.php");
	}
	
	/**
	 * Init Client Review Edit Menu Content
	 */
	public function init_menu_francais_client_review_edit() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-client-review-edit.php");
	}
	
	/**
	 * Init Subscriber List
	 */
	public function init_menu_francais_subscriber_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-subscriber-list.php");
	}
}
endif;

(new FC_Admin())->init();