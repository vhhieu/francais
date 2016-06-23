<?php
/**
 * Admin View: Main Menu -> Francias -> Course List
 *
 * @var string $view
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'FC_Course_List' ) ) :
class FC_Course_List extends WP_List_Table {
	private $cities, $micro_discipline;
	function __construct(){
		include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
		$this->cities = FC_Util::get_cities_list();
		$this->micro_discipline = FC_Util::get_micro_discipline_list();
		
		parent::__construct( array(
			'singular'  => 'movie',     //singular name of the listed records
			'plural'    => 'movies',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
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
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "francais_course_trial WHERE course_id IN ($ids)");
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "postmeta WHERE post_id IN (SELECT post_id FROM " . $wpdb->prefix . "francais_course WHERE course_id IN ($ids))");
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "posts WHERE ID IN (SELECT post_id FROM " . $wpdb->prefix . "francais_course WHERE course_id IN ($ids))");
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "francais_course WHERE course_id IN ($ids)");
			}
				
			//wp_die(var_dump( $wpdb->last_query ));
			if ($result) {
				wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-course", 301);
				exit();
			}
		}
	
	}
	
	function buildTree($result) {
		$current_discipline = "";
		$current_city = "";
		$current_age_group = "";
		$discipline_arr = array();
		foreach ($result as $c) {
	
			if ($c->micro_discipline !== $current_discipline) {
				$discipline_arr[$c->micro_discipline] = array();
				$current_discipline = $c->micro_discipline;
			}
	
			if ($c->age_group != $current_age_group) {
				$discipline_arr[$c->micro_discipline][$c->age_group] = array();
				$current_age_group = $c->age_group;
			}
			
			if ($c->city !== $current_city) {
				$discipline_arr[$c->micro_discipline][$c->age_group][$c->city] = array();
				$current_city = $c->city;
			}
	
			$discipline_arr[$c->micro_discipline][$c->age_group][$c->city][] = $c;
		}
		return $discipline_arr;
	}
	
	function build_micro_discipline_row($micro_dis, $age_group_arr) {
		$result = "<tr><td bgcolor='#CCCCCC' colspan='8'>{$this->micro_discipline[$micro_dis]}</td></tr>";
		global $AGE_GROUP;
		foreach ($age_group_arr as $age_group => $city_arr) {
			$result .= "<tr><td bgcolor='#999999' colspan='8'>&nbsp;&nbsp;&nbsp;&nbsp;{$AGE_GROUP[$age_group]}</td></tr>";
			$content = $this->build_age_group_row($city_arr);
			$result .= "<tr><td colspan='8'>{$content}</td></tr>";
		}
		return $result;
	}
	
	function build_age_group_row($city_arr) {
		$content = "";
		global $COURSE_TRIAL;
		global $COURSE_MODE;
		foreach ($city_arr as $city => $arr) {
			$content .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<font color='#CC33FF'><b>{$this->cities[$city]}</b></font></td></tr>";
			foreach ($arr as $item) {
				$room_index = $this->column_room_index($item);
				$discipline_index = $this->column_discipline_index($item);
					
				$content .= "<tr>
								<td style='width: 20%' data-colname='Lieu'>{$room_index}</td>
								<td style='width: 25%'>{$discipline_index}</td>
								<td style='text-align: center'>{$item->prof_name}</td>
								<td style='text-align: center'>{$item->number_available}</td>
								<td style='text-align: center'>{$item->price}</td>
								<td style='text-align: center'>{$item->promo_value}</td>
								<td style='text-align: center'>{$COURSE_MODE[$item->course_mode]}</td>
								<td style='text-align: center'>{$COURSE_TRIAL[$item->trial_mode]}</td>
							</tr>";
			}
		}
		
		$result = "<table class='wp-list-table widefat fixed striped movies'>
					<thead>
						<tr>
							<th scope='col' id='room_index' class='manage-column column-room_index column-primary'>Lieu</th>
							<th scope='col' id='discipline_index' class='manage-column column-discipline_index'>Formule de cours</th>
							<th scope='col' style='text-align: center' id='prof_name' class='manage-column column-prof_name'>Prof Name</th>
							<th scope='col' style='text-align: center' id='number_available' class='manage-column column-number_available'>Nombre places disponibles</th>
							<th scope='col' style='text-align: center' id='price' class='manage-column column-price'>Prix (€)</th>
							<th scope='col' style='text-align: center' id='promo_value' class='manage-column column-promo_value'>Promo (€)</th>
							<th scope='col' style='text-align: center' id='course_mode' class='manage-column column-course_mode'>Mode</th>
							<th scope='col' style='text-align: center' id='trial_mode' class='manage-column column-trial_mode'>Seance essai</th>
						</tr>
					</thead>
					<tbody>{$content}</tbody>
				  </table>";
		return $result;
	}
	
	function column_room_index($item) {
		$room_index = $item->country . " - " . $this->cities[$item->city] . " - "
				. $item->zip_code . " - " . $item->room_name;
		
		$actions = array(
				'view'      => sprintf('<a href="%s">View Course</a>', $item->course_url),
				'edit'      => sprintf('<a href="?page=francais-course-edit&movie=%s">Edit</a>', $item->course_id),
				'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item->course_id),
		);
		
		return sprintf('%1$s %2$s',
				/*$1%s*/ $room_index,
				/*$3%s*/ $this->row_actions($actions)
				);
	}
	
	function column_discipline_index($item){
		global $MARCO_DISCIPLINE;
		global $AGE_GROUP;
		$macro_discipline_key = $item->macro_discipline;
		$macro_discipline = $MARCO_DISCIPLINE[$macro_discipline_key];
	
		$value = $item->course_type . " - " . $macro_discipline . " - "
				. $this->micro_discipline[$item->micro_discipline] . " - " . $AGE_GROUP[$item->age_group];
	
		return $value;
	}
	
	function render() {
		global $wpdb;
		$table_prefix = $wpdb->prefix . 'francais_';
		$sql = "SELECT
					r.country, r.city, r.zip_code, r.room_name,
					d.course_type, d.macro_discipline, d.micro_discipline, d.age_group,
					CONCAT(p.first_name, ' ', p.family_name) AS prof_name,
					c.course_id,
					c.number_available,
					c.promo_value,
					c.course_mode,
					c.trial_mode,
					d.price,
					po.guid AS course_url
				FROM {$table_prefix}course c
					LEFT JOIN {$table_prefix}discipline d USING (discipline_id)
					LEFT JOIN {$table_prefix}room r USING (room_id)
					LEFT JOIN {$table_prefix}profs p USING (profs_id)
					LEFT JOIN {$wpdb->prefix}posts po ON c.post_id = po.ID
				ORDER BY d.micro_discipline, d.age_group, r.city;";
		
		$result = $wpdb->get_results($sql);
		
		$data = "";
		if (!$result) {
			$data = "Not found";
		}
		
		$discipline_arr = $this->buildTree($result);
		$content = "";
		foreach ($discipline_arr as $micro_dis => $age_group_arr) {
			$content .= $this->build_micro_discipline_row($micro_dis, $age_group_arr);
		}
		
		$data = "<table class='wp-list-table widefat fixed striped movies' width='100%' border='0' cellpadding='2' cellspacing='2'>
					<tbody>{$content}</tbody>
				</table>";
		echo $data;
	}
}
endif;
?>
<div class="wrap">
	<div id="icon-users" class="icon32"><br/></div>
	<h1>
		Cours <a
			href="<?php echo admin_url('admin.php?page=francais-course-add'); ?>"
			class="page-title-action">Add New</a>
	</h1>

    <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
    	<p>For any lesson, we want to be able to create it, and then if needed, to modify it (and maybe to delete it).</p> 
      	<p>For this, we have thought about a layout with different criteria to fix. Here are the details for each.</p>
    </div>
    <div style="margin-top: 20px;">
    <?php $cl = new FC_Course_List(); $cl->render();?>
    </div>
</div>