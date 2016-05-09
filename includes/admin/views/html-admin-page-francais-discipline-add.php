<?php
/**
 * Admin View: Main Menu - Francias - Discipline - Add
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
	
	if (intval($_POST['application_fee']) < 0) {
		$result[] = "Frais de dossier must be an unsigned number!";
	}
	
	$uploadedfile = $_FILES['photo'];
	
	$upload_overrides = array( 'test_form' => false );
	
	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
	
	if ( $movefile && ! isset( $movefile['error'] ) ) {
		// echo "File is valid, and was successfully uploaded.\n";
		if (in_array($movefile['type'], array('image/jpeg', 'image/png'))) {
			$img = wp_get_image_editor( $movefile['file'] );
			if ( ! is_wp_error( $img ) ) {
				$img->save($movefile['file']);
				$_POST['photo_text'] = substr($movefile['file'], strlen(ABSPATH));
			}
		} else {
			$result[] = "Photo image must be in jpeg and png format!";
		}
	} else {
		/**
		 * Error generated by _wp_handle_upload()
		 * @see _wp_handle_upload() in wp-admin/includes/file.php
		 */
		$result[] = $movefile['error'];
	}
	
	if (count($result) > 0) {
		unlink($movefile['file']);
	}
	
	return $result;
}
//insert
if(isset($_POST['createdisciplinesubmit']) || isset($_POST['createdisciplineandcontinue'])){
	global $wpdb;
	$errors = validate_input();
	$result = false;
	
	if (count($errors) === 0) {
		$result = $wpdb->insert(
			$wpdb->prefix . 'francais_discipline', //table
			array(
				'course_type' => $_POST['course_type'],
				'macro_discipline' => $_POST['macro_discipline'],
				'micro_discipline' => $_POST['micro_discipline'],
				'age_group' => $_POST['age_group'],
				'short_description' => stripslashes_deep($_POST['short_description']),
				'discipline_description' => stripslashes_deep($_POST['discipline_description']),
				'lesson_target' => stripslashes_deep($_POST['lesson_target']),
				'lesson_duration' => intval($_POST['lesson_duration']),
				'price' => intval($_POST['price']),
				'application_fee' => intval($_POST['application_fee']),
				'photo'  => $_POST['photo_text']
			), //data
			array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s') //data format
		);
	}
	
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
		if (count($errors) == 0) {
			$message = "Insert failure, Debug sql: " . $wpdb->last_query;
		} else {
			$message = implode("<br/>", $errors);
		}
	}
}
?>
<div class="wrap">
	<h1>Add New Formule de cours</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result !== FALSE ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Create a new Formule de cours and add them to this site.</p>
	<form method="post" name="creatediscipline" id="creatediscipline" class="validate" enctype="multipart/form-data"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="course_type">Type de cours</label></th>
					<td><select name="course_type" id="course_type" class="selectbox-general">
							<?php global $COURSE_TYPE; foreach ($COURSE_TYPE as $course_type_key => $course_type) {?>
							<option value="<?= $course_type_key ?>" <?php echo ($_POST['course_type'] == $course_type_key ? "selected='selected'" : "") ?>><?= $course_type ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="macro_discipline">Macro discipline</label></th>
					<td><select name="macro_discipline" id="macro_discipline" class="selectbox-general">
							<?php global $MARCO_DISCIPLINE; foreach ($MARCO_DISCIPLINE as $macro_key => $macro_value) {?>
							<option value="<?= $macro_key ?>" <?php echo ($_POST['macro_discipline'] == $macro_key ? "selected='selected'" : "") ?>><?= $macro_value ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline">Micro discipline <span class="description">(required)</span></label></th>
					<td><select name="micro_discipline" id="micro_discipline" class="selectbox-general">
						<?php global $MICRO_DISCIPLINE;
							$macro_discipline = "Dance";
							if (isset($_POST['macro_discipline'])) {
								$macro_discipline = $_POST['macro_discipline'];
							}
							$micro_discipline = $MICRO_DISCIPLINE[$macro_discipline];
							foreach ($micro_discipline as $micro_key => $micro_value) {?>
								<option value="<?= $micro_value ?>" <?php echo ($_POST['micro_discipline'] == $micro_value ? "selected='selected'" : "") ?>><?= $micro_value ?></option>
						<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="age_group">Tranche age</label></th>
					<td><select name="age_group" id="age_group" class="selectbox-general">
							<?php global $AGE_GROUP; foreach ($AGE_GROUP as $age_key => $age_value) {?>
							<option value="<?= $age_key ?>" <?php echo ($_POST['age_group'] == $age_key ? "selected='selected'" : "") ?>><?= $age_value ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="short_description">Short Description</label></th>
					<td>
						<input id="short_description" name="short_description" type="text" value="<?= $_POST['short_description'] ?>"> 
					</td>
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
						
						wp_editor($_POST["discipline_description"], "disciplinedescription", $settings);
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
						
						wp_editor($_POST["lesson_target"], "lessontarget", $settings);
						?>
					</td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="lesson_duration">Durée du cours (minutes) <span class="description">(required)</span></label></th>
					<td><input name="lesson_duration" type="number" id="lesson_duration" value="<?= $_POST['lesson_duration'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="price">Prix (€) <span class="description">(required)</span></label></th>
					<td><input name="price" type="number" id="price" value="<?= $_POST['price'] ?>" size="30" 
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="application_fee">Frais de dossier (€)</label></th>
					<td><input name="application_fee" type="number" id="application_fee" value="<?= $_POST['application_fee'] ?>" size="30"
							onkeypress='return is_number(event);'
							placeholder="Only number" style="width: 10%"></td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="photo">Photo</label></th>
					<td><img id="photo_img" src="#" alt="Preview Photo" /> <input type="file" name="photo" id="photo"></td>
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

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            jQuery('#photo_img').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

jQuery("#photo").change(function(){
    readURL(this);
});
</script>