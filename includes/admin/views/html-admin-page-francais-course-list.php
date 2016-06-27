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
	
			if ($c->city !== $current_city) {
				$discipline_arr[$c->micro_discipline][$c->city] = array();
				$current_city = $c->city;
			}
	
			if ($c->age_group != $current_age_group) {
				$discipline_arr[$c->micro_discipline][$c->city][$c->age_group] = array();
				$current_age_group = $c->age_group;
			}
	
			$discipline_arr[$c->micro_discipline][$c->city][$c->age_group][] = $c;
		}
		return $discipline_arr;
	}
	
	function build_micro_discipline_row($micro_dis, $city_arr) {
		$img = FC_PLUGIN_URL . "/assets/images/expand.png";
		$result = "<tr><td bgcolor='#CCCCCC' colspan='8'>{$this->micro_discipline[$micro_dis]}</td></tr>";
		foreach ($city_arr as $city => $age_group_arr) {
			$result .= "<tr onclick='javascript:show_city(this);'><td bgcolor='#0033AA' colspan='8' style='color: white;'>&nbsp;&nbsp;<img src='{$img}'/>&nbsp;&nbsp;{$this->cities[$city]}</td></tr>";
			$content = $this->build_age_group_row($age_group_arr);
			$result .= "<tr style='display: none;'><td colspan='8'>{$content}</td></tr>";
		}
		return $result;
	}
	
	function build_age_group_row($age_group_arr) {
		$content = "";
		global $AGE_GROUP;
		global $COURSE_TRIAL;
		global $COURSE_MODE;
		foreach ($age_group_arr as $age_group => $arr) {
			$content .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<font color='#CC33FF'><b>{$AGE_GROUP[$age_group]}</b></font></td></tr>";
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
				'client'    => sprintf('<a href="?page=francais-course&course_id=%s">Clients</a>', $item->course_id),
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
				ORDER BY d.micro_discipline, r.city, d.age_group;";
		
		$result = $wpdb->get_results($sql);
		
		$data = "";
		if (!$result) {
			$data = "Not found";
		}
		
		$discipline_arr = $this->buildTree($result);
		$content = "";
		foreach ($discipline_arr as $micro_dis => $city_arr) {
			$content .= $this->build_micro_discipline_row($micro_dis, $city_arr);
		}
		
		$data = "<table class='wp-list-table widefat fixed striped movies' width='100%' border='1px' cellpadding='2' cellspacing='2'>
					<tbody>{$content}</tbody>
				</table>";
		echo $data;
	}
	
	public function render_course($course_id) {
		$product_id = $this->get_product($course_id);
		global $wpdb;
		$sql = "SELECT p_order.*, pm.meta_key, pm.meta_value
				FROM
				(
					SELECT pim.meta_value AS product_id, oi.order_item_id, oi.order_id
					FROM `{$wpdb->prefix}woocommerce_order_itemmeta` pim
					LEFT JOIN `{$wpdb->prefix}woocommerce_order_items` oi USING (order_item_id)
					WHERE meta_key = '_product_id' AND pim.meta_value = %s
				) p_order
				LEFT JOIN `{$wpdb->prefix}postmeta` pm ON p_order.order_id = pm.post_id
				WHERE pm.meta_key IN ('_order_key','_billing_address_1', '_billing_city', '_billing_email', '_billing_first_name', '_billing_last_name', '_billing_phone', '_billing_postcode')
				ORDER BY order_item_id,  meta_key";
		$sql = $wpdb->prepare($sql, $product_id);
		$data = $wpdb->get_results($sql);
		$objects = array();
		$current_id = -1;
		$current = null;
		foreach ($data as $obj) {
			if ($current_id != $obj->order_item_id) {
				$current_id = $obj->order_item_id;
				$current = new stdClass();
				$objects[] = $current;
				$current->order_id = $obj->order_id;
			}
			switch ($obj->meta_key) {
				case "_billing_city":
					$current->city = $obj->meta_value;
					break;
				case "_billing_address_1":
					$current->address = $obj->meta_value;
					break;
				case "_billing_email":
					$current->email = $obj->meta_value;
					break;
				case "_billing_first_name":
					$current->first_name = $obj->meta_value;
					break;
				case "_billing_last_name":
					$current->last_name = $obj->meta_value;
					break;
				case "_billing_phone":
					$current->phone = $obj->meta_value;
					break;
				case "_billing_postcode":
					$current->postcode = $obj->meta_value;
					break;
				case "_order_key":
					$current->order_key = $obj->meta_value;
					break;
			}
		}
		
		$content = "";
		foreach ($objects as $idx => $obj) {
			$nor = $idx + 1;
			$order_url = home_url() . "/wp-admin/post.php?post={$obj->order_id}&action=edit";
			$content .= "
				<tr>
					<td>{$nor}</td>
					<td><a href='{$order_url}'>{$obj->order_key}</a></td>
					<td>{$obj->first_name} {$obj->last_name}</td>
					<td>{$obj->phone}</td>
					<td>{$obj->address}</td>
					<td>{$obj->postcode}</td>
					<td>{$obj->email}</td>
				</tr>
			";
		}
		$info = $this->get_course_info($course_id, count($objects));
		$html = 
			"{$info}
			<div style='margin-top: 20px;'>
				<table class='wp-list-table widefat fixed striped'>
					<thead>
						<tr>
							<th scope='col' style='text-align: center; width: 15px' id='no_of_record' class='manage-column'>#</th>
							<th scope='col' style='text-align: center' id='order_key' class='manage-column'>Order</th>
							<th scope='col' style='text-align: center' id='name' class='manage-column'>Prénom + Nom</th>
							<th scope='col' style='text-align: center' id='phone' class='manage-column'>Phone</th>
							<th scope='col' style='text-align: center' id='address' class='manage-column'>Address</th>
							<th scope='col' style='text-align: center' id='postcode' class='manage-column'>Postal code</th>
							<th scope='col' style='text-align: center' id='email' class='manage-column'>Email</th>
						</tr>
					</thead>
					<tbody>{$content}</tbody>
				</table>
    		</div>";
		echo $html;
	}
	
	public function get_course_info($course_id, $count_register) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT c.course_id, c.start_date, c.start_time, c.end_date, c.number_available, c.course_mode, c.trial_mode,
				CONCAT(p.first_name, ' ', p.family_name) profs_name,
				CONCAT(r.room_name, ', ', r.address, ', ', r.zip_code, ', ', r.city) room_info, r.city,
				d.course_type, d.macro_discipline, d.age_group, d.micro_discipline, d.short_description, d.lesson_duration, d.photo,
				po.guid AS course_url, po.post_title as course_title
			FROM {$prefix}francais_course c
				LEFT JOIN {$prefix}francais_discipline d USING(discipline_id)
				LEFT JOIN {$prefix}francais_room r USING(room_id)
				LEFT JOIN {$prefix}francais_profs p USING(profs_id)
				LEFT JOIN {$wpdb->prefix}posts po ON c.post_id = po.ID
			WHERE c.course_id = {$course_id}";
		
		$course = $wpdb->get_row($sql);
		
		setlocale(LC_TIME, get_locale());
		$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
		$to_time = $from_time + $course->lesson_duration * 60;
		$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();
		
		$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
		$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
		$start_date_str = strftime("%d %b %Y", $start_date);
		$day_of_week = strftime("%A", $start_date);
		
		$html ="
		<div class='row' style='background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;'>
			<div class='col-md-12'>
				<p><b><u><a href='{$course->course_url}'>{$course->course_title}</a></u></b></p>
				<p><b>Jour et horaire du cours</b>: Tous les {$day_of_week} de {$from_time_str} à {$to_time_str} à partir du {$start_date_str} (hors vacances scolaires)</p>
				<p><b>Lieu</b>: {$course->room_info}</p>
				<p><b>Professeur</b>: {$course->profs_name}</p>
				<p><b>Description</b>: {$course->short_description}</p>
				<p><b><i>{$count_register}</i></b> registered for <b>{$course->number_available}</b> places</p>
			</div>
		</div>
		";
		
		return $html;
	}
	
	public function get_product($course_id) {
		global $wpdb;
		$sql = "SELECT product_id FROM {$wpdb->prefix}francais_course WHERE course_id = %d";
		$sql = $wpdb->prepare($sql, $course_id);
		return $wpdb->get_var($sql);
	}
}
endif;

// Init Object
$cl = new FC_Course_List();
?>
<div class="wrap">
	<div id="icon-users" class="icon32"><br/></div>
	<h1>
		Cours <a href="<?php echo admin_url('admin.php?page=francais-course-add'); ?>"
			class="page-title-action">Add New</a>
	</h1>

    <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
    	<p>For any lesson, we want to be able to create it, and then if needed, to modify it (and maybe to delete it).</p> 
      	<p>For this, we have thought about a layout with different criteria to fix. Here are the details for each.</p>
    </div>
    <?php
    	if (isset($_GET["course_id"])) {
    		$cl->render_course($_GET["course_id"]);
    	}
    ?>
    <div style="margin-top: 20px;">
    	<form method="POST" class="validate" novalidate="novalidate"
				action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    		<table class="form-table" >
			<colgroup>
				<col span="1" class="header-block">
			</colgroup>
			<tbody>
				<tr class="form-field form-required">
					<td scope="rowgroup" rowspan="5" valign="middle" width="25%" style="text-align: left">
						Extraction des cours du <select id="start_date_from" name="start_date_from">
							<?php for ($year = 2016; $year <= 2030; $year++) {
								echo "<option value='{$year}' " . ($year == intval($_POST['start_date_from']) ? "selected='selected'" : "") . ">{$year}</option>";
							}?>
						</select>
						 au 
						<select id="start_date_to" name="start_date_to">
							<?php for ($year = 2016; $year <= 2030; $year++) {
								echo "<option value='{$year}' " . ($year == intval($_POST['start_date_to']) ? "selected='selected'" : "") . ">{$year}</option>";
							}?>
						</select>
						<button type="submit" value="export" style="vertical-align: middle;">
							<img src="<?= FC_PLUGIN_URL . "/assets/images/picto-excel.png"?>">
						</button>
					</td>
				</tr>
			</tbody>
		</table>
    	</form>
    </div>
    <div style="margin-top: 20px;">
    <?php $cl->render();?>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	
    jQuery('.datepicker').live('focusin', function() {
    	jQuery(this).datepicker({
    		changeMonth: false,
            changeYear: true,
            dateFormat : 'yyyy',
            showOn: 'focus',
            viewMode: "years", 
            minViewMode: "years"
        });
    });
    
});

	function show_city(node) {
		var trTag = jQuery(node).closest('tr').next('tr');
		var imgTag = jQuery(node).find("img");
		var displayValue = jQuery(trTag).css('display');

		var expandUrl = '<?php echo FC_PLUGIN_URL . "/assets/images/expand.png"?>';
		var collapseUrl = '<?php echo FC_PLUGIN_URL . "/assets/images/collapse.png"?>';
		if (displayValue == 'none') {
			jQuery(trTag).css('display', 'table-row');
			jQuery(imgTag).attr('src', collapseUrl);
		} else {
			jQuery(trTag).css('display', 'none');
			jQuery(imgTag).attr('src', expandUrl);
		}
	}
</script>