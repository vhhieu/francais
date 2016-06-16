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
if ( ! class_exists( 'FC_Shortcode' ) ) :
class FC_Shortcode {
	
	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_shortcode( 'banner-research', array('FC_Shortcode', 'shortcode_banner_research') );
		add_shortcode( 'prof-list', array('FC_Shortcode', 'shortcode_prof_list') );
		add_shortcode( 'essai-registration', array('FC_Shortcode', 'shortcode_essai_registration') );
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
	
	public static function build_options($arr = array(), $map) {
		$result = "";
		foreach ($arr as $value) {
			$result .= "<option value='{$value}'>{$map[$value]}</option>\n";
		}
		return $result;
	}
	
	public static function process_essai_registration($trial) {
		$result = "";
		global $wpdb;
		$table_prefix = $wpdb->prefix . "francais_";
		$sql = "SELECT count(*) FROM {$table_prefix}course_trial_registration WHERE course_id = %d AND trial_no = %d";
		$sql = $wpdb->prepare($sql, $trial->course_id, $trial->trial_no);
		$count = $wpdb->get_var($sql);
		if ($count >= $trial->number_available) {
			return "The seance d'essa is full!";
		}
		
		$error = array();
		if (!$_POST['billing_first_name']) {
			$error[] = "Prénom required";
		}
		if (!$_POST['billing_last_name']) {
			$error[] = "Nom required";
		}
		include_once ( FC_PLUGIN_PATH . 'lib/EmailAddressValidator.php');
		if (empty($_POST['billing_email'])) {
			$error[] = "Adresse e-mail required";
		} else if (!(new EmailAddressValidator())->check_email_address($_POST['billing_email'])) {
			$result[] = "Adresse e-mail ({$_POST['email']}) is invalid";
		}
		if (!$_POST['billing_phone']) {
			$error[] = "Téléphone required";
		}
		if (!$_POST['billing_address_1']) {
			$error[] = "Adresse required";
		}
		if (!$_POST['billing_postcode']) {
			$error[] = "Code postal required";
		}
		if (!$_POST['billing_city']) {
			$error[] = "Ville required";
		}
		
		if (count($error) > 0) {
			$result = "<ul><li>";
			$result .= implode("</li><li>", $error);
			$result .= "</li></ul>";
		} else {
			// insert database
			$_POST      = array_map('stripslashes_deep', $_POST);
			$result = $wpdb->insert(
				$table_prefix . 'course_trial_registration', //table
				array(
					'course_id' => $_POST['course_id'],
					'trial_no' => $_POST['trial_no'],
					'register_no' => $count + 1,
					'first_name' => $_POST['billing_first_name'],
					'last_name' => $_POST['billing_last_name'],
					'company_name' => $_POST['billing_company'],
					'email' => $_POST['billing_email'],
					'phone' => $_POST['billing_phone'],
					'country' => 'France',
					'address' => $_POST['billing_address_1'],
					'address2' => $_POST['billing_address_2'],
					'zipcode' => $_POST['billing_postcode'],
					'city' => $_POST['billing_city'],
				), //data
				array('%d','%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') //data format
			);
			
			if ($result !== null) {
				$result = "";
				// send mail
			} else {
				$result = "Course essai registration fail, please try again";
			}
			
		}
		
		return $result;
	}
	
	public static function build_essai_title($course_id, $trial_no) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT ct.start_date, ct.start_time, c.course_id, r.city,
					d.course_type, d.macro_discipline, d.age_group, d.micro_discipline, d.lesson_duration
				FROM {$prefix}francais_course_trial ct
				    LEFT JOIN {$prefix}francais_course c USING (course_id)
					LEFT JOIN {$prefix}francais_discipline d USING(discipline_id)
					LEFT JOIN {$prefix}francais_room r USING(room_id)
				WHERE c.course_id = %d AND ct.trial_no = %d\n";
		
		$sql = $wpdb->prepare($sql, $course_id, $trial_no);
 		$course = $wpdb->get_row($sql);
		
		setlocale(LC_TIME, get_locale());
		$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
		$to_time = $from_time + $course->lesson_duration * 60; // 1 hour
		$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();
			
		$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
		$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
		$start_date_str = strftime("%d %b %Y", $start_date);
		$day_of_week = strftime("%A", $start_date);

		include_once(WP_PLUGIN_DIR . "/francais/includes/admin/class-fc-util.php");
		$micro_arr = FC_Util::get_micro_discipline_list();
		$micro_discipline = $micro_arr[$course->micro_discipline];
		global $AGE_GROUP;
		$age_group = $course->age_group;
		
		$title = strtoupper("COURS DE {$micro_discipline} {$AGE_GROUP[$age_group]} A {$course->city} LE {$day_of_week} {$start_date_str} DE {$from_time_str} À {$to_time_str}");
// $title = $course->course_id;
		return $title;
	}
	
	public static function shortcode_essai_registration( $atts, $content = "" ) {
		$is_get = false;
		if ($_SERVER['REQUEST_METHOD'] === "GET") {
			$is_get = true;
			$course_id = intval($_GET['c']);
			$trial_no = intval($_GET['t']);
		} else {
			$course_id = intval($_POST['course_id']);
			$trial_no = intval($_POST['trial_no']);
		}
		
		global $wpdb;
		$table_prefix = $wpdb->prefix . "francais_";
		$sql = "SELECT * FROM {$table_prefix}course_trial WHERE course_id = %d AND trial_no = %d ORDER BY TRIAL_NO";
		$sql = $wpdb->prepare($sql, $course_id, $trial_no);
		$trial = $wpdb->get_row($sql);
		if (!$trial) {
			return "<b>Invalid essai</b>, Return <a href='" . home_url() . "'>Home</a>";
		}
		
		if (!$is_get) {
			$result = FC_Shortcode::process_essai_registration($trial);
		}
		
		$course_title = FC_Shortcode::build_essai_title($course_id, $trial_no);
		if ($is_get || $result) {
			$error = "";
			if ($result) {
				$error = "<div id='payment' class='woocommerce-error'>{$result}</div>";
			}

			$html = "<div class='woocommerce'>
					<form name='essai_registration' method='post' class='checkout woocommerce-checkout' action=''>
					{$error}
					<div class='woocommerce-billing-fields'>
					   <h3>Coordonnées:</h3>
					   <p class='form-row form-row form-row-first validate-required' id='billing_first_name_field'><label for='billing_first_name' class=''>Prénom <abbr class='required' title='requis'>*</abbr></label><input type='text' class='input-text ' name='billing_first_name' id='billing_first_name' placeholder='' value='{$_POST['billing_first_name']}'></p>
					   <p class='form-row form-row form-row-last validate-required' id='billing_last_name_field'><label for='billing_last_name' class=''>Nom <abbr class='required' title='requis'>*</abbr></label><input type='text' class='input-text ' name='billing_last_name' id='billing_last_name' placeholder='' value='{$_POST['billing_last_name']}'></p>
					   <div class='clear'></div>
					   <p class='form-row form-row form-row-wide' id='billing_company_field'><label for='billing_company' class=''>Nom de l’entreprise</label><input type='text' class='input-text ' name='billing_company' id='billing_company' placeholder='' value='{$_POST['billing_company']}'></p>
					   <p class='form-row form-row form-row-first validate-required validate-email' id='billing_email_field'><label for='billing_email' class=''>Adresse e-mail <abbr class='required' title='requis'>*</abbr></label><input type='email' class='input-text ' name='billing_email' id='billing_email' placeholder='' value='{$_POST['billing_email']}'></p>
					   <p class='form-row form-row form-row-last validate-required validate-phone' id='billing_phone_field'><label for='billing_phone' class=''>Téléphone&nbsp; <abbr class='required' title='requis'>*</abbr></label><input type='tel' class='input-text ' name='billing_phone' id='billing_phone' placeholder='' value='{$_POST['billing_phone']}'></p>
					   <div class='clear'></div>
					   <p class='form-row form-row form-row-wide address-field update_totals_on_change validate-required woocommerce-validated' id='billing_country_field'><label for='billing_country' class=''>Pays <abbr class='required' title='requis'>*</abbr></label> France</p>
					   <p class='form-row form-row form-row-wide address-field validate-required' id='billing_address_1_field'><label for='billing_address_1' class=''>Adresse&nbsp; <abbr class='required' title='requis'>*</abbr></label><input type='text' class='input-text ' name='billing_address_1' id='billing_address_1' placeholder='Adresse' value='{$_POST['billing_address_1']}'></p>
					   <p class='form-row form-row form-row-wide address-field' id='billing_address_2_field' style='display: none;'><input type='text' class='input-text ' name='billing_address_2' id='billing_address_2' placeholder='Appartement, bureau, etc. (optionnel)' value='{$_POST['billing_address_2']}'></p>
					   <p class='form-row form-row address-field validate-postcode form-row-first' id='billing_postcode_field' data-o_class='form-row form-row form-row-last address-field validate-postcode'><label for='billing_postcode' class=''>Code postal <abbr class='required' title='requis'>*</abbr></label><input type='text' class='input-text ' name='billing_postcode' id='billing_postcode' placeholder='' value='{$_POST['billing_postcode']}'></p>
					   <p class='form-row form-row address-field validate-required form-row-last' id='billing_city_field' data-o_class='form-row form-row form-row-wide address-field validate-required'><label for='billing_city' class=''>Ville <abbr class='required' title='requis'>*</abbr></label><input type='text' class='input-text ' name='billing_city' id='billing_city' placeholder='' value='{$_POST['billing_city']}'></p>
					   <div class='clear'></div>
					</div>
					<div id='order_review' class='woocommerce-checkout-review-order'>
					   <table class='shop_table woocommerce-checkout-review-order-table'>
					      <thead>
					         <tr>
					            <th class='product-name'>Produit</th>
					         </tr>
					      </thead>
					      <tbody>
					         <tr class='cart_item'>
					            <td class='product-name'>
					            {$course_title}
					            </td>
					         </tr>
					      </tbody>
					   </table>
					   <div id='payment' class='woocommerce-checkout-payment'>
					      <div class='form-row place-order'>
					      	 <input type='hidden' name='course_id' value='{$course_id}'>
					      	 <input type='hidden' name='trial_no' value='{$trial_no}'>
					         <input type='hidden' name='event' value='essai_register'>
					         <input type='submit' class='button alt' name='submit' id='place_order' value='Commander'>
					      </div>
					   </div>
					</div>
					</form>
					</div>";
		} else {
			$html = "<div class='col-md-9'>
					  <section class='blog-main text-center' role='main'>
					    <article class='post-entry text-left'>
					      <div class='entry-main no-img'>
					        <div class='entry-header'>
					          <h1 class='entry-title'>Réservation confirmée</h1>
					        </div>
					        <div class='entry-content'>
					          <div class='woocommerce'>
					            <p class='woocommerce-thankyou-order-received'>Merci. Votre réservation est confirmée.</p>
					            <h2>Détails de la réservation</h2>
					            <table class='shop_table order_details'>
					              <thead>
					                <tr>
					                  <th class='product-name'>Produit</th>
					                </tr>
					              </thead>
					              <tbody>
					                <tr class='order_item'>
					                  <td class='product-name'>
					                    ${course_title}
					                  </td>
					                </tr>
					              </tbody>
					            </table>
					            <header>
					              <h2>Détails client</h2>
					            </header>
					            <table class='shop_table customer_details'>
					              <tbody>
					                <tr>
					                  <th>E-mail&nbsp;:</th>
					                  <td>{$_POST['billing_email']}</td>
					                </tr>
					                <tr>
					                  <th>Téléphone&nbsp;:</th>
					                  <td>{$_POST['billing_phone']}</td>
					                </tr>
					              </tbody>
					            </table>
					            <header class='title'>
					              <h3>Adresse</h3>
					            </header>
					            <address>
					              {$_POST['billing_first_name']} {$_POST['billing_last_name']}<br> {$_POST['billing_address_1']} <br>{$_POST['billing_postcode']} {$_POST['billing_city']}
					            </address>
					          </div>
					        </div>
					      </div>
					    </article>
					  </section>
					</div>";
			FC_Shortcode::send_mail_registration();
		}
		return $html;
	}
	
	public static function find_product( $course_id, $trial_no ) {
		global $wpdb;
		$table_prefix = $wpdb->prefix . "francais_";
		$sql = "SELECT
					c.course_id, ct.start_date, ct.start_time,
					CONCAT(p.first_name, ' ', p.family_name) prof_name,
					CONCAT(r.room_name, ', ', r.address, ', ', r.zip_code) room_info, r.city room_city,
					d.age_group, d.micro_discipline, d.lesson_duration
				FROM  {$table_prefix}course_trial ct
				    LEFT JOIN {$table_prefix}course c USING (course_id)
					LEFT JOIN {$table_prefix}discipline d USING (discipline_id)
					LEFT JOIN {$table_prefix}room r USING (room_id)
					LEFT JOIN {$table_prefix}profs p USING (profs_id)
				WHERE ct.course_id = %d AND ct.trial_no = %d";
		$sql = $wpdb->prepare($sql, $course_id, $trial_no);
		$result = $wpdb->get_row($sql);
		return $result;
	}
	
	public static function send_mail_registration() {
		$first_name = $_POST['billing_first_name'];
		include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
		$cities = FC_Util::get_cities_list();
		$micro_list = FC_Util::get_micro_discipline_list();
		
 		global $AGE_GROUP;
		
 		$course = FC_Shortcode::find_product($_POST['course_id'], $_POST['trial_no']);// find course
		$content = "";
		if ($course !== NULL) {
			setlocale(LC_TIME, get_locale());
			$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
			$to_time = $from_time + $course->lesson_duration * 60;
			$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();
	
			$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
			$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
			$start_date_str = strftime("%d %b %Y", $start_date);
			$day_of_week = strftime("%A", $start_date);
	
			$content = "Bonjour {FIRST_NAME},<br/>
				<br/>
Ravi de vous accueillir chez nous au Club Français ! Une séance d'essai est sans doute le meilleur moyen de commencer !<br/>
				<br/>
				Nous vous confirmons quelques détails pratiques pour votre cours de {MICRO_DISCIPLINE} pour {AGE_GROUP} :<br/>
				- Date : le {START_DATE}<br/>
				- Lieu : {ROOM_INFO}<br/>
				- Horaires : de {START_TIME} à {END_TIME}<br/>
				<br/>
				On espère vraiment que vous allez vous amuser et faire rapidement de jolis progrès avec votre professeur {PROF_NAME} :-)<br/>
				<br/>
				A bientôt,<br/>
				Célestine, du service client";
			$content = str_replace("{FIRST_NAME}", $first_name, $content);
			$content = str_replace("{ROOM_INFO}", $course->room_info . ", " . $cities[$course->room_city], $content);
			$content = str_replace("{PROF_NAME}", $course->prof_name, $content);
			$content = str_replace("{MICRO_DISCIPLINE}", $micro_list[$course->micro_discipline], $content);
			$content = str_replace("{AGE_GROUP}", $AGE_GROUP[$course->age_group], $content);
			$content = str_replace("{START_DATE}", $start_date_str, $content);
			$content = str_replace("{START_TIME}", $from_time_str, $content);
			$content = str_replace("{END_TIME}", $to_time_str, $content);
		}
	
		$course_title = FC_Shortcode::build_essai_title($_POST['course_id'], $_POST['trial_no']);
		$html = "<table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color:#fdfdfd;border:1px solid #dcdcdc;border-radius:3px!important'>
				  <tbody>
				    <tr>
				      <td align='center' valign='top'>
				        <table border='0' cellpadding='0' cellspacing='0' width='600' style='background-color:#557da1;border-radius:3px 3px 0 0!important;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif'>
				          <tbody>
				            <tr>
				              <td style='padding:36px 48px;display:block'>
				                <h1 style='color:#ffffff;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left'>
									Votre séance d’essai est confirmée.
				                </h1>
				              </td>
				            </tr>
				          </tbody>
				        </table>
				      </td>
				    </tr>
				    <tr>
				      <td align='center' valign='top'>
				        <span class='HOEnZb'><font color='#888888'>
				        </font></span>
				        <table border='0' cellpadding='0' cellspacing='0' width='600'>
				          <tbody>
				            <tr>
				              <td valign='top' style='background-color:#fdfdfd'>
				                <span class='HOEnZb'><font color='#888888'>
				                </font></span>
				                <table border='0' cellpadding='20' cellspacing='0' width='100%'>
				                  <tbody>
				                    <tr>
				                      <td valign='top' style='padding:48px'>
				                        <div style='color:#737373;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left'>
				                          {$content}
				                          <span class='HOEnZb'><font color='#888888'>
				                          </font></span>
				                          <table cellspacing='0' cellpadding='0' style='width:100%;vertical-align:top' border='0'>
				                            <tbody>
				                              <tr>
				                                <td valign='top' width='50%'>
				                                  <h3 style='color:#557da1;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:16px;font-weight:bold;line-height:130%;margin:16px 0 8px;text-align:left'>Détails des coordonnées</h3>
				                                  <p style='color:#505050;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;margin:0 0 16px'>
													{$_POST['billing_first_name']} {$_POST['billing_last_name']}<br> {$_POST['billing_address_1']} <br>{$_POST['billing_postcode']} {$_POST['billing_city']}
				                                  </p>
				                                  <span class='HOEnZb'><font color='#888888'></font></span>
				                                  <ul>
						                            <li>
						                              <strong>E-mail&nbsp;:</strong> <span style='color:#505050;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif'><a href='mailto:{$_POST['billing_email']}' target='_blank'>{$_POST['billing_email']}</a></span>
						                            </li>
						                            <li>
						                              <strong>Tél.:</strong> <span style='color:#505050;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif'>{$_POST['billing_phone']}</span>
						                            </li>
						                          </ul>
				                                </td>
				                              </tr>
				                            </tbody>
				                          </table>
				                          <span class='HOEnZb'><font color='#888888'>
				                          </font></span>
				                        </div>
				                        <span class='HOEnZb'><font color='#888888'>
				                        </font></span>
				                      </td>
				                    </tr>
				                  </tbody>
				                </table>
				                <span class='HOEnZb'><font color='#888888'>
				                </font></span>
				              </td>
				            </tr>
				          </tbody>
				        </table>
				        <span class='HOEnZb'><font color='#888888'>
				        </font></span>
				      </td>
				    </tr>
				    <tr>
				      <td align='center' valign='top'>
				        <table border='0' cellpadding='10' cellspacing='0' width='600'>
				          <tbody>
				            <tr>
				              <td valign='top' style='padding:0'>
				                <table border='0' cellpadding='10' cellspacing='0' width='100%'>
				                  <tbody>
				                    <tr>
				                      <td colspan='2' valign='middle' style='padding:0 48px 48px 48px;border:0;color:#99b1c7;font-family:Arial;font-size:12px;line-height:125%;text-align:center'>
				                        <p>Le Club Français de la Danse et du Théâtre
				                        </p>
				                      </td>
				                    </tr>
				                  </tbody>
				                </table>
				              </td>
				            </tr>
				          </tbody>
				        </table>
				      </td>
				    </tr>
				  </tbody>
				</table>";
		$mailer = WC()->mailer();
		$subject = "Reçu de votre commande du {$start_date_str} sur Le Club Français";
		$mailer->send($_POST['billing_email'], $subject, $html, '', '');
	}
	
	public static function shortcode_banner_research( $atts, $content = "" ) {
		include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
		$cities = FC_Util::get_cities_list();
		$disciplines = FC_Util::get_micro_discipline_list();
		global $AGE_GROUP;
		
		$options = FC_Shortcode::build_data_options();
		$city_options = FC_Shortcode::build_options($options[1], $cities);
		//$age_group_options = FC_Shortcode::build_options($options[2], $AGE_GROUP);
		//$discipline_options = FC_Shortcode::build_options($options[3], $disciplines);
		$script = FC_Shortcode::build_script($options[0], $options[1], $AGE_GROUP, $disciplines);
		
		$html = "<div class='fixed-bottom'>
				  <div class='row'>
				   <div class='col-md-10'>
				    <form id='researchform' method='post'>
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
					   </select></div>
					   <div class='col-md-3'><select id = 'dis' name='dis' class='selectsearch'>
					      <option value='' selected='selected'>Choisissez votre discipline</option>
					   </select></div>
					</div>
				   </div>
				   <div class='col-md-2' style='margin-top: 40px'>
		               <button type='submit' name='event' value='course_category' class='btn-danger btn-lg' style='width: 90%'>Rechercher <span class='glyphicon glyphicon-chevron-right' style='text-align: right;'></span></button>
				   </div>
				   </form>
				  </div>
				</div>
				{$script}";
		return $html;
	}
	
	public static function build_city_tree($data) {
		$result = array();
		$current_city = "";
		$current_age_group = "";
		foreach ($data as $row) {
			if ($row->city !== $current_city) {
				$current_city = $row->city;
				$current_age_group = $row->age_group;
				$result[$current_city] = array();
				$result[$current_city][$current_age_group] = array();
			} else if ($current_age_group !== $row->age_group) {
				$current_age_group = $row->age_group;
				$result[$current_city][$current_age_group] = array();
			}
			
			$result[$current_city][$current_age_group][] = $row->micro_discipline;
		}
		return $result;
	}
	public static function build_script($data, $city_data, $age_group_data, $discipline_data) {
		include_once(FC_PLUGIN_PATH . "includes/class-fc-frontend.php");
		$data = FC_Shortcode::build_city_tree($data);
		$json = json_encode($data);
// 		$json_city = json_encode($city_data);
		$json_age_group = json_encode($age_group_data);
		$json_discipline = json_encode($discipline_data);
		$html .= "
			<script type='text/javascript'>
				var data = {$json};
				var age_groups = {$json_age_group};
				var disciplines = {$json_discipline};
				jQuery('#city').on('change', function() {
					var city = jQuery('#city').val();
					
					jQuery('#age').find('option').remove().end();
					jQuery('#dis').find('option').remove().end();
					jQuery('#age').append(\"<option selected='selected' value=''>Choisissez votre tranche d'age</option>\");
					jQuery('#dis').append(\"<option selected='selected' value=''>Choisissez votre discipline</option>\");
					var values = data[city];
					if (Object.keys(values).length > 0) {
						for (i = 0; i < Object.keys(values).length; i++) {
				 			jQuery('#age').append(\"<option value='\" + Object.keys(values)[i] + \"'>\" + age_groups[Object.keys(values)[i]] + \" </option>\");
				 		}
					}
				});
				jQuery('#age').on('change', function() {
					var city = jQuery('#city').val();
					var age_group = jQuery('#age').val();
					jQuery('#dis').find('option').remove().end();
					jQuery('#dis').append(\"<option selected='selected' value=''>Choisissez votre discipline</option>\");
					var values = data[city][age_group];
					if (Object.keys(values).length > 0) {
						for (i = 0; i < Object.keys(values).length; i++) {
				 			jQuery('#dis').append(\"<option value='\" + values[Object.keys(values)[i]] + \"'>\" + disciplines[values[Object.keys(values)[i]]] + \" </option>\");
				 		}
			 		}
				});
			</script>";
		return $html;
	}
	
	public static function shortcode_prof_list( $atts, $content = "" ) {
		global $wpdb;
		$prefix = $wpdb->prefix . "francais_";
		$sql = "SELECT CONCAT(first_name, ' ', family_name) as full_name, photo, micro_discipline_1, city_1, description FROM {$prefix}profs LIMIT 0,4";
		$data = $wpdb->get_results($sql);
		
		$total = count($data);
		if ($total <= 0) {
			return "";
		}
		
		// TODO: will use it.
		$num_of_page = (int) ($total / 12) + ($total % 12 > 0 ? 1 : 0); // TODO: need 12 because option NUMBER OF TEACHER.
		
		include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
		$cities = FC_Util::get_cities_list();
		$micro_list = FC_Util::get_micro_discipline_list();
		
		$html = "<div class='row margin-left-right-20'>";
		$count = 0;
		foreach ($data as $obj) {
			$count++;
			$html .= FC_Shortcode::teacher_content($obj, $cities, $micro_list);
		}
		
		$html .= "</div><script type='text/javascript'>
					function showHideTeacher() {
						alert('SHOW');
					}
				</script>";
			
		return $html;
	}
	
	public static function teacher_content($t, $cities, $micro_list) {
		$img = home_url() . '/' . $t->photo;
		if (empty($t->photo)) {
			$img = plugins_url('../assets/images/no_image_available.png', __FILE__);
		}
		
		$micro_discipline = strtoupper($micro_list[$t->micro_discipline_1]);
		$city = strtoupper($cities[$t->city_1]);
		$full_name = strtoupper($t->full_name);
		
		$html = "<div class='col-lg-3 col-md-3 col-sm-6 col-xs-12 teacher-item'>
					<div class='teacher-image'>
						<div class='teacher-image-avatar'>
							<a href='#' onclick='showHideTeacher();'><img src='{$img}' alt='AMÉLIE' class='img-circle' width='100%'  /></a>
						</div>
						<div class='teacher-image-text'>
							<h4>{$full_name}</h4>
							<h3 class='bottom-border'>{$micro_discipline}</h3>
							<h3>{$city}</h3>
						</div>
					</div>
					<div class='teacher-description text-center hidden'>
						<p class='color-blue'>
							{$t->description}
						</p>
						<p><a href='#' class='teacher-back'>RETOUR</a></p>
					</div>
				</div>";
		return $html;
	}
}
endif;
FC_Shortcode::init();