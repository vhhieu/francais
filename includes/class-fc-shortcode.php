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
		global $CITY_LIST;
		return FC_Shortcode::build_options($CITY_LIST);
	}
	
	public static function build_age_groups_options() {
		global $AGE_GROUP;
		return FC_Shortcode::build_options($AGE_GROUP);
	}
	
	public static function build_micro_discipline_options() {
		global $MICRO_DISCIPLINE;
		return FC_Shortcode::build_options(array_merge($MICRO_DISCIPLINE['Dance'], $MICRO_DISCIPLINE['Theatre']));
	}
	
	public static function build_options($arr = array()) {
		$result = "";
		foreach ($arr as $value => $text) {
			$result .= "<option value='{$value}'>{$text}</option>\n";
		}
		return $result;
	}
	
	public static function shortcode( $atts, $content = "" ) {
		$city_options = FC_Shortcode::build_city_options();
		$age_groups_options = FC_Shortcode::build_age_groups_options();
		$micro_discipline_options = FC_Shortcode::build_micro_discipline_options();
		
		$html = "<div class='fixed-bottom'>
				  <div class='row'>
				   <div class='col-md-10'>
					<div class='row text-left' style='padding-top: 5px; padding-bottom: 5px;'>
						<div class='col-md-3'></div>
						<div class='col-md-9'>
						   <span><b>TESTEZ L'UN DE NOS COURS DE DANSE OU DE NOS COURS DE THEATRE:</b></span>
						</div>
					</div>
					<form>
					<div class='row' style='margin-top: 5px'>
				        <div class='col-md-3'></div>
						<div class='col-md-3'>VOTRE VILLE :</div>
						<div class='col-md-3'>VOTRE TRANCHE D'AGE :</div>
						<div class='col-md-3'>VOTRE DISCIPLINE :</div>
					</div>
					<div class='row' style='margin-top: 5px'>
				       <div class='col-md-3'></div>
					   <div class='col-md-3'><select name='city' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez une ville</option>
					      {$city_options}
					   </select></div>
					   <div class='col-md-3'><select name='city' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre tranche d'age</option>
					      {$age_groups_options}
					   </select></div>
					   <div class='col-md-3'><select name='city' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre discipline</option>
					      {$micro_discipline_options}
					   </select></div>
					</div>
					</form>
				   </div>
				   <div class='col-md-2' style='margin-top: 40px'>
		             <a>
		              <button class='btn-danger btn-lg' style='width: 90%'>Rechercher <span class='glyphicon glyphicon-chevron-right' style='text-align: right;'></span></button>
		             </a>
				   </div>
				  </div>
				</div>";
		return $html;
	}
}

FC_Shortcode::init();