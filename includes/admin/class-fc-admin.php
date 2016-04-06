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
		add_menu_page( 'Francais Plugin', 'Francais', 'manage_options', 'francais',
				array(__CLASS__, "init_menu_francais"),
		        plugins_url('../../assests/images/dancing_logo.png', __FILE__),'2.2.9');
		
		// MENU 1 - COURSE
		add_submenu_page( 'francais', 'Course List', 'Course List', 'manage_options', "francais", 
				array(__CLASS__, "init_menu_francais_course_list"), 1);
		
		// MENU 2 - LIEU
		add_submenu_page( 'francais', 'Lieu List', 'Lieu List', 'manage_options', "francais-lieu",
				array(__CLASS__, "init_menu_francais_lieu_list"), 2);
		
		add_submenu_page( "francais-lieu", 'Add Lieu', 'Add Lieu', 'manage_options', "francais-lieu-add",
				array(__CLASS__, "init_page_francais_lieu_add"));
		
		add_submenu_page( "francais-lieu", 'Edit Lieu', 'Edit Lieu', 'manage_options', "francais-lieu-edit",
				array(__CLASS__, "init_page_francais_lieu_edit"));
		
		// MENU 3 - PROFS
		add_submenu_page( 'francais', 'Profs List', 'Profs List', 'manage_options', "francais-profs",
				array(__CLASS__, "init_menu_francais_profs_list"), 3);
		
		add_submenu_page( 'francais-profs', 'Add Profs', 'Add Profs', 'manage_options', "francais-profs-add",
				array(__CLASS__, "init_page_francais_profs_add"));
		
		add_submenu_page( 'francais-profs', 'Edit Profs', 'Edit Profs', 'manage_options', "francais-profs-edit",
				array(__CLASS__, "init_page_francais_profs_edit"));
		
		// MENU 4 - DISCIPLINE
		add_submenu_page( 'francais', 'Formule de cours', 'Formule de cours', 'manage_options', "francais-discipline",
				array(__CLASS__, "init_menu_francais_discipline_list"), 4);
		
		add_submenu_page( 'francais-discipline', 'Add Formule de cours', 'Add Formule de cours', 'manage_options', "francais-discipline-add",
				array(__CLASS__, "init_page_francais_discipline_add"));
		
		add_submenu_page( 'francais-discipline', 'Edit Formule de cours', 'Edit Formule de cours', 'manage_options', "francais-discipline-edit",
				array(__CLASS__, "init_page_francais_discipline_edit"));
		
// 		remove_submenu_page( 'francais', 'francais-discipline-add' );
// 		remove_submenu_page( 'francais', 'francais-discipline-edit' );
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
	
	/**
	 * Init Lieu List Menu Content
	 */
	public function init_menu_francais_lieu_list() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include_once("views/html-admin-page-francais-lieu-list.php");
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
}
endif;

(new FC_Admin())->init();