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
		
		$sub_citys = FC_Frontend::create_sub_menu("Dance");
		
		$items .= "<li class='menu-item menu-item-type-custom menu-item-object-custom'>
				      <a href='#'><span>COURS DE DANSE</span></a>
				      {$sub_citys}
				   </li>";
		$sub_citys = FC_Frontend::create_sub_menu("Theatre");
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
		$prefix = $wpdb->prefix . "francais";
		$sql = "SELECT age_group, micro_discipline FROM {$prefix}_discipline WHERE macro_discipline = '{$macro_discipline}'
				  AND EXISTS (
				    SELECT 1 FROM {$prefix}_course WHERE room_id IN (
				     SELECT room_id FROM {$prefix}_room WHERE city = '{$city}'
				    )
				  ) ORDER BY age_group;";
		$data = $wpdb->get_results ( $sql );
		if (empty($data)) {
			return "";
		}
		
		$sub_micro = "";
		foreach ($data as $entity) {
			$value = strtoupper($entity->micro_discipline);
			$sub_micro .=
				"<li class='menu-item menu-item-type-custom menu-item-object-custom'>
			    	<a href='#'><span>COURS DE {$value}</span></a>
				</li>";
		}
		$result = "<ul class='sub-menu'>{$sub_micro}</ul>";
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