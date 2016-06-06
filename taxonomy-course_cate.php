<?php
/**
 * The course_cate Taxonomy template file.
 *
 * @since onetake 1.0.0
 */

global $wpdb;
$slug = get_query_var ( 'course_cate' );

include_once(WP_PLUGIN_DIR . "/francais/includes/admin/class-fc-util.php");
$cities = FC_Util::get_cities_list();
$micro_list = FC_Util::get_micro_discipline_list();
//$micro_arr = FC_Util::get_micro_discipline_array();

$prefix = $wpdb->prefix . "francais_";
$category_table = $prefix . "category";
$sql = "SELECT * FROM {$category_table} WHERE slug = %s";
$sql = $wpdb->prepare ( $sql, $slug );

$result = $wpdb->get_row ( $sql );

if (! $result) {
	wp_redirect( home_url( '404' ), 302 );
	exit ();
}
$city = "";
if (isset($_GET['city'])) {
	$city = $_GET['city'];
	if (!term_exists($city, 'city')) {
		wp_redirect( home_url( '404' ), 302 );
		exit ();
	}
}

$description = $result->description;
$title = $result->title;
$meta_keyword = $result->meta_keyword;
$meta_description = $result->meta_description;

if (!empty($city)) {
	$city_val = $cities[$city];
	$description = $result->description_with_city;
	$description = str_replace("{{CITY}}", $city_val, $description);
	$title = $result->title_with_city;
	$title  = str_replace("{{CITY}}", $city_val, $title);
	$meta_keyword = $result->meta_keyword_with_city;
	$meta_keyword = str_replace("{{CITY}}", $city_val, $meta_keyword);
	$meta_description = $result->meta_description_with_city;
	$meta_description = str_replace("{{CITY}}", $city_val, $meta_description);
}

$age_group = $result->age_group;
$macro_discipline = $result->macro_discipline;
$micro_discipline = $result->micro_discipline;
$cate_img = home_url() . "/" . $result->photo;

get_header();
?>
<div class="breadcrumb-box">
	<div class="container">
    	<h1 class="text-center"><?= $title ?></h1>
    </div>
</div>
<div class="blog-list">
	<div class="container">
		<div class="row">
			<div class='col-md-3'><img src="<?= $cate_img ?>"></div>
			<div class='col-md-9'><?= $description ?></div>
		</div>
		<br/>
		<div class="row">
			<div class='col-md-3'></div>
			<div class='col-md-9'><?php $text = build_title_text($macro_discipline, $micro_discipline, $age_group, $city);
		           echo "<div class='col-md-12'><b>{$text}</b></div>"; ?></div>
		</div>
		<?php // find course
			$courses = find_courses($macro_discipline, $micro_discipline, $age_group, $city);
			if (empty($courses)) {
				echo "<div class='row'><div class='col-md-12'>No course found !</div></div>";		
			} else {
				foreach ($courses as $course) {
			    	render_course($course);		
			    }
			}
		?>
	</div>	
</div>
<?php 
get_footer();

function build_title_text($macro_discipline, $micro_discipline, $age_group, $city) {
	$result = "---------- DECOUVREZ NOS COURS DE ";
	if (empty($micro_discipline)) {
		$result .= $macro_discipline; // TODO: Map to text
	} else {
		$result .= $micro_discipline; // TODO: Map to text
	}
	
	if (!empty($age_group)) {
		global $AGE_GROUP;
		$result .= " POUR {$AGE_GROUP[$age_group]}";
	}
	
	if (!empty($city)) {
		$result .= " A {$city}";
	}
	
	$result .= " ----------";
	return strtoupper($result);
}
function find_courses($macro_discipline, $micro_discipline, $age_group, $city) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$sql = "SELECT c.course_id, c.start_date, c.start_time, c.end_date, c.number_available, c.course_mode, c.trial_mode,
				CONCAT(p.first_name, ' ', p.family_name) profs_name,
				CONCAT(r.room_name, ', ', r.address, ', ', r.zip_code, ', ', r.city) room_info, r.city,
				d.course_type, d.macro_discipline, d.age_group, d.micro_discipline, d.short_description, d.lesson_duration, d.photo,
				po.guid AS course_url
			FROM {$prefix}francais_course c
			LEFT JOIN {$prefix}francais_discipline d USING(discipline_id)
			LEFT JOIN {$prefix}francais_room r USING(room_id)
			LEFT JOIN {$prefix}francais_profs p USING(profs_id)
			LEFT JOIN {$wpdb->prefix}posts po ON c.post_id = po.ID
	WHERE d.macro_discipline = %s\n";
	$sql = $wpdb->prepare($sql, $macro_discipline);
	if (!empty($micro_discipline)) {
		$sql .= "AND d.micro_discipline = %s\n";
		$sql = $wpdb->prepare($sql, $micro_discipline);
	}
	
	if (!empty($city)) {
		$sql .= "AND r.city = %s\n";
		$sql = $wpdb->prepare($sql, $city);
	}
	
	if (!empty($age_group)) {
		$sql .= "AND d.age_group = %s";
		$sql = $wpdb->prepare($sql, $age_group);
	}
	
	$result = $wpdb->get_results( $sql );
	return $result;
}

function render_course($course) {
	setlocale(LC_TIME, get_locale());
	$img_url = home_url() . "/" . $course->photo;
	$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
	$to_time = $from_time + $course->lesson_duration * 60;
	$start_date = DateTime::createFromFormat('Y-m-d', $course->start_date)->getTimestamp();

	$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
	$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
	$start_date_str = strftime("%d %b %Y", $start_date);
	$day_of_week = strftime("%A", $start_date);
	global $AGE_GROUP;

	$title = strtoupper("COURS DE {$course->micro_discipline} {$AGE_GROUP[$course->age_group]} A {$course->city} LE {$day_of_week} DE {$from_time_str} À {$to_time_str}");
	$html ="
 		<div class='row' style='margin-top: 15px;'>
 		    <div class='col-md-3'><a href='{$course->course_url}'><img src='{$img_url}' /></a></div>
 		    <div class='col-md-9'>
 				<p><b><u><a href='{$course->course_url}'>{$title}</a></u></b></p>
 				<br/>
 				<p>Jour et horaire du cours: Tous les {$day_of_week} de {$from_time_str} à {$to_time_str} à partir du {$start_date_str} (hors vacances scolaires)</p>
 				<br/>
 				<p>Lieu: {$course->room_info}</p>
 				<br/>
 				<p>Professeur: {$course->profs_name}</p>
 		        <br/>
 				<p>Description: {$course->short_description}</p>
 		        <br/>
 				<p>Séance d'essai: satisfait ou remboursé !</p>
 				<p><a class='btn btn-info' href='{$course->course_url}'>Je decouvre ! <span class='glyphicon glyphicon-chevron-right'></span></a></p>
 			</div>
 		</div>
 	";
	echo $html;
}
?>