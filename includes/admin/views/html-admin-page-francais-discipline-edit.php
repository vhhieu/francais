<?php
/**
 * Admin View: Main Menu - Francias - Discipline - Edit
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

if ($_SERVER['REQUEST_METHOD'] === "GET") {
	if (isset($_REQUEST['movie'])) {
		global $wpdb;
		$sql = "SELECT * FROM " . $wpdb->prefix . "francais_discipline WHERE discipline_id = %d";
		$obj = $wpdb->get_results($wpdb->prepare($sql, intval($_REQUEST['movie'])));
		$data = json_decode(json_encode($obj), true);
		$data = $data[0];
		if (!$data) {
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-discipline", 301);
			exit();
		}
	}
}

//update
if(isset($_POST['updatedisciplinesubmit'])){
	global $wpdb;

	$result = $wpdb->update(
			$wpdb->prefix . 'francais_discipline', //table
			array(
					'course_type' => $_POST['course_type'],
					'macro_discipline' => $_POST['macro_discipline'],
					'micro_discipline' => $_POST['micro_discipline'],
					'age_group' => $_POST['age_group'],
					'discipline_description' => $_POST['discipline_description'],
					'lesson_target' => $_POST['lesson_target'],
					'lesson_duration' => intval($_POST['lesson_duration']),
					'price' => intval($_POST['price']),
					'application_fee' => intval($_POST['application_fee']),
			), //data
			array('discipline_id' => $_POST['discipline_id']),
			array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'), //data format
			array("%d")
	);
	
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result) {
		// redirect
		$message.="Formule de cours updated successful!";
	} else {
		$message.="Some Data invalid, Please input valid data.";
	}
	
	$data = $_POST;
}
?>
<div class="wrap">
	<h1>Edit Formule de cours</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Edit Formule de cours information.</p>
	<form method="post" name="updatediscipline" id="updatediscipline" class="validate"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input name="discipline_id" type="hidden" id="discipline_id" value="<?= $data['discipline_id'] ?>"/>

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="course_type">Type de cours <span class="description">(required)</span></label></th>
					<td><select name="course_type" id="course_type">
							<option selected="selected" value="Annuel">Annuel</option>
							<option value="Trimestriel">Trimestriel</option>
							<option value="Stage journée">Stage journée</option>
							<option value="Stage WE">Stage WE</option>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="macro_discipline">Macro discipline</label></th>
					<td><select name="macro_discipline" id="macro_discipline">
							<option selected="selected" value="Theatre">Theatre</option>
							<option value="Dance">Dance</option>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline">Micro discipline </label></th>
					<td><input name="micro_discipline" type="text" id="micro_discipline" value="<?= $data['micro_discipline'] ?>" size="30"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="age_group">Macro discipline</label></th>
					<td><select name="age_group" id="age_group">
							<option selected="selected" value="Enfants">Enfants</option>
							<option value="Ado">Ado</option>
							<option value="Adultes">Adultes</option>
							<option value="Seniors">Seniors</option>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="discipline_description">Description du cours</label></th>
					<td><textarea style="height: 320px; margin-top: 37px;" cols="40"
					     name="discipline_description" id="discipline_description"><?= $data['discipline_description'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="lesson_target">A qui s'adresse ce cours</label></th>
					<td><textarea style="height: 320px; margin-top: 37px;" cols="40"
					     name="lesson_target" id="lesson_target"><?= $data['lesson_target'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="max_number">Durée du cours</label></th>
					<td><input name="lesson_duration" type="text" id="lesson_duration" value="<?= $data['lesson_duration'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="area_m2">Prix</label></th>
					<td><input name="price" type="text" id="price" value="<?= $data['price'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="application_fee">Frais de dossier</label></th>
					<td><input name="application_fee" type="text" id="application_fee" value="<?= $data['application_fee'] ?>" size="30"></td>
				</tr>
				
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="updatedisciplinesubmit" id="updatedisciplinesubmit"
				class="button button-primary" value="Update Formule de cours">
			<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-discipline" ?>" class="button button-primary">Back to Formule de cours List</a>
		</p>
	</form>
</div>