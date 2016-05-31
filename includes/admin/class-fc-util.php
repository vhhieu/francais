<?php
/**
 * Francais Util Class
*
* @author   hieuvh
* @category Admin
* @package  Francais/Admin
* @version  1.0.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'FC_Util' ) ) :
class FC_Util {
	/**
	 * Return key->value of 'city' taxonomy.
	 */
	public static function get_cities_list() {
		global $wpdb;
		$result = array();
		$arr = get_terms('city', array('taxonomy' => 'city', 'hide_empty' => false));
		foreach ($arr as $city) {
			$result[$city->slug] = $city->name;
		}
		return $result;
	}
	
	/**
	 * Return key->value of 'micro_discipline' taxonomy.
	 */
	public static function get_micro_discipline_list() {
		global $wpdb;
		$result = array();
		$arr = get_terms('discipline', array('taxonomy' => 'discipline', 'hide_empty' => false));
		foreach ($arr as $micro_discipline) {
			$result[$micro_discipline->slug] = $micro_discipline->name;
		}
		return $result;
	}
	
	/**
	 * Return key->value of 'micro_discipline' taxonomy.
	 */
	public static function get_micro_discipline_array() {
		global $wpdb;
		$result = array('danse' => array(), 'theatre' => array());
		$arr = get_terms('discipline', array('taxonomy' => 'discipline', 'hide_empty' => false));
		foreach ($arr as $micro_discipline) {
			// find macro
			$term_meta = get_option( "taxonomy_{$micro_discipline->term_id}" );
			$macro_discipline = $term_meta['macro_discipline'];
			if (empty($macro_discipline)) {
				$macro_discipline = "danse";
			}
			$result[$macro_discipline][$micro_discipline->slug] = $micro_discipline->name;
		}
		return $result;
	}
	
	public static function get_macro_discipline($micro_discipline) {
		$arr = get_terms('discipline',
				array('taxonomy' => 'discipline', 'slug' => $micro_discipline,
						'hide_empty' => false));
		$macro_discipline = 'danse';
		if (!empty($arr)) {
			$md = $arr[0];
			$term_meta = get_option( "taxonomy_{$md->term_id}" );
			$macro_discipline = $term_meta['macro_discipline'];
			if (empty($macro_discipline)) {
				$macro_discipline = "danse";
			}
		}
		return $macro_discipline;
	}
}
endif;