<?php
/**
 * Frontend config using for frontend.
 *
 * @author   hieuvh
 * @category Frontend
 * @package  Francais/Classes
 * @version  2.4.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * FC_Frontend Class.
 */
class FC_Frontend {
	
	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array ("FC_Frontend", "register_style"));
		add_action( 'wp_enqueue_scripts', array ("FC_Frontend", "using_style"));
		add_filter( 'wp_nav_menu_items', array("FC_Frontend", "your_custom_menu_item"), 10, 2 );
		
	}
	
	public static function your_custom_menu_item ( $items, $args ) {
		if ($args->theme_location != 'onepage') {
			return $items;
		}
		
		$sub_citys = FC_Frontend::create_sub_menu("danse");
		
		$items .= "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
				      <a href='#'><span>COURS DE DANSE</span></a>
				      {$sub_citys}
				   </li>";
		$sub_citys = FC_Frontend::create_sub_menu("theatre");
		$items .= "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
					<a href='#'><span>COURS DE THEATRE</span></a>
					{$sub_citys}
					</li>";
		
		return $items;
	}
	
	public static function create_sub_menu($macro_discipline) {
		global $wpdb;
		$prefix = $wpdb->prefix . "francais";
		$sql = "SELECT DISTINCT (city) FROM {$prefix}_room WHERE room_id IN (
				  SELECT room_id FROM {$prefix}_course WHERE discipline_id IN (
				    SELECT discipline_id FROM {$prefix}_discipline WHERE macro_discipline = '{$macro_discipline}'
				  )
				);";
		$data = $wpdb->get_col ( $sql );
		if (empty($data)) {
			return "";
		}
		
		$subcity = "";
		foreach ($data as $city) {
			$sub_micro = FC_Frontend::create_sub_menu_micro($macro_discipline, $city);
			$value = strtoupper($city);
			$macro_discipline = strtoupper($macro_discipline);
			$subcity .= 
			   "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
					<a href='#'><span>COURS DE {$macro_discipline} {$value}</span></a>
					{$sub_micro}
			    </li>";
		}
		$result = "<ul class='sub-menu'>{$subcity}</ul>";
		return $result;
	}
	
	public static function create_sub_menu_micro($macro_discipline, $city) {
		global $wpdb;
		$macro_discipline = strtolower($macro_discipline);
		
		$prefix = $wpdb->prefix . "francais";
		$sql = "SELECT d.age_group, d.micro_discipline FROM {$prefix}_discipline d 
					INNER JOIN {$prefix}_course c USING(discipline_id)
					INNER JOIN {$prefix}_room r USING (room_id)
				WHERE d.macro_discipline = '{$macro_discipline}' AND r.city = '{$city}'
				GROUP BY d.age_group, d.micro_discipline
				ORDER BY d.age_group;";
	
		$data = $wpdb->get_results ( $sql );
		if (empty($data)) {
			return "";
		}
		
		include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
		$micro_list = FC_Util::get_micro_discipline_list();
		
		$city_val = strtoupper($city);
		$macro_discipline_val = strtoupper($macro_discipline);
		
		$sub_micro = "<li><b>COURS DE {$macro_discipline_val} {$city_val}</b></li><li></li>";
		$current_age_group = "";
		foreach ($data as $entity) {
			$value = strtoupper($entity->micro_discipline);
			$age_group = strtoupper($entity->age_group);
			if ($age_group !== $current_age_group) {
				$current_age_group = $age_group;
				$sub_micro .= "<li><b>COURS {$age_group}:</b></li>";
			}
			
			$url = FC_Frontend::build_category_url($macro_discipline, $entity->age_group, $entity->micro_discipline, $city);
			$sub_micro .=
				"<li class='menu-item menu-item-type-custom menu-item-object-custom'>
			    	<a href='{$url}'><span>COURS DE {$value}</span></a>
				</li>";
		}
		$result = "<ul class='sub-menu'>{$sub_micro}</ul>";
		return $result;
	}
	
	public static function build_category_url($macro_discipline, $age_group, $micro_discipline, $city) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT c.slug
				FROM {$prefix}francais_category c 
				WHERE c.macro_discipline = %s\n";
		$sql = $wpdb->prepare($sql, $macro_discipline);
		if (!empty($micro_discipline)) {
			$sql .= "AND c.micro_discipline = %s\n";
			$sql = $wpdb->prepare($sql, $micro_discipline);
		} else {
			$sql .= "AND c.micro_discipline = ''\n";
		}
		
		if (!empty($age_group)) {
			$sql .= "AND c.age_group = %s";
			$sql = $wpdb->prepare($sql, $age_group);
		} else {
			$sql .= "AND c.age_group = ''\n";
		}
		
		$result = $wpdb->get_col( $sql );
		if (!empty($result)) {
			$result = $result[0] . "-" . $city;
		} else {
			return "#";
		}
		
		$result = home_url() . "/cours-de-" . $result;
		return $result;
	}
	
	public static function register_style() {
		wp_register_style( "custom_wp_css", FC_PLUGIN_URL . "assets/css/style.css");
	}
	
	public static function using_style() {
		wp_enqueue_style( 'custom_wp_css' );
	}
}

FC_Frontend::init();