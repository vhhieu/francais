<?php
/**
 * The Dance Taxonomy template file.
 *
 * @since onetake 1.0.0
 */

global $wpdb;

$city = $_GET['city'];
$age_group = $_GET['age'];
$discipline = $_GET['dis'];

$sql = "SELECT d.* FROM wp_francais_discipline d
			INNER JOIN wp_francais_course c USING(discipline_id)
			INNER JOIN wp_francais_room r USING (room_id)
		WHERE d.micro_discipline = %s AND r.city = %s AND d.age_group = %s";

$sql = $wpdb->prepare($sql, $discipline, $city, $age_group);
$results = $wpdb->get_results( $sql );

$cate_content = "";
if (!empty($results)) {
	$obj = $results[0];
	$cate_content = $obj->discipline_description;
	$cate_content = str_replace("{{CITY}}", $city, $cate_content);
}

get_header();
?>
<div class="breadcrumb-box">
	<div class="container">
    	<?php echo "(FOR RESEARCH : {$city} / {$age_group} / {$discipline})" ?>
    </div>
</div>

<?php if (empty($results)) { ?>
<div class="blog-list">
	<div class="container">
		<div class="row">
		   No results found!		   
		</div>
	</div>	
</div>
<?php } else { // START main contents?>
<div class="blog-list">
	<div class="container">
		<div class="row">
			<div class='col-md-3'>City Photo</div>
			<div class='col-md-9'><?= $cate_content ?></div>
		</div>
		<br/>
		<div class="row">
		    <?php echo "<div class='col-md-12'><b>---------- DECOUVREZ NOS COURS DE {$discipline} POUR {$age_group} A {$city} ----------</b></div>"; ?> 
		</div>
<?php // find course
	$courses = find_courses($city, $age_group, $discipline);
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
<?php } // END main contents
get_footer();
?>

<?php 
function find_courses($city, $age_group, $discipline) {
	global $wpdb;
	$sql = "SELECT c.course_id, c.start_date, c.start_time, c.end_date, c.number_available, c.course_mode, c.trial_mode,
			       CONCAT(p.first_name, ' ', p.family_name) profs_name,
			       CONCAT(r.room_name, ', ', r.address, ', ', r.zip_code, ', ', r.city) room_info, r.city,
			       d.course_type, d.macro_discipline, d.age_group, d.micro_discipline, d.short_description, d.lesson_duration, d.photo
			FROM wp_francais_course c
			LEFT JOIN wp_francais_discipline d USING(discipline_id)
			LEFT JOIN wp_francais_room r USING(room_id)
			LEFT JOIN wp_francais_profs p USING(profs_id)
			WHERE d.micro_discipline = %s AND r.city = %s AND d.age_group = %s";
	$sql = $wpdb->prepare($sql, $discipline, $city, $age_group);
	$result = $wpdb->get_results( $sql );
	return $result;
}

function render_course($course) {
	$img_url = home_url() . "/" . $course->photo;
	$from_time = DateTime::createFromFormat('H:i:s', $course->start_time)->getTimestamp();
	$to_time = $from_time + $course->lesson_duration * 60;
	
	$from_time_str = date("H", $from_time) . "h" . date("i", $from_time);
	$to_time_str = date("H", $to_time) . "h" . date("i", $to_time);
	
	$html ="
 		<div class='row' style='margin-top: 15px;'>
 		    <div class='col-md-3'><a href='#'><img src='{$img_url}' /></a></div>
 		    <div class='col-md-9'>
 				<p><b><u><a href='#'>COURS DE {$course->micro_discipline} {$course->age_group} A {$course->city} LE VENDREDI DE 17h à 18h</a></u></b></p>
 				<br/>
 				<p>Jour et horaire du cours: samedi et dimanche de {$from_time_str} à {$to_time_str}</p>
 				<br/>
 				<p>Lieu: {$course->room_info}</p>
 				<br/>
 				<p>Professeur: {$course->profs_name}</p>
 		        <br/>
 				<p>Description: {$course->short_description}</p>
 		        <br/>
 				<p>Séance d'essai: Non</p>
 				<p><a class='btn btn-info' href='#'>Je decouvre ! <span class='glyphicon glyphicon-chevron-right'></span></a></p>
 			</div>
 		</div>
 	";
	echo $html;
}
?>