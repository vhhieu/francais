<?php
/**
 * Shortcode using for frontend.
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
 * FC_Shortcode Class.
 */
class FC_Shortcode {
	
	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_shortcode( 'banner-research', array('FC_Shortcode', 'shortcode') );
	}
	
	public static function build_city_options() {
		global $wpdb;
		$prefix = "{$wpdb->prefix}francais_";
		$sql = "SELECT r.city, d.age_group, d.micro_discipline FROM {$prefix}room r
					INNER JOIN {$prefix}course c USING (room_id)
					INNER JOIN {$prefix}discipline d USING (discipline_id)
				GROUP BY city,age_group,micro_discipline
				ORDER BY city,age_group,micro_discipline;
		";
		
		$data = $wpdb->get_results($sql);
		//wp_die(print_r($data));
		$result = array();
		if (! empty($data)) {
			foreach ($data as $obj) {
				if (!isset($result[$obj->city])) {
					$result[$obj->city] = array();
				}
				if (!isset($result[$obj->city][$obj->age_group])) {
					$result[$obj->city][$obj->age_group] = array();
				}
				$result[$obj->city][$obj->age_group][] = $obj->micro_discipline;
			}
		}
		
		return $result;
	}
	
	public static function build_options($arr = array()) {
		$result = "";
		foreach ($arr as $value) {
			$result .= "<option value='{$value}'>{$value}</option>\n";
		}
		return $result;
	}
	
	public static function shortcode( $atts, $content = "" ) {
		$data = FC_Shortcode::build_city_options();
		$city_options = FC_Shortcode::build_options(array_keys($data));
		$script = FC_Shortcode::build_script($data);
		
		$html = "<div class='fixed-bottom'>
				  <div class='row'>
				   <div class='col-md-10'>
				    <form id='researchform'>
					<div class='row text-left' style='padding-top: 5px; padding-bottom: 5px;'>
						<div class='col-md-3'></div>
						<div class='col-md-9'>
						   <span><b>TESTEZ L'UN DE NOS COURS DE DANSE OU DE NOS COURS DE THEATRE:</b></span>
						</div>
					</div>
					
					<div class='row' style='margin-top: 5px'>
				        <div class='col-md-3'></div>
						<div class='col-md-3'>VOTRE VILLE :</div>
						<div class='col-md-3'>VOTRE TRANCHE D'AGE :</div>
						<div class='col-md-3'>VOTRE DISCIPLINE :</div>
					</div>
					<div class='row' style='margin-top: 5px'>
				       <div class='col-md-3'></div>
					   <div class='col-md-3'><select id = 'city' name='city' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez une ville</option>
					      {$city_options}
					   </select></div>
					   <div class='col-md-3'><select id = 'age_group' name='age_group' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre tranche d'age</option>
					   </select></div>
					   <div class='col-md-3'><select id = 'dis' name='dis' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre discipline</option>
					   </select></div>
					</div>
				   </div>
				   <div class='col-md-2' style='margin-top: 40px'>
		             <a>
		              <button class='btn-danger btn-lg' style='width: 90%'>Rechercher <span class='glyphicon glyphicon-chevron-right' style='text-align: right;'></span></button>
		             </a>
				   </div>
				   </form>
				  </div>
				</div>
				{$script}";
		return $html;
	}
	
	public static function build_script($data) {
		$json = json_encode($data);
		$html .= "
			<script type='text/javascript'>
				var data = '{$json}';
				jQuery('#city').change(function() {
					alert(data);
				});
			</script>";
		return $html;
	}
}

FC_Shortcode::init();