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
		    <?php echo "<b>---------- DECOUVREZ NOS COURS DE {$discipline} POUR {$age_group} A {$city} ----------</b>"; ?> 
		</div>
<?php // find course
	$courses = array('1', '2', '3');
	foreach ($courses as $course) {
    	render_course($course);		
    }
?>
	</div>	
</div>
<?php } // END main contents
get_footer();
?>

<?php 
function render_course($course) {
	$html = "";
	$html ="
 		<div class='row' style='margin-top: 15px;'>
 		    <div class='col-md-3'><a href='#'><img src='http://localhost/francais/wp-content/uploads/2016/04/img.png' /></a></div>
 		    <div class='col-md-9'>
 				<p><b><u><a href='#'>COURS DE CLIP DANSE ENFANTS A NANTES LE VENDREDI DE 17h à 18h</a></u></b></p>
 				<br/>
 				<p>Jour et horaire du cours: samedi et dimanche de 9h à 17h</p>
 				<br/>
 				<p>Lieu: Nantes Power, 22 rue Jean Jaures, 44002 Nantes</p>
 				<br/>
 				<p>Professeur: Antonio Drillado</p>
 		        <br/>
 				<p>Description: Short Description of course</p>
 		        <br/>
 				<p>Séance d'essai: Non</p>
 				<p><a class='btn btn-info' href='#'>Je decouvre ! <span class='glyphicon glyphicon-chevron-right'></span></a></p>
 			</div>
 		</div>
 	";
	echo $html;
}
?>