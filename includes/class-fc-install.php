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
		add_action( 'init', array( __CLASS__, 'create_post_type' ), 1 );
		add_action( 'init', array( __CLASS__, 'create_taxonomies' ), 1 );
		add_action( 'init', array( __CLASS__, 'create_danse_categories' ), 1 );
		add_action( 'init', array( __CLASS__, 'create_city_taxonomies' ), 2 );
		add_action( 'init', array( __CLASS__, 'create_discipline_taxonomies' ), 2 );
		add_action( 'init', array( __CLASS__, 'custom_rewrite_rule' ), 10, 0 );
		
		add_action( 'discipline_add_form_fields', array( __CLASS__, 'discipline_taxonomy_add_new_meta_field'), 10, 2 );
		add_action( 'discipline_edit_form_fields', array( __CLASS__, 'discipline_taxonomy_edit_meta_field'), 10, 2 );
		add_action( 'create_discipline', array( __CLASS__, 'save_macro_discipline'), 10, 2 );
		add_action( 'edited_discipline', array( __CLASS__, 'save_macro_discipline'), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'template_include', array( __CLASS__, 'taxonomy_template_include' ), 99 );
	}
	
	public static function taxonomy_template_include($template) {
		if (get_query_var("city")) {
			$_GET['city'] = get_query_var("city");
		}
		return $template;
	}
	
	public static function custom_query_vars( $vars ) {
		$vars[] = 'city';
		return $vars;
	}
	
	public static function custom_rewrite_rule() {
		// "cours-de-danse-classique-ados"
		add_rewrite_rule('^cours-de-danse/?$', 'index.php?course_cate=danse', 'top');
		add_rewrite_rule('^cours-de-theatre/?$', 'index.php?course_cate=theatre', 'top');
		$cities = get_terms('city', array('taxonomy' => 'city', 'hide_empty' => false));
		$city_regex = "(";
		$count = 0;
		foreach ($cities as $city) {
			$count++;
			add_rewrite_rule('cours-de-danse-' . $city->slug . '/?', 'index.php?course_cate=danse&city=' . $city->slug, 'top');
			add_rewrite_rule('cours-de-theatre-' . $city->slug . '/?', 'index.php?course_cate=theatre&city=' . $city->slug, 'top');
			$city_regex .= $city->slug;
			if ($count < sizeof($cities)) {
				$city_regex .= "|";
			}
		}
		$city_regex .= ")";
// 		wp_die($city_regex);
		add_rewrite_rule('cours-de-(.*)-' . $city_regex . '/?', 'index.php?course_cate=$matches[1]&city=$matches[2]', 'top');
		add_rewrite_rule('cours-de-(.*)/?', 'index.php?course_cate=$matches[1]', 'top');
		add_filter( 'query_vars', array( __CLASS__, 'custom_query_vars' ));
		flush_rewrite_rules ();
	}
		
	// Save
	public static function save_macro_discipline($term_id) {
		if (isset($_POST['term_meta'])) {
			$t_id = $term_id;
			$term_meta = get_option( "taxonomy_$t_id" );
			$cat_keys = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			
			// Save the option array.
			update_option( "taxonomy_$t_id", $term_meta );
		}
	}
	
	// Add Meta (macro discipline)
	public static function discipline_taxonomy_add_new_meta_field() {
		$html = 
		"<div class='form-field'>
			<label for='term_meta[macro_discipline]'>Macro Discipline</label>
			<select name='term_meta[macro_discipline]' id='macro_discipline' class='selectbox-general'>
				<option value='danse' selected='selected'>Danse</option>
				<option value='theatre'>Theatre</option>
			</select>
		</div>";
		echo $html;
	}
	
	// Add Meta (macro discipline)
	public static function discipline_taxonomy_edit_meta_field($term) {
		// put the term ID into a variable
		$t_id = $term->term_id;
		
		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "taxonomy_$t_id" );
		$macro_discipline = esc_attr( $term_meta['macro_discipline'] ) ? esc_attr( $term_meta['macro_discipline'] ) : '';
		
		$danse_selected = $macro_discipline === "Danse" ? "selected='selected'" : "";
		$theatre_selected = $macro_discipline === "Theatre" ? "selected='selected'" : "";
		
		$html =
		"<tr class='form-field'>
			<th scope='row' valign='top'><label for='term_meta[custom_term_meta]'>Macro Discipline</label></th>
			<td>
				<select name='term_meta[macro_discipline]' id='macro_discipline' class='selectbox-general'>
				<option value='danse' {$danse_selected}>Danse</option>
				<option value='theatre' {$theatre_selected}>Theatre</option>
			</select>
			</td>
		</tr>";
		echo $html;
	}
		
	public static function create_post_type() {
		if (post_type_exists('courses')) {
			return;
		}
		
		$labels = array(
			"name" => "Courses",	
			"singular_name" => "Course"
		);
		
		$args = array(
			"labels" => $labels,
			"public" => true,
			'rewrite' => array('slug' => 'courses','with_front' => false),
		);
		
		register_post_type("courses", $args);
		flush_rewrite_rules(true);
	}
	
	public static function create_city_taxonomies() {
		if (taxonomy_exists ( 'city' )) {
			return;
		}
		
		$labels = array (
			'name' => _x ( 'Villes', 'taxonomy general name' ),
			'singular_name' => _x ( 'City', 'taxonomy singular name' ),
			'search_items' => "Search Cities",
			'menu_name' => __("Ville"),
			'all_items' => __("All Villes"),
			'edit_item' => __("Edit Ville"),
			'add_new_item' => __("Add Ville"),
			'new_item_name' => __("Ville Name"),
			'not_found' => __("No Ville found.")
		);
		
		register_taxonomy ( 'city', 'courses', array (
				'hierarchical' => false,
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => false,
		) );
		
		global $CITY_LIST;
		foreach ($CITY_LIST as $slug => $name) {
			if (!term_exists($name, 'city')) {
				wp_insert_term($name, 'city', array('description' => $name, 'slug' => $slug));
			}
		}
	}
	
	public static function create_discipline_taxonomies() {
		if (taxonomy_exists ( 'discipline' )) {
			return;
		}
	
		$labels = array (
				'name' => _x ( 'Micro Disciplines', 'taxonomy general name' ),
				'singular_name' => _x ( 'Micro Discipline', 'taxonomy singular name' ),
				'search_items' => "Search Micro Discipline",
				'menu_name' => __("Micro Disciplines"),
				'all_items' => __("All Micro Disciplines"),
				'edit_item' => __("Edit Micro Discipline"),
				'add_new_item' => __("Add Micro Discipline"),
				'new_item_name' => __("Micro Discipline Name"),
				'not_found' => __("No Micro Discipline found.")
		);
	
		register_taxonomy ( 'discipline', 'courses', array (
				'hierarchical' => false,
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => false,
		) );
		
		global $MICRO_DISCIPLINE;
		foreach ($MICRO_DISCIPLINE as $macro => $arr) {
			foreach ($arr as $dis) {
				if (!term_exists($dis, 'discipline')) {
					wp_insert_term($dis, 'discipline', array('description' => $dis, 'slug' => $dis));
				}
			}
		}
	}
	
	public static function create_danse_categories() {
		if (taxonomy_exists ( 'course_cate' )) {
			return;
		}
	
		// Add new taxonomy, make it hierarchical like categories
		// first do the translations part for GUI
		$labels = array (
				'name' => _x ( 'course_cate', 'taxonomy general name' )
		);
	
		// Now register the taxonomy
		register_taxonomy ( 'course_cate', 'courses', array (
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'public' => true,
				'show_in_menu' => false,
				'show_in_nav_menus' => false,
				'query_var' => true,
				'rewrite' => array (
						'slug' => 'course_cate',
						'with_front' => false,
				)
		) );
		
		if (!term_exists('Test Categories', 'course_cate')) {
			wp_insert_term('Test Categories', 'course_cate', array('description' => 'Test Categories', 'slug' => 'test_course'));
		}
	
		flush_rewrite_rules ();
	}
	
	// create a custom taxonomy name it topics for your posts
	public static function create_taxonomies() {
		if (taxonomy_exists ( 'danse' ) || taxonomy_exists ( 'theatre' )) {
			return;
		}
		
		// Add new taxonomy, make it hierarchical like categories
		// first do the translations part for GUI
		$labels = array (
			'name' => _x ( 'Danse', 'taxonomy general name' ) 
		);
		
		// Now register the taxonomy
		register_taxonomy ( 'danse', 'courses', array (
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'public' => true,
				'show_in_menu' => false,
				'show_in_nav_menus' => false,
				'rewrite' => array (
					'slug' => 'danse',
					'with_front' => true 
				)
		) );
		
		if (!term_exists('Courses', 'danse')) {
			wp_insert_term('Courses', 'danse', array('description' => 'Danse Course', 'slug' => 'courses'));
		}
		
		// Add new taxonomy, make it hierarchical like categories
		// first do the translations part for GUI
		$labels = array (
				'name' => _x ( 'Theatre', 'taxonomy general name' )
		);
		
		// Now register the taxonomy
		register_taxonomy ( 'theatre', 'courses', array (
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'show_in_menu' => false,
				'show_in_nav_menus' => false,
				'public' => true,
				'rewrite' => array (
						'slug' => 'theatre',
						'with_front' => true
				)
		) );
		
		if (!term_exists('Courses', 'theatre')) {
			wp_insert_term('Courses', 'theatre', array('description' => 'Theatre Course', 'slug' => 'courses'));
		}
		
		flush_rewrite_rules ();
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
		FC_Install::create_post_type();
		FC_Install::create_taxonomies();
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
			post_id bigint(20),
			product_id bigint(20),
			course_name varchar(256),
			course_description text,
            room_id bigint(20) NOT NULL,
			discipline_id bigint(20) NOT NULL,
			profs_id  bigint(20) NOT NULL,
			number_available int(10) NOT NULL,
			start_date date NOT NULL,
			end_date date NOT NULL,
			start_time time NOT NULL,
			promo_value float NOT NULL,
			course_mode tinyint(1) NOT NULL,
			trial_mode tinyint(1) NOT NULL,
			delete_flg tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (course_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		// COURSE_TRIAL table
 		$coursetrialtable = $tableprefix . 'course_trial';
 		$sql = "CREATE TABLE " . $coursetrialtable . " (
            course_id bigint(20) NOT NULL,
	 		trial_no int(10) NOT NULL,
	 		start_date date NOT NULL,
	 		start_time time NOT NULL,
	 		number_available int(10) NOT NULL,
	 		PRIMARY KEY  (course_id, trial_no),
	 		KEY course_id (course_id),
	 		KEY trial_no (trial_no)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		// COURSE_TRIAL table
 		$coursetrialregistertable = $tableprefix . 'course_trial_registration';
 		$sql = "CREATE TABLE " . $coursetrialregistertable . " (
            course_id bigint(20) NOT NULL,
	 		trial_no int(10) NOT NULL,
	 		register_no int(10) NOT NULL,
			first_name varchar(255) NOT NULL,
			last_name varchar(255) NOT NULL,
			company_name varchar(255),
			email varchar(64) NOT NULL,
			phone varchar(20) NOT NULL,
			country varchar(128) NOT NULL DEFAULT 'France',
			address text NOT NULL,
			address2 text,
			zipcode varchar(8),
			city varchar(128),
	 		PRIMARY KEY  (course_id, trial_no, register_no),
	 		KEY course_id (course_id),
	 		KEY trial_no (trial_no),
			KEY register_no (register_no)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		// ROOM table
 		$roomtable = $tableprefix . 'room';
 		$sql = "CREATE TABLE " . $roomtable . " (
            room_id bigint(20) NOT NULL AUTO_INCREMENT,
            country varchar(64) NOT NULL,
			city varchar(64) NOT NULL,
			zip_code varchar(10) NOT NULL,
			room_name varchar(128) NOT NULL,
			address varchar(256),
			address_detail varchar(256),
			max_number int(10) NOT NULL DEFAULT 0,
			area_m2 int(10) NOT NULL DEFAULT 0,
	 		is_erp tinyint(1) NOT NULL DEFAULT 0,
			room_description text,
			room_manager_name varchar(128) NOT NULL,
			room_manager_email varchar(64) NOT NULL,
			room_manager_tel varchar(16) NOT NULL,
	 		photo_1 varchar(256),
	 		photo_2 varchar(256),
	 		photo_3 varchar(256),
	 		photo_4 varchar(256),
	 		photo_5 varchar(256),
	 		micro_discipline_1 varchar(32),
	 		micro_discipline_2 varchar(32),
	 		micro_discipline_3 varchar(32),
	 		micro_discipline_4 varchar(32),
	 		micro_discipline_5 varchar(32), 		
	 		bus_tram_1 varchar(256),
	 		bus_tram_2 varchar(256),
	 		bus_tram_3 varchar(256),
			UNIQUE KEY room_unique (country, city, zip_code, room_name),
            PRIMARY KEY  (room_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		// Discipline table
 		$disciplinetable = $tableprefix . 'discipline';
 		$sql = "CREATE TABLE " . $disciplinetable . " (
            discipline_id bigint(20) NOT NULL AUTO_INCREMENT,
            course_type varchar(32) NOT NULL,
			macro_discipline varchar(32) NOT NULL,
			micro_discipline varchar(32) NOT NULL,
			age_group varchar(64) NOT NULL,
			photo varchar(512),
	 		short_description varchar(512),
			discipline_description text,
	 		lesson_target text,
			lesson_duration int(10) NOT NULL DEFAULT 0,
			price int(10) NOT NULL DEFAULT 0,
			application_fee int(10) NOT NULL DEFAULT 0,
			UNIQUE KEY discipline_unique (course_type, macro_discipline, micro_discipline, age_group),
            PRIMARY KEY  (discipline_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		$profstable = $tableprefix . 'profs';
 		$sql = "CREATE TABLE " . $profstable . " (
            profs_id bigint(20) NOT NULL AUTO_INCREMENT,
            first_name varchar(32) NOT NULL,
			family_name varchar(32) NOT NULL,
			phone varchar(20),
			email varchar(64) NOT NULL,
			login_name varchar(32) NOT NULL,
			password varchar(128) NOT NULL,
	 		admin_type varchar(32) NOT NULL,
			description text,
	 		photo varchar(256),
 			micro_discipline_1 varchar(32),
 			micro_discipline_2 varchar(32),
 			micro_discipline_3 varchar(32),
 			city_1 varchar(64),
 			city_2 varchar(64),
 			city_3 varchar(64),
 			room_id_1 bigint(20),
 			room_id_2 bigint(20),
 			room_id_3 bigint(20),
			UNIQUE KEY login_name_unique (login_name),
 			UNIQUE KEY profs_unique (email),
            PRIMARY KEY  (profs_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		
 		$categoriestable = $tableprefix . 'category';
 		$sql = "CREATE TABLE " . $categoriestable . " (
            category_id bigint(20) NOT NULL AUTO_INCREMENT,
            slug varchar(32) NOT NULL,
 			macro_discipline varchar(32) NOT NULL,
			micro_discipline varchar(32),
			age_group varchar(32),
			photo varchar(256),
			description text NOT NULL,
			description_with_city text NOT NULL,
			title text NOT NULL,
			title_with_city text NOT NULL,
			meta_description text NOT NULL,
			meta_description_with_city text NOT NULL,
			meta_keyword text NOT NULL,
			meta_keyword_with_city text NOT NULL,
			UNIQUE KEY slug (slug),
			UNIQUE KEY discipline_age_group (macro_discipline, micro_discipline, age_group),
            PRIMARY KEY  (category_id)
        ) ". $charset_collate .";";
 		dbDelta( $sql );
 		//wp_die(var_dump( $wpdb->last_query ));
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
