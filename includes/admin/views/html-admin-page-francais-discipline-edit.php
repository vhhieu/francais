<?php
/**
 * Admin View: Main Menu - Francias - Discipline - Edit
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

function validate_input() {
	$result = array();
	if (empty($_POST['lesson_duration'])) {
		$result[] = "Durée du cours is required!";
	} else if (intval($_POST['lesson_duration']) <= 0) {
		$result[] = "Durée du cours must be an unsigned number!";
	}
	
	if (empty($_POST['price'])) {
		$result[] = "Prix is required!";
	} else if (intval($_POST['price']) <= 0) {
		$result[] = "Prix must be an unsigned number!";
	}
	
	if (empty($_POST['application_fee'])) {
		$result[] = "Frais de dossier is required!";
	} else if (intval($_POST['application_fee']) <= 0) {
		$result[] = "Durée du cours must be an unsigned number!";
	}
	
	return $result;
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
	$errors = validate_input();
	$result = false;
	
	if (count($errors) === 0) {
		$result = $wpdb->update(
				$wpdb->prefix . 'francais_discipline', //table
				array(
						'course_type' => $_POST['course_type'],
						'macro_discipline' => $_POST['macro_discipline'],
						'micro_discipline' => $_POST['micro_discipline'],
						'age_group' => $_POST['age_group'],
						'discipline_description' => stripslashes_deep($_POST['discipline_description']),
						'lesson_target' => stripslashes_deep($_POST['lesson_target']),
						'lesson_duration' => intval($_POST['lesson_duration']),
						'price' => intval($_POST['price']),
						'application_fee' => intval($_POST['application_fee']),
				), //data
				array('discipline_id' => $_POST['discipline_id']),
				array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'), //data format
				array("%d")
		);
	}
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result !== FALSE) {
		// redirect
		$message.="Formule de cours updated successful!";
	} else {
		if (count($errors) == 0) {
			$message = "Update failure, Debug sql: " . $wpdb->last_query;
		} else {
			$message = implode("<br/>", $errors);
		}
	}
	
	$data = $_POST;
}
?>
<div class="wrap">
	<h1>Edit Formule de cours <a
			href="<?php echo admin_url('admin.php?page=francais-discipline-add'); ?>"
			class="page-title-action">Add New</a></h1>
	<?php if (isset($message)): ?><div class="<?php echo $result !== FALSE ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Edit Formule de cours information.</p>
	<form method="post" name="updatediscipline" id="updatediscipline" class="validate"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input name="discipline_id" type="hidden" id="discipline_id" value="<?= $data['discipline_id'] ?>"/>

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="course_type">Type de cours</label></th>
					<td><select name="course_type" id="course_type" class="selectbox-general">
							<?php global $COURSE_TYPE; foreach ($COURSE_TYPE as $course_type_key => $course_type) {?>
							<option value="<?= $course_type_key ?>" <?php echo ($data['course_type'] == $course_type_key ? "selected='selected'" : "") ?>><?= $course_type ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="macro_discipline">Macro discipline</label></th>
					<td><select name="macro_discipline" id="macro_discipline" class="selectbox-general">
							<?php global $MARCO_DISCIPLINE; foreach ($MARCO_DISCIPLINE as $macro_key => $macro_value) {?>
							<option value="<?= $macro_key ?>" <?php echo ($data['macro_discipline'] == $macro_key ? "selected='selected'" : "") ?>><?= $macro_value ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline">Micro discipline <span class="description">(required)</span></label></th>
					<td><select name="micro_discipline" id="micro_discipline" class="selectbox-general">
						<?php global $MICRO_DISCIPLINE;
							$macro_discipline = "Dance";
							if (isset($data['macro_discipline'])) {
								$macro_discipline = $data['macro_discipline'];
							}
							$micro_discipline = $MICRO_DISCIPLINE[$macro_discipline];
							foreach ($micro_discipline as $micro_key => $micro_value) {?>
								<option value="<?= $micro_value ?>" <?php echo ($data['micro_discipline'] == $micro_value ? "selected='selected'" : "") ?>><?= $micro_value ?></option>
						<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="age_group">Tranche age</label></th>
					<td><select name="age_group" id="age_group" class="selectbox-general">
							<?php global $AGE_GROUP; foreach ($AGE_GROUP as $age_key => $age_value) {?>
							<option value="<?= $age_key ?>" <?php echo ($data['age_group'] == $age_key ? "selected='selected'" : "") ?>><?= $age_value ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="discipline_description">Description du cours</label></th>
					<td>
						<?php
						$settings =array(
						    'wpautop' => true,
							"textarea_name" => "discipline_description",
						    'media_buttons' => false,
						    'quicktags' => true
						);
						
						wp_editor($data["discipline_description"], "disciplinedescription", $settings);
						?>
					</td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="lesson_target">A qui s'adresse ce cours</label></th>
					<td>
						<?php
						$settings =array(
						    'wpautop' => true,
							"textarea_name" => "lesson_target",
						    'media_buttons' => false,
						    'quicktags' => true
						);
						
						wp_editor($data["lesson_target"], "lessontarget", $settings);
						?>
					</td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="lesson_duration">Durée du cours (minutes) <span class="description">(required)</span></label></th>
					<td><input name="lesson_duration" type="number" id="lesson_duration" value="<?= $data['lesson_duration'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="price">Prix (€) <span class="description">(required)</span></label></th>
					<td><input name="price" type="number" id="price" value="<?= $data['price'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="application_fee">Frais de dossier (€) <span class="description">(required)</span></label></th>
					<td><input name="application_fee" type="number" id="application_fee" value="<?= $data['application_fee'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
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
<script type="text/javascript">
var micro_discipline = {};
<?php global $MICRO_DISCIPLINE; foreach ($MICRO_DISCIPLINE as $marco => $discipline) {?>
micro_discipline['<?= $marco ?>'] = {};
<?php foreach ($discipline as $key => $value) {?>
micro_discipline['<?= $marco ?>']['<?= $key ?>'] = '<?= $value ?>';
<?php }}?>

jQuery('select[name="macro_discipline"]').change(
    function(){
    	var md = jQuery(this).val();
        jQuery('select[name="micro_discipline"]').find('option').remove().end();
        var arr = micro_discipline[md];
 		for (i = 0; i < Object.keys(arr).length; i++) {
 			jQuery('select[name="micro_discipline"]').append("<option value='" + arr[i] + "'>" + arr[i] + " </option>");
 		}         
    });
function is_number(event) {
	
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 46
     || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 38 || event.keyCode == 40) {
        return true;
    } else if ( key < 48 || key > 57 ) {
        return false;
    }
    else return true;
}
</script>