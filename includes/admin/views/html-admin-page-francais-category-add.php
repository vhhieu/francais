<?php
/**
 * Admin View: Main Menu - Francias - Category - Add
 *
 * @var string $view
 */
if (! defined ( 'ABSPATH' )) {
	exit ();
}

function validate_input() {
	$result = array();
	if (empty($_POST['slug'])) {
		$result[] = "slug is required!";
	}
	
	if (empty($_POST['category_name'])) {
		$result[] = "Category Name is required!";
	}
	
	if (empty($_POST['category_name_with_city'])) {
		$result[] = "Category Name(Ville) is required!";
	}
	
	if (empty($_POST['description'])) {
		$result[] = "Description is required!";
	}
	
	if (empty($_POST['description_with_city'])) {
		$result[] = "Description(Ville) is required!";
	}
	
	if (empty($_POST['title'])) {
		$result[] = "Title is required!";
	}
	
	if (empty($_POST['title_with_city'])) {
		$result[] = "Title(Ville) is required!";
	}
	
	if (empty($_POST['meta_description'])) {
		$result[] = "Meta Description is required!";
	}
	
	if (empty($_POST['meta_description_with_city'])) {
		$result[] = "Meta Description(Ville) is required!";
	}
	
	if (empty($_POST['meta_keyword'])) {
		$result[] = "Meta Keyword is required!";
	}
	
	if (empty($_POST['meta_keyword_with_city'])) {
		$result[] = "Meta Keyword(Ville) is required!";
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
if(isset($_POST['createcategorysubmit']) || isset($_POST['createcategoryandcontinue'])){
	$_POST      = array_map('stripslashes_deep', $_POST);
	global $wpdb;
	$errors = validate_input();
	$result = false;
	
	if (count($errors) === 0) {
		$result = $wpdb->insert(
			$wpdb->prefix . 'francais_category', //table
			array(
				'macro_discipline' => $_POST['macro_discipline'],
				'micro_discipline' => $_POST['micro_discipline'],
				'age_group' => $_POST['age_group'],
				'slug' => stripslashes($_POST['slug']),
				'category_name' => stripslashes($_POST['category_name']),
				'category_name_with_city' => stripslashes($_POST['category_name_with_city']),
				'description' => stripslashes($_POST['description']),
				'description_with_city' => stripslashes($_POST['description_with_city']),
				'title' => stripslashes($_POST['title']),
				'title_with_city' => stripslashes($_POST['title_with_city']),
				'meta_keyword' => stripslashes($_POST['meta_keyword']),
				'meta_keyword_with_city' => stripslashes($_POST['meta_keyword_with_city']),
				'meta_description' => stripslashes($_POST['meta_description']),
				'meta_description_with_city' => stripslashes($_POST['meta_description_with_city']),
				'photo'  => $_POST['photo_text']
			), //data
			array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s') //data format
		);
	}
	
	//wp_die(var_dump( $wpdb->last_query ));
	if ($result !== FALSE) {
		if (!term_exists($_POST['slug'], 'course_cate')) {
			wp_insert_term($_POST['slug'], 'course_cate', array('description' => $_POST['slug'], 'slug' => $_POST['slug']));
		}
		
		if ($_POST['createcategorysubmit']) {
			// redirect
			wp_redirect( home_url() . "/wp-admin/admin.php?page=francais-category", 301);
			exit();
		} else {
			$_POST = array();
			$message.="Category inserted";
		}
	} else {
		if (count($errors) == 0) {
			$message = "Insert failure, Debug sql: " . $wpdb->last_query;
		} else {
			$message = implode("<br/>", $errors);
		}
	}
}

include_once(FC_PLUGIN_PATH . "includes/admin/class-fc-util.php");
$micro_arr = FC_Util::get_micro_discipline_array();
?>
<div class="wrap">
	<h1>Add New Category</h1>
	<?php if (isset($message)): ?><div class="<?php echo $result !== FALSE ? "updated": "error" ?>"><p><?php echo $message;?></p></div><?php endif;?>
	<p>Create a new Category and add them to this site.</p>
	<form method="post" name="createcategory" id="createcategory" class="validate" enctype="multipart/form-data"
		novalidate="novalidate" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

		<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="macro_discipline">Macro discipline <span class="description">(required)</span></label></th>
					<td><select name="macro_discipline" id="macro_discipline" class="selectbox-general">
							<?php global $MARCO_DISCIPLINE; foreach ($MARCO_DISCIPLINE as $macro_key => $macro_value) {?>
							<option value="<?= $macro_key ?>" <?php echo ($_POST['macro_discipline'] == $macro_key ? "selected='selected'" : "") ?>><?= $macro_value ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="micro_discipline">Micro discipline</label></th>
					<td><select name="micro_discipline" id="micro_discipline" class="selectbox-general">
						<option <?php echo $_POST['micro_discipline'] == "" ? "selected='selected'" : ""?> value="">Neutre</option>
						<?php global $MICRO_DISCIPLINE;
							$macro_discipline = "danse";
							if (isset($_POST['macro_discipline'])) {
								$macro_discipline = $_POST['macro_discipline'];
							}
							$micro_discipline = $micro_arr[$macro_discipline];
							foreach ($micro_discipline as $micro_key => $micro_value) {?>
								<option value="<?= $micro_key ?>" <?php echo ($_POST['micro_discipline'] == $micro_key ? "selected='selected'" : "") ?>><?= $micro_value ?></option>
						<?php }?>
					</select></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="age_group">Tranche d'âge</label></th>
					<td><select name="age_group" id="age_group" class="selectbox-general">
							<option <?php echo $_POST['age_group'] == "" ? "selected='selected'" : ""?> value="">Neutre</option>
							<?php global $AGE_GROUP; foreach ($AGE_GROUP as $age_key => $age_value) {?>
							<option value="<?= $age_key ?>" <?php echo ($_POST['age_group'] == $age_key ? "selected='selected'" : "") ?>><?= $age_value ?></option>
							<?php }?>
					</select></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="category_name">Category name <span class="description">(required)</span></label></th>
					<td>
						<input id="category_name" name="category_name" type="text" value="<?= $_POST['category_name'] ?>"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="category_name_with_city">Category name(Ville)<span class="description">(required)</span></label></th>
					<td>
						<input id="category_name_with_city" name="category_name_with_city" type="text" value="<?= $_POST['category_name_with_city'] ?>"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="slug">Slug <span class="description">(required)</span></label></th>
					<td>
						<span>cours-de-</span><input id="slug" name="slug" type="text" value="<?= $_POST['slug'] ?>" style="width: 80%"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="description">Description <span class="description">(required)</span></label></th>
					<td>
						<?php
						$settings =array(
						    'wpautop' => true,
							"textarea_name" => "description",
						    'media_buttons' => false,
						    'quicktags' => true
						);
						
						wp_editor($_POST["description"], "description", $settings);
						?>
					</td>
				</tr>
				
				<tr class="form-field form-required">
					<th scope="row"><label for="description_with_city">Description (City) <span class="description">(required)</span></label></th>
					<td>
						<?php
						$settings =array(
						    'wpautop' => true,
							"textarea_name" => "description_with_city",
						    'media_buttons' => false,
						    'quicktags' => true
						);
						
						wp_editor($_POST["description_with_city"], "descriptionwithcity", $settings);
						?>
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="title">Title <span class="description">(required)</span></label></th>
					<td>
						<input id="title" name="title" type="text" value="<?= $_POST['title'] ?>"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="title_with_city">Title (Ville)<span class="description">(required)</span></label></th>
					<td>
						<input id="title_with_city" name="title_with_city" type="text" value="<?= $_POST['title_with_city'] ?>"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="meta_keyword">Meta Keyword <span class="description">(required)</span></label></th>
					<td>
						<input id="meta_keyword" name="meta_keyword" type="text" value="<?= $_POST['meta_keyword'] ?>"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="meta_keyword_with_city">Meta Keyword (Ville) <span class="description">(required)</span></label></th>
					<td>
						<input id="meta_keyword_with_city" name="meta_keyword_with_city" type="text" value="<?= $_POST['meta_keyword_with_city'] ?>"> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="meta_description">Meta Description <span class="description">(required)</span></label></th>
					<td>
						<textarea rows="4" cols="50" id="meta_description" name="meta_description"><?= $_POST['meta_description'] ?></textarea> 
					</td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="meta_description_with_city">Meta Description (Ville) <span class="description">(required)</span></label></th>
					<td>
						<textarea rows="4" cols="50" id="meta_description_with_city" name="meta_description_with_city"><?= $_POST['meta_description_with_city'] ?></textarea> 
					</td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="photo">Photo</label></th>
					<td><img id="photo_img" src="#" alt="Preview Photo" /> <input type="file" name="photo" id="photo"></td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="createcategorysubmit" id="createcategorysubmit"
				class="button button-primary" value="Add New Category">
			<input type="submit" name="createcategoryandcontinue" id="createcategoryandcontinue"
				class="button button-primary" value="Add and Continue">
			<a href="<?= home_url() . "/wp-admin/admin.php?page=francais-category" ?>" class="button button-primary">Back to Category List</a>
		</p>
	</form>
</div>
<script type="text/javascript">
var micro_discipline = {};
<?php foreach ($micro_arr as $marco => $discipline) {?>
micro_discipline['<?= $marco ?>'] = {};
<?php foreach ($discipline as $key => $value) {?>
micro_discipline['<?= $marco ?>']['<?= $key ?>'] = '<?= $value ?>';
<?php }}?>

jQuery('select[name="macro_discipline"]').change(
    function(){
    	var md = jQuery(this).val();
        jQuery('select[name="micro_discipline"]').find('option').remove().end();
        var arr = micro_discipline[md];
        jQuery('select[name="micro_discipline"]').append("<option value=''>Neutre</option>");
 		for (i = 0; i < Object.keys(arr).length; i++) {
 			jQuery('select[name="micro_discipline"]').append("<option value='" + Object.keys(arr)[i] + "'>" + arr[Object.keys(arr)[i]] + " </option>");
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