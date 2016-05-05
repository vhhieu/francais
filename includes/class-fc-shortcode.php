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
		add_shortcode( 'banner-research', array('FC_Shortcode', 'shortcode_banner_research') );
		add_shortcode( 'prof-list', array('FC_Shortcode', 'shortcode_prof_list') );
	}
	
	public static function build_data_options() {
		global $wpdb;
		$prefix = "{$wpdb->prefix}francais_";
		$sql = "SELECT r.city, d.age_group, d.micro_discipline FROM {$prefix}room r
					INNER JOIN {$prefix}course c USING (room_id)
					INNER JOIN {$prefix}discipline d USING (discipline_id)
				GROUP BY city,age_group,micro_discipline
				ORDER BY city,age_group,micro_discipline;
		";
		
		$data = $wpdb->get_results($sql);
		
		$data_city = array();
		$data_age_group = array();
		$data_discipline = array();
		
		$result = array();
		if (! empty($data)) {
			foreach ($data as $obj) {
				if (! in_array($obj->city, $data_city)) {
					$data_city[] = $obj->city;
				}
				if (! in_array($obj->age_group, $data_age_group)) {
					$data_age_group[] = $obj->age_group;
				}
				if (! in_array($obj->micro_discipline, $data_discipline)) {
					$data_discipline[] = $obj->micro_discipline;
				}
			}
		}
		$result[0] = $data;
		$result[1] = $data_city;
		$result[2] = $data_age_group;
		$result[3] = $data_discipline;
		return $result;
	}
	
	public static function build_options($arr = array()) {
		$result = "";
		foreach ($arr as $value) {
			$result .= "<option value='{$value}'>{$value}</option>\n";
		}
		return $result;
	}
	
	public static function shortcode_banner_research( $atts, $content = "" ) {
		$options = FC_Shortcode::build_data_options();
		$city_options = FC_Shortcode::build_options($options[1]);
		$age_group_options = FC_Shortcode::build_options($options[2]);
		$discipline_options = FC_Shortcode::build_options($options[3]);
		$script = FC_Shortcode::build_script($options[0], $options[1], $options[2], $options[3]);
		
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
					   <div class='col-md-3'><select id = 'age' name='age' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre tranche d'age</option>
					      {$age_group_options}
					   </select></div>
					   <div class='col-md-3'><select id = 'dis' name='dis' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre discipline</option>
					      {$discipline_options}
					   </select></div>
					</div>
				   </div>
				   <div class='col-md-2' style='margin-top: 40px'>
		             <a href='#' onclick='click_research()'>
		              <button class='btn-danger btn-lg' style='width: 90%'>Rechercher <span class='glyphicon glyphicon-chevron-right' style='text-align: right;'></span></button>
		             </a>
				   </div>
				   </form>
				  </div>
				</div>
				{$script}";
		return $html;
	}
	
	public static function build_script($data, $city_data, $age_group_data, $discipline_data) {
		$json = json_encode($data);
		$json_city = json_encode($city_data);
		$json_age_group = json_encode($age_group_data);
		$json_discipline = json_encode($discipline_data);
		$dance_url = home_url() . "/dance/courses?city={0}&age={1}&dis={2}";
		$theatre_url = home_url() . "/theatre/courses?city={0}&age={1}&dis={2}";
		$html .= "
			<script type='text/javascript'>
				var data = {$json};
				function click_research() {
					var city = jQuery('#city').val();
					var age_group = jQuery('#age').val();
					var dis = jQuery('#dis').val();
					
					if (city && age_group && dis) {
						var rs = data.filter(function (el) {
							return el.city == city && el.age_group == age_group && el.micro_discipline == dis;
						});
						if (rs && rs.length > 0) {
							var url = '{$dance_url}';
							if (dis == 'Théâtre') {
								url = '{$theatre_url}';
							}
							
							if (!String.prototype.format) {
							  String.prototype.format = function() {
							    var args = arguments;
							    return this.replace(/{(\d+)}/g, function(match, number) { 
							      return typeof args[number] != 'undefined'
							        ? args[number]
							        : match
							      ;
							    });
							  };
							}
							url = url.format(city, age_group, dis);
							document.location=url;
						} else {
							alert('No course match your condition!');
						}
					} else {
						alert('Please select more condition!');
					}
				}
			</script>";
		return $html;
	}
	
	public static function shortcode_prof_list( $atts, $content = "" ) {
		global $wpdb;
		$prefix = $wpdb->prefix . "francais_";
		$sql = "SELECT CONCAT(first_name, ' ', family_name) as full_name, photo, micro_discipline_1 FROM {$prefix}profs";
		$data = $wpdb->get_results($sql);
		
		$total = count($data);
		if ($total <= 0) {
			return "";
		}
		
		// TODO: will use it.
		$num_of_page = (int) ($total / 12) + ($total % 12 > 0 ? 1 : 0); // TODO: need 12 because option NUMBER OF TEACHER.
		
		$html = "<div class='row' style='padding-top: 50px;'>";
		$count = 0;
		foreach ($data as $obj) {
			$count++;
			$html .= FC_Shortcode::teacher_content($obj);
			if ($count % 6 == 0 && $count < $total) {
				$html .= "</div>
						<div class='row' style='padding-top: 50px;'>";
			}
		}
		
		$html .= "</div>";
		for ($page = 1; $page <= $num_of_page; $page++) {
			
		}
		
	
		return $html;
	}
	
	public static function teacher_content($t) {
		$img = home_url() . '/' . $t->photo;
		if (empty($t->photo)) {
			$img = plugins_url('../assets/images/no_image_available.png', __FILE__);
		}
		
		$html = "
		  <div class='col-md-2'>
		    <div class='row'>
		      <div class='col-md-11 course-block text-center'>
		          <img src='{$img}'><br/>
		          <p>{$t->full_name}</p>
		          <p style='color: #DEC67E;'>{$t->micro_discipline_1}</p>
		      </div>
		      <div class='col-md-1'>
		      </div>
		    </div>
		  </div>";
		return $html;
	}
}

FC_Shortcode::init();