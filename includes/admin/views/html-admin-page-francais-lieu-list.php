<?php
/**
 * Admin View: Main Menu - Francias - Lieu
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Room_List_Table extends WP_List_Table {
	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct(){
		global $status, $page;
	
		add_action( 'admin_head', array( &$this, 'admin_header' ) );
		
		//Set parent defaults
		parent::__construct( array(
				'singular'  => 'movie',     //singular name of the listed records
				'plural'    => 'movies',    //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
		) );
	}
	
	public function admin_header() {
		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		wp_die("PAGE: {$page}");
		if( 'francais-lieu' != $page ) {
			return;
		}
		
		echo '<style type="text/css">';
		echo '.wp-list-table .column-room_index { width: 40%; }';
		echo '</style>';
	}
	
	function get_columns() {
		return array(
				"cb" => "<input type=\"checkbox\" />",
				"room_index" => "Nom concaténé",
				"max_number" => "Nombre de pers max",
				"area_m2" => "Nombre m2",
				"room_manager_name" => "Nom et prénom du gestionnaire",
				"room_manager_email" => "Mail du gestionnaire",
				"room_manager_tel" => "Tél du gestionnaire"
		);
	}
	
	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default($item, $column_name){
		$result = $item[$column_name];
		
		if (empty($result)) {
			$result = "";
		}
		
		return $result;
	}
	
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_room_index($item){
	
		//Build row actions
		$actions = array(
				'edit'      => sprintf('<a href="?page=francais-lieu-edit&movie=%s">Edit</a>', $item['room_id']),
				'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['room_id']),
		);
	
		$value = $item['country'] . " - " . $item['city'] . " - " 
				. $item['zip_code'] . " - " . $item['room_name'];
		//Return the title contents
		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
				/*$1%s*/ $value,
				/*$2%s*/ $item['room_id'],
				/*$3%s*/ $this->row_actions($actions)
				);
	}
	

	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_cb($item){
		return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
				/*$2%s*/ $item['room_id']                //The value of the checkbox should be the record's id
				);
	}
	
	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
				'delete'    => 'Delete'
		);
		return $actions;
	}
	
	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	function process_bulk_action() {
	
		//Detect when a bulk action is being triggered...
		if( 'delete'===$this->current_action() && isset($_REQUEST['movie'])) {
			global $wpdb;
			//wp_die('Items deleted (or they would be if we had items to delete)!');
			
			$ids = isset($_REQUEST['movie']) ? $_REQUEST['movie'] : array();
			if (is_array($ids)) $ids = implode(',', $ids);
			
			$result = "";
			if (!empty($ids)) {
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "francais_room WHERE room_id IN($ids)");
			}
			
			//wp_die(var_dump( $wpdb->last_query ));
			if ($result) {
				wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-lieu", 301);
				exit();
			}
		}
	
	}
	
	
	
	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		global $wpdb; //This is used only if making any database queries
	
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = 10;
	
	
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
	
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);
	
	
		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();
	
	
		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
		$table_name = $wpdb->prefix . 'francais_room';
		$sql = "SELECT * FROM " . $table_name;
		$data = $wpdb->get_results ( $sql );
		$data = json_decode(json_encode($data), true);
		
		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 *
		 * In a real-world situation involving a database, you would probably want
		 * to handle sorting by passing the 'orderby' and 'order' values directly
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */
		function usort_reorder($a,$b){
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'room_id'; //If no sort, default to title
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			$result = strcmp('' . $a[$orderby], '' . $b[$orderby]); //Determine sort order
			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
		}
		usort($data, 'usort_reorder');
		//wp_die(print_r($data));
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();
	
		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count($data);
	
	
		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		 */
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
	
	
	
		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $data;
	
	
		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
				'total_items' => $total_items,                  //WE have to calculate the total number of items
				'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
				'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
}

//Create an instance of our package class...
$testListTable = new Room_List_Table();
//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();

?>

<div class="wrap">
	<div id="icon-users" class="icon32"><br/></div>
	<h1>
		Lieu <a
			href="<?php echo admin_url('admin.php?page=francais-lieu-add'); ?>"
			class="page-title-action">Add New</a>
	</h1>

    <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
    	<p>Lieu is the room in which the lesson will be given, defined by 4 criteria :</p> 
      	<ul>
       		<li>The country (example : France)</li>
       		<li>The city (example : Paris)</li>
       		<li>The district in the city (example : 75009)</li>
       		<li>The name of the room (example : studio moutarde 19)</li>
       		<li>Among the different lieu that will have been pre-defined (in the database, see chapter 2), we want to choose with a drop-down list</li>
       	</ul>
    </div>
        
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="movies-filter" method="get">
    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>
</div>
