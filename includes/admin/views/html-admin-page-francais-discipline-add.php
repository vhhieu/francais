<?php
/**
 * Admin View: Main Menu - Francias - Discipline - Add
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

//insert
if(isset($_POST['createdisciplinesubmit']) || isset($_POST['createdisciplineandcontinue'])){
	global $wpdb;

	$result = $wpdb->insert(
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
			array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d') //data format
	);
	
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result !== FALSE) {
		
		if ($_POST['createdisciplinesubmit']) {
			// redirect
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-discipline", 301);
			exit();
		} else {
			$_POST = array();
			$message.="Discipline inserted";
		}
	} else {
		
		$message.="Some Data invalid, Please input valid data.";
	}
}
?>
<div class="wrap">
	<h1>Add New Formule de cours</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Create a new Formule de cours and add them to this site.</p>
	<form method="post" name="creatediscipline" id="creatediscipline" class="validate"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="course_type">Type de cours</label></th>
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
					<th scope="row"><label for="micro_discipline">Micro discipline <span class="description">(required)</span></label></th>
					<td><input name="micro_discipline" type="text" id="micro_discipline" value="<?= $_POST['micro_discipline'] ?>" size="30"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="age_group">Tranche age</label></th>
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
					     name="discipline_description" id="discipline_description"><?= $_POST['discipline_description'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="lesson_target">A qui s'adresse ce cours</label></th>
					<td><textarea style="height: 320px; margin-top: 37px;" cols="40"
					     name="lesson_target" id="lesson_target"><?= $_POST['lesson_target'] ?></textarea></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="max_number">Durée du cours (minutes) <span class="description">(required)</span></label></th>
					<td><input name="lesson_duration" type="text" id="lesson_duration" value="<?= $_POST['lesson_duration'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="area_m2">Prix (€) <span class="description">(required)</span></label></th>
					<td><input name="price" type="text" id="price" value="<?= $_POST['price'] ?>" size="30"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="application_fee">Frais de dossier (€) <span class="description">(required)</span></label></th>
					<td><input name="application_fee" type="text" id="application_fee" value="<?= $_POST['application_fee'] ?>" size="30"></td>
				</tr>
				
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="createdisciplinesubmit" id="createdisciplinesubmit"
				class="button button-primary" value="Add New Formule de cours">
			<input type="submit" name="createdisciplineandcontinue" id="createdisciplineandcontinue"
				class="button button-primary" value="Add and Continue">
				<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-discipline" ?>" class="button button-primary">Back to Formule de cours List</a>
		</p>
	</form>
</div>